<?php
$TipoCer = "R";
require_once("../lib/fpdf/tfpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCertificado.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
require_once("../inc/inspeccion/CerMyRCab.php");

// Inicio Variables
$anchos = array(275, 125, 75, 20, 25, 70, 90, 125, 65, 145, 150); // Ancho de las columnas del pdf
$nPage = $NumReg = $IdMarca = 0;
$Query = "SELECT DISTINCT COUNT(L.Id) AS Total FROM ListaControl LC JOIN Lineas L ON L.Id = LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea = LC.IdLinea JOIN Pletinas P ON P.Id=LP.IdPletina
	WHERE L.IdParque=".$Parque." AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' AND LC.LTipo=P.Tipo AND LC.Tipo='R' AND LC.Resultado='1' GROUP BY L.Id ORDER BY Total DESC";
if (($result = mysql_query($Query, $conn)))
{
	if (mysql_num_rows($result) == 0)
		echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No existen certificados para esas fechas.</b></span>';
	else
	{   // Obtenemos el número de Lineas de Vida, para saber de qué tipo será el Certificado
		if (($TmpRow = mysql_fetch_array($result)))
			$NumReg = $TmpRow['Total'];
		unset($TmpRow, $result);
			
		// * Creamos PDF *
		$pdf = new tFPDF('L','mm','A4');
		$pdf->SetMargins(10, 5);
		$pdf->SetTitle($titulo);
		$pdf->SetAuthor("Abantos Vertical");
		$pdf->SetAutoPageBreak(true, 0.5);
		$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf', true);
		$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf', true);

		// Datos, ODG 09.08.13 Ahora Gonzalo comenta que quiere que en el Certificado aparezca primero la línea de Nacelle .1, y debajo Servicio .2
		$Query = "SELECT LC.IdLinea, LC.Fecha AS FechaRevision, L.NumeroTorre, L.TipoAerogenerador, L.TipoAerogeneradorGAMESA, LP.NumeroSerie, LP.NumeroCable, 
			P.Tipo AS TipoPletina, L.IdMarca FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea JOIN Pletinas P ON P.Id=LP.IdPletina
			WHERE L.IdParque=".$Parque." AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' AND LC.LTipo=P.Tipo AND LC.Resultado='1' AND LC.Tipo='R' ORDER BY L.NumeroTorre, L.IdMarca, LC.LTipo ASC;";
		if (($result = mysql_query($Query, $conn)))
		{   // Cab. Página
			$IdLinea = 0; $NewPage = true; $TiposAEG = false;
			while($row = mysql_fetch_array($result))
			{
				if ($IdMarca != $row['IdMarca']) {
					if (!$PiePag)
						fPiePagina($pdf);
					fCargaLiteral();
					fCabGrupo($pdf);
				}

				$Erroneo = false;
				$alto = 5; $nPletinas = 0;
				if ($NumReg > 1)	// Dos Lineas (Servicio y Nacelle)
				{   // Obtenemos el Numero de Pletinas OK
					$Query = "SELECT LP.IdPletina FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea 
						JOIN Pletinas P ON P.Id=LP.IdPletina WHERE L.Id='".$row['IdLinea']."' AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' AND LC.LTipo=P.Tipo AND LC.Resultado='1' AND LC.Tipo='R'";
					if (($Consulta = mysql_query($Query, $conn)))
						$alto *= ($nPletinas = mysql_num_rows($Consulta));
					// Obtenemos el Total de Pletinas del AEG.
					// En el caso de tener AEG's con 2 Pletinas, sólo saldrán en el Certificado los AEG's que tengan
					//	todas sus pletinas OK, sino NO...
					$Query = "SELECT COUNT(TAP.IdPletina) AS Total FROM Lineas L
						JOIN TAeroPletinas TAP ON TAP.IdTipoAEG=L.TipoAerogenerador WHERE L.Id='".$row['IdLinea']."'";
					if (($Consulta = mysql_query($Query, $conn)))
					{
						if (($TmpRow = mysql_fetch_array($Consulta)))
							$Erroneo = ($nPletinas < $TmpRow['Total']) ? true : false;
						unset($Consulta, $TmpRow);
					}
				}
				
				// Si la Torre está totalmente OK, la presento en el Certificado
				if (!$Erroneo)
				{   // Compruebo si voy añadir un grupo nuevo, que no exceda el máximo de la página
					if (($pdf->GetY() + $alto) > fCalculaLimite()) {
						if ($row['IdLinea'] != $IdLinea) {
							fPiePagina($pdf);
							fCabGrupo($pdf);
						}
					}
				
					// Fecha
					$pdf->Cell($anchos[3],5,fFechaDMY($row['FechaRevision']),1,0,'C');

					if ($NumReg > 1)	// Dos Lineas (Servicio y Nacelle)
					{   // Nº Torre
						$NumAEG = fQuitaZeros($row['NumeroTorre']);
						if ($row['IdLinea'] == $IdLinea)
						{
							$ap=$pdf->getX(); $bp=$pdf->getY();
							$pdf->Cell($anchos[3],$alto,'',0,0,'C');
							$pdf->SetXY($ap+$anchos[3],$bp);
						}
						else
							$pdf->Cell($anchos[3],$alto,$NumAEG,1,0,'C');
						$IdLinea = $row['IdLinea'];
						
						// Tipo GAMESA, Nº Serie, Nº Cable
						$pdf->Cell($anchos[4],5,$row['TipoAerogeneradorGAMESA'],1,0,'C');
						$pdf->Cell($anchos[5],5,$row['NumeroSerie'],1,0,'C',false,'',35);
						$pdf->Cell($anchos[4],5,$row['NumeroCable'],1,0,'C',false,'',20);
						// Tipo Línea de Vida
						if ($row['TipoPletina'] == 1)
						{	// Nacelle/Acceso
							$pdf->Cell($anchos[6]/2,5,$NumAEG.' .1',1,0,'C');
							$pdf->Cell($anchos[6]/2,5,'-',1,0,'C');
						}
						else
						{	// Servicio
							$pdf->Cell($anchos[6]/2,5,'-',1,0,'C');
							$pdf->Cell($anchos[6]/2,5,$NumAEG.' .2',1,0,'C');
						}
					}
					else
					{	// Una sóla línea "Nacelle"
						// Nº Torre
						$pdf->Cell($anchos[3],5,($NumAEG = fQuitaZeros($row['NumeroTorre'])),1,0,'C');
						// Tipo GAMESA, Nº Serie, Nº Cable y Tipo Línea
						$pdf->Cell($anchos[4],5,$row['TipoAerogeneradorGAMESA'],1,0,'C');
						$pdf->Cell($anchos[5],5,$row['NumeroSerie'],1,0,'C',false,'',35);
						$pdf->Cell($anchos[4],5,$row['NumeroCable'],1,0,'C',false,'',20);
						$pdf->Cell($anchos[6],5,$NumAEG.' .'.$row['TipoPletina'],1,0,'C');
					}
				
					// Fecha Revisión
					$mes_rev1 = date("m",strtotime($row['FechaRevision']));
					$mes_rev1 = $array_meses[$mes_rev1-1];
					$year_rev1 = date("Y",strtotime($row['FechaRevision']))+1;
					$pdf->Cell($anchos[4],5,$mes_rev1.'-'.$year_rev1,1,1,'C');

					// Guardo la torre que irá en ésta página para después buscar sus Refuerzos...
					$Torres[] = $row['IdLinea'];
				}
			}
			unset ($row, $result);
			
			// Sino se ha impreso el Pie de página, lo ponemos
			if (!$PiePag)
				fPiePagina($pdf);
		}
		
		unset($LiteralPie,$cabecera,$cliente,$titulo,$subtit1,$subtit2,$subtit3,$tit_parque,$titulo2,
			$htabla1,$htabla2,$htabla3,$htabla4,$htabla5,$htabla6,$hsubtabla6a,$hsubtabla6b,$htabla7,
			$detal1,$detal2,$detal3,$detal4,$obs1,$obs2,$obs3,$pie1,$pie2,$pie3);
			
		// Añadimos Listas Control
		include("../inc/inspeccion/AddListaCtrl.php");
		$pdf->Output($NomPDF, 'I');
	}
}

if ($conn)
	mysql_close($conn);

function fCalculaLimite()
{
	global $TiposAEG, $row;
	// Registro el Tipo de Aerogenerador, 
	//	para controlar cuantas líneas de Refuerzos puede haber en el pie de ésta página y cálcular su ancho
	if (!$TiposAEG)
		$TiposAEG[] = $row['TipoAerogenerador'];
	else
	{
		$Erroneo = false;
		foreach ($TiposAEG as $Valor)
		{
			if ($Valor == $row['TipoAerogenerador'])
			{
				$Erroneo = true;
				break;
			}
		}
		
		if (!$Erroneo)
			$TiposAEG[] = $row['TipoAerogenerador'];
	}
	
	return 210 - 46.5 - (3.5 * count($TiposAEG));
}

function fCargaLiteral()
{   // Cargamos Literales Certificados Líneas de Vida
	global $row,$conn,$NumReg,$LiteralPie,$IdMarca,$IdiomaCer, $cabecera, $cliente, $titulo, $subtit1, $subtit2, 
		$subtit3, $tit_parque, $titulo2, $htabla1, $htabla2, $htabla3, $htabla4, $htabla5, $htabla6, $hsubtabla6a, 
		$hsubtabla6b, $htabla7, $detal1, $detal2, $detal3, $detal4, $obs1, $obs2, $obs3, $pie1, $pie2, $pie3;

	$Literal = fGetLiterales (4, ($IdMarca = $row['IdMarca']),$IdiomaCer, $conn);
	$LiteralPie = fGetLiterales (4, $IdMarca, 0, $conn);
			
	$cabecera = $Literal[0];
	$cliente  = $Literal[1];
	$titulo   = $Literal[2];
	$subtit1  = $Literal[3];
	$subtit2  = $Literal[4];
	$subtit3  = $Literal[5];
	$tit_parque = $Literal[6];
	$titulo2  = $Literal[7];
	$htabla1  = $Literal[8];
	$htabla2  = $Literal[9];
	$htabla3  = $Literal[10];
	$htabla4  = $Literal[11];
	$htabla5  = $Literal[12];
	$htabla6  = ($NumReg == 1) ? $Literal[13] : $Literal[14];
	$hsubtabla6a = $Literal[15];
	$hsubtabla6b = $Literal[16];
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
	unset($Literal);			
}
?>