<?php

namespace App\Libraries;

/**
 * Haversine distance helpers for geofencing.
 * location.radius is stored in meters.
 */
class GeoLocation
{
    private const EARTH_RADIUS_METERS = 6371000.0;

    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $latTo   = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    public static function isWithinRadius(
        float $userLat,
        float $userLng,
        float $centerLat,
        float $centerLng,
        float $radiusMeters
    ): bool {
        if ($radiusMeters <= 0) {
            return false;
        }

        return self::distanceMeters($userLat, $userLng, $centerLat, $centerLng) <= $radiusMeters;
    }

    public static function isValidLatitude(float $lat): bool
    {
        return $lat >= -90.0 && $lat <= 90.0;
    }

    public static function isValidLongitude(float $lng): bool
    {
        return $lng >= -180.0 && $lng <= 180.0;
    }
}
