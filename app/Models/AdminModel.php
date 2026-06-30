<?php
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
use DateTime;

class AdminModel extends Model
{
    protected $table = 'user';

    function Settingdata()
    {
        $builder = $this->db->table('settingg');
        $builder->select('*');
        $builder->where('settingg_id', 1);
        return $builder->get()->getResult();
    }

    public function get_active_driver_materials($driver_id)
    {
        return $this->db->table('driver_material_issue')
            ->where('driver_id', $driver_id)
            ->where('status', 'Active')
            ->get()->getResult();
    }

    public function get_all_material_issues($driver_id = null)
    {
        $builder = $this->db->table('driver_material_issue')
            ->select('driver_material_issue.*, staff.name as driver_name')
            ->join('staff', 'staff.id = driver_material_issue.driver_id', 'left');

        if ($driver_id) {
            $builder->where('driver_material_issue.driver_id', $driver_id);
        }

        return $builder->orderBy('issued_date', 'DESC')
            ->get()->getResult();
    }

    public function get_all_material_reissues()
    {
        return $this->db->table('driver_material_reissue')
            ->select('driver_material_reissue.*, staff.name as driver_name')
            ->join('staff', 'staff.id = driver_material_reissue.driver_id', 'left')
            ->orderBy('reissue_date', 'DESC')
            ->get()->getResult();
    }

    function Vehicle()
    {
        $builder = $this->db->table('vehicle');
        $builder->select('vehicle.*, location.location_name, vehicle_types.type_name');
        $builder->join('location', 'location.location_id = vehicle.location_id', 'left');
        $builder->join('vehicle_types', 'vehicle_types.id = vehicle.vehicle_type', 'left');
        return $builder->get()->getResult();
    }
    function Getallstaf()
    {
        $builder = $this->db->table('staff');
        $builder->select('staff.*,location.location_name');
        $builder->join('location', 'location.location_id = staff.location_id', 'left');
        return $builder->get()->getResult();
    }

    /**
     * Driver master dropdown/list — staff with user_type = DRIVER only.
     *
     * @return list<object>
     */
    function GetallDrivers()
    {
        $builder = $this->db->table('staff');
        $builder->select('staff.*, location.location_name');
        $builder->join('location', 'location.location_id = staff.location_id', 'left');
        $builder->where('staff.user_type', 'DRIVER');

        return $builder->orderBy('staff.id', 'ASC')->get()->getResult();
    }

    public function GetActiveStaff($date = null, $type = null)
    {
        $builder = $this->db->table('staff');
        $builder->select('staff.*, location.location_name');
        $builder->join('location', 'location.location_id = staff.location_id', 'left');

        if (!empty($date)) {
            $builder->groupStart()
                ->where('staff.doj <=', $date)
                ->orWhere('staff.doj', '0000-00-00')
                ->orWhere('staff.doj', null)
                ->groupEnd();

            $builder->groupStart()
                ->where('staff.resign_date IS NULL')
                ->orWhere('staff.resign_date', '0000-00-00')
                ->orWhere('staff.resign_date >=', $date)
                ->groupEnd();
        }

        if (!empty($type)) {
            $builder->where('staff.user_type', $type);
        }

        return $builder->orderBy('staff.name', 'ASC')->get()->getResult();
    }
    public function driverasignment($from_date = null, $to_date = null)
    {
        $builder = $this->db->table('driver_assignment');
        $builder->select('driver_assignment.*, vehicle.vehicle_no as vehicle_number, staff.name as driver_name, staff.staff_code as driver_code');
        $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');
        $builder->join('staff', 'staff.id = driver_assignment.driver', 'left');

        if (!empty($from_date) && !empty($to_date)) {
            $builder->where('driver_assignment.from_date >=', $from_date);
            $builder->where('driver_assignment.from_date <=', $to_date);
        }

        return $builder->get()->getResult();
    }




    function singledriverasignment($asign_id)
    {
        $builder = $this->db->table('driver_assignment');
        $builder->select('driver_assignment.*, vehicle.vehicle_no as vehicle_number, staff.name as driver_name');
        $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');
        $builder->join('staff', 'staff.id = driver_assignment.driver', 'left');
        $builder->where('driver_assignment.id', $asign_id);
        return $builder->get()->getResult();
    }

    function staffadvance($from_date, $to_date)
    {
        if (empty($from_date)) {
            $from_date = date('Y-m-01');

        }
        // If $to_date is empty, set it to the current date
        if (empty($to_date)) {
            $to_date = date('Y-m-d');
        }
        // echo $to_date;exit;
        $builder = $this->db->table('staff_advance');
        $builder->select('staff_advance.*, location.location_name, staff.name, staff.staff_code, staff.user_type');
        $builder->join('location', 'location.location_id = staff_advance.location_id', 'left');
        $builder->join('staff', 'staff.id = staff_advance.staff_id', 'left');
        $builder->where('adv_date >=', $from_date);
        $builder->where('adv_date <=', $to_date);
        return $builder->get()->getResult();
    }

    function regularcheckup()
    {
        $builder = $this->db->table('vehicle_maintenance');
        $builder->select('vehicle_maintenance.*, vehicle.vehicle_no');
        $builder->join('vehicle', 'vehicle.id = vehicle_maintenance.vehicle_no', 'left');
        return $builder->get()->getResult();
    }

    // 	function items()
// 	{
// 		$builder = $this->db->table('items');
// 		$builder->select('items.*,stock.quantity');
// 		$builder->join('stock', 'items.id = stock.sproduct_id','left');
// 		return $builder->get()->getResult();
// 	}
    public function items()
    {
        // Subquery for stock_in_store (total stock quantity per product)
        $stockBuilder = $this->db->table('stock')
            ->select('sproduct_id, SUM(quantity) as stock_in_store')
            ->groupBy('sproduct_id');
        $stockSubquery = $stockBuilder->getCompiledSelect();

        // Subquery for stock_out_store (total maintenance quantity per product)
        $maintenanceBuilder = $this->db->table('inhouse_maintenance')
            ->select('item, SUM(qty) as stock_out_store')
            ->groupBy('item');
        $maintenanceSubquery = $maintenanceBuilder->getCompiledSelect();

        // Main query joining items with subqueries
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            COALESCE(s.stock_in_store, 0) as stock_in_store,
            COALESCE(m.stock_out_store, 0) as stock_out_store,
            (COALESCE(s.stock_in_store, 0) - COALESCE(m.stock_out_store, 0)) as available_stock
        ');
        $builder->join("($stockSubquery) s", 's.sproduct_id = items.id', 'left');
        $builder->join("($maintenanceSubquery) m", 'm.item = items.id', 'left');

        return $builder->get()->getResult();
    }

    /**
     * Items with location-wise available qty (admin/Purchaseentry getItemsDetails).
     *
     * @return list<object>
     */
    public function getPurchaseItemsByLocation(int $locationId, bool $onlyAvailable = false): array
    {
        if ($locationId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                i.id,
                i.item_id,
                i.item_name,
                i.amount,
                i.unit_id,
                u.unit_name,
                (
                    COALESCE((
                        SELECT SUM(s.quantity)
                        FROM stock s
                        WHERE s.sproduct_id = i.id
                        AND s.location_id = ?
                    ), 0)
                    -
                    COALESCE((
                        SELECT SUM(im.qty)
                        FROM inhouse_maintenance im
                        WHERE im.item = i.id
                        AND im.location = ?
                    ), 0)
                ) AS available_qty
            FROM items i
            LEFT JOIN units u ON u.unit_id = i.unit_id
        ";

        if ($onlyAvailable) {
            $sql .= ' HAVING available_qty > 0';
        }

        $sql .= ' ORDER BY i.item_name ASC';

        return $this->db->query($sql, [$locationId, $locationId])->getResult();
    }

    public function getNextStockCode(): int
    {
        $row = $this->db->query(
            'SELECT MAX(CAST(stock_code AS UNSIGNED)) AS max_code FROM stock'
        )->getRow();
        $max = (int) ($row->max_code ?? 0);

        return $max > 0 ? $max + 1 : 1;
    }

    /**
     * Insert purchase stock rows (admin/Inserpurchasetstock).
     *
     * @param array{supplier_id:int,location_id:int,invoice_date:string,invoice_no?:string,remarks?:string} $header
     * @param list<array{product_id:int,qty:float,rate:float,item_name?:string}> $lineItems
     *
     * @return array{stock_code:int,total_amount:float,items: list<array<string,mixed>>}
     */
    public function storePurchaseStock(array $header, array $lineItems, ?string $billPhoto = null): array
    {
        $stockCode   = $this->getNextStockCode();
        $totalAmount = 0.0;
        $savedItems  = [];

        foreach ($lineItems as $line) {
            $lineTotal = (float) $line['qty'] * (float) $line['rate'];
            $totalAmount += $lineTotal;

            $data = [
                'stock_code'     => $stockCode,
                'sproduct_id'    => (int) $line['product_id'],
                'date'           => $header['invoice_date'],
                'supplier_id'    => (int) $header['supplier_id'],
                'invoice_date'   => $header['invoice_date'],
                'quantity'       => (float) $line['qty'],
                'available_qty'  => (float) $line['qty'],
                'rate'           => (float) $line['rate'],
                'amount'         => $lineTotal,
                'gst_amount'     => $totalAmount,
                'invoice_number' => $header['invoice_no'] ?? '',
                'location_id'    => (int) $header['location_id'],
                'bill_photo'     => $billPhoto,
                'remarks'        => $header['remarks'] ?? '',
            ];

            $this->db->table('stock')->insert($data);

            $savedItems[] = [
                'product_id' => (int) $line['product_id'],
                'item_name'  => $line['item_name'] ?? null,
                'qty'        => (float) $line['qty'],
                'rate'       => (float) $line['rate'],
                'amount'     => $lineTotal,
            ];
        }

        return [
            'stock_code'   => $stockCode,
            'total_amount' => $totalAmount,
            'items'        => $savedItems,
        ];
    }

    /**
     * Update an existing purchase batch (admin/edit_stock + finalize_edit_stock).
     *
     * @param list<array{stock_id?:int,product_id:int,qty:float,rate:float,item_name?:string}> $lineItems
     *
     * @return array{stock_code:int,total_amount:float,items:list<array<string,mixed>>}
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function updatePurchaseStock(int $stockCode, array $header, array $lineItems, ?string $billPhoto = null): array
    {
        if ($stockCode <= 0) {
            throw new \InvalidArgumentException('Valid stock_code is required.');
        }

        $existingRows = $this->db->table('stock')
            ->where('stock_code', $stockCode)
            ->get()
            ->getResult();

        if ($existingRows === []) {
            throw new \RuntimeException('Purchase voucher not found.');
        }

        $invoiceNo = trim((string) ($existingRows[0]->invoice_number ?? ''));
        if (str_starts_with($invoiceNo, 'stock-trans')) {
            throw new \RuntimeException('Stock transfer batches cannot be edited.');
        }

        $existingById = [];
        foreach ($existingRows as $row) {
            $existingById[(int) $row->stock_id] = $row;
        }

        $keptStockIds = [];
        $savedItems   = [];
        $totalAmount  = 0.0;

        foreach ($lineItems as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $qty       = (float) ($line['qty'] ?? 0);
            $rate      = (float) ($line['rate'] ?? 0);
            $stockId   = (int) ($line['stock_id'] ?? 0);

            if ($productId <= 0 || $qty <= 0 || $rate < 0) {
                throw new \InvalidArgumentException('Each item must have product_id, qty > 0, and rate >= 0.');
            }

            $lineTotal = $qty * $rate;
            $totalAmount += $lineTotal;

            $rowData = [
                'sproduct_id'    => $productId,
                'date'           => $header['invoice_date'],
                'supplier_id'    => (int) $header['supplier_id'],
                'invoice_date'   => $header['invoice_date'],
                'quantity'       => $qty,
                'rate'           => $rate,
                'amount'         => $lineTotal,
                'invoice_number' => $header['invoice_no'] ?? '',
                'location_id'    => (int) $header['location_id'],
                'remarks'        => $header['remarks'] ?? '',
            ];

            if ($billPhoto !== null && $billPhoto !== '') {
                $rowData['bill_photo'] = $billPhoto;
            }

            if ($stockId > 0 && isset($existingById[$stockId])) {
                $existing = $existingById[$stockId];
                $issued   = (float) $existing->quantity - (float) $existing->available_qty;
                if ($qty + 0.00001 < $issued) {
                    throw new \InvalidArgumentException(
                        'Quantity cannot be less than already issued qty for stock_id ' . $stockId . '.'
                    );
                }

                $rowData['available_qty'] = $qty - $issued;
                $this->db->table('stock')->where('stock_id', $stockId)->update($rowData);
                $keptStockIds[] = $stockId;

                $savedItems[] = [
                    'stock_id'   => $stockId,
                    'product_id' => $productId,
                    'item_name'  => $line['item_name'] ?? null,
                    'qty'        => $qty,
                    'rate'       => $rate,
                    'amount'     => $lineTotal,
                ];
                continue;
            }

            if ($stockId > 0) {
                throw new \InvalidArgumentException('Invalid stock_id for this purchase batch: ' . $stockId);
            }

            $rowData['stock_code']     = $stockCode;
            $rowData['available_qty']  = $qty;
            $rowData['gst_amount']     = 0;
            if ($billPhoto === null || $billPhoto === '') {
                $rowData['bill_photo'] = $existingRows[0]->bill_photo ?? null;
            }

            $this->db->table('stock')->insert($rowData);
            $newStockId = (int) $this->db->insertID();
            $keptStockIds[] = $newStockId;

            $savedItems[] = [
                'stock_id'   => $newStockId,
                'product_id' => $productId,
                'item_name'  => $line['item_name'] ?? null,
                'qty'        => $qty,
                'rate'       => $rate,
                'amount'     => $lineTotal,
            ];
        }

        foreach ($existingById as $stockId => $existing) {
            if (in_array($stockId, $keptStockIds, true)) {
                continue;
            }

            $issued = (float) $existing->quantity - (float) $existing->available_qty;
            if ($issued > 0.00001) {
                throw new \InvalidArgumentException(
                    'Cannot remove item stock_id ' . $stockId . ' — quantity already issued from stock.'
                );
            }

            $this->db->table('stock')->where('stock_id', $stockId)->delete();
        }

        $headerUpdate = ['gst_amount' => $totalAmount];
        if ($billPhoto !== null && $billPhoto !== '') {
            $headerUpdate['bill_photo'] = $billPhoto;
        }
        $this->db->table('stock')
            ->where('stock_code', $stockCode)
            ->update($headerUpdate);

        return [
            'stock_code'   => $stockCode,
            'total_amount' => $totalAmount,
            'items'        => $savedItems,
        ];
    }


    function dieseldata($from_date, $to_date)
    {
        // If $from_date is empty, set it to the first day of the current month
        if (empty($from_date)) {
            $from_date = date('Y-m-01');
        }

        // If $to_date is empty, set it to the current date
        if (empty($to_date)) {
            $to_date = date('Y-m-d');
        }


        $builder = $this->db->table('diselentry');
        $builder->select('diselentry.*,vendor.name as vendor_name, vehicle.vehicle_no ');
        $builder->join('vendor', 'vendor.id = diselentry.vendor_id', 'left');
        $builder->join('vehicle', 'vehicle.id = diselentry.vehicle_id', 'left');

        $builder->where('diesel_date >=', $from_date);
        $builder->where('diesel_date <=', $to_date);
        $builder->where('diselentry.deleted_by', Null);

        return $builder->get()->getResult();
    }

    function extra_diesel_issue($from_date = null, $to_date = null, $vehicle_id = null, $driver_id = null)
    {
        if (empty($from_date))
            $from_date = date('Y-m-01');
        if (empty($to_date))
            $to_date = date('Y-m-d');

        $builder = $this->db->table('extra_diesel_issue');
        $builder->select('extra_diesel_issue.*, vehicle.vehicle_no as truck_no, staff.name as driver_name, user.full_name as issued_by_name');
        $builder->join('vehicle', 'vehicle.id = extra_diesel_issue.vehicle_id', 'left');
        $builder->join('staff', 'staff.id = extra_diesel_issue.driver_id', 'left');
        $builder->join('user', 'user.id = extra_diesel_issue.issued_by', 'left');
        $builder->where('issue_date >=', $from_date);
        $builder->where('issue_date <=', $to_date);
        if (!empty($vehicle_id))
            $builder->where('extra_diesel_issue.vehicle_id', $vehicle_id);
        if (!empty($driver_id))
            $builder->where('extra_diesel_issue.driver_id', $driver_id);
        $builder->where('extra_diesel_issue.deleted_by', null);
        return $builder->get()->getResult();
    }

    function passenger_vehicle_diesel($from_date = null, $to_date = null, $vehicle_id = null, $location_id = null)
    {
        if (empty($from_date))
            $from_date = date('Y-m-01');
        if (empty($to_date))
            $to_date = date('Y-m-d');

        $builder = $this->db->table('passenger_vehicle_diesel');
        $builder->select('passenger_vehicle_diesel.*, vehicle.vehicle_no as truck_no, location.location_name, user.full_name as issued_by_name');
        $builder->join('vehicle', 'vehicle.id = passenger_vehicle_diesel.vehicle_id', 'left');
        $builder->join('location', 'location.location_id = passenger_vehicle_diesel.location_id', 'left');
        $builder->join('user', 'user.id = passenger_vehicle_diesel.issued_by', 'left');
        $builder->where('entry_date >=', $from_date);
        $builder->where('entry_date <=', $to_date);
        if (!empty($vehicle_id))
            $builder->where('passenger_vehicle_diesel.vehicle_id', $vehicle_id);
        if (!empty($location_id))
            $builder->where('passenger_vehicle_diesel.location_id', $location_id);
        $builder->where('passenger_vehicle_diesel.deleted_by', null);
        return $builder->get()->getResult();
    }

    function diesel_rate_master()
    {
        return $this->db->table('diesel_rate_master')
            ->orderBy('from_date', 'DESC')
            ->get()
            ->getResult();
    }

    function get_diesel_rate_master($from_date = null, $to_date = null)
    {
        $builder = $this->db->table('diesel_rate_master');
        if (!empty($from_date))
            $builder->where('from_date >=', $from_date);
        if (!empty($to_date))
            $builder->where('to_date <=', $to_date);
        $builder->orderBy('from_date', 'DESC');
        return $builder->get()->getResult();
    }

    function purcartdetails($user_id)
    {
        $builder = $this->db->table('cart');
        $builder->select('cart.cart_id,cart.invoicedate, cart.sup-cust_id as supplier_id ,items.id as product_id, cart.location, items.item_name, cart.rate as rate, invoiceno, units.unit_name, cart.qty,   cart.user_id, vendor.name as supplier_name, location.location_name');
        $builder->where('user_id', $user_id);
        $builder->where('cart_type', 1);
        $builder->join('items', 'items.id = cart.product_id');
        $builder->join('units', 'items.unit_id = units.unit_id');
        $builder->join('vendor', 'vendor.id = cart.`sup-cust_id`', 'left');
        $builder->join('location', 'location.location_id = cart.location', 'left');
        return $builder->get()->getResult();
    }
    function stockTransferdetails($user_id)
    {
        $builder = $this->db->table('cart');
        $builder->select('cart.cart_id,cart.invoicedate, cart.sup-cust_id as supplier_id ,items.id as product_id, location, items.item_name, cart.rate as rate, invoiceno, units.unit_name, cart.qty,   cart.user_id,cart.to_location ');
        $builder->where('user_id', $user_id);
        $builder->where('cart_type', 3);
        $builder->join('items', 'items.id = cart.product_id');
        $builder->join('units', 'items.unit_id = units.unit_id');
        return $builder->get()->getResult();
    }
    function out_side($from_date, $to_date)
    {
        // If $from_date is empty, set it to the first day of the current month
        if (empty($from_date)) {
            $from_date = date('Y-m-01');
        }

        // If $to_date is empty, set it to the current date
        if (empty($to_date)) {
            $to_date = date('Y-m-d');
        }
        $builder = $this->db->table('outside_maintenance');
        $builder->select('outside_maintenance.*,vehicle.vehicle_no, vendor.name, location.location_name');
        $builder->join('vehicle', 'vehicle.id = outside_maintenance.vehicle_id', 'left');
        $builder->join('vendor', 'vendor.id = outside_maintenance.vendor_id', 'left');
        $builder->join('location', 'location.location_id = outside_maintenance.location_id', 'left');
        $builder->where('date >=', $from_date);
        $builder->where('date <=', $to_date);
        $builder->where('outside_maintenance.deleted_by', Null);
        return $builder->get()->getResult();
    }

    function doregistration_dtls()
    {
        $builder = $this->db->table('do_registration');
        $builder->select('do_registration.*,route.location_shortname, created_by_user.full_name AS created_by_name, updated_by_user.full_name AS updated_by_name');
        $builder->join('route', 'route.id = do_registration.route_id', 'left');
        $builder->join('user AS created_by_user', 'created_by_user.id = do_registration.created_by', 'left');
        $builder->join('user AS updated_by_user', 'updated_by_user.id = do_registration.updated_by', 'left');
        $builder->where('do_registration.deleted_by', null);
        return $builder->get()->getResult();
    }
    public function partyNames()
    {
        $builder = $this->db->table('vendor');
        $builder->select('*');
        $builder->where('type', 'Party');
        return $builder->get()->getResult();
    }


    function bank()
    {
        $builder = $this->db->table('bank');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function dosingleregistration_dtls($doreg_id)
    {
        $builder = $this->db->table('do_registration');
        $builder->select('*');
        $builder->join('route', 'route.id = do_registration.route_id', 'left');
        $builder->where('do_registration.do_registration_id', $doreg_id);
        return $builder->get()->getResult();
    }

    // function tonnage_dtls()
    // {
    //     $builder = $this->db->table('tonnage');
    //     $builder->select('tonnage.*, created_by_user.full_name AS created_by_name, updated_by_user.full_name AS updated_by_name');
    //     $builder->join('user AS created_by_user', 'created_by_user.id = tonnage.created_by', 'left');
    //     $builder->join('user AS updated_by_user', 'updated_by_user.id = tonnage.updated_by', 'left');
    //     $builder->where('tonnage.deleted_by', null);
    //     return $builder->get()->getResult();
    // }

    // function tonnage_dtls()
    // {
    //     $builder = $this->db->table('tonnage');

    //     $builder->select('
    //         tonnage.*,
    //         set_master.set_name,
    //         created_by_user.full_name AS created_by_name,
    //         updated_by_user.full_name AS updated_by_name
    //     ');

    //     // 🔹 Join set_master to get set_name
    //     $builder->join(
    //         'set_master',
    //         'set_master.id = tonnage.set_id',
    //         'left'
    //     );

    //     // 🔹 Created / Updated users
    //     $builder->join(
    //         'user AS created_by_user',
    //         'created_by_user.id = tonnage.created_by',
    //         'left'
    //     );

    //     $builder->join(
    //         'user AS updated_by_user',
    //         'updated_by_user.id = tonnage.updated_by',
    //         'left'
    //     );

    //     $builder->where('tonnage.deleted_by', null);

    //     return $builder->get()->getResult();
    // }

    function tonnage_dtls()
    {
        $builder = $this->db->table('tonnage');

        $builder->select('
            tonnage.*,
            set_master.set_name,
            created_by_user.full_name AS created_by_name,
            updated_by_user.full_name AS updated_by_name
        ');

        // 🔹 Join set_master to get set_name
        $builder->join(
            'set_master',
            'set_master.id = tonnage.set_id',
            'left'
        );

        // 🔹 Created / Updated users
        $builder->join(
            'user AS created_by_user',
            'created_by_user.id = tonnage.created_by',
            'left'
        );

        $builder->join(
            'user AS updated_by_user',
            'updated_by_user.id = tonnage.updated_by',
            'left'
        );

        // ✅ FIX: Proper NULL check
        $builder->where('tonnage.deleted_by IS NULL', null, false);

        return $builder->get()->getResult();
    }



    function single_tonnage($id)
    {
        $builder = $this->db->table('tonnage');
        $builder->select('*');
        $builder->where('id', $id);
        return $builder->get()->getRow();
    }

    // Set Master Functions
    function set_dtls()
    {
        $builder = $this->db->table('set_master');
        $builder->select('set_master.*, created_by_user.full_name AS created_by_name, updated_by_user.full_name AS updated_by_name');
        $builder->join('user AS created_by_user', 'created_by_user.id = set_master.created_by', 'left');
        $builder->join('user AS updated_by_user', 'updated_by_user.id = set_master.updated_by', 'left');
        $builder->where('set_master.deleted_by', null);
        $builder->orderBy('set_master.created_at', 'DESC');
        return $builder->get()->getResult();
    }

    function single_set($id)
    {
        $builder = $this->db->table('set_master');
        $builder->select('*');
        $builder->where('id', $id);
        return $builder->get()->getRow();
    }

    function tonnage_by_set($set_id)
    {
        $builder = $this->db->table('tonnage');
        $builder->select('tonnage.*, created_by_user.full_name AS created_by_name, updated_by_user.full_name AS updated_by_name, set_master.set_name');
        $builder->join('user AS created_by_user', 'created_by_user.id = tonnage.created_by', 'left');
        $builder->join('user AS updated_by_user', 'updated_by_user.id = tonnage.updated_by', 'left');
        $builder->join('set_master', 'set_master.id = tonnage.set_id', 'left');
        $builder->where('tonnage.set_id', $set_id);
        $builder->where('tonnage.deleted_by', null);
        $builder->orderBy('tonnage.min', 'ASC');
        return $builder->get()->getResult();
    }

    function all_tonnage()
    {
        $builder = $this->db->table('tonnage');
        $builder->select('tonnage.*, created_by_user.full_name AS created_by_name, updated_by_user.full_name AS updated_by_name, set_master.set_name');
        $builder->join('user AS created_by_user', 'created_by_user.id = tonnage.created_by', 'left');
        $builder->join('user AS updated_by_user', 'updated_by_user.id = tonnage.updated_by', 'left');
        $builder->join('set_master', 'set_master.id = tonnage.set_id', 'left');
        $builder->where('tonnage.deleted_by', null);
        $builder->where('set_master.deleted_by', null);
        $builder->orderBy('set_master.set_name', 'ASC');
        $builder->orderBy('tonnage.min', 'ASC');
        return $builder->get()->getResult();
    }

    function all_sets()
    {
        $builder = $this->db->table('set_master');
        $builder->select('*');
        $builder->where('deleted_by', null);
        $builder->orderBy('set_name', 'ASC');
        return $builder->get()->getResult();
    }

    function sets_with_range_count()
    {
        $builder = $this->db->table('set_master');
        $builder->select('set_master.*, COUNT(tonnage.id) AS range_count');
        $builder->join('tonnage', 'tonnage.set_id = set_master.id AND tonnage.deleted_by IS NULL', 'left');
        $builder->where('set_master.deleted_by', null);
        $builder->groupBy('set_master.id');
        $builder->orderBy('set_master.set_name', 'ASC');
        return $builder->get()->getResult();
    }

    public function stock_dtls()
    {
        return $this->getPurchaseVoucherList();
    }

    /**
     * Purchase voucher list grouped by stock_code (admin/Purchase_Voucher → Allstock_vw).
     *
     * @param array{
     *     stock_code?: int,
     *     location_id?: int,
     *     supplier_id?: int,
     *     from_date?: string,
     *     to_date?: string,
     *     search?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getPurchaseVoucherList(array $filters = []): array
    {
        $builder = $this->db->table('stock');
        $builder->select(
            'MAX(stock.stock_id) AS stock_id, MAX(stock.date) AS date,'
            . ' MAX(location.location_name) AS location_name, MAX(location.location_id) AS location_id,'
            . ' MAX(stock.invoice_number) AS invoice_number, MAX(vendor.id) AS supplier_id,'
            . ' MAX(vendor.name) AS supplier_name, stock.stock_code,'
            . ' SUM(stock.quantity) AS total_quantity,'
            . ' SUM(stock.gst_amount) AS total_gst_amount,'
            . ' MAX(stock.bill_photo) AS bill_photo, MAX(stock.remarks) AS remarks'
        );
        $builder->join('items', 'items.id = stock.sproduct_id', 'left');
        $builder->join('units', 'units.unit_id = items.unit_id', 'left');
        $builder->join('location', 'location.location_id = stock.location_id', 'left');
        $builder->join('vendor', 'vendor.id = stock.supplier_id', 'left');

        $stockCode = (int) ($filters['stock_code'] ?? 0);
        if ($stockCode > 0) {
            $builder->where('stock.stock_code', $stockCode);
        }

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('stock.location_id', $locationId);
        }

        $supplierId = (int) ($filters['supplier_id'] ?? 0);
        if ($supplierId > 0) {
            $builder->where('stock.supplier_id', $supplierId);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('stock.date >=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('stock.date <=', $toDate);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('stock.invoice_number', $search)
                ->orLike('vendor.name', $search)
                ->orLike('location.location_name', $search)
                ->orLike('stock.stock_code', $search)
            ->groupEnd();
        }

        // Regular purchases: group by stock_code + invoice + date.
        // Stock transfers keep one row per stock_code (two invoice numbers per batch).
        $builder->groupBy(
            'stock.stock_code,'
            . " CASE WHEN stock.invoice_number LIKE 'stock-trans%'"
            . " THEN '' ELSE COALESCE(stock.invoice_number, '') END,"
            . " CASE WHEN stock.invoice_number LIKE 'stock-trans%'"
            . " THEN '1970-01-01' ELSE stock.date END",
            false
        );

        $builder->orderBy('MAX(stock.date)', 'DESC', false);

        return $builder->get()->getResult();
    }



    function singleStock($invoiceno)
    {
        $builder = $this->db->table('stock');
        $builder->select('stock.*,items.item_name, items.item_id,vendor.name,units.unit_name');
        $builder->join('items', 'items.id = stock.sproduct_id', 'left');
        $builder->join('units', 'units.unit_id = items.unit_id', 'left');
        $builder->join('vendor', 'vendor.id = stock.supplier_id', 'left');
        $builder->where('stock_code', $invoiceno);
        $builder->orderBy('date', 'DESC');
        return $builder->get()->getResult();
    }

    /**
     * Delete all stock rows for a purchase batch (admin/delete_stock).
     */
    public function deletePurchaseByStockCode(int $stockCode): int
    {
        if ($stockCode <= 0) {
            return 0;
        }

        $this->db->table('stock')->where('stock_code', $stockCode)->delete();

        return $this->db->affectedRows();
    }


    function stockdata($segment)
    {
        $builder = $this->db->table('stock');
        $builder->select('stock.*,location.location_name, location.location_id,items.item_name, items.id as items_id,vendor.id as supplier_id,vendor.name as supplier_name,units.unit_name');
        $builder->join('items', 'items.id = stock.sproduct_id', 'left');
        $builder->join('units', 'units.unit_id = items.unit_id', 'left');
        $builder->join('vendor', 'vendor.id = stock.supplier_id', 'left');
        $builder->join('location', 'location.location_id = stock.location_id', 'left');
        $builder->where('stock_code', $segment);
        $builder->orderBy('date', 'DESC');
        return $builder->get()->getResult();
    }


    function Get_vendor()
    {
        // Main query builder
        $builder = $this->db->table('vendor');
        $builder->select('vendor.*, location.location_name, vendor_rate.vendor_rate, vendor_rate.from_date');
        $builder->join('location', 'location.location_id = vendor.location', 'left');

        // Subquery to get the latest vendor_rate for each vendor
        $subquery = $this->db->table('vendor_rate as vr1')
            ->select('vr1.vendor_id, vr1.vendor_rate, vr1.from_date')
            ->join(
                '(SELECT vendor_id, MAX(from_date) as max_date FROM vendor_rate GROUP BY vendor_id) as vr2',
                'vr1.vendor_id = vr2.vendor_id AND vr1.from_date = vr2.max_date',
                'inner',
                false
            );

        // Convert subquery to string
        $subquerySql = $subquery->getCompiledSelect(false); // Use false to prevent adding parentheses

        // Join subquery with the main query
        $builder->join("($subquerySql) as vendor_rate", 'vendor_rate.vendor_id = vendor.id', 'left');

        // Group by vendor.id to ensure each vendor appears only once
        $builder->groupBy('vendor.id');

        return $builder->get()->getResult();
    }



    // function itemdtls(){
// 	$builder = $this->db->table('items');
// 	$builder->select('items.*, units.unit_id, units.unit_name');
// 	$builder->join('units', 'items.unit_id = units.unit_id', 'left');
// 	return $builder->get()->getResult();
// }
    public function itemdtls()
    {
        // Subquery for stock_in_store
        $stockBuilder = $this->db->table('stock')
            ->select('sproduct_id, SUM(quantity) as stock_in_store')
            ->groupBy('sproduct_id');
        $stockSubquery = $stockBuilder->getCompiledSelect();

        // Subquery for stock_out_store (from inhouse_maintenance)
        $maintenanceBuilder = $this->db->table('inhouse_maintenance')
            ->select('item, SUM(qty) as stock_out_store')
            ->groupBy('item');
        $maintenanceSubquery = $maintenanceBuilder->getCompiledSelect();

        // Main query with joins
        $builder = $this->db->table('items');
        $builder->select('
        items.*,
        units.unit_id,
        units.unit_name,
        COALESCE(s.stock_in_store, 0) as stock_in_store,
        COALESCE(m.stock_out_store, 0) as stock_out_store,
        (COALESCE(s.stock_in_store, 0) - COALESCE(m.stock_out_store, 0)) as available_stock
    ');
        $builder->join('units', 'items.unit_id = units.unit_id', 'left');
        $builder->join("($stockSubquery) s", 's.sproduct_id = items.id', 'left');
        $builder->join("($maintenanceSubquery) m", 'm.item = items.id', 'left');

        return $builder->get()->getResult();
    }




    function Getvehicle()
    {
        $builder = $this->db->table('vehicle');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function Getvehicle_details($from_date, $to_date, $type)
    {
        $builder = $this->db->table('vehicle');
        $builder->select('vehicle.*, location.location_name, vehicle_types.type_name');
        $builder->join('location', 'location.location_id = vehicle.location_id', 'left');
        $builder->join('vehicle_types', 'vehicle_types.id = vehicle.vehicle_type', 'left');

        if ($type != '') {
            // Add conditions for expiring within the date range
            if ($type == 'road_tax') {
                $builder->groupStart();
                $builder->where('tax_exp_date >=', $from_date);
                $builder->where('tax_exp_date <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('tax_exp_date <', $from_date); // Expired
            }

            if ($type == 'fitness') {
                $builder->groupStart();
                $builder->where('fitness_exp_date >=', $from_date);
                $builder->where('fitness_exp_date <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('fitness_exp_date <', $from_date); // Expired
            }

            if ($type == 'insurance') {
                $builder->groupStart();
                $builder->where('ins_exp_date >=', $from_date);
                $builder->where('ins_exp_date <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('ins_exp_date <', $from_date); // Expired
            }

            if ($type == 'permit') {
                $builder->groupStart();
                $builder->where('permit_exp_date >=', $from_date);
                $builder->where('permit_exp_date <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('permit_exp_date <', $from_date); // Expired
            }

            if ($type == 'npermit') {
                $builder->groupStart();
                $builder->where('npermit_exp_date >=', $from_date);
                $builder->where('npermit_exp_date <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('npermit_exp_date <', $from_date); // Expired
            }

            if ($type == 'amc') {
                $builder->groupStart();
                $builder->where('amc_expary >=', $from_date);
                $builder->where('amc_expary <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('amc_expary <', $from_date); // Expired
            }

            if ($type == 'ims') {
                $builder->groupStart();
                $builder->where('i3ms_expary >=', $from_date);
                $builder->where('i3ms_expary <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('i3ms_expary <', $from_date); // Expired
            }

            if ($type == 'Khanij') {
                $builder->groupStart();
                $builder->where('khanij_expiri >=', $from_date);
                $builder->where('khanij_expiri <=', $to_date);
                $builder->groupEnd();
                $builder->orWhere('khanij_expiri <', $from_date); // Expired
            }
        }
        $builder->orderBy('vehicle.id', 'DESC');
        return $builder->get()->getResult();
    }



    function getItem()
    {
        $builder = $this->db->table('items');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function additem($data)
    {
        $query = $this->db->table('item')->insert($data);
        return $query;
    }
    function add_adjust_salary($data)
    {
        $query = $this->db->table('adjust_salary')->insert($data);
        return $query;
    }

    // Function to get driver details by ID
    function get_driver_by_id($driver_id)
    {
        return $this->db->table('staff')
            ->where('id', $driver_id)
            ->get()
            ->getRow();
    }

    // Function to get location details by ID
    function get_location_by_id($location_id)
    {
        return $this->db->table('location')
            ->where('location_id', $location_id)
            ->get()
            ->getRow();
    }

    public function showadjust_salary($year = null, $month = null)
    {
        $builder = $this->db->table('adjust_salary');
        $builder->select('adjust_salary.*, vehicle.vehicle_no');
        $builder->join('vehicle', 'vehicle.id = adjust_salary.vehicle_id', 'left');

        if (!empty($year)) {
            $builder->where('YEAR(from_date)', $year);
        }
        if (!empty($month)) {
            $builder->where('MONTH(from_date)', $month);
        }
        return $builder->get()->getResult();
    }

    public function delete_adjust_salary($id)
    {
        return $this->db->table('adjust_salary')->where('id', $id)->delete();
    }
    // public function get_adjust_salary_by_id($id)
// {
//     return $this->db->table('adjust_salary')->where('id', $id)->get()->getRow();
// }
    public function salarydata($salary_id)
    {
        // Assuming you are using a query builder or raw query to fetch the salary adjustment data
        return $this->db->table('adjust_salary')
            ->where('id', $salary_id)
            ->get()
            ->getRowArray(); // This returns the result as an associative array
    }

    public function update_salary_adjustment($id, $data)
    {
        return $this->db->table('adjust_salary')
            ->where('id', $id)
            ->update($data); // This returns true if successful, false if not
    }

    public function Getallvehicle()
    {
        $builder = $this->db->table('vehicle');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function userdata($user_id)
    {
        $builder = $this->db->table('user');
        $builder->select('*');
        $builder->where('id', $user_id);
        return $builder->get()->getResult();
    }
    function Customerdata()
    {
        $builder = $this->db->table('user');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function Getcity($state_id)
    {
        $builder = $this->db->table('city');
        $builder->select('*');
        $builder->where('state_id', $state_id);
        return $builder->get()->getResult();
    }
    function GetAllcity()
    {
        $builder = $this->db->table('city');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function GetAllstate()
    {
        $builder = $this->db->table('state');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function getAllCms()
    {
        $builder = $this->db->table('cms');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function AddPages($data)
    {
        $query = $this->db->table('cms')->insert($data);
        return $query;
    }

    function DeleteCsm($pageId)
    {
        $query = $this->db->table('cms')->delete(array('id' => $pageId));
        return $query;
    }
    function single_page($page_id)
    {
        $builder = $this->db->table('cms');
        $builder->select('*');
        $builder->where('id', $page_id);
        return $builder->get()->getResult();
    }

    function UpdatePages($data, $pageId)
    {
        $query = $this->db->table('cms')->update($data, array('id' => $pageId));
        return $query;
    }

    function UpdateSetting($data, $settingid)
    {
        $query = $this->db->table('settingg')->update($data, array('settingg_id' => $settingid));
        return $query;
    }

    function UpdateProfile($data, $user_id)
    {
        $query = $this->db->table('user')->update($data, array('id' => $user_id));
        return $query;
    }

    function despatch_dtls($from_date, $to_date)
    {
        // If $from_date is empty, set it to the first day of the current month
        if (empty($from_date)) {
            $from_date = date('Y-m-01');
        }

        // If $to_date is empty, set it to the current date
        if (empty($to_date)) {
            $to_date = date('Y-m-d');
        }

        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, vehicle.vehicle_no as vehicle_number, do_registration.do_no as doreg_no');
        $builder->join('vehicle', 'vehicle.id = despatch.vehicle_no', 'left');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');

        // Filter strictly by trip/despatch date (des_date), not created_at
        $builder->where('despatch.des_date >=', $from_date);
        $builder->where('despatch.des_date <=', $to_date);
        $builder->where('despatch.deleted_by IS NULL', null, false);
        $builder->where('despatch.deleted_at IS NULL', null, false);
        $builder->orderBy('despatch.des_date', 'ASC');
        $builder->orderBy('despatch.despatch_id', 'ASC');

        return $builder->get()->getResult();
    }

    function doregistration_dtls1($from_date = null, $to_date = null, $voucher_id = null)
    {
        $builder = $this->db->table('do_registration');
        $builder->select('do_registration.*, vendor.name as party_name');
        $builder->join('vendor', 'vendor.id = do_registration.party', 'left');

        if (!empty($voucher_id)) {
            $builder->join('despatch', 'despatch.do_no = do_registration.do_registration_id');
            $builder->where('despatch.voucher_id', $voucher_id);
            $builder->groupBy('do_registration.do_registration_id'); // Ensure unique DOs
        } else {
            // Only apply date filter if NO voucher is selected
            if (!empty($from_date) && !empty($to_date)) {
                $builder->groupStart()
                    ->where("do_registration.from_date BETWEEN '$from_date' AND '$to_date'")
                    ->orWhere("do_registration.to_date BETWEEN '$from_date' AND '$to_date'")
                    ->orWhere("(do_registration.from_date <= '$from_date' AND do_registration.to_date >= '$to_date')")
                    ->groupEnd();
            }
        }

        $builder->orderBy('do_registration_id', 'DESC');
        return $builder->get()->getResult();
    }

    // public function doregistration_dtls1($from_date = null, $to_date = null, $voucher_id = null)
    // {
    //     $builder = $this->db->table('do_registration');
    //     $builder->select('
    //         do_registration.*, 
    //         vendor.name as party_name,
    //         set_master.set_name as tonnage_set_name,
    //         route.from_city, 
    //         route.to_city, 
    //         route.location_id,
    //         location.location_name,
    //         location.location_shortname
    //     ');

    //     // Join related tables
    //     $builder->join('vendor', 'vendor.id = do_registration.party', 'left');
    //     $builder->join('set_master', 'set_master.id = do_registration.load_tonnage_id', 'left'); // <-- set name
    //     $builder->join('route', 'route.id = do_registration.route_id', 'left'); // <-- route info
    //     $builder->join('location', 'location.location_id = route.location_id', 'left'); // <-- location info

    //     // Voucher filter
    //     if (!empty($voucher_id)) {
    //         $builder->join('despatch', 'despatch.do_no = do_registration.do_registration_id', 'left');
    //         $builder->where('despatch.voucher_id', $voucher_id);
    //         $builder->groupBy('do_registration.do_registration_id'); // Ensure unique DOs
    //     } else {
    //         // Only apply date filter if no voucher is selected
    //         if (!empty($from_date) && !empty($to_date)) {
    //             $builder->groupStart()
    //                     ->where("do_registration.from_date BETWEEN '$from_date' AND '$to_date'")
    //                     ->orWhere("do_registration.to_date BETWEEN '$from_date' AND '$to_date'")
    //                     ->orWhere("(do_registration.from_date <= '$from_date' AND do_registration.to_date >= '$to_date')")
    //                     ->groupEnd();
    //         }
    //     }

    //     $builder->orderBy('do_registration_id', 'DESC');
    //     return $builder->get()->getResult();
    // }

    public function despatch_count($from_date = null, $to_date = null, $do_no = null, $chalan_status = null, $payment_status = null, $deposited_status = null, $voucher_id = null, $search = null)
    {
        $builder = $this->db->table('despatch');
        $builder->select('COUNT(*) as total');
        $builder->join('vehicle', 'vehicle.id = despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');
        $builder->where('despatch.deleted_by IS NULL');

        if (!empty($voucher_id)) {
            $builder->where('despatch.voucher_id', $voucher_id);
        }
        if (empty($voucher_id)) {
            if (!empty($from_date)) {
                $builder->where('des_date >=', $from_date);
            }
            if (!empty($to_date)) {
                $builder->where('des_date <=', $to_date);
            }
        }

        if (!empty($do_no)) {
            $builder->where('despatch.do_no', $do_no);
        }

        // Apply chalan_status filter
        if ($chalan_status == '1') {
            // Received
            $builder->where("(rest_amount IS NOT NULL AND rest_amount != '' AND rest_amount != 0)", null, false);
        } elseif ($chalan_status == '2') {
            // Not received
            $builder->where("(rest_amount IS NULL OR rest_amount = '' OR rest_amount = 0)", null, false);
        }

        // Payment status
        if ($payment_status === '1' || $payment_status === 1) {
            $builder->where('payment_status', 1);
        } elseif ($payment_status === '0' || $payment_status === 0) {
            $builder->where('payment_status', 0);
        }

        // Deposited status
        if ($deposited_status === '1') {
            $builder->where('deposited', 1);
        } elseif ($deposited_status === '0') {
            $builder->where('deposited', 0);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('vehicle.vehicle_no', $search)
                ->orLike('despatch.ref_no', $search)
                ->orLike('do_registration.do_no', $search)
                ->groupEnd();
        }

        $query = $builder->get();
        return $query->getRow()->total;
    }

    public function despatch_dtls1_paginated($from_date = null, $to_date = null, $do_no = null, $chalan_status = null, $payment_status = null, $deposited_status = null, $limit = 10, $offset = 0, $voucher_id = null, $search = null)
    {
        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, vehicle.vehicle_no as vehicle_number, do_registration.do_no as doreg_no, do_registration.rate, do_registration.shortage_qty as min_qty, do_registration.shortage_rate, do_registration.diesel_rate, do_registration.diesel_payment_type, do_registration.cash_type, do_registration.special_shortage, creator.full_name as made_by, COALESCE(do_registration.tds_percentage, 2.00) as tds_percentage, voucher.group_code');
        $builder->join('vehicle', 'vehicle.id = despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');
        $builder->join('voucher', 'voucher.id = despatch.voucher_id', 'left');
        $builder->join('activity_logs', "activity_logs.model_id = despatch.despatch_id AND activity_logs.action = 'create' AND (activity_logs.menu = 'despatch_entry' OR activity_logs.menu = 'insert_despatch_entry')", 'left');
        $builder->join('user as creator', 'creator.id = activity_logs.user_id', 'left');
        $builder->where('despatch.deleted_by IS NULL');

        if (!empty($voucher_id)) {
            $builder->where('despatch.voucher_id', $voucher_id);
        }
        if (empty($voucher_id)) {
            if (!empty($from_date)) {
                $builder->where('des_date >=', $from_date);
            }
            if (!empty($to_date)) {
                $builder->where('des_date <=', $to_date);
            }
        }

        if (!empty($do_no)) {
            $builder->where('despatch.do_no', $do_no);
        }

        // Apply chalan_status filter
        if ($chalan_status == '1') {
            // Received
            $builder->where("(rest_amount IS NOT NULL AND rest_amount != '' AND rest_amount != 0)", null, false);
        } elseif ($chalan_status == '2') {
            // Not received
            $builder->where("(rest_amount IS NULL OR rest_amount = '' OR rest_amount = 0)", null, false);
        }

        // Payment status
        if ($payment_status === '1' || $payment_status === 1) {
            $builder->where('payment_status', 1);
        } elseif ($payment_status === '0' || $payment_status === 0) {
            $builder->where('payment_status', 0);
        }

        // Deposited status
        if ($deposited_status === '1') {
            $builder->where('deposited', 1);
        } elseif ($deposited_status === '0') {
            $builder->where('deposited', 0);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('vehicle.vehicle_no', $search)
                ->orLike('despatch.ref_no', $search)
                ->orLike('do_registration.do_no', $search)
                ->groupEnd();
        }

        $builder->limit($limit, $offset);

        $results = $builder->get()->getResult();
        return $results ?? []; // ✅ Always return array, never null
    }
    public function despatch_dtls1($from_date = null, $to_date = null, $do_no = null, $chalan_status = null, $payment_status = null, $deposited_status = null, $limit = 10, $offset = 0, $voucher_id = null)
    {
        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, vehicle.vehicle_no as vehicle_number, do_registration.do_no as doreg_no, do_registration.rate, do_registration.shortage_qty as min_qty, do_registration.shortage_rate, do_registration.diesel_rate, do_registration.diesel_payment_type, do_registration.cash_type, do_registration.special_shortage, creator.full_name as made_by, COALESCE(do_registration.tds_percentage, 2.00) as tds_percentage, voucher.group_code');
        $builder->join('vehicle', 'vehicle.id = despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');
        $builder->join('voucher', 'voucher.id = despatch.voucher_id', 'left');
        $builder->join('activity_logs', "activity_logs.model_id = despatch.despatch_id AND activity_logs.action = 'create' AND (activity_logs.menu = 'despatch_entry' OR activity_logs.menu = 'insert_despatch_entry')", 'left');
        $builder->join('user as creator', 'creator.id = activity_logs.user_id', 'left');
        $builder->where('despatch.deleted_by IS NULL');

        if (!empty($voucher_id)) {
            $builder->where('despatch.voucher_id', $voucher_id);
        } else {
            if (!empty($from_date)) {
                $builder->where('des_date >=', $from_date);
            }
            if (!empty($to_date)) {
                $builder->where('des_date <=', $to_date);
            }
        }

        if (!empty($do_no)) {
            $builder->where('despatch.do_no', $do_no);
        }

        // Apply chalan_status filter
        if ($chalan_status == '1') {
            // Received
            $builder->where("(rest_amount IS NOT NULL AND rest_amount != '' AND rest_amount != 0)", null, false);
        } elseif ($chalan_status == '2') {
            // Not received
            $builder->where("(rest_amount IS NULL OR rest_amount = '' OR rest_amount = 0)", null, false);
        }

        // Payment status
        if ($payment_status === '1' || $payment_status === 1) {
            $builder->where('payment_status', 1);
        } elseif ($payment_status === '0' || $payment_status === 0) {
            $builder->where('payment_status', 0);
        }

        // Deposited status
        if ($deposited_status === '1') {
            $builder->where('deposited', 1);
        } elseif ($deposited_status === '0') {
            $builder->where('deposited', 0);
        }
        $results = $builder->get()->getResult();
        return $results ?? []; // ✅ Always return array, never null
    }

    public function getVouchersForDeposit($from_date = null, $to_date = null, $party = null, $voucher_no = null, $status = null)
    {
        $builder = $this->db->table('voucher');
        // Using MAX(vendor.name) to get a party name even if multiple despatches exist (they should have the same party now)
        $builder->select('voucher.*, SUM(despatch.net_amount) as total_net_amount, COUNT(despatch.despatch_id) as challan_count, MAX(vendor.name) as party_name');

        // Using LEFT JOINs to ensure we see the voucher even if some links are missing (though they shouldn't be)
        $builder->join('despatch', 'despatch.voucher_id = voucher.id', 'left');
        $builder->join('do_registration', 'despatch.do_no = do_registration.do_registration_id', 'left');
        // Using a more robust join that handles both numeric IDs and "Name (ID)" formatted strings
        $builder->join('vendor', 'vendor.id = do_registration.party OR vendor.id = SUBSTRING_INDEX(SUBSTRING_INDEX(do_registration.party, "(", -1), ")", 1)', 'left');

        if ($from_date && $to_date) {
            $builder->where('despatch.des_date >=', $from_date);
            $builder->where('despatch.des_date <=', $to_date);
        }

        if ($party) {
            $builder->where('do_registration.party', $party);
        }

        if ($voucher_no) {
            $builder->like('voucher.group_code', $voucher_no);
        }

        if ($status === 'deposited') {
            $builder->where("(voucher.deposit_date IS NOT NULL AND voucher.deposit_date != '0000-00-00' AND voucher.deposit_date != '')");
        } elseif ($status === 'not_deposited') {
            $builder->where("(voucher.deposit_date IS NULL OR voucher.deposit_date = '0000-00-00' OR voucher.deposit_date = '')");
        }

        $builder->groupBy('voucher.id');
        $builder->orderBy('voucher.id', 'DESC');
        return $builder->get()->getResult();
    }

    public function updateVoucher($id, $data)
    {
        return $this->db->table('voucher')->where('id', $id)->update($data);
    }




    function single_despatch_dtls($despatch_id)
    {
        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, vehicle.vehicle_no as vehicle_number, do_registration.do_no as doreg_no');
        $builder->join('vehicle', 'vehicle.id=despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id=despatch.do_no');
        $builder->where('despatch_id', $despatch_id);
        return $builder->get()->getResult();
    }

    function satutary_dtls()
    {
        $builder = $this->db->table('statutory');
        $builder->select('statutory.*, vehicle.vehicle_no');
        $builder->join('vehicle', 'vehicle.id=statutory.vehicle_id');
        return $builder->get()->getResult();
    }

    function getAllBlog()
    {
        $builder = $this->db->table('blog');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function AddBlog($data)
    {
        $query = $this->db->table('blog')->insert($data);
        return $query;
    }
    function Deletevehicle($vehicle_id)
    {
        $query = $this->db->table('blog')->delete(array('id' => $vehicle_id));
        return $query;
    }

    function singleBlog($blog_id)
    {
        $builder = $this->db->table('blog');
        $builder->select('*');
        $builder->where('blog_id', $blog_id);
        return $builder->get()->getResult();
    }
    function UpdateBlog($data, $blog_id)
    {
        $query = $this->db->table('blog')->update($data, array('blog_id' => $blog_id));
        return $query;
    }




    function GetSinglecity($city)
    {
        $builder = $this->db->table('city');
        $builder->select('*');
        $builder->where('city_id', $city);
        return $builder->get()->getResult();
    }

    function AddCity($data)
    {
        $query = $this->db->table('city')->insert($data);
        return $query;
    }

    function DeleteCity($stateId)
    {
        $query = $this->db->table('state')->delete(array('state_id' => $stateId));
        return $query;
    }
    function EditCity($data, $cid)
    {
        $query = $this->db->table('city')->update($data, array('city_id' => $cid));
        return $query;
    }


    function GetAllpin()
    {
        $builder = $this->db->table('pin');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function GetSinglePIN($PIN_ID)
    {
        $builder = $this->db->table('pin');
        $builder->select('*');
        $builder->where('pin_id', $PIN_ID);
        return $builder->get()->getResult();
    }

    function AddPIN($data)
    {
        $query = $this->db->table('pin')->insert($data);
        return $query;
    }

    function DeletePIN($pin_id)
    {
        $query = $this->db->table('pin')->delete(array('pin_id' => $pin_id));
        return $query;
    }
    function EditPIN($data, $pin_id)
    {
        $query = $this->db->table('pin')->update($data, array('pin_id' => $pin_id));
        return $query;
    }

    function GetCupon()
    {
        $builder = $this->db->table('coupon_code');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function AddCuponCode($data)
    {
        $query = $this->db->table('coupon_code')->insert($data);
        return $query;
    }

    function EditCuponCode($data, $cupn_id)
    {
        $query = $this->db->table('coupon_code')->update($data, array('coupon_code_id' => $cupn_id));
        return $query;
    }

    function deletecupon($cuponId)
    {
        $query = $this->db->table('coupon_code')->delete(array('coupon_code_id' => $cuponId));
        return $query;
    }
    function bannerdata()
    {
        $builder = $this->db->table('banner');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function addbanner($data)
    {

        $query = $this->db->table('banner')->insert($data);
        return $query;
    }

    function DeleteBanner($BannerId)
    {
        $query = $this->db->table('banner')->delete(array('banner_id' => $BannerId));
        return $query;
    }
    function single_bannerdata($banner_id)
    {
        $builder = $this->db->table('banner');
        $builder->select('*');
        $builder->where('banner_id', $banner_id);
        return $builder->get()->getResult();
    }
    function Editbanner($data, $banner_id)
    {
        $query = $this->db->table('banner')->update($data, array('banner_id' => $banner_id));
        return $query;
    }


    function getcategory()
    {
        $builder = $this->db->table('category');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function addCategory($data)
    {
        $query = $this->db->table('category')->insert($data);
        return $query;
    }
    function DeleteCategory($cat_id)
    {
        $query = $this->db->table('category')->delete(array('cat_id' => $cat_id));
        return $query;
    }
    function catsingle_data($cat_id)
    {
        $builder = $this->db->table('category');
        $builder->select('*');
        $builder->where('cat_id', $cat_id);
        return $builder->get()->getResult();
    }

    function updateCategory($data, $cid)
    {
        $query = $this->db->table('category')->update($data, array('cat_id' => $cid));
        return $query;
    }

    function GetAllCustomer($user_type)
    {
        $builder = $this->db->table('user');
        $builder->select('*');
        $builder->where('user_type', $user_type);
        $builder->where('deleted_by', null); // Correct syntax for IS NULL
        return $builder->get()->getResult();
    }




    function adduser($data)
    {
        $query = $this->db->table('user')->insert($data);
        return $query;
    }
    function deleteuser($user_id)
    {
        $query = $this->db->table('user')->delete(array('id' => $user_id));
        return $query;
    }
    function UserStatusActive($data, $user_id)
    {
        $query = $this->db->table('user')->update($data, array('id' => $user_id));
        return $query;
    }

    function updateUser($data, $id)
    {
        // echo'<pre>';
        // print_r($data);
        // exit;
        $query = $this->db->table('user')->update($data, array('id' => $id));
        return $query;
    }

    public function getUserById($table, $id)
    {
        return $this->db->table($table)->where('id', $id)->get()->getRowArray();
    }


    function GetAllService()
    {
        $builder = $this->db->table(' services');
        $builder->select('*');
        return $builder->get()->getResult();
    }
    function GetSingleService($service_id)
    {
        $builder = $this->db->table(' services');
        $builder->select('*');
        $builder->where('service_id', $service_id);
        return $builder->get()->getResult();
    }

    function GetServiceByCategory($category_id)
    {
        $builder = $this->db->table(' services');
        $builder->select('*');
        $builder->where('cat_id', $category_id);
        return $builder->get()->getResult();
    }
    function GetAllPrice($service_id)
    {
        $builder = $this->db->table('price');
        $builder->select('*');
        $builder->where('service_id', $service_id);
        return $builder->get()->getResult();
    }

    function Addtocart($data)
    {
        return $this->db
            ->table('cart')
            ->insert($data);
    }

    function RemoveFromCart($user_id, $service_id)
    {
        $builder = $this->db->table('cart');
        $builder->select('*');
        $builder->where('user_id', $user_id);
        $builder->where('product_id', $service_id);
        $builder->delete();
    }

    function Allcart($user_id)
    {
        $builder = $this->db->table('cart');
        $builder->select('*');
        $builder->where('user_id', $user_id);
        $builder->join('services', 'services.service_id=cart.product_id');
        return $builder->get()->getResult();
    }
    function GetAddress($customer_id)
    {
        $builder = $this->db->table('address');
        $builder->select('*');
        $builder->where('user_id', $customer_id);
        return $builder->get()->getResult();
    }
    function AddAddress($address)
    {
        return $this->db
            ->table('address')
            ->insert($address);
    }

    function addService($data)
    {

        $query = $this->db->table('services')->insert($data);
        return $query;
    }

    function ServiceStatus($data, $service_id)
    {
        $query = $this->db->table('services')->update($data, array('service_id' => $service_id));
        return $query;
    }
    function DeleteService($ServiceId)
    {
        $query = $this->db->table('services')->delete(array('service_id' => $ServiceId));
        return $query;
    }
    function UpdateService($data, $service_id)
    {
        $query = $this->db->table('services')->update($data, array('service_id' => $service_id));
        return $query;
    }


    public function orderdtl()
    {
        // Subquery to calculate the total amount from orders
        $ordersSubquery = $this->db->table('orders')
            ->select('order_id, SUM(qty * price) AS total_amount')
            ->groupBy('order_id')
            ->getCompiledSelect();

        // Subquery to calculate the additional service subtotal
        $additionalServiceSubquery = $this->db->table('additional_service')
            ->select('order_id, SUM(add_service_price * qtty) AS additional_service_subtotal')
            ->groupBy('order_id')
            ->getCompiledSelect();

        // Main query
        $builder = $this->db->table('orders o');
        $builder->select('o.*, os.total_amount, ass.additional_service_subtotal')
            ->join("($ordersSubquery) os", 'os.order_id = o.order_id', 'left')
            ->join("($additionalServiceSubquery) ass", 'ass.order_id = o.order_id', 'left')
            ->orderBy('o.created_date', 'DESC');

        $orders = $builder->get()->getResult();

        // Iterate through each order and fetch the address
        foreach ($orders as $order) {
            $address = $this->getsingleaddress($order->address_id);
            if ($address) {
                $order->address = $address;
            } else {
                $order->address = null; // Handle case where no address is found
            }
        }

        foreach ($orders as $order) {
            $customer = $this->db->query("SELECT contact_no, full_name, email FROM user WHERE id = ?", [$order->user_id])->getResult();
            if ($customer) {
                $order->customer = $customer;
            } else {
                $customer->customer = null; // Handle case where no address is found
            }
        }

        foreach ($orders as $order) {
            $technician = $this->db->query("SELECT contact_no, full_name, email FROM user WHERE id = ?", [$order->vendor_id])->getResult();
            if ($technician) {
                $order->technician = $technician;
            } else {
                $order->technician = null; // Handle case where no address is found
            }
        }

        return $orders;
    }



    public function inhouse_dtls($from_date = null, $to_date = null, $location_id = null)
    {
        $builder = $this->db->table('inhouse_maintenance im');

        $builder->select('
            im.id,
            im.order_id,
            im.item,
            im.qty,
            im.price,
            im.date,
            im.time,
            im.invoiceno,
            im.driver_name,
            im.check_by,
            im.mechanic_name,
            im.vehicle AS vehicle_id,
            im.location AS location_id,
            vehicle.vehicle_no,
            location.location_name,
            items.item_name,
            items.item_id
        ');

        $builder->join('vehicle', 'vehicle.id = im.vehicle', 'left');
        $builder->join('location', 'location.location_id = im.location', 'left');
        $builder->join('items', 'items.id = im.item', 'left');

        // Date filter
        if (!empty($from_date) && !empty($to_date)) {
            $builder->where('im.date >=', $from_date);
            $builder->where('im.date <=', $to_date);
        }

        // Location filter
        if (!empty($location_id)) {
            $builder->where('im.location', $location_id);
        }

        $builder->orderBy('im.order_id', 'DESC');
        $builder->orderBy('im.id', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * Mechanics for in-house maintenance form (admin/inhouse_maintenance).
     *
     * @return list<object>
     */
    public function getInhouseMechanics(): array
    {
        return $this->db->table('staff')
            ->select('id, name, staff_code, user_type')
            ->where('user_type', 'MECHANIC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Active staff + mechanics for a location (drivers excluded).
     *
     * @return list<object>
     */
    public function getActiveNonDriverStaffByLocation(int $locationId, ?string $referenceDate = null): array
    {
        if ($locationId <= 0) {
            return [];
        }

        $referenceDate = ($referenceDate !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $referenceDate))
            ? $referenceDate
            : date('Y-m-d');

        return $this->db->table('staff s')
            ->select('s.*, l.location_name')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->where('s.location_id', $locationId)
            ->whereIn('s.user_type', ['STAFF', 'MECHANIC'])
            ->groupStart()
                ->where('s.doj IS NULL', null, false)
                ->orWhere('s.doj', '0000-00-00')
                ->orWhere('s.doj <=', $referenceDate)
            ->groupEnd()
            ->groupStart()
                ->where('s.resign_date IS NULL', null, false)
                ->orWhere('s.resign_date', '0000-00-00')
                ->orWhere('s.resign_date >=', $referenceDate)
            ->groupEnd()
            ->orderBy('s.user_type', 'ASC')
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Users for "Checked by" dropdown (admin/inhouse_maintenance).
     *
     * @return list<object>
     */
    public function getInhouseCheckByUsers(): array
    {
        return $this->db->table('user')
            ->select('id, full_name, user_type')
            ->orderBy('full_name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Store in-house maintenance order (admin/insert_inhouse).
     *
     * @param array{
     *     vehicle: int,
     *     driver_name?: string,
     *     date: string,
     *     time: string,
     *     invoiceno?: string,
     *     location: int,
     *     check_by: string
     * } $header
     * @param list<array{
     *     item: int,
     *     qty: float|string|int,
     *     price: float|string|int,
     *     itemUseAs: int|string,
     *     mechanic_name?: string|null
     * }> $lines
     *
     * @return array{order_id: string, inserted_ids: list<int>}|null
     */
    public function storeInhouseMaintenance(array $header, array $lines): ?array
    {
        if ($lines === []) {
            return null;
        }

        $orderId   = 'ORD-' . strtoupper(uniqid());
        $inserted  = [];

        $this->db->transStart();

        foreach ($lines as $line) {
            $itemId = (int) ($line['item'] ?? 0);
            $qty    = $line['qty'] ?? 0;
            if ($itemId <= 0 || (float) $qty <= 0) {
                $this->db->transRollback();

                return null;
            }

            $this->db->table('inhouse_maintenance')->insert([
                'order_id'      => $orderId,
                'item'          => $itemId,
                'qty'           => $qty,
                'price'         => $line['price'] ?? 0,
                'vehicle'       => (int) ($header['vehicle'] ?? 0),
                'date'          => trim((string) ($header['date'] ?? '')),
                'time'          => trim((string) ($header['time'] ?? '')),
                'invoiceno'     => trim((string) ($header['invoiceno'] ?? '')),
                'driver_name'   => trim((string) ($header['driver_name'] ?? '')),
                'location'      => (int) ($header['location'] ?? 0),
                'itemUseAs'     => (int) ($line['itemUseAs'] ?? 1),
                'check_by'      => trim((string) ($header['check_by'] ?? '')),
                'mechanic_name' => isset($line['mechanic_name']) ? trim((string) $line['mechanic_name']) : null,
            ]);

            $newId = (int) $this->db->insertID();
            if ($newId <= 0) {
                $this->db->transRollback();

                return null;
            }

            $inserted[] = $newId;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'order_id'     => $orderId,
            'inserted_ids' => $inserted,
        ];
    }

    /**
     * Update in-house maintenance order (admin/update_inhouse).
     * Web deletes old order_id rows and inserts with a new order_id.
     *
     * @return array{old_order_id: string, order_id: string, inserted_ids: list<int>}|null
     */
    public function updateInhouseMaintenance(string $oldOrderId, array $header, array $lines): ?array
    {
        $oldOrderId = trim($oldOrderId);
        if ($oldOrderId === '' || $lines === []) {
            return null;
        }

        $exists = $this->db->table('inhouse_maintenance')->where('order_id', $oldOrderId)->countAllResults();
        if ($exists === 0) {
            return null;
        }

        $newOrderId = 'ORD-' . strtoupper(uniqid());
        $inserted   = [];

        $this->db->transStart();
        $this->db->table('inhouse_maintenance')->where('order_id', $oldOrderId)->delete();

        foreach ($lines as $line) {
            $itemId = (int) ($line['item'] ?? 0);
            $qty    = $line['qty'] ?? 0;
            if ($itemId <= 0 || (float) $qty <= 0) {
                $this->db->transRollback();

                return null;
            }

            $this->db->table('inhouse_maintenance')->insert([
                'order_id'      => $newOrderId,
                'item'          => $itemId,
                'qty'           => $qty,
                'price'         => $line['price'] ?? 0,
                'vehicle'       => (int) ($header['vehicle'] ?? 0),
                'date'          => trim((string) ($header['date'] ?? '')),
                'time'          => trim((string) ($header['time'] ?? '')),
                'invoiceno'     => trim((string) ($header['invoiceno'] ?? '')),
                'driver_name'   => trim((string) ($header['driver_name'] ?? '')),
                'location'      => (int) ($header['location'] ?? 0),
                'itemUseAs'     => (int) ($line['itemUseAs'] ?? 1),
                'check_by'      => trim((string) ($header['check_by'] ?? '')),
                'mechanic_name' => isset($line['mechanic_name']) ? trim((string) $line['mechanic_name']) : null,
            ]);

            $newId = (int) $this->db->insertID();
            if ($newId <= 0) {
                $this->db->transRollback();

                return null;
            }

            $inserted[] = $newId;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'old_order_id' => $oldOrderId,
            'order_id'     => $newOrderId,
            'inserted_ids' => $inserted,
        ];
    }

    /**
     * Delete in-house maintenance order (admin/delete_inhouse).
     */
    public function deleteInhouseMaintenanceByOrderId(string $orderId): int
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return 0;
        }

        $count = $this->db->table('inhouse_maintenance')->where('order_id', $orderId)->countAllResults();
        if ($count === 0) {
            return 0;
        }

        $this->db->table('inhouse_maintenance')->where('order_id', $orderId)->delete();

        return $count;
    }

    public function getItemById($id)
    {
        return $this->db->table('items')
            ->where('id', $id)
            ->get()
            ->getRow();
    }


    public function get_active_groups()
    {
        $builder = $this->db->table('voucher');
        $builder->select('group_code');
        $builder->groupBy('group_code');
        $builder->orderBy('id', 'DESC');
        return $builder->get()->getResult();
    }

    public function getVouchersForPayment($from_date = null, $to_date = null, $party = null, $voucher_no = null)
    {
        $builder = $this->db->table('voucher');
        $builder->select('voucher.*, SUM(despatch.net_amount) as total_net_amount, MAX(vendor.name) as party_name');

        $builder->join('despatch', 'despatch.voucher_id = voucher.id', 'left');
        $builder->join('do_registration', 'despatch.do_no = do_registration.do_no', 'left');
        $builder->join('vendor', 'vendor.id = do_registration.party OR vendor.id = SUBSTRING_INDEX(SUBSTRING_INDEX(do_registration.party, "(", -1), ")", 1)', 'left');

        if ($from_date && $to_date) {
            $builder->where('voucher.created_at >=', $from_date . ' 00:00:00');
            $builder->where('voucher.created_at <=', $to_date . ' 23:59:59');
        }

        if ($party) {
            $builder->where('do_registration.party', $party);
        }

        if ($voucher_no) {
            $builder->like('voucher.group_code', $voucher_no);
        }

        $builder->groupBy('voucher.id');
        $builder->orderBy('voucher.id', 'DESC');

        $results = $builder->get()->getResult();
        $paidIds = array_flip($this->getVoucherIdsAlreadyInPayment());

        foreach ($results as $voucher) {
            $voucher->in_payment = isset($paidIds[(int) $voucher->id]);
        }

        return $results;
    }

    /**
     * Voucher IDs already linked to any voucher_payment record.
     *
     * @return list<int>
     */
    public function getVoucherIdsAlreadyInPayment(): array
    {
        $rows = $this->db->table('voucher_payment')
            ->select('voucher_ids')
            ->where('voucher_ids IS NOT NULL', null, false)
            ->where('voucher_ids !=', '')
            ->get()
            ->getResult();

        $ids = [];
        foreach ($rows as $row) {
            foreach (explode(',', (string) $row->voucher_ids) as $id) {
                $id = (int) trim($id);
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    public function isVoucherInPayment(int $voucherId): bool
    {
        if ($voucherId <= 0) {
            return false;
        }

        $row = $this->db->table('voucher_payment')
            ->select('id')
            ->where('FIND_IN_SET(' . (int) $voucherId . ', voucher_ids) >', 0, false)
            ->limit(1)
            ->get()
            ->getRow();

        return $row !== null;
    }

    /**
     * @param list<int|string> $voucherIds
     *
     * @return list<int>
     */
    public function filterVoucherIdsAlreadyInPayment(array $voucherIds): array
    {
        $paidIds = array_flip($this->getVoucherIdsAlreadyInPayment());
        $duplicates = [];

        foreach ($voucherIds as $voucherId) {
            $id = (int) $voucherId;
            if ($id > 0 && isset($paidIds[$id])) {
                $duplicates[$id] = $id;
            }
        }

        return array_values($duplicates);
    }

    public function createVoucherPayment($voucher_ids)
    {
        if (empty($voucher_ids) || ! is_array($voucher_ids)) {
            return ['status' => 'error', 'message' => 'No vouchers selected'];
        }

        $voucher_ids = array_values(array_unique(array_filter(array_map('intval', $voucher_ids), static fn ($id) => $id > 0)));
        if ($voucher_ids === []) {
            return ['status' => 'error', 'message' => 'No valid vouchers selected'];
        }

        $duplicateIds = $this->filterVoucherIdsAlreadyInPayment($voucher_ids);
        if ($duplicateIds !== []) {
            $labels = $this->db->table('voucher')
                ->select('id, group_code')
                ->whereIn('id', $duplicateIds)
                ->get()
                ->getResult();

            $labelMap = [];
            foreach ($labels as $label) {
                $labelMap[(int) $label->id] = $label->group_code ?? ('ID ' . $label->id);
            }

            $duplicateLabels = array_map(
                static fn ($id) => $labelMap[$id] ?? ('ID ' . $id),
                $duplicateIds
            );

            return [
                'status' => 'error',
                'message' => 'These voucher(s) are already added to payment: ' . implode(', ', $duplicateLabels),
                'duplicate_voucher_ids' => $duplicateIds,
            ];
        }

        $builder = $this->db->table('voucher');
        $builder->select('voucher.id, voucher.group_code, despatch.do_no, despatch.net_amount');
        $builder->join('despatch', 'despatch.voucher_id = voucher.id', 'left');
        $builder->whereIn('voucher.id', $voucher_ids);
        $results = $builder->get()->getResult();

        if (empty($results)) {
            return ['status' => 'error', 'message' => 'No voucher details found'];
        }

        $total_net_amount = 0;
        $do_numbers = [];
        $processed_vouchers = [];

        foreach ($results as $row) {
            $total_net_amount += (float) ($row->net_amount ?? 0);
            if (! empty($row->do_no)) {
                $do_numbers[] = $row->do_no;
            }
            $processed_vouchers[(int) $row->id] = (int) $row->id;
        }

        $missingIds = array_diff($voucher_ids, array_keys($processed_vouchers));
        if ($missingIds !== []) {
            return ['status' => 'error', 'message' => 'Some selected vouchers were not found'];
        }

        $do_numbers = array_values(array_unique($do_numbers));
        $processed_vouchers = array_values($processed_vouchers);

        $po_number = 'PO-' . date('YmdHis') . '-' . rand(100, 999);

        $data = [
            'po_number' => $po_number,
            'do_numbers' => implode(',', $do_numbers),
            'voucher_ids' => implode(',', $processed_vouchers),
            'total_net_amount' => $total_net_amount,
            'received_amount' => 0,
            'difference_amount' => $total_net_amount,
            'adjustment_amount' => 0,
            'adjustment_remarks' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->transStart();

        // Re-check inside transaction to reduce duplicate race conditions.
        $lateDuplicates = $this->filterVoucherIdsAlreadyInPayment($processed_vouchers);
        if ($lateDuplicates !== []) {
            $this->db->transRollback();

            return [
                'status' => 'error',
                'message' => 'One or more vouchers were just added to payment by another user. Please refresh and try again.',
                'duplicate_voucher_ids' => $lateDuplicates,
            ];
        }

        $this->db->table('voucher_payment')->insert($data);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['status' => 'error', 'message' => 'Failed to create payment record'];
        }

        return [
            'status' => 'success',
            'message' => 'Payment record created successfully',
            'po_number' => $po_number,
        ];
    }

    public function getVoucherPayments($from_date = null, $to_date = null, $party = null)
    {
        $builder = $this->db->table('voucher_payment');
        $builder->select('voucher_payment.*');

        if ($from_date && $to_date) {
            $builder->where('created_at >=', $from_date . ' 00:00:00');
            $builder->where('created_at <=', $to_date . ' 23:59:59');
        }

        $records = $builder->orderBy('id', 'DESC')->get()->getResult();

        $filtered_records = [];

        foreach ($records as $rec) {
            $do_numbers_str = $rec->do_numbers; // e.g., "123, 124" or just "123"

            // Clean and prepare DO numbers array
            $do_numbers = [];
            if (!empty($do_numbers_str)) {
                $parts = explode(',', $do_numbers_str);
                foreach ($parts as $p) {
                    $do_numbers[] = trim($p);
                }
            }

            $rec->party_name = 'N/A';
            $rec->party_id = null; // Store ID for filtering

            if (!empty($do_numbers)) {
                // Find Party and actual DO No
                $first_do = $do_numbers[0]; // For party lookup

                // Collect actual DO strings
                $actual_do_strings = [];

                foreach ($do_numbers as $d_val) {
                    $do_query = $this->db->table('do_registration')
                        ->select('party, do_no')
                        ->where('do_no', $d_val)
                        ->orWhere('do_registration_id', $d_val)
                        ->get()->getRow();

                    if ($do_query) {
                        $actual_do_strings[] = $do_query->do_no;

                        // Set party from the first found DO context if not already set
                        if ($rec->party_id === null && !empty($do_query->party)) {
                            $party_val = $do_query->party;
                            // ... (Vendor lookup logic reused or simplified)
                            // Re-using the simplified vendor lookup for now since it was working
                            $vendor = null;
                            if (is_numeric($party_val)) {
                                $vendor = $this->db->table('vendor')->select('id, name')->where('id', $party_val)->get()->getRow();
                            } else {
                                preg_match('#\((.*?)\)#', $party_val, $match);
                                if (isset($match[1])) {
                                    $vendor_id = $match[1];
                                    $vendor = $this->db->table('vendor')->select('id, name')->where('id', $vendor_id)->get()->getRow();
                                } else {
                                    $vendor = $this->db->table('vendor')->select('id, name')->where('id', $party_val)->get()->getRow();
                                }
                            }

                            if ($vendor) {
                                $rec->party_name = $vendor->name;
                                $rec->party_id = $vendor->id;
                            }
                        }
                    } else {
                        // Fallback: Use the value as is if not found
                        $actual_do_strings[] = $d_val;
                    }
                }

                // Update the display field
                $rec->do_numbers = implode(', ', $actual_do_strings);
            }

            // Calculate total challans
            $voucher_ids_str = $rec->voucher_ids;
            $v_ids = [];
            if (!empty($voucher_ids_str)) {
                $v_parts = explode(',', $voucher_ids_str);
                foreach ($v_parts as $v_p) {
                    $trimmed_v = trim($v_p);
                    if (!empty($trimmed_v))
                        $v_ids[] = $trimmed_v;
                }
            }

            if (!empty($v_ids)) {
                $rec->total_challans = $this->db->table('despatch')
                    ->whereIn('voucher_id', $v_ids)
                    ->countAllResults();
            } else {
                $rec->total_challans = 0;
            }

            // Filter Logic
            if ($party) {
                if ($rec->party_id == $party) {
                    $filtered_records[] = $rec;
                }
            } else {
                $filtered_records[] = $rec;
            }
        }

        return $filtered_records;
    }

    public function getPaymentVoucherById($id)
    {
        $builder = $this->db->table('voucher_payment');
        // Remove join with user as deposited_by does not exist in voucher_payment
        $builder->select('voucher_payment.*');
        $builder->where('voucher_payment.id', $id);
        return $builder->get()->getRow();
    }

    public function getVouchersByList($voucher_ids_array)
    {
        if (empty($voucher_ids_array)) {
            return [];
        }

        $builder = $this->db->table('voucher');
        // Logic adapted from getVouchersForDeposit
        $builder->select('voucher.*, SUM(despatch.net_amount) as total_net_amount, COUNT(despatch.despatch_id) as challan_count, MAX(vendor.name) as party_name, user.full_name as deposited_by_name');

        $builder->join('despatch', 'despatch.voucher_id = voucher.id', 'left');
        $builder->join('do_registration', 'despatch.do_no = do_registration.do_registration_id', 'left');
        $builder->join('vendor', 'vendor.id = do_registration.party OR vendor.id = SUBSTRING_INDEX(SUBSTRING_INDEX(do_registration.party, "(", -1), ")", 1)', 'left');
        $builder->join('user', 'user.id = voucher.deposited_by', 'left');

        $builder->whereIn('voucher.id', $voucher_ids_array);

        $builder->groupBy('voucher.id');
        $builder->orderBy('voucher.id', 'DESC');
        return $builder->get()->getResult();
    }

    public function getChallansByDoList($do_numbers_array)
    {
        if (empty($do_numbers_array)) {
            return [];
        }

        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, vehicle.vehicle_no as vehicle_number, do_registration.do_no as doreg_no, do_registration.rate, do_registration.shortage_qty as min_qty, do_registration.shortage_rate, do_registration.diesel_rate, do_registration.diesel_payment_type, do_registration.cash_type, do_registration.special_shortage, creator.full_name as made_by, COALESCE(do_registration.tds_percentage, 2.00) as tds_percentage, voucher.group_code');
        $builder->join('vehicle', 'vehicle.id = despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');
        $builder->join('voucher', 'voucher.id = despatch.voucher_id', 'left');
        $builder->join('activity_logs', "activity_logs.model_id = despatch.despatch_id AND activity_logs.action = 'create' AND (activity_logs.menu = 'despatch_entry' OR activity_logs.menu = 'insert_despatch_entry')", 'left');
        $builder->join('user as creator', 'creator.id = activity_logs.user_id', 'left');
        $builder->where('despatch.deleted_by IS NULL');

        // Filter by DO numbers
        // Since do_numbers in voucher_payment can match either ID or String, we should try to match both if possible, 
        // but despatch.do_no is the ID from do_registration table. 
        // The Input $do_numbers_array comes from the controller which should parse the strings.
        // However, in this system, it seems `despatch.do_no` is the linking key.

        // We'll assume the controller passes valid DO IDs or DO Numbers that match. 
        // Wait, despatch.do_no is a foreign key to do_registration.do_registration_id. 
        // The DO numbers stored in `voucher_payment` are strings (do_no column of do_registration).
        // So we need to match `do_registration.do_no` OR `do_registration.do_registration_id` against the input.

        $builder->groupEnd();

        return $builder->get()->getResult();
    }

    public function getChallansByVoucherList($voucher_ids_array)
    {
        if (empty($voucher_ids_array)) {
            return [];
        }

        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, vehicle.vehicle_no as vehicle_number, do_registration.do_no as doreg_no, do_registration.rate, do_registration.shortage_qty as min_qty, do_registration.shortage_rate, do_registration.diesel_rate, do_registration.diesel_payment_type, do_registration.cash_type, do_registration.special_shortage, creator.full_name as made_by, COALESCE(do_registration.tds_percentage, 2.00) as tds_percentage, voucher.group_code');
        $builder->join('vehicle', 'vehicle.id = despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');
        $builder->join('voucher', 'voucher.id = despatch.voucher_id', 'left');
        $builder->join('activity_logs', "activity_logs.model_id = despatch.despatch_id AND activity_logs.action = 'create' AND (activity_logs.menu = 'despatch_entry' OR activity_logs.menu = 'insert_despatch_entry')", 'left');
        $builder->join('user as creator', 'creator.id = activity_logs.user_id', 'left');
        $builder->where('despatch.deleted_by IS NULL');

        // Filter by Voucher IDs
        $builder->whereIn('despatch.voucher_id', $voucher_ids_array);

        return $builder->get()->getResult();
    }

    function inhouse_orderdtls($order_id)
    {
        // echo($order_id);exit;

        $builder = $this->db->table('inhouse_maintenance');
        $builder->select('inhouse_maintenance.*,vehicle.vehicle_no, location.location_name,items.item_name, items.item_id');
        $builder->join('vehicle', 'vehicle.id = inhouse_maintenance.vehicle', 'left');
        $builder->join('location', 'location.location_id = inhouse_maintenance.location', 'left');
        $builder->join('items', 'items.id = inhouse_maintenance.item', 'left');
        $builder->where('order_id', $order_id);
        return $builder->get()->getResult();

    }





    function IteamDetails($order_id)
    {
        $builder = $this->db->table('orders');
        $builder->select('*');
        $builder->where('order_id', $order_id);
        return $builder->get()->getResult();
    }

    function additionalIteam($order_id)
    {
        $builder = $this->db->table('additional_service');
        $builder->select('*');
        $builder->where('order_id', $order_id);
        return $builder->get()->getResult();
    }



    function updateorder($data, $order_id)
    {
        $query = $this->db->table('orders')->update($data, array('order_id' => $order_id));
        return $query;
    }
    function getsingleaddress($addre_id)
    {
        $builder = $this->db->table('address');
        $builder->select('*');
        $builder->join('city', 'city.city_id = address.cityname');
        $builder->where('address_id', $addre_id);
        return $builder->get()->getResult();
    }

    function testimonial()
    {
        $builder = $this->db->table('testimonial');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function addtestimonial($data)
    {

        $query = $this->db->table('testimonial')->insert($data);
        return $query;
    }
    function DeleteTestimonial($testimonial_id)
    {
        $query = $this->db->table('testimonial')->delete(array('testimonial_id' => $testimonial_id));
        return $query;
    }


    public function sendPushNotification($message, $title, $sender_id)
    {


        $fields = array
        (
            'to' => "$sender_id",
            'priority' => 'high',
            'notification' => array(
                'body' => $message,
                'title' => $title,
                'sound' => 'default',
                'icon' => 'https://collegeprojectz.com/apniseva/uploads/fav.png',
                'image' => ''
            ),
            'data' => array(
                'message' => $message,
                'title' => $title,
                'sound' => 'default',
                'icon' => 'https://collegeprojectz.com/apniseva/uploads/fav.png',
                'image' => ''
            )


        );

        //sendPushNotification($fields);

        $API_ACCESS_KEY = 'AAAAQFQwKyE:APA91bH5Beqfc4XbzX4pAvLDTuX_IruIVJjwCpcRGrq3tNShwmP1LLfQlA95gkLL0IVloiJmjGaxkPq61DmkUffVHuicKeKpKLugmTalcn9CbvomeGUJUn9ILBlDorFDBoECfsD5GiG9';
        $headers = array
        (
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        //echo $result;exit;

        return $result;

    }

    function GetAllOrder($Partner_id)
    {
        $builder = $this->db->table('orders');
        $builder->select('*');
        $builder->where('vendor_id', $Partner_id);
        $builder->where('status', 5);
        $builder->orderBy('booking_date', 'DESC');
        $builder->groupBy('order_id');
        return $builder->get()->getResult();

    }

    function ratingreview()
    {
        $builder = $this->db->table('rating_review');
        $builder->select('rating_review.*, from_user.full_name as from_full_name, from_user.user_type as from_user_type, to_user.full_name as to_full_name, to_user.user_type as to_user_type');
        $builder->join('user as from_user', 'from_user.id = rating_review.from_user_id');
        $builder->join('user as to_user', 'to_user.id = rating_review.to_user_id');
        return $builder->get()->getResult();
    }

    function filterBooking($from_date, $to_date, $status, $city)
    {
        $builder = $this->db->table('orders');
        $builder->select('orders.*, address.cityname, address.created_date AS address_created_date');
        $builder->join('address', 'orders.address_id = address.address_id');
        $builder->where('orders.created_date >=', $from_date);
        $builder->where('orders.created_date <=', $to_date);
        if (!empty($status)) {
            if ($status == "null1") {
                $builder->where('orders.status', null);
            } else {
                $builder->where('orders.status', $status);
            }
        }


        if (!empty($city)) {
            $builder->where('address.cityname', $city);
        }
        $builder->orderBy('orders.created_date', 'DESC');
        $builder->groupBy('orders.order_id');
        return $builder->get()->getResult();
    }

    // function exportorder($from_date, $to_date, $status, $city)
    // {
    //     $builder = $this->db->table('orders');
    //     $builder->select('orders.order_id,
    //                       orders.booking_date,
    //                       user.full_name, 
    //                       user.contact_no,
    //                       vendor.full_name as technician_name,
    //                       address.first_name,
    //                       address.last_name,
    //                       address.email,
    //                       city.city_name,
    //                       address.state,
    //                       address.pincode,
    //                       address.address1,
    //                       address.adress2,
    //                       orders.payment_mode,
    //                       orders.status as order_status,
    //                       SUM(orders.price) as total_price,
    //                       SUM(additional_service.add_service_price) as additional_service_price,
    //                       SUM(orders.price + additional_service.add_service_price - orders.coupon_amnt) as grand_total,
    //                       coupon_amnt,
    //                       services.service_name,
    //                       services.service_id,
    //                       services.cat_id');
    //     $builder->join('address', 'orders.address_id = address.address_id');
    //     $builder->join('user', 'orders.orders_id = user.id');
    //     $builder->join('user as vendor', 'orders.vendor_id = vendor.id', 'left');
    //     $builder->join('city', 'address.cityname = city.city_id','left');
    //     $builder->join('additional_service', 'additional_service.order_id = orders.order_id', 'left'); // Join additional_service table
    //     $builder->join('services', 'services.service_name = orders.productname');

    //     $builder->where('user.user_type', 4);
    //     $builder->where('orders.created_date >=', $from_date);
    //     $builder->where('orders.created_date <=', $to_date);
    //     if (!empty($status)) {
    //         $builder->where('orders.status', $status);
    //     }
    //     if (!empty($city)) {
    //         $builder->where('address.cityname', $city);
    //     }
    //     $builder->orderBy('orders.created_date', 'DESC');
    //     $builder->groupBy('orders.order_id');
    //     return $builder->get()->getResult();
    // }

    function exportorder($from_date, $to_date, $status, $city)
    {


        $builder = $this->db->table('orders');
        $builder->select('orders.order_id,
                      orders.booking_date,
                      user.full_name, 
                      address.number as numbb,
                      vendor.full_name as technician_name,
                      address.first_name,
                      address.last_name,
                      address.email,
                      city.city_name,
                      address.state,
                      address.pincode,
                      address.address1,
                      address.adress2,
                      orders.payment_mode,
                      orders.status as order_status,
                      SUM(orders.price) as total_price,
                      SUM(additional_service.add_service_price) as additional_service_price,
                      SUM(orders.price + additional_service.add_service_price - orders.coupon_amnt) as grand_total,
                      coupon_amnt,
                      txn,
                      vendor_commision_byorder,
                      services.service_name,
                      services.service_id,
                      services.cat_id,
                      category.cat_id as cat_id,
                      category.cat_name as cat_name,
                      parent_category.cat_id as parent_cat_id,
                      parent_category.cat_name as parent_cat_name');

        $builder->join('address', 'orders.address_id = address.address_id', 'left');
        $builder->join('user', 'orders.orders_id = user.id', 'left');
        $builder->join('user as vendor', 'orders.vendor_id = vendor.id', 'left');
        $builder->join('city', 'address.cityname = city.city_id', 'left');
        $builder->join('additional_service', 'additional_service.order_id = orders.order_id', 'left'); // Join additional_service table
        $builder->join('services', 'services.service_name = orders.productname', 'left'); // Join services table
        $builder->join('category', 'category.cat_id = services.cat_id', 'left'); // Join category table
        $builder->join('category as parent_category', 'category.parent_id = parent_category.cat_id', 'left'); // Join parent category table

        //$builder->where('user.user_type', 4);
        $builder->where('orders.created_date >=', $from_date);
        $builder->where('orders.created_date <=', $to_date);
        if (!empty($status)) {
            if ($status == "null1") {
                $builder->where('orders.status', null);
            } else {
                $builder->where('orders.status', $status);
            }
        }

        if (!empty($city)) {
            $builder->where('address.cityname', $city);
        }
        $builder->orderBy('orders.created_date', 'DESC');
        $builder->groupBy('orders.order_id');
        return $builder->get()->getResult();
    }


    function exportorderreport($from_date, $to_date, $status, $city, $customer, $vendor)
    {
        $builder = $this->db->table('orders');
        $builder->select('orders.order_id,
                      orders.booking_date,
                      user.id,
                      user.full_name, 
                      user.contact_no,
                      vendor.id,
                      vendor.full_name as technician_name,
                      address.first_name,
                      address.last_name,
                      address.email,
                      city.city_name,
                      address.state,
                      address.pincode,
                      address.address1,
                      address.adress2,
                      orders.payment_mode,
                      orders.status as order_status,
                      SUM(orders.price) as total_price,
                      SUM(additional_service.add_service_price) as additional_service_price,
                      SUM(orders.price + additional_service.add_service_price - orders.coupon_amnt) as grand_total,
                      coupon_amnt,
                      txn,
                      vendor_commision_byorder,
                      services.service_name,
                      services.service_id,
                      services.cat_id,
                      category.cat_id as cat_id,
                      category.cat_name as cat_name,
                      parent_category.cat_id as parent_cat_id,
                      parent_category.cat_name as parent_cat_name');

        $builder->join('address', 'orders.address_id = address.address_id', 'left');
        $builder->join('user', 'orders.orders_id = user.id', 'left');
        $builder->join('user as vendor', 'orders.vendor_id = vendor.id', 'left');
        $builder->join('city', 'address.cityname = city.city_id', 'left');
        $builder->join('additional_service', 'additional_service.order_id = orders.order_id', 'left'); // Join additional_service table
        $builder->join('services', 'services.service_name = orders.productname', 'left'); // Join services table
        $builder->join('category', 'category.cat_id = services.cat_id', 'left'); // Join category table
        $builder->join('category as parent_category', 'category.parent_id = parent_category.cat_id', 'left'); // Join parent category table

        //$builder->where('user.user_type', 4);
        $builder->where('orders.created_date >=', $from_date);
        $builder->where('orders.created_date <=', $to_date);
        if (!empty($status)) {
            if ($status == "null1") {
                $builder->where('orders.status', null);
            } else {
                $builder->where('orders.status', $status);
            }
        }




        if (!empty($city)) {
            $builder->where('address.cityname', $city);
        }

        if (!empty($customer)) {
            $builder->where('user.id', $customer);
        }

        if (!empty($vendor)) {
            $builder->where('vendor.id', $vendor);
        }

        $builder->orderBy('orders.created_date', 'DESC');
        $builder->groupBy('orders.order_id');
        return $builder->get()->getResult();
    }



    function staff_salary_details($year, $month, $location)
    {
        $monthStart = sprintf('%04d-%02d-01', (int) $year, (int) $month);
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $builder = $this->db->table('staff');
        $builder->select('staff.*, 
                      staff_salary_details.working_day, 
                      staff_salary_details.bonus_days, 
                      staff_salary_details.insentive, 
                      staff_salary_details.total_salary, 
                      staff_salary_details.net_salary,  
                      location.location_name, 
                      IFNULL(FORMAT(staff_advance.total_advance, 2), "0.00") as total_advance,
                      IFNULL(attendance_days.present_days, 0) as present_days');
        $builder->whereIn('staff.user_type', ['STAFF', 'MECHANIC']);
        if (!empty($location)) {
            $builder->where('staff.location_id', $location);
        }
        $builder->join('location', 'location.location_id = staff.location_id', 'left');

        $subQueryAdvance = $this->db->table('staff_advance')
            ->select('staff_id, COALESCE(SUM(amount), 0) AS total_advance')
            ->where('adv_date >=', $monthStart)
            ->where('adv_date <=', $monthEnd)
            ->groupBy('staff_id')
            ->getCompiledSelect();

        $builder->join("($subQueryAdvance) AS staff_advance", 'staff_advance.staff_id = staff.id', 'left');

        $subQueryAttendance = $this->db->table('staff_attendance sa')
            ->select('sa.staff_id, COUNT(DISTINCT sa.attendance_date) AS present_days')
            ->join('staff s', 's.id = sa.staff_id', 'inner')
            ->whereIn('sa.status', ['Present', 'Holiday'])
            ->where('sa.attendance_date >=', $monthStart)
            ->where('sa.attendance_date <=', $monthEnd)
            ->groupStart()
                ->where('s.doj IS NULL', null, false)
                ->orWhere('s.doj', '0000-00-00')
                ->orWhere('sa.attendance_date >= s.doj', null, false)
            ->groupEnd()
            ->groupStart()
                ->where('s.resign_date IS NULL', null, false)
                ->orWhere('s.resign_date', '0000-00-00')
                ->orWhere('sa.attendance_date <= s.resign_date', null, false)
            ->groupEnd()
            ->groupBy('sa.staff_id')
            ->getCompiledSelect();

        $builder->join("($subQueryAttendance) AS attendance_days", 'attendance_days.staff_id = staff.id', 'left');

        $subQuerySalary = $this->db->table('staff_salary')
            ->select('user_id, working_day, bonus_days, insentive, total_salary, net_salary')
            ->where('year', $year)
            ->where('month', $month)
            ->groupBy('user_id')
            ->getCompiledSelect();

        $builder->join("($subQuerySalary) AS staff_salary_details", 'staff_salary_details.user_id = staff.id', 'left');

        return $builder->get()->getResult();
    }

    function resolveStaffSalaryWorkingDay($savedWorkingDay, $presentDays)
    {
        // Always use fresh attendance count (Present + Holiday distinct dates).
        return (int) ($presentDays ?? 0);
    }

    function getStaffSalaryPresentDays(int $staffId, int $year, int $month): int
    {
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $row = $this->db->table('staff_attendance sa')
            ->select('COUNT(DISTINCT sa.attendance_date) AS present_days', false)
            ->join('staff s', 's.id = sa.staff_id', 'inner')
            ->where('sa.staff_id', $staffId)
            ->whereIn('sa.status', ['Present', 'Holiday'])
            ->where('sa.attendance_date >=', $monthStart)
            ->where('sa.attendance_date <=', $monthEnd)
            ->groupStart()
                ->where('s.doj IS NULL', null, false)
                ->orWhere('s.doj', '0000-00-00')
                ->orWhere('sa.attendance_date >= s.doj', null, false)
            ->groupEnd()
            ->groupStart()
                ->where('s.resign_date IS NULL', null, false)
                ->orWhere('s.resign_date', '0000-00-00')
                ->orWhere('sa.attendance_date <= s.resign_date', null, false)
            ->groupEnd()
            ->get()
            ->getRow();

        return (int) ($row->present_days ?? 0);
    }





    // function driver_salary_details($year, $month, $location)
// {
//     $builder = $this->db->table('staff');
//     $builder->select('staff.*, 
//                       location.location_name,
//                       IFNULL(FORMAT(staff_advance.total_advance, 2), "0.00") as total_advance,
//                       driver_assignment.from_date,
//                       driver_assignment.to_date,
//                       driver_assignment.vehicle_no as assignment_vehicle_no,
//                       vehicle.vehicle_no,
//                       ');

    //     $builder->join('location', 'location.location_id = staff.address', 'left');
//     $builder->where('staff.user_type', 'DRIVER');
//     if (!empty($location)) {
//         $builder->where('staff.address', $location);  
//     }

    //     // Subquery for staff advances
//     $subQueryAdvance = $this->db->table('staff_advance')
//                                 ->select('staff_id, COALESCE(SUM(amount), 0) AS total_advance')
//                                 ->where("YEAR(created_at)", $year)
//                                 ->where("MONTH(created_at)", $month)
//                                 ->groupBy('staff_id')
//                                 ->getCompiledSelect();

    //     // Subquery for driver assignments and vehicle details
//     $subQueryAssignment = $this->db->table('driver_assignment')
//                                   ->select('driver AS staff_id, 
//                                              from_date, 
//                                              to_date, 
//                                              vehicle_no')
//                                   ->where("YEAR(from_date)", $year)
//                                   ->where("MONTH(from_date)", $month)
//                                   ->getCompiledSelect();

    //     // Join the subqueries with the main query
//     $builder->join("($subQueryAdvance) AS staff_advance", 'staff_advance.staff_id = staff.id', 'left');
//     $builder->join("($subQueryAssignment) AS driver_assignment", 'driver_assignment.staff_id = staff.id', 'left');
//     $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');

    //     return $builder->get()->getResult();
// }
// 17 feb 2025
// function driver_salary_details($year, $month, $location)
// {
//     $builder = $this->db->table('staff');
//     $builder->select('staff.*, 
//                       location.location_name,
//                       IFNULL(FORMAT(staff_advance.total_advance, 2), "0.00") as total_advance,
//                       driver_assignment.from_date,
//                       driver_assignment.to_date,
//                       driver_assignment.vehicle_no as assignment_vehicle_no,
//                       vehicle.vehicle_no,
//                       adjust_salary.amount'); // Fetch the adjust_salary amount

    //     // Join staff with location, advance, driver assignment, vehicle, and adjust_salary
//     $builder->join('location', 'location.location_id = staff.address', 'left');
//     $builder->join('adjust_salary', 'adjust_salary.driver_id = staff.id', 'left'); // Join with adjust_salary

    //     $builder->where('staff.user_type', 'DRIVER');
//     if (!empty($location)) {
//         $builder->where('staff.address', $location);  
//     }


    //     $firstDayOfMonth = "$year-$month-01";
//     $lastDayOfMonth = date("Y-m-t", strtotime($firstDayOfMonth));
//     // Subquery for driver assignments and vehicle details
//     $subQueryAssignment = $this->db->table('driver_assignment')
//                                   ->select('driver AS staff_id, 
//                                              from_date, 
//                                              to_date, 
//                                              vehicle_no')
//                                   ->where('driver_assignment.from_date <=', $lastDayOfMonth)
//                                   ->where('driver_assignment.from_date >=', $firstDayOfMonth)
//                                   ->getCompiledSelect();



    //     // Subquery for staff advances
//     $subQueryAdvance = $this->db->table('staff_advance')
//                                 ->select('staff_id, COALESCE(SUM(amount), 0) AS total_advance,')

    //                                 ->where("YEAR(adv_date)", $year)
//                                 ->where("MONTH(adv_date)", $month)

    //                                 ->groupBy('staff_id')
//                                 ->getCompiledSelect();




    //     // Join the subqueries with the main query
//     $builder->join("($subQueryAdvance) AS staff_advance", 'staff_advance.staff_id = staff.id', 'left');
//     $builder->join("($subQueryAssignment) AS driver_assignment", 'driver_assignment.staff_id = staff.id', 'left');
//     $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');

    //     return $builder->get()->getResult();
// }

    function driver_salary_details($year, $month, $location, $driver_id = null)
    {
        $builder = $this->db->table('staff');
        $builder->select('staff.*, 
                          location.location_name,
                          IFNULL(FORMAT(staff_advance.total_advance, 2), "0.00") as total_advance,
                          driver_assignment.from_date,
                          driver_assignment.to_date,
                          driver_assignment.vehicle_no as assignment_vehicle_no,
                          driver_assignment.opening_hsd,
                          driver_assignment.closing_hsd,
                          vehicle.vehicle_no,
                          adjust_salary.amount,adjust_salary.remark'); // Fetch the adjust_salary amount

        // Join staff with location, driver assignment, vehicle, and adjust_salary
        $builder->join('location', 'location.location_id = staff.address', 'left');
        $builder->join('adjust_salary', 'adjust_salary.driver_id = staff.id', 'left'); // Join with adjust_salary
        $builder->where('driver_assignment.staff_id IS NOT NULL');

        $builder->where('staff.user_type', 'DRIVER');
        if (!empty($location)) {
            $builder->where('staff.address', $location);
        }
        if (!empty($driver_id)) {
            $builder->where('staff.id', $driver_id);
        }

        $firstDayOfMonth = "$year-$month-01";
        $lastDayOfMonth = date("Y-m-t", strtotime($firstDayOfMonth));

        // Subquery for driver assignments and vehicle details
        $subQueryAssignment = $this->db->table('driver_assignment')
            ->select('driver AS staff_id, 
                                                 from_date, 
                                                 to_date, 
                                                 vehicle_no,
                                                 opening_hsd,
                                                 closing_hsd')
            ->where('driver_assignment.from_date <=', $lastDayOfMonth)
            ->where('driver_assignment.from_date >=', $firstDayOfMonth)
            ->getCompiledSelect();

        // Subquery for staff advances, only considering advances **within** the assignment period
        $subQueryAdvance = $this->db->table('staff_advance sa')
            ->select('sa.staff_id, 
                                              COALESCE(SUM(sa.amount), 0) AS total_advance')
            ->join("($subQueryAssignment) AS da", 'da.staff_id = sa.staff_id', 'inner')
            ->where('sa.adv_date >= da.from_date')
            ->where('sa.adv_date <= da.to_date')
            ->groupBy('sa.staff_id')
            ->getCompiledSelect();
        // $subQueryAdjust = $this->db->table('adjust_salary ad')
        //                             ->select('ad.driver_id, 
        //                                       COALESCE(SUM(ad.amount), 0) AS total_advance')
        //                             ->join("($subQueryAssignment) AS da", 'da.staff_id = sa.staff_id', 'inner')
        //                             ->where('sa.adv_date >= da.from_date')
        //                             ->where('sa.adv_date <= da.to_date')
        //                             ->groupBy('sa.staff_id')
        //                             ->getCompiledSelect();

        // Join the subqueries with the main query
        $builder->join("($subQueryAdvance) AS staff_advance", 'staff_advance.staff_id = staff.id', 'left');
        $builder->join("($subQueryAssignment) AS driver_assignment", 'driver_assignment.staff_id = staff.id', 'left');
        $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');
        $builder->groupBy(['staff.id', 'driver_assignment.from_date']);
        return $builder->get()->getResult();
    }






    // function hsd_details($driver_id, $year, $month)
// {
//     $builder = $this->db->table('driver_assignment');
//     $builder->select('
//         IFNULL(driver_assignment.opening_hsd, 0) as opening_hsd, 
//         IFNULL(driver_assignment.closing_hsd, 0) as closing_hsd,
//         IFNULL(diselentry.rate, 0) as diesel_rate,
//         IFNULL(SUM(diselentry.qty), 0) as total_diselexpence,
//         IFNULL(driver_assignment.opening_hsd, 0) + IFNULL(SUM(diselentry.qty), 0) - IFNULL(driver_assignment.closing_hsd, 0) as used_hsd
//     ');

    //     $builder->join('diselentry', 'diselentry.vehicle_id = driver_assignment.vehicle_no', 'left');

    //     // Add conditions to the join to handle NULL values
//     $builder->groupStart()
//             ->groupStart()
//                 ->where('diselentry.diesel_date >= driver_assignment.from_date')
//                 ->where('diselentry.diesel_date <= driver_assignment.to_date')
//             ->groupEnd()
//             ->orGroupStart()
//                 ->where('driver_assignment.from_date IS NULL')
//                 ->where('driver_assignment.to_date IS NULL')
//             ->groupEnd()
//         ->groupEnd();

    //     $builder->where('driver', $driver_id);

    //     // Handle NULL and year/month filtering for from_date
//     $builder->groupStart()
//             ->groupStart()
//                 ->where("YEAR(from_date)", $year)
//                 ->where("MONTH(from_date)", $month)
//             ->groupEnd()
//             ->orGroupStart()
//                 ->where("from_date IS NULL")
//             ->groupEnd()
//         ->groupEnd();

    //     // Handle NULL and year/month filtering for to_date
//     $builder->groupStart()
//             ->groupStart()
//                 ->where("YEAR(to_date)", $year)
//                 ->where("MONTH(to_date)", $month)
//             ->groupEnd()
//             ->orGroupStart()
//                 ->where("to_date IS NULL")
//             ->groupEnd()
//         ->groupEnd();

    //     // Year/month filtering for diselentry.diesel_date
//     $builder->groupStart()
//             ->where("YEAR(diselentry.diesel_date)", $year)
//             ->where("MONTH(diselentry.diesel_date)", $month)
//         ->groupEnd();

    //     // Group by the relevant fields to aggregate diselentry.qty
//     $builder->groupBy('driver_assignment.id');

    //     return $builder->get()->getResult();
// }

    function hsd_details($driver_id, $from_date, $to_date)
    {

        $builder = $this->db->table('driver_assignment');
        $builder->select('
        IFNULL(driver_assignment.opening_hsd, 0) as opening_hsd, 
        IFNULL(driver_assignment.closing_hsd, 0) as closing_hsd,
        IFNULL(diselentry.rate, 0) as diesel_rate,
        IFNULL(SUM(diselentry.qty), 0) as total_diselexpence,
        (IFNULL(driver_assignment.opening_hsd, 0) + IFNULL(SUM(diselentry.qty), 0) - IFNULL(driver_assignment.closing_hsd, 0)) as used_hsd
    ');

        $builder->join('diselentry', 'diselentry.vehicle_id = driver_assignment.vehicle_no', 'left');

        // Ensure diesel entries are within the driver assignment period
        $builder->groupStart()
            ->where('diselentry.diesel_date >=', $from_date)
            ->where('diselentry.diesel_date <=', $to_date)
            ->groupEnd();

        // Filter by the driver ID
        $builder->where('driver_assignment.driver', $driver_id);

        // Filter by driver assignment period
        $builder->groupStart()
            ->where('driver_assignment.from_date <=', $to_date)
            ->where('driver_assignment.to_date >=', $from_date)
            ->groupEnd();

        // Group by driver assignment ID to aggregate diesel quantity correctly
        $builder->groupBy('driver_assignment.id');

        return $builder->get()->getResult();
    }

    function hsd_detailsData($vehicle_no, $from_date, $to_date)
    {

        //return $builder->get()->getResult();
    }





    function vehicle_disel_details($vehicle_id, $from_date = null, $to_date = null)
    {
        // Set default values if $from_date or $to_date are empty or null
        if (empty($from_date)) {
            $from_date = date('Y-m-01'); // First day of the current month
        }
        if (empty($to_date)) {
            $to_date = date('Y-m-d'); // Current date
        }
        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, 
                      do_registration.diesel_type as diesel_for_trip, 
                      despatch.quantity as total_weight
                      ');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->where('despatch.vehicle_no', $vehicle_id);
        $builder->where('despatch.des_date >=', $from_date);
        $builder->where('despatch.des_date <=', $to_date);
        $builder->where('despatch.deleted_at IS NULL', null, false);
        $builder->where('despatch.deleted_by IS NULL', null, false);

        return $builder->get()->getResult();
    }



    function tripexpence($vehicle_id, $year, $month)
    {
        $builder = $this->db->table('despatch');
        $builder->select('
            do_registration.do_no,
            do_registration.trip_expenses1,
            do_registration.trip_expenses2,
            do_registration.trip_expenses3,
            do_registration.trip_expenses4,
            do_registration.trip_expenses5,
            do_registration.trip_expenses6,
            DATE(despatch.des_date) as despatch_date,
            SUM(despatch.quantity) as total_weight,
            COUNT(despatch.despatch_id) as total_number_of_trip
        ');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->where('despatch.vehicle_no', $vehicle_id);
        $builder->where("YEAR(despatch.des_date)", $year);
        $builder->where("MONTH(despatch.des_date)", $month);
        $builder->groupBy('DATE(despatch.des_date)'); // Group by the date only

        $result = $builder->get()->getResult();

        foreach ($result as $row) {
            if ($row->total_number_of_trip == 1) {
                $row->day_trip_expense = $row->trip_expenses1;
            } elseif ($row->total_number_of_trip == 2) {
                $row->day_trip_expense = $row->trip_expenses2;
            } elseif ($row->total_number_of_trip == 3) {
                $row->day_trip_expense = $row->trip_expenses3;
            } elseif ($row->total_number_of_trip == 4) {
                $row->day_trip_expense = $row->trip_expenses4;
            } elseif ($row->total_number_of_trip == 5) {
                $row->day_trip_expense = $row->trip_expenses5;
            } elseif ($row->total_number_of_trip >= 6) {
                $row->day_trip_expense = $row->trip_expenses6;
            } else {
                $row->day_trip_expense = 0; // Default case if no matching condition
            }
        }

        return $result;
    }

    function tripexpence1($vehicle_id, $driver_id, $year, $month, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table('despatch');
        $builder->select(' do_registration.do_no, despatch.vehicle_no, despatch.do_no as do_id, do_registration.do_registration_id, 
                            driver_assignment.driver, do_registration.trip_expenses1, do_registration.trip_expenses2,
                            do_registration.trip_expenses3, do_registration.trip_expenses4, do_registration.trip_expenses5,
                            do_registration.trip_expenses6, DATE(despatch.des_date) as despatch_date, doprice_change.trip1
                            as doprice_trip1, doprice_change.trip2 as doprice_trip2, doprice_change.trip3 as doprice_trip3,
                            doprice_change.trip4 as doprice_trip4, doprice_change.trip5 as doprice_trip5, doprice_change.trip6 as doprice_trip6,
                            SUM(despatch.quantity) as total_weight, COUNT(DISTINCT despatch.despatch_id) as total_number_of_trip '
        );
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->join('doprice_change', 'do_registration.do_registration_id = doprice_change.dono AND despatch.des_date >= doprice_change.from_date', 'left');
        $builder->join('driver_assignment', 'driver_assignment.vehicle_no = despatch.vehicle_no', 'inner');
        $builder->where('despatch.vehicle_no', $vehicle_id);
        $builder->where('driver_assignment.driver', $driver_id);
        $builder->where('YEAR(despatch.des_date)', $year);
        $builder->where('MONTH(despatch.des_date)', $month);
        $builder->where('despatch.deleted_at IS NULL', null, false);
        $builder->where('despatch.deleted_by IS NULL', null, false);

        if (!empty($from_date) && !empty($to_date)) {
            $builder->where('driver_assignment.from_date', $from_date);
            $builder->where('driver_assignment.to_date', $to_date);
            $builder->where('despatch.des_date >=', $from_date);
            $builder->where('despatch.des_date <=', $to_date);
        } else {
            $builder->where('despatch.des_date >=', 'driver_assignment.from_date', false);
            $builder->where('despatch.des_date <=', 'driver_assignment.to_date', false);
        }

        // Per day, per DO trip count → apply that DO's slab rate
        $builder->groupBy([
            'despatch.vehicle_no',
            'driver_assignment.driver',
            'despatch.do_no',
            'DATE(despatch.des_date)',
        ]);

        $result = $builder->get()->getResult();
        foreach ($result as $row) {
            $this->applyDayTripExpenseToRow($row);
        }

        return $result;
    }

    public function tripexpence1Sum($vehicle_id, $driver_id, $year, $month, $from_date = null, $to_date = null): float
    {
        $total = 0.0;
        foreach ($this->tripexpence1($vehicle_id, $driver_id, $year, $month, $from_date, $to_date) as $row) {
            $total += (float) ($row->day_trip_expense ?? 0);
        }

        return $total;
    }

    /**
     * Batch totals for driver salary grid — same logic as tripexpence1(), keyed by "vehicleId|driverId|from|to".
     *
     * @param list<array{vehicle_id:int, driver_id:int, from_date:string, to_date:string}> $pairs
     * @return array<string, float>
     */
    public function tripexpence1BatchTotals(array $pairs, $year, $month): array
    {
        $totals = [];
        $vehicleIds = [];
        $driverIds = [];

        foreach ($pairs as $pair) {
            $vehicleId = (int) ($pair['vehicle_id'] ?? 0);
            $driverId = (int) ($pair['driver_id'] ?? 0);
            $fromDate = (string) ($pair['from_date'] ?? '');
            $toDate = (string) ($pair['to_date'] ?? '');
            if ($vehicleId <= 0 || $driverId <= 0 || $fromDate === '' || $toDate === '') {
                continue;
            }
            $vehicleIds[$vehicleId] = $vehicleId;
            $driverIds[$driverId] = $driverId;
            $totals[$vehicleId . '|' . $driverId . '|' . $fromDate . '|' . $toDate] = 0.0;
        }

        if ($vehicleIds === [] || $driverIds === []) {
            return $totals;
        }

        $builder = $this->db->table('despatch');
        $builder->select(' do_registration.do_no, despatch.vehicle_no, despatch.do_no as do_id, do_registration.do_registration_id, 
                            driver_assignment.driver, driver_assignment.from_date, driver_assignment.to_date,
                            do_registration.trip_expenses1, do_registration.trip_expenses2,
                            do_registration.trip_expenses3, do_registration.trip_expenses4, do_registration.trip_expenses5,
                            do_registration.trip_expenses6, DATE(despatch.des_date) as despatch_date, doprice_change.trip1
                            as doprice_trip1, doprice_change.trip2 as doprice_trip2, doprice_change.trip3 as doprice_trip3,
                            doprice_change.trip4 as doprice_trip4, doprice_change.trip5 as doprice_trip5, doprice_change.trip6 as doprice_trip6,
                            SUM(despatch.quantity) as total_weight, COUNT(DISTINCT despatch.despatch_id) as total_number_of_trip '
        );
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->join('doprice_change', 'do_registration.do_registration_id = doprice_change.dono AND despatch.des_date >= doprice_change.from_date', 'left');
        $builder->join('driver_assignment', 'driver_assignment.vehicle_no = despatch.vehicle_no', 'inner');
        $builder->whereIn('despatch.vehicle_no', array_values($vehicleIds));
        $builder->whereIn('driver_assignment.driver', array_values($driverIds));
        $builder->where('YEAR(despatch.des_date)', $year);
        $builder->where('MONTH(despatch.des_date)', $month);
        $builder->where('despatch.des_date >=', 'driver_assignment.from_date', false);
        $builder->where('despatch.des_date <=', 'driver_assignment.to_date', false);
        $builder->where('despatch.deleted_at IS NULL', null, false);
        $builder->where('despatch.deleted_by IS NULL', null, false);
        $builder->groupBy([
            'despatch.vehicle_no',
            'driver_assignment.driver',
            'driver_assignment.from_date',
            'driver_assignment.to_date',
            'despatch.do_no',
            'DATE(despatch.des_date)',
        ]);

        $result = $builder->get()->getResult();
        foreach ($result as $row) {
            $this->applyDayTripExpenseToRow($row);
            $key = (int) $row->vehicle_no . '|' . (int) $row->driver . '|' . (string) $row->from_date . '|' . (string) $row->to_date;
            if (isset($totals[$key])) {
                $totals[$key] += (float) $row->day_trip_expense;
            }
        }

        return $totals;
    }

    private function applyDayTripExpenseToRow(object $row): void
    {
        $tripCount = (int) ($row->total_number_of_trip ?? 0);
        if ($tripCount <= 0) {
            $row->day_trip_expense = 0;
            return;
        }

        $slab = min($tripCount, 6);
        $dopriceField = 'doprice_trip' . $slab;
        $tripField = 'trip_expenses' . $slab;
        $dopriceVal = $row->{$dopriceField} ?? null;

        $row->day_trip_expense = (float) (
            !empty($dopriceVal) ? $dopriceVal : ($row->{$tripField} ?? 0)
        );
    }

    // Helper function to determine trip expense based on total number of trips
    private function getTripExpense($base_expense, $total_number_of_trip)
    {
        switch ($total_number_of_trip) {
            case 1:
                return $base_expense; // $row->doprice_trip1 or $row->trip_expenses1
            case 2:
                return $base_expense; // $row->doprice_trip2 or $row->trip_expenses2
            case 3:
                return $base_expense; // $row->doprice_trip3 or $row->trip_expenses3
            case 4:
                return $base_expense; // $row->doprice_trip4 or $row->trip_expenses4
            case 5:
                return $base_expense; // $row->doprice_trip5 or $row->trip_expenses5
            default:
                return $base_expense; // For 6 or more trips, return $row->doprice_trip6 or $row->trip_expenses6
        }
    }



    function tripexpence2($vehicle_id, $year, $month)
    {
        // Step 1: Fetch the date range and driver IDs from driver_assignment
        $builder = $this->db->table('driver_assignment');
        $builder->select('from_date, to_date, driver')
            ->where('vehicle_no', $vehicle_id)
            ->where('YEAR(from_date)', $year)
            ->where('MONTH(from_date)', $month);

        $query = $builder->get();
        $driver_assignments = $query->getResult();

        // Return empty if no records found
        if (empty($driver_assignments)) {
            return [];
        }

        // Prepare an array to hold driver IDs
        $data['driver_ids'] = array_column($driver_assignments, 'driver');
        // Prepare an array to hold dispatch data
        $data['dispatch_data'] = []; // Initialize dispatch_data array

        // Step 2: Loop through each driver assignment to get dispatch records
        foreach ($driver_assignments as $assignment) {
            $from_date = $assignment->from_date;
            $to_date = $assignment->to_date;

            // Fetch data from the despatch table for each driver
            $builder = $this->db->table('despatch');
            $builder->where('vehicle_no', $vehicle_id)
                ->where('des_date >=', $from_date) // Using >= to include from_date
                ->where('des_date <=', $to_date); // Using <= to include to_date

            $query = $builder->get();

            // Merge the results into the dispatch_data array
            $data['dispatch_data'] = array_merge($data['dispatch_data'], $query->getResult());
        }

        // Return the aggregated results from the despatch table
        return $data;
    }



    public function stock_details($from_date = null, $to_date = null, $location = null)
    {
        $builder = $this->db->table('items i');
        $builder->select("
            i.id AS item_id,
            i.item_name,
            i.item_id AS item_code,
            u.unit_short_name,
            IFNULL(i.opening_stock, 0) AS opening_stock,
            IFNULL(i.amount, 0) AS amount,
            IFNULL(s.total_purchase, 0) AS purchase_stock,
            IFNULL(ts.transfer_out, 0) AS transfer_stock,
            IFNULL(im.total_consumed, 0) AS consumed_stock,
            (IFNULL(s.total_purchase, 0) - IFNULL(im.total_consumed, 0) - IFNULL(ts.transfer_out, 0)) AS available_stock
        ");

        $builder->join('units u', 'u.unit_id = i.unit_id', 'left');

        // ✅ Purchase Stock (only positive quantity)
        $builder->join("(
            SELECT sproduct_id, SUM(quantity) AS total_purchase
            FROM stock
            WHERE quantity > 0
            " . (!empty($from_date) && !empty($to_date) ? " AND date BETWEEN '{$from_date}' AND '{$to_date}'" : "") . "
            " . (!empty($location) ? " AND location_id = {$location}" : "") . "
            GROUP BY sproduct_id
        ) s", 's.sproduct_id = i.id', 'left');

        // ✅ Transfer Stock (negative quantity)
        $builder->join("(
            SELECT sproduct_id, SUM(ABS(quantity)) AS transfer_out
            FROM stock
            WHERE quantity < 0
            " . (!empty($from_date) && !empty($to_date) ? " AND date BETWEEN '{$from_date}' AND '{$to_date}'" : "") . "
            " . (!empty($location) ? " AND location_id = {$location}" : "") . "
            GROUP BY sproduct_id
        ) ts", 'ts.sproduct_id = i.id', 'left');

        // ✅ Inhouse Consumption
        $builder->join("(
            SELECT item AS sproduct_id, SUM(qty) AS total_consumed
            FROM inhouse_maintenance
            WHERE location IS NOT NULL AND location != ''
            " . (!empty($location) ? " AND location = '{$location}'" : "") . "
            GROUP BY item
        ) im", 'im.sproduct_id = i.id', 'left');

        // ✅ Include items with activity
        $builder->where("(s.total_purchase IS NOT NULL OR im.total_consumed IS NOT NULL OR ts.transfer_out IS NOT NULL)");

        $query = $builder->get();
        return $query->getResult();
    }


    public function Getvehicle_tyer()
    {
        $builder = $this->db->table('vehicle');
        $builder->select('vehicle.id, vehicle.vehicle_no, tyer_management.id as tyer_id, tyer_management.tyer_sl_no, tyer_management.tyer_position');
        $builder->join('tyer_management', 'vehicle.id = tyer_management.vehicle_id', 'left');
        $results = $builder->get()->getResult(); // Get result as array of objects

        // Organize data by vehicle ID for easier access in the view
        $vehicles = [];
        foreach ($results as $result) {
            $vehicle_id = $result->id;
            if (!isset($vehicles[$vehicle_id])) {
                $vehicles[$vehicle_id] = [
                    'id' => $result->id,
                    'vehicle_no' => $result->vehicle_no,
                    'tyer_position' => [],
                    'tyer_ids' => []
                ];
            }
            if ($result->tyer_position) {
                $vehicles[$vehicle_id]['tyer_position'][$result->tyer_position] = $result->tyer_sl_no;
                $vehicles[$vehicle_id]['tyer_ids'][$result->tyer_position] = $result->tyer_id;
            }
        }

        return $vehicles;
    }


    // function vehicle_inhouse($from_date, $to_date, $vehicle){
//     $builder = $this->db->table('inhouse_maintenance');
//     $builder->select('*'); 
//     $builder->where('inhouse_maintenance.vehicle', $vehicle);
//     $builder->where('inhouse_maintenance.date >=', $from_date);
//     $builder->where('inhouse_maintenance.date <=', $to_date);
//     return $builder->get()->getResult();
// }

    // function vehicle_outside($from_date, $to_date,$vehicle){
//     $builder = $this->db->table('outside_maintenance');
//     $builder->select('outside_maintenance.*, vehicle.vehicle_no'); 
//     $builder->join('vehicle', 'vehicle.id = outside_maintenance.vehicle_id');
//     $builder->where('outside_maintenance.vehicle_id', $vehicle);
//     $builder->where('outside_maintenance.date>=', $from_date);
//     $builder->where('outside_maintenance.date <=', $to_date);
//     return $builder->get()->getResult();
// }

    // function vehicle_deisel($from_date, $to_date,$vehicle)
// {
//      $builder = $this->db->table('diselentry');
//     $builder->select('*'); 
//     $builder->where('diselentry.vehicle_id', $vehicle);
//     $builder->where('diselentry.diesel_date >=', $from_date);
//     $builder->where('diselentry.diesel_date <=', $to_date);
//     return $builder->get()->getResult();
// }

    // function despatch_data($from_date, $to_date,$vehicle)
// {
//     $builder = $this->db->table('despatch');
//     $builder->select('despatch.*,do_registration.rate,do_registration.do_no as doregno'); 
//     $builder->where('despatch.vehicle_no', $vehicle);
//     $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');

    //     $builder->where('despatch.des_date >=', $from_date);
//     $builder->where('despatch.des_date <=', $to_date);
//     return $builder->get()->getResult();
// }

    function vehicle_inhouse($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('inhouse_maintenance');
        $builder->select('*');
        $builder->where('inhouse_maintenance.date >=', $from_date);
        $builder->where('inhouse_maintenance.date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('inhouse_maintenance.vehicle', $vehicle);
        }
        return $builder->get()->getResult();
    }


    function vehicle_outside($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('outside_maintenance');
        $builder->select('outside_maintenance.*, vehicle.vehicle_no');
        $builder->join('vehicle', 'vehicle.id = outside_maintenance.vehicle_id');
        $builder->where('outside_maintenance.date >=', $from_date);
        $builder->where('outside_maintenance.date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('outside_maintenance.vehicle_id', $vehicle);
        }
        return $builder->get()->getResult();
    }

    function vehicle_deisel($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('diselentry');
        $builder->select('*');
        $builder->where('diselentry.diesel_date >=', $from_date);
        $builder->where('diselentry.diesel_date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('diselentry.vehicle_id', $vehicle);
        }
        return $builder->get()->getResult();
    }
    function despatch_data($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('despatch');
        $builder->select('despatch.*, do_registration.rate, do_registration.do_no as doregno');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no');
        $builder->where('despatch.des_date >=', $from_date);
        $builder->where('despatch.des_date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('despatch.vehicle_no', $vehicle);
        }
        return $builder->get()->getResult();
    }

    // function driver_data($from_date, $to_date,$vehicle)
// {
//      $builder = $this->db->table('driver_assignment');
//         $builder->select('*'); 
//         $builder->where('driver_assignment.vehicle_no', $vehicle);
//         $builder->where('driver_assignment.from_date >=', $from_date);
//         $builder->where('driver_assignment.from_date <=', $to_date);
//         return $builder->get()->getResult();
// }

    function driver_data($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('driver_assignment');
        $builder->select('*');
        $builder->select('driver_assignment.*, vehicle.vehicle_no, vehicle.id as vehicle_id');
        $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no');

        $builder->where('driver_assignment.from_date >=', $from_date);
        $builder->where('driver_assignment.from_date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('driver_assignment.vehicle_no', $vehicle);
        }
        return $builder->get()->getResult();
    }



    function driver_salary_details_eport($year, $month, $driver_id)
    {

        $builder = $this->db->table('staff');
        $builder->select('staff.*, 
                      location.location_name,
                      IFNULL(FORMAT(staff_advance.total_advance, 2), "0.00") as total_advance,
                      driver_assignment.from_date,
                      driver_assignment.to_date,
                      driver_assignment.vehicle_no as assignment_vehicle_no,
                      vehicle.vehicle_no,
                      adjust_salary.amount');
        $builder->join('adjust_salary', 'adjust_salary.driver_id = staff.id', 'left'); // Join with adjust_salary
        $builder->join('location', 'location.location_id = staff.address', 'left');
        $builder->where('staff.user_type', 'DRIVER');
        if (!empty($driver_id)) {
            $builder->where('staff.id', $driver_id);
        }

        // Subquery for staff advances
        $subQueryAdvance = $this->db->table('staff_advance')
            ->select('staff_id, COALESCE(SUM(amount), 0) AS total_advance')
            ->where("YEAR(created_at)", $year)
            ->where("MONTH(created_at)", $month)
            ->groupBy('staff_id')
            ->getCompiledSelect();

        // Subquery for driver assignments and vehicle details
        $subQueryAssignment = $this->db->table('driver_assignment')
            ->select('driver AS staff_id, 
                                             from_date, 
                                             to_date, 
                                             vehicle_no')
            ->where("YEAR(from_date)", $year)
            ->where("MONTH(from_date)", $month)
            ->getCompiledSelect();

        // Join the subqueries with the main query
        $builder->join("($subQueryAdvance) AS staff_advance", 'staff_advance.staff_id = staff.id', 'left');
        $builder->join("($subQueryAssignment) AS driver_assignment", 'driver_assignment.staff_id = staff.id', 'left');
        $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');

        return $builder->get()->getResult();
    }

    function overalexpence_data($loc_id, $from_date, $to_date)
    {


        $builder = $this->db->table('overall_expence');
        $builder->select('*');
        $builder->where('overall_expence.location_id', $loc_id);
        $builder->join('location', 'overall_expence.location_id = location.location_id');
        $builder->where("DATE(overall_expence.created_at) >=", $from_date);
        $builder->where("DATE(overall_expence.created_at) <=", $to_date);
        return $builder->get()->getResult();

    }

    function satury_data($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('statutory');
        $builder->select('statutory.*, vehicle.vehicle_no');
        $builder->join('vehicle', 'vehicle.id=statutory.vehicle_id');

        // Convert the datetime string to date for comparison
        $builder->where("DATE(statutory.created_by) >=", $from_date);
        $builder->where("DATE(statutory.created_by) <=", $to_date);

        // Optionally filter by vehicle ID if needed
        if ($vehicle !== null) {
            $builder->where('statutory.vehicle_id', $vehicle);
        }

        return $builder->get()->getResult();
    }

    function getTyreDetailsBySlNo($tyer_sl_no)
    {
        return $this->db->table('tyer_management')
            ->select('*')
            ->where('tyer_sl_no', $tyer_sl_no)
            ->get()
            ->getRow();
    }
    function all_repaire_report()
    {
        $builder = $this->db->table('other_report');
        $builder->select('*');
        return $builder->get()->getResult();
    }

    function tripexpence3($vehicle_id, $driver_id, $year, $month)
    {
        // Second Query to fetch despatch data and matching with the driver
        $builder = $this->db->table('despatch');
        $builder->select('
        do_registration.do_no,
        despatch.vehicle_no,
        do_registration.do_registration_id,
        driver_assignment.driver,
        do_registration.trip_expenses1,
        do_registration.trip_expenses2,
        do_registration.trip_expenses3,
        do_registration.trip_expenses4,
        do_registration.trip_expenses5,
        do_registration.trip_expenses6,
        DATE(despatch.des_date) as despatch_date,
        doprice_change.trip1 as doprice_trip1,
        doprice_change.trip2 as doprice_trip2,
        doprice_change.trip3 as doprice_trip3,
        doprice_change.trip4 as doprice_trip4,
        doprice_change.trip5 as doprice_trip5,
        doprice_change.trip6 as doprice_trip6,
        SUM(despatch.quantity) as total_weight,
        COUNT(despatch.despatch_id) as total_number_of_trip
    ');

        // Joining necessary tables
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->join('doprice_change', 'do_registration.do_registration_id = doprice_change.dono AND despatch.des_date >= doprice_change.from_date', 'left');
        $builder->join('driver_assignment', 'driver_assignment.vehicle_no = despatch.vehicle_no', 'left');

        // Apply filters
        $builder->where('despatch.vehicle_no', $vehicle_id);
        $builder->where("YEAR(despatch.des_date)", $year);
        $builder->where("MONTH(despatch.des_date)", $month);

        // Add filter to match the driver
        $builder->where('driver_assignment.driver', $driver_id); // Match with the drivers from the first query
        $builder->where('despatch.des_date >=', 'driver_assignment.from_date', false);
        $builder->where('despatch.des_date <=', 'driver_assignment.to_date', false);

        // Group by the date only
        $builder->groupBy('DATE(despatch.des_date)');

        // Fetch the result
        $result = $builder->get()->getResult();

        // Loop through the result to calculate day trip expense
        foreach ($result as $row) {
            // Determine the day_trip_expense based on the total_number_of_trip
            switch ($row->total_number_of_trip) {
                case 1:
                    // Use doprice_trip1 if available, otherwise fallback to trip_expenses1
                    $row->day_trip_expense = !empty($row->doprice_trip1) ? $this->getTripExpense($row->doprice_trip1, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses1, $row->total_number_of_trip);
                    break;
                case 2:
                    // Use doprice_trip2 if available, otherwise fallback to trip_expenses2
                    $row->day_trip_expense = !empty($row->doprice_trip2) ? $this->getTripExpense($row->doprice_trip2, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses2, $row->total_number_of_trip);
                    break;
                case 3:
                    // Use doprice_trip3 if available, otherwise fallback to trip_expenses3
                    $row->day_trip_expense = !empty($row->doprice_trip3) ? $this->getTripExpense($row->doprice_trip3, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses3, $row->total_number_of_trip);
                    break;
                case 4:
                    // Use doprice_trip4 if available, otherwise fallback to trip_expenses4
                    $row->day_trip_expense = !empty($row->doprice_trip4) ? $this->getTripExpense($row->doprice_trip4, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses4, $row->total_number_of_trip);
                    break;
                case 5:
                    // Use doprice_trip5 if available, otherwise fallback to trip_expenses5
                    $row->day_trip_expense = !empty($row->doprice_trip5) ? $this->getTripExpense($row->doprice_trip5, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses5, $row->total_number_of_trip);
                    break;
                case 6:
                    // Use doprice_trip6 if available, otherwise fallback to trip_expenses6
                    $row->day_trip_expense = !empty($row->doprice_trip6) ? $this->getTripExpense($row->doprice_trip6, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses6, $row->total_number_of_trip);
                    break;
                default:
                    // Fallback to trip_expenses1 if no matching case
                    $row->day_trip_expense = !empty($row->doprice_trip1) ? $this->getTripExpense($row->doprice_trip1, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses1, $row->total_number_of_trip);
                    break;
            }
        }

        return $result;
    }


    public function getPaymentById($table, $id)
    {
        return $this->db->table($table)->where('pay_id', $id)->get()->getRowArray();
    }

    public function getAllStaffAndVendors()
    {
        $staffBuilder = $this->db->table('staff');
        $staffBuilder->select('id, name, user_type,"staff" as source');
        $staff = $staffBuilder->get()->getResult();
        $vendorBuilder = $this->db->table('vendor');
        $vendorBuilder->select('id, name, type as user_type,"vendor" as source');
        $vendors = $vendorBuilder->get()->getResult();
        return array_merge($staff, $vendors);
    }
    function GetAllPaymentVoucher($from_date, $to_date)
    {
        if (empty($from_date)) {
            $from_date = date('Y-m-01'); // First day of current month
        }

        if (empty($to_date)) {
            $to_date = date('Y-m-d'); // Today's date
        }

        $builder = $this->db->table('payment_voucher');

        // Select fields with alias to avoid name conflict
        $builder->select('
			payment_voucher.*,
			location.location_name,
			staff.name AS staff_name,
			staff.staff_code AS staff_code,
			vendor.name AS vendor_name,
			vendor.type AS vendor_type,
			bank.bank_name
		');

        $builder->join('location', 'location.location_id = payment_voucher.location_id', 'left');
        $builder->join('staff', 'staff.id = payment_voucher.staff_id AND (payment_voucher.user_type = "DRIVER" OR payment_voucher.user_type = "STAFF")', 'left');
        $builder->join('vendor', 'vendor.id = payment_voucher.staff_id AND (payment_voucher.user_type = "Party" OR payment_voucher.user_type = "Pump" OR payment_voucher.user_type = "Vendor")', 'left');
        $builder->join('bank', 'bank.id = payment_voucher.bank', 'left');
        $builder->where('payment_voucher.deleted_by', null);
        $builder->where('pay_date >=', $from_date);
        $builder->where('pay_date <=', $to_date);

        return $builder->get()->getResult();
    }


    public function GetAllPaymentVoucherByUserType($from_date = null, $to_date = null, $pump_id = null, $user_type = null, $payment_type = null)
    {
        if (empty($from_date)) {
            $from_date = date('Y-m-01');
        }
        if (empty($to_date)) {
            $to_date = date('Y-m-d');
        }
        $builder = $this->db->table('payment_voucher');
        $builder->select('
            payment_voucher.*,
            location.location_name,
            vendor.name AS vendor_name,vendor.bal,
            vendor.type AS vendor_type,
            bank.bank_name
        ');
        $builder->join('location', 'location.location_id = payment_voucher.location_id', 'left');
        $builder->join('vendor', 'vendor.id = payment_voucher.staff_id', 'left'); // Only vendor-related users
        $builder->join('bank', 'bank.id = payment_voucher.bank', 'left');
        $builder->where('payment_voucher.deleted_by', null);
        $builder->where('payment_voucher.pay_date >=', $from_date);
        $builder->where('payment_voucher.pay_date <=', $to_date);
        if (!empty($user_type)) {
            $builder->where('payment_voucher.staff_id', $pump_id);
        }
        if (!empty($user_type)) {
            $builder->where('payment_voucher.user_type', $user_type);
        }
        if (!empty($payment_type)) {
            $builder->where('payment_voucher.credit_debit', $payment_type);
        }
        return $builder->get()->getResult();
    }

    function GetAllVendor($type)
    {
        $builder = $this->db->table('vendor');
        $builder->select('*');
        $builder->where('vendor.type', $type);
        return $builder->get()->getResult();
    }
    public function getItemsByLocation($locationId)
    {
        return $this->db->table('stock s')
            ->select('
                s.sproduct_id,
                i.item_name,
                s.rate,
                u.unit_name,
                i.item_id,
                SUM(s.available_qty) AS total_stock_qty,
                IFNULL((
                    SELECT SUM(im.qty) 
                    FROM inhouse_maintenance im
                    WHERE im.item = s.sproduct_id 
                    AND im.location = s.location_id
                ), 0) AS total_inhouse_qty,
                (SUM(s.available_qty) - IFNULL((
                    SELECT SUM(im.qty) 
                    FROM inhouse_maintenance im
                    WHERE im.item = s.sproduct_id 
                    AND im.location = s.location_id
                ), 0)) AS available_qty
            ')
            ->join('items i', 'i.id = s.sproduct_id')
            ->join('units u', 'u.unit_id = i.unit_id', 'left')
            ->where('s.location_id', $locationId)
            ->groupBy('s.sproduct_id, i.item_name, u.unit_name, i.item_id')
            ->get()
            ->getResult();
    }
    public function getGroupDetails()
    {
        return $this->db->table('group g')
            ->select('g.*')
            ->get()
            ->getResult();
    }
    public function getyearsDetails()
    {
        return $this->db->table('financial_year fy')
            ->select('fy.*')
            ->get()
            ->getResult();
    }
    public function getAllUser()
    {
        return $this->db->table('user')
            ->select('user.*')
            ->where('user_type !=', 1)   // exclude admin
            ->get()
            ->getResult();
    }
    public function getAllTasks()
    {
        $tasks = $this->db->table('tasks t')
            ->select('t.*, u.full_name as assigned_by_name')
            ->join('user u', 'u.id = t.assigned_by', 'left')
            ->orderBy('t.id', 'DESC')
            ->get()->getResult();
        return $this->resolveTaskUserNames($tasks);
    }
    public function getTasksByUser($user_id)
    {
        $uid = (int) $user_id;
        $tasks = $this->db->table('tasks t')
            ->select('t.*, u.full_name as assigned_by_name')
            ->join('user u', 'u.id = t.assigned_by', 'left')
            ->where("FIND_IN_SET({$uid}, t.assigned_to) > 0", null, false)
            ->orderBy('t.id', 'DESC')
            ->get()->getResult();
        return $this->resolveTaskUserNames($tasks);
    }
    /**
     * Given raw task rows (with CSV assigned_to / cc columns),
     * resolve the user names and attach them as display-ready strings.
     */
    private function resolveTaskUserNames(array $tasks): array
    {
        if (empty($tasks)) return [];

        // Collect all user IDs referenced across all tasks
        $allIds = [];
        foreach ($tasks as $t) {
            foreach (array_filter(array_map('intval', explode(',', (string)($t->assigned_to ?? '')))) as $id) $allIds[$id] = true;
            foreach (array_filter(array_map('intval', explode(',', (string)($t->cc         ?? '')))) as $id) $allIds[$id] = true;
        }

        // Fetch all needed users in one query
        $userMap = [];
        if (!empty($allIds)) {
            $rows = $this->db->table('user')->select('id, full_name')->whereIn('id', array_keys($allIds))->get()->getResult();
            foreach ($rows as $r) $userMap[(int)$r->id] = $r->full_name;
        }

        // Attach resolved names to each task
        foreach ($tasks as $t) {
            $atIds = array_filter(array_map('intval', explode(',', (string)($t->assigned_to ?? ''))));
            $ccIds = array_filter(array_map('intval', explode(',', (string)($t->cc         ?? ''))));
            $t->assigned_to_name = implode(', ', array_map(fn($id) => $userMap[$id] ?? '', $atIds));
            $t->cc_name          = implode(', ', array_map(fn($id) => $userMap[$id] ?? '', $ccIds));
        }

        return $tasks;
    }
    public function getHistoryRecords($filters = [])
    {
        $tyre_ids = [];
        if (!empty($filters['tyre_id'])) {
            $tyre_ids[] = (int) $filters['tyre_id'];

            // Find all ancestors in the replacement chain
            $current_id = (int) $filters['tyre_id'];
            while ($current_id) {
                $tyre = $this->db->table('tyer_management')
                    ->select('replaced_from_id')
                    ->where('id', $current_id)
                    ->get()
                    ->getRow();

                if ($tyre && !empty($tyre->replaced_from_id)) {
                    $tyre_ids[] = (int) $tyre->replaced_from_id;
                    $current_id = (int) $tyre->replaced_from_id;
                } else {
                    $current_id = null;
                }
            }
        }

        $builder = $this->db->table('tyer_management_history h')
            ->select([
                'h.*',
                'tm.tyer_sl_no',
                'tm.brand_name',
                'tm.tyer_type',
                'v.vehicle_no',
                've.name AS vendor_name',
                'l.location_name',
                'lf.location_name AS from_location',
                'lt.location_name AS to_location',
            ])
            ->join('tyer_management tm', 'tm.id = h.tyre_id', 'left')
            ->join('vehicle v', 'v.id = h.vehicle_id', 'left')
            ->join('vendor ve', 've.id = h.vendor_id', 'left')
            ->join('location l', 'l.location_id = h.location_id', 'left')
            ->join('location lf', 'lf.location_id = h.transfer_from', 'left')
            ->join('location lt', 'lt.location_id = h.transfer_to', 'left')
            ->orderBy('h.event_date', 'ASC')
            ->orderBy('h.tyre_history_id', 'ASC');

        // Filter by tyre IDs (including ancestors if applicable)
        if (!empty($tyre_ids)) {
            $builder->whereIn('h.tyre_id', $tyre_ids);
        }

        // Filter by event type
        if (!empty($filters['event_type'])) {
            $builder->where('h.event_type', $filters['event_type']);
        }

        return $builder->get()->getResult();
    }

    // Add this method to get single tyre details
    public function getTyreById($id)
    {
        return $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name, ven.name as vendor_name, veh.vehicle_no, parent.tyer_sl_no as replaced_from_serial, child.tyer_sl_no as replaced_to_serial')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('vendor ven', 'ven.id = tm.vendor_id', 'left')
            ->join('vehicle veh', 'veh.id = tm.vehicle_id', 'left')
            ->join('tyer_management parent', 'parent.id = tm.replaced_from_id', 'left')
            ->join('tyer_management child', 'child.id = tm.replaced_to_id', 'left')
            ->where('tm.id', $id)
            ->get()
            ->getRow();
    }

    public function get_driver_daily_report_data($driver_id, $from_date, $to_date)
    {
        $driver = $this->db->table('staff')->where('id', $driver_id)->get()->getRow();
        $driver_name = $driver ? $driver->name : '-';

        $assignments = $this->db->table('driver_assignment')
            ->select('driver_assignment.*, vehicle.vehicle_no as truck_number')
            ->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left')
            ->where('driver_assignment.driver', $driver_id)
            ->groupStart()
            ->where('driver_assignment.from_date <=', $to_date)
            ->groupStart()
            ->where('driver_assignment.to_date >=', $from_date)
            ->orWhere('driver_assignment.to_date IS NULL')
            ->groupEnd()
            ->groupEnd()
            ->orderBy('driver_assignment.from_date', 'DESC')
            ->orderBy('driver_assignment.id', 'DESC')
            ->get()->getResult();

        $v_ids = !empty($assignments) ? array_column($assignments, 'vehicle_no') : [0];

        // 2. Trips and Expenses
        $builder = $this->db->table('despatch');
        $builder->select('despatch.des_date, despatch.vehicle_no, COUNT(despatch.despatch_id) as trips, 
                          do_registration.trip_expenses1, do_registration.trip_expenses2, do_registration.trip_expenses3, 
                          do_registration.trip_expenses4, do_registration.trip_expenses5, do_registration.trip_expenses6,
                          doprice_change.trip1 as doprice_trip1, doprice_change.trip2 as doprice_trip2, doprice_change.trip3 as doprice_trip3,
                          doprice_change.trip4 as doprice_trip4, doprice_change.trip5 as doprice_trip5, doprice_change.trip6 as doprice_trip6');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->join('doprice_change', 'do_registration.do_registration_id = doprice_change.dono AND despatch.des_date >= doprice_change.from_date', 'left');
        $builder->where('despatch.des_date >=', $from_date);
        $builder->where('despatch.des_date <=', $to_date);
        $builder->whereIn('despatch.vehicle_no', $v_ids);
        $builder->groupBy(['despatch.des_date', 'despatch.vehicle_no']);
        $trips_data = $builder->get()->getResult();

        // 3. Cash Paid
        $cash_data = $this->db->table('staff_advance')
            ->select('adv_date, SUM(amount) as total_cash')
            ->where('staff_id', $driver_id)
            ->where('adv_date >=', $from_date)
            ->where('adv_date <=', $to_date)
            ->groupBy('adv_date')
            ->get()->getResult();

        // 4. Diesel Issued
        $diesel_data = $this->db->table('diselentry')
            ->select('diesel_date, vehicle_id, SUM(qty) as total_diesel')
            ->where('diesel_date >=', $from_date)
            ->where('diesel_date <=', $to_date);
        if (!empty($v_ids)) {
            $diesel_data->whereIn('vehicle_id', $v_ids);
        }
        $diesel_data = $diesel_data->groupBy(['diesel_date', 'vehicle_id'])
            ->get()->getResult();

        // 5. Build Result array
        $result = [];
        $current = strtotime($from_date);
        $end = strtotime($to_date);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);

            $active_asgn = null;
            foreach ($assignments as $asgn) {
                if ($date >= $asgn->from_date && ($asgn->to_date === null || $date <= $asgn->to_date)) {
                    $active_asgn = $asgn;
                    break;
                }
            }

            $day_info = [
                'date' => date('d-m-Y', $current),
                'truck_no' => $active_asgn ? $active_asgn->truck_number : '-',
                'driver_name' => $driver_name,
                'trips' => 0,
                'accrued_expense' => 0,
                'cash_paid' => 0,
                'diesel_issued' => 0,
                'opening_hsd' => $active_asgn ? $active_asgn->opening_hsd : 0,
                'closing_hsd' => $active_asgn ? $active_asgn->closing_hsd : 0,
                'opening_km' => $active_asgn ? $active_asgn->opening_km : 0,
                'closing_km' => $active_asgn ? $active_asgn->closing_km : 0,
                'remarks' => ''
            ];

            if ($active_asgn) {
                foreach ($trips_data as $td) {
                    if ($td->des_date == $date && $td->vehicle_no == $active_asgn->vehicle_no) {
                        $day_info['trips'] = $td->trips;
                        $count = $td->trips;
                        $expense = 0;
                        $reg_col = "trip_expenses" . ($count <= 6 ? $count : 6);
                        $doprice_col = "doprice_trip" . ($count <= 6 ? $count : 6);

                        if (!empty($td->$doprice_col)) {
                            $expense = $this->getTripExpense($td->$doprice_col, $count);
                        } else {
                            $expense = $this->getTripExpense($td->$reg_col, $count);
                        }
                        $day_info['accrued_expense'] = $expense;
                        break;
                    }
                }

                foreach ($diesel_data as $dd) {
                    if ($dd->diesel_date == $date && $dd->vehicle_id == $active_asgn->vehicle_no) {
                        $day_info['diesel_issued'] = $dd->total_diesel;
                        break;
                    }
                }
            }

            foreach ($cash_data as $cd) {
                if ($cd->adv_date == $date) {
                    $day_info['cash_paid'] = $cd->total_cash;
                    break;
                }
            }

            $result[] = $day_info;
            $current = strtotime("+1 day", $current);
        }

        return $result;
    }

    public function get_vehicle_daily_report_data($vehicle_id, $from_date, $to_date)
    {
        $assignments = $this->db->table('driver_assignment')
            ->select('driver_assignment.*, staff.name as driver_name, vehicle.vehicle_no as truck_number')
            ->join('staff', 'staff.id = driver_assignment.driver', 'left')
            ->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left')
            ->where('driver_assignment.vehicle_no', $vehicle_id)
            ->groupStart()
            ->where('driver_assignment.from_date <=', $to_date)
            ->groupStart()
            ->where('driver_assignment.to_date >=', $from_date)
            ->orWhere('driver_assignment.to_date IS NULL')
            ->groupEnd()
            ->groupEnd()
            ->orderBy('driver_assignment.from_date', 'DESC')
            ->orderBy('driver_assignment.id', 'DESC')
            ->get()->getResult();

        // 2. Trips and Expenses
        $builder = $this->db->table('despatch');
        $builder->select('despatch.des_date, despatch.vehicle_no, COUNT(despatch.despatch_id) as trips, 
                          do_registration.trip_expenses1, do_registration.trip_expenses2, do_registration.trip_expenses3, 
                          do_registration.trip_expenses4, do_registration.trip_expenses5, do_registration.trip_expenses6,
                          doprice_change.trip1 as doprice_trip1, doprice_change.trip2 as doprice_trip2, doprice_change.trip3 as doprice_trip3,
                          doprice_change.trip4 as doprice_trip4, doprice_change.trip5 as doprice_trip5, doprice_change.trip6 as doprice_trip6');
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left');
        $builder->join('doprice_change', 'do_registration.do_registration_id = doprice_change.dono AND despatch.des_date >= doprice_change.from_date', 'left');
        $builder->where('despatch.des_date >=', $from_date);
        $builder->where('despatch.des_date <=', $to_date);
        $builder->where('despatch.vehicle_no', $vehicle_id);
        $builder->groupBy(['despatch.des_date']);
        $trips_data = $builder->get()->getResult();

        // 3. Diesel Issued
        $diesel_data = $this->db->table('diselentry')
            ->select('diesel_date, SUM(qty) as total_diesel')
            ->where('diesel_date >=', $from_date)
            ->where('diesel_date <=', $to_date)
            ->where('vehicle_id', $vehicle_id)
            ->groupBy('diesel_date')
            ->get()->getResult();

        // 4. Cash Paid (Need to link via drivers assigned to this vehicle)
        $cash_builder = $this->db->table('staff_advance');
        $cash_builder->select('adv_date, staff_id, SUM(amount) as total_cash');
        $cash_builder->where('adv_date >=', $from_date);
        $cash_builder->where('adv_date <=', $to_date);
        $driver_ids = !empty($assignments) ? array_unique(array_column($assignments, 'driver')) : [0];
        $cash_builder->whereIn('staff_id', $driver_ids);
        $cash_builder->groupBy(['adv_date', 'staff_id']);
        $cash_data = $cash_builder->get()->getResult();

        // 5. Build Result array
        $result = [];
        $current = strtotime($from_date);
        $end = strtotime($to_date);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);

            $active_asgn = null;
            foreach ($assignments as $asgn) {
                if ($date >= $asgn->from_date && ($asgn->to_date === null || $date <= $asgn->to_date)) {
                    $active_asgn = $asgn;
                    break;
                }
            }

            $day_info = [
                'date' => date('d-m-Y', $current),
                'truck_no' => $active_asgn ? $active_asgn->truck_number : '-',
                'driver_name' => $active_asgn ? $active_asgn->driver_name : '-',
                'trips' => 0,
                'accrued_expense' => 0,
                'cash_paid' => 0,
                'diesel_issued' => 0,
                'opening_hsd' => $active_asgn ? $active_asgn->opening_hsd : 0,
                'closing_hsd' => $active_asgn ? $active_asgn->closing_hsd : 0,
                'remarks' => ''
            ];

            foreach ($trips_data as $td) {
                if ($td->des_date == $date) {
                    $day_info['trips'] = $td->trips;
                    $count = $td->trips;
                    $expense = 0;
                    $reg_col = "trip_expenses" . ($count <= 6 ? $count : 6);
                    $doprice_col = "doprice_trip" . ($count <= 6 ? $count : 6);

                    if (!empty($td->$doprice_col)) {
                        $expense = $this->getTripExpense($td->$doprice_col, $count);
                    } else {
                        $expense = $this->getTripExpense($td->$reg_col, $count);
                    }
                    $day_info['accrued_expense'] = $expense;
                    break;
                }
            }

            foreach ($diesel_data as $dd) {
                if ($dd->diesel_date == $date) {
                    $day_info['diesel_issued'] = $dd->total_diesel;
                    break;
                }
            }

            if ($active_asgn) {
                foreach ($cash_data as $cd) {
                    if ($cd->adv_date == $date && $cd->staff_id == $active_asgn->driver) {
                        $day_info['cash_paid'] = $cd->total_cash;
                        break;
                    }
                }
            }

            $result[] = $day_info;
            $current = strtotime("+1 day", $current);
        }

        return $result;
    }

    public function getNextVoucherNo($type)
    {
        $prefix = '';
        if ($type == 'Payment')
            $prefix = 'PAYV-';
        elseif ($type == 'Receipt')
            $prefix = 'RECV-';
        elseif ($type == 'Journal')
            $prefix = 'JRNL-';

        $builder = $this->db->table('account_vouchers');
        $builder->select('voucher_no');
        $builder->where('voucher_type', $type);
        $builder->orderBy('id', 'DESC');
        $builder->limit(1);
        $row = $builder->get()->getRow();

        if ($row) {
            $lastNo = (int) str_replace($prefix, '', $row->voucher_no);
            $nextNo = $lastNo + 1;
        } else {
            $nextNo = 1;
        }

        return $prefix . str_pad($nextNo, 5, '0', STR_PAD_LEFT);
    }

    public function saveVoucher($voucherData, $entries)
    {
        $this->db->transBegin();

        $this->db->table('account_vouchers')->insert($voucherData);
        $db_err = $this->db->error();
        if ($db_err['code'] !== 0) {
            session()->setFlashdata('last_db_error', $db_err['message']);
            $this->db->transRollback();
            return false;
        }

        $voucherId = $this->db->insertID();

        if (!$voucherId) {
            session()->setFlashdata('last_db_error', 'Failed to get insert ID for voucher');
            $this->db->transRollback();
            return false;
        }

        foreach ($entries as $entry) {
            $entry['voucher_id'] = $voucherId;
            $this->db->table('account_voucher_entries')->insert($entry);
            $db_err = $this->db->error();
            if ($db_err['code'] !== 0) {
                session()->setFlashdata('last_db_error', $db_err['message']);
                $this->db->transRollback();
                return false;
            }
        }

        if ($this->db->transStatus() === FALSE) {
            $db_err = $this->db->error();
            session()->setFlashdata('last_db_error', $db_err['message'] ?: 'Transaction status failed');
            $this->db->transRollback();
            return false;
        } else {
            $this->db->transCommit();
            return true;
        }
    }

    public function getVouchers($filters = [])
    {
        $builder = $this->db->table('account_vouchers v');
        $builder->select('v.*, fy.from_date as fy_from, fy.to_date as fy_to');
        $builder->join('financial_year fy', 'fy.fy_id = v.fy_id', 'left');

        if (!empty($filters['from_date'])) {
            $builder->where('v.voucher_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $builder->where('v.voucher_date <=', $filters['to_date']);
        }
        if (!empty($filters['voucher_type'])) {
            $builder->where('v.voucher_type', $filters['voucher_type']);
        }

        $builder->orderBy('v.voucher_date', 'DESC');
        $builder->orderBy('v.id', 'DESC');

        return $builder->get()->getResult();
    }

    public function getVoucherDetails($voucher_id)
    {
        $voucher = $this->db->table('account_vouchers v')
            ->select('v.*, fy.from_date as fy_from, fy.to_date as fy_to')
            ->join('financial_year fy', 'fy.fy_id = v.fy_id')
            ->where('v.id', $voucher_id)
            ->get()->getRow();

        if ($voucher) {
            $entries = $this->db->table('account_voucher_entries e')
                ->select('e.*')
                ->where('e.voucher_id', $voucher_id)
                ->get()->getResult();

            foreach ($entries as &$entry) {
                $entry->ledger_name = 'Unknown';
                $entry->group_name = 'Unknown';
                $group = $this->db->table('group_master')->where('group_id', $entry->group_id)->get()->getRow();
                if ($group)
                    $entry->group_name = $group->group_name;

                switch ($entry->group_id) {
                    case 6:
                        $row = $this->db->table('vendor')->where('id', $entry->ledger_id)->get()->getRow();
                        if ($row)
                            $entry->ledger_name = $row->name;
                        break;
                    case 5:
                    case 4:
                        $row = $this->db->table('staff')->where('id', $entry->ledger_id)->get()->getRow();
                        if ($row)
                            $entry->ledger_name = $row->name;
                        break;
                    case 3:
                        $row = $this->db->table('vehicle')->where('id', $entry->ledger_id)->get()->getRow();
                        if ($row)
                            $entry->ledger_name = $row->vehicle_no;
                        break;
                    case 2:
                        $row = $this->db->table('location')->where('location_id', $entry->ledger_id)->get()->getRow();
                        if ($row)
                            $entry->ledger_name = $row->location_name;
                        break;
                    case 7:
                        $row = $this->db->table('bank')->where('id', $entry->ledger_id)->get()->getRow();
                        if ($row)
                            $entry->ledger_name = $row->bank_name;
                        break;
                    default:
                        $row = $this->db->table('ledger')->where('ledger_id', $entry->ledger_id)->get()->getRow();
                        if ($row)
                            $entry->ledger_name = $row->ledger_name;
                        break;
                }
            }

            return [
                'voucher' => $voucher,
                'entries' => $entries
            ];
        }
        return null;
    }

    public function getLedgerStatement($group_id = null, $ledger_id = null, $from_date = null, $to_date = null, $voucher_type = null)
    {
        $opening_bal = 0;
        $ledger_name = 'All Vouchers';

        // 1. Specific Ledger Mode: Calculate Opening Balance
        if ($group_id && $ledger_id) {
            $initial_opening_bal = 0;
            switch ($group_id) {
                case 6: // Vendor
                    $row = $this->db->table('vendor')->where('id', $ledger_id)->get()->getRow();
                    if ($row) {
                        $bal = $row->bal ?? 0;
                        $type = $row->transaction_type ?? ($row->type ?? 'CR');
                        $initial_opening_bal = ($type == 'DR' ? $bal : -$bal);
                        $ledger_name = $row->name;
                    }
                    break;
                case 5: // Staff
                case 4: // Driver
                    $row = $this->db->table('staff')->where('id', $ledger_id)->get()->getRow();
                    if ($row) {
                        $bal = $row->opening_balance ?? 0;
                        $type = $row->transaction_type ?? 'DR';
                        $initial_opening_bal = ($type == 'DR' ? $bal : -$bal);
                        $ledger_name = $row->name;
                    }
                    break;
                case 3: // Vehicle
                    $row = $this->db->table('vehicle')->where('id', $ledger_id)->get()->getRow();
                    if ($row) {
                        $bal = $row->opening_balance ?? 0;
                        $initial_opening_bal = $bal;
                        $ledger_name = $row->vehicle_no;
                    }
                    break;
                case 2: // Cash Book (Location)
                    $row = $this->db->table('location')->where('location_id', $ledger_id)->get()->getRow();
                    if ($row) {
                        $bal = $row->opening_balance ?? 0;
                        // Locations typically have DR opening balances if positive
                        $initial_opening_bal = $bal;
                        $ledger_name = $row->location_name;
                    }
                    break;
                case 7: // Bank
                    $row = $this->db->table('bank')->where('id', $ledger_id)->get()->getRow();
                    if ($row) {
                        $bal = $row->opening_balance ?? 0;
                        $initial_opening_bal = $bal;
                        $ledger_name = $row->bank_name;
                    }
                    break;
                default:
                    $row = $this->db->table('ledger')->where('ledger_id', $ledger_id)->get()->getRow();
                    if ($row) {
                        $bal = $row->balance ?? 0;
                        $type = $row->transaction_type ?? 'DR';
                        $initial_opening_bal = ($type == 'DR' ? $bal : -$bal);
                        $ledger_name = $row->ledger_name;
                    }
                    break;
            }

            // Sum transactions BEFORE from_date to get current opening balance
            $prev_query = $this->db->table('account_voucher_entries e')
                ->select('SUM(CASE WHEN e.entry_type = 1 THEN e.amount ELSE -e.amount END) as total')
                ->join('account_vouchers v', 'v.id = e.voucher_id')
                ->where('e.group_id', $group_id)
                ->where('e.ledger_id', $ledger_id);

            if ($from_date) {
                $prev_query->where('v.voucher_date <', $from_date);
            }

            $prev_transactions = $prev_query->get()->getRow();
            $opening_bal = $initial_opening_bal + ($prev_transactions->total ?? 0);
        }

        // 2. Get transactions within date range (or all if no date)
        $builder = $this->db->table('account_voucher_entries e')
            ->select('e.*, v.voucher_no, v.voucher_type, v.voucher_date, v.narration as voucher_narration')
            ->select("(CASE 
                WHEN e.group_id = 6 THEN (SELECT name FROM vendor WHERE id = e.ledger_id)
                WHEN e.group_id IN (4, 5) THEN (SELECT name FROM staff WHERE id = e.ledger_id)
                WHEN e.group_id = 3 THEN (SELECT vehicle_no FROM vehicle WHERE id = e.ledger_id)
                WHEN e.group_id = 2 THEN (SELECT location_name FROM location WHERE location_id = e.ledger_id)
                WHEN e.group_id = 7 THEN (SELECT bank_name FROM bank WHERE id = e.ledger_id)
                ELSE (SELECT ledger_name FROM ledger WHERE ledger_id = e.ledger_id)
            END) as resolved_ledger_name")
            ->join('account_vouchers v', 'v.id = e.voucher_id');

        if ($group_id) {
            $builder->where('e.group_id', $group_id);
        }
        if ($ledger_id) {
            $builder->where('e.ledger_id', $ledger_id);
        }
        if ($from_date) {
            $builder->where('v.voucher_date >=', $from_date);
        }
        if ($to_date) {
            $builder->where('v.voucher_date <=', $to_date);
        }
        if ($voucher_type) {
            $builder->where('v.voucher_type', $voucher_type);
        }

        $entries = $builder->orderBy('v.voucher_date', 'ASC')->orderBy('v.id', 'ASC')->get()->getResult();

        return [
            'ledger' => (object) ['ledger_name' => $ledger_name],
            'opening_bal' => $opening_bal,
            'entries' => $entries
        ];
    }


    /**
     * Record tyre swap/replacement for admin/tyre_exchange_report.
     *
     * @return bool True when inserted or already exists
     */
    public function recordTyreExchange(
        ?int $vehicleId,
        int $fromTyreId,
        int $toTyreId,
        ?string $tyrePosition,
        string $exchangeDate,
        string $remarks = ''
    ): bool {
        $fromTyreId   = (int) $fromTyreId;
        $toTyreId     = (int) $toTyreId;
        $exchangeDate = trim($exchangeDate);
        $remarks      = trim($remarks);

        if ($fromTyreId <= 0 || $toTyreId <= 0 || $exchangeDate === '' || $fromTyreId === $toTyreId) {
            return false;
        }

        $exists = $this->db->table('tyre_exchange_history')
            ->where('from_tyre_id', $fromTyreId)
            ->where('to_tyre_id', $toTyreId)
            ->countAllResults();

        if ($exists > 0) {
            return true;
        }

        return (bool) $this->db->table('tyre_exchange_history')->insert([
            'vehicle_id'    => $vehicleId !== null && $vehicleId > 0 ? $vehicleId : null,
            'from_tyre_id'  => $fromTyreId,
            'to_tyre_id'    => $toTyreId,
            'tyre_position' => $tyrePosition !== null && trim($tyrePosition) !== '' ? trim($tyrePosition) : null,
            'exchange_date' => $exchangeDate,
            'remarks'       => $remarks !== '' ? $remarks : null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Backfill tyre_exchange_history from past assignment/vendor exchange data.
     */
    public function backfillTyreExchangeHistory(): void
    {
        $pairs = $this->db->query("
            SELECT
                rem.vehicle_id,
                rem.tyre_id AS from_tyre_id,
                ass.tyre_id AS to_tyre_id,
                rem.tyre_position,
                rem.event_date AS exchange_date
            FROM tyer_management_history rem
            INNER JOIN tyer_management_history ass
                ON ass.vehicle_id = rem.vehicle_id
                AND ass.tyre_position = rem.tyre_position
                AND ass.event_date = rem.event_date
                AND ass.event_type = 3
            WHERE rem.event_type = 4
              AND rem.tyre_id != ass.tyre_id
            ORDER BY rem.event_date ASC, rem.tyre_history_id ASC
        ")->getResult();

        foreach ($pairs as $row) {
            $this->recordTyreExchange(
                $row->vehicle_id !== null ? (int) $row->vehicle_id : null,
                (int) $row->from_tyre_id,
                (int) $row->to_tyre_id,
                $row->tyre_position ?? null,
                (string) $row->exchange_date,
                'Tyre replaced on vehicle from stock'
            );
        }

        $vendorRows = $this->db->table('tyer_management tm')
            ->select('tm.id AS to_tyre_id, tm.replaced_from_id AS from_tyre_id, tm.vehicle_id, tm.tyer_position, tm.asign_date, tm.date')
            ->where('tm.replaced_from_id IS NOT NULL', null, false)
            ->where('tm.replaced_from_id >', 0)
            ->get()
            ->getResult();

        foreach ($vendorRows as $row) {
            $exchangeDate = trim((string) ($row->asign_date ?? $row->date ?? ''));
            if ($exchangeDate === '') {
                $exchangeDate = date('Y-m-d');
            }

            $this->recordTyreExchange(
                $row->vehicle_id !== null ? (int) $row->vehicle_id : null,
                (int) $row->from_tyre_id,
                (int) $row->to_tyre_id,
                $row->tyer_position ?? null,
                $exchangeDate,
                'Vendor warranty exchange'
            );
        }
    }

    /**
     * Get detailed history of tyre exchanges between vehicles and stock
     */
    public function getExchangeHistory($filters = [])
    {
        $this->backfillTyreExchangeHistory();

        $builder = $this->db->table('tyre_exchange_history eh')
            ->select([
                'eh.*',
                'v.vehicle_no',
                'old_t.tyer_sl_no AS old_serial',
                'old_t.brand_name AS old_brand',
                'new_t.tyer_sl_no AS new_serial',
                'new_t.brand_name AS new_brand'
            ])
            ->join('vehicle v', 'v.id = eh.vehicle_id', 'left')
            ->join('tyer_management old_t', 'old_t.id = eh.from_tyre_id', 'left')
            ->join('tyer_management new_t', 'new_t.id = eh.to_tyre_id', 'left');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('v.vehicle_no', $s)
                ->orLike('old_t.tyer_sl_no', $s)
                ->orLike('new_t.tyer_sl_no', $s)
                ->orLike('eh.remarks', $s)
                ->groupEnd();
        }

        if (!empty($filters['vehicle_id'])) {
            $builder->where('eh.vehicle_id', $filters['vehicle_id']);
        }

        return $builder->orderBy('eh.exchange_date', 'DESC')
            ->orderBy('eh.id', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Single exchange record for admin/tyre_exchange_report detail.
     */
    public function getExchangeHistoryById(int $exchangeId): ?object
    {
        $exchangeId = (int) $exchangeId;
        if ($exchangeId <= 0) {
            return null;
        }

        return $this->db->table('tyre_exchange_history eh')
            ->select([
                'eh.*',
                'v.vehicle_no',
                'old_t.tyer_sl_no AS old_serial',
                'old_t.brand_name AS old_brand',
                'new_t.tyer_sl_no AS new_serial',
                'new_t.brand_name AS new_brand',
            ])
            ->join('vehicle v', 'v.id = eh.vehicle_id', 'left')
            ->join('tyer_management old_t', 'old_t.id = eh.from_tyre_id', 'left')
            ->join('tyer_management new_t', 'new_t.id = eh.to_tyre_id', 'left')
            ->where('eh.id', $exchangeId)
            ->get()
            ->getRow();
    }

    /**
     * Tyre purchase bills grouped list (admin/tyer_management).
     *
     * @param array{
     *     location_id?: int,
     *     vendor_id?: int,
     *     bill_no?: string,
     *     from_date?: string,
     *     to_date?: string,
     *     search?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getTyrePurchaseBillList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select(
                'MAX(tm.id) AS id, tm.bill_no, MAX(tm.date) AS date, tm.vendor_id, tm.location_id,'
                . ' MAX(tm.brand_name) AS brand_name, MAX(tm.model) AS model, MAX(tm.price) AS price,'
                . ' l.location_name, v.name AS vendor_name, COUNT(tm.id) AS qty',
                false
            )
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('vendor v', 'v.id = tm.vendor_id', 'left');

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $vendorId = (int) ($filters['vendor_id'] ?? 0);
        if ($vendorId > 0) {
            $builder->where('tm.vendor_id', $vendorId);
        }

        $billNo = trim((string) ($filters['bill_no'] ?? ''));
        if ($billNo !== '') {
            $builder->where('tm.bill_no', $billNo);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('tm.date >=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('tm.date <=', $toDate);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.bill_no', $search)
                ->orLike('v.name', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('l.location_name', $search)
            ->groupEnd();
        }

        return $builder
            ->groupBy('tm.bill_no, tm.location_id, tm.vendor_id, l.location_name, v.name')
            ->orderBy('MAX(tm.id)', 'DESC', false)
            ->get()
            ->getResult();
    }

    /**
     * Serial numbers for a purchase bill (admin/tyer_management → getTyerDetailsByBillNo).
     *
     * @return list<object>
     */
    public function getTyreSerialsByBillNo(string $billNo, int $locationId = 0): array
    {
        $billNo = trim($billNo);
        if ($billNo === '') {
            return [];
        }

        $builder = $this->db->table('tyer_management')
            ->select('id, tyer_sl_no, tyer_type, brand_name, model, location_id, bill_no, date, price')
            ->where('bill_no', $billNo);

        if ($locationId > 0) {
            $builder->where('location_id', $locationId);
        }

        return $builder->orderBy('id', 'ASC')->get()->getResult();
    }

    /**
     * Full purchase bill detail (admin/tyer_management → edit_tyer / view bill).
     *
     * @return array{header: object, tyres: list<object>}|null
     */
    public function getTyrePurchaseBillDetail(int $tyreId = 0, string $billNo = '', int $locationId = 0): ?array
    {
        if ($tyreId > 0) {
            $seed = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
            if ($seed === null) {
                return null;
            }

            $billNo = trim((string) ($seed->bill_no ?? ''));
        }

        $billNo = trim($billNo);
        if ($billNo === '') {
            return null;
        }

        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name, v.name AS vendor_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('vendor v', 'v.id = tm.vendor_id', 'left')
            ->where('tm.bill_no', $billNo);

        // When opened via list row id, web edit_tyer loads all tyres for bill_no.
        if ($tyreId <= 0 && $locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $tyres = $builder->orderBy('tm.id', 'ASC')->get()->getResult();
        if ($tyres === []) {
            return null;
        }

        return [
            'header' => $tyres[0],
            'tyres'  => $tyres,
        ];
    }

    /**
     * Store new tyre purchase bill (admin/insert_tyer → addtyerbill).
     *
     * @param array{
     *     bill_no: string,
     *     vendor_id: int,
     *     date: string,
     *     price: float|string|int,
     *     location_id: int,
     *     brand_name: string,
     *     model: string
     * } $header
     * @param list<array{tyer_sl_no: string, tyer_type: string}> $tyreLines
     *
     * @return array{bill_no: string, inserted_ids: list<int>}|null
     */
    public function storeTyrePurchaseBill(array $header, array $tyreLines): ?array
    {
        if ($tyreLines === []) {
            return null;
        }

        $billNo = trim((string) ($header['bill_no'] ?? ''));
        if ($billNo === '') {
            return null;
        }

        $locationId = (int) ($header['location_id'] ?? 0);
        $vendorId   = (int) ($header['vendor_id'] ?? 0);
        $date       = trim((string) ($header['date'] ?? ''));

        $inserted = [];

        $this->db->transStart();

        foreach ($tyreLines as $line) {
            $serial = trim((string) ($line['tyer_sl_no'] ?? ''));
            $type   = trim((string) ($line['tyer_type'] ?? ''));
            if ($serial === '' || $type === '') {
                $this->db->transRollback();

                return null;
            }

            $this->db->table('tyer_management')->insert([
                'location_id' => $locationId,
                'brand_name'  => trim((string) ($header['brand_name'] ?? '')),
                'tyer_type'   => $type,
                'model'       => trim((string) ($header['model'] ?? '')),
                'tyer_sl_no'  => $serial,
                'vendor_id'   => $vendorId,
                'bill_no'     => $billNo,
                'price'       => $header['price'] ?? 0,
                'status'      => 1,
                'date'        => $date,
            ]);

            $newId = (int) $this->db->insertID();
            if ($newId <= 0) {
                $this->db->transRollback();

                return null;
            }

            $inserted[] = $newId;
        }

        $tyres = $this->db->table('tyer_management')
            ->select('id')
            ->where('bill_no', $billNo)
            ->where('date', $date)
            ->get()
            ->getResult();

        foreach ($tyres as $tyre) {
            $tyreId = (int) ($tyre->id ?? 0);
            if ($tyreId <= 0) {
                continue;
            }

            $exists = $this->db->table('tyer_management_history')
                ->where('tyre_id', $tyreId)
                ->where('event_type', 1)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('tyer_management_history')->insert([
                'tyre_id'     => $tyreId,
                'event_type'  => 1,
                'location_id' => $locationId,
                'event_date'  => $date,
                'vendor_id'   => $vendorId,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'bill_no'      => $billNo,
            'inserted_ids' => $inserted,
        ];
    }

    /**
     * Update purchase bill tyres (admin/update_tyer).
     *
     * @param array{
     *     bill_no: string,
     *     vendor_id: int,
     *     date: string,
     *     price: float|string|int,
     *     location_id: int,
     *     brand_name: string,
     *     model: string
     * } $header
     * @param list<array{tyre_id?: int, tyer_sl_no: string, tyer_type: string}> $tyreLines
     *
     * @return array{inserted: list<int>, updated: list<int>, bill_no: string}|null
     */
    public function updateTyrePurchaseBill(array $header, array $tyreLines): ?array
    {
        if ($tyreLines === []) {
            return null;
        }

        $billNo = trim((string) ($header['bill_no'] ?? ''));
        if ($billNo === '') {
            return null;
        }

        $inserted = [];
        $updated  = [];

        $this->db->transStart();

        foreach ($tyreLines as $line) {
            $tyreId = (int) ($line['tyre_id'] ?? 0);
            $serial = trim((string) ($line['tyer_sl_no'] ?? ''));
            $type   = trim((string) ($line['tyer_type'] ?? ''));

            if ($serial === '' || $type === '') {
                $this->db->transRollback();

                return null;
            }

            $duplicate = $this->db->table('tyer_management')
                ->where('tyer_sl_no', $serial);
            if ($tyreId > 0) {
                $duplicate->where('id !=', $tyreId);
            }
            if ($duplicate->countAllResults() > 0) {
                $this->db->transRollback();

                return null;
            }

            $data = [
                'location_id' => (int) ($header['location_id'] ?? 0),
                'brand_name'  => trim((string) ($header['brand_name'] ?? '')),
                'tyer_type'   => $type,
                'model'       => trim((string) ($header['model'] ?? '')),
                'tyer_sl_no'  => $serial,
                'vendor_id'   => (int) ($header['vendor_id'] ?? 0),
                'bill_no'     => $billNo,
                'price'       => $header['price'] ?? 0,
                'status'      => 1,
                'date'        => trim((string) ($header['date'] ?? '')),
            ];

            if ($tyreId <= 0) {
                $this->db->table('tyer_management')->insert($data);
                $newId = (int) $this->db->insertID();
                if ($newId <= 0) {
                    $this->db->transRollback();

                    return null;
                }

                $inserted[] = $newId;
            } else {
                $exists = $this->db->table('tyer_management')->where('id', $tyreId)->countAllResults();
                if ($exists === 0) {
                    $this->db->transRollback();

                    return null;
                }

                $this->db->table('tyer_management')->where('id', $tyreId)->update($data);
                $updated[] = $tyreId;
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'bill_no'  => $billNo,
            'inserted' => $inserted,
            'updated'  => $updated,
        ];
    }

    /**
     * Delete entire purchase bill (all tyres with same bill_no).
     * Same as web admin/delete_tyer/{id}:
     * 1) resolve bill_no from list row tyre id
     * 2) delete all rows where bill_no matches
     *
     * @return array{bill_no: string, deleted_count: int, deleted_ids: list<int>}|null
     */
    public function deleteTyrePurchaseBillByTyreId(int $tyreId): ?array
    {
        $seed = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
        if ($seed === null) {
            return null;
        }

        $billNo = trim((string) ($seed->bill_no ?? ''));
        if ($billNo === '') {
            return null;
        }

        $rows = $this->db->table('tyer_management')
            ->select('id')
            ->where('bill_no', $billNo)
            ->get()
            ->getResult();

        if ($rows === []) {
            return null;
        }

        $deletedIds = [];
        foreach ($rows as $row) {
            $deletedIds[] = (int) ($row->id ?? 0);
        }

        $this->db->table('tyer_management')->where('bill_no', $billNo)->delete();

        return [
            'bill_no'       => $billNo,
            'deleted_count' => count($deletedIds),
            'deleted_ids'   => $deletedIds,
        ];
    }

    /**
     * Delete single tyre record.
     * Same as web admin/delete_tyersingle/{id}.
     */
    public function deleteTyrePurchaseSingle(int $tyreId): bool
    {
        $exists = $this->db->table('tyer_management')->where('id', $tyreId)->countAllResults();
        if ($exists === 0) {
            return false;
        }

        $this->db->table('tyer_management')->where('id', $tyreId)->delete();

        return true;
    }

    /**
     * Tyres available at location for transfer (admin/tyreTransfer → get_tyers_by_location).
     *
     * @return list<object>
     */
    public function getTransferTyresByLocation(int $locationId): array
    {
        if ($locationId <= 0) {
            return [];
        }

        return $this->db->table('tyer_management')
            ->select('id, tyer_sl_no, brand_name, model, tyer_type, location_id, status')
            ->where('location_id', $locationId)
            ->where('status', 1)
            ->orderBy('tyer_sl_no', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Tyre brand/model by serial (admin/tyreTransfer → get_tyer_details).
     */
    public function getTyreTransferDetailBySerial(string $serial): ?object
    {
        $serial = trim($serial);
        if ($serial === '') {
            return null;
        }

        return $this->db->table('tyer_management')
            ->select('id, tyer_sl_no, brand_name, model, location_id, status')
            ->where('tyer_sl_no', $serial)
            ->get()
            ->getRow();
    }

    /**
     * Transfer tyres to another location (admin/update_tyer_details).
     *
     * @param list<string> $serials
     *
     * @return array{
     *     from_location_id: int|null,
     *     to_location_id: int,
     *     transfer_date: string,
     *     transferred_count: int,
     *     tyre_serials: list<string>,
     *     tyres: list<array{tyre_id: int, tyre_serial: string, brand_name: string|null, model: string|null}>
     * }|null
     */
    public function transferTyresToLocation(int $fromLocationId, int $toLocationId, string $date, array $serials): ?array
    {
        $toLocationId = (int) $toLocationId;
        $date         = trim($date);
        if ($toLocationId <= 0 || $date === '') {
            return null;
        }

        $uniqueSerials = [];
        foreach ($serials as $serial) {
            $serial = trim((string) $serial);
            if ($serial === '') {
                continue;
            }

            $uniqueSerials[$serial] = $serial;
        }

        if ($uniqueSerials === []) {
            return null;
        }

        $this->db->transStart();

        foreach ($uniqueSerials as $serial) {
            $this->db->table('tyer_management')
                ->where('tyer_sl_no', $serial)
                ->update([
                    'location_id'   => $toLocationId,
                    'transfer_date' => $date,
                ]);
        }

        $tyres = $this->db->table('tyer_management')
            ->select('id, tyer_sl_no, brand_name, model')
            ->whereIn('tyer_sl_no', array_values($uniqueSerials))
            ->get()
            ->getResult();

        $transferred = [];
        foreach ($tyres as $tyre) {
            $history = [
                'tyre_id'       => (int) ($tyre->id ?? 0),
                'event_type'    => 2,
                'location_id'   => $toLocationId,
                'event_date'    => $date,
                'transfer_from' => $fromLocationId > 0 ? $fromLocationId : null,
                'transfer_to'   => $toLocationId,
            ];

            $this->db->table('tyer_management_history')->insert($history);

            $transferred[] = [
                'tyre_id'     => (int) ($tyre->id ?? 0),
                'tyre_serial' => $tyre->tyer_sl_no ?? null,
                'brand_name'  => $tyre->brand_name ?? null,
                'model'       => $tyre->model ?? null,
            ];
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'from_location_id'  => $fromLocationId > 0 ? $fromLocationId : null,
            'to_location_id'    => $toLocationId,
            'transfer_date'     => $date,
            'transferred_count' => count($transferred),
            'tyre_serials'      => array_values($uniqueSerials),
            'tyres'             => $transferred,
        ];
    }

    /**
     * Active locations for dropdowns (admin/tyreTransfer).
     *
     * @return list<object>
     */
    public function getActiveLocationList(): array
    {
        return $this->db->table('location')
            ->select('location_id, location_name, status')
            ->groupStart()
                ->where('status IS NULL', null, false)
                ->orWhere('status', 'Active')
            ->groupEnd()
            ->orderBy('location_name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Stock tyres not on any vehicle (admin/StockTyer_management).
     *
     * @param array{
     *     location_id?: int,
     *     from_date?: string,
     *     to_date?: string,
     *     search?: string,
     *     tyre_condition?: string,
     *     brand_name?: string,
     *     tyer_type?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getStockTyreList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name')
            ->select(
                'CASE WHEN tm.asign_date IS NOT NULL THEN "Old" ELSE "New" END AS tyre_condition',
                false
            )
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.vehicle_id', null)
            ->where('tm.status', 1)
            ->groupBy('tm.id');

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('tm.date >=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('tm.date <=', $toDate);
        }

        $brandName = trim((string) ($filters['brand_name'] ?? ''));
        if ($brandName !== '') {
            $builder->where('tm.brand_name', $brandName);
        }

        $tyerType = trim((string) ($filters['tyer_type'] ?? ''));
        if ($tyerType !== '') {
            $builder->where('tm.tyer_type', $tyerType);
        }

        $condition = strtolower(trim((string) ($filters['tyre_condition'] ?? '')));
        if ($condition === 'new') {
            $builder->where('tm.asign_date', null);
        } elseif ($condition === 'old') {
            $builder->where('tm.asign_date IS NOT NULL', null, false);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.tyer_sl_no', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('tm.bill_no', $search)
                ->orLike('l.location_name', $search)
            ->groupEnd();
        }

        return $builder->orderBy('tm.date', 'DESC')
            ->orderBy('tm.id', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Scrap yard tyres (admin/scrapTyer_management) — status = 3.
     *
     * @param array{
     *     location_id?: int,
     *     from_date?: string,
     *     to_date?: string,
     *     search?: string,
     *     brand_name?: string,
     *     tyer_type?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getScrapTyreList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.status', 3);

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('tm.date >=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('tm.date <=', $toDate);
        }

        $brandName = trim((string) ($filters['brand_name'] ?? ''));
        if ($brandName !== '') {
            $builder->where('tm.brand_name', $brandName);
        }

        $tyerType = trim((string) ($filters['tyer_type'] ?? ''));
        if ($tyerType !== '') {
            $builder->where('tm.tyer_type', $tyerType);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.tyer_sl_no', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('tm.bill_no', $search)
                ->orLike('l.location_name', $search)
            ->groupEnd();
        }

        return $builder->orderBy('tm.id', 'DESC')->get()->getResult();
    }

    /**
     * Tyres sent to vendor / exchange requested (admin/sentToVendorTyer_management) — status = 10.
     *
     * @param array{
     *     location_id?: int,
     *     from_date?: string,
     *     to_date?: string,
     *     search?: string,
     *     brand_name?: string,
     *     tyer_type?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getSentToVendorTyreList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name, v.name as vendor_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('vendor v', 'v.id = tm.vendor_id', 'left')
            ->where('tm.status', 10);

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('tm.date >=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('tm.date <=', $toDate);
        }

        $brandName = trim((string) ($filters['brand_name'] ?? ''));
        if ($brandName !== '') {
            $builder->where('tm.brand_name', $brandName);
        }

        $tyerType = trim((string) ($filters['tyer_type'] ?? ''));
        if ($tyerType !== '') {
            $builder->where('tm.tyer_type', $tyerType);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.tyer_sl_no', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('tm.bill_no', $search)
                ->orLike('l.location_name', $search)
                ->orLike('v.name', $search)
            ->groupEnd();
        }

        return $builder->orderBy('tm.id', 'DESC')->get()->getResult();
    }

    /**
     * Sold tyres (admin/soldTyer_management) — status = 7.
     *
     * @param array{
     *     location_id?: int,
     *     vendor_id?: int,
     *     from_date?: string,
     *     to_date?: string,
     *     search?: string,
     *     brand_name?: string,
     *     tyer_type?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getSoldTyreList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name, v.name as vendor_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('vendor v', 'v.id = tm.vendor_id', 'left')
            ->where('tm.status', 7);

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $vendorId = (int) ($filters['vendor_id'] ?? 0);
        if ($vendorId > 0) {
            $builder->where('tm.vendor_id', $vendorId);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('tm.selling_date >=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('tm.selling_date <=', $toDate);
        }

        $brandName = trim((string) ($filters['brand_name'] ?? ''));
        if ($brandName !== '') {
            $builder->where('tm.brand_name', $brandName);
        }

        $tyerType = trim((string) ($filters['tyer_type'] ?? ''));
        if ($tyerType !== '') {
            $builder->where('tm.tyer_type', $tyerType);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.tyer_sl_no', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('tm.bill_no', $search)
                ->orLike('tm.remark', $search)
                ->orLike('l.location_name', $search)
                ->orLike('v.name', $search)
            ->groupEnd();
        }

        return $builder->orderBy('tm.selling_date', 'DESC')
            ->orderBy('tm.id', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Cancel sale and restore sold tyres (admin/soldTyreBackToStock).
     *
     * @param list<int>  $tyreIds
     * @param 'stock'|'scrap' $destination
     *
     * @return list<int>
     */
    public function restoreSoldTyres(array $tyreIds, string $destination = 'stock'): array
    {
        $destination = strtolower(trim($destination));
        if (! in_array($destination, ['stock', 'scrap'], true)) {
            return [];
        }

        $status      = $destination === 'scrap' ? 3 : 1;
        $remarkText  = $destination === 'scrap'
            ? 'Sale cancelled, restored to scrap yard'
            : 'Sale cancelled, restored to stock';
        $eventType   = $destination === 'scrap' ? 9 : 6;
        $eventRemarks = $destination === 'scrap'
            ? 'Sale cancelled, tyre restored to scrap yard'
            : 'Sale cancelled, tyre restored to stock';

        $restored = [];

        if ($tyreIds === []) {
            return $restored;
        }

        $this->db->transStart();

        foreach ($tyreIds as $tyreId) {
            $tyreId = (int) $tyreId;
            if ($tyreId <= 0) {
                continue;
            }

            $tyre = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
            if ($tyre === null || (int) ($tyre->status ?? 0) !== 7) {
                continue;
            }

            $this->db->table('tyer_management')->where('id', $tyreId)->update([
                'status'       => $status,
                'selling_date' => null,
                'remark'       => $remarkText,
            ]);

            $this->db->table('tyer_management_history')->insert([
                'tyre_id'    => $tyreId,
                'event_type' => $eventType,
                'event_date' => date('Y-m-d'),
                'remarks'    => $eventRemarks,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $restored[] = $tyreId;
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false ? $restored : [];
    }

    /**
     * Tyre inventory report (admin/tyer_report) — excludes in-stock (status != 1).
     *
     * @param array{
     *     location_id?: int,
     *     status?: int|string|null,
     *     search?: string,
     *     brand_name?: string,
     *     tyer_type?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getTyreReportList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name', false)
            ->select(
                'CASE WHEN th.tyre_id IS NULL THEN "New" ELSE "Old" END AS tyre_condition',
                false
            )
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('tyer_management_history th', 'th.tyre_id = tm.id', 'left')
            ->where('tm.status !=', 1)
            ->groupBy('tm.id');

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $status = $filters['status'] ?? null;
        if ($status !== null && $status !== '' && $status !== 'all') {
            $builder->where('tm.status', (int) $status);
        }

        $brandName = trim((string) ($filters['brand_name'] ?? ''));
        if ($brandName !== '') {
            $builder->where('tm.brand_name', $brandName);
        }

        $tyerType = trim((string) ($filters['tyer_type'] ?? ''));
        if ($tyerType !== '') {
            $builder->where('tm.tyer_type', $tyerType);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.tyer_sl_no', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('tm.bill_no', $search)
                ->orLike('l.location_name', $search)
            ->groupEnd();
        }

        return $builder->orderBy('tm.id', 'DESC')->get()->getResult();
    }

    /**
     * Tyres under repair (admin/repaire_report) — status = 4.
     *
     * @param array{
     *     location_id?: int,
     *     search?: string,
     *     brand_name?: string,
     *     tyer_type?: string
     * } $filters
     *
     * @return list<object>
     */
    public function getRepairReportList(array $filters = []): array
    {
        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name, v.name AS exchange_vendorname')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('vendor v', 'v.id = tm.ex_ven_id', 'left')
            ->where('tm.status', 4);

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('tm.location_id', $locationId);
        }

        $brandName = trim((string) ($filters['brand_name'] ?? ''));
        if ($brandName !== '') {
            $builder->where('tm.brand_name', $brandName);
        }

        $tyerType = trim((string) ($filters['tyer_type'] ?? ''));
        if ($tyerType !== '') {
            $builder->where('tm.tyer_type', $tyerType);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tm.tyer_sl_no', $search)
                ->orLike('tm.brand_name', $search)
                ->orLike('tm.model', $search)
                ->orLike('tm.bill_no', $search)
                ->orLike('l.location_name', $search)
                ->orLike('v.name', $search)
                ->orLike('tm.remark', $search)
            ->groupEnd();
        }

        return $builder->orderBy('tm.id', 'DESC')->get()->getResult();
    }

    /**
     * Move repaired tyre back to stock (admin/repaire_report → update_tyer_repair).
     *
     * @return bool
     */
    public function completeRepairBackToStock(int $tyreId, int $vendorId, int $locationId, string $stockDate, string $remark = ''): bool
    {
        $tyreId     = (int) $tyreId;
        $vendorId   = (int) $vendorId;
        $locationId = (int) $locationId;
        $stockDate  = trim($stockDate);
        $remark     = trim($remark);

        if ($tyreId <= 0 || $vendorId <= 0 || $locationId <= 0 || $stockDate === '') {
            return false;
        }

        $tyre = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
        if ($tyre === null || (int) ($tyre->status ?? 0) !== 4) {
            return false;
        }

        $historyRemark = $remark !== '' ? $remark : 'Tyre repaired and updated to stock';

        $this->db->transStart();

        $this->db->table('tyer_management')->where('id', $tyreId)->update([
            'vendor_id'   => $vendorId,
            'location_id' => $locationId,
            'date'        => $stockDate,
            'status'      => 1,
            'remark'      => $historyRemark,
        ]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'     => $tyreId,
            'event_type'  => 6,
            'location_id' => $locationId,
            'event_date'  => $stockDate,
            'vendor_id'   => $vendorId,
            'remarks'     => $historyRemark,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    /**
     * Distinct tyre brands for vendor exchange dropdown (admin/vendor_exchange).
     *
     * @return list<object>
     */
    public function getDistinctTyreBrands(): array
    {
        return $this->db->table('tyer_management')
            ->select('brand_name')
            ->distinct()
            ->where('brand_name IS NOT NULL')
            ->where('brand_name !=', '')
            ->orderBy('brand_name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Restore scrap-yard tyres to stock (admin/scrapTyreBackToStock, bulkScrapTyreBackToStock).
     *
     * @param list<int> $tyreIds
     *
     * @return list<int> Successfully restored tyre ids
     */
    public function restoreScrapTyresToStock(array $tyreIds): array
    {
        $restored = [];

        if ($tyreIds === []) {
            return $restored;
        }

        $this->db->transStart();

        foreach ($tyreIds as $tyreId) {
            $tyreId = (int) $tyreId;
            if ($tyreId <= 0) {
                continue;
            }

            $tyre = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
            if ($tyre === null || (int) ($tyre->status ?? 0) !== 3) {
                continue;
            }

            $this->db->table('tyer_management')->where('id', $tyreId)->update([
                'status' => 1,
                'remark' => 'Back from scrap yard to stock',
            ]);

            $this->db->table('tyer_management_history')->insert([
                'tyre_id'    => $tyreId,
                'event_type' => 1,
                'event_date' => date('Y-m-d'),
                'remarks'    => 'Tyre restored from scrap yard to stock',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $restored[] = $tyreId;
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false ? $restored : [];
    }

    /**
     * Sell scrap-yard tyres (admin/process_tyre_sale).
     *
     * @param list<int> $tyreIds
     *
     * @return list<int> Successfully sold tyre ids
     */
    public function sellScrapTyres(array $tyreIds, int $vendorId, string $sellingDate, string $remark = ''): array
    {
        $sold = [];
        $remark = trim($remark);

        if ($tyreIds === [] || $vendorId <= 0) {
            return $sold;
        }

        $this->db->transStart();

        foreach ($tyreIds as $tyreId) {
            $tyreId = (int) $tyreId;
            if ($tyreId <= 0) {
                continue;
            }

            $tyre = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
            if ($tyre === null || (int) ($tyre->status ?? 0) !== 3) {
                continue;
            }

            $this->db->table('tyer_management')->where('id', $tyreId)->update([
                'status'       => 7,
                'vendor_id'    => $vendorId,
                'selling_date' => $sellingDate,
                'remark'       => $remark !== '' ? "SOLD: {$remark}" : 'SOLD',
            ]);

            $this->db->table('tyer_management_history')->insert([
                'tyre_id'    => $tyreId,
                'event_type' => 7,
                'event_date' => $sellingDate,
                'vendor_id'  => $vendorId,
                'remarks'    => "TYRE SOLD to Buyer ID {$vendorId}. {$remark}",
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $sold[] = $tyreId;
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false ? $sold : [];
    }

    /**
     * Update tyre status (admin/tyer_exchange → update_tyer_report).
     * Clears vehicle assignment and logs tyer_management_history.
     */
    public function updateTyreLifecycleStatus(int $tyreId, int $status, ?int $vendorId = null, string $remark = ''): bool
    {
        $tyre = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
        if ($tyre === null) {
            return false;
        }

        $update = [
            'status'        => $status,
            'ex_ven_id'     => $vendorId > 0 ? $vendorId : null,
            'remark'        => $remark,
            'vehicle_id'    => null,
            'tyer_position' => null,
        ];

        $eventType = match ($status) {
            1       => 6,
            3       => 9,
            4       => 5,
            7       => 7,
            default => 8,
        };

        $this->db->transStart();

        $this->db->table('tyer_management')->where('id', $tyreId)->update($update);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'    => $tyreId,
            'event_type' => $eventType,
            'event_date' => date('Y-m-d'),
            'vendor_id'  => $vendorId > 0 ? $vendorId : null,
            'remarks'    => $remark,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    /**
     * Request vendor exchange (admin/StockTyer_management → sent_to_vendor).
     */
    public function requestTyreExchange(int $tyreId, string $remark = ''): bool
    {
        if ($tyreId <= 0) {
            return false;
        }

        $tyre = $this->db->table('tyer_management')->where('id', $tyreId)->get()->getRow();
        if ($tyre === null) {
            return false;
        }

        $remarkText = trim($remark);

        $this->db->transStart();

        $this->db->table('tyer_management')->where('id', $tyreId)->update([
            'status' => 10,
            'remark' => $remarkText !== '' ? "EXCHANGE REQUESTED: {$remarkText}" : 'EXCHANGE REQUESTED',
        ]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id'    => $tyreId,
            'event_type' => 10,
            'event_date' => date('Y-m-d'),
            'remarks'    => $remarkText !== '' ? "EXCHANGE INITIATED: {$remarkText}" : 'EXCHANGE INITIATED',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    /**
     * Complete vendor warranty exchange (admin/process_vendor_exchange).
     *
     * @return array{old_tyre_id: int, new_tyre_id: int}|null
     */
    public function storeVendorTyreExchange(
        int $oldTyreId,
        string $newSerial,
        string $brandName,
        string $newModel = '',
        string $exchangeDate = '',
        string $remark = ''
    ): ?array {
        if ($oldTyreId <= 0 || trim($newSerial) === '' || trim($brandName) === '') {
            return null;
        }

        $newSerial = trim($newSerial);
        $brandName = trim($brandName);
        $newModel  = trim($newModel);
        $remark    = trim($remark);
        $exchangeDate = trim($exchangeDate) !== '' ? trim($exchangeDate) : date('Y-m-d');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $exchangeDate)) {
            return null;
        }

        $serialExists = $this->db->table('tyer_management')
            ->where('tyer_sl_no', $newSerial)
            ->countAllResults();

        if ($serialExists > 0) {
            return null;
        }

        $oldTyre = $this->db->table('tyer_management')->where('id', $oldTyreId)->get()->getRow();
        if ($oldTyre === null) {
            return null;
        }

        $newStatus = $oldTyre->vehicle_id !== null ? 2 : 1;

        $this->db->transStart();

        $newData = [
            'brand_name'       => $brandName,
            'model'            => $newModel,
            'tyer_sl_no'       => $newSerial,
            'tyer_type'        => $oldTyre->tyer_type,
            'vendor_id'        => $oldTyre->vendor_id,
            'date'             => date('Y-m-d'),
            'bill_no'          => $oldTyre->bill_no,
            'price'            => $oldTyre->price,
            'status'           => $newStatus,
            'location_id'      => $oldTyre->location_id,
            'vehicle_id'       => $oldTyre->vehicle_id,
            'tyer_position'    => $oldTyre->tyer_position,
            'asign_date'       => $oldTyre->vehicle_id !== null ? $exchangeDate : null,
            'remark'           => "Received as warranty replacement for {$oldTyre->tyer_sl_no}. {$remark}",
            'replaced_from_id' => $oldTyreId,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tyer_management')->insert($newData);
        $newTyreId = (int) $this->db->insertID();

        if ($newTyreId <= 0) {
            $this->db->transRollback();

            return null;
        }

        $this->db->table('tyer_management')->where('id', $oldTyreId)->update([
            'status'          => 11,
            'vehicle_id'      => null,
            'tyer_position'   => null,
            'replaced_to_id'  => $newTyreId,
            'remark'          => "Exchange completed. Replaced by {$newSerial}. {$remark}",
        ]);

        $this->db->table('tyer_management_history')->insert([
            'tyre_id' => $newTyreId,
            'event_type' => 11,
            'event_date' => $exchangeDate,
            'remarks'    => "Exchange completed. Received from vendor as replacement for {$oldTyre->tyer_sl_no}. {$remark}",
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->recordTyreExchange(
            $oldTyre->vehicle_id !== null ? (int) $oldTyre->vehicle_id : null,
            $oldTyreId,
            $newTyreId,
            $oldTyre->tyer_position ?? null,
            $exchangeDate,
            $remark !== '' ? "Vendor warranty exchange. {$remark}" : 'Vendor warranty exchange'
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'old_tyre_id' => $oldTyreId,
            'new_tyre_id' => $newTyreId,
        ];
    }

    /**
     * Create staff/driver/mechanic (admin/Add_staf).
     *
     * @param array<string, mixed> $data
     *
     * @return array{staff_id: int, staff_code: string}|null
     */
    public function storeStaffMember(array $data, float $openingBalance = 0.0): ?array
    {
        $this->db->transStart();

        $this->db->table('staff')->insert($data);
        $staffId = (int) $this->db->insertID();

        if ($staffId <= 0) {
            $this->db->transRollback();

            return null;
        }

        $cleanName       = preg_replace('/[^a-zA-Z]/', '', (string) ($data['name'] ?? ''));
        $firstThreeChars = strtoupper(substr($cleanName, 0, 3));
        $aadhaarClean    = preg_replace('/[^0-9]/', '', (string) ($data['aadhaar_no'] ?? ''));
        $aadhaarLast4    = $aadhaarClean !== '' ? substr($aadhaarClean, -4) : '0000';
        $staffCode       = $firstThreeChars . $aadhaarLast4 . '-' . $staffId;

        $this->db->table('staff')->where('id', $staffId)->update(['staff_code' => $staffCode]);

        if ($openingBalance != 0.0) {
            $this->db->table('opening_closing')->insert([
                'staff_id'  => $staffId,
                'oamout'    => $openingBalance,
                'oc_type'   => 1,
                'yearmonth' => date('Y-m-d'),
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return null;
        }

        return [
            'staff_id'   => $staffId,
            'staff_code' => $staffCode,
        ];
    }

    /**
     * @return object|null
     */
    public function getStaffById(int $staffId)
    {
        if ($staffId <= 0) {
            return null;
        }

        return $this->db->table('staff s')
            ->select('s.*, l.location_name')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->where('s.id', $staffId)
            ->get()
            ->getRow();
    }

    /**
     * Driver master list (admin/staf filtered by user_type = DRIVER).
     *
     * @param array<string, mixed> $filters
     *
     * @return list<object>
     */
    public function getDriverList(array $filters = []): array
    {
        $builder = $this->db->table('staff s')
            ->select('s.*, l.location_name')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->where('s.user_type', 'DRIVER');

        $locationId = (int) ($filters['location_id'] ?? 0);
        if ($locationId > 0) {
            $builder->where('s.location_id', $locationId);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('s.name', $search)
                ->orLike('s.staff_code', $search)
                ->orLike('s.tel', $search)
                ->orLike('s.dl_number', $search)
                ->orLike('s.aadhaar_no', $search)
            ->groupEnd();
        }

        $status  = strtolower(trim((string) ($filters['status'] ?? '')));
        $asOnDate = trim((string) ($filters['as_on_date'] ?? date('Y-m-d')));
        if ($status === 'active' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOnDate)) {
            $builder->groupStart()
                ->where('s.doj <=', $asOnDate)
                ->orWhere('s.doj', '0000-00-00')
                ->orWhere('s.doj', null)
            ->groupEnd();

            $builder->groupStart()
                ->where('s.resign_date IS NULL')
                ->orWhere('s.resign_date', '0000-00-00')
                ->orWhere('s.resign_date >=', $asOnDate)
            ->groupEnd();
        } elseif ($status === 'resigned' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOnDate)) {
            $builder->where('s.resign_date IS NOT NULL')
                ->where('s.resign_date !=', '0000-00-00')
                ->where('s.resign_date <', $asOnDate);
        }

        return $builder->orderBy('s.id', 'DESC')->get()->getResult();
    }
}
