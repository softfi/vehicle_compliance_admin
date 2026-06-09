<?php

namespace App\Controllers\Api;

/**
 * Material Re-Issue APIs (admin/re_issue).
 */
class MaterialReissueController extends BaseApiController
{
    /**
     * GET|POST /api/material-reissue/driver/active-items
     * Same as web driver select: active items + assigned vehicle.
     * Query/body: driver_id=14
     */
    public function activeItems()
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

        $activeRows = $this->adminModel->get_active_driver_materials($driverId);

        $items = [];
        foreach ($activeRows as $row) {
            $names = $this->parseItemNameToArray($row->item_name ?? '');
            foreach ($names as $name) {
                $items[] = [
                    'id'          => (int) $row->id,
                    'issue_id'    => (int) $row->id,
                    'item_name'   => $name,
                    'label'       => $name,
                    'issued_date' => $row->issued_date ?? null,
                    'status'      => $row->status ?? 'Active',
                ];
            }
        }

        return $this->apiSuccess('Active replaceable items loaded.', [
            'driver' => [
                'id'         => $driverId,
                'name'       => $driver->name ?? null,
                'staff_code' => $driver->staff_code ?? null,
                'label'      => ($driver->staff_code ?? '') !== ''
                    ? "{$driver->name} ({$driver->staff_code})"
                    : ($driver->name ?? null),
            ],
            'assigned_vehicle' => $this->buildAssignedVehiclePayload($driverId),
            'total'            => count($items),
            'items'            => $items,
        ]);
    }

    /**
     * Same as web Admin::get_driver_vehicle() / material-issue assigned-vehicle API.
     *
     * @return array<string, mixed>
     */
    protected function buildAssignedVehiclePayload(int $driverId): array
    {
        $assignment = $this->db->table('driver_assignment da')
            ->select('da.id AS assignment_id, da.vehicle_no, da.driver, da.from_date, da.to_date,
                da.opening_hsd, da.opening_km, da.closing_hsd, da.closing_km,
                v.id AS vehicle_id, v.vehicle_no AS reg_vehicle_no, v.location_id, v.chassis_no, v.vehicle_type,
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
            return [
                'assigned'   => false,
                'message'    => 'No vehicle assigned',
                'vehicle_id' => null,
                'vehicle_no' => null,
                'vehicle'    => null,
                'assignment' => null,
            ];
        }

        $toDate = $assignment->to_date ?? null;
        if ($toDate === '' || $toDate === '0000-00-00') {
            $toDate = null;
        }

        $vehicleNo = $assignment->reg_vehicle_no ?? $assignment->vehicle_no ?? null;

        return [
            'assigned'   => true,
            'message'    => null,
            'vehicle_id' => $assignment->vehicle_id ? (int) $assignment->vehicle_id : null,
            'vehicle_no' => $vehicleNo,
            'vehicle'    => [
                'id'                => $assignment->vehicle_id ? (int) $assignment->vehicle_id : null,
                'vehicle_no'        => $vehicleNo,
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
        ];
    }

    /**
     * POST /api/material-reissue/store
     * Same as web Admin::save_re_issue() — multipart/form-data.
     *
     * Web stores only: driver_id, item_name, pics, reissue_date, remarks
     * in driver_material_reissue (item_id is NOT saved to DB).
     * item_id is optional and used only to mark old issue Replaced + create new Active row.
     *
     * Fields:
     * - driver_id (required)
     * - item_name (required)
     * - item_id / issue_id / id (optional) — driver_material_issue.id from active-items API
     * - reissue_date (required, YYYY-MM-DD)
     * - remarks (optional)
     * - old_item_pic (required file)
     * - new_item_pic (required file)
     */
    public function store()
    {
        $payload     = $this->parseRequestPayload();
        $driverId    = (int) ($payload['driver_id'] ?? $payload['driver'] ?? 0);
        $itemId      = (int) ($payload['item_id'] ?? $payload['issue_id'] ?? $payload['id'] ?? 0);
        $itemName    = trim((string) ($payload['item_name'] ?? ''));
        $reissueDate = trim((string) ($payload['reissue_date'] ?? $payload['date'] ?? ''));
        $remarks     = trim((string) ($payload['remarks'] ?? ''));

        $errors = [];
        if ($driverId <= 0) {
            $errors[] = 'driver_id is required';
        }
        if ($itemName === '') {
            $errors[] = 'item_name is required';
        }
        if ($reissueDate === '') {
            $errors[] = 'reissue_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $reissueDate)) {
            $errors[] = 'reissue_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $driver = $this->db->table('staff')->where('id', $driverId)->get()->getRow();
        if (! $driver) {
            return $this->apiError('15', 'Invalid driver.', 400);
        }

        // Web does not validate item_id; if omitted, resolve from active items by item_name.
        $itemId = $this->resolveIssueIdForReplacement($driverId, $itemId, $itemName);

        $oldFile = $this->request->getFile('old_item_pic');
        $newFile = $this->request->getFile('new_item_pic');

        if ($oldFile === null || ! $oldFile->isValid()) {
            return $this->apiError('03', 'old_item_pic file is required.', 400);
        }
        if ($newFile === null || ! $newFile->isValid()) {
            return $this->apiError('03', 'new_item_pic file is required.', 400);
        }

        $oldPicName = $this->uploadMaterialImage($oldFile);
        if ($oldPicName === null) {
            return $this->apiError('05', 'Failed to upload old_item_pic.', 400);
        }

        $newPicName = $this->uploadMaterialImage($newFile);
        if ($newPicName === null) {
            return $this->apiError('05', 'Failed to upload new_item_pic.', 400);
        }

        $reissueData = [
            'driver_id'    => $driverId,
            'item_name'    => $itemName,
            'old_item_pic' => $oldPicName,
            'new_item_pic' => $newPicName,
            'reissue_date' => $reissueDate,
            'remarks'      => $remarks !== '' ? $remarks : null,
        ];

        $this->db->table('driver_material_reissue')->insert($reissueData);
        $reissueId = (int) $this->db->insertID();

        $newIssueId = null;
        if ($itemId > 0) {
            // Same as web Admin::save_re_issue() — no driver/status validation on item_id.
            $this->db->table('driver_material_issue')
                ->where('id', $itemId)
                ->update(['status' => 'Replaced']);

            $newIssueData = [
                'driver_id'   => $driverId,
                'item_name'   => $itemName,
                'issued_date' => $reissueDate,
                'status'      => 'Active',
            ];
            $this->db->table('driver_material_issue')->insert($newIssueData);
            $newIssueId = (int) $this->db->insertID();
        }

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_material_reissue',
                'action'     => 'create',
                'model'      => 'driver_material_reissue',
                'model_id'   => $reissueId,
                'changes'    => json_encode([
                    'data'         => $reissueData,
                    'item_id'      => $itemId > 0 ? $itemId : null,
                    'new_issue_id' => $newIssueId,
                    'source'       => 'material_reissue_api',
                ]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->fetchReissueRow($reissueId);

        $issuePayload = null;
        if ($itemId > 0) {
            $issuePayload = [
                'replaced_issue_id' => $itemId,
                'new_issue_id'      => $newIssueId,
                'new_status'        => 'Active',
            ];
        }

        return $this->apiSuccess('Material re-issue recorded successfully.', [
            'reissue' => $this->formatReissue($row),
            'issue'   => $issuePayload,
        ], 201);
    }

    /**
     * GET /api/material-reissue
     * Same list as web re_issue history table.
     * Optional: ?driver_id=14 or ?filter_driver=14
     */
    public function index()
    {
        $driverId = (int) ($this->request->getGet('filter_driver')
            ?? $this->request->getGet('driver_id')
            ?? $this->request->getGet('driver')
            ?? 0);

        $builder = $this->db->table('driver_material_reissue dmr');
        $builder->select('dmr.*, s.name AS driver_name, s.staff_code');
        $builder->join('staff s', 's.id = dmr.driver_id', 'left');

        if ($driverId > 0) {
            $builder->where('dmr.driver_id', $driverId);
        }

        $builder->orderBy('dmr.reissue_date', 'DESC');
        $builder->orderBy('dmr.id', 'DESC');
        $rows = $builder->get()->getResult();

        $reissues = [];
        foreach ($rows as $row) {
            $reissues[] = $this->formatReissue($row);
        }

        return $this->apiSuccess('Material re-issues loaded.', [
            'filters' => [
                'driver_id' => $driverId > 0 ? $driverId : null,
            ],
            'total'    => count($reissues),
            'reissues' => $reissues,
        ]);
    }

    /**
     * GET /api/material-reissue/{id}
     * Same as web edit modal — driver/item readonly, date/remarks/pics editable.
     */
    public function show($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid re-issue id is required.', 400);
        }

        $row = $this->fetchReissueRow($id);
        if (! $row) {
            return $this->apiError('04', 'Material re-issue not found.', 404);
        }

        return $this->apiSuccess('Material re-issue loaded.', [
            'reissue' => $this->formatReissue($row),
            'editable_fields' => [
                'reissue_date',
                'remarks',
                'old_item_pic',
                'new_item_pic',
            ],
            'readonly_fields' => [
                'driver_id',
                'driver_name',
                'item_name',
            ],
        ]);
    }

    /**
     * POST /api/material-reissue/{id}
     * Same as web Admin::update_re_issue() — multipart/form-data.
     *
     * Fields:
     * - reissue_date (required)
     * - remarks (optional)
     * - old_item_pic (optional file)
     * - new_item_pic (optional file)
     */
    public function update($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid re-issue id is required.', 400);
        }

        $existing = $this->fetchReissueRow($id);
        if (! $existing) {
            return $this->apiError('04', 'Material re-issue not found.', 404);
        }

        $payload     = $this->parseRequestPayload();
        $reissueDate = trim((string) ($payload['reissue_date'] ?? $payload['date'] ?? ''));
        $remarks     = trim((string) ($payload['remarks'] ?? ''));

        $errors = [];
        if ($reissueDate === '') {
            $errors[] = 'reissue_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $reissueDate)) {
            $errors[] = 'reissue_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $data = [
            'reissue_date' => $reissueDate,
            'remarks'      => $remarks !== '' ? $remarks : null,
        ];

        $oldFile = $this->request->getFile('old_item_pic');
        if ($oldFile !== null && $oldFile->isValid() && ! $oldFile->hasMoved()) {
            $oldPicName = $this->uploadMaterialImage($oldFile);
            if ($oldPicName === null) {
                return $this->apiError('05', 'Failed to upload old_item_pic.', 400);
            }
            $this->deleteMaterialImage($existing->old_item_pic ?? null);
            $data['old_item_pic'] = $oldPicName;
        }

        $newFile = $this->request->getFile('new_item_pic');
        if ($newFile !== null && $newFile->isValid() && ! $newFile->hasMoved()) {
            $newPicName = $this->uploadMaterialImage($newFile);
            if ($newPicName === null) {
                return $this->apiError('05', 'Failed to upload new_item_pic.', 400);
            }
            $this->deleteMaterialImage($existing->new_item_pic ?? null);
            $data['new_item_pic'] = $newPicName;
        }

        $this->db->table('driver_material_reissue')->where('id', $id)->update($data);

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_material_reissue',
                'action'     => 'update',
                'model'      => 'driver_material_reissue',
                'model_id'   => $id,
                'changes'    => json_encode(['data' => $data, 'source' => 'material_reissue_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->fetchReissueRow($id);

        return $this->apiSuccess('Material re-issue updated successfully.', [
            'reissue' => $this->formatReissue($row),
        ]);
    }

    /**
     * DELETE /api/material-reissue/{id}
     * Removes re-issue history record and uploaded images.
     */
    public function destroy($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid re-issue id is required.', 400);
        }

        $existing = $this->fetchReissueRow($id);
        if (! $existing) {
            return $this->apiError('04', 'Material re-issue not found.', 404);
        }

        $snapshot = $this->formatReissue($existing);

        $this->deleteMaterialImage($existing->old_item_pic ?? null);
        $this->deleteMaterialImage($existing->new_item_pic ?? null);

        $this->db->table('driver_material_reissue')->where('id', $id)->delete();

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_material_reissue',
                'action'     => 'delete',
                'model'      => 'driver_material_reissue',
                'model_id'   => $id,
                'changes'    => json_encode(['deleted' => $snapshot, 'source' => 'material_reissue_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->apiSuccess('Material re-issue deleted successfully.', [
            'deleted' => $snapshot,
        ]);
    }

    /**
     * Web uses item_id only for side effects; it is not stored in driver_material_reissue.
     * If API client omits item_id, pick the first active issue row containing item_name.
     */
    protected function resolveIssueIdForReplacement(int $driverId, int $itemId, string $itemName): int
    {
        if ($itemId > 0) {
            return $itemId;
        }

        $activeRows = $this->adminModel->get_active_driver_materials($driverId);
        foreach ($activeRows as $row) {
            $names = $this->parseItemNameToArray($row->item_name ?? '');
            if (in_array($itemName, $names, true)) {
                return (int) $row->id;
            }
        }

        return 0;
    }

    protected function uploadMaterialImage($file): ?string
    {
        if ($file->hasMoved()) {
            return null;
        }

        $dir = 'uploads/material/';
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = $file->getRandomName();
        if (! $file->move($dir, $fileName)) {
            return null;
        }

        return $fileName;
    }

    protected function deleteMaterialImage(?string $fileName): void
    {
        if ($fileName === null || trim($fileName) === '') {
            return;
        }

        $path = 'uploads/material/' . ltrim($fileName, '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function fetchReissueRow(int $id): ?object
    {
        return $this->db->table('driver_material_reissue dmr')
            ->select('dmr.*, s.name AS driver_name, s.staff_code')
            ->join('staff s', 's.id = dmr.driver_id', 'left')
            ->where('dmr.id', $id)
            ->get()
            ->getRow();
    }

    protected function formatReissue(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        $oldPic = $row->old_item_pic ?? '';
        $newPic = $row->new_item_pic ?? '';

        return [
            'id'           => (int) $row->id,
            'driver_id'    => (int) $row->driver_id,
            'driver_name'  => $row->driver_name ?? null,
            'staff_code'   => $row->staff_code ?? null,
            'driver_label' => ($row->driver_name && $row->staff_code)
                ? "{$row->driver_name} ({$row->staff_code})"
                : ($row->driver_name ?? null),
            'item_name'    => $row->item_name ?? null,
            'reissue_date' => $row->reissue_date ?? null,
            'remarks'      => $row->remarks ?? null,
            'old_item_pic' => $oldPic !== '' ? $oldPic : null,
            'new_item_pic' => $newPic !== '' ? $newPic : null,
            'old_item_url' => $oldPic !== '' ? base_url('uploads/material/' . $oldPic) : null,
            'new_item_url' => $newPic !== '' ? base_url('uploads/material/' . $newPic) : null,
        ];
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
}
