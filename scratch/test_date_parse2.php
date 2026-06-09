<?php
$d = '05/15/2026';
$o = DateTime::createFromFormat('d/m/Y', $d);
echo "d/m/Y on 05/15/2026: " . ($o ? $o->format('Y-m-d') : 'FAIL') . "\n";
if ($o) print_r(DateTime::getLastErrors());

$d2 = '15-05-2026';
$o2 = DateTime::createFromFormat('d/m/Y', $d2);
echo "d/m/Y on 15-05-2026: " . ($o2 ? $o2->format('Y-m-d') : 'FAIL') . " => fallback today: " . date('Y-m-d') . "\n";
