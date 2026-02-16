<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use DateTimeImmutable;
use DateTime;

class Admin extends BaseController
{
    public $db, $AdminModel, $session, $uri;
    public function __construct()
    {
        $db = db_connect();
        $this->db = db_connect();
        date_default_timezone_set('Asia/Kolkata');
        $this->AdminModel = new AdminModel($db);
        $this->session = session();
        helper(['form', 'url', 'validation']);
    }

    public function index()
    {
        return view('admin/login');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/admin');
    }


    function loginAuth()
    {

        $session = session();
        $AdminModel = new AdminModel();
        $username = $this->request->getVar('username');
        $password = base64_encode(base64_encode($this->request->getVar('password')));

        $data = $AdminModel->where('user_name', $username)->first();

        //echo "<pre>";
        //Print_r ($data);exit;
        if ($data) {
            $pass = $data['password'];
            $status = $data['status'];
            //$authenticatePassword = password_verify($password, $pass);

            if ($pass == $password and $status = 1) {
                $ses_data = [
                    'user_id' => $data['id'],
                    'fullname' => $data['full_name'],
                    'email' => $data['email'],
                    'user_type' => $data['user_type'],
                    'isLoggedIn' => TRUE
                ];
                $session->set($ses_data);
                return redirect()->to('admin/Dashboard');
            } else {
                $session->setFlashdata('msg', 'Password is incorrect.');
                return redirect()->to('admin/');
            }
        } else {
            $session->setFlashdata('msg', 'username does not exist.');
            return redirect()->to('admin/');
        }
    }
    function profile()
    {

        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);


            return view('admin/profile_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }
    function pro()
    {

        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);


            $rules = [
                'fullname' => 'required|min_length[3]',
                'email' => 'required|valid_email',
                'contact' => 'required|numeric|max_length[10]',
                'username' => 'required|min_length[5]',
                'password' => 'required|min_length[6]',

            ];

            if ($this->validate($rules)) {
                $fullname = $this->request->getPost('fullname');
                $email = $this->request->getPost('email');
                $contact = $this->request->getPost('contact');
                $username = $this->request->getPost('username');
                $password = base64_encode(base64_encode($this->request->getVar('password')));

                $file = $this->request->getFile('img');
                if ($file->isValid() && !$file->hasMoved()) {
                    $imagename = $file->getRandomName();
                    $file->move('uploads/', $imagename);
                } else {
                    $imagename = "";
                }

                if ($imagename != '') {
                    $data = [
                        'full_name' => $fullname,
                        'email' => $email,
                        'contact_no' => $contact,
                        'user_name' => $username,
                        'password' => $password,
                        'profile_image' => $imagename,
                    ];
                } else {
                    $data = [
                        'full_name' => $fullname,
                        'email' => $email,
                        'contact_no' => $contact,
                        'user_name' => $username,
                        'password' => $password,
                    ];
                }

                $this->AdminModel->UpdateProfile($data, $user_id);

                return redirect()->to('admin/profile');
            } else {
                $data['validation'] = $this->validator;
                return view('admin/profile_vw', $data);
            }
        } else {
            return redirect()->to('admin/');
        }
    }
    function setting()
    {

        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);



            return view('admin/Setting_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }
    function Dashboard()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        // echo "<pre>";
        // print_r($data['singleuser']);exit;
        $user_type = null;
        if (!empty($data['singleuser'][0])) {
            $user_type = $data['singleuser'][0]->user_type;
        }
        
        if ($user_type == 1) {
            
            $data['tasks'] = $this->AdminModel->getAllTasks();
        } else {
            $data['tasks'] = $this->AdminModel->getTasksByUser($user_id);
        }
        // echo "<pre>";
        // print_r($data['vehicle']);exit;
        return view('admin/dashboard_vw', $data);
    }
    function vehicle()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');

        $from_date = $this->request->getPost('from_date');
        $to_date = $this->request->getPost('to_date');
        $type = $this->request->getPost('type');

        $data['vehicle'] = $this->AdminModel->Getvehicle_details($from_date, $to_date, $type);
        // echo "<pre>";
        // print_r($data['vehicle']);exit;
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['locations'] = $this->db->query("SELECT * FROM location")->getResult();
        return view('admin/vehicle', $data);
    }
    function Insertvehicle()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicle'] = $this->AdminModel->Vehicle();
            $data['locations'] = $this->db->query("SELECT * FROM location")->getResult();
            $vehicleType = $this->request->getPost('vehicle_type');
            if($vehicleType == '1'){
                
                $rules = [
                    'vehicle_no' => 'required|is_unique[vehicle.vehicle_no]',
                    'chassis_no' => 'required',
                    'engine_no' => 'required',
                    'fitness_exp_date' => 'required',
                    'fitness_amount' => 'required',
                    'tax_exp_date' => 'required',
                    'road_tax_amount' => 'required',
                    'ins_company' => 'required',
                    'ins_exp_date' => 'required',
                    'Insurance_Amount' => 'required',
                    'permit_exp_date' => 'required',
                    'Permit_Amount' => 'required',
                    'npermit_exp_date' => 'required',
                    'nPermit_Amount' => 'required',
                    'finance' => 'required',
                    'deduct_Amount' => 'required',
                    'emi_account' => 'required',
                    'horsemake' => 'required',
                    'HorseModel' => 'required',
                    'HorseRate' => 'required',
                    'DalaRate' => 'required',
                    'DalaMake' => 'required',
                    'RTOExpenses' => 'required',
                    'amc' => 'required',
                    'amcamount' => 'required',
                    'amc_expary' => 'required',
                    'amc_frequency' => 'required',
                    'Pucc' => 'required',
                    'Pucc_amount' => 'required',
                    'I3MSexpairy' => 'required',
                    'I3MSRECHARGE' => 'required',
                    'KHANIJEXPIRI' => 'required',
                    'location_name' => 'required'
                ];
            } else {
                $rules = [
                    'vehicle_no' => 'required|is_unique[vehicle.vehicle_no]',
                    'chassis_no' => 'required',
                    'engine_no' => 'required',
                    'fitness_exp_date' => 'required',
                    'fitness_amount' => 'required',
                    'tax_exp_date' => 'required',
                    'road_tax_amount' => 'required',
                    'ins_company' => 'required',
                    'ins_exp_date' => 'required',
                    'Insurance_Amount' => 'required',
                    'finance' => 'required',
                    'deduct_Amount' => 'required',
                    'emi_account' => 'required',
                    'location_name' => 'required'
                ];
            }

            if ($this->validate($rules)) {
                $vehicle_no = $this->request->getPost('vehicle_no');
                $chassis_no = $this->request->getPost('chassis_no');
                $engine_no = $this->request->getPost('engine_no');
                $fitness_exp_date = $this->request->getPost('fitness_exp_date');
                $fitness_amount = $this->request->getPost('fitness_amount');
                $tax_exp_date = $this->request->getPost('tax_exp_date');
                $road_tax_amount = $this->request->getPost('road_tax_amount');
                $ins_company = $this->request->getPost('ins_company');
                $ins_exp_date = $this->request->getPost('ins_exp_date');
                $Insurance_Amount = $this->request->getPost('Insurance_Amount');
                $permit_exp_date = $this->request->getPost('permit_exp_date');
                $nPermit_Amount = $this->request->getPost('nPermit_Amount');
                $npermit_exp_date = $this->request->getPost('npermit_exp_date');
                $Permit_Amount = $this->request->getPost('Permit_Amount');
                $finance = $this->request->getPost('finance');
                $deduct_Amount = $this->request->getPost('deduct_Amount');
                $emi_account = $this->request->getPost('emi_account');
                $horsemake = $this->request->getPost('horsemake');
                $HorseModel = $this->request->getPost('HorseModel');
                $HorseRate = $this->request->getPost('HorseRate');
                $DalaRate = $this->request->getPost('DalaRate');
                $DalaMake = $this->request->getPost('DalaMake');
                $RTOExpenses = $this->request->getPost('RTOExpenses');
                $amc = $this->request->getPost('amc');
                $amc_frequancy = $this->request->getPost('amc_fre');
                $amcamount = $this->request->getPost('amcamount');
                $amc_expary = $this->request->getPost('amc_expary');
                $amc_frequency = $this->request->getPost('amc_frequency');
                $Pucc = $this->request->getPost('Pucc');
                $Pucc_amount = $this->request->getPost('Pucc_amount');
                $I3MSexpairy = $this->request->getPost('I3MSexpairy');
                $I3MSRECHARGE = $this->request->getPost('I3MSRECHARGE');
                $KHANIJEXPIRI = $this->request->getPost('KHANIJEXPIRI');
                $khanij_amount = $this->request->getPost('khanij_amount');
                $remark = $this->request->getPost('remark');
                $location_id = $this->request->getPost('location_name');

                $img = $this->request->getFile('document');
                if ($img->isValid() && !$img->hasMoved()) {
                    $imgName = $img->getRandomName();
                    $img->move('uploads/', $imgName);
                    $imgPath = 'uploads/' . $imgName;
                } else {
                    $imgPath = "";
                }

                $data = [
                    'vehicle_no' => $vehicle_no,
                    'chassis_no' => $chassis_no,
                    'engine_no' => $engine_no,
                    'fitness_exp_date' => $fitness_exp_date,
                    'fitness_amount' => $fitness_amount,
                    'tax_exp_date' => $tax_exp_date,
                    'road_tax_amount' => $road_tax_amount,
                    'ins_company' => $ins_company,
                    'ins_exp_date' => $ins_exp_date,
                    'insurance_amount' => $Insurance_Amount,
                    'permit_exp_date' => $permit_exp_date,
                    'permit_amount' => $Permit_Amount,
                    'npermit_exp_date' => $npermit_exp_date,
                    'npermit_amount' => $nPermit_Amount,
                    'finance' => $finance,
                    'deduct_amount' => $deduct_Amount,
                    'emi_account' => $emi_account,
                    'horse_make' => $horsemake,
                    'horse_model' => $HorseModel,
                    'horse_rate' => $HorseRate,
                    'dala_rate' => $DalaRate,
                    'dala_make' => $DalaMake,
                    'rto_expenses' => $RTOExpenses,
                    'amc' => $amc,
                    'amc_frequancy' => $amc_frequancy,
                    'amc_amount' => $amcamount,
                    'amc_expary' => $amc_expary,
                    'amc_frequancy' => $amc_frequency,
                    'pucc' => $Pucc,
                    'pucc_amount' => $Pucc_amount,
                    'i3ms_expary' => $I3MSexpairy,
                    'i3ms_recharge' => $I3MSRECHARGE,
                    'khanij_expiri' => $KHANIJEXPIRI,
                    'khanij_amount' => $khanij_amount,
                    'remark' => $remark,
                    'location_id' => $location_id,
                    'document' => $imgPath,
                ];
                // echo "<pre>";
                // print_r($data);exit;
                $this->db->table('vehicle')->insert($data);
                return redirect()->to('Admin/Vehicle');
            } else {
                $data['validation'] = $this->validator;
                return view('admin/vehicle', $data);
            }
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function upload()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                $vehicle_no = $row[0];
                $CountVehicle = $this->db->query("SELECT * FROM vehicle  where vehicle_no='$vehicle_no'")->getResult();

                $fitness_expairy_date = isset($row[3]) ? $row[3] : null;
                $tax_exp_date = isset($row[5]) ? $row[5] : null;
                $ins_date = isset($row[8]) ? $row[8] : null;
                $permit_ex = isset($row[10]) ? $row[10] : null;
                $npermit_ex = isset($row[12]) ? $row[12] : null;
                $amc_ex = isset($row[26]) ? $row[26] : null;
                $pucc_ex = isset($row[27]) ? $row[27] : '';
                $im3_ex = isset($row[29]) ? $row[29] : null;
                $khanija_ex = isset($row[31]) ? $row[31] : null;

                $vehicleData = [
                    'vehicle_no' => isset($row[0]) ? $row[0] : '',
                    'chassis_no' => isset($row[1]) ? $row[1] : '',
                    'engine_no' => isset($row[2]) ? $row[2] : '',
                    'fitness_exp_date' => date('Y-m-d', strtotime($fitness_expairy_date)),
                    'fitness_amount' => isset($row[4]) ? $row[4] : 0.00,
                    'tax_exp_date' => date('Y-m-d', strtotime($tax_exp_date)),
                    'road_tax_amount' => isset($row[6]) ? $row[6] : 0.00,
                    'ins_company' => isset($row[7]) ? $row[7] : '',
                    'ins_exp_date' => date('Y-m-d', strtotime($ins_date)),
                    'insurance_amount' => isset($row[9]) ? $row[9] : 0.00,
                    'permit_exp_date' => date('Y-m-d', strtotime($permit_ex)),
                    'permit_amount' => isset($row[11]) ? $row[11] : 0.00,
                    'npermit_exp_date' => date('Y-m-d', strtotime($npermit_ex)),
                    'npermit_amount' => isset($row[13]) ? $row[13] : 0.00,
                    'finance' => isset($row[14]) ? $row[14] : '',
                    'deduct_amount' => isset($row[15]) ? $row[15] : 0.00,
                    'emi_account' => isset($row[16]) ? $row[16] : '',
                    'horse_make' => isset($row[17]) ? $row[17] : '',
                    'horse_model' => isset($row[18]) ? $row[18] : '',
                    'horse_rate' => isset($row[19]) ? $row[19] : 0.00,
                    'dala_rate' => isset($row[20]) ? $row[20] : 0.00,
                    'dala_make' => isset($row[21]) ? $row[21] : '',
                    'rto_expenses' => isset($row[22]) ? $row[22] : 0.00,
                    'amc' => isset($row[23]) ? $row[23] : '',
                    'amc_amount' => isset($row[24]) ? $row[24] : 0.00,
                    'amc_frequancy' => isset($row[25]) ? $row[25] : '',
                    'amc_expary' => date('Y-m-d', strtotime($amc_ex)),
                    'pucc' => date('Y-m-d', strtotime($pucc_ex)),
                    'pucc_amount' => isset($row[28]) ? $row[28] : '',
                    'i3ms_expary' => date('Y-m-d', strtotime($im3_ex)),
                    'i3ms_recharge' => isset($row[30]) ? $row[30] : '',
                    'khanij_expiri' => date('Y-m-d', strtotime($khanija_ex)),
                    'khanij_amount' => isset($row[32]) ? $row[32] : null,
                    'remark' => isset($row[33]) ? $row[33] : '',

                ];
                if (count($CountVehicle) == 0) {
                    $this->db->table('vehicle')->insert($vehicleData);
                } else {
                    foreach ($CountVehicle as $vehicle) {
                    }
                    $vid = $vehicle->id;
                    $this->db->table('vehicle')->update($vehicleData, ['id' => $vid]);
                }
            }

            $session = session();
            $session->setFlashdata('msg', 'Data Inserted Successfully');
            return redirect()->to('Admin/Vehicle');
        } else {
            $session = session();
            $session->setFlashdata('msg', 'Error in file upload');
            return redirect()->to('Admin/Vehicle');
        }
    }
    public function Editvehicle()
    {
        $vid = $this->request->getPost('vid');
        $vehicle_no = $this->request->getPost('vehicle_no');
        $chassis_no = $this->request->getPost('chassis_no');
        $engine_no = $this->request->getPost('engine_no');
        $fitness_exp_date = $this->request->getPost('fitness_exp_date');
        $fitness_amount = $this->request->getPost('fitness_amount');
        $tax_exp_date = $this->request->getPost('tax_exp_date');
        $road_tax_amount = $this->request->getPost('road_tax_amount');
        $ins_company = $this->request->getPost('ins_company');
        $ins_exp_date = $this->request->getPost('ins_exp_date');
        $insurance_amount = $this->request->getPost('insurance_amount');
        $permit_exp_date = $this->request->getPost('permit_exp_date');
        $permit_amount = $this->request->getPost('permit_amount');
        $npermit_exp_date = $this->request->getPost('npermit_exp_date');
        $npermit_amount = $this->request->getPost('npermit_amount');

        $finance = $this->request->getPost('finance');
        $deduct_amount = $this->request->getPost('deduct_amount');
        $emi_account = $this->request->getPost('emi_account');
        $horse_make = $this->request->getPost('horse_make');
        $horse_model = $this->request->getPost('horse_model');
        $horse_rate = $this->request->getPost('horse_rate');
        $dala_rate = $this->request->getPost('dala_rate');
        $dala_make = $this->request->getPost('dala_make');
        $rto_expenses = $this->request->getPost('rto_expenses');
        $amc = $this->request->getPost('amc');
        $amc_frequancy = $this->request->getPost('amc_frequancy');
        $amc_amount = $this->request->getPost('amc_amount');
        $amc_expiry = $this->request->getPost('amc_expiry');
        $amc_frequency = $this->request->getPost('amc_frequency');


        $pucc = $this->request->getPost('pucc');
        $pucc_amount = $this->request->getPost('pucc_amount');
        $i3ms_expiry = $this->request->getPost('i3ms_expiry');
        $i3ms_recharge = $this->request->getPost('i3ms_recharge');
        $khanij_expiry = $this->request->getPost('khanij_expiry');
        $khanij_amount = $this->request->getPost('khanij_amount');
        $location_name = $this->request->getPost('location_name');
        $remark = $this->request->getPost('remark');
        $document = $this->request->getFile('document');



        $Countvehicle = $this->db->query("SELECT * FROM vehicle  where vehicle_no='$vehicle_no' and id!='$vid' ")->getResult();
        if (count($Countvehicle) != 0) {
            $this->session->setFlashdata('msg', 'Vehicle Number  Already  exist.');
            $this->session->setFlashdata('vid', $id);
        }
        $data = [
            'vehicle_no' => $vehicle_no,
            'chassis_no' => $chassis_no,
            'engine_no' => $engine_no,
            'fitness_exp_date' => $fitness_exp_date,
            'fitness_amount' => $fitness_amount,
            'tax_exp_date' => $tax_exp_date,
            'road_tax_amount' => $road_tax_amount,
            'ins_company' => $ins_company,
            'ins_exp_date' => $ins_exp_date,
            'insurance_amount' => $insurance_amount,
            'permit_exp_date' => $permit_exp_date,
            'permit_amount' => $permit_amount,
            'npermit_exp_date' => $npermit_exp_date,
            'npermit_amount' => $npermit_amount,
            'finance' => $finance,
            'deduct_amount' => $deduct_amount,
            'emi_account' => $emi_account,
            'horse_make' => $horse_make,
            'horse_model' => $horse_model,
            'horse_rate' => $horse_rate,
            'dala_rate' => $dala_rate,
            'dala_make' => $dala_make,
            'rto_expenses' => $rto_expenses,
            'amc' => $amc,
            'amc_frequancy' => $amc_frequency,

            'amc_amount' => $amc_amount,
            'amc_expary' => $amc_expiry,
            'pucc' => $pucc,
            'pucc_amount' => $pucc_amount,
            'i3ms_expary' => $i3ms_expiry,
            'i3ms_recharge' => $i3ms_recharge,
            'khanij_expiri' => $khanij_expiry,
            'khanij_amount' => $khanij_amount,
            'location_id' => $location_name,

            'remark' => $remark
        ];

        // Handling file upload if a file is provided
        if ($document && $document->isValid() && !$document->hasMoved()) {
            $documentName = $document->getRandomName();
            $document->move('uploads/documents/', $documentName);
            $data['document'] = $documentName;
        }

        $this->db->table('vehicle')->where('id', $vid)->update($data);
        return redirect()->to('Admin/Vehicle');
    }
    public function deleteVehicle()
    {

        $segment = $this->request->getUri()->getSegment(3);
        $this->db->table('vehicle')->delete(array('id' => $segment));
        return redirect()->to('Admin/Vehicle');
    }
    function edit_vehicle()
    {

        $vehicle_id = $this->request->getPost('vehicle_id');
        $vechile = $this->db->query("SELECT * FROM vehicle  where id='$vehicle_id' ")->getResult();
        $location = $this->db->query("SELECT * FROM location")->getResult();
        foreach ($vechile as $vec) {
        }
?>





        <form action="<?php echo base_url(); ?>/Admin/Editvehicle" enctype="multipart/form-data" method="post">
            <div class="modal-body">
                <div class="uk-child-width-1-2@m uk-grid-small" uk-grid>
                    <div class="form-group">
                        <label for="vehicle_no">Truck Number</label>
                        <input class="form-control" type="hidden" name="vid" id="vid" value="<?= $vec->id ?>">
                        <input class="form-control" type="text" name="vehicle_no" id="vehicle_no" value="<?= $vec->vehicle_no ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('vehicle_no'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="chassis_no">Chassis Number</label>
                        <input class="form-control" type="text" name="chassis_no" id="chassis_no" value="<?= $vec->chassis_no ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('chassis_no'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="engine_no">Engine Number</label>
                        <input class="form-control" type="text" name="engine_no" id="engine_no" value="<?= $vec->engine_no ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('engine_no'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="fitness_exp_date">Fitness Expiry Date</label>
                        <input class="form-control" type="date" name="fitness_exp_date" id="fitness_exp_date" value="<?= $vec->fitness_exp_date ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('fitness_exp_date'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="fitness_amount">Fitness Amount</label>
                        <input class="form-control" type="text" name="fitness_amount" id="fitness_amount" value="<?= $vec->fitness_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('fitness_amount'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="tax_exp_date">Road Tax Expiry Date</label>
                        <input class="form-control" type="date" name="tax_exp_date" id="tax_exp_date" value="<?= $vec->tax_exp_date ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('tax_exp_date'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="road_tax_amount">Road Tax Amount</label>
                        <input class="form-control" type="text" name="road_tax_amount" id="road_tax_amount" value="<?= $vec->road_tax_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('road_tax_amount'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="ins_company">Insurance Company</label>
                        <input class="form-control" type="text" name="ins_company" id="ins_company" value="<?= $vec->ins_company ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('ins_company'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="ins_exp_date">Insurance Expiry Date</label>
                        <input class="form-control" type="date" name="ins_exp_date" id="ins_exp_date" value="<?= $vec->ins_exp_date ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('ins_exp_date'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="insurance_amount">Insurance Amount</label>
                        <input class="form-control" type="text" name="insurance_amount" id="insurance_amount" value="<?= $vec->insurance_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('insurance_amount'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="permit_exp_date"> Permit Expiry Date</label>
                        <input class="form-control" type="date" name="permit_exp_date" id="permit_exp_date" value="<?= $vec->permit_exp_date ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('permit_exp_date'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="permit_amount"> Permit Amount</label>
                        <input class="form-control" type="text" name="permit_amount" id="permit_amount" value="<?= $vec->permit_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('permit_amount'); ?></span><?php } ?>
                    </div>

                    <div class="form-group">
                        <label for="permit_exp_date">National Permit Expiry Date</label>
                        <input class="form-control" type="date" name="npermit_exp_date" id="npermit_exp_date" value="<?= $vec->npermit_exp_date ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('npermit_exp_date'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="permit_amount">National Permit Amount</label>
                        <input class="form-control" type="text" name="npermit_amount" id="npermit_amount" value="<?= $vec->npermit_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('npermit_amount'); ?></span><?php } ?>
                    </div>


                    <div class="form-group">
                        <label for="finance">Finance Company/ Funding Bank</label>
                        <input class="form-control" type="text" name="finance" id="finance" value="<?= $vec->finance ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('finance'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="deduct_amount">Deduct Amount</label>
                        <input class="form-control" type="text" name="deduct_amount" id="deduct_amount" value="<?= $vec->deduct_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('deduct_amount'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="emi_account">Account from EMI Deducted</label>
                        <input class="form-control" type="text" name="emi_account" id="emi_account" value="<?= $vec->emi_account ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('emi_account'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="horse_make">Horse Make</label>
                        <input class="form-control" type="text" name="horse_make" id="horse_make" value="<?= $vec->horse_make ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('horse_make'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="horse_model">Horse Model</label>
                        <input class="form-control" type="text" name="horse_model" id="horse_model" value="<?= $vec->horse_model ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('horse_model'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="horse_rate">Horse Rate</label>
                        <input class="form-control" type="text" name="horse_rate" id="horse_rate" value=" <?= $vec->horse_rate ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('horse_rate'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="dala_rate">Dala Rate</label>
                        <input class="form-control" type="text" name="dala_rate" id="dala_rate" value="<?= $vec->dala_rate ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('dala_rate'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="dala_make">Dala Make</label>
                        <input class="form-control" type="text" name="dala_make" id="dala_make" value="<?= $vec->dala_make ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('dala_make'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="rto_expenses">RTO Expenses</label>
                        <input class="form-control" type="text" name="rto_expenses" id="rto_expenses" value="<?= $vec->rto_expenses ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('rto_expenses'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="amc">AMC</label>
                        <input class="form-control" type="text" name="amc" id="amc" value="<?= $vec->amc ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amc'); ?></span><?php } ?>
                    </div>

                    <div class="form-group">
                        <label for="amc">AMC</label>
                        <input class="form-control" type="text" name="amc_frequancy" id="amc" value="<?= $vec->amc_frequancy ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amc_frequancy'); ?></span><?php } ?>
                    </div>


                    <div class="form-group">
                        <label for="amc_amount">AMC Monthly Amount</label>
                        <input class="form-control" type="text" name="amc_amount" id="amc_amount" value="<?= $vec->amc_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amc_amount'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="amc_expiry">AMC Expiry Date</label>
                        <input class="form-control" type="text" name="amc_expiry" id="amc_expiry" value="<?= $vec->amc_expary ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amc_expiry'); ?></span><?php } ?>
                    </div>

                    <div class="form-group">
                        <label for="example-nf-email">Amc Frequency</label>
                        <input class="form-control" type="date" name="amc_frequency" id="Permit" value="<?= $vec->amc_frequancy ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amc_frequency'); ?></span><?php } ?>
                    </div>

                    <div class="form-group">
                        <label for="pucc">PUCC</label>
                        <input class="form-control" type="date" name="pucc" id="pucc" value="<?= $vec->pucc ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('pucc'); ?></span><?php } ?>
                    </div>

                    <div class="form-group">
                        <label for="pucc">PUCC Amount</label>
                        <input class="form-control" type="text" name="pucc_amount" id="pucc_amount" value="<?= $vec->pucc_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('pucc_amount'); ?></span><?php } ?>
                    </div>


                    <div class="form-group">
                        <label for="i3ms_expiry">I3MS Expiry</label>
                        <input class="form-control" type="date" name="i3ms_expiry" id="i3ms_expiry" value="<?= $vec->i3ms_expary ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('i3ms_expiry'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="i3ms_recharge">I3MS Recharge</label>
                        <input class="form-control" type="text" name="i3ms_recharge" id="i3ms_recharge" value="<?= $vec->i3ms_recharge ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('i3ms_recharge'); ?></span><?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="khanij_expiry">Khanij Expiry</label>
                        <input class="form-control" type="date" name="khanij_expiry" id="khanij_expiry" value="<?= $vec->khanij_expiri ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('khanij_expiry'); ?></span><?php } ?>
                    </div>

                    <div class="form-group">
                        <label for="khanij_expiry">Khanij Amount</label>
                        <input class="form-control" type="text" name="khanij_amount" id="khanij_amount" value="<?= $vec->khanij_amount ?>">
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('khanij_amount'); ?></span><?php } ?>
                    </div>


                    <div class="form-group">
                        <label for="remark">Remark</label>
                        <input class="form-control" type="text" name="remark" id="remark" value="<?= $vec->remark ?>">
                    </div>

                    <div class="uk-margin-bottom">
                        <lable>Location Name</lable>
                        <select class="form-control" name="location_name" required>
                            <option value=''>Select Location</option>
                            <?php foreach ($location as $loc) { ?>
                                <option <?php if ($vec->location_id == $loc->location_id) {
                                            echo "selected";
                                        } ?> value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                            <?php } ?>
                        </select>

                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location_name'); ?></span><?php } ?>
                    </div>



                    <div class="form-group">
                        <label for="document">Document</label>
                        <input type="file" name="document" class="form-control">
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>


        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

        <!-- JavaScript to show/hide fields -->
        <script>
            $(document).ready(function() {
                $('input[name="fitness_permit"]').on('change', function() {
                    if ($(this).val() === 'yes') {
                        $('.fitness_expiry_date_field').show();
                    } else {
                        $('.fitness_expiry_date_field').hide();
                    }
                });

                $('input[name="tax_permit"]').on('change', function() {
                    if ($(this).val() === 'yes') {
                        $('.tax_expiry_date_field').show();
                    } else {
                        $('.tax_expiry_date_field').hide();
                    }
                });

                $('input[name="need_permit"]').on('change', function() {
                    if ($(this).val() === 'yes') {
                        $('.permit_expiry_date_field').show();
                    } else {
                        $('.permit_expiry_date_field').hide();
                    }
                });

                // Initialize visibility based on checked radio buttons
                if ($('input[name="fitness_permit"]:checked').val() === 'yes') {
                    $('.fitness_expiry_date_field').show();
                }
                if ($('input[name="tax_permit"]:checked').val() === 'yes') {
                    $('.tax_expiry_date_field').show();
                }
                if ($('input[name="need_permit"]:checked').val() === 'yes') {
                    $('.permit_expiry_date_field').show();
                }
            });
        </script>


        <?php
    }
    
    
    public function staf()
    {
        if ($this->session->get('user_id')) {
    
            $user_id = $this->session->get('user_id');
    
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }
            $builder = $this->db->table('staff');
            $builder->select('staff.*, location.location_name');
            $builder->join('location', 'location.location_id = staff.address', 'left');
            $data['allstaf'] = $builder->get()->getResult();
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
    
            return view('admin/staf_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }
    
    public function Add_staf()
    {
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['allstaf'] = $this->AdminModel->Getallstaf(3);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

        // Define validation rules
        $rules = [
            'user_type' => 'required',
            'name' => 'required|max_length[100]',
            'salary' => 'required|decimal',
            // 'driving_l' => 'uploaded[driving_l]|max_size[driving_l,2048]|is_image[driving_l]',
            // 'dl_number' => 'required|max_length[20]',
            // 'dl_expiry' => 'required|valid_date[Y-m-d]',
            // 'aadhaar_no' => 'required|max_length[20]',
            // 'aadhaar' => 'uploaded[aadhaar]|max_size[aadhaar,2048]|is_image[aadhaar]',
            'tel' => 'required|numeric|min_length[10]|max_length[15]',
            'address' => 'required|max_length[255]',
        ];

        // Validate the input data
        if ($this->validate($rules)) {

            // Retrieve input data
            $user_type = $this->request->getPost('user_type');
            $name = $this->request->getPost('name');
            $doj = $this->request->getPost('doj');
            $salary = $this->request->getPost('salary');
            $name_bank = $this->request->getPost('name_bank');
            $ac_no = $this->request->getPost('ac_no');
            $ifsc = $this->request->getPost('ifsc');
            $dl_number = $this->request->getPost('dl_number');
            $dl_expiry = $this->request->getPost('dl_expiry');
            $aadhaar_no = $this->request->getPost('aadhaar_no');
            $tel = $this->request->getPost('tel');
            $fathers_name = $this->request->getPost('fathers_name');
            $spouse_name = $this->request->getPost('spouse_name');
            $dob = $this->request->getPost('dob');
            $family_contact = $this->request->getPost('family_contact');
            $blood_group = $this->request->getPost('blood_group');
            $opening_balance = $this->request->getPost('opening_balance');
            $address = $this->request->getPost('address');

            // Handle file uploads
            $imgPath = $this->uploadFile('img');
            $drivingLPathFront = $this->uploadFile('dl_front');
            $drivingLPathBack = $this->uploadFile('dl_back');
            $aadhaarPathFront = $this->uploadFile('aadhaar_front');
            $aadhaarPathBack = $this->uploadFile('aadhaar_back');

            // Prepare data for insertion
            $data = [
                'user_type' => $user_type,
                'name' => $name,
                'doj' => $doj,
                'salary' => $salary,
                'img' => $imgPath,
                'name_bank' => $name_bank,
                'ac_no' => $ac_no,
                'ifsc' => $ifsc,
                'dl_front' => $drivingLPathFront,
                'dl_back' => $drivingLPathBack,
                'dl_number' => $dl_number,
                'dl_expiry' => $dl_expiry,
                'aadhaar_no' => $aadhaar_no,
                'aadhaar_front' => $aadhaarPathFront,
                'aadhaar_back' => $aadhaarPathBack,
                'tel' => $tel,
                'fathers_name' => $fathers_name,
                'spouse_name' => $spouse_name,
                'dob' => $dob,
                'family_contact' => $family_contact,
                'blood_group' => $blood_group,
                'opening_balance' => $opening_balance,
                'address' => $address,
            ];

            $yearMonth = date('Y-m-d');
            // Insert data into the database
            $this->db->table('staff')->insert($data);
            $lastInsertID = $this->db->insertID();

            $clean_name = preg_replace('/[^a-zA-Z0-9]/', '', $name);
            $firstThreeChars = substr($clean_name, 0, 3);
            $staff_code = $lastInsertID . '-' . $firstThreeChars;



            $data2 = [
                'staff_code' => $staff_code,
            ];
            $this->db->table('staff')->where('id', $lastInsertID)->update($data2);


            $yearMonth = date('Y-m-d');
            $data1 = [
                'staff_id' => $lastInsertID,
                'oamout' => $opening_balance,
                'oc_type' => 1,
                'yearmonth' => $yearMonth

            ];
            $this->db->table('opening_closing')->insert($data1);



            return redirect()->to('admin/staf')->with('msg', 'Staff member added successfully');
        } else {
            $data['validation'] = $this->validator;
            echo view('admin/staf_vw', $data);
        }
    }

    // Helper function for file uploads
    private function uploadFile($fieldName)
    {
        $file = $this->request->getFile($fieldName);
        if ($file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/', $fileName);
            return $fileName;
        }
        return "";
    }

    public function PrintStaff()
    {
        $staff_id = $this->request->getPost('staff_id');
        $staff = $this->db->query("SELECT * FROM staff WHERE id='$staff_id'")->getRow();
        // echo "<pre>";
        // print_r($staff);exit;
        $location = $this->db->query("SELECT * FROM location")->getResult();

        if ($staff) {
        ?>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Driver Registration Form</title>
            </head>

            <body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">
                <div style="width: 80%; margin: 20px auto; border: 1px solid #000; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);" id="staff-details">
                    <div style="text-align: center; margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                        <img src="<?php echo base_url(); ?>/assets/admin/images/yasujalogo.png" alt="Yasuja Logo" style="height: 50px; margin-right: 10px;">
                        <h2 style="margin: 0; color: red; background-color: lightyellow; padding: 5px;">YASUJA ENTERPRISES PVT. LTD.</h2>
                    </div>
                    <p style="text-align: center; margin: 0;">(DRIVER REGISTRATION FORM)</p>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; padding: 10px; margin-right: 10px;">
                            <span style="flex: 0 0 auto; margin-right: 10px;">TRUCK NO:</span>
                            <div style="flex: 1; border: 1px solid black; padding: 5px; text-align: center;">
                                <strong></strong>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; padding: 10px;">
                            <span style="flex: 0 0 auto; margin-right: 10px;">DRIVER CODE:</span>
                            <div style="flex: 1; border: 1px solid black; padding: 5px; text-align: center;">
                                <strong><?= $staff->staff_code; ?></strong>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">

                        <!-- Left Side: Driver Data -->
                        <div style="flex: 2; padding-right: 10px;">
                            <div>DRIVER NAME: <?= $staff->name; ?></div>
                            <div>DATE OF JOINING: <?= date('d-m-Y', strtotime($staff->doj)); ?></div>
                            <div>OPENING HSD: <?= $staff->opening_balance; ?></div>
                            <div>CONTACT NO: <?= $staff->tel; ?></div>
                            <div>REFERENCE:</div>
                        </div>

                        <!-- Right Side: Image Field -->
                        <div style="flex: 1;  padding: 10px; text-align: center;">
                            <img src="<?php echo base_url(); ?>/uploads/<?= $staff->img ?>" alt="Driver Photo" style="width: 100%; height: auto; max-height: 100px;">
                        </div>
                    </div>

                    <hr style="border: 2px solid #000; margin-bottom: 1px;">
                    <hr style="border: 2px solid #000; margin-top: 1px;">

                    <h4 style="text-align: center; margin-bottom: 10px;  color: red; background-color: lightyellow;">DOCUMENT DETAILS</h4>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; padding: 10px; margin-right: 10px;">
                            <span style="flex: 0 0 auto; margin-right: 10px;">DL NO:</span>
                            <div style="flex: 1; border: 1px solid black; padding: 5px; text-align: center;">
                                <strong><?= $staff->dl_number; ?></strong>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; padding: 10px;">
                            <span style="flex: 0 0 auto; margin-right: 10px;">AADHAR NO:</span>
                            <div style="flex: 1; border: 1px solid black; padding: 5px; text-align: center;">
                                <strong><?= $staff->aadhaar_no; ?></strong>
                            </div>
                        </div>
                    </div>
                    <h4 style="text-align: center; margin-bottom: 10px;  color: red; background-color: lightyellow;">BANK ACCOUNT DETAILS</h4>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #000; padding: 10px; text-align: center;">A/C NO</th>
                                <th style="border: 1px solid #000; padding: 10px; text-align: center;">IFSC CODE</th>
                                <th style="border: 1px solid #000; padding: 10px; text-align: center;">A/C HOLDER NAME</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #000; padding: 10px; text-align: center;"><?= $staff->ac_no; ?></td>
                                <td style="border: 1px solid #000; padding: 10px; text-align: center;"><?= $staff->ifsc; ?></td>
                                <td style="border: 1px solid #000; padding: 10px; text-align: center;"><?= $staff->name_bank; ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <hr style="border: 2px solid #000; margin-bottom: 1px;">
                    <hr style="border: 2px solid #000; margin-top: 1px;">

                    <h4 style="text-align: center; margin-bottom: 10px;  color: red; background-color: lightyellow;">PERSONAL DETAILS</h4>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center;">
                            <span style="flex: 1;">FATHER'S NAME:</span>
                            <div style="flex: 2; border: 1px solid black; padding: 5px; text-align: center;"><?= $staff->fathers_name; ?></div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="flex: 1;">SPOUSE NAME:</span>
                            <div style="flex: 2; border: 1px solid black; padding: 5px; text-align: center;"><?= $staff->spouse_name; ?></div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="flex: 1;">DATE OF BIRTH:</span>
                            <div style="flex: 2; border: 1px solid black; padding: 5px; text-align: center;"><?= $staff->dob; ?></div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="flex: 1;">BLOOD GROUP:</span>
                            <div style="flex: 2; border: 1px solid black; padding: 5px; text-align: center;"><?= $staff->blood_group; ?></div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="flex: 1;">FAMILY CONTACT NO:</span>
                            <div style="flex: 2; border: 1px solid black; padding: 5px; text-align: center;"><?= $staff->family_contact; ?></div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="flex: 1;">ADDRESS:</span>
                            <div style="flex: 2; border: 1px solid black; padding: 5px; text-align: center;"><?= $staff->address; ?></div>
                        </div>
                    </div>

                    <hr style="border: 2px solid #000; margin-bottom: 1px;">
                    <hr style="border: 2px solid #000; margin-top: 1px;">


                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <div style="flex: 1; text-align: center;">SIGNATURE OF AUTHORITY</div>
                        <div style="flex: 1; text-align: center; margin-left: 20px;">SIGNATURE OF DRIVER</div>
                    </div>
                    <button onclick="printStaffData()" class="btn btn-success">Print</button>
                </div>
            </body>

            </html>
            <script src="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/js/uikit.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/js/uikit-icons.min.js"></script>
            <script>
                function printStaffData() {
                    var content = document.getElementById('staff-details').innerHTML;
                    var printWindow = window.open('', '', 'width=800,height=600');

                    // Write the HTML structure to the new window
                    printWindow.document.write('<html><head><title>Staff Details</title>');
                    printWindow.document.write('<style>body{font-family: Arial, sans-serif; padding: 20px;} table{width: 100%; border-collapse: collapse;} table, th, td{border: 1px solid black;} th, td{padding: 10px; text-align: left;}</style>');
                    printWindow.document.write('</head><body>');

                    // Write the content directly
                    printWindow.document.write(content);

                    // Add a script to print and close the window
                    printWindow.document.write('<script>window.onload = function() { window.print(); window.close(); };</' + 'script>');

                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                }
            </script>
        <?php
        } else {
            echo "<p>No staff found with the provided ID.</p>";
        }
    }

    public function DeleteStaff()
    {

        $segment = $this->request->getUri()->getSegment(3);
        $this->db->table('staff')->delete(array('id' => $segment));
        return redirect()->to('Admin/staf');
    }
    public function edit_staff()
    {
        $staff_id = $this->request->getPost('staff_id');
        $staff = $this->db->query("SELECT * FROM staff WHERE id='$staff_id'")->getRow();
        $location = $this->db->query("SELECT * FROM location")->getResult();

        if ($staff) {
        ?>
            <form action="<?php echo base_url(); ?>/admin/edit_staf" enctype="multipart/form-data" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Employee Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Enter Your Name" value="<?= $staff->name; ?>">
                                <?php if (isset($validation) && $validation->getError('name')): ?>
                                    <span class="text-danger"><?= $validation->getError('name'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <input type="hidden" name="staff_id" value="<?= $staff->id; ?>" />
                            <div class="uk-margin">
                                <label>User Type</label>
                                <select class="form-control" name="user_type" aria-label="Select">
                                    <option value="DRIVER" <?= $staff->user_type == 'DRIVER' ? 'selected' : ''; ?>>Driver</option>
                                    <option value="STAFF" <?= $staff->user_type == 'STAFF' ? 'selected' : ''; ?>>Staff </option>
                                </select>
                                <?php if (isset($validation) && $validation->getError('user_type')): ?>
                                    <span class="text-danger"><?= $validation->getError('user_type'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Date of Join</label>
                                <input type="date" class="form-control" name="doj" placeholder="Enter DOJ" value="<?= $staff->doj; ?>">
                                <?php if (isset($validation) && $validation->getError('doj')): ?>
                                    <span class="text-danger"><?= $validation->getError('doj'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Salary</label>
                                <input type="text" class="form-control" name="salary" placeholder="Enter Salary" value="<?= $staff->salary; ?>">
                                <?php if (isset($validation) && $validation->getError('salary')): ?>
                                    <span class="text-danger"><?= $validation->getError('salary'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label>Upload Image</label>
                            <input type="file" name="img" class="form-control">
                            <?php if (isset($validation) && $validation->getError('img')): ?>
                                <span class="text-danger"><?= $validation->getError('img'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Name in Bank</label>
                                <input type="text" class="form-control" name="name_bank" placeholder="Enter your name in bank" value="<?= $staff->name_bank; ?>">
                                <?php if (isset($validation) && $validation->getError('name_bank')): ?>
                                    <span class="text-danger"><?= $validation->getError('name_bank'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>A/c No</label>
                                <input type="number" class="form-control" name="ac_no" placeholder="Enter account number" value="<?= $staff->ac_no; ?>">
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('ac_no'); ?></span><?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>IFSC Code</label>
                                <input type="text" class="form-control" name="ifsc" placeholder="Enter your IFSC code" value="<?= $staff->ifsc; ?>">
                                <?php if (isset($validation) && $validation->getError('ifsc')): ?>
                                    <span class="text-danger"><?= $validation->getError('ifsc'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>DL Front</label>
                                <input type="file" class="form-control" name="dl_front" placeholder="Upload your DL picture" value="<?= $staff->dl_front; ?>">
                                <?php if (isset($validation) && $validation->getError('dl_front')): ?>
                                    <span class="text-danger"><?= $validation->getError('dl_front'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>DL Back</label>
                                <input type="file" class="form-control" name="dl_back" placeholder="Upload your DL picture" value="<?= $staff->dl_back; ?>">
                                <?php if (isset($validation) && $validation->getError('dl_back')): ?>
                                    <span class="text-danger"><?= $validation->getError('dl_back'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>DL Number</label>
                                <input type="text" class="form-control" name="dl_number" placeholder="Enter DL number " value="<?= $staff->dl_number; ?>">
                                <?php if (isset($validation) && $validation->getError('dl_number')): ?>
                                    <span class="text-danger"><?= $validation->getError('dl_number'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>DL Expiry</label>
                                <input type="date" class="form-control" name="dl_expiry" placeholder="Enter DL expiry date" value="<?= $staff->dl_expiry; ?>">
                                <?php if (isset($validation) && $validation->getError('dl_expiry')): ?>
                                    <span class="text-danger"><?= $validation->getError('dl_expiry'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Aadhaar Number</label>
                                <input type="text" class="form-control" name="aadhaar_no" placeholder=" Aadhaar Number" value="<?= $staff->aadhaar_no; ?>">
                                <?php if (isset($validation) && $validation->getError('aadhaar_no')): ?>
                                    <span class="text-danger"><?= $validation->getError('aadhaar_no'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Aadhaar Front</label>
                                <input type="file" class="form-control" name="aadhaar_front" placeholder="Upload Aadhaar" value="<?= $staff->aadhaar_front; ?>">
                                <?php if (isset($validation) && $validation->getError('aadhaar_front')): ?>
                                    <span class="text-danger"><?= $validation->getError('aadhaar_front'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Aadhaar Back</label>
                                <input type="file" class="form-control" name="aadhaar_back" placeholder="Upload Aadhaar" value="<?= $staff->aadhaar_back; ?>">
                                <?php if (isset($validation) && $validation->getError('aadhaar_back')): ?>
                                    <span class="text-danger"><?= $validation->getError('aadhaar_back'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Contact No.</label>
                                <input type="tel" class="form-control" name="tel" placeholder="Enter your Contact Number" value="<?= $staff->tel; ?>">
                                <?php if (isset($validation) && $validation->getError('tel')): ?>
                                    <span class="text-danger"><?= $validation->getError('tel'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Father's Name</label>
                                <input type="text" class="form-control" name="fathers_name" placeholder="Enter your Father's Name" value="<?= $staff->fathers_name; ?>">
                                <?php if (isset($validation) && $validation->getError('fathers_name')): ?>
                                    <span class="text-danger"><?= $validation->getError('fathers_name'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Spouse Name</label>
                                <input type="text" class="form-control" name="spouse_name" placeholder="Enter your Spouse Name" value="<?= $staff->spouse_name; ?>">
                                <?php if (isset($validation) && $validation->getError('spouse_name')): ?>
                                    <span class="text-danger"><?= $validation->getError('spouse_name'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="dob" placeholder="Enter your Date of Birth" value="<?= $staff->dob; ?>">
                                <?php if (isset($validation) && $validation->getError('dob')): ?>
                                    <span class="text-danger"><?= $validation->getError('dob'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Family Contact No.</label>
                                <input type="tel" class="form-control" name="family_contact" placeholder="Enter your Family Contact Number" value="<?= $staff->family_contact; ?>">
                                <?php if (isset($validation) && $validation->getError('family_contact')): ?>
                                    <span class="text-danger"><?= $validation->getError('family_contact'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Blood Group</label>
                                <select class="form-control" name="blood_group" aria-label="Select">
                                    <option value="O+" <?= $staff->blood_group == 'O+' ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?= $staff->blood_group == 'O-' ? 'selected' : ''; ?>>O-</option>
                                    <option value="A+" <?= $staff->blood_group == 'A+' ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?= $staff->blood_group == 'A-' ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?= $staff->blood_group == 'B+' ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?= $staff->blood_group == 'B-' ? 'selected' : ''; ?>>B-</option>
                                    <option value="AB+" <?= $staff->blood_group == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?= $staff->blood_group == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                </select>
                                <?php if (isset($validation) && $validation->getError('blood_group')): ?>
                                    <span class="text-danger"><?= $validation->getError('blood_group'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Opening Balance</label>
                                <input type="text" name="opening_balance" class="form-control" value="<?= $staff->opening_balance; ?>" />
                                <?php if (isset($validation) && $validation->getError('opening_balance')): ?>
                                    <span class="text-danger"><?= $validation->getError('opening_balance'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Address</label>
                                <label for="location">Location</label>
                                <select name="address" id="location" class="form-control">
                                    <option value="">Select location</option>
                                    <?php foreach ($location as $loc): ?>
                                        <option <?= ($loc->location_id == $staff->address) ? 'selected' : ''; ?> value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (isset($validation) && $validation->getError('address')): ?>
                                    <span class="text-danger"><?= $validation->getError('address'); ?></span>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
                <p></p>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php
        } else {
            echo "No staff found with the provided ID.";
        }
    }
    public function edit_staf()
    {
        $staff_id = $this->request->getPost('staff_id');
        $user_id = $this->session->get('user_id');
        $data['allstaf'] = $this->AdminModel->Getallstaf();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $rules = [
            'user_type' => 'required',
            'name' => 'required|max_length[100]',
            'salary' => 'required|numeric',
            'tel' => 'required|numeric|min_length[10]|max_length[15]',
            'address' => 'required|integer',
        ];

        if ($this->validate($rules)) {
            $data = [
                'user_type'       => $this->request->getPost('user_type'),
                'name'            => $this->request->getPost('name'),
                'doj'             => $this->request->getPost('doj'),
                'salary'          => $this->request->getPost('salary'),
                'name_bank'       => $this->request->getPost('name_bank'),
                'ac_no'           => $this->request->getPost('ac_no'),
                'ifsc'            => $this->request->getPost('ifsc'),
                'dl_number'       => $this->request->getPost('dl_number'),
                'dl_expiry'       => $this->request->getPost('dl_expiry'),
                'aadhaar_no'      => $this->request->getPost('aadhaar_no'),
                'tel'             => $this->request->getPost('tel'),
                'fathers_name'    => $this->request->getPost('fathers_name'),
                'spouse_name'     => $this->request->getPost('spouse_name'),
                'dob'             => $this->request->getPost('dob'),
                'family_contact'  => $this->request->getPost('family_contact'),
                'blood_group'     => $this->request->getPost('blood_group'),
                'opening_balance' => $this->request->getPost('opening_balance'),
                'address'         => $this->request->getPost('address'),
            ];

            // Handle file uploads correctly
            $fileFields = ['img', 'dl_front', 'dl_back', 'aadhaar_front', 'aadhaar_back'];
            foreach ($fileFields as $fileField) {
                $file = $this->request->getFile($fileField);
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/', $newName);
                    $data[$fileField] = $newName;
                }
            }

            // Update staff record
            $this->db->table('staff')->where('id', $staff_id)->update($data);

            return redirect()->to(base_url('/admin/staf'))
                            ->with('success', 'Staff details updated successfully');
        } else {
            // Reload staff and location for view in case of validation errors
            $data['validation'] = $this->validator;
            $data['staff'] = $this->db->table('staff')->where('id', $staff_id)->get()->getRow();
            $data['location'] = $this->db->table('location')->get()->getResult();

            return view('admin/staf_vw', $data);
        }
    }
    public function upload_staff_excel()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            // Process the data
            foreach ($data as $row) {
                $location_name = $row[13];
                $locati = $this->db->table('location')->where('location_name', $location_name)->get()->getResult();
                foreach ($locati as $loc) {
                }

                if (!empty($locati)) {
                    if (!empty($row[0])) { // Assuming the first column is not empty
                        $staffData = [
                            'user_type' => $row[0],
                            'name' => $row[1],
                            'doj' => $row[2],
                            'salary' => $row[3],
                            'name_bank' => $row[4],
                            'ac_no' => $row[5],
                            'ifsc' => $row[6],
                            'dl_expiry' => $row[7],
                            'tel' => $row[8],
                            'spouse_name' => $row[9],
                            'dob' => $row[10],
                            'family_contact' => $row[11],
                            'blood_group' => $row[12],
                            'address' => $loc->location_id,
                            'img' => $row[14], // Add img field
                            'dl_number' => $row[15], // Add DL number field
                            'aadhaar_no' => $row[16], // Add Aadhaar number field
                            'fathers_name' => $row[17], // Add Father's name field
                            'opening_balance' => $row[18], // Add opening balance field
                        ];

                        // Save to the database
                        $this->db->table('staff')->insert($staffData);
                        $lastInsertID = $this->db->insertID();

                        $clean_name = preg_replace('/[^a-zA-Z0-9]/', '', $row[1]);
                        $firstThreeChars = substr($clean_name, 0, 3);
                        $staff_code = $lastInsertID . '-' . $firstThreeChars;

                        $data2 = [
                            'staff_code' => $staff_code,
                        ];
                        $this->db->table('staff')->where('id', $lastInsertID)->update($data2);

                        $data1 = [
                            'staff_id' => $lastInsertID,
                            'oamout' => $row[18],
                            'oc_type' => 1,
                            // 'yearmonth' => $yearMonth

                        ];
                        $this->db->table('opening_closing')->insert($data1);
                    }
                }
            }

            // Redirect to a success page
            return redirect()->to(base_url('/admin/staf'))->with('success', 'Staff data uploaded successfully');
        } else {
            return redirect()->back()->with('error', 'File upload failed');
        }
    }
    function Vendor()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allvendor'] = $this->AdminModel->Get_vendor();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            // 			echo "<pre>";
            //             print_r($data['allvendor']);exit;


            return view('admin/vendor_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }
    public function AddVendor()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allvendor'] = $this->AdminModel->Get_vendor();

            $rules = [
                'name' => 'required',
                'gst' => 'required',
                'type' => 'required',
                'pan' => 'required',
                'bal' => 'required|numeric',

            ];

            $vendor_name = $this->request->getPost('name');

            if ($this->validate($rules)) {

                $data = [
                    'name' => $vendor_name,
                    'gst' => $this->request->getPost('gst'),
                    'type' => $this->request->getPost('type'),
                    'pan' => $this->request->getPost('pan'),
                    'bal' => $this->request->getPost('bal'),
                    'location' => $this->request->getPost('location'),

                ];

                $this->db->table('vendor')->insert($data);
                $insertID = $this->db->insertID();


                $clean_vendor_name = preg_replace('/[^a-zA-Z0-9]/', '', $vendor_name);
                $firstThreeChars = substr($clean_vendor_name, 0, 3);
                $vendor_code = $insertID . '-' . $firstThreeChars;



                $data2 = [
                    'vendor_code' => $vendor_code,
                ];
                $this->db->table('vendor')->where('id', $insertID)->update($data2);
                $data1 = [
                    'vendor_id' => $insertID,
                    'from_date' => $this->request->getPost('fromdate'),
                    'vendor_rate' => $this->request->getPost('rate'),
                ];

                $this->db->table('vendor_rate')->insert($data1);

                return redirect()->to('/admin/Vendor')->with('status', 'Sub-admin added successfully!');
            } else {
                $data['validation'] = $this->validator;
                $data['setting'] = $this->AdminModel->Settingdata();
                $data['singleuser'] = $this->AdminModel->userdata($user_id);
                $data['allvendor'] = $this->AdminModel->Get_vendor();
                $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
                echo view('admin/vendor_vw', $data);
            }
        } else {
            $data['validation'] = $this->validator;
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allvendor'] = $this->AdminModel->Get_vendor();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            echo view('admin/staf_vw', $data);
        }
    }
    function vendor_rate()
    {
        $vendor_id = $this->request->getPost('vendor_id');
        $vendor_rate = $this->db->query("SELECT * FROM vendor_rate  where vendor_id='$vendor_id'  ")->getResult();
        //  echo "<pre>";
        //  print_r($vendor_rate);
        ?>
        <form action="<?= base_url(); ?>/Admin/add_vendor_rate" method="post">
            <div class="uk-grid uk-grid-small uk-child-width-expand">

                <div>
                    <lable>From Date</lable>
                    <input type="hidden" class="uk-input" name="vendorrate_id" value="<?= $vendor_id ?>" />
                    <input type="date" class="form-control" name="from_date" required />
                </div>
                <div>
                    <lable>Rate</lable>
                    <input type="text" class="form-control" name="rate" required />
                </div>
                <div>
                    <p></p>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </div>
        </form>
        <table class="table">
            <tr>
                <th>Sl no</th>
                <th> From Date</th>
                <th>Rate</th>
                <th>Action</th>
            </tr>
            <?php $i = 1;
            foreach ($vendor_rate as $vr) { ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td> <?= $vr->from_date ?></td>
                    <td><?= $vr->vendor_rate ?></td>
                    <td><a href="<?= base_url(); ?>/Admin/delete_vendor_rate/<?= $vr->vendor_rate_id ?>" class="btn btn-danger">delete</a></td>
                </tr>
            <?php } ?>

        </table>

        <?php }
    function add_vendor_rate()
    {
        $vendor_id = $this->request->getVar('vendorrate_id');
        $from_date = $this->request->getVar('from_date');
        $rate = $this->request->getPost('rate');

        $countvr = $this->db->query("SELECT * FROM vendor_rate WHERE vendor_id='$vendor_id' AND from_date='$from_date'")->getResult();
        foreach ($countvr as $cntvr) {
        }



        if (count($countvr) == 0) {
            $data1 = [
                'vendor_id' => $vendor_id,
                'from_date' => $from_date,
                'vendor_rate' => $rate,
            ];
            $this->db->table('vendor_rate')->insert($data1);
        } else {
            $data1 = [
                'vendor_rate' => $rate,
            ];

            $this->db->table('vendor_rate')
                ->where('vendor_rate_id', $cntvr->vendor_rate_id)
                ->update($data1);
        }



        $data2 = [
            'rate' => $rate,
        ];
        $this->db->table('diselentry')
            ->where('vendor_id', $vendor_id)
            ->where('diesel_date >=', $from_date)
            ->update($data2);

        return redirect()->to('/admin/Vendor')->with('status', 'Sub-admin added successfully!');
    }
    function delete_vendor_rate()
    {
        $segment = $this->request->getUri()->getSegment(3);
        $this->db->table('vendor_rate')->delete(array('vendor_rate_id' => $segment));
        return redirect()->to('Admin/vendor');
    }
    public function deletevendor()
    {

        $segment = $this->request->getUri()->getSegment(3);
        $this->db->table('vendor')->delete(array('id' => $segment));
        return redirect()->to('Admin/vendor');
    }
    public function edit_vendor()
    {
        $vendor_id = $this->request->getPost('vendor_id');
        $vendor = $this->db->query("SELECT * FROM vendor WHERE id='$vendor_id'")->getRow();
        $location = $this->db->query("SELECT * FROM location")->getResult();
        if ($vendor) {
        ?>
            <form action="<?= base_url(); ?>/Admin/EditVendor" enctype="multipart/form-data" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="hidden" name="vendor_id" value="<?= $vendor_id ?>" />
                                <input type="text" class="form-control" name="name" placeholder="Enter Your Name" value="<?= htmlspecialchars($vendor->name) ?>">
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('name'); ?></span><?php } ?>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>GST</label>
                                <input type="text" class="form-control" id="gst" name="gst" placeholder="Enter GST number" value="<?= htmlspecialchars($vendor->gst) ?>">
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('gst'); ?></span><?php } ?>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Location</label>
                                <select class="form-control" name="location">
                                    <option value="">Select Location</option>
                                    <?php foreach ($location as $loc) { ?>
                                        <option <?php if ($loc->location_id == $vendor->location) {
                                                    echo "selected";
                                                } ?> value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location'); ?></span><?php } ?>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label>Party/Pump/Vender</label>
                            <select class="form-control" aria-label="Select" name="type">
                                <option value="Party" <?= $vendor->type == 'Party' ? 'selected' : '' ?>>PARTY</option>
                                <option value="Pump" <?= $vendor->type == 'Pump' ? 'selected' : '' ?>>PUMP</option>
                                <option value="Vender" <?= $vendor->type == 'Vendor' ? 'selected' : '' ?>>VENDOR</option>
                            </select>
                            <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('type'); ?></span><?php } ?>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>PAN No</label>
                                <input type="text" class="form-control" id="panNo" name="pan" placeholder="Enter your PAN No." value="<?= htmlspecialchars($vendor->pan) ?>">
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('pan'); ?></span><?php } ?>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Opening Bal</label>
                                <input type="number" class="form-control" id="bal" name="bal" placeholder="Enter your opening balance" value="<?= htmlspecialchars($vendor->bal) ?>">
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('bal'); ?></span><?php } ?>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        <?php
        } else {
            echo 'Vendor not found';
        }
    }
    function EditVendor()
    {
        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['allvendor'] = $this->AdminModel->Get_vendor();
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

        $rules = [
            'name' => 'required',
            'location' => 'required',
            'type' => 'required',
            'pan' => 'required',
            'bal' => 'required|numeric',
        ];

        // Validate the form data
        if ($this->validate($rules)) {
            // Get the form data
            $vendor_id = $this->request->getPost('vendor_id');
            $name = $this->request->getPost('name');
            $gst = $this->request->getPost('gst');
            $location = $this->request->getPost('location');
            $type = $this->request->getPost('type');
            $pan = $this->request->getPost('pan');
            $bal = $this->request->getPost('bal');
            $fromdate = $this->request->getPost('fromdate');
            $rate = $this->request->getPost('rate');

            // Update the vendor data in the database
            $data = [
                'name' => $name,
                'gst' => $gst,
                'location' => $location,
                'type' => $type,
                'pan' => $pan,
                'bal' => $bal,
                'fromdate' => $fromdate,
                'rate' => $rate
            ];

            $this->db->table('vendor')->where('id', $vendor_id)->update($data);
            return redirect()->to(base_url('Admin/Vendor'))->with('success', 'Vendor updated successfully.');
        } else {
            $data['validation'] = $this->validator;
            return view('admin/vendor_vw', $data);
        }
    }
    function vendor_filter()
    {
        $vendor_type = $this->request->getPost('type');
        $allvendor = $this->AdminModel->Get_vendor();
        ?>
        <div class="table-responsive">
            <table class="display" id="row_create" style="width:100%">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Vendor Code</th>
                        <th>GST</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>PAN</th>
                        <th>Balance</th>
                        <th>From Date</th>
                        <th>Rate</th>
                        <th>
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($allvendor as $vendor) {
                        if ($vendor->type == $vendor_type) {
                    ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $vendor->name; ?></td>
                                <td><?= $vendor->vendor_code; ?></td>
                                <td><?= $vendor->gst; ?></td>
                                <td><?= $vendor->type; ?></td>
                                <td><?= $vendor->location_name; ?></td>
                                <td><?= $vendor->pan; ?></td>
                                <td><?= $vendor->bal; ?></td>
                                <td><?= $vendor->from_date; ?></td>
                                <td><?= $vendor->vendor_rate; ?></td>
                                <td>
                                    <div class="uk-button-group">
                                        <a href="javascript:void(0);" onClick="editvendor('<?= $vendor->id; ?>');" class="uk-button uk-button-small uk-button-secondary">
                                            Edit
                                        </a>

                                        <a href="javascript:void(0);" onClick="vendor_rate('<?= $vendor->id; ?>');" class="uk-button uk-button-small uk-button-primary">
                                            view rate
                                        </a>
                                        <a href="<?= base_url('Admin/deletevendor/' . $vendor->id); ?>" onclick="return confirm('Are you sure you want to delete this item?');" class="uk-button uk-button-small uk-button-danger">
                                            delete
                                        </a>

                                    </div>
                                </td>


                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
        <script src="<?php echo base_url(); ?>/assets/admin/js/datatable/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>/assets/admin/js/datatable/datatables/datatable.custom.js"></script>
        <script src="<?php echo base_url(); ?>/assets/admin/js/datatable/datatables/datatable.custom1.js"></script>
    <?php
    }
    function upload_vendor_excel()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                if ($row[0] == 'id') {
                    continue; // Skip the header row
                }


                $location_name = $row[3];
                $vendor_name = $row[1];
                $location_id = '';
                $location = $this->db->query("SELECT * FROM location where location_shordname='$location_name'")->getResult();
                if (!empty($location)) {
                    foreach ($location as $loc) {
                    }
                    $location_id = $loc->location_id;
                }

                $vendorData = [

                    'name' => $row[1],
                    'gst' => $row[2],
                    'location' => $location_id,
                    'type' => $row[4],
                    'pan' => $row[5],
                    'bal' => $row[6],

                ];

                $vend = $this->db->query("SELECT * FROM vendor where pan='$row[5]'")->getResult();

                if (!empty($vend)) {
                    foreach ($vend as $ven) {
                    }
                    $this->db->table('vendor')->where('id', $ven->id)->update($vendorData);
                } else {

                    $this->db->table('vendor')->insert($vendorData);
                    $insertID = $this->db->insertID();


                    $clean_vendor_name = preg_replace('/[^a-zA-Z0-9]/', '', $vendor_name);
                    $firstThreeChars = substr($clean_vendor_name, 0, 3);
                    $vendor_code = $insertID . '-' . $firstThreeChars;

                    $data2 = [
                        'vendor_code' => $vendor_code,
                    ];
                    $this->db->table('vendor')->where('id', $insertID)->update($data2);
                }
            }

            return redirect()->to(base_url('/Admin/Vendor'))->with('success', 'Vendor data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }


    function location()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

            return view('admin/location', $data);
        } else {
            return redirect()->to('admin/');
        }
    }
    function insert_location()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();;
            $rules = [
                'city_name' => 'required|is_unique[location.location_name]',
                'short_name' => 'required|max_length[100]',
            ];

            // Validate the input data
            if ($this->validate($rules)) {
                $city_name = $this->request->getPost('city_name');
                $short_name = $this->request->getPost('short_name');

                $data = [
                    'location_name' => $city_name,
                    'location_shordname' => $short_name,
                ];
                $this->db->table('location')->insert($data);
                return redirect()->to('admin/location');
            } else {

                $data['validation'] = $this->validator;
                return view('admin/location', $data);
            }
        } else {
            return redirect()->to('admin/');
        }
    }
    function excel_location()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                if ($row[0] == 'id') {
                    continue; // Skip the header row
                }

                $location_name = $row[0];
                $location_shordname = $row[1];

                // Check if the location_name or location_shordname already exists
                $existingLocation = $this->db->table('location')
                    ->where('location_name', $location_name)
                    ->orWhere('location_shordname', $location_shordname)
                    ->get()
                    ->getRow();

                if (!$existingLocation) {
                    // If not exists, insert the data
                    $data = [
                        'location_name' => $location_name,
                        'location_shordname' => $location_shordname,
                    ];

                    $this->db->table('location')->insert($data);
                }
            }

            return redirect()->to(base_url('/Admin/location'))->with('success', 'Vendor data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }
    function edit_location()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');

            $unit_id = $this->request->getVar('unit_id');
            $name = $this->request->getVar('name');
            $sname = $this->request->getVar('sname');

            $data = [
                'location_name' => $name,
                'location_shordname' => $sname,
            ];

            $this->db->table('location')->update($data, ['location_id' => $unit_id]);

            return redirect()->to('admin/location');
        } else {
            return redirect()->to('admin/');
        }
    }
    function delete_location()
    {

        if ($this->session->get('user_id')) {
            $user_id = $this->request->getVar('user_id');
            $this->db->table('location')->delete(array('location_id' => $user_id));
            return redirect()->to('admin/location');
        } else {
            return redirect()->to('admin/');
        }
    }

    function unit()
    {

        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['units'] = $this->db->query("SELECT * FROM units")->getResult();



            return view('admin/units_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }

    function addunit()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');


            $name = $this->request->getVar('name');
            $sname = $this->request->getVar('sname');

            $data = [
                'unit_name' => $name,
                'unit_short_name' => $sname,
            ];

            $this->db->table('units')->insert($data);
            return redirect()->to('admin/unit');
        } else {
            return redirect()->to('admin/');
        }
    }
    function editunit()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');

            $unit_id = $this->request->getVar('unit_id');
            $name = $this->request->getVar('name');
            $sname = $this->request->getVar('sname');

            $data = [
                'unit_name' => $name,
                'unit_short_name' => $sname,
            ];

            $this->db->table('units')->update($data, ['unit_id' => $unit_id]);

            return redirect()->to('admin/unit');
        } else {
            return redirect()->to('admin/');
        }
    }
    function delete_units()
    {

        if ($this->session->get('user_id')) {
            $user_id = $this->request->getVar('user_id');
            $this->db->table('units')->delete(array('unit_id' => $user_id));
            return redirect()->to('admin/unit');
        } else {
            return redirect()->to('admin/');
        }
    }
    function excel_units()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                if ($row[0] == 'id') {
                    continue; // Skip the header row
                }

                $data = [
                    'unit_name' => $row[0],
                    'unit_short_name' => $row[1],

                ];

                // Check if vendor exists
                $this->db->table('units')->insert($data);
            }

            return redirect()->to(base_url('/Admin/unit'))->with('success', 'Vendor data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }
    public function items()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['units'] = $this->db->query("SELECT * FROM units")->getResult();
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['items'] = $this->AdminModel->itemdtls();
        // echo "<pre>";
        // print_r($data['items']);exit;

        return view('admin/items_vw', $data);
    }
    function delete_items()
    {

        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('items')->delete(array('id' => $segment));
            return redirect()->to('Admin/items');
        } else {
            return redirect()->to('admin/');
        }
    }
    public function insert_items()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['units'] = $this->db->query("SELECT * FROM units")->getResult();
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['items'] = $this->AdminModel->itemdtls();

        // Set validation rules
        $rules = [
            'item_id' => 'required',
            'item_name' => 'required',
            'unit_of_measurement' => 'required',
            'avg_price_rate' => 'required|numeric',
            'amount' => 'required',
        ];

        // If validation fails, reload the form with errors
        if ($this->validate($rules)) {
            $itemCode = $this->request->getPost('item_id');
            $itemName = $this->request->getPost('item_name');
            $unitOfMeasurement = $this->request->getPost('unit_of_measurement');
            $avgPriceRate = $this->request->getPost('avg_price_rate');
            $amount = $this->request->getPost('amount');
            $openingStock = $this->request->getPost('opening_stock');
            $locationWiseQt = $this->request->getPost('location_wise_qt');

            $file = $this->request->getFile('upload_photo');
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads', $newName);
                $uploadPhoto = $newName;
            } else {
                $uploadPhoto = null;
            }

            // Prepare data for insertion
            $data = [
                'item_id' => $itemCode,
                'item_name' => $itemName,
                'unit_id' => $unitOfMeasurement,
                'avg_price_rate' => $avgPriceRate,
                'amount' => $amount,
                'opening_stock' => $openingStock,
                'location_wise_qt' => $locationWiseQt,
                'upload_photo' => $uploadPhoto,
            ];


            $this->db->table('items')->insert($data);
            return redirect()->to('admin/items');
        } else {
            $data['validation'] = $this->validator;
            return view('admin/items_vw', $data);
        }
    }
    function edit_item_data()
    {

        $item_id = $this->request->getVar('id');
        $units = $this->db->query("SELECT * FROM units")->getResult();
        $location = $this->db->query("SELECT * FROM location")->getResult();
        $items = $this->db->query("SELECT * FROM items where id='$item_id'")->getResult();

        foreach ($items as $itm) {
        }
    ?>

        <form action="<?php echo base_url(); ?>/Admin/update_items" enctype="multipart/form-data" method="post">
            <div class="uk-margin-bottom">
                <lable>Item Code </lable>
                <input type="hidden" name="item_id" value="<?= $itm->id ?>" />
                <input type="text" name="item_code" placeholder="enter Item code" id="item_id" class="form-control" value="<?= $itm->item_id ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('item_id'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <lable>Item Name </lable>
                <input type="hidden" name="item_id" value="<?= $itm->id ?>" />
                <input type="text" name="item_name" placeholder="enter Item Name" id="item_name" class="form-control" value="<?= $itm->item_name ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('item_name'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <lable>Unit Of Measurement </lable>

                <select class="form-control" name="unit_of_measurement">
                    <option value="">Select</option>
                    <?php foreach ($units as $unit) { ?>
                        <option <?php if ($itm->unit_id == $unit->unit_id) {
                                    echo "selected";
                                } ?> value="<?= $unit->unit_id; ?>"><?= $unit->unit_name; ?></option>
                    <?php  } ?>
                </select>
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('unit_of_measurement'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <lable>Avg Price Rate</lable>
                <input type="number" name="avg_price_rate" placeholder="enter avg. price rate" id="avg_price_rate" class="form-control" value="<?= $itm->avg_price_rate ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('avg_price_rate'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <lable>Amount</lable>
                <input type="text" name="amount" placeholder="enter  amount " id="amount" class="form-control" value="<?= $itm->amount ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amount'); ?></span><?php } ?>
            </div>
            <!--    <div class="uk-margin-bottom">-->
            <!--    <lable>Location  </lable>-->
            <!--     <select class="form-control" name="location_wise_qt">-->
            <!--          <option value="">Select</option>-->
            <!--          <?php foreach ($location as $unit) { ?>-->
            <!--          <option <?php if ($itm->location_wise_qt == $unit->location_id) {
                                        echo "selected";
                                    } ?>   value="<?= $unit->location_id; ?>"><?= $unit->location_name; ?></option>-->
            <!--         <?php  } ?>-->
            <!--      </select>-->
            <!--    <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location_wise_qt'); ?></span><?php } ?>-->
            <!--</div> -->
            <!--    <div class="uk-margin-bottom">-->
            <!--    <lable>Opening Stock </lable>-->
            <!--    <input type="number" name="opening_stock" placeholder="enter Opening stock " id="opening_stock" class="form-control" value="<?= $itm->opening_stock ?>" />-->
            <!--    <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('opening_stock'); ?></span><?php } ?>-->
            <!--</div>   -->

            <div class="uk-margin-bottom">
                <lable>Upload Photo </lable>
                <input type="file" name="upload_photo" placeholder="Upload ur Photo" id="upload_photo" class="form-control" value="<?= set_value('upload_photo') ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('upload_photo'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

    <?php
    }
    public function update_items()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['units'] = $this->db->query("SELECT * FROM units")->getResult();
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['items'] = $this->AdminModel->itemdtls();

        // Set validation rules
        $rules = [
            'item_id' => 'required',
            'item_name' => 'required',
            'unit_of_measurement' => 'required',
            'avg_price_rate' => 'required|numeric',
            'amount' => 'required',
        ];

        // If validation fails, reload the form with errors
        if ($this->validate($rules)) {

            $item_id = $this->request->getPost('item_id');
            $item_code = $this->request->getPost('item_code');
            $itemName = $this->request->getPost('item_name');
            $unitOfMeasurement = $this->request->getPost('unit_of_measurement');
            $avgPriceRate = $this->request->getPost('avg_price_rate');
            $amount = $this->request->getPost('amount');
            $openingStock = $this->request->getPost('opening_stock');
            $locationWiseQt = $this->request->getPost('location_wise_qt');

            $file = $this->request->getFile('upload_photo');
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads', $newName);
                $uploadPhoto = $newName;
            } else {
                $uploadPhoto = null;
            }

            // Prepare data for insertion
            $data = [
                'item_id' => $item_code,
                'item_name' => $itemName,
                'unit_id' => $unitOfMeasurement,
                'avg_price_rate' => $avgPriceRate,
                'amount' => $amount,
                'opening_stock' => $openingStock,
                'location_wise_qt' => $locationWiseQt,
            ];

            if (!empty($uploadPhoto)) {
                $data += [
                    'upload_photo' => $uploadPhoto,
                ];
            }


            $this->db->table('items')->update($data, ['id' => $item_id]);
            return redirect()->to('admin/items');
        } else {
            $data['validation'] = $this->validator;
            return view('admin/items_vw', $data);
        }
    }
    public function excel_items()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                if ($row[0] == 'items code') {
                    continue; // Skip header row
                }

                $unit_name = $row[2];
                $unit = $this->db->query("SELECT * FROM units WHERE unit_short_name='$unit_name'")->getRow();

                $unit_id = $unit ? $unit->unit_id : null;

                $insertData = [
                    'item_id'        => $row[0],
                    'item_name'      => $row[1],
                    'unit_id'        => $unit_id,
                    'avg_price_rate' => $row[3],
                    'amount'         => $row[4],
                ];

                $this->db->table('items')->insert($insertData);
            }

            return redirect()->to(base_url('/Admin/items'))->with('success', 'Item data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }


    public function opening_stocks()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                $item_code = $row[0];



                if (!empty($item_code)) {

                    $item = $this->db->query("SELECT * FROM items WHERE item_name='$item_code'")->getResult();
                    foreach ($item as $itm) {
                    }

                    $location_name = $row[1];
                    $location = $this->db->query("SELECT * FROM location WHERE location_name='$location_name'")->getResult();


                    foreach ($location as $loc) {
                    }

                    // Get the maximum stock code
                    $maxStockCodeResult = $this->db->query("SELECT MAX(stock_code) AS max_stock_code FROM stock")->getRow();
                    $stockCode = $maxStockCodeResult ? $maxStockCodeResult->max_stock_code + 1 : 1000;

                    $date = '1770-06-1';

                    $data = [
                        'stock_code' => $stockCode,
                        'sproduct_id' => $itm->id,
                        'date' => $date,
                        'invoice_date' => $date,
                        'quantity' => $row[2],
                        'available_qty' => $row[2],
                        'location_id' => $loc->location_id,
                    ];

                    $this->db->table('stock')->insert($data);
                } else {

                    continue; // Skip to the next row
                }
            }

            return redirect()->to(base_url('/Admin/items'))->with('success', 'Vendor data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }

    function edit_stock()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $segment = $this->request->getUri()->getSegment(3);
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['stock_edit'] = $this->AdminModel->stockdata($segment);
            $data['product'] = $this->AdminModel->items();
            $data['cart_dtls'] = $this->AdminModel->purcartdetails($user_id);
            $data['stock_dtls'] = $this->AdminModel->stock_dtls();
            $data['vendor'] = $this->AdminModel->Get_vendor();
            $data['vendor'] = $this->db->query("SELECT * FROM vendor")->getResult();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['items'] = $this->db->query("SELECT * FROM items")->getResult();

            return view('admin/edit_stock_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    
    public function update_stock(){
        if (!$this->session->get('user_id')) {
            return redirect()->to('Admin');
        }
    
        $stock_id = $this->request->getPost('stock_id');
        $item_name       = $this->request->getPost('item_name');
        $supplier_name       = $this->request->getPost('supplier_name');
        $location_name     = $this->request->getPost('location_name');
        $quantity      = $this->request->getPost('quantity');
        $amount     = $this->request->getPost('rate');
        $totalAmount = $quantity * $amount;
    
        $data = [
            'sproduct_id' => $item_name,
            'supplier_id'  => $supplier_name,
            'location_id'       => $location_name,
            'quantity'      => $quantity,
            'rate'      => $amount,
            'amount'      => $totalAmount,
        ];
    
        $builder = $this->db->table('stock');
        $builder->where('stock_id', $stock_id);
        $updated = $builder->update($data);
    
        if ($updated) {
            return redirect()->to('admin/Purchase_Voucher');
            // return $this->response->setJSON(['status' => 'success', 'message' => 'Updated successfully.']);
        } else {
            return redirect()->to('admin/');
            // return $this->response->setJSON(['status' => 'error', 'message' => 'Update failed or no changes made.']);
        }
    }

    function Purchase_Voucher()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['stock_dtls'] = $this->AdminModel->stock_dtls();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['vendor'] = $this->db->query("SELECT * FROM vendor")->getResult();
            return view('admin/Allstock_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function downloadStock($stock_code)
    {
        $stockDetails = $this->AdminModel->singleStock($stock_code);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header row
        $sheet->setCellValue('A1', 'Sl No');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Supplier Name');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->setCellValue('E1', 'Unit');
        $sheet->setCellValue('F1', 'Rate');
        $sheet->setCellValue('G1', 'Amount');

        // Styling header
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        // Data
        $row = 2;
        $i = 1;
        $total = 0;

        foreach ($stockDetails as $stk) {
            $amount = $stk->quantity * $stk->rate;
            $total += $amount;

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $stk->item_name . ' (' . $stk->item_id . ')');
            $sheet->setCellValue('C' . $row, $stk->name);
            $sheet->setCellValue('D' . $row, $stk->quantity);
            $sheet->setCellValue('E' . $row, $stk->unit_name);
            $sheet->setCellValue('F' . $row, $stk->rate);
            $sheet->setCellValue('G' . $row, $amount);

            $row++;
        }

        // Total row
        $sheet->setCellValue('F' . $row, 'Total');
        $sheet->setCellValue('G' . $row, $total);
        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $filename = 'stock_' . $stock_code . '.xlsx';
        $writer = new XlsxWriter($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    function stock_transfer()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['product'] = $this->AdminModel->items();
            $data['cart_dtls'] = $this->AdminModel->stockTransferdetails($user_id);
            $data['vendor'] = $this->AdminModel->Get_vendor();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            return view('admin/stock_transfer_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    function frm_addtocartStockTransfer()
    {

        $user_id = $this->session->get('user_id');
        $product_id = $this->request->getPost('productId');
        $invoicedate = $this->request->getPost('invoicedate');
        $from_location = $this->request->getPost('location_id');
        $to_location = $this->request->getPost('location_id1');
        $CountProduct = $this->db->query("SELECT * FROM cart  where user_id='$user_id' and product_id='$product_id' and cart_type='3'")->getResult();
        $ProductDTLS = $this->db->query("SELECT * FROM items  where id='$product_id' ")->getResult();

        if (count($CountProduct) == 0) {
            foreach ($ProductDTLS as $prd) {
            }

            $data = [
                'user_id' => $user_id,
                'qty' => 1,
                'product_id' => $product_id,
                'invoicedate' => $invoicedate,
                'location' => $from_location,
                'to_location' => $to_location,
                'rate' => $prd->amount,
                'cart_type' => 3,
            ];

            $this->db->table('cart')->insert($data);
        } else {

            echo "and cart_type='3'";
        }
        $cart_dtls = $this->AdminModel->stockTransferdetails($user_id);
        // echo "<pre>";
        // print_r($cart_dtls);exit;
    ?>

        <table class="table table-responsive table-striped table-bordered" style="border:solid 1px #ccc;">
            <thead>
                <tr>
                    <th class="text-center">SI.No.</th>
                    <th class="text-center">Items Name</th>
                    <th class="text-center">QTY</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Rate</th>
                    <th class="text-center"> Amount</th>

                    <td>Action</td>
                </tr>
            </thead>
            <tbody id="TextBoxContainer">
                <?php
                $i = 1;
                $tqty = 0;
                $Totalamount = 0;
                foreach ($cart_dtls as $cart) {
                    $tqty += $cart->qty;
                ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $cart->item_name; ?></td>
                        <td>
                            <input type="hidden" class="uk-input uk-form-small cart-data" name="cart_id" style="width:50px" value="<?= $cart->cart_id; ?>" />
                            <input type="number" class="uk-input uk-form-small cart-data" name="qty" style="width:50px" value="<?= $cart->qty; ?>" />
                        </td>
                        <td><?= $cart->unit_name; ?></td>
                        <td><input type="text" class="uk-input uk-form-small cart-data" name="rate" style="width:200px" value="<?= $cart->rate; ?>" /></td>
                        <td><?php echo $cart->rate * $cart->qty; ?></td>

                        <td><a href="javascript:void(0);" onClick="deleteRecord('<?= $cart->cart_id; ?>');" uk-icon="icon: trash" class="uk-text-danger"></a></td>
                    </tr>
                <?php

                } ?>

            </tbody>
        </table>

        <script>
            $(document).ready(function() {
                // Listen for changes in any input field with the class 'cart-data'
                $('#TextBoxContainer').on('change', 'input.cart-data', function() {
                    // Get the closest row to the changed input field
                    var currentRow = $(this).closest('tr');

                    // Gather all input values within the current row
                    var formData = {
                        cart_id: currentRow.find('.cart-data[name="cart_id"]').val(),
                        qty: currentRow.find('.cart-data[name="qty"]').val(),
                        rate: currentRow.find('.cart-data[name="rate"]').val(),

                    };

                    // Send the data to the server using AJAX
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo base_url(); ?>/Admin/frm_updatestockTransfer',
                        data: formData,
                        success: function(response) {
                            // Update the HTML with the response
                            $('#responseContainer').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                });
            });
        </script>
    <?php
    }
    function frm_updatestockTransfer()
    {
        $user_id = $this->session->get('user_id');
        $cart_id = $this->request->getVar('cart_id');
        $qty = $this->request->getVar('qty');
        $rate = $this->request->getVar('rate');


        $data = [
            'qty' => $qty,
            'rate' => $rate,

        ];


        $this->db->table('cart')->update($data, array('cart_id' => $cart_id,));
        $cart_dtls = $this->AdminModel->stockTransferdetails($user_id);
    ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <table class="table table-responsive table-striped table-bordered" style="border:solid 1px #ccc;">
            <thead>
                <tr>
                    <th class="text-center">SI.No.</th>
                    <th class="text-center">Items Name</th>
                    <th class="text-center">QTY</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Rate</th>
                    <th class="text-center"> Amount</th>

                    <td>Action</td>
                </tr>
            </thead>
            <tbody id="TextBoxContainer">
                <?php
                $i = 1;
                $tqty = 0;
                $Totalamount = 0;
                foreach ($cart_dtls as $cart) {
                    $tqty += $cart->qty;
                ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $cart->item_name; ?></td>
                        <td>
                            <input type="hidden" class="uk-input uk-form-small cart-data" name="cart_id" style="width:50px" value="<?= $cart->cart_id; ?>" />
                            <input type="number" class="uk-input uk-form-small cart-data" name="qty" style="width:50px" value="<?= $cart->qty; ?>" />
                        </td>
                        <td><?= $cart->unit_name; ?></td>
                        <td><input type="text" class="uk-input uk-form-small cart-data" name="rate" style="width:200px" value="<?= $cart->rate; ?>" /></td>
                        <td><?php echo $cart->rate * $cart->qty; ?></td>

                        <td><a href="javascript:void(0);" onClick="deleteRecord('<?= $cart->cart_id; ?>');" uk-icon="icon: trash" class="uk-text-danger"></a></td>
                    </tr>
                <?php

                } ?>

            </tbody>
        </table>


        <script>
            $(document).ready(function() {
                // Listen for changes in any input field with the class 'cart-data'
                $('#TextBoxContainer').on('change', 'input.cart-data', function() {
                    // Get the closest row to the changed input field
                    var currentRow = $(this).closest('tr');

                    // Gather all input values within the current row
                    var formData = {
                        cart_id: currentRow.find('.cart-data[name="cart_id"]').val(),
                        qty: currentRow.find('.cart-data[name="qty"]').val(),
                        rate: currentRow.find('.cart-data[name="rate"]').val(),

                    };

                    // Send the data to the server using AJAX
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo base_url(); ?>/Admin/frm_updatestockTransfer',
                        data: formData,
                        success: function(response) {
                            // Update the HTML with the response
                            $('#responseContainer').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                });
            });
        </script>
    <?php
    }
    public function InsertstockTransfer()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $cart_dtls = $this->AdminModel->stockTransferdetails($user_id);
            $timestamp = date('YmdHis');
            $random_from = rand(1000, 9999);
            $random_to = rand(1000, 9999);

            $invoice_no_from = 'stock-trans-' . $timestamp . '-' . $random_from;
            $invoice_no_to = 'stock-trans-' . $timestamp . '-' . $random_to;
            // echo "<pre>";
            // print_r($cart_dtls);exit;
            $sum = 0;
            foreach ($cart_dtls as $cartdtls) {
            }
            $stockCode = 1;
            $stk_dtls = $this->db->query("SELECT * FROM stock")->getResult();
            if (!empty($stk_dtls)) {
                foreach ($stk_dtls as $stcode) {
                };
                $stockCode = $stcode->stock_code + 1;
            }
            foreach ($cart_dtls as $cartdtls) {
                $tcart = $cartdtls->rate * $cartdtls->qty;
                //   echo"<pre>";
                // print_r($cartdtls);exit;
                $from_qty = 0;
                $from_qty -= $cartdtls->qty;
                $sum += $tcart;

                $data = [
                    'stock_code' => $stockCode,
                    'sproduct_id' => $cartdtls->product_id,
                    'date' => $cartdtls->invoicedate,
                    'supplier_id' => $cartdtls->supplier_id,
                    'invoice_date' => $cartdtls->invoicedate,
                    'quantity' => $from_qty,
                    'available_qty' => $from_qty,
                    'rate' => $cartdtls->rate,
                    'amount' => $tcart,
                    'gst_amount' => $sum,
                    'invoice_number' => $invoice_no_from,
                    'location_id' => $cartdtls->location,
                ];
                $this->db->table('stock')->insert($data);

                $data1 = [
                    'stock_code' => $stockCode,
                    'sproduct_id' => $cartdtls->product_id,
                    'date' => $cartdtls->invoicedate,
                    'supplier_id' => $cartdtls->supplier_id,
                    'invoice_date' => $cartdtls->invoicedate,
                    'quantity' => $cartdtls->qty,
                    'available_qty' => $cartdtls->qty,
                    'rate' => $cartdtls->rate,
                    'amount' => $tcart,
                    'gst_amount' => $sum,
                    'invoice_number' => $invoice_no_to,
                    'location_id' => $cartdtls->to_location,
                ];
                $this->db->table('stock')->insert($data1);

                //print_r ($data);exit;
            }

            $this->db->table('cart')->delete(['user_id' => $user_id]);
            return redirect()->to('Admin/stock_transfer');
        } else {
            return redirect()->to('Admin/');
        }
    }
    function Purchaseentry()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['product'] = $this->AdminModel->items();
            $data['cart_dtls'] = $this->AdminModel->purcartdetails($user_id);
            $data['vendor'] = $this->AdminModel->Get_vendor();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            // $data['item'] = $this->AdminModel->getItem();

            // 			echo '<pre>';
            // 			print_r($data);exit;


            return view('admin/purchase_voucher_new', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function getItemsDetails()
    {
        $location_id = $this->request->getPost('location_id');

        $builder = $this->db->table('items i');
        $builder->select("
            i.id,
            i.item_id,
            i.item_name,
            i.amount,
            (
                COALESCE((
                    SELECT SUM(s.quantity)
                    FROM stock s
                    WHERE s.sproduct_id = i.id
                    AND s.location_id = {$location_id}
                ),0)
                -
                COALESCE((
                    SELECT SUM(im.qty)
                    FROM inhouse_maintenance im
                    WHERE im.item = i.id
                    AND im.location = {$location_id}
                ),0)
            ) AS available_qty
        ");

        $items = $builder->get()->getResult();
        return $this->response->setJSON($items);
    }
    public function getItemsDetails1()
    {
        $location_id = $this->request->getPost('location_id');

        $builder = $this->db->table('items i');
        $builder->select("
            i.id,
            i.item_id,
            i.item_name,
            i.amount,
            (
                COALESCE((
                    SELECT SUM(s.quantity)
                    FROM stock s
                    WHERE s.sproduct_id = i.id
                    AND s.location_id = {$location_id}
                ), 0)
                -
                COALESCE((
                    SELECT SUM(im.qty)
                    FROM inhouse_maintenance im
                    WHERE im.item = i.id
                    AND im.location = {$location_id}
                ), 0)
            ) AS available_qty
        ");

        // Filter out items where available_qty is 0
        $builder->having('available_qty >', 0);

        $items = $builder->get()->getResult();
        return $this->response->setJSON($items);
    }
    public function deletepurchasecart(){
        if ($this->session->get('user_id')) {
            $cart_id = $this->request->getPost('cartid');
            $this->db->table('cart')->delete(array('cart_id' => $cart_id));

            return redirect()->to('Admin/Purchaseentry');
        } else {
            return redirect()->to('Admin/');
        }
    }
    function deletestock_transfer()
    {
        if ($this->session->get('user_id')) {
            $cart_id = $this->request->getVar('cartid');
            $this->db->table('cart')->delete(array('cart_id' => $cart_id));

            return redirect()->to('Admin/stock_transfer');
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function frm_addtocartpurchase()
    {
        $user_id     = $this->session->get('user_id');
        $supplier_id = $this->request->getPost('supplierId');
        $product_id  = $this->request->getPost('productId');
        $invoicedate = $this->request->getPost('invoicedate');
        $invoiceno   = $this->request->getPost('invoiceno');
        $location    = $this->request->getPost('location_id');

        if (empty($user_id) || empty($supplier_id) || empty($product_id) || empty($invoicedate)) {
            return $this->response->setStatusCode(400)->setBody('Missing required fields.');
        }

        $CountProduct = $this->db->query("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND cart_type IN ('1','2')", [$user_id, $product_id])->getResult();
        $ProductDTLS  = $this->db->query("SELECT * FROM items WHERE id = ?", [$product_id])->getResult();

        if (count($CountProduct) == 0 && !empty($ProductDTLS)) {
            $prd = $ProductDTLS[0];

            $data = [
                'user_id'       => $user_id,
                'qty'          => 1,
                'sup-cust_id'  => $supplier_id,  // Correct key
                'product_id'   => $product_id,
                'invoicedate'  => $invoicedate,
                'invoiceno'    => $invoiceno,
                'location'     => $location,
                'rate'        => $prd->amount,
                'cart_type'   => 1,
            ];

            $this->db->table('cart')->insert($data);

            $cart_dtls = $this->AdminModel->purcartdetails($user_id);

            $html = '';
            $i = 1;
            foreach ($cart_dtls as $cart) {
                $html .= '<tr>';
                $html .= '<td>' . $i++ . '</td>';
                $html .= '<td>' . htmlspecialchars($cart->item_name) . '</td>';
                $html .= '<td><input type="hidden" name="cart_id" value="' . $cart->cart_id . '" class="uk-input uk-form-small cart-data"/>';
                $html .= '<input type="number" name="qty" value="' . $cart->qty . '" class="uk-input uk-form-small cart-data" min="0.01" step="any"/></td>';
                $html .= '<td>' . htmlspecialchars($cart->unit_name) . '</td>';
                $html .= '<td><input type="text" name="rate" value="' . $cart->rate . '" class="uk-input uk-form-small cart-data"/></td>';
                $html .= '<td>' . ($cart->rate * $cart->qty) . '</td>';
                $html .= '<td><a href="javascript:void(0);" onClick="deleteRecord(\'' . $cart->cart_id . '\');" uk-icon="icon: trash" class="uk-text-danger"></a></td>';
                $html .= '</tr>';
            }

            return $this->response->setBody($html);
        } else {
            return $this->response->setBody('<tr><td colspan="7">Item already added in cart.</td></tr>');
        }
    }
    function frm_updatepurchasecart()
    {
        $user_id = $this->session->get('user_id');
        $cart_id = $this->request->getVar('cart_id');
        $qty = $this->request->getVar('qty');
        $rate = $this->request->getVar('rate');


        $data = [
            'qty' => $qty,
            'rate' => $rate,

        ];


        $this->db->table('cart')->update($data, array('cart_id' => $cart_id,));
        $cart_dtls = $this->AdminModel->purcartdetails($user_id);
    ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <table class="table table-responsive table-striped table-bordered" style="border:solid 1px #ccc;">
            <thead>
                <tr>
                    <th class="text-center">SI.No.</th>
                    <th class="text-center">Items Name</th>
                    <th class="text-center">QTY</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Rate</th>
                    <th class="text-center"> Amount</th>

                    <td>Action</td>
                </tr>
            </thead>
            <tbody id="TextBoxContainer">
                <?php
                $i = 1;
                $tqty = 0;
                $Totalamount = 0;
                foreach ($cart_dtls as $cart) {
                    $tqty += $cart->qty;
                ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $cart->item_name; ?></td>
                        <td>
                            <input type="hidden" class="uk-input uk-form-small cart-data" name="cart_id" style="width:50px" value="<?= $cart->cart_id; ?>" />
                            <input type="number" class="uk-input uk-form-small cart-data" name="qty" style="width:50px" value="<?= $cart->qty; ?>" />
                        </td>
                        <td><?= $cart->unit_name; ?></td>
                        <td><input type="text" class="uk-input uk-form-small cart-data" name="rate" style="width:200px" value="<?= $cart->rate; ?>" /></td>
                        <td><?php echo $cart->rate * $cart->qty; ?></td>

                        <td><a href="javascript:void(0);" onClick="deleteRecord('<?= $cart->cart_id; ?>');" uk-icon="icon: trash" class="uk-text-danger"></a></td>
                    </tr>
                <?php

                } ?>

            </tbody>
        </table>


        <script>
            $(document).ready(function() {
                // Listen for changes in any input field with the class 'cart-data'
                $('#TextBoxContainer').on('change', 'input.cart-data', function() {
                    // Get the closest row to the changed input field
                    var currentRow = $(this).closest('tr');

                    // Gather all input values within the current row
                    var formData = {
                        cart_id: currentRow.find('.cart-data[name="cart_id"]').val(),
                        qty: currentRow.find('.cart-data[name="qty"]').val(),
                        rate: currentRow.find('.cart-data[name="rate"]').val(),

                    };

                    // Send the data to the server using AJAX
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo base_url(); ?>/Admin/frm_updatepurchasecart',
                        data: formData,
                        success: function(response) {
                            // Update the HTML with the response
                            $('#responseContainer').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                });
            });
        </script>
        <?php
    }
    public function Inserpurchasetstock()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $cart_dtls = $this->AdminModel->purcartdetails($user_id);
            $sum = 0;

            // Calculate the total sum first
            foreach ($cart_dtls as $cartdtls) {
            }
            $stockCode = 1;
            $stk_dtls = $this->db->query("SELECT * FROM stock   ")->getResult();
            if (!empty($stk_dtls)) {
                foreach ($stk_dtls as $stcode) {
                };
                $stockCode = $stcode->stock_code + 1;
            }








            foreach ($cart_dtls as $cartdtls) {
                $tcart = $cartdtls->rate * $cartdtls->qty;
                //   echo"<pre>";
                // print_r($cartdtls);exit;

                $sum += $tcart;

                $data = [
                    'stock_code' => $stockCode,
                    'sproduct_id' => $cartdtls->product_id,
                    'date' => $cartdtls->invoicedate,
                    'supplier_id' => $cartdtls->supplier_id,
                    'invoice_date' => $cartdtls->invoicedate,
                    'quantity' => $cartdtls->qty,
                    'available_qty' => $cartdtls->qty,
                    'rate' => $cartdtls->rate,
                    'amount' => $tcart,
                    'gst_amount' => $sum,

                    'invoice_number' => $cartdtls->invoiceno,
                    'location_id' => $cartdtls->location,
                ];

                //print_r ($data);exit;
                $this->db->table('stock')->insert($data);
            }

            $this->db->table('cart')->delete(['user_id' => $user_id]);
            return redirect()->to('Admin/Purchaseentry');
        } else {
            return redirect()->to('Admin/');
        }
    }
    function delete_stock()
    {
        $stock_code = $this->request->getPost('user_id');
        $this->db->table('stock')->delete(array('stock_code' => $stock_code));
        return redirect()->to('Admin/Purchase_Voucher');
    }
    function getVehicleDetails()
    {
        $vehicle_id = $this->request->getPost('vehicle_id');
        $asign = $this->db->query("SELECT * FROM driver_assignment where vehicle_no='$vehicle_id'")->getResult();
        foreach ($asign as $dasign) {
        }
        if (!empty($dasign)) {
            $driver = $this->db->query("SELECT * FROM staff where id='$dasign->driver'")->getResult();
            foreach ($driver as $dr) {
        ?>
                <label>Select Driver</label>
                <input type="text" name="driver" value="<?= $dr->name; ?>" class="form-control" readonly />

            <?php
            }
        } else {
            ?>
            <lable>Select Driver</lable>
            <input type="text" name="driver" class="form-control" readonly />
        <?php
        }
    }

    function inhouse_maintenance()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicles'] = $this->AdminModel->Getvehicle();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['items'] = $this->AdminModel->itemdtls();
            return view('admin/add_inhouse_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function get_items_by_location()
    {
        $locationId = $this->request->getPost('location_id');
        $items = $this->AdminModel->getItemsByLocation($locationId);

        $options = '<option value="">Select items</option>';

        foreach ($items as $item) {
            $options .= '<option data-unitsname = "'.$item->unit_name.'" data-unitprice="' . $item->rate . '" data-available="' . $item->available_qty . '" value="' . $item->sproduct_id . '">[' . $item->item_id . '] ' . $item->item_name . '</option>';
        }

        return $this->response->setBody($options);
    }

    
    public function add_inhouse()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $order_id = $this->request->getVar('orderid_id');
            $from_date = $this->request->getVar('from_date') ?? date('Y-m-01'); // Default to the first day of the current month
            $to_date = $this->request->getVar('to_date') ?? date('Y-m-d'); // Default to the current date
            $location_id = $this->request->getVar('location');
            $data['selected_location_id'] = $location_id; // send selected location
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['inhousedtls'] = $this->AdminModel->inhouse_dtls($from_date, $to_date, $location_id); // Pass the dates

            $data['date'] = [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];
            // echo "<pre>";
            // print_r( $data['inhousedtls']);exit;

            return view('admin/inhouse_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function downloadInhouseExcel()
    {
        // Get the filter parameters from the URL
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');
        $location_id = $this->request->getVar('location'); // GET LOCATION

        // Validate that both dates are provided
        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        // Fetch data with location filter
        $data = $this->AdminModel->inhouse_dtls($from_date, $to_date, $location_id);

        // Create a new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header row
        $headers = [
            'ID',
            'Vehicle No',
            'Driver Name',
            'Item Name',
            'Item Id',
            'Date',
            'Time',
            'Remark',
            'Check By',
            'Location Name',
            'Quantity'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Add data row-by-row
        $row = 2;
        $i = 1;

        foreach ($data as $entry) {
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $entry->vehicle_no);
            $sheet->setCellValue('C' . $row, $entry->driver_name);
            $sheet->setCellValue('D' . $row, $entry->item_name);
            $sheet->setCellValue('E' . $row, $entry->item_id);
            $sheet->setCellValue('F' . $row, date('d-m-Y', strtotime($entry->date)));
            $sheet->setCellValue('G' . $row, $entry->time);
            $sheet->setCellValue('H' . $row, $entry->invoiceno);
            $sheet->setCellValue('I' . $row, $entry->check_by);
            $sheet->setCellValue('J' . $row, $entry->location_name);
            $sheet->setCellValue('K' . $row, $entry->qty);
            $row++;
        }

        // Filename
        $filename = 'inhouse_maintenance_' . date('YmdHis') . '.xlsx';

        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    function delete_inhouse()
    {

        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('inhouse_maintenance')->delete(array('order_id' => $segment));
            return redirect()->to('Admin/add_inhouse');
        } else {
            return redirect()->to('admin/');
        }
    }


    public function edit_inhouse()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicles'] = $this->AdminModel->Getvehicle();
            $data['location'] = $this->db->table('location')->get()->getResult();
            $data['items'] = $this->AdminModel->itemdtls();
            $segment = $this->request->getUri()->getSegment(3);
            $data['orderdtls'] = $this->AdminModel->inhouse_orderdtls($segment);

            // Prepare filtered items by location
            $data['location_items_map'] = [];
            foreach ($data['orderdtls'] as $orddtlsRow) {
                $itemsForLocation = $this->db->table('stock s')
                    ->select('s.sproduct_id as id, i.item_name, s.rate as amount')
                    ->join('items i', 'i.id = s.sproduct_id', 'left')
                    ->where('s.location_id', $orddtlsRow->location)
                    ->groupBy('s.sproduct_id')
                    ->get()
                    ->getResult();

                $data['location_items_map'][$orddtlsRow->order_id] = $itemsForLocation;

                // Add currentItem for fallback if not found in itemsForLocation
                $data['currentItem'][$orddtlsRow->order_id] = $this->AdminModel->getItemById($orddtlsRow->item);
            }

            return view('admin/inhouseedit_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }


    public function getItemsByLocationInEdit()
    {
        $location_id = $this->request->getPost('location_id');
    
        // Connect to database
        $db = \Config\Database::connect();
    
        $items = $db->table('stock s')
            ->select('
                s.sproduct_id AS id,
                i.item_name,
                s.rate AS amount,
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
            ->join('items i', 'i.id = s.sproduct_id', 'left')
            ->join('units u', 'u.unit_id = i.unit_id', 'left')
            ->where('s.location_id', $location_id)
            ->groupBy('s.sproduct_id, i.item_name, u.unit_name, i.item_id, s.rate')
            ->get()
            ->getResult();
    
        return $this->response->setJSON($items);
    }


    public function insert_inhouse()
    {
        // Get the input values from the form
        $vehicle = $this->request->getPost('vehicle');
        $driver = $this->request->getPost('driver');
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');
        $invoiceno = $this->request->getPost('invoiceno');
        $location = $this->request->getPost('location');
        $check_by = $this->request->getPost('check_by');
        $itemUseAs = $this->request->getPost('itemUseAs');
        $items = $this->request->getPost('items');
        $qty = $this->request->getPost('qty');
        $price = $this->request->getPost('price');

        $order_id = 'ORD-' . strtoupper(uniqid());


        if ($items) {
            // Insert the items into the related table

            foreach ($items as $key => $item) {
                $itemsData = [
                    'order_id' => $order_id,
                    'item' => $item,
                    'qty' => $qty[$key],
                    'price' => $price[$key],
                    'vehicle' => $vehicle,
                    'date' => $date,
                    'time' => $time,
                    'invoiceno' => $invoiceno,
                    'driver_name' => $driver,
                    'location' => $location,
                    'itemUseAs' => $itemUseAs[$key],
                    'check_by' => $check_by,
                ];
                $this->db->table('inhouse_maintenance')->insert($itemsData);
            }
            return redirect()->to('Admin/inhouse_maintenance');
        } else {
            // Set error message and redirect
            session()->setFlashdata('error', 'Failed to add in-house maintenance record.');
            return redirect()->back()->withInput();
        }
    }


    public function update_inhouse()
    {
        // Get the input values from the form

        $oorder_id = $this->request->getPost('oorder_id');
        $this->db->table('inhouse_maintenance')->delete(array('order_id' => $oorder_id));

        $vehicle = $this->request->getPost('vehicle');
        $driver = $this->request->getPost('driver');
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');
        $invoiceno = $this->request->getPost('invoiceno');
        $itemUseAs = $this->request->getPost('itemUseAs');
        $location = $this->request->getPost('location');
        $check_by = $this->request->getPost('check_by');
        $items = $this->request->getPost('items[]');
        $qty = $this->request->getPost('qty[]');
        $price = $this->request->getPost('price[]');

        $order_id = 'ORD-' . strtoupper(uniqid());


        if ($items) {
            // Insert the items into the related table

            foreach ($items as $key => $item) {
                if ($qty[$key] != '') {
                    $itemsData = [
                        'order_id' => $order_id,
                        'item' => $item,
                        'qty' => $qty[$key],
                        'price' => $price[$key],
                        'vehicle' => $vehicle,
                        'date' => $date,
                        'time' => $time,
                        'invoiceno' => $invoiceno,
                        'driver_name' => $driver,
                        'location' => $location,
                        'itemUseAs' => $itemUseAs[$key],
                        'check_by' => $check_by,
                    ];
                    $this->db->table('inhouse_maintenance')->insert($itemsData);
                }
            }
            return redirect()->to('Admin/add_inhouse');
        } else {
            // Set error message and redirect
            session()->setFlashdata('error', 'Failed to add in-house maintenance record.');
            return redirect()->back()->withInput();
        }
    }


    function outside_mentainance()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            $to_date = $this->request->getVar('to_date');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicle'] = $this->AdminModel->Getvehicle();
            $data['vendor'] = $this->AdminModel->Get_vendor();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['outside'] = $this->AdminModel->out_side($from_date, $to_date);
            $data['date'] = [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];


            return view('admin/outside_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    public function export_outside_maintenance_excel()
    {
        // Get the date range from the request
        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01'); // Default to the first day of the current month
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-d'); // Default to the current date

        // Load the PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Fetch the data from the database with date filtering
        $data = $this->AdminModel->out_side($from_date, $to_date);

        // Set the header row
        $sheet->setCellValue('A1', 'Sl no');
        $sheet->setCellValue('B1', 'Vehicle no');
        $sheet->setCellValue('C1', 'Bill No');
        $sheet->setCellValue('D1', 'Amount');
        $sheet->setCellValue('E1', 'Vendor Name');
        $sheet->setCellValue('F1', 'Location');
        $sheet->setCellValue('G1', 'Date');
        $sheet->setCellValue('H1', 'Remark');
        // Exclude the 'File' column from the header

        // Populate the sheet with data
        $row = 2;
        $sl_no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $sl_no++);
            $sheet->setCellValue('B' . $row, $item->vehicle_no);
            $sheet->setCellValue('C' . $row, $item->bill_no);
            $sheet->setCellValue('D' . $row, $item->amount);
            $sheet->setCellValue('E' . $row, $item->name); // Vendor Name
            $sheet->setCellValue('F' . $row, $item->location_name);
            $sheet->setCellValue('G' . $row, $item->date);
            $sheet->setCellValue('H' . $row, $item->remark);
            // Exclude the 'File' column from the data rows
            $row++;
        }

        // Set filename and headers
        $filename = "outside_maintenance_details_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Save the spreadsheet to output
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }



    public function insert_outside()
    {
        // Check if user is logged in
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        // Retrieve form data
        $data = [
            'vehicle_id' => $this->request->getPost('vehicle_id'),
            'bill_no' => $this->request->getPost('bill_no'),
            'amount' => $this->request->getPost('amount'),
            'vendor_id' => $this->request->getPost('vendor_id'),
            'location_id' => $this->request->getPost('location_id'),
            'date' => $this->request->getPost('date'),
            'remark' => $this->request->getPost('remark'),
            'upload_file' => $this->uploadFile('upload_file')
        ];

        // Insert data into the statutory table
        $this->db->table('outside_maintenance')->insert($data);

        $user_id = $this->session->get('user_id');
        $menu = $this->request->getUri()->getSegment(2);
        $this->logActivity($user_id, 'create', 'outside_maintenance', $this->db->insertID(), ['data' => $data], $menu);


        // Redirect to a specific route after insertion
        return redirect()->to('admin/outside_mentainance');
    }

    // function delete_outside()
    // {

    //     if ($this->session->get('user_id')) {
    //         $segment = $this->request->getUri()->getSegment(3);
    //         $this->db->table('outside_maintenance')->delete(array('id' => $segment));
    //         return redirect()->to('Admin/outside_mentainance');
    //     } else {
    //         return redirect()->to('admin/');
    //     }
    // }

    public function delete_outside()
    {
        try {
            if ($this->session->get('user_id')) {
                // Get the current user ID
                $user_id = $this->session->get('user_id');
                $outside_id = $this->request->getUri()->getSegment(3); // Get the ID from the URL segment

                if (empty($outside_id)) {
                    return redirect()->to('Admin/outside_mentainance')->with('error', 'Outside maintenance ID is required.');
                }

                // Check if the record exists
                $record = $this->db->table('outside_maintenance')->where('id', $outside_id)->get()->getRow();
                if (!$record) {
                    return redirect()->to('Admin/outside_mentainance')->with('error', 'No outside maintenance record found for the given ID.');
                }

                // Soft delete the record
                $data = [
                    'deleted_by' => $user_id,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('outside_maintenance')->where('id', $outside_id)->update($data);

                // Log the deletion activity in the activity_logs table
                $activity_log = [
                    'user_id' => $user_id, // User who performed the action
                    'menu' => 'delete_outside', // Menu/action name
                    'action' => 'delete', // Action type
                    'model' => 'outside_maintenance', // Affected model/table
                    'model_id' => $outside_id, // ID of the outside maintenance record deleted
                    'changes' => json_encode($data), // Details of the changes
                    'created_at' => date('Y-m-d H:i:s'), // Timestamp
                ];
                $this->db->table('activity_logs')->insert($activity_log);

                // Redirect with a success message
                return redirect()->to('Admin/outside_mentainance')->with('success', 'Outside maintenance record deleted successfully.');
            } else {
                // Redirect to login if the user is not logged in
                return redirect()->to('admin/');
            }
        } catch (\Exception $e) {
            // Handle any errors
            return redirect()->to('Admin/outside_mentainance')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function delete_multiple_staf_advance()
    {
        if ($this->session->get('user_id')) {
            $ids = $this->request->getPost('select_del[]'); // Assuming ids are posted as an array

            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $this->db->table('staff_advance')->delete(['id' => $id]);
                }

                return redirect()->to('Admin/staf_advance')->with('success', 'Selected despatches deleted successfully.');
            } else {
                return redirect()->to('Admin/staf_advance')->with('error', 'Please select despatches to delete.');
            }
        } else {
            return redirect()->to('admin/');
        }
    }

    function staf_advance()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            $to_date = $this->request->getVar('to_date');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['allstaf'] = $this->AdminModel->Getallstaf();
            $data['staffadvance'] = $this->AdminModel->staffadvance($from_date, $to_date);
            $data['date'] = [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];
            // echo "<pre>";
            // print_r($data['staffadvance']);exit;	
            return view('admin/staf_advance_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    public function download_staffadvance_excel()
    {
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        $staffadvance = $this->AdminModel->staffadvance($from_date, $to_date);

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Employ Name',
            'Code',
            'Date',
            'Bank/Cash',
            'Amount',
            'Location'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($staffadvance as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->name);
            $sheet->setCellValue('C' . $row, $record->staff_code);
            $sheet->setCellValue('D' . $row, date('d-m-Y', strtotime($record->adv_date)));
            $sheet->setCellValue('E' . $row, $record->bank_cash);
            $sheet->setCellValue('F' . $row, $record->amount);
            $sheet->setCellValue('G' . $row, $record->location_name);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'staffadvance_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }



    function insert_staf_advance()
    {
        $data = [
            'staff_id' => $this->request->getPost('staff_id'),
            'adv_date' => $this->request->getPost('date'),
            'bank_cash' => $this->request->getPost('bank_cash'),
            'amount' => $this->request->getPost('amount'),
            'location_id' => $this->request->getPost('location_id'),
            'upload_file' => $this->uploadFile('upload_file') // Handle file upload
        ];
            
        $this->db->table('staff_advance')->insert($data);

        $user_id = $this->session->get('user_id');
        $menu = $this->request->getUri()->getSegment(2);
        $this->logActivity($user_id, 'create', 'staff_advance', $this->db->insertID(), ['data' => $data], $menu); 

        return redirect()->to('Admin/staf_advance');
    }


    public function upload_staf_advance()
    {
        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Determine the reader type based on file extension
            $reader = ($fileExtension === 'csv') ? new \PhpOffice\PhpSpreadsheet\Reader\Csv() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            try {
                // Load the spreadsheet
                $spreadsheet = $reader->load($filePath);
                $sheetData = $spreadsheet->getActiveSheet()->toArray();

                // echo"<pre>";
                // print_r($sheetData);
                // exit;    

                $data = [];
                foreach ($sheetData as $index => $rowData) {
                    $staff_dtl = null;
                    $loc_dtl = null;
                    $date = '';
                    $is_valid = true;
                    $staff_dtl = $this->db->query("SELECT * FROM staff WHERE staff_code = ?", [$rowData[1]])->getFirstRow();
                    if (!$staff_dtl) {
                        $is_valid = false;
                    }
                    $loc_dtl = $this->db->query("SELECT * FROM location WHERE location_name = ?", [$rowData[5]])->getFirstRow();
                    if (!$loc_dtl) {
                        $is_valid = false;
                    }
                    $formattedDate = null;
                    $des_date = $rowData[2] ?? '';
                    if (!empty($des_date)) {
                        if (is_numeric($des_date)) {
                            // Excel numeric date (not in your case)
                            $excelDateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($des_date);
                            $formattedDate = $excelDateTime->format('Y-m-d');
                        } else {
                            // Excel date in dd/mm/yyyy format
                            $dateObject = DateTime::createFromFormat('d/m/Y', $des_date);

                            if ($dateObject) {
                                $formattedDate = $dateObject->format('Y-m-d');
                            } 
                            else {
                                $formattedDate = date('Y-m-d'); // fallback
                            }
                        }
                    } else {
                        $formattedDate = date('Y-m-d');
                    }
                    $data = [
                        'adv_date' => $formattedDate,
                        'staff_name' => $rowData[0] ?? '',
                        'bank_cash' => $rowData[3] ?? '',
                        'amount' => $rowData[4] ?? '',
                        'location_id' => $loc_dtl->location_id ?? null,
                        'upload_file' => $upload_file ?? '', // Add upload_file logic if needed
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    // echo"<pre>";
                    // print_r($data);
                    // exit; 
                    // Insert into the appropriate table based on validity
                    if ($is_valid) {
                        $data['staff_id'] = $staff_dtl->id; // Include staff_id for valid records
                        unset($data['staff_name']); // Remove staff_name as it's not required in valid table
                        $this->db->table('staff_advance')->insert($data);
                    } else {
                        $this->db->table('staff_advance1')->insert($data);
                    }
                }
                return redirect()->to(base_url('Admin/staf_advance'))->with('success', 'Data imported successfully.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Failed to upload file.');
    }


    function editstaf_advance()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $segment = $this->request->getUri()->getSegment(3);

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['allstaf'] = $this->AdminModel->Getallstaf();

            $data['single_stafadv'] = $this->db->query("SELECT * FROM staff_advance  where staff_advance.id='$segment' ")->getResult();

            // print_r($data['single_stafadv']); exit;

            return view('admin/edit_staf_advance_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    function update_StaffAdvance()
    {
        $said = $this->request->getPost('adv_id');

        // Prepare data array without the file initially
        $data = [
            'staff_id' => $this->request->getPost('staff_id'),
            'bank_cash' => $this->request->getPost('bank_cash'),
            'adv_date' => $this->request->getPost('date'),
            'amount' => $this->request->getPost('amount'),
            'location_id' => $this->request->getPost('location_id')
        ];

        // Check if there is a file uploaded
        if ($this->request->getFile('upload_file')->isValid()) {
            $data['upload_file'] = $this->uploadFile('upload_file'); // Handle file upload
        }

        $this->db->table('staff_advance')->update($data, ['id' => $said]);

        return redirect()->to('Admin/staf_advance');
    }


    function delete_StaffAdvance()
    {
        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('staff_advance')->delete(array('id' => $segment));
            return redirect()->to('Admin/staf_advance');
        } else {
            return redirect()->to('admin/');
        }
    }
    public function CashBank(){
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            // echo "<pre>";
            // print_r($data['staffadvance']);exit;	
            return view('admin/cashbank_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }


    function Driver_Assignment()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            
            
            
            $to_date = $this->request->getVar('to_date');
            if (empty($from_date)) {
                $from_date = date('Y-m-01'); // First day of current month
            }
            
            if (empty($to_date)) {
                $to_date = date('Y-m-d'); // Current date
            }
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicles'] = $this->AdminModel->Getvehicle();
            $data['drivers'] = $this->AdminModel->Getallstaf();
            $data['drivers_asignment'] = $this->AdminModel->driverasignment($from_date, $to_date);
            $data['date'] = [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];
            // echo "<pre>";
            // print_r($data['drivers']);exit;

            return view('admin/Driver_Assignment_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    function Edit_Driver_Assignment()
    {
        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $user_id = $this->session->get('user_id');

            $year = $this->request->getPost('year');
            $month = $this->request->getPost('month');


            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicles'] = $this->AdminModel->Getvehicle();
            $data['drivers'] = $this->AdminModel->Getallstaf();
            $data['drivers_asignment'] = $this->AdminModel->singledriverasignment($segment);

            // print_r($data['drivers_asignment']);exit;

            return view('admin/Edit_Driver_Assignment_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }



    // function insert_driver_asignment()
    // {
    //     // Check if user is logged in
    //     if ($this->session->get('user_id') == '') {
    //         return redirect()->to('Admin/');
    //     }

    //     // Gather data from the form
    //     $vehicle_id = $this->request->getPost('vehicle_no');
    //     $driver_id = $this->request->getPost('driver');
    //     $from_date = $this->request->getPost('from_date');
    //     $to_date = $this->request->getPost('to_date');
    //     $opening_hsd = $this->request->getPost('opening_hsd');
    //     $opening_km = $this->request->getPost('opening_km');
    //     $closing_hsd = $this->request->getPost('closing_hsd');
    //     $closing_km = $this->request->getPost('closing_km');

    //     // $countVehicle= $this->db->query("
    //     //                                     SELECT * 
    //     //                                     FROM driver_assignment 
    //     //                                     WHERE vehicle_no='$vehicle_id' 
    //     //                                     AND MONTH(from_date) = MONTH('$from_date')
    //     //                                 ")->getResult();

    //     // if (count($countVehicle)!=0){
    //     // $this->session->setFlashdata('msg', 'Vehicle Already Asigned for this month.');
    //     //  return redirect()->to('Admin/Driver_Assignment');
    //     // }


    //     //  $countdriver= $this->db->query("
    //     //                                     SELECT * 
    //     //                                     FROM driver_assignment 
    //     //                                     WHERE driver='$driver_id' 
    //     //                                     AND MONTH(from_date) = MONTH('$from_date')
    //     //                                 ")->getResult();

    //     // if (count($countdriver)!=0){
    //     // $this->session->setFlashdata('msg', 'Driver Already Asigned for this month.');
    //     //  return redirect()->to('Admin/Driver_Assignment');
    //     // }



    //     // Prepare data array for insertion
    //     $data = [
    //         'vehicle_no' => $vehicle_id,
    //         'driver' => $driver_id,
    //         'from_date' => $from_date,
    //         'to_date' => $to_date,
    //         'opening_hsd' => $opening_hsd,
    //         'opening_km' => $opening_km,
    //         'closing_hsd' => $closing_hsd,
    //         'closing_km' => $closing_km
    //     ];

    //     // Insert data into the 'driver_assignment' table
    //     $this->db->table('driver_assignment')->insert($data);

    //     // Redirect to Driver_Assignment page
    //     return redirect()->to('Admin/Driver_Assignment');
    // }
    function insert_driver_asignment()
    {
        // Check if user is logged in
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        // Gather data from the form
        $vehicle_id = $this->request->getPost('vehicle_no');
        $driver_id = $this->request->getPost('driver');
        $from_date = $this->request->getPost('from_date');
        // print_r($from_date);exit; //2024-10-14
        $to_date = $this->request->getPost('to_date');
        if (!$to_date) {
            $to_date = null;
        }
        $opening_hsd = $this->request->getPost('opening_hsd');
        $opening_km = $this->request->getPost('opening_km');
        $closing_hsd = $this->request->getPost('closing_hsd');
        $closing_km = $this->request->getPost('closing_km');

        $countVehicle = $this->db->query("
            SELECT * 
            FROM driver_assignment 
            WHERE vehicle_no='$vehicle_id' 
            AND MONTH(from_date) = MONTH('$from_date')
        ")->getResult();

        // foreach ($countVehicle as $assignment) {
        //     if (is_null($assignment->to_date)) {
        //         $this->session->setFlashdata('msg', 'Cannot assign as the vehicle already has an existing assignment with no end date.');
        //         return redirect()->to('Admin/Driver_Assignment');
        //     }
        //     if ($assignment->to_date > $from_date) {
        //         $this->session->setFlashdata('msg', 'Cannot assign as the vehicle already has an existing assignment with no end date.');
        //         return redirect()->to('Admin/Driver_Assignment');
        //     }
        // }

        $countdriver = $this->db->query("
            SELECT * 
            FROM driver_assignment 
            WHERE driver='$driver_id' 
            AND MONTH(from_date) = MONTH('$from_date')
        ")->getResult();
        // echo'<pre>';
        // print_r($countdriver);exit;
        foreach ($countdriver as $assignmentDriver) {
            // print_r($assignmentDriver->to_date);exit; //2024-10-16
            if (is_null($assignmentDriver->to_date)) {
                $this->session->setFlashdata('msg', 'Driver Already Asigned for this month.');
                return redirect()->to('Admin/Driver_Assignment');
            }
            if ($assignmentDriver->to_date > $from_date) {
                $this->session->setFlashdata('msg', 'Driver Already Asigned for this month.');
                return redirect()->to('Admin/Driver_Assignment');
            }
        }
        // print_r('Hyyy');exit;
        // Prepare data array for insertion
        $data = [
            'vehicle_no' => $vehicle_id,
            'driver' => $driver_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'opening_hsd' => $opening_hsd,
            'opening_km' => $opening_km,
            'closing_hsd' => $closing_hsd,
            'closing_km' => $closing_km
        ];
        // Insert data into the 'driver_assignment' table
        $this->db->table('driver_assignment')->insert($data);
        // Redirect to Driver_Assignment page
        return redirect()->to('Admin/Driver_Assignment');
    }


    function update_driver_asignment()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $id = $this->request->getPost('id');
        // Gather data from the form
        $data = [
            'vehicle_no' => $this->request->getPost('vehicle_no'),
            'driver' => $this->request->getPost('driver'),
            'from_date' => $this->request->getPost('from_date'),
            'opening_hsd' => $this->request->getPost('opening_hsd'),
            'opening_km' => $this->request->getPost('opening_km'),
            'to_date' => $this->request->getPost('to_date'),
            'closing_hsd' => $this->request->getPost('closing_hsd'),
            'closing_km' => $this->request->getPost('closing_km')
        ];


        $this->db->table('driver_assignment')->update($data, ['id' => $id]);

        return redirect()->to('Admin/Driver_Assignment');
    }

    function delete_driver_asignment()
    {
        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('driver_assignment')->delete(array('id' => $segment));
            return redirect()->to('Admin/Driver_Assignment');
        } else {
            return redirect()->to('admin/');
        }
    }
    public function downloadExcel()
    {
        // Get the year and month from the request
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        // Load the PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the headers for the columns
        // $sheet->setCellValue('A1', 'ID');
        // $sheet->setCellValue('B1', 'Vehicle Number');
        // $sheet->setCellValue('C1', 'Driver Name');
        // $sheet->setCellValue('D1', 'From Date');
        // $sheet->setCellValue('E1', 'Opening HSD');
        // $sheet->setCellValue('F1', 'Opening KM');
        // $sheet->setCellValue('G1', 'To Date');
        // $sheet->setCellValue('H1', 'Closing HSD');
        // $sheet->setCellValue('I1', 'Closing KM');
        
        
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Vehicle Number');
        $sheet->setCellValue('C1', 'Driver Name');
        $sheet->setCellValue('D1', 'Driver Code');
        $sheet->setCellValue('E1', 'From Date');
        $sheet->setCellValue('F1', 'Opening HSD');
        $sheet->setCellValue('G1', 'Opening KM');
        $sheet->setCellValue('H1', 'To Date');
        $sheet->setCellValue('I1', 'Closing HSD');
        $sheet->setCellValue('J1', 'Closing KM');

        // Fetch the filtered driver assignment data
        $drivers_asignment = $this->AdminModel->driverasignment($from_date, $to_date);

        // Populate the Excel sheet with data
        $row = 2; // Start from the second row
        foreach ($drivers_asignment as $record) {
            // $sheet->setCellValue('A' . $row, $record->id);
            // $sheet->setCellValue('B' . $row, $record->vehicle_number);
            // $sheet->setCellValue('C' . $row, $record->driver_name . ' (' . $record->driver_code . ')');
            // $sheet->setCellValue('D' . $row, $record->from_date);
            // $sheet->setCellValue('E' . $row, $record->opening_hsd);
            // $sheet->setCellValue('F' . $row, $record->opening_km);
            // $sheet->setCellValue('G' . $row, $record->to_date);
            // $sheet->setCellValue('H' . $row, $record->closing_hsd);
            // $sheet->setCellValue('I' . $row, $record->closing_km);
            // $sheet->setCellValue('J' . $row, $record->driver_code);
            
            
            $sheet->setCellValue('A' . $row, $record->id);
            $sheet->setCellValue('B' . $row, $record->vehicle_number);
            $sheet->setCellValue('C' . $row, $record->driver_name); // only name
            $sheet->setCellValue('D' . $row, $record->driver_code); // code is now right after name
            $sheet->setCellValue('E' . $row, $record->from_date);
            $sheet->setCellValue('F' . $row, $record->opening_hsd);
            $sheet->setCellValue('G' . $row, $record->opening_km);
            $sheet->setCellValue('H' . $row, $record->to_date);
            $sheet->setCellValue('I' . $row, $record->closing_hsd);
            $sheet->setCellValue('J' . $row, $record->closing_km);
            $row++;
        }

        // Set the headers to trigger the download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="driver_assignments.xlsx"');
        header('Cache-Control: max-age=0');

        // Write the file to output
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }


    function Regular_Checkup()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['vehicles'] = $this->AdminModel->Getvehicle();
            $data['regularcheckup'] = $this->AdminModel->regularcheckup();
            //  print_r($data['regularcheckup']);exit;

            return view('admin/Regular_Checkup_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    function delete_regularcheckup()
    {
        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('vehicle_maintenance')->delete(array('id' => $segment));
            return redirect()->to('Admin/Regular_Checkup');
        } else {
            return redirect()->to('admin/');
        }
    }


    public function submit_vehicle_maintenance()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        // Gather data from the form
        $data = [
            'date' => $this->request->getPost('date'),
            'vehicle_no' => $this->request->getPost('vehicle_no'),
            'engine_oil' => $this->request->getPost('engine_oil'),
            'gear_oil' => $this->request->getPost('gear_oil'),
            'crown_oil' => $this->request->getPost('crown_oil'),
            'coolent' => $this->request->getPost('coolent'),
            'break_oil' => $this->request->getPost('break_oil'),
            'stell_wat' => $this->request->getPost('stell_wat'),
            'clutch_oil' => $this->request->getPost('clutch_oil'),
            'hydrolic_oil' => $this->request->getPost('hydrolic_oil'),
            'greasing' => $this->request->getPost('greasing'),
            'tyre_air_check' => $this->request->getPost('tyre_air_check'),
            'brake_adjustment' => $this->request->getPost('brake_adjustment'),
            'uria' => $this->request->getPost('uria'),

            'dengine_oil' => $this->request->getPost('dengine_oil'),
            'dgear_oil' => $this->request->getPost('dgear_oil'),
            'dcrown_oil' => $this->request->getPost('dcrown_oil'),
            'dcoolent' => $this->request->getPost('dcoolent'),
            'dbreak_oil' => $this->request->getPost('dbreak_oil'),
            'dstell_wat' => $this->request->getPost('dstell_wat'),
            'dclutch_oil' => $this->request->getPost('dclutch_oil'),
            'dhydrolic_oil' => $this->request->getPost('dhydrolic_oil'),
            'dgreasing' => $this->request->getPost('dgreasing'),
            'dtyre_air_check' => $this->request->getPost('dtyre_air_check'),
            'dbrake_adjustment' => $this->request->getPost('dbrake_adjustment'),
            'duria' => $this->request->getPost('duria'),


            'remark' => $this->request->getPost('remark'),
            'checked_by' => $this->request->getPost('checked_by')
        ];

        // Insert data into the database
        $this->db->table('vehicle_maintenance')->insert($data);

        // Redirect to the specified page
        return redirect()->to('Admin/Regular_Checkup');
    }
    public function Overall_Expence()
    {
        if ($this->session->get('user_id')) {

            // Get user_id from session
            $user_id = $this->session->get('user_id');

            // Get the date range from the request, with defaults to current month
            $from_date = $this->request->getVar('from_date') ?? date('Y-m-01'); // Default to the first day of the current month
            $to_date = $this->request->getVar('to_date') ?? date('Y-m-d'); // Default to the current date

            // Get settings and user data
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

            // Modify query to filter by date range
            $data['overall'] = $this->db->query("
            SELECT oe.*, l.location_name
            FROM overall_expence oe
            JOIN location l ON l.location_id = oe.location_id
            WHERE oe.date >= '$from_date' AND oe.date <= '$to_date'
        ")->getResult();

            // Pass the date range for use in the view
            $data['date'] = [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];

            // Load the view with the data
            return view('admin/Overall_Expence_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }


    public function export_overalexpence_excel()
    {
        // Get the date range from the request
        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01'); // Default to the first day of the current month
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-d'); // Default to the current date

        // Load the PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Fetch the data from the database with date filtering
        $data['overall'] = $this->db->query("
        SELECT oe.*, l.location_name
        FROM overall_expence oe
        JOIN location l ON l.location_id = oe.location_id
        WHERE oe.date >= '$from_date' AND oe.date <= '$to_date'
    ")->getResultArray();

        // Set the header row
        $sheet->setCellValue('A1', 'Sl no');
        $sheet->setCellValue('B1', 'Amount');
        $sheet->setCellValue('C1', 'Date');
        $sheet->setCellValue('D1', 'Location');
        // $sheet->setCellValue('E1', 'File'); // Uncomment if you want to include File column
        $sheet->setCellValue('F1', 'Naration');
        $sheet->setCellValue('G1', 'Remark');

        // Populate the sheet with data
        $row = 2;
        $sl_no = 1;
        foreach ($data['overall'] as $item) {
            $sheet->setCellValue('A' . $row, $sl_no++);
            $sheet->setCellValue('B' . $row, $item['amount']);
            $sheet->setCellValue('C' . $row, $item['date']);
            $sheet->setCellValue('D' . $row, $item['location_name']);
            // $sheet->setCellValue('E' . $row, $item['upload_file']); // Uncomment if you want to include File column
            $sheet->setCellValue('F' . $row, $item['narration']);
            $sheet->setCellValue('G' . $row, $item['remark']);
            $row++;
        }

        // Set filename and headers
        $filename = "overall_expence_details_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Save the spreadsheet to output
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }


    public function insert_overalexpence()
    {
        $imgPath = $this->uploadFile('upload_file');
        $data = [
            'amount' => $this->request->getPost('amount'),
            'date' => $this->request->getPost('date'),
            'location_id' => $this->request->getPost('location_id'),
            'upload_file' => $imgPath,
            'narration' => $this->request->getPost('Naration'),
            'remark' => $this->request->getPost('remark')

        ];

        $this->db->table('overall_expence')->insert($data);

        return redirect()->to(base_url('Admin/Overall_Expence'));
    }

    function delete_overalexpence()
    {

        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('overall_expence')->delete(array('id' => $segment));
            return redirect()->to('Admin/Overall_Expence');
        } else {
            return redirect()->to('admin/');
        }
    }

    function Driver_Salary()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            return view('admin/Driver_Salary_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    function Driver_Report()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['drivers'] = $this->db->table('staff')->where('user_type', 'DRIVER')->get()->getResult();
            return view('admin/driver_report_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    function get_driver_report_details()
    {
        $driver_id = $this->request->getVar('driver');
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        if (empty($driver_id) || empty($from_date) || empty($to_date)) {
            return $this->response->setStatusCode(400)->setBody("Please select Driver and Date Range.");
        }

        $all_data = $this->AdminModel->get_driver_daily_report_data($driver_id, $from_date, $to_date);

        ?>
        <div class="table-responsive">
            <div class="mb-3 text-end">
                <button type="button" class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
            </div>
            <table class="uk-table uk-table-striped uk-table-small" style="width:100%">
                <thead>
                    <?php if (!empty($all_data)): ?>
                        <tr style="font-weight: bold; background: #f0f0f0;">
                            <td colspan="3" style="border-right: 1px solid #ddd;">Opening Diesel: <?= $all_data[0]['opening_hsd']; ?></td>
                            <td colspan="4">Closing Diesel: <?= $all_data[count($all_data)-1]['closing_hsd']; ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Date</th>
                        <th>Truck No</th>
                        <th>No of Trips</th>
                        <th>Trip Expenses Accrued</th>
                        <th>Cash Paid</th>
                        <th>Diesel Issued</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_data)) {
                        $total_trips = 0;
                        $total_expense = 0;
                        $total_cash = 0;
                        $total_diesel = 0;
                        
                        foreach ($all_data as $row) {
                            $total_trips += $row['trips'];
                            $total_expense += $row['accrued_expense'];
                            $total_cash += $row['cash_paid'];
                            $total_diesel += $row['diesel_issued'];
                            ?>
                            <tr>
                                <td><?= $row['date']; ?></td>
                                <td><?= $row['truck_no']; ?></td>
                                <td><?= $row['trips']; ?></td>
                                <td><?= number_format($row['accrued_expense'], 2); ?></td>
                                <td><?= number_format($row['cash_paid'], 2); ?></td>
                                <td><?= number_format($row['diesel_issued'], 2); ?></td>
                                <td><?= $row['remarks']; ?></td>
                            </tr>
                        <?php } ?>
                        <tr style="font-weight: bold; background: #eee;">
                            <td colspan="2">Total</td>
                            <td><?= $total_trips; ?></td>
                            <td><?= number_format($total_expense, 2); ?></td>
                            <td><?= number_format($total_cash, 2); ?></td>
                            <td><?= number_format($total_diesel, 2); ?></td>
                            <td></td>
                        </tr>
                    <?php } else { ?>
                        <tr><td colspan="7">No data found for the selected period.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function get_driver_report_excel()
    {
        $driver_id = $this->request->getVar('driver');
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        $driver = $this->db->table('staff')->where('id', $driver_id)->get()->getRow();
        $all_data = $this->AdminModel->get_driver_daily_report_data($driver_id, $from_date, $to_date);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Driver: ' . ($driver ? $driver->name : 'Unknown'));
        $sheet->setCellValue('A2', 'Period: ' . $from_date . ' to ' . $to_date);

        if (!empty($all_data)) {
            $sheet->setCellValue('A3', 'Opening Diesel:');
            $sheet->setCellValue('B3', (float) $all_data[0]['opening_hsd']);
            $sheet->setCellValue('C3', 'Closing Diesel:');
            $sheet->setCellValue('D3', (float) $all_data[count($all_data)-1]['closing_hsd']);
        }

        $headers = ['Date', 'Truck No', 'No of Trips', 'Trip Expenses Accrued', 'Cash Paid', 'Diesel Issued', 'Remarks'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $col++;
        }

        $row_idx = 6;
        $total_trips = 0;
        $total_expense = 0;
        $total_cash = 0;
        $total_diesel = 0;

        foreach ($all_data as $row) {
            $sheet->setCellValue('A' . $row_idx, $row['date']);
            $sheet->setCellValue('B' . $row_idx, $row['truck_no']);
            $sheet->setCellValue('C' . $row_idx, $row['trips']);
            $sheet->setCellValue('D' . $row_idx, (float) $row['accrued_expense']);
            $sheet->setCellValue('E' . $row_idx, (float) $row['cash_paid']);
            $sheet->setCellValue('F' . $row_idx, (float) $row['diesel_issued']);
            $sheet->setCellValue('G' . $row_idx, $row['remarks']);

            $total_trips += $row['trips'];
            $total_expense += (float)$row['accrued_expense'];
            $total_cash += (float)$row['cash_paid'];
            $total_diesel += (float)$row['diesel_issued'];

            $row_idx++;
        }

        // Add Total Row
        $sheet->setCellValue('A' . $row_idx, 'Total');
        $sheet->setCellValue('C' . $row_idx, $total_trips);
        $sheet->setCellValue('D' . $row_idx, $total_expense);
        $sheet->setCellValue('E' . $row_idx, $total_cash);
        $sheet->setCellValue('F' . $row_idx, $total_diesel);

        // Bold the Total Row
        $sheet->getStyle('A' . $row_idx . ':F' . $row_idx)->getFont()->setBold(true);

        $row_idx++;

        $filename = "Driver_Report_" . ($driver ? str_replace(' ', '_', $driver->name) : 'Report') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }



    function getdriver_salary_details()
    {

        $year = $this->request->getVar('year');
        $month = $this->request->getVar('month');

        // First day of the month  
        $first_datesam = date("Y-m-01", strtotime("$year-$month-01"));

        // Last day of the month  
        $last_datesam = date("Y-m-t", strtotime("$year-$month-01"));



        $location = $this->request->getVar('location');

        $alldriver = $this->AdminModel->driver_salary_details($year, $month, $location);
        //   echo'<pre>';
        //   print_r($alldriver);
        //   exit;
        $data['allamount'] = $this->AdminModel->showadjust_salary();



        // Validate the inputs
        if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
            return $this->response->setStatusCode(400)->setBody("Invalid input. Please provide a valid year and month.");
        }

        // Create a DateTimeImmutable object for the first day of the given month and year
        $date = new DateTimeImmutable("$year-$month-01");


        // Get the number of days in the month
        $curent_monthday = $date->format('t');





        ?>

        <div class="table-responsive">
            <table class="uk-table uk-table-striped uk-table-small" id="row_create" style="width:100%">
                <thead>
                    <tr>
                        <th>Sl no</th>
                        <th>Name</th>
                        <th>Truck No.</th>
                        <th>Location</th>
                        <th>Salary</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Working Day</th>
                        <th>Salary</th>
                        <!-- <th>Opening Balance</th> -->
                        <th>Advance</th>
                        <th>HSD Ltr</th>
                        <th>HSD Amount</th>
                        <th>Trip Expence</th>
                        <th>Adjust Amount</th>
                        <th>Net Salary</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $HSD_LTR = 0;

                    foreach ($alldriver as $staf) {



                        $first_date = !empty($staf->from_date) ? $staf->from_date : $first_datesam;
                        $last_date = !empty($staf->to_date) ? $staf->to_date : $last_datesam;

                        $hsd_details = $this->AdminModel->hsd_details($staf->id, $first_date, $last_date);


                        // echo'<pre>';
                        // print_r($staf->vehicle_no);
                        // exit;
                        $hsd_detailsDatas = $this->AdminModel->hsd_detailsData($staf->vehicle_no, $first_date, $last_date);


                        $used_hsd = 0;
                        $diesel_rate = 0;
                        if (!empty($hsd_details) && isset($hsd_details[0]->used_hsd)) {
                            $used_hsd = $hsd_details[0]->used_hsd;
                            $diesel_rate = $hsd_details[0]->diesel_rate;
                        }



                        $disel_entry = $this->AdminModel->vehicle_disel_details($staf->assignment_vehicle_no, $staf->from_date, $staf->to_date);

                        //  echo'<pre>';
                        // print_r($hsd_details);
                        // exit;


                        $disel_trip = 0;
                        $total_d_req = 0;
                        $trip_expence = 0;

                        $hsd_amount = 0;
                        if (!empty($disel_entry)) {
                            foreach ($disel_entry as $entry) {
                                $total_d_req += $entry->diesel_for_trip;
                            }
                        }



                        $HSD_LTR = $total_d_req - $used_hsd;
                        if ($HSD_LTR > 0) {
                            $HSD_LTR = 0;
                        }

                        $hsd_amount = $HSD_LTR * $diesel_rate;


                        $trip_expence = $this->AdminModel->tripexpence1($staf->assignment_vehicle_no, $staf->id, $year, $month);

                        //     echo"<pre>";
                        //   print_r($trip_expence);exit;

                        $total_month_expence = 0;

                        foreach ($trip_expence as $trex) {
                            $total_month_expence += $trex->day_trip_expense;
                        }




                        if (!empty($staf->from_date) && !empty($staf->to_date)) {
                            $getSum = 0;
                            $getSumAdjust = 0;
                            $from_date_obj = new DateTime($staf->from_date);
                            $to_date_obj = new DateTime($staf->to_date);

                            $interval = $from_date_obj->diff($to_date_obj);
                            $days_count = $interval->days + 1;


                            $id = $staf->id;
                            $getMultiRow = $this->db->table('staff_advance')
                                ->where('staff_id', $id)
                                ->where('adv_date >=', $staf->from_date)
                                ->where('adv_date <=', $staf->to_date)
                                ->get()
                                ->getResult(); // Use getResult() to fetch multiple rows

                            foreach ($getMultiRow as $row) {
                                $getSum += $row->amount; // Add each row's 'amount' to the total sum
                            }
                            // Calculate the sum of the 'amount' column

                            $getMultiRowAdjust = $this->db->table('adjust_salary')
                                ->where('driver_id', $id)
                                ->where('from_date >=', $staf->from_date)
                                ->where('from_date <=', $staf->to_date)
                                ->get()
                                ->getResult();
                            //   echo'<pre>';
                            //   print_r($getMultiRowAdjust);
                            //   exit;            
                            foreach ($getMultiRowAdjust as $row) {
                                $getSumAdjust += $row->amount; // Add each row's 'amount' to the total sum
                            }
                        } else {
                            $days_count = 0;
                        }
                        $d_salary = $staf->salary / $curent_monthday * $days_count;


                    ?>




                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $staf->name ?> ( <?= $staf->staff_code ?> )</td>
                            <td> <?= $staf->vehicle_no ?></td>
                            <td><?= $staf->location_name ?></td>
                            <td><?= $staf->salary ?></td>
                            <td><?= $staf->from_date ?></td>
                            <td><?= $staf->to_date ?></td>
                            <td><?= $days_count; ?></td>
                            <td><?= $d_salary; ?></td>
                            <!-- <td><?= $staf->opening_balance ?></td> -->
                            <!--<td><?= $staf->total_advance ?></td>-->
                            <td><?= $getSum ?></td>
                            <td>(<?= $total_d_req ?> - <?= $used_hsd ?>) === <?= $HSD_LTR; ?></td>
                            <td> <?= $hsd_amount; ?></td>
                            <td><?= $total_month_expence ?></td>
                            <!--<td><?= $staf->amount ?></td>-->
                            <td><?= $getSumAdjust ?></td>
                            <td>
                                <?php
                                $total_advance = str_replace(',', '', $staf->total_advance);
                                $tsalary = ($d_salary + $hsd_amount + $total_month_expence + $staf->amount) - (int)$total_advance;
                                echo $tsalary

                                ?>
                            </td>



                        </tr>
                    <?php } ?>

                </tbody>
                <tfoot>
                    <th>Sl no</th>
                    <th>Name</th>
                    <th>Truck No.</th>
                    <th>Location</th>
                    <th>Salary</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Working Day</th>
                    <th>Salary</th>
                    <!-- <th>Opening Balance</th> -->
                    <th>Advance</th>
                    <th>HSD Ltr</th>
                    <th>HSD Amount</th>
                    <th>Trip Expence</th>
                    <th>Adjust Salary</th>
                    <th>Net Salary</th>
                </tfoot>
            </table>


        </div>
        <script src="<?php echo base_url(); ?>/assets/admin/js/datatable/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>/assets/admin/js/datatable/datatables/datatable.custom.js"></script>
        <script src="<?php echo base_url(); ?>/assets/admin/js/datatable/datatables/datatable.custom1.js"></script>

    <?php
    }
    function getdriver_salary_details_excel()
    {
        // Get request parameters
        $year = $this->request->getVar('year');
        $month = $this->request->getVar('month');
        $location = $this->request->getVar('location');

        // First and last day of the month
        $first_datesam = date("Y-m-01", strtotime("$year-$month-01"));
        $last_datesam = date("Y-m-t", strtotime("$year-$month-01"));

        if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
            return $this->response->setStatusCode(400)->setBody("Invalid input. Please provide a valid year and month.");
        }

        $alldriver = $this->AdminModel->driver_salary_details($year, $month, $location);
        $curent_monthday = date('t', strtotime("$year-$month-01"));

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Sl No', 'Name', 'Truck No.', 'Location', 'Salary', 'From Date', 'To Date', 'Working Days', 'Daily Salary', 'Opening Balance', 'Advance', 'HSD Ltr', 'HSD Amount', 'Trip Expense', 'Adjust Salary', 'Adjust Salary Remark', 'Net Salary', 'Staff Code', 'Account No', 'IFSC Code', 'Bank Name'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Populate Data
        $row = 2;
        $sl_no = 1;

        foreach ($alldriver as $staf) {
            $days_count = 0;
            $getSum = 0;
            $d_salary = 0;
            $HSD_LTR = 0;
            $hsd_amount = 0;
            $trip_expence_sum = 0;
            $tsalary = 0;

            if (!empty($staf->from_date) && !empty($staf->to_date)) {
                $from_date_obj = new DateTime($staf->from_date);
                $to_date_obj = new DateTime($staf->to_date);
                $interval = $from_date_obj->diff($to_date_obj);
                $days_count = $interval->days + 1;

                // Fetch advance amount
                $id = $staf->id;
                $getMultiRow = $this->db->table('staff_advance')
                    ->where('staff_id', $id)
                    ->where('adv_date >=', $staf->from_date)
                    ->where('adv_date <=', $staf->to_date)
                    ->get()
                    ->getResult();

                foreach ($getMultiRow as $rowData) {
                    $getSum += (float) $rowData->amount;
                }
            }

            // Calculate daily salary
            $salary = isset($staf->salary) ? (float) $staf->salary : 0;
            $d_salary = ($salary / (float) $curent_monthday) * (float) $days_count;

            // Fetch additional details
            $first_date = !empty($staf->from_date) ? $staf->from_date : $first_datesam;
            $last_date = !empty($staf->to_date) ? $staf->to_date : $last_datesam;
            $hsd_details = $this->AdminModel->hsd_details($staf->id, $first_date, $last_date);
            $disel_entry = $this->AdminModel->vehicle_disel_details($staf->assignment_vehicle_no, $staf->from_date, $staf->to_date);
            $trip_expence = $this->AdminModel->tripexpence1($staf->assignment_vehicle_no, $staf->id, $year, $month);

            // HSD Details
            $used_hsd = 0;
            $diesel_rate = 0;
            if (!empty($hsd_details) && isset($hsd_details[0]->used_hsd)) {
                $used_hsd = (float) $hsd_details[0]->used_hsd;
                $diesel_rate = (float) $hsd_details[0]->diesel_rate;
            }

            // Diesel Calculation
            $total_d_req = 0;
            if (!empty($disel_entry)) {
                foreach ($disel_entry as $entry) {
                    $total_d_req += (float) $entry->diesel_for_trip;
                }
            }

            // Fix HSD Calculation
            $HSD_LTR = (float) $total_d_req - (float) $used_hsd;
            if ($HSD_LTR > 0) {
                $HSD_LTR = 0; // Adjusted as per the HTML logic
            }

            $hsd_amount = $HSD_LTR * $diesel_rate; // Final HSD amount calculation

            // Trip Expenses Calculation
            if (!empty($trip_expence)) {
                foreach ($trip_expence as $trex) {
                    $trip_expence_sum += (float) $trex->day_trip_expense;
                }
            }

            // Net Salary Calculation
            $tsalary = ($d_salary + $hsd_amount + $trip_expence_sum + $staf->amount) - $getSum;

            // Write to Excel
            $sheet->setCellValue('A' . $row, (string) $sl_no++);
            $sheet->setCellValue('B' . $row, (string) $staf->name);
            $sheet->setCellValue('C' . $row, (string) $staf->vehicle_no);
            $sheet->setCellValue('D' . $row, (string) $staf->location_name);
            $sheet->setCellValue('E' . $row, (float) $salary);
            $sheet->setCellValue('F' . $row, (string) $staf->from_date);
            $sheet->setCellValue('G' . $row, (string) $staf->to_date);
            $sheet->setCellValue('H' . $row, (int) $days_count);
            $sheet->setCellValue('I' . $row, (float) $d_salary);
            $sheet->setCellValue('J' . $row, (float) $staf->opening_balance);
            $sheet->setCellValue('K' . $row, (float) $getSum);
            $sheet->setCellValue('L' . $row, (float) $HSD_LTR);
            $sheet->setCellValue('M' . $row, (float) $hsd_amount);
            $sheet->setCellValue('N' . $row, (float) $trip_expence_sum);
            $sheet->setCellValue('O' . $row, (string) $staf->amount);
            $sheet->setCellValue('P' . $row, (string) $staf->remark);
            $sheet->setCellValue('Q' . $row, (float) $tsalary);
            $sheet->setCellValue('R' . $row, (string) $staf->staff_code);
            $sheet->setCellValue('S' . $row, (int) $staf->ac_no);
            $sheet->setCellValue('T' . $row, (string) $staf->ifsc);
            $sheet->setCellValue('U' . $row, (string) $staf->name_bank);

            $row++;
        }

        // Generate Excel File
        $filename = "driver_salary_details_{$year}_{$month}.xlsx";
        $response = service('response');
        $response->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return $response->setBody($excelOutput);
    }



    // Function to display the adjust salary form
    public function adjust_salary()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);

            $data['drivers'] = $this->AdminModel->Getallstaf();
            $data['vehicles'] = $this->AdminModel->Getallvehicle();

            $yearData = $this->request->getPost('year') ?: date('Y');
            $monthData = $this->request->getPost('month') ?: date('n');
            $data['drivers_assignment'] = $this->AdminModel->driverasignment($yearData, $monthData);

            $year = $this->request->getPost('year') ?: date('Y');
            $month = $this->request->getPost('month') ?: date('m');

            // Debugging: Output the received filter values
            // echo "Year: " . $year . "<br>";
            // echo "Month: " . $month . "<br>";

            // Pass filter values to the model
            $data['allamount'] = $this->AdminModel->showadjust_salary($year, $month);

            // Debugging: Check if data is retrieved
            // echo"<pre>";
            // print_r($data['allamount']);
            // exit;

            return view('admin/adjust_salary_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }


    public function getDriverAssignments()
    {
        $year = $this->request->getPost('year') ?: date('Y');
        $month = $this->request->getPost('month') ?: date('n');
        if (!$year || !$month) {
            echo json_encode(['success' => false, 'message' => 'Year and month are required.']);
            return;
        }

        $data['drivers_assignment'] = $this->AdminModel->driverasignment($year, $month);
        echo json_encode(['success' => true, 'drivers_assignment' => $data['drivers_assignment']]);
    }



    // Function to add the adjusted salary
    function add_adjust_salary()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/');
        }

        // Get driver name and location name from the database based on their IDs
        $driver_id = $this->request->getVar('driver');
        $location_id = $this->request->getVar('location');
        $month_num = $this->request->getVar('month');

        // Fetch the driver's name
        $driver = $this->AdminModel->get_driver_by_id($driver_id);
        $driver_name = $driver ? $driver->name : '';

        // Fetch the location's name
        $location = $this->AdminModel->get_location_by_id($location_id);
        $location_name = $location ? $location->location_name : '';

        // Get the month name from the month number
        $months = [
            1 => "January",
            2 => "February",
            3 => "March",
            4 => "April",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "August",
            9 => "September",
            10 => "October",
            11 => "November",
            12 => "December"
        ];
        $month_name = $months[$month_num] ?? '';

        // Prepare data for insertion
        $data = [
            'driver_id' => $driver_id,
            'driver_name' => $driver_name,
            'location' => $location_name,
            'amount' => $this->request->getVar('amount'),
            'from_date' => $this->request->getVar('from_date'),
            'remark' => $this->request->getVar('remark'),
            // 'year' => $this->request->getVar('year'),
            // 'month' => $month_name,
        ];

        // Debugging: Print the array to check values
        // echo "<pre>";
        // print_r($data);
        // exit;

        // Insert the data into the database
        $this->AdminModel->add_adjust_salary($data);

        // Redirect to the salary adjustment page
        return redirect()->to('admin/adjust_salary');
    }



    public function delete_adjust_salary($id)
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/');
        }

        // Delete the record from the database
        $this->AdminModel->delete_adjust_salary($id);

        // Redirect to the salary adjustment page
        return redirect()->to('admin/adjust_salary');
    }
    public function edit_adjust_salary()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/');
        }

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['drivers'] = $this->AdminModel->Getallstaf();

        // Fetch the salary adjustment details using the ID
        $salary_id = $this->request->getUri()->getSegment(3);
        $data['adjustment'] = $this->AdminModel->salarydata($salary_id);

        return view('admin/edit_adjust_salary_vw', $data);
    }


    public function update_adjust_salary()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/');
        }

        $id = $this->request->getPost('id');
        $driver_name = $this->request->getPost('driver_name');
        $location = $this->request->getPost('location');
        $amount = $this->request->getPost('amount');
        $month_number = $this->request->getPost('month');
        $remark = $this->request->getPost('remark');

        // Map the month number to the month name
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        $month_name = $months[$month_number];

        // Prepare the data to update
        $update_data = [
            'driver_name' => $driver_name,
            'location' => $location,
            'amount' => $amount,
            'remark' => $remark,
            'month' => $month_name // Store the month name instead of number
        ];

        $update_status = $this->AdminModel->update_salary_adjustment($id, $update_data);

        if ($update_status) {
            return redirect()->to('admin/adjust_salary/' . $id)->with('success', 'Salary adjustment updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update salary adjustment. Please try again.');
        }
    }





    function staff_Salary()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

            return view('admin/staff_Salary_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }

    function getstaff_salary_details()
    {
        $year = $this->request->getVar('year');
        $month = $this->request->getVar('month');
        $location = $this->request->getVar('location');

        $allstaf = $this->AdminModel->staff_salary_details($year, $month, $location);
        // echo"<pre>";
        // print_r ($allstaf);exit;

        // Validate the inputs
        if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
            return $this->response->setStatusCode(400)->setBody("Invalid input. Please provide a valid year and month.");
        }

        // Create a DateTimeImmutable object for the first day of the given month and year
        $date = new DateTimeImmutable("$year-$month-01");

        // Get the number of days in the month
        $curent_monthday = $date->format('t');
    ?>
        <div class="table-responsive">
            <table class="display" id="row_create" style="width:100%">
                <thead>
                    <tr>
                        <th>Sl no</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Contact No.</th>
                        <th>Date of Join</th>
                        <th>Salary</th>
                        <th>Opening Balance</th>
                        <th>Advance</th>
                        <th style="width:150px">No. of Days</th>
                        <th style="width:150px">Incentive/Penalty</th>
                        <th style="width:150px">Salary</th>
                        <th style="width:150px">Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($allstaf as $staf) {
                    ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $staf->name ?></td>
                            <td><?= $staf->location_name ?></td>
                            <td><?= $staf->tel ?></td>
                            <td><?= date('d/m/Y', strtotime($staf->doj)) ?></td>
                            <td><?= $staf->salary ?></td>
                            <td><?= $staf->opening_balance ?></td>
                            <td><?= $staf->total_advance ?></td>
                            <td>
                                <input type="text" class="form-control working_day" value="<?php if ($staf->working_day == '') {
                                                                                                echo "0";
                                                                                            } else {
                                                                                                echo $staf->working_day;
                                                                                            } ?>" name="t_working_day" data-staffid="<?= $staf->id ?>" data-year="<?= $year ?>" data-month="<?= $month ?>" data-salary="<?= $staf->salary ?>" data-opening-balance="<?= $staf->opening_balance ?>" data-total-advance="<?= $staf->total_advance ?>">
                            </td>
                            <td>
                                <input type="number" class="form-control insentive" name="insentive[]" value="<?php if ($staf->insentive == '') {
                                                                                                                    echo "0.00";
                                                                                                                } else {
                                                                                                                    echo $staf->insentive;
                                                                                                                } ?>">
                            </td>
                            <td>
                                <input type="number" class="form-control tsalary" value="<?= $staf->total_salary; ?>">
                            </td>
                            <td>
                                <input type="number" class="form-control netsalary" value="<?= $staf->net_salary; ?>">
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Sl no</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Contact No.</th>
                        <th>Date of Join</th>
                        <th>Salary</th>
                        <th>Opening Balance</th>
                        <th>Advance</th>
                        <th>No. of Days</th>
                        <th>Incentive/Penalty</th>
                        <th>Salary</th>
                        <th style="width:150px">Net Salary</th>
                    </tr>
                </tfoot>
            </table>


        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.working_day, .insentive').on('input', function() {
                    var row = $(this).closest('tr');
                    var staff_id = parseFloat(row.find('.working_day').data('staffid'));
                    var year = parseFloat(row.find('.working_day').data('year'));
                    var month = parseFloat(row.find('.working_day').data('month'));


                    var salary = parseFloat(row.find('.working_day').data('salary'));
                    var opening_balance = parseFloat(row.find('.working_day').data('opening-balance'));
                    var total_advance = row.find('.working_day').data('total-advance').toString().replace(/,/g, ''); // Remove commas
                    total_advance = parseFloat(total_advance); // Parse as float
                    var working_day = parseFloat(row.find('.working_day').val());
                    var insentive = parseFloat(row.find('.insentive').val());

                    // Calculate the total salary based on working days
                    var total_salary = (salary / <?= $curent_monthday ?>) * working_day;

                    // Calculate the net salary considering opening balance, total advance, and incentive
                    var net_salary = total_salary + opening_balance - total_advance + insentive;

                    // Set the calculated values to the respective input fields
                    row.find('.tsalary').val(total_salary.toFixed(2)); // Set the total salary with two decimal places
                    row.find('.netsalary').val(net_salary.toFixed(2)); // Set the net salary with two decimal places

                    // Prepare the data to be sent via AJAX
                    var dataToSend = {

                        working_day: working_day,
                        insentive: insentive,
                        staff_id: staff_id,
                        year: year,
                        month: month,
                        tsalary: total_salary,
                        netsalary: net_salary,
                    };


                    // Make the AJAX call
                    $.ajax({
                        url: '<?php echo base_url(); ?>/admin/insert_workingday', // Replace with the URL of your server-side script
                        type: 'POST',
                        data: dataToSend,
                        success: function(response) {
                            // Handle the response from the server if needed
                            console.log('Server response:', response);
                        },
                        error: function(xhr, status, error) {
                            // Handle any errors that occur during the AJAX request
                            console.error('AJAX error:', status, error);
                        }
                    });
                });
            });
        </script>

    <?php
    }
    public function getstaff_salary_details_excel()
    {
        $year = $this->request->getVar('year');
        $month = $this->request->getVar('month');
        $location = $this->request->getVar('location');

        // Validate the inputs
        if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
            return $this->response->setStatusCode(400)->setBody("Invalid input. Please provide a valid year and month.");
        }

        // Fetch the staff data
        $allstaf = $this->AdminModel->staff_salary_details($year, $month, $location);

        // Create a DateTimeImmutable object for the first day of the given month and year
        $date = new DateTimeImmutable("$year-$month-01");

        // Get the number of days in the month
        $curent_monthday = $date->format('t');

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header row
        $sheet->setCellValue('A1', 'Sl No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Location');
        $sheet->setCellValue('D1', 'Contact No.');
        $sheet->setCellValue('E1', 'Date of Join');
        $sheet->setCellValue('F1', 'Salary');
        $sheet->setCellValue('G1', 'Opening Balance');
        $sheet->setCellValue('H1', 'Advance');
        $sheet->setCellValue('I1', 'No. of Days');
        $sheet->setCellValue('J1', 'Incentive/Penalty');
        $sheet->setCellValue('K1', 'Salary');
        $sheet->setCellValue('L1', 'Net Salary');

        // Populate the sheet with staff data
        $row = 2;
        $sl_no = 1;
        foreach ($allstaf as $staf) {
            // Ensure variables are numeric
            $salary = isset($staf->salary) ? (float) $staf->salary : 0;
            $opening_balance = isset($staf->opening_balance) ? (float) $staf->opening_balance : 0;
            $total_advance = isset($staf->total_advance) ? (float) $staf->total_advance : 0;
            $working_day = isset($staf->working_day) ? (float) $staf->working_day : 0;
            $insentive = isset($staf->insentive) ? (float) $staf->insentive : 0;

            // Calculate total salary and net salary
            $total_salary = ($salary / $curent_monthday) * $working_day;
            $net_salary = $total_salary + $opening_balance - $total_advance + $insentive;

            $sheet->setCellValue('A' . $row, $sl_no++);
            $sheet->setCellValue('B' . $row, $staf->name);
            $sheet->setCellValue('C' . $row, $staf->location_name);
            $sheet->setCellValue('D' . $row, $staf->tel);
            $sheet->setCellValue('E' . $row, date('d/m/Y', strtotime($staf->doj)));
            $sheet->setCellValue('F' . $row, $salary);
            $sheet->setCellValue('G' . $row, $opening_balance);
            $sheet->setCellValue('H' . $row, $total_advance);
            $sheet->setCellValue('I' . $row, $working_day);
            $sheet->setCellValue('J' . $row, $insentive);
            $sheet->setCellValue('K' . $row, $total_salary);
            $sheet->setCellValue('L' . $row, $net_salary);
            $row++;
        }

        // Set the filename and download the Excel file
        $filename = "staff_salary_details_{$year}_{$month}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }



    function insert_workingday()
    {
        $working_day = $this->request->getVar('working_day');
        $insentive = $this->request->getVar('insentive');
        $staff_id = $this->request->getVar('staff_id');
        $year = $this->request->getVar('year');
        $month = $this->request->getVar('month');
        $tsalary = $this->request->getVar('tsalary');
        $netsalary = $this->request->getVar('netsalary');

        $data = [

            'working_day' => $working_day,
            'insentive' => $insentive,
            'user_id' => $staff_id,
            'year' => $year,
            'month' => $month,
            'total_salary' => $tsalary,
            'net_salary' => $netsalary,
        ];
        // $this->db->table('staff_salary')->delete(array('user_id' => $staff_id,'year' => $year,'month' => $month, )); 

        $staffsalarydtls = $this->db->query("SELECT * FROM staff_salary WHERE user_id = ? AND year = ? AND month = ?", [$staff_id, $year, $month])->getResult();
        if (!empty($staffsalarydtls)) {
            foreach ($staffsalarydtls as $stdtl) {
            }
            $this->db->table('staff_salary')->update($data, ['staff_salary_id' => $stdtl->staff_salary_id]);
        } else {
            $this->db->table('staff_salary')->insert($data);
        }
    }

    function diesel_entry()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');



        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vendor'] = $this->AdminModel->Get_vendor();
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['diesel'] = $this->AdminModel->dieseldata($from_date, $to_date);

        $data['date'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
        ];
        return view('admin/diesel_vw', $data);
    }

    public function insert_diesel()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vendor'] = $this->AdminModel->Get_vendor();
        $data['vehicle'] = $this->AdminModel->Getvehicle();


        // Define validation rules
        $rules = [
            'vendor' => 'required',
            'vehicle' => 'required',
            'qty' => 'required|numeric',
            'rate' => 'required|numeric',
            'date' => 'required',
        ];

        // Validate the input data
        if ($this->validate($rules)) {
            // Retrieve input data
            $vendor = $this->request->getPost('vendor');
            $vehicle = $this->request->getPost('vehicle');
            $qty = $this->request->getPost('qty');
            $rate = $this->request->getPost('rate');
            $date = $this->request->getPost('date');

            // Prepare data for insertion
            $data = [
                'vendor_id' => $vendor,
                'vehicle_id' => $vehicle,
                'qty' => $qty,
                'rate' => $rate,
                'diesel_date' => $date,
            ];

            // Insert data into the database
            $this->db->table('diselentry')->insert($data);
            $user_id = $this->session->get('user_id');
            $menu = $this->request->getUri()->getSegment(2);
            $this->logActivity($user_id, 'create', 'diselentry', $this->db->insertID(), ['data' => $data], $menu);
            // Redirect to a success page
            return redirect()->to(base_url('/admin/diesel_entry'))->with('success', 'Diesel data added successfully');
        } else {
            // If validation fails, pass the validation object to the view
            $data['validation'] = $this->validator;

            // Fetch the vendor and vehicle data to repopulate the form
            $data['vendor'] = $this->AdminModel->getVendors();
            $data['vehicle'] = $this->AdminModel->getVehicles();

            // Load the view with the form
            echo view('admin/diesel_form', $data);
        }
    }

    // function delete_dieselentry()
    // {
    //     $stock_code = $this->request->getPost('user_id');
    //     $this->db->table('diselentry')->delete(array('diselentry_id' => $stock_code));
    //     return redirect()->to('Admin/diesel_entry');
    // }

    public function delete_dieselentry()
    {
        try {
            if ($this->session->get('user_id')) {
                // Get the current user ID and diesel entry ID
                $user_id = $this->session->get('user_id');
                $diselentry_id = $this->request->getPost('user_id');

                if (empty($diselentry_id)) {
                    return $this->fail('Diesel entry ID is required.', 400);
                }

                // Check if the record exists
                $record = $this->db->table('diselentry')->where('diselentry_id', $diselentry_id)->get()->getRow();
                if (!$record) {
                    return $this->failNotFound('No diesel entry found for the given ID.');
                }

                // Soft delete the record
                $data = [
                    'deleted_by' => $user_id,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('diselentry')->where('diselentry_id', $diselentry_id)->update($data);

                // Log the deletion activity in the activity_logs table
                $activity_log = [
                    'user_id' => $user_id,
                    'menu' => 'delete_dieselentry',
                    'action' => 'delete',
                    'model' => 'diselentry',
                    'model_id' => $diselentry_id,
                    'changes' => json_encode($data),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('activity_logs')->insert($activity_log);

                return redirect()->to('Admin/diesel_entry')->with('success', 'Diesel entry deleted successfully.');
            } else {
                // Redirect to login if the user is not logged in
                return redirect()->to('admin/');
            }
        } catch (\Exception $e) {
            // Handle any errors
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }


    public function edit_diesel()
    {
        // Get the diesel entry ID from the request
        $diesel_entry_id = $this->request->getVar('id');

        // Query the database to get the diesel entry by ID


        $diesel_data = $this->db->query(" SELECT d.*, v.name, ve.vehicle_no
        FROM diselentry d
        JOIN vendor v ON d.vendor_id = v.id
        JOIN vehicle ve ON d.vehicle_id = ve.id
        WHERE d.diselentry_id = '$diesel_entry_id'")->getResult();

        // Check if data is fetched

        foreach ($diesel_data as $data) {
        }
    ?>
        <form method="post" action="<?= base_url(); ?>/admin/update_diesel">
            <div class="uk-child-width-1-2@m" uk-grid>
                <div class="form-group">
                    <label for="vender_name">Vendor Name</label>
                    <input class="form-control" type="hidden" name="id" value="<?= $data->diselentry_id; ?>">
                    <input class="form-control" type="text" name="vender_name" value="<?= $data->name; ?>">
                </div>

                <div class="form-group">
                    <label for="vehicle_no">Vehicle No</label>
                    <input class="form-control" type="text" name="vehicle_no" value="<?= $data->vehicle_no; ?>">
                </div>
                <div class="form-group">
                    <label for="diesel_date">Date</label>
                    <input class="form-control" type="date" name="diesel_date" value="<?= $data->diesel_date; ?>">
                </div>
                <div class="form-group">
                    <label for="qty">Quantity</label>
                    <input class="form-control" type="text" name="qty" value="<?= $data->qty; ?>">
                </div>

                <div class="form-group">
                    <label for="rate">Rate</label>
                    <input class="form-control" type="text" name="rate" value="<?= $data->rate; ?>">
                </div>

                <div class="form-group">
                    <div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-angle-right"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php


    }


    public function getDiesel($table, $id)
    {
        return $this->db->table($table)->where('diselentry_id', $id)->get()->getRowArray();
    }

    public function update_diesel()
    {
        // Get the form data
        $diesel_entry_id = $this->request->getVar('id');
        $vendor_name = $this->request->getVar('vender_name');
        $vehicle_no = $this->request->getVar('vehicle_no');
        $diesel_date = $this->request->getVar('diesel_date');
        $quantity = $this->request->getVar('qty');
        $rate = $this->request->getVar('rate');

        // First, find the vendor_id and vehicle_id based on vendor name and vehicle number
        // Assuming vendor names and vehicle numbers are unique, adjust query if not.
        $vendor_data = $this->db->query("SELECT id FROM vendor WHERE name = '$vendor_name'")->getRow();
        $vehicle_data = $this->db->query("SELECT id FROM vehicle WHERE vehicle_no = '$vehicle_no'")->getRow();


        $oldData = $this->getDiesel('diselentry', $diesel_entry_id);

        if ($vendor_data && $vehicle_data) {
            $vendor_id = $vendor_data->id;
            $vehicle_id = $vehicle_data->id;

            // Update the diesel entry in the database
            $data = [
                'vendor_id' => $vendor_id,
                'vehicle_id' => $vehicle_id,
                'diesel_date' => $diesel_date,
                'qty' => $quantity,
                'rate' => $rate
            ];
            $changes = $this->getChanges($oldData, $data);
            $this->db->table('diselentry')
                ->where('diselentry_id', $diesel_entry_id)
                ->update($data);
            $user_id = $this->session->get('user_id');
            $menu = $this->request->getUri()->getSegment(2);
            $this->logActivityy($user_id, 'update', 'diselentry', $diesel_entry_id, $changes, $menu);
            // Redirect or provide a success message
            return redirect()->to(base_url('/admin/diesel_entry'))->with('status', 'Diesel entry updated successfully!');
        } else {
            // Handle cases where the vendor or vehicle isn't found
            return redirect()->back()->with('error', 'Vendor or vehicle not found.');
        }
    }

    function download_diesel_excel()
    {
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        $diesel = $this->AdminModel->dieseldata($from_date, $to_date);

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Sl no',
            'Vendor Name',
            'Vehicle No',
            'Date',
            'Quantity',
            'Rate',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($diesel as $index => $des) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $des->vendor_name);
            $sheet->setCellValue('C' . $row, $des->vehicle_no);
            $sheet->setCellValue('D' . $row, date('d-m-Y', strtotime($des->diesel_date)));
            $sheet->setCellValue('E' . $row, $des->qty);
            $sheet->setCellValue('F' . $row, $des->rate);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = '$diesel_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    function excel_dieselentry()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            $reader = $fileExtension == 'csv' ? new \PhpOffice\PhpSpreadsheet\Reader\Csv() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();


            foreach ($data as $row) {
                // Skip the header row
                if ($row[0] == 'id') {
                    continue;
                }

                $des_date = $row[0] ?? '';

                // Convert the date from dd-mm-yyyy to Y-m-d
                if (!empty($des_date)) {
                    $date_parts = explode('/', $des_date);
                    if (count($date_parts) == 3 && checkdate($date_parts[1], $date_parts[0], $date_parts[2])) {
                        $date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                    } else {
                        // Handle unexpected date format
                        $date = date('Y-m-d', strtotime($des_date));
                    }
                } else {
                    $date = null;
                }

                // echo $date;exit;

                $vendor_code = $row[1] ?? '';
                $vehicle_no = $row[2] ?? '';
                $qty = $row[3] ?? 0;
                $rate = $row[4] ?? 0;

                // Ensure the necessary data is not empty
                if (!empty($vendor_code) && !empty($vehicle_no) && $qty > 0 && $rate > 0) {
                    // Get vendor ID
                    $vendor = $this->db->table('vendor')->select('id')->where('name', $vendor_code)->get()->getRow();
                    $vendor_id = $vendor->id ?? '';

                    // Get vehicle ID
                    $vehicle = $this->db->table('vehicle')->select('id')->where('vehicle_no', $vehicle_no)->get()->getRow();
                    $vehicle_id = $vehicle->id ?? '';

                    // Only insert if both vendor_id and vehicle_id are found
                    if (!empty($vendor_id) && !empty($vehicle_id)) {
                        $insertData = [
                            'vendor_id' => $vendor_id,
                            'vehicle_id' => $vehicle_id,
                            'qty' => $qty,
                            'rate' => $rate,
                            'diesel_date' => $date,
                        ];

                        $this->db->table('diselentry')->insert($insertData);
                    }
                }
            }

            return redirect()->to(base_url('/Admin/diesel_entry'))->with('success', 'Vendor data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }




    public function bank()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['bank_details'] = $this->AdminModel->bank();


        return view('admin/bank_vw', $data);
    }

    function delete_bank()
    {

        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('bank')->delete(array('id' => $segment));
            return redirect()->to('Admin/bank');
        } else {
            return redirect()->to('admin/');
        }
    }

    public function insert_bank()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $data = [
            'bank_name' => $this->request->getPost('bank_name'),
            'ifsc_code' => $this->request->getPost('ifsc_code'),
            'ac_no' => $this->request->getPost('ac_no'),
            'short_name' => $this->request->getPost('short_name'),
            'opening_balance' => $this->request->getPost('opening_balance')
        ];


        $this->db->table('bank')->insert($data);
        return redirect()->to('admin/bank');
    }

    public function update_bank($id)
    {
        // Check if user is logged in
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $id = $this->request->getPost('bank_id');
        // Retrieve form data
        $data = [
            'bank_name' => $this->request->getPost('bank_name'),
            'ifsc_code' => $this->request->getPost('ifsc_code'),
            'ac_no' => $this->request->getPost('ac_no'),
            'short_name' => $this->request->getPost('short_name'),
            'opening_balance' => $this->request->getPost('opening_balance')
        ];

        // Update data in the bank table
        $this->db->table('bank')->update($data, ['id' => $id]);

        // Redirect to a specific route after update
        return redirect()->to('admin/bank');
    }


    public function Route()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['route'] = $this->db->query("
                            SELECT route.*, location.location_name 
                            FROM route 
                            JOIN location ON location.location_id = route.location_id
                        ")->getResult();

        return view('admin/Route_vw', $data);
    }
    public function insert_route()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['route'] = $this->db->query("
                            SELECT route.*, location.location_name 
                            FROM route 
                            JOIN location ON location.location_id = route.location_id
                        ")->getResult();

        // $rules = [
        //     'location_name' => 'required',
        //     'short_name' => 'required|max_length[100]',
        //     'from' => 'required',
        //     'to' => 'required',
        // ];

        // Validate the input data
        // if ($this->validate($rules)) {
        $location_name = $this->request->getPost('location_name');
        $short_name = $this->request->getPost('short_name');
        $from = $this->request->getPost('from');
        $to = $this->request->getPost('to');

        $data = [
            'location_id' => $location_name,
            'location_shortname' => $short_name,
            'from_city' => $from,
            'to_city' => $to,
        ];

        $this->db->table('route')->insert($data);
        return redirect()->to('admin/Route');
        // } else {
        //     $data['validation'] = $this->validator;
        //     return view('admin/Route_vw', $data);
        // }
    }
    public function edit_route()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }



        $route_id = $this->request->getPost('route_id');
        $location_name = $this->request->getPost('location_name');
        $short_name = $this->request->getPost('short_name');
        $from = $this->request->getPost('from');
        $tor = $this->request->getPost('tor');

        $data = [
            'location_id' => $location_name,
            'location_shortname' => $short_name,
            'from_city' => $from,
            'to_city' => $tor,
        ];


        $this->db->table('route')->update($data, ['id' => $route_id]);
        return redirect()->to('admin/Route');
    }
    function delete_route()
    {

        if ($this->session->get('user_id')) {
            $user_id = $this->request->getVar('user_id');
            $this->db->table('route')->delete(array('id' => $user_id));
            return redirect()->to('admin/Route');
        } else {
            return redirect()->to('admin/');
        }
    }
    function excel_route()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                if ($row[0] == 'id') {
                    continue; // Skip the header row
                }

                $location_name = $row[0];
                $short_name = $row[1];
                $from_city = $row[2];
                $to_city = $row[3];



                $location_id = '';
                $location = $this->db->query("SELECT * FROM location where location_shordname='$location_name'")->getResult();
                if (!empty($location)) {
                    foreach ($location as $loc) {
                    }
                    $location_id = $loc->location_id;
                }


                if ($location_id != '') {
                    $data = [
                        'location_id' => $location_id,
                        'location_shortname' => $short_name,
                        'from_city' => $from_city,
                        'to_city' => $to_city,
                    ];

                    $this->db->table('route')->insert($data);
                }
            }

            return redirect()->to(base_url('/Admin/Route'))->with('success', 'Vendor data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }
    public function do_registration()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);

        // $data['doregistration'] = $this->db->query("
        //     SELECT 
        //         do_registration.*,
        //         route.location_shortname,
        //         set_master.set_name AS tonnage_set_name
        //     FROM do_registration
        //     LEFT JOIN route ON route.id = do_registration.route_id
        //     LEFT JOIN set_master ON set_master.id = do_registration.load_tonnage_id
        //     WHERE do_registration.deleted_at IS NULL
        //     ORDER BY do_registration.do_registration_id DESC
        // ")->getResult();

        $data['doregistration'] = $this->db->query("
            SELECT 
                do_registration.*,
                route.location_shortname,
                set_master.set_name AS tonnage_set_name,
                vendor.name AS party_name
            FROM do_registration
            LEFT JOIN route ON route.id = do_registration.route_id
            LEFT JOIN set_master ON set_master.id = do_registration.load_tonnage_id
            LEFT JOIN vendor ON vendor.id = do_registration.party
            WHERE do_registration.deleted_at IS NULL
            ORDER BY do_registration.do_registration_id DESC
        ")->getResult();




        $data['partyNames'] = $this->AdminModel->partyNames();
        // print_r ($data['partyNames']);exit;
        $data['route'] = $this->db->query("
                            SELECT route.*, location.location_name 
                            FROM route 
                            JOIN location ON location.location_id = route.location_id
                        ")->getResult();



        // Add Sets Data (replacing tonnage_list)
        $data['sets'] = $this->AdminModel->all_sets();

        return view('admin/do_registration_vw', $data);
    }

    public function download_doRegistration_excel()
    {
        // Fetch all DO Registration data
        $doregistration = $this->AdminModel->doregistration_dtls(); // Make sure this returns all records

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Sl No',
            'DO No',
            'Route',
            'Diesel/Trip OR Km',
            'Trip Expenses',
            'Party',
            'From Date',
            'To Date',
            'Rate',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data rows
        $row = 2;
        foreach ($doregistration as $index => $doreg) {
            $tripExpenses = "1st = Rs. {$doreg->trip_expenses1}, 2nd = Rs. {$doreg->trip_expenses2}, "
                . "3rd = Rs. {$doreg->trip_expenses3}, 4th = Rs. {$doreg->trip_expenses4}, "
                . "5th = Rs. {$doreg->trip_expenses5}, 6th = Rs. {$doreg->trip_expenses6}";

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $doreg->do_no);
            $sheet->setCellValue('C' . $row, $doreg->location_shortname);
            $sheet->setCellValue('D' . $row, $doreg->diesel_type);
            $sheet->setCellValue('E' . $row, $tripExpenses);
            $sheet->setCellValue('F' . $row, $doreg->party);
            $sheet->setCellValue('G' . $row, $doreg->from_date);
            $sheet->setCellValue('H' . $row, $doreg->to_date);
            $sheet->setCellValue('I' . $row, $doreg->rate);
            $row++;
        }

        // Output file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'do_registration_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    function delete_doregistration()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $do_id = $this->request->getVar('user_id');
            $data = [
                'deleted_by' => $user_id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('do_registration')->update($data, ['do_registration_id' => $do_id]);
            return redirect()->to('admin/do_registration');
        } else {
            return redirect()->to('admin/');
        }
    }
    public function insert_do_registration()
    {
        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['doregistration'] = $this->AdminModel->doregistration_dtls();
                $data['partyNames'] = $this->AdminModel->partyNames();

        $data['route'] = $this->db->query("
                            SELECT route.*, location.location_name 
                            FROM route 
                            JOIN location ON location.location_id = route.location_id
                        ")->getResult();
        
        $data['sets'] = $this->AdminModel->all_sets();





        $rules = [
            'do_no' => 'required',
            'route' => 'required',
            'diesel' => 'required',
            'from_date' => 'required|valid_date',
            'to_date' => 'required|valid_date',
            'rate' => 'required|numeric'
        ];

        if ($this->validate($rules)) {
            $data = [
                'do_no'      => $this->request->getPost('do_no'),
                'route_id'   => $this->request->getPost('route'),
                'max_trip'   => $this->request->getPost('max_trip'),
                'diesel_type'=> $this->request->getPost('diesel_type') . '-' . $this->request->getPost('diesel'),
                'created_by'=> $user_id,
                'created_at'=> date('Y-m-d H:i:s'),
                'bonus'     => $this->request->getPost('bonus'),
                'party'     => $this->request->getPost('party'),
                'from_date' => $this->request->getPost('from_date'),
                'to_date'   => $this->request->getPost('to_date'),
                'rate'      => $this->request->getPost('rate'),

                'cash_type'    => $this->request->getPost('cash_type'),
                'diesel_payment_type' => $this->request->getPost('diesel_payment_type'),
                'diesel_rate' => $this->request->getPost('diesel_rate'),
                'tds_percentage' => $this->request->getPost('tds_percentage') ?? 2.00,
                'shortage_qty' => $this->request->getPost('shortage_qty'),
                'shortage_rate' => $this->request->getPost('shortage_rate'),
                'special_shortage' => $this->request->getPost('special_shortage') ? 1 : 0,

                'trip_expenses1' => $this->request->getPost('1trip_expenses'),
                'trip_expenses2' => $this->request->getPost('2trip_expenses'),
                'trip_expenses3' => $this->request->getPost('3trip_expenses'),
                'trip_expenses4' => $this->request->getPost('4trip_expenses'),
                'trip_expenses5' => $this->request->getPost('5trip_expenses'),
                'trip_expenses6' => $this->request->getPost('6trip_expenses'),
                
                'load_tonnage_id' => $this->request->getPost('load_tonnage'),
            ];

            $this->db->table('do_registration')->insert($data);
            return redirect()->to('admin/do_registration');
        } else {
            $data['validation'] = $this->validator;
            return view('admin/do_registration_vw', $data);
        }
    }
    function edit_doregistration()
    {
        $doreg_id = $this->request->getVar('doreg_id');
        $doregistration = $this->AdminModel->dosingleregistration_dtls($doreg_id);
        $route = $this->db->query("
                            SELECT route.*, location.location_name 
                            FROM route 
                            JOIN location ON location.location_id = route.location_id
                        ")->getResult();
        $tonnage_list = $this->AdminModel->tonnage_dtls();
        $partyNames = $this->AdminModel->partyNames();

        foreach ($doregistration as $doreg) {
        }
        
        // Handle concatenated diesel type
        // Stored as "Type-Value" e.g. "Trip-50" or "Km-100"
        $diesel_full = explode('-', $doreg->diesel_type);
        $diesel_select_val = isset($diesel_full[0]) ? $diesel_full[0] : '';
        $diesel_input_val = isset($diesel_full[1]) ? $diesel_full[1] : '';
        // If legacy data or format issue, fallback might be needed, but assuming new format for now.
        // If old data was just "50", then [0]="50", [1]=undefined.
        if(count($diesel_full) < 2) {
             # Old format or just value
             $diesel_select_val = ''; 
             $diesel_input_val = $doreg->diesel_type; 
        }

    ?>
        <form action="<?= base_url(); ?>/Admin/update_do_registration" enctype="multipart/form-data" method="post">
            <div class="uk-margin-bottom">
                <label>DO No</label>
                <input type="hidden" name="doreg_no" id="edit_doreg_no" class="form-control" value="<?= $doreg->do_registration_id ?>" readonly />
                <input type="text" name="do_no" id="edit_do_no" class="form-control" value="<?= $doreg->do_no ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('do_no'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <label>Route</label>
                <select class="js-states form-control" name="route" id="edit_route">
                    <option value="">Select Route</option>
                    <?php foreach ($route as $rut) { ?>
                        <option value="<?= $rut->id ?>" <?= $doreg->route_id == $rut->id ? 'selected' : '' ?>>(<?= $rut->location_shortname ?>) <?= $rut->from_city ?> === <?= $rut->to_city ?></option>
                    <?php } ?>
                </select>
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('route'); ?></span><?php } ?>
            </div>
            
            <div class="uk-margin-bottom">
                <label>Diesel</label>
                 <select class="form-control" name="diesel_type" id="edit_diesel_type">
                    <option value="">Select Diesel Trip/Km</option>
                    <option value="Trip" <?= ($diesel_select_val == 'Trip') ? 'selected' : '' ?>>Trip</option>
                    <option value="Km" <?= ($diesel_select_val == 'Km') ? 'selected' : '' ?>>Km</option>
                </select>
                <input type="number" class="form-control uk-margin-small-top" name="diesel" value="<?= $diesel_input_val ?>" min='0' />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('diesel'); ?></span><?php } ?>
            </div>

            <div class="uk-margin-bottom" id="edit_load_tonnage_group">
                <label>Tonnage Set</label>
                <select class="form-control" name="load_tonnage" id="edit_load_tonnage">
                    <option value="">Select Set</option>
                    <?php 
                    // Fetch all tonnage sets directly from set_master
                    $tonnage_list = $this->db->table('set_master')->get()->getResult();
                    foreach ($tonnage_list as $s) { ?>
                        <option value="<?= $s->id ?>" <?= ($doreg->load_tonnage_id == $s->id) ? 'selected' : '' ?>>
                            <?= $s->set_name ?>
                        </option>
                    <?php } ?>
                </select>
            </div>


            <div class="uk-child-width-1-2@m uk-grid-small edit_trip_expenses_field" uk-grid>
                <div class="uk-margin-bottom">
                    <label>1Trip Expenses</label>
                    <input type="number" name="1trip_expenses" id="1trip_expenses" class="form-control" value="<?= $doreg->trip_expenses1 ?>" min='0' />
                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('1trip_expenses'); ?></span><?php } ?>
                </div>
                <div class="uk-margin-bottom">
                    <label>2Trip Expenses</label>
                    <input type="number" name="2trip_expenses" id="2trip_expenses" class="form-control" value="<?= $doreg->trip_expenses2 ?>" min='0' />
                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('2trip_expenses'); ?></span><?php } ?>
                </div>
                <div class="uk-margin-bottom">
                    <label>3Trip Expenses</label>
                    <input type="number" name="3trip_expenses" id="3trip_expenses" class="form-control" value="<?= $doreg->trip_expenses3 ?>" min='0' />
                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('3trip_expenses'); ?></span><?php } ?>
                </div>
                <div class="uk-margin-bottom">
                    <label>4Trip Expenses</label>
                    <input type="number" name="4trip_expenses" id="4trip_expenses" class="form-control" value="<?= $doreg->trip_expenses4 ?>" min='0' />
                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('4trip_expenses'); ?></span><?php } ?>
                </div>
                <div class="uk-margin-bottom">
                    <label>5Trip Expenses</label>
                    <input type="number" name="5trip_expenses" id="5trip_expenses" class="form-control" value="<?= $doreg->trip_expenses5 ?>" min='0' />
                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('5trip_expenses'); ?></span><?php } ?>
                </div>
                <div class="uk-margin-bottom">
                    <label>6Trip Expenses</label>
                    <input type="number" name="6trip_expenses" id="6trip_expenses" class="form-control" value="<?= $doreg->trip_expenses6 ?>" min='0' />
                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('6trip_expenses'); ?></span><?php } ?>
                </div>
            </div>

            <div class="uk-margin-bottom">
                <label>Cash</label>
                <select class="form-control" name="cash_type" id="edit_cash_type">
                    <option value="">Select Cash Type</option>
                    <option value="Own" <?= ($doreg->cash_type == 'Own') ? 'selected' : '' ?>>Own</option>
                    <option value="Party" <?= ($doreg->cash_type == 'Party') ? 'selected' : '' ?>>Party</option>
                </select>
            </div>
            <div class="uk-margin-bottom">
                <label>Diesel Source</label>
                <select class="form-control" name="diesel_payment_type" id="edit_diesel_payment_type">
                    <option value="">Select Diesel Source</option>
                    <option value="Own" <?= ($doreg->diesel_payment_type == 'Own') ? 'selected' : '' ?>>Own</option>
                    <option value="Party" <?= ($doreg->diesel_payment_type == 'Party') ? 'selected' : '' ?>>Party</option>
                </select>
            </div>
            <div class="uk-margin-bottom">
                <label>Diesel Rate</label>
                <input type="number" step="0.01" name="diesel_rate" id="edit_diesel_rate" class="form-control" value="<?= $doreg->diesel_rate ?>" />
            </div>

            <div class="uk-margin-bottom">
                <label>From Date</label>
                <input type="date" name="from_date" id="edit_from_date" class="form-control" value="<?= $doreg->from_date ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('from_date'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <label>To Date</label>
                <input type="date" name="to_date" id="edit_to_date" class="form-control" value="<?= $doreg->to_date ?>" />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('to_date'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <label>Party</label>
                <select class="js-states form-control" name="party" id="edit_party">
                    <option value="">Select Party</option>
                    <?php foreach ($partyNames as $pn) { ?>
                        <option value="<?= $pn->id ?>" <?= ($doreg->party == $pn->id || $doreg->party == $pn->name . ' (' . $pn->id . ')') ? 'selected' : '' ?>><?= $pn->name ?></option>
                    <?php } ?>
                </select>
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('party'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <label>Rate</label>
                <input type="number" class="form-control" name="rate" id="edit_rate" value="<?= $doreg->rate ?>" min='0' />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('rate'); ?></span><?php } ?>
            </div>
            
            <div class="uk-margin-bottom">
                <label>TDS Rate (%)</label>
                <input type="number" step="0.01" name="tds_percentage" id="edit_tds_percentage" class="form-control" value="<?= $doreg->tds_percentage ?? 2.00; ?>" />
            </div>
            <div class="uk-margin-bottom">
                <label>Shortage Qty</label>
                <input type="number" step="0.01" name="shortage_qty" id="edit_shortage_qty" class="form-control" value="<?= $doreg->shortage_qty ?>" />
            </div>
            <div class="uk-margin-bottom">
                <label>Shortage Rate</label>
                <input type="number" step="0.01" name="shortage_rate" id="edit_shortage_rate" class="form-control" value="<?= $doreg->shortage_rate ?>" />
            </div>
            <div class="uk-margin-bottom" style="display: flex; align-items: center;">
                <input type="checkbox" name="special_shortage" id="edit_special_shortage" class="uk-checkbox" value="1" <?= ($doreg->special_shortage == 1) ? 'checked' : '' ?> />
                <label for="edit_special_shortage" style="margin-left: 10px; margin-top: 0;">Special Shortage</label>
            </div>
            
            <div class="uk-margin-bottom">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
        
        <!-- <script>
        $(document).ready(function() {
            // Initialize Select2 for Edit fields
            $('#edit_route').select2({
                placeholder: "Select an option",
                allowClear: true
            });
            $('#edit_party').select2({
                placeholder: "Select an option",
                allowClear: true
            });
            $('#edit_load_tonnage').select2({
                placeholder: "Select an option",
                allowClear: true
            });

            // Tonnage Set selection (no amount calculation needed)
        });
        </script> -->
        <script>
            $(document).ready(function () {

                $('#edit_route').select2({
                    placeholder: "Select Route",
                    allowClear: true,
                    dropdownParent: $('#edit_route').parent()
                });

                $('#edit_party').select2({
                    placeholder: "Select Party",
                    allowClear: true,
                    dropdownParent: $('#edit_party').parent()
                });

                $('#edit_load_tonnage').select2({
                    placeholder: "Select Tonnage Set",
                    allowClear: true,
                    dropdownParent: $('#edit_load_tonnage').parent()
                });

            });
        </script>

    <?php
    }

    function update_do_registration()
    {
        $user_id = $this->session->get('user_id');
        $doregid = $this->request->getVar('doreg_no');

        $expense_type = $this->request->getPost('expense_type');
        
        $data = [
            'do_no' => $this->request->getPost('do_no'),
            'route_id' => $this->request->getPost('route'),
            'diesel_type' => $this->request->getPost('diesel_type') . '-' .$this->request->getPost('diesel'),

            'trip_expenses1' => $this->request->getPost('1trip_expenses'),
            'trip_expenses2' => $this->request->getPost('2trip_expenses'),
            'trip_expenses3' => $this->request->getPost('3trip_expenses'),
            'trip_expenses4' => $this->request->getPost('4trip_expenses'),
            'trip_expenses5' => $this->request->getPost('5trip_expenses'),
            'trip_expenses6' => $this->request->getPost('6trip_expenses'),

            'bonus' => $this->request->getPost('bonus'),
            'party' => $this->request->getPost('party'),
            'from_date' => $this->request->getPost('from_date'),
            'to_date' => $this->request->getPost('to_date'),
            'rate' => $this->request->getPost('rate'),
            'max_trip' => $this->request->getPost('max_trip'),
            
            'load_tonnage_id' => $this->request->getPost('load_tonnage'),
            'cash_type' => $this->request->getPost('cash_type'),
            'diesel_payment_type' => $this->request->getPost('diesel_payment_type'),
            'diesel_rate' => $this->request->getPost('diesel_rate'),
            'tds_percentage' => $this->request->getPost('tds_percentage') ?? 2.00,
            'shortage_qty' => $this->request->getPost('shortage_qty'),
            'shortage_rate' => $this->request->getPost('shortage_rate'),
            'special_shortage' => $this->request->getPost('special_shortage') ? 1 : 0,
            
            'updated_by' => $user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('do_registration')->update($data, ['do_registration_id' => $doregid]);

        return redirect()->to('admin/do_registration');
    }

    function changeprice_doregistration()
    {
        $doreg_id = $this->request->getVar('doreg_id');

        // Fetch DO Registration details to get expense_type
        $do_registration = $this->db->query("
            SELECT expense_type, do_no 
            FROM do_registration 
            WHERE do_registration_id = '$doreg_id'
        ")->getRow();

        $expense_type = $do_registration->expense_type ?? 'Trip'; // Default to Trip if not set
        $do_no = $do_registration->do_no;

    ?>

        <h4>Change Price for DO: <?= $do_no ?></h4>
        <p><strong>Expense Type:</strong> <?= $expense_type ?></p>
        <hr>

        <?php if ($expense_type == 'Trip'): ?>
            <!-- ============================================ -->
            <!-- TRIP EXPENSE SECTION (Only for Trip Type)    -->
            <!-- ============================================ -->
            <?php
            // Fetch Trip Price Change History
            $do_pric = $this->db->query("
                SELECT doprice_change.*, do_registration.do_no 
                FROM doprice_change 
                JOIN do_registration ON do_registration.do_registration_id = doprice_change.dono 
                WHERE doprice_change.dono='$doreg_id'
                ORDER BY doprice_change.id DESC
            ")->getResult();
            ?>

            <h5>Trip Expense Change History</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Trip 1</th>
                            <th>Trip 2</th>
                            <th>Trip 3</th>
                            <th>Trip 4</th>
                            <th>Trip 5</th>
                            <th>Trip 6</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($do_pric)): ?>
                            <?php foreach ($do_pric as $item): ?>
                                <tr>
                                    <td><?= $item->id ?></td>
                                    <td><?= $item->trip1 ?></td>
                                    <td><?= $item->trip2 ?></td>
                                    <td><?= $item->trip3 ?></td>
                                    <td><?= $item->trip4 ?></td>
                                    <td><?= $item->trip5 ?></td>
                                    <td><?= $item->trip6 ?></td>
                                    <td><?= $item->from_date ?></td>
                                    <td><?= $item->to_date ?></td>
                                    <td><?= $item->rate ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No trip expense history found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p>&nbsp;</p>

            <h5>Add New Trip Expense Change</h5>
            <form action="<?= base_url(); ?>/Admin/changeprice_do_registration" method="post" class="uk-margin-large-bottom">
                <div class="uk-child-width-1-2@m uk-grid-small uk-grid" uk-grid="">
                    <input type="hidden" name="doreg_id" value="<?= $doreg_id ?>" class="form-control" />
                    <div class="uk-margin-bottom uk-first-column">
                        <label>1=Trip Expenses</label>
                        <input type="text" placeholder="enter 1Trip Expenses" class="form-control" name="1trip_expenses" value="">
                    </div>
                    <div class="uk-margin-bottom">
                        <label>2=Trip Expenses</label>
                        <input type="text" placeholder="enter 2 Trip Expenses" class="form-control" name="2trip_expenses" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin uk-first-column">
                        <label>3=Trip Expenses</label>
                        <input type="text" placeholder="enter 3 Trip Expenses" class="form-control" name="3trip_expenses" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin">
                        <label>4=Trip Expenses</label>
                        <input type="text" placeholder="enter 4Trip Expenses" class="form-control" name="4trip_expenses" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin uk-first-column">
                        <label>5=Trip Expenses</label>
                        <input type="text" placeholder="enter 5 Trip Expenses" class="form-control" name="5trip_expenses" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin">
                        <label>6=Trip Expenses</label>
                        <input type="text" placeholder="enter 6 Trip Expenses" class="form-control" name="6trip_expenses" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin uk-first-column">
                        <label>From Date </label>
                        <input type="date" name="from_date" class="form-control" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin">
                        <label>To date </label>
                        <input type="date" name="to_date" class="form-control" value="">
                    </div>
                    <div class="uk-margin-bottom uk-grid-margin">
                        <label>Rate </label>
                        <input type="number" name="rate" placeholder="Enter Rate" class="form-control" value="">
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Submit Trip Expense Change</button>
            </form>
            <hr class="uk-margin-medium-top">
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- DIESEL RATE SECTION (For All Types)         -->
        <!-- ============================================ -->
        <?php
        // Fetch Diesel Rate Change History
        $diesel_history = $this->db->query("
            SELECT * 
            FROM dodiesel_change 
            WHERE dono='$doreg_id'
            ORDER BY id DESC
        ")->getResult();
        ?>

        <h5 style="color: #000;">Diesel Rate Change History</h5>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Diesel Rate</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($diesel_history)): ?>
                        <?php foreach ($diesel_history as $item): ?>
                            <tr>
                                <td><?= $item->id ?></td>
                                <td>₹<?= number_format($item->diesel_rate, 2) ?></td>
                                <td><?= $item->from_date ?></td>
                                <td><?= $item->to_date ?></td>
                                <td><?= $item->created_at ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No diesel rate history found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p>&nbsp;</p>

        <h5>Add New Diesel Rate Change</h5>
        <form action="<?= base_url(); ?>/Admin/changeprice_diesel_registration" method="post">
            <div class="uk-child-width-1-2@m uk-grid-small uk-grid" uk-grid="">
                <input type="hidden" name="doreg_id" value="<?= $doreg_id ?>" class="form-control" />
                <div class="uk-margin-bottom">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="" required>
                </div>
                <div class="uk-margin-bottom uk-grid-margin uk-first-column">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="" required>
                </div>
                <div class="uk-margin-bottom uk-first-column">
                    <label>Diesel Rate (₹)</label>
                    <input type="number" step="0.01" name="diesel_rate" placeholder="Enter Diesel Rate" class="form-control" value="" required>
                </div>
            </div>
            <button class="btn btn-success" type="submit">Submit Diesel Rate Change</button>
        </form>

    <?php
    }


    function changeprice_do_registration()
    {
        $doreg_id = $this->request->getVar('doreg_id');
        $trip_expenses1 = $this->request->getVar('1trip_expenses');
        $trip_expenses2 = $this->request->getVar('2trip_expenses');
        $trip_expenses3 = $this->request->getVar('3trip_expenses');
        $trip_expenses4 = $this->request->getVar('4trip_expenses');
        $trip_expenses5 = $this->request->getVar('5trip_expenses');
        $trip_expenses6 = $this->request->getVar('6trip_expenses');
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');
        $rate = $this->request->getVar('rate');

        $data = [
            'dono' => $doreg_id,
            'trip1' => $trip_expenses1,
            'trip2' => $trip_expenses2,
            'trip3' => $trip_expenses3,
            'trip4' => $trip_expenses4,
            'trip5' => $trip_expenses5,
            'trip6' => $trip_expenses6,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'rate' => $rate,
        ];

        //   echo "<pre>";
        //   print_r ($data);
        $this->db->table('doprice_change ')->insert($data);

        return redirect()->to('admin/do_registration');
    }

    function changeprice_diesel_registration()
    {
        $user_id = $this->session->get('user_id');
        $doreg_id = $this->request->getVar('doreg_id');
        $diesel_rate = $this->request->getVar('diesel_rate');
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        $data = [
            'dono' => $doreg_id,
            'diesel_rate' => $diesel_rate,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $user_id
        ];

        $this->db->table('dodiesel_change')->insert($data);

        return redirect()->to('admin/do_registration');
    }
    function addshortage()
    {
        $doreg_id = $this->request->getVar('doreg_id');

        // Fetch shortage details if they exist
        $do_pric = $this->db->query(
            "
        SELECT * FROM shortage_details WHERE do_id = ?",
            [$doreg_id]
        )->getRow();

    ?>
        <form action="<?= base_url(); ?>/Admin/insert_shortage" method="post">
            <div class="uk-child-width-1-1@m uk-grid-small uk-grid" uk-grid>

                <input type="hidden" name="doreg_id" value="<?= $doreg_id ?>" class="form-control" />

                <div class="uk-margin-bottom">
                    <label>Shortage Qty</label>
                    <input type="text" placeholder="Qty" id="qty" class="form-control" name="qty"
                        value="<?= $do_pric->qty ?? '' ?>">
                </div>

                <div class="uk-margin-bottom">
                    <label for="shortage_value">Greater Than Amount</label>
                    <div>
                        <input type="text" placeholder="Price" id="greater" class="form-control" name="greater"
                            value="<?= $do_pric->greater_than ?? '' ?>">
                    </div>
                </div>

                <div class="uk-margin-bottom">
                    <label for="shortage_value">Less Than Amount</label>
                    <div>
                        <input type="text" placeholder="Price" id="less" class="form-control" name="less"
                            value="<?= $do_pric->less_than ?? '' ?>">
                    </div>
                </div>

                <div class="uk-margin-bottom">
                    <label for="shortage_value">Equal To Amount</label>
                    <div>
                        <input type="text" placeholder="Price" id="equal" class="form-control" name="equal"
                            value="<?= $do_pric->equal_to ?? '' ?>">
                    </div>
                </div>

                <div class="uk-margin-bottom">
                    <label>Special Deduction</label>
                    <div>
                        <label>
                            <input type="radio" name="special_deduction" value="1"
                                <?= isset($do_pric->special_deduction) && $do_pric->special_deduction == 1 ? 'checked' : '' ?>> Yes
                        </label>
                        <label>
                            <input type="radio" name="special_deduction" value="0"
                                <?= !isset($do_pric->special_deduction) || $do_pric->special_deduction == 0 ? 'checked' : '' ?>> No
                        </label>
                    </div>
                </div>

            </div>
            <button class="btn btn-primary" type="submit">Submit</button>
        </form>
    <?php
    }

    function insert_shortage()
    {
        $doreg_id = $this->request->getVar('doreg_id');
        $qty = $this->request->getVar('qty');
        $greater_amount = $this->request->getVar('greater');
        $less_amount = $this->request->getVar('less');
        $equal_amount = $this->request->getVar('equal');
        $special_deduction = $this->request->getVar('special_deduction');

        // Delete old records for this do_id
        $this->db->table('shortage_details')->where('do_id', $doreg_id)->delete();

        // Insert new record
        $data = [
            'do_id' => $doreg_id,
            'qty' => $qty,
            'greater_than' => $greater_amount,
            'less_than' => $less_amount,
            'equal_to' => $equal_amount,
            'special_deduction' => $special_deduction,
        ];

        $this->db->table('shortage_details')->insert($data);

        return redirect()->to('admin/do_registration')->with('success', 'Shortage details updated successfully!');
    }


    public function Payment()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/');
        }

        $user_id = $this->session->get('user_id');
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');
        $party = $this->request->getVar('party');
        $voucher_no = $this->request->getVar('voucher_no');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['all_party'] = $this->AdminModel->Getallvendor('Party');
        $data['all_vouchers'] = $this->AdminModel->get_active_groups(); 

        // Use the new method to fetch from voucher_payment table
        $data['payment_vouchers'] = $this->AdminModel->getVoucherPayments($from_date, $to_date, $party);
        
        $data['filters'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'party' => $party,
        ];

        return view('admin/voucherpayment_vw', $data);
    }

public function getPaymentVoucherDetails()
{
    $id = $this->request->getVar('id');
    $payment = $this->AdminModel->getPaymentVoucherById($id);
    
    if ($payment) {
        // Parse voucher_ids
        $voucher_ids_str = $payment->voucher_ids; // e.g. "1,2,3"
        $voucher_ids = [];
        if (!empty($voucher_ids_str)) {
             $parts = explode(',', $voucher_ids_str);
             foreach($parts as $p) $voucher_ids[] = trim($p);
        }
        
        if (empty($voucher_ids)) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }
        
        $vouchers = $this->AdminModel->getVouchersByList($voucher_ids);
        return $this->response->setJSON(['status' => 'success', 'data' => $vouchers]);
    } else {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Payment record not found']);
    }
}

public function getPaymentChallanDetails()
{
    $id = $this->request->getVar('id');
    
    // 1. Get the payment voucher record
    $payment_voucher = $this->AdminModel->getPaymentVoucherById($id);
    
    if (!$payment_voucher) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Payment record not found']);
    }

    // 2. Parse Voucher IDs
    $voucher_ids_str = $payment_voucher->voucher_ids;
    $voucher_ids = [];
    if (!empty($voucher_ids_str)) {
        $parts = explode(',', $voucher_ids_str);
        foreach($parts as $p) {
            $voucher_ids[] = trim($p); 
        }
    }

    if (empty($voucher_ids)) {
        return $this->response->setJSON(['status' => 'success', 'data' => []]);
    }

    // 3. Get Challans for these Voucher IDs
    $challans = $this->AdminModel->getChallansByVoucherList($voucher_ids);
    return $this->response->setJSON(['status' => 'success', 'data' => $challans]);
}

public function exportPaymentVoucherExcel()
{
    $id = $this->request->getVar('id');
    $payment = $this->AdminModel->getPaymentVoucherById($id);
    
    if (!$payment) {
        return "Payment Record Not Found";
    }

    $voucher_ids_str = $payment->voucher_ids;
    $voucher_ids = [];
    if (!empty($voucher_ids_str)) {
        $parts = explode(',', $voucher_ids_str);
        foreach($parts as $p) $voucher_ids[] = trim($p);
    }
    
    $vouchers = !empty($voucher_ids) ? $this->AdminModel->getVouchersByList($voucher_ids) : [];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Linked Vouchers');

    $headers = ['Sl No', 'Party Name', 'Voucher ID', 'No of Challan', 'Total Net Amount', 'Deposited by', 'Deposited on', 'Deposit Place'];
    $col = 'A';
    foreach($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    $row = 2;
    foreach($vouchers as $index => $v) {
        $sheet->setCellValue('A'.$row, $index + 1);
        $sheet->setCellValue('B'.$row, $v->party_name ?? 'N/A');
        $sheet->setCellValue('C'.$row, $v->group_code ?? '-');
        $sheet->setCellValue('D'.$row, $v->challan_count ?? 0);
        $sheet->setCellValue('E'.$row, $v->total_net_amount ?? 0);
        $sheet->setCellValue('F'.$row, $v->deposited_by_name ?? '-');
        $sheet->setCellValue('G'.$row, $v->deposit_date ?? '-');
        $sheet->setCellValue('H'.$row, $v->deposit_place ?? '-');
        $row++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Linked_Vouchers_'.$id.'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportPaymentChallanExcel()
{
    $id = $this->request->getVar('id');
    $payment = $this->AdminModel->getPaymentVoucherById($id);
    
    if (!$payment) {
        return "Payment Record Not Found";
    }

    $voucher_ids_str = $payment->voucher_ids;
    $voucher_ids = [];
    if (!empty($voucher_ids_str)) {
        $parts = explode(',', $voucher_ids_str);
        foreach($parts as $p) $voucher_ids[] = trim($p);
    }
    
    $challans = !empty($voucher_ids) ? $this->AdminModel->getChallansByVoucherList($voucher_ids) : [];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Challan Details');

    $totals = [
        'qty' => 0, 'received' => 0, 'shortage' => 0, 's_price' => 0, 
        'd_qty' => 0, 'd_amount' => 0, 'cash' => 0, 'bilty' => 0, 
        'tds' => 0, 'net' => 0
    ];

    $headers = [
        'Sl No', 'Date', 'Voucher ID', 'DO No', 'Vehicle No', 'Ref No', 'Challan No', 
        'Qty', 'Party Rate', 'Received Qty', 'Min Qty', 'Shortage', 'Shortage Price', 
        'Diesel Qty', 'Diesel Amount', 'Cash', 'Bilty Comm', 'TDS', 'Net Amount', 'Added By'
    ];

    $col = 'A';
    foreach($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    $row = 2;
    foreach($challans as $index => $des) {
        // Recalculate values exactly like in JS for consistency
        $qty = (float)($des->quantity ?? 0);
        $rate = (float)($des->rate ?? 0);
        $received = (float)($des->rest_amount ?? 0);
        $do_min = (float)($des->min_qty ?? 0);
        $s_rate = (float)($des->shortage_rate ?? 0);
        $d_qty = (float)($des->dieselQty ?? 0);
        $d_rate = (float)($des->diesel_rate ?? 0);
        $cash = (float)($des->cash ?? 0);
        $bilty = (float)($des->bilty_commission ?? 0);
        $tds_p = (float)($des->tds_percentage ?? 2.00);
        $special_shortage = (int)($des->special_shortage ?? 0);

        $actual_min = min($qty, $received);
        $actual_shortage = max(0, $qty - $received); // Corrected from $qty - received
        $freight = $actual_min * $rate;
        
        $s_price = 0;
        if ($actual_shortage > 0) {
            $chargeable_shortage = ($special_shortage == 1) ? max(0, $actual_shortage - $do_min) : $actual_shortage;
            $s_price = $chargeable_shortage * ($s_rate > 0 ? $s_rate : $rate);
        }
        
        $d_amount = $d_qty * $d_rate;
        $tds = ($actual_min * $rate * $tds_p) / 100;
        $net = $freight - $s_price - $d_amount + $cash - $bilty - $tds;

        $sheet->setCellValue('A'.$row, $index + 1);
        $sheet->setCellValue('B'.$row, $des->des_date ? date('d-m-Y', strtotime($des->des_date)) : '-');
        $sheet->setCellValue('C'.$row, $des->group_code ?? '-');
        $sheet->setCellValue('D'.$row, $des->doreg_no ?? '-');
        $sheet->setCellValue('E'.$row, $des->vehicle_number ?? '-');
        $sheet->setCellValue('F'.$row, $des->ref_no ?? '-');
        $sheet->setCellValue('G'.$row, $des->challan_no ?? '-');
        $sheet->setCellValue('H'.$row, $qty);
        $sheet->setCellValue('I'.$row, $rate);
        $sheet->setCellValue('J'.$row, $received);
        $sheet->setCellValue('K'.$row, $do_min);
        $sheet->setCellValue('L'.$row, $actual_shortage);
        $sheet->setCellValue('M'.$row, $s_price);
        $sheet->setCellValue('N'.$row, $d_qty);
        $sheet->setCellValue('O'.$row, $d_amount);
        $sheet->setCellValue('P'.$row, $cash);
        $sheet->setCellValue('Q'.$row, $bilty);
        $sheet->setCellValue('R'.$row, $tds);
        $sheet->setCellValue('S'.$row, $net);
        $sheet->setCellValue('T'.$row, $des->made_by ?? '-');

        // Add to totals
        $totals['qty'] += $qty;
        $totals['received'] += $received;
        $totals['shortage'] += $actual_shortage;
        $totals['s_price'] += $s_price;
        $totals['d_qty'] += $d_qty;
        $totals['d_amount'] += $d_amount;
        $totals['cash'] += $cash;
        $totals['bilty'] += $bilty;
        $totals['tds'] += $tds;
        $totals['net'] += $net;
        
        $row++;
    }

    // Add Totals Row
    $sheet->setCellValue('G' . $row, 'TOTAL:');
    $sheet->setCellValue('H' . $row, $totals['qty']);
    $sheet->setCellValue('J' . $row, $totals['received']);
    $sheet->setCellValue('L' . $row, $totals['shortage']);
    $sheet->setCellValue('M' . $row, $totals['s_price']);
    $sheet->setCellValue('N' . $row, $totals['d_qty']);
    $sheet->setCellValue('O' . $row, $totals['d_amount']);
    $sheet->setCellValue('P' . $row, $totals['cash']);
    $sheet->setCellValue('Q' . $row, $totals['bilty']);
    $sheet->setCellValue('R' . $row, $totals['tds']);
    $sheet->setCellValue('S' . $row, $totals['net']);

    // Style the totals row
    $sheet->getStyle('A' . $row . ':T' . $row)->getFont()->setBold(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Challan_Details_'.$id.'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportCollectionExcel()
{
    if ($this->session->get('user_id') == '') {
        return "Session Expired";
    }

    $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
    $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
    $do_no = $this->request->getVar('do_no');
    $voucher_id = $this->request->getVar('voucher_id');
    $chalan_status = $this->request->getVar('chalan_status');
    $payment_status = $this->request->getVar('payment_status');
    $deposited_status = $this->request->getVar('deposited_status');

    $despatch = $this->AdminModel->despatch_dtls1($from_date, $to_date, $do_no, $chalan_status, $payment_status, $deposited_status, null, null, $voucher_id);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Collection Report');

    $headers = [
        'Sl No', 'Date', 'DO No', 'Vehicle No', 'Ref No', 'Challan No', 
        'Qty', 'Party Rate', 'Received Qty', 'Min Qty', 'Shortage', 
        'Freight', 'Shortage Price', 'Diesel Qty', 'Diesel Amount', 
        'Cash', 'Bilty Comm', 'TDS', 'Net Amount', 'Added By', 'Voucher ID'
    ];

    $col = 'A';
    foreach($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    $row = 2;
    $totals = [
        'qty' => 0, 'min_qty' => 0, 'shortage' => 0, 'freight' => 0,
        's_price' => 0, 'd_qty' => 0, 'd_amount' => 0, 'cash' => 0,
        'bilty' => 0, 'tds' => 0, 'net' => 0
    ];

    foreach($despatch as $index => $des) {
        $qty = (float)($des->quantity ?? 0);
        $rate = (float)($des->rate ?? 0);
        $received = $des->rest_amount;
        $do_min = (float)($des->min_qty ?? 0);
        $s_rate = (float)($des->shortage_rate ?? 0);
        $d_qty = (float)($des->dieselQty ?? 0);
        $d_rate = (float)($des->diesel_rate ?? 0);
        $cash = (float)($des->cash ?? 0);
        $bilty = (float)($des->bilty_commission ?? 0);
        $tds_p = (float)($des->tds_percentage ?? 2.00);
        $special_shortage = (int)($des->special_shortage ?? 0);

        if ($received !== null && $received !== '' && $received > 0) {
            $actual_min = min($qty, $received);
            $actual_shortage = max(0, $qty - $received);
            $freight = $actual_min * $rate;
            
            if ($actual_shortage <= 0) {
                $s_price = 0;
            } else {
                $chargeable_shortage = ($special_shortage == 1) ? max(0, $actual_shortage - $do_min) : $actual_shortage;
                $s_price = $chargeable_shortage * ($s_rate > 0 ? $s_rate : $rate);
            }
            $tds = ($actual_min * $rate * $tds_p) / 100;
        } else {
            $actual_min = 0; $actual_shortage = 0; $freight = 0; $s_price = 0; $tds = 0;
        }
        
        $d_amount = $d_qty * $d_rate;
        $net = $freight - $s_price - $d_amount + $cash - $bilty - $tds;

        $sheet->setCellValue('A'.$row, $index + 1);
        $sheet->setCellValue('B'.$row, $des->des_date ? date('d-m-Y', strtotime($des->des_date)) : '-');
        $sheet->setCellValue('C'.$row, $des->doreg_no ?? '-');
        $sheet->setCellValue('D'.$row, $des->vehicle_number ?? '-');
        $sheet->setCellValue('E'.$row, $des->ref_no ?? '-');
        $sheet->setCellValue('F'.$row, $des->challan_no ?? '-');
        $sheet->setCellValue('G'.$row, $qty);
        $sheet->setCellValue('H'.$row, $rate);
        $sheet->setCellValue('I'.$row, $received ?? 0);
        $sheet->setCellValue('J'.$row, $actual_min);
        $sheet->setCellValue('K'.$row, $actual_shortage);
        $sheet->setCellValue('L'.$row, $freight);
        $sheet->setCellValue('M'.$row, $s_price);
        $sheet->setCellValue('N'.$row, $d_qty);
        $sheet->setCellValue('O'.$row, $d_amount);
        $sheet->setCellValue('P'.$row, $cash);
        $sheet->setCellValue('Q'.$row, $bilty);
        $sheet->setCellValue('R'.$row, $tds);
        $sheet->setCellValue('S'.$row, $net);
        $sheet->setCellValue('T'.$row, $des->deposit_by ?? '-');
        $sheet->setCellValue('U'.$row, $des->group_code ?? '-');

        // Totals
        $totals['qty'] += $qty;
        $totals['min_qty'] += $actual_min;
        $totals['shortage'] += $actual_shortage;
        $totals['freight'] += $freight;
        $totals['s_price'] += $s_price;
        $totals['d_qty'] += $d_qty;
        $totals['d_amount'] += $d_amount;
        $totals['cash'] += $cash;
        $totals['bilty'] += $bilty;
        $totals['tds'] += $tds;
        $totals['net'] += $net;

        $row++;
    }

    $sheet->setCellValue('F' . $row, 'TOTAL:');
    $sheet->setCellValue('G' . $row, $totals['qty']);
    $sheet->setCellValue('J' . $row, $totals['min_qty']);
    $sheet->setCellValue('K' . $row, $totals['shortage']);
    $sheet->setCellValue('L' . $row, $totals['freight']);
    $sheet->setCellValue('M' . $row, $totals['s_price']);
    $sheet->setCellValue('N' . $row, $totals['d_qty']);
    $sheet->setCellValue('O' . $row, $totals['d_amount']);
    $sheet->setCellValue('P' . $row, $totals['cash']);
    $sheet->setCellValue('Q' . $row, $totals['bilty']);
    $sheet->setCellValue('R' . $row, $totals['tds']);
    $sheet->setCellValue('S' . $row, $totals['net']);
    
    $sheet->getStyle('A' . $row . ':U' . $row)->getFont()->setBold(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Collection_Report_'.date('YmdHis').'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportDepositExcel()
{
    if ($this->session->get('user_id') == '') {
        return "Session Expired";
    }

    $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
    $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
    $party = $this->request->getVar('party');
    $status = $this->request->getVar('status');

    $vouchers = $this->AdminModel->getVouchersForDeposit($from_date, $to_date, $party, null, $status);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Deposit Report');

    $headers = [
        'Sl No', 'Party Name', 'Voucher No', 'No of Challan', 
        'Total Net Amount', 'Deposited by', 'Deposited on', 'Deposit Place'
    ];

    $col = 'A';
    foreach($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    $row = 2;
    $total_amount = 0;
    $total_challans = 0;

    foreach($vouchers as $index => $v) {
        $sheet->setCellValue('A'.$row, $index + 1);
        $sheet->setCellValue('B'.$row, $v->party_name ?? '-');
        $sheet->setCellValue('C'.$row, $v->group_code ?? '-');
        $sheet->setCellValue('D'.$row, $v->challan_count ?? 0);
        $sheet->setCellValue('E'.$row, (float)($v->total_net_amount ?? 0));
        $sheet->setCellValue('F'.$row, $v->deposited_by ?? '-');
        $sheet->setCellValue('G'.$row, ($v->deposit_date && $v->deposit_date != '0000-00-00') ? date('d-m-Y', strtotime($v->deposit_date)) : '-');
        $sheet->setCellValue('H'.$row, $v->deposit_place ?? '-');

        $total_amount += (float)($v->total_net_amount ?? 0);
        $total_challans += (int)($v->challan_count ?? 0);
        $row++;
    }

    $sheet->setCellValue('C' . $row, 'TOTAL:');
    $sheet->setCellValue('D' . $row, $total_challans);
    $sheet->setCellValue('E' . $row, $total_amount);
    
    $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Deposit_Report_'.date('YmdHis').'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportPaymentExcel()
{
    if ($this->session->get('user_id') == '') {
        return "Session Expired";
    }

    $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
    $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
    $party = $this->request->getVar('party');

    $payments = $this->AdminModel->getVoucherPayments($from_date, $to_date, $party);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Payment Report');

    $headers = [
        'Sl No', 'Party Name', 'No. of Vouchers', 'No. of Challans', 
        'Total Amount', 'Received Date', 'Received Amount', 
        'Adjustment Amount', 'Difference', 'Adjustment Remarks'
    ];

    $col = 'A';
    foreach($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    $row = 2;
    $totals = [
        'vouchers' => 0, 'challans' => 0, 'total' => 0, 
        'received' => 0, 'adjustment' => 0, 'difference' => 0
    ];

    foreach($payments as $index => $v) {
        $v_count = !empty($v->voucher_ids) ? count(explode(',', $v->voucher_ids)) : 0;
        $diff = (float)($v->total_net_amount ?? 0) - (float)($v->received_amount ?? 0) - (float)($v->adjustment_amount ?? 0);

        $sheet->setCellValue('A'.$row, $index + 1);
        $sheet->setCellValue('B'.$row, $v->party_name ?? 'N/A');
        $sheet->setCellValue('C'.$row, $v_count);
        $sheet->setCellValue('D'.$row, $v->total_challans ?? 0);
        $sheet->setCellValue('E'.$row, (float)($v->total_net_amount ?? 0));
        $sheet->setCellValue('F'.$row, ($v->received_date && $v->received_date != '0000-00-00') ? date('d-m-Y', strtotime($v->received_date)) : '-');
        $sheet->setCellValue('G'.$row, (float)($v->received_amount ?? 0));
        $sheet->setCellValue('H'.$row, (float)($v->adjustment_amount ?? 0));
        $sheet->setCellValue('I'.$row, $diff);
        $sheet->setCellValue('J'.$row, $v->adjustment_remarks ?? '-');

        // Totals
        $totals['vouchers'] += $v_count;
        $totals['challans'] += (int)($v->total_challans ?? 0);
        $totals['total'] += (float)($v->total_net_amount ?? 0);
        $totals['received'] += (float)($v->received_amount ?? 0);
        $totals['adjustment'] += (float)($v->adjustment_amount ?? 0);
        $totals['difference'] += $diff;

        $row++;
    }

    $sheet->setCellValue('B' . $row, 'TOTAL:');
    $sheet->setCellValue('C' . $row, $totals['vouchers']);
    $sheet->setCellValue('D' . $row, $totals['challans']);
    $sheet->setCellValue('E' . $row, $totals['total']);
    $sheet->setCellValue('G' . $row, $totals['received']);
    $sheet->setCellValue('H' . $row, $totals['adjustment']);
    $sheet->setCellValue('I' . $row, $totals['difference']);
    
    $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Payment_Report_'.date('YmdHis').'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
    exit;
}

    public function updateVoucherPayment()
    {
        if ($this->session->get('user_id') == '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Session expired']);
        }

        $id = $this->request->getPost('id');
        $received_amount = (float)$this->request->getPost('received_amount');
        $adjustment_amount = (float)$this->request->getPost('adjustment_amount');

        $data = [
            'received_date' => $this->request->getPost('received_date'),
            'received_amount' => $received_amount,
            'adjustment_amount' => $adjustment_amount,
            'adjustment_remarks' => $this->request->getPost('adjustment_remarks'),
        ];

        // Fetch current total to calculate difference
        $current = $this->db->table('voucher_payment')->select('total_net_amount')->where('id', $id)->get()->getRow();
        if ($current) {
            $data['difference_amount'] = (float)$current->total_net_amount - $received_amount - $adjustment_amount;
        }

        // Update the voucher_payment table
        $updated = $this->db->table('voucher_payment')->where('id', $id)->update($data);

        if ($updated) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Payment updated successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update payment']);
        }
    }

    public function addToPayment()
    {
        if ($this->session->get('user_id') == '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Session expired']);
        }

        $voucher_ids = $this->request->getPost('voucher_ids');
        
        // Ensure voucher_ids is an array
        if (!is_array($voucher_ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data format']);
        }

        $result = $this->AdminModel->createVoucherPayment($voucher_ids);
        
        return $this->response->setJSON($result);
    }


    public function voucher_commition_entry()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
        $do_no = $this->request->getVar('do_no');


        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['doregistration'] = $this->AdminModel->doregistration_dtls1($from_date, $to_date);

        $data['despatch'] = $this->AdminModel->despatch_dtls1($from_date, $to_date, $do_no);
        // echo "<pre>";
        // print_r($data['doregistration']);exit;
        $data['date'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'do_no' => $do_no,
        ];


        return view('admin/voucher_commition_vw', $data);
    }
    public function updateCommition()
    {
        $id = $this->request->getPost('id');
        $commition = $this->request->getPost('commition');

        // Database connection
        $db = db_connect();
        $builder = $db->table('despatch');

        // Prepare data for update
        $data = ['commition' => $commition];

        // Update the commission field
        $updated = $builder->where('despatch_id', $id)->update($data);

        if ($updated) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update'
            ]);
        }
    }



    public function voucher_entry()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        // Get pagination parameters
        $records_per_page = $this->request->getVar('per_page') ? (int)$this->request->getVar('per_page') : 10;
        $current_page = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $offset = ($current_page - 1) * $records_per_page;

        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
        $do_no = $this->request->getVar('do_no');
        $chalan_status = $this->request->getVar('chalan_status');
        $payment_status = $this->request->getVar('payment_status');
        $deposited_status = $this->request->getVar('deposited_status');

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['doregistration'] = $this->AdminModel->doregistration_dtls1($from_date, $to_date);

        // Get total count for pagination
        $data['total_count'] = $this->AdminModel->despatch_count(
            $from_date,
            $to_date,
            $do_no,
            $chalan_status,
            $payment_status,
            $deposited_status
        );

        // Get paginated data
        $data['despatch'] = $this->AdminModel->despatch_dtls1_paginated(
            $from_date,
            $to_date,
            $do_no,
            $chalan_status,
            $payment_status,
            $deposited_status,
            $records_per_page,
            $offset
        );

        // Include deposited_status in $data['date'] for form retention
        $data['date'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'do_no' => $do_no,
            'chalan_status' => $chalan_status,
            'payment_status' => $payment_status,
            'deposited_status' => $deposited_status,
        ];
        
        // Add pagination data to view
        $data['current_page'] = $current_page;
        $data['records_per_page'] = $records_per_page;

        return view('admin/voucher_vw', $data);
    }

    public function Collection()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $records_per_page = $this->request->getVar('per_page') ? (int)$this->request->getVar('per_page') : 10;
        $current_page = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $offset = ($current_page - 1) * $records_per_page;

        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
        $do_no = $this->request->getVar('do_no');
        $voucher_id = $this->request->getVar('voucher_id');
        $chalan_status = $this->request->getVar('chalan_status');
        $payment_status = $this->request->getVar('payment_status');
        $deposited_status = $this->request->getVar('deposited_status');

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['doregistration'] = $this->AdminModel->doregistration_dtls1($from_date, $to_date, $voucher_id);
        
        // Fetch active vouchers for the filter dropdown
        $voucher_builder = $this->db->table('voucher')
            ->select('voucher.id, voucher.group_code')
            ->where('voucher.status', 1);
            
        if ($do_no) {
            // Join to despatch only to find vouchers that have challans from this DO
            $voucher_builder->join('despatch', 'despatch.voucher_id = voucher.id');
            $voucher_builder->where('despatch.do_no', $do_no);
            $voucher_builder->distinct();
        }
        
        // If there's a voucher_id selected, we MUST make sure it's in the list even if it's NOT in the filtered list
        // (to avoid the dropdown losing the selection)
        if ($voucher_id && $do_no) {
             $voucher_builder->groupStart()
                 ->where('voucher.status', 1) // redundant but safe
                 ->orWhere('voucher.id', $voucher_id)
             ->groupEnd();
        }
        
        $data['vouchers'] = $voucher_builder->orderBy('voucher.created_at', 'DESC')
            ->get()
            ->getResult();

        $data['total_count'] = $this->AdminModel->despatch_count($from_date, $to_date, $do_no, $chalan_status, $payment_status, $deposited_status, $voucher_id);
        $data['despatch'] = $this->AdminModel->despatch_dtls1_paginated($from_date, $to_date, $do_no, $chalan_status, $payment_status, $deposited_status, $records_per_page, $offset, $voucher_id);

        $data['date'] = ['from_date' => $from_date, 'to_date' => $to_date, 'do_no' => $do_no, 'voucher_id' => $voucher_id, 'chalan_status' => $chalan_status, 'payment_status' => $payment_status, 'deposited_status' => $deposited_status];
        $data['current_page'] = $current_page;
        $data['records_per_page'] = $records_per_page;

        return view('admin/collection_vw', $data);
    }

        public function Deposit()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $records_per_page = $this->request->getVar('per_page') ? (int)$this->request->getVar('per_page') : 10;
        $current_page = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $offset = ($current_page - 1) * $records_per_page;

        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
        $party = $this->request->getVar('party');
        $voucher_no = $this->request->getVar('voucher_no');
        $status = $this->request->getVar('status');

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['all_users'] = $this->AdminModel->Customerdata();
        $data['vendors'] = $this->AdminModel->Get_vendor(); // Fetch vendors for filter

        // Fetch active vouchers for the filter dropdown
        $data['voucher_list'] = $this->db->table('voucher')
            ->select('id, group_code')
            ->where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        // $data['vouchers'] replaced by vouchers
        $data['vouchers'] = $this->AdminModel->getVouchersForDeposit($from_date, $to_date, $party, $voucher_no, $status);

        $data['date'] = ['from_date' => $from_date, 'to_date' => $to_date, 'party' => $party, 'voucher_no' => $voucher_no, 'status' => $status];
        $data['current_page'] = $current_page;
        $data['records_per_page'] = $records_per_page;

        return view('admin/deposit_vw', $data);
    }

    public function updateVoucherDeposit()
    {
        $id = $this->request->getPost('voucher_id');
        
        $data = [
            'deposited_by' => $this->request->getPost('deposited_by'),
            'deposit_date' => $this->request->getPost('deposit_date'),
            'deposit_place' => $this->request->getPost('deposit_place'),
        ];

        // Fetch existing voucher data to get current images
        $existingVoucher = $this->db->table('voucher')->where('id', $id)->get()->getRow();
        $images = [];
        if (!empty($existingVoucher->receipt_image)) {
            $images = json_decode($existingVoucher->receipt_image, true);
            if (!is_array($images)) {
                // Handle legacy single image string
                $images = [$existingVoucher->receipt_image];
            }
        }
        
        $files = $this->request->getFileMultiple('challan_receipt');
        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(ROOTPATH . 'public/assets/uploads/receipts', $newName);
                    $images[] = $newName;
                }
            }
        }
        
        $data['receipt_image'] = json_encode($images);

        if ($this->AdminModel->updateVoucher($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Voucher updated successfully']);
        } else {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update voucher']);
        }
    }

    public function bulkUpdateVoucherDeposit()
    {
        if ($this->session->get('user_id') == '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Session expired']);
        }

        $vouchers = $this->request->getPost('vouchers');

        if (empty($vouchers) || !is_array($vouchers)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No vouchers selected']);
        }

        $success_count = 0;
        $fail_count = 0;

        foreach ($vouchers as $v) {
            $id = $v['voucher_id'];
            $data = [
                'deposited_by' => $v['deposited_by'],
                'deposit_date' => $v['deposit_date'],
                'deposit_place' => $v['deposit_place'],
            ];

            if ($this->AdminModel->updateVoucher($id, $data)) {
                $success_count++;
            } else {
                $fail_count++;
            }
        }

        return $this->response->setJSON([
            'status' => 'success', 
            'message' => "Bulk update completed. Success: $success_count, Failed: $fail_count"
        ]);
    }

    public function deleteChallanImage()
    {
        $id = $this->request->getPost('voucher_id');
        $imageName = $this->request->getPost('image_name');

        $existingVoucher = $this->db->table('voucher')->where('id', $id)->get()->getRow();
        if (!$existingVoucher || empty($existingVoucher->receipt_image)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Voucher or image not found']);
        }

        $images = json_decode($existingVoucher->receipt_image, true);
        if (!is_array($images)) {
            $images = [$existingVoucher->receipt_image];
        }

        // Find and remove the image
        if (($key = array_search($imageName, $images)) !== false) {
            unset($images[$key]);
            
            // Delete physical file
            $filePath = ROOTPATH . 'public/assets/uploads/receipts/' . $imageName;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $updatedImages = array_values($images);
        $updatedData = [
            'receipt_image' => empty($updatedImages) ? '' : json_encode($updatedImages)
        ];

        if ($this->AdminModel->updateVoucher($id, $updatedData)) {
            return $this->response->setJSON([
                'status' => 'success', 
                'message' => 'Image deleted successfully',
                'new_data' => $updatedData['receipt_image']
            ]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update database']);
        }
    }




    public function export_voucher_excel()
    {
        try {
            // Get filter values with default fallback
            $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
            $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
            $do_no = $this->request->getVar('do_no');
            $chalan_status = $this->request->getVar('chalan_status');
            $payment_status = $this->request->getVar('payment_status');
            $deposited_status = $this->request->getVar('deposited_status');
    
            // Fetch data with all filters
            $despatchData = $this->AdminModel->despatch_dtls1(
                $from_date,
                $to_date,
                $do_no,
                $chalan_status,
                $payment_status,
                $deposited_status
            );
    
            // Check if data exists
            if (empty($despatchData)) {
                return $this->response->setStatusCode(404)->setBody("No data found for the given filters.");
            }
    
            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            // Set Column Headers (matching your database structure)
            $headers = [
                'A1' => 'Sl No',
                'B1' => 'Date',
                'C1' => 'DO No',
                'D1' => 'Vehicle No',
                'E1' => 'Challan No',
                'F1' => 'Quantity',
                'G1' => 'Ref No',
                'H1' => 'Rest Amount',
                'I1' => 'Rate',
                'J1' => 'Shortage',
                'K1' => 'Freight',
                'L1' => 'Shortage Price',
                'M1' => 'Diesel Price',
                'N1' => 'Diesel Qty',
                'O1' => 'Total Diesel Rate',
                'P1' => 'Driver Expense',
                'Q1' => 'Total Deduction',
                'R1' => 'Net Amount',
                'S1' => 'Added',
                'T1' => 'Added By',
                'U1' => 'Added Date',
                'V1' => 'TDS',
                'W1' => 'Other Deduction',
                'X1' => 'Payment Status',
                'Y1' => 'Received Date'
            ];
    
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
    
            // Fill Data (using correct database field names)
            $row = 2;
            $i = 1;
            foreach ($despatchData as $des) {
                $sheet->setCellValue('A' . $row, $i++);
                $sheet->setCellValue('B' . $row, isset($des->des_date) ? date('d-m-Y', strtotime($des->des_date)) : '');
                $sheet->setCellValue('C' . $row, $des->doreg_no ?? ''); // From JOIN
                $sheet->setCellValue('D' . $row, $des->vehicle_number ?? ''); // From JOIN
                $sheet->setCellValue('E' . $row, $des->challan_no ?? '');
                $sheet->setCellValue('F' . $row, $des->quantity ?? '');
                $sheet->setCellValue('G' . $row, $des->ref_no ?? '');
                $sheet->setCellValue('H' . $row, $des->rest_amount ?? '');
                $sheet->setCellValue('I' . $row, $des->rate ?? ''); // From JOIN
                $sheet->setCellValue('J' . $row, $des->shortage ?? '');
                $sheet->setCellValue('K' . $row, $des->freight ?? '');
                $sheet->setCellValue('L' . $row, $des->shortage_price ?? '');
                $sheet->setCellValue('M' . $row, $des->dieselPrice ?? '');
                $sheet->setCellValue('N' . $row, $des->dieselQty ?? '');
                $sheet->setCellValue('O' . $row, $des->totaldieselRate ?? '');
                $sheet->setCellValue('P' . $row, $des->driver_expence ?? '');
                $sheet->setCellValue('Q' . $row, $des->total_deduction ?? '');
                $sheet->setCellValue('R' . $row, $des->net_amount ?? '');
                $sheet->setCellValue('S' . $row, isset($des->deposited) ? ($des->deposited == 1 ? 'Yes' : 'No') : 'No');
                $sheet->setCellValue('T' . $row, $des->deposit_by ?? '');
                $sheet->setCellValue('U' . $row, isset($des->deposit_date) && $des->deposit_date ? date('d-m-Y', strtotime($des->deposit_date)) : '');
                $sheet->setCellValue('V' . $row, $des->tds ?? '');
                $sheet->setCellValue('W' . $row, $des->other_deduction ?? '');
                $sheet->setCellValue('X' . $row, isset($des->payment_status) ? ($des->payment_status == 1 ? 'Paid' : 'Unpaid') : 'Unpaid');
                $sheet->setCellValue('Y' . $row, isset($des->received_date) && $des->received_date ? date('d-m-Y', strtotime($des->received_date)) : '');
                $row++;
            }
    
            // Set Auto Width for columns
            foreach (range('A', 'Y') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
    
            // Output to Browser
            $filename = 'Voucher_Report_' . date('Ymd_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    
            $writer = new XlsxWriter($spreadsheet);
            $writer->save('php://output');
            exit;
    
        } catch (\Exception $e) {
            log_message('error', 'Excel Export Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Excel Export Error: " . $e->getMessage());
        }
    }

    public function export_voucher_commition()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getVar('to_date') ?? date('Y-m-t');
        $do_no = $this->request->getVar('do_no');

        $despatchData = $this->AdminModel->despatch_dtls1($from_date, $to_date, $do_no);
        // echo "<pre>";
        // print_r($despatchData);exit;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header row
        $sheet->setCellValue('A1', 'Sl No')
            ->setCellValue('B1', 'Date')
            ->setCellValue('C1', 'DO No')
            ->setCellValue('D1', 'Vehicle No')
            ->setCellValue('E1', 'Challan No')
            ->setCellValue('F1', 'Challan Qty')
            ->setCellValue('G1', 'Receive Qty')
            ->setCellValue('H1', 'Rate')
            ->setCellValue('I1', 'Shortage')
            ->setCellValue('J1', 'Freight')
            ->setCellValue('K1', 'Shortage Price')
            ->setCellValue('L1', 'Diesel')
            ->setCellValue('M1', 'Driver Exp')
            ->setCellValue('N1', 'Total Deduction')
            ->setCellValue('O1', 'Net Amount')
            ->setCellValue('P1', 'Challan Added')
            ->setCellValue('Q1', 'Added By')
            ->setCellValue('R1', 'Added Date')
            ->setCellValue('S1', 'Commission');

        // Populate data
        $row = 2;
        $sl = 1;
        foreach ($despatchData as $des) {
            $sheet->setCellValue('A' . $row, $sl++)
                ->setCellValue('B' . $row, date('d-m-Y', strtotime($des->des_date)))
                ->setCellValue('C' . $row, $des->doreg_no)
                ->setCellValue('D' . $row, $des->vehicle_number)
                ->setCellValue('E' . $row, $des->ref_no)
                ->setCellValue('F' . $row, $des->quantity)
                ->setCellValue('G' . $row, $des->rest_amount)
                ->setCellValue('H' . $row, $des->rate)
                ->setCellValue('I' . $row, $des->shortage)
                ->setCellValue('J' . $row, $des->freight)
                ->setCellValue('K' . $row, $des->shortage_price)
                ->setCellValue('L' . $row, $des->totaldieselRate)
                ->setCellValue('M' . $row, $des->driver_expence)
                ->setCellValue('N' . $row, $des->total_deduction)
                ->setCellValue('O' . $row, $des->net_amount)
                ->setCellValue('P' . $row, ($des->deposited == 1) ? 'Yes' : 'No')
                ->setCellValue('Q' . $row, $des->deposit_by)
                ->setCellValue('R' . $row, $des->deposit_date)
                ->setCellValue('S' . $row, $des->commition);
            $row++;
        }

        // Set the headers to download the file
        $filename = 'Voucher_Commition_Report.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit();
    }



    public function getDoNumbers()
    {
        $from_date = $this->request->getPost('from_date');
        $to_date = $this->request->getPost('to_date');

        $do_numbers = $this->AdminModel->doregistration_dtls1($from_date, $to_date);

        $output = '<option value="">Select DO No.</option>';
        foreach ($do_numbers as $do) {
            $output .= '<option value="' . $do->do_registration_id . '">' . $do->do_no . '</option>';
        }
        return $this->response->setBody($output);
    }

    public function getVouchersByByDo()
    {
        // Renamed to fix possible collision or just be safe
        $do_id = $this->request->getPost('do_id');
        
        $builder = $this->db->table('voucher');
        $builder->select('voucher.id, voucher.group_code');
        $builder->join('despatch', 'despatch.voucher_id = voucher.id');
        $builder->where('despatch.do_no', $do_id);
        $builder->distinct();
        $builder->where('voucher.status', 1);
        $builder->orderBy('voucher.created_at', 'DESC');
        $vouchers = $builder->get()->getResult();

        $output = '<option value="">All Vouchers</option>';
        foreach ($vouchers as $v) {
            $output .= '<option value="' . $v->id . '">' . $v->group_code . '</option>';
        }
        return $this->response->setBody($output);
    }
    public function getDoNumbers1()
    {
        $from_date = $this->request->getPost('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getPost('to_date') ?? date('Y-m-t');

        // Fetch DO Numbers based on date range
        $do_numbers = $this->AdminModel->doregistration_dtls1($from_date, $to_date);
        // print_r($do_numbers);exit;
        // Generate HTML options for the dropdown
        $output = '<option value="">Select DO No.</option>';
        foreach ($do_numbers as $do) {
            $output .= '<option value="' . $do->do_registration_id . '">' . $do->do_no . '</option>';
        }

        return $this->response->setBody($output);
    }

    public function updateDispatch()
    {
        $id = $this->request->getPost('id');
        $rest_amount = (float)($this->request->getPost('rest_amount') ?? 0);
        $shortage = (float)($this->request->getPost('shortage') ?? 0);
        $freight = (float)($this->request->getPost('freight') ?? 0);
        $dieselQty = (float)($this->request->getPost('dieselQty') ?? 0);
        $totaldieselRate = (float)($this->request->getPost('totaldieselRate') ?? 0);
        $cash = (float)($this->request->getPost('cash') ?? 0);
        $bilty_commission = (float)($this->request->getPost('bilty_commission') ?? 0);
        $deposited = (int)($this->request->getPost('deposited') ?? 0);
        $deposit_by = $this->request->getPost('deposit_by');
        $deposit_date = $this->request->getPost('deposit_date');
        $net_amount = (float)($this->request->getPost('net_amount') ?? 0);
        $tds = (float)($this->request->getPost('tds') ?? 0);
        $otherDeduction = (float)($this->request->getPost('otherDeduction') ?? 0);
        $paymentStatus = (int)($this->request->getPost('paymentStatus') ?? 0);
        $received_date = $this->request->getPost('received_date');

        if (empty($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid dispatch ID!'
            ]);
        }

        // Fetch dispatch & DO details
        $despatch = $this->db->query("
            SELECT d.*, dr.rate, dr.shortage_qty AS min_qty, dr.shortage_rate, dr.diesel_rate, dr.diesel_payment_type, dr.cash_type, dr.special_shortage,
                COALESCE(dr.tds_percentage, 2.00) AS tds_percentage
            FROM despatch d
            LEFT JOIN do_registration dr ON dr.do_registration_id = d.do_no
            WHERE d.despatch_id = ?
        ", [$id])->getRow();

        if (!$despatch) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dispatch record not found'
            ]);
        }

        $rate = (float)($despatch->rate ?? 0);
        $shortage_rate_from_do = (float)($despatch->shortage_rate ?? 0);
        $diesel_rate = (float)($despatch->diesel_rate ?? 0);

        // Diesel calculation
        if ($totaldieselRate == 0 && $dieselQty > 0 && $diesel_rate > 0) {
            $totaldieselRate = $dieselQty * $diesel_rate;
        }

        // Shortage calculation flow
        $shortage_price = 0;
        if ($shortage > 0) {
            $chargeable_shortage = 0;
            if (($despatch->special_shortage ?? 0) == 1) {
                $chargeable_shortage = max(0, $shortage - ($despatch->min_qty ?? 0));
            } else {
                $chargeable_shortage = $shortage;
            }
            $apply_s_rate = ($shortage_rate_from_do > 0) ? $shortage_rate_from_do : $rate;
            $shortage_price = $chargeable_shortage * $apply_s_rate;
        }

        // Total deduction logic matching user's view: 
        // net = freight - shortage_price + (Own_Diesel ? diesel : -diesel) + (Own_Cash ? cash : -cash) + bilty + tds
        // Net = freight - Total_Deduction
        // So: Total_Deduction = shortage_price - (Own_Diesel ? diesel : -diesel) - (Own_Cash ? cash : -cash) - bilty - tds
        
        $d_type = strtoupper($despatch->diesel_payment_type ?? 'Party');
        $c_type = strtoupper($despatch->cash_type ?? 'Party');
        
        $diesel_effect = ($d_type === 'OWN') ? $totaldieselRate : -$totaldieselRate;
        $cash_effect = ($c_type === 'OWN') ? $cash : -$cash;
        
        // Total deduction = shortage - diesel_effect - cash_effect + bilty + tds
        $total_deduction = $shortage_price - $diesel_effect - $cash_effect + $bilty_commission + $tds;

        // Net amount
        // $net_amount = $freight - $total_deduction;

        $user_id = $this->session->get('user_id');
        
        // Update data
        $data = [
            'rest_amount'       => $rest_amount,
            'shortage'          => $shortage,
            'freight'           => $freight,
            'shortage_price'    => $shortage_price,
            'dieselPrice'       => $diesel_rate,
            'dieselQty'         => $dieselQty,
            'totaldieselRate'   => $totaldieselRate,
            'cash'              => $cash,
            'bilty_commission'  => $bilty_commission,
            'deposited'         => $deposited,
            'deposit_by'        => $deposit_by,
            'deposit_date'      => $deposit_date,
            'total_deduction'   => $total_deduction,
            'net_amount'        => $net_amount,
            'tds'               => $tds,
            'other_deduction'   => $otherDeduction,
            'payment_status'    => $paymentStatus,
            'received_date'     => $received_date,
            'updated_by'        => $user_id,
        ];

        $updated = $this->db->table('despatch')
            ->where('despatch_id', $id)
            ->update($data);

        if ($updated) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Updated successfully',
                'calculations' => [
                    'shortage' => number_format($shortage, 2, '.', ''),
                    'shortage_price' => number_format($shortage_price, 2, '.', ''),
                    'freight' => number_format($freight, 2, '.', ''),
                    'tds' => number_format($tds, 2, '.', ''),
                    'total_deduction' => number_format($total_deduction, 2, '.', ''),
                    'net_amount' => number_format($net_amount, 2, '.', '')
                ]
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to update'
        ]);
    }

    // public function create_collection_group()
    // {
    //     $ids = $this->request->getPost('ids');
    //     if (empty($ids) || !is_array($ids)) {
    //         return $this->response->setJSON(['status' => 'error', 'message' => 'No records selected']);
    //     }

    //     // Check if all selected records belong to the SAME DO
    //     $do_counts = $this->db->table('despatch')
    //         ->select('do_no, COUNT(*) as count')
    //         ->whereIn('despatch_id', $ids)
    //         ->groupBy('do_no')
    //         ->get()
    //         ->getResult();

    //     if (count($do_counts) > 1) {
    //         return $this->response->setJSON(['status' => 'error', 'message' => 'Error: Selected challans must belong to the SAME DO Number.']);
    //     }

    //     // Check if any selected records are already in a voucher
    //     $existing = $this->db->table('despatch')
    //         ->select('ref_no')
    //         ->whereIn('despatch_id', $ids)
    //         ->where('voucher_id IS NOT NULL', null, false)
    //         ->where('voucher_id !=', 0)
    //         ->get()
    //         ->getResult();

    //     if (!empty($existing)) {
    //         $challans = implode(', ', array_column($existing, 'ref_no'));
    //         return $this->response->setJSON([
    //             'status' => 'error', 
    //             'message' => "The following challans are already in a voucher: $challans. Please remove them from their current voucher first."
    //         ]);
    //     }

    //     $user_id = $this->session->get('user_id');

    //     // Check if all selected records belong to the SAME DO
    //     $do_counts = $this->db->table('despatch')
    //         ->select('do_no, COUNT(*) as count')
    //         ->whereIn('despatch_id', $ids)
    //         ->groupBy('do_no')
    //         ->get()
    //         ->getResult();

    //     if (count($do_counts) > 1) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Error: Selected challans must belong to the SAME DO Number.'
    //         ]);
    //     }

    //     // ✅ FIX: get DO number
    //     $do_no = trim($do_counts[0]->do_no);


    //     // Count existing vouchers for this DO number
    //     $existingVoucherCount = $this->db->table('voucher')
    //         ->like('group_code', $do_no . '-', 'after')
    //         ->countAllResults();

    //     // Sequence 01, 02, 03...
    //     $sequence = str_pad($existingVoucherCount + 1, 2, '0', STR_PAD_LEFT);

    //     // ✅ FINAL group code (NO "DO" TEXT)
    //     $group_code = $do_no . '-' . $sequence;

    //     // $group_code = 'GRP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    //     $this->db->transStart();

    //     // 1. Create group entry with challan IDs stored as JSON
    //     $this->db->table('voucher')->insert([
    //         'group_code' => $group_code,
    //         'challan_ids' => json_encode($ids), // Store all challan IDs
    //         'created_at' => date('Y-m-d H:i:s'),
    //         'created_by' => $user_id,
    //         'status' => 1
    //     ]);

    //     $group_id = $this->db->insertID();

    //     // 2. Update despatch records
    //     $this->db->table('despatch')
    //         ->whereIn('despatch_id', $ids)
    //         ->update(['voucher_id' => $group_id]);

    //     $this->db->transComplete();

    //     if ($this->db->transStatus() === FALSE) {
    //         return $this->response->setJSON(['status' => 'error', 'message' => 'Transaction failed']);
    //     }

    //     return $this->response->setJSON([
    //         'status' => 'success', 
    //         'group_code' => $group_code
    //     ]);
    // }

    public function create_collection_group()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No records selected']);
        }

        // 🔹 Ensure all selected despatch records belong to SAME DO
        $do_counts = $this->db->table('despatch')
            ->select('do_no, COUNT(*) as cnt')
            ->whereIn('despatch_id', $ids)
            ->groupBy('do_no')
            ->get()
            ->getResult();

        if (count($do_counts) !== 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error: Selected challans must belong to the SAME DO Number.'
            ]);
        }

        // 🔹 Get DO number from despatch
        $despatch_do_no = trim($do_counts[0]->do_no);


        // 🔹 FIX: do_registration me id nahi, do_no se match hoga
        $do_registration = $this->db->table('do_registration')
            ->select('do_no')
            ->where('do_registration_id', $despatch_do_no)
            ->get()
            ->getRow();

        if (!$do_registration) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid DO Number. DO not found in DO Registration.'
            ]);
        }

        $fixed_do_no = trim($do_registration->do_no);

        // 🔹 Check if any challan already in voucher
        $existing = $this->db->table('despatch')
            ->select('ref_no')
            ->whereIn('despatch_id', $ids)
            ->where('voucher_id IS NOT NULL', null, false)
            ->where('voucher_id !=', 0)
            ->get()
            ->getResult();

        if (!empty($existing)) {
            $challans = implode(', ', array_column($existing, 'ref_no'));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => "The following challans are already in a voucher: $challans"
            ]);
        }

        // 🔹 Generate voucher sequence DO-01, DO-02 ...
        $existingVoucherCount = $this->db->table('voucher')
            ->like('group_code', $fixed_do_no . '-', 'after')
            ->countAllResults();

        $sequence   = str_pad($existingVoucherCount + 1, 2, '0', STR_PAD_LEFT);
        $group_code = $fixed_do_no . '-' . $sequence;

        $user_id = $this->session->get('user_id');

        $this->db->transStart();

        // 🔹 Insert voucher
        $this->db->table('voucher')->insert([
            'group_code'  => $group_code,
            'challan_ids' => json_encode($ids),
            'created_at'  => date('Y-m-d H:i:s'),
            'created_by'  => $user_id,
            'status'      => 1
        ]);

        $group_id = $this->db->insertID();

        // 🔹 Update despatch records
        $this->db->table('despatch')
            ->whereIn('despatch_id', $ids)
            ->update(['voucher_id' => $group_id]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Transaction failed']);
        }

        return $this->response->setJSON([
            'status'      => 'success',
            'group_code' => $group_code
        ]);
    }



    public function get_active_groups()
    {
        try {
            $do_id = $this->request->getVar('do_id');
            
            $builder = $this->db->table('voucher');
            $builder->distinct();
            $builder->select('voucher.id, voucher.group_code, voucher.challan_ids, voucher.created_at');
            
            // If DO ID provided, filter by it
            if ($do_id) {
                // Join to despatch to find vouchers that:
                // 1. Have challans matching this DO
                // 2. Or have NO challans at all (available for any DO)
                $builder->join('despatch', 'despatch.voucher_id = voucher.id', 'left');
                $builder->groupStart();
                $builder->where('despatch.do_no', $do_id);
                $builder->orWhere('despatch.despatch_id IS NULL');
                $builder->groupEnd();
            }
            
            $groups = $builder->where('voucher.status', 1)
                ->orderBy('voucher.created_at', 'DESC')
                ->limit(50)
                ->get()
                ->getResult();

            // Decode challan_ids for each group
            foreach ($groups as $group) {
                $group->challan_ids = json_decode($group->challan_ids, true);
            }

            return $this->response->setJSON(['status' => 'success', 'groups' => $groups]);
        } catch (\Exception $e) {
            // Return actual error message to help debugging
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Query error: ' . $e->getMessage()
            ]);
        }
    }

    public function manage_collection_group()
    {
        $ids = $this->request->getPost('ids');
        $action = $this->request->getPost('action'); // 'add' or 'remove'
        $group_id = $this->request->getPost('group_id'); // Required for 'add'

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No records selected']);
        }

        $user_id = $this->session->get('user_id');
        
        $this->db->transStart();

        if ($action === 'remove') {
            // Get current challan_ids from voucher
            $voucher = $this->db->table('despatch')
                ->select('voucher_id')
                ->where('despatch_id', $ids[0])
                ->get()
                ->getRow();

            if ($voucher && $voucher->voucher_id) {
                $voucherData = $this->db->table('voucher')
                    ->select('challan_ids')
                    ->where('id', $voucher->voucher_id)
                    ->get()
                    ->getRow();

                if ($voucherData && !empty($voucherData->challan_ids)) {
                    $existingIds = json_decode($voucherData->challan_ids, true);
                    // NULL check add karo
                    if (is_array($existingIds)) {
                        $updatedIds = array_values(array_diff($existingIds, $ids));
                        
                        // Update voucher with remaining challan IDs
                        $this->db->table('voucher')
                            ->where('id', $voucher->voucher_id)
                            ->update(['challan_ids' => json_encode($updatedIds)]);
                    }
                }
            }

            // Ungroup selected records
            $this->db->table('despatch')
                ->whereIn('despatch_id', $ids)
                ->update(['voucher_id' => null, 'updated_by' => $user_id]);
                
            $message = 'Records removed from voucher successfully';

        } elseif ($action === 'add') {
            if (empty($group_id)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Voucher ID is required for adding']);
            }

            // Check if all selected records belong to the SAME DO
            $do_counts = $this->db->table('despatch')
                ->select('do_no, COUNT(*) as count')
                ->whereIn('despatch_id', $ids)
                ->groupBy('do_no')
                ->get()
                ->getResult();

            if (count($do_counts) > 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Error: Selected challans must belong to the SAME DO Number.']);
            }
            $selected_do = $do_counts[0]->do_no;

            // Check compatibility with existing voucher items (if any)
            $existing_voucher_items = $this->db->table('despatch')
                ->select('do_no')
                ->where('voucher_id', $group_id)
                ->limit(1)
                ->get()
                ->getRow();

            if ($existing_voucher_items && $existing_voucher_items->do_no != $selected_do) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Error: This voucher contains challans from a different DO Number.']);
            }

            // Check if any selected records are already in a voucher
            $existing = $this->db->table('despatch')
                ->select('ref_no')
                ->whereIn('despatch_id', $ids)
                ->where('voucher_id IS NOT NULL', null, false)
                ->where('voucher_id !=', 0)
                ->get()
                ->getResult();

            if (!empty($existing)) {
                $challans = implode(', ', array_column($existing, 'ref_no'));
                return $this->response->setJSON([
                    'status' => 'error', 
                    'message' => "The following challans are already in a voucher: $challans. Please remove them from their current voucher first."
                ]);
            }

            // Check if group exists and get current challan_ids
            $voucherData = $this->db->table('voucher')
                ->select('challan_ids')
                ->where('id', $group_id)
                ->get()
                ->getRow();

            if (!$voucherData) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid Voucher ID']);
            }

            // Merge new IDs with existing IDs
            // NULL check aur empty check dono karo
            $existingIds = [];
            if (!empty($voucherData->challan_ids)) {
                $decoded = json_decode($voucherData->challan_ids, true);
                if (is_array($decoded)) {
                    $existingIds = $decoded;
                }
            }
            
            $updatedIds = array_unique(array_merge($existingIds, $ids));

            // Update voucher with new challan IDs
            $this->db->table('voucher')
                ->where('id', $group_id)
                ->update(['challan_ids' => json_encode($updatedIds)]);

            // Add records to group
            $this->db->table('despatch')
                ->whereIn('despatch_id', $ids)
                ->update(['voucher_id' => $group_id, 'updated_by' => $user_id]);
                
            $message = 'Records added to voucher successfully';
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid action']);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            $error = $this->db->error();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Transaction failed: ' . $error['message']]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $message]);
    }

    public function updateDispatch1()
    {
        $id = $this->request->getPost('id');
        $challan_no = $this->request->getPost('challan_no');

        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid despatch ID!']);
        }

        $data = [
            'challan_no' => $challan_no
        ];

        $builder = $this->db->table('despatch');
        $updated = $builder->where('despatch_id', $id)->update($data);

        if ($updated) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Updated successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update']);
        }
    }

    public function despatch_entry()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');


        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['doregistration'] = $this->AdminModel->doregistration_dtls();
        $data['despatch'] = $this->AdminModel->despatch_dtls($from_date, $to_date);
        //print_r($data['despatch']);exit;
        $data['date'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
        ];


        return view('admin/despatch_entry_vw', $data);
    }
    public function delete_despatch()
    {
        if ($this->session->get('user_id')) {
            // Get the current user ID
            $user_id = $this->session->get('user_id');
            $despatch_id = $this->request->getUri()->getSegment(3); // Get the ID from the URL segment

            if (!empty($despatch_id)) {
                // Update the despatch record with deletion details
                $data = [
                    'deleted_by' => $user_id,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ];
                // print_r($data);
                // exit;
                $this->db->table('despatch')->where('despatch_id', $despatch_id)->update($data);

                // Log the deletion activity in the activity_logs table
                $activity_log = [
                    'user_id' => $user_id, // User who performed the action
                    'menu' => 'delete_despatch', // Menu/action name
                    'action' => 'delete', // Action type
                    'model' => 'despatch', // Affected model/table
                    'model_id' => $despatch_id, // ID of the despatch deleted
                    'changes' => json_encode($data), // Details of the changes
                    'created_at' => date('Y-m-d H:i:s'), // Timestamp
                ];
                $this->db->table('activity_logs')->insert($activity_log);

                // Redirect with a success message
                return redirect()->to('Admin/despatch_entry')->with('success', 'Despatch deleted successfully.');
            } else {
                // Redirect with an error message if no ID is provided
                return redirect()->to('Admin/despatch_entry')->with('error', 'Invalid despatch ID.');
            }
        } else {
            // Redirect to login if the user is not logged in
            return redirect()->to('admin/');
        }
    }

    public function delete_multiple_despatch()
    {
        if ($this->session->get('user_id')) {
            // Get the current user ID
            $user_id = $this->session->get('user_id');
            $ids = $this->request->getPost('select_del[]'); // Array of IDs to delete

            if (!empty($ids)) {
                foreach ($ids as $id) {
                    // Update the despatch record with deletion details
                    $data = [
                        'deleted_by' => $user_id,
                        'deleted_at' => date('Y-m-d H:i:s'),
                    ];
                    $this->db->table('despatch')->where('despatch_id', $id)->update($data);

                    // Log the deletion activity in activity_logs
                    $activity_log = [
                        'user_id' => $user_id, // User who performed the action
                        'menu' => 'delete_multiple_despatch', // Menu/action name
                        'action' => 'delete', // Action type
                        'model' => 'despatch', // Affected model/table
                        'model_id' => $id, // ID of the despatch deleted
                        'changes' => json_encode($data), // Details of the changes
                        'created_at' => date('Y-m-d H:i:s'), // Timestamp
                    ];
                    $this->db->table('activity_logs')->insert($activity_log);
                }

                // Redirect with a success message
                return redirect()->to('Admin/despatch_entry')->with('success', 'Selected despatches deleted successfully.');
            } else {
                // Redirect with an error message if no IDs were selected
                return redirect()->to('Admin/despatch_entry')->with('error', 'Please select despatches to delete.');
            }
        } else {
            // Redirect to login if the user is not logged in
            return redirect()->to('admin/');
        }
    }


    public function delete_multiple_diesel()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $ids = $this->request->getPost('select_del[]'); // Assuming IDs are posted as an array

            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $data = [
                        'deleted_by' => $user_id,
                        'deleted_at' => date('Y-m-d H:i:s'),
                    ];
                    $this->db->table('diselentry')->where('diselentry_id', $id)->update($data);
                    $activity_log = [
                        'user_id' => $user_id, // User who performed the action
                        'menu' => 'delete_multiple_diesel', // Menu/action name
                        'action' => 'delete', // Action type
                        'model' => 'diselentry', // Affected model/table
                        'model_id' => $id, // ID of the diesel entry deleted
                        'changes' => json_encode($data), // Details of the changes
                        'created_at' => date('Y-m-d H:i:s'), // Timestamp
                    ];
                    $this->db->table('activity_logs')->insert($activity_log);
                }
                return redirect()->to('Admin/diesel_entry')->with('success', 'Selected diesel entries deleted successfully.');
            } else {
                return redirect()->to('Admin/diesel_entry')->with('error', 'Please select diesel entries to delete.');
            }
        } else {
            return redirect()->to('admin/');
        }
    }





    function insert_despatch_entry()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $data = [
            'do_no' => $this->request->getPost('do_no'),
            'vehicle_no' => $this->request->getPost('vehicle_no'),
            'quantity' => $this->request->getPost('quantity'),
            'ref_no' => $this->request->getPost('ref_no'),
            'des_date' => $this->request->getPost('date'),

        ];
        $menu = $this->request->getUri()->getSegment(2);
        $user_id = $this->session->get('user_id');
        
        $this->db->table('despatch')->insert($data);
        $this->logActivity($user_id, 'create', 'despatch', $this->db->insertID(), ['data' => $data], $menu);
        return redirect()->to('admin/despatch_entry');
    }
    public function excel_despatch()
    {
        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            $filePath      = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Choose reader based on file type
            if ($fileExtension == 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();

            $data = [];
            // ✅ Read each cell EXACTLY as shown in Excel (prevents number changes)
            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = trim($cell->getFormattedValue());  // Preserve text format
                }
                $data[] = $rowData;
            }

            // Process each row
            foreach ($data as $row) {

                // ---------- DATE ----------
                $des_date = $row[0] ?? '';
                if (!empty($des_date)) {
                    $date_parts = explode('/', $des_date);
                    if (
                        count($date_parts) == 3 &&
                        checkdate($date_parts[1], $date_parts[0], $date_parts[2])
                    ) {
                        // Convert dd/mm/yyyy → yyyy-mm-dd
                        $date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                    } else {
                        // Fallback if Excel already gave proper date
                        $date = date('Y-m-d', strtotime($des_date));
                    }
                } else {
                    $date = null;
                }

                // ---------- VEHICLE ----------
                $vehicle_no = trim($row[1] ?? '');
                $vehicle    = $this->db
                    ->query("SELECT * FROM vehicle WHERE vehicle_no = ?", [$vehicle_no])
                    ->getRow();

                // ---------- DO NO ----------
                $dono  = trim($row[2] ?? '');  // ✅ leading zeros preserved
                $doreg = $this->db
                    ->query("SELECT * FROM do_registration WHERE do_no = ?", [$dono])
                    ->getRow();

                // ---------- INSERT ----------
                if (!empty($vehicle) && !empty($doreg)) {
                    $insertData = [
                        'des_date'   => $date,
                        'vehicle_no' => $vehicle->id,
                        'do_no'      => $doreg->do_registration_id,
                        'quantity'   => $row[3] ?? null,
                        'ref_no'     => $row[4] ?? null,
                    ];

                    $this->db->table('despatch')->insert($insertData);
                }
            }

            return redirect()->to(base_url('/Admin/despatch_entry'))
                            ->with('success', 'Despatch data uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload the file.');
    }

    public function getOldData($table, $id)
    {
        return $this->db->table($table)->where('despatch_id', $id)->get()->getRowArray();
    }

    function edit_despatch_entry()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $despatch_id = $this->request->getPost('despatch_id');
        $oldData = $this->getOldData('despatch', $despatch_id);

        $data = [
            'do_no' => $this->request->getPost('do_no'),
            'vehicle_no' => $this->request->getPost('vehicle_no'),
            'quantity' => $this->request->getPost('quantity'),
            'ref_no' => $this->request->getPost('ref_no'),
            'des_date' => $this->request->getPost('date'),
        ];

        $changes = $this->getChanges($oldData, $data);
        $user_id = $this->session->get('user_id');
        $menu = $this->request->getUri()->getSegment(2);

        $this->db->table('despatch')->update($data, ['despatch_id' => $despatch_id]);
        $this->logActivityy($user_id, 'update', 'despatch', $despatch_id, $changes, $menu);

        return redirect()->to('admin/despatch_entry');
    }
    function edit_despatchd()
    {
        $despatch_id = $this->request->getVar('did');
        $despatch = $this->AdminModel->single_despatch_dtls($despatch_id);
        $vehicle = $this->AdminModel->Getvehicle();
        $doregistration = $this->AdminModel->doregistration_dtls();

        foreach ($despatch as $des) {
        }
    ?>

        <form action="<?php echo base_url(); ?>/Admin/edit_despatch_entry" enctype="multipart/form-data" method="post">
            <div class="uk-margin-bottom">
                <lable>Vehicle No</lable>

                <input type="hidden" name="despatch_id" value="<?= $des->despatch_id; ?>" />

                <select class="form-control" id="single1" name="vehicle_no" required>
                    <option value="">Select Vehicle</option>
                    <?php foreach ($vehicle as $vec) { ?>
                        <option <?php if ($vec->id == $des->vehicle_no) {
                                    echo "selected";
                                } ?> value="<?= $vec->id ?>"><?= $vec->vehicle_no; ?></option>
                    <?php } ?>
                </select>
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('vehicle_no'); ?></span><?php } ?>
            </div>

            <div class="uk-margin-bottom">
                <input type="date" name="date" class="form-control" value="<?= $des->des_date ?>" required />
            </div>

            <div class="uk-margin-bottom">
                <lable>Quantity </lable>
                <input type="text" name="quantity" placeholder="enter Quantity" id="quantity" class="form-control" value="<?= $des->quantity; ?>" required />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('quantity'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <lable>DO No</lable>
                <select class="form-control" name="do_no" required>
                    <option value="">Select Do Number</option>
                    <?php foreach ($doregistration as $dor) { ?>
                        <option <?php if ($dor->do_registration_id == $des->do_no) {
                                    echo "selected";
                                } ?> value="<?= $dor->do_registration_id ?>"><?= $dor->do_no; ?></option>
                    <?php } ?>
                </select>
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('do_no'); ?></span><?php } ?>
            </div>
            <div class="uk-margin-bottom">
                <lable>Ref No.</lable>
                <input type="text" name="ref_no" placeholder="enter Ref No.  " id="ref_no" class="form-control" value="<?= $des->ref_no; ?>" required />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('ref_no'); ?></span><?php } ?>
            </div>

            <div class="uk-margin-bottom">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>


    <?php
    }

    public function download_despatch_excel()
    {
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        $despatch = $this->AdminModel->despatch_dtls($from_date, $to_date);

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Sl no',
            'Vehicle No',
            'Date',
            'Quantity',
            'DO No',
            'Ref No',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($despatch as $index => $des) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $des->vehicle_number);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($des->des_date)));
            $sheet->setCellValue('D' . $row, $des->quantity);
            $sheet->setCellValue('E' . $row, $des->doreg_no);
            $sheet->setCellValue('F' . $row, $des->ref_no);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'despatch_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    function Statutory_Entry()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Vehicle();
        $data['satutary'] = $this->AdminModel->satutary_dtls();
        return view('admin/Statutory_Entry_vw', $data);
    }
    function deletesat()
    {

        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);
            $this->db->table('statutory')->delete(array('statutory_id' => $segment));
            return redirect()->to('Admin/Statutory_Entry');
        } else {
            return redirect()->to('admin/');
        }
    }
    // public function editsat()
    // {
    //     if (!$this->session->get('user_id')) {
    //         return redirect()->to('Admin');
    //     }
    
    //     $statutory_id = $this->request->getUri()->getSegment(3); // Get ID from URL
    
    //     // Fetch statutory details with vehicle number
    //     $data['statutory'] = $this->db->table('statutory')
    //         ->select('statutory.*, vehicle.vehicle_no')
    //         ->join('vehicle', 'vehicle.id = statutory.vehicle_id', 'left')
    //         ->where('statutory.statutory_id', $statutory_id)
    //         ->get()
    //         ->getRow();
            
    //         // echo "<pre>";
    //         // print_r($data['statutory']);
    //         // echo "</pre>";
    //         // exit;
    
    //     if (!$data['statutory']) {
    //         return redirect()->to('Admin/Statutory_Entry')->with('error', 'No data found.');
    //     }
    
    //     return view('admin/Statutory_Entry_vw', $data);
    // }
    public function editsat($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }
    
        $statutory = $this->db->table('statutory')
            ->select('statutory.*, vehicle.vehicle_no')
            ->join('vehicle', 'vehicle.id = statutory.vehicle_id', 'left')
            ->where('statutory.statutory_id', $id)
            ->get()
            ->getRow();
    
        if (!$statutory) {
            return $this->response->setJSON(['success' => false]);
        }
    
        return $this->response->setJSON([
            'success' => true,
            'data' => $statutory
        ]);
    }
    
    public function update_Statutory()
    {
        if (!$this->session->get('user_id')) {
            return redirect()->to('Admin');
        }

        $statutory_id = $this->request->getPost('statutory_id');
        $vehicle_id   = $this->request->getPost('vehicle_no');
        $expence_type = $this->request->getPost('type');
        $amount       = $this->request->getPost('Amount');
        $exp_date     = $this->request->getPost('exp_date');
        $done_by      = $this->request->getPost('done_by');

        $builder = $this->db->table('statutory');
        $builder->selectMax('expary_date');
        $builder->where('vehicle_id', $vehicle_id);
        $builder->where('expence_type', $expence_type);
        $query = $builder->get()->getRow();
        $old_max_expiry = $query->expary_date ?? '0000-00-00';

        $data = [
            'vehicle_id'   => $vehicle_id,
            'expence_type' => $expence_type,
            'expary_date'  => $exp_date,
            'amount'       => $amount,
            'done_by'      => $done_by,
        ];
        // echo'<pre>';
        // print_r($data);
        // exit;
        // 🔹 File upload handling
        $docType = $expence_type; // normalize case
        $file = $this->request->getFile($docType);

        $documentColumnMap = [
            'road_tax'        => 'road_tax_doc',
            'insurance'       => 'insurance_doc',
            'Fitness'         => 'fitness_doc',
            'permit'          => 'permit_doc',
            'national_permit' => 'national_permit_doc',
            'pucc'            => 'PUCC_doc'
        ];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/documents/', $fileName);
            $filePath = $fileName;

            if (isset($documentColumnMap[$docType])) {
                $data[$documentColumnMap[$docType]] = $filePath;
            }
        }
        // echo'<pre>';
        // print_r($data[$documentColumnMap[$docType]]);
        // exit;

        // 🔹 Update statutory record
        $builder = $this->db->table('statutory');
        $builder->where('statutory_id', $statutory_id);
        $updated = $builder->update($data);

        if ($exp_date > $old_max_expiry) {
            $vehicleColumnMap = [
                'road_tax'        => 'tax_exp_date',
                'Fitness'         => 'fitness_exp_date',
                'insurance'       => 'ins_exp_date',
                'permit'          => 'permit_exp_date',
                'national_permit' => 'npermit_exp_date',
                'AMC'             => 'amc_expary',
                'I3MS'             => 'i3ms_expary',
                'KHANIJ'          => 'khanij_expiri',
            ];
    
            if (isset($vehicleColumnMap[$expence_type])) {
                $this->db->table('vehicle')
                    ->where('id', $vehicle_id)
                    ->update([$vehicleColumnMap[$expence_type] => $exp_date]);
            }
        }

        if ($updated) {
            return redirect()->to('admin/Statutory_Entry')->with('success', 'Updated successfully.');
        } else {
            return redirect()->to('admin/Statutory_Entry')->with('error', 'Update failed or no changes made.');
        }
    }

    public function insert_Statutory()
    {
        $vehicle_id = $this->request->getVar('vehicle_no');
        $expence_type = $this->request->getVar('type');
        $amount = $this->request->getVar('Amount');
        $exp_date = $this->request->getVar('exp_date');
        $done_by = $this->request->getVar('done_by');
    
        // 1. Get max expiry date before inserting
        $builder = $this->db->table('statutory');
        $builder->selectMax('expary_date');
        $builder->where('vehicle_id', $vehicle_id);
        $builder->where('expence_type', $expence_type);
        $query = $builder->get()->getRow();
        $old_max_expiry = $query->expary_date ?? '0000-00-00';
    
        // 2. Prepare insert data
        $data = [
            'vehicle_id'   => $vehicle_id,
            'expence_type' => $expence_type,
            'expary_date'  => $exp_date,
            'amount'       => $amount,
            'done_by'      => $done_by,
        ];
    
        // 3. Handle file upload
        $docType = $expence_type; // Same as 'type'
        $file = $this->request->getFile($docType);
        
        $documentColumnMap = [
            'road_tax'        => 'road_tax_doc',
            'insurance'       => 'insurance_doc',
            'Fitness'         => 'fitness_doc',
            'permit'          => 'permit_doc',
            'national_permit' => 'national_permit_doc',
            'pucc'            => 'PUCC_doc'
        ];
    
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/documents/', $fileName);
            $filePath = $fileName;
    
            if (isset($documentColumnMap[$docType])) {
                $data[$documentColumnMap[$docType]] = $filePath;
            }
        }
    
        // 4. Insert into statutory table
        $this->db->table('statutory')->insert($data);
    
        // 5. Update vehicle table if new expiry > old expiry
        if ($exp_date > $old_max_expiry) {
            $vehicleColumnMap = [
                'road_tax'        => 'tax_exp_date',
                'Fitness'         => 'fitness_exp_date',
                'insurance'       => 'ins_exp_date',
                'permit'          => 'permit_exp_date',
                'national_permit' => 'npermit_exp_date',
                'AMC'             => 'amc_expary',
                'I3MS'             => 'i3ms_expary',
                'KHANIJ'          => 'khanij_expiri',
            ];
    
            if (isset($vehicleColumnMap[$expence_type])) {
                $this->db->table('vehicle')
                    ->where('id', $vehicle_id)
                    ->update([$vehicleColumnMap[$expence_type] => $exp_date]);
            }
        }
    
        return redirect()->to('admin/Statutory_Entry');
    }



    function Subadmin()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allsubadmin'] = $this->AdminModel->GetAllCustomer(2);
            // echo'<pre>';
            // print_r($data['allsubadmin']);
            // exit;
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            return view('admin/sub_admin_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }
    function role()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            if ($this->session->get('user_type') != 1 and $this->session->get('user_type') != 2) {
                return redirect()->to('admin/');
            }

            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allsubadmin'] = $this->AdminModel->GetAllCustomer(2);

            $id = $this->request->getVar('id');
            $role = $this->request->getVar('role[]');

            $job = implode(',', $role);

            $data = [
                'roles' => $job,
            ];

            $this->AdminModel->updateUser($data, $id);

            return redirect()->to('admin/Subadmin');
        } else {
            return redirect()->to('admin/');
        }
    }

    
    function addsubadmin()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $menu = $this->request->getUri()->getSegment(2);
            if (!in_array($this->session->get('user_type'), [1, 2])) {
                return redirect()->to('admin/');
            }

            // Prepare necessary data for the view
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allsubadmin'] = $this->AdminModel->GetAllCustomer(2);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

            // Define validation rules
            $rules = [
                'name' => 'required|min_length[3]',
                'contact' => 'required|numeric|exact_length[10]|is_unique[user.contact_no]',
                'whatsapp' => 'required|numeric|exact_length[10]|is_unique[user.whatsapp_no]',
                'username' => 'required|max_length[10]|is_unique[user.user_name]',
                'password' => 'required|min_length[6]',
            ];

            // Validate input
            if ($this->validate($rules)) {
                // Handle file upload
                $file = $this->request->getFile('img');
                $imagename = "";
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $imagename = $file->getRandomName();
                    $file->move('uploads/', $imagename);
                }

                // Prepare data for insertion
                $data = [
                    'full_name' => $this->request->getVar('name'),
                    'user_name' => $this->request->getVar('username'),
                    'contact_no' => $this->request->getVar('contact'),
                    'whatsapp_no' => $this->request->getVar('whatsapp'),
                    'location_id' => $this->request->getVar('location'),
                    'password' => base64_encode(base64_encode($this->request->getVar('password'))),
                    'profile_image' => $imagename,
                    'created_by' => $user_id,
                    'created_date' => date('Y-m-d H:i:s'),
                    'status' => 1,
                    'user_type' => 2,
                ];

                // Insert new sub-admin into the database
                $this->AdminModel->adduser($data);

                // Log the creation
                $this->logActivity($user_id, 'create', 'user', $this->db->insertID(), ['data' => $data], $menu);

                // Redirect to the sub-admin list
                $this->session->setFlashdata('msg', 'Sub-admin added successfully.');
                return redirect()->to('admin/Subadmin');
            } else {
                // Validation failed; show errors
                $data['validation'] = $this->validator;
                echo view('admin/sub_admin_vw', $data);
            }
        } else {
            return redirect()->to('admin/');
        }
    }

    // Helper function to log activity
    private function logActivity($userId, $action, $model, $modelId, $details, $menu)
    {
        $data = [
            'user_id' => $userId,
            'menu' => $menu,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => json_encode($details),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('activity_logs')->insert($data);
    }



    function deleteSubadmin()
    {
        if ($this->session->get('user_id')) {
            // Get current user and sub-admin ID
            $sub_id = $this->request->getVar('user_id');
            $user_id = $this->session->get('user_id');

            // Update the sub-admin's record as deleted
            $data = [
                'deleted_by' => $user_id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            $this->AdminModel->updateUser($data, $sub_id);
            $data1 = [
                'deleted_by' => $sub_id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            // Log the deletion activity in activity_logs
            $activity_log = [
                'user_id' => $user_id, // User who performed the action
                'menu' => 'deleteSubadmin', // Menu/action name
                'action' => 'delete', // Action type
                'model' => 'user', // Affected model/table
                'model_id' => $sub_id, // ID of the sub-admin deleted
                'changes' => json_encode($data1), // Details of the changes
                'created_at' => date('Y-m-d H:i:s'), // Timestamp
            ];
            $this->db->table('activity_logs')->insert($activity_log);

            // Redirect to the sub-admin listing page with a success message
            $this->session->setFlashdata('msg', 'Sub-admin deleted successfully.');
            return redirect()->to('admin/Subadmin');
        } else {
            return redirect()->to('admin/');
        }
    }


    function statusBlock()
    {
        $user_id = $this->request->getUri()->getSegment(3);
        $data = [
            'status'  => 0
        ];
        $this->AdminModel->UserStatusActive($data, $user_id);
        return redirect()->to('admin/Subadmin');
    }
    function statusActive()
    {
        $user_id = $this->request->getUri()->getSegment(3);
        $data = [
            'status'  => 1
        ];
        $this->AdminModel->UserStatusActive($data, $user_id);
        return redirect()->to('admin/Subadmin');
    }


    
    function editsubadmin()
    {
        if ($this->session->get('user_id')) {
            $user_id = $this->session->get('user_id');
            $id = $this->request->getPost('id');

            // Fetch old data before making any updates
            $oldData = $this->AdminModel->getUserById('user', $id);

            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $contact = $this->request->getPost('contact');
            $whatsapp = $this->request->getPost('whatsapp');
            $username = $this->request->getPost('username');
            $password = base64_encode(base64_encode($this->request->getVar('password')));

            $CountEmail = $this->db->query("SELECT * FROM user WHERE email='$email' AND id!='$id'")->getResult();
            $CountContact = $this->db->query("SELECT * FROM user WHERE contact_no='$contact' AND id!='$id'")->getResult();
            $CountUsername = $this->db->query("SELECT * FROM user WHERE user_name='$username' AND id!='$id'")->getResult();

            if (count($CountEmail) == 0) {
                if (count($CountContact) == 0) {
                    if (count($CountUsername) == 0) {
                        $file = $this->request->getFile('img');
                        if ($file->isValid() && !$file->hasMoved()) {
                            $imagename = $file->getRandomName();
                            $file->move('uploads/', $imagename);
                        } else {
                            $imagename = $oldData['profile_image']; // Keep old image if not updated
                        }

                        $data = [
                            'full_name' => $name,
                            'email' => $email,
                            'user_name' => $username,
                            'contact_no' => $contact,
                            'whatsapp_no' => $whatsapp,
                            'password' => $password,
                            'location_id' => $this->request->getVar('location'),
                            'profile_image' => $imagename,
                            'updated_by' => $user_id,
                            'updated_date' => date('Y-m-d H:i:s'),
                            'status' => 1,
                            'user_type' => 2
                        ];

                        // Compare old and new data for logging
                        $changes = $this->getChanges($oldData, $data);

                        // Update the user record
                        $this->AdminModel->updateUser($data, $id);
                        $menu = $this->request->getUri()->getSegment(2);
                        // Log changes in the activity_logs table
                        $this->logActivityy($user_id, 'update', 'user', $id, $changes, $menu);

                        $this->session->setFlashdata('msg', 'Sub-admin updated successfully.');
                        return redirect()->to('admin/Subadmin');
                    } else {
                        $this->session->setFlashdata('msg', 'Username already exists.');
                        $this->session->setFlashdata('uid', $id);
                    }
                } else {
                    $this->session->setFlashdata('msg', 'Contact number already exists.');
                    $this->session->setFlashdata('uid', $id);
                }
            } else {
                $this->session->setFlashdata('msg', 'Email already exists.');
                $this->session->setFlashdata('uid', $id);
            }

            return redirect()->to('admin/Subadmin');
        } else {
            return redirect()->to('admin/');
        }
    }

    // Helper Function to Compare Changes
    private function getChanges($oldData, $newData)
    {
        $changes = [];
        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }
        return $changes;
    }

    // Log Activity Function
    private function logActivityy($userId, $action, $model, $modelId, $changes, $menu)
    {
        $data = [
            'user_id' => $userId,
            'menu' => $menu,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => json_encode($changes),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('activity_logs')->insert($data);
    }

    function get_petrol_rate()
    {
        $vendor_id = $this->request->getVar('vendor_id');
        $date = $this->request->getVar('date');

        $sql = "SELECT * FROM vendor_rate WHERE vendor_id = ? AND from_date <= ? ORDER BY from_date DESC LIMIT 1";
        $vendor_rate = $this->db->query($sql, [$vendor_id, $date])->getResult();

        //print_r ($vendor_rate);exit;
        foreach ($vendor_rate as $vr) {
        }
    ?>

        <input type="text" name="rate" value="<?php if ($vr->vendor_rate != '') {
                                                    echo $vr->vendor_rate;
                                                } else {
                                                } ?>" class="form-control" />
    <?php
    }



    function billing_details()
    {
        $order_id = $this->request->getVar('orderid_id');
        $orderdtls = $this->AdminModel->inhouse_orderdtls($order_id);
        //      echo "<pre>";
        //   print_r($orderdtls);
        foreach ($orderdtls as $ard) {
        }
    ?>
        <p>
            Vehicle No:<?= $ard->vehicle_no ?> <br>
            Driver Name :<?= $ard->driver_name ?><br>
            Location :<?= $ard->location_name ?><br>
            Date :<?= $ard->date ?><br><br>
        </p>
        <table width="100%" border="0" cellpadding="5" cellspacing="0" style="border:solid 1px #ccc;" class="uk-table uk-table-small">
            <tbody>
                <tr>

                    <td style=" width:50px; border: solid 1px #ccc;">Sl no</td>
                    <td style=" width:350px; border: solid 1px #ccc;">Product Name No</td>

                    <td style=" width:150px; border: solid 1px #ccc;">Quantity </td>
                    <td style=" width:150px; border: solid 1px #ccc;">Rate</td>
                    <td style=" width:150px; border: solid 1px #ccc;">Amount</td>


                    <?php
                    $i = 1;
                    $sum = 0;
                    foreach ($orderdtls as $ord) {
                        $sum += $ord->qty * $ord->price;
                    ?>
                </tr>


                <tr>
                    <td style=" width:50px; border: solid 1px #ccc;"><?= $i++; ?></td>
                    <td style=" width:350px; border: solid 1px #ccc;"><?= $ord->item_name; ?></td>

                    <td style=" width:150px; border: solid 1px #ccc;"> <?= $ord->qty; ?> </td>
                    <td style=" width:350px; border: solid 1px #ccc;"><?= $ord->price; ?></td>
                    <td style=" width:100px; border: solid 1px #ccc;"><?= $ord->qty * $ord->price; ?> </td>

                </tr>
            <?php } ?>
            <tr>


            </tr>
            <tr>
                <td colspan="3">
                    <h3>Total</h3> <?= $sum; ?>
                </td>
            </tr>


            </tbody>
        </table>
    <?php
    }


    function Stock_Report()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $from_date = $this->request->getPost('from_date');
        $to_date = $this->request->getPost('to_date');
        $location = $this->request->getPost('location');

        if ($from_date == '') {
            $from_date = '';
        }

        if ($to_date == '') {
            $to_date = date('Y-m-t');
        }

        $data['date'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'location' => $location,
        ];


        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['stock_dtls'] = $this->AdminModel->stock_details($from_date, $to_date, $location);
        $data['locations'] = $this->db->query("SELECT * FROM location")->getResult();

        // echo "<pre>";
        // print_r($data['stock_dtls']);exit;

        return view('admin/stockReport_vw', $data);
    }

    public function export_stock_report_to_excel()
    {
        // Get request parameters
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');
        $location = $this->request->getVar('location');

        // Load the PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Fetch stock details based on the parameters
        $stock_dtls = $this->AdminModel->stock_details($from_date, $to_date, $location);

        // Set the header for the Excel columns
        $sheet->setCellValue('A1', 'Sl.#');
        $sheet->setCellValue('B1', 'Item Name');
        $sheet->setCellValue('C1', 'Item Code');
        $sheet->setCellValue('D1', 'Unit');
        $sheet->setCellValue('E1', 'Amount');
        $sheet->setCellValue('F1', 'Opening Stocks');
        $sheet->setCellValue('G1', 'Purchases Stocks');
        $sheet->setCellValue('H1', 'Transfer Stocks');
        $sheet->setCellValue('I1', 'Consume');
        $sheet->setCellValue('J1', 'Available Stock');

        // Populate the Excel file with stock data
        $row = 2;
        $i = 1;
        foreach ($stock_dtls as $stock) {
            // $available_stock = $stock->opening_stock + $stock->stock_in_store - $stock->stock_out_store;

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $stock->item_name);
            $sheet->setCellValue('C' . $row, $stock->item_code);
            $sheet->setCellValue('D' . $row, $stock->unit_short_name);
            $sheet->setCellValue('E' . $row, number_format($stock->amount, 2));
            $sheet->setCellValue('F' . $row, $stock->opening_stock);
            $sheet->setCellValue('G' . $row, $stock->purchase_stock);
            $sheet->setCellValue('H' . $row, $stock->transfer_stock);
            $sheet->setCellValue('I' . $row, $stock->consumed_stock);
            $sheet->setCellValue('J' . $row, $stock->available_stock);

            $row++;
        }

        // Output the Excel file
        $filename = 'stock_report_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    function vehicleBillNo($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('tyer_management');
        $builder->select('*');
        $builder->where('tyer_management.asign_date >=', $from_date);
        $builder->where('tyer_management.asign_date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('tyer_management.vehicle_id', $vehicle);
        }
        return $builder->get()->getResult();
    }

    function Vehicle_Ledger()
    {
        // Check if the user is logged in
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $from_date = $this->request->getPost('from_date');
        $to_date = $this->request->getPost('to_date');
        $vehicle = $this->request->getPost('vehicle_id');

        // Set default values if not provided
        if ($from_date == '') {
            $from_date = date('Y-m-01');
        }
        if ($to_date == '') {
            $to_date = date('Y-m-t');
        }
        if ($vehicle == '') {
            $vehicle = 0;
        }

        // Prepare data for the view
        $data['filter_data'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'vehicle_id' => $vehicle,
        ];

        //vehicle no show in view file
        $vehicle_bill_no = $this->vehicleBillNo($from_date, $to_date, $vehicle);

        $Billnumber = [];
        foreach ($vehicle_bill_no as $vehicle_bill) {
            $query = $this->db->query("SELECT DISTINCT bill_no FROM tyer_management WHERE tyer_sl_no = ?", [$vehicle_bill->tyer_sl_no]);
            $result = $query->getResult();
            foreach ($result as $row) {
                $Billnumber[] = $row->bill_no;
            }
        }

        // $Billnumber = array_unique($Billnumber); // Ensure unique bill numbers

        $resultCount = [];
        $billPrices = [];

        // Get tyre count and bill price for each bill
        foreach ($Billnumber as $Billnumber_single) {
            $query = $this->db->query("SELECT COUNT(*) AS tyre_count, price FROM tyer_management WHERE bill_no = ?", [$Billnumber_single]);
            $result = $query->getRow();

            if ($result) {
                $resultCount[$Billnumber_single] = $result->tyre_count;
                $billPrices[$Billnumber_single] = $result->price;
            }
        }

        // Calculate per-tyre cost and total cost
        $totalCost = 0;
        foreach ($Billnumber as $bill_no) {
            if (isset($resultCount[$bill_no]) && isset($billPrices[$bill_no]) && $resultCount[$bill_no] > 0) {
                $per_tyre_cost = $billPrices[$bill_no] / $resultCount[$bill_no];
                $totalCost += $per_tyre_cost;
            }
        }

        // echo "Total Cost for Tyres: " . $totalCost;


        // // echo '<pre>';
        // // print_r($TyerSlNo);
        // exit;

        $data['totalCost'] = $totalCost;


        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['inhouse_maintanance'] = $this->AdminModel->vehicle_inhouse($from_date, $to_date, $vehicle);
        $data['outside_maintanance'] = $this->AdminModel->vehicle_outside($from_date, $to_date, $vehicle);
        $data['satutary_data'] = $this->AdminModel->satury_data($from_date, $to_date, $vehicle);
        $data['diesel_data'] = $this->AdminModel->vehicle_deisel($from_date, $to_date, $vehicle);
        $data['despatch_data'] = $this->AdminModel->despatch_data($from_date, $to_date, $vehicle);

        $location_dtls = $this->db->query("SELECT * FROM vehicle  where  id='$vehicle'")->getResult();

        $loc_id = 0;
        foreach ($location_dtls as $loc) {
            $loc_id = $loc->location_id;
        }
        $numberofvehicle = $this->db->query("SELECT * FROM vehicle  where  location_id='$loc_id'")->getResult();
        $data['noofvehicle'] = count($numberofvehicle);

        $data['overall_expence'] = $this->AdminModel->overalexpence_data($loc_id, $from_date, $to_date);


        // Extract year and month from from_date
        $date = new DateTime($from_date);
        $year = $date->format('Y');
        $month = $date->format('m');

        // Initialize array to hold driver salary details
        $data['alldriver'] = [];

        // Fetch driver data and accumulate salary details
        $driver_data = $this->AdminModel->driver_data($from_date, $to_date, $vehicle);
        // echo "<pre>";
        // print_r($driver_data);exit;
        foreach ($driver_data as $driver) {
            $data['alldriver'][] = $this->AdminModel->driver_salary_details_eport($year, $month, $driver->driver);
        }

        // Return the view with data
        return view('admin/Vehicle_Ledger_vw', $data);
    }

    public function generateExcel($from_date, $to_date, $vehicle)
    {
        $date = new DateTime($from_date);
        $year = $date->format('Y');
        $month = $date->format('m');
        $driver_data = $this->AdminModel->driver_data($from_date, $to_date, $vehicle);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Sl No');
        $sheet->setCellValue('B1', 'Vehicle No');
        $sheet->setCellValue('C1', 'Total In-House Maintenance');
        $sheet->setCellValue('D1', 'Total Statutory Costs');
        $sheet->setCellValue('E1', 'Total Gross Salary');
        $sheet->setCellValue('F1', 'Total Diesel Cost');
        $sheet->setCellValue('G1', 'Total Overall Expense');

        $row = 2; // Start data rows after the header
        foreach ($driver_data as $key => $data) {
            // echo'<pre>';
            // print_r($data);
            // exit;
            // Fetch in-house maintenance cost
            $inhouse_maintanance = $this->AdminModel->vehicle_inhouse($from_date, $to_date, $data->vehicle_id);
            $total_inhouse = array_reduce($inhouse_maintanance, fn($sum, $item) => $sum + $item->price, 0);

            // Fetch statutory costs
            $satutary_data = $this->AdminModel->satury_data($from_date, $to_date, $data->vehicle_id);
            $tstatutary = array_reduce($satutary_data, fn($sum, $item) => $sum + $item->price, 0);

            // Fetch driver salary details
            $data->alldriver = [];
            $data->alldriver[] = $this->AdminModel->driver_salary_details_eport($year, $month, $data->driver);
            $nsalary = 0;



            foreach ($data->alldriver as $staf) {
                if (!empty($staf)) {
                    $from_date_obj = new DateTime($staf[0]->from_date);
                    $to_date_obj = new DateTime($staf[0]->to_date);

                    $date = new DateTimeImmutable($from_date_obj->format('Y-m-01'));
                    $curent_monthday = $date->format('t');

                    $hsd_details = $this->AdminModel->hsd_details($staf[0]->id, $year, $month);
                    $used_hsd = $hsd_details[0]->used_hsd ?? 0;
                    $diesel_rate = $hsd_details[0]->diesel_rate ?? 0;

                    $disel_entry = $this->AdminModel->vehicle_disel_details($staf[0]->assignment_vehicle_no, $staf[0]->from_date, $staf[0]->to_date);
                    $total_d_req = array_sum(array_column($disel_entry, 'diesel_for_trip'));
                    $HSD_LTR = $total_d_req - $used_hsd;
                    $hsd_amount = $HSD_LTR * $diesel_rate;

                    $trip_expense = $this->AdminModel->tripexpence($staf[0]->assignment_vehicle_no, $year, $month);
                    $total_month_expense = array_sum(array_column($trip_expense, 'day_trip_expense'));

                    $interval = $from_date_obj->diff($to_date_obj);
                    $days_count = $interval->days + 1;

                    $d_salary = $staf[0]->salary / $curent_monthday * $days_count;
                    $total_advance = isset($staf[0]->total_advance)
                        ? (float)preg_replace('/[^0-9.-]/', '', $staf[0]->total_advance)
                        : 0;

                    $tsalary = ($d_salary + $hsd_amount + $total_month_expense + $staf[0]->amount - $total_advance);
                    $nsalary += $tsalary;
                }
            }

            // Calculate diesel cost
            $diesel_data = $this->AdminModel->vehicle_deisel($from_date, $to_date, $data->vehicle_id);
            $total_diesel = array_reduce($diesel_data, fn($sum, $item) => $sum + ($item->qty * $item->rate), 0);

            // Calculate overall expense
            // $location_dtls = $this->db->query("SELECT * FROM vehicle  where  id='$vehicle'")->getResult();
            $location = $this->db->query("SELECT location_id FROM vehicle WHERE id = ?", [$data->vehicle_id])->getRow();
            $loc_id = $location->location_id ?? 0;

            $overall_expense_data = $this->AdminModel->overalexpence_data($loc_id, $from_date, $to_date);
            $numberofvehicle = $this->db->query("SELECT * FROM vehicle  where  location_id='$loc_id'")->getResult();
            $noofvehicle = count($numberofvehicle);
            $over_expence11 = array_reduce($overall_expense_data, fn($sum, $item) => $sum + $item->amount, 0);
            $over_expence = ($over_expence11 > 0 && $noofvehicle > 0) ? number_format($over_expence11 / $noofvehicle, 2) : 0;
            // Write data to the Excel sheet
            $sheet->setCellValue('A' . $row, $key + 1); // Sl No
            $sheet->setCellValue('B' . $row, $data->vehicle_no); // Vehicle No
            $sheet->setCellValue('C' . $row, $total_inhouse); // Total In-House Maintenance
            $sheet->setCellValue('D' . $row, $tstatutary); // Total Statutory Costs
            $sheet->setCellValue('E' . $row, $nsalary); // Total Gross Salary
            $sheet->setCellValue('F' . $row, $total_diesel); // Total Diesel Cost
            $sheet->setCellValue('G' . $row, $over_expence); // Total Overall Expense

            $row++;
        }

        // Set filename and initiate download
        $filename = 'Vehicle_Ledger_' . date('Ymd') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    public function getvehicledtls()
    {

        $driver_id = $this->request->getPost('staff_id');
        $date = $this->request->getPost('date');

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));

        $query = $this->db->query("
        SELECT v.vehicle_no 
        FROM driver_assignment da
        JOIN vehicle v ON da.vehicle_no = v.id
        WHERE da.driver = ? AND YEAR(da.from_date) = ? AND MONTH(da.from_date) = ?
    ", array($driver_id, $year, $month));

        $result = $query->getResult();

        if (!empty($result)) {
            foreach ($result as $row) {
                echo '<label>Vehicle</label>';
                echo '<input type="text" readonly class="uk-input" value="' . htmlspecialchars($row->vehicle_no) . '"/>';
            }
        } else {
            echo '<label>Vehicle</label>';
            echo '<input type="text" readonly class="uk-input" value="No vehicle found"/>';
        }
    }


    public function openinghsddtl()
    {
        $vehicle_id = $this->request->getPost('vehicle_id');

        // Fetch the last entry for the specified vehicle_id
        $query = $this->db->table('driver_assignment')
            ->select('closing_hsd, closing_km')
            ->where('vehicle_no', $vehicle_id)
            ->orderBy('id', 'DESC') // Assuming 'id' is the primary key and auto-incremented
            ->limit(1)
            ->get();

        $result = $query->getRow();

        $closing_hsd = $result ? $result->closing_hsd : '';
        $closing_km = $result ? $result->closing_km : '';
    ?>
        <div class="form-group">
            <label for="opening_hsd">Opening HSD:</label>
            <input type="text" value="<?= htmlspecialchars($closing_hsd); ?>" class="form-control" name="opening_hsd" id="opening_hsd" required>
        </div>

        <div class="form-group">
            <label for="opening_km">Opening KM:</label>
            <input type="text" value="<?= htmlspecialchars($closing_km); ?>" class="form-control" name="opening_km" id="opening_km" required>
        </div>
    <?php
    }


    function tyer_management()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

        $data['tyer_data'] = $this->db->query("
            SELECT tm.*, l.location_name, v.name, COUNT(tm.id) as qty
            FROM tyer_management tm
            LEFT JOIN location l ON tm.location_id = l.location_id
            LEFT JOIN vendor v ON tm.vendor_id = v.id
            GROUP BY tm.bill_no, l.location_name, v.name
            ORDER BY MAX(tm.date) DESC
        ")->getResult();
        $data['vendor'] = $this->AdminModel->Get_vendor();
        return view('admin/tyermanagement_vw', $data);
    }
    
    public function StockTyer_management()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to(base_url('login'));
        }

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);

        $data['tyer_list'] = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name')
            ->select('
                CASE 
                    WHEN tm.asign_date IS NOT NULL THEN "Old"
                    ELSE "New"
                END AS tyre_condition
            ', false)
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.vehicle_id', null)
            ->where('tm.status', 1)
            ->groupBy('tm.id')
            ->get()
            ->getResult();

        return view('admin/stock_tyer_management_vw', $data);
    }
    public function trashTyer_management(){
        if($this->session->get('user_id') == ''){
            return redirect()->to(base_url('login'));
        }
        $user_id = $this->session->get('user_id');
        $data['setting']=$this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['tyer_list'] = $this->db->table('tyer_management tm')
            ->select('tm.*,l.location_name')
            ->join('location l', 'l.location_id = tm.location_id','left')
            ->where('tm.status',3)
            ->get()
            ->getResult();
        return view('admin/trashTyer_management_vw',$data);
    }
    
    public function trashTyreBackToStock($id = null){
        if($this->session->get('user_id') == ''){
            return redirect()->to(base_url('login'));
        }
        $user_id = $this->session->get('user_id');
        $data['setting']=$this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['tyre_back_stock'] = $this->db->table('tyer_management')
            ->where('id',$id)
            ->update([
                'status'=>1,
                'remark'=>'Back from trash to stock'
            ]);
        $data['tyre_history'] = $this->db->table('tyer_management_history')
            ->insert([
                'tyre_id'   => $id,
                'event_type' => 1,
                'event_date'      => date('Y-m-d'),
                'remarks' => 'Tyre restored from trash to stock',
            ]);
        return redirect()->to('Admin/trashTyer_management');
    }
    
    public function getTyerDetailsByBillNo()
    {
        $bill_no  = $this->request->getPost('bill_no');
        $location = $this->request->getPost('location'); // ✅ get location from AJAX

        $builder = $this->db->table('tyer_management')
            ->select('tyer_sl_no, tyer_type')
            ->where('bill_no', $bill_no);

        // ✅ Apply location filter only if location value exists
        if (!empty($location)) {
            $builder->where('location_id', $location); 
            // If your column name is city, area, state, replace 'location'
        }

        $data = $builder->get()->getResult();

        echo json_encode($data);
    }

    public function tyreTransfer(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();

        return view('admin/tyreTransfer_vw', $data);
    }
    public function get_tyers_by_location()
    {
        $locationId = $this->request->getPost('location_id');
    
        $tyres = $this->db->table('tyer_management')
            ->select('tyer_sl_no')
            ->where('location_id', $locationId)
            ->where('status', 1) // Only available stock
            ->get()
            ->getResult();
    
        return $this->response->setJSON($tyres);
    }
    public function get_tyer_details()
    {
        $tyerSlNo = $this->request->getPost('tyer_sl_no');
    
        $tyre = $this->db->table('tyer_management')
            ->select('brand_name, model')
            ->where('tyer_sl_no', $tyerSlNo)
            ->get()
            ->getRow();
    
        if ($tyre) {
            return $this->response->setJSON([
                'brand_name' => $tyre->brand_name,
                'tyer_model' => $tyre->model
            ]);
        } else {
            return $this->response->setJSON(null);
        }
    }
    public function update_tyer_details()
    {
        $to_location = $this->request->getPost('to_location');
        $date        = $this->request->getPost('date');
        $tyer_sl_no  = $this->request->getPost('tyer_sl_no'); // array
    
        if (!empty($tyer_sl_no) && !empty($to_location) && !empty($date)) {
            foreach ($tyer_sl_no as $sl_no) {
                if (!empty($sl_no)) {
                    $updateData = [
                        'location_id'   => $to_location,
                        'transfer_date' => $date
                    ];
    
                    $this->db->table('tyer_management')
                        ->where('tyer_sl_no', $sl_no)
                        ->update($updateData);
                }
            }
    
            $this->session->setFlashdata('success', 'Tyres transferred successfully.');
        } else {
            $this->session->setFlashdata('error', 'Please fill all required fields.');
        }
        $tyres = $this->db->table('tyer_management')
                  ->where('DATE(transfer_date)', $date)
                  ->get()
                  ->getResult();

        foreach ($tyres as $tyre) {
            $data1 = [
                'tyre_id'   => $tyre->id,
                'event_type' => 2,
                'location_id' => $to_location,
                'event_date'      => $date,
                'transfer_from' => $from_location,
                'transfer_to' => $to_location,
            ];
            $this->db->table('tyer_management_history')->insert($data1);
        }
    
        return redirect()->to('Admin/tyreTransfer');
    }
    
    function addtyerbill()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['vendor'] = $this->AdminModel->Get_vendor();

        return view('admin/addtyerbill_vw', $data);
    }

    public function insert_tyer()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $vendor_id = $this->request->getVar('vendor_id');
        $date = $this->request->getVar('date');
        $bill_no = $this->request->getVar('billno');
        $price = $this->request->getVar('tamount');
        $location_id = $this->request->getVar('location');
        $brand_name = $this->request->getVar('brand_name');
        $model = $this->request->getVar('model');

        $tyer_sl_nos = $this->request->getVar('tyer_sl_no'); // Array of tyer serial numbers
        $tyer_types = $this->request->getVar('tyer_type'); // Array of tyer types

        $existingBill = $this->db->table('tyer_management')
            ->where('bill_no', $bill_no)
            ->get()
            ->getRow();

        if ($existingBill) {
            return redirect()->back()->withInput()->with('errors', ['billno' => 'Bill number already exists']);
        }
        // Loop through each tyer_sl_no and tyer_type pair and insert them
        foreach ($tyer_sl_nos as $index => $tyer_sl_no) {
            $tyer_type = $tyer_types[$index];

            // Insert data into the database
            $data = [
                'location_id' => $location_id,
                'brand_name' => $brand_name,
                'tyer_type' => $tyer_type,
                'model' => $model,
                'tyer_sl_no' => $tyer_sl_no,
                'vendor_id' => $vendor_id,
                'bill_no' => $bill_no,
                'price' => $price,
                'status' => 1,
                'date' => $date,
            ];
            $this->db->table('tyer_management')->insert($data);
        }
        $tyres = $this->db->table('tyer_management')
                  ->where('date', $date)
                  ->get()
                  ->getResult();

        foreach ($tyres as $tyre) {
            $data1 = [
                'tyre_id'   => $tyre->id,   // assuming your PK in tyer_management is 'id'
                'event_type' => 1,
                'location_id' => $location_id,
                'event_date'      => $date,
                'vendor_id' => $vendor_id,
            ];

            $this->db->table('tyer_management_history')->insert($data1);
        }

        return redirect()->to('admin/tyer_management');
    }


    public function delete_tyer()
    {
        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);

            $tyerdata = $this->db->query("SELECT * FROM tyer_management  where id='$segment'")->getResult();
            if (!empty($tyerdata)) {
                foreach ($tyerdata as $tyer) {
                }
                $billno = $tyer->bill_no;
            }


            $this->db->table('tyer_management')->delete(array('bill_no' => $billno));
            return redirect()->to('Admin/tyer_management');
        } else {
            return redirect()->to('admin/');
        }
    }
    



    public function delete_tyersingle()
    {
        if ($this->session->get('user_id')) {
            $segment = $this->request->getUri()->getSegment(3);

            $this->db->table('tyer_management')->delete(array('id' => $segment));
            // return redirect()->to('Admin/tyer_management');
            return redirect()->back();
        } else {
            return redirect()->to('admin/');
        }
    }




    function edit_tyer()
    {
        if ($this->session->get('user_id')) {

            $segment = $this->request->getUri()->getSegment(3);

            $tyerdata = $this->db->query("SELECT * FROM tyer_management  where id='$segment'")->getResult();

            foreach ($tyerdata as $tyer) {
            }
            $billno = $tyer->bill_no;

            $user_id = $this->session->get('user_id');
            $data['tyer_data'] = $this->db->query("SELECT * FROM tyer_management  where bill_no='$billno'")->getResult();
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['vendor'] = $this->AdminModel->Get_vendor();


            return view('admin/tyeredit_vw', $data);
        } else {
            return redirect()->to('admin/');
        }
    }

    function update_tyer()
    {
        // Check if user is logged in
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        // Retrieve form data
        $bill_no = $this->request->getVar('billno');
        $vendor_id = $this->request->getVar('vendor_id');
        $date = $this->request->getVar('date');
        $price = $this->request->getVar('tamount');
        $location_id = $this->request->getVar('location');
        $brand_name = $this->request->getVar('brand_name');
        $model = $this->request->getVar('model');

        // Arrays of tyre details
        $tyer_ids = $this->request->getVar('tyer_id');
        $tyer_sl_nos = $this->request->getVar('tyer_sl_no'); // Array of tyre serial numbers
        $tyer_types = $this->request->getVar('tyer_type'); // Array of tyre types

        // Check if arrays exist and are not empty
        if ($tyer_ids && $tyer_sl_nos && $tyer_types) {
            // Loop through each tyre serial number and tyre type pair and insert them
            foreach ($tyer_sl_nos as $index => $tyer_sl_no) {
                $tyer_type = $tyer_types[$index];
                $tyer_id = $tyer_ids[$index];

                // Prepare data for insertion or update
                $data = [
                    'location_id' => $location_id,
                    'brand_name' => $brand_name,
                    'tyer_type' => $tyer_type,
                    'model' => $model,
                    'tyer_sl_no' => $tyer_sl_no,
                    'vendor_id' => $vendor_id,
                    'bill_no' => $bill_no,
                    'price' => $price,
                    'status' => 1,
                    'date' => $date,
                ];

                // Insert or update based on the tyre ID
                if ($tyer_id == 0) {
                    $this->db->table('tyer_management')->insert($data);
                } else {
                    $this->db->table('tyer_management')->update($data, ['id' => $tyer_id]);
                }
            }
        }

        // Redirect to the tyre management page after update
        return redirect()->to('admin/tyer_management');
    }

    

    function Asign_Tyer()
    {

        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle_tyer();
        // echo'<pre>';
        // print_r($data['vehicle']);
        // exit;
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['tyer_data'] = $this->db->query("
                                                SELECT tm.*, l.location_name 
                                                FROM tyer_management tm
                                                LEFT JOIN location l ON tm.location_id = l.location_id
                                            ")->getResult();
        $data['vendor'] = $this->AdminModel->Get_vendor();

        return view('admin/Asign_Tyer_vw', $data);
    }



    public function downloadExcelAsign_tyer()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $data = $this->AdminModel->Getvehicle_tyer();
        header('Content-Type: text/csv');

        header('Content-Disposition: attachment; filename="vehicle_data.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, [

            'Sl.No',

            'Vehicle Number',

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

            'Rear8 Left'

        ]);


        // Write the data rows

        $sr_no = 1;

        foreach ($data as $vehic) {

            $positions = [

                'Front Right' => '',

                'Front Left' => '',

                'Rear1 Right' => '',

                'Rear1 Left' => '',

                'Rear2 Right' => '',

                'Rear2 Left' => '',

                'Rear3 Right' => '',

                'Rear3 Left' => '',

                'Rear4 Right' => '',

                'Rear4 Left' => '',

                'Rear5 Right' => '',

                'Rear5 Left' => '',

                'Rear6 Right' => '',

                'Rear6 Left' => '',

                'Rear7 Right' => '',

                'Rear7 Left' => '',

                'Rear8 Right' => '',

                'Rear8 Left' => '',

            ];


            // Populate positions with data

            foreach ($vehic['tyer_position'] as $position => $serial_no) {

                $positions[$position] = $serial_no;
            }


            // Write the row to the CSV

            fputcsv($output, [

                $sr_no++,

                $vehic['vehicle_no'],

                $positions['Front Right'],

                $positions['Front Left'],

                $positions['Rear1 Right'],

                $positions['Rear1 Left'],

                $positions['Rear2 Right'],

                $positions['Rear2 Left'],

                $positions['Rear3 Right'],

                $positions['Rear3 Left'],

                $positions['Rear4 Right'],

                $positions['Rear4 Left'],

                $positions['Rear5 Right'],

                $positions['Rear5 Left'],

                $positions['Rear6 Right'],

                $positions['Rear6 Left'],

                $positions['Rear7 Right'],

                $positions['Rear7 Left'],

                $positions['Rear8 Right'],

                $positions['Rear8 Left'],

            ]);
        }


        // Close the output stream

        fclose($output);

        exit();
    }






    public function upload_tyer_excel()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Read file
            if ($fileExtension == 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            $spreadsheet = $reader->load($filePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            // Define allowed tire positions
            $allowedPositions = [
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
                'Rear8 Left'
            ];

            // Process the data
            foreach ($data as $row) {
                $vehicle_no = $row[1];
                $vehicle = $this->db->table('vehicle')->where('vehicle_no', $vehicle_no)->get()->getRow();

                // Validate if the vehicle exists and if $row[2] matches allowed positions
                if (!empty($vehicle) && in_array($row[2], $allowedPositions)) {
                    if (!empty($row[0])) { // Assuming the first column is not empty
                        $tyreData = [
                            'vehicle_id' => $vehicle->id,
                            'tyer_position' => $row[2],
                        ];

                        // Update tyer management based on 'tyer_sl_no'
                        $this->db->table('tyer_management')->where('tyer_sl_no', $row[0])->update($tyreData);
                    }
                }
            }

            // Redirect to a success page
            return redirect()->to(base_url('/admin/Asign_Tyer'))->with('success', 'Tyer data uploaded successfully');
        } else {
            return redirect()->back()->with('error', 'File upload failed');
        }
    }






    function gettyer()
    {
        $location_id = $this->request->getVar('location_id');
        $tyer_data = $this->db->query("
                SELECT * 
                FROM tyer_management 
                WHERE location_id = $location_id 
                AND status = 1
                AND vehicle_id IS NULL
            ")->getResult();
    ?>
        <label>Select Tyer</label>
        <select class="form-control" id="single" name="tyer_id">
            <option value="">No Stock Available</option>
            <?php foreach ($tyer_data as $tyer) { ?>
                <option value="<?= $tyer->id ?>"><?= $tyer->tyer_sl_no ?></option>
            <?php } ?>
        </select>

        <!--    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />-->
        <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>-->
        <!-- Select2 -->
        <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>-->


        <!--     <script>-->
        <!--    $("#single").select2({-->
        <!--        placeholder: "Select an option",-->
        <!--        allowClear: true-->
        <!--    });-->
        <!--    </script>-->

        <!--    <style>-->
        <!--        .select2-container--default .select2-selection--single .select2-selection__arrow {-->
        <!--  height: 24px !important;-->
        <!--  position: absolute;-->
        <!--  top: 1px;-->
        <!--  right: 1px;-->
        <!--  width: 20px;-->
        <!--  top: 10px!important;-->
        <!--}-->

        <!--.select2-container--open .select2-dropdown--below {-->
        <!--  border-top: none;-->
        <!--  border-top-left-radius: 0;-->
        <!--  border-top-right-radius: 0;-->
        <!--  margin: -20px 0 0 0;-->
        <!--}-->

        <!--    </style> -->
<?php

    }
    public function asign_tyers()
{
    if ($this->session->get('user_id') == '') {
        return redirect()->to('Admin/');
    }

    $user_id = $this->session->get('user_id');
    $lvehicle_id = $this->request->getUri()->getSegment(3);

    $data['lvehicle_id'] = $lvehicle_id;
    $data['setting'] = $this->AdminModel->Settingdata();
    $data['singleuser'] = $this->AdminModel->userdata($user_id);
    $data['vehicle'] = $this->AdminModel->Getvehicle_tyer();
    $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
    $data['tyer_data'] = $this->db->query("
            SELECT tm.tyer_position, tm.asign_date, tm.tyer_type, tm.brand_name, l.location_name, tm.tyer_sl_no
            FROM tyer_management tm
            LEFT JOIN location l ON tm.location_id = l.location_id
            LEFT JOIN vehicle v ON tm.vehicle_id = v.id
            WHERE tm.vehicle_id = '$lvehicle_id';
        ")->getResult();
    $data['vendor'] = $this->AdminModel->Get_vendor();

    // All possible tyre positions
    $all_positions = [
        "Front Right", "Front Left",
        "Rear1 Right", "Rear1 Left",
        "Rear2 Right", "Rear2 Left",
        "Rear3 Right", "Rear3 Left",
        "Rear4 Right", "Rear4 Left",
        "Rear5 Right", "Rear5 Left",
        "Rear6 Right", "Rear6 Left",
        "Rear7 Right", "Rear7 Left",
        "Rear8 Right", "Rear8 Left"
    ];

    // Get already assigned positions for this vehicle
    $assigned_positions = $this->db->table('tyer_management')
        ->select('tyer_position')
        ->where('vehicle_id', $lvehicle_id)
        ->get()
        ->getResultArray();

    $assigned_positions = array_column($assigned_positions, 'tyer_position');

    // Filter out assigned ones
    $available_positions = array_diff($all_positions, $assigned_positions);

    $data['available_positions'] = $available_positions;

    return view('admin/Asign_Tyers', $data);
}


    function update_tyer_data()
    {
        $location = $this->request->getPost('location');
        $lvehicle_id = $this->request->getVar('vehicle_id');
        $tyer_id = $this->request->getVar('tyer_id');
        $tyer_position = $this->request->getVar('tyer_position');
        $asign_date = $this->request->getVar('asign_date');

        $data = [
            'vehicle_id' => $lvehicle_id,
            'tyer_position' => $tyer_position,
            'asign_date' => $asign_date,
            'status' => 2,
        ];

        $data1 = [
            'tyre_id'   => $tyer_id,
            'event_type' => 3,
            'location_id' => $location,
            'event_date'      => $asign_date,
            'vehicle_id' => $lvehicle_id,
            'tyre_position' => $tyer_position,
        ];
        $this->db->table('tyer_management_history')->insert($data1);

        // echo "<pre>";
        //  print_r ($data);exit;

        $this->db->table('tyer_management')->update($data, ['id' => $tyer_id]);
        return redirect()->to('admin/Asign_Tyer');
    }




    function exchange_tyer_data()
    {
        $session = session();
        $lvehicle_id = $this->request->getVar('vehicle_id');
        $tyer_id = $this->request->getVar('tyer_id');
        $location = $this->request->getVar('location');
        $tyer_position = $this->request->getVar('tyer_position');

        $tyer_data = $this->db->query("SELECT * FROM tyer_management  where  vehicle_id='$lvehicle_id' AND tyer_position='$tyer_position'")->getResult();

        if (count($tyer_data) != 0) {
            $data = [

                'vehicle_id' => $lvehicle_id,
                'tyer_position' => $tyer_position,
                'asign_date' => date('Y-m-d'),
                'status' => 2,
            ];

            $this->db->table('tyer_management')->update($data, ['id' => $tyer_id]);

            foreach ($tyer_data as $tyrre) {
            }
            $data1 = [
                'location_id' => $location,
                'tyer_position' => NULL,
                'status' => 1,
                'vehicle_id' => NULL,
            ];

            $this->db->table('tyer_management')->update($data1, ['id' => $tyrre->id]);

        } else {
            $session->setFlashdata('msg1', 'No tyer Asigned Previously');
        }
        $data2 = [
            'tyre_id'   => $tyer_id,
            'event_type' => 4,
            'location_id' => $location,
            'event_date'      => date('Y-m-d'),
            'vehicle_id' => $lvehicle_id,
            'tyre_position' => $tyer_position,
        ];
        $this->db->table('tyer_management_history')->insert($data2);

        return redirect()->to('admin/Asign_Tyer');
    }


    public function tyer_report()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id     = $this->session->get('user_id');
        $location_id = $this->request->getVar('location_id');

        $data['setting']    = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location']   = $this->db->table('location')->get()->getResult();
        $data['vendor']     = $this->AdminModel->Get_vendor();

        $builder = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name', false)
            ->select('CASE 
                        WHEN th.tyre_id IS NULL THEN "New" 
                        ELSE "Old" 
                    END AS tyre_condition', false)
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->join('tyer_management_history th', 'th.tyre_id = tm.id', 'left')
            ->whereIn('tm.status', [1, 2])
            ->groupBy('tm.id');

        // Location filter
        if (!empty($location_id)) {
            $builder->where('tm.location_id', $location_id);
        }

        $data['tyer_data'] = $builder->get()->getResult();

        return view('admin/tyerreport_vw', $data);
    }

    function tyer_exchange()
    {
        $tyer_id = $this->request->getUri()->getSegment(3);
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['tyer_data'] = $this->db->query("SELECT * FROM tyer_management   where id='$tyer_id' ")->getResult();
        $data['vendor'] = $this->AdminModel->Get_vendor();
        // echo "<pre>";
        // print_r($data);exit;
        return view('admin/tyerexchange_vw', $data);
    }


    function update_tyer_report()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/update_tyer_report');
        }

        $tyer_id = $this->request->getPost('tyer_id');
        $data = [
            'status' => $this->request->getPost('status'),
            'ex_ven_id' => $this->request->getPost('vendor_id'),
            'remark' => $this->request->getPost('remark'),
            'vehicle_id'     => null,
            'tyer_position'  => null // always set to NULL
        ];
        // echo "<pre>";
        // print_r($data);exit;
        
        $this->db->table('tyer_management')
            ->where('id', $tyer_id)
            ->update($data);
        $status = $this->request->getPost('status');
        if($status == 1){
            $data1 = [
                'tyre_id'   => $tyer_id,
                'event_type' => 7,
                'event_date'      => date('Y-m-d'),
                'vendor_id' => $this->request->getPost('vendor_id'),
                'remarks' => $this->request->getPost('remark'),
            ];
            $this->db->table('tyer_management_history')->insert($data1);
        } else if($status == 4){
            $data1 = [
                'tyre_id'   => $tyer_id,
                'event_type' => 5,
                'event_date'      => date('Y-m-d'),
                'vendor_id' => $this->request->getPost('vendor_id'),
                'remarks' => $this->request->getPost('remark'),
            ];
            $this->db->table('tyer_management_history')->insert($data1);
        } else{
            $data1 = [
                'tyre_id'   => $tyer_id,
                'event_type' => 8,
                'event_date'      => date('Y-m-d'),
                'vendor_id' => $this->request->getPost('vendor_id'),
                'remarks' => $this->request->getPost('remark'),
            ];
            $this->db->table('tyer_management_history')->insert($data1);
        }
        

        return redirect()->to(base_url('/Admin/tyer_report'))->with('status', 'Market updated successfully');
    }
    function expert_excel()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $location_id = $this->request->getVar('location_id');

        $sql = "
        SELECT tyer_management.*, location.location_name 
        FROM tyer_management 
        LEFT JOIN location ON location.location_id = tyer_management.location_id 
        WHERE tyer_management.status = 1
    ";

        // Add location filter if location_id is not empty
        if (!empty($location_id)) {
            $sql .= " AND tyer_management.location_id = " . $this->db->escape($location_id);
        }

        $tyer_data = $this->db->query($sql)->getResult();

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Sl no',
            'Bill no',
            'Seriel No',
            'Location',
            'Brand Name',
            'Tyer Type',
            'Model'
        ];

        // Set the header columns
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Populate the data rows
        $rowNumber = 2;
        $slNo = 1;
        foreach ($tyer_data as $tyer) {
            $sheet->setCellValue('A' . $rowNumber, $slNo++);
            $sheet->setCellValue('B' . $rowNumber, $tyer->bill_no);
            $sheet->setCellValue('C' . $rowNumber, $tyer->tyer_sl_no);
            $sheet->setCellValue('D' . $rowNumber, $tyer->location_name);
            $sheet->setCellValue('E' . $rowNumber, $tyer->brand_name);
            $sheet->setCellValue('F' . $rowNumber, $tyer->tyer_type);
            $sheet->setCellValue('G' . $rowNumber, $tyer->model);
            $rowNumber++;
        }

        // Set the filename and save the Excel file
        $filename = 'tyer_data_export_' . date('Ymd_His') . '.xlsx';

        // Redirect output to a client’s web browser (Excel)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        // Exit to ensure no further output is sent
        exit();
    }

    function expert_excel_tyre_management()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $location_id = $this->request->getVar('location_id');

        $sql = "
            SELECT 
                tyer_management.*, 
                vendor.name AS vendor_name, 
                location.location_name 
            FROM 
                tyer_management
            LEFT JOIN 
                vendor ON vendor.id = tyer_management.vendor_id
            LEFT JOIN 
                location ON location.location_id = tyer_management.location_id
        ";


        $tyer_data = $this->db->query($sql)->getResult();
        if (empty($tyer_data)) {
            return redirect()->back()->with('error', 'No data available to export.');
        }

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Sl no',
            'Vendor Name',
            'Date',
            'Bill no',
            'Serial No',
            'Location',
            'Brand Name',
            'Tyer Type',
            'Model'
        ];

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Populate the data rows
        $rowNumber = 2;
        $slNo = 1;
        foreach ($tyer_data as $tyer) {
            $sheet->setCellValue('A' . $rowNumber, $slNo++);
            $sheet->setCellValue('B' . $rowNumber, $tyer->vendor_name);
            $sheet->setCellValue('C' . $rowNumber, $tyer->date);
            $sheet->setCellValue('D' . $rowNumber, $tyer->bill_no);
            $sheet->setCellValue('E' . $rowNumber, $tyer->tyer_sl_no);
            $sheet->setCellValue('F' . $rowNumber, $tyer->location_name);
            $sheet->setCellValue('G' . $rowNumber, $tyer->brand_name);
            $sheet->setCellValue('H' . $rowNumber, $tyer->tyer_type);
            $sheet->setCellValue('I' . $rowNumber, $tyer->model);
            $rowNumber++;
        }

        // Set filename and save the Excel file
        $filename = 'tyre_data_export_' . date('Ymd_His') . '.xlsx';

        // Redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        // Ensure no further output is sent
        exit();
    }
    public function export_excel_Stocktyre_management(){
        $tyer_data = $this->db->table('tyer_management')
            ->select('tyer_management.*, location.location_name')
            ->join('location', 'location.location_id = tyer_management.location_id', 'left')
            ->where('tyer_management.vehicle_id', null)
            ->get()
            ->getResult();
        if (empty($tyer_data)) {
            return redirect()->back()->with('error', 'No data available to export.');
        }

        // Load PhpSpreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Sl no',
            'Date',
            'Bill no',
            'Serial No',
            'Location',
            'Brand Name',
            'Tyer Type',
            'Model'
        ];

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Populate the data rows
        $rowNumber = 2;
        $slNo = 1;
        foreach ($tyer_data as $tyer) {
            $sheet->setCellValue('A' . $rowNumber, $slNo++);
            $sheet->setCellValue('B' . $rowNumber, $tyer->date);
            $sheet->setCellValue('C' . $rowNumber, $tyer->bill_no);
            $sheet->setCellValue('D' . $rowNumber, $tyer->tyer_sl_no);
            $sheet->setCellValue('E' . $rowNumber, $tyer->location_name);
            $sheet->setCellValue('F' . $rowNumber, $tyer->brand_name);
            $sheet->setCellValue('G' . $rowNumber, $tyer->tyer_type);
            $sheet->setCellValue('H' . $rowNumber, $tyer->model);
            $rowNumber++;
        }

        // Set filename and save the Excel file
        $filename = 'tyre_data_export_' . date('Ymd_His') . '.xlsx';

        // Redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        // Ensure no further output is sent
        exit();
    }
    public function exprt_excel()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $year = $this->request->getPost('year');
        $month = $this->request->getPost('month');
        $location_id = $this->request->getPost('location');
        $sql = "
    SELECT staff.*, location.location_name 
    FROM staff  
    JOIN location  ON staff.address = location.location_id 
    WHERE staff.address = $location_id
    AND staff.doj = $year
    AND staffs.doj = $month
";

        // Execute the query and fetch results
        $staff_data = $this->db->query($sql)->getResultArray();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Sl No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Location');
        $sheet->setCellValue('D1', 'Contact No');
        $sheet->setCellValue('E1', 'Date of Join');
        $sheet->setCellValue('F1', 'Salary');
        $sheet->setCellValue('G1', 'Opening Balance');
        $sheet->setCellValue('H1', 'Advance');
        $sheet->setCellValue('I1', 'No. of Days');
        $sheet->setCellValue('J1', 'Incentive/Penalty');
        $sheet->setCellValue('K1', 'Salary');
        $sheet->setCellValue('L1', 'Net Salary');
        $row = 2;
        $sl_no = 1;
        foreach ($staff_data as $staff) {
            $sheet->setCellValue('A' . $row, $sl_no++);
            $sheet->setCellValue('B' . $row, $staff['name']);
            $sheet->setCellValue('C' . $row, $staff['location_name']);
            $sheet->setCellValue('D' . $row, $staff['tel']);
            $sheet->setCellValue('E' . $row, date('d/m/Y', strtotime($staff['doj'])));
            $sheet->setCellValue('F' . $row, $staff['salary']);
            $sheet->setCellValue('G' . $row, $staff['opening_balance']);
            $sheet->setCellValue('H' . $row, $staff['total_advance']);
            $sheet->setCellValue('I' . $row, $staff['working_day']);
            $sheet->setCellValue('J' . $row, $staff['insentive']);
            $sheet->setCellValue('K' . $row, $staff['total_salary']);
            $sheet->setCellValue('L' . $row, $staff['net_salary']);
            $row++;
        }
        $filename = "staff_data_{$year}_{$month}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }
    function repaire_report()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $location_id = $this->request->getVar('location_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
        $data['vendor'] = $this -> db -> query("SELECT * FROM vendor") ->getResult();


        $sql = "
            SELECT 
                tyer_management.*, 
                location.location_name, 
                vendor.name AS exchange_vendorname
            FROM 
                tyer_management 
            LEFT JOIN 
                location ON location.location_id = tyer_management.location_id 
            LEFT JOIN 
                vendor ON vendor.id = tyer_management.ex_ven_id
            WHERE 
                tyer_management.status IN (4)
        ";

        // Add location filter if location_id is not empty
        if (!empty($location_id)) {
            $sql .= " AND tyer_management.location_id = " . $this->db->escape($location_id);
        }

        $data['tyer_data'] = $this->db->query($sql)->getResult();

        return view('admin/repaire_report_vw', $data);
    }
    public function update_tyer_repair()
    {
        // Get input values
        $tyer_sl_no = trim($this->request->getPost('tyer_sl_no'));
        $vendor     = trim($this->request->getPost('vendor'));
        $location   = trim($this->request->getPost('location'));
        $date       = trim($this->request->getPost('date'));
        // echo "<pre>";
        // print_r($tyer_sl_no); exit;
        // ✅ Step 1: Validate required fields
        if (empty($tyer_sl_no) || empty($vendor) || empty($location) || empty($date)) {
            return redirect()->back()->with('error', 'All fields are required.');
        }

        // ✅ Step 2: Check if tyre exists
        $tyre = $this->db->table('tyer_management')
                        ->where('tyer_sl_no', $tyer_sl_no)
                        ->get()
                        ->getRow();

        if (!$tyre) {
            return redirect()->back()->with('error', 'Tyre not found in database.');
        }

        // ✅ Step 3: Prepare data for update
        $updateData = [
            'vendor_id'   => $vendor,
            'location_id' => $location,
            'date'        => $date,
            'status'      => 1,  // 1 = Back to stock
            'remark'     => 'Tyre repaired and updated to stock'
        ];

        // REMOVED: Debug statement that was causing the exit
        // echo "<pre>";
        // print_r($updateData, $tyer_sl_no); exit;

        // ✅ Step 4: Run the update query
        $updated = $this->db->table('tyer_management')
                            ->where('tyer_sl_no', $tyer_sl_no)
                            ->update($updateData);

        // Optional debug (uncomment if needed)
        // log_message('debug', 'Update Query: ' . $this->db->getLastQuery());

        // Check if update was successful
        if ($this->db->affectedRows() === 0 && !$updated) {
            return redirect()->back()->with('error', 'Failed to update tyre details.');
        }

        // ✅ Step 5: Prepare and insert history data
        $historyData = [
            'tyre_id'     => $tyre->id,
            'event_type'  => 6, // 6 = Repaired
            'location_id' => $location,
            'event_date'  => $date,
            'vendor_id'   => $vendor,
            'remarks'     => 'Tyre repaired and updated to stock',
        ];

        $inserted = $this->db->table('tyer_management_history')->insert($historyData);

        if (!$inserted) {
            // Log the error but don't stop the process since main update succeeded
            log_message('error', 'Failed to insert tyre history for tyre_sl_no: ' . $tyer_sl_no);
        }

        // ✅ Step 6: Redirect with success
        return redirect()->to(base_url('Admin/repaire_report'))
                        ->with('success', 'Tyre repaired and moved back to stock successfully!');
    }


    function add_repaire_report()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('admin/');
        }
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $rules = [
            'location_id' => 'required',
            'tyer_sl_no' => 'required',
            'bill_no' => 'required',
            'remark' => 'required'
        ];
        if ($this->validate($rules)) {
            $data = [
                'location_id' => $this->request->getVar('location_id'),
                'tyer_sl_no' => $this->request->getVar('tyer_sl_no'),
                'bill_no' => $this->request->getVar('bill_no'),
                'remark' => $this->request->getVar('remark')
            ];

            $this->db->table('other_report')->insert($data);
            return redirect()->to('Admin/repaire_report');
        } else {
            $data['validation'] = $this->validator;
            echo view('Admin/repaire_report_vw', $data);
        }
    }
    public function getTyerData1()
    {
        $location_id = $this->request->getVar('location_id'); // Get the location_id from the AJAX request

        // Query to get tyer_sl_no based on the location_id
        $sql = "
        SELECT tyer_sl_no 
        FROM tyer_management 
        WHERE status = 1 AND location_id = " . $this->db->escape($location_id);

        // Execute the query and return the result as a JSON response
        $tyer_data = $this->db->query($sql)->getResult();

        // Return the data as JSON
        return $this->response->setJSON($tyer_data);
    }
    public function getBillNo1()
    {
        $tyer_sl_no = $this->request->getVar('tyer_sl_no'); // Get the tyer_sl_no from the AJAX request

        // Query to fetch the bill_no based on the tyer_sl_no
        $sql = "
        SELECT bill_no 
        FROM tyer_management 
        WHERE status = 1 AND tyer_sl_no = " . $this->db->escape($tyer_sl_no);

        // Execute the query and get the bill_no
        $bill_data = $this->db->query($sql)->getRow();

        // Return the bill_no as JSON
        return $this->response->setJSON($bill_data);
    }
    public function getTyreDetails()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        $tyer_sl_no = $this->request->getPost('tyer_sl_no');

        // Fetch tyre details from the database based on the tyre serial number
        $tyreDetails = $this->AdminModel->getTyreDetailsBySlNo($tyer_sl_no);

        if ($tyreDetails) {
            return $this->response->setJSON([
                'brand_name' => $tyreDetails->brand_name,
                'tyer_type' => $tyreDetails->tyer_type,
                'model' => $tyreDetails->model,
                'price' => $tyreDetails->price,
                'tyer_position' => $tyreDetails->tyer_position
            ]);
        } else {
            // Log or show an error message in case no details are found
            return $this->response->setJSON(['error' => 'No tyre details found for this serial number']);
        }
    }
    public function downloadDatabase()
    {
        // Database credentials
        $db_host = 'localhost'; // Your DB host
        $db_name = 'u929406983_yasujalogistic'; // Your DB name
        $db_user = 'u929406983_yasujalogistic'; // Your DB user
        $db_pass = '&Qw73Q/SI'; // Your DB password

        // Set the file name
        $backup_file = $db_name . "_" . date("Y-m-d-H-i-s") . ".sql";

        // Create the command to dump the database using `mysqldump`
        $command = "mysqldump --user=$db_user --password=$db_pass --host=$db_host $db_name > /tmp/$backup_file";

        // Execute the command
        system($command);

        // Set headers to download the file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($backup_file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize("/tmp/$backup_file"));

        // Read and output the file
        readfile("/tmp/$backup_file");

        // Delete the file from the temp directory after download
        unlink("/tmp/$backup_file");

        exit;
    }

    public function gettyerData()
    {
        $location_id = $this->request->getVar('location_id');
        // Ensure location_id is an integer to prevent SQL injection
        if (is_numeric($location_id)) {
            // Use parameter binding for the query
            $tyer_data = $this->db->query("
                SELECT * 
                FROM tyer_management 
                WHERE location_id = ? 
                AND status = 1 
                AND vehicle_id IS NULL
            ", [$location_id])->getResult();

            // Check if data was found and return it
            if ($tyer_data) {
                return $this->response->setJSON($tyer_data);
            } else {
                // Return an empty array if no data found
                return $this->response->setJSON([]);
            }
        } else {
            // Handle the case where location_id is not valid
            return $this->response->setJSON(['error' => 'Invalid location ID']);
        }
    }


    function tripexpencee()
    {
        $vid = $this->request->getVar('vid');
        $did = $this->request->getVar('did');
        $year = $this->request->getVar('did');
    }






    public function upload_adjust_salary()
    {
        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $fileExtension = $file->getClientExtension();

            // Determine the reader type based on file extension
            $reader = ($fileExtension === 'csv') ? new \PhpOffice\PhpSpreadsheet\Reader\Csv() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            try {
                // Load the spreadsheet
                $spreadsheet = $reader->load($filePath);
                $sheetData = $spreadsheet->getActiveSheet()->toArray();

                // echo"<pre>";
                // print_r($sheetData);
                // exit;    

                $data = [];
                foreach ($sheetData as $index => $rowData) {
                    $staff_dtl = null;
                    $loc_dtl = null;
                    $date = '';
                    $is_valid = true;

                    $staff_dtl = $this->db->query("SELECT * FROM staff WHERE staff_code = ?", [$rowData[1]])->getFirstRow();

                    if (!$staff_dtl) {
                        $is_valid = false;
                    }

                    $des_date = $rowData[2] ?? '';

                    if (!empty($des_date)) {
                        $dateObject = DateTime::createFromFormat('n/j/Y', $des_date);
                        $formattedDate = $dateObject ? $dateObject->format('y-m-d') : date('y-m-d');
                    }


                    // Prepare data for insertion
                    $data = [
                        'from_date' => $formattedDate,
                        'driver_name' => $rowData[0] ?? '',
                        'amount' => $rowData[3] ?? '',
                        'remark' => $rowData[4] ?? '',
                        'created_date' => date('Y-m-d H:i:s'),
                    ];
                    // echo"<pre>";
                    // print_r($data);
                    // exit; 
                    // Insert into the appropriate table based on validity
                    if ($is_valid) {
                        $data['driver_id'] = $staff_dtl->id;
                        $this->db->table('adjust_salary')->insert($data);
                    } else {
                        $this->db->table('staff_advance1')->insert($data);
                    }
                }
                return redirect()->to(base_url('Admin/adjust_salary'))->with('success', 'Data imported successfully.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Failed to upload file.');
    }





    function Extraactivites()
    {
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        // Fetch general settings and user data
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);

        // Fetch unique menu names for the filter
        $data['menu_list'] = $this->db->table('activity_logs')
            ->select('menu')
            ->groupBy('menu')
            ->get()
            ->getResult();

        // Handle filtering
        $menu_filter = $this->request->getVar('menu');
        if ($menu_filter) {
            // Fetch filtered activity logs
            $data['activity_logs'] = $this->db->table('activity_logs')
                ->select('activity_logs.*, user.full_name, user.email')  // select columns you want from both tables
                ->join('user', 'user.id = activity_logs.user_id', 'left')  // left join users table on user_id
                ->where('activity_logs.menu', $menu_filter)
                ->get()
                ->getResult();
        } else {
            $data['activity_logs'] = $this->db->table('activity_logs')
                ->select('activity_logs.*, user.full_name, user.email')
                ->join('user', 'user.id = activity_logs.user_id', 'left')
                ->get()
                ->getResult();
        }

        return view('admin/extra_activity_data', $data);
    }
    
    
    
    function payment_voucher()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            $to_date = $this->request->getVar('to_date');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['allUsers'] = $this->AdminModel->getAllStaffAndVendors();
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['allbank'] = $this->db->query("SELECT * FROM bank")->getResult();
            $data['allstaf'] = $this->AdminModel->Getallstaf();
            $data['AllPaymentVoucher'] = $this->AdminModel->GetAllPaymentVoucher($from_date, $to_date);
            $data['date'] = [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];
            return view('admin/payment_voucher_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
	public function getUsersByType()
	{
		$user_type = $this->request->getPost('user_type');
		$data = [];

		if (in_array($user_type, ['Party', 'Pump', 'Vendor'])) {
			$builder = $this->db->table('vendor');
			$builder->select('id, name, type as code');
			$builder->where('LOWER(type)', strtolower($user_type));
			$query = $builder->get();
			$data = $query->getResult();
		} elseif (in_array($user_type, ['DRIVER', 'STAFF'])) {
			$builder = $this->db->table('staff');
			$builder->select('id, name, staff_code');
			$builder->where('LOWER(user_type)', strtolower($user_type));
			$query = $builder->get();
			$data = $query->getResult();
		}
		echo json_encode($data);
		return;
	}

	public function insert_payment_voucher()
	{
		if (!$this->session->get('user_id')) {
			return redirect()->to('Admin/');
		}

		$user_id = $this->session->get('user_id');
		$menu = $this->request->getUri()->getSegment(2);

		// Collect post data
		$data = [
			'user_type'     => $this->request->getPost('user_type'),
			'staff_id'      => $this->request->getPost('staff_id'),
			'pay_date'      => $this->request->getPost('date'),
			'bank'          => $this->request->getPost('bank'),
			'location_id'   => $this->request->getPost('location_id'),
			'credit_debit'  => $this->request->getPost('credit_debit'),
			'bank_cash'     => $this->request->getPost('bank_cash'),
			'utr_no'        => $this->request->getPost('utr_no'),
			'amount'        => $this->request->getPost('amount'),
			'remark'        => $this->request->getPost('remark'),
			'upload_file'   => $this->uploadFile('upload_file') // Your file upload handler
		];

		$this->db->table('payment_voucher')->insert($data);

		// Logging
		$this->logActivity($user_id, 'create', 'payment_voucher', $this->db->insertID(), ['data' => $data], $menu);

		return redirect()->to('Admin/payment_voucher');
	}

    function editpayment_vouccher()
    { 
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $segment = $this->request->getUri()->getSegment(3);
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['location'] = $this->db->query("SELECT * FROM location")->getResult();
            $data['allbank'] = $this->db->query("SELECT * FROM bank")->getResult();
            $data['single_payment_voucher'] = $this->db->query("SELECT * FROM payment_voucher  where payment_voucher.pay_id='$segment' ")->getResult();            
            return view('admin/edit_payment_voucher_vw', $data);
        } else {
            return redirect()->to('Admin/');
        } 
    }
    
	public function update_payment_voucher()
	{
		  if (!$this->session->get('user_id')) {
			return redirect()->to('Admin/');
		}
		$user_id = $this->session->get('user_id');
		$pay_id = $this->request->getPost('pay_id');
		$oldData = $this->AdminModel->getPaymentById('payment_voucher', $pay_id);
		$data = [
			'user_type'     => $this->request->getPost('user_type'),
			'staff_id'      => $this->request->getPost('staff_id'),
			'pay_date'      => $this->request->getPost('date'),
			'bank'          => $this->request->getPost('bank'),
			'location_id'   => $this->request->getPost('location_id'),
			'credit_debit'  => $this->request->getPost('credit_debit'),
			'bank_cash'     => $this->request->getPost('bank_cash'),
			'utr_no'        => $this->request->getPost('utr_no'),
			'amount'        => $this->request->getPost('amount'),
			'remark'        => $this->request->getPost('remark')
		];
		$file = $this->request->getFile('upload_file');
		if ($file && $file->isValid() && !$file->hasMoved()) {
			$data['upload_file'] = $this->uploadFile('upload_file'); // Your helper method
		}
		$changes = $this->getChanges($oldData, $data);
		$this->db->table('payment_voucher')->update($data, ['pay_id' => $pay_id]);
		$menu = $this->request->getUri()->getSegment(2);
		$this->logActivityy($user_id, 'update', 'payment_voucher', $pay_id, $changes, $menu);
		return redirect()->to('Admin/payment_voucher');
	}


    function delete_payment_vouccher()
    {
        if ($this->session->get('user_id')) {
            // Get current user and sub-admin ID
            $segment = $this->request->getUri()->getSegment(3);
            $user_id = $this->session->get('user_id');
            
            // Update the sub-admin's record as deleted
            $data = [
                'deleted_by' => $user_id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('payment_voucher')->update($data, ['pay_id' => $segment]);
            $data1 = [
                'deleted_by' => $segment,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            $activity_log = [
                'user_id' => $user_id,
                'menu' => 'delete_payment_vouccher', 
                'action' => 'delete', 
                'model' => 'user', 
                'model_id' => $segment, 
                'changes' => json_encode($data1), 
                'created_at' => date('Y-m-d H:i:s'), 
            ];
            $this->db->table('activity_logs')->insert($activity_log);
            $this->session->setFlashdata('msg', 'Sub-admin deleted successfully.');
            return redirect()->to('admin/payment_voucher');
        } else {
            return redirect()->to('admin/');
        }
    }
	public function download_payment_voucher_excel()
	{
		if ($this->session->get('user_id') == '') {
		return redirect()->to('Admin/');
		}
		$user_id = $this->session->get('user_id');
		$menu = $this->request->getUri()->getSegment(2);
		$from_date = $this->request->getVar('from_date');
		$to_date = $this->request->getVar('to_date');

		if (!$from_date || !$to_date) {
			return redirect()->back()->with('error', 'Invalid date range');
		}

		$paymentVouchers = $this->AdminModel->GetAllPaymentVoucher($from_date, $to_date);

		// Load PhpSpreadsheet library
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		// Set header row
		$headers = [
			'Sl no',
			'User Type',
			'Employ Name',
			'Date',
			'Bank Name',
			'Location',
			'Bank/Cash',
			'Credit/Debit',
			'Amount',
			'Remark'
		];
		$sheet->fromArray($headers, NULL, 'A1');

		// Mapping
		$userTypeMap = [
			'Party' => 'PARTY',
			'Pump' => 'PUMP',
			'Vendor' => 'VENDOR',
			'DRIVER' => 'Driver',
			'STAFF' => 'Staff'
		];

		$row = 2;
		foreach ($paymentVouchers as $index => $record) {
			$employName = '';
			if (in_array($record->user_type, ['DRIVER', 'STAFF'])) {
				$employName = $record->staff_name . ' (' . $record->staff_code . ')';
			} else {
				$employName = $record->vendor_name . ' (' . $record->vendor_type . ')';
			}

			$sheet->setCellValue('A' . $row, $index + 1);
			$sheet->setCellValue('B' . $row, $userTypeMap[$record->user_type] ?? $record->user_type);
			$sheet->setCellValue('C' . $row, $employName);
			$sheet->setCellValue('D' . $row, date('d-m-Y', strtotime($record->pay_date)));
			$sheet->setCellValue('E' . $row, $record->bank_name);
			$sheet->setCellValue('F' . $row, $record->location_name);
			$sheet->setCellValue('G' . $row, $record->bank_cash == 1 ? 'Bank' : ($record->bank_cash == 2 ? 'Cash' : ''));
			$sheet->setCellValue('H' . $row, $record->credit_debit == 1 ? 'Credit' : ($record->credit_debit == 2 ? 'Debit' : ''));
			$sheet->setCellValue('I' . $row, $record->amount);
			$sheet->setCellValue('J' . $row, $record->remark);

			$row++;
		}

		// Output file
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$filename = 'payment_voucher_' . date('YmdHis') . '.xlsx';

		// Send headers
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
			$changes = 'Downloaded Payment_voucher data in Excel';
		$this->logActivityy($user_id, 'Download', 'Payment_voucher', null, $changes, $menu);
		exit;
	}
	
	
	//Payment Report Controller by Debasis Code Start
    function pump_report()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            $to_date = $this->request->getVar('to_date');
            $pump_id = $this->request->getVar('pump_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['Allvendor'] = $this->AdminModel->GetAllVendor('Pump');
            $data['AllPaymentVoucherCredit'] = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date,$pump_id,'Pump',1);
            // echo "<pre>";
            // print_r($data['AllPaymentVoucherCredit']);exit;
            $data['AllPaymentVoucherDebit'] = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date,$pump_id,'Pump',2);
          $data['filter_data'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'pump_id' => $pump_id,
        ];
            return view('admin/pump_report_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    function party_report()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            $to_date = $this->request->getVar('to_date');
            $pump_id = $this->request->getVar('pump_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['Allvendor'] = $this->AdminModel->GetAllVendor('Party');
            $data['AllPaymentVoucherCredit'] = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date,$pump_id,'Party',1);
            // echo "<pre>";
            // print_r($data['AllPaymentVoucherCredit']);exit;
            $data['AllPaymentVoucherDebit'] = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date,$pump_id,'Party',2);
          $data['filter_data'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'pump_id' => $pump_id,
        ];
            return view('admin/party_report_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    function vendor_report()
    {
        if ($this->session->get('user_id')) {

            $user_id = $this->session->get('user_id');
            $from_date = $this->request->getVar('from_date');
            $to_date = $this->request->getVar('to_date');
            $pump_id = $this->request->getVar('pump_id');
            $data['setting'] = $this->AdminModel->Settingdata();
            $data['singleuser'] = $this->AdminModel->userdata($user_id);
            $data['Allvendor'] = $this->AdminModel->GetAllVendor('Vendor');
            $data['AllPaymentVoucherCredit'] = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date,$pump_id,'Vendor',1);
            // echo "<pre>";
            // print_r($data['AllPaymentVoucherCredit']);exit;
            $data['AllPaymentVoucherDebit'] = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date,$pump_id,'Vendor',2);
          $data['filter_data'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'pump_id' => $pump_id,
        ];
            return view('admin/vendor_report_vw', $data);
        } else {
            return redirect()->to('Admin/');
        }
    }
    function exportParty_paymentExcel()
    {
    if ($this->session->get('user_id') == '') {
    return redirect()->to('Admin/');
    }
        $user_id = $this->session->get('user_id');
        $menu = $this->request->getUri()->getSegment(2);
        $from_date = $this->request->getGet('from_date');
        $to_date   = $this->request->getGet('to_date');
        $pump_id   = $this->request->getGet('pump_id');

        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        // Get data
        $creditData = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date, $pump_id, 'Party', 1);
        $debitData  = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date, $pump_id, 'Party', 2);

        // Initialize totals
        $total_credit = 0;
        $total_debit = 0;
        $total_opn_bal = 0;

        // Setup Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Party Payment Report');

        // Header row
        $headers = [
            'Sl No', 'Party Name', 'Date', 'Bank Name', 'Location', 'Bank/Cash', 'UTR No.', 'Amount', 'Opening Balance', 'File'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Fill Credit Data
        $row = 2;
        $sl = 1;

        foreach ($creditData as $record) {
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $record->vendor_name);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($record->pay_date)));
            $sheet->setCellValue('D' . $row, $record->bank_name);
            $sheet->setCellValue('E' . $row, $record->location_name);
            $sheet->setCellValue('F' . $row, $record->bank_cash == 1 ? 'Cash' : 'UPI');
            $sheet->setCellValue('G' . $row, $record->utr_no);
            $sheet->setCellValue('H' . $row, $record->amount);
            $sheet->setCellValue('I' . $row, isset($record->bal) ? $record->bal : 0);
            $sheet->setCellValue('J' . $row, !empty($record->upload_file) ? base_url($record->upload_file) : 'No File');

            // Totals
            $total_credit += $record->amount;
            $total_opn_bal += isset($record->bal) ? $record->bal : 0;
            $row++;
        }

        // Empty row and title before Debit Data
        $row++;
        $sheet->setCellValue('A' . $row, 'Party Debit Payment');
        $row++;

        // Fill Debit Data
        $sl = 1;
        foreach ($debitData as $record) {
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $record->vendor_name);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($record->pay_date)));
            $sheet->setCellValue('D' . $row, $record->bank_name);
            $sheet->setCellValue('E' . $row, $record->location_name);
            $sheet->setCellValue('F' . $row, $record->bank_cash == 1 ? 'Cash' : 'UPI');
            $sheet->setCellValue('G' . $row, $record->utr_no);
            $sheet->setCellValue('H' . $row, $record->amount);
            $sheet->setCellValue('I' . $row, '');
            $sheet->setCellValue('J' . $row, !empty($record->upload_file) ? base_url($record->upload_file) : 'No File');

            // Totals
            $total_debit += $record->amount;
            $row++;
        }

        // Summary Section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Summary');
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Opening Balance');
        $sheet->setCellValue('B' . $row, $total_opn_bal);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Credit');
        $sheet->setCellValue('B' . $row, $total_credit);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Debit');
        $sheet->setCellValue('B' . $row, $total_debit);
        $row++;

        $profit_loss = ($total_opn_bal + $total_credit) - $total_debit;
        $sheet->setCellValue('A' . $row, 'Total Profit/Loss');
        $sheet->setCellValue('B' . $row, $profit_loss);

        // Output to browser
        $writer = new XlsxWriter($spreadsheet);
        $filename = 'Party_Payment_Report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        $changes = 'Downloaded Party_voucher_payment data in Excel';
        $this->logActivityy($user_id, 'Download', 'Payment_voucher', null, $changes, $menu);
        exit;
    }
    function exportvendor_paymentExcel()
    {
   if ($this->session->get('user_id') == '') {
    return redirect()->to('Admin/');
    }
        $user_id = $this->session->get('user_id');
        $menu = $this->request->getUri()->getSegment(2);
        $from_date = $this->request->getGet('from_date');
        $to_date   = $this->request->getGet('to_date');
        $pump_id   = $this->request->getGet('pump_id');

        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        // Get data
        $creditData = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date, $pump_id, 'Vendor', 1);
        $debitData  = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date, $pump_id, 'Vendor', 2);

        // Initialize totals
        $total_credit = 0;
        $total_debit = 0;
        $total_opn_bal = 0;

        // Setup Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Party Payment Report');

        // Header row
        $headers = [
            'Sl No', 'Vendor Name', 'Date', 'Bank Name', 'Location', 'Bank/Cash', 'UTR No.', 'Amount', 'Opening Balance', 'File'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Fill Credit Data
        $row = 2;
        $sl = 1;

        foreach ($creditData as $record) {
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $record->vendor_name);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($record->pay_date)));
            $sheet->setCellValue('D' . $row, $record->bank_name);
            $sheet->setCellValue('E' . $row, $record->location_name);
            $sheet->setCellValue('F' . $row, $record->bank_cash == 1 ? 'Cash' : 'UPI');
            $sheet->setCellValue('G' . $row, $record->utr_no);
            $sheet->setCellValue('H' . $row, $record->amount);
            $sheet->setCellValue('I' . $row, isset($record->bal) ? $record->bal : 0);
            $sheet->setCellValue('J' . $row, !empty($record->upload_file) ? base_url($record->upload_file) : 'No File');

            // Totals
            $total_credit += $record->amount;
            $total_opn_bal += isset($record->bal) ? $record->bal : 0;
            $row++;
        }

        // Empty row and title before Debit Data
        $row++;
        $sheet->setCellValue('A' . $row, 'Party Debit Payment');
        $row++;

        // Fill Debit Data
        $sl = 1;
        foreach ($debitData as $record) {
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $record->vendor_name);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($record->pay_date)));
            $sheet->setCellValue('D' . $row, $record->bank_name);
            $sheet->setCellValue('E' . $row, $record->location_name);
            $sheet->setCellValue('F' . $row, $record->bank_cash == 1 ? 'Cash' : 'UPI');
            $sheet->setCellValue('G' . $row, $record->utr_no);
            $sheet->setCellValue('H' . $row, $record->amount);
            $sheet->setCellValue('I' . $row, '');
            $sheet->setCellValue('J' . $row, !empty($record->upload_file) ? base_url($record->upload_file) : 'No File');

            // Totals
            $total_debit += $record->amount;
            $row++;
        }

        // Summary Section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Summary');
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Opening Balance');
        $sheet->setCellValue('B' . $row, $total_opn_bal);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Credit');
        $sheet->setCellValue('B' . $row, $total_credit);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Debit');
        $sheet->setCellValue('B' . $row, $total_debit);
        $row++;

        $profit_loss = ($total_opn_bal + $total_credit) - $total_debit;
        $sheet->setCellValue('A' . $row, 'Total Profit/Loss');
        $sheet->setCellValue('B' . $row, $profit_loss);

        // Output to browser
        $writer = new XlsxWriter($spreadsheet);
        $filename = 'vendor_Payment_Report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        $changes = 'Downloaded vendor_voucher_payment data in Excel';
        $this->logActivityy($user_id, 'Download', 'Payment_voucher', null, $changes, $menu);
        exit;
    }
    function exportpump_paymentExcel()
    {
    if ($this->session->get('user_id') == '') {
    return redirect()->to('Admin/');
    }
        $user_id = $this->session->get('user_id');
        $menu = $this->request->getUri()->getSegment(2);
        $from_date = $this->request->getGet('from_date');
        $to_date   = $this->request->getGet('to_date');
        $pump_id   = $this->request->getGet('pump_id');

        if (!$from_date || !$to_date) {
            return redirect()->back()->with('error', 'Invalid date range');
        }

        // Get data
        $creditData = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date, $pump_id, 'Pump', 1);
        $debitData  = $this->AdminModel->GetAllPaymentVoucherByUserType($from_date, $to_date, $pump_id, 'Pump', 2);

        // Initialize totals
        $total_credit = 0;
        $total_debit = 0;
        $total_opn_bal = 0;

        // Setup Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pump Payment Report');

        // Header row
        $headers = [
            'Sl No', 'Pump Name', 'Date', 'Bank Name', 'Location', 'Bank/Cash', 'UTR No.', 'Amount', 'Opening Balance', 'File'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Fill Credit Data
        $row = 2;
        $sl = 1;

        foreach ($creditData as $record) {
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $record->vendor_name);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($record->pay_date)));
            $sheet->setCellValue('D' . $row, $record->bank_name);
            $sheet->setCellValue('E' . $row, $record->location_name);
            $sheet->setCellValue('F' . $row, $record->bank_cash == 1 ? 'Cash' : 'UPI');
            $sheet->setCellValue('G' . $row, $record->utr_no);
            $sheet->setCellValue('H' . $row, $record->amount);
            $sheet->setCellValue('I' . $row, isset($record->bal) ? $record->bal : 0);
            $sheet->setCellValue('J' . $row, !empty($record->upload_file) ? base_url($record->upload_file) : 'No File');

            // Totals
            $total_credit += $record->amount;
            $total_opn_bal += isset($record->bal) ? $record->bal : 0;
            $row++;
        }

        // Empty row and title before Debit Data
        $row++;
        $sheet->setCellValue('A' . $row, 'Pump Debit Payment');
        $row++;

        // Fill Debit Data
        $sl = 1;
        foreach ($debitData as $record) {
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $record->vendor_name);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($record->pay_date)));
            $sheet->setCellValue('D' . $row, $record->bank_name);
            $sheet->setCellValue('E' . $row, $record->location_name);
            $sheet->setCellValue('F' . $row, $record->bank_cash == 1 ? 'Cash' : 'UPI');
            $sheet->setCellValue('G' . $row, $record->utr_no);
            $sheet->setCellValue('H' . $row, $record->amount);
            $sheet->setCellValue('I' . $row, '');
            $sheet->setCellValue('J' . $row, !empty($record->upload_file) ? base_url($record->upload_file) : 'No File');

            // Totals
            $total_debit += $record->amount;
            $row++;
        }

        // Summary Section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Summary');
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Opening Balance');
        $sheet->setCellValue('B' . $row, $total_opn_bal);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Credit');
        $sheet->setCellValue('B' . $row, $total_credit);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Debit');
        $sheet->setCellValue('B' . $row, $total_debit);
        $row++;

        $profit_loss = ($total_opn_bal + $total_credit) - $total_debit;
        $sheet->setCellValue('A' . $row, 'Total Profit/Loss');
        $sheet->setCellValue('B' . $row, $profit_loss);

        // Output to browser
        $writer = new XlsxWriter($spreadsheet);
        $filename = 'Pump_Payment_Report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        $changes = 'Downloaded Pump_voucher_payment data in Excel';
        $this->logActivityy($user_id, 'Download', 'Payment_voucher', null, $changes, $menu);
        exit;
    }
    public function track_vehicle(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['vehicle'] = $this->AdminModel->Getvehicle();
        // echo "<pre>";
        // print_r($data['vehicle']);exit;
        return view('admin/track_vehicle_vw', $data);
    }
    public function voucher_payment(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        
        return view('admin/voucher_payment_vw', $data);
    }


    public function voucher_received(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');

        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        
        return view('admin/voucher_received_vw', $data);
    }
    public function group(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['groups'] = $this->AdminModel->getGroupDetails();
        
        return view('admin/group_vw',$data);
    }
    public function insertGroup(){
        
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $data = [
            'group_name' => $this->request->getPost('group_name')
        ];
        $this->db->table('group')->insert($data);
        return redirect()->to('admin/group');
    }
    public function toggleGroupStatus($group_id)
    {
        $builder = $this->db->table('group');
        $group = $builder->where('group_id', $group_id)->get()->getRowArray();
    
        if ($group) {
            $newStatus = ($group['status'] == 1) ? 0 : 1;
    
            $builder->where('group_id', $group_id)
                    ->update(['status' => $newStatus]);
        }
    
        return redirect()->to('admin/group');
    }
    public function deleteGroup($group_id)
    {
        $builder = $this->db->table('group');
        $builder->where('group_id', $group_id)->delete();
    
        return redirect()->to('admin/group')->with('msg', 'Group deleted successfully!');
    }
    public function financial_year(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['years'] = $this->AdminModel->getyearsDetails();
        
        return view('admin/fy_vw',$data); 
    }
    public function insertFinancialYear(){
        if ($this->session->get('user_id') == '') {
            return redirect()->to('Admin/');
        }
        $data = [
            'from_date' => $this->request->getPost('from_date'),
            'to_date' => $this->request->getPost('to_date')
        ];
        $this->db->table('financial_year')->insert($data);
        return redirect()->to('admin/financial_year');
    }
    public function toggleFinancialYearStatus($fy_id){
        $builder = $this->db->table('financial_year');
        $fy = $builder->where('fy_id', $fy_id)->get()->getRowArray();
    
        if ($fy) {
            $newStatus = ($fy['status'] == 1) ? 0 : 1;
    
            $builder->where('fy_id', $fy_id)
                    ->update(['status' => $newStatus]);
        }
    
        return redirect()->to('admin/financial_year');
    }

    public function ledger(){
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['financial_years'] = $this->AdminModel->getyearsDetails();
        $data['groups'] = $this->AdminModel->getGroupDetails();
        $data['ledgers'] = $this->AdminModel->getLedgerDetails();
        
        return view ('admin/ledger_vw',$data);
    }
   public function insertLedger()
    {
        // Validate form input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'fy_id'          => 'required|integer',
            'group_id'       => 'required|integer',
            'ledger_name'    => 'required|string|max_length[255]',
            'balance'        => 'required|decimal',
            'transaction_type' => 'required|in_list[CR,DR]',
        ]);
    
        if (!$this->validate($validation->getRules())) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }
    
        // Collect data
        $data = [
            'fy_id'            => $this->request->getPost('fy_id'),
            'group_id'         => $this->request->getPost('group_id'),
            'ledger_name'      => $this->request->getPost('ledger_name'),
            'balance'          => $this->request->getPost('balance'),
            'transaction_type' => $this->request->getPost('transaction_type'),
        ];
        
        $this->db->table('ledger')->insert($data);
        // Insert into ledger table
        return redirect()->to('admin/ledger');
    }
    public function task_Assignment(){
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
    
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['staff_list'] = $this->AdminModel->getAllUser();
    
        // extract user_type safely
        $user_type = null;
        if (!empty($data['singleuser'][0])) {
            $user_type = $data['singleuser'][0]->user_type;
        }
    
        if ($user_type == 1) {
            $data['tasks'] = $this->AdminModel->getAllTasks();
        } else {
            $data['tasks'] = $this->AdminModel->getTasksByUser($user_id);
        }
    
        return view('admin/taskAssignment_vw', $data);
    }
    public function task_management(){
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
    
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['staff_list'] = $this->AdminModel->getAllUser();
    
        // extract user_type safely
        $user_type = null;
        if (!empty($data['singleuser'][0])) {
            $user_type = $data['singleuser'][0]->user_type;
        }
    
        if ($user_type == 1) {
            $data['tasks'] = $this->AdminModel->getAllTasks();
        } else {
            $data['tasks'] = $this->AdminModel->getTasksByUser($user_id);
        }
    
        return view($data);
    }


    public function saveTask()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
    
        $user_id = $this->session->get('user_id');
    
        $data = [
            'task_description' => $this->request->getPost('task_description'),
            'assigned_to'      => $this->request->getPost('assigned_to'),
            'assigned_by'      => $user_id,
            'created_date'     => date('Y-m-d'),
            'completion_date'  => $this->request->getPost('completion_date'),
            'remarks'          => $this->request->getPost('remarks'),
            'status'           => 1
        ];
    
        $this->db->table('tasks')->insert($data);
    
        return redirect()->to('Admin/task_Assignment')->with('success', 'Task assigned successfully!');
    }
    public function updateTask()
    {
        if (!$this->session->get('user_id')) {
            return redirect()->to('Admin/');
        }

        $taskid = $this->request->getPost('task_id');
        $assigned_to = $this->request->getPost('assigned_to'); // array
        $cc          = $this->request->getPost('cc');          // array

        $updateData = [
            'task_description' => $this->request->getPost('task_description'),
            'assigned_to'      => !empty($assigned_to) ? implode(',', $assigned_to) : null,
            'cc'               => !empty($cc) ? implode(',', $cc) : null,
            'completion_date'  => $this->request->getPost('completion_date'),
            'status'           => $this->request->getPost('status'),
            'remarks'          => $this->request->getPost('remarks')
        ];

        $this->db->table('tasks')
                ->where('id', $taskid)
                ->update($updateData);

        return redirect()->to('Admin/task_Assignment')->with('success', 'Task updated successfully!');
    }
    public function delete_assignment(){
        $segment = $this->request->getUri()->getSegment(3);
        $this->db->table('tasks')->delete(array('id' => $segment));
        return redirect()->to('Admin/task_Assignment')->with('success', 'Task assigned Delete successfully!');
    }
    public function tyre_details_vw($tyre_id = null)
    {
        // Session check
        if (!$this->session->get('user_id')) {
            return redirect()->to('Admin/');
        }

        $user_id = $this->session->get('user_id');
        
        // Get basic data
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        
        // Get tyre details if ID provided
        if ($tyre_id) {
            $data['tyre'] = $this->AdminModel->getTyreById($tyre_id);
            
            // If tyre not found, redirect or show error
            if (!$data['tyre']) {
                return redirect()->to('Admin/tyre_list')->with('error', 'Tyre not found');
            }
        }
        
        // Get filters from query string
        $filters = [
            'search' => $this->request->getGet('search'),
            'event_type' => $this->request->getGet('event_type'),
            'tyre_id' => $tyre_id // Add tyre_id to filters
        ];
        
        // Get history records with filters
        $data['history'] = $this->AdminModel->getHistoryRecords($filters);
        
        return view('admin/tyre_details_vw', $data);
    }

    // Set Master Functions
    // View Tonnage Set (separate page)
    public function view_tonnage_set()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        
        $user_id = $this->session->get('user_id');
        $set_id = $this->request->getGet('set_id');
        
        if(empty($set_id)) {
            return redirect()->to('admin/tonnage')->with('error', 'Set ID is required');
        }
        
        // Get basic data
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        
        // Get set details
        $data['set'] = $this->AdminModel->single_set($set_id);
        
        if(empty($data['set'])) {
            return redirect()->to('admin/tonnage')->with('error', 'Set not found');
        }
        
        // Get all ranges for this set
        $data['tonnage'] = $this->AdminModel->tonnage_by_set($set_id);
        
        return view('admin/view_tonnage_set_vw', $data);
    }

    // Delete Set (with all ranges)
    public function delete_set()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $id = $this->request->getPost('id');

        // Start transaction
        $this->db->transStart();

        try {
            // Soft delete all ranges for this set
            $this->db->table('tonnage')
                    ->where('set_id', $id)
                    ->where('deleted_by', null)
                    ->update([
                        'deleted_by' => $user_id,
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

            // Soft delete the set
            $data = [
                'deleted_by' => $user_id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('set_master')->where('id', $id)->update($data);

            $this->db->transComplete();

            if($this->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Error deleting set. Please try again.');
            }

            return redirect()->to('admin/tonnage')->with('success', 'Set and all ranges deleted successfully!');

        } catch(\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function tonnage()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        $data['sets'] = $this->AdminModel->all_sets();
        
        // Load sets with range count for listing
        $data['sets_with_count'] = $this->AdminModel->sets_with_range_count();
        
        return view('admin/tonnage_vw', $data);
    }

    public function insert_tonnage()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'set_id' => 'required',
            'min' => 'required|numeric',
            'max' => 'permit_empty|numeric',
        ]);

        if (!$this->validate($validation->getRules())) {
            return redirect()->back()->withInput();
        }

        $max_value = $this->request->getPost('max');
        $max_value = ($max_value === '' || $max_value === null) ? null : $max_value;
        
        $data = [
            'set_id' => $this->request->getPost('set_id'),
            'min' => $this->request->getPost('min'),
            'max' => $max_value, // NULL means unlimited/above this value
            'penalty_value' => $this->request->getPost('penalty_value') ?: 0,
            'weight' => null,
            'price' => 0.00, // NOT NULL field, must have default value
            'created_by' => $user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tonnage')->insert($data);
        $set_id = $this->request->getPost('set_id');
        return redirect()->to('admin/tonnage?set_id=' . $set_id);
    }

    public function edit_tonnage()
    {
        $id = $this->request->getPost('id');

        $data = $this->db->table('tonnage')
                        ->where('id', $id)
                        ->get()
                        ->getRow();

        return $this->response->setJSON($data);
    }



    public function update_tonnage()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $id = $this->request->getPost('id');

        $max_value = $this->request->getPost('max');
        $max_value = ($max_value === '' || $max_value === null) ? null : $max_value;
        
        $data = [
            'set_id' => $this->request->getPost('set_id'),
            'min' => $this->request->getPost('min'),
            'max' => $max_value, // NULL means unlimited/above this value
            'penalty_value' => $this->request->getPost('penalty_value') ?: 0,
            'updated_by' => $user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tonnage')->where('id', $id)->update($data);
        $set_id = $this->request->getPost('set_id');
        return redirect()->to('admin/tonnage?set_id=' . $set_id);
    }

    public function delete_tonnage()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $id = $this->request->getPost('id');
        $set_id = $this->request->getPost('set_id');

        $data = [
            'deleted_by' => $user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ];
        // Soft delete
        $this->db->table('tonnage')->where('id', $id)->update($data);
        
        return redirect()->to('admin/tonnage');
    }

    // Insert Set with Multiple Ranges (New Set)
    public function insert_set_with_ranges()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'set_name' => 'required',
        ]);

        if (!$this->validate($validation->getRules())) {
            return redirect()->back()->withInput()->with('error', 'Set Name is required');
        }

        $set_name = $this->request->getPost('set_name');
        $ranges = $this->request->getPost('ranges');

        // Check if set name already exists
        $existing_set = $this->db->table('set_master')
                                ->where('set_name', $set_name)
                                ->where('deleted_by', null)
                                ->get()
                                ->getRow();
        
        if($existing_set) {
            return redirect()->back()->withInput()->with('error', 'Set Name already exists!');
        }

        // Validate ranges
        if(empty($ranges) || !is_array($ranges)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one range');
        }

        // Validate that at least one range has min value before starting transaction
        $hasValidRange = false;
        foreach($ranges as $range) {
            if(isset($range['min']) && $range['min'] !== '' && $range['min'] !== null) {
                $hasValidRange = true;
                break;
            }
        }

        if(!$hasValidRange) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one range with Min value');
        }

        // Start transaction
        $this->db->transStart();

        try {
            // Insert Set
            $set_data = [
                'set_name' => $set_name,
                'created_by' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            
            if(!$this->db->table('set_master')->insert($set_data)) {
                throw new \Exception('Failed to insert set');
            }
            
            $set_id = $this->db->insertID();
            
            if(empty($set_id)) {
                throw new \Exception('Failed to get set ID after insertion');
            }

            // Insert Ranges
            $ranges_data = [];
            foreach($ranges as $range) {
                // Check if range has min value (required field)
                if(isset($range['min']) && $range['min'] !== '' && $range['min'] !== null) {
                    $max_value = (!empty($range['max']) && $range['max'] !== '' && $range['max'] !== null) ? $range['max'] : null;
                    $ranges_data[] = [
                        'set_id' => $set_id,
                        'min' => $range['min'],
                        'max' => $max_value,
                        'penalty_value' => (!empty($range['penalty_value']) && $range['penalty_value'] !== '') ? $range['penalty_value'] : 0,
                        'weight' => null, // VARCHAR field, can be null
                        'price' => 0.00,  // NOT NULL field, must have default value
                        'created_by' => $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }

            if(empty($ranges_data)) {
                throw new \Exception('No valid ranges to insert. Please add at least one range with Min value');
            }

            if(!$this->db->table('tonnage')->insertBatch($ranges_data)) {
                throw new \Exception('Failed to insert ranges');
            }

            $this->db->transComplete();

            if($this->db->transStatus() === false) {
                $error = $this->db->error();
                $errorMessage = 'Database transaction failed';
                if(!empty($error) && is_array($error)) {
                    if(isset($error['message'])) {
                        $errorMessage = $error['message'];
                    } elseif(isset($error['code'])) {
                        $errorMessage = 'Error Code: ' . $error['code'];
                    }
                }
                // Log error for debugging
                log_message('error', 'Set insert failed: ' . json_encode($error));
                return redirect()->back()->withInput()->with('error', 'Error saving data: ' . $errorMessage);
            }

            return redirect()->to('admin/tonnage')->with('success', 'Set and ranges created successfully!');

        } catch(\Exception $e) {
            $this->db->transRollback();
            $error = $this->db->error();
            $errorMessage = $e->getMessage();
            if(!empty($error) && is_array($error) && isset($error['message'])) {
                $errorMessage .= ' | DB Error: ' . $error['message'];
            }
            // Log error for debugging
            log_message('error', 'Set insert exception: ' . $errorMessage);
            return redirect()->back()->withInput()->with('error', 'Error: ' . $errorMessage);
        }
    }

    // Insert Multiple Ranges to Existing Set
    public function insert_ranges_to_set()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');

        $set_id = $this->request->getPost('existing_set_id');
        $ranges = $this->request->getPost('ranges');

        if(empty($set_id)) {
            return redirect()->back()->withInput()->with('error', 'Please select a Set');
        }

        // Validate ranges
        if(empty($ranges) || !is_array($ranges)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one range');
        }

        // Insert Ranges
        $ranges_data = [];
        foreach($ranges as $range) {
            if(!empty($range['min'])) {
                $max_value = (!empty($range['max']) && $range['max'] != '') ? $range['max'] : null;
                $ranges_data[] = [
                    'set_id' => $set_id,
                    'min' => $range['min'],
                    'max' => $max_value,
                    'penalty_value' => !empty($range['penalty_value']) ? $range['penalty_value'] : 0,
                    'weight' => null, // VARCHAR field, can be null
                    'price' => 0.00,  // NOT NULL field, must have default value
                    'created_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if(!empty($ranges_data)) {
            $this->db->table('tonnage')->insertBatch($ranges_data);
            return redirect()->to('admin/tonnage')->with('success', 'Ranges added successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Please add at least one valid range');
        }
    }

    // Update Single Range
    public function update_tonnage_range()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        $id = $this->request->getPost('id');
        $ranges = $this->request->getPost('ranges');

        if(empty($id)) {
            return redirect()->back()->withInput()->with('error', 'Invalid range ID');
        }

        // Get first range (since we're updating single range in edit mode)
        $range = !empty($ranges) && is_array($ranges) ? reset($ranges) : null;

        if(empty($range) || empty($range['min'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid range data');
        }

        $max_value = (!empty($range['max']) && $range['max'] != '') ? $range['max'] : null;
        
        $data = [
            'min' => $range['min'],
            'max' => $max_value,
            'penalty_type' => 'percentage',
            'penalty_value' => !empty($range['penalty_value']) ? $range['penalty_value'] : 0,
            'updated_by' => $user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tonnage')->where('id', $id)->update($data);
        return redirect()->to('admin/tonnage')->with('success', 'Range updated successfully!');
    }

    // Edit Set with all ranges
    public function edit_set_with_ranges()
    {
        $set_id = $this->request->getPost('set_id');
        
        if(empty($set_id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Set ID required']);
        }

        // Get set details
        $set = $this->AdminModel->single_set($set_id);
        
        // Get all ranges for this set
        $ranges = $this->AdminModel->tonnage_by_set($set_id);
        
        return $this->response->setJSON([
            'status' => 'success',
            'set' => $set,
            'ranges' => $ranges
        ]);
    }

    // Update Set with Ranges
    public function update_set_with_ranges()
    {
        if(($this->session->get('user_id')=='')){
            return redirect()->to('Admin/');
        }
        $user_id = $this->session->get('user_id');
        
        $set_id = $this->request->getPost('set_id');
        $set_name = $this->request->getPost('set_name');
        $ranges = $this->request->getPost('ranges');

        if(empty($set_id)) {
            return redirect()->back()->withInput()->with('error', 'Set ID is required');
        }

        if(empty($set_name)) {
            return redirect()->back()->withInput()->with('error', 'Set Name is required');
        }

        // Validate ranges
        if(empty($ranges) || !is_array($ranges)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one range');
        }

        // Start transaction
        $this->db->transStart();

        try {
            // Update Set Name
            $set_data = [
                'set_name' => $set_name,
                'updated_by' => $user_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('set_master')->where('id', $set_id)->update($set_data);

            // Delete existing ranges for this set
            $this->db->table('tonnage')
                    ->where('set_id', $set_id)
                    ->where('deleted_by', null)
                    ->update([
                        'deleted_by' => $user_id,
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

            // Insert new ranges
            $ranges_data = [];
            foreach($ranges as $range) {
                // Check if range has min value (required field)
                if(isset($range['min']) && $range['min'] !== '' && $range['min'] !== null) {
                    $max_value = (!empty($range['max']) && $range['max'] !== '' && $range['max'] !== null) ? $range['max'] : null;
                    $ranges_data[] = [
                        'set_id' => $set_id,
                        'min' => $range['min'],
                        'max' => $max_value,
                        'penalty_value' => (!empty($range['penalty_value']) && $range['penalty_value'] !== '') ? $range['penalty_value'] : 0,
                        'weight' => null, // VARCHAR field, can be null
                        'price' => 0.00,  // NOT NULL field, must have default value
                        'created_by' => $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }

            if(empty($ranges_data)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Please add at least one valid range with Min value');
            }

            $this->db->table('tonnage')->insertBatch($ranges_data);

            $this->db->transComplete();

            if($this->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Error updating data. Please try again.');
            }

            return redirect()->to('admin/tonnage')->with('success', 'Set and ranges updated successfully!');

        } catch(\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}