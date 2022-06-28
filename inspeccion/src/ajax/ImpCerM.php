<?php
$TipoCer = "M";
require_once("../lib/fpdf/tfpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCertificado.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
require_once("../inc/inspeccion/CerMyRCab.php");

// Inicio Variables
$anchos = array(250, 100, 75, 25, 25, 65, 60, 125, 65, 145); // Ancho de las columnas del pdf
$NumReg = $IdControl = $IdMarca = 0;
// OD, 10.06.13 Gonzalo comenta que salen duplicados los certificados y es porque el GROUP debe de ser por L.Id y no LC.Id
$Query = "SELECT L.Id AS IdLinea, L.NumeroTorre, L.TipoAerogeneradorGAMESA, LP.NumeroSerie, LP.NumeroCable, LC.Fecha,
	 LC.LTipo, LC.IdControl, L.IdMarca FROM Lineas L JOIN LineasPletina LP ON LP.IdLinea=L.Id JOIN ListaControl LC ON L.Id=LC.IdLinea
		WHERE L.IdParque=".$Parque." AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' AND LC.Resultado='1' AND LC.Tipo='M' GROUP BY L.Id ORDER BY L.NumeroTorre, LC.Fecha";
if (($result = mysql_query($Query, $conn)))
	$NumReg = mysql_num_rows($result);

if ($NumReg == 0)
	echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No existen certificados para esas fechas.</b></span>';
else
{
	$pdf = new tFPDF('L','mm','A4');
	$pdf->SetMargins(20, 5);
	$pdf->SetAuthor('Abantos Vertical');
	$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
	$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf',true);
	
	// Datos
	while($row = mysql_fetch_array($result))
	{   
		if ($IdMarca != $row['IdMarca'])
		{   // Cargamos Literales Certificados Líneas de Vida
			$Literal = fGetLiterales (4, ($IdMarca = $row['IdMarca']),$IdiomaCer,$conn);
			$LiteralPie = fGetLiterales (4, $IdMarca, 0, $conn);
	
			$htabla7 = $Literal[17];
			$detal1 = $Literal[18];
			$detal2 = $Literal[19];
			$detal3 = $Literal[20];
			$detal4 = $Literal[21];
			$obs1 = $Literal[22];
			$obs2 = $Literal[23];
			$obs3 = $Literal[24];
			$pie1 = $Literal[25];
			$pie2 = $Literal[26];
			$pie3 = $Literal[27];				
		}
			
		// Comprobamos si el AEG tiene una ó varias Pletinas
		$Query = "SELECT LC.Fecha AS FechaRevision, LC.IdLinea, L.NumeroTorre, L.TipoAerogeneradorGAMESA, LP.NumeroSerie, LP.NumeroCable,
		 	P.Tipo AS TipoPletina FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea
 			JOIN Pletinas P ON P.Id=LP.IdPletina WHERE LC.IdLinea=".($Torres[0] = $row['IdLinea'])." AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' 
			AND LC.Resultado='1' AND LC.Tipo='M' AND LC.LTipo=P.Tipo ORDER BY P.Tipo ASC";
		if (($Consulta = mysql_query($Query, $conn)))
		{   // Obtenemos el Total de Pletinas del AEG.
			// En el caso de tener AEG's con 2 Pletinas, sólo saldrán en el Certificado los AEG's que tengan
			//	todas sus pletinas OK, sino NO...
			$Erroneo = false;
			if (($Tipo = (mysql_num_rows($Consulta)==1)?"1":"2") == "2")
			{   // Tipo = 2, Servicio y Nacelle
				$Query = "SELECT COUNT(TAP.IdPletina) AS Total FROM Lineas L
					JOIN TAeroPletinas TAP ON TAP.IdTipoAEG=L.TipoAerogenerador WHERE L.Id='".$row['IdLinea']."'";
				if (($ConTmp = mysql_query($Query, $conn)))
				{
					if (($TmpRow = mysql_fetch_array($ConTmp)))
						$Erroneo = (mysql_num_rows($Consulta) < $TmpRow['Total']) ? true : false;
					unset ($ConTmp, $TmpRow);
				}
			}
			
			if (!$Erroneo)
			{   // Cabecera
				$pdf->AddPage();
				$pdf->SetTitle($titulo);
				$pdf->Image("../img/img_pdf.jpg",$pdf->getX(),$pdf->getY());
				$pdf->Cell(250,22,'',0,1);
			
				$pdf->SetFont('DejaVu','',9);
				$pdf->MultiCell(250,4,$Literal[0],0,'L');		// Cabecera
				$pdf->Ln();
				$pdf->Cell($anchos[0],6,$Literal[1],1,1,'C');	// Cliente
				$pdf->SetFont('DejaVu','B',9);
				$pdf->Cell($anchos[0],6,$Literal[2],1,1,'C');	// Título
			
				$pdf->SetFont('DejaVu','',8);
				$pdf->Cell($anchos[1],6,$Literal[3],1,0,'L');	// Subtit1
				$pdf->Cell($anchos[2],6,$Literal[4],1,0,'L');	// Subtit2
				$pdf->Cell($anchos[2],6,$Literal[5],1,1,'L');	// Subtit3
				$pdf->Cell($anchos[0],6,$Literal[6]." ".$NombreParque,1,1,'L');
				
				$NumAEG = fQuitaZeros($row['NumeroTorre']);
				$pdf->SetFont('DejaVu','',7);
				$pdf->SetFillColor(225,225,225);
				$pdf->Cell($anchos[3],($Alto = ($Tipo == "1")?6:10),$Literal[8],1,0,'C',true,'',20);	// htabla1
				$pdf->Cell($anchos[3],$Alto,$Literal[9],1,0,'C',true,'',20);	// htabla2
				$pdf->Cell($anchos[4],$Alto,$Literal[10],1,0,'C',true,'',20);  // htabla3
				$pdf->Cell($anchos[5],$Alto,$Literal[11],1,0,'C',true,'',35);  // htabla4
				$pdf->Cell($anchos[4],$Alto,$Literal[12],1,0,'C',true,'',20);  // htabla5

				if ($Tipo == "1")
				{   // Una Línea de Vida
					$pdf->Cell($anchos[6],6,$Literal[13],1,0,'C',true);	 // htabla6
					if (mb_strlen($htabla7, "UTF-8") < 20)
						$pdf->Cell($anchos[3],6,$htabla7,1,1,'C',true);
					else
						$pdf->MultiCell($anchos[3],3,$htabla7,1,'C',true);

					$pdf->SetFont('DejaVu','',7);
					$pdf->Cell($anchos[3],6,fFechaDMY($row['Fecha']),1,0,'C');
					$pdf->Cell($anchos[3],6,$NumAEG,1,0,'C');
					$pdf->Cell($anchos[4],6,$row['TipoAerogeneradorGAMESA'],1,0,'C');
					$pdf->Cell($anchos[5],6,$row['NumeroSerie'],1,0,'C',false,'',40);
					$pdf->Cell($anchos[4],6,$row['NumeroCable'],1,0,'C',false,'',20);
					$pdf->Cell($anchos[6],6,$NumAEG.' .'.$row['LTipo'],1,0,'C');
				
					$mes_rev = (int)date("m",strtotime($row['Fecha']));
					$mes_rev = $array_meses[$mes_rev-1];
					$year_rev=date("Y",strtotime($row['Fecha']))+1;
					$pdf->Cell($anchos[3],6,$mes_rev.'-'.$year_rev,1,1,'C');
				}
				else
				{   // Líneas Nacelle y Servicio
					$a1=$pdf->getX(); $b1=$pdf->getY();
					$pdf->Cell($anchos[6],5,$Literal[14],1,0,'C',true);	// htabla6
					$a2=$pdf->getX(); $b2=$pdf->getY();
					$pdf->SetXY($a1,$b1+5);
					
					$pdf->Cell(30,5,$Literal[15],1,0,'C',true);			// hsubtabla6a
					$pdf->Cell(30,5,$Literal[16],1,0,'C',true);			// hsubtabla6b
					$pdf->SetXY($a2,$b2);
					if (strlen($htabla7, "UTF-8") < 20)
						$pdf->Cell($anchos[3],10,$htabla7,1,1,'C',true);
					else
						$pdf->MultiCell($anchos[3],5,$htabla7,1,'C',true);
			
					$IdTorre = 0;
					while($row_datos = mysql_fetch_array($Consulta))
					{
						$pdf->SetFont('DejaVu','',7);
						$pdf->Cell($anchos[3], 5, fFechaDMY($row_datos['FechaRevision']),1,0,'C');
						if ($row_datos['IdLinea'] == $IdTorre)
						{
							$ap = $pdf->getX(); $bp = $pdf->getY();
							$pdf->Cell($anchos[3], 10,"",0,0,'C');
							$pdf->SetXY($ap+$anchos[3],$bp);
						}
						else
							$pdf->Cell($anchos[3], 10, $NumAEG,1,0,'C');
				
						$IdTorre = $row_datos['IdLinea'];
						$pdf->Cell($anchos[4],5,$row_datos['TipoAerogeneradorGAMESA'],1,0,'C');
						$pdf->Cell($anchos[5],5,$row_datos['NumeroSerie'],1,0,'C',false,'',40);
						$pdf->Cell($anchos[4],5,$row_datos['NumeroCable'],1,0,'C',false,'',20);

						if ($row_datos['TipoPletina']==1)
						{   // Nacelle/Acceso
							$pdf->Cell(30,5,$NumAEG.' .1',1,0,'C');
							$pdf->Cell(30,5,'-',1,0,'C');
						} else {
							// Servicio
							$pdf->Cell(30,5,'-',1,0,'C');
							$pdf->Cell(30,5,$NumAEG.' .2',1,0,'C');
						}

						$mes_rev1 = date("m",strtotime($row_datos['FechaRevision']));
						$mes_rev1 = $array_meses[$mes_rev1-1];
						$year_rev1 = date("Y",strtotime($row_datos['FechaRevision']))+1;
						$pdf->Cell($anchos[3],5,$mes_rev1.'-'.$year_rev1,1,1,'C');
					}
					unset($row_datos, $IdTorre);
				}
			
				// Seleccionamos sólo los Operarios que han intervenido en éste AEG's
				if ($row['IdControl'] != $IdControl && $row['IdControl'] > 0)
				{
					$IdControl = $row['IdControl'];
					$Query = "SELECT DISTINCT Trabajadores.Nombre, Trabajadores.Firma FROM ListaControl LC
						JOIN Trabajadores ON Trabajadores.Id = LC.IdTrabajador WHERE LC.IdControl='".$IdControl."' AND (LC.Resultado='1' OR LC.Resultado='2')";
					if (($ConTmp = mysql_query($Query, $conn)))
					{
						if (mysql_num_rows($ConTmp) > 0)
						{
							$Trabajadores = $Firmas = false;
							while($RowTmp = mysql_fetch_row($ConTmp)) {
								$Trabajadores[] = $RowTmp[0];
								$Firmas[] = $RowTmp[1];
							}
							unset($ConTmp, $RowRmp);
						}
					}
				}

				// Pie Certificado
				fPiePagina($pdf);
			}
		}
	}
	unset($result,$row,$Literal,$LiteralPie, $cabecera,$cliente,$titulo,$subtit1,$subtit2,$subtit3,
		$tit_parque,$titulo2,$htabla1,$htabla2,$htabla3,$htabla4,$htabla5,$htabla6,$hsubtabla6a,$hsubtabla6b,
		$htabla7,$detal1,$detal2,$detal3,$detal4,$obs1,$obs2,$obs3,$pie1,$pie2,$pie3);

	// Añadimos Listas Control
	// OD, 08.04.13 Gonzalo comenta que no quiere que estén en el mismo fichero las listas de Control.
	//include("../inc/inspeccion/AddListaCtrl.php");
	$pdf->Output($NomPDF, 'I');
}

if ($conn)
	mysql_close($conn);
?>