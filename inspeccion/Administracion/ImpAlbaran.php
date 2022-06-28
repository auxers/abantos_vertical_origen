<?php
require_once("../lib/fpdf/fpdf.php");
require_once("../lib/excel/PHPExcel.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$Query = "SELECT Alb.*, P.Nombre FROM AlbaranCAB Alb JOIN Parques P ON P.Id=Alb.IdParque WHERE ";
if (!isset($_REQUEST['Id']))
{
	$Anyo = isset($_REQUEST['Anyo']) ? $_REQUEST['Anyo'] : date('Y');
	$Query .= isset($_REQUEST['Tipo']) ? "Alb.Tipo=".$_REQUEST['Tipo'] : "Alb.Tipo = 1 OR Alb.Tipo = 2";
	if (is_numeric(($Parque = isset($_REQUEST['Parque']) ? $_REQUEST['Parque'] : "")))
		$Query .= " AND Alb.IdParque=".$Parque;
	if (is_numeric(($Mes = isset($_REQUEST['Mes']) ? $_REQUEST['Mes'] : "")))
		$Query .= " AND Alb.Fecha >='".$Anyo."-".$Mes."-01' AND Alb.Fecha <= '".$Anyo."-".$Mes."-"."31'";
	else 
		$Query .= " AND Alb.Fecha >='".$Anyo."-01-01' AND Alb.Fecha <= '".$Anyo."-12-"."31'";
}
else
	$Query .= "Alb.Id=".$_REQUEST['Id'];
$Query .= " ORDER BY Alb.Tipo, Alb.NALb ASC;";

if (($result = mysql_query($Query, $conn)))
{
	if (mysql_num_rows($result) == 0)
		echo '<script languaje="javascript">alert("No existen Albaranes en esas fechas.");window.close();</script>'; 
	else
	{   // Inicializar Variables
		$albaran = -1;
		$anchos = array(20,85,20,20,20,20);
		$cabeceras = array('Torre', 'Concepto', 'Precio', 'Unidades', 'OT', 'Importe');
		$Grupo = array("LÍNEAS DE VIDA","DESCENSORES","EXTINTORES","MATERIALES EXTRA","TRABAJOS EXTRA","DESPLAZAMIENTOS EXTRA","INSPECCIONES NO OK");

		if (!($Excel = isset($_REQUEST['Excel']) ? $_REQUEST['Excel'] : 0))
		{
			$pdf = new FPDF('P','mm','A4');
			$pdf->SetTitle ('Albaran');
			$pdf->SetAuthor('Abantos Vertical');
			$pdf->SetAutoPageBreak(true, 1);
		}
		else
		{
			$pdf = new PHPExcel();
			$pdf->getProperties()->setCreator("Abantos Vertical");
			$pdf->getProperties()->setDescription("Albaranes");
			$pdf->getProperties()->setTitle("Albaran");
		}

		while($row = mysql_fetch_array($result))
		{   // Cabecera Albarán			
			fCabecera($pdf);

			// OD, 10.06.13 Gonzalo comenta que no quieren que salgan las líneas cuyos importes sea '0.00'
			$ImpTotal = $GpoTotal = 0;
			$anchos[1] = ($row['Tipo']==3)?85:105;
			if (($Lineas = mysql_query("SELECT * FROM AlbaranDET WHERE IdAlbaran=".$row['Id']." AND Importe != 0 ORDER BY Tipo, IdTorre ASC", $conn)))
			{   // Detalle del Albarán
				$TipoL = $IdTorre = ""; $Count = $TotGpo = 0;
				$TotLin = mysql_num_rows($Lineas);				// Total Líneas Albarán
				if (($ConTmp = mysql_query("SELECT DISTINCT Tipo FROM AlbaranDET WHERE IdAlbaran=".$row['Id']." AND Importe != 0", $conn)))
					$TotGpo = 2 * mysql_num_rows($ConTmp);		// Total Grupos
				$Limite = 250;
				while($rowx = mysql_fetch_array($Lineas))
				{
					if ($TipoL != $rowx['Tipo'])
					{   // Cuando se cambio el tipo de Línea, también obligamos al cambio de torre...
						$IdTorre = "";
						if ($GpoTotal != 0)
							fTotalGrupo($pdf);
						$TipoL = $rowx['Tipo'];

						if (!$Excel)
						{
							$pdf->SetFont('Arial','B',9);
							$pdf->Cell($anchos[0]+$anchos[1],5,$Grupo[$TipoL],1,0,'C');
							$pdf->Cell($anchos[2],5,$cabeceras[2],1,0,'C');		// Precio
							$pdf->Cell($anchos[3],5,$cabeceras[3],1,0,'C');		// Unidades
							if ($row['Tipo'] == 3)								// OT
								$pdf->Cell($anchos[4],5,$cabeceras[4],1,0,'C');
							$pdf->Cell($anchos[5],5,$cabeceras[5],1,1,'C');		// Importe
							$pdf->SetFont('Arial','',8);
						}
						else
						{
							$Fila += 1;
							$pdf->getActiveSheet()->setCellValue("A".$Fila, $Grupo[$TipoL]);
							if ($row['Tipo'] == 3)
							{   // Revisión
								$pdf->getActiveSheet()->mergeCells("A".$Fila.":C".$Fila);
								$pdf->getActiveSheet()->setCellValue("D".$Fila, $cabeceras[2]);	// Precio
								$pdf->getActiveSheet()->setCellValue("E".$Fila, $cabeceras[3]);	// Unidades
								$pdf->getActiveSheet()->setCellValue("F".$Fila, $cabeceras[4]);	// OT
								$pdf->getActiveSheet()->setCellValue("G".$Fila, $cabeceras[5]);	// Importe
							}
							else
							{   // Montaje
								$pdf->getActiveSheet()->mergeCells("A".$Fila.":D".$Fila);
								$pdf->getActiveSheet()->setCellValue("E".$Fila, $cabeceras[2]);
								$pdf->getActiveSheet()->setCellValue("F".$Fila, $cabeceras[3]);
								$pdf->getActiveSheet()->setCellValue("G".$Fila, $cabeceras[5]);
							}
							$pdf->getActiveSheet()->getStyle("A".$Fila.":G".$Fila)->applyFromArray(array('font'=>array('size'=>'12', 'bold'=>true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));
						}
					}
					
					$Fila += 1;
					$Alto = (strlen($rowx['Concepto']) > 65) ? 10 : 5;
					if ($IdTorre !== $rowx['IdTorre'])
					{   $Query = "SELECT Id FROM AlbaranDET WHERE IdAlbaran=".$row['Id']." AND IdTorre='".($IdTorre = $rowx['IdTorre'])."' AND Tipo=".$TipoL." AND Importe != 0";
						if (($Consulta = mysql_query($Query, $conn)))
							$Total = mysql_num_rows($Consulta);

						if (!$Excel)
						{   // Buscamos cuantos conceptos mayores a 80 hay en el grupo de la Torre y Tipo, para preveer
							//	los altos de las celdas para que todo cuadre...
							$Query = "SELECT Concepto FROM AlbaranDET WHERE IdAlbaran=".$row['Id']." AND IdTorre='".$IdTorre."' AND Tipo=".$TipoL." AND Importe != 0";
							if (($TmpLineas = mysql_query($Query, $conn)))
							{
								while($TmpRow = mysql_fetch_array($TmpLineas))
									$Total += ($Alto > 5) ? 1 : 0;
							}
							unset ($TmpLineas, $TmpRow);
							
							if (($pdf->getY()+5*$Total) > $Limite) {
								$pdf->Cell(180,10,"Página Siguiente...",0,1,'R');
								fCabecera($pdf);
							}

							$PosX = $pdf->getX(); $PosY = $pdf->getY();
							$pdf->Cell($anchos[0],5*$Total,'Torre '.fQuitaZeros($IdTorre),1,0,'C');
							$pdf->SetXY($PosX+$anchos[0],$PosY);
						}
						else
						{
							$pdf->getActiveSheet()->getRowDimension($Fila)->setRowHeight(($Alto == 5)?15:30);
							$pdf->getActiveSheet()->setCellValue("A".$Fila, "Torre ".fQuitaZeros($IdTorre));
							if (($Total -=1) >= 1)
								$pdf->getActiveSheet()->mergeCells("A".$Fila.":A".($Fila+$Total));
							$pdf->getActiveSheet()->getStyle("A".$Fila.":A".($Fila+$Total))->applyFromArray(array(
								'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
								'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));
						}
					}
					else if (!$Excel)
						$pdf->Cell($anchos[0],$Alto,'',0,0,'C');
					
					if (!$Excel)
					{
						$PosX = $pdf->getX(); $PosY = $pdf->getY();
						$pdf->MultiCell($anchos[1],5,$rowx['Concepto'],1,'L',false,true);
						$pdf->SetXY($PosX+$anchos[1],$PosY);
						$pdf->Cell($anchos[2],$Alto,number_format($rowx['Precio'],2),1,0,'R');
						$pdf->Cell($anchos[3],$Alto,number_format($rowx['Unidades'],2),1,0,'R');
						if ($row['Tipo'] == 3)
							$pdf->Cell($anchos[4],$Alto,substr($rowx['OT'],0,15),1,0,'C');
						$pdf->Cell($anchos[5],$Alto,number_format($rowx['Importe'],2),1,0,'R');

						if (fSaltoLinea($pdf) > $Limite) {
							$pdf->Cell(180,10,"Página Siguiente...",0,1,'R');
							fCabecera($pdf);
						}
					}
					else
					{
						$pdf->getActiveSheet()->setCellValue("B".$Fila, $rowx['Concepto']);
						$pdf->getActiveSheet()->getStyle("B".$Fila)->getAlignment()->setWrapText(true);						
						
						if ($row['Tipo'] == 3)
						{   // Revisión Anual
							$pdf->getActiveSheet()->mergeCells("B".$Fila.":C".$Fila);
							$pdf->getActiveSheet()->getStyle("B".$Fila.":C".$Fila)->applyFromArray(array(
								'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
								'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));

							$pdf->getActiveSheet()->setCellValue("D".$Fila, $rowx['Precio']);
							$pdf->getActiveSheet()->getStyle("D".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
							$pdf->getActiveSheet()->setCellValue("E".$Fila, $rowx['Unidades']);
							$pdf->getActiveSheet()->getStyle("E".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
							$pdf->getActiveSheet()->setCellValue("F".$Fila, $rowx['OT']);
							$pdf->getActiveSheet()->setCellValue("G".$Fila, $rowx['Importe']);
							$pdf->getActiveSheet()->getStyle("G".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
							$pdf->getActiveSheet()->getStyle("D".$Fila.":G".$Fila)->applyFromArray(array(
								'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
								'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));
						}
						else
						{   // Montaje
							$pdf->getActiveSheet()->mergeCells("B".$Fila.":D".$Fila);
							$pdf->getActiveSheet()->getStyle("B".$Fila.":D".$Fila)->applyFromArray(array(
								'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
								'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));

							$pdf->getActiveSheet()->setCellValue("E".$Fila, $rowx['Precio']);
							$pdf->getActiveSheet()->getStyle("E".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
							$pdf->getActiveSheet()->setCellValue("F".$Fila, $rowx['Unidades']);
							$pdf->getActiveSheet()->getStyle("F".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
							$pdf->getActiveSheet()->setCellValue("G".$Fila, $rowx['Importe']);
							$pdf->getActiveSheet()->getStyle("G".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
							$pdf->getActiveSheet()->getStyle("E".$Fila.":G".$Fila)->applyFromArray(array(
								'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
								'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));
						}
					}
					
					$ImpTotal += $rowx['Importe'];
					$GpoTotal += $rowx['Importe'];
				}
				unset ($Lineas, $ConTmp, $rowx, $TotLin, $TotGpo);
			}

			fPiePagina($pdf);
		}

		if (!$Excel)
			$pdf->Output();
		else
		{	// Titulo del libro y seguridad
			$pdf->getSecurity()->setLockWindows(true);
			$pdf->getSecurity()->setLockStructure(true);				
			// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="Albaran.xls"');
			header('Cache-Control: max-age=0');
			// Creamos el Archivo .xls
			$objWriter = PHPExcel_IOFactory::createWriter($pdf, 'Excel5'); 
			$objWriter->save("php://output");			
		}
	}
}
else
	echo $Query;
	
if ($conn)
	mysql_close($conn);
	
function fCabecera($ObjPDF)
{   // Cabecera Albarán LV ó MX
	global $row,$conn, $Excel,$Fila,$albaran;
	
	$NAlb = ($row['Tipo']==1) ? "LV" : (($row['Tipo']==2)?"MX":"");
	$Ancho = 68;
	if ($row['Tipo'] != 2)
		$Texto = " Certificación de líneas de vida en el parque de, ";
	else
	{
		$Ancho = 115;
		$Texto = " Materiales y trabajos extra para la certificación de líneas de vida en el parque de, ";
	}

	// ODG, 01.10.13 Obtenemos la fecha en la que se hizo la inspección con la que se creó éste Albarán
	//	Se busca en Líneas de Vida, Descensores y Extintores por si a caso, nos quedamos con la primera que haya
	$FechaIns = $row['Fecha'];	// Por defecto, la fecha es en la que se creó el Albarán...
	foreach (array ("ListaControl", "ListaCtrlDes", "ListaCtrlExt") as $Tmp)
	{
		if (($ConTmp = mysql_query("SELECT LC.Fecha FROM ".$Tmp." LC WHERE LC.IdAlbaran=".$row['Id'], $conn)))
		{
			if (($TmpRow = mysql_fetch_row($ConTmp))) {
				$FechaIns = $TmpRow[0]; break;
			}
		}
		unset ($ConTmp, $TmpRow);
	}

	if (!$Excel)
	{
		$ObjPDF->AddPage();
		$ObjPDF->Image("../img/img_pdf.jpg");
		$ObjPDF->SetY(35);

		$ObjPDF->SetFont('Arial','BI',9);
		$NAlb .= " ".sprintf("%05s",$row['NAlb'])." / ".$row['Anyo'];
		$ObjPDF->Cell(140,6,"Albarán Nº ".$NAlb,0,0,'L');
		$ObjPDF->SetFont('Arial','',9);
		$ObjPDF->Cell(20,6,"FECHA :",0,0,'R');
		$ObjPDF->Cell(20,6,fFechaDMY($row['Fecha']),0,1,'C');
		$ObjPDF->SetFont('Arial','B',9);
		$ObjPDF->Cell(35,6,"  DE :",1,0,'L');
		$ObjPDF->Cell(145,6,"Abantos Vertical, S.L.",1,1,'L');
		$ObjPDF->Cell(35,6,"  PARA :",1,0,'L');
		$ObjPDF->Cell(145,6,"Gamesa Eólica, S.L.",1,1,'L');
		$ObjPDF->Cell(35,6,"  Nº de PEDIDO :",1,0,'L');
		$ObjPDF->Cell(145,6,$row['Pedido'],1,1,'L');
		
		$ObjPDF->Cell(22.5,6,"CONCEPTO :",0,1,'L');
		$ObjPDF->SetFont('Arial','',9);
		$ObjPDF->Cell($Ancho,4,$Texto,0,0,'L');			
		$ObjPDF->SetFont('Arial','B',9);
		$ObjPDF->Cell(50,4,$row['Nombre'],0,1,'L');
		
		$ObjPDF->Cell(50,6,"SEMANA ".date('W',strtotime($FechaIns)),0,1,'L');
		$ObjPDF->SetFont('Arial','',9);
		$ObjPDF->Cell(140,4," ".$row['CabTxt'],0,1,'L');
		$ObjPDF->Ln();
	}
	else
	{
		if (($albaran += 1) > 0)
			$ObjPDF->createSheet($albaran);
		$ObjPDF->setActiveSheetIndex($albaran);
		$ObjPDF->getActiveSheet()->getPageMargins()->setTop(0.45);
		$ObjPDF->getActiveSheet()->getPageMargins()->setRight(0.35);
		$ObjPDF->getActiveSheet()->getPageMargins()->setLeft(0.35);
		$ObjPDF->getActiveSheet()->getPageMargins()->setBottom(0.45);
		$ObjPDF->getActiveSheet()->setTitle($NAlb.sprintf("%05s",$row['NAlb'])."-".$row['Anyo']);
		$NAlb .= " ".sprintf("%05s",$row['NAlb'])." / ".$row['Anyo'];
		
		// Logo Empresa
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName("Logo");
		$objDrawing->setDescription("Logo");
		$objDrawing->setPath("../img/img_pdf.jpg");
		$objDrawing->setWidth(55);
		$objDrawing->setHeight(72.5);
		$objDrawing->setCoordinates('A1');
		$objDrawing->setWorksheet($ObjPDF->getActiveSheet());
		// Tamaño Columnas
		$ObjPDF->getActiveSheet()->getColumnDimension('A')->setWidth(13.5);
		$ObjPDF->getActiveSheet()->getColumnDimension('B')->setWidth(5);
		$ObjPDF->getActiveSheet()->getColumnDimension('C')->setWidth(40);
		$ObjPDF->getActiveSheet()->getColumnDimension('D')->setWidth(8);
		$ObjPDF->getActiveSheet()->getColumnDimension('E')->setWidth(10);
		$ObjPDF->getActiveSheet()->getColumnDimension('F')->setWidth(12);
		$ObjPDF->getActiveSheet()->getColumnDimension('G')->setWidth(10);
		
		$ObjPDF->getActiveSheet()->setCellValue("A5", "Albarán Nº ");
		$ObjPDF->getActiveSheet()->getStyle("A5")->applyFromArray(array('font'=>array('size'=>'12', 'bold'=>true),
			'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)));
		$ObjPDF->getActiveSheet()->setCellValue("B5", $NAlb);
		$ObjPDF->getActiveSheet()->setCellValue("F5", "Fecha :");
		$ObjPDF->getActiveSheet()->getStyle("F5")->applyFromArray(array('alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
		$ObjPDF->getActiveSheet()->setCellValue("G5", fFechaDMY($row['Fecha']));
							
		$ObjPDF->getActiveSheet()->setCellValue("A7", "DE");
		$ObjPDF->getActiveSheet()->getStyle("A7")->applyFromArray(($style1 = array('font'=>array('size'=>'12', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
			'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN)))));
		$ObjPDF->getActiveSheet()->setCellValue("B7", "Abantos Vertical, S.L.");
		$ObjPDF->getActiveSheet()->mergeCells("B7:G7");
		$ObjPDF->getActiveSheet()->getStyle("B7:G7")->applyFromArray(($style2 = array('font'=>array('size'=>'12', 'bold'=>false),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
			'borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN)))));
		$ObjPDF->getActiveSheet()->setCellValue("A8", "PARA");
		$ObjPDF->getActiveSheet()->getStyle("A8")->applyFromArray($style1);
		$ObjPDF->getActiveSheet()->setCellValue("B8", "Gamesa Eólica, S.L.");
		$ObjPDF->getActiveSheet()->mergeCells("B8:G8");
		$ObjPDF->getActiveSheet()->getStyle("B8:G8")->applyFromArray($style2);
		$ObjPDF->getActiveSheet()->setCellValue("A9", "Nº PEDIDO");
		$ObjPDF->getActiveSheet()->getStyle("A9")->applyFromArray($style1);
		$ObjPDF->getActiveSheet()->setCellValue("B9", $row['Pedido']);
		$ObjPDF->getActiveSheet()->mergeCells("B9:G9");
		$ObjPDF->getActiveSheet()->getStyle("B9:G9")->applyFromArray($style2);
		$ObjPDF->getActiveSheet()->setCellValue("A10", "CONCEPTO :");
		$ObjPDF->getActiveSheet()->getStyle("A10")->applyFromArray(($style3 = array('font'=>array('size'=>'12', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT))));
		$ObjPDF->getActiveSheet()->setCellValue("A11", $Texto." ".$row['Nombre']);
		$ObjPDF->getActiveSheet()->getStyle("A11")->applyFromArray(($style4 = array('font'=>array('size'=>'10', 'bold'=>false),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT))));
		$ObjPDF->getActiveSheet()->setCellValue("A12", "SEMANA ".date('W',strtotime($FechaIns)));
		$ObjPDF->getActiveSheet()->getStyle("A12")->applyFromArray($style3);
		$ObjPDF->getActiveSheet()->setCellValue("A13", " ".$row['CabTxt']);
		$ObjPDF->getActiveSheet()->getStyle("A13")->applyFromArray($style4);
		
		unset ($style1, $style2, $style3, $style4); 
		$Fila = 14;
	}
}

function fPiePagina($ObjPDF)
{   // Pie Albarán LV ó MX
	global $Excel, $Fila, $anchos, $ImpTotal, $GpoTotal;
	
	// Pongo el total del Grupo si procede
	if ($ImpTotal != $GpoTotal)
		fTotalGrupo($ObjPDF);

	if (!$Excel)
	{   // Total Albarán
		$ObjPDF->Cell(165,6,"Importe Total :",0,0,'R');
		$ObjPDF->Cell($anchos[4],6,number_format(fRound2Dec($ImpTotal),2),0,0,'R');
		$ObjPDF->Ln();
		$ObjPDF->Ln();
		$ObjPDF->Cell(25,6,"",0,0,'L');
		$ObjPDF->Cell(75,6,"Fdo. Abantos Vertical, S.L.",0,0,'L');
		$ObjPDF->Cell(75,6,"Fdo. Gamesa Eólica, S.L.",0,1,'C');
		$ObjPDF->Image("../img/sello_firma1.png", 35);
	}
	else
	{   // Total Albarán
		$Fila += 2;
		$ObjPDF->getActiveSheet()->setCellValue("D".$Fila, "TOTAL");
		$ObjPDF->getActiveSheet()->mergeCells("D".$Fila.":F".$Fila);
		$ObjPDF->getActiveSheet()->getStyle("D".$Fila.":F".$Fila)->applyFromArray(($style1 = array('font'=>array('size'=>'12', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
			'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'startcolor'=>array('argb'=>'FFA0A0A0')))));

		$ObjPDF->getActiveSheet()->setCellValue("G".$Fila, $ImpTotal);
		$ObjPDF->getActiveSheet()->getStyle("G".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
		$ObjPDF->getActiveSheet()->getStyle("G".$Fila)->applyFromArray(array('font'=>array('size'=>'12', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
			'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'startcolor'=>array('argb'=>'FFA0A0A0'))));
		$Fila += 2;
		
		$ObjPDF->getActiveSheet()->setCellValue("A".$Fila, "Fdo. Abantos Vertical, S.L.");
		$ObjPDF->getActiveSheet()->getStyle("A".$Fila)->applyFromArray(($style2 = array('font'=>array('size'=>'12', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT))));
		$ObjPDF->getActiveSheet()->setCellValue("F".$Fila, "Fdo. Gamesa Eólica, S.L.");
		$ObjPDF->getActiveSheet()->getStyle("F".$Fila)->applyFromArray($style2);
		unset ($style1, $style2);
		$Fila += 1;
		
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Firma');
		$objDrawing->setDescription('Firma');
		$objDrawing->setPath("../img/sello_firma1.png");
		$objDrawing->setWidth(100);
		$objDrawing->setHeight(100);
		$objDrawing->setCoordinates('A'.$Fila);
		$objDrawing->setWorksheet($ObjPDF->getActiveSheet());
	}
}

function fTotalGrupo($ObjPDF)
{
	global $Excel, $Fila, $anchos, $Grupo, $TipoL, $GpoTotal;

	if (!$Excel)
	{
		$ObjPDF->SetX(100);
		$ObjPDF->Cell(75,5,"TOTAL ".$Grupo[$TipoL],1,0,'L');
		$ObjPDF->Cell($anchos[4],5,number_format($GpoTotal,2),1,1,'R');
		$ObjPDF->Ln();
	}
	else
	{
		$Fila += 1;
		$ObjPDF->getActiveSheet()->setCellValue("D".$Fila, "TOTAL ".$Grupo[$TipoL]);
		$ObjPDF->getActiveSheet()->mergeCells("D".$Fila.":F".$Fila);
		$ObjPDF->getActiveSheet()->getStyle("D".$Fila.":F".$Fila)->applyFromArray(array('font'=>array('size'=>'10', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
			'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'startcolor'=>array('argb'=>'FFA0A0A0'))));

		$ObjPDF->getActiveSheet()->setCellValue("G".$Fila, $GpoTotal);
		$ObjPDF->getActiveSheet()->getStyle("G".$Fila)->getNumberFormat()->setFormatCode('#,##0.00');
		$ObjPDF->getActiveSheet()->getStyle("G".$Fila)->applyFromArray(array('font'=>array('size'=>'10', 'bold'=>true),
			'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
			'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'startcolor'=>array('argb'=>'FFA0A0A0'))));
		$Fila += 1;
	}
	$GpoTotal = 0;
}

function fSaltoLinea($ObjPDF)
{
	global $Limite;
			
	if ($ObjPDF->GetY() < $Limite)
		$ObjPDF->Ln();
		
	return $ObjPDF->GetY();
}
?>