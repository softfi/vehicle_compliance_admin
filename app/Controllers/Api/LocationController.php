<?php

namespace App\Controllers\Api;

class LocationController extends BaseApiController
{
    /**
     * GET /api/locations
     * Bearer token required. Sorted by location_id ASC.
     * Optional: ?search=OD&status=Active
     */
    public function index()
    {
        $search = trim($this->request->getGet('search') ?? '');
        $status = trim($this->request->getGet('status') ?? '');

        $builder = $this->db->table('location');
        $builder->select('location_id, location_name, location_shordname, opening_balance, radius, status');

        if ($status !== '') {
            $builder->where('status', $status);
        }
        if ($search !== '') {
            $builder->groupStart()
                ->like('location_name', $search)
                ->orLike('location_shordname', $search)
            ->groupEnd();
        }

        $builder->orderBy('location_id', 'ASC');
        $rows = $builder->get()->getResult();

        $locations = [];
        foreach ($rows as $row) {
            $locations[] = $this->formatLocation($row);
        }

        return $this->apiSuccess('Locations loaded.', [
            'total'     => count($locations),
            'locations' => $locations,
        ]);
    }

    protected function formatLocation(object $row): array
    {
        return [
            'id'                 => (int) $row->location_id,
            'location_id'        => (int) $row->location_id,
            'location_name'      => $row->location_name ?? null,
            'location_shortname' => $row->location_shordname ?? null,
            'opening_balance'    => isset($row->opening_balance) ? (float) $row->opening_balance : 0.0,
            'radius'             => isset($row->radius) ? (float) $row->radius : 0.0,
            'status'             => $row->status ?? null,
        ];
    }
}
