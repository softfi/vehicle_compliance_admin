<?php

namespace App\Controllers\Api;

/**
 * Staff Advance APIs (admin/staf_advance).
 */
class StaffAdvanceController extends BaseApiController
{
    /**
     * GET /api/staff-advance/staff-types
     * Same as web staf_advance "Staff Type" dropdown (distinct staff.user_type).
     */
    public function staffTypes()
    {
        $rows = $this->db->table('staff')
            ->select('user_type, COUNT(*) AS total')
            ->where('user_type IS NOT NULL')
            ->where('user_type !=', '')
            ->groupBy('user_type')
            ->orderBy('user_type', 'ASC')
            ->get()
            ->getResult();

        $types = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row->user_type ?? ''));
            if ($value === '') {
                continue;
            }

            $types[] = [
                'value' => $value,
                'label' => $this->staffTypeLabel($value),
                'total' => (int) ($row->total ?? 0),
            ];
        }

        return $this->apiSuccess('Staff types loaded.', [
            'total'       => count($types),
            'staff_types' => $types,
        ]);
    }

    /**
     * GET /api/staff-advance
     * Same list as web staf_advance history table.
     *
     * Query:
     * - from_date (default: first day of current month)
     * - to_date (default: today)
     * - staff_id (optional)
     * - staff_type / user_type (optional)
     * - location_id (optional)
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

        $errors = [];
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $errors[] = 'from_date must be YYYY-MM-DD';
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $errors[] = 'to_date must be YYYY-MM-DD';
        }
        if ($errors === [] && strtotime($fromDate) > strtotime($toDate)) {
            $errors[] = 'from_date cannot be after to_date';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $staffId    = (int) ($this->request->getGet('staff_id') ?? $this->request->getGet('staff') ?? 0);
        $locationId = (int) ($this->request->getGet('location_id') ?? $this->request->getGet('location') ?? 0);
        $staffType  = $this->normalizeStaffType((string) ($this->request->getGet('staff_type')
            ?? $this->request->getGet('user_type')
            ?? $this->request->getGet('type')
            ?? ''));

        $builder = $this->db->table('staff_advance sa');
        $builder->select('sa.*, s.name AS staff_name, s.staff_code, s.user_type, l.location_name');
        $builder->join('staff s', 's.id = sa.staff_id', 'left');
        $builder->join('location l', 'l.location_id = sa.location_id', 'left');
        $builder->where('sa.adv_date >=', $fromDate);
        $builder->where('sa.adv_date <=', $toDate);

        if ($staffId > 0) {
            $builder->where('sa.staff_id', $staffId);
        }
        if ($locationId > 0) {
            $builder->where('sa.location_id', $locationId);
        }
        if ($staffType !== '') {
            $builder->where('s.user_type', $staffType);
        }

        $builder->orderBy('sa.adv_date', 'DESC');
        $builder->orderBy('sa.id', 'DESC');
        $rows = $builder->get()->getResult();

        $advances = [];
        foreach ($rows as $row) {
            $advances[] = $this->formatAdvance($row);
        }

        return $this->apiSuccess('Staff advances loaded.', [
            'filters' => [
                'from_date'   => $fromDate,
                'to_date'     => $toDate,
                'staff_id'    => $staffId > 0 ? $staffId : null,
                'staff_type'  => $staffType !== '' ? $staffType : null,
                'location_id' => $locationId > 0 ? $locationId : null,
            ],
            'total'    => count($advances),
            'advances' => $advances,
        ]);
    }

    /**
     * GET|POST /api/staff-advance/employees
     * Same as web "Employ Name" dropdown filtered by Staff Type.
     *
     * Query/body:
     * - staff_type=STAFF  (or staff, staff master)
     * - staff_type=DRIVER (or driver)
     * - date=YYYY-MM-DD (optional — filters by doj/resign_date like web)
     * - search=name_or_code (optional)
     */
    public function employees()
    {
        $payload   = $this->parseRequestPayload();
        $staffType = $this->normalizeStaffType(
            $this->request->getGet('staff_type')
                ?? $this->request->getGet('type')
                ?? $this->request->getGet('user_type')
                ?? $payload['staff_type']
                ?? $payload['type']
                ?? $payload['user_type']
                ?? ''
        );

        if ($staffType === '') {
            return $this->apiError('03', 'staff_type is required (e.g. STAFF or DRIVER).', 400);
        }

        $date   = trim((string) ($this->request->getGet('date') ?? $payload['date'] ?? ''));
        $search = trim((string) ($this->request->getGet('search') ?? $payload['search'] ?? ''));

        if ($date !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->apiError('03', 'date must be YYYY-MM-DD.', 400);
        }

        $rows = $this->adminModel->GetActiveStaff($date !== '' ? $date : null, $staffType);

        $employees = [];
        foreach ($rows as $row) {
            if ($search !== '') {
                $haystack = strtolower(($row->name ?? '') . ' ' . ($row->staff_code ?? ''));
                if (strpos($haystack, strtolower($search)) === false) {
                    continue;
                }
            }

            $employees[] = $this->formatEmployee($row);
        }

        return $this->apiSuccess('Employees loaded.', [
            'filters' => [
                'staff_type' => $staffType,
                'staff_type_label' => $this->staffTypeLabel($staffType),
                'date'   => $date !== '' ? $date : null,
                'search' => $search !== '' ? $search : null,
            ],
            'total'     => count($employees),
            'employees' => $employees,
        ]);
    }

    /**
     * GET /api/staff-advance/cash-paid-by/users
     * Sub-admin users only (user_type = 2).
     * paid_by = sub-admin user id (stored in staff_advance.paid_by).
     * Optional: ?search=name
     */
    public function cashPaidByUsers()
    {
        $search = trim($this->request->getGet('search') ?? '');

        $builder = $this->db->table('user');
        $builder->select('id, full_name, user_name, contact_no, email, user_type, location_id');
        $builder->where('user_type', 2);
        $builder->where('deleted_by', null);

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
            $fullName  = $u->full_name ?? '';
            $typeLabel = 'Subadmin';

            $users[] = [
                'id'              => (int) $u->id,
                'full_name'       => $fullName,
                'paid_by'         => (string) $u->id,
                'paid_by_id'      => (int) $u->id,
                'label'           => $fullName !== '' ? "{$fullName} ({$typeLabel})" : $typeLabel,
                'user_name'       => $u->user_name ?? null,
                'contact_no'      => $u->contact_no ?? null,
                'email'           => $u->email ?? null,
                'user_type'       => isset($u->user_type) ? (int) $u->user_type : null,
                'user_type_label' => $typeLabel,
                'location_id'     => $u->location_id ? (int) $u->location_id : null,
            ];
        }

        return $this->apiSuccess('Sub-admin users loaded.', [
            'total' => count($users),
            'users' => $users,
        ]);
    }

    /**
     * GET|POST /api/staff-advance/employee-details
     * Same as web Admin::getvehicledtls() when staff type + employee + date selected.
     *
     * Query/body:
     * - staff_type / user_type (required) — DRIVER, STAFF, MECHANIC, etc.
     * - staff_id (required)
     * - date (required, YYYY-MM-DD)
     *
     * DRIVER: trip_advance_balance, salary_balance, assigned_vehicle, trips
     * All other types: advance_balance, salary_balance
     */
    public function employeeDetails()
    {
        $payload   = $this->parseRequestPayload();
        $staffId   = (int) ($this->request->getGet('staff_id')
            ?? $payload['staff_id']
            ?? $payload['staff']
            ?? 0);
        $staffType = $this->normalizeStaffType(
            (string) ($this->request->getGet('staff_type')
                ?? $this->request->getGet('user_type')
                ?? $this->request->getGet('type')
                ?? $payload['staff_type']
                ?? $payload['user_type']
                ?? $payload['type']
                ?? '')
        );
        $date = trim((string) ($this->request->getGet('date')
            ?? $payload['date']
            ?? ''));

        $errors = [];
        if ($staffType === '') {
            $errors[] = 'staff_type (user_type) is required — e.g. DRIVER or STAFF';
        }
        if ($staffId <= 0) {
            $errors[] = 'staff_id is required';
        }
        if ($date === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $staff = $this->db->table('staff s')
            ->select('s.id, s.name, s.staff_code, s.user_type, s.location_id, l.location_name')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->where('s.id', $staffId)
            ->get()
            ->getRow();

        if (! $staff) {
            return $this->apiError('15', 'Invalid staff.', 400);
        }

        $dbUserType = strtoupper(trim((string) ($staff->user_type ?? '')));
        if ($dbUserType !== '' && $dbUserType !== $staffType) {
            return $this->apiError('03', "staff_type mismatch. Selected employee is {$dbUserType}, but {$staffType} was sent.", 400);
        }

        $isDriver = $staffType === 'DRIVER';

        // 1. Vehicle assignment (web getvehicledtls query)
        $vResult = $this->db->query('
            SELECT v.vehicle_no, v.id AS vehicle_id
            FROM driver_assignment da
            JOIN vehicle v ON da.vehicle_no = v.id
            WHERE da.driver = ? AND da.from_date <= ? AND da.to_date >= ?
        ', [$staffId, $date, $date])->getRow();

        // 2. Trips for driver on selected date
        $trips = [];
        if ($isDriver && $vResult) {
            $tripRows = $this->db->table('despatch')
                ->select('despatch.despatch_id, despatch.ref_no, route.to_city AS location')
                ->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left')
                ->join('route', 'route.id = do_registration.route_id', 'left')
                ->where('despatch.des_date', $date)
                ->where('despatch.vehicle_no', $vResult->vehicle_id)
                ->get()
                ->getResult();

            foreach ($tripRows as $t) {
                $refNo    = $t->ref_no ?? '';
                $location = $t->location ?? '';
                $label    = '#' . $t->despatch_id
                    . ($refNo !== '' ? ' (' . $refNo . ')' : '')
                    . ($location !== '' ? ' - ' . $location : '');

                $trips[] = [
                    'despatch_id' => (int) $t->despatch_id,
                    'ref_no'      => $refNo !== '' ? $refNo : null,
                    'location'    => $location !== '' ? $location : null,
                    'label'       => $label,
                ];
            }
        }

        // 3. Advance balances (same as web)
        if ($isDriver) {
            $tripAdvanceBalance = (float) ($this->db->query(
                'SELECT SUM(amount) AS total FROM staff_advance WHERE staff_id = ?',
                [$staffId]
            )->getRow()->total ?? 0);
            $advanceBalance = null;
        } else {
            $advanceBalance = (float) ($this->db->query(
                'SELECT SUM(amount) AS total FROM staff_advance WHERE staff_id = ? AND (despatch_id IS NULL OR despatch_id = \'\')',
                [$staffId]
            )->getRow()->total ?? 0);
        }

        // 4. Salary balance for month of selected date
        $year  = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $salaryRow = $this->db->query(
            'SELECT net_salary FROM staff_salary WHERE user_id = ? AND Year = ? AND month = ?',
            [$staffId, $year, $month]
        )->getRow();
        $salaryBalance = (float) ($salaryRow->net_salary ?? 0);

        $staffCode = $staff->staff_code ?? '';
        $staffName = $staff->name ?? '';

        $details = [
            'salary_balance' => $salaryBalance,
            'salary_balance_formatted' => number_format($salaryBalance, 2),
        ];

        if ($isDriver) {
            $details['trip_advance_balance'] = $tripAdvanceBalance;
            $details['trip_advance_balance_formatted'] = number_format($tripAdvanceBalance, 2);
            $details['assigned_vehicle'] = [
                'vehicle_id' => $vResult ? (int) $vResult->vehicle_id : null,
                'vehicle_no' => $vResult->vehicle_no ?? 'No vehicle found',
            ];
            $details['trips'] = $trips;
            $details['trip_required'] = true;
            $details['trip_message'] = $trips === []
                ? 'No Trip Found (Record still saved as Trip Advance)'
                : null;
        } else {
            $details['advance_balance'] = $advanceBalance;
            $details['advance_balance_formatted'] = number_format($advanceBalance, 2);
            $details['assigned_vehicle'] = null;
            $details['trips'] = [];
            $details['trip_required'] = false;
        }

        $visibleFields = $isDriver
            ? ['trip_advance_balance', 'salary_balance', 'assigned_vehicle', 'trips']
            : ['advance_balance', 'salary_balance'];

        return $this->apiSuccess('Employee details loaded.', [
            'filters' => [
                'staff_type'       => $staffType,
                'staff_type_label' => $this->staffTypeLabel($staffType),
                'staff_id'         => $staffId,
                'date'             => $date,
            ],
            'staff' => [
                'id'            => $staffId,
                'name'          => $staffName,
                'staff_code'    => $staffCode,
                'label'         => $staffCode !== '' ? "{$staffName} ({$staffCode})" : $staffName,
                'user_type'     => $staff->user_type ?? null,
                'location_id'   => ! empty($staff->location_id) ? (int) $staff->location_id : null,
                'location_name' => $staff->location_name ?? null,
            ],
            'date'           => $date,
            'visible_fields' => $visibleFields,
            'details'        => $details,
        ]);
    }

    /**
     * POST /api/staff-advance/store
     * Same as web Admin::insert_staf_advance() — multipart/form-data.
     *
     * Fields:
     * - staff_id (required)
     * - date / adv_date (required, YYYY-MM-DD)
     * - bank_cash (required: Cash or Bank)
     * - amount (required)
     * - location_id (required)
     * - paid_by / paid_by_id (optional — sub-admin user id)
     * - despatch_id (optional — for DRIVER trip link)
     * - upload_file (optional file)
     * - staff_type / user_type (optional — validated against employee if sent)
     */
    public function store()
    {
        $payload = $this->parseRequestPayload();
        $built   = $this->buildAdvanceData($payload);
        if ($built['error'] !== null) {
            $code = str_contains($built['error'], 'Invalid staff') || str_contains($built['error'], 'Invalid location') ? '15' : '03';

            return $this->apiError($code, $built['error'], 400);
        }

        $data = $built['data'];

        $uploadFileName = '';
        $uploadFile     = $this->request->getFile('upload_file');
        if ($uploadFile !== null && $uploadFile->isValid() && ! $uploadFile->hasMoved()) {
            $uploadFileName = $this->uploadAdvanceFile($uploadFile) ?? '';
        }
        $data['upload_file'] = $uploadFileName !== '' ? $uploadFileName : null;

        $staff    = $this->db->table('staff')->where('id', $data['staff_id'])->get()->getRow();
        $location = $this->db->table('location')->where('location_id', $data['location_id'])->get()->getRow();
        $paidByResolved = $this->resolvePaidBySubAdminId($payload);

        $this->db->table('staff_advance')->insert($data);
        $insertId = (int) $this->db->insertID();

        if ($data['bank_cash'] === 'Cash') {
            $fy    = $this->db->query('SELECT fy_id FROM financial_year WHERE status = 1')->getRow();
            $fyId  = $fy->fy_id ?? 0;
            $groupId = ($staff->user_type === 'STAFF') ? 5 : 4;

            $entries = [
                [
                    'group_id'   => $groupId,
                    'ledger_id'  => $data['staff_id'],
                    'entry_type' => 1,
                    'amount'     => $data['amount'],
                    'narration'  => 'Advance received. [Adv ID: ' . $insertId . ']',
                ],
                [
                    'group_id'   => 2,
                    'ledger_id'  => $data['location_id'],
                    'entry_type' => 2,
                    'amount'     => $data['amount'],
                    'narration'  => 'Advance paid to ' . ($staff->name ?? 'Staff'),
                ],
            ];

            $voucherData = [
                'voucher_no'   => $this->adminModel->getNextVoucherNo('Payment'),
                'voucher_date' => $data['adv_date'],
                'voucher_type' => 'Payment',
                'fy_id'        => $fyId,
                'location'     => $data['location_id'],
                'total_amount' => $data['amount'],
                'narration'    => 'Advance paid to ' . ($staff->name ?? 'Staff')
                    . ' at ' . ($location->location_name ?? 'Location')
                    . '. Paid by: ' . ($paidByResolved['paid_by_name'] ?? $data['paid_by'] ?? ''),
                'created_at'   => date('Y-m-d H:i:s'),
                'created_by'   => $this->authUserId(),
            ];

            $this->adminModel->saveVoucher($voucherData, $entries);
        }

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_staff_advance',
                'action'     => 'create',
                'model'      => 'staff_advance',
                'model_id'   => $insertId,
                'changes'    => json_encode(['data' => $data, 'source' => 'staff_advance_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->fetchAdvanceRow($insertId);

        return $this->apiSuccess('Staff advance recorded successfully.', [
            'advance' => $this->formatAdvance($row),
        ], 201);
    }

    /**
     * GET /api/staff-advance/{id}
     * Same as web Admin::editstaf_advance() — load record for edit form.
     */
    public function show($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid advance id is required.', 400);
        }

        $row = $this->fetchAdvanceRow($id);
        if (! $row) {
            return $this->apiError('04', 'Staff advance not found.', 404);
        }

        $staffType = strtoupper(trim((string) ($row->user_type ?? '')));
        $isDriver  = $staffType === 'DRIVER';

        $editableFields = [
            'staff_id',
            'date',
            'bank_cash',
            'amount',
            'location_id',
            'paid_by',
            'upload_file',
        ];
        if ($isDriver) {
            $editableFields[] = 'despatch_id';
        }

        return $this->apiSuccess('Staff advance loaded.', [
            'advance'         => $this->formatAdvance($row),
            'editable_fields' => $editableFields,
            'staff_type'      => $staffType !== '' ? $staffType : null,
            'trip_required'   => $isDriver,
        ]);
    }

    /**
     * POST /api/staff-advance/{id}
     * Same as web Admin::update_StaffAdvance() — multipart/form-data.
     *
     * Fields:
     * - staff_id (required)
     * - date / adv_date (required, YYYY-MM-DD)
     * - bank_cash (required: Cash or Bank)
     * - amount (required)
     * - location_id (required)
     * - paid_by / paid_by_id (optional — sub-admin user id)
     * - despatch_id (optional — for DRIVER)
     * - upload_file (optional file)
     * - staff_type / user_type (optional — validated against employee if sent)
     */
    public function update($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid advance id is required.', 400);
        }

        $existing = $this->fetchAdvanceRow($id);
        if (! $existing) {
            return $this->apiError('04', 'Staff advance not found.', 404);
        }

        $payload = $this->parseRequestPayload();
        $built   = $this->buildAdvanceData($payload);
        if ($built['error'] !== null) {
            $code = str_contains($built['error'], 'Invalid staff') || str_contains($built['error'], 'Invalid location') ? '15' : '03';

            return $this->apiError($code, $built['error'], 400);
        }

        $data = $built['data'];

        $uploadFile = $this->request->getFile('upload_file');
        if ($uploadFile !== null && $uploadFile->isValid() && ! $uploadFile->hasMoved()) {
            $uploadFileName = $this->uploadAdvanceFile($uploadFile);
            if ($uploadFileName === null) {
                return $this->apiError('05', 'Failed to upload file.', 400);
            }
            $data['upload_file'] = $uploadFileName;
        }

        $this->db->table('staff_advance')->where('id', $id)->update($data);

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_staff_advance',
                'action'     => 'update',
                'model'      => 'staff_advance',
                'model_id'   => $id,
                'changes'    => json_encode(['data' => $data, 'source' => 'staff_advance_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->fetchAdvanceRow($id);

        return $this->apiSuccess('Staff advance updated successfully.', [
            'advance' => $this->formatAdvance($row),
        ]);
    }

    /**
     * DELETE /api/staff-advance/{id}
     * Same as web Admin::delete_StaffAdvance().
     */
    public function destroy($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid advance id is required.', 400);
        }

        $existing = $this->fetchAdvanceRow($id);
        if (! $existing) {
            return $this->apiError('04', 'Staff advance not found.', 404);
        }

        $snapshot = $this->formatAdvance($existing);

        $this->db->table('staff_advance')->where('id', $id)->delete();

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_staff_advance',
                'action'     => 'delete',
                'model'      => 'staff_advance',
                'model_id'   => $id,
                'changes'    => json_encode(['deleted' => $snapshot, 'source' => 'staff_advance_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->apiSuccess('Staff advance deleted successfully.', [
            'id'      => $id,
            'deleted' => $snapshot,
        ]);
    }

    /**
     * @return array{error: ?string, data: ?array}
     */
    protected function buildAdvanceData(array $payload): array
    {
        $staffId    = (int) ($payload['staff_id'] ?? $payload['staff'] ?? 0);
        $advDate    = trim((string) ($payload['date'] ?? $payload['adv_date'] ?? ''));
        $bankCash   = trim((string) ($payload['bank_cash'] ?? ''));
        $amount     = $payload['amount'] ?? null;
        $locationId = (int) ($payload['location_id'] ?? $payload['location'] ?? 0);
        $despatchId = trim((string) ($payload['despatch_id'] ?? ''));
        $staffType  = $this->normalizeStaffType((string) ($payload['staff_type'] ?? $payload['user_type'] ?? $payload['type'] ?? ''));

        $errors = [];
        if ($staffId <= 0) {
            $errors[] = 'staff_id is required';
        }
        if ($advDate === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $advDate)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($bankCash === '' || ! in_array($bankCash, ['Cash', 'Bank'], true)) {
            $errors[] = 'bank_cash is required (Cash or Bank)';
        }
        if ($amount === null || $amount === '' || ! is_numeric($amount) || (float) $amount <= 0) {
            $errors[] = 'amount is required and must be greater than 0';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($errors !== []) {
            return ['error' => implode('; ', $errors), 'data' => null];
        }

        $staff = $this->db->table('staff')->where('id', $staffId)->get()->getRow();
        if (! $staff) {
            return ['error' => 'Invalid staff.', 'data' => null];
        }

        $dbUserType = strtoupper(trim((string) ($staff->user_type ?? '')));
        if ($staffType !== '' && $dbUserType !== '' && $dbUserType !== $staffType) {
            return ['error' => "staff_type mismatch. Selected employee is {$dbUserType}, but {$staffType} was sent.", 'data' => null];
        }

        if ($staff->doj !== '0000-00-00' && ! empty($staff->doj) && $advDate < $staff->doj) {
            return ['error' => 'Advance cannot be recorded before staff joining date (' . date('d-m-Y', strtotime($staff->doj)) . ').', 'data' => null];
        }
        if ($staff->resign_date !== '0000-00-00' && ! empty($staff->resign_date) && $advDate > $staff->resign_date) {
            return ['error' => 'Advance cannot be recorded after staff resign date (' . date('d-m-Y', strtotime($staff->resign_date)) . ').', 'data' => null];
        }

        $location = $this->db->table('location')->where('location_id', $locationId)->get()->getRow();
        if (! $location) {
            return ['error' => 'Invalid location.', 'data' => null];
        }

        $paidByResolved = $this->resolvePaidBySubAdminId($payload);
        if ($paidByResolved['error'] !== null) {
            return ['error' => $paidByResolved['error'], 'data' => null];
        }

        return [
            'error' => null,
            'data'  => [
                'staff_id'    => $staffId,
                'adv_date'    => $advDate,
                'bank_cash'   => $bankCash,
                'amount'      => (float) $amount,
                'location_id' => $locationId,
                'despatch_id' => $despatchId !== '' ? $despatchId : null,
                'paid_by'     => $paidByResolved['paid_by'],
            ],
        ];
    }

    /**
     * @return array{error: ?string, paid_by: ?string, paid_by_name: ?string}
     */
    protected function resolvePaidBySubAdminId(array $payload): array
    {
        $raw = trim((string) ($payload['paid_by'] ?? $payload['paid_by_id'] ?? ''));
        if ($raw === '') {
            return ['error' => null, 'paid_by' => null, 'paid_by_name' => null];
        }

        if (! ctype_digit($raw)) {
            return [
                'error'         => 'paid_by must be sub-admin user id',
                'paid_by'       => null,
                'paid_by_name'  => null,
            ];
        }

        $userId = (int) $raw;
        $user   = $this->db->table('user')
            ->select('id, full_name')
            ->where('id', $userId)
            ->where('user_type', 2)
            ->where('deleted_by', null)
            ->get()
            ->getRow();

        if (! $user) {
            return [
                'error'        => 'Invalid sub-admin for paid_by.',
                'paid_by'      => null,
                'paid_by_name' => null,
            ];
        }

        return [
            'error'        => null,
            'paid_by'      => (string) $userId,
            'paid_by_name' => $user->full_name ?? null,
        ];
    }

    protected function uploadAdvanceFile($file): ?string
    {
        if ($file->hasMoved()) {
            return null;
        }

        $dir = 'uploads/';
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = $file->getRandomName();

        return $file->move($dir, $fileName) ? $fileName : null;
    }

    protected function fetchAdvanceRow(int $id): ?object
    {
        return $this->db->table('staff_advance sa')
            ->select('sa.*, s.name AS staff_name, s.staff_code, s.user_type, l.location_name')
            ->join('staff s', 's.id = sa.staff_id', 'left')
            ->join('location l', 'l.location_id = sa.location_id', 'left')
            ->where('sa.id', $id)
            ->get()
            ->getRow();
    }

    protected function formatAdvance(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        $uploadFile = $row->upload_file ?? '';
        $paidById   = null;
        $paidByName = null;
        if (! empty($row->paid_by) && ctype_digit((string) $row->paid_by)) {
            $paidById = (int) $row->paid_by;
            $payer    = $this->db->table('user')->select('full_name')->where('id', $paidById)->get()->getRow();
            $paidByName = $payer->full_name ?? null;
        }

        return [
            'id'           => (int) $row->id,
            'staff_id'     => (int) $row->staff_id,
            'staff_name'   => $row->staff_name ?? null,
            'staff_code'   => $row->staff_code ?? null,
            'staff_label'  => ($row->staff_name && $row->staff_code)
                ? "{$row->staff_name} ({$row->staff_code})"
                : ($row->staff_name ?? null),
            'user_type'    => $row->user_type ?? null,
            'adv_date'     => $row->adv_date ?? null,
            'bank_cash'    => $row->bank_cash ?? null,
            'amount'       => isset($row->amount) ? (float) $row->amount : null,
            'location_id'  => (int) ($row->location_id ?? 0),
            'location_name'=> $row->location_name ?? null,
            'paid_by'      => $row->paid_by ?? null,
            'paid_by_id'   => $paidById,
            'paid_by_name' => $paidByName,
            'despatch_id'  => $row->despatch_id ?? null,
            'upload_file'  => $uploadFile !== '' ? $uploadFile : null,
            'upload_url'   => $uploadFile !== '' ? base_url('uploads/' . $uploadFile) : null,
        ];
    }

    protected function normalizeStaffType(string $raw): string
    {
        $raw = strtoupper(trim($raw));
        if ($raw === '') {
            return '';
        }

        $aliases = [
            'STAFF'        => 'STAFF',
            'STAFF MASTER' => 'STAFF',
            'EMPLOYEE'     => 'STAFF',
            'EMPLOY'       => 'STAFF',
            'DRIVER'       => 'DRIVER',
            'MECHANIC'     => 'MECHANIC',
        ];

        return $aliases[$raw] ?? $raw;
    }

    protected function staffTypeLabel(string $userType): string
    {
        return match (strtoupper($userType)) {
            'STAFF'   => 'Staff',
            'DRIVER'  => 'Driver',
            'MECHANIC'=> 'Mechanic',
            default   => $userType,
        };
    }

    protected function formatEmployee(object $row): array
    {
        $name      = $row->name ?? '';
        $staffCode = $row->staff_code ?? '';
        $label     = $staffCode !== '' ? "{$name} ({$staffCode})" : $name;

        $doj = $row->doj ?? null;
        if ($doj === '0000-00-00' || $doj === '0000-00-00 00:00:00') {
            $doj = null;
        }

        $resignDate = $row->resign_date ?? null;
        if ($resignDate === '0000-00-00' || $resignDate === '0000-00-00 00:00:00') {
            $resignDate = null;
        }

        return [
            'id'            => (int) $row->id,
            'staff_id'      => (int) $row->id,
            'name'          => $name,
            'staff_code'    => $staffCode,
            'label'         => $label,
            'user_type'     => $row->user_type ?? null,
            'location_id'   => ! empty($row->location_id) ? (int) $row->location_id : null,
            'location_name' => $row->location_name ?? null,
            'doj'           => $doj,
            'resign_date'   => $resignDate,
            'tel'           => $row->tel ?? null,
        ];
    }
}
