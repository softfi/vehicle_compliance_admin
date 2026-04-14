# QUICK START GUIDE - ATTENDANCE MODULE

## ⚡ 5-MINUTE SETUP

### Step 1: Verify Database Migration (Already Done ✅)
The database tables have been created automatically.

**Verify by running:**
```bash
php spark migrate:status
```

### Step 2: Add Permissions (Manual - 2 minutes)
Open your database tool and run the SQL from `attendance_permissions.sql`:

```sql
INSERT INTO job_assign (job_id, job_name) VALUES
('18.1', 'View Attendance'),
('18.2', 'Add Attendance'),
('18.3', 'Edit Attendance'),
('18.4', 'Delete Attendance'),
('18.5', 'Export Attendance'),
('18.6', 'View Attendance Reports');
```

Or copy the SQL file location:
```
c:\xampp\htdocs\transport\attendance_permissions.sql
```

### Step 3: Add Menu Link (Optional - 1 minute)
Edit: `app/Views/admin/mainsidebar.php`

Add this link in the sidebar:
```html
<li>
    <a href="<?= base_url('admin/attendance'); ?>" class="sidebar-link">
        <i class="icon-sm" data-feather="calendar"></i>
        <span>Attendance</span>
    </a>
</li>
```

### Step 4: Test the Module (2 minutes)
1. Login to admin panel
2. Navigate to: http://localhost/admin/attendance
3. Click "Add Attendance" and add a test record
4. Verify it appears in the list
5. Try exporting to Excel
6. Try viewing reports

✅ **Done! Module is ready to use**

---

## 🎯 MAIN FEATURES

### Adding Attendance
- Single entry: Admin/attendance → Add Attendance
- Bulk upload: Admin/attendance → Bulk Upload

### Viewing Attendance
- List view: Admin/attendance (with filters)
- Calendar view: Admin/attendance/calendar
- Reports: Admin/attendance/reports

### Exporting Data
- Excel: Click "Export to Excel" on any listing
- Template: Admin/attendance → Download Template

### Analytics
- Admin/attendance/analytics → Select staff → View trends

---

## 📁 FILES CREATED/MODIFIED

### Created Files (13)
1. `app/Models/AttendanceModel.php` - Data model
2. `app/Controllers/Attendance.php` - Business logic
3. `app/Database/Migrations/2026-04-14-000001_CreateAttendanceTables.php` - Migration
4. `app/Views/admin/attendance_vw.php` - Main listing
5. `app/Views/admin/add_attendance_vw.php` - Add form
6. `app/Views/admin/edit_attendance_vw.php` - Edit form
7. `app/Views/admin/bulk_attendance_vw.php` - Bulk upload
8. `app/Views/admin/attendance_reports_vw.php` - Reports hub
9. `app/Views/admin/attendance_report_details_vw.php` - Report display
10. `app/Views/admin/calendar_view.php` - Calendar
11. `app/Views/admin/attendance_analytics.php` - Analytics
12. `attendance_permissions.sql` - SQL permissions
13. `ATTENDANCE_MODULE_COMPLETE.md` - Full documentation

### Modified Files (1)
1. `app/Config/Routes.php` - Added 18 attendance routes

---

## 🔗 ACCESS POINTS

| Feature | URL |
|---------|-----|
| **Main List** | /admin/attendance |
| **Add Record** | /admin/attendance/add |
| **Bulk Upload** | /admin/attendance/bulk |
| **Reports** | /admin/attendance/reports |
| **Calendar** | /admin/attendance/calendar |
| **Analytics** | /admin/attendance/analytics |

---

## 💡 TIPS & TRICKS

### Download Excel Template
- Go to Bulk Upload page
- Click "Download Template" button
- Use this template for consistent data format

### Bulk Upload Tips
1. Fill the Excel template
2. Make sure date format is YYYY-MM-DD
3. Status must be: Present, Absent, Leave, Half-day, or Sick-leave
4. Upload from Bulk Upload page
5. Review errors (if any) before confirming

### Filter & Search
- Use date range filters to find specific periods
- Filter by staff member, status, or location
- Click "Reset" to clear all filters

### Export & Print
- Click "Export to Excel" to download filtered data
- Click "Print" in reports to view print preview
- All exports include today's date and filters applied

---

## 🚨 COMMON ISSUES & FIXES

### Issue: "Attendance module not showing in menu"
**Fix:** Manually add link to sidebar.php (see Step 3 above)

### Issue: "Permission denied when adding attendance"
**Fix:** Add permissions to job_assign table (see Step 2 above)

### Issue: "No data in calendar or analytics"
**Fix:** Make sure you've added attendance records first

### Issue: "Excel export shows errors"
**Fix:** Check that staff IDs are numeric and exist in database

### Issue: "Duplicate entry error"
**Fix:** Staff already has attendance for this date - edit existing record

---

## 📞 SUPPORT

For issues or questions:
1. Check `ATTENDANCE_MODULE_COMPLETE.md` for full documentation
2. Review error messages in browser console
3. Check database migration status: `php spark migrate:status`
4. Verify permissions are correctly added to job_assign table

---

## ✅ VERIFICATION

To verify everything is working:

```bash
# 1. Check migrations ran
php spark migrate:status

# 2. Check permissions are in database
# Run: SELECT * FROM job_assign WHERE job_id LIKE '18.%';

# 3. Test attendance page
# Navigate to: http://localhost/admin/attendance

# 4. Try adding an attendance record
# Click Add Attendance → Fill form → Save
```

If all above work, ✅ module is fully functional!

---

**Last Updated:** April 14, 2026  
**Status:** ✅ READY TO USE  
**Support:** See ATTENDANCE_MODULE_COMPLETE.md for detailed documentation
