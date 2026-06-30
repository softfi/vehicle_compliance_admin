<?php

namespace App\Controllers\Api;

use App\Libraries\GeoLocation;

class LocationStaffController extends BaseApiController
{
    /**
     * POST /api/location/verify-and-staff
     * GET  /api/location/verify-and-staff
     *
     * Body/query:
     * - latitude, longitude (required)
     * - attendance_date / date (optional, default: today YYYY-MM-DD)
     *
     * Verifies GPS within assigned location radius, then returns location staff
     * (STAFF + MECHANIC) with is_present for the selected date.
     */
    public function verifyAndStaff()
    {
        $payload = $this->mergeRequestPayload();

        $latitude = $this->payloadValue($payload, ['latitude', 'lat'], null);
        $longitude = $this->payloadValue($payload, ['longitude', 'lng', 'lon'], null);

        if ($latitude === null || $latitude === '' || $longitude === null || $longitude === '') {
            $latitude = $this->request->getGet('latitude') ?? $this->request->getGet('lat');
            $longitude = $this->request->getGet('longitude') ?? $this->request->getGet('lng');
        }

        if ($latitude === null || $latitude === '' || $longitude === null || $longitude === '') {
            return $this->apiError('03', 'latitude and longitude are required.', 400);
        }

        $attendanceDate = trim((string) (
            $this->request->getGet('attendance_date')
            ?? $this->request->getGet('date')
            ?? $payload['attendance_date']
            ?? $payload['date']
            ?? ''
        ));

        if ($attendanceDate === '') {
            $attendanceDate = date('Y-m-d');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendanceDate)) {
            return $this->apiError('03', 'attendance_date must be YYYY-MM-DD.', 400);
        }

        $userLat = (float) $latitude;
        $userLng = (float) $longitude;

        if (! GeoLocation::isValidLatitude($userLat) || ! GeoLocation::isValidLongitude($userLng)) {
            return $this->apiError('03', 'Invalid latitude or longitude.', 400);
        }

        $user = $this->authUser();
        $locationId = (int) ($user->location_id ?? 0);

        if ($locationId <= 0) {
            return $this->apiError('17', 'No location assigned to your account.', 400);
        }

        $location = $this->db->table('location')
            ->select('location_id, location_name, location_shordname, latitude, longitude, radius, status')
            ->where('location_id', $locationId)
            ->get()
            ->getRow();

        if ($location === null) {
            return $this->apiError('04', 'Assigned location not found.', 404);
        }

        $status = trim((string) ($location->status ?? 'Active'));
        if ($status !== '' && strcasecmp($status, 'Inactive') === 0) {
            return $this->apiError('18', 'Your assigned location is inactive.', 400);
        }

        $centerLat = $location->latitude;
        $centerLng = $location->longitude;

        if ($centerLat === null || $centerLng === null || $centerLat === '' || $centerLng === '') {
            return $this->apiError('19', 'Location coordinates are not configured. Contact admin.', 400);
        }

        $centerLat = (float) $centerLat;
        $centerLng = (float) $centerLng;
        $radiusMeters = (float) ($location->radius ?? 0);

        if ($radiusMeters <= 0) {
            return $this->apiError('20', 'Location radius is not configured. Contact admin.', 400);
        }

        $distanceMeters = GeoLocation::distanceMeters($userLat, $userLng, $centerLat, $centerLng);
        $withinRadius = $distanceMeters <= $radiusMeters;

        if (! $withinRadius) {
            return $this->apiError(
                '21',
                'You are not within location radius.',
                403,
                [
                    'attendance_date' => $attendanceDate,
                    'location' => [
                        'location_id'        => $locationId,
                        'location_name'      => $location->location_name ?? null,
                        'location_shortname' => $location->location_shordname ?? null,
                        'latitude'           => $centerLat,
                        'longitude'          => $centerLng,
                        'radius_meters'      => $radiusMeters,
                        'distance_meters'    => round($distanceMeters, 2),
                        'within_radius'      => false,
                    ],
                    'your_coordinates' => [
                        'latitude'  => $userLat,
                        'longitude' => $userLng,
                    ],
                ]
            );
        }

        $rows = $this->adminModel->getActiveNonDriverStaffByLocation($locationId, $attendanceDate);
        $staffIds = array_map(static fn ($row) => (int) $row->id, $rows);

        $attendanceMap = [];
        if ($staffIds !== []) {
            $attendanceRows = $this->db->table('staff_attendance')
                ->whereIn('staff_id', $staffIds)
                ->where('attendance_date', $attendanceDate)
                ->get()
                ->getResult();

            foreach ($attendanceRows as $attendanceRow) {
                $attendanceMap[(int) $attendanceRow->staff_id] = $attendanceRow;
            }
        }

        $staffList = [];
        $presentCount = 0;
        $notPresentCount = 0;

        foreach ($rows as $row) {
            $staffId = (int) ($row->id ?? 0);
            $attendance = $attendanceMap[$staffId] ?? null;
            $rawStatus = $attendance !== null ? (string) ($attendance->status ?? '') : null;
            $statusMeta = $this->mapAttendanceStatusMeta($rawStatus);
            $isPresent = $rawStatus === 'Present';
            $isMarked = $attendance !== null;

            if ($isPresent) {
                $presentCount++;
            } else {
                $notPresentCount++;
            }

            $staffList[] = array_merge(
                $this->formatStaffMember($row),
                [
                    'attendance_date' => $attendanceDate,
                    'attendance_id'   => $attendance ? (int) ($attendance->id ?? 0) : null,
                    'is_present'      => $isPresent,
                    'is_marked'       => $isMarked,
                    'status'          => $statusMeta['status'],
                    'status_label'    => $statusMeta['status_label'],
                    'status_color'    => $statusMeta['status_color'],
                    'status_icon'     => $statusMeta['status_icon'],
                    'check_in_time'   => $attendance->check_in_time ?? null,
                    'check_out_time'  => $attendance->check_out_time ?? null,
                    'leave_type'      => $attendance->leave_type ?? null,
                    'notes'           => $attendance->notes ?? null,
                ]
            );
        }

        return $this->apiSuccess('Location verified. Staff loaded.', [
            'attendance_date' => $attendanceDate,
            'location' => [
                'location_id'        => $locationId,
                'location_name'      => $location->location_name ?? null,
                'location_shortname' => $location->location_shordname ?? null,
                'latitude'           => $centerLat,
                'longitude'          => $centerLng,
                'radius_meters'      => $radiusMeters,
                'distance_meters'    => round($distanceMeters, 2),
                'within_radius'      => true,
            ],
            'your_coordinates' => [
                'latitude'  => $userLat,
                'longitude' => $userLng,
            ],
            'summary' => [
                'total_staff'   => count($staffList),
                'present_count' => $presentCount,
                'not_present_count' => $notPresentCount,
            ],
            'total' => count($staffList),
            'staff' => $staffList,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatStaffMember(object $row): array
    {
        $img = trim((string) ($row->img ?? ''));
        $resignDate = trim((string) ($row->resign_date ?? ''));
        $isActive = $resignDate === ''
            || $resignDate === '0000-00-00'
            || $resignDate >= date('Y-m-d');

        $name = trim((string) ($row->name ?? ''));
        $staffCode = trim((string) ($row->staff_code ?? ''));
        $label = $staffCode !== '' ? "{$name} ({$staffCode})" : $name;

        return [
            'staff_id'      => (int) ($row->id ?? 0),
            'staff_code'    => $staffCode !== '' ? $staffCode : null,
            'name'          => $name !== '' ? $name : null,
            'label'         => $label !== '' ? $label : null,
            'user_type'     => $row->user_type ?? null,
            'is_active'     => $isActive,
            'tel'           => $row->tel ?? null,
            'doj'           => $row->doj ?? null,
            'resign_date'   => $row->resign_date ?? null,
            'location_id'   => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name' => $row->location_name ?? null,
            'photo_url'     => $img !== '' ? base_url('uploads/' . $img) : null,
        ];
    }

    /**
     * @return array{status: string, status_label: string, status_color: string, status_icon: string}
     */
    private function mapAttendanceStatusMeta(?string $rawStatus): array
    {
        if ($rawStatus === null || $rawStatus === '') {
            return [
                'status'       => 'Not Marked',
                'status_label' => 'Not Marked',
                'status_color' => 'none',
                'status_icon'  => 'plus',
            ];
        }

        return match ($rawStatus) {
            'Present' => [
                'status'       => 'Present',
                'status_label' => 'Present',
                'status_color' => 'present',
                'status_icon'  => 'check',
            ],
            'Absent' => [
                'status'       => 'Absent',
                'status_label' => 'Absent',
                'status_color' => 'absent',
                'status_icon'  => 'times',
            ],
            'Leave' => [
                'status'       => 'Leave',
                'status_label' => 'Leave',
                'status_color' => 'leave',
                'status_icon'  => 'plane',
            ],
            'Half-day' => [
                'status'       => 'Half-day',
                'status_label' => 'Half-day',
                'status_color' => 'half',
                'status_icon'  => 'adjust',
            ],
            'Sick-leave' => [
                'status'       => 'Sick-leave',
                'status_label' => 'Sick-leave',
                'status_color' => 'leave',
                'status_icon'  => 'plane',
            ],
            'Holiday' => [
                'status'       => 'Holiday',
                'status_label' => 'Holiday',
                'status_color' => 'holiday',
                'status_icon'  => 'star',
            ],
            default => [
                'status'       => $rawStatus,
                'status_label' => $rawStatus,
                'status_color' => 'none',
                'status_icon'  => 'minus',
            ],
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function apiError(string $responseCode, string $message, int $httpStatus = 400, array $data = [])
    {
        $payload = [
            'status'   => $httpStatus,
            'error'    => true,
            'messages' => [
                'responsecode' => $responseCode,
                'message'      => $message,
            ],
        ];

        if ($data !== []) {
            $payload['messages']['data'] = $data;
        }

        return $this->respond($payload, $httpStatus);
    }
}
