<?php

namespace App\Libraries;

/**
 * Request-scoped authenticated API user (set by ApiAuthFilter).
 */
class ApiAuthContext
{
    protected static ?object $user = null;
    protected static ?object $token = null;

    public static function set(object $user, ?object $token = null): void
    {
        self::$user  = $user;
        self::$token = $token;
    }

    public static function user(): ?object
    {
        return self::$user;
    }

    public static function token(): ?object
    {
        return self::$token;
    }

    public static function userId(): ?int
    {
        return self::$user ? (int) self::$user->id : null;
    }

    public static function clear(): void
    {
        self::$user  = null;
        self::$token = null;
    }
}
