<?php

namespace App\Controllers\Api;

/**
 * Driver list for Driver Assignment module (admin/Driver_Assignment).
 */
class DriverAssignmentController extends BaseApiController
{
    /**
     * GET /api/driver-assignment/drivers
     * Same drivers as web Driver field: staff with user_type = DRIVER.
     * Optional: ?search=name_or_code
     */
    public function drivers()
    {
        $search = trim($this->request->getGet('search') ?? '');

        $builder = $this->db->table('staff s');
        $builder->select('s.id, s.name, s.staff_code, s.user_type, s.location_id, s.tel, s.doj, s.resign_date, l.location_name');
        $builder->join('location l', 'l.location_id = s.location_id', 'left');
        $builder->where('s.user_type', 'DRIVER');

        if ($search !== '') {
            $builder->groupStart()
                ->like('s.name', $search)
                ->orLike('s.staff_code', $search)
            ->groupEnd();
        }

        $builder->orderBy('s.id', 'ASC');
        $rows = $builder->get()->getResult();

        $drivers = [];
        foreach ($rows as $row) {
            $name      = $row->name ?? '';
            $staffCode = $row->staff_code ?? '';
            $label     = $staffCode !== '' ? "{$name} ({$staffCode})" : $name;

            $drivers[] = [
                'id'            => (int) $row->id,
                'name'          => $name,
                'staff_code'    => $staffCode,
                'label'         => $label,
                'user_type'     => $row->user_type,
                'location_id'   => $row->location_id ? (int) $row->location_id : null,
                'location_name' => $row->location_name ?? null,
                'tel'           => $row->tel ?? null,
                'doj'           => $row->doj ?? null,
                'resign_date'   => $row->resign_date ?? null,
            ];
        }

        return $this->apiSuccess('Drivers loaded.', [
            'total'   => count($drivers),
            'drivers' => $drivers,
        ]);
    }

    /**
     * GET /api/driver-assignment?from_date=&to_date=&vehicle_id=&driver_id=
     * Same list filter as web Driver_Assignment table.
     */
    public function index()
    {
        $fromDate = trim((string) ($this->request->getGet('from_date') ?? ''));
        $toDate   = trim((string) ($this->request->getGet('to_date') ?? ''));

        if ($fromDate === '') {
            $fromDate = date('Y-m-01');
        }
        if ($toDate === '') {
            $toDate = date('Y-m-d');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            return $this->apiError('03', 'from_date and to_date must be YYYY-MM-DD', 400);
        }
        if (strtotime($fromDate) > strtotime($toDate)) {
            return $this->apiError('03', 'from_date cannot be after to_date', 400);
        }

        $vehicleId = (int) ($this->request->getGet('vehicle_id') ?? $this->request->getGet('vehicle_no') ?? 0);
        $driverId  = (int) ($this->request->getGet('driver_id') ?? $this->request->getGet('driver') ?? 0);

        $builder = $this->buildAssignmentListQuery($fromDate, $toDate, $vehicleId, $driverId);
        $builder->orderBy('da.id', 'DESC');
        $rows = $builder->get()->getResult();

        return $this->apiSuccess('Driver assignments loaded.', [
            'filters' => [
                'from_date'  => $fromDate,
                'to_date'    => $toDate,
                'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
                'driver_id'  => $driverId > 0 ? $driverId : null,
            ],
            'total'       => count($rows),
            'assignments' => array_map(fn ($r) => $this->formatAssignment($r), $rows),
        ]);
    }

    /**
     * GET /api/driver-assignment/{id}
     * Detail for edit screen (Edit_Driver_Assignment).
     */
    public function show($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid assignment id is required.', 400);
        }

        $row = $this->fetchAssignmentRow($id);
        if (! $row) {
            return $this->apiError('04', 'Driver assignment not found.', 404);
        }

        return $this->apiSuccess('Driver assignment loaded.', [
            'assignment' => $this->formatAssignment($row),
        ]);
    }

    /**
     * POST /api/driver-assignment/store
     * Same fields as web insert_driver_asignment form.
     */
    public function store()
    {
        $payload = $this->parseRequestPayload();
        $errors  = [];
        $data    = $this->buildAssignmentDataFromPayload($payload, $errors, true);

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $validationError = $this->validateAssignmentReferences($data);
        if ($validationError !== null) {
            return $validationError;
        }

        $driverConflict = $this->validateDriverMonthlyAssignment((int) $data['driver'], $data['from_date']);
        if ($driverConflict !== null) {
            return $this->apiError('17', $driverConflict, 400);
        }

        $this->db->table('driver_assignment')->insert($data);
        $insertId = (int) $this->db->insertID();
        $this->logAssignmentActivity('create', $insertId, $data);

        $row = $this->fetchAssignmentRow($insertId);

        return $this->apiSuccess('Driver assigned successfully.', [
            'assignment' => $this->formatAssignment($row),
        ], 201);
    }

    /**
     * POST /api/driver-assignment/{id}
     * Same fields as web update_driver_asignment / Edit_Driver_Assignment form.
     */
    public function update($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid assignment id is required.', 400);
        }

        $existing = $this->db->table('driver_assignment')->where('id', $id)->get()->getRow();
        if (! $existing) {
            return $this->apiError('04', 'Driver assignment not found.', 404);
        }

        $payload = $this->parseRequestPayload();
        $errors  = [];
        $data    = $this->buildAssignmentDataFromPayload($payload, $errors, true);

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $validationError = $this->validateAssignmentReferences($data);
        if ($validationError !== null) {
            return $validationError;
        }

        $this->db->table('driver_assignment')->where('id', $id)->update($data);
        $this->logAssignmentActivity('update', $id, $data);

        $row = $this->fetchAssignmentRow($id);

        return $this->apiSuccess('Driver assignment updated successfully.', [
            'assignment' => $this->formatAssignment($row),
        ]);
    }

    /**
     * DELETE /api/driver-assignment/{id}
     * Same as web Admin::delete_driver_asignment() — hard delete by id.
     */
    public function destroy($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid assignment id is required.', 400);
        }

        $existing = $this->db->table('driver_assignment')->where('id', $id)->get()->getRow();
        if (! $existing) {
            return $this->apiError('04', 'Driver assignment not found.', 404);
        }

        $snapshot = $this->formatAssignment($this->fetchAssignmentRow($id));

        $this->db->table('driver_assignment')->where('id', $id)->delete();
        $this->logAssignmentActivity('delete', $id, (array) $existing);

        return $this->apiSuccess('Driver assignment deleted successfully.', [
            'deleted_id' => $id,
            'assignment' => $snapshot,
        ]);
    }

    /**
     * Same monthly driver conflict check as Admin::insert_driver_asignment().
     */
    protected function validateDriverMonthlyAssignment(int $driverId, string $fromDate): ?string
    {
        $existing = $this->db->table('driver_assignment')
            ->where('driver', $driverId)
            ->where('MONTH(from_date) = MONTH(' . $this->db->escape($fromDate) . ')', null, false)
            ->get()
            ->getResult();

        foreach ($existing as $assignment) {
            $toDate = $assignment->to_date ?? null;
            if ($toDate === null || $toDate === '' || $toDate === '0000-00-00') {
                return 'Driver Already Asigned for this month.';
            }
            if ($toDate > $fromDate) {
                return 'Driver Already Asigned for this month.';
            }
        }

        return null;
    }

    protected function buildAssignmentListQuery(string $fromDate, string $toDate, int $vehicleId, int $driverId)
    {
        $builder = $this->db->table('driver_assignment da');
        $builder->select('da.*, v.vehicle_no AS vehicle_number, s.name AS driver_name, s.staff_code AS driver_code');
        $builder->join('vehicle v', 'v.id = da.vehicle_no', 'left');
        $builder->join('staff s', 's.id = da.driver', 'left');
        $builder->where('da.from_date >=', $fromDate);
        $builder->where('da.from_date <=', $toDate);

        if ($vehicleId > 0) {
            $builder->where('da.vehicle_no', $vehicleId);
        }
        if ($driverId > 0) {
            $builder->where('da.driver', $driverId);
        }

        return $builder;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildAssignmentDataFromPayload(array $payload, array &$errors, bool $requireAll = false): array
    {
        $vehicleId  = (int) ($payload['vehicle_no'] ?? $payload['vehicle_id'] ?? 0);
        $driverId   = (int) ($payload['driver'] ?? $payload['driver_id'] ?? 0);
        $fromDate   = trim($payload['from_date'] ?? '');
        $toDate     = trim($payload['to_date'] ?? '');
        $openingHsd = $payload['opening_hsd'] ?? null;
        $openingKm  = $payload['opening_km'] ?? null;
        $closingHsd = $payload['closing_hsd'] ?? null;
        $closingKm  = $payload['closing_km'] ?? null;

        if ($requireAll) {
            if ($vehicleId <= 0) {
                $errors[] = 'vehicle_no (vehicle_id) is required';
            }
            if ($driverId <= 0) {
                $errors[] = 'driver (driver_id) is required';
            }
            if ($fromDate === '') {
                $errors[] = 'from_date is required';
            }
            if ($openingHsd === null || $openingHsd === '' || ! is_numeric($openingHsd)) {
                $errors[] = 'opening_hsd is required';
            }
            if ($openingKm === null || $openingKm === '' || ! is_numeric($openingKm)) {
                $errors[] = 'opening_km is required';
            }
        }

        if ($fromDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $errors[] = 'from_date must be YYYY-MM-DD';
        }
        if ($toDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $errors[] = 'to_date must be YYYY-MM-DD';
        }

        if ($toDate === '') {
            $toDate = null;
        }

        return [
            'vehicle_no'  => $vehicleId,
            'driver'      => $driverId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'opening_hsd' => ($openingHsd !== null && $openingHsd !== '') ? (float) $openingHsd : null,
            'opening_km'  => ($openingKm !== null && $openingKm !== '') ? (float) $openingKm : null,
            'closing_hsd' => ($closingHsd !== null && $closingHsd !== '') ? (float) $closingHsd : null,
            'closing_km'  => ($closingKm !== null && $closingKm !== '') ? (float) $closingKm : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function validateAssignmentReferences(array $data)
    {
        $vehicle = $this->db->table('vehicle')->where('id', $data['vehicle_no'])->get()->getRow();
        if (! $vehicle) {
            return $this->apiError('11', 'Invalid vehicle.', 400);
        }

        $driver = $this->db->table('staff')
            ->where('id', $data['driver'])
            ->where('user_type', 'DRIVER')
            ->get()
            ->getRow();
        if (! $driver) {
            return $this->apiError('15', 'Invalid driver.', 400);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $changes
     */
    protected function logAssignmentActivity(string $action, int $modelId, array $changes): void
    {
        if (! $this->db->tableExists('activity_logs')) {
            return;
        }

        $this->db->table('activity_logs')->insert([
            'user_id'    => $this->authUserId(),
            'menu'       => 'api_driver_assignment',
            'action'     => $action,
            'model'      => 'driver_assignment',
            'model_id'   => $modelId,
            'changes'    => json_encode(['data' => $changes, 'source' => 'driver_assignment_api']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function fetchAssignmentRow(int $id): ?object
    {
        return $this->db->table('driver_assignment da')
            ->select('da.*, v.vehicle_no AS vehicle_number, s.name AS driver_name, s.staff_code AS driver_code')
            ->join('vehicle v', 'v.id = da.vehicle_no', 'left')
            ->join('staff s', 's.id = da.driver', 'left')
            ->where('da.id', $id)
            ->get()
            ->getRow();
    }

    protected function formatAssignment(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        $toDate = $row->to_date ?? null;
        if ($toDate === '' || $toDate === '0000-00-00') {
            $toDate = null;
        }

        return [
            'id'              => (int) $row->id,
            'vehicle_id'      => (int) $row->vehicle_no,
            'vehicle_no'      => $row->vehicle_number ?? null,
            'driver_id'       => (int) $row->driver,
            'driver_name'     => $row->driver_name ?? null,
            'driver_code'     => $row->driver_code ?? null,
            'driver_label'    => ($row->driver_name && $row->driver_code)
                ? "{$row->driver_name} ({$row->driver_code})"
                : ($row->driver_name ?? null),
            'from_date'       => $row->from_date,
            'to_date'         => $toDate,
            'opening_hsd'     => isset($row->opening_hsd) ? (float) $row->opening_hsd : null,
            'opening_km'      => isset($row->opening_km) ? (float) $row->opening_km : null,
            'closing_hsd'     => isset($row->closing_hsd) && $row->closing_hsd !== '' ? (float) $row->closing_hsd : null,
            'closing_km'      => isset($row->closing_km) && $row->closing_km !== '' ? (float) $row->closing_km : null,
        ];
    }
}
