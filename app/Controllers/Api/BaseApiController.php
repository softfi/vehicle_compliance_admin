<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\ApiAuthContext;
use App\Models\AdminModel;
use CodeIgniter\API\ResponseTrait;

abstract class BaseApiController extends BaseController
{
    use ResponseTrait;

    protected $db;
    protected AdminModel $adminModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->adminModel = new AdminModel($this->db);
        helper(['form', 'url']);
    }

    protected function authUser(): object
    {
        $user = ApiAuthContext::user();
        if (! $user) {
            throw new \RuntimeException('Authenticated user not available.');
        }

        return $user;
    }

    protected function authUserId(): int
    {
        return (int) $this->authUser()->id;
    }

    protected function formatUserPayload(object $user): array
    {
        $profileImageFile = trim((string) ($user->profile_image ?? ''));
        $profileImageUrl = $profileImageFile !== ''
            ? base_url('uploads/' . ltrim($profileImageFile, '/'))
            : null;

        $rolesRaw = trim((string) ($user->roles ?? ''));
        $rolesList = $rolesRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $rolesRaw))))
            : [];

        $userType = isset($user->user_type) ? (int) $user->user_type : null;

        return [
            'user_id'            => (int) $user->id,
            'fullname'           => $user->full_name ?? null,
            'username'           => $user->user_name ?? null,
            'email'              => $user->email ?? null,
            'contact'            => $user->contact_no ?? null,
            'whatsapp'           => $user->whatsapp_no ?? null,
            'user_type'          => $userType,
            'user_type_label'    => $this->resolveUserTypeLabel($userType),
            'is_admin'           => $userType === 1,
            'is_sub_admin'       => $userType === 2,
            'location_id'        => ! empty($user->location_id) ? (int) $user->location_id : null,
            'location_name'      => $user->location_name ?? null,
            'location_shortname' => $user->location_shordname ?? null,
            'roles'              => $rolesRaw !== '' ? $rolesRaw : null,
            'roles_list'         => $rolesList,
            'permissions_list'   => $this->resolvePermissionNames($rolesList),
            'profile_image'      => $profileImageUrl,
            'profile_image_url'  => $profileImageUrl,
            'profile_image_file' => $profileImageFile !== '' ? $profileImageFile : null,
            'status'             => isset($user->status) ? (int) $user->status : null,
        ];
    }

    protected function resolvePermissionNames(array $roleIds): array
    {
        $allPermissions = [
            '28' => 'Dashboard',
            '27' => 'Sub admin',
            '27.1' => 'Sub admin Submit',
            '27.2' => 'Sub admin Deactive/Active',
            '27.3' => 'Sub admin Edit',
            '27.4' => 'Sub admin Delete',
            '27.5' => 'Sub admin Role',
            '1' => 'Purchase',
            '1.1' => 'Purchase Enter Stock',
            '1.2' => 'Purchase View',
            '1.3' => 'Purchase Delete',
            '1.4' => 'Purchase Edit',
            '2' => 'Do Registration',
            '2.1' => 'Do Registration Submit',
            '2.2' => 'Do Registration Change Price',
            '2.3' => 'Do Registration Edit',
            '2.4' => 'Do Registration Delete',
            '3' => 'Despatch Entry',
            '3.1' => 'Despatch Entry Submit',
            '3.2' => 'Despatch Entry Upload Excel',
            '3.3' => 'Despatch Entry Filter',
            '3.4' => 'Despatch Entry Download Excel',
            '3.5' => 'Despatch Entry Edit',
            '3.6' => 'Despatch Entry Delete',
            '3.7' => 'Despatch Entry Delete Multiple',
            '36' => 'Voucher Entry / Group',
            '36.1' => 'Voucher Entry Add / Group Status',
            '36.2' => 'Group Delete',
            '4' => 'Diesel Entry',
            '4.1' => 'Diesel Entry Submit',
            '4.2' => 'Diesel Entry Upload Excel',
            '4.3' => 'Diesel Entry Filter',
            '4.4' => 'Diesel Entry Download Excel',
            '4.5' => 'Diesel Entry Delete',
            '4.6' => 'Diesel Entry Edit',
            '4.7' => 'Diesel Entry Delete Multiple',
            '4.8' => 'Diesel Entry Delete all',
            '5' => 'Inhouse Maintenance',
            '5.1' => 'Inhouse Maintenance Add New',
            '5.2' => 'Inhouse Maintenance Edit',
            '5.3' => 'Inhouse Maintenance View',
            '5.4' => 'Inhouse Maintenance Delete',
            '6' => 'Outside Maintenance',
            '6.1' => 'Outside Maintenance Submit',
            '6.2' => 'Outside Maintenance Delete',
            '7' => 'Staff Advance',
            '7.1' => 'Staff Advance Submit',
            '7.2' => 'Staff Advance Upload Excel',
            '7.3' => 'Staff Advance Edit',
            '7.4' => 'Staff Advance Delete',
            '8' => 'Driver Assignment',
            '8.1' => 'Driver Assignment Submit',
            '8.2' => 'Driver Assignment Filter',
            '8.3' => 'Driver Assignment Edit',
            '8.4' => 'Driver Assignment Delete',
            '9' => 'Regular Checkup',
            '9.1' => 'Regular Checkup Submit',
            '10' => 'Overall Expense',
            '10.1' => 'Overall Expense Submit',
            '10.2' => 'Overall Expense Download Excel',
            '10.3' => 'Overall Expense Delete',
            '11' => 'Driver Salary',
            '11.1' => 'Driver Salary Submit',
            '35' => 'Adjust Salary',
            '35.1' => 'Adjust Salary Submit',
            '35.2' => 'Adjust Salary Sample Excel',
            '35.3' => 'Adjust Salary Upload',
            '35.4' => 'Adjust Salary Filter',
            '35.5' => 'Adjust Salary Edit',
            '35.6' => 'Adjust Salary Delete',
            '12' => 'Staff Salary',
            '12.1' => 'Staff Salary Submit',
            '12.2' => 'Staff Salary Download Excel',
            '29' => 'Vehicle',
            '13' => 'Vehicle Master',
            '13.1' => 'Vehicle Master Upload Excel',
            '13.2' => 'Vehicle Master Admin New Vehicle',
            '13.3' => 'Vehicle Master Sample Excel',
            '13.4' => 'Vehicle Master Edit',
            '13.5' => 'Vehicle Master Delete',
            '14' => 'Statutory Entry',
            '14.1' => 'Statutory Entry Submit',
            '14.2' => 'Statutory Entry Delete',
            '14.3' => 'Statutory Entry Edit',
            '30' => 'Master Entry',
            '15' => 'Add Staff/Driver',
            '15.1' => 'Add Staff/Driver Upload Excel',
            '15.2' => 'Add Staff/Driver Add Staff',
            '15.3' => 'Add Staff/Driver Edit',
            '15.4' => 'Add Staff/Driver Delete',
            '16' => 'Vendor',
            '16.1' => 'Vendor Upload Excel',
            '16.2' => 'Vendor Add Vendor/Party',
            '16.3' => 'Vendor Edit',
            '16.4' => 'Vendor View Rate',
            '16.5' => 'Vendor Delete',
            '17' => 'Items',
            '17.1' => 'Items Submit',
            '17.2' => 'Items Upload Excel',
            '17.4' => 'Items Edit',
            '17.5' => 'Items Delete',
            '18' => 'Unit',
            '18.1' => 'Unit Submit',
            '18.2' => 'Unit Upload Excel',
            '18.3' => 'Unit Edit',
            '18.4' => 'Unit Delete',
            '19' => 'Location',
            '19.1' => 'Location Submit',
            '19.2' => 'Location Upload Excel',
            '19.3' => 'Location Edit',
            '19.4' => 'Location Delete',
            '20' => 'Route',
            '20.1' => 'Route Submit',
            '20.2' => 'Route Upload Excel',
            '20.3' => 'Route Edit',
            '20.4' => 'Route Delete',
            '21' => 'Bank',
            '21.1' => 'Bank Submit',
            '21.2' => 'Bank Edit',
            '21.3' => 'Bank Delete',
            '31' => 'Report',
            '22' => 'Stock Report',
            '22.1' => 'Stock Report Submit',
            '23' => 'Vehicle Ledger',
            '23.1' => 'Vehicle Ledger Submit',
            '32' => 'Tyre Management',
            '24' => 'Purchase Tyre',
            '24.1' => 'Purchase Tyre Enter Tyre Stock',
            '24.2' => 'Purchase Tyre View',
            '24.3' => 'Purchase Tyre Edit',
            '24.4' => 'Purchase Tyre Delete',
            '25' => 'Assign Tyre',
            '25.1' => 'Assign Tyre Assign',
            '25.2' => 'Assign Tyre Exchange',
            '26' => 'Report Filter',
            '26.1' => 'Report Filter',
            '33' => 'Repair Report',
            '33.1' => 'Repair Report Submit',
            '38' => 'Scrap Tyre',
            '38.1' => 'Scrap Tyre Back To Stock',
            '34' => 'Download Database',
            '37' => 'Task Assignment',
            '37.1' => 'Task Assignment Edit',
            '37.2' => 'Task Assignment Delete',
            '40' => 'Payment Voucher',
            '40.1' => 'Payment Voucher Submit',
            '40.2' => 'Payment Voucher Upload',
            '40.3' => 'Payment Voucher Edit',
            '40.4' => 'Payment Voucher Delete',
            '41' => 'Payment Report',
            '42' => 'Pump',
            '42.1' => 'Pump Submit',
            '43' => 'Party',
            '43.1' => 'Party Submit',
            '44' => 'Vendor (Other)',
            '44.1' => 'Vendor (Other) Submit',
            '45' => 'Attendance / View Attendance',
            '46' => 'Attendance Add',
            '47' => 'Attendance Bulk Upload',
            '48' => 'Attendance Reports',
        ];

        $resolved = [];
        foreach ($roleIds as $id) {
            $idStr = (string) $id;
            $resolved[] = [
                'id'   => $idStr,
                'name' => $allPermissions[$idStr] ?? 'Unknown Permission'
            ];
        }

        return $resolved;
    }

    protected function resolveUserTypeLabel(?int $userType): ?string
    {
        return match ($userType) {
            1       => 'Admin',
            2       => 'Sub Admin',
            default => null,
        };
    }

    protected function isAdminUser(?object $user = null): bool
    {
        $user = $user ?? $this->authUser();

        return (int) ($user->user_type ?? 0) === 1;
    }

    protected function hasRole(string $role): bool
    {
        $roles = array_map('trim', explode(',', (string) ($this->authUser()->roles ?? '')));

        return in_array($role, $roles, true);
    }

    /**
     * Query location_id overrides logged-in sub-admin location when provided.
     */
    protected function resolveLocationIdForUser(?object $user = null): ?int
    {
        $fromGet = $this->request->getGet('location_id');
        if ($fromGet !== null && $fromGet !== '') {
            return (int) $fromGet;
        }

        $user = $user ?? $this->authUser();
        if ($user && ! empty($user->location_id)) {
            return (int) $user->location_id;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseRequestPayload(): array
    {
        $post = $this->request->getPost();
        if (is_array($post) && $post !== []) {
            return $post;
        }

        $raw = (string) $this->request->getBody();
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Merge POST/form fields with JSON body when Content-Type is application/json.
     * Safe for multipart/form-data (does not call getJSON on non-JSON requests).
     *
     * @return array<string, mixed>
     */
    protected function mergeRequestPayload(): array
    {
        $payload = $this->normalizeFormPayload($this->parseRequestPayload());

        if ($this->request->is('json')) {
            try {
                $json = $this->request->getJSON(true);
                if (is_array($json) && $json !== []) {
                    $payload = array_merge($payload, $this->normalizeFormPayload($json));
                }
            } catch (\CodeIgniter\HTTP\Exceptions\HTTPException) {
                // Ignore invalid JSON body; form payload may still be valid.
            }
        }

        return $payload;
    }

    /**
     * Strip accidental surrounding quotes from multipart form text values.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    protected function normalizeFormPayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if (
                (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"'))
                || (str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'"))
            ) {
                $payload[$key] = substr($trimmed, 1, -1);
            }
        }

        return $payload;
    }

    /**
     * Read a value from merged payload or request vars (first non-empty key wins).
     *
     * @param array<string, mixed> $payload
     * @param list<string>         $keys
     */
    protected function payloadValue(array $payload, array $keys, mixed $default = ''): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== '' && $payload[$key] !== null) {
                return $payload[$key];
            }

            $fromRequest = $this->request->getVar($key);
            if ($fromRequest !== null && $fromRequest !== '') {
                return $fromRequest;
            }
        }

        return $default;
    }

    protected function apiSuccess(string $message, array $data = [], int $httpStatus = 200)
    {
        return $this->respond([
            'status'   => $httpStatus,
            'error'    => false,
            'messages' => [
                'responsecode' => '00',
                'message'      => $message,
                'data'         => $data,
            ],
        ], $httpStatus);
    }

    protected function apiError(string $responseCode, string $message, int $httpStatus = 400)
    {
        return $this->respond([
            'status'   => $httpStatus,
            'error'    => true,
            'messages' => [
                'responsecode' => $responseCode,
                'message'      => $message,
            ],
        ], $httpStatus);
    }

    /**
     * Parse list filters: date range, vehicle, location (web filter_* aliases supported).
     *
     * @return array{errors: list<string>, filters: array<string, mixed>}
     */
    protected function parseDieselListFilters(bool $includeDriver = false): array
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

        $vehicleId = (int) ($this->request->getGet('vehicle_id')
            ?? $this->request->getGet('filter_vehicle')
            ?? $this->request->getGet('vehicle')
            ?? 0);

        $locationId = (int) ($this->request->getGet('location_id')
            ?? $this->request->getGet('filter_location')
            ?? $this->request->getGet('location')
            ?? 0);

        $filters = [
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'vehicle_id'  => $vehicleId > 0 ? $vehicleId : null,
            'location_id' => $locationId > 0 ? $locationId : null,
        ];

        if ($includeDriver) {
            $driverId = (int) ($this->request->getGet('driver_id')
                ?? $this->request->getGet('filter_driver')
                ?? $this->request->getGet('driver')
                ?? 0);
            $filters['driver_id'] = $driverId > 0 ? $driverId : null;
        }

        return ['errors' => $errors, 'filters' => $filters];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function formatLocationBlock(?int $locationId, ?string $locationName, ?string $locationShortname = null): ?array
    {
        if ($locationId === null || $locationId <= 0) {
            return null;
        }

        return [
            'id'                 => $locationId,
            'location_id'        => $locationId,
            'location_name'      => $locationName,
            'location_shortname' => $locationShortname,
        ];
    }
}
