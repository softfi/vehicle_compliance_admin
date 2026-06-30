<?php

namespace App\Controllers\Api;

class TaskController extends BaseApiController
{
    // ------------------------------------------------------------------ //
    // Helpers                                                              //
    // ------------------------------------------------------------------ //

    /** Parse a comma-separated ID string into a clean int array. */
    private function parseCsv(?string $csv): array
    {
        if ($csv === null || trim($csv) === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $csv))));
    }

    /** Resolve user names for a CSV of user IDs. Returns array of {id, full_name}. */
    private function resolveUsers(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->db->table('user')
            ->select('id, full_name')
            ->whereIn('id', $ids)
            ->get()
            ->getResult();
    }

    /** Map integer status to human-readable label. */
    private function statusLabel(int $status): string
    {
        return match ($status) {
            2       => 'In Progress',
            3       => 'Completed',
            default => 'Pending',
        };
    }

    /** Format a raw task DB row into a clean API response block. */
    private function formatTask(object $task): array
    {
        $assignedToIds  = $this->parseCsv($task->assigned_to ?? null);
        $ccIds          = $this->parseCsv($task->cc ?? null);
        $assignedToList = $this->resolveUsers($assignedToIds);
        $ccList         = $this->resolveUsers($ccIds);

        $statusInt = (int) ($task->status ?? 1);

        return [
            'id'               => (int) $task->id,
            'task_description' => $task->task_description ?? null,
            'assigned_to'      => [
                'ids'   => $assignedToIds,
                'users' => $assignedToList,
            ],
            'cc' => [
                'ids'   => $ccIds,
                'users' => $ccList,
            ],
            'assigned_by' => [
                'id'        => (int) ($task->assigned_by ?? 0),
                'full_name' => $task->assigned_by_name ?? null,
            ],
            'created_date'    => $task->created_date    ?? null,
            'completion_date' => $task->completion_date ?? null,
            'status'          => $statusInt,
            'status_label'    => $this->statusLabel($statusInt),
            'remarks'         => $task->remarks ?? null,
        ];
    }

    // ------------------------------------------------------------------ //
    // GET /api/tasks/users                                                 //
    // Returns full list of non-admin users (for initial load).            //
    // ------------------------------------------------------------------ //
    public function users()
    {
        $list = $this->db->table('user')
            ->select('id, full_name, user_name, email, contact_no')
            ->where('user_type !=', 1)
            ->where('deleted_by', null)
            ->orderBy('full_name', 'ASC')
            ->get()
            ->getResult();

        return $this->apiSuccess('User list loaded.', ['users' => $list]);
    }

    // ------------------------------------------------------------------ //
    // GET /api/tasks/users/assign-to                                       //
    // Returns users eligible for "Assign To".                             //
    // Pass currently selected CC IDs to exclude them:                     //
    //   ?exclude_ids=20,22   (comma-separated)                            //
    // ------------------------------------------------------------------ //
    public function assignToUsers()
    {
        $excludeRaw = trim((string) ($this->request->getGet('exclude_ids') ?? ''));
        $excludeIds = $this->parseCsv($excludeRaw);

        $builder = $this->db->table('user')
            ->select('id, full_name, user_name, email, contact_no')
            ->where('user_type !=', 1)
            ->where('deleted_by', null)
            ->orderBy('full_name', 'ASC');

        if ($excludeIds !== []) {
            $builder->whereNotIn('id', $excludeIds);
        }

        $list = $builder->get()->getResult();

        return $this->apiSuccess('Assign To user list loaded.', [
            'excluded_ids' => $excludeIds,
            'users'        => $list,
        ]);
    }

    // ------------------------------------------------------------------ //
    // GET /api/tasks/users/cc                                              //
    // Returns users eligible for "CC".                                    //
    // Pass currently selected Assign To IDs to exclude them:              //
    //   ?exclude_ids=15,18   (comma-separated)                            //
    // ------------------------------------------------------------------ //
    public function ccUsers()
    {
        $excludeRaw = trim((string) ($this->request->getGet('exclude_ids') ?? ''));
        $excludeIds = $this->parseCsv($excludeRaw);

        $builder = $this->db->table('user')
            ->select('id, full_name, user_name, email, contact_no')
            ->where('user_type !=', 1)
            ->where('deleted_by', null)
            ->orderBy('full_name', 'ASC');

        if ($excludeIds !== []) {
            $builder->whereNotIn('id', $excludeIds);
        }

        $list = $builder->get()->getResult();

        return $this->apiSuccess('CC user list loaded.', [
            'excluded_ids' => $excludeIds,
            'users'        => $list,
        ]);
    }

    // ------------------------------------------------------------------ //
    // GET /api/tasks                                                       //
    // Admin sees all tasks; sub-admin sees only tasks assigned to them.   //
    // Optional query params: status (1|2|3), from_date, to_date           //
    // ------------------------------------------------------------------ //
    public function index()
    {
        $user   = $this->authUser();
        $userId = (int) $user->id;

        $builder = $this->db->table('tasks t')
            ->select('t.*, u.full_name as assigned_by_name')
            ->join('user u', 'u.id = t.assigned_by', 'left')
            ->orderBy('t.id', 'DESC');

        // Non-admins only see tasks assigned to them (FIND_IN_SET handles CSV)
        if ((int) $user->user_type !== 1) {
            $builder->where("FIND_IN_SET({$userId}, t.assigned_to) > 0", null, false);
        }

        // Optional filters
        $status    = $this->request->getGet('status');
        $fromDate  = $this->request->getGet('from_date');
        $toDate    = $this->request->getGet('to_date');

        if ($status !== null && $status !== '') {
            $builder->where('t.status', (int) $status);
        }
        if ($fromDate) {
            $builder->where('t.created_date >=', $fromDate);
        }
        if ($toDate) {
            $builder->where('t.created_date <=', $toDate);
        }

        $rows  = $builder->get()->getResult();
        $tasks = array_map([$this, 'formatTask'], $rows);

        return $this->apiSuccess('Tasks loaded.', [
            'total' => count($tasks),
            'tasks' => $tasks,
        ]);
    }

    // ------------------------------------------------------------------ //
    // GET /api/tasks/{id}                                                  //
    // ------------------------------------------------------------------ //
    public function show(int $id)
    {
        $user   = $this->authUser();
        $userId = (int) $user->id;

        $task = $this->db->table('tasks t')
            ->select('t.*, u.full_name as assigned_by_name')
            ->join('user u', 'u.id = t.assigned_by', 'left')
            ->where('t.id', $id)
            ->get()
            ->getRow();

        if (! $task) {
            return $this->apiError('04', 'Task not found.', 404);
        }

        // Non-admins can only view tasks assigned to them
        if ((int) $user->user_type !== 1) {
            $ids = $this->parseCsv($task->assigned_to ?? null);
            if (! in_array($userId, $ids, true)) {
                return $this->apiError('05', 'Access denied.', 403);
            }
        }

        return $this->apiSuccess('Task loaded.', ['task' => $this->formatTask($task)]);
    }

    // ------------------------------------------------------------------ //
    // POST /api/tasks/store                                                //
    // Body (JSON or form-data):                                            //
    //   task_description* | assigned_to* (comma CSV or array)             //
    //   completion_date*  | cc (comma CSV or array, optional)             //
    //   remarks (optional)                                                 //
    // ------------------------------------------------------------------ //
    public function store()
    {
        $user    = $this->authUser();
        $payload = $this->parseRequestPayload();

        $description    = trim((string) ($payload['task_description'] ?? ''));
        $completionDate = trim((string) ($payload['completion_date']  ?? ''));

        if ($description === '') {
            return $this->apiError('01', 'task_description is required.', 422);
        }
        if ($completionDate === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $completionDate)) {
            return $this->apiError('02', 'completion_date is required and must be YYYY-MM-DD.', 422);
        }

        // assigned_to — accept array or comma string
        $assignedToRaw = $payload['assigned_to'] ?? '';
        if (is_array($assignedToRaw)) {
            $assignedToIds = array_values(array_filter(array_map('intval', $assignedToRaw)));
        } else {
            $assignedToIds = $this->parseCsv((string) $assignedToRaw);
        }

        if ($assignedToIds === []) {
            return $this->apiError('03', 'At least one assigned_to user ID is required.', 422);
        }

        // cc — accept array or comma string (optional)
        $ccRaw = $payload['cc'] ?? '';
        if (is_array($ccRaw)) {
            $ccIds = array_values(array_filter(array_map('intval', $ccRaw)));
        } else {
            $ccIds = $this->parseCsv((string) $ccRaw);
        }

        // status — optional on create, defaults to 1 (Pending)
        $statusVal = isset($payload['status']) ? (int) $payload['status'] : 1;
        if (! in_array($statusVal, [1, 2, 3], true)) {
            return $this->apiError('06', 'status must be 1 (Pending), 2 (In Progress), or 3 (Completed).', 422);
        }

        $this->db->table('tasks')->insert([
            'task_description' => $description,
            'assigned_to'      => implode(',', $assignedToIds),
            'cc'               => $ccIds !== [] ? implode(',', $ccIds) : null,
            'assigned_by'      => (int) $user->id,
            'created_date'     => date('Y-m-d'),
            'completion_date'  => $completionDate,
            'remarks'          => trim((string) ($payload['remarks'] ?? '')),
            'status'           => $statusVal,
        ]);

        $newId = $this->db->insertID();

        $task = $this->db->table('tasks t')
            ->select('t.*, u.full_name as assigned_by_name')
            ->join('user u', 'u.id = t.assigned_by', 'left')
            ->where('t.id', $newId)
            ->get()
            ->getRow();

        return $this->apiSuccess('Task created successfully.', [
            'task' => $this->formatTask($task),
        ], 201);
    }

    // ------------------------------------------------------------------ //
    // POST /api/tasks/{id}                                                 //
    // Body (JSON or form-data): any of the fields above + status (1|2|3)  //
    // ------------------------------------------------------------------ //
    public function update(int $id)
    {
        $user   = $this->authUser();
        $userId = (int) $user->id;

        $task = $this->db->table('tasks')->where('id', $id)->get()->getRow();

        if (! $task) {
            return $this->apiError('04', 'Task not found.', 404);
        }

        // Only admin or the task creator can update
        if ((int) $user->user_type !== 1 && (int) $task->assigned_by !== $userId) {
            return $this->apiError('05', 'Access denied. Only the task creator or admin can update.', 403);
        }

        $payload = $this->parseRequestPayload();

        $updateData = [];

        if (isset($payload['task_description']) && trim($payload['task_description']) !== '') {
            $updateData['task_description'] = trim((string) $payload['task_description']);
        }

        if (isset($payload['completion_date']) && $payload['completion_date'] !== '') {
            $date = trim((string) $payload['completion_date']);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->apiError('02', 'completion_date must be YYYY-MM-DD.', 422);
            }
            $updateData['completion_date'] = $date;
        }

        if (isset($payload['assigned_to'])) {
            $raw = $payload['assigned_to'];
            $ids = is_array($raw)
                ? array_values(array_filter(array_map('intval', $raw)))
                : $this->parseCsv((string) $raw);
            if ($ids !== []) {
                $updateData['assigned_to'] = implode(',', $ids);
            }
        }

        if (isset($payload['cc'])) {
            $raw = $payload['cc'];
            $ids = is_array($raw)
                ? array_values(array_filter(array_map('intval', $raw)))
                : $this->parseCsv((string) $raw);
            $updateData['cc'] = $ids !== [] ? implode(',', $ids) : null;
        }

        if (isset($payload['status'])) {
            $s = (int) $payload['status'];
            if (! in_array($s, [1, 2, 3], true)) {
                return $this->apiError('06', 'status must be 1 (Pending), 2 (In Progress), or 3 (Completed).', 422);
            }
            $updateData['status'] = $s;
        }

        if (isset($payload['remarks'])) {
            $updateData['remarks'] = trim((string) $payload['remarks']);
        }

        if ($updateData === []) {
            return $this->apiError('07', 'No fields provided to update.', 422);
        }

        $this->db->table('tasks')->where('id', $id)->update($updateData);

        $updated = $this->db->table('tasks t')
            ->select('t.*, u.full_name as assigned_by_name')
            ->join('user u', 'u.id = t.assigned_by', 'left')
            ->where('t.id', $id)
            ->get()
            ->getRow();

        return $this->apiSuccess('Task updated successfully.', [
            'task' => $this->formatTask($updated),
        ]);
    }

    // ------------------------------------------------------------------ //
    // DELETE /api/tasks/{id}                                               //
    // Only admin or task creator can delete.                               //
    // ------------------------------------------------------------------ //
    public function destroy(int $id)
    {
        $user   = $this->authUser();
        $userId = (int) $user->id;

        $task = $this->db->table('tasks')->where('id', $id)->get()->getRow();

        if (! $task) {
            return $this->apiError('04', 'Task not found.', 404);
        }

        if ((int) $user->user_type !== 1 && (int) $task->assigned_by !== $userId) {
            return $this->apiError('05', 'Access denied. Only the task creator or admin can delete.', 403);
        }

        $this->db->table('tasks')->where('id', $id)->delete();

        return $this->apiSuccess('Task deleted successfully.', ['deleted_id' => $id]);
    }
}
