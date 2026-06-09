<?php

namespace App\Controllers\Api;

/**
 * APIs for Passenger Vehicle Diesel (admin/passenger_diesel).
 */
class PassengerDieselController extends BaseApiController
{
    /**
     * GET /api/passenger-diesel/issued-by/users
     */
    public function issuedByUsers()
    {
        $search = trim($this->request->getGet('search') ?? '');

        $builder = $this->db->table('user');
        $builder->select('id, full_name, user_name, contact_no, email, user_type, location_id');

        if ($this->db->fieldExists('deleted_by', 'user')) {
            $builder->where('deleted_by', null);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('full_name', $search)
                ->orLike('user_name', $search)
                ->orLike('contact_no', $search)
            ->groupEnd();
        }

        $builder->orderBy('full_name', 'ASC');
        $rows = $builder->get()->getResult();

        $users = [];
        foreach ($rows as $u) {
            $users[] = [
                'id'              => (int) $u->id,
                'full_name'       => $u->full_name,
                'user_name'       => $u->user_name ?? null,
                'contact_no'      => $u->contact_no ?? null,
                'email'           => $u->email ?? null,
                'user_type'       => isset($u->user_type) ? (int) $u->user_type : null,
                'user_type_label' => $this->userTypeLabel($u->user_type ?? null),
                'location_id'     => $u->location_id ? (int) $u->location_id : null,
            ];
        }

        return $this->apiSuccess('Issued by users loaded.', [
            'total' => count($users),
            'users' => $users,
        ]);
    }

    /**
     * GET /api/passenger-diesel
     * Filters: from_date, to_date, vehicle_id (filter_vehicle), location_id (filter_location)
     */
    public function index()
    {
        $parsed = $this->parseDieselListFilters(false);
        if ($parsed['errors'] !== []) {
            return $this->apiError('03', implode('; ', $parsed['errors']), 400);
        }

        $filters  = $parsed['filters'];
        $fromDate = $filters['from_date'];
        $toDate   = $filters['to_date'];
        $vehicleId  = $filters['vehicle_id'] ?? 0;
        $locationId = $filters['location_id'] ?? 0;

        $rows = $this->buildListQuery($fromDate, $toDate, (int) $vehicleId, (int) $locationId)
            ->orderBy('p.entry_date', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResult();

        return $this->apiSuccess('Passenger diesel entries loaded.', [
            'filters' => $filters,
            'total'   => count($rows),
            'entries' => array_map(fn ($r) => $this->formatRow($r), $rows),
        ]);
    }

    /**
     * GET /api/passenger-diesel/{id}
     */
    public function show($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->apiError('03', 'Valid entry id is required.', 400);
        }

        $row = $this->fetchRow($id);
        if (! $row || $row->deleted_by !== null) {
            return $this->apiError('04', 'Entry not found.', 404);
        }

        return $this->apiSuccess('Passenger diesel entry loaded.', [
            'entry' => $this->formatRow($row),
        ]);
    }

    /**
     * POST /api/passenger-diesel/store
     * Web fields: vehicle, location, date, qty, issued_by
     */
    public function store()
    {
        $payload = $this->parseRequestPayload();
        $errors  = [];
        $data    = $this->buildInsertDataFromPayload($payload, $errors);

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $validationError = $this->validateForeignKeys($data);
        if ($validationError !== null) {
            return $validationError;
        }

        $this->db->table('passenger_vehicle_diesel')->insert($data);
        $insertId = (int) $this->db->insertID();

        $this->logActivity('create', $insertId, $data);

        $row = $this->fetchRow($insertId);

        return $this->apiSuccess('Passenger diesel data added.', [
            'entry' => $this->formatRow($row),
        ], 201);
    }

    /**
     * POST /api/passenger-diesel/{id}
     * Same body as store + optional id in URL
     */
    public function update($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid entry id is required.', 400);
        }

        $existing = $this->db->table('passenger_vehicle_diesel')->where('id', $id)->get()->getRow();
        if (! $existing || $existing->deleted_by !== null) {
            return $this->apiError('04', 'Entry not found.', 404);
        }

        $payload = $this->parseRequestPayload();
        $errors  = [];
        $data    = $this->buildInsertDataFromPayload($payload, $errors);

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $validationError = $this->validateForeignKeys($data);
        if ($validationError !== null) {
            return $validationError;
        }

        $this->db->table('passenger_vehicle_diesel')->where('id', $id)->update($data);
        $this->logActivity('update', $id, $data);

        $row = $this->fetchRow($id);

        return $this->apiSuccess('Passenger diesel updated successfully.', [
            'entry' => $this->formatRow($row),
        ]);
    }

    /**
     * DELETE /api/passenger-diesel/{id}
     */
    public function destroy($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid entry id is required.', 400);
        }

        $existing = $this->db->table('passenger_vehicle_diesel')->where('id', $id)->get()->getRow();
        if (! $existing || $existing->deleted_by !== null) {
            return $this->apiError('04', 'Entry not found.', 404);
        }

        $update = [
            'deleted_by' => $this->authUserId(),
            'deleted_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('passenger_vehicle_diesel')->where('id', $id)->update($update);
        $this->logActivity('delete', $id, $update);

        return $this->apiSuccess('Passenger diesel entry deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildInsertDataFromPayload(array $payload, array &$errors): array
    {
        $vehicleId  = (int) ($payload['vehicle'] ?? $payload['vehicle_id'] ?? 0);
        $locationId = (int) ($payload['location'] ?? $payload['location_id'] ?? 0);
        $entryDate  = trim($payload['date'] ?? $payload['entry_date'] ?? '');
        $qty        = $payload['qty'] ?? null;
        $issuedBy   = (int) ($payload['issued_by'] ?? 0);

        if ($vehicleId <= 0) {
            $errors[] = 'vehicle (vehicle_id) is required';
        }
        if ($locationId <= 0) {
            $errors[] = 'location (location_id) is required';
        }
        if ($entryDate === '') {
            $errors[] = 'date (entry_date) is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($qty === null || $qty === '' || ! is_numeric($qty) || (float) $qty <= 0) {
            $errors[] = 'qty must be a positive number';
        }
        if ($issuedBy <= 0) {
            $errors[] = 'issued_by is required';
        }

        return [
            'entry_date'  => $entryDate,
            'vehicle_id'  => $vehicleId,
            'location_id' => $locationId,
            'qty'         => (float) $qty,
            'issued_by'   => $issuedBy,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function validateForeignKeys(array $data)
    {
        $vehicle = $this->db->table('vehicle')->where('id', $data['vehicle_id'])->get()->getRow();
        if (! $vehicle) {
            return $this->apiError('11', 'Invalid vehicle.', 400);
        }

        $location = $this->db->table('location')->where('location_id', $data['location_id'])->get()->getRow();
        if (! $location) {
            return $this->apiError('13', 'Invalid location.', 400);
        }

        $issuer = $this->db->table('user')->where('id', $data['issued_by'])->get()->getRow();
        if (! $issuer) {
            return $this->apiError('16', 'Invalid issued_by user.', 400);
        }

        if ($this->db->fieldExists('deleted_by', 'user') && $issuer->deleted_by !== null) {
            return $this->apiError('16', 'Invalid issued_by user.', 400);
        }

        return null;
    }

    protected function buildListQuery(string $fromDate, string $toDate, int $vehicleId, int $locationId)
    {
        $builder = $this->db->table('passenger_vehicle_diesel p');
        $builder->select('p.*, v.vehicle_no AS truck_no, l.location_name, l.location_shordname,
            u.full_name AS issued_by_name, u.user_name AS issued_by_user_name,
            u.contact_no AS issued_by_contact_no, u.email AS issued_by_email,
            u.user_type AS issued_by_user_type, u.location_id AS issued_by_location_id');
        $builder->join('vehicle v', 'v.id = p.vehicle_id', 'left');
        $builder->join('location l', 'l.location_id = p.location_id', 'left');
        $builder->join('user u', 'u.id = p.issued_by', 'left');
        $builder->where('p.entry_date >=', $fromDate);
        $builder->where('p.entry_date <=', $toDate);
        $builder->where('p.deleted_by', null);

        if ($vehicleId > 0) {
            $builder->where('p.vehicle_id', $vehicleId);
        }
        if ($locationId > 0) {
            $builder->where('p.location_id', $locationId);
        }

        return $builder;
    }

    protected function fetchRow(int $id): ?object
    {
        return $this->db->query("
            SELECT p.*, v.vehicle_no AS truck_no, l.location_name, l.location_shordname,
                   u.full_name AS issued_by_name, u.user_name AS issued_by_user_name,
                   u.contact_no AS issued_by_contact_no, u.email AS issued_by_email,
                   u.user_type AS issued_by_user_type, u.location_id AS issued_by_location_id
            FROM passenger_vehicle_diesel p
            LEFT JOIN vehicle v ON v.id = p.vehicle_id
            LEFT JOIN location l ON l.location_id = p.location_id
            LEFT JOIN user u ON u.id = p.issued_by
            WHERE p.id = ?
        ", [$id])->getRow();
    }

    protected function formatRow(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        return [
            'id'          => (int) $row->id,
            'entry_date'  => $row->entry_date,
            'date'        => $row->entry_date,
            'vehicle_id'  => (int) $row->vehicle_id,
            'vehicle_no'  => $row->truck_no ?? null,
            'location_id' => (int) $row->location_id,
            'location'    => $this->formatLocationBlock(
                (int) $row->location_id,
                $row->location_name ?? null,
                $row->location_shordname ?? null
            ),
            'qty'       => (float) $row->qty,
            'issued_by' => $this->formatIssuedByUser($row),
        ];
    }

    /**
     * @param object $row Row with issued_by* joined columns or user table columns
     */
    protected function formatIssuedByUser(object $row): ?array
    {
        $id = (int) ($row->issued_by ?? 0);
        if ($id <= 0) {
            return null;
        }

        $userType = $row->issued_by_user_type ?? $row->user_type ?? null;

        return [
            'id'              => $id,
            'full_name'       => $row->issued_by_name ?? $row->full_name ?? null,
            'user_name'       => $row->issued_by_user_name ?? $row->user_name ?? null,
            'contact_no'      => $row->issued_by_contact_no ?? $row->contact_no ?? null,
            'email'           => $row->issued_by_email ?? $row->email ?? null,
            'user_type'       => $userType !== null ? (int) $userType : null,
            'user_type_label' => $this->userTypeLabel($userType),
            'location_id'     => isset($row->issued_by_location_id) && $row->issued_by_location_id !== null
                ? (int) $row->issued_by_location_id
                : null,
        ];
    }

    protected function userTypeLabel($userType): string
    {
        return match ((int) $userType) {
            1       => 'Admin',
            2       => 'Sub Admin',
            default => 'User',
        };
    }

    /**
     * @param array<string, mixed> $changes
     */
    protected function logActivity(string $action, int $modelId, array $changes): void
    {
        if (! $this->db->tableExists('activity_logs')) {
            return;
        }

        $this->db->table('activity_logs')->insert([
            'user_id'    => $this->authUserId(),
            'menu'       => 'api_passenger_diesel',
            'action'     => $action,
            'model'      => 'passenger_vehicle_diesel',
            'model_id'   => $modelId,
            'changes'    => json_encode(['data' => $changes, 'source' => 'passenger_diesel_api']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
