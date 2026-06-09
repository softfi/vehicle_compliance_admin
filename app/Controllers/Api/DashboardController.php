<?php

namespace App\Controllers\Api;

class DashboardController extends BaseApiController
{
    /**
     * GET /api/dashboard
     * Header: Authorization: Bearer {token}
     * Optional: ?location_id=12&date=2026-06-01 (defaults to logged-in sub-admin location)
     */
    public function index()
    {
        $user       = $this->authUser();
        $locationId = $this->resolveLocationIdForUser($user);
        $asOfDate   = trim($this->request->getGet('date') ?? '') ?: date('Y-m-d');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOfDate)) {
            return $this->apiError('03', 'Invalid date format. Use YYYY-MM-DD.', 400);
        }

        return $this->apiSuccess('Dashboard counts loaded.', [
            'as_of_date'            => $asOfDate,
            'location_id'           => $locationId,
            'active_vehicle_count'  => $this->countActiveVehicles($locationId),
            'active_driver_count'   => $this->countActiveDrivers($asOfDate, $locationId),
            'user'                  => [
                'user_id'       => (int) $user->id,
                'fullname'      => $user->full_name,
                'location_name' => $user->location_name ?? null,
            ],
        ]);
    }

    protected function countActiveVehicles(?int $locationId): int
    {
        $builder = $this->db->table('vehicle');
        if ($locationId) {
            $builder->where('location_id', $locationId);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Active driver = DRIVER type, joined on/before date, not resigned before date.
     * Same rules as AdminModel::GetActiveStaff() / attendance module.
     */
    protected function countActiveDrivers(string $asOfDate, ?int $locationId): int
    {
        $builder = $this->db->table('staff');
        $builder->where('user_type', 'DRIVER');

        $builder->groupStart()
            ->where('doj IS NULL', null, false)
            ->orWhere('doj', '0000-00-00')
            ->orWhere('doj <=', $asOfDate)
        ->groupEnd();

        $builder->groupStart()
            ->where('resign_date IS NULL', null, false)
            ->orWhere('resign_date', '0000-00-00')
            ->orWhere('resign_date >=', $asOfDate)
        ->groupEnd();

        if ($locationId) {
            $builder->where('location_id', $locationId);
        }

        return (int) $builder->countAllResults();
    }
}
