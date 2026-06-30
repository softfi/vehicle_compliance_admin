<?php

namespace App\Controllers\Api;

use App\Models\ApiTokenModel;

class AuthController extends BaseApiController
{
    protected ApiTokenModel $tokenModel;

    public function __construct()
    {
        parent::__construct();
        $this->tokenModel = new ApiTokenModel();
    }

    /**
     * POST /api/subadmin/login
     * POST /api/login (alias)
     *
     * Admin (user_type=1) aur Sub-admin (user_type=2) dono login kar sakte hain.
     * Body: username, password, device_id (optional)
     */
    public function login()
    {
        $payload = $this->parseRequestPayload();

        $username = trim($payload['username'] ?? '');
        $password = $payload['password'] ?? '';
        $deviceId = trim($payload['device_id'] ?? '') ?: null;

        if ($username === '' || $password === '') {
            return $this->respond([
                'status'   => 400,
                'error'    => true,
                'messages' => [
                    'responsecode' => '03',
                    'message'      => 'Username and password are required.',
                ],
            ], 400);
        }

        $encodedPassword = base64_encode(base64_encode($password));

        $user = $this->db->query("
            SELECT u.*, l.location_name, l.location_shordname
            FROM user u
            LEFT JOIN location l ON l.location_id = u.location_id
            WHERE u.user_name = ?
              AND u.user_type IN (1, 2)
              AND u.deleted_by IS NULL
            LIMIT 1
        ", [$username])->getRow();

        if (! $user) {
            return $this->respond([
                'status'   => 400,
                'error'    => true,
                'messages' => [
                    'responsecode' => '02',
                    'message'      => 'User not found.',
                ],
            ], 400);
        }

        if ((int) ($user->status ?? 0) !== 1) {
            return $this->respond([
                'status'   => 400,
                'error'    => true,
                'messages' => [
                    'responsecode' => '08',
                    'message'      => 'Your account is inactive. Contact administrator.',
                ],
            ], 400);
        }

        if ($user->password !== $encodedPassword) {
            return $this->respond([
                'status'   => 400,
                'error'    => true,
                'messages' => [
                    'responsecode' => '01',
                    'message'      => 'Invalid password.',
                ],
            ], 400);
        }

        $tokenData = $this->tokenModel->createForUser((int) $user->id, $deviceId);

        return $this->respond([
            'status'   => 200,
            'error'    => false,
            'messages' => [
                'responsecode' => '00',
                'message'      => 'Login successful.',
                'data'         => array_merge(
                    $this->formatUserPayload($user),
                    [
                        'access_token' => $tokenData['access_token'],
                        'token_type'   => $tokenData['token_type'],
                        'expires_at'   => $tokenData['expires_at'],
                        'expires_in'   => $tokenData['expires_in'],
                        'isLoggedIn'   => true,
                    ]
                ),
            ],
        ], 200);
    }

    /**
     * GET /api/subadmin/me
     * GET /api/profile
     *
     * Logged-in sub-admin profile with profile image URL.
     * Header: Authorization: Bearer {token}
     */
    public function me()
    {
        return $this->profile();
    }

    /**
     * GET /api/profile
     */
    public function profile()
    {
        $authUser = $this->authUser();
        $userId = (int) ($authUser->id ?? 0);

        $user = $this->db->query("
            SELECT u.*, l.location_name, l.location_shordname
            FROM user u
            LEFT JOIN location l ON l.location_id = u.location_id
            WHERE u.id = ?
              AND u.user_type IN (1, 2)
              AND u.deleted_by IS NULL
            LIMIT 1
        ", [$userId])->getRow();

        if ($user === null) {
            return $this->apiError('04', 'User profile not found.', 404);
        }

        return $this->apiSuccess('Profile loaded.', [
            'profile' => array_merge(
                $this->formatUserPayload($user),
                ['is_logged_in' => true]
            ),
        ]);
    }

    /**
     * POST /api/subadmin/logout
     * Header: Authorization: Bearer {token}
     */
    public function logout()
    {
        $plainToken = $this->extractBearerToken();
        if ($plainToken === '') {
            return $this->respond([
                'status'   => 401,
                'error'    => true,
                'messages' => [
                    'responsecode' => '05',
                    'message'      => 'Authorization Bearer token is required.',
                ],
            ], 401);
        }

        $this->tokenModel->revokeByPlainToken($plainToken);

        return $this->respond([
            'status'   => 200,
            'error'    => false,
            'messages' => [
                'responsecode' => '00',
                'message'      => 'Logged out successfully.',
            ],
        ], 200);
    }

    /**
     * POST /api/subadmin/refresh
     * Header: Authorization: Bearer {token}
     */
    public function refresh()
    {
        $plainToken = $this->extractBearerToken();
        if ($plainToken === '') {
            return $this->respond([
                'status'   => 401,
                'error'    => true,
                'messages' => [
                    'responsecode' => '05',
                    'message'      => 'Authorization Bearer token is required.',
                ],
            ], 401);
        }

        $user = $this->authUser();
        $this->tokenModel->revokeByPlainToken($plainToken);

        $payload = $this->parseRequestPayload();
        $deviceId = trim($payload['device_id'] ?? '') ?: null;

        $tokenData = $this->tokenModel->createForUser((int) $user->id, $deviceId);

        return $this->respond([
            'status'   => 200,
            'error'    => false,
            'messages' => [
                'responsecode' => '00',
                'message'      => 'Token refreshed.',
                'data'         => array_merge(
                    $this->formatUserPayload($user),
                    [
                        'access_token' => $tokenData['access_token'],
                        'token_type'   => $tokenData['token_type'],
                        'expires_at'   => $tokenData['expires_at'],
                        'expires_in'   => $tokenData['expires_in'],
                        'isLoggedIn'   => true,
                    ]
                ),
            ],
        ], 200);
    }

    protected function extractBearerToken(): string
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
