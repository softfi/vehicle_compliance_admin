<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\AdminModel;

class API extends BaseController
{
    use ResponseTrait;
    public $ApiModal, $AdminModel, $UserModel, $session, $db;

    public function __construct()
    {
        $db = db_connect();
        $this->db = db_connect();

        $this->AdminModel = new AdminModel($db);
        $this->session = session();
        helper(['form', 'url', 'validation']);
    }

    public function insert_despatch_entry()
    {
        try {
            // Decode JSON input data
            $input = $this->request->getJSON();
    
            if (!$input) {
                return $this->respond([
                    'status' => 400,
                    'error' => true,
                    'message' => 'Invalid JSON payload.',
                ], 400); // Bad Request
            }

            $data = [
                'PassNo'      => $input->PassNo,
                'VehicleType' => $input->VehicleType,
                'VehicleNo' => $input->VehicleNo,
                'SourceUser' => $input->SourceUser,
                'SourceAddress' => $input->SourceAddress,
                'MineralName' => $input->MineralName,
                'MineralForm' => $input->MineralForm,
                'MineralGrade' => $input->MineralGrade,
                'MineralQuantity' => $input->MineralQuantity,
                'DateTimeDispatched' => $input->DateTimeDispatched,
                'DestinationUser' => $input->DestinationUser,
                'DestinationAddress' => $input->DestinationAddress,
                'NameOfDriver' => $input->NameOfDriver,
                'DriverContactNo' => $input->DriverContactNo,
                'Status' => $input->Status,
            ];
    
            // Insert data into the database
            $this->db->table('despatch_fetch_api_data')->insert($data);
    
            return $this->respond([
                'status' => 200,
                'error' => false,
                'message' => 'Despatch entry inserted successfully.',
            ], 200); // Success
        } catch (\Exception $e) {
            // Handle database or general errors
            return $this->respond([
                'status' => 500,
                'error' => true,
                'message' => 'Failed to insert despatch entry.',
                'details' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }
    
    public function index()
    {
        echo "samir";
    }

    public function Login()
    {
        $contact = $this->request->getVar('contact');
        $password = base64_encode(base64_encode($this->request->getVar('password')));

        // Query to check user by contact
        $user = $this->db->query("
            SELECT user.*
            FROM user
            WHERE user.contact_no = ?
        ", [$contact])->getRow();

        if ($user) {
            if ($user->password == $password) {
                $data = [
                    'user_id' => $user->id,
                    'fullname' => $user->full_name,
                    'email' => $user->email,
                    'contact' => $user->contact_no,
                    'status' => $user->status,
                    'lat' => $user->lat,
                    'lng' => $user->lng,
                    'profile_image' => $user->profile_image,
                    'address' => $user->address1,
                    'role' => $user->roles,
                    'isLoggedIn' => true
                ];
                $response = [
                    'status'   => 200,
                    'error'    => false,
                    'messages' => [
                        'responsecode' => "00",
                        'data' => $data
                    ]
                ];
            } else {
                // Incorrect password
                $response = [
                    'status'   => 400,
                    'error'    => true,
                    'messages' => [
                        'responsecode' => "01",
                        'message' => "Invalid password."
                    ]
                ];
            }
        } else {
            // User not found
            $response = [
                'status'   => 400,
                'error'    => true,
                'messages' => [
                    'responsecode' => "02",
                    'message' => "User not found."
                ]
            ];
        }

        return $this->respond($response);
    }

    function update_profile()
    {
        $user_id = $this->request->getVar('user_id');
        $fullname = $this->request->getVar('full_name');
        $email = $this->request->getVar('e_mail');
        $contact = $this->request->getVar('contact_number');
        $city_id = $this->request->getVar('city_id');
        $state_id = $this->request->getVar('state_id');
        $pincode = $this->request->getVar('pincode');
        $lat = $this->request->getVar('lat');
        $long = $this->request->getVar('long');
        $store_name = $this->request->getVar('store_name');
        $address = $this->request->getPost('address');

        $file = $this->request->getFile('store_photo');
        if ($file->isValid() && !$file->hasMoved()) {
            $imagename = $file->getRandomName();
            $file->move('uploads/', $imagename);
        } else {
            $imagename = "";
        }





        $data = [
            'full_name' => $fullname,
            'email' => $email,
            'contact_no' => $contact,
            'city_id' => $city_id,
            'state_id' => $state_id,
            'pin' => $pincode,
            'lat' => $lat,
            'lng' => $long,
            'store_name' => $store_name,
            'address1' => $address,
        ];

        if ($imagename != '') {
            $data += [
                'profile_image' => $imagename,
            ];
        }

        $CountContact = $this->db->query("SELECT * FROM user  where contact_no='$contact' and id!='$user_id' and user_type='4' ")->getResult();
        if (count($CountContact) == 0) {
            $this->db->table('user')->update($data, array('id' => $user_id));
            $response = [
                'status'   => 200,
                'error'    => true,
                'messages' => [
                    'responsecode' => "01",
                    'status' => 'Update Data Succesfully'
                ]
            ];
        } else {
            $response = [
                'status'   => 400,
                'error'    => true,
                'messages' => [
                    'responsecode' => "01",
                    'status' => 'Contact No Already Exit'
                ]
            ];
        }
        return $this->respond($response);
    }

    public function cms()
    {
        $data['cms'] = $this->db->query("SELECT * FROM cms ")->getResult();
        $response = [
            'status'   => 200,
            'error'    => true,
            'messages' => [
                'responsecode' => "00",
                'status' => $data
            ]
        ];
        return $this->respond($response);
    }

    public function forgot_password()
    {
        $contact_no = $this->request->getVar('contact_no');

        // Check if the email exists in the database
        $query = $this->db->query("SELECT * FROM user WHERE contact_no = ? AND user_type = 5 AND status = 1", [$contact_no]);
        $user = $query->getRow();

        if (!$user) {
            // Email not found, return error response
            $msg = "Contact No  not found";
            $response = [
                'status' => 200,
                'error' => true,
                'messages' => [
                    'responsecode' => "00",
                    'status' => $msg,
                ]
            ];
            return $this->respond($response);
        } else {
            $otp = "1234";
            $msg = "An Otp Send to Your registered mobile Number  ";

            $data = [
                'msg' => $msg,
                'OTP' => $otp,
            ];

            $response = [
                'status' => 200,
                'error' => false,
                'messages' => [
                    'responsecode' => "00",
                    'status' => $data,
                ]
            ];
            return $this->respond($response);
        }
    }

    function reset_password()
    {
        $contact_no = $this->request->getVar('contact_no');
        $password = base64_encode(base64_encode($this->request->getVar('new_password')));

        $data = [
            'password' => $password,
        ];

        $this->db->table('user')->update($data, array('contact_no' => $contact_no));

        $response = [
            'status' => 200,
            'error' => false,
            'messages' => [
                'responsecode' => "00",
                'status' => "password Reset Succesfully",
            ]
        ];
        return $this->respond($response);
    }

        // Driver_Assignment// Outside Maintenance 
    // public function getVehicles()
    // {
    //     try {
    //         $builder = $this->db->table('vehicle');
    //         $vehicles = $builder->select('id, vehicle_no')->get()->getResult();

    //         return $this->respond([
    //             'status' => true,
    //             'data' => $vehicles,
    //         ]);
    //     } catch (\Exception $e) {
    //         return $this->fail('An error occurred: ' . $e->getMessage());
    //     }
    // }
    
    public function getVehicles()
{
    try {
        $builder = $this->db->table('vehicle v');
        $builder->select('v.id, v.vehicle_no, s.name as driver_name');

        // Join the last driver assignment per vehicle by from_date
        $builder->join(
            '(SELECT da1.vehicle_no, da1.driver
              FROM driver_assignment da1
              JOIN (
                  SELECT vehicle_no, MAX(from_date) as max_from_date
                  FROM driver_assignment
                  GROUP BY vehicle_no
              ) latest
              ON da1.vehicle_no = latest.vehicle_no AND da1.from_date = latest.max_from_date) da',
            'da.vehicle_no = v.id',
            'left'
        );

        $builder->join('staff s', 's.id = da.driver', 'left');

        $vehicles = $builder->get()->getResult();

        return $this->respond([
            'status' => true,
            'data' => $vehicles,
        ]);
    } catch (\Exception $e) {
        return $this->fail('An error occurred: ' . $e->getMessage());
    }
}
    
    public function Getlocation()
    {
        try {
            $builder = $this->db->table('location');
            $locations = $builder->select('*')->get()->getResult();

            return $this->respond([
                'status' => true,
                'data' => $locations,
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage());
        }
    }

    public function getVendors()
    {
        try {
            $builder = $this->db->table('vendor');
            $builder->select('vendor.*, location.location_name, vendor_rate.vendor_rate, vendor_rate.from_date');
            $builder->join('location', 'location.location_id = vendor.location', 'left');

            // Subquery to get the latest vendor_rate for each vendor
            $subquery = $this->db->table('vendor_rate as vr1')
                ->select('vr1.vendor_id, vr1.vendor_rate, vr1.from_date')
                ->join('(SELECT vendor_id, MAX(from_date) as max_date FROM vendor_rate GROUP BY vendor_id) as vr2', 
                    'vr1.vendor_id = vr2.vendor_id AND vr1.from_date = vr2.max_date', 'inner', false);

            $subquerySql = $subquery->getCompiledSelect(false);

            $builder->join("($subquerySql) as vendor_rate", 'vendor_rate.vendor_id = vendor.id', 'left');
            $builder->groupBy('vendor.id');

            $vendors = $builder->get()->getResult();

            return $this->respond([
                'status' => true,
                'data' => $vendors,
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage());
        }
    }

    // Outside Maintenance 
    public function insert_outside_maintanance()
    {
        // Initialize response
        $response = [
            'status' => false,
            'message' => '',
        ];

        try {
            // Handle file upload
            $file = $this->request->getFile('upload_file');
            $imagename = '';
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $imagename = $file->getRandomName();
                $file->move('uploads/', $imagename);
            }

            // Collect form data
            $data = [
                'vehicle_id' => $this->request->getPost('vehicle_id'),
                'bill_no' => $this->request->getPost('bill_no'),
                'amount' => $this->request->getPost('amount'),
                'vendor_id' => $this->request->getPost('vendor_id'),
                'location_id' => $this->request->getPost('location_id'),
                'date' => $this->request->getPost('date'),
                'remark' => $this->request->getPost('remark'),
            ];

            // Add file name if uploaded
            if (!empty($imagename)) {
                $data['upload_file'] = $imagename;
            }

            // Insert data into database
            $this->db->table('outside_maintenance')->insert($data);

            // Prepare successful response
            $response['status'] = true;
            $response['message'] = 'Data successfully inserted.';
        } catch (\Exception $e) {
            // Handle exceptions
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }

        return $this->respond($response);
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
		return $builder->get()->getResult();
	}

    public function outside_maintananceview()
    {
        try {
            // Retrieve dates from the request
            $from_date = $this->request->getPost('from_date');
            $to_date = $this->request->getPost('to_date');

            // Fetch staff advance data based on the date range
            $staff_advance_data = $this->out_side($from_date, $to_date);

            if (empty($staff_advance_data)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No records found.',
                    'data' => []
                ], 404);
            }

            // Return a success response with the fetched data
            return $this->respond([
                'status' => true,
                'message' => 'Staff advance data fetched successfully.',
                'data' => $staff_advance_data
            ], 200);
        } catch (\Exception $e) {
            // Handle errors and return a failure response
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Get all staff with location details
     */
    public function getAllStaff()
    {
        try {
            // Query to fetch staff details with location
            $builder = $this->db->table('staff');
            $builder->select('staff.*, location.location_name');
            $builder->join('location', 'location.location_id = staff.location_id', 'left');
            $staff = $builder->get()->getResult();

            return $this->respond([
                'status' => true,
                'data' => $staff,
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage());
        }
    }

    
    //Start  Asign Driver //Driver_Assignment
    public function fetchHsdKM()
    {
        try {
            $vehicle_id = $this->request->getPost('vehicle_id');

            if (!$vehicle_id) {
                return $this->fail('Vehicle ID is required.', 400);
            }

            // Fetch the last entry for the specified vehicle_id
            $query = $this->db->table('driver_assignment')
                ->select('closing_hsd, closing_km')
                ->where('vehicle_no', $vehicle_id)
                ->orderBy('id', 'DESC') // Assuming 'id' is the primary key and auto-incremented
                ->limit(1)
                ->get();

            $result = $query->getRow();

            if ($result) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Data fetched successfully.',
                    'data' => [
                        'closing_hsd' => $result->closing_hsd,
                        'closing_km' => $result->closing_km,
                    ],
                ], 200);
            } else {
                return $this->failNotFound('No data found for the given vehicle ID.');
            }
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    
    public function insertDriverAssignment()
    {
        $response = [
            'status' => false,
            'message' => '',
        ];

        try {
            // Gather data from the request
            $vehicle_id = $this->request->getPost('vehicle_no');
            $driver_id = $this->request->getPost('driver');
            $from_date = $this->request->getPost('from_date');
            $to_date = $this->request->getPost('to_date') ?: null;
            $opening_hsd = $this->request->getPost('opening_hsd');
            $opening_km = $this->request->getPost('opening_km');
            $closing_hsd = $this->request->getPost('closing_hsd');
            $closing_km = $this->request->getPost('closing_km');

            // Validate existing assignments for the vehicle
            $existingVehicleAssignments = $this->db->query("
                SELECT * 
                FROM driver_assignment 
                WHERE vehicle_no = '$vehicle_id' 
                AND MONTH(from_date) = MONTH('$from_date')
            ")->getResult();

            foreach ($existingVehicleAssignments as $assignment) {
                if (is_null($assignment->to_date) || $assignment->to_date > $from_date) {
                    $response['message'] = 'Cannot assign as the vehicle already has an existing assignment.';
                    return $this->respond($response, 400);
                }
            }

            // Validate existing assignments for the driver
            $existingDriverAssignments = $this->db->query("
                SELECT * 
                FROM driver_assignment 
                WHERE driver = '$driver_id' 
                AND MONTH(from_date) = MONTH('$from_date')
            ")->getResult();

            foreach ($existingDriverAssignments as $assignmentDriver) {
                if (is_null($assignmentDriver->to_date) || $assignmentDriver->to_date > $from_date) {
                    $response['message'] = 'Driver already assigned for this month.';
                    return $this->respond($response, 400);
                }
            }

            // Prepare data array for insertion
            $data = [
                'vehicle_no' => $vehicle_id,
                'driver' => $driver_id,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'opening_hsd' => $opening_hsd,
                'opening_km' => $opening_km,
                'closing_hsd' => $closing_hsd,
                'closing_km' => $closing_km,
            ];

            // Insert data into the 'driver_assignment' table
            $this->db->table('driver_assignment')->insert($data);

            // Respond with success
            $response['status'] = true;
            $response['message'] = 'Driver successfully assigned.';
            return $this->respond($response, 200);
        } catch (\Exception $e) {
            // Handle exceptions
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            return $this->respond($response, 500);
        }
    }

    public function getDriverAssignments()
    {
        try {
            $from_date = $this->request->getPost('from_date');
            $to_date = $this->request->getPost('to_date');

            $builder = $this->db->table('driver_assignment');
            $builder->select('driver_assignment.*, vehicle.vehicle_no as vehicle_number, staff.name as driver_name, staff.staff_code as driver_code');
            $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');
            $builder->join('staff', 'staff.id = driver_assignment.driver', 'left');

            if (!empty($from_date) && !empty($to_date)) {
                $builder->groupStart()
                        ->where('driver_assignment.from_date <=', $to_date)
                        ->where('(driver_assignment.to_date >= "' . $from_date . '" OR driver_assignment.to_date IS NULL)')
                        ->groupEnd();
            }

            $assignments = $builder->get()->getResult();

            return $this->respond([
                'status' => true,
                'data' => $assignments,
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage());
        }
    }

    public function getSingleDriverAssignment()
    {
        $asign_id = $this->request->getPost('asign_id');
        $response = [
            'status' => false,
            'message' => '',
            'data' => null,
        ];

        try {
            // Validate input
            if (empty($asign_id)) {
                $response['message'] = 'Assignment ID is required.';
                return $this->respond($response, 400);
            }

            // Build query to fetch assignment details
            $builder = $this->db->table('driver_assignment');
            $builder->select('driver_assignment.*, vehicle.vehicle_no as vehicle_number, staff.name as driver_name');
            $builder->join('vehicle', 'vehicle.id = driver_assignment.vehicle_no', 'left');
            $builder->join('staff', 'staff.id = driver_assignment.driver', 'left');
            $builder->where('driver_assignment.id', $asign_id);

            $assignment = $builder->get()->getRow(); 

            if ($assignment) {
                $response['status'] = true;
                $response['data'] = $assignment;
                $response['message'] = 'Driver assignment retrieved successfully.';
            } else {
                $response['message'] = 'No driver assignment found with the given ID.';
                return $this->respond($response, 404);
            }

            return $this->respond($response);
        } catch (\Exception $e) {
            // Handle exceptions
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            return $this->respond($response, 500);
        }
    }

    public function deleteDriverAssignment()
    {
        $asign_id = $this->request->getPost('asign_id');
        $response = [
            'status' => false,
            'message' => '',
        ];

        try {
            // Validate input
            if (empty($asign_id)) {
                $response['message'] = 'Assignment ID is required.';
                return $this->respond($response, 400);
            }

            // Check if the record exists
            $builder = $this->db->table('driver_assignment');
            $assignment = $builder->where('id', $asign_id)->get()->getRow();

            if (!$assignment) {
                $response['message'] = 'No driver assignment found with the given ID.';
                return $this->respond($response, 400);
            }

            // Delete the record
            $builder->where('id', $asign_id)->delete();

            $response['status'] = true;
            $response['message'] = 'Driver assignment deleted successfully.';
            return $this->respond($response, 200);
        } catch (\Exception $e) {
            // Handle exceptions
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            return $this->respond($response, 500);
        }
    }
    //End  Asign Driver // Driver_Assignment

    //Start tyer_report

    public function tyerReport()
    {
        try {
            // Get the location_id parameter from the request (if provided)
            $location_id = $this->request->getPost('location_id');

            // Base query
            $sql = "
                SELECT tyer_management.*, location.location_name 
                FROM tyer_management 
                LEFT JOIN location ON location.location_id = tyer_management.location_id 
                WHERE tyer_management.status IN (1, 2)
            ";

            // Add location filter if location_id is not empty
            if (!empty($location_id)) {
                $sql .= " AND tyer_management.location_id = " . $this->db->escape($location_id);
            }

            // Execute the query
            $tyer_data = $this->db->query($sql)->getResult();

            // Return API response
            return $this->respond([
                'status' => true,
                'message' => 'Tyer report fetched successfully.',
                'data' => $tyer_data,
            ], 200);
        } catch (\Exception $e) {
            // Return error response
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function Status()
    {
        // Return statuses as an associative array for clarity
        return [
            5 => 'Exchange',
            4 => 'Repair',
            3 => 'Trash'
        ];
    }

    public function tyer_exchange()
    {
        try {
            $tyer_id = $this->request->getPost('tyer_id');

            if (empty($tyer_id)) {
                return $this->fail('Tyer ID is required.', 400);
            }

            // Query to fetch tyer management details by ID
            $builder = $this->db->table('tyer_management');
            $tyer_management = $builder->select('*')->where('id', $tyer_id)->get()->getRow();

            if (!$tyer_management) {
                return $this->failNotFound('No tyer management record found for the given ID.');
            }

            // Fetch vendor data and status options
            $vendors = $this->AdminModel->Get_vendor();
            $statuses = $this->Status();

            // Prepare response data
            $data = [
                'tyer_management' => $tyer_management,
                'vendor' => $vendors,
                'status' => $statuses,
            ];

            return $this->respond([
                'status' => true,
                'message' => 'Tyer exchange data fetched successfully.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function update_tyer_report()
    {
        try {
            // Retrieve input from the request
            $tyer_id = $this->request->getPost('tyer_id');
            $status = $this->request->getPost('status');
            $vendor_id = $this->request->getPost('vendor_id');
            $remark = $this->request->getPost('remark');

            // Validate input
            if (empty($tyer_id) || empty($status)) {
                return $this->fail('Tyer ID and Status are required.', 400);
            }

            // Prepare data for update
            $data = [
                'status' => $status,
                'ex_ven_id' => $vendor_id,
                'remark' => $remark,
            ];

            // Update the record in the database
            $updated = $this->db->table('tyer_management')
                                ->where('id', $tyer_id)
                                ->update($data);

            if ($updated) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Tyer report updated successfully.',
                ], 200);
            } else {
                return $this->fail('Failed to update tyer report. Please try again.', 500);
            }
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    //End tyer_report

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

    public function staffadvanceview()
    {
        try {
            // Retrieve dates from the request
            $from_date = $this->request->getPost('from_date');
            $to_date = $this->request->getPost('to_date');

            // Fetch staff advance data based on the date range
            $staff_advance_data = $this->staffadvance($from_date, $to_date);

            if (empty($staff_advance_data)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No staff advance records found for the given date range.',
                    'data' => []
                ], 404);
            }

            // Return a success response with the fetched data
            return $this->respond([
                'status' => true,
                'message' => 'Staff advance data fetched successfully.',
                'data' => $staff_advance_data
            ], 200);
        } catch (\Exception $e) {
            // Handle errors and return a failure response
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    
	public function insert_user_tracking()
	{
		// Initialize response
		$response = [
			'status' => false,
			'message' => '',
		];

		try {
			// Collect data from POST
			$data = [
				'user_id' => $this->request->getPost('user_id'),
				'date'    => $this->request->getPost('date'),
				'time'    => $this->request->getPost('time'),
				'status'  => $this->request->getPost('status'),
				'lat'     => $this->request->getPost('lat'),
				'lang'    => $this->request->getPost('lang'),
			];

			// Validate required fields
			if (empty($data['user_id']) || empty($data['date']) || empty($data['time'])) {
				$response['message'] = 'user_id, date, and time are required.';
				return $this->respond($response, 400);
			}

			// Insert into the database
			$this->db->table('user_tracking')->insert($data);

			// Set success response
			$response['status'] = true;
			$response['message'] = 'User tracking data inserted successfully.';
		} catch (\Exception $e) {
			$response['message'] = 'An error occurred: ' . $e->getMessage();
		}

		return $this->respond($response);
	}



    // Despatch CRUD START 
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

    public function getOldData($table, $id)
    {
        return $this->db->table($table)->where('despatch_id', $id)->get()->getRowArray();
    }

    public function Despatchview()
    {
        try {
            // Retrieve dates from the request
            $from_date = $this->request->getPost('from_date');
            $to_date = $this->request->getPost('to_date');
            $despatch_data = $this->AdminModel->despatch_dtls($from_date, $to_date);
// echo "<pre>";
// print_r($despatch_data);exit;
            if (empty($despatch_data)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No Record Found.',
                    'data' => []
                ], 404);
            }

            // Return a success response with the fetched data
            return $this->respond([
                'status' => true,
                'message' => 'Record Fetched successfully.',
                'data' => $despatch_data
            ], 200);
        } catch (\Exception $e) {
            // Handle errors and return a failure response
            return $this->fail('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function insert_despatch()
    {
        $response = [
            'status' => false,
            'message' => '',
        ];

        try {
            // Collect data from POST
            $data = [
                'do_no'      => $this->request->getPost('do_no'),
                'vehicle_no' => $this->request->getPost('vehicle_no'),
                'quantity'   => $this->request->getPost('quantity'),
                'ref_no'     => $this->request->getPost('ref_no'),
                'des_date'   => $this->request->getPost('des_date'),
            ];

            // Validate required fields
            if (empty($data['do_no']) || empty($data['vehicle_no']) || empty($data['quantity']) || empty($data['des_date'])) {
                $response['message'] = 'do_no, vehicle_no, quantity, and date are required.';
                return $this->respond($response, 400);
            }

            // Get additional required data
            $menu = $this->request->getPost('menu');
            $user_id = $this->request->getPost('user_id');

            if (empty($user_id)) {
                $response['message'] = 'User not authenticated.';
                return $this->respond($response, 400);
            }

            // Insert data into the database
            $this->db->table('despatch')->insert($data);

            // Log activity
            $this->logActivity(
                $user_id,
                'create',
                'despatch',
                $this->db->insertID(),
                ['data' => $data],
                $menu
            );

            // Set success response
            $response['status'] = true;
            $response['message'] = 'Despatch entry created successfully.';
        } catch (\Exception $e) {
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            return $this->respond($response, 500);
        }

        return $this->respond($response, 200);
    }

    public function edit_despatch_entry()
    {
        $response = [
            'status' => false,
            'message' => '',
        ];

        try {
            $despatch_id = $this->request->getPost('despatch_id');
            if (empty($despatch_id)) {
                $response['message'] = 'Despatch ID is required.';
                return $this->respond($response, 400);
            }

            // Fetch old data for logging changes
            $oldData = $this->getOldData('despatch', $despatch_id);
            if (empty($oldData)) {
                $response['message'] = 'No record found for the provided Despatch ID.';
                return $this->respond($response, 404);
            }

            // Collect updated data from POST
            $data = [
                'do_no'      => $this->request->getPost('do_no'),
                'vehicle_no' => $this->request->getPost('vehicle_no'),
                'quantity'   => $this->request->getPost('quantity'),
                'ref_no'     => $this->request->getPost('ref_no'),
                'des_date'   => $this->request->getPost('des_date'),
            ];

            // Validate required fields
            if (empty($data['do_no']) || empty($data['vehicle_no']) || empty($data['quantity']) || empty($data['des_date'])) {
                $response['message'] = 'do_no, vehicle_no, quantity, and des_date are required.';
                return $this->respond($response, 400);
            }

            // Get user ID and menu for logging
            $user_id = $this->request->getPost('user_id');
            if (empty($user_id)) {
                $response['message'] = 'User not authenticated.';
                return $this->respond($response, 400);
            }

            $menu = $this->request->getPost('menu');

            // Compare changes
            $changes = $this->getChanges($oldData, $data);
            if (empty($changes)) {
                $response['message'] = 'No changes detected.';
                return $this->respond($response, 400);
            }

            // Update data in the database
            $this->db->table('despatch')->update($data, ['despatch_id' => $despatch_id]);

            // Log the changes
            $this->logActivity(
                $user_id,
                'update',
                'despatch',
                $despatch_id,
                $changes,
                $menu
            );

            // Set success response
            $response['status'] = true;
            $response['message'] = 'Despatch entry updated successfully.';
        } catch (\Exception $e) {
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            return $this->respond($response, 500);
        }

        return $this->respond($response, 200);
    }

    public function delete_despatch()
    {
        // Initialize response
        $response = [
            'status' => false,
            'message' => '',
        ];

        try {
            $user_id = $this->request->getPost('user_id');
            if (empty($user_id)) {
                $response['message'] = 'User not authenticated.';
                return $this->respond($response, 400);
            }

            $despatch_id = $this->request->getPost('despatch_id');
            if (empty($despatch_id)) {
                $response['message'] = 'Despatch ID is required.';
                return $this->respond($response, 400);
            }

            $despatch = $this->db->table('despatch')->where('despatch_id', $despatch_id)->get()->getRow();
            if (empty($despatch)) {
                $response['message'] = 'No record found for the provided Despatch ID.';
                return $this->respond($response, 400);
            }

            // Update the despatch record with deletion details
            $data = [
                'deleted_by' => $user_id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('despatch')->where('despatch_id', $despatch_id)->update($data);

            // Log the deletion activity in the activity_logs table
            $activity_log = [
                'user_id' => $user_id,
                'menu' => 'delete_despatch',
                'action' => 'delete',
                'model' => 'despatch',
                'model_id' => $despatch_id,
                'changes' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('activity_logs')->insert($activity_log);

            // Set success response
            $response['status'] = true;
            $response['message'] = 'Despatch deleted successfully.';
            return $this->respond($response, 200); // Success
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            return $this->respond($response, 500); // Internal server error
        }
    }

    public function submit_vehicle_maintenance()
    {
    

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
        return $this->respond([
                'status' => true,
                'message' => ' Vehicle Regular checkup successfully.',
            ], 200);
    }
    
    function Regular_Checkup()
    {
        

        $data['regularcheckup'] = $this->AdminModel->regularcheckup();
            //  print_r($data['regularcheckup']);exit;
        return $this->respond([
                'status' => true,
                'message' => ' Vehicle Regular checkup successfully.',
                'data'=>$data,
            ], 200);
        
    }
    public function getDOList()
    {
        try {
            $builder = $this->db->table('do_registration');
            $vehicles = $builder->select('do_registration_id, do_no')->get()->getResult();

            return $this->respond([
                'status' => true,
                'data' => $vehicles,
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage());
        }
    }
    function update_driver_asignment()
    {
        $id = $this->request->getPost('id');
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
        return $this->respond([
                'status' => true,
                'message' => ' Driver Assignment successfully.',
            ], 200);
    }
    public function update_tyer_data()
    {
        // Get POST/PUT data
        $lvehicle_id   = $this->request->getVar('vehicle_id');
        $tyer_id       = $this->request->getVar('tyer_id');
        $tyer_position = $this->request->getVar('tyer_position');
        $asign_date    = $this->request->getVar('asign_date');

        // Validate required fields
        if (empty($lvehicle_id) || empty($tyer_id) || empty($tyer_position) || empty($asign_date)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Missing required fields.'
            ]);
        }

        $data = [
            'vehicle_id'    => $lvehicle_id,
            'tyer_position' => $tyer_position,
            'asign_date'    => $asign_date,
            'status'        => 2
        ];

        // Perform update
        $builder = $this->db->table('tyer_management');
        $update  = $builder->update($data, ['id' => $tyer_id]);

        if ($update) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Tyre data updated successfully.',
                'updated_data' => $data
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Failed to update tyre data.'
            ]);
        }
    }
    public function getAllTyre()
    {
        try {
            $builder = $this->db->table('tyer_management');
            $builder->select('tyer_management.tyer_position, tyer_management.tyer_sl_no, location.location_name, tyer_management.vehicle_id, tyer_management.id, vehicle.vehicle_no');
            $builder->join('location', 'location.location_id = tyer_management.location_id', 'left');
            $builder->join('vehicle', 'vehicle.id = tyer_management.vehicle_id', 'left');
            $results = $builder->get()->getResult();
    
            $grouped = [];
    
            foreach ($results as $row) {
                $vehicle_no = $row->vehicle_no ?? 'UNKNOWN';
                $vehicle_id = $row->vehicle_id ?? 'UNKNOWN';
    
                if (!isset($grouped[$vehicle_no])) {
                    $grouped[$vehicle_no] = [
                        'vehicle_no' => $vehicle_no,
                        'vehicle_id' => $vehicle_id,
                        'tyres' => [],
                    ];
                }
    
                if (!empty($row->tyer_position)) {
                    $grouped[$vehicle_no]['tyres'][] = [
                        'tyre_id' => $row->id,
                        'tyer_position' => $row->tyer_position,
                        'location_name' => $row->location_name,
                        'tyer_sl_no' => $row->tyer_sl_no,
                    ];
                }
            }
    
            return $this->respond([
                'status' => true,
                'data' => array_values($grouped),
            ]);
        } catch (\Exception $e) {
            return $this->fail('An error occurred: ' . $e->getMessage());
        }
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
        return $this->respond([
                'status' => true,
                'message' => ' Staff Add successfully.',
            ], 200);
    }
    
    
     private function uploadFile($fieldName)
    {
        $file = $this->request->getFile($fieldName);
        if ($file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/', $fileName);
            return 'uploads/' . $fileName;
        }
        return "";
    }
    
    function update_StaffAdvance()
    {
        $said = $this->request->getPost('adv_id');

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
        return $this->respond([
                'status' => true,
                'message' => ' Staff Add successfully.',
            ], 200);
    }
    // In house maintenance start
    public function view_inhouse()
    {
        $from_date = $this->request->getVar('from_date') ?? date('Y-m-01');
        $to_date   = $this->request->getVar('to_date') ?? date('Y-m-d');

        // Get result as objects
        $rows = $this->AdminModel->inhouse_dtls($from_date, $to_date);

        $groupedData = [];

        foreach ($rows as $row) {
            $orderId = $row->order_id;

            if (!isset($groupedData[$orderId])) {
                $groupedData[$orderId] = [
                    'order_id' => $orderId,
                    'driver_name' => $row->driver_name,
                    'vehicle_no' => $row->vehicle_no,
                    'location_name' => $row->location_name,
                    'date' => $row->date,
                    'time' => $row->time,
                    'invoiceno' => $row->invoiceno,
                    'check_by' => $row->check_by,
                    'created_at' => $row->created_at,
                    'items' => [] // only item, qty, price, and type
                ];
            }

            // Map itemUseAs to readable type
            // $type = ($row->itemUseAs == 1) ? 'service' : (($row->itemUseAs == 2) ? 'product' : 'unknown');

            $groupedData[$orderId]['items'][] = [
                'item_id'   => $row->item,
                'item_name' => $row->item_name,
                'qty'       => $row->qty,
                'price'     => $row->price,
                'type'      => $row->itemUseAs,
                'available_qty' => $row->available_qty,
            ];
        }

        // Reset indexes
        $data = array_values($groupedData);

        return $this->respond([
            'status' => true,
            'message' => $data,
        ], 200);
    }



    
    public function insert_inhouse()
    {
        $vehicle   = $this->request->getPost('vehicle');
        $driver    = $this->request->getPost('driver');
        $date      = $this->request->getPost('date');
        $time      = $this->request->getPost('time');
        $invoiceno = $this->request->getPost('invoiceno');
        $location  = $this->request->getPost('location');
        $check_by  = $this->request->getPost('check_by');
        $itemUseAs = $this->request->getPost('itemUseAs');
        $items = $this->request->getPost('items');
        $qty   = $this->request->getPost('qty');
        $price = $this->request->getPost('price');
    
        $order_id = 'ORD-' . strtoupper(uniqid());
    
        if (is_array($items) && count($items) > 0) {
            foreach ($items as $key => $item) {
                $itemsData = [
                    'order_id'   => $order_id,
                    'item'       => $item,
                    'qty'        => $qty[$key] ?? 0,
                    'price'      => $price[$key] ?? 0,
                    'vehicle'    => $vehicle,
                    'date'       => $date,
                    'time'       => $time,
                    'invoiceno'  => $invoiceno,
                    'driver_name'=> $driver,
                    'location'   => $location,
                    'itemUseAs' => $itemUseAs[$key],
                    'check_by'   => $check_by,
                ];
    
                // Insert each row
                $this->db->table('inhouse_maintenance')->insert($itemsData);
            }
    
            return $this->respond([
                'status'  => true,
                'message' => 'Items added successfully.',
            ], 200);
        }
    
        return $this->respond([
            'status'  => false,
            'message' => 'No items found in request.',
        ], 400);
    }

    
    function edit_inhouse()
    {
        $user_id   = $this->request->getPost('user_id');
        $order_id = $this->request->getPost('order_id');
        $data['orderdtls'] = $this->AdminModel->inhouse_orderdtls($order_id);
        
        return $this->respond([
            'status' => true,
            'message' => $data,
        ], 200);
    }
    public function update_inhouse()
{
    $oorder_id = $this->request->getPost('oorder_id');

    // Delete existing records for this order_id
    $this->db->table('inhouse_maintenance')->where('order_id', $oorder_id)->delete();

    $vehicle    = $this->request->getPost('vehicle');
    $driver     = $this->request->getPost('driver');
    $date       = $this->request->getPost('date');
    $time       = $this->request->getPost('time');
    $invoiceno  = $this->request->getPost('invoiceno');
    $location   = $this->request->getPost('location');
    $itemUseAs  = $this->request->getPost('itemUseAs');
    $check_by   = $this->request->getPost('check_by');
    $items      = $this->request->getPost('items');
    $qty        = $this->request->getPost('qty');
    $price      = $this->request->getPost('price');

    $order_id = $oorder_id;

    if ($items) {
        foreach ($items as $key => $item) {
            if ($qty[$key] != '') {
                $itemsData = [
                    'order_id'    => $order_id,
                    'item'        => $item,
                    'qty'         => $qty[$key],
                    'price'       => $price[$key],
                    'vehicle'     => $vehicle,
                    'date'        => $date,
                    'time'        => $time,
                    'invoiceno'   => $invoiceno,
                    'driver_name' => $driver,
                    'location'    => $location,
                    'itemUseAs'   => $itemUseAs[$key],
                    'check_by'    => $check_by,
                ];

                $this->db->table('inhouse_maintenance')->insert($itemsData);
            }
        }

        return $this->respond([
            'status'  => true,
            'message' => 'In-house maintenance updated successfully.',
        ], 200);
    } else {
        return $this->respond([
            'status'  => false,
            'message' => 'No items found in the request.',
        ], 400);
    }
}







    function delete_inhouse()
    {
        $order_id   = $this->request->getPost('order_id');
        $this->db->table('inhouse_maintenance')->delete(array('order_id' => $order_id));
        return $this->respond([
            'status' => true,
            'message' => "Inhouse maintenance delete succefully",
        ], 200);
    }
    public function get_items_by_location()
    {
        $locationId = $this->request->getPost('location_id');
    
        if (!$locationId) {
            return $this->respond([
                'status'  => false,
                'message' => 'location_id is required'
            ], 400);
        }
    
        $items = $this->db->table('stock s')
            ->select('
                s.sproduct_id,
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
            ->join('items i', 'i.id = s.sproduct_id')
            ->join('units u', 'u.unit_id = i.unit_id', 'left')
            ->where('s.location_id', $locationId)
            ->groupBy('s.sproduct_id, i.item_name, s.rate, u.unit_name, i.item_id')
            ->get()
            ->getResult();
    
        return $this->respond([
            'status'  => true,
            'message' => $items
        ], 200);
    }
    
    public function staf()
    {
        // Fetch staff data with location in descending order
        $builder = $this->db->table('staff');
        $builder->select('staff.*, location.location_name');
        $builder->join('location', 'location.location_id = staff.address', 'left');
        $builder->orderBy('staff.id', 'DESC'); // <-- added descending order
        $allStaf = $builder->get()->getResult();
    
        return $this->respond([
            'status'  => true,
            'message' => 'Staff data fetched successfully.',
            'data'    => [
                'allStaf' => $allStaf,
            ],
        ], 200);
    }
    public function Add_staf_api()
    {
        // Define validation rules
        $rules = [
            'user_type'      => 'required',
            'name'           => 'required|max_length[100]',
            'salary'         => 'required|decimal',
            'tel'            => 'required|numeric|min_length[10]|max_length[15]',
            'address'        => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        try {
            // Retrieve input data
            $data = [
                'user_type'       => $this->request->getPost('user_type'),
                'name'            => $this->request->getPost('name'),
                'doj'             => $this->request->getPost('doj'),
                'salary'          => $this->request->getPost('salary'),
                'img'             => $this->uploadFile('img'),
                'name_bank'       => $this->request->getPost('name_bank'),
                'ac_no'           => $this->request->getPost('ac_no'),
                'ifsc'            => $this->request->getPost('ifsc'),
                'dl_front'        => $this->uploadFile('dl_front'),
                'dl_back'         => $this->uploadFile('dl_back'),
                'dl_number'       => $this->request->getPost('dl_number'),
                'dl_expiry'       => $this->request->getPost('dl_expiry'),
                'aadhaar_no'      => $this->request->getPost('aadhaar_no'),
                'aadhaar_front'   => $this->uploadFile('aadhaar_front'),
                'aadhaar_back'    => $this->uploadFile('aadhaar_back'),
                'tel'             => $this->request->getPost('tel'),
                'fathers_name'    => $this->request->getPost('fathers_name'),
                'spouse_name'     => $this->request->getPost('spouse_name'),
                'dob'             => $this->request->getPost('dob'),
                'family_contact'  => $this->request->getPost('family_contact'),
                'blood_group'     => $this->request->getPost('blood_group'),
                'opening_balance' => $this->request->getPost('opening_balance'),
                'address'         => $this->request->getPost('address'),
            ];

            // Insert into staff
            $this->db->table('staff')->insert($data);
            $lastInsertID = $this->db->insertID();

            // Generate staff code
            $clean_name = preg_replace('/[^a-zA-Z0-9]/', '', $data['name']);
            $firstThreeChars = substr($clean_name, 0, 3);
            $staff_code = $lastInsertID . '-' . $firstThreeChars;

            $this->db->table('staff')->where('id', $lastInsertID)->update([
                'staff_code' => $staff_code,
            ]);

            // Opening balance entry
            $this->db->table('opening_closing')->insert([
                'staff_id' => $lastInsertID,
                'oamout'   => $data['opening_balance'],
                'oc_type'  => 1,
                'yearmonth'=> date('Y-m-d')
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Staff member added successfully',
                'staff_id' => $lastInsertID,
                'staff_code' => $staff_code
            ])->setStatusCode(201);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    public function update_staf_api()
    {
        $staff_id = $this->request->getPost('staff_id');
    
        $rules = [
            'user_type' => 'required',
            'name' => 'required|max_length[100]',
            'salary' => 'required|numeric',
            'tel' => 'required|numeric|min_length[10]|max_length[15]',
            'address' => 'required|max_length[255]',
        ];
    
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }
    
        try {
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
    
            // File uploads
            foreach (['img','dl_front','dl_back','aadhaar_front','aadhaar_back'] as $fileField) {
                $file = $this->request->getFile($fileField);
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/', $newName);
                    $data[$fileField] = $newName;
                }
            }
    
            // Update staff record
            $this->db->table('staff')->where('id', $staff_id)->update($data);
    
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Staff details updated successfully',
                'staff_id' => $staff_id,
                'updated_data' => $data
            ])->setStatusCode(200);
    
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    public function tyer_management()
    {
        // Enable JSON response
        helper(['url']);
    
        // Fetch Data
        $tyer_data  = $this->db->query("
            SELECT tm.*, l.location_name, v.name, COUNT(tm.id) as qty
            FROM tyer_management tm
            LEFT JOIN location l ON tm.location_id = l.location_id
            LEFT JOIN vendor v ON tm.vendor_id = v.id
            GROUP BY tm.bill_no, l.location_name, v.name
            ORDER BY MAX(tm.date) DESC
        ")->getResult();
    
        // Return JSON
        return $this->respond([
            'status'  => true,
            'message' => 'Tyer management data fetched successfully.',
            'data'    => [
                'tyer_data' => $tyer_data,
            ]
        ], 200);
    }
    public function insert_tyer_api()
    {
        // Use ResponseTrait for API
        helper(['url']);
        // Get JSON or POST data
        $vendor_id   = $this->request->getVar('vendor_id');
        $date        = $this->request->getVar('date');
        $bill_no     = $this->request->getVar('billno');
        $price       = $this->request->getVar('tamount');
        $location_id = $this->request->getVar('location');
        $brand_name  = $this->request->getVar('brand_name');
        $model       = $this->request->getVar('model');

        $tyer_sl_nos = $this->request->getVar('tyer_sl_no'); // Array
        $tyer_types  = $this->request->getVar('tyer_type');  // Array

        // Basic validation
        if (empty($bill_no) || empty($vendor_id) || empty($tyer_sl_nos) || empty($tyer_types)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Missing required fields.'
            ], 400);
        }

        // Check if bill number exists
        $existingBill = $this->db->table('tyer_management')
            ->where('bill_no', $bill_no)
            ->get()
            ->getRow();

        if ($existingBill) {
            return $this->respond([
                'status'  => false,
                'message' => 'Bill number already exists.'
            ], 409);
        }

        // Insert all tyers
        foreach ($tyer_sl_nos as $index => $tyer_sl_no) {
            $tyer_type = $tyer_types[$index] ?? null;

            $data = [
                'location_id' => $location_id,
                'brand_name'  => $brand_name,
                'tyer_type'   => $tyer_type,
                'model'       => $model,
                'tyer_sl_no'  => $tyer_sl_no,
                'vendor_id'   => $vendor_id,
                'bill_no'     => $bill_no,
                'price'       => $price,
                'status'      => 1,
                'date'        => $date,
            ];

            $this->db->table('tyer_management')->insert($data);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Tyer(s) inserted successfully.',
            'data'    => [
                'bill_no' => $bill_no,
                'total_inserted' => count($tyer_sl_nos)
            ]
        ], 200);
    }
    public function tyer_transfer_api()
    {
        helper(['url']);

        // Get JSON or POST data
        $to_location = $this->request->getVar('to_location');
        $date        = $this->request->getVar('date');
        $tyer_sl_no  = $this->request->getVar('tyer_sl_no'); // array

        // Validate required fields
        if (empty($tyer_sl_no) || empty($to_location) || empty($date)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Please provide to_location, date, and at least one tyre serial number.'
            ], 400);
        }

        // Ensure tyer_sl_no is an array
        if (!is_array($tyer_sl_no)) {
            $tyer_sl_no = [$tyer_sl_no];
        }

        $updatedCount = 0;
        foreach ($tyer_sl_no as $sl_no) {
            if (!empty($sl_no)) {
                $updateData = [
                    'location_id'   => $to_location,
                    'transfer_date' => $date
                ];

                $this->db->table('tyer_management')
                    ->where('tyer_sl_no', $sl_no)
                    ->update($updateData);

                if ($this->db->affectedRows() > 0) {
                    $updatedCount++;
                }
            }
        }

        return $this->respond([
            'status'  => true,
            'message' => $updatedCount > 0
                ? "Tyres transferred successfully."
                : "No records were updated (check serial numbers).",
            'data'    => [
                'to_location'   => $to_location,
                'transfer_date' => $date,
                'updated_count' => $updatedCount
            ]
        ], 200);
    }
    public function getTyerDetailsByBillNo_api()
    {
        helper(['url']);

        // Get bill_no from request (supports JSON or form-data)
        $bill_no = $this->request->getVar('bill_no');

        // Validate input
        if (empty($bill_no)) {
            return $this->respond([
                'status'  => false,
                'message' => 'bill_no is required.'
            ], 400);
        }

        // Fetch tyre details
        $data = $this->db->table('tyer_management')
            ->select('tyer_sl_no, tyer_type, id')
            ->where('bill_no', $bill_no)
            ->get()
            ->getResult();

        // Check if records exist
        if (empty($data)) {
            return $this->respond([
                'status'  => false,
                'message' => 'No tyre details found for the provided bill number.'
            ], 404);
        }

        // Return as JSON
        return $this->respond([
            'status'  => true,
            'message' => 'Tyre details fetched successfully.',
            'data'    => $data
        ], 200);
    }
    public function update_tyer_api()
    {
        helper(['url']);

        // Retrieve request data (JSON or form-data)
        $bill_no     = $this->request->getVar('billno');
        $vendor_id   = $this->request->getVar('vendor_id');
        $date        = $this->request->getVar('date');
        $price       = $this->request->getVar('tamount');
        $location_id = $this->request->getVar('location');
        $brand_name  = $this->request->getVar('brand_name');
        $model       = $this->request->getVar('model');

        $tyer_ids    = $this->request->getVar('tyer_id');    // Array of IDs (0 for new)
        $tyer_sl_nos = $this->request->getVar('tyer_sl_no'); // Array of serial numbers
        $tyer_types  = $this->request->getVar('tyer_type');  // Array of tyre types

        // ✅ Basic validation
        if (empty($bill_no) || empty($vendor_id) || empty($date) || 
            empty($price) || empty($location_id) || 
            empty($brand_name) || empty($model) || 
            empty($tyer_sl_nos) || empty($tyer_types) || empty($tyer_ids)) 
        {
            return $this->respond([
                'status'  => false,
                'message' => 'Missing required fields.'
            ], 400);
        }

        // ✅ Ensure arrays are aligned
        if (count($tyer_ids) !== count($tyer_sl_nos) || count($tyer_ids) !== count($tyer_types)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Tyre arrays length mismatch.'
            ], 400);
        }

        $inserted = 0;
        $updated  = 0;

        // ✅ Insert or Update Tyres
        foreach ($tyer_sl_nos as $index => $tyer_sl_no) {
            $tyer_type = $tyer_types[$index];
            $tyer_id   = $tyer_ids[$index];

            $data = [
                'location_id' => $location_id,
                'brand_name'  => $brand_name,
                'tyer_type'   => $tyer_type,
                'model'       => $model,
                'tyer_sl_no'  => $tyer_sl_no,
                'vendor_id'   => $vendor_id,
                'bill_no'     => $bill_no,
                'price'       => $price,
                'status'      => 1,
                'date'        => $date,
            ];

            if ((int)$tyer_id === 0) {
                // ➕ Insert new record
                $this->db->table('tyer_management')->insert($data);
                $inserted++;
            } else {
                // ✏️ Update existing record
                $this->db->table('tyer_management')->update($data, ['id' => $tyer_id]);
                if ($this->db->affectedRows() > 0) {
                    $updated++;
                }
            }
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Tyer details processed successfully.',
            'data'    => [
                'bill_no'  => $bill_no,
                'inserted' => $inserted,
                'updated'  => $updated
            ]
        ], 200);
    }
    public function StockTyer_management_api()
    {
        helper(['url']);

        // Fetch tyre stock list
        $tyer_list = $this->db->table('tyer_management tm')
            ->select('tm.*, l.location_name')
            ->join('location l', 'l.location_id = tm.location_id', 'left')
            ->where('tm.vehicle_id', null) // Tyres not yet assigned to any vehicle
            ->get()
            ->getResult();

        // Return JSON response
        return $this->respond([
            'status'  => true,
            'message' => 'Stock tyre data fetched successfully.',
            'data'    => [
                'tyer_list' => $tyer_list
            ]
        ], 200);
    }
    public function trashTyer_management_api(){
        
        helper(['url']);

        $tyer_list = $this->db->table('tyer_management tm')
            ->select('tm.*,l.location_name')
            ->join('location l', 'l.location_id = tm.location_id','left')
            ->where('tm.status',3)
            ->get()
            ->getResult();
        return $this->respond([
            'status'  => true,
            'message' => 'Trash tyre data fetched successfully.',
            'data'    => [
                'tyer_list' => $tyer_list
            ]
        ], 200);
    }
    public function getTyersByLocationApi()
    {
        // Get location_id from GET or POST request
        $locationId = $this->request->getVar('location_id');

        // Validate input
        if (empty($locationId)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Location ID is required.',
                'data'    => []
            ]);
        }

        // Fetch available tyres with brand and model
        $tyres = $this->db->table('tyer_management')
            ->select('tyer_sl_no, brand_name, model')
            ->where('location_id', $locationId)
            ->where('status', 1) // Only available stock
            ->get()
            ->getResult();

        // Return JSON response
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Tyres fetched successfully.',
            'data'    => $tyres
        ]);
    }
    public function deleteTyerSingleApi()
    {
        // Get tyre ID from request (POST/GET)
        $tyerId = $this->request->getVar('id');

        // Validate input
        if (empty($tyerId)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tyre ID is required.',
            ]);
        }

        // Check if tyre exists
        $exists = $this->db->table('tyer_management')
            ->where('id', $tyerId)
            ->get()
            ->getRow();

        if (!$exists) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'No tyre found with the provided ID.',
            ]);
        }

        // Delete tyre
        $this->db->table('tyer_management')->delete(['id' => $tyerId]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Tyre deleted successfully.'
        ]);
    }
    public function repaireReportApi()
    {
        // Base SQL query
        $sql = "
            SELECT 
                tm.*, 
                l.location_name, 
                v.name AS exchange_vendorname
            FROM 
                tyer_management tm
            LEFT JOIN 
                location l ON l.location_id = tm.location_id 
            LEFT JOIN 
                vendor v ON v.id = tm.ex_ven_id
            WHERE 
                tm.status IN (4)
        ";

        // Execute query
        $tyerData = $this->db->query($sql)->getResult();

        // Return JSON response
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Repair report fetched successfully.',
            'data'    => $tyerData
        ]);
    }
    public function updateTyerRepairApi()
    {
        // Get request data
        $tyerSlNo = $this->request->getVar('tyer_sl_no');
        $vendor   = $this->request->getVar('vendor');
        $location = $this->request->getVar('location');
        $date     = $this->request->getVar('date');
    
        // Validate input
        if (empty($tyerSlNo) || empty($vendor) || empty($location) || empty($date)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'All fields (tyer_sl_no, vendor, location, date) are required.'
            ]);
        }
    
        // Prepare update data
        $updateData = [
            'vendor_id'   => $vendor,
            'location_id' => $location,
            'date'        => $date,
            'status'      => 1   // Set back to active/available
        ];
    
        // Execute update
        $updated = $this->db->table('tyer_management')
            ->where('tyer_sl_no', $tyerSlNo)
            ->update($updateData);
    
        // Return response
        if ($updated) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Tyre repair details updated successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Failed to update tyre details. Please check the serial number.'
            ]);
        }
    }
    public function gettyerDataApi()
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
                return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'stock Tyres fetched successfully.',
                    'data'    => $tyer_data
                ]);
            } else {
                // Return an empty array if no data found
                return $this->response->setJSON([]);
            }
        } else {
            // Handle the case where location_id is not valid
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Failed to fetch stock tyres.'
            ]);
        }
    }
}
