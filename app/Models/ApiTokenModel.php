<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Api as ApiConfig;

class ApiTokenModel extends Model
{
    protected $table            = 'api_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'user_id',
        'token_hash',
        'device_id',
        'expires_at',
        'last_used_at',
        'revoked_at',
        'created_at',
    ];

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function createForUser(int $userId, ?string $deviceId = null): array
    {
        $config = config(ApiConfig::class);
        $plainToken = bin2hex(random_bytes(32));
        $expiresAt  = date('Y-m-d H:i:s', strtotime('+' . $config->tokenTtlDays . ' days'));

        if ($config->maxTokensPerUser > 0) {
            $this->enforceTokenLimit($userId, $config->maxTokensPerUser);
        }

        $this->insert([
            'user_id'    => $userId,
            'token_hash' => $this->hashToken($plainToken),
            'device_id'  => $deviceId,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'access_token' => $plainToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $expiresAt,
            'expires_in'   => $config->tokenTtlDays * 86400,
        ];
    }

    public function findValidByPlainToken(string $plainToken): ?object
    {
        if ($plainToken === '') {
            return null;
        }

        $row = $this->where('token_hash', $this->hashToken($plainToken))
            ->where('revoked_at', null)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        return $row ?: null;
    }

    public function revokeByPlainToken(string $plainToken): bool
    {
        $row = $this->findValidByPlainToken($plainToken);

        if (! $row) {
            return false;
        }

        return $this->update($row->id, ['revoked_at' => date('Y-m-d H:i:s')]);
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->where('user_id', $userId)
            ->where('revoked_at', null)
            ->set(['revoked_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    public function touchLastUsed(int $tokenId): void
    {
        $this->update($tokenId, ['last_used_at' => date('Y-m-d H:i:s')]);
    }

    protected function enforceTokenLimit(int $userId, int $maxTokens): void
    {
        $active = $this->where('user_id', $userId)
            ->where('revoked_at', null)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'ASC')
            ->findAll();

        $overflow = count($active) - $maxTokens + 1;
        if ($overflow < 1) {
            return;
        }

        foreach (array_slice($active, 0, $overflow) as $token) {
            $this->update($token->id, ['revoked_at' => date('Y-m-d H:i:s')]);
        }
    }
}
