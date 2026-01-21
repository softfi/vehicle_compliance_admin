<?php include("header.php"); ?>

<style>
    /* Page enhancements */
    .page-title h3 { margin-bottom: .25rem; }
    .tm-stats .badge { font-size: .85rem; padding: .6rem .75rem; }

    /* Form polish */
    .tm-card { border: 1px solid rgba(0,0,0,.06); box-shadow: 0 2px 10px rgba(0,0,0,.04); }
    .tm-help { font-size: .8rem; color: #6c757d; }
    .tm-counter { font-size: .8rem; color: #6c757d; text-align: right; }

    /* Table polish */
    .table thead th { white-space: nowrap; }
    .status-badge { font-weight: 600; }
    .status-pending { background: #fff3cd; color: #946c00; }
    .status-inprogress { background: #cfe2ff; color: #084298; }
    .status-complete { background: #d1e7dd; color: #0f5132; }

    .row-overdue { background: #fff5f5 !important; }
    .due-badge { font-size: .75rem; }

    /* Avatar initials */
    .avatar-initials { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-weight: 600; color: #fff; background: #6777ef; margin-right: .4rem; font-size: .8rem; }

    /* Simple modal */
    .tm-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 1050; }
    .tm-modal { background: #fff; width: min(720px, 92vw); border-radius: .5rem; box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden; }
    .tm-modal-header { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1rem; border-bottom: 1px solid rgba(0,0,0,.075); }
    .tm-modal-body { padding: 1rem; max-height: 70vh; overflow: auto; }
    .tm-modal-footer { padding: .75rem 1rem; border-top: 1px solid rgba(0,0,0,.075); text-align: right; }
    .tm-close { border: 0; background: transparent; font-size: 1.5rem; line-height: 1; }

    @media (max-width: 767.98px) {
        .tm-stats { margin-top: .5rem; }
    }
</style>

<?php
    // Build status counters and normalize data for UI
    $pendingCount = 0; $inProgressCount = 0; $completedCount = 0; $totalCount = 0;
    if (!empty($tasks)) {
        foreach ($tasks as $t) {
            $totalCount++;
            switch ((int)$t->status) {
                case 1: $pendingCount++; break;
                case 2: $inProgressCount++; break;
                case 3: $completedCount++; break;
            }
        }
    }
    $todayYmd = date('Y-m-d');
?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div class="me-3">
                    <h3 class="mb-0">Task Management</h3>
                    <div class="text-muted">Create, assign, and track tasks efficiently.</div>
                </div>
                <div class="tm-stats d-flex gap-2">
                    <span class="badge rounded-pill bg-secondary">Total: <?php echo (int)$totalCount; ?></span>
                    <span class="badge rounded-pill bg-warning text-dark">Pending: <?php echo (int)$pendingCount; ?></span>
                    <span class="badge rounded-pill bg-primary">In Progress: <?php echo (int)$inProgressCount; ?></span>
                    <span class="badge rounded-pill bg-success">Completed: <?php echo (int)$completedCount; ?></span>
                </div>
            </div>

            <!-- Task Form -->
            <div class="card tm-card p-3 mb-4">
                <form method="post" action="<?= base_url('Admin/saveTask') ?>" id="taskForm">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Task Description</label>
                            <textarea name="task_description" class="form-control" rows="3" placeholder="Describe the task clearly..." maxlength="500" required></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="tm-help">Provide clear steps or expected outcome.</small>
                                <small class="tm-counter"><span id="descCount">0</span>/500</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Assign To</label>
                            <div id="assignToContainer">
                                <div class="input-group mb-2 assign-row">
                                    <select name="assigned_to[]" class="form-control" required>
                                        <option value="">-- Select Staff --</option>
                                        <?php foreach($staff_list as $s): ?>
                                            <option value="<?= $s->id ?>"><?= $s->full_name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- remove button will be toggled via JS -->
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addAssignBtn">Add Assignee</button>
                            <small class="tm-help d-block mt-1">Add one assignee at a time. Click Add Assignee to include more.</small>

                            <div class="mt-3">
                                <label class="form-label">CC (Optional)</label>
                                <div id="ccContainer">
                                    <div class="input-group mb-2 cc-row">
                                        <select name="cc[]" class="form-control">
                                            <option value="">-- Select Staff --</option>
                                            <?php foreach($staff_list as $s): ?>
                                                <option value="<?= $s->id ?>"><?= $s->full_name ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <!-- remove button will be toggled via JS -->
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="addCcBtn">Add CC</button>
                                <small class="tm-help d-block mt-1">Add one CC at a time. Click Add CC to include more.</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Today's Date</label>
                            <input type="date" name="today_date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Task Completing Date</label>
                            <input type="date" name="completion_date" id="completion_date" class="form-control" min="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="col-md-9">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Additional notes (optional)" maxlength="300"></textarea>
                            <div class="d-flex justify-content-end mt-1">
                                <small class="tm-counter"><span id="remarksCount">0</span>/300</small>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="saveBtn">Save Task</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filters -->
            <div class="card tm-card p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" id="taskSearch" class="form-control" placeholder="Search description, assignee, remarks...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter by Status</label>
                        <select id="statusFilter" class="form-control">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="in-progress">In Progress</option>
                            <option value="complete">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" value="1" id="overdueOnly">
                            <label class="form-check-label" for="overdueOnly">Overdue only</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">Clear</button>
                    </div>
                </div>
            </div>

            <!-- Task List Table -->
            <div class="card tm-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="mb-0">Task List</h5>
                    <small class="text-muted" id="visibleCount"></small>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="tasksTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Assigned By</th>
                            <th>CC</th>
                            <th>Created Date</th>
                            <th>Completion Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($tasks)): ?>
                            <?php foreach($tasks as $task): ?>
                                <?php
                                    $statusLabel = 'Pending';
                                    $statusClass = 'status-pending';
                                    $statusKey = 'pending';
                                    switch ((int)$task->status) {
                                        case 1: $statusLabel = 'Pending'; $statusClass = 'status-pending'; $statusKey = 'pending'; break;
                                        case 2: $statusLabel = 'In Progress'; $statusClass = 'status-inprogress'; $statusKey = 'in-progress'; break;
                                        case 3: $statusLabel = 'Completed'; $statusClass = 'status-complete'; $statusKey = 'complete'; break;
                                    }
                                    $isOverdue = (strtotime($task->completion_date) < strtotime($todayYmd)) && ($statusKey !== 'complete');
                                    $daysDiff = (int) floor((strtotime($task->completion_date) - strtotime($todayYmd)) / 86400);
                                    $dueText = $statusKey === 'complete' ? 'Done' : ($daysDiff < 0 ? (abs($daysDiff) . 'd overdue') : ($daysDiff === 0 ? 'Due today' : ($daysDiff . 'd left')));

                                    $assignedTo = isset($task->assigned_to_name) ? $task->assigned_to_name : '';
                                    $assignedBy = isset($task->assigned_by_name) ? $task->assigned_by_name : '';
                                    $ccList     = isset($task->cc_name) ? $task->cc_name : '';

                                    // Build initials for avatar (assigned to)
                                    $initials = '';
                                    $parts = preg_split('/\s+/', trim((string)$assignedTo));
                                    foreach ($parts as $p) { if ($p !== '') { $initials .= strtoupper(substr($p, 0, 1)); } }
                                    $initials = substr($initials, 0, 2);

                                    // Fulltext for client-side search
                                    $fulltext = strtolower(trim(
                                        ($task->task_description ?? '') . ' ' .
                                        $assignedTo . ' ' .
                                        $assignedBy . ' ' .
                                        $ccList . ' ' .
                                        ($task->remarks ?? '') . ' ' .
                                        (string)$task->id
                                    ));
                                ?>
                                <tr class="task-row <?php echo $isOverdue ? 'row-overdue' : '';?>"
                                    data-status="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-overdue="<?= $isOverdue ? '1' : '0'; ?>"
                                    data-text="<?= htmlspecialchars($fulltext, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><?= $task->id ?></td>
                                    <td style="max-width: 360px;">
                                        <div class="fw-600 text-truncate" title="<?= htmlspecialchars($task->task_description, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($task->task_description, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="avatar-initials" title="<?= htmlspecialchars($assignedTo, ENT_QUOTES, 'UTF-8'); ?>"><?= $initials ?: 'U'; ?></span>
                                        <span><?= htmlspecialchars($assignedTo, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($assignedBy, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($ccList, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($task->created_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($task->completion_date, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <span class="badge rounded-pill bg-light text-dark border due-badge" title="Timeline"><?= $dueText; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge status-badge <?= $statusClass; ?>"><?= $statusLabel; ?></span>
                                    </td>
                                    <td style="max-width: 260px;">
                                        <span class="text-truncate d-inline-block" style="max-width: 240px;" title="<?= htmlspecialchars((string)$task->remarks, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars((string)$task->remarks, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary tm-details-btn"
                                            data-id="<?= (int)$task->id; ?>"
                                            data-desc="<?= htmlspecialchars((string)$task->task_description, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-assignto="<?= htmlspecialchars($assignedTo, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-assignby="<?= htmlspecialchars($assignedBy, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-cc="<?= htmlspecialchars($ccList, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-created="<?= htmlspecialchars((string)$task->created_date, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-completion="<?= htmlspecialchars((string)$task->completion_date, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-status="<?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-remarks="<?= htmlspecialchars((string)$task->remarks, ENT_QUOTES, 'UTF-8'); ?>">
                                            Details
                                        </button>
                                        <?php if(in_array(37.1,$jobAssign)){ ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary tm-edit-btn"
                                        data-id="<?= (int)$task->id; ?>"
                                        data-desc="<?= htmlspecialchars((string)$task->task_description, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-remarks="<?= htmlspecialchars((string)$task->remarks, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-assignees="<?= htmlspecialchars(isset($task->assigned_to_ids) ? (string)$task->assigned_to_ids : (isset($task->assigned_to) ? (string)$task->assigned_to : ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-cc="<?= htmlspecialchars(isset($task->cc_ids) ? (string)$task->cc_ids : (isset($task->cc) ? (string)$task->cc : ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-completion="<?= htmlspecialchars((string)$task->completion_date, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-status-label="<?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>">
                                        Edit
                                        </button>
                                        <?php }?>
                                        <?php if(in_array(37.2,$jobAssign)){ ?>
                                        <a class="btn btn-danger" href="<?= base_url('admin/delete_assignment/'.$task->id) ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                        <?php }?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No tasks found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Simple Details Modal -->
<div class="tm-modal-backdrop" id="tmModalBackdrop">
    <div class="tm-modal">
        <div class="tm-modal-header">
            <h6 class="mb-0" id="tmModalTitle">Task Details</h6>
            <button class="tm-close" type="button" id="tmModalClose">×</button>
        </div>
        <div class="tm-modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Task ID</div>
                    <div id="tmFieldId" class="fw-bold"></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Status</div>
                    <div id="tmFieldStatus" class="fw-bold"></div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Description</div>
                    <div id="tmFieldDesc"></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Assigned To</div>
                    <div id="tmFieldAssignTo"></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Assigned By</div>
                    <div id="tmFieldAssignBy"></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">CC</div>
                    <div id="tmFieldCC"></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Created Date</div>
                    <div id="tmFieldCreated"></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Completion Date</div>
                    <div id="tmFieldCompletion"></div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Remarks</div>
                    <div id="tmFieldRemarks"></div>
                </div>
            </div>
        </div>
        <div class="tm-modal-footer">
            <button class="btn btn-secondary" type="button" id="tmModalClose2">Close</button>
        </div>
    </div>
</div>
        
        <!-- Edit Modal -->
<div class="tm-modal-backdrop" id="tmEditBackdrop">
    <div class="tm-modal">
        <div class="tm-modal-header">
            <h6 class="mb-0">Edit Task</h6>
            <button class="tm-close" type="button" id="tmEditClose">×</button>
        </div>
        <form method="post" action="<?= base_url('Admin/updateTask') ?>" id="tmEditForm">
            <div class="tm-modal-body">
                <input type="hidden" name="task_id" id="tmEditId">

                <div class="mb-3">
                    <label class="form-label">Task Description</label>
                    <textarea name="task_description" id="tmEditDesc" class="form-control" rows="3" maxlength="500" required></textarea>
                    <small class="tm-help">Describe the task clearly.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign To</label>
                    <div id="assignToEditContainer">
                        <div class="input-group mb-2 assign-edit-row">
                            <select name="assigned_to[]" class="form-control" required>
                                <option value="">-- Select Staff --</option>
                                <?php foreach($staff_list as $s): ?>
                                    <option value="<?= $s->id ?>"><?= $s->full_name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <!-- remove button will be toggled via JS -->
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addAssignEditBtn">Add Assignee</button>
                    <small class="tm-help d-block mt-1">Add one assignee at a time. Click Add Assignee to include more.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">CC (Optional)</label>
                    <div id="ccEditContainer">
                        <div class="input-group mb-2 cc-edit-row">
                            <select name="cc[]" class="form-control">
                                <option value="">-- Select Staff --</option>
                                <?php foreach($staff_list as $s): ?>
                                    <option value="<?= $s->id ?>"><?= $s->full_name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <!-- remove button will be toggled via JS -->
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addCcEditBtn">Add CC</button>
                    <small class="tm-help d-block mt-1">Add one CC at a time. Click Add CC to include more.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Completion Date</label>
                    <input type="date" class="form-control" name="completion_date" id="tmEditCompletion" min="<?= date('Y-m-d'); ?>" required>
                    <small class="tm-help">Cannot be before today.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="tmEditStatus" class="form-control" required>
                        <option value="1">Pending</option>
                        <option value="2">In Progress</option>
                        <option value="3">Completed</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" id="tmEditRemarks" class="form-control" rows="2" maxlength="300"></textarea>
                </div>
            </div>
            <div class="tm-modal-footer">
                <button class="btn btn-secondary" type="button" id="tmEditClose2">Cancel</button>
                <button class="btn btn-primary" type="submit" id="tmEditSaveBtn">Save</button>
            </div>
        </form>
    </div>
</div>
        
<script>
    (function(){
        // Char counters
        const desc = document.querySelector('textarea[name="task_description"]');
        const descCount = document.getElementById('descCount');
        if (desc && descCount) {
            const update = () => descCount.textContent = String(desc.value.length);
            desc.addEventListener('input', update);
            update();
        }
        const remarks = document.querySelector('textarea[name="remarks"]');
        const remarksCount = document.getElementById('remarksCount');
        if (remarks && remarksCount) {
            const update = () => remarksCount.textContent = String(remarks.value.length);
            remarks.addEventListener('input', update);
            update();
        }
    
        // Dynamic multi-select initializer (prevents duplicates, add/remove rows)
        function initDynamicMultiSelect({containerId, addBtnId, rowClass, selectName}) {
            const container = document.getElementById(containerId);
            const addBtn = document.getElementById(addBtnId);
            if (!container) return;
            const firstRow = container.querySelector(`.${rowClass}`);
            const baseOptionsHtml = firstRow ? (firstRow.querySelector('select')?.innerHTML || '') : '';
            function getSelects(){ return Array.from(container.querySelectorAll(`select[name="${selectName}"]`)); }
            function toggleRemoveButtons(){
                const rows = Array.from(container.querySelectorAll(`.${rowClass}`));
                const showRemove = rows.length > 1;
                rows.forEach(row => {
                    const btn = row.querySelector('.remove-row');
                    if (btn) btn.classList.toggle('d-none', !showRemove);
                });
            }
            function updateOptionStates(){
                const selects = getSelects();
                const chosen = new Set(selects.map(s => s.value).filter(Boolean));
                selects.forEach(sel => {
                    Array.from(sel.options).forEach(opt => {
                        if (!opt.value) return;
                        opt.disabled = chosen.has(opt.value) && sel.value !== opt.value;
                    });
                });
            }
            function bindRow(row){
                const sel = row.querySelector('select');
                const rm = row.querySelector('.remove-row');
                if (sel) sel.addEventListener('change', updateOptionStates);
                if (rm) rm.addEventListener('click', () => {
                    row.remove();
                    toggleRemoveButtons();
                    updateOptionStates();
                });
            }
            function addRow(){
                const wrap = document.createElement('div');
                wrap.className = `input-group mb-2 ${rowClass}`;
                wrap.innerHTML = `
                    <select name="${selectName}" class="form-control">
                        ${baseOptionsHtml}
                    </select>
                    <button class="btn btn-outline-danger remove-row" type="button" title="Remove">&times;</button>
                `;
                container.appendChild(wrap);
                bindRow(wrap);
                toggleRemoveButtons();
                updateOptionStates();
            }
            if (firstRow) {
                if (!firstRow.querySelector('.remove-row')) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-danger remove-row d-none';
                    btn.title = 'Remove';
                    btn.textContent = '×';
                    firstRow.appendChild(btn);
                }
                bindRow(firstRow);
                toggleRemoveButtons();
                updateOptionStates();
            }
            if (addBtn) addBtn.addEventListener('click', addRow);
        }

        // Initialize for CC (optional)
        initDynamicMultiSelect({
            containerId: 'ccContainer',
            addBtnId: 'addCcBtn',
            rowClass: 'cc-row',
            selectName: 'cc[]'
        });

        // Initialize for Assign To (required first row)
        initDynamicMultiSelect({
            containerId: 'assignToContainer',
            addBtnId: 'addAssignBtn',
            rowClass: 'assign-row',
            selectName: 'assigned_to[]'
        });

        // Initialize for Edit modal containers
        initDynamicMultiSelect({
            containerId: 'assignToEditContainer',
            addBtnId: 'addAssignEditBtn',
            rowClass: 'assign-edit-row',
            selectName: 'assigned_to[]'
        });
        initDynamicMultiSelect({
            containerId: 'ccEditContainer',
            addBtnId: 'addCcEditBtn',
            rowClass: 'cc-edit-row',
            selectName: 'cc[]'
        });

        // Submit UX
        const form = document.getElementById('taskForm');
        const saveBtn = document.getElementById('saveBtn');
        if (form && saveBtn) {
            form.addEventListener('submit', function(){
                // Remove empty CC and Assignee rows before submit (create and edit)
                document.querySelectorAll('#ccContainer select[name="cc[]"], #assignToContainer select[name="assigned_to[]"], #ccEditContainer select[name="cc[]"], #assignToEditContainer select[name="assigned_to[]"]').forEach(sel => {
                    if (!sel.value) {
                        const group = sel.closest('.input-group');
                        if (group) group.remove();
                    }
                });
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';
            });
        }
    
        // Filters
        const searchInput = document.getElementById('taskSearch');
        const statusFilter = document.getElementById('statusFilter');
        const overdueOnly = document.getElementById('overdueOnly');
        const clearFilters = document.getElementById('clearFilters');
        const rows = Array.from(document.querySelectorAll('#tasksTable tbody tr.task-row'));
        const visibleCount = document.getElementById('visibleCount');
    
        function applyFilters() {
            const q = (searchInput?.value || '').trim().toLowerCase();
            const status = statusFilter?.value || '';
            const overdue = overdueOnly?.checked || false;
            let shown = 0;
            rows.forEach(row => {
                const text = row.getAttribute('data-text') || '';
                const s = row.getAttribute('data-status') || '';
                const od = row.getAttribute('data-overdue') === '1';
                let ok = true;
                if (q && !text.includes(q)) ok = false;
                if (status && s !== status) ok = false;
                if (overdue && !od) ok = false;
                row.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            if (visibleCount) {
                visibleCount.textContent = shown + ' of ' + rows.length + ' visible';
            }
        }
    
        ['input','change'].forEach(evt => {
            if (searchInput) searchInput.addEventListener(evt, applyFilters);
            if (statusFilter) statusFilter.addEventListener(evt, applyFilters);
            if (overdueOnly) overdueOnly.addEventListener(evt, applyFilters);
        });
        if (clearFilters) {
            clearFilters.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (statusFilter) statusFilter.value = '';
                if (overdueOnly) overdueOnly.checked = false;
                applyFilters();
            });
        }
        applyFilters();
    
        // Details modal
        const backdrop = document.getElementById('tmModalBackdrop');
        const closeButtons = [document.getElementById('tmModalClose'), document.getElementById('tmModalClose2')];
        const fields = {
            id: document.getElementById('tmFieldId'),
            status: document.getElementById('tmFieldStatus'),
            desc: document.getElementById('tmFieldDesc'),
            assignto: document.getElementById('tmFieldAssignTo'),
            assignby: document.getElementById('tmFieldAssignBy'),
            cc: document.getElementById('tmFieldCC'),
            created: document.getElementById('tmFieldCreated'),
            completion: document.getElementById('tmFieldCompletion'),
            remarks: document.getElementById('tmFieldRemarks')
        };
    
        function openModal(data) {
            if (!backdrop) return;
            document.getElementById('tmModalTitle').textContent = 'Task #' + (data.id || '');
            fields.id.textContent = data.id || '';
            fields.status.textContent = data.status || '';
            fields.desc.textContent = data.desc || '';
            fields.assignto.textContent = data.assignto || '';
            fields.assignby.textContent = data.assignby || '';
            if (fields.cc) fields.cc.textContent = data.cc || '';
            fields.created.textContent = data.created || '';
            fields.completion.textContent = data.completion || '';
            fields.remarks.textContent = data.remarks || '';
            backdrop.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal(){
            if (!backdrop) return;
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }
        if (backdrop) {
            backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
        }
        closeButtons.forEach(btn => btn && btn.addEventListener('click', closeModal));
    
        document.querySelectorAll('.tm-details-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openModal({
                    id: btn.getAttribute('data-id'),
                    status: btn.getAttribute('data-status'),
                    desc: btn.getAttribute('data-desc'),
                    assignto: btn.getAttribute('data-assignto'),
                    assignby: btn.getAttribute('data-assignby'),
                    cc: btn.getAttribute('data-cc'),
                    created: btn.getAttribute('data-created'),
                    completion: btn.getAttribute('data-completion'),
                    remarks: btn.getAttribute('data-remarks')
                });
            });
        });
    
        // Edit modal logic
        const editBackdrop = document.getElementById('tmEditBackdrop');
        const editForm = document.getElementById('tmEditForm');
        const editId = document.getElementById('tmEditId');
        const editCompletion = document.getElementById('tmEditCompletion');
        const editStatus = document.getElementById('tmEditStatus');
        const editDesc = document.getElementById('tmEditDesc');
        const editRemarks = document.getElementById('tmEditRemarks');
        const assignEditContainer = document.getElementById('assignToEditContainer');
        const addAssignEditBtn = document.getElementById('addAssignEditBtn');
        const ccEditContainer = document.getElementById('ccEditContainer');
        const addCcEditBtn = document.getElementById('addCcEditBtn');
        const editSaveBtn = document.getElementById('tmEditSaveBtn');
        const editCloseBtns = [document.getElementById('tmEditClose'), document.getElementById('tmEditClose2')];
    
        function openEditModal(data) {
            if (!editBackdrop) return;

            // Enforce today's date or later
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayStr = `${yyyy}-${mm}-${dd}`;
            if (editCompletion) editCompletion.min = todayStr;

            if (editId) editId.value = data.id || '';

            // Description and remarks
            if (editDesc) editDesc.value = data.desc || '';
            if (editRemarks) editRemarks.value = data.remarks || '';

            // Pre-fill completion date (clamp to today if older)
            if (editCompletion) {
                let val = (data.completion || '').slice(0,10) || todayStr;
                if (val < todayStr) val = todayStr;
                editCompletion.value = val;
            }

            // Pre-select status by label -> map to 1/2/3
            if (editStatus) {
                const label = (data.statusLabel || '').toLowerCase();
                let v = '1';
                if (label.includes('progress')) v = '2';
                else if (label.includes('complete')) v = '3';
                editStatus.value = v;
            }

            // Helper to reset container to a single empty row
            function resetContainer(container, rowSelector, selectName) {
                if (!container) return;
                const rows = Array.from(container.querySelectorAll(`.${rowSelector}`));
                rows.slice(1).forEach(r => r.remove());
                const first = container.querySelector(`.${rowSelector} select[name="${selectName}"]`);
                if (first) first.value = '';
            }
            // Helper to ensure rows count and set values
            function setMulti(container, addBtn, rowSelector, selectName, csv) {
                if (!container) return;
                const ids = (csv || '').split(',').map(s => s.trim()).filter(Boolean);
                const getSelects = () => Array.from(container.querySelectorAll(`select[name="${selectName}"]`));
                // Ensure at least one row exists
                if (getSelects().length === 0 && addBtn) addBtn.click();
                // Reset existing
                resetContainer(container, rowSelector, selectName);
                // Add rows as needed
                while (getSelects().length < Math.max(1, ids.length)) {
                    if (addBtn) addBtn.click(); else break;
                }
                // Apply values
                const selects = getSelects();
                ids.forEach((id, idx) => {
                    if (selects[idx]) selects[idx].value = id;
                });
                // Trigger change to update option disabling
                selects.forEach(sel => sel && sel.dispatchEvent(new Event('change')));
            }

            // Apply Assign To and CC selections
            setMulti(assignEditContainer, addAssignEditBtn, 'assign-edit-row', 'assigned_to[]', data.assignees);
            setMulti(ccEditContainer, addCcEditBtn, 'cc-edit-row', 'cc[]', data.cc);

            editBackdrop.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeEditModal() {
            if (!editBackdrop) return;
            editBackdrop.style.display = 'none';
            document.body.style.overflow = '';
            if (editSaveBtn) { editSaveBtn.disabled = false; editSaveBtn.textContent = 'Save'; }
        }
        if (editBackdrop) {
            editBackdrop.addEventListener('click', (e) => { if (e.target === editBackdrop) closeEditModal(); });
        }
        editCloseBtns.forEach(btn => btn && btn.addEventListener('click', closeEditModal));
        document.querySelectorAll('.tm-edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openEditModal({
                    id: btn.getAttribute('data-id'),
                    completion: btn.getAttribute('data-completion'),
                    statusLabel: btn.getAttribute('data-status-label')
                });
            });
        });
        if (editForm && editSaveBtn) {
            editForm.addEventListener('submit', function() {
                editSaveBtn.disabled = true;
                editSaveBtn.textContent = 'Saving...';
            });
        }
    })();
</script>

<?php include("footer.php"); ?>
