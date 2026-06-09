<?php

namespace App\Controllers\Api;

class ManageDieselController extends BaseApiController
{
    /**
     * GET /api/manage-diesel/vehicles/active
     * Bearer token required. Optional: ?search=OD16&location_id=
     */
    public function activeVehicles()
    {
        if (! $this->hasRole('4.1') && ! $this->hasRole('4')) {
            return $this->apiError('08', 'You do not have permission to view diesel data.', 403);
        }

        $user       = $this->authUser();
        $locationId = $this->resolveLocationIdForUser($user);
        $search     = trim($this->request->getGet('search') ?? '');
        $today      = date('Y-m-d');

        $builder = $this->db->table('vehicle v');
        $builder->select('v.id, v.vehicle_no, v.location_id, v.chassis_no, v.vehicle_type, l.location_name, vt.type_name AS vehicle_type_name');
        $builder->join('location l', 'l.location_id = v.location_id', 'left');
        $builder->join('vehicle_types vt', 'vt.id = v.vehicle_type', 'left');

        if ($locationId) {
            $builder->where('v.location_id', $locationId);
        }
        if ($search !== '') {
            $builder->like('v.vehicle_no', $search);
        }

        $builder->orderBy('v.id', 'ASC');
        $vehicles = $builder->get()->getResult();

        $driverMap = $this->getCurrentDriverAssignmentsMap($today, $locationId);

        $list = [];
        foreach ($vehicles as $v) {
            $vid = (int) $v->id;
            $assignment = $driverMap[$vid] ?? null;

            $list[] = [
                'id'                => $vid,
                'vehicle_no'        => $v->vehicle_no,
                'location_id'       => $v->location_id ? (int) $v->location_id : null,
                'location_name'     => $v->location_name ?? null,
                'vehicle_type_id'   => $v->vehicle_type ? (int) $v->vehicle_type : null,
                'vehicle_type_name' => $v->vehicle_type_name ?? null,
                'chassis_no'        => $v->chassis_no ?? null,
                'current_driver'    => $assignment ? [
                    'driver_id'   => (int) $assignment->driver,
                    'driver_name' => $assignment->driver_name,
                    'staff_code'  => $assignment->staff_code ?? null,
                ] : null,
            ];
        }

        return $this->apiSuccess('Active vehicles loaded.', [
            'location_id' => $locationId,
            'total'       => count($list),
            'vehicles'    => $list,
        ]);
    }

    /**
     * GET /api/manage-diesel/vehicle/assigned-driver
     * Same logic as Admin::get_vehicle_driver() (extra_diesel module).
     * Query: vehicle_id=54  OR  vehicle_no=OD16L0937
     * POST body also supported: { "vehicle_id": 54 } or { "vehicle_no": "OD16L0937" }
     */
    public function assignedDriver()
    {
        if (! $this->hasRole('4.1') && ! $this->hasRole('4')) {
            return $this->apiError('08', 'You do not have permission to view diesel data.', 403);
        }

        $vehicle = $this->resolveVehicleFromRequest();
        if ($vehicle === null) {
            return $this->apiError('03', 'vehicle_id or vehicle_no is required.', 400);
        }

        $locationId = $this->resolveLocationIdForUser($this->authUser());
        if ($locationId && (int) ($vehicle->location_id ?? 0) !== $locationId) {
            return $this->apiError('12', 'Vehicle does not belong to your location.', 403);
        }

        $assignment = $this->db->table('driver_assignment da')
            ->select('da.id AS assignment_id, da.vehicle_no, da.driver, da.from_date, da.to_date, da.opening_hsd, da.closing_hsd, s.name AS driver_name, s.staff_code, s.tel, s.user_type')
            ->join('staff s', 's.id = da.driver', 'left')
            ->where('da.vehicle_no', (int) $vehicle->id)
            ->where('(da.to_date IS NULL OR da.to_date = "0000-00-00" OR da.to_date >= CURDATE())', null, false)
            ->orderBy('da.id', 'DESC')
            ->get()
            ->getRow();

        if (! $assignment) {
            return $this->apiError('14', 'No driver assigned to this vehicle.', 404);
        }

        return $this->apiSuccess('Assigned driver loaded.', [
            'vehicle' => [
                'id'         => (int) $vehicle->id,
                'vehicle_no' => $vehicle->vehicle_no,
            ],
            'driver' => [
                'driver_id'     => (int) $assignment->driver,
                'driver_name'   => $assignment->driver_name,
                'staff_code'    => $assignment->staff_code ?? null,
                'contact_no'    => $assignment->tel ?? null,
                'user_type'     => $assignment->user_type ?? null,
            ],
            'assignment' => [
                'assignment_id' => (int) $assignment->assignment_id,
                'from_date'     => $assignment->from_date,
                'to_date'       => ($assignment->to_date === '0000-00-00' || $assignment->to_date === '') ? null : $assignment->to_date,
                'opening_hsd'   => $assignment->opening_hsd ?? null,
                'closing_hsd'   => $assignment->closing_hsd ?? null,
            ],
        ]);
    }

    /**
     * GET /api/manage-diesel/entries?from_date=&to_date=&vehicle_id=
     */
    public function entries()
    {
        if (! $this->hasRole('4.1') && ! $this->hasRole('4')) {
            return $this->apiError('08', 'You do not have permission to view diesel data.', 403);
        }

        $user       = $this->authUser();
        $locationId = $this->resolveLocationIdForUser($user);
        $fromDate   = $this->request->getGet('from_date') ?: date('Y-m-01');
        $toDate     = $this->request->getGet('to_date') ?: date('Y-m-d');
        $vehicleId  = (int) ($this->request->getGet('vehicle_id') ?? 0);

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
        if ($vehicleId > 0) {
            $builder->where('d.vehicle_id', $vehicleId);
        }

        $builder->orderBy('d.diesel_date', 'DESC');
        $builder->orderBy('d.diselentry_id', 'DESC');

        $rows = $builder->get()->getResult();

        return $this->apiSuccess('Diesel entries loaded.', [
            'from_date' => $fromDate,
            'to_date'   => $toDate,
            'total'     => count($rows),
            'entries'   => array_map(fn ($row) => $this->formatDieselRow($row), $rows),
        ]);
    }

    /**
     * GET /api/manage-diesel/entries/{id}
     */
    public function show($id = null)
    {
        if (! $this->hasRole('4.1') && ! $this->hasRole('4')) {
            return $this->apiError('08', 'You do not have permission to view diesel data.', 403);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->apiError('03', 'Invalid diesel entry id.', 400);
        }

        $locationId = $this->resolveLocationIdForUser($this->authUser());

        $row = $this->db->query("
            SELECT d.*, v.name AS vendor_name, veh.vehicle_no, veh.location_id AS vehicle_location_id
            FROM diselentry d
            LEFT JOIN vendor v ON v.id = d.vendor_id
            LEFT JOIN vehicle veh ON veh.id = d.vehicle_id
            WHERE d.diselentry_id = ? AND d.deleted_by IS NULL
        ", [$id])->getRow();

        if (! $row) {
            return $this->apiError('13', 'Diesel entry not found.', 404);
        }

        if ($locationId && (int) ($row->vehicle_location_id ?? 0) !== $locationId) {
            return $this->apiError('12', 'Diesel entry is outside your location.', 403);
        }

        return $this->apiSuccess('Diesel entry loaded.', [
            'entry' => $this->formatDieselRow($row),
        ]);
    }

    /**
     * POST /api/manage-diesel/entries
     * Body: vendor_id, vehicle_id, qty, rate, diesel_date
     */
    public function store()
    {
        if (! $this->hasRole('4.1')) {
            return $this->apiError('08', 'You do not have permission to add diesel entry.', 403);
        }

        $payload    = $this->parseRequestPayload();
        $locationId = $this->resolveLocationIdForUser($this->authUser());

        $vendorId   = (int) ($payload['vendor_id'] ?? 0);
        $vehicleId  = (int) ($payload['vehicle_id'] ?? 0);
        $qty        = $payload['qty'] ?? null;
        $rate       = $payload['rate'] ?? null;
        $dieselDate = trim($payload['diesel_date'] ?? $payload['date'] ?? '');

        $errors = [];
        if ($vendorId <= 0) {
            $errors[] = 'vendor_id is required';
        }
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
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

        $pump = $this->db->table('vendor')->where('id', $vendorId)->where('type', 'Pump')->get()->getRow();
        if (! $pump) {
            return $this->apiError('10', 'Invalid pump vendor.', 400);
        }

        $vehicle = $this->db->table('vehicle')->where('id', $vehicleId)->get()->getRow();
        if (! $vehicle) {
            return $this->apiError('11', 'Invalid vehicle.', 400);
        }

        if ($locationId && isset($vehicle->location_id) && (int) $vehicle->location_id !== $locationId) {
            return $this->apiError('12', 'Vehicle does not belong to your location.', 403);
        }

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
            'user_id'    => $this->authUserId(),
            'menu'       => 'api_manage_diesel',
            'action'     => 'create',
            'model'      => 'diselentry',
            'model_id'   => $insertId,
            'changes'    => json_encode(['data' => $insert, 'source' => 'manage_diesel_api']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $row = $this->fetchDieselRow($insertId);

        return $this->apiSuccess('Diesel entry saved successfully.', [
            'entry' => $this->formatDieselRow($row),
        ], 201);
    }

    /**
     * DELETE /api/manage-diesel/entries/{id}
     */
    public function destroy($id = null)
    {
        if (! $this->hasRole('4.1') && ! $this->hasRole('4.2')) {
            return $this->apiError('08', 'You do not have permission to delete diesel entry.', 403);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->apiError('03', 'Invalid diesel entry id.', 400);
        }

        $locationId = $this->resolveLocationIdForUser($this->authUser());

        $record = $this->db->table('diselentry d')
            ->select('d.*, veh.location_id AS vehicle_location_id')
            ->join('vehicle veh', 'veh.id = d.vehicle_id', 'left')
            ->where('d.diselentry_id', $id)
            ->where('d.deleted_by', null)
            ->get()
            ->getRow();

        if (! $record) {
            return $this->apiError('13', 'Diesel entry not found.', 404);
        }

        if ($locationId && (int) ($record->vehicle_location_id ?? 0) !== $locationId) {
            return $this->apiError('12', 'Diesel entry is outside your location.', 403);
        }

        $this->db->table('diselentry')->where('diselentry_id', $id)->update([
            'deleted_by' => $this->authUserId(),
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->apiSuccess('Diesel entry deleted successfully.', []);
    }

    /**
     * GET /api/manage-diesel/pumps
     * Active pump vendors for dropdown.
     */
    public function activePumps()
    {
        if (! $this->hasRole('4.1') && ! $this->hasRole('4')) {
            return $this->apiError('08', 'You do not have permission to view diesel data.', 403);
        }

        $locationId = $this->resolveLocationIdForUser($this->authUser());
        $search     = trim($this->request->getGet('search') ?? '');

        $pumps = [];
        foreach ($this->adminModel->Get_vendor() as $vendor) {
            if (strcasecmp((string) ($vendor->type ?? ''), 'Pump') !== 0) {
                continue;
            }
            if ($locationId && (int) ($vendor->location ?? 0) !== $locationId) {
                continue;
            }
            if ($search !== '' && stripos((string) $vendor->name, $search) === false) {
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

        return $this->apiSuccess('Active pumps loaded.', [
            'location_id' => $locationId,
            'total'       => count($pumps),
            'pumps'       => $pumps,
        ]);
    }

    /**
     * Resolve vehicle by vehicle_id (extra_diesel uses id) or vehicle_no string.
     */
    protected function resolveVehicleFromRequest(): ?object
    {
        $payload = $this->parseRequestPayload();

        $vehicleId = (int) ($this->request->getGet('vehicle_id')
            ?: $payload['vehicle_id']
            ?? 0);

        if ($vehicleId > 0) {
            return $this->db->table('vehicle')->where('id', $vehicleId)->get()->getRow() ?: null;
        }

        $vehicleNo = trim($this->request->getGet('vehicle_no')
            ?: $payload['vehicle']
            ?: $payload['vehicle_no']
            ?? '');

        if ($vehicleNo === '') {
            return null;
        }

        $row = $this->db->table('vehicle')->where('vehicle_no', $vehicleNo)->get()->getRow();
        if ($row) {
            return $row;
        }

        $normalized = preg_replace('/[\s\-]/', '', $vehicleNo);

        return $this->db->query(
            'SELECT * FROM vehicle WHERE REPLACE(REPLACE(vehicle_no, " ", ""), "-", "") = ? LIMIT 1',
            [$normalized]
        )->getRow() ?: null;
    }

    /**
     * @return array<int, object>
     */
    protected function getCurrentDriverAssignmentsMap(string $asOfDate, ?int $locationId): array
    {
        $sql = "
            SELECT da.vehicle_no, da.driver, s.name AS driver_name, s.staff_code, da.id
            FROM driver_assignment da
            INNER JOIN staff s ON s.id = da.driver
            INNER JOIN vehicle v ON v.id = da.vehicle_no
            WHERE da.from_date <= ?
              AND (da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date >= ?)
        ";
        $params = [$asOfDate, $asOfDate];

        if ($locationId) {
            $sql .= ' AND v.location_id = ?';
            $params[] = $locationId;
        }

        $sql .= ' ORDER BY da.id DESC';

        $rows = $this->db->query($sql, $params)->getResult();
        $map  = [];
        foreach ($rows as $row) {
            $vid = (int) $row->vehicle_no;
            if (! isset($map[$vid])) {
                $map[$vid] = $row;
            }
        }

        return $map;
    }

    protected function fetchDieselRow(int $id): ?object
    {
        return $this->db->query("
            SELECT d.*, v.name AS vendor_name, veh.vehicle_no, veh.location_id AS vehicle_location_id
            FROM diselentry d
            LEFT JOIN vendor v ON v.id = d.vendor_id
            LEFT JOIN vehicle veh ON veh.id = d.vehicle_id
            WHERE d.diselentry_id = ?
        ", [$id])->getRow();
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
