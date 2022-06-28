<?php
require_once("../lib/fpdf/tfpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$TipoCer = (isset($_REQUEST['Tipo'])) ? $_REQUEST['Tipo'] : "";
$Resultado = (isset($_REQUEST['Resultado'])) ? $_REQUEST['Resultado'] : false;
include ("../inc/inspeccion/CerMyRCab.php");
// Creamos el PDF
$pdf = new tFPDF('P','mm','A4');
$pdf->SetTitle ('Listas de Control');
$pdf->SetAuthor('Abantos Vertical');
$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf',true);
// Añadimos Listas Control
include("../inc/inspeccion/AddListaCtrl.php");

if ($HayDatos)
	$pdf->Output();
else
	echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No existen Listas de Control.</b></span>';
	
if ($conn)
	mysql_close($conn);
?>