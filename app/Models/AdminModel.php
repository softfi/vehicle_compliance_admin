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
	
	function Vehicle()
	{
		$builder = $this->db->table('vehicle');
        $builder->select('*', 'location.location_name');
        $builder->join('location', 'location.location_id = vehicle.location_id', 'left');
		return $builder->get()->getResult();
	}
	function Getallstaf()
	{
	    $builder = $this->db->table('staff');
		$builder->select('staff.*,location.location_name');
		$builder->join('location', 'location.location_id = staff.address','left');
		return $builder->get()->getResult();
	}
public function driverasignment($from_date = null, $to_date = null)
{
    $builder = $this->db->table('driver_assignment');
    $builder->select('driver_assignment.*, vehicle.vehicle_no as vehicle_number, staff.name as driver_name, staff.staff_code as driver_code');
    $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');
    $builder->join('staff', 'staff.id = driver_assignment.driver', 'left');

    if (!empty($from_date) && !empty($to_date)) {
        $builder->where('driver_assignment.from_date <=', $to_date);
        $builder->where('driver_assignment.to_date >=', $from_date);
        $builder->orWhere('driver_assignment.to_date IS NULL');
    }

    return $builder->get()->getResult();
}



	
	function singledriverasignment($asign_id)
	{
	     $builder = $this->db->table('driver_assignment');
		$builder->select('driver_assignment.*, vehicle.vehicle_no as vehicle_number, staff.name as driver_name');
		$builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no','left');
		$builder->join('staff', 'staff.id = driver_assignment.driver','left');
		$builder->where('driver_assignment.id', $asign_id);
		return $builder->get()->getResult();
	}
	
	function staffadvance($from_date,$to_date)
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
		$builder->select('staff_advance.*, location.location_name, staff.name, staff.staff_code');
		$builder->join('location', 'location.location_id = staff_advance.location_id','left');
		$builder->join('staff', 'staff.id = staff_advance.staff_id','left');
        $builder->where('adv_date >=', $from_date);
        $builder->where('adv_date <=', $to_date);
		return $builder->get()->getResult();
	}
	
	function regularcheckup()
	{
	    $builder = $this->db->table('vehicle_maintenance');
		$builder->select('vehicle_maintenance.*, vehicle.vehicle_no');
		$builder->join('vehicle', 'vehicle.id = vehicle_maintenance.vehicle_no','left');
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


	
	function dieseldata($from_date,$to_date)
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
		$builder->join('vendor', 'vendor.id = diselentry.vendor_id','left');
		$builder->join('vehicle', 'vehicle.id = diselentry.vehicle_id','left');
		
		$builder->where('diesel_date >=', $from_date);
        $builder->where('diesel_date <=', $to_date);
        $builder->where('diselentry.deleted_by', Null);

		return $builder->get()->getResult();  
	}
	
	function purcartdetails($user_id)
	{
		$builder = $this->db->table('cart');
		$builder->select('cart.cart_id,cart.invoicedate, cart.sup-cust_id as supplier_id ,items.id as product_id, location, items.item_name, cart.rate as rate, invoiceno, units.unit_name, cart.qty,   cart.user_id, ');
		$builder->where('user_id', $user_id);
		$builder->where('cart_type', 1);
		$builder->join('items', 'items.id = cart.product_id');
		$builder->join('units', 'items.unit_id = units.unit_id');
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
	function out_side($from_date,$to_date)
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
	public function partyNames(){
	    $builder= $this->db->table('vendor');
	    $builder->select('*');
        $builder->where('type', 'Party');
	    return $builder->get()->getResult();
	}


	function bank(){
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
    $builder = $this->db->table('stock');
    $builder->select('stock.stock_id,stock.date,location.location_name, location.location_id, stock.invoice_number,vendor.id as supplier_id, vendor.name as supplier_name, stock.stock_code, SUM(stock.quantity) AS total_quantity, SUM(stock.gst_amount) AS total_gst_amount');
    $builder->join('items', 'items.id = stock.sproduct_id', 'left');
    $builder->join('units', 'units.unit_id = items.unit_id', 'left');
    $builder->join('location', 'location.location_id = stock.location_id', 'left');
    $builder->join('vendor', 'vendor.id = stock.supplier_id', 'left');
    //$builder->where('stock.date >', '1770-06-02');
    $builder->groupBy('stock_code');
    $builder->orderBy('date', 'DESC');
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

	
function Get_vendor(){
    // Main query builder
    $builder = $this->db->table('vendor');
    $builder->select('vendor.*, location.location_name, vendor_rate.vendor_rate, vendor_rate.from_date');
    $builder->join('location', 'location.location_id = vendor.location', 'left');

    // Subquery to get the latest vendor_rate for each vendor
    $subquery = $this->db->table('vendor_rate as vr1')
        ->select('vr1.vendor_id, vr1.vendor_rate, vr1.from_date')
        ->join('(SELECT vendor_id, MAX(from_date) as max_date FROM vendor_rate GROUP BY vendor_id) as vr2', 
               'vr1.vendor_id = vr2.vendor_id AND vr1.from_date = vr2.max_date', 'inner', false);

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
    $builder->select('vehicle.*, location.location_name');
    $builder->join('location', 'location.location_id = vehicle.location_id', 'left');

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

    public function Getallvehicle(){
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
        $builder->join('vehicle', 'vehicle.id=despatch.vehicle_no');
        $builder->join('do_registration', 'do_registration.do_registration_id=despatch.do_no');
        
        // Apply date filter on des_date
        $builder->where('des_date >=', $from_date);
        $builder->where('des_date <=', $to_date);
        $builder->where('despatch.deleted_by', Null);
    
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

public function despatch_count($from_date = null, $to_date = null, $do_no = null, $chalan_status = null, $payment_status = null, $deposited_status = null, $voucher_id = null)
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

    $query = $builder->get();
    return $query->getRow()->total;
}

public function despatch_dtls1_paginated($from_date = null, $to_date = null, $do_no = null, $chalan_status = null, $payment_status = null, $deposited_status = null, $limit = 10, $offset = 0, $voucher_id = null)
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
	function UpdateService($data,$service_id)
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
	return $builder->get()->getResult();
}

public function createVoucherPayment($voucher_ids)
{
    if (empty($voucher_ids)) {
        return ['status' => 'error', 'message' => 'No vouchers selected'];
    }

    $builder = $this->db->table('voucher');
    $builder->select('voucher.id, despatch.do_no, despatch.net_amount');
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
        $total_net_amount += $row->net_amount;
        if (!empty($row->do_no)) {
            $do_numbers[] = $row->do_no;
        }
        $processed_vouchers[] = $row->id;
    }

    $do_numbers = array_unique($do_numbers);
    $processed_vouchers = array_unique($processed_vouchers);

    $po_number = 'PO-' . date('YmdHis') . '-' . rand(100, 999);

    $data = [
        'po_number' => $po_number,
        'do_numbers' => implode(',', $do_numbers),
        'voucher_ids' => implode(',', $processed_vouchers),
        'total_net_amount' => $total_net_amount,
        'received_amount' => 0,
        'difference_amount' => $total_net_amount, // Initially difference is full amount
        'adjustment_amount' => 0,
        'adjustment_remarks' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $this->db->table('voucher_payment')->insert($data);
    return ['status' => 'success', 'message' => 'Payment record created successfully'];
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
             foreach($parts as $p) {
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
            
            foreach($do_numbers as $d_val) {
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
            foreach($v_parts as $v_p) {
                $trimmed_v = trim($v_p);
                if (!empty($trimmed_v)) $v_ids[] = $trimmed_v;
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
	 $builder->select('inhouse_maintenance.*,vehicle.vehicle_no, location.location_name,items.item_name'); 
	 $builder->join('vehicle', 'vehicle.id = inhouse_maintenance.vehicle','left');
	 $builder->join('location', 'location.location_id = inhouse_maintenance.location','left');
	 $builder->join('items', 'items.id = inhouse_maintenance.item','left');
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
		
		
		
	function updateorder($data,$order_id)
	{
	    $query = $this->db->table('orders')->update($data, array('order_id' => $order_id));
		return $query;
	}
	function getsingleaddress ($addre_id)
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
	
	
	 public function sendPushNotification($message,$title,$sender_id)

    {  
       

       $fields = array
    (
    'to'  => "$sender_id",
    'priority' => 'high',
    'notification' => array(
        'body' => $message,
        'title' => $title,
        'sound' => 'default',
        'icon' => 'https://collegeprojectz.com/apniseva/uploads/fav.png',
       	'image'=> ''
    ),
    'data' => array(
        'message' => $message,
        'title' => $title,
        'sound' => 'default',
        'icon' => 'https://collegeprojectz.com/apniseva/uploads/fav.png',
        'image'=> ''
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
    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch );
    curl_close( $ch );
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
                    }else{
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
    $builder->join('city', 'address.cityname = city.city_id','left');
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
            }else{
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

  
function exportorderreport($from_date, $to_date, $status, $city,$customer,$vendor)
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
    $builder->join('city', 'address.cityname = city.city_id','left');
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
            }else{
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
    $builder = $this->db->table('staff');
    $builder->select('staff.*, 
                      staff_salary_details.working_day, 
                      staff_salary_details.insentive, 
                      staff_salary_details.total_salary, 
                      staff_salary_details.net_salary,  
                      location.location_name, 
                      IFNULL(FORMAT(staff_advance.total_advance, 2), "0.00") as total_advance');
    $builder->where('staff.user_type', 'STAFF');
    if (!empty($location)) {
        $builder->where('staff.address', $location);  // Corrected line
    }
    $builder->join('location', 'location.location_id = staff.address', 'left');

    // Subquery to get the sum of staff_advance.amount for each staff member
    $subQueryAdvance = $this->db->table('staff_advance')
                                ->select('staff_id, COALESCE(SUM(amount), 0) AS total_advance')
                                ->where("YEAR(created_at)", $year)
                                ->where("MONTH(created_at)", $month)
                                ->groupBy('staff_id')
                                ->getCompiledSelect();

    // Join the subquery results with the main query
    $builder->join("($subQueryAdvance) AS staff_advance", 'staff_advance.staff_id = staff.id', 'left');
    
    // Subquery to get the staff salary details
    $subQuerySalary = $this->db->table('staff_salary')
                               ->select('user_id, working_day, insentive, total_salary, net_salary')
                               ->where("year", $year)
                               ->where("month", $month)
                               ->groupBy('user_id')
                               ->getCompiledSelect();

    // Join the subquery results with the main query
    $builder->join("($subQuerySalary) AS staff_salary_details", 'staff_salary_details.user_id = staff.id', 'left');

    return $builder->get()->getResult();
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
                $row->day_trip_expense =  $row->trip_expenses2;
            } elseif ($row->total_number_of_trip == 3) {
                $row->day_trip_expense =  $row->trip_expenses3;
            } elseif ($row->total_number_of_trip == 4) {
                $row->day_trip_expense =  $row->trip_expenses4;
            } elseif ($row->total_number_of_trip == 5) {
                $row->day_trip_expense =  $row->trip_expenses5;
            } elseif ($row->total_number_of_trip >= 6) {
                $row->day_trip_expense =  $row->trip_expenses6;
            } else {
                $row->day_trip_expense = 0; // Default case if no matching condition
            }
        }
    
        return $result;
    }

    function tripexpence1($vehicle_id, $driver_id, $year, $month) { 
        // Second Query to fetch despatch data and match with the driver 
        $builder = $this->db->table('despatch'); 
        $builder->select(' do_registration.do_no, despatch.vehicle_no, do_registration.do_registration_id, 
                            driver_assignment.driver, do_registration.trip_expenses1, do_registration.trip_expenses2,
                            do_registration.trip_expenses3, do_registration.trip_expenses4, do_registration.trip_expenses5,
                            do_registration.trip_expenses6, DATE(despatch.des_date) as despatch_date, doprice_change.trip1
                            as doprice_trip1, doprice_change.trip2 as doprice_trip2, doprice_change.trip3 as doprice_trip3,
                            doprice_change.trip4 as doprice_trip4, doprice_change.trip5 as doprice_trip5, doprice_change.trip6 as doprice_trip6,
                            SUM(despatch.quantity) as total_weight, COUNT(despatch.despatch_id) as total_number_of_trip '
                        );
        // Joining necessary tables 
        $builder->join('do_registration', 'do_registration.do_registration_id = despatch.do_no', 'left'); 
        $builder->join('doprice_change', 'do_registration.do_registration_id = doprice_change.dono AND despatch.des_date >= doprice_change.from_date', 'left');
        $builder->join('driver_assignment', 'driver_assignment.vehicle_no = despatch.vehicle_no', 'left');
        // Apply filters 
        $builder->where('despatch.vehicle_no', $vehicle_id); 
        // $builder->where('despatch.vehicle_no', 61);
        $builder->where("YEAR(despatch.des_date)", $year);
        $builder->where("MONTH(despatch.des_date)", $month);
        // Match despatch with correct driver assignment (multiple driver handling)
        $builder->where('driver_assignment.driver', $driver_id);
        // $builder->where('driver_assignment.driver', 1136);
        $builder->where('despatch.des_date >=', 'driver_assignment.from_date', false);
        $builder->where('despatch.des_date <=', 'driver_assignment.to_date', false); 
        // Group by date and driver 
        $builder->groupBy(['DATE(despatch.des_date)', 'driver_assignment.driver']);
        // Fetch result
        $result = $builder->get()->getResult();
        // echo"<pre>"; 
        // print_r($result);exit; 
        //Loop through results to calculate per-day and total trip expense 
        foreach ($result as $row) { 
            switch ($row->total_number_of_trip) { 
                case 1: $row->day_trip_expense = !empty($row->doprice_trip1) ? $this->getTripExpense($row->doprice_trip1, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses1, $row->total_number_of_trip);
                break; 
                case 2: $row->day_trip_expense = !empty($row->doprice_trip2) ? $this->getTripExpense($row->doprice_trip2, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses2, $row->total_number_of_trip);
                break; 
                case 3: $row->day_trip_expense = !empty($row->doprice_trip3) ? $this->getTripExpense($row->doprice_trip3, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses3, $row->total_number_of_trip);
                break;
                case 4: $row->day_trip_expense = !empty($row->doprice_trip4) ? $this->getTripExpense($row->doprice_trip4, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses4, $row->total_number_of_trip);
                break;
                case 5: $row->day_trip_expense = !empty($row->doprice_trip5) ? $this->getTripExpense($row->doprice_trip5, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses5, $row->total_number_of_trip);
                break;
                case 6: $row->day_trip_expense = !empty($row->doprice_trip6) ? $this->getTripExpense($row->doprice_trip6, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses6, $row->total_number_of_trip);
                break;
                default: $row->day_trip_expense = !empty($row->doprice_trip1) ? $this->getTripExpense($row->doprice_trip1, $row->total_number_of_trip) : $this->getTripExpense($row->trip_expenses1, $row->total_number_of_trip);
                break; 
            } } 
        // ✅ Calculate total trip expense for the driver 
        $total_expense = 0; 
        foreach ($result as $row) { 
            $total_expense += (float)$row->day_trip_expense; 
        }
        // echo"<pre>";
        // print_r($total_expense);
        // exit; 
        // Return result array (so getdriver_salary_details() can sum them) 
        return $result; 
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
    $builder->select('vehicle.id, vehicle.vehicle_no, tyer_management.tyer_sl_no, tyer_management.tyer_position');
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
                'tyer_position' => []
            ];
        }
        $vehicles[$vehicle_id]['tyer_position'][$result->tyer_position] = $result->tyer_sl_no;
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

function overalexpence_data($loc_id,$from_date,$to_date){
   
    
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

  function getTyreDetailsBySlNo($tyer_sl_no) {
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


    public function GetAllPaymentVoucherByUserType($from_date = null, $to_date = null,$pump_id = null, $user_type = null, $payment_type = null)
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
    public function getGroupDetails(){
        return $this->db->table('group g')
            ->select('g.*')
            ->get()
            ->getResult();
    }
    public function getyearsDetails(){
        return $this->db->table('financial_year fy')
        ->select('fy.*')
        ->get()
        ->getResult();
    }
    public function getLedgerDetails(){
        return $this->db->table('ledger l')
            ->select('l.*,g.group_name,')
            ->join('group g','g.group_id = l.ledger_id')
            ->get()
            ->getResult();
    }
    public function getAllUser(){
        return $this->db->table('user')
            ->select('user.*')
            ->where('user_type !=', 1)   // exclude admin
            ->get()
            ->getResult();
    }
    public function getAllTasks()
    {
        return $this->db->table('tasks')
            ->select('tasks.*, u1.full_name as assigned_to_name, u2.full_name as assigned_by_name')
            ->join('user u1', 'u1.id = tasks.assigned_to')
            ->join('user u2', 'u2.id = tasks.assigned_by')
            ->orderBy('tasks.id', 'DESC')
            ->get()
            ->getResult();
    }
    public function getTasksByUser($user_id)
    {
        return $this->db->table('tasks')
            ->select('tasks.*, u1.full_name as assigned_to_name, u2.full_name as assigned_by_name')
            ->join('user u1', 'u1.id = tasks.assigned_to')
            ->join('user u2', 'u2.id = tasks.assigned_by')
            ->where('tasks.assigned_to', $user_id)
            ->orderBy('tasks.id', 'DESC')
            ->get()
            ->getResult();
    }
    public function getHistoryRecords($filters = [])
    {
        $builder = $this->db->table('tyer_management_history th');
        
        // Join with other tables for additional info
        $builder->select('
            th.*,
            t.tyer_sl_no,
            t.brand_name,
            v.vehicle_no,
            vn.name AS vendor_name,
            l.location_name,
            l1.location_name as from_location,
            l2.location_name as to_location
        ');
        
        $builder->join('tyer_management t', 't.id = th.tyre_id', 'left');
        $builder->join('vehicle v', 'v.id = th.vehicle_id', 'left');
        $builder->join('vendor vn', 'vn.id = th.vendor_id', 'left');
        $builder->join('location l', 'l.location_id = th.location_id', 'left');
        $builder->join('location l1', 'l1.location_id = th.transfer_from', 'left');
        $builder->join('location l2', 'l2.location_id = th.transfer_to', 'left');
        
        // Filter by tyre_id if provided
        if (!empty($filters['tyre_id'])) {
            $builder->where('th.tyre_id', $filters['tyre_id']);
        }
        
        // Filter by event_type
        if (!empty($filters['event_type'])) {
            $builder->where('th.event_type', $filters['event_type']);
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('t.tyer_sl_no', $filters['search'])
                ->orLike('v.vehicle_no', $filters['search'])
                ->orLike('t.brand_name', $filters['search'])
                ->orLike('th.remarks', $filters['search'])
                ->groupEnd();
        }
        
        $builder->orderBy('th.created_at', 'DESC');
        
        return $builder->get()->getResult();
    }

    // Add this method to get single tyre details
    public function getTyreById($tyre_id)
    {
        return $this->db->table('tyer_management t')
            ->select('t.*, l.location_name')
            ->join('location l', 'l.location_id = t.location_id', 'left')
            ->where('t.id', $tyre_id)
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

}


