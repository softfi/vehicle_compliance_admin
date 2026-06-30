<?php

namespace App\Controllers\Api;

/**
 * Purchase entry APIs (admin/Purchaseentry).
 */
class PurchaseEntryController extends BaseApiController
{
    /**
     * GET /api/purchase-entry
     * Same list as web admin/Purchase_Voucher (Allstock_vw).
     *
     * Optional query:
     * - location_id, supplier_id
     * - from_date, to_date (YYYY-MM-DD)
     * - search (invoice no, supplier, location, stock_code)
     */
    public function index()
    {
        $fromDate = trim((string) ($this->request->getGet('from_date') ?? ''));
        $toDate   = trim((string) ($this->request->getGet('to_date') ?? ''));

        $errors = [];
        if ($fromDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $errors[] = 'from_date must be YYYY-MM-DD';
        }
        if ($toDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $errors[] = 'to_date must be YYYY-MM-DD';
        }
        if ($errors === [] && $fromDate !== '' && $toDate !== '' && strtotime($fromDate) > strtotime($toDate)) {
            $errors[] = 'from_date cannot be after to_date';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);
        $supplierId = (int) ($this->request->getGet('supplier_id')
            ?? $this->request->getGet('supplier')
            ?? 0);
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $rows = $this->adminModel->getPurchaseVoucherList([
            'location_id' => $locationId,
            'supplier_id' => $supplierId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'search'      => $search,
        ]);

        $purchases = [];
        foreach ($rows as $row) {
            $purchases[] = $this->formatPurchaseSummary($row);
        }

        return $this->apiSuccess('Purchase vouchers loaded.', [
            'filters' => [
                'location_id' => $locationId > 0 ? $locationId : null,
                'supplier_id' => $supplierId > 0 ? $supplierId : null,
                'from_date'   => $fromDate !== '' ? $fromDate : null,
                'to_date'     => $toDate !== '' ? $toDate : null,
                'search'      => $search !== '' ? $search : null,
            ],
            'total'     => count($purchases),
            'purchases' => $purchases,
        ]);
    }

    /**
     * GET /api/purchase-entry/{stock_code}
     * Same detail as web Purchase_Voucher view modal (singleStock).
     */
    public function show($stockCode = null)
    {
        $stockCode = (int) ($stockCode ?? 0);
        if ($stockCode <= 0) {
            return $this->apiError('03', 'Valid stock_code is required.', 400);
        }

        $headerRows = $this->adminModel->getPurchaseVoucherList(['stock_code' => $stockCode]);
        $header     = $headerRows[0] ?? null;

        if ($header === null) {
            return $this->apiError('04', 'Purchase voucher not found.', 404);
        }

        $lineRows = $this->adminModel->singleStock($stockCode);
        if ($lineRows === []) {
            return $this->apiError('04', 'Purchase voucher not found.', 404);
        }

        $items       = $this->formatPurchaseLineItems($lineRows);
        $totalAmount = array_sum(array_column($items, 'amount'));

        $billPhoto = $this->resolvePurchaseBillPhoto($header, $lineRows);
        $summary = $this->formatPurchaseSummary($header, $billPhoto);
        $summary['total_amount']      = $totalAmount;
        $summary['total_gst_amount']  = (float) ($header->total_gst_amount ?? 0);
        $summary['calculated_total']  = $totalAmount;
        $summary['items']             = $items;
        $summary['download_url']      = base_url('admin/downloadStock/' . $stockCode);

        return $this->apiSuccess('Purchase voucher loaded.', [
            'purchase' => $summary,
        ]);
    }

    /**
     * DELETE /api/purchase-entry/{stock_code}
     * Same as web Admin::delete_stock() — deletes all rows with stock_code.
     * Web permission role: 1.3
     */
    public function destroy($stockCode = null)
    {
        if (! $this->hasRole('1.3')) {
            return $this->apiError('07', 'You do not have permission to delete purchase.', 403);
        }

        $stockCode = (int) ($stockCode ?? 0);
        if ($stockCode <= 0) {
            return $this->apiError('03', 'Valid stock_code is required.', 400);
        }

        $headerRows = $this->adminModel->getPurchaseVoucherList(['stock_code' => $stockCode]);
        $header     = $headerRows[0] ?? null;
        if ($header === null) {
            return $this->apiError('04', 'Purchase voucher not found.', 404);
        }

        $lineRows = $this->adminModel->singleStock($stockCode);
        $items    = $this->formatPurchaseLineItems($lineRows);

        $snapshot = $this->formatPurchaseSummary($header);
        $snapshot['items']             = $items;
        $snapshot['calculated_total']  = array_sum(array_column($items, 'amount'));

        $deletedRows = $this->adminModel->deletePurchaseByStockCode($stockCode);
        if ($deletedRows <= 0) {
            return $this->apiError('06', 'Failed to delete purchase voucher.', 500);
        }

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_purchase_entry',
                'action'     => 'delete',
                'model'      => 'stock',
                'model_id'   => $stockCode,
                'changes'    => json_encode(['deleted' => $snapshot, 'source' => 'purchase_entry_api']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->apiSuccess('Purchase voucher deleted successfully.', [
            'stock_code'   => $stockCode,
            'deleted_rows' => $deletedRows,
            'deleted'      => $snapshot,
        ]);
    }

    /**
     * GET /api/purchase-entry/suppliers
     * Same supplier dropdown as web purchase_voucher_new:
     * all vendors from Get_vendor() where type != Pump.
     *
     * Optional: ?search=name&location_id=1
     */
    public function suppliers()
    {
        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);
        $search = trim($this->request->getGet('search') ?? '');

        $user       = $this->authUser();
        $userLocId  = $this->resolveLocationIdForUser($user);
        if ($locationId <= 0 && $userLocId) {
            $locationId = $userLocId;
        }

        $suppliers = [];
        foreach ($this->adminModel->Get_vendor() as $vendor) {
            if (strcasecmp((string) ($vendor->type ?? ''), 'Pump') === 0) {
                continue;
            }

            if ($locationId > 0 && (int) ($vendor->location ?? 0) !== $locationId) {
                continue;
            }

            $name = trim((string) ($vendor->name ?? ''));
            if ($search !== '' && stripos($name, $search) === false) {
                continue;
            }

            $suppliers[] = [
                'id'            => (int) $vendor->id,
                'name'          => $name,
                'label'         => $name,
                'type'          => $vendor->type ?? null,
                'vendor_code'   => $vendor->vendor_code ?? null,
                'gst'           => $vendor->gst ?? null,
                'pan'           => $vendor->pan ?? null,
                'location_id'   => $vendor->location ? (int) $vendor->location : null,
                'location_name' => $vendor->location_name ?? null,
                'vendor_rate'   => $vendor->vendor_rate ?? null,
                'rate_from_date'=> $vendor->from_date ?? null,
                'opening_balance' => isset($vendor->bal) ? (float) $vendor->bal : null,
            ];
        }

        return $this->apiSuccess('Active suppliers loaded.', [
            'location_id' => $locationId > 0 ? $locationId : null,
            'total'       => count($suppliers),
            'suppliers'   => $suppliers,
        ]);
    }

    /**
     * GET /api/purchase-entry/items?location_id=1
     * POST body also supported: { "location_id": 1 }
     * Same as web Admin/getItemsDetails (purchase_voucher_new location change).
     *
     * Optional: ?only_available=1  (same as getItemsDetails1)
     * Optional: ?search=tyre
     */
    public function items()
    {
        $payload    = $this->mergeRequestPayload();
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        $onlyAvailable = filter_var(
            $this->request->getGet('only_available') ?? $payload['only_available'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        $search = trim((string) ($this->request->getGet('search') ?? $payload['search'] ?? ''));

        if ($locationId <= 0) {
            return $this->apiError('03', 'location_id is required.', 400);
        }

        $location = $this->db->table('location')
            ->where('location_id', $locationId)
            ->get()
            ->getRow();

        if ($location === null) {
            return $this->apiError('04', 'Location not found.', 404);
        }

        $rows  = $this->adminModel->getPurchaseItemsByLocation($locationId, $onlyAvailable);
        $items = [];

        foreach ($rows as $row) {
            $itemId   = trim((string) ($row->item_id ?? ''));
            $itemName = trim((string) ($row->item_name ?? ''));
            $amount   = isset($row->amount) ? (float) $row->amount : 0.0;
            $availableQty = isset($row->available_qty) ? (float) $row->available_qty : 0.0;

            if ($search !== '') {
                $haystack = strtolower($itemId . ' ' . $itemName);
                if (strpos($haystack, strtolower($search)) === false) {
                    continue;
                }
            }

            $label = $itemId . ' - ' . $itemName
                . ' (₹' . number_format($amount, 2, '.', '') . ')'
                . ' | Avl: ' . $availableQty;

            $items[] = [
                'id'             => (int) $row->id,
                'item_id'        => $itemId,
                'item_name'      => $itemName,
                'amount'         => $amount,
                'rate'           => $amount,
                'available_qty'  => $availableQty,
                'unit_id'        => $row->unit_id ? (int) $row->unit_id : null,
                'unit_name'      => $row->unit_name ?? null,
                'label'          => $label,
            ];
        }

        return $this->apiSuccess('Purchase items loaded.', [
            'location_id'   => $locationId,
            'location_name' => $location->location_name ?? null,
            'only_available'=> $onlyAvailable,
            'total'         => count($items),
            'items'         => $items,
        ]);
    }

    /**
     * POST /api/purchase-entry/store
     * Same final save as web Admin/Inserpurchasetstock (without session cart).
     *
     * JSON body:
     * {
     *   "supplier_id": 5,
     *   "location_id": 1,
     *   "invoice_date": "2026-06-08",
     *   "invoice_no": "INV-001",
     *   "remarks": "optional",
     *   "items": [
     *     { "product_id": 10, "qty": 5, "rate": 1500 }
     *   ]
     * }
     *
     * Multipart: bill_photo (optional image file) + same fields as form.
     */
    public function store()
    {
        $payload = $this->mergeRequestPayload();

        $supplierId  = (int) $this->payloadValue($payload, ['supplier_id', 'supplierId'], 0);
        $locationId  = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        $invoiceDate = trim((string) $this->payloadValue($payload, ['invoice_date', 'invoicedate', 'date'], ''));
        $invoiceNo   = trim((string) $this->payloadValue($payload, ['invoice_no', 'invoiceno'], ''));
        $remarks     = trim((string) $this->payloadValue($payload, ['remarks'], ''));

        $errors = [];
        if ($supplierId <= 0) {
            $errors[] = 'supplier_id is required';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($invoiceDate === '') {
            $errors[] = 'invoice_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $invoiceDate)) {
            $errors[] = 'invoice_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $lineItems = $this->normalizePurchaseItems($payload);
        if ($lineItems === []) {
            return $this->apiError('03', 'items is required (at least one line item).', 400);
        }

        $supplier = $this->db->table('vendor')->where('id', $supplierId)->get()->getRow();
        if ($supplier === null) {
            return $this->apiError('04', 'Supplier not found.', 404);
        }
        if (strcasecmp((string) ($supplier->type ?? ''), 'Pump') === 0) {
            return $this->apiError('05', 'Pump vendor cannot be used for purchase entry.', 409);
        }

        $location = $this->db->table('location')->where('location_id', $locationId)->get()->getRow();
        if ($location === null) {
            return $this->apiError('04', 'Location not found.', 404);
        }

        $seenProducts = [];
        $resolvedLines = [];
        foreach ($lineItems as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $qty       = (float) ($line['qty'] ?? 0);
            $rate      = isset($line['rate']) ? (float) $line['rate'] : null;

            if ($productId <= 0) {
                return $this->apiError('03', 'Each item must have product_id.', 400);
            }
            if (isset($seenProducts[$productId])) {
                return $this->apiError('05', 'Duplicate product_id in items: ' . $productId, 409);
            }
            $seenProducts[$productId] = true;

            if ($qty <= 0) {
                return $this->apiError('03', 'Each item qty must be greater than 0.', 400);
            }

            $product = $this->db->table('items')->where('id', $productId)->get()->getRow();
            if ($product === null) {
                return $this->apiError('04', 'Product not found: ' . $productId, 404);
            }

            if ($rate === null) {
                $rate = (float) ($product->amount ?? 0);
            }
            if ($rate < 0) {
                return $this->apiError('03', 'Item rate cannot be negative.', 400);
            }

            $resolvedLines[] = [
                'product_id' => $productId,
                'qty'        => $qty,
                'rate'       => $rate,
                'item_name'  => $product->item_name ?? null,
            ];
        }

        $billPhoto = $this->saveBillPhoto();

        $this->db->transStart();

        $result = $this->adminModel->storePurchaseStock([
            'supplier_id'  => $supplierId,
            'location_id'  => $locationId,
            'invoice_date' => $invoiceDate,
            'invoice_no'   => $invoiceNo,
            'remarks'      => $remarks,
        ], $resolvedLines, $billPhoto);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to store purchase.', 500);
        }

        return $this->apiSuccess('Purchase stored successfully.', [
            'stock_code'    => $result['stock_code'],
            'supplier_id'   => $supplierId,
            'supplier_name' => $supplier->name ?? null,
            'location_id'   => $locationId,
            'location_name' => $location->location_name ?? null,
            'invoice_date'  => $invoiceDate,
            'invoice_no'    => $invoiceNo !== '' ? $invoiceNo : null,
            'remarks'       => $remarks !== '' ? $remarks : null,
            'bill_photo'    => $billPhoto,
            'total_amount'  => $result['total_amount'],
            'items'         => $result['items'],
        ], 201);
    }

    /**
     * POST /api/purchase-entry/{stock_code}
     * POST /api/purchase-entry/{stock_code}/update
     * Same as web Admin/edit_stock + finalize_edit_stock (without session cart).
     * Web permission role: 1.4
     *
     * JSON body:
     * {
     *   "supplier_id": 5,
     *   "location_id": 1,
     *   "invoice_date": "2026-06-08",
     *   "invoice_no": "INV-001",
     *   "remarks": "optional",
     *   "items": [
     *     { "stock_id": 101, "product_id": 10, "qty": 5, "rate": 1500 },
     *     { "product_id": 12, "qty": 2, "rate": 800 }
     *   ]
     * }
     *
     * - stock_id: existing line (update). Omit for new lines.
     * - Lines not sent in items are removed (only if nothing issued from stock).
     *
     * Multipart: bill_photo (optional) + same fields.
     */
    public function update($stockCode = null)
    {
        if (! $this->hasRole('1.4')) {
            return $this->apiError('07', 'You do not have permission to edit purchase.', 403);
        }

        $stockCode = (int) ($stockCode ?? 0);
        if ($stockCode <= 0) {
            return $this->apiError('03', 'Valid stock_code is required.', 400);
        }

        $headerRows = $this->adminModel->getPurchaseVoucherList(['stock_code' => $stockCode]);
        $header     = $headerRows[0] ?? null;
        if ($header === null) {
            return $this->apiError('04', 'Purchase voucher not found.', 404);
        }

        $invoiceNo = trim((string) ($header->invoice_number ?? ''));
        if (str_starts_with($invoiceNo, 'stock-trans')) {
            return $this->apiError('05', 'Stock transfer batches cannot be edited.', 409);
        }

        $payload = $this->mergeRequestPayload();

        $supplierId  = (int) $this->payloadValue($payload, ['supplier_id', 'supplierId'], (int) ($header->supplier_id ?? 0));
        $locationId  = (int) $this->payloadValue($payload, ['location_id', 'location'], (int) ($header->location_id ?? 0));
        $invoiceDate = trim((string) $this->payloadValue($payload, ['invoice_date', 'invoicedate', 'date'], (string) ($header->date ?? '')));
        $invoiceNoNew = trim((string) $this->payloadValue($payload, ['invoice_no', 'invoiceno'], $invoiceNo));
        $remarks     = trim((string) $this->payloadValue($payload, ['remarks'], (string) ($header->remarks ?? '')));

        $errors = [];
        if ($supplierId <= 0) {
            $errors[] = 'supplier_id is required';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($invoiceDate === '') {
            $errors[] = 'invoice_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $invoiceDate)) {
            $errors[] = 'invoice_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $lineItems = $this->normalizePurchaseItems($payload);
        if ($lineItems === []) {
            return $this->apiError('03', 'items is required (at least one line item).', 400);
        }

        $supplier = $this->db->table('vendor')->where('id', $supplierId)->get()->getRow();
        if ($supplier === null) {
            return $this->apiError('04', 'Supplier not found.', 404);
        }
        if (strcasecmp((string) ($supplier->type ?? ''), 'Pump') === 0) {
            return $this->apiError('05', 'Pump vendor cannot be used for purchase entry.', 409);
        }

        $location = $this->db->table('location')->where('location_id', $locationId)->get()->getRow();
        if ($location === null) {
            return $this->apiError('04', 'Location not found.', 404);
        }

        $seenProducts = [];
        $resolvedLines = [];
        foreach ($lineItems as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $qty       = (float) ($line['qty'] ?? 0);
            $rate      = isset($line['rate']) ? (float) $line['rate'] : null;
            $stockId   = (int) ($line['stock_id'] ?? 0);

            if ($productId <= 0) {
                return $this->apiError('03', 'Each item must have product_id.', 400);
            }
            if ($qty <= 0) {
                return $this->apiError('03', 'Each item qty must be greater than 0.', 400);
            }

            $productKey = $stockId > 0 ? 's' . $stockId : 'p' . $productId;
            if (isset($seenProducts[$productKey])) {
                return $this->apiError('05', 'Duplicate line item in request.', 409);
            }
            $seenProducts[$productKey] = true;

            $product = $this->db->table('items')->where('id', $productId)->get()->getRow();
            if ($product === null) {
                return $this->apiError('04', 'Product not found: ' . $productId, 404);
            }

            if ($rate === null) {
                $rate = (float) ($product->amount ?? 0);
            }
            if ($rate < 0) {
                return $this->apiError('03', 'Item rate cannot be negative.', 400);
            }

            $resolved = [
                'product_id' => $productId,
                'qty'        => $qty,
                'rate'       => $rate,
                'item_name'  => $product->item_name ?? null,
            ];
            if ($stockId > 0) {
                $resolved['stock_id'] = $stockId;
            }
            $resolvedLines[] = $resolved;
        }

        $billPhoto = $this->saveBillPhoto();

        $this->db->transStart();

        try {
            $result = $this->adminModel->updatePurchaseStock($stockCode, [
                'supplier_id'  => $supplierId,
                'location_id'  => $locationId,
                'invoice_date' => $invoiceDate,
                'invoice_no'   => $invoiceNoNew,
                'remarks'      => $remarks,
            ], $resolvedLines, $billPhoto);
        } catch (\InvalidArgumentException $e) {
            $this->db->transRollback();

            return $this->apiError('03', $e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            $this->db->transRollback();

            return $this->apiError('05', $e->getMessage(), 409);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->apiError('06', 'Failed to update purchase.', 500);
        }

        if ($this->db->tableExists('activity_logs')) {
            $this->db->table('activity_logs')->insert([
                'user_id'    => $this->authUserId(),
                'menu'       => 'api_purchase_entry',
                'action'     => 'update',
                'model'      => 'stock',
                'model_id'   => $stockCode,
                'changes'    => json_encode([
                    'header' => [
                        'supplier_id'  => $supplierId,
                        'location_id'  => $locationId,
                        'invoice_date' => $invoiceDate,
                        'invoice_no'   => $invoiceNoNew,
                        'remarks'      => $remarks,
                    ],
                    'items'  => $resolvedLines,
                    'source' => 'purchase_entry_api',
                ]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->show($stockCode);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<array<string, mixed>>
     */
    private function normalizePurchaseItems(array $payload): array
    {
        $items = $payload['items'] ?? $payload['line_items'] ?? $payload['products'] ?? [];
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items   = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? $item['productId'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $normalized[] = [
                'stock_id'   => (int) ($item['stock_id'] ?? $item['stockId'] ?? 0) ?: null,
                'product_id' => $productId,
                'qty'        => $item['qty'] ?? $item['quantity'] ?? 0,
                'rate'       => $item['rate'] ?? $item['amount'] ?? null,
            ];
        }

        return $normalized;
    }

    private function formatPurchaseSummary(object $row, ?string $billPhotoOverride = null): array
    {
        $invoiceNo = trim((string) ($row->invoice_number ?? ''));
        $date      = (string) ($row->date ?? '');
        $billPhoto = $billPhotoOverride !== null
            ? trim($billPhotoOverride)
            : trim((string) ($row->bill_photo ?? ''));
        $billPhotoUrl = $this->buildPurchaseBillUrl($billPhoto);

        return [
            'stock_code'        => (int) ($row->stock_code ?? 0),
            'date'              => $date !== '' ? $date : null,
            'date_display'      => $date !== '' ? date('d-m-Y', strtotime($date)) : null,
            'invoice_no'        => $invoiceNo,
            'invoice_number'    => $invoiceNo,
            'supplier_id'       => isset($row->supplier_id) ? (int) $row->supplier_id : null,
            'supplier_name'     => $row->supplier_name ?? null,
            'total_quantity'    => isset($row->total_quantity) ? (float) $row->total_quantity : 0.0,
            'total_amount'      => isset($row->total_gst_amount) ? (float) $row->total_gst_amount : 0.0,
            'total_gst_amount'  => isset($row->total_gst_amount) ? (float) $row->total_gst_amount : 0.0,
            'location_id'       => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name'     => $row->location_name ?? null,
            'remarks'           => trim((string) ($row->remarks ?? '')) ?: null,
            'bill_photo'        => $billPhoto !== '' ? $billPhoto : null,
            'bill_photo_url'    => $billPhotoUrl,
            'has_bill'          => $billPhotoUrl !== null,
            'is_stock_transfer' => str_starts_with($invoiceNo, 'stock-trans'),
            'can_edit'          => ! str_starts_with($invoiceNo, 'stock-trans'),
        ];
    }

    /**
     * @param list<object> $lineRows
     */
    private function resolvePurchaseBillPhoto(?object $header, array $lineRows = []): string
    {
        $billPhoto = trim((string) ($header->bill_photo ?? ''));
        if ($billPhoto !== '') {
            return $billPhoto;
        }

        foreach ($lineRows as $row) {
            $candidate = trim((string) ($row->bill_photo ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    private function buildPurchaseBillUrl(string $billPhoto): ?string
    {
        $billPhoto = trim($billPhoto);
        if ($billPhoto === '') {
            return null;
        }

        return base_url('public/uploads/purchase_bills/' . $billPhoto);
    }

    /**
     * @param list<object> $rows
     *
     * @return list<array<string, mixed>>
     */
    private function formatPurchaseLineItems(array $rows): array
    {
        $items = [];
        $slNo  = 1;
        foreach ($rows as $row) {
            $qty    = isset($row->quantity) ? (float) $row->quantity : 0.0;
            $rate   = isset($row->rate) ? (float) $row->rate : 0.0;
            $amount = $qty * $rate;

            $items[] = [
                'sl_no'         => $slNo++,
                'stock_id'      => isset($row->stock_id) ? (int) $row->stock_id : null,
                'product_id'    => isset($row->sproduct_id) ? (int) $row->sproduct_id : null,
                'item_id'       => $row->item_id ?? null,
                'item_name'     => $row->item_name ?? null,
                'product_label' => trim(($row->item_name ?? '') . ' (' . ($row->item_id ?? '') . ')'),
                'supplier_id'   => isset($row->supplier_id) ? (int) $row->supplier_id : null,
                'supplier_name' => $row->name ?? null,
                'quantity'      => $qty,
                'unit_name'     => $row->unit_name ?? null,
                'quantity_label'=> trim($qty . ' ' . ($row->unit_name ?? '')),
                'rate'          => $rate,
                'amount'        => $amount,
            ];
        }

        return $items;
    }

    private function saveBillPhoto(): ?string
    {
        $file = $this->request->getFile('bill_photo');
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadDir = FCPATH . 'public/uploads/purchase_bills';
        if (! is_dir($uploadDir) && ! @mkdir($uploadDir, 0777, true) && ! is_dir($uploadDir)) {
            throw new \RuntimeException('Unable to create purchase bill upload directory.');
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return $newName;
    }

}
