<?php
namespace App\Controllers;
use App\Models\AdminModel;

class DebugData extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('voucher');
        $builder->select('voucher.id, voucher.group_code, vendor.name as party_name, despatch.do_no');
        $builder->join('despatch', 'despatch.voucher_id = voucher.id', 'left');
        $builder->join('do_registration', 'despatch.do_no = do_registration.do_registration_id', 'left');
        $builder->join('vendor', 'do_registration.party = vendor.id', 'left');
        $builder->limit(10);
        $results = $builder->get()->getResult();
        
        echo "<pre>";
        print_r($results);
        echo "</pre>";
        
        // Also check if do_registration has party names
        $do = $db->table('do_registration')->limit(5)->get()->getResult();
        print_r($do);
    }
}
