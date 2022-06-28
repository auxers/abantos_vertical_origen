<?php
$TipoCer = "D";
require_once("../lib/fpdf/tfpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCertificado.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
require_once("../inc/inspeccion/CerMyRCab.php");

// Inicio Variables
$anchos = array(275, 200, 75, 20, 25, 55, 32.5, 125, 65, 145, 150); // Ancho de las columnas del pdf
$nPage = $IdMarca = $NumRow = 0; $Limite = 210 - 42;

$Query = "SELECT LD.Fecha, L.NumeroTorre, L.TipoAerogeneradorGAMESA AS TipoAegGAMESA, LD.NSerie, LD.Fabricante, A.Nombre AS DesMarca, 
	B.Nombre AS DesModel, PSA.AnyoFabCuerdaPri, PSA.NSerieSeguridad, PSA.AnyoFabCuerdaSeg, MRG.AnyoCuerdaPri, MRG.NSerieCuerdaSeg1,
	 MRG.AnyoFabCuerdaSeg1, MRG.NSerieCuerdaSeg2, MRG.AnyoFabCuerdaSeg2
	FROM ListaCtrlDes LD LEFT JOIN DetLstCtrlPSA PSA ON PSA.IdLista = LD.Id LEFT JOIN DetLstCtrlMRG MRG ON MRG.IdLista = LD.Id
	JOIN Lineas L ON L.Id = LD.IdLinea JOIN MarcaDes A ON A.Id = LD.Fabricante JOIN ModeloDes B ON B.Id = LD.ModeloDes
	WHERE L.IdParque =".$Parque." AND LD.Fecha >= '".$FechaIni."' AND LD.Fecha <= '".$FechaFin."' AND LD.Estado='1' ORDER BY L.NumeroTorre, LD.Fecha, LD.Fabricante ASC;";
if (($result = mysql_query($Query, $conn)))
{
	if (($TotRow = mysql_num_rows($result)) == 0)
		echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No existen certificados para esas fechas.</b></span>';
	else
	{   // * Creamos PDF *
		$pdf = new tFPDF('L','mm','A4');
		$pdf->SetMargins(10, 5);
		$pdf->SetTitle($titulo);
		$pdf->SetAuthor("Abantos Vertical");
		$pdf->SetAutoPageBreak(true, 0.5);
		$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf', true);
		$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf', true);

		// Datos
		while($row = mysql_fetch_array($result))
		{   // Selecciono Textos del Certificado
			if ($IdMarca != $row['Fabricante']) {
				if (!$PiePag)
					fPiePagina($pdf);
				fCargaLiteral();
				fCabGrupo($pdf);
			}

			// ODG, 28.02.14, Se Añaden 3 Columnas más al certificado			
			$Alto = ($row['Fabricante']==2) ? 10 : ((strlen($row['NSerieSeguridad']) > 20)?10:5);
			$pdf->Cell($anchos[3],$Alto,date("d-m-Y",strtotime($row['Fecha'])),1,0,'C');
			$pdf->Cell($anchos[3],$Alto,($NumAEG = fQuitaZeros($row['NumeroTorre'])),1,0,'C');
			$pdf->Cell($anchos[4],$Alto,$row['TipoAegGAMESA'],1,0,'C');
			if (strlen($row['NSerie']) > 35) {
				$pdf->SetFont('DejaVu','',6);
				$pdf->Cell($anchos[5],$Alto,$row['NSerie'],1,0,'C', false,'',40);
				$pdf->SetFont('DejaVu','',7);
			} else
				$pdf->Cell($anchos[5],$Alto,$row['NSerie'],1,0,'C');
			// Año Fabricación Cuerda
			$pdf->Cell($anchos[4],$Alto,($row['Fabricante']==2)?$row['AnyoCuerdaPri']:$row['AnyoFabCuerdaPri'],1,0,'C');
			$pdf->Cell($anchos[3],$Alto,$row['DesMarca'],1,0,'C', false,'',20);
			$pdf->Cell($anchos[3],$Alto,$row['DesModel'],1,0,'C', false,'',20);
			// Nº Serie Cuerda Seguridad y Año Fabricación
			$a1=$pdf->getX(); $b1=$pdf->getY();
			if ($row['Fabricante']==2)
			{		// MITTELMANN
				$pdf->Cell($anchos[6],5,$row['NSerieCuerdaSeg1'],1,0,'C', false,'',20);
				$pdf->Cell($anchos[6],5,$row['AnyoFabCuerdaSeg1'],1,0,'C', false,'',20);
				$pdf->SetXY($a1,$b1+5);
				$pdf->Cell($anchos[6],5,$row['NSerieCuerdaSeg2'],1,0,'C', false,'',20);
				$pdf->Cell($anchos[6],5,$row['AnyoFabCuerdaSeg2'],1,0,'C', false,'',20);
				$pdf->SetXY($a1+($anchos[6]*2),$b1);
			}
			else
			{		// Por defecto, PSA
				if ($Alto == 5)
					$pdf->Cell($anchos[6],$Alto,$row['NSerieSeguridad'],1,0,'C', false,'',20);
				else {
					if (strlen($row['NSerieSeguridad'])>35)
						$pdf->SetFont('DejaVu','',6);
					$pdf->MultiCell($anchos[6],(strlen($row['NSerieSeguridad'])>20)?$Alto/2:$Alto,$row['NSerieSeguridad'],1,'C');
					$pdf->SetXY($a1+$anchos[6],$b1);
					if (strlen($row['NSerieSeguridad'])>35)
						$pdf->SetFont('DejaVu','',7);
				}
				$pdf->Cell($anchos[6],$Alto,$row['AnyoFabCuerdaSeg'],1,0,'C', false,'',20);
			}
			// Próxima Revisión
			$mes_rev1 = date("m",strtotime($row['Fecha']));
			$mes_rev1 = $array_meses[$mes_rev1-1];
			$year_rev1 = date("Y",strtotime($row['Fecha']))+1;
			$pdf->Cell($anchos[4],$Alto,$mes_rev1.'-'.$year_rev1,1,1,'C');

			$NumRow ++;
			if (($pdf->GetY() + $Alto) > $Limite) {
				fPiePagina($pdf);				
				if ($NumRow < $TotRow)
					fCabGrupo($pdf);
			}
		}

		// Sino se ha impreso el Pie de página, lo ponemos
		if (!$PiePag)
			fPiePagina($pdf);

		unset($result,$row,$LiteralPie, $cabecera,$cliente,$titulo,$subtit1,$subtit2,$subtit3,$tit_parque,$titulo2,
			$htabla1,$htabla2,$htabla3,$htabla4,$htabla5,$htabla6,$hsubtabla6a,$hsubtabla6b,$htabla7,
			$detal1,$detal2,$detal3,$detal4,$obs1,$obs2,$obs3,$pie1,$pie2,$pie3);
			
		// Añadimos Listas Control
		include("../inc/inspeccion/AddListaCtrl.php");
		$pdf->Output($NomPDF, 'I');
	}
}

if ($conn)
	mysql_close($conn);
	
function fCargaLiteral()
{   // Cargamos Literales Certificados Líneas de Vida
	global $row,$conn,$LiteralPie,$IdMarca,$IdiomaCer, $cabecera, $cliente, $titulo, $subtit1, $subtit2, 
		$subtit3, $tit_parque, $titulo2, $htabla1, $htabla2, $htabla3, $htabla4, $htabla5, $htabla6, $hsubtabla6a, 
		$hsubtabla6b, $htabla7, $detal1, $detal2, $detal3, $obs1, $obs2, $obs3, $pie1, $pie2, $pie3;

	$Literal = fGetLiterales(5, ($IdMarca=$row['Fabricante']), $IdiomaCer, $conn);
	$LiteralPie = fGetLiterales(5, $IdMarca, 0, $conn);

	$cabecera = $Literal[0];
	$cliente  = $Literal[1];
	$titulo   = $Literal[2];
	$subtit1  = $Literal[3];
	$subtit2  = $Literal[4];
	$subtit3  = $detal4 = "";
	$tit_parque = $Literal[5];
	$titulo2  = $Literal[6];
	$htabla1  = $Literal[7];		// Fecha
	$htabla2  = $Literal[8];		// Torre
	$htabla3  = $Literal[9];		// Tipo de AEG
	$htabla4  = $Literal[10];	   // Nº de Serie
	$detal1   = $Literal[11];	   // Año Fabricación Cuerda
	$htabla5  = $Literal[12];	   // Fabricante
	$detal2   = $Literal[13];	   // Modelo
	$htabla6  = $Literal[14];	   // Cuerda de Seguridad
	$hsubtabla6a = $Literal[15];	// 	Nº de Serie
	$hsubtabla6b = $Literal[16];	// 	Año Fabricación
	$htabla7 = $Literal[17];		// Próxima Revisión
	$detal3 = $Literal[18];
	$obs1 = $Literal[19];
	$obs2 = $Literal[20];
	$obs3 = $Literal[21];
	$pie1 = $Literal[22];
	$pie2 = $Literal[23];
	$pie3 = $Literal[24];
	unset($Literal);
}	
?>