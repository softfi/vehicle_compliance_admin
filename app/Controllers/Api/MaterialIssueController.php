<?php

namespace App\Controllers\Api;

/**
 * Material Issue module APIs (admin/material_issue).
 */
class MaterialIssueController extends BaseApiController
{
    /**
     * Fixed particulars list — same as web material_issue_vw checkboxes.
     *
     * @return list<string>
     */
    protected function allowedParticulars(): array
    {
        return [
            'Stepny',
            'Jack',
            'Jack Rod',
            'Wheel Pana',
            'Pechkush',
            'Hammer',
            'Fire Extingusher',
            'Tirpal',
            'Safety Shoes',
            'Safety Jacket (2)',
            'Safety Helmet',
        ];
    }

    /**
     * GET /api/material-issue/particulars
     * Web form checkbox options.
     */
    public function particulars()
    {
        $items = $this->allowedParticulars();

        return $this->apiSuccess('Material particulars loaded.', [
            'total'       => count($items),
            'particulars' => $items,
        ]);
    }

    /**
     * GET /api/material-issue/drivers
     * Same driver dropdown as web material_issue (Getallstaf).
     * Optional: ?search=name_or_code
     */
    public function drivers()
    {
        $search = trim($this->request->getGet('search') ?? '');

        $builder = $this->db->table('staff s');
        $builder->select('s.id, s.name, s.staff_code, s.user_type, s.location_id, s.tel, l.location_name');
        $builder->join('location l', 'l.location_id = s.location_id', 'left');

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
                'user_type'     => $row->user_type ?? null,
                'location_id'   => $row->location_id ? (int) $row->location_id : null,
                'location_name' => $row->location_name ?? null,
                'tel'           => $row->tel ?? null,
            ];
        }

        return $this->apiSuccess('Drivers loaded.', [
            'total'   => count($drivers),
            'drivers' => $drivers,
        ]);
    }

    /**
     * GET|POST /api/material-issue/driver/assigned-vehicle
     * Same as web Admin::get_driver_vehicle() on driver select.
     * Query/body: driver_id=14
     */
    public function assignedVehicle()
    {
        $payload  = $this->parseRequestPayload();
        $driverId = (int) ($this->request->getGet('driver_id')
            ?? $payload['driver_id']
            ?? $payload['driver']
            ?? 0);

        if ($driverId <= 0) {
            return $this->apiError('03', 'driver_id is required.', 400);
        }

        $driver = $this->db->table('staff')->where('id', $driverId)->get()->getRow();
        if (! $driver) {
            return $this->apiError('15', 'Invalid driver.', 400);
        }

        $assignment = $this->db->table('driver_assignment da')
            ->select('da.id AS assignment_id, da.vehicle_no, da.driver, da.from_date, da.to_date,
                da.opening_hsd, da.opening_km, da.closing_hsd, da.closing_km,
                v.id AS vehicle_id, v.vehicle_no, v.location_id, v.chassis_no, v.vehicle_type,
                l.location_name, vt.type_name AS vehicle_type_name')
            ->join('vehicle v', 'v.id = da.vehicle_no', 'left')
            ->join('location l', 'l.location_id = v.location_id', 'left')
            ->join('vehicle_types vt', 'vt.id = v.vehicle_type', 'left')
            ->where('da.driver', $driverId)
            ->where('(da.to_date IS NULL OR da.to_date = "0000-00-00" OR da.to_date >= CURDATE())', null, false)
            ->orderBy('da.id', 'DESC')
            ->get()
            ->getRow();

        if (! $assignment) {
            return $this->apiError('18', 'No vehicle assigned.', 404);
        }

        $toDate = $assignment->to_date ?? null;
        if ($toDate === '' || $toDate === '0000-00-00') {
            $toDate = null;
        }

        return $this->apiSuccess('Assigned vehicle loaded.', [
            'driver' => [
                'id'         => $driverId,
                'name'       => $driver->name ?? null,
                'staff_code' => $driver->staff_code ?? null,
                'label'      => ($driver->staff_code ?? '') !== ''
                    ? "{$driver->name} ({$driver->staff_code})"
                    : ($driver->name ?? null),
            ],
            'vehicle_id'   => (int) $assignment->vehicle_id,
            'vehicle_no'   => $assignment->vehicle_no ?? null,
            'vehicle'      => [
                'id'                => (int) $assignment->vehicle_id,
                'vehicle_no'        => $assignment->vehicle_no ?? null,
                'location_id'       => $assignment->location_id ? (int) $assignment->location_id : null,
                'location_name'     => $assignment->location_name ?? null,
                'vehicle_type_id'   => $assignment->vehicle_type ? (int) $assignment->vehicle_type : null,
                'vehicle_type_name' => $assignment->vehicle_type_name ?? null,
                'chassis_no'        => $assignment->chassis_no ?? null,
            ],
            'assignment' => [
                'assignment_id' => (int) $assignment->assignment_id,
                'from_date'     => $assignment->from_date,
                'to_date'       => $toDate,
                'opening_hsd'   => isset($assignment->opening_hsd) ? (float) $assignment->opening_hsd : null,
                'opening_km'    => isset($assignment->opening_km) ? (float) $assignment->opening_km : null,
                'closing_hsd'   => isset($assignment->closing_hsd) && $assignment->closing_hsd !== '' ? (float) $assignment->closing_hsd : null,
                'closing_km'    => isset($assignment->closing_km) && $assignment->closing_km !== '' ? (float) $assignment->closing_km : null,
            ],
        ]);
    }

    /**
     * GET /api/material-issue?filter_driver=&driver_id=
     * Same list as web material_issue history table.
     */
    public function index()
    {
        $driverId = (int) ($this->request->getGet('filter_driver')
            ?? $this->request->getGet('driver_id')
            ?? $this->request->getGet('driver')
            ?? 0);

        $builder = $this->db->table('driver_material_issue dmi');
        $builder->select('dmi.*, s.name AS driver_name, s.staff_code');
        $builder->join('staff s', 's.id = dmi.driver_id', 'left');

        if ($driverId > 0) {
            $builder->where('dmi.driver_id', $driverId);
        }

        $builder->orderBy('dmi.issued_date', 'DESC');
        $builder->orderBy('dmi.id', 'DESC');
        $rows = $builder->get()->getResult();

        return $this->apiSuccess('Material issues loaded.', [
            'filters' => [
                'driver_id' => $driverId > 0 ? $driverId : null,
            ],
            'total'  => count($rows),
            'issues' => array_map(fn ($r) => $this->formatIssue($r), $rows),
        ]);
    }

    /**
     * GET /api/material-issue/{id}
     * Detail for edit modal (driver readonly, date + items editable).
     */
    public function show($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid issue id is required.', 400);
        }

        $row = $this->fetchIssueRow($id);
        if (! $row) {
            return $this->apiError('04', 'Material issue not found.', 404);
        }

        $selectedItems = $this->parseItemNameToArray($row->item_name ?? '');

        return $this->apiSuccess('Material issue loaded.', [
            'issue'       => $this->formatIssue($row, $selectedItems),
            'particulars' => $this->buildParticularsWithSelection($selectedItems),
        ]);
    }

    /**
     * POST /api/material-issue/store
     * Same as web Admin::save_material_issue().
     * Fields: driver_id, issued_date, items (array of particular names)
     */
    public function store()
    {
        $payload    = $this->parseRequestPayload();
        $driverId   = (int) ($payload['driver_id'] ?? $payload['driver'] ?? 0);
        $issuedDate = trim($payload['issued_date'] ?? $payload['date'] ?? '');
        $items      = $this->normalizeItemsFromPayload($payload);

        $errors = [];
        if ($driverId <= 0) {
            $errors[] = 'driver_id is required';
        }
        if ($issuedDate === '') {
            $errors[] = 'issued_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $issuedDate)) {
            $errors[] = 'issued_date must be YYYY-MM-DD';
        }
        if ($items === []) {
            $errors[] = 'items is required (select at least one particular)';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $invalidItems = array_diff($items, $this->allowedParticulars());
        if ($invalidItems !== []) {
            return $this->apiError('03', 'Invalid items: ' . implode(', ', $invalidItems), 400);
        }

        $driver = $this->db->table('staff')->where('id', $driverId)->get()->getRow();
        if (! $driver) {
            return $this->apiError('15', 'Invalid driver.', 400);
        }

        // Web: implode(', ', $items) → single row in driver_material_issue
        $insert = [
            'driver_id'   => $driverId,
            'item_name'   => implode(', ', $items),
            'issued_date' => $issuedDate,
            'status'      => 'Active',
        ];

        $this->db->table('driver_material_issue')->insert($insert);
        $insertId = (int) $this->db->insertID();

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_material_issue',
                'action'     => 'create',
                'model'      => 'driver_material_issue',
                'model_id'   => $insertId,
                'changes'    => json_encode(['data' => $insert, 'source' => 'material_issue_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->fetchIssueRow($insertId);

        return $this->apiSuccess('Material issued successfully.', [
            'issue' => $this->formatIssue($row, $items),
        ], 201);
    }

    /**
     * POST /api/material-issue/{id}
     * Same as web Admin::update_material_issue() — only issued_date and items update.
     */
    public function update($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid issue id is required.', 400);
        }

        $existing = $this->fetchIssueRow($id);
        if (! $existing) {
            return $this->apiError('04', 'Material issue not found.', 404);
        }

        $payload    = $this->parseRequestPayload();
        $issuedDate = trim($payload['issued_date'] ?? $payload['date'] ?? '');
        $items      = $this->normalizeItemsFromPayload($payload);

        $errors = [];
        if ($issuedDate === '') {
            $errors[] = 'issued_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $issuedDate)) {
            $errors[] = 'issued_date must be YYYY-MM-DD';
        }
        if ($items === []) {
            $errors[] = 'items is required (select at least one particular)';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $invalidItems = array_diff($items, $this->allowedParticulars());
        if ($invalidItems !== []) {
            return $this->apiError('03', 'Invalid items: ' . implode(', ', $invalidItems), 400);
        }

        $update = [
            'item_name'   => implode(', ', $items),
            'issued_date' => $issuedDate,
        ];

        $this->db->table('driver_material_issue')->where('id', $id)->update($update);

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_material_issue',
                'action'     => 'update',
                'model'      => 'driver_material_issue',
                'model_id'   => $id,
                'changes'    => json_encode(['data' => $update, 'source' => 'material_issue_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->fetchIssueRow($id);

        return $this->apiSuccess('Material issue updated successfully.', [
            'issue' => $this->formatIssue($row, $items),
        ]);
    }

    /**
     * @return list<string>
     */
    protected function normalizeItemsFromPayload(array $payload): array
    {
        $raw = $payload['items'] ?? $payload['particulars'] ?? null;

        if (is_string($raw)) {
            $raw = array_map('trim', explode(',', $raw));
        }

        if (! is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return array_values(array_unique($items));
    }

    /**
     * @return list<string>
     */
    protected function parseItemNameToArray(?string $itemName): array
    {
        if ($itemName === null || trim($itemName) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $itemName))));
    }

    /**
     * Edit screen checkboxes — web edit modal jaisa.
     *
     * @param list<string> $selectedItems
     * @return list<array{name: string, selected: bool}>
     */
    protected function buildParticularsWithSelection(array $selectedItems): array
    {
        $selectedMap = array_flip($selectedItems);
        $list        = [];

        foreach ($this->allowedParticulars() as $name) {
            $list[] = [
                'name'     => $name,
                'selected' => isset($selectedMap[$name]),
            ];
        }

        return $list;
    }

    protected function fetchIssueRow(int $id): ?object
    {
        return $this->db->table('driver_material_issue dmi')
            ->select('dmi.*, s.name AS driver_name, s.staff_code')
            ->join('staff s', 's.id = dmi.driver_id', 'left')
            ->where('dmi.id', $id)
            ->get()
            ->getRow();
    }

    /**
     * @param list<string> $items
     */
    protected function formatIssue(?object $row, array $items = []): ?array
    {
        if (! $row) {
            return null;
        }

        if ($items === [] && ! empty($row->item_name)) {
            $items = array_map('trim', explode(',', $row->item_name));
        }

        return [
            'id'          => (int) $row->id,
            'driver_id'   => (int) $row->driver_id,
            'driver_name' => $row->driver_name ?? null,
            'staff_code'  => $row->staff_code ?? null,
            'driver_label' => ($row->driver_name && $row->staff_code)
                ? "{$row->driver_name} ({$row->staff_code})"
                : ($row->driver_name ?? null),
            'items'       => $items,
            'item_name'   => $row->item_name ?? null,
            'issued_date' => $row->issued_date,
            'status'      => $row->status ?? 'Active',
        ];
    }
}
