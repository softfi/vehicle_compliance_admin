<?php

namespace App\Controllers\Api;

/**
 * APIs for Extra Diesel Issue module (admin/extra_diesel).
 */
class ExtraDieselController extends BaseApiController
{
    /**
     * GET /api/extra-diesel/issued-by/users
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
     * POST /api/extra-diesel/store
     * Same fields as web form insert_extra_diesel:
     * vehicle (vehicle_id), driver (driver_id), date (issue_date), qty, issued_by, remarks
     */
    public function store()
    {
        $payload = $this->parseRequestPayload();
        $user    = $this->authUser();
        $locationId = $this->resolveLocationIdForUser($user);

        $vehicleId = (int) ($payload['vehicle'] ?? $payload['vehicle_id'] ?? 0);
        $driverId  = (int) ($payload['driver'] ?? $payload['driver_id'] ?? 0);
        $issueDate = trim($payload['date'] ?? $payload['issue_date'] ?? '');
        $qty       = $payload['qty'] ?? null;
        $issuedBy  = (int) ($payload['issued_by'] ?? 0);
        $remarks   = trim($payload['remarks'] ?? '');

        if ($vehicleId <= 0) {
            $vehicleNoInput = $payload['vehicle_no'] ?? '';
            if ($vehicleNoInput === '' && isset($payload['vehicle']) && ! is_numeric($payload['vehicle'])) {
                $vehicleNoInput = (string) $payload['vehicle'];
            }
            $vehicle = $this->resolveVehicleByNumber($vehicleNoInput);
            $vehicleId = $vehicle ? (int) $vehicle->id : 0;
        }

        if ($driverId <= 0 && $vehicleId > 0) {
            $driverId = $this->resolveAssignedDriverId($vehicleId);
        }

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle (vehicle_id) is required';
        }
        if ($driverId <= 0) {
            $errors[] = 'driver (driver_id) is required';
        }
        if ($issueDate === '') {
            $errors[] = 'date (issue_date) is required';
        }
        if ($qty === null || $qty === '' || ! is_numeric($qty) || (float) $qty <= 0) {
            $errors[] = 'qty must be a positive number';
        }
        if ($issuedBy <= 0) {
            $errors[] = 'issued_by is required';
        }

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vehicle = $this->db->table('vehicle')->where('id', $vehicleId)->get()->getRow();
        if (! $vehicle) {
            return $this->apiError('11', 'Invalid vehicle.', 400);
        }

        if ($locationId && isset($vehicle->location_id) && (int) $vehicle->location_id !== $locationId) {
            return $this->apiError('12', 'Vehicle does not belong to your location.', 403);
        }

        $driver = $this->db->table('staff')
            ->where('id', $driverId)
            ->where('user_type', 'DRIVER')
            ->get()
            ->getRow();
        if (! $driver) {
            return $this->apiError('15', 'Invalid driver.', 400);
        }

        $issuer = $this->db->table('user')->where('id', $issuedBy)->get()->getRow();
        if (! $issuer) {
            return $this->apiError('16', 'Invalid issued_by user.', 400);
        }

        if ($this->db->fieldExists('deleted_by', 'user') && $issuer->deleted_by !== null) {
            return $this->apiError('16', 'Invalid issued_by user.', 400);
        }

        $insert = [
            'issue_date' => $issueDate,
            'vehicle_id' => $vehicleId,
            'driver_id'  => $driverId,
            'qty'        => (float) $qty,
            'issued_by'  => $issuedBy,
            'remarks'    => $remarks !== '' ? $remarks : null,
        ];

        $this->db->table('extra_diesel_issue')->insert($insert);
        $insertId = (int) $this->db->insertID();

        $this->db->table('activity_logs')->insert([
            'user_id'    => $this->authUserId(),
            'menu'       => 'api_extra_diesel',
            'action'     => 'create',
            'model'      => 'extra_diesel_issue',
            'model_id'   => $insertId,
            'changes'    => json_encode(['data' => $insert, 'source' => 'extra_diesel_api']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $row = $this->fetchExtraDieselRow($insertId);

        return $this->apiSuccess('Extra diesel issued successfully.', [
            'entry' => $this->formatExtraDieselRow($row),
        ], 201);
    }

    /**
     * GET /api/extra-diesel
     * Filters: from_date, to_date, vehicle_id (filter_vehicle), location_id (filter_location), driver_id (filter_driver)
     */
    public function index()
    {
        $parsed = $this->parseDieselListFilters(true);
        if ($parsed['errors'] !== []) {
            return $this->apiError('03', implode('; ', $parsed['errors']), 400);
        }

        $filters  = $parsed['filters'];
        $fromDate = $filters['from_date'];
        $toDate   = $filters['to_date'];
        $today    = date('Y-m-d');

        $filteredCount = $this->countExtraDieselEntries($filters, $fromDate, $toDate);
        $todayCount    = $this->countExtraDieselEntries($filters, $today, $today);

        $builder = $this->db->table('extra_diesel_issue e');
        $builder->select('e.*, v.vehicle_no AS truck_no, v.location_id AS vehicle_location_id,
            loc.location_name AS vehicle_location_name, loc.location_shordname AS vehicle_location_shortname,
            s.name AS driver_name, s.staff_code,
            u.full_name AS issued_by_name, u.user_name AS issued_by_user_name,
            u.contact_no AS issued_by_contact_no, u.email AS issued_by_email,
            u.user_type AS issued_by_user_type, u.location_id AS issued_by_location_id');
        $this->applyExtraDieselListFilters($builder, $filters, $fromDate, $toDate);
        $builder->join('location loc', 'loc.location_id = v.location_id', 'left');
        $builder->join('staff s', 's.id = e.driver_id', 'left');
        $builder->join('user u', 'u.id = e.issued_by', 'left');
        $builder->orderBy('e.issue_date', 'DESC');
        $builder->orderBy('e.id', 'DESC');

        $rows = $builder->get()->getResult();

        return $this->apiSuccess('Extra diesel entries loaded.', [
            'filters' => $filters,
            'summary' => [
                'total_extra_diesel_count' => $filteredCount,
                'today_extra_diesel_count' => $todayCount,
                'today_date'               => $today,
            ],
            'total'   => $filteredCount,
            'entries' => array_map(fn ($r) => $this->formatExtraDieselRow($r), $rows),
        ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function applyExtraDieselListFilters($builder, array $filters, ?string $fromDate, ?string $toDate): void
    {
        $builder->join('vehicle v', 'v.id = e.vehicle_id', 'left');
        $builder->where('e.deleted_by', null);

        if ($fromDate !== null) {
            $builder->where('e.issue_date >=', $fromDate);
        }
        if ($toDate !== null) {
            $builder->where('e.issue_date <=', $toDate);
        }
        if (! empty($filters['vehicle_id'])) {
            $builder->where('e.vehicle_id', $filters['vehicle_id']);
        }
        if (! empty($filters['location_id'])) {
            $builder->where('v.location_id', $filters['location_id']);
        }
        if (! empty($filters['driver_id'])) {
            $builder->where('e.driver_id', $filters['driver_id']);
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function countExtraDieselEntries(array $filters, ?string $fromDate, ?string $toDate): int
    {
        $builder = $this->db->table('extra_diesel_issue e');
        $this->applyExtraDieselListFilters($builder, $filters, $fromDate, $toDate);

        return (int) $builder->countAllResults();
    }

    protected function resolveVehicleByNumber(string $vehicleNo): ?object
    {
        $vehicleNo = trim($vehicleNo);
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

    protected function resolveAssignedDriverId(int $vehicleId): int
    {
        $assignment = $this->db->table('driver_assignment')
            ->where('vehicle_no', $vehicleId)
            ->where('(to_date IS NULL OR to_date = "0000-00-00" OR to_date >= CURDATE())', null, false)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        return $assignment ? (int) $assignment->driver : 0;
    }

    protected function fetchExtraDieselRow(int $id): ?object
    {
        return $this->db->query("
            SELECT e.*, v.vehicle_no AS truck_no, v.location_id AS vehicle_location_id,
                   loc.location_name AS vehicle_location_name, loc.location_shordname AS vehicle_location_shortname,
                   s.name AS driver_name, s.staff_code,
                   u.full_name AS issued_by_name, u.user_name AS issued_by_user_name,
                   u.contact_no AS issued_by_contact_no, u.email AS issued_by_email,
                   u.user_type AS issued_by_user_type, u.location_id AS issued_by_location_id
            FROM extra_diesel_issue e
            LEFT JOIN vehicle v ON v.id = e.vehicle_id
            LEFT JOIN location loc ON loc.location_id = v.location_id
            LEFT JOIN staff s ON s.id = e.driver_id
            LEFT JOIN user u ON u.id = e.issued_by
            WHERE e.id = ?
        ", [$id])->getRow();
    }

    protected function formatExtraDieselRow(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        $vehicleLocationId = isset($row->vehicle_location_id) ? (int) $row->vehicle_location_id : null;

        return [
            'id'              => (int) $row->id,
            'issue_date'      => $row->issue_date,
            'date'            => $row->issue_date,
            'vehicle_id'      => (int) $row->vehicle_id,
            'vehicle_no'      => $row->truck_no ?? null,
            'location_id'     => $vehicleLocationId > 0 ? $vehicleLocationId : null,
            'location'        => $this->formatLocationBlock(
                $vehicleLocationId > 0 ? $vehicleLocationId : null,
                $row->vehicle_location_name ?? null,
                $row->vehicle_location_shortname ?? null
            ),
            'driver_id'       => (int) $row->driver_id,
            'driver_name'     => $row->driver_name ?? null,
            'staff_code'      => $row->staff_code ?? null,
            'qty'             => (float) $row->qty,
            'issued_by'       => $this->formatIssuedByUser($row),
            'remarks'         => $row->remarks ?? null,
        ];
    }

    /**
     * @param object $row Row with issued_by* joined columns or user table columns
     */
    protected function formatIssuedByUser(object $row): ?array
    {
        $id = (int) ($row->issued_by ?? $row->id ?? 0);
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
}
