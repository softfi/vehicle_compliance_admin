<?php

namespace App\Filters;

use App\Libraries\ApiAuthContext;
use App\Models\ApiTokenModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        ApiAuthContext::clear();

        $header = $request->getHeaderLine('Authorization');
        if ($header === '' || ! preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return $this->unauthorized('05', 'Authorization Bearer token is required.');
        }

        $plainToken = $matches[1];
        $tokenModel = new ApiTokenModel();
        $tokenRow   = $tokenModel->findValidByPlainToken($plainToken);

        if (! $tokenRow) {
            $existing = $tokenModel->where('token_hash', $tokenModel->hashToken($plainToken))->first();

            if ($existing && $existing->revoked_at !== null) {
                return $this->unauthorized('07', 'Token has been revoked. Please login again.');
            }

            return $this->unauthorized('06', 'Invalid or expired token.');
        }

        $db = db_connect();
        $user = $db->query("
            SELECT u.*, l.location_name, l.location_shordname
            FROM user u
            LEFT JOIN location l ON l.location_id = u.location_id
            WHERE u.id = ?
              AND u.user_type = 2
              AND u.deleted_by IS NULL
            LIMIT 1
        ", [$tokenRow->user_id])->getRow();

        if (! $user) {
            return $this->unauthorized('06', 'Invalid or expired token.');
        }

        $tokenModel->touchLastUsed((int) $tokenRow->id);
        ApiAuthContext::set($user, $tokenRow);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        ApiAuthContext::clear();
    }

    protected function unauthorized(string $responseCode, string $message)
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'status'   => 401,
                'error'    => true,
                'messages' => [
                    'responsecode' => $responseCode,
                    'message'      => $message,
                ],
            ]);
    }
}
