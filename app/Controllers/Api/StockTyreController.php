<?php

namespace App\Controllers\Api;

/**
 * Stock tyre APIs (admin/StockTyer_management).
 */
class StockTyreController extends BaseApiController
{
    private const EVENT_TYPE_LABELS = [
        1  => 'Purchased',
        2  => 'Transferred',
        3  => 'Assigned',
        4  => 'Exchanged',
        5  => 'Sent for Repair',
        6  => 'Back to Stock',
        7  => 'Sold',
        8  => 'Rotation',
        9  => 'Moved to Scrap',
        10 => 'Exchange Requested',
        11 => 'Exchange Completed',
    ];

    private const STATUS_LABELS = [
        1  => 'In Stock',
        2  => 'Assigned',
        3  => 'Scrap Yard',
        4  => 'Under Repair',
        7  => 'Sold',
        10 => 'Exchange Requested',
        11 => 'Exchange Completed',
    ];

    /**
     * GET /api/stock-tyre/brands
     * GET /api/tyre-brands
     * Distinct tyre brands from tyer_management + default edit-form brands.
     *
     * Optional query: search
     */
    public function brands()
    {
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $rows   = $this->adminModel->getDistinctTyreBrands();

        $brandMap = [];
        foreach ($this->getTyreBrandOptions() as $option) {
            $brand = trim((string) ($option['value'] ?? ''));
            if ($brand !== '') {
                $brandMap[$brand] = [
                    'value' => $brand,
                    'label' => $brand,
                ];
            }
        }

        foreach ($rows as $row) {
            $brand = trim((string) ($row->brand_name ?? ''));
            if ($brand === '') {
                continue;
            }

            if (! isset($brandMap[$brand])) {
                $brandMap[$brand] = [
                    'value' => $brand,
                    'label' => $brand,
                ];
            }
        }

        ksort($brandMap, SORT_NATURAL | SORT_FLAG_CASE);

        $brands = [];
        foreach ($brandMap as $brand) {
            if ($search !== '' && stripos($brand['value'], $search) === false) {
                continue;
            }

            $brands[] = $brand;
        }

        return $this->apiSuccess('Tyre brands loaded.', [
            'total'  => count($brands),
            'search' => $search !== '' ? $search : null,
            'brands' => $brands,
        ]);
    }

    /**
     * GET /api/stock-tyre/vendors
     * Same vendor dropdown as web admin/tyer_exchange/{id} (AdminModel::Get_vendor).
     *
     * Optional query: search, location_id
     */
    public function vendors()
    {
        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $vendors = [];
        foreach ($this->adminModel->Get_vendor() as $vendor) {
            $name = trim((string) ($vendor->name ?? ''));
            if ($name === '') {
                continue;
            }

            if ($locationId > 0 && (int) ($vendor->location ?? 0) !== $locationId) {
                continue;
            }

            if ($search !== '' && stripos($name, $search) === false) {
                continue;
            }

            $vendors[] = [
                'id'              => (int) $vendor->id,
                'name'            => $name,
                'label'           => $name,
                'type'            => $vendor->type ?? null,
                'vendor_code'     => $vendor->vendor_code ?? null,
                'location_id'     => $vendor->location ? (int) $vendor->location : null,
                'location_name'   => $vendor->location_name ?? null,
                'vendor_rate'     => $vendor->vendor_rate ?? null,
                'rate_from_date'  => $vendor->from_date ?? null,
            ];
        }

        return $this->apiSuccess('Vendors loaded.', [
            'total'       => count($vendors),
            'search'      => $search !== '' ? $search : null,
            'location_id' => $locationId > 0 ? $locationId : null,
            'vendors'     => $vendors,
        ]);
    }

    /**
     * GET /api/tyre-purchase
     * GET /api/tyre-management
     * Same grouped bill list as web admin/tyer_management.
     *
     * Optional query:
     * - search (bill, vendor, brand, model, location)
     * - location_id, vendor_id, bill_no
     * - from_date, to_date (purchase date YYYY-MM-DD)
     */
    public function purchaseBills()
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
        $vendorId = (int) ($this->request->getGet('vendor_id') ?? 0);
        $billNo   = trim((string) ($this->request->getGet('bill_no') ?? ''));
        $search   = trim((string) ($this->request->getGet('search') ?? ''));

        $filters = [
            'location_id' => $locationId,
            'vendor_id'   => $vendorId,
            'bill_no'     => $billNo,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'search'      => $search,
        ];

        $rows = $this->adminModel->getTyrePurchaseBillList($filters);

        $bills = [];
        foreach ($rows as $row) {
            $bills[] = $this->formatPurchaseBillRow($row);
        }

        return $this->apiSuccess('Tyre purchase bills loaded.', [
            'filters' => [
                'search'      => $search !== '' ? $search : null,
                'location_id' => $locationId > 0 ? $locationId : null,
                'vendor_id'   => $vendorId > 0 ? $vendorId : null,
                'bill_no'     => $billNo !== '' ? $billNo : null,
                'from_date'   => $fromDate !== '' ? $fromDate : null,
                'to_date'     => $toDate !== '' ? $toDate : null,
            ],
            'total' => count($bills),
            'bills' => $bills,
        ]);
    }

    /**
     * GET /api/tyre-purchase/form
     * Same dropdown data as web admin/addtyerbill (new purchase form).
     */
    public function purchaseBillCreateForm()
    {
        return $this->apiSuccess('Tyre purchase form loaded.', [
            'defaults' => [
                'purchase_date' => date('Y-m-d'),
            ],
            'dropdowns' => $this->getPurchaseBillDropdowns(),
        ]);
    }

    /**
     * POST /api/tyre-purchase/store
     * Same as web admin/insert_tyer (addtyerbill form submit).
     *
     * Body:
     * - bill_no / billno
     * - vendor_id
     * - date / purchase_date (YYYY-MM-DD)
     * - price / tamount
     * - location_id / location
     * - brand_name, model
     * - tyres: [{tyre_serial, tyre_type}]  OR  tyer_sl_no[], tyer_type[]
     */
    public function purchaseBillStore()
    {
        $payload = $this->mergeRequestPayload();

        $billNo = trim((string) $this->payloadValue($payload, ['bill_no', 'billno'], ''));
        $vendorId = (int) $this->payloadValue($payload, ['vendor_id', 'vendor'], 0);
        $date = trim((string) $this->payloadValue($payload, ['date', 'purchase_date'], ''));
        $price = $this->payloadValue($payload, ['price', 'tamount', 'total_amount'], 0);
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        $brandName = trim((string) $this->payloadValue($payload, ['brand_name'], ''));
        $model = trim((string) $this->payloadValue($payload, ['model'], ''));

        $errors = [];
        if ($billNo === '') {
            $errors[] = 'bill_no is required';
        }
        if ($vendorId <= 0) {
            $errors[] = 'vendor_id is required';
        }
        if ($date === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($brandName === '') {
            $errors[] = 'brand_name is required';
        }
        if ($model === '') {
            $errors[] = 'model is required';
        }

        $tyreLines = $this->parsePurchaseBillTyreLines($payload, true);
        if ($tyreLines === []) {
            $errors[] = 'At least one tyre row is required (tyres array or tyer_sl_no[])';
        }

        if ($billNo !== '') {
            $existingBill = $this->db->table('tyer_management')
                ->where('bill_no', $billNo)
                ->countAllResults();
            if ($existingBill > 0) {
                $errors[] = 'Bill number already exists: ' . $billNo;
            }
        }

        $serialsSeen = [];
        foreach ($tyreLines as $line) {
            $serial = $line['tyer_sl_no'];
            if (isset($serialsSeen[$serial])) {
                $errors[] = "Duplicate tyre serial in request: {$serial}";
                continue;
            }
            $serialsSeen[$serial] = true;

            if ($this->db->table('tyer_management')->where('tyer_sl_no', $serial)->countAllResults() > 0) {
                $errors[] = "Tyre serial already exists: {$serial}";
            }
        }

        if ($errors !== []) {
            $httpStatus = str_contains(implode('; ', $errors), 'already exists') ? 409 : 400;

            return $this->apiError('03', implode('; ', $errors), $httpStatus);
        }

        $header = [
            'bill_no'     => $billNo,
            'vendor_id'   => $vendorId,
            'date'        => $date,
            'price'       => $price,
            'location_id' => $locationId,
            'brand_name'  => $brandName,
            'model'       => $model,
        ];

        $storeLines = [];
        foreach ($tyreLines as $line) {
            $storeLines[] = [
                'tyer_sl_no' => $line['tyer_sl_no'],
                'tyer_type'  => $line['tyer_type'],
            ];
        }

        $result = $this->adminModel->storeTyrePurchaseBill($header, $storeLines);
        if ($result === null) {
            return $this->apiError('06', 'Failed to store tyre purchase bill.', 500);
        }

        $detail = $this->adminModel->getTyrePurchaseBillDetail(0, $billNo);
        $billResponse = null;
        if ($detail !== null) {
            $headerRow = $detail['header'];
            $items = [];
            foreach ($detail['tyres'] as $row) {
                $items[] = $this->formatPurchaseBillTyreItem($row);
            }

            $billResponse = [
                'tyre_id'       => (int) ($headerRow->id ?? 0),
                'bill_no'       => $headerRow->bill_no ?? null,
                'purchase_date' => $headerRow->date ?? null,
                'vendor_id'     => isset($headerRow->vendor_id) ? (int) $headerRow->vendor_id : null,
                'vendor_name'   => $headerRow->vendor_name ?? null,
                'brand_name'    => $headerRow->brand_name ?? null,
                'model'         => $headerRow->model ?? null,
                'price'         => isset($headerRow->price) ? (float) $headerRow->price : null,
                'location_id'   => isset($headerRow->location_id) ? (int) $headerRow->location_id : null,
                'location_name' => $headerRow->location_name ?? null,
                'quantity'      => count($items),
                'tyres'         => $items,
            ];
        }

        return $this->apiSuccess('Tyre purchase bill stored successfully.', [
            'bill_no'        => $result['bill_no'],
            'inserted_count' => count($result['inserted_ids']),
            'inserted_ids'   => $result['inserted_ids'],
            'bill'           => $billResponse,
        ]);
    }

    /**
     * GET /api/tyre-purchase/bill-serials
     * Same as web admin/tyer_management → getTyerDetailsByBillNo (view serials popup).
     *
     * Required query: bill_no
     * Optional: location_id
     */
    public function purchaseBillSerials()
    {
        $billNo = trim((string) ($this->request->getGet('bill_no') ?? ''));
        if ($billNo === '') {
            return $this->apiError('03', 'bill_no is required.', 400);
        }

        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);

        $rows = $this->adminModel->getTyreSerialsByBillNo($billNo, $locationId);

        $serials = [];
        foreach ($rows as $row) {
            $serials[] = [
                'tyre_id'     => (int) ($row->id ?? 0),
                'tyre_serial' => $row->tyer_sl_no ?? null,
                'tyre_type'   => $row->tyer_type ?? null,
                'brand_name'  => $row->brand_name ?? null,
                'model'       => $row->model ?? null,
                'bill_no'     => $row->bill_no ?? null,
                'purchase_date' => $row->date ?? null,
                'price'       => isset($row->price) ? (float) $row->price : null,
                'location_id' => isset($row->location_id) ? (int) $row->location_id : null,
            ];
        }

        return $this->apiSuccess('Tyre serials loaded.', [
            'filters' => [
                'bill_no'     => $billNo,
                'location_id' => $locationId > 0 ? $locationId : null,
            ],
            'total'   => count($serials),
            'serials' => $serials,
        ]);
    }

    /**
     * GET /api/tyre-purchase/{id}
     * GET /api/tyre-purchase/bill-detail?bill_no=...&location_id=...
     * Full bill detail like web admin/edit_tyer/{id} (header + all tyre serials).
     */
    public function purchaseBillShow($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        $billNo = trim((string) ($this->request->getGet('bill_no') ?? ''));
        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);

        if ($tyreId <= 0 && $billNo === '') {
            return $this->apiError('03', 'tyre_id or bill_no is required.', 400);
        }

        $detail = $this->adminModel->getTyrePurchaseBillDetail($tyreId, $billNo, $locationId);
        if ($detail === null) {
            return $this->apiError('04', 'Bill not found.', 404);
        }

        $header = $detail['header'];
        $tyres  = $detail['tyres'];

        $items = [];
        foreach ($tyres as $row) {
            $items[] = $this->formatPurchaseBillTyreItem($row);
        }

        return $this->apiSuccess('Tyre purchase bill loaded.', [
            'bill' => [
                'tyre_id'       => (int) ($header->id ?? 0),
                'bill_no'       => $header->bill_no ?? null,
                'purchase_date' => $header->date ?? null,
                'vendor_id'     => isset($header->vendor_id) ? (int) $header->vendor_id : null,
                'vendor_name'   => $header->vendor_name ?? null,
                'brand_name'    => $header->brand_name ?? null,
                'model'         => $header->model ?? null,
                'price'         => isset($header->price) ? (float) $header->price : null,
                'location_id'   => isset($header->location_id) ? (int) $header->location_id : null,
                'location_name' => $header->location_name ?? null,
                'quantity'      => count($items),
                'tyres'         => $items,
            ],
        ]);
    }

    /**
     * GET /api/tyre-purchase/{id}/edit
     * Same form data as web admin/edit_tyer/{id} (bill fields + tyre lines + dropdowns).
     */
    public function purchaseBillEdit($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $detail = $this->adminModel->getTyrePurchaseBillDetail($tyreId);
        if ($detail === null) {
            return $this->apiError('04', 'Bill not found.', 404);
        }

        $header = $detail['header'];

        $tyreLines = [];
        foreach ($detail['tyres'] as $row) {
            $tyreLines[] = [
                'tyre_id'     => (int) ($row->id ?? 0),
                'tyre_serial' => $row->tyer_sl_no ?? null,
                'tyre_type'   => $row->tyer_type ?? null,
            ];
        }

        return $this->apiSuccess('Tyre edit form loaded.', [
            'form' => [
                'tyre_id'       => (int) ($header->id ?? 0),
                'vendor_id'     => isset($header->vendor_id) ? (int) $header->vendor_id : null,
                'vendor_name'   => $header->vendor_name ?? null,
                'purchase_date' => $header->date ?? null,
                'bill_no'       => $header->bill_no ?? null,
                'price'         => isset($header->price) ? (float) $header->price : null,
                'brand_name'    => $header->brand_name ?? null,
                'model'         => $header->model ?? null,
                'location_id'   => isset($header->location_id) ? (int) $header->location_id : null,
                'location_name' => $header->location_name ?? null,
                'tyres'         => $tyreLines,
            ],
            'dropdowns' => $this->getPurchaseBillDropdowns(),
        ]);
    }

    /**
     * POST /api/tyre-purchase/{id}/update
     * Same as web admin/update_tyer (edit_tyer form save).
     *
     * Body:
     * - bill_no / billno
     * - vendor_id
     * - date / purchase_date (YYYY-MM-DD)
     * - price / tamount
     * - location_id / location
     * - brand_name, model
     * - tyres: [{tyre_id, tyre_serial, tyre_type}]  OR  tyer_id[], tyer_sl_no[], tyer_type[]
     */
    public function purchaseBillUpdate($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $seed = $this->adminModel->getTyreById($tyreId);
        if ($seed === null) {
            return $this->apiError('04', 'Bill not found.', 404);
        }

        $payload = $this->mergeRequestPayload();

        $billNo = trim((string) $this->payloadValue($payload, ['bill_no', 'billno'], (string) ($seed->bill_no ?? '')));
        $vendorId = (int) $this->payloadValue($payload, ['vendor_id', 'vendor'], (int) ($seed->vendor_id ?? 0));
        $date = trim((string) $this->payloadValue($payload, ['date', 'purchase_date'], (string) ($seed->date ?? '')));
        $price = $this->payloadValue($payload, ['price', 'tamount', 'total_amount'], $seed->price ?? 0);
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], (int) ($seed->location_id ?? 0));
        $brandName = trim((string) $this->payloadValue($payload, ['brand_name'], (string) ($seed->brand_name ?? '')));
        $model = trim((string) $this->payloadValue($payload, ['model'], (string) ($seed->model ?? '')));

        $errors = [];
        if ($billNo === '') {
            $errors[] = 'bill_no is required';
        }
        if ($vendorId <= 0) {
            $errors[] = 'vendor_id is required';
        }
        if ($date === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }
        if ($locationId <= 0) {
            $errors[] = 'location_id is required';
        }
        if ($brandName === '') {
            $errors[] = 'brand_name is required';
        }
        if ($model === '') {
            $errors[] = 'model is required';
        }

        $tyreLines = $this->parsePurchaseBillTyreLines($payload);
        if ($tyreLines === []) {
            $errors[] = 'At least one tyre row is required (tyres array or tyer_sl_no[])';
        }

        $serialsSeen = [];
        foreach ($tyreLines as $line) {
            $serial = $line['tyer_sl_no'];
            if (isset($serialsSeen[$serial])) {
                $errors[] = "Duplicate tyre serial in request: {$serial}";
                continue;
            }
            $serialsSeen[$serial] = true;

            $duplicate = $this->db->table('tyer_management')
                ->where('tyer_sl_no', $serial);
            if ($line['tyre_id'] > 0) {
                $duplicate->where('id !=', $line['tyre_id']);
            }
            if ($duplicate->countAllResults() > 0) {
                $errors[] = "Tyre serial already exists: {$serial}";
            }
        }

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $header = [
            'bill_no'     => $billNo,
            'vendor_id'   => $vendorId,
            'date'        => $date,
            'price'       => $price,
            'location_id' => $locationId,
            'brand_name'  => $brandName,
            'model'       => $model,
        ];

        $result = $this->adminModel->updateTyrePurchaseBill($header, $tyreLines);
        if ($result === null) {
            return $this->apiError('06', 'Failed to update tyre purchase bill.', 500);
        }

        $detail = $this->adminModel->getTyrePurchaseBillDetail(0, $billNo);
        $billResponse = null;
        if ($detail !== null) {
            $headerRow = $detail['header'];
            $items = [];
            foreach ($detail['tyres'] as $row) {
                $items[] = $this->formatPurchaseBillTyreItem($row);
            }

            $billResponse = [
                'tyre_id'       => (int) ($headerRow->id ?? 0),
                'bill_no'       => $headerRow->bill_no ?? null,
                'purchase_date' => $headerRow->date ?? null,
                'vendor_id'     => isset($headerRow->vendor_id) ? (int) $headerRow->vendor_id : null,
                'vendor_name'   => $headerRow->vendor_name ?? null,
                'brand_name'    => $headerRow->brand_name ?? null,
                'model'         => $headerRow->model ?? null,
                'price'         => isset($headerRow->price) ? (float) $headerRow->price : null,
                'location_id'   => isset($headerRow->location_id) ? (int) $headerRow->location_id : null,
                'location_name' => $headerRow->location_name ?? null,
                'quantity'      => count($items),
                'tyres'         => $items,
            ];
        }

        return $this->apiSuccess('Tyre purchase bill updated successfully.', [
            'bill_no'        => $result['bill_no'],
            'inserted_count' => count($result['inserted']),
            'updated_count'  => count($result['updated']),
            'inserted_ids'   => $result['inserted'],
            'updated_ids'    => $result['updated'],
            'bill'           => $billResponse,
        ]);
    }

    /**
     * DELETE /api/tyre-purchase/{id}
     * POST   /api/tyre-purchase/{id}/delete
     * GET    /api/tyre-purchase/delete/{id}   (same as web admin/delete_tyer/{id} link)
     *
     * List row का tyre_id pass करें (GET /api/tyre-purchase से).
     * Web जैसा: id → bill_no resolve → उस bill_no की सभी tyres delete.
     */
    public function purchaseBillDestroy($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $result = $this->adminModel->deleteTyrePurchaseBillByTyreId($tyreId);
        if ($result === null) {
            return $this->apiError('04', 'Bill not found.', 404);
        }

        return $this->apiSuccess('Tyre purchase bill deleted successfully.', $result);
    }

    /**
     * DELETE /api/tyre-purchase/tyre/{id}
     * Delete single tyre line. Same as admin/delete_tyersingle/{id}.
     */
    public function purchaseTyreDestroy($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        if (! $this->adminModel->deleteTyrePurchaseSingle($tyreId)) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        return $this->apiSuccess('Tyre deleted successfully.', [
            'tyre_id' => $tyreId,
        ]);
    }

    /**
     * GET /api/tyre-transfer/form
     * Same dropdown data as web admin/tyreTransfer page.
     */
    public function transferForm()
    {
        $locations = [];
        foreach ($this->adminModel->getActiveLocationList() as $row) {
            $locations[] = [
                'id'            => (int) ($row->location_id ?? 0),
                'location_id'   => (int) ($row->location_id ?? 0),
                'location_name' => $row->location_name ?? null,
                'label'         => $row->location_name ?? null,
            ];
        }

        return $this->apiSuccess('Tyre transfer form loaded.', [
            'locations' => $locations,
        ]);
    }

    /**
     * GET /api/tyre-transfer/tyres
     * POST /api/tyre-transfer/tyres
     * Same as web admin/get_tyers_by_location (from location dropdown).
     *
     * Required: location_id
     */
    public function transferTyresByLocation()
    {
        $payload    = $this->mergeRequestPayload();
        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location', 'from_location', 'from_location_id'], 0);

        if ($locationId <= 0) {
            $locationId = (int) ($this->request->getGet('location_id')
                ?? $this->request->getGet('location')
                ?? 0);
        }

        if ($locationId <= 0) {
            return $this->apiError('03', 'location_id is required.', 400);
        }

        $rows = $this->adminModel->getTransferTyresByLocation($locationId);

        $tyres = [];
        foreach ($rows as $row) {
            $serial = $row->tyer_sl_no ?? null;
            $tyres[] = [
                'tyre_id'     => (int) ($row->id ?? 0),
                'tyre_serial' => $serial,
                'tyer_sl_no'  => $serial,
                'brand_name'  => $row->brand_name ?? null,
                'model'       => $row->model ?? null,
                'tyer_type'   => $row->tyer_type ?? null,
                'location_id' => isset($row->location_id) ? (int) $row->location_id : null,
                'status'      => isset($row->status) ? (int) $row->status : null,
            ];
        }

        return $this->apiSuccess('Transfer tyres loaded.', [
            'location_id' => $locationId,
            'total'       => count($tyres),
            'tyres'       => $tyres,
        ]);
    }

    /**
     * GET /api/tyre-transfer/tyre-detail
     * POST /api/tyre-transfer/tyre-detail
     * Same as web admin/get_tyer_details (serial select → brand/model fill).
     *
     * Required: tyre_serial / tyer_sl_no
     */
    public function transferTyreDetail()
    {
        $payload = $this->mergeRequestPayload();
        $serial  = trim((string) $this->payloadValue($payload, ['tyre_serial', 'tyer_sl_no'], ''));

        if ($serial === '') {
            $serial = trim((string) ($this->request->getGet('tyre_serial')
                ?? $this->request->getGet('tyer_sl_no')
                ?? ''));
        }

        if ($serial === '') {
            return $this->apiError('03', 'tyre_serial is required.', 400);
        }

        $row = $this->adminModel->getTyreTransferDetailBySerial($serial);
        if ($row === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        return $this->apiSuccess('Tyre detail loaded.', [
            'tyre_id'     => (int) ($row->id ?? 0),
            'tyre_serial' => $row->tyer_sl_no ?? null,
            'tyer_sl_no'  => $row->tyer_sl_no ?? null,
            'brand_name'  => $row->brand_name ?? null,
            'model'       => $row->model ?? null,
            'tyer_model'  => $row->model ?? null,
            'location_id' => isset($row->location_id) ? (int) $row->location_id : null,
            'status'      => isset($row->status) ? (int) $row->status : null,
        ]);
    }

    /**
     * POST /api/tyre-transfer
     * Same as web admin/update_tyer_details (tyreTransfer form submit).
     *
     * Body:
     * - from_location / from_location_id
     * - to_location / to_location_id
     * - date / transfer_date (YYYY-MM-DD)
     * - tyre_serials / tyer_sl_no[] (array)
     */
    public function transferStore()
    {
        $payload = $this->mergeRequestPayload();

        $fromLocationId = (int) $this->payloadValue($payload, ['from_location', 'from_location_id'], 0);
        $toLocationId   = (int) $this->payloadValue($payload, ['to_location', 'to_location_id'], 0);
        $date           = trim((string) $this->payloadValue($payload, ['date', 'transfer_date'], ''));

        $errors = [];
        if ($toLocationId <= 0) {
            $errors[] = 'to_location is required';
        }
        if ($date === '') {
            $errors[] = 'date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'date must be YYYY-MM-DD';
        }

        $serials = $this->parseTransferTyreSerials($payload);
        if ($serials === []) {
            $errors[] = 'At least one tyre serial is required (tyre_serials or tyer_sl_no[])';
        }

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $result = $this->adminModel->transferTyresToLocation($fromLocationId, $toLocationId, $date, $serials);
        if ($result === null || $result['transferred_count'] === 0) {
            return $this->apiError('06', 'No tyres were transferred. Check serial numbers.', 400);
        }

        return $this->apiSuccess('Tyres transferred successfully.', $result);
    }

    /**
     * GET /api/tyre-report
     * Same list as web admin/tyer_report (excludes status 1 / in stock).
     *
     * Optional query:
     * - location_id
     * - status (default 2 = Assigned; use all for every status except stock)
     * - search (serial, brand, model, bill, location)
     * - brand_name, tyer_type
     */
    public function report()
    {
        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);

        $statusRaw = $this->request->getGet('status');
        $status    = $statusRaw === null ? 2 : trim((string) $statusRaw);

        if ($status !== '' && $status !== 'all' && ! is_numeric($status)) {
            return $this->apiError('03', 'status must be a number or all.', 400);
        }

        $search    = trim((string) ($this->request->getGet('search') ?? ''));
        $brandName = trim((string) ($this->request->getGet('brand_name') ?? ''));
        $tyerType  = trim((string) ($this->request->getGet('tyer_type') ?? ''));

        $filters = [
            'location_id' => $locationId,
            'status'      => $status === 'all' ? 'all' : ($status === '' ? '' : (int) $status),
            'search'      => $search,
            'brand_name'  => $brandName,
            'tyer_type'   => $tyerType,
        ];

        $rows = $this->adminModel->getTyreReportList($filters);

        $tyres = [];
        foreach ($rows as $row) {
            $tyres[] = $this->formatTyreReportRow($row);
        }

        return $this->apiSuccess('Tyre report loaded.', [
            'filters' => [
                'location_id' => $locationId > 0 ? $locationId : null,
                'status'      => $status === 'all' ? 'all' : ($status === '' ? null : (int) $status),
                'search'      => $search !== '' ? $search : null,
                'brand_name'  => $brandName !== '' ? $brandName : null,
                'tyer_type'   => $tyerType !== '' ? $tyerType : null,
            ],
            'total' => count($tyres),
            'tyres' => $tyres,
        ]);
    }

    /**
     * GET /api/repair-report
     * Same list as web admin/repaire_report (status = 4, Under Repair).
     *
     * Optional query:
     * - location_id
     * - search (serial, brand, model, bill, location, vendor, remark)
     * - brand_name, tyer_type
     */
    public function repairReport()
    {
        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);
        $search    = trim((string) ($this->request->getGet('search') ?? ''));
        $brandName = trim((string) ($this->request->getGet('brand_name') ?? ''));
        $tyerType  = trim((string) ($this->request->getGet('tyer_type') ?? ''));

        $filters = [
            'location_id' => $locationId,
            'search'      => $search,
            'brand_name'  => $brandName,
            'tyer_type'   => $tyerType,
        ];

        $rows = $this->adminModel->getRepairReportList($filters);

        $tyres = [];
        foreach ($rows as $row) {
            $tyres[] = $this->formatRepairReportRow($row);
        }

        return $this->apiSuccess('Repair report loaded.', [
            'filters' => [
                'location_id' => $locationId > 0 ? $locationId : null,
                'search'      => $search !== '' ? $search : null,
                'brand_name'  => $brandName !== '' ? $brandName : null,
                'tyer_type'   => $tyerType !== '' ? $tyerType : null,
            ],
            'total' => count($tyres),
            'tyres' => $tyres,
        ]);
    }

    /**
     * GET /api/tyre-exchange-report
     * Same list as web admin/tyre_exchange_report.
     *
     * Optional query:
     * - search (vehicle no, old/new serial, remarks)
     * - vehicle_id
     */
    public function exchangeReport()
    {
        $vehicleId = (int) ($this->request->getGet('vehicle_id') ?? 0);
        $search    = trim((string) ($this->request->getGet('search') ?? ''));

        $filters = [
            'search'     => $search,
            'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
        ];

        $rows = $this->adminModel->getExchangeHistory($filters);

        $exchanges = [];
        foreach ($rows as $row) {
            $exchanges[] = $this->formatExchangeReportRow($row);
        }

        return $this->apiSuccess('Tyre exchange report loaded.', [
            'filters' => [
                'search'     => $search !== '' ? $search : null,
                'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
            ],
            'total'     => count($exchanges),
            'exchanges' => $exchanges,
        ]);
    }

    /**
     * GET /api/tyre-exchange-report/{id}
     * Single exchange record from admin/tyre_exchange_report.
     */
    public function exchangeReportShow($exchangeId = null)
    {
        $exchangeId = (int) ($exchangeId ?? 0);
        if ($exchangeId <= 0) {
            return $this->apiError('03', 'Valid exchange id is required.', 400);
        }

        $row = $this->adminModel->getExchangeHistoryById($exchangeId);
        if ($row === null) {
            return $this->apiError('04', 'Exchange record not found.', 404);
        }

        return $this->apiSuccess('Exchange record loaded.', [
            'exchange' => $this->formatExchangeReportRow($row),
        ]);
    }

    /**
     * GET /api/tyre-exchange-report/{id}/history
     * Same as web History button → admin/tyre_details_vw/{to_tyre_id}.
     *
     * Optional query: event_type (1-11)
     */
    public function exchangeReportHistory($exchangeId = null)
    {
        $exchangeId = (int) ($exchangeId ?? 0);
        if ($exchangeId <= 0) {
            return $this->apiError('03', 'Valid exchange id is required.', 400);
        }

        $exchange = $this->adminModel->getExchangeHistoryById($exchangeId);
        if ($exchange === null) {
            return $this->apiError('04', 'Exchange record not found.', 404);
        }

        $tyreId = (int) ($exchange->to_tyre_id ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('04', 'Installed tyre not found for this exchange.', 404);
        }

        $tyre = $this->adminModel->getTyreById($tyreId);
        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        $eventTypeRaw = trim((string) ($this->request->getGet('event_type') ?? ''));
        $historyFilters = ['tyre_id' => $tyreId];

        if ($eventTypeRaw !== '') {
            if (! is_numeric($eventTypeRaw)) {
                return $this->apiError('03', 'event_type must be a number.', 400);
            }
            $historyFilters['event_type'] = (int) $eventTypeRaw;
        }

        $records = $this->adminModel->getHistoryRecords($historyFilters);

        $history = [];
        foreach ($records as $row) {
            $history[] = $this->formatHistoryRow($row, (string) ($tyre->tyer_sl_no ?? ''));
        }

        return $this->apiSuccess('Exchange tyre history loaded.', [
            'filters' => [
                'exchange_id' => $exchangeId,
                'tyre_id'     => $tyreId,
                'event_type'  => $eventTypeRaw !== '' ? (int) $eventTypeRaw : null,
            ],
            'exchange' => $this->formatExchangeReportRow($exchange),
            'tyre'     => $this->formatTyreDetail($tyre),
            'total'    => count($history),
            'history'  => $history,
        ]);
    }

    /**
     * POST /api/repair-report/back-to-stock
     * POST /api/repair-report/{id}/back-to-stock
     * Same as web admin/repaire_report → Admin/update_tyer_repair.
     *
     * Body:
     * - tyre_id / tyer_id OR tyer_sl_no (required if id not in URL)
     * - vendor_id / vendor (optional — defaults to tyre ex_ven_id)
     * - location_id / location (required)
     * - date / stock_entry_date (required, YYYY-MM-DD; default today)
     * - remark (optional)
     */
    public function repairBackToStock($tyreId = null)
    {
        $payload = $this->mergeRequestPayload();

        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            $tyreId = (int) $this->payloadValue($payload, ['tyre_id', 'tyer_id'], 0);
        }

        $serial = trim((string) $this->payloadValue($payload, ['tyer_sl_no', 'tyre_serial'], ''));

        if ($tyreId <= 0 && $serial === '') {
            return $this->apiError('03', 'tyre_id or tyer_sl_no is required.', 400);
        }

        if ($tyreId > 0) {
            $tyre = $this->adminModel->getTyreById($tyreId);
        } else {
            $tyre = $this->adminModel->getTyreDetailsBySlNo($serial);
        }

        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        if ((int) ($tyre->status ?? 0) !== 4) {
            return $this->apiError('05', 'Only tyres under repair (status 4) can be moved back to stock.', 409);
        }

        $vendorId = (int) $this->payloadValue($payload, ['vendor_id', 'vendor'], 0);
        if ($vendorId <= 0) {
            $vendorId = (int) ($tyre->ex_ven_id ?? 0);
        }
        if ($vendorId <= 0) {
            return $this->apiError('03', 'vendor_id is required (repair vendor).', 400);
        }

        $locationId = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        if ($locationId <= 0) {
            return $this->apiError('03', 'location_id is required.', 400);
        }

        $stockDate = trim((string) $this->payloadValue($payload, ['date', 'stock_entry_date', 'entry_date'], date('Y-m-d')));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $stockDate)) {
            return $this->apiError('03', 'date must be YYYY-MM-DD.', 400);
        }

        $remark = trim((string) $this->payloadValue($payload, ['remark', 'remarks'], ''));

        $resolvedId = (int) $tyre->id;
        if (! $this->adminModel->completeRepairBackToStock($resolvedId, $vendorId, $locationId, $stockDate, $remark)) {
            return $this->apiError('06', 'Failed to move tyre back to stock.', 500);
        }

        $updated = $this->adminModel->getTyreById($resolvedId);

        return $this->apiSuccess('Tyre repaired and moved back to stock successfully.', [
            'tyre' => $this->formatTyreDetail($updated),
        ]);
    }

    /**
     * GET /api/stock-tyre
     * Same list as web admin/StockTyer_management.
     *
     * Optional query:
     * - location_id
     * - from_date, to_date (purchase date YYYY-MM-DD)
     * - search (serial, brand, model, bill, location)
     * - tyre_condition (new|old)
     * - brand_name, tyer_type
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
        $search         = trim((string) ($this->request->getGet('search') ?? ''));
        $tyreCondition  = strtolower(trim((string) ($this->request->getGet('tyre_condition') ?? '')));
        $brandName      = trim((string) ($this->request->getGet('brand_name') ?? ''));
        $tyerType       = trim((string) ($this->request->getGet('tyer_type') ?? ''));

        if ($tyreCondition !== '' && ! in_array($tyreCondition, ['new', 'old'], true)) {
            return $this->apiError('03', 'tyre_condition must be new or old.', 400);
        }

        $filters = [
            'location_id'    => $locationId,
            'from_date'      => $fromDate,
            'to_date'        => $toDate,
            'search'         => $search,
            'tyre_condition' => $tyreCondition,
            'brand_name'     => $brandName,
            'tyer_type'      => $tyerType,
        ];

        $rows = $this->adminModel->getStockTyreList($filters);

        $tyres = [];
        foreach ($rows as $row) {
            $tyres[] = $this->formatTyreRow($row);
        }

        return $this->apiSuccess('Stock tyres loaded.', [
            'filters' => [
                'location_id'    => $locationId > 0 ? $locationId : null,
                'from_date'      => $fromDate !== '' ? $fromDate : null,
                'to_date'        => $toDate !== '' ? $toDate : null,
                'search'         => $search !== '' ? $search : null,
                'tyre_condition' => $tyreCondition !== '' ? $tyreCondition : null,
                'brand_name'     => $brandName !== '' ? $brandName : null,
                'tyer_type'      => $tyerType !== '' ? $tyerType : null,
            ],
            'total' => count($tyres),
            'tyres' => $tyres,
        ]);
    }

    /**
     * GET /api/scrap-tyre
     * Same list as web admin/scrapTyer_management (status = 3).
     *
     * Optional query:
     * - location_id
     * - from_date, to_date (purchase date YYYY-MM-DD)
     * - search (serial, brand, model, bill, location)
     * - brand_name, tyer_type
     */
    public function scrap()
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
        $search    = trim((string) ($this->request->getGet('search') ?? ''));
        $brandName = trim((string) ($this->request->getGet('brand_name') ?? ''));
        $tyerType  = trim((string) ($this->request->getGet('tyer_type') ?? ''));

        $filters = [
            'location_id' => $locationId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'search'      => $search,
            'brand_name'  => $brandName,
            'tyer_type'   => $tyerType,
        ];

        $rows = $this->adminModel->getScrapTyreList($filters);

        $tyres = [];
        foreach ($rows as $row) {
            $tyres[] = $this->formatTyreDetail($row);
        }

        return $this->apiSuccess('Scrap tyres loaded.', [
            'filters' => [
                'location_id' => $locationId > 0 ? $locationId : null,
                'from_date'   => $fromDate !== '' ? $fromDate : null,
                'to_date'     => $toDate !== '' ? $toDate : null,
                'search'      => $search !== '' ? $search : null,
                'brand_name'  => $brandName !== '' ? $brandName : null,
                'tyer_type'   => $tyerType !== '' ? $tyerType : null,
            ],
            'total' => count($tyres),
            'tyres' => $tyres,
        ]);
    }

    /**
     * GET /api/sent-to-vendor-tyre
     * Same list as web admin/sentToVendorTyer_management (status = 10, Exchange Requested).
     *
     * Optional query:
     * - location_id
     * - from_date, to_date (purchase/request date YYYY-MM-DD)
     * - search (serial, brand, model, bill, location, vendor)
     * - brand_name, tyer_type
     */
    public function sentToVendor()
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
        $search    = trim((string) ($this->request->getGet('search') ?? ''));
        $brandName = trim((string) ($this->request->getGet('brand_name') ?? ''));
        $tyerType  = trim((string) ($this->request->getGet('tyer_type') ?? ''));

        $filters = [
            'location_id' => $locationId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'search'      => $search,
            'brand_name'  => $brandName,
            'tyer_type'   => $tyerType,
        ];

        $rows = $this->adminModel->getSentToVendorTyreList($filters);

        $tyres = [];
        foreach ($rows as $row) {
            $tyres[] = $this->formatTyreDetail($row);
        }

        return $this->apiSuccess('Sent to vendor tyres loaded.', [
            'filters' => [
                'location_id' => $locationId > 0 ? $locationId : null,
                'from_date'   => $fromDate !== '' ? $fromDate : null,
                'to_date'     => $toDate !== '' ? $toDate : null,
                'search'      => $search !== '' ? $search : null,
                'brand_name'  => $brandName !== '' ? $brandName : null,
                'tyer_type'   => $tyerType !== '' ? $tyerType : null,
            ],
            'total' => count($tyres),
            'tyres' => $tyres,
        ]);
    }

    /**
     * GET /api/sold-tyre
     * Same list as web admin/soldTyer_management (status = 7).
     *
     * Optional query:
     * - location_id
     * - vendor_id
     * - from_date, to_date (selling_date YYYY-MM-DD)
     * - search (serial, brand, model, bill, remark, location, vendor)
     * - brand_name, tyer_type
     */
    public function sold()
    {
        $fromDate = trim((string) ($this->request->getGet('from_date')
            ?? $this->request->getGet('selling_from_date')
            ?? ''));
        $toDate = trim((string) ($this->request->getGet('to_date')
            ?? $this->request->getGet('selling_to_date')
            ?? ''));

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
        $vendorId  = (int) ($this->request->getGet('vendor_id') ?? 0);
        $search    = trim((string) ($this->request->getGet('search') ?? ''));
        $brandName = trim((string) ($this->request->getGet('brand_name') ?? ''));
        $tyerType  = trim((string) ($this->request->getGet('tyer_type') ?? ''));

        $filters = [
            'location_id' => $locationId,
            'vendor_id'   => $vendorId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'search'      => $search,
            'brand_name'  => $brandName,
            'tyer_type'   => $tyerType,
        ];

        $rows = $this->adminModel->getSoldTyreList($filters);

        $tyres = [];
        foreach ($rows as $row) {
            $tyres[] = $this->formatTyreDetail($row);
        }

        return $this->apiSuccess('Sold tyres loaded.', [
            'filters' => [
                'location_id' => $locationId > 0 ? $locationId : null,
                'vendor_id'   => $vendorId > 0 ? $vendorId : null,
                'from_date'   => $fromDate !== '' ? $fromDate : null,
                'to_date'     => $toDate !== '' ? $toDate : null,
                'search'      => $search !== '' ? $search : null,
                'brand_name'  => $brandName !== '' ? $brandName : null,
                'tyer_type'   => $tyerType !== '' ? $tyerType : null,
            ],
            'total' => count($tyres),
            'tyres' => $tyres,
        ]);
    }

    /**
     * POST /api/sold-tyre/restore
     * Same as web admin/soldTyreBackToStock — cancel sale and restore tyre.
     *
     * Body:
     * - tyre_ids (array) OR tyre_id / tyer_id (single id or comma-separated)
     * - destination (optional): stock | scrap (default stock)
     */
    public function restoreSold()
    {
        $payload     = $this->mergeRequestPayload();
        $tyreIds     = $this->parseTyreIds($payload);
        $destination = strtolower(trim((string) $this->payloadValue($payload, ['destination', 'restore_to'], 'stock')));

        if ($tyreIds === []) {
            return $this->apiError('03', 'At least one tyre_id is required.', 400);
        }

        if (! in_array($destination, ['stock', 'scrap'], true)) {
            return $this->apiError('03', 'destination must be stock or scrap.', 400);
        }

        $restored = $this->adminModel->restoreSoldTyres($tyreIds, $destination);

        if ($restored === []) {
            return $this->apiError('05', 'No sold tyres could be restored. Tyres must exist with status 7 (Sold).', 409);
        }

        $tyres = [];
        foreach ($restored as $tyreId) {
            $row = $this->adminModel->getTyreById($tyreId);
            if ($row !== null) {
                $tyres[] = $this->formatTyreDetail($row);
            }
        }

        $message = $destination === 'scrap'
            ? 'Tyre(s) restored to scrap yard successfully.'
            : 'Tyre(s) restored to stock successfully.';

        return $this->apiSuccess($message, [
            'destination'    => $destination,
            'restored_count' => count($restored),
            'restored_ids'   => $restored,
            'tyres'          => $tyres,
        ]);
    }

    /**
     * POST /api/scrap-tyre/return-to-stock
     * Same as web admin/scrapTyreBackToStock + bulkScrapTyreBackToStock.
     *
     * Body:
     * - tyre_ids (array) OR tyre_id / tyer_id (single id or comma-separated)
     */
    public function returnToStock()
    {
        $payload = $this->mergeRequestPayload();
        $tyreIds = $this->parseTyreIds($payload);

        if ($tyreIds === []) {
            return $this->apiError('03', 'At least one tyre_id is required.', 400);
        }

        $restored = $this->adminModel->restoreScrapTyresToStock($tyreIds);

        if ($restored === []) {
            return $this->apiError('05', 'No scrap tyres could be restored. Tyres must exist with status 3 (Scrap Yard).', 409);
        }

        $tyres = [];
        foreach ($restored as $tyreId) {
            $row = $this->adminModel->getTyreById($tyreId);
            if ($row !== null) {
                $tyres[] = $this->formatTyreDetail($row);
            }
        }

        return $this->apiSuccess('Tyre(s) restored to stock successfully.', [
            'restored_count' => count($restored),
            'restored_ids'   => $restored,
            'tyres'          => $tyres,
        ]);
    }

    /**
     * POST /api/scrap-tyre/sell
     * Same as web admin/process_tyre_sale.
     *
     * Body:
     * - tyre_ids (array) OR tyre_id / tyer_id (single or comma-separated)
     * - vendor_id (required) — buyer/vendor
     * - selling_date (required, YYYY-MM-DD)
     * - remark (optional)
     */
    public function sell()
    {
        $payload = $this->mergeRequestPayload();

        $tyreIds     = $this->parseTyreIds($payload);
        $vendorId    = (int) $this->payloadValue($payload, ['vendor_id', 'vendor', 'buyer_id'], 0);
        $sellingDate = trim((string) $this->payloadValue($payload, ['selling_date', 'sale_date'], date('Y-m-d')));
        $remark      = trim((string) $this->payloadValue($payload, ['remark', 'remarks'], ''));

        $errors = [];
        if ($tyreIds === []) {
            $errors[] = 'At least one tyre_id is required';
        }
        if ($vendorId <= 0) {
            $errors[] = 'vendor_id is required';
        }
        if ($sellingDate === '') {
            $errors[] = 'selling_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $sellingDate)) {
            $errors[] = 'selling_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $vendor = $this->db->table('vendor')->where('id', $vendorId)->get()->getRow();
        if ($vendor === null) {
            return $this->apiError('04', 'Vendor/buyer not found.', 404);
        }

        $sold = $this->adminModel->sellScrapTyres($tyreIds, $vendorId, $sellingDate, $remark);

        if ($sold === []) {
            return $this->apiError('05', 'No scrap tyres could be sold. Tyres must exist with status 3 (Scrap Yard).', 409);
        }

        $tyres = [];
        foreach ($sold as $tyreId) {
            $row = $this->adminModel->getTyreById($tyreId);
            if ($row !== null) {
                $tyres[] = $this->formatTyreDetail($row);
            }
        }

        return $this->apiSuccess('Tyre(s) sold successfully.', [
            'sold_count'   => count($sold),
            'sold_ids'     => $sold,
            'vendor_id'    => $vendorId,
            'vendor_name'  => $vendor->name ?? null,
            'selling_date' => $sellingDate,
            'remark'       => $remark !== '' ? $remark : null,
            'tyres'        => $tyres,
        ], 201);
    }

    /**
     * GET /api/stock-tyre/{id}/history
     * GET /api/tyre-details/{id}
     * Same as web admin/tyre_details_vw/{id} (tyre info + full history timeline).
     *
     * Optional query: event_type (1-11)
     */
    public function history($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $tyre = $this->adminModel->getTyreById($tyreId);
        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        $eventTypeRaw = trim((string) ($this->request->getGet('event_type') ?? ''));
        $historyFilters = ['tyre_id' => $tyreId];

        if ($eventTypeRaw !== '') {
            if (! is_numeric($eventTypeRaw)) {
                return $this->apiError('03', 'event_type must be a number.', 400);
            }
            $historyFilters['event_type'] = (int) $eventTypeRaw;
        }

        $records = $this->adminModel->getHistoryRecords($historyFilters);

        $history = [];
        foreach ($records as $row) {
            $history[] = $this->formatHistoryRow($row, (string) ($tyre->tyer_sl_no ?? ''));
        }

        return $this->apiSuccess('Tyre history loaded.', [
            'filters' => [
                'tyre_id'    => $tyreId,
                'event_type' => $eventTypeRaw !== '' ? (int) $eventTypeRaw : null,
            ],
            'tyre'    => $this->formatTyreDetail($tyre),
            'total'   => count($history),
            'history' => $history,
        ]);
    }

    /**
     * POST /api/stock-tyre/{id}/update-status
     * Same as web admin/tyer_exchange → Admin/update_tyer_report.
     *
     * Body (JSON or form):
     * - status: 4 = For Repair, 3 = Move to Scrap Yard
     * - vendor_id: optional for repair (recommended), not used for scrap
     * - remark: optional notes
     */
    public function updateStatus($tyreId = null)
    {
        $tyreId  = (int) ($tyreId ?? 0);
        $payload = $this->mergeRequestPayload();

        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $status = (int) $this->payloadValue($payload, ['status'], 0);
        if (! in_array($status, [3, 4], true)) {
            return $this->apiError('03', 'status must be 4 (repair) or 3 (scrap yard).', 400);
        }

        $remark   = trim((string) $this->payloadValue($payload, ['remark', 'remarks'], ''));
        $vendorId = (int) $this->payloadValue($payload, ['vendor_id', 'vendor'], 0);

        if ($status === 4 && $vendorId <= 0) {
            return $this->apiError('03', 'vendor_id is required when status is 4 (repair).', 400);
        }

        $tyre = $this->adminModel->getTyreById($tyreId);
        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        if (! $this->adminModel->updateTyreLifecycleStatus($tyreId, $status, $vendorId > 0 ? $vendorId : null, $remark)) {
            return $this->apiError('06', 'Failed to update tyre status.', 500);
        }

        $updated = $this->adminModel->getTyreById($tyreId);

        $message = $status === 4
            ? 'Tyre sent for repair successfully.'
            : 'Tyre moved to scrap yard successfully.';

        return $this->apiSuccess($message, [
            'tyre' => $this->formatTyreDetail($updated),
        ]);
    }

    /**
     * POST /api/stock-tyre/{id}/request-exchange
     * Same as web admin/StockTyer_management → sent_to_vendor (Exchange button).
     *
     * Body: remark (optional)
     */
    public function requestExchange($tyreId = null)
    {
        $tyreId  = (int) ($tyreId ?? 0);
        $payload = $this->mergeRequestPayload();

        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $tyre = $this->adminModel->getTyreById($tyreId);
        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        if ((int) ($tyre->status ?? 0) !== 1) {
            return $this->apiError('05', 'Only in-stock tyres (status 1) can be sent for exchange.', 409);
        }

        $remark = trim((string) $this->payloadValue($payload, ['remark', 'remarks'], ''));

        if (! $this->adminModel->requestTyreExchange($tyreId, $remark)) {
            return $this->apiError('06', 'Failed to request tyre exchange.', 500);
        }

        $updated = $this->adminModel->getTyreById($tyreId);

        return $this->apiSuccess('Exchange requested successfully.', [
            'tyre' => $this->formatTyreDetail($updated),
        ], 201);
    }

    /**
     * POST /api/stock-tyre/exchange/store
     * Same as web admin/process_vendor_exchange (vendor warranty exchange complete).
     *
     * Body:
     * - old_tyre_id (required)
     * - new_serial (required)
     * - brand_name or brand_id (required)
     * - new_model (optional)
     * - exchange_date (optional, YYYY-MM-DD, default today)
     * - remark (optional)
     */
    public function exchangeStore()
    {
        $payload   = $this->mergeRequestPayload();
        $oldTyreId = (int) $this->payloadValue($payload, ['old_tyre_id', 'tyre_id', 'old_id'], 0);

        return $this->completeVendorExchange($oldTyreId, $payload, false);
    }

    /**
     * GET /api/vendor-exchange/{id}
     * Same screen data as web admin/vendor_exchange/{id}.
     */
    public function vendorExchangeShow($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $tyre = $this->adminModel->getTyreById($tyreId);
        if ($tyre === null) {
            return $this->apiError('04', 'Tyre not found.', 404);
        }

        $currentBrand = trim((string) ($tyre->brand_name ?? ''));
        $brands       = [];

        foreach ($this->adminModel->getDistinctTyreBrands() as $row) {
            $brand = trim((string) ($row->brand_name ?? ''));
            if ($brand === '') {
                continue;
            }

            $brands[] = [
                'value'    => $brand,
                'label'    => $brand,
                'selected' => $brand === $currentBrand,
            ];
        }

        return $this->apiSuccess('Vendor exchange form loaded.', [
            'tyre_id'       => $tyreId,
            'can_complete'  => (int) ($tyre->status ?? 0) === 10,
            'old_tyre'      => $this->formatTyreDetail($tyre),
            'readonly'      => [
                'defective_serial' => $tyre->tyer_sl_no ?? null,
                'brand_name'       => $tyre->brand_name ?? null,
                'model'            => $tyre->model ?? null,
                'brand_model'      => trim(($tyre->brand_name ?? '') . ' | ' . ($tyre->model ?? ''), ' |'),
                'vendor_id'        => isset($tyre->vendor_id) ? (int) $tyre->vendor_id : null,
                'vendor_name'      => $tyre->vendor_name ?? null,
            ],
            'brands'        => $brands,
            'form_defaults' => [
                'old_tyre_id'   => $tyreId,
                'new_serial'    => '',
                'brand_name'    => $currentBrand !== '' ? $currentBrand : null,
                'new_model'     => $tyre->model ?? null,
                'exchange_date' => date('Y-m-d'),
                'remark'        => '',
            ],
        ]);
    }

    /**
     * POST /api/vendor-exchange/{id}
     * Same as web admin/process_vendor_exchange for admin/vendor_exchange/{id}.
     *
     * Body:
     * - new_serial (required)
     * - brand_name or brand_id (required)
     * - new_model (optional)
     * - exchange_date (optional, YYYY-MM-DD, default today)
     * - remark (optional)
     */
    public function vendorExchangeUpdate($tyreId = null)
    {
        $tyreId = (int) ($tyreId ?? 0);
        if ($tyreId <= 0) {
            return $this->apiError('03', 'Valid tyre id is required.', 400);
        }

        $payload = $this->mergeRequestPayload();

        return $this->completeVendorExchange($tyreId, $payload, true);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function completeVendorExchange(int $oldTyreId, array $payload, bool $requireExchangeRequestedStatus)
    {
        if ($oldTyreId <= 0) {
            return $this->apiError('03', 'old_tyre_id is required.', 400);
        }

        $newSerial    = trim((string) $this->payloadValue($payload, ['new_serial', 'tyre_serial', 'tyer_sl_no'], ''));
        $brandName    = trim((string) $this->payloadValue($payload, ['brand_name', 'brand_id', 'brand'], ''));
        $newModel     = trim((string) $this->payloadValue($payload, ['new_model', 'model'], ''));
        $exchangeDate = trim((string) $this->payloadValue($payload, ['exchange_date', 'replacement_date'], date('Y-m-d')));
        $remark       = trim((string) $this->payloadValue($payload, ['remark', 'remarks'], ''));

        $errors = [];
        if ($newSerial === '') {
            $errors[] = 'new_serial is required';
        }
        if ($brandName === '') {
            $errors[] = 'brand_name is required';
        }
        if ($exchangeDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $exchangeDate)) {
            $errors[] = 'exchange_date must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $oldTyre = $this->adminModel->getTyreById($oldTyreId);
        if ($oldTyre === null) {
            return $this->apiError('04', 'Original tyre not found.', 404);
        }

        if ($requireExchangeRequestedStatus && (int) ($oldTyre->status ?? 0) !== 10) {
            return $this->apiError('05', 'Tyre is not in Exchange Requested status (status 10).', 409);
        }

        $serialExists = $this->db->table('tyer_management')
            ->where('tyer_sl_no', $newSerial)
            ->countAllResults() > 0;

        if ($serialExists) {
            return $this->apiError('05', 'The replacement serial number already exists.', 409);
        }

        $result = $this->adminModel->storeVendorTyreExchange(
            $oldTyreId,
            $newSerial,
            $brandName,
            $newModel,
            $exchangeDate,
            $remark
        );

        if ($result === null) {
            return $this->apiError('06', 'Failed to complete vendor exchange.', 500);
        }

        $oldTyreUpdated = $this->adminModel->getTyreById($result['old_tyre_id']);
        $newTyre        = $this->adminModel->getTyreById($result['new_tyre_id']);

        return $this->apiSuccess('Vendor exchange completed successfully.', [
            'exchange' => [
                'old_tyre_id'   => $result['old_tyre_id'],
                'new_tyre_id'   => $result['new_tyre_id'],
                'new_serial'    => $newSerial,
                'brand_name'    => $brandName,
                'new_model'     => $newModel !== '' ? $newModel : null,
                'exchange_date' => $exchangeDate,
                'remark'        => $remark !== '' ? $remark : null,
            ],
            'old_tyre' => $this->formatTyreDetail($oldTyreUpdated),
            'new_tyre' => $this->formatTyreDetail($newTyre),
        ], 201);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<int>
     */
    private function parseTyreIds(array $payload): array
    {
        $raw = $this->payloadValue($payload, ['tyre_ids', 'tyer_ids'], null);

        if (is_array($raw)) {
            return array_values(array_unique(array_filter(array_map('intval', $raw), static fn (int $id): bool => $id > 0)));
        }

        $single = $this->payloadValue($payload, ['tyre_id', 'tyer_id', 'id'], '');
        if (is_array($single)) {
            return array_values(array_unique(array_filter(array_map('intval', $single), static fn (int $id): bool => $id > 0)));
        }

        $single = trim((string) $single);
        if ($single === '') {
            return [];
        }

        if (str_contains($single, ',')) {
            $parts = array_map('trim', explode(',', $single));

            return array_values(array_unique(array_filter(array_map('intval', $parts), static fn (int $id): bool => $id > 0)));
        }

        $id = (int) $single;

        return $id > 0 ? [$id] : [];
    }

    /**
     * @return list<string>
     */
    private function parseTransferTyreSerials(array $payload): array
    {
        $raw = $this->payloadValue($payload, ['tyre_serials', 'tyer_sl_no', 'tyre_serial'], []);

        if (is_string($raw)) {
            if ($raw === '') {
                $raw = [];
            } else {
                $parts = preg_split('/\s*,\s*/', $raw);
                $raw   = $parts !== false ? $parts : [$raw];
            }
        }

        if (! is_array($raw)) {
            return [];
        }

        $serials = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $serial = trim((string) ($item['tyre_serial'] ?? $item['tyer_sl_no'] ?? ''));
            } else {
                $serial = trim((string) $item);
            }

            if ($serial !== '') {
                $serials[] = $serial;
            }
        }

        return $serials;
    }

    /**
     * @return list<array{tyre_id: int, tyer_sl_no: string, tyer_type: string}>
     */
    private function parsePurchaseBillTyreLines(array $payload, bool $forStore = false): array
    {
        $raw = $payload['tyres'] ?? $payload['tyre_lines'] ?? null;
        if (is_array($raw) && $raw !== []) {
            $lines = [];
            foreach ($raw as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $serial = trim((string) ($item['tyre_serial'] ?? $item['tyer_sl_no'] ?? ''));
                $type   = trim((string) ($item['tyre_type'] ?? $item['tyer_type'] ?? ''));
                if ($serial === '' || $type === '') {
                    continue;
                }

                $lines[] = [
                    'tyre_id'    => $forStore ? 0 : (int) ($item['tyre_id'] ?? $item['tyer_id'] ?? 0),
                    'tyer_sl_no' => $serial,
                    'tyer_type'  => $type,
                ];
            }

            if ($lines !== []) {
                return $lines;
            }
        }

        $ids     = $forStore ? [] : $this->payloadValue($payload, ['tyer_id', 'tyre_ids'], []);
        $serials = $this->payloadValue($payload, ['tyer_sl_no', 'tyre_serials', 'tyre_serial'], []);
        $types   = $this->payloadValue($payload, ['tyer_type', 'tyre_types', 'tyre_type'], []);

        if (! is_array($ids)) {
            $ids = $ids === '' || $ids === null ? [] : [$ids];
        }
        if (! is_array($serials)) {
            $serials = $serials === '' || $serials === null ? [] : [$serials];
        }
        if (! is_array($types)) {
            $types = $types === '' || $types === null ? [] : [$types];
        }

        $lines = [];
        $count = max(count($ids), count($serials), count($types));
        for ($i = 0; $i < $count; $i++) {
            $serial = trim((string) ($serials[$i] ?? ''));
            $type   = trim((string) ($types[$i] ?? ''));
            if ($serial === '' || $type === '') {
                continue;
            }

            $lines[] = [
                'tyre_id'    => $forStore ? 0 : (int) ($ids[$i] ?? 0),
                'tyer_sl_no' => $serial,
                'tyer_type'  => $type,
            ];
        }

        return $lines;
    }

    /**
     * @return array{vendors: list<array<string, mixed>>, locations: list<array<string, mixed>>, brands: list<array<string, string>>}
     */
    private function getPurchaseBillDropdowns(): array
    {
        $vendors = [];
        foreach ($this->adminModel->Get_vendor() as $vendor) {
            $name = trim((string) ($vendor->name ?? ''));
            if ($name === '') {
                continue;
            }

            $vendors[] = [
                'id'            => (int) $vendor->id,
                'name'          => $name,
                'label'         => $name,
                'location_id'   => $vendor->location ? (int) $vendor->location : null,
                'location_name' => $vendor->location_name ?? null,
            ];
        }

        $locations = [];
        foreach ($this->adminModel->getActiveLocationList() as $row) {
            $locations[] = [
                'id'            => (int) ($row->location_id ?? 0),
                'location_id'   => (int) ($row->location_id ?? 0),
                'location_name' => $row->location_name ?? null,
                'label'         => $row->location_name ?? null,
            ];
        }

        return [
            'vendors'   => $vendors,
            'locations' => $locations,
            'brands'    => $this->getTyreBrandOptions(),
        ];
    }

    private function formatTyreRow(object $row): array
    {
        $condition = (string) ($row->tyre_condition ?? '');

        return [
            'tyre_id'              => (int) ($row->id ?? 0),
            'tyre_serial'          => $row->tyer_sl_no ?? null,
            'brand_name'           => $row->brand_name ?? null,
            'model'                => $row->model ?? null,
            'tyre_type'            => $row->tyer_type ?? null,
            'tyre_position'        => $row->tyer_position ?? null,
            'tyre_condition'       => $condition !== '' ? strtolower($condition) : null,
            'tyre_condition_label' => $condition !== '' ? $condition : null,
            'bill_no'              => $row->bill_no ?? null,
            'purchase_date'        => $row->date ?? null,
            'location_id'          => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name'        => $row->location_name ?? null,
            'status'               => (int) ($row->status ?? 1),
            'status_label'         => 'In Stock',
            'remark'               => $row->remark ?? null,
        ];
    }

    private function formatTyreDetail(object $row): array
    {
        $summary = $this->formatTyreRow($row);
        $status  = (int) ($row->status ?? 1);

        $summary['status']               = $status;
        $summary['status_label']         = self::STATUS_LABELS[$status] ?? 'Unknown';
        $summary['vehicle_id']           = isset($row->vehicle_id) ? (int) $row->vehicle_id : null;
        $summary['vehicle_no']           = $row->vehicle_no ?? null;
        $summary['assign_date']          = $row->asign_date ?? null;
        $summary['price']                = isset($row->price) ? (float) $row->price : null;
        $summary['vendor_id']            = isset($row->vendor_id) ? (int) $row->vendor_id : null;
        $summary['vendor_name']          = $row->vendor_name ?? null;
        $summary['selling_date']         = $row->selling_date ?? null;
        $summary['replaced_from_id']     = isset($row->replaced_from_id) ? (int) $row->replaced_from_id : null;
        $summary['replaced_to_id']       = isset($row->replaced_to_id) ? (int) $row->replaced_to_id : null;
        $summary['replaced_from_serial'] = $row->replaced_from_serial ?? null;
        $summary['replaced_to_serial']   = $row->replaced_to_serial ?? null;

        if ($row->asign_date !== null && (string) $row->asign_date !== '') {
            $summary['tyre_condition']       = 'old';
            $summary['tyre_condition_label'] = 'Old';
        }

        return $summary;
    }

    private function formatPurchaseBillRow(object $row): array
    {
        return [
            'tyre_id'       => (int) ($row->id ?? 0),
            'bill_no'       => $row->bill_no ?? null,
            'purchase_date' => $row->date ?? null,
            'vendor_id'     => isset($row->vendor_id) ? (int) $row->vendor_id : null,
            'vendor_name'   => $row->vendor_name ?? ($row->name ?? null),
            'brand_name'    => $row->brand_name ?? null,
            'model'         => $row->model ?? null,
            'quantity'      => (int) ($row->qty ?? 0),
            'price'         => isset($row->price) ? (float) $row->price : null,
            'location_id'   => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name' => $row->location_name ?? null,
        ];
    }

    private function formatPurchaseBillTyreItem(object $row): array
    {
        $item = $this->formatTyreDetail($row);
        $item['bill_no']       = $row->bill_no ?? null;
        $item['purchase_date'] = $row->date ?? null;
        $item['vendor_id']     = isset($row->vendor_id) ? (int) $row->vendor_id : null;
        $item['vendor_name']   = $row->vendor_name ?? null;

        return $item;
    }

    /**
     * Brand options from web admin/tyeredit_vw.
     *
     * @return list<array{value: string, label: string}>
     */
    private function getTyreBrandOptions(): array
    {
        $brands = ['MRF', 'CEAT', 'Apollo', 'JK Tyre', 'Bridgestone', 'Michelin', 'Goodyear', 'Continental', 'Falken', 'Other'];
        $options = [];

        foreach ($brands as $brand) {
            $options[] = [
                'value' => $brand,
                'label' => $brand,
            ];
        }

        return $options;
    }

    private function formatExchangeReportRow(object $row): array
    {
        $vehicleId = isset($row->vehicle_id) ? (int) $row->vehicle_id : 0;

        return [
            'exchange_id'   => (int) ($row->id ?? 0),
            'vehicle_id'    => $vehicleId > 0 ? $vehicleId : null,
            'vehicle_no'    => $row->vehicle_no ?? null,
            'tyre_position' => $row->tyre_position ?? null,
            'exchange_date' => $row->exchange_date ?? null,
            'created_at'    => $row->created_at ?? null,
            'remarks'       => $row->remarks ?? null,
            'old_tyre'      => [
                'tyre_id' => isset($row->from_tyre_id) ? (int) $row->from_tyre_id : null,
                'serial'  => $row->old_serial ?? null,
                'brand'   => $row->old_brand ?? null,
            ],
            'new_tyre' => [
                'tyre_id' => isset($row->to_tyre_id) ? (int) $row->to_tyre_id : null,
                'serial'  => $row->new_serial ?? null,
                'brand'   => $row->new_brand ?? null,
            ],
        ];
    }

    /**
     * Report row keeps history-based New/Old condition from web tyer_report query.
     */
    private function formatTyreReportRow(object $row): array
    {
        $detail    = $this->formatTyreDetail($row);
        $condition = trim((string) ($row->tyre_condition ?? ''));

        if ($condition !== '') {
            $detail['tyre_condition']       = strtolower($condition);
            $detail['tyre_condition_label'] = $condition;
        }

        $status = (int) ($row->status ?? 0);
        $detail['can_update_status'] = ! in_array($status, [3, 7], true);

        return $detail;
    }

    /**
     * Repair report row — includes repair vendor from ex_ven_id join.
     */
    private function formatRepairReportRow(object $row): array
    {
        $detail = $this->formatTyreDetail($row);

        $detail['repair_vendor_id']   = isset($row->ex_ven_id) ? (int) $row->ex_ven_id : null;
        $detail['repair_vendor_name'] = $row->exchange_vendorname ?? null;

        return $detail;
    }

    private function formatHistoryRow(object $row, string $currentSerial): array
    {
        $eventType = isset($row->event_type) ? (int) $row->event_type : 0;
        $rowSerial = (string) ($row->tyer_sl_no ?? '');

        return [
            'history_id'        => (int) ($row->tyre_history_id ?? $row->id ?? 0),
            'tyre_id'           => (int) ($row->tyre_id ?? 0),
            'tyre_serial'       => $rowSerial !== '' ? $rowSerial : null,
            'brand_name'        => $row->brand_name ?? null,
            'tyre_type'         => $row->tyer_type ?? null,
            'is_ancestor_tyre'  => $currentSerial !== '' && $rowSerial !== '' && $rowSerial !== $currentSerial,
            'vehicle_no'        => $row->vehicle_no ?? null,
            'tyre_position'     => $row->tyre_position ?? null,
            'event_type'        => $eventType,
            'event_type_label'  => self::EVENT_TYPE_LABELS[$eventType] ?? 'Unknown',
            'event_date'        => $row->event_date ?? null,
            'vendor_id'         => isset($row->vendor_id) ? (int) $row->vendor_id : null,
            'vendor_name'       => $row->vendor_name ?? null,
            'location_id'       => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name'     => $row->location_name ?? null,
            'from_location'     => $row->from_location ?? null,
            'to_location'       => $row->to_location ?? null,
            'remarks'           => $row->remarks ?? null,
            'created_at'        => $row->created_at ?? null,
        ];
    }
}
