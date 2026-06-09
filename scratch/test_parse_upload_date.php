<?php
require __DIR__ . '/../vendor/autoload.php';

// Copy of parseUploadDateDmY logic for quick test
function parseUploadDateDmY($dateStr): ?string
{
    if ($dateStr === null || $dateStr === '') {
        return null;
    }

    if (is_numeric($dateStr)) {
        $serial = (float) $dateStr;
        if ($serial > 10000 && $serial < 100000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($serial)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    $dateStr = trim((string) $dateStr);
    if ($dateStr === '') {
        return null;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
        [$year, $month, $day] = array_map('intval', explode('-', $dateStr));

        return checkdate($month, $day, $year)
            ? sprintf('%04d-%02d-%02d', $year, $month, $day)
            : null;
    }

    $normalized = str_replace(['/', '.'], '-', $dateStr);
    $formats    = ['d-m-Y', 'd-m-y', 'j-n-Y', 'j-n-y'];

    foreach ($formats as $format) {
        $parsed = DateTime::createFromFormat('!' . $format, $normalized);
        if (! $parsed) {
            continue;
        }

        $errors = DateTime::getLastErrors();
        if (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            continue;
        }

        if ($parsed->format($format) !== $normalized) {
            continue;
        }

        $day   = (int) $parsed->format('j');
        $month = (int) $parsed->format('n');
        $year  = (int) $parsed->format('Y');

        if (! checkdate($month, $day, $year)) {
            continue;
        }

        return $parsed->format('Y-m-d');
    }

    return null;
}

$cases = [
    '15-05-2026' => '2026-05-15',
    '15/05/2026' => '2026-05-15',
    '05/15/2026' => null,
    '05-15-2026' => null,
    '2026-05-15' => '2026-05-15',
    '15-5-2026'  => '2026-05-15',
];

foreach ($cases as $input => $expected) {
    $got = parseUploadDateDmY($input);
    $ok  = $got === $expected ? 'OK' : 'FAIL';
    echo "{$ok} input={$input} expected=" . var_export($expected, true) . ' got=' . var_export($got, true) . "\n";
}
