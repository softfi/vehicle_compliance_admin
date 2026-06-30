<?php

namespace App\Controllers\Api;

/**
 * Tyre assignment positions (admin/Asign_Tyer).
 * Returns all 18 fixed positions per vehicle with assigned serial or empty flag.
 */
class TyreAssignmentController extends BaseApiController
{
    private const ALL_POSITIONS = [
        'Front Right',
        'Front Left',
        'Rear1 Right',
        'Rear1 Left',
        'Rear2 Right',
        'Rear2 Left',
        'Rear3 Right',
        'Rear3 Left',
        'Rear4 Right',
        'Rear4 Left',
        'Rear5 Right',
        'Rear5 Left',
        'Rear6 Right',
        'Rear6 Left',
        'Rear7 Right',
        'Rear7 Left',
        'Rear8 Right',
        'Rear8 Left',
    ];

    /**
     * GET /api/tyre-assignment
     * Optional: ?vehicle_id=5  OR  ?search=OD16
     */
    public function index()
    {
        $vehicleId = (int) ($this->request->getGet('vehicle_id') ?? 0);
        $search    = trim($this->request->getGet('search') ?? '');

        if ($vehicleId > 0) {
            $vehicle = $this->getVehicleRow($vehicleId);
            if ($vehicle === null) {
                return $this->apiError('04', 'Vehicle not found.', 404);
            }

            $assigned = $this->getAssignedTyresForVehicle($vehicleId);

            return $this->apiSuccess('Tyre positions loaded.', $this->formatVehiclePositions($vehicle, $assigned));
        }

        $vehicles    = $this->getVehicles($search);
        $assignedMap = $this->getAssignedTyresMap();

        $list = [];
        foreach ($vehicles as $vehicle) {
            $vid      = (int) $vehicle->id;
            $assigned = $assignedMap[$vid] ?? [];
            $list[]   = $this->formatVehiclePositions($vehicle, $assigned);
        }

        return $this->apiSuccess('Tyre assignments loaded.', [
            'total'    => count($list),
            'vehicles' => $list,
        ]);
    }

    /**
     * GET /api/tyre-assignment/{vehicle_id}
     */
    public function show($vehicleId)
    {
        $vehicleId = (int) $vehicleId;
        if ($vehicleId <= 0) {
            return $this->apiError('03', 'Invalid vehicle id.', 400);
        }

        $vehicle = $this->getVehicleRow($vehicleId);
        if ($vehicle === null) {
            return $this->apiError('04', 'Vehicle not found.', 404);
        }

        $assigned = $this->getAssignedTyresForVehicle($vehicleId);

        return $this->apiSuccess('Tyre positions loaded.', $this->formatVehiclePositions($vehicle, $assigned));
    }

    /**
     * GET /api/tyre-assignment/stock-tyres?location_id=1
     * Same stock list as web Admin/gettyer (status=1, not on any vehicle).
     */
    public function stockTyres()
    {
        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);

        if ($locationId <= 0) {
            return $this->apiError('03', 'location_id is required.', 400);
        }

        $rows = $this->db->table('tyer_management tm')
            ->select('tm.id, tm.tyer_sl_no, tm.tyer_type, tm.brand_name, tm.model, tm.location_id, l.location_name, tm.status')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.location_id', $locationId)
            ->where('tm.status', 1)
            ->where('tm.vehicle_id IS NULL', null, false)
            ->orderBy('tm.tyer_sl_no', 'ASC')
            ->get()
            ->getResult();

        $tyres = [];
        foreach ($rows as $row) {
            $serial = trim((string) ($row->tyer_sl_no ?? ''));
            $tyres[] = [
                'tyre_id'       => (int) $row->id,
                'tyre_serial'   => $serial,
                'label'         => $serial,
                'tyre_type'     => $row->tyer_type ?? null,
                'brand_name'    => $row->brand_name ?? null,
                'model'         => $row->model ?? null,
                'location_id'   => $row->location_id ? (int) $row->location_id : null,
                'location_name' => $row->location_name ?? null,
                'status'        => (int) ($row->status ?? 1),
            ];
        }

        return $this->apiSuccess('Stock tyres loaded.', [
            'location_id' => $locationId,
            'total'       => count($tyres),
            'tyres'       => $tyres,
        ]);
    }

    /**
     * POST /api/tyre-assignment/assign
     * Same as web Admin/update_tyer_data — assign stock tyre to empty vehicle position.
     *
     * Body: vehicle_id, tyre_id, position_name (or tyer_position),
     *       assign_date (or asign_date / assigned_date) — required,
     *       location_id (or location)
     */
    public function assign()
    {
        $payload = $this->mergeRequestPayload();

        $vehicleId    = (int) $this->payloadValue($payload, ['vehicle_id'], 0);
        $tyreId       = (int) $this->payloadValue($payload, ['tyre_id', 'tyer_id'], 0);
        $positionName = trim((string) $this->payloadValue($payload, ['position_name', 'tyer_position'], ''));
        $assignDate   = trim((string) $this->payloadValue($payload, ['assign_date', 'asign_date', 'assigned_date'], ''));
        $locationId   = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
        }
        if ($tyreId <= 0) {
            $errors[] = 'tyre_id is required';
        }
        if ($positionName === '') {
            $errors[] = 'position_name is required';
        } elseif (! in_array($positionName, self::ALL_POSITIONS, true)) {
            $errors[] = 'position_name is invalid';
        }
        if ($assignDate === '') {
            $errors[] = 'assign_date is required (use assign_date, asign_date, or assigned_date)';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $assignDate)) {
            $errors[] = 'assign_date must be YYYY-MM-DD';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vehicle = $this->getVehicleRow($vehicleId);
        if ($vehicle === null) {
            return $this->apiError('04', 'Vehicle not found.', 404);
        }

        $assigned = $this->getAssignedTyresForVehicle($vehicleId);
        if (isset($assigned[$positionName])) {
            return $this->apiError('05', 'This position is already occupied. Use replace API instead.', 409);
        }

        $tyre = $this->db->table('tyer_management')
            ->where('id', $tyreId)
            ->get()
            ->getRow();

        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        if ((int) ($tyre->status ?? 0) !== 1) {
            return $this->apiError('05', 'Tyre is not in stock (status must be 1).', 409);
        }

        if ($tyre->vehicle_id !== null && (int) $tyre->vehicle_id > 0) {
            return $this->apiError('05', 'Tyre is already assigned to another vehicle.', 409);
        }

        if ((int) ($tyre->location_id ?? 0) !== $locationId) {
            return $this->apiError('05', 'Tyre does not belong to the selected location.', 409);
        }

        $updateData = [
            'vehicle_id'    => $vehicleId,
            'tyer_position' => $positionName,
            'asign_date'    => $assignDate,
            'status'        => 2,
        ];

        $historyData = [
            'tyre_id'       => $tyreId,
            'event_type'    => 3,
            'location_id'   => $locationId,
            'event_date'    => $assignDate,
            'vehicle_id'    => $vehicleId,
            'tyre_position' => $positionName,
        ];

        $this->db->transStart();

        $this->db->table('tyer_management')->update($updateData, ['id' => $tyreId]);
        $this->db->table('tyer_management_history')->insert($historyData);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to assign tyre.', 500);
        }

        $assigned = $this->getAssignedTyresForVehicle($vehicleId);

        return $this->apiSuccess('Tyre assigned successfully.', [
            'assignment' => [
                'vehicle_id'      => $vehicleId,
                'vehicle_no'      => $vehicle->vehicle_no ?? null,
                'tyre_id'         => $tyreId,
                'tyre_serial'     => $tyre->tyer_sl_no ?? null,
                'position_name'   => $positionName,
                'assign_date'     => $assignDate,
                'location_id'     => $locationId,
            ],
            'vehicle' => $this->formatVehiclePositions($vehicle, $assigned),
        ], 201);
    }

    /**
     * POST /api/tyre-assignment/replace
     * Same as web Admin/exchange_tyer_data — replace occupied position with stock tyre.
     *
     * Body: vehicle_id, tyre_id (new stock tyre), position_name (or tyer_position),
     *       replacement_date (or replace_date / assign_date / asign_date) — required,
     *       location_id (or location) — old tyre returns to this location stock
     */
    public function replace()
    {
        $payload = $this->mergeRequestPayload();

        $vehicleId        = (int) $this->payloadValue($payload, ['vehicle_id'], 0);
        $newTyreId        = (int) $this->payloadValue($payload, ['tyre_id', 'tyer_id', 'new_tyre_id'], 0);
        $positionName     = trim((string) $this->payloadValue($payload, ['position_name', 'tyer_position'], ''));
        $replacementDate  = trim((string) $this->payloadValue($payload, [
            'replacement_date',
            'replace_date',
            'assign_date',
            'asign_date',
            'assigned_date',
        ], ''));
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
        }
        if ($newTyreId <= 0) {
            $errors[] = 'tyre_id is required (new stock tyre)';
        }
        if ($positionName === '') {
            $errors[] = 'position_name is required';
        } elseif (! in_array($positionName, self::ALL_POSITIONS, true)) {
            $errors[] = 'position_name is invalid';
        }
        if ($replacementDate === '') {
            $errors[] = 'replacement_date is required (use replacement_date, replace_date, assign_date, or asign_date)';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $replacementDate)) {
            $errors[] = 'replacement_date must be YYYY-MM-DD';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vehicle = $this->getVehicleRow($vehicleId);
        if ($vehicle === null) {
            return $this->apiError('04', 'Vehicle not found.', 404);
        }

        $oldTyre = $this->db->table('tyer_management')
            ->where('vehicle_id', $vehicleId)
            ->where('tyer_position', $positionName)
            ->get()
            ->getRow();

        if ($oldTyre === null) {
            return $this->apiError('05', 'No tyre found at this position. Use assign API for empty positions.', 409);
        }

        if ((int) $oldTyre->id === $newTyreId) {
            return $this->apiError('05', 'New tyre cannot be the same as the current tyre.', 409);
        }

        $newTyre = $this->db->table('tyer_management')
            ->where('id', $newTyreId)
            ->get()
            ->getRow();

        if ($newTyre === null) {
            return $this->apiError('04', 'New tyre not found.', 404);
        }

        if ((int) ($newTyre->status ?? 0) !== 1) {
            return $this->apiError('05', 'New tyre is not in stock (status must be 1).', 409);
        }

        if ($newTyre->vehicle_id !== null && (int) $newTyre->vehicle_id > 0) {
            return $this->apiError('05', 'New tyre is already assigned to another vehicle.', 409);
        }

        if ((int) ($newTyre->location_id ?? 0) !== $locationId) {
            return $this->apiError('05', 'New tyre does not belong to the selected location.', 409);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('tyer_management')->update([
            'vehicle_id'    => null,
            'tyer_position' => null,
            'location_id'   => $locationId,
            'status'        => 1,
        ], ['id' => (int) $oldTyre->id]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'       => (int) $oldTyre->id,
            'event_type'    => 4,
            'location_id'   => $locationId,
            'event_date'    => $replacementDate,
            'vehicle_id'    => $vehicleId,
            'tyre_position' => $positionName,
            'created_at'    => $now,
        ]);

        $this->db->table('tyer_management')->update([
            'vehicle_id'    => $vehicleId,
            'tyer_position' => $positionName,
            'status'        => 2,
            'asign_date'    => $replacementDate,
        ], ['id' => $newTyreId]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'       => $newTyreId,
            'event_type'    => 3,
            'event_date'    => $replacementDate,
            'vehicle_id'    => $vehicleId,
            'tyre_position' => $positionName,
            'created_at'    => $now,
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to replace tyre.', 500);
        }

        $this->adminModel->recordTyreExchange(
            $vehicleId,
            (int) $oldTyre->id,
            $newTyreId,
            $positionName,
            $replacementDate,
            'Tyre replaced on vehicle from stock'
        );

        $assigned = $this->getAssignedTyresForVehicle($vehicleId);

        return $this->apiSuccess('Tyre replaced successfully.', [
            'replacement' => [
                'vehicle_id'        => $vehicleId,
                'vehicle_no'        => $vehicle->vehicle_no ?? null,
                'position_name'     => $positionName,
                'replacement_date'  => $replacementDate,
                'location_id'       => $locationId,
                'old_tyre'          => [
                    'tyre_id'     => (int) $oldTyre->id,
                    'tyre_serial' => $oldTyre->tyer_sl_no ?? null,
                    'moved_to'    => 'stock',
                ],
                'new_tyre' => [
                    'tyre_id'     => $newTyreId,
                    'tyre_serial' => $newTyre->tyer_sl_no ?? null,
                ],
            ],
            'vehicle' => $this->formatVehiclePositions($vehicle, $assigned),
        ]);
    }

    /**
     * POST /api/tyre-assignment/back-to-stock
     * Same as web Admin/backToStock_tyer_data — remove tyre from vehicle to location stock.
     *
     * Body: vehicle_id, position_name (or tyer_position),
     *       replacement_date (or remove_date / back_to_stock_date) — required,
     *       location_id (or location) — tyre returns to this location stock
     */
    public function backToStock()
    {
        $payload = $this->mergeRequestPayload();

        $vehicleId   = (int) $this->payloadValue($payload, ['vehicle_id'], 0);
        $positionName = trim((string) $this->payloadValue($payload, ['position_name', 'tyer_position'], ''));
        $eventDate   = trim((string) $this->payloadValue($payload, [
            'replacement_date',
            'remove_date',
            'back_to_stock_date',
            'assign_date',
            'asign_date',
        ], ''));
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
        }
        if ($positionName === '') {
            $errors[] = 'position_name is required';
        } elseif (! in_array($positionName, self::ALL_POSITIONS, true)) {
            $errors[] = 'position_name is invalid';
        }
        if ($eventDate === '') {
            $errors[] = 'replacement_date is required (use replacement_date, remove_date, or back_to_stock_date)';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            $errors[] = 'replacement_date must be YYYY-MM-DD';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vehicle = $this->getVehicleRow($vehicleId);
        if ($vehicle === null) {
            return $this->apiError('04', 'Vehicle not found.', 404);
        }

        $oldTyre = $this->db->table('tyer_management')
            ->where('vehicle_id', $vehicleId)
            ->where('tyer_position', $positionName)
            ->get()
            ->getRow();

        if ($oldTyre === null) {
            return $this->apiError('05', 'No tyre found at this position.', 404);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('tyer_management')->update([
            'vehicle_id'    => null,
            'tyer_position' => null,
            'location_id'   => $locationId,
            'status'        => 1,
        ], ['id' => (int) $oldTyre->id]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'       => (int) $oldTyre->id,
            'event_type'    => 6,
            'location_id'   => $locationId,
            'event_date'    => $eventDate,
            'vehicle_id'    => $vehicleId,
            'tyre_position' => $positionName,
            'created_at'    => $now,
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to move tyre back to stock.', 500);
        }

        $assigned = $this->getAssignedTyresForVehicle($vehicleId);

        return $this->apiSuccess('Tyre moved back to stock successfully.', [
            'back_to_stock' => [
                'vehicle_id'         => $vehicleId,
                'vehicle_no'         => $vehicle->vehicle_no ?? null,
                'position_name'      => $positionName,
                'replacement_date'   => $eventDate,
                'location_id'        => $locationId,
                'tyre_id'            => (int) $oldTyre->id,
                'tyre_serial'        => $oldTyre->tyer_sl_no ?? null,
            ],
            'vehicle' => $this->formatVehiclePositions($vehicle, $assigned),
        ]);
    }

    /**
     * POST /api/tyre-assignment/rotate
     * Same as web Admin/rotate_tyre_data — swap two tyres on the same vehicle.
     *
     * Body: vehicle_id, position_name (target / clicked position),
     *       source_tyre_id (or source_tyer_id) OR source_position_name,
     *       replacement_date (or rotation_date) — required
     */
    public function rotate()
    {
        $payload = $this->mergeRequestPayload();

        $vehicleId      = (int) $this->payloadValue($payload, ['vehicle_id'], 0);
        $targetPosition = trim((string) $this->payloadValue($payload, ['position_name', 'tyer_position', 'target_position'], ''));
        $sourceTyreId   = (int) $this->payloadValue($payload, ['source_tyre_id', 'source_tyer_id'], 0);
        $sourcePosition = trim((string) $this->payloadValue($payload, ['source_position_name', 'source_position', 'source_tyer_position'], ''));
        $rotationDate   = trim((string) $this->payloadValue($payload, [
            'replacement_date',
            'rotation_date',
            'rotate_date',
            'assign_date',
            'asign_date',
        ], ''));

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
        }
        if ($targetPosition === '') {
            $errors[] = 'position_name is required (target position to rotate)';
        } elseif (! in_array($targetPosition, self::ALL_POSITIONS, true)) {
            $errors[] = 'position_name is invalid';
        }
        if ($sourceTyreId <= 0 && $sourcePosition === '') {
            $errors[] = 'source_tyre_id or source_position_name is required';
        } elseif ($sourcePosition !== '' && ! in_array($sourcePosition, self::ALL_POSITIONS, true)) {
            $errors[] = 'source_position_name is invalid';
        }
        if ($rotationDate === '') {
            $errors[] = 'replacement_date is required (use replacement_date or rotation_date)';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rotationDate)) {
            $errors[] = 'replacement_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vehicle = $this->getVehicleRow($vehicleId);
        if ($vehicle === null) {
            return $this->apiError('04', 'Vehicle not found.', 404);
        }

        $targetTyre = $this->db->table('tyer_management')
            ->where('vehicle_id', $vehicleId)
            ->where('tyer_position', $targetPosition)
            ->get()
            ->getRow();

        if ($targetTyre === null) {
            return $this->apiError('05', 'No tyre found at target position.', 404);
        }

        if ($sourceTyreId <= 0) {
            $sourceTyre = $this->db->table('tyer_management')
                ->where('vehicle_id', $vehicleId)
                ->where('tyer_position', $sourcePosition)
                ->get()
                ->getRow();

            if ($sourceTyre === null) {
                return $this->apiError('05', 'No tyre found at source position.', 404);
            }

            $sourceTyreId = (int) $sourceTyre->id;
        } else {
            $sourceTyre = $this->db->table('tyer_management')
                ->where('id', $sourceTyreId)
                ->get()
                ->getRow();
        }

        if ($sourceTyre === null) {
            return $this->apiError('04', 'Source tyre not found.', 404);
        }

        if ((int) ($sourceTyre->vehicle_id ?? 0) !== $vehicleId) {
            return $this->apiError('05', 'Source tyre does not belong to this vehicle.', 409);
        }

        $sourcePosition = trim((string) ($sourceTyre->tyer_position ?? ''));

        if ($sourcePosition === '' || $sourcePosition === $targetPosition) {
            return $this->apiError('05', 'Source and target positions must be different occupied positions.', 409);
        }

        if ((int) $sourceTyre->id === (int) $targetTyre->id) {
            return $this->apiError('05', 'Cannot rotate a tyre with itself.', 409);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('tyer_management')->update([
            'tyer_position' => $sourcePosition,
            'asign_date'    => $rotationDate,
        ], ['id' => (int) $targetTyre->id]);

        $this->db->table('tyer_management')->update([
            'tyer_position' => $targetPosition,
            'asign_date'    => $rotationDate,
        ], ['id' => $sourceTyreId]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'       => (int) $targetTyre->id,
            'event_type'    => 8,
            'event_date'    => $rotationDate,
            'vehicle_id'    => $vehicleId,
            'tyre_position' => $sourcePosition,
            'remarks'       => "Rotated from {$targetPosition} to {$sourcePosition}",
            'created_at'    => $now,
        ]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'       => $sourceTyreId,
            'event_type'    => 8,
            'event_date'    => $rotationDate,
            'vehicle_id'    => $vehicleId,
            'tyre_position' => $targetPosition,
            'remarks'       => "Rotated from {$sourcePosition} to {$targetPosition}",
            'created_at'    => $now,
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to rotate tyres.', 500);
        }

        $this->adminModel->recordTyreExchange(
            $vehicleId,
            (int) $targetTyre->id,
            $sourceTyreId,
            $targetPosition,
            $rotationDate,
            "Internal rotation: {$sourcePosition} <-> {$targetPosition}"
        );

        $assigned = $this->getAssignedTyresForVehicle($vehicleId);

        return $this->apiSuccess('Tyres rotated successfully.', [
            'rotation' => [
                'vehicle_id'       => $vehicleId,
                'vehicle_no'       => $vehicle->vehicle_no ?? null,
                'replacement_date' => $rotationDate,
                'target_position'  => $targetPosition,
                'source_position'  => $sourcePosition,
                'target_tyre'      => [
                    'tyre_id'          => (int) $targetTyre->id,
                    'tyre_serial'      => $targetTyre->tyer_sl_no ?? null,
                    'moved_to_position' => $sourcePosition,
                ],
                'source_tyre' => [
                    'tyre_id'          => $sourceTyreId,
                    'tyre_serial'      => $sourceTyre->tyer_sl_no ?? null,
                    'moved_to_position' => $targetPosition,
                ],
            ],
            'vehicle' => $this->formatVehiclePositions($vehicle, $assigned),
        ]);
    }

    /**
     * @return list<object>
     */
    private function getVehicles(string $search): array
    {
        $builder = $this->db->table('vehicle v');
        $builder->select('v.id, v.vehicle_no, v.location_id, l.location_name');
        $builder->join('location l', 'l.location_id = v.location_id', 'left');

        if ($search !== '') {
            $builder->like('v.vehicle_no', $search);
        }

        $builder->orderBy('v.id', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * @return object|null
     */
    private function getVehicleRow(int $vehicleId)
    {
        return $this->db->table('vehicle v')
            ->select('v.id, v.vehicle_no, v.location_id, l.location_name')
            ->join('location l', 'l.location_id = v.location_id', 'left')
            ->where('v.id', $vehicleId)
            ->get()
            ->getRow();
    }

    /**
     * @return array<string, object>
     */
    private function getAssignedTyresForVehicle(int $vehicleId): array
    {
        $rows = $this->db->table('tyer_management tm')
            ->select('tm.id, tm.tyer_sl_no, tm.tyer_position, tm.asign_date, tm.tyer_type, tm.brand_name, tm.status, tm.location_id, l.location_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.vehicle_id', $vehicleId)
            ->where('tm.tyer_position IS NOT NULL', null, false)
            ->where('tm.tyer_position !=', '')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $position = trim((string) ($row->tyer_position ?? ''));
            if ($position !== '') {
                $map[$position] = $row;
            }
        }

        return $map;
    }

    /**
     * @return array<int, array<string, object>>
     */
    private function getAssignedTyresMap(): array
    {
        $rows = $this->db->table('tyer_management tm')
            ->select('tm.id, tm.vehicle_id, tm.tyer_sl_no, tm.tyer_position, tm.asign_date, tm.tyer_type, tm.brand_name, tm.status, tm.location_id, l.location_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.vehicle_id IS NOT NULL', null, false)
            ->where('tm.tyer_position IS NOT NULL', null, false)
            ->where('tm.tyer_position !=', '')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $vehicleId = (int) ($row->vehicle_id ?? 0);
            $position  = trim((string) ($row->tyer_position ?? ''));
            if ($vehicleId <= 0 || $position === '') {
                continue;
            }
            if (! isset($map[$vehicleId])) {
                $map[$vehicleId] = [];
            }
            $map[$vehicleId][$position] = $row;
        }

        return $map;
    }

    /**
     * @param array<string, object> $assignedByPosition
     *
     * @return array<string, mixed>
     */
    private function formatVehiclePositions(object $vehicle, array $assignedByPosition): array
    {
        $positions      = [];
        $emptyPositions = [];

        foreach (self::ALL_POSITIONS as $positionName) {
            $assigned = $assignedByPosition[$positionName] ?? null;
            $isEmpty  = $assigned === null;

            if ($isEmpty) {
                $emptyPositions[] = $positionName;
                $positions[]      = [
                    'position_name' => $positionName,
                    'is_empty'      => true,
                    'tyre_id'       => null,
                    'tyre_serial'   => null,
                    'assign_date'   => null,
                    'tyre_type'     => null,
                    'brand_name'    => null,
                    'status'        => null,
                    'location_id'   => null,
                    'location_name' => null,
                ];
                continue;
            }

            $positions[] = [
                'position_name' => $positionName,
                'is_empty'      => false,
                'tyre_id'       => (int) $assigned->id,
                'tyre_serial'   => $assigned->tyer_sl_no ?? null,
                'assign_date'   => $assigned->asign_date ?? null,
                'tyre_type'     => $assigned->tyer_type ?? null,
                'brand_name'    => $assigned->brand_name ?? null,
                'status'        => $assigned->status !== null ? (int) $assigned->status : null,
                'location_id'   => $assigned->location_id ? (int) $assigned->location_id : null,
                'location_name' => $assigned->location_name ?? null,
            ];
        }

        $assignedCount = count(self::ALL_POSITIONS) - count($emptyPositions);

        return [
            'vehicle_id'      => (int) $vehicle->id,
            'vehicle_no'      => $vehicle->vehicle_no ?? null,
            'location_id'     => $vehicle->location_id ? (int) $vehicle->location_id : null,
            'location_name'   => $vehicle->location_name ?? null,
            'total_positions' => count(self::ALL_POSITIONS),
            'assigned_count'  => $assignedCount,
            'empty_count'     => count($emptyPositions),
            'empty_positions' => $emptyPositions,
            'positions'       => $positions,
        ];
    }
}
