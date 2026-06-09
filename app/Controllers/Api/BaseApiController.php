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
        $profileImage = $user->profile_image ?? '';
        if ($profileImage !== '' && $profileImage !== null) {
            $profileImage = base_url('uploads/' . ltrim($profileImage, '/'));
        } else {
            $profileImage = null;
        }

        return [
            'user_id'            => (int) $user->id,
            'fullname'           => $user->full_name,
            'username'           => $user->user_name,
            'email'              => $user->email,
            'contact'            => $user->contact_no,
            'whatsapp'           => $user->whatsapp_no,
            'user_type'          => (int) $user->user_type,
            'location_id'        => $user->location_id ? (int) $user->location_id : null,
            'location_name'      => $user->location_name ?? null,
            'location_shortname' => $user->location_shordname ?? null,
            'roles'              => $user->roles,
            'profile_image'      => $profileImage,
            'status'             => (int) $user->status,
        ];
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
