<?php
require_once("../lib/fpdf/fpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$Query = "SELECT LC.Fecha, P.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG, LC.Localizacion, LC.NPlaca, A.Nombre AS ExtMarca, 
	LC.AgenteExtintor, LC.OT, LC.Estado FROM ListaCtrlExt LC JOIN Lineas L ON L.Id=LC.IdLinea 
	JOIN Parques P ON P.Id=L.IdParque JOIN MarcaExt A ON A.Id=LC.Marca WHERE ";
$Query .= "LC.Estado ".(($_REQUEST['Resultado'] != "") ? "=".$_REQUEST['Resultado']:"!=2");
	
if ($_REQUEST['Fecha'] != "")
	$Query .= " AND LC.Fecha = '".$_REQUEST['Fecha']."'";
if ($_REQUEST['Parque'] != "")
	$Query .= " AND L.IdParque = ".$_REQUEST['Parque'];
if ($_REQUEST['Torre'] != "")
	$Query .= " AND L.NumeroTorre = '".fNumTorre($_REQUEST['Torre'])."'";
if ($_REQUEST['TipoAEG'] != "")
	$Query .= " AND L.TipoAerogenerador = ".$_REQUEST['TipoAEG'];
if ($_REQUEST['NSerie'] != "")
	$Query .= " AND LC.NPlaca LIKE ('%".$_REQUEST['NSerie']."%')";
if ($_REQUEST['OT'] != "")
	$Query .= " AND LC.OT ='".$_REQUEST['OT']."'";
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
			$pdf->Cell(30,5,$row['NPlaca'],0,0,'C',false,'', true,20);
			$pdf->Cell(25,5,$row['ExtMarca'],0,0,'C',false,'', true,20);
			$pdf->Cell(25,5,$row['Localizacion'],0,0,'C',false,'', true,20);
			$pdf->Cell(25,5,$row['AgenteExtintor'],0,0,'C',false,'', true,15);
			$pdf->Cell(25,5,$row['OT'],0,0,'C',false,'', true,15);
			$pdf->Cell(15,5,($row['Estado'] == 1) ? "OK" : "No OK",0,0,'C',false,'', true,15);
			
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
	$ObjPDF->Cell(285,5,"Seguimiento Diario de Extintores",'1',0,'C');
	$ObjPDF->Ln(6.5);
	
	$ObjPDF->SetFont('Arial','B', 9);
	$ObjPDF->Cell(15,5,"Fecha",'B',0,'C');
	$ObjPDF->Cell(40,5,"Parque",'B',0,'C');
	$ObjPDF->Cell(15,5,"Torre",'B',0,'C');
	$ObjPDF->Cell(30,5,"Tipo AEG",'B',0,'C');
	$ObjPDF->Cell(30,5,"Nº Placa",'B',0,'C');
	$ObjPDF->Cell(25,5,"Marca",'B',0,'C');
	$ObjPDF->Cell(25,5,"Localizacion",'B',0,'C');
	$ObjPDF->Cell(25,5,"Agente",'B',0,'C');
	$ObjPDF->Cell(25,5,"OT",'B',0,'C');
	$ObjPDF->Cell(15,5,"Resultado",'B',0,'C');
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