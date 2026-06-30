<?php

namespace App\Controllers\Api;

/**
 * In-house maintenance APIs (admin/add_inhouse list, admin/inhouse_maintenance form).
 */
class InhouseMaintenanceController extends BaseApiController
{
    private const USAGE_TYPES = [
        1 => 'Service',
        2 => 'Product',
    ];

    /**
     * GET /api/inhouse-maintenance/form
     * Same dropdown data as web admin/inhouse_maintenance (add form).
     */
    public function form()
    {
        $vehicles = [];
        foreach ($this->adminModel->Getvehicle() as $vehicle) {
            $vehicles[] = [
                'id'         => (int) ($vehicle->id ?? 0),
                'vehicle_id' => (int) ($vehicle->id ?? 0),
                'vehicle_no' => $vehicle->vehicle_no ?? null,
                'label'      => $vehicle->vehicle_no ?? null,
            ];
        }

        $locations = [];
        foreach ($this->adminModel->getActiveLocationList() as $loc) {
            $locations[] = [
                'id'            => (int) ($loc->location_id ?? 0),
                'location_id'   => (int) ($loc->location_id ?? 0),
                'location_name' => $loc->location_name ?? null,
                'label'         => $loc->location_name ?? null,
            ];
        }

        $users = [];
        foreach ($this->adminModel->getInhouseCheckByUsers() as $user) {
            $id   = (int) ($user->id ?? 0);
            $name = trim((string) ($user->full_name ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }

            $value = $id . ' - ' . $name;
            $users[] = [
                'id'        => $id,
                'full_name' => $name,
                'value'     => $value,
                'label'     => $value,
            ];
        }

        $mechanics = $this->formatMechanicList();

        $usageTypes = [];
        foreach (self::USAGE_TYPES as $value => $label) {
            $usageTypes[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $this->apiSuccess('In-house maintenance form loaded.', [
            'defaults' => [
                'date' => date('Y-m-d'),
                'time' => date('H:i'),
            ],
            'vehicles'    => $vehicles,
            'locations'   => $locations,
            'check_by_users' => $users,
            'mechanics'   => $mechanics,
            'usage_types' => $usageTypes,
        ]);
    }

    /**
     * GET /api/inhouse-maintenance/mechanics
     * Same mechanic dropdown as web admin/inhouse_maintenance (staff.user_type = MECHANIC).
     *
     * Optional query: search
     */
    public function mechanics()
    {
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $mechanics = $this->formatMechanicList($search);

        return $this->apiSuccess('Mechanics loaded.', [
            'search'    => $search !== '' ? $search : null,
            'total'     => count($mechanics),
            'mechanics' => $mechanics,
        ]);
    }

    /**
     * GET /api/inhouse-maintenance/items
     * POST /api/inhouse-maintenance/items
     * Same as web admin/get_items_by_location.
     *
     * Required: location_id
     */
    public function itemsByLocation()
    {
        $payload    = $this->mergeRequestPayload();
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);

        if ($locationId <= 0) {
            $locationId = (int) ($this->request->getGet('location_id')
                ?? $this->request->getGet('location')
                ?? 0);
        }

        if ($locationId <= 0) {
            return $this->apiError('03', 'location_id is required.', 400);
        }

        $rows = $this->adminModel->getItemsByLocation($locationId);

        $items = [];
        foreach ($rows as $row) {
            $itemCode = $row->item_id ?? '';
            $itemName = $row->item_name ?? '';
            $label    = $itemCode !== '' ? '[' . $itemCode . '] ' . $itemName : $itemName;

            $items[] = [
                'sproduct_id'   => (int) ($row->sproduct_id ?? 0),
                'item_id'       => $itemCode !== '' ? $itemCode : null,
                'item_name'     => $itemName !== '' ? $itemName : null,
                'label'         => $label !== '' ? $label : null,
                'unit_name'     => $row->unit_name ?? null,
                'unit_price'    => isset($row->rate) ? (float) $row->rate : 0.0,
                'rate'          => isset($row->rate) ? (float) $row->rate : 0.0,
                'available_qty' => isset($row->available_qty) ? (float) $row->available_qty : 0.0,
                'total_stock_qty' => isset($row->total_stock_qty) ? (float) $row->total_stock_qty : 0.0,
                'total_inhouse_qty' => isset($row->total_inhouse_qty) ? (float) $row->total_inhouse_qty : 0.0,
            ];
        }

        return $this->apiSuccess('Items loaded for location.', [
            'location_id' => $locationId,
            'total'       => count($items),
            'items'       => $items,
        ]);
    }

    /**
     * GET /api/inhouse-maintenance/vehicle-driver
     * POST /api/inhouse-maintenance/vehicle-driver
     * Same as web admin/get_vehicle_driver (with driver name for form).
     *
     * Required: vehicle_id
     */
    public function vehicleDriver()
    {
        $payload   = $this->mergeRequestPayload();
        $vehicleId = (int) $this->payloadValue($payload, ['vehicle_id', 'vehicle'], 0);

        if ($vehicleId <= 0) {
            $vehicleId = (int) ($this->request->getGet('vehicle_id')
                ?? $this->request->getGet('vehicle')
                ?? 0);
        }

        if ($vehicleId <= 0) {
            return $this->apiError('03', 'vehicle_id is required.', 400);
        }

        $vehicle = $this->db->table('vehicle')->where('id', $vehicleId)->get()->getRow();
        if ($vehicle === null) {
            return $this->apiError('04', 'Vehicle not found.', 404);
        }

        $assignment = $this->db->table('driver_assignment da')
            ->select('da.id AS assignment_id, da.driver, s.name AS driver_name, s.staff_code')
            ->join('staff s', 's.id = da.driver', 'left')
            ->where('da.vehicle_no', $vehicleId)
            ->where('(da.to_date IS NULL OR da.to_date = "0000-00-00" OR da.to_date >= CURDATE())', null, false)
            ->orderBy('da.id', 'DESC')
            ->get()
            ->getRow();

        if ($assignment === null) {
            return $this->apiError('14', 'No driver assigned to this vehicle.', 404);
        }

        return $this->apiSuccess('Assigned driver loaded.', [
            'vehicle' => [
                'vehicle_id' => $vehicleId,
                'vehicle_no' => $vehicle->vehicle_no ?? null,
            ],
            'driver_id'   => (int) ($assignment->driver ?? 0),
            'driver_name' => $assignment->driver_name ?? null,
            'staff_code'  => $assignment->staff_code ?? null,
        ]);
    }

    /**
     * POST /api/inhouse-maintenance/store
     * Same as web admin/insert_inhouse.
     */
    public function store()
    {
        $payload = $this->mergeRequestPayload();

        $vehicleId = (int) $this->payloadValue($payload, ['vehicle_id', 'vehicle'], 0);
        $driverName = trim((string) $this->payloadValue($payload, ['driver_name', 'driver'], ''));
        $date       = trim((string) $this->payloadValue($payload, ['date'], ''));
        $time       = trim((string) $this->payloadValue($payload, ['time'], ''));
        $remark     = trim((string) $this->payloadValue($payload, ['remark', 'invoiceno'], ''));
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        $checkBy    = trim((string) $this->payloadValue($payload, ['check_by'], ''));

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
        }
        if ($date === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($time === '') {
            $errors[] = 'time is required';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($checkBy === '') {
            $errors[] = 'check_by is required';
        }

        $lines = $this->parseInhouseItemLines($payload);
        if ($lines === []) {
            $errors[] = 'At least one item line is required (items array)';
        }

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $header = [
            'vehicle'     => $vehicleId,
            'driver_name' => $driverName,
            'date'        => $date,
            'time'        => $time,
            'invoiceno'   => $remark,
            'location'    => $locationId,
            'check_by'    => $checkBy,
        ];

        $result = $this->adminModel->storeInhouseMaintenance($header, $lines);
        if ($result === null) {
            return $this->apiError('06', 'Failed to store in-house maintenance.', 500);
        }

        $orderRows = $this->adminModel->inhouse_orderdtls($result['order_id']);
        $records   = [];
        foreach ($orderRows as $row) {
            $records[] = $this->formatInhouseRow($row);
        }

        return $this->apiSuccess('In-house maintenance stored successfully.', [
            'order_id'     => $result['order_id'],
            'inserted_ids' => $result['inserted_ids'],
            'total_lines'  => count($records),
            'records'      => $records,
        ]);
    }

    /**
     * GET /api/inhouse-maintenance/{order_id}
     * Same as web admin/add_inhouse → View (billing_details popup).
     */
    public function show($orderId = null)
    {
        $orderId = $this->resolveOrderId($orderId);
        if ($orderId === '') {
            return $this->apiError('03', 'order_id is required.', 400);
        }

        $rows = $this->getOrderRows($orderId);
        if ($rows === []) {
            return $this->apiError('04', 'In-house maintenance order not found.', 404);
        }

        return $this->apiSuccess('In-house maintenance detail loaded.', $this->buildOrderView($rows));
    }

    /**
     * GET /api/inhouse-maintenance/{order_id}/edit
     * Same as web admin/edit_inhouse/{order_id}.
     */
    public function edit($orderId = null)
    {
        $orderId = $this->resolveOrderId($orderId);
        if ($orderId === '') {
            return $this->apiError('03', 'order_id is required.', 400);
        }

        $rows = $this->getOrderRows($orderId);
        if ($rows === []) {
            return $this->apiError('04', 'In-house maintenance order not found.', 404);
        }

        $header = $rows[0];
        $locationId = (int) ($header->location ?? 0);

        $itemOptions = [];
        foreach ($this->adminModel->getItemsByLocation($locationId) as $row) {
            $itemCode = $row->item_id ?? '';
            $itemName = $row->item_name ?? '';
            $label    = $itemCode !== '' ? '[' . $itemCode . '] ' . $itemName : $itemName;

            $itemOptions[] = [
                'sproduct_id'   => (int) ($row->sproduct_id ?? 0),
                'item_name'     => $itemName !== '' ? $itemName : null,
                'label'         => $label !== '' ? $label : null,
                'unit_price'    => isset($row->rate) ? (float) $row->rate : 0.0,
                'available_qty' => isset($row->available_qty) ? (float) $row->available_qty : 0.0,
            ];
        }

        $lines = [];
        foreach ($rows as $row) {
            $qty   = isset($row->qty) ? (float) $row->qty : 0.0;
            $price = isset($row->price) ? (float) $row->price : 0.0;

            $lines[] = [
                'line_id'          => (int) ($row->id ?? 0),
                'sproduct_id'      => isset($row->item) ? (int) $row->item : null,
                'item_name'        => $row->item_name ?? null,
                'item_code'        => $row->item_id ?? null,
                'usage_type'       => isset($row->itemUseAs) ? (int) $row->itemUseAs : null,
                'usage_type_label' => isset($row->itemUseAs) ? (self::USAGE_TYPES[(int) $row->itemUseAs] ?? null) : null,
                'quantity'         => $qty,
                'unit_price'       => $price,
                'total_amount'     => round($qty * $price, 2),
                'mechanic_name'    => $row->mechanic_name ?? null,
            ];
        }

        return $this->apiSuccess('In-house maintenance edit form loaded.', [
            'form' => [
                'order_id'      => $orderId,
                'vehicle_id'    => isset($header->vehicle) ? (int) $header->vehicle : null,
                'vehicle_no'    => $header->vehicle_no ?? null,
                'driver_name'   => $header->driver_name ?? null,
                'date'          => $header->date ?? null,
                'time'          => $header->time ?? null,
                'remark'        => $header->invoiceno ?? null,
                'invoiceno'     => $header->invoiceno ?? null,
                'location_id'   => $locationId > 0 ? $locationId : null,
                'location_name' => $header->location_name ?? null,
                'check_by'      => $header->check_by ?? null,
                'items'         => $lines,
            ],
            'dropdowns' => array_merge($this->getFormDropdowns(), [
                'location_items' => $itemOptions,
            ]),
        ]);
    }

    /**
     * POST /api/inhouse-maintenance/{order_id}
     * Same as web admin/update_inhouse.
     */
    public function update($orderId = null)
    {
        $orderId = $this->resolveOrderId($orderId);
        if ($orderId === '') {
            return $this->apiError('03', 'order_id is required.', 400);
        }

        if ($this->getOrderRows($orderId) === []) {
            return $this->apiError('04', 'In-house maintenance order not found.', 404);
        }

        $payload = $this->mergeRequestPayload();

        $vehicleId = (int) $this->payloadValue($payload, ['vehicle_id', 'vehicle'], 0);
        $driverName = trim((string) $this->payloadValue($payload, ['driver_name', 'driver'], ''));
        $date       = trim((string) $this->payloadValue($payload, ['date'], ''));
        $time       = trim((string) $this->payloadValue($payload, ['time'], ''));
        $remark     = trim((string) $this->payloadValue($payload, ['remark', 'invoiceno'], ''));
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        $checkBy    = trim((string) $this->payloadValue($payload, ['check_by'], ''));

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'vehicle_id is required';
        }
        if ($date === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($time === '') {
            $errors[] = 'time is required';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($checkBy === '') {
            $errors[] = 'check_by is required';
        }

        $lines = $this->parseInhouseItemLines($payload);
        if ($lines === []) {
            $errors[] = 'At least one item line is required (items array)';
        }

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $header = [
            'vehicle'     => $vehicleId,
            'driver_name' => $driverName,
            'date'        => $date,
            'time'        => $time,
            'invoiceno'   => $remark,
            'location'    => $locationId,
            'check_by'    => $checkBy,
        ];

        $result = $this->adminModel->updateInhouseMaintenance($orderId, $header, $lines);
        if ($result === null) {
            return $this->apiError('06', 'Failed to update in-house maintenance.', 500);
        }

        $rows = $this->getOrderRows($result['order_id']);

        return $this->apiSuccess('In-house maintenance updated successfully.', [
            'old_order_id' => $result['old_order_id'],
            'order_id'     => $result['order_id'],
            'inserted_ids' => $result['inserted_ids'],
            'order'        => $this->buildOrderView($rows),
        ]);
    }

    /**
     * DELETE /api/inhouse-maintenance/{order_id}
     * GET    /api/inhouse-maintenance/delete/{order_id}
     * POST   /api/inhouse-maintenance/{order_id}/delete
     * Same as web admin/delete_inhouse/{order_id}.
     */
    public function destroy($orderId = null)
    {
        $orderId = $this->resolveOrderId($orderId);
        if ($orderId === '') {
            return $this->apiError('03', 'order_id is required.', 400);
        }

        $deletedCount = $this->adminModel->deleteInhouseMaintenanceByOrderId($orderId);
        if ($deletedCount === 0) {
            return $this->apiError('04', 'In-house maintenance order not found.', 404);
        }

        return $this->apiSuccess('In-house maintenance deleted successfully.', [
            'order_id'       => $orderId,
            'deleted_count'  => $deletedCount,
        ]);
    }

    /**
     * GET /api/inhouse-maintenance
     * GET /api/add-inhouse
     * Same list as web admin/add_inhouse (inhouse_vw).
     *
     * Query:
     * - from_date (default: first day of current month)
     * - to_date (default: today)
     * - location_id / location (optional)
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

        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);

        $rows = $this->adminModel->inhouse_dtls(
            $fromDate,
            $toDate,
            $locationId > 0 ? $locationId : null
        );

        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->formatInhouseRow($row);
        }

        return $this->apiSuccess('In-house maintenance list loaded.', [
            'filters' => [
                'from_date'   => $fromDate,
                'to_date'     => $toDate,
                'location_id' => $locationId > 0 ? $locationId : null,
            ],
            'total'   => count($records),
            'records' => $records,
        ]);
    }

    /**
     * @return list<array{id: int, name: string, staff_code: string|null, user_type: string|null, label: string}>
     */
    private function formatMechanicList(string $search = ''): array
    {
        $mechanics = [];

        foreach ($this->adminModel->getInhouseMechanics() as $mechanic) {
            $name = trim((string) ($mechanic->name ?? ''));
            if ($name === '') {
                continue;
            }

            if ($search !== '' && stripos($name, $search) === false && stripos((string) ($mechanic->staff_code ?? ''), $search) === false) {
                continue;
            }

            $mechanics[] = [
                'id'         => (int) ($mechanic->id ?? 0),
                'name'       => $name,
                'staff_code' => $mechanic->staff_code ?? null,
                'user_type'  => $mechanic->user_type ?? 'MECHANIC',
                'label'      => $name,
            ];
        }

        return $mechanics;
    }

    /**
     * @return list<array{item: int, qty: float, price: float, itemUseAs: int, mechanic_name: string|null}>
     */
    private function parseInhouseItemLines(array $payload): array
    {
        $raw = $payload['items'] ?? $payload['item_lines'] ?? null;

        if (is_array($raw) && $raw !== []) {
            $lines = [];
            foreach ($raw as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = (int) ($item['sproduct_id'] ?? $item['item_id'] ?? $item['item'] ?? $item['items'] ?? 0);
                $qty    = (float) ($item['quantity'] ?? $item['qty'] ?? 0);
                if ($itemId <= 0 || $qty <= 0) {
                    continue;
                }

                $price = $item['unit_price'] ?? $item['price'] ?? 0;
                if ((float) $price <= 0) {
                    $stockItem = $this->adminModel->getItemsByLocation((int) ($payload['location_id'] ?? $payload['location'] ?? 0));
                    foreach ($stockItem as $stockRow) {
                        if ((int) ($stockRow->sproduct_id ?? 0) === $itemId) {
                            $price = $stockRow->rate ?? 0;
                            break;
                        }
                    }
                }

                $usageType = (int) ($item['usage_type'] ?? $item['itemUseAs'] ?? $item['item_use_as'] ?? 1);
                if (! isset(self::USAGE_TYPES[$usageType])) {
                    $usageType = 1;
                }

                $lines[] = [
                    'item'          => $itemId,
                    'qty'           => $qty,
                    'price'         => $price,
                    'itemUseAs'     => $usageType,
                    'mechanic_name' => isset($item['mechanic_name']) ? trim((string) $item['mechanic_name']) : null,
                ];
            }

            if ($lines !== []) {
                return $lines;
            }
        }

        $itemIds   = $this->payloadValue($payload, ['items', 'item_ids'], []);
        $qtys      = $this->payloadValue($payload, ['qty', 'quantities'], []);
        $prices    = $this->payloadValue($payload, ['price', 'unit_prices'], []);
        $usageTypes = $this->payloadValue($payload, ['itemUseAs', 'usage_types', 'item_use_as'], []);
        $mechanics = $this->payloadValue($payload, ['mechanic_name', 'mechanic_names'], []);

        if (! is_array($itemIds)) {
            $itemIds = $itemIds === '' || $itemIds === null ? [] : [$itemIds];
        }
        if (! is_array($qtys)) {
            $qtys = $qtys === '' || $qtys === null ? [] : [$qtys];
        }
        if (! is_array($prices)) {
            $prices = $prices === '' || $prices === null ? [] : [$prices];
        }
        if (! is_array($usageTypes)) {
            $usageTypes = $usageTypes === '' || $usageTypes === null ? [] : [$usageTypes];
        }
        if (! is_array($mechanics)) {
            $mechanics = $mechanics === '' || $mechanics === null ? [] : [$mechanics];
        }

        $lines = [];
        $count = max(count($itemIds), count($qtys), count($prices));
        for ($i = 0; $i < $count; $i++) {
            $itemId = (int) ($itemIds[$i] ?? 0);
            $qty    = (float) ($qtys[$i] ?? 0);
            if ($itemId <= 0 || $qty <= 0) {
                continue;
            }

            $usageType = (int) ($usageTypes[$i] ?? 1);
            if (! isset(self::USAGE_TYPES[$usageType])) {
                $usageType = 1;
            }

            $lines[] = [
                'item'          => $itemId,
                'qty'           => $qty,
                'price'         => $prices[$i] ?? 0,
                'itemUseAs'     => $usageType,
                'mechanic_name' => isset($mechanics[$i]) ? trim((string) $mechanics[$i]) : null,
            ];
        }

        return $lines;
    }

    private function formatInhouseRow(object $row): array
    {
        $qty   = isset($row->qty) ? (float) $row->qty : 0.0;
        $price = isset($row->price) ? (float) $row->price : 0.0;

        return [
            'id'            => (int) ($row->id ?? 0),
            'order_id'      => $row->order_id ?? null,
            'vehicle_id'    => isset($row->vehicle_id) ? (int) $row->vehicle_id : (isset($row->vehicle) ? (int) $row->vehicle : null),
            'vehicle_no'    => $row->vehicle_no ?? null,
            'driver_name'   => $row->driver_name ?? null,
            'item_id'       => $row->item_id ?? null,
            'item_db_id'    => isset($row->item) ? (int) $row->item : null,
            'item_name'     => $row->item_name ?? null,
            'date'          => $row->date ?? null,
            'time'          => $row->time ?? null,
            'remark'        => $row->invoiceno ?? null,
            'invoiceno'     => $row->invoiceno ?? null,
            'check_by'      => $row->check_by ?? null,
            'mechanic_name' => $row->mechanic_name ?? null,
            'location_id'   => isset($row->location_id) ? (int) $row->location_id : (isset($row->location) ? (int) $row->location : null),
            'location_name' => $row->location_name ?? null,
            'usage_type'    => isset($row->itemUseAs) ? (int) $row->itemUseAs : null,
            'usage_type_label' => isset($row->itemUseAs) ? (self::USAGE_TYPES[(int) $row->itemUseAs] ?? null) : null,
            'quantity'      => $qty,
            'unit_price'    => $price,
            'total_amount'  => round($qty * $price, 2),
        ];
    }

    private function resolveOrderId($orderId): string
    {
        return trim(urldecode((string) ($orderId ?? '')));
    }

    /**
     * @return list<object>
     */
    private function getOrderRows(string $orderId): array
    {
        return $this->adminModel->inhouse_orderdtls($orderId);
    }

    /**
     * @param list<object> $rows
     *
     * @return array<string, mixed>
     */
    private function buildOrderView(array $rows): array
    {
        $header = $rows[0];
        $items  = [];
        $total  = 0.0;
        $slNo   = 1;

        foreach ($rows as $row) {
            $qty    = isset($row->qty) ? (float) $row->qty : 0.0;
            $price  = isset($row->price) ? (float) $row->price : 0.0;
            $amount = round($qty * $price, 2);
            $total += $amount;

            $items[] = [
                'sl_no'         => $slNo++,
                'line_id'       => (int) ($row->id ?? 0),
                'item_name'     => $row->item_name ?? null,
                'item_code'     => $row->item_id ?? null,
                'quantity'      => $qty,
                'unit_price'    => $price,
                'amount'        => $amount,
                'mechanic_name' => $row->mechanic_name ?? null,
                'usage_type'    => isset($row->itemUseAs) ? (int) $row->itemUseAs : null,
                'usage_type_label' => isset($row->itemUseAs) ? (self::USAGE_TYPES[(int) $row->itemUseAs] ?? null) : null,
            ];
        }

        return [
            'order_id'      => $header->order_id ?? null,
            'vehicle_id'    => isset($header->vehicle) ? (int) $header->vehicle : null,
            'vehicle_no'    => $header->vehicle_no ?? null,
            'driver_name'   => $header->driver_name ?? null,
            'location_id'   => isset($header->location) ? (int) $header->location : null,
            'location_name' => $header->location_name ?? null,
            'date'          => $header->date ?? null,
            'time'          => $header->time ?? null,
            'remark'        => $header->invoiceno ?? null,
            'invoiceno'     => $header->invoiceno ?? null,
            'check_by'      => $header->check_by ?? null,
            'total_amount'  => round($total, 2),
            'total_lines'   => count($items),
            'items'         => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getFormDropdowns(): array
    {
        $vehicles = [];
        foreach ($this->adminModel->Getvehicle() as $vehicle) {
            $vehicles[] = [
                'id'         => (int) ($vehicle->id ?? 0),
                'vehicle_id' => (int) ($vehicle->id ?? 0),
                'vehicle_no' => $vehicle->vehicle_no ?? null,
                'label'      => $vehicle->vehicle_no ?? null,
            ];
        }

        $locations = [];
        foreach ($this->adminModel->getActiveLocationList() as $loc) {
            $locations[] = [
                'id'            => (int) ($loc->location_id ?? 0),
                'location_id'   => (int) ($loc->location_id ?? 0),
                'location_name' => $loc->location_name ?? null,
                'label'         => $loc->location_name ?? null,
            ];
        }

        $users = [];
        foreach ($this->adminModel->getInhouseCheckByUsers() as $user) {
            $id   = (int) ($user->id ?? 0);
            $name = trim((string) ($user->full_name ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }

            $value = $id . ' - ' . $name;
            $users[] = [
                'id'        => $id,
                'full_name' => $name,
                'value'     => $value,
                'label'     => $value,
            ];
        }

        $usageTypes = [];
        foreach (self::USAGE_TYPES as $value => $label) {
            $usageTypes[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return [
            'vehicles'       => $vehicles,
            'locations'      => $locations,
            'check_by_users' => $users,
            'mechanics'      => $this->formatMechanicList(),
            'usage_types'    => $usageTypes,
        ];
    }
}
