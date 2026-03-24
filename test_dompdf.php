<?php
require 'c:/xampp8.2/htdocs/transport/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml('<h1>Test PDF</h1><p>This is a test.</p>');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$output = $dompdf->output();
file_put_contents('c:/xampp8.2/htdocs/transport/test.pdf', $output);
echo "PDF generated successfully.";
?>
