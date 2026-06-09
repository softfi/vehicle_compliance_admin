<?php

namespace App\Controllers\Api;

class DieselController extends BaseApiController
{
    /**
     * GET /api/diesel/meta?location_id= (optional)
     * Public — no Bearer token required.
     */
    public function meta()
    {
        $locationId = $this->resolveLocationId();

        $pumps = [];
        foreach ($this->adminModel->Get_vendor() as $vendor) {
            if (strcasecmp((string) ($vendor->type ?? ''), 'Pump') !== 0) {
                continue;
            }
            if ($locationId && (int) ($vendor->location ?? 0) !== $locationId) {
                continue;
            }
            $pumps[] = [
                'id'            => (int) $vendor->id,
                'name'          => $vendor->name,
                'location_id'   => $vendor->location ? (int) $vendor->location : null,
                'location_name' => $vendor->location_name ?? null,
                'vendor_rate'   => $vendor->vendor_rate ?? null,
            ];
        }

        $vehicles = [];
        foreach ($this->adminModel->Getvehicle() as $vehicle) {
            if ($locationId && isset($vehicle->location_id) && (int) $vehicle->location_id !== $locationId) {
                continue;
            }
            $vehicles[] = [
                'id'          => (int) $vehicle->id,
                'vehicle_no'  => $vehicle->vehicle_no,
                'location_id' => isset($vehicle->location_id) ? (int) $vehicle->location_id : null,
            ];
        }

        return $this->apiSuccess('Diesel form data loaded.', [
            'pumps'    => $pumps,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * POST /api/diesel/store
     * Public — no Bearer token required.
     * Body: vendor, vehicle, qty, rate, diesel_date (+ optional location_id)
     */
    public function store()
    {
        $payload    = $this->parseRequestPayload();
        $locationId = $this->resolveLocationId($payload);

        $vendorInput  = $this->normalizeLookupText(
            $payload['vendor'] ?? $payload['vendor_name'] ?? ''
        );
        $vehicleInput = $this->normalizeLookupText(
            $payload['vehicle'] ?? $payload['vehicle_no'] ?? ''
        );
        $qty        = $payload['qty'] ?? null;
        $rate       = $payload['rate'] ?? null;
        $dieselDate = trim($payload['diesel_date'] ?? $payload['date'] ?? '');

        $pump    = null;
        $vehicle = null;

        if ($vendorInput !== '') {
            $pumpResult = $this->findPumpByName($vendorInput, $locationId);
            if ($pumpResult['error'] !== null) {
                return $this->apiError($pumpResult['code'], $pumpResult['error'], 400);
            }
            $pump = $pumpResult['row'];
        } elseif ((int) ($payload['vendor_id'] ?? 0) > 0) {
            $pump = $this->db->table('vendor')
                ->where('id', (int) $payload['vendor_id'])
                ->where('type', 'Pump')
                ->get()
                ->getRow();
            if (! $pump) {
                return $this->apiError('10', 'Invalid pump vendor id.', 400);
            }
        }

        if ($vehicleInput !== '') {
            $vehicleResult = $this->findVehicleByNumber($vehicleInput, $locationId);
            if ($vehicleResult['error'] !== null) {
                return $this->apiError($vehicleResult['code'], $vehicleResult['error'], 400);
            }
            $vehicle = $vehicleResult['row'];
        } elseif ((int) ($payload['vehicle_id'] ?? 0) > 0) {
            $vehicle = $this->db->table('vehicle')
                ->where('id', (int) $payload['vehicle_id'])
                ->get()
                ->getRow();
            if (! $vehicle) {
                return $this->apiError('11', 'Invalid vehicle id.', 400);
            }
        }

        $errors = [];
        if (! $pump) {
            $errors[] = 'vendor (pump name) is required';
        }
        if (! $vehicle) {
            $errors[] = 'vehicle (vehicle number) is required';
        }
        if ($qty === null || $qty === '' || ! is_numeric($qty) || (float) $qty <= 0) {
            $errors[] = 'qty must be a positive number';
        }
        if ($rate === null || $rate === '' || ! is_numeric($rate) || (float) $rate <= 0) {
            $errors[] = 'rate must be a positive number';
        }
        if ($dieselDate === '') {
            $errors[] = 'diesel_date is required';
        }

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vendorId  = (int) $pump->id;
        $vehicleId = (int) $vehicle->id;

        $insert = [
            'vendor_id'   => $vendorId,
            'vehicle_id'  => $vehicleId,
            'qty'         => (float) $qty,
            'rate'        => (float) $rate,
            'diesel_date' => $dieselDate,
        ];

        $this->db->table('diselentry')->insert($insert);
        $insertId = (int) $this->db->insertID();

        $this->db->table('activity_logs')->insert([
            'user_id'    => 0,
            'menu'       => 'api_diesel_public',
            'action'     => 'create',
            'model'      => 'diselentry',
            'model_id'   => $insertId,
            'changes'    => json_encode(['data' => $insert, 'source' => 'public_api']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $row = $this->db->query("
            SELECT d.*, v.name AS vendor_name, veh.vehicle_no
            FROM diselentry d
            LEFT JOIN vendor v ON v.id = d.vendor_id
            LEFT JOIN vehicle veh ON veh.id = d.vehicle_id
            WHERE d.diselentry_id = ?
        ", [$insertId])->getRow();

        return $this->apiSuccess('Diesel entry saved successfully.', [
            'entry' => $this->formatDieselRow($row),
        ], 201);
    }

    /**
     * GET /api/diesel?from_date=&to_date=&location_id= (optional)
     * Public — no Bearer token required.
     */
    public function index()
    {
        $fromDate = $this->request->getGet('from_date') ?: date('Y-m-01');
        $toDate   = $this->request->getGet('to_date') ?: date('Y-m-d');

        $locationId = $this->resolveLocationId();

        $builder = $this->db->table('diselentry d');
        $builder->select('d.*, v.name AS vendor_name, veh.vehicle_no, veh.location_id AS vehicle_location_id');
        $builder->join('vendor v', 'v.id = d.vendor_id', 'left');
        $builder->join('vehicle veh', 'veh.id = d.vehicle_id', 'left');
        $builder->where('d.diesel_date >=', $fromDate);
        $builder->where('d.diesel_date <=', $toDate);
        $builder->where('d.deleted_by', null);
        if ($locationId) {
            $builder->where('veh.location_id', $locationId);
        }
        $builder->orderBy('d.diesel_date', 'DESC');
        $builder->orderBy('d.diselentry_id', 'DESC');

        $rows = $builder->get()->getResult();
        $entries = array_map(fn ($row) => $this->formatDieselRow($row), $rows);

        return $this->apiSuccess('Diesel entries loaded.', [
            'from_date' => $fromDate,
            'to_date'   => $toDate,
            'entries'   => $entries,
            'total'     => count($entries),
        ]);
    }

    /**
     * Optional filter: ?location_id= or JSON body location_id
     */
    protected function resolveLocationId(?array $payload = null): ?int
    {
        $fromGet = $this->request->getGet('location_id');
        if ($fromGet !== null && $fromGet !== '') {
            return (int) $fromGet;
        }

        if ($payload !== null && isset($payload['location_id']) && $payload['location_id'] !== '') {
            return (int) $payload['location_id'];
        }

        return null;
    }

    protected function normalizeLookupText($value): string
    {
        $text = trim((string) $value);
        $text = preg_replace('/\s+/', ' ', $text);

        return $text ?? '';
    }

    /**
     * @return array{row: ?object, error: ?string, code: string}
     */
    protected function findPumpByName(string $name, ?int $locationId): array
    {
        $builder = $this->db->table('vendor')->where('type', 'Pump');
        if ($locationId) {
            $builder->where('location', $locationId);
        }
        $candidates = $builder->get()->getResult();

        if ($candidates === []) {
            return [
                'row'   => null,
                'error' => 'No pump vendor found.',
                'code'  => '10',
            ];
        }

        $exact = [];
        $partial = [];
        $needle = strtolower($name);

        foreach ($candidates as $row) {
            $vendorName = $this->normalizeLookupText($row->name ?? '');
            if ($vendorName === '') {
                continue;
            }
            if (strcasecmp($vendorName, $name) === 0) {
                $exact[] = $row;
                continue;
            }
            if (str_contains(strtolower($vendorName), $needle)) {
                $partial[] = $row;
            }
        }

        if (count($exact) === 1) {
            return ['row' => $exact[0], 'error' => null, 'code' => '00'];
        }
        if (count($exact) > 1) {
            $names = implode(', ', array_map(fn ($r) => $r->name, $exact));

            return [
                'row'   => null,
                'error' => "Multiple pumps match '{$name}': {$names}. Please type exact pump name.",
                'code'  => '10',
            ];
        }

        if (count($partial) === 1) {
            return ['row' => $partial[0], 'error' => null, 'code' => '00'];
        }
        if (count($partial) > 1) {
            $names = implode(', ', array_map(fn ($r) => $r->name, $partial));

            return [
                'row'   => null,
                'error' => "Multiple pumps match '{$name}': {$names}. Please type exact pump name.",
                'code'  => '10',
            ];
        }

        return [
            'row'   => null,
            'error' => "Pump '{$name}' not found. Check name from /api/diesel/meta list.",
            'code'  => '10',
        ];
    }

    /**
     * @return array{row: ?object, error: ?string, code: string}
     */
    protected function findVehicleByNumber(string $vehicleNo, ?int $locationId): array
    {
        $builder = $this->db->table('vehicle');
        if ($locationId) {
            $builder->where('location_id', $locationId);
        }
        $candidates = $builder->get()->getResult();

        if ($candidates === []) {
            return [
                'row'   => null,
                'error' => 'No vehicle found.',
                'code'  => '11',
            ];
        }

        $exact = [];
        $partial = [];
        $needle        = strtolower($vehicleNo);
        $needleCompact = preg_replace('/[\s\-]/', '', $needle);

        foreach ($candidates as $row) {
            $no = $this->normalizeLookupText($row->vehicle_no ?? '');
            if ($no === '') {
                continue;
            }
            $noLower   = strtolower($no);
            $noCompact = preg_replace('/[\s\-]/', '', $noLower);

            if (strcasecmp($no, $vehicleNo) === 0 || $noCompact === $needleCompact) {
                $exact[] = $row;
                continue;
            }
            if (str_contains($noLower, $needle) || str_contains($noCompact, $needleCompact)) {
                $partial[] = $row;
            }
        }

        if (count($exact) === 1) {
            return ['row' => $exact[0], 'error' => null, 'code' => '00'];
        }
        if (count($exact) > 1) {
            $nos = implode(', ', array_map(fn ($r) => $r->vehicle_no, $exact));

            return [
                'row'   => null,
                'error' => "Multiple vehicles match '{$vehicleNo}': {$nos}. Please type exact vehicle number.",
                'code'  => '11',
            ];
        }

        if (count($partial) === 1) {
            return ['row' => $partial[0], 'error' => null, 'code' => '00'];
        }
        if (count($partial) > 1) {
            $nos = implode(', ', array_map(fn ($r) => $r->vehicle_no, $partial));

            return [
                'row'   => null,
                'error' => "Multiple vehicles match '{$vehicleNo}': {$nos}. Please type exact vehicle number.",
                'code'  => '11',
            ];
        }

        return [
            'row'   => null,
            'error' => "Vehicle '{$vehicleNo}' not found. Check number from /api/diesel/meta list.",
            'code'  => '11',
        ];
    }

    protected function formatDieselRow(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        $qty  = (float) ($row->qty ?? 0);
        $rate = (float) ($row->rate ?? 0);

        return [
            'diselentry_id' => (int) $row->diselentry_id,
            'vendor_id'     => (int) $row->vendor_id,
            'vendor_name'   => $row->vendor_name ?? null,
            'vehicle_id'    => (int) $row->vehicle_id,
            'vehicle_no'    => $row->vehicle_no ?? null,
            'qty'           => $qty,
            'rate'          => $rate,
            'amount'        => round($qty * $rate, 2),
            'diesel_date'   => $row->diesel_date,
        ];
    }
}
