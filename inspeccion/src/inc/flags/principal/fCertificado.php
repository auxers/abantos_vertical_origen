<?php
function fCabGrupo($ObjPDF)
{
	global $nPage, $NewPage, $PiePag, $NumReg, $anchos, $cabecera, $cliente, $titulo, $subtit1, $subtit2, 
		$subtit3, $tit_parque, $NombreParque, $titulo2, $htabla1, $htabla2, $htabla3, $htabla4, $htabla5, 
		$htabla6, $hsubtabla6a, $hsubtabla6b, $htabla7, $TipoCer, $Torres, $TiposAEG, $AnchoCab, $detal1,$detal2;

	$nPage += 1;
	$NewPage = $PiePag = $Torres = $TiposAEG = false;
	// Cabecera Común
	$ObjPDF->AddPage();
	$ObjPDF->Image("../img/img_pdf.jpg",$ObjPDF->getX(), $ObjPDF->getY());
	$ObjPDF->Cell(275,22,"",0,1,'R');
	$ObjPDF->SetFont('DejaVu','',9);
	$ObjPDF->MultiCell(275,4,$cabecera,0,'L');
	$ObjPDF->Ln();
	
	$ObjPDF->Cell($anchos[0],6,$cliente,1,1,'C');
	$ObjPDF->SetFont('DejaVu','B',9);
	$ObjPDF->Cell($anchos[0],6,$titulo,1,1,'C');
	$ObjPDF->SetFont('DejaVu','',8);
	
	$ObjPDF->Cell($anchos[1],6,$subtit1,1,0,'L');
	$ObjPDF->Cell($anchos[2],6,$subtit2,1,0,'L');
	if ($TipoCer != "D")
		$ObjPDF->Cell($anchos[2],6,$subtit3,1,0,'L');
	$ObjPDF->Ln();
	$ObjPDF->Cell($anchos[0],6,$tit_parque." ".$NombreParque,1,1,'L');
	$ObjPDF->Cell($anchos[0],6,$titulo2,1,1,'C');

	// Cabecera Grupo
	$ObjPDF->SetFont('DejaVu','',7);
	$ObjPDF->SetFillColor(225,225,225);
	if ($TipoCer != "D")
	{	// Revisión y Montaje
		$ObjPDF->Cell($anchos[3],10,$htabla1,1,0,'C',true,'',20);
		$ObjPDF->Cell($anchos[3],10,$htabla2,1,0,'C',true,'',20);
		$ObjPDF->Cell($anchos[4],10,$htabla3,1,0,'C',true,'',20);
		$ObjPDF->Cell($anchos[5],10,$htabla4,1,0,'C',true,'',35);
		$ObjPDF->Cell($anchos[4],10,$htabla5,1,0,'C',true,'',20);

		if ($NumReg > 1)
		{   // Dos Lineas (Servicio y Nacelle)
			$a1=$ObjPDF->getX(); $b1=$ObjPDF->getY();
			$ObjPDF->Cell($anchos[6],5,$htabla6,1,0,'C',true);
			$a2=$ObjPDF->getX(); $b2=$ObjPDF->getY();
			$ObjPDF->SetXY($a1,$b1+5);
			$ObjPDF->Cell($anchos[6]/2,5,$hsubtabla6a,1,0,'C',true);
			$ObjPDF->Cell($anchos[6]/2,5,$hsubtabla6b,1,0,'C',true);
			$ObjPDF->SetXY($a2,$b2);
		}
		else
		{   // Una línea (Nacelle)
			$ObjPDF->Cell($anchos[6],10,$htabla6,1,0,'C',true);
		}
	
		if (mb_strlen($htabla7, "UTF-8") < 20)
			$ObjPDF->Cell($anchos[4],10,$htabla7,1,1,'C',true);
		else
			$ObjPDF->MultiCell($anchos[4],5,$htabla7,1,'C',true);
	}
	else
	{	// Descensores
		$ObjPDF->Cell($anchos[3],10,$htabla1,1,0,'C',true,'',20);	// Fecha
		$ObjPDF->Cell($anchos[3],10,$htabla2,1,0,'C',true,'',20);	// Torre
		$ObjPDF->Cell($anchos[4],10,$htabla3,1,0,'C',true,'',20);	// Tipo AEG				
		$ObjPDF->Cell($anchos[5],10,$htabla4,1,0,'C',true,'',40);	// Nº de Serie del Descensor 
		$a1=$ObjPDF->getX(); $b1=$ObjPDF->getY();
		if (mb_strlen($detal1, "UTF-8") < 20)					   // Año Fabricación cuerda
			$ObjPDF->Cell($anchos[4],10,$detal1,1,0,'C',true,'',20);
		else
			$ObjPDF->MultiCell($anchos[4],5,$detal1,1,'C',true);
		$ObjPDF->SetXY($a1+$anchos[4],$b1);
		$ObjPDF->Cell($anchos[3],10,$htabla5,1,0,'C',true,'',20);	// Fabricante
		$ObjPDF->Cell($anchos[3],10,$detal2,1,0,'C',true,'',20);	 // Modelo

		$a1=$ObjPDF->getX(); $b1=$ObjPDF->getY();
		$ObjPDF->Cell($anchos[6]*2,5,$htabla6,1,0,'C',true,'',40);  // Cuerda de Seguridad
		$a2=$ObjPDF->getX(); $b2=$ObjPDF->getY();
		$ObjPDF->SetXY($a1,$b1+5);
		$ObjPDF->Cell($anchos[6],5,$hsubtabla6a,1,0,'C',true,'',20);	// Nº Serie
		$ObjPDF->Cell($anchos[6],5,$hsubtabla6b,1,0,'C',true,'',20);	// Año Fabricación
		$ObjPDF->SetXY($a2,$b2);
		if (mb_strlen($htabla7, "UTF-8") < 20)						 // Próxima Revisión
			$ObjPDF->Cell($anchos[4],10,$htabla7,1,1,'C',true);
		else
			$ObjPDF->MultiCell($anchos[4],5,$htabla7,1,'C',true);
	}
}

function fPiePagina($ObjPDF)
{
	global $TipoCer,$PiePag, $anchos, $conn, $row, $FechaIni, $FechaFin, $detal1, $detal2, $detal3, $detal4, 
		$obs1, $obs2, $obs3, $pie1, $pie2, $pie3, $Trabajadores, $Firmas, $Torres, $LiteralPie;
		
	$PiePag = true;
	$ObjPDF->SetFont('DejaVu','',8);
	if ($TipoCer != "D")
	{   // * * Refuerzos * *	
		$Lineas = "";
		foreach($Torres as $Valor)
	    	$Lineas .= $Valor.",";
		$Lineas = substr($Lineas,0, strlen($Lineas)-1);

		$SqlTmp = " FROM Lineas L WHERE L.Id IN ($Lineas)";
		if (($Consulta = mysql_query("SELECT DISTINCT L.TipoAerogenerador AS TipoAEG".$SqlTmp, $conn)))
		{
			while($rowx = mysql_fetch_array($Consulta))
			{   // Obtenemos los Refuerzos
				$Texto = "";
				if (($ConTmp = mysql_query("SELECT TAP.Refuerzo FROM TAeroPletinas TAP WHERE TAP.IdTipoAEG=".$rowx["TipoAEG"], $conn)))
				{
					while($rowTmp = mysql_fetch_array($ConTmp))
						$Texto .= $rowTmp['Refuerzo'].", ";
					unset ($ConTmp, $rowTmp);
				}
				$Texto = substr($Texto, 0, strlen($Texto)-2);

				// Si hay distintos Tipos de AEG's, debo de indicar el Nº Torre al que corresponde el Refuerzo
				$Erroneo = false; $nPletinas = 0;
				if (mysql_num_rows($Consulta) > 1)
				{   // Varios tipos de AEG's en el parque.
					$Tmp = "";
					if (($ConTorres = mysql_query("SELECT DISTINCT L.NumeroTorre, L.Id AS IdLinea ".$SqlTmp." AND L.TipoAerogenerador=".$rowx['TipoAEG'], $conn)))
					{   $Count = 1;
						while($rowTmp = mysql_fetch_array($ConTorres))
						{   // Compruebo que todas sus líneas estén OK, sino NO sale en el certificado
							$TmpSQL = "SELECT LP.IdPletina FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea JOIN Pletinas P ON P.Id=LP.IdPletina
								WHERE L.Id='".$rowTmp['IdLinea']."' AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' AND LC.LTipo=P.Tipo AND LC.Resultado=1 AND LC.Tipo='".$TipoCer."'";
							if (($ConTmp = mysql_query($TmpSQL, $conn)))
								$nPletinas = mysql_num_rows($ConTmp);
							// Obtenemos el Total de Pletinas del AEG.
							// En el caso de tener AEG's con 2 Pletinas, sólo saldrán en el Certificado los AEG's que tengan
							//	todas sus pletinas OK, sino NO...
							$ErrLin = false;
							$TmpSQL = "SELECT COUNT(TAP.IdPletina) AS Total FROM Lineas L
								JOIN TAeroPletinas TAP ON TAP.IdTipoAEG=L.TipoAerogenerador WHERE L.Id='".$rowTmp['IdLinea']."'";
							if (($ConTmp = mysql_query($TmpSQL, $conn)))
							{
								if (($TmpRow = mysql_fetch_array($ConTmp)))
									$ErrLin = ($nPletinas < $TmpRow['Total']) ? true : false;
								unset ($ConTmp, $TmpRow);
							}

							if (!$ErrLin)
							{
								$Tmp .= fQuitaZeros($rowTmp['NumeroTorre']);
								$Tmp .= ($Count < mysql_num_rows($ConTorres) - 1) ? ", " : " y ";
								$Count ++;
							}
							else
								$Erroneo = (mysql_num_rows($ConTorres) == 1) ? true : false;
						}
						unset ($ConTorres, $rowTmp, $Count);
					}
					$Texto .= " AEG's ".substr($Tmp, 0, strlen($Tmp)-2);
				}

				if (!$Erroneo)
					$ObjPDF->Cell($anchos[0],3.5,$detal1." ".$Texto,1,1,'L');
			}
			unset ($Consulta, $rowx);
		}

		$ObjPDF->Cell($anchos[0],4,$detal2,1,1,'L');
		$ObjPDF->Cell($anchos[7],4,$detal3,1,0,'L');
		$ObjPDF->Cell(($TipoCer == "M")?$anchos[7]:$anchos[10],4,$detal4,1,1,'L');
	}

	// Observaciones
	$ObjPDF->SetFont('DejaVu','',7);
	$ObjPDF->MultiCell($anchos[0],3.5,$obs1,1);
	$ObjPDF->MultiCell($anchos[0],3.5,$obs2,1);
	$ObjPDF->MultiCell($anchos[0],3.5,$obs3,1);

	// Grupo 1
	$Ancho = ($TipoCer == "M") ? 150 : 175;
	$ObjPDF->Cell(60,4,$pie1,'LTR',0,'C');
	$ObjPDF->Cell($Ancho/3,4,isset($Trabajadores[2]) ? $Trabajadores[2]:"",'LTB',0,'L');
	$ObjPDF->Cell($Ancho/3,4,$pie2,'TB',0,'C');
	$ObjPDF->Cell($Ancho/3,4,isset($Trabajadores[3]) ? $Trabajadores[3]:"",'RTB',0,'R');
	$ObjPDF->Cell(40,4,$pie3,'LTRB',1,'C');

	// Grupo 2
	$ObjPDF->Cell(60,4,$ObjPDF->Image("../img/img_firma1.png", $ObjPDF->GetX(), $ObjPDF->GetY()),'LR',0,'C');
	if (isset($Firmas[2]) || isset($Firmas[3]))
	{   // Izquierda
		if (file_exists(($File = '../img/firmas/'.($Firma = isset($Firmas[2]) ? $Firmas[2] : ""))) && $Firma != "")
			$ObjPDF->Cell($Ancho/3,4, $ObjPDF->Image($File, $ObjPDF->GetX() + 2.5, $ObjPDF->GetY(), 45, 10),'T');
		else
			$ObjPDF->Cell($Ancho/3,4,'','L');
		// Centro
		$ObjPDF->Cell($Ancho/3,4,$ObjPDF->Image("../img/sello_firma1.png", $ObjPDF->GetX()+5, $ObjPDF->GetY()),'T',0,'C');
		// Derecha
		if (file_exists(($File = '../img/firmas/'.($Firma = isset($Firmas[3]) ? $Firmas[3] : ""))) && $Firma != "")
			$ObjPDF->Cell($Ancho/3,4, $ObjPDF->Image($File, $ObjPDF->GetX() + 2.5, $ObjPDF->GetY(), 45, 10),'T');
		else
			$ObjPDF->Cell($Ancho/3,4,'','R');
	}
	else
		$ObjPDF->Cell($Ancho,4,$ObjPDF->Image("../img/sello_firma1.png", $ObjPDF->GetX() + 55, $ObjPDF->GetY()),'LTR',0,'C');
	$ObjPDF->Cell(40,4,'','LR',1,'C');

	// Grupo 3
	$ObjPDF->Cell(60,4,'','LR',0,'C');
	$ObjPDF->Cell($Ancho,4,'','LR',0,'C');
	if (mb_strlen($LiteralPie[0], "UTF-8") >= 28)    // 'Somain Securité' : 'PSA Sicherheitstechnik'
		$ObjPDF->SetFont('DejaVu','',6);
	$ObjPDF->Cell(40,4,$LiteralPie[0],'LR',1,'C');
	$ObjPDF->SetFont('DejaVu','',7);
	
	// Grupo 4
	$ObjPDF->Cell(60,4,'','LR',0,'C');
	if (isset($Firmas[0]) || isset($Firmas[1]))
	{
		if (file_exists(($File = '../img/firmas/'.($Firma = isset($Firmas[0]) ? $Firmas[0] : ""))) && $Firma != "")
			$ObjPDF->Cell($Ancho/2,4, $ObjPDF->Image($File, $ObjPDF->GetX() + 2.5, $ObjPDF->GetY() + 2.5, 45, 10));
		else
			$ObjPDF->Cell($Ancho/2,4,'','L');

		if (file_exists(($File = '../img/firmas/'.($Firma = isset($Firmas[1]) ? $Firmas[1] : ""))) && $Firma != "")
			$ObjPDF->Cell($Ancho/2,4, $ObjPDF->Image($File, $ObjPDF->GetX() + 30, $ObjPDF->GetY() + 2.5, 45, 10));
		else
			$ObjPDF->Cell($Ancho/2,4,'','R');
	}
	else
		$ObjPDF->Cell($Ancho,4,'','LR',0,'L');
	$ObjPDF->Cell(40,4, $LiteralPie[1],'LR',1,'C'); // 'ZL de Monterrat' : 'GmbH & Co. KG'

	// Grupos 5, 6, y 7
	$ObjPDF->Cell(60,4,'','LR',0,'C');
	$ObjPDF->Cell($Ancho,4,'','LR',0,'L');
	$ObjPDF->Cell(40,4, $LiteralPie[2],'LR',1,'C'); // '42500 Le Chambon' : 'Dellenfeld 44'
	
	$ObjPDF->Cell(60,4,'','LR',0,'C');
	$ObjPDF->Cell($Ancho,4,'','LR',0,'L');
	$ObjPDF->Cell(40,4, $LiteralPie[3],'LR',1,'C'); // 'FEUGEROLLES' : '42653 Solingen'

	$ObjPDF->Cell(60,4,'','LRB',0,'C');
	$ObjPDF->Cell($Ancho/2,4,isset($Trabajadores[0]) ? $Trabajadores[0]:"",'LB',0,'L');
	$ObjPDF->Cell($Ancho/2,4,isset($Trabajadores[1]) ? $Trabajadores[1]:"",'B',0,'R');
	$ObjPDF->Cell(40,4,'','LRB',0,'C');
}
?>