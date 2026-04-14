=== ATTENDANCE BULK UPDATE IMPLEMENTATION PLAN ===

CURRENT STATE:
✓ Attendance listing page exists with filters
✓ Individual Edit/Delete buttons for each record
✗ No multiple selection capability
✗ No bulk update with radio buttons

REQUIREMENT:
- Add checkboxes to select multiple attendance records
- Add radio buttons for Status selection (Present, Absent, Leave, Half-day, Sick-leave)
- Add bulk action buttons: Update, Delete
- Save only selected records
- Show success/error messages

═══════════════════════════════════════════════════════════════════════════════

IMPLEMENTATION PLAN:

PHASE 1: FRONTEND CHANGES (HTML/JS in View)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Views/admin/attendance_vw.php

1.1 - ADD BULK ACTION SECTION (Above Table)
   └─ Select Status radio buttons:
      • Present (radio)
      • Absent (radio)
      • Leave (radio)
      • Half-day (radio)
      • Sick-leave (radio)
   └─ Action Buttons:
      • "Update Selected" button (Submit)
      • "Clear Selection" button (Reset)
   └─ Selected Count Display: "X records selected"

1.2 - MODIFY TABLE HEADER
   └─ Add checkbox column at start: <input type="checkbox" id="select-all">
   └─ Label: "Select"

1.3 - MODIFY TABLE ROWS
   └─ Add checkbox for each record: <input type="checkbox" name="selected_ids[]" value="">
   └─ Keep existing Action buttons (Edit, Delete)

1.4 - ADD JAVASCRIPT
   └─ Select/Deselect all functionality
   └─ Update selected count dynamically
   └─ Form validation:
      ✓ Check if at least 1 record selected
      ✓ Check if status is selected
      ✓ Show error message if not valid
   └─ Handle form submission


PHASE 2: BACKEND CHANGES (Controller)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Controllers/Attendance.php

2.1 - CREATE NEW METHOD: bulkUpdate()
   └─ Receives:
      ✓ selected_ids[] (array of attendance IDs)
      ✓ status (selected status)
      ✓ Optional: notes, leave_type
   └─ Validations:
      ✓ Check if IDs array is not empty
      ✓ Check if status is valid (present in allowed list)
      ✓ Check user authorization
   └─ Process:
      ✓ Loop through each ID
      ✓ Validate ownership/permission
      ✓ Update record with new status
      ✓ Return count of updated records
   └─ Response:
      ✓ Redirect back with success/error message
      ✓ Flash data: "X records updated successfully"


PHASE 3: MODEL CHANGES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Models/AttendanceModel.php

3.1 - CREATE NEW METHOD: updateMultiple()
   └─ Parameters:
      ✓ $ids (array of attendance IDs)
      ✓ $data (array of update data: status, notes, etc.)
   └─ Logic:
      ✓ Build WHERE IN query
      ✓ Execute update for all matching IDs
      ✓ Return number of affected rows
   
   Example Query:
   UPDATE staff_attendance 
   SET status = 'Present', updated_at = NOW()
   WHERE id IN (1, 2, 3, 4)


PHASE 4: ROUTING
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Config/Routes.php

4.1 - ADD ROUTES
   $routes->post('admin/attendance/bulk-update', 'Attendance::bulkUpdate');


═══════════════════════════════════════════════════════════════════════════════

DETAILED CODE BREAKDOWN:

1. HTML STRUCTURE (In attendance_vw.php)
   ┌──────────────────────────────────────────┐
   │ BULK ACTION SECTION (Before Table)       │
   │                                          │
   │ Status Selection:                        │
   │ ◯ Present  ◯ Absent  ◯ Leave            │
   │ ◯ Half-day  ◯ Sick-leave                │
   │                                          │
   │ [Update Selected] [Clear Selection]      │
   │ "0 records selected"                     │
   └──────────────────────────────────────────┘
            ↓
   ┌──────────────────────────────────────────┐
   │ TABLE WITH CHECKBOXES                    │
   │                                          │
   │ ☑ Select All | ☐ | Staff | Date | ...  │
   │ ☐ Record 1   | 1  | John  | 14/4 | ...  │
   │ ☐ Record 2   | 2  | Jane  | 14/4 | ...  │
   │ ☐ Record 3   | 3  | Mike  | 14/4 | ...  │
   └──────────────────────────────────────────┘


2. FORM STRUCTURE
   
   <form id="bulk-update-form" method="POST" action="<?= base_url('admin/attendance/bulk-update') ?>">
   
   <!-- Status Selection -->
   <div class="form-group">
     <label>Select Status:</label>
     <div>
       <label><input type="radio" name="status" value="Present"> Present</label>
       <label><input type="radio" name="status" value="Absent"> Absent</label>
       <label><input type="radio" name="status" value="Leave"> Leave</label>
       <label><input type="radio" name="status" value="Half-day"> Half-day</label>
       <label><input type="radio" name="status" value="Sick-leave"> Sick-leave</label>
     </div>
   </div>
   
   <!-- Hidden selected IDs -->
   <!-- (These will be populated by JavaScript) -->
   
   <!-- Buttons -->
   <button type="submit" class="btn btn-primary">Update Selected</button>
   <button type="reset" class="btn btn-secondary">Clear Selection</button>
   
   </form>


3. JAVASCRIPT LOGIC
   
   - Select All Checkbox:
     └─ Click #select-all → check/uncheck all row checkboxes
   
   - Row Checkboxes:
     └─ On change → update selected count
   
   - Form Submission:
     └─ Collect all checked IDs
     └─ Put into hidden form field
     └─ Validate:
        • At least 1 record selected?
        • Status selected?
     └─ If valid → Submit form
     └─ If invalid → Show error message


4. CONTROLLER LOGIC (Pseudo Code)
   
   public function bulkUpdate() {
       $selected_ids = $this->request->getPost('selected_ids');
       $status = $this->request->getPost('status');
       
       // Validate
       if (empty($selected_ids) || empty($status)) {
           return back()->with('error', 'Please select records and status');
       }
       
       // Update
       $updated = $this->attendanceModel->updateMultiple($selected_ids, [
           'status' => $status,
           'updated_at' => date('Y-m-d H:i:s')
       ]);
       
       return back()->with('msg', "$updated records updated successfully");
   }


═══════════════════════════════════════════════════════════════════════════════

FILE CHANGES SUMMARY:

1. app/Views/admin/attendance_vw.php
   ├─ Add bulk action section
   ├─ Add checkbox column in table
   ├─ Add JavaScript functions

2. app/Controllers/Attendance.php
   ├─ Add bulkUpdate() method

3. app/Models/AttendanceModel.php
   ├─ Add updateMultiple() method

4. app/Config/Routes.php
   ├─ Add POST route for bulk-update


═══════════════════════════════════════════════════════════════════════════════

DATABASE QUERIES:

Current Attendance Table Structure:
+----+----------+-----------------+--------+---------------+----------------+-------+
| id | staff_id | attendance_date | status | check_in_time | check_out_time | notes |
+----+----------+-----------------+--------+---------------+----------------+-------+

Update Query (Generated by updateMultiple):
UPDATE staff_attendance 
SET status = ?, updated_at = NOW()
WHERE id IN (?, ?, ?)


═══════════════════════════════════════════════════════════════════════════════

USER WORKFLOW:

1. User navigates to Attendance page
2. Filters records (optional)
3. Selects multiple records using checkboxes
4. Selects a status using radio button
5. Clicks "Update Selected" button
6. System validates selections
7. If valid → Updates all selected records in database
8. Shows success message with count: "5 records updated successfully"
9. Page refreshes and shows updated statuses


═══════════════════════════════════════════════════════════════════════════════

VALIDATION RULES:

✓ Must select at least 1 record
✓ Must select exactly 1 status
✓ Status must be valid: Present, Absent, Leave, Half-day, Sick-leave
✓ User must have permission (role 45-48)
✓ Cannot update deleted records
✓ Cannot update records from other locations (if restriction applied)


═══════════════════════════════════════════════════════════════════════════════

FEATURES INCLUDED:

✓ Multiple record selection with checkboxes
✓ Select All / Deselect All functionality
✓ Radio button for single status selection
✓ Live count display ("X records selected")
✓ Form validation (client-side and server-side)
✓ Bulk update in single database query
✓ Success/error flash messages
✓ Edit individual records still available
✓ Delete individual records still available
✓ Maintains existing filters and pagination


═══════════════════════════════════════════════════════════════════════════════

ESTIMATED COMPLEXITY:
- Frontend (View + JS): 30 lines (HTML) + 40 lines (JavaScript) = ~70 lines
- Controller: ~20 lines
- Model: ~15 lines  
- Routes: 1 line
- Total: ~106 lines of new code


═══════════════════════════════════════════════════════════════════════════════
