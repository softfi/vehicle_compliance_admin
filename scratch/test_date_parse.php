<?php
require __DIR__ . '/../vendor/autoload.php';

$tests = ['15-05-2026', '15/05/2026', '05-15-2026', '15-05-26', '3/5/2027', '05/03/2027'];
foreach ($tests as $d) {
    echo "Input: {$d}\n";
    $o = DateTime::createFromFormat('d/m/Y', $d);
    echo '  d/m/Y: ' . ($o ? $o->format('Y-m-d') : 'FAIL') . "\n";
    $o2 = DateTime::createFromFormat('d-m-Y', $d);
    echo '  d-m-Y: ' . ($o2 ? $o2->format('Y-m-d') : 'FAIL') . "\n";
    $ts = strtotime($d);
    echo '  strtotime: ' . ($ts ? date('Y-m-d', $ts) : 'FAIL') . "\n";
    echo "  is_numeric: " . (is_numeric($d) ? 'yes' : 'no') . "\n\n";
}

foreach ([45822, 15, 20260515, 45427, 58139] as $n) {
    try {
        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($n);
        echo "Excel serial {$n} => " . $dt->format('Y-m-d') . "\n";
    } catch (Exception $e) {
        echo "Excel serial {$n} => ERROR\n";
    }
}

// Simulate upload logic for 15-05-2026
$des_date = '15-05-2026';
if (is_numeric($des_date)) {
    echo "numeric path\n";
} else {
    $dateObject = DateTime::createFromFormat('d/m/Y', $des_date);
    if ($dateObject) {
        echo "upload result: " . $dateObject->format('Y-m-d') . "\n";
    } else {
        echo "upload fallback today: " . date('Y-m-d') . "\n";
    }
}

$more = ['05/15/2026', '5/15/2026', '15/5/2026', '05-15-2026'];
foreach ($more as $d) {
    echo "Input: {$d}\n";
    $o = DateTime::createFromFormat('d/m/Y', $d);
    echo '  d/m/Y: ' . ($o ? $o->format('Y-m-d') : 'FAIL') . "\n";
    if ($o) {
        $warn = DateTime::getLastErrors();
        if ($warn['warning_count']) print_r($warn);
    }
    $o2 = DateTime::createFromFormat('d-m-Y', $d);
    echo '  d-m-Y: ' . ($o2 ? $o2->format('Y-m-d') : 'FAIL') . "\n";
    echo "\n";
}

// What if Excel toArray gives float for date typed as 15-05-2026?
// Common: user enters 15/05/2026 in excel date cell
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', '15-05-2026');
$sheet->setCellValue('A2', '15/05/2026');
$sheet->setCellValue('A3', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime('2026-05-15')));
$arr = $sheet->toArray();
foreach ($arr as $i => $row) {
    $val = $row[0];
    echo "toArray row " . ($i+1) . " type=" . gettype($val) . " val=" . var_export($val, true) . "\n";
    if (is_numeric($val)) {
        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
        echo "  => excel parse: " . $dt->format('Y-m-d') . "\n";
    }
}
