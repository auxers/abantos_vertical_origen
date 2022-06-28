<?php
require_once("../lib/fpdf/fpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$Query = "SELECT LC.Fecha, Parques.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG, PL.Tipo, 
	LP.NumeroSerie, LC.OT, LC.Observaciones, LC.TrabajosPendientes, LC.Resultado
	FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea JOIN Pletinas PL ON PL.Id=LP.IdPletina JOIN Parques ON Parques.Id=L.IdParque
	WHERE LC.Tipo='R' AND PL.Tipo=LC.LTipo";
if ($_REQUEST['Fecha'] != "")
	$Query .= " AND LC.Fecha = '".$_REQUEST['Fecha']."'";
if ($_REQUEST['Parque'] != "")
	$Query .= " AND L.IdParque = ".$_REQUEST['Parque'];
if ($_REQUEST['Torre'] != "")
	$Query .= " AND L.NumeroTorre = '".fNumTorre($_REQUEST['Torre'])."'";
if ($_REQUEST['TipoAEG'] != "")
	$Query .= " AND L.TipoAerogenerador = ".$_REQUEST['TipoAEG'];
if ($_REQUEST['NSerie'] != "")
	$Query .= " AND LP.NumeroSerie LIKE ('%".$_REQUEST['NSerie']."%')";
if ($_REQUEST['OT'] != "")
	$Query .= " AND LC.OT ='".$_REQUEST['OT']."'";
$Query .= " AND LC.Resultado ".(($_REQUEST['Resultado'] != "") ? "=".$_REQUEST['Resultado'] : "!=2");
$Query .= " ORDER BY L.IdParque, L.NumeroTorre ASC;";

if (($result = mysql_query($Query, $conn)))
{
	if (mysql_num_rows($result) == 0)
		echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No existen Datos.</b></span>';
	else
	{
		// * Creamos PDF *
		$pdf = new FPDF('L','mm','A4');
		$pdf->SetMargins(5,5);
		$pdf->SetTitle ("Seguimiento Diario");
		$pdf->SetAuthor("Abantos Vertical");
		
		// Cabecera común
		fCabecera($pdf);

		while($row = mysql_fetch_array($result))
		{
			$pdf->Cell(15,5,date("d/m/y",strtotime($row['Fecha'])),0,0,'C');
			$pdf->Cell(40,5,$row['Nombre'],0,0,'L',false,'',true,20);
			$pdf->Cell(15,5,$row['Torre'],0,0,'C',false,'', true,5);
			$pdf->Cell(30,5,$row['TipoAEG'],0,0,'C',false,'', true,15);
			$pdf->Cell(40,5,$row['NumeroSerie'],0,0,'C',false,'', true,20);
			$pdf->Cell(20,5,(($row['Tipo']=="1")?"Nacelle":"Servicio"),0,0,'C');
			$pdf->Cell(25,5,$row['OT'],0,0,'C',false,'', true,15);
			$pdf->Cell(50,5,$row['Observaciones'],0,0,'L',false,'', true,30);
			$pdf->Cell(35,5,$row['TrabajosPendientes'],0,0,'L',false,'', true,20);
			
			fSaltoLinea($pdf);
		}
		
		$pdf->Output();
	}
}

mysql_close($conn);

// ===========================================================================================
//	\fn fCabecera($ObjPDF)
//	\brief Crea la Cabecera del Informe
// ===========================================================================================
function fCabecera($ObjPDF)
{
	$ObjPDF->AddPage();
	$ObjPDF->SetFont('Arial','B',12);
	$ObjPDF->Cell(285,5,"Seguimiento Diario de Revisiones",'1',0,'C');
	$ObjPDF->Ln(6.5);
	
	$ObjPDF->SetFont('Arial','B', 9);
	$ObjPDF->Cell(15,5,"Fecha",'B',0,'C');
	$ObjPDF->Cell(40,5,"Parque",'B',0,'C');
	$ObjPDF->Cell(15,5,"Torre",'B',0,'C');
	$ObjPDF->Cell(30,5,"Tipo AEG",'B',0,'C');
	$ObjPDF->Cell(40,5,"Nº Serie",'B',0,'C');
	$ObjPDF->Cell(20,5,"Línea",'B',0,'C');
	$ObjPDF->Cell(25,5,"OT",'B',0,'C');
	$ObjPDF->Cell(50,5,"Observaciones",'B',0,'C');
	$ObjPDF->Cell(35,5,"Tra.Pendientes",'B',0,'C');
	$ObjPDF->Ln(6.5);

	$ObjPDF->SetFont('Arial','', 9);
}

// ===========================================================================================
//	\fn fSaltoLinea($ObjPDF)
//	\brief Controla el máximo de Y para saber si debemos de generar una Página nueva...
// ===========================================================================================
function fSaltoLinea($ObjPDF)
{
	if ($ObjPDF->getY() < 190)
		$ObjPDF->Ln();
	else
		fCabecera($ObjPDF);
}
?>