<?php
require_once __DIR__."/tcpdf/tcpdf.php";

class xtcpdf extends TCPDF { }
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetFont('dejavusans', '', 10);

$pdf->AddPage();
$pdf->writeHTML($template, true, false, true, false, '');
$pdf->Output('example_006.pdf', 'I');
?>