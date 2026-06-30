<?php

namespace App\Controllers\Api;

/**
 * Despatch entry APIs (admin/despatch_entry).
 */
class DespatchEntryController extends BaseApiController
{
    /**
     * GET /api/despatch-entry/form
     * Dropdown data for create form — vehicles + active DO registrations.
     */
    public function form()
    {
        $searchVehicle = trim((string) ($this->request->getGet('search_vehicle') ?? ''));
        $searchDo      = trim((string) ($this->request->getGet('search_do') ?? ''));

        $vehicles = [];
        foreach ($this->adminModel->Getvehicle() as $vehicle) {
            $vehicleNo = trim((string) ($vehicle->vehicle_no ?? ''));
            if ($searchVehicle !== '' && stripos($vehicleNo, $searchVehicle) === false) {
                continue;
            }

            $vehicles[] = [
                'id'          => (int) $vehicle->id,
                'vehicle_id'  => (int) $vehicle->id,
                'vehicle_no'  => $vehicleNo,
                'label'       => $vehicleNo,
                'location_id' => isset($vehicle->location_id) ? (int) $vehicle->location_id : null,
            ];
        }

        $dos = [];
        foreach ($this->adminModel->doregistration_dtls() as $do) {
            $doNo = trim((string) ($do->do_no ?? ''));
            if ($searchDo !== '' && stripos($doNo, $searchDo) === false) {
                continue;
            }

            $dos[] = [
                'id'                 => (int) $do->do_registration_id,
                'do_registration_id' => (int) $do->do_registration_id,
                'do_no'              => $doNo,
                'label'              => $doNo,
                'party_name'         => $do->party_name ?? null,
                'from_date'          => $do->from_date ?? null,
                'to_date'            => $do->to_date ?? null,
            ];
        }

        return $this->apiSuccess('Despatch form data loaded.', [
            'total_vehicles' => count($vehicles),
            'total_dos'      => count($dos),
            'vehicles'       => $vehicles,
            'dos'            => $dos,
        ]);
    }

    /**
     * GET /api/despatch-entry?from_date=&to_date=&vehicle_id=&do_id=&search=
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

        $vehicleId = (int) ($this->request->getGet('vehicle_id') ?? $this->request->getGet('vehicle_no') ?? 0);
        $doId      = (int) ($this->request->getGet('do_id') ?? $this->request->getGet('do_no') ?? 0);
        $search    = trim((string) ($this->request->getGet('search') ?? ''));

        $rows = $this->adminModel->despatch_dtls($fromDate, $toDate);
        $entries = [];

        foreach ($rows as $row) {
            if ($vehicleId > 0 && (int) ($row->vehicle_no ?? 0) !== $vehicleId) {
                continue;
            }
            if ($doId > 0 && (int) ($row->do_no ?? 0) !== $doId) {
                continue;
            }
            if ($search !== '') {
                $haystack = strtolower(
                    ($row->vehicle_number ?? '') . ' '
                    . ($row->doreg_no ?? '') . ' '
                    . ($row->ref_no ?? '') . ' '
                    . ($row->quantity ?? '')
                );
                if (strpos($haystack, strtolower($search)) === false) {
                    continue;
                }
            }

            $entries[] = $this->formatDespatch($row);
        }

        return $this->apiSuccess('Despatch entries loaded.', [
            'filters' => [
                'from_date'  => $fromDate,
                'to_date'    => $toDate,
                'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
                'do_id'      => $doId > 0 ? $doId : null,
                'search'     => $search !== '' ? $search : null,
            ],
            'total'   => count($entries),
            'entries' => $entries,
        ]);
    }

    /**
     * GET /api/despatch-entry/{id}
     */
    public function show($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid despatch_id is required.', 400);
        }

        $row = $this->fetchDespatchRow($id);
        if ($row === null) {
            return $this->apiError('04', 'Despatch entry not found.', 404);
        }

        return $this->apiSuccess('Despatch entry loaded.', [
            'entry' => $this->formatDespatch($row),
        ]);
    }

    /**
     * POST /api/despatch-entry/store
     * POST /api/dispatch-entry/store
     * Public — no Bearer token required (same pattern as /api/diesel/store).
     *
     * Single entry JSON:
     * {
     *   "vehicle_id": 74,
     *   "do_id": 433,
     *   "quantity": "Trip-40",
     *   "ref_no": "CH-12345",
     *   "des_date": "2026-06-18"
     * }
     *
     * Bulk JSON:
     * { "entries": [ { ... }, { ... } ] }
     *
     * Aliases accepted:
     * - vehicle_id / vehicle_no (id or truck number string)
     * - do_id / do_no / do_registration_id (id or DO number string)
     * - des_date / date
     */
    public function store()
    {
        $payload = $this->mergeRequestPayload();
        $entries = $this->normalizeStoreEntries($payload);

        if ($entries === []) {
            return $this->apiError('03', 'At least one despatch entry is required.', 400);
        }

        $saved = [];
        $this->db->transStart();

        foreach ($entries as $index => $entryPayload) {
            $built = $this->buildDespatchData($entryPayload);
            if ($built['error'] !== null) {
                $this->db->transRollback();

                return $this->apiError('03', 'Entry #' . ($index + 1) . ': ' . $built['error'], 400);
            }

            $this->db->table('despatch')->insert($built['data']);
            $insertId = (int) $this->db->insertID();

            $this->logActivity('create', $insertId, $built['data']);
            $row = $this->fetchDespatchRow($insertId);
            if ($row !== null) {
                $saved[] = $this->formatDespatch($row);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to store despatch entry.', 500);
        }

        $message = count($saved) === 1
            ? 'Despatch entry stored successfully.'
            : count($saved) . ' despatch entries stored successfully.';

        return $this->apiSuccess($message, [
            'total'   => count($saved),
            'entries' => $saved,
        ], 201);
    }

    /**
     * POST /api/despatch-entry/{id}
     * Same as web Admin::edit_despatch_entry(). Role: 3.5
     */
    public function update($id = null)
    {
        if (! $this->hasRole('3.5')) {
            return $this->apiError('07', 'You do not have permission to edit despatch entry.', 403);
        }

        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid despatch_id is required.', 400);
        }

        $existing = $this->fetchDespatchRow($id);
        if ($existing === null) {
            return $this->apiError('04', 'Despatch entry not found.', 404);
        }

        $payload = $this->mergeRequestPayload();
        $built   = $this->buildDespatchData($payload);
        if ($built['error'] !== null) {
            return $this->apiError('03', $built['error'], 400);
        }

        $this->db->table('despatch')->where('despatch_id', $id)->update($built['data']);
        $this->logActivity('update', $id, $built['data']);

        $row = $this->fetchDespatchRow($id);

        return $this->apiSuccess('Despatch entry updated successfully.', [
            'entry' => $this->formatDespatch($row),
        ]);
    }

    /**
     * DELETE /api/despatch-entry/{id}
     * Same as web Admin::delete_despatch(). Role: 3.6
     */
    public function destroy($id = null)
    {
        if (! $this->hasRole('3.6')) {
            return $this->apiError('07', 'You do not have permission to delete despatch entry.', 403);
        }

        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid despatch_id is required.', 400);
        }

        $existing = $this->fetchDespatchRow($id);
        if ($existing === null) {
            return $this->apiError('04', 'Despatch entry not found.', 404);
        }

        $deleteData = [
            'deleted_by' => $this->authUserId(),
            'deleted_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('despatch')->where('despatch_id', $id)->update($deleteData);
        $this->logActivity('delete', $id, $deleteData);

        return $this->apiSuccess('Despatch entry deleted successfully.', [
            'despatch_id' => $id,
            'deleted'     => $this->formatDespatch($existing),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeStoreEntries(array $payload): array
    {
        $entries = $payload['entries'] ?? $payload['despatch_entries'] ?? null;
        if (is_string($entries)) {
            $decoded = json_decode($entries, true);
            $entries = is_array($decoded) ? $decoded : [];
        }

        if (is_array($entries) && $entries !== []) {
            return array_values(array_filter($entries, 'is_array'));
        }

        if (
            $this->payloadValue($payload, ['vehicle_id', 'vehicle_no'], '') !== ''
            || $this->payloadValue($payload, ['do_id', 'do_no', 'do_registration_id'], '') !== ''
        ) {
            return [$payload];
        }

        return [];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{error: ?string, data: array<string, mixed>}
     */
    private function buildDespatchData(array $payload): array
    {
        $vehicleResult = $this->resolveVehicle($payload);
        if ($vehicleResult['error'] !== null) {
            return ['error' => $vehicleResult['error'], 'data' => []];
        }

        $doResult = $this->resolveDo($payload);
        if ($doResult['error'] !== null) {
            return ['error' => $doResult['error'], 'data' => []];
        }

        $desDate = trim((string) $this->payloadValue($payload, ['des_date', 'date'], ''));
        $quantity = trim((string) $this->payloadValue($payload, ['quantity', 'qty'], ''));
        $refNo    = trim((string) $this->payloadValue($payload, ['ref_no', 'refno', 'reference_no'], ''));

        if ($desDate === '') {
            return ['error' => 'des_date is required', 'data' => []];
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $desDate)) {
            return ['error' => 'des_date must be YYYY-MM-DD', 'data' => []];
        }
        if ($quantity === '') {
            return ['error' => 'quantity is required', 'data' => []];
        }
        if ($refNo === '') {
            return ['error' => 'ref_no is required', 'data' => []];
        }

        return [
            'error' => null,
            'data'  => [
                'vehicle_no' => $vehicleResult['id'],
                'do_no'      => $doResult['id'],
                'quantity'   => $quantity,
                'ref_no'     => $refNo,
                'des_date'   => $desDate,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{error: ?string, id: int}
     */
    private function resolveVehicle(array $payload): array
    {
        $vehicleId = (int) $this->payloadValue($payload, ['vehicle_id'], 0);
        if ($vehicleId > 0) {
            $row = $this->db->table('vehicle')->where('id', $vehicleId)->get()->getRow();
            if ($row === null) {
                return ['error' => 'Vehicle not found for vehicle_id ' . $vehicleId, 'id' => 0];
            }

            return ['error' => null, 'id' => $vehicleId];
        }

        $vehicleNo = trim((string) $this->payloadValue($payload, ['vehicle_no', 'vehicle_number', 'truck_no'], ''));
        if ($vehicleNo === '') {
            return ['error' => 'vehicle_id or vehicle_no is required', 'id' => 0];
        }

        if (ctype_digit($vehicleNo)) {
            $row = $this->db->table('vehicle')->where('id', (int) $vehicleNo)->get()->getRow();
            if ($row !== null) {
                return ['error' => null, 'id' => (int) $row->id];
            }
        }

        $row = $this->db->table('vehicle')->where('vehicle_no', $vehicleNo)->get()->getRow();
        if ($row === null) {
            return ['error' => 'Vehicle not found: ' . $vehicleNo, 'id' => 0];
        }

        return ['error' => null, 'id' => (int) $row->id];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{error: ?string, id: int}
     */
    private function resolveDo(array $payload): array
    {
        $doId = (int) $this->payloadValue($payload, ['do_id', 'do_registration_id'], 0);
        if ($doId > 0) {
            $row = $this->db->table('do_registration')
                ->where('do_registration_id', $doId)
                ->where('deleted_by', null)
                ->get()
                ->getRow();
            if ($row === null) {
                return ['error' => 'DO not found for do_id ' . $doId, 'id' => 0];
            }

            return ['error' => null, 'id' => $doId];
        }

        $doNo = trim((string) $this->payloadValue($payload, ['do_no', 'do_number'], ''));
        if ($doNo === '') {
            return ['error' => 'do_id or do_no is required', 'id' => 0];
        }

        if (ctype_digit($doNo)) {
            $row = $this->db->table('do_registration')
                ->where('do_registration_id', (int) $doNo)
                ->where('deleted_by', null)
                ->get()
                ->getRow();
            if ($row !== null) {
                return ['error' => null, 'id' => (int) $row->do_registration_id];
            }
        }

        $row = $this->db->table('do_registration')
            ->where('do_no', $doNo)
            ->where('deleted_by', null)
            ->get()
            ->getRow();
        if ($row === null) {
            return ['error' => 'DO not found: ' . $doNo, 'id' => 0];
        }

        return ['error' => null, 'id' => (int) $row->do_registration_id];
    }

    private function fetchDespatchRow(int $id): ?object
    {
        $rows = $this->adminModel->single_despatch_dtls($id);
        if ($rows === []) {
            return null;
        }

        $row = $rows[0];
        if (! empty($row->deleted_by) || ! empty($row->deleted_at)) {
            return null;
        }

        return $row;
    }

    private function formatDespatch(object $row): array
    {
        $desDate = (string) ($row->des_date ?? '');

        return [
            'despatch_id'        => (int) ($row->despatch_id ?? 0),
            'vehicle_id'         => isset($row->vehicle_no) ? (int) $row->vehicle_no : null,
            'vehicle_no'         => $row->vehicle_number ?? null,
            'do_id'              => isset($row->do_no) ? (int) $row->do_no : null,
            'do_no'              => $row->doreg_no ?? null,
            'quantity'           => $row->quantity ?? null,
            'ref_no'             => $row->ref_no ?? null,
            'des_date'           => $desDate !== '' ? $desDate : null,
            'des_date_display'   => $desDate !== '' ? date('d-m-Y', strtotime($desDate)) : null,
            'voucher_id'         => isset($row->voucher_id) ? (int) $row->voucher_id : null,
            'cash'               => isset($row->cash) ? (float) $row->cash : null,
            'bilty_commission'   => isset($row->bilty_commission) ? (float) $row->bilty_commission : null,
        ];
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function logActivity(string $action, int $modelId, array $changes): void
    {
        if (! $this->db->tableExists('activity_logs')) {
            return;
        }

        $userId = \App\Libraries\ApiAuthContext::userId() ?? 0;
        $source = $userId > 0 ? 'despatch_entry_api' : 'public_api';

        $this->db->table('activity_logs')->insert([
            'user_id'    => $userId,
            'menu'       => $userId > 0 ? 'api_despatch_entry' : 'api_despatch_public',
            'action'     => $action,
            'model'      => 'despatch',
            'model_id'   => $modelId,
            'changes'    => json_encode(['data' => $changes, 'source' => $source]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
