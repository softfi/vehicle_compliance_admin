<?php

namespace App\Controllers\Api;

/**
 * Driver APIs (admin/staf → Add_staf with user_type DRIVER).
 */
class DriverController extends BaseApiController
{
    /**
     * Same options as web admin/staf blood group dropdown.
     *
     * @return list<string>
     */
    private function bloodGroupOptions(): array
    {
        return ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
    }

    /**
     * GET /api/drivers/blood-groups
     * Same list as web admin/staf → Blood Group dropdown.
     */
    public function bloodGroups()
    {
        $groups = [];
        foreach ($this->bloodGroupOptions() as $value) {
            $groups[] = [
                'value' => $value,
                'label' => $value,
            ];
        }

        return $this->apiSuccess('Blood groups loaded.', [
            'total'        => count($groups),
            'blood_groups' => $groups,
        ]);
    }

    /**
     * GET /api/drivers
     * Driver master list with full details (admin/staf → user_type DRIVER).
     *
     * Optional query:
     * - search (name, staff_code, tel, dl_number, aadhaar_no)
     * - location_id
     * - status (active|resigned|all — default all)
     * - as_on_date (YYYY-MM-DD, used with status; default today)
     */
    public function index()
    {
        $status   = strtolower(trim((string) ($this->request->getGet('status') ?? 'all')));
        $asOnDate = trim((string) ($this->request->getGet('as_on_date') ?? date('Y-m-d')));

        if (! in_array($status, ['active', 'resigned', 'all', ''], true)) {
            return $this->apiError('03', 'status must be active, resigned, or all.', 400);
        }
        if ($status === '') {
            $status = 'all';
        }
        if ($asOnDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOnDate)) {
            return $this->apiError('03', 'as_on_date must be YYYY-MM-DD.', 400);
        }

        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('location')
            ?? 0);
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $rows = $this->adminModel->getDriverList([
            'search'      => $search,
            'location_id' => $locationId,
            'status'      => $status === 'all' ? '' : $status,
            'as_on_date'  => $asOnDate,
        ]);

        $drivers = [];
        foreach ($rows as $row) {
            $drivers[] = $this->formatDriver($row);
        }

        return $this->apiSuccess('Drivers loaded.', [
            'filters' => [
                'search'      => $search !== '' ? $search : null,
                'location_id' => $locationId > 0 ? $locationId : null,
                'status'      => $status,
                'as_on_date'  => $asOnDate,
            ],
            'total'   => count($drivers),
            'drivers' => $drivers,
        ]);
    }

    /**
     * GET /api/drivers/{id}
     * Single driver details.
     */
    public function show($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid driver id is required.', 400);
        }

        $driver = $this->adminModel->getStaffById($id);
        if ($driver === null || strtoupper((string) ($driver->user_type ?? '')) !== 'DRIVER') {
            return $this->apiError('04', 'Driver not found.', 404);
        }

        return $this->apiSuccess('Driver loaded.', [
            'driver' => $this->formatDriver($driver),
        ]);
    }

    /**
     * POST /api/drivers/store
     * Same as web admin/Add_staf when user_type = DRIVER.
     *
     * JSON body or multipart/form-data supported.
     * Optional files: img, dl_front, dl_back, aadhaar_front, aadhaar_back
     */
    public function store()
    {
        $payload = $this->mergeRequestPayload();

        $name            = trim((string) $this->payloadValue($payload, ['name'], ''));
        $doj             = trim((string) $this->payloadValue($payload, ['doj', 'date_of_join'], date('Y-m-d')));
        $resignDate      = trim((string) $this->payloadValue($payload, ['resign_date'], ''));
        $salary          = $this->payloadValue($payload, ['salary'], '');
        $tel             = trim((string) $this->payloadValue($payload, ['tel', 'contact', 'contact_no'], ''));
        $locationId      = (int) $this->payloadValue($payload, ['location_id', 'location'], 0);
        $dlNumber        = trim((string) $this->payloadValue($payload, ['dl_number'], ''));
        $dlExpiry        = trim((string) $this->payloadValue($payload, ['dl_expiry'], ''));
        $aadhaarNo       = trim((string) $this->payloadValue($payload, ['aadhaar_no'], ''));
        $panNo           = strtoupper(trim((string) $this->payloadValue($payload, ['pan_no'], '')));
        $nameBank        = trim((string) $this->payloadValue($payload, ['name_bank'], ''));
        $acNo            = trim((string) $this->payloadValue($payload, ['ac_no', 'account_no'], ''));
        $ifsc            = trim((string) $this->payloadValue($payload, ['ifsc', 'ifsc_code'], ''));
        $fathersName     = trim((string) $this->payloadValue($payload, ['fathers_name', 'father_name'], ''));
        $spouseName      = trim((string) $this->payloadValue($payload, ['spouse_name'], ''));
        $dob             = trim((string) $this->payloadValue($payload, ['dob', 'date_of_birth'], ''));
        $familyContact   = trim((string) $this->payloadValue($payload, ['family_contact'], ''));
        $bloodGroup      = trim((string) $this->payloadValue($payload, ['blood_group'], ''));
        $address         = trim((string) $this->payloadValue($payload, ['address', 'home_address'], ''));
        $openingBalance  = (float) $this->payloadValue($payload, ['opening_balance'], 0);

        $errors = [];
        if ($name === '') {
            $errors[] = 'name is required';
        }
        if ($salary === '' || ! is_numeric($salary)) {
            $errors[] = 'salary is required and must be numeric';
        }
        if ($tel === '' || ! preg_match('/^\d{10,15}$/', $tel)) {
            $errors[] = 'tel is required (10-15 digits)';
        }
        if ($doj !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $doj)) {
            $errors[] = 'doj must be YYYY-MM-DD';
        }
        if ($resignDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $resignDate)) {
            $errors[] = 'resign_date must be YYYY-MM-DD';
        }
        if ($dlExpiry !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dlExpiry)) {
            $errors[] = 'dl_expiry must be YYYY-MM-DD';
        }
        if ($dob !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $errors[] = 'dob must be YYYY-MM-DD';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        if ($resignDate !== '' && $doj !== '' && $doj !== '0000-00-00' && $resignDate < $doj) {
            return $this->apiError('03', 'resign_date cannot be before doj.', 400);
        }

        if ($locationId > 0) {
            $location = $this->db->table('location')->where('location_id', $locationId)->get()->getRow();
            if ($location === null) {
                return $this->apiError('04', 'Location not found.', 404);
            }
        }

        $insert = [
            'user_type'       => 'DRIVER',
            'name'            => $name,
            'doj'             => $doj !== '' ? $doj : null,
            'salary'          => (float) $salary,
            'img'             => $this->uploadStaffFile('img'),
            'name_bank'       => $nameBank,
            'ac_no'           => $acNo,
            'ifsc'            => $ifsc,
            'dl_front'        => $this->uploadStaffFile('dl_front'),
            'dl_back'         => $this->uploadStaffFile('dl_back'),
            'dl_number'       => $dlNumber,
            'dl_expiry'       => $dlExpiry !== '' ? $dlExpiry : null,
            'aadhaar_no'      => $aadhaarNo,
            'aadhaar_front'   => $this->uploadStaffFile('aadhaar_front'),
            'aadhaar_back'    => $this->uploadStaffFile('aadhaar_back'),
            'pan_no'          => $panNo !== '' ? $panNo : null,
            'tel'             => $tel,
            'fathers_name'    => $fathersName,
            'spouse_name'     => $spouseName,
            'dob'             => $dob !== '' ? $dob : null,
            'family_contact'  => $familyContact,
            'blood_group'     => $bloodGroup,
            'opening_balance' => $openingBalance,
            'address'         => $address,
            'location_id'     => $locationId > 0 ? $locationId : null,
            'resign_date'     => $resignDate !== '' ? $resignDate : null,
        ];

        $result = $this->adminModel->storeStaffMember($insert, $openingBalance);
        if ($result === null) {
            return $this->apiError('06', 'Failed to create driver.', 500);
        }

        $driver = $this->adminModel->getStaffById($result['staff_id']);

        return $this->apiSuccess('Driver created successfully.', [
            'driver' => $this->formatDriver($driver),
        ], 201);
    }

    private function formatDriver(?object $row): ?array
    {
        if ($row === null) {
            return null;
        }

        $img = trim((string) ($row->img ?? ''));
        $resignDate = trim((string) ($row->resign_date ?? ''));
        $isActive   = $resignDate === ''
            || $resignDate === '0000-00-00'
            || $resignDate >= date('Y-m-d');

        return [
            'driver_id'       => (int) ($row->id ?? 0),
            'staff_id'        => (int) ($row->id ?? 0),
            'staff_code'      => $row->staff_code ?? null,
            'name'            => $row->name ?? null,
            'user_type'       => $row->user_type ?? 'DRIVER',
            'is_active'       => $isActive,
            'doj'             => $row->doj ?? null,
            'resign_date'     => $row->resign_date ?? null,
            'salary'          => isset($row->salary) ? (float) $row->salary : null,
            'tel'             => $row->tel ?? null,
            'dl_number'       => $row->dl_number ?? null,
            'dl_expiry'       => $row->dl_expiry ?? null,
            'aadhaar_no'      => $row->aadhaar_no ?? null,
            'pan_no'          => $row->pan_no ?? null,
            'name_bank'       => $row->name_bank ?? null,
            'ac_no'           => $row->ac_no ?? null,
            'ifsc'            => $row->ifsc ?? null,
            'fathers_name'    => $row->fathers_name ?? null,
            'spouse_name'     => $row->spouse_name ?? null,
            'dob'             => $row->dob ?? null,
            'family_contact'  => $row->family_contact ?? null,
            'blood_group'     => $row->blood_group ?? null,
            'opening_balance' => isset($row->opening_balance) ? (float) $row->opening_balance : 0.0,
            'address'         => $row->address ?? null,
            'location_id'     => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name'   => $row->location_name ?? null,
            'photo_url'       => $img !== '' ? base_url('uploads/' . $img) : null,
            'dl_front_url'    => ! empty($row->dl_front) ? base_url('uploads/' . $row->dl_front) : null,
            'dl_back_url'     => ! empty($row->dl_back) ? base_url('uploads/' . $row->dl_back) : null,
            'aadhaar_front_url' => ! empty($row->aadhaar_front) ? base_url('uploads/' . $row->aadhaar_front) : null,
            'aadhaar_back_url'  => ! empty($row->aadhaar_back) ? base_url('uploads/' . $row->aadhaar_back) : null,
        ];
    }

    private function uploadStaffFile(string $field): string
    {
        $file = $this->request->getFile($field);
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return '';
        }

        $fileName = $file->getRandomName();
        $file->move('uploads/', $fileName);

        return $fileName;
    }
}
