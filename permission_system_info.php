<?php
// Summary of Permission System

echo "=== PERMISSION SYSTEM FLOW ===\n\n";

echo "1. USER ROLES STORAGE:\n";
echo "   └─ Location: user table, 'roles' column\n";
echo "   └─ Format: Comma-separated Job IDs\n";
echo "   └─ Example: '45,46,47,48' or '1,2,3,4'\n\n";

echo "2. SIDEBAR LOGIC (mainsidebar.php):\n";
echo "   └─ Line 3-6: Extract and explode user roles\n";
echo "   └─ \$jobAssign = explode(',', \$user->roles);\n\n";

echo "3. MENU VISIBILITY CHECKS:\n";
echo "   └─ if(in_array(jobId, \$jobAssign))\n";
echo "   └─ Shows menu ONLY if user has that job ID\n\n";

echo "4. ALL MENU JOB IDs IN SYSTEM:\n";

$menus = [
    28 => "Dashboard",
    27 => "Sub Admin",
    1  => "Purchase",
    2  => "Do Registration",
    3  => "Despatch Entry",
    36 => "Voucher Entry",
    4  => "Diesel Management",
    5  => "In House Maintenance",
    6  => "Outside Maintenance",
    7  => "Advance",
    8  => "Driver Assignment",
    37 => "Task Assignment",
    9  => "Checkup",
    10 => "Overall Expense",
    11 => "Driver Salary",
    35 => "Adjust Salary",
    12 => "Staff Salary",
    40 => "Payment Voucher",
    45 => "Attendance - View",
    46 => "Attendance - Add",
    47 => "Attendance - Bulk Upload",
    48 => "Attendance - Reports",
    41 => "Payment Report (Menu Header)",
    42 => "Payment Report - Pump",
    43 => "Payment Report - Party",
    44 => "Payment Report - Vendor",
    29 => "Vehicle (Menu Header)",
    13 => "Vehicle - Master",
    14 => "Vehicle - Statutory Entry",
    30 => "Master Entry (Menu Header)",
    15 => "Master Entry - Staff/Driver",
    16 => "Master Entry - Vendor",
    17 => "Master Entry - Items",
    18 => "Master Entry - Unit",
    19 => "Master Entry - Location",
    20 => "Master Entry - Route",
    21 => "Master Entry - Bank",
    31 => "Reports (Menu Header)",
    22 => "Reports - Stock Report",
    23 => "Reports - Vehicle Ledger",
    32 => "Tyre Management (Menu Header)",
    24 => "Tyre Management - Purchase",
    34 => "Tyre Management - Stock / Download Database",
    38 => "Tyre Management - Trash Tyre",
    25 => "Tyre Management - Assign Tyre",
    26 => "Tyre Management - Report Tyre",
    33 => "Tyre Management - Repair Report"
];

$sorted_ids = array_keys($menus);
sort($sorted_ids);

foreach ($sorted_ids as $id) {
    echo "   " . str_pad($id, 3) . " => " . $menus[$id] . "\n";
}

echo "\n5. IMPORTANT NOTE:\n";
echo "   ✓ job_assign table has been DELETED\n";
echo "   ✓ System still works because checks use user.roles, NOT job_assign\n";
echo "   ✓ Assign permissions in Sub Admin > Role modal\n";
echo "   ✓ Assigned roles are stored in user.roles column\n\n";

echo "6. TO SHOW ALL TABS TO A USER:\n";
echo "   └─ Give user ALL job IDs in roles column\n";
echo "   └─ Example: user.roles = '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48'\n";
?>
