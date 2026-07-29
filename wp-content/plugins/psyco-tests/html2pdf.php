<?php
require_once ('./dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$dompdf=new Dompdf();
$html = '<h1>سلام</h1>';
$dompdf->loadhtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream();
// $pdf = $dompdf->output();
// file_put_contents('test.pdf', $pdf);
