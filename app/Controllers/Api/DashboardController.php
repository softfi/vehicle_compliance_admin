<?php

namespace App\Controllers\Api;

class DashboardController extends BaseApiController
{
    /**
     * GET /api/dashboard
     * Header: Authorization: Bearer {token}
     * Optional: ?date=2026-06-01 (defaults to today — used for active driver count)
     *
     * Returns total active vehicles and drivers across ALL locations.
     */
    public function index()
    {
        $user     = $this->authUser();
        $asOfDate = trim($this->request->getGet('date') ?? '') ?: date('Y-m-d');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOfDate)) {
            return $this->apiError('03', 'Invalid date format. Use YYYY-MM-DD.', 400);
        }

        return $this->apiSuccess('Dashboard counts loaded.', [
            'as_of_date'           => $asOfDate,
            'active_vehicle_count' => $this->countActiveVehicles(),
            'active_driver_count'  => $this->countActiveDrivers($asOfDate),
            'user'                 => [
                'user_id'       => (int) $user->id,
                'fullname'      => $user->full_name,
                'location_id'   => ! empty($user->location_id) ? (int) $user->location_id : null,
                'location_name' => $user->location_name ?? null,
            ],
        ]);
    }

    protected function countActiveVehicles(): int
    {
        return (int) $this->db->table('vehicle')->countAllResults();
    }

    /**
     * Active driver = DRIVER type, joined on/before date, not resigned before date.
     * Counts all locations.
     */
    protected function countActiveDrivers(string $asOfDate): int
    {
        $builder = $this->db->table('staff');
        $builder->where('staff.user_type', 'DRIVER');

        $builder->groupStart()
            ->where('staff.doj IS NULL', null, false)
            ->orWhere('staff.doj', '0000-00-00')
            ->orWhere('staff.doj <=', $asOfDate)
        ->groupEnd();

        $builder->groupStart()
            ->where('staff.resign_date IS NULL', null, false)
            ->orWhere('staff.resign_date', '0000-00-00')
            ->orWhere('staff.resign_date >=', $asOfDate)
        ->groupEnd();

        return (int) $builder->countAllResults();
    }
}
