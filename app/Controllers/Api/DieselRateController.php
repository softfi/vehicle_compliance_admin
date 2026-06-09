<?php

namespace App\Controllers\Api;

/**
 * Diesel Rate Master APIs (admin/diesel_rate).
 */
class DieselRateController extends BaseApiController
{
    /**
     * GET /api/diesel-rate?from_date=&to_date=
     */
    public function index()
    {
        $fromDate = trim((string) ($this->request->getGet('from_date') ?? ''));
        $toDate   = trim((string) ($this->request->getGet('to_date') ?? ''));

        if ($fromDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            return $this->apiError('03', 'from_date must be YYYY-MM-DD', 400);
        }
        if ($toDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            return $this->apiError('03', 'to_date must be YYYY-MM-DD', 400);
        }

        $builder = $this->db->table('diesel_rate_master');
        if ($fromDate !== '') {
            $builder->where('from_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $builder->where('to_date <=', $toDate);
        }
        $builder->orderBy('from_date', 'DESC');
        $builder->orderBy('id', 'DESC');

        $rows = $builder->get()->getResult();

        return $this->apiSuccess('Diesel rates loaded.', [
            'filters' => [
                'from_date' => $fromDate !== '' ? $fromDate : null,
                'to_date'   => $toDate !== '' ? $toDate : null,
            ],
            'total' => count($rows),
            'rates' => array_map(fn ($r) => $this->formatRate($r), $rows),
        ]);
    }

    /**
     * GET /api/diesel-rate/{id}
     */
    public function show($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->apiError('03', 'Valid rate id is required.', 400);
        }

        $row = $this->fetchRate($id);
        if (! $row) {
            return $this->apiError('04', 'Diesel rate not found.', 404);
        }

        return $this->apiSuccess('Diesel rate loaded.', [
            'rate' => $this->formatRate($row),
        ]);
    }

    /**
     * POST /api/diesel-rate/store
     * Web fields: from_date, to_date, rate
     */
    public function store()
    {
        $payload = $this->parseRequestPayload();
        $errors  = [];
        $data    = $this->buildRateDataFromPayload($payload, $errors);

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $this->db->table('diesel_rate_master')->insert($data);
        $insertId = (int) $this->db->insertID();

        $this->logActivity('create', $insertId, $data);

        $row = $this->fetchRate($insertId);

        return $this->apiSuccess('Diesel rate added successfully.', [
            'rate' => $this->formatRate($row),
        ], 201);
    }

    /**
     * POST /api/diesel-rate/{id}
     */
    public function update($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid rate id is required.', 400);
        }

        if (! $this->fetchRate($id)) {
            return $this->apiError('04', 'Diesel rate not found.', 404);
        }

        $payload = $this->parseRequestPayload();
        $errors  = [];
        $data    = $this->buildRateDataFromPayload($payload, $errors);

        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $this->db->table('diesel_rate_master')->where('id', $id)->update($data);
        $this->logActivity('update', $id, $data);

        $row = $this->fetchRate($id);

        return $this->apiSuccess('Diesel rate updated successfully.', [
            'rate' => $this->formatRate($row),
        ]);
    }

    /**
     * DELETE /api/diesel-rate/{id}
     */
    public function destroy($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->apiError('03', 'Valid rate id is required.', 400);
        }

        if (! $this->fetchRate($id)) {
            return $this->apiError('04', 'Diesel rate not found.', 404);
        }

        $this->db->table('diesel_rate_master')->where('id', $id)->delete();
        $this->logActivity('delete', $id, ['id' => $id]);

        return $this->apiSuccess('Diesel rate deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildRateDataFromPayload(array $payload, array &$errors): array
    {
        $fromDate = trim($payload['from_date'] ?? '');
        $toDate   = trim($payload['to_date'] ?? '');
        $rate     = $payload['rate'] ?? null;

        if ($fromDate === '') {
            $errors[] = 'from_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $errors[] = 'from_date must be YYYY-MM-DD';
        }

        if ($toDate === '') {
            $errors[] = 'to_date is required';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $errors[] = 'to_date must be YYYY-MM-DD';
        }

        if ($errors === [] && strtotime($fromDate) > strtotime($toDate)) {
            $errors[] = 'from_date cannot be after to_date';
        }

        if ($rate === null || $rate === '' || ! is_numeric($rate) || (float) $rate <= 0) {
            $errors[] = 'rate must be a positive number';
        }

        return [
            'from_date' => $fromDate,
            'to_date'   => $toDate,
            'rate'      => (float) $rate,
        ];
    }

    protected function fetchRate(int $id): ?object
    {
        return $this->db->table('diesel_rate_master')->where('id', $id)->get()->getRow();
    }

    protected function formatRate(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        return [
            'id'        => (int) $row->id,
            'from_date' => $row->from_date,
            'to_date'   => $row->to_date,
            'rate'      => (float) $row->rate,
        ];
    }

    /**
     * @param array<string, mixed> $changes
     */
    protected function logActivity(string $action, int $modelId, array $changes): void
    {
        if (! $this->db->tableExists('activity_logs')) {
            return;
        }

        $this->db->table('activity_logs')->insert([
            'user_id'    => $this->authUserId(),
            'menu'       => 'api_diesel_rate',
            'action'     => $action,
            'model'      => 'diesel_rate_master',
            'model_id'   => $modelId,
            'changes'    => json_encode(['data' => $changes, 'source' => 'diesel_rate_api']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
