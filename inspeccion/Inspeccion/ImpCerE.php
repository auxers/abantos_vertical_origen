<?php
require_once("../inc/function/funcs.php");
require_once("../lib/excel/PHPExcel.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$Parque = isset($_REQUEST['Parque']) ? $_REQUEST['Parque'] : "";
$FechaIni = isset($_REQUEST['FechaIni']) ? fFechaYMD($_REQUEST['FechaIni']) : date('Y-01-01');
$FechaFin = isset($_REQUEST['FechaFin']) ? fFechaYMD($_REQUEST['FechaFin']) : date('Y-12-31');
if (($objPHPExcel = new PHPExcel()))
{
	$Colum = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF");
	$Texto = array ("Parque","Fecha Revisión","Nº Torre","Localización","Colocación","Nº Placa ó Botella","Marca","Modelo","Fecha Fabricación",
		"Último Retimbrado","Agente Extintor","Peso/Presión Agente","Estado","Causa","Sustituido","Nº Placa Sustitución","Movido A...",
		"Presencia Precinto Retimbrado", "PRECINTO RETIMBRADO","CARTEL LUMINISCENTE SEÑALIZACIÓN","PEGATINA CARACTERÍSTICAS USO","PEGATINA REVISIÓN ANUAL",
		"MARCADO CE>2002","ESTADO CUERPO EXTINTOR","ESTADO CABEZA","PASADOR","MANGUERA","JUNTA DE RACCORD","VÁLVULA","SOPORTE",
		"MATERIALES COLOCADOS","OBSERVACIONES");

	$objPHPExcel->getProperties()->setCreator("Abantos Vertical");
	$objPHPExcel->getProperties()->setLastModifiedBy("Abantos");
	$objPHPExcel->getProperties()->setTitle("Inventario Extintores");
	$objPHPExcel->getProperties()->setSubject("");
	$objPHPExcel->getProperties()->setDescription("Inventario Extintores");
	$objPHPExcel->setActiveSheetIndex(0);	// Trabajamos con la hoja activa principal
	
	// Buscamos a los Técnicos...
	$Trabajadores = false; $NomParque = "";
	$Query = "SELECT DISTINCT Trabajadores.Nombre, Parques.Nombre AS Parque FROM ListaCtrlExt LC JOIN Lineas L ON L.Id = LC.IdLinea
		JOIN Parques ON Parques.Id=L.IdParque JOIN Trabajadores ON Trabajadores.Id = LC.IdTrabajador WHERE LC.IdTrabajador > 0";
	if (is_numeric($Parque))
		$Query .= " AND L.IdParque = ".$Parque;
	$Query .= " AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."'";
	if (($Consulta = mysql_query($Query, $conn)))
	{
		while($rowx = mysql_fetch_row($Consulta)) {
			$Trabajadores[] = $rowx[0];
			$NomParque = $rowx[1];
		}	
		unset($Consulta, $rowx);
	}

	// Cabecera
	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('Logo');
	$objDrawing->setDescription('Logo');
	$objDrawing->setPath('../img/img_pdf.jpg');
	$objDrawing->setWidth(55);
	$objDrawing->setHeight(72.5);
	$objDrawing->setCoordinates('A1');
	$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
	
	$objPHPExcel->getActiveSheet()->setCellValue("D2", "INFORME INVENTARIO EXTINTORES");
	$objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray(array('font'=>array('size'=>'18', 'bold'=>true),
		'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)));
	$objPHPExcel->getActiveSheet()->setCellValue("E4", "TÉCNICOS");
	$objPHPExcel->getActiveSheet()->getStyle("E4")->applyFromArray(($styleCab = array('font' => array('size'=>'12', 'bold'=>true),
		'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT))));
	$objPHPExcel->getActiveSheet()->setCellValue("F4", (isset($Trabajadores[0]) ? $Trabajadores[0] : "").
		(isset($Trabajadores[1]) ? " y ".$Trabajadores[1] : ""));
	$objPHPExcel->getActiveSheet()->getStyle("F4")->applyFromArray(array('font'=>array('size'=>'12')));	
	$objPHPExcel->getActiveSheet()->setCellValue("A6", "CLIENTE");
	$objPHPExcel->getActiveSheet()->getStyle("A6")->applyFromArray(array('font'=>array('size'=>'12')));
	$objPHPExcel->getActiveSheet()->setCellValue("B6", "GAMESA");
	$objPHPExcel->getActiveSheet()->getStyle("B6")->applyFromArray(array('font'=>array('size'=>'14','bold'=>true)));
	$objPHPExcel->getActiveSheet()->setCellValue("E6", "PARQUE EÓLICO");
	$objPHPExcel->getActiveSheet()->getStyle("E6")->applyFromArray($styleCab);
	$objPHPExcel->getActiveSheet()->setCellValue("F6", $NomParque);
	
	$styleArray = array(
		'font'=>array('size'=>'10','bold'=>true),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		),
		'borders'=>array(
			'allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN)
		),
		'fill'=>array(
			'type'=>PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor'=>array('argb'=>'FF808000'),
		),
	);

	$Fila = 8; $Count = 0;
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
	foreach ($Colum as $col)
	{
		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getStyle($col.$Fila)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
		$objPHPExcel->getActiveSheet()->getStyle($col.$Fila)->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->setCellValue($col.$Fila, $Texto[$Count]);
		$Count ++;
	}
	
	// Buscamos si hay Revisión de Extintores
	$Query = "SELECT LC.*, P.Nombre, L.NumeroTorre AS Torre, A.Nombre AS ExtMarca, B.Nombre AS ExtModel, C.Nombre AS ExtLocal, D.Nombre AS ExtColocacion
	  FROM ListaCtrlExt LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN Parques P ON P.Id=L.IdParque JOIN MarcaExt A ON A.Id=LC.Marca 
	  	JOIN ModeloExt B ON B.Id=LC.Modelo JOIN Localizacion C ON C.Id=LC.Localizacion JOIN Colocacion D ON D.Id=LC.Colocacion 
		WHERE LC.Estado != 2";
	if (is_numeric($Parque))
		$Query .= " AND L.IdParque = ".$Parque;
	$Query .= " AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."'";
	$Query .= " ORDER BY L.IdParque, L.NumeroTorre ASC;";
	
	$Fila = 9;
	if (($ListaCtrlExt = mysql_query($Query, $conn)))
	{
		while($Row = mysql_fetch_array($ListaCtrlExt))
		{
			$objPHPExcel->getActiveSheet()->setCellValue("A".$Fila, $Row['Nombre']);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$Fila, date('d/m/Y',strtotime($Row['Fecha'])));
			$objPHPExcel->getActiveSheet()->setCellValue("C".$Fila, $Row['Torre']);
			$objPHPExcel->getActiveSheet()->setCellValue("D".$Fila, $Row['ExtLocal']);
			$objPHPExcel->getActiveSheet()->setCellValue("E".$Fila, $Row['ExtColocacion']);
			$objPHPExcel->getActiveSheet()->setCellValue("F".$Fila, $Row['NPlaca']);
			$objPHPExcel->getActiveSheet()->setCellValue("G".$Fila, $Row['ExtMarca']);
			$objPHPExcel->getActiveSheet()->setCellValue("H".$Fila, $Row['ExtModel']);
			$objPHPExcel->getActiveSheet()->setCellValue("I".$Fila, $Row['FechaFabricacion']);
			$objPHPExcel->getActiveSheet()->setCellValue("J".$Fila, $Row['FechaRetimbrado']);
			$objPHPExcel->getActiveSheet()->setCellValue("K".$Fila, $Row['AgenteExtintor']);
			$objPHPExcel->getActiveSheet()->setCellValue("L".$Fila, $Row['PesoAgExtintor']);
			$objPHPExcel->getActiveSheet()->setCellValue("M".$Fila, ($Row['Estado']==1)?'OK':'NO OK');
			// * Causas
			$Tmp = "";
			if ($Row['FaltaPeso'] == 1)
				$Tmp .= "Falta Peso,";
			if ($Row['Caducidad'] == 1)
				$Tmp .= "Caducado,";
			$Tmp .= $Row['Otra'];
			$objPHPExcel->getActiveSheet()->setCellValue("N".$Fila, $Tmp);
						
			$objPHPExcel->getActiveSheet()->setCellValue("O".$Fila, ($Row['Sustituido']==1)?'SI':'NO');
			$objPHPExcel->getActiveSheet()->setCellValue("P".$Fila, $Row['PlacaSustitucion']);
			$objPHPExcel->getActiveSheet()->setCellValue("Q".$Fila, $Row['Movido']);
			$objPHPExcel->getActiveSheet()->setCellValue("R".$Fila, 
				($Row['PrecintoSustitucion']==1)?'SI':'NO');   // OD, 22.04.14 debe aparecer SI ó NO
			$objPHPExcel->getActiveSheet()->setCellValue("S".$Fila, ($Row['PrecintoRetimbrado']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("T".$Fila, ($Row['CartelLu']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("U".$Fila, ($Row['PegatinaCaracUso']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("V".$Fila, ($Row['PegatinaRevision']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("W".$Fila, ($Row['MarcadoCE']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("X".$Fila, ($Row['EstadoCuerpo']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("Y".$Fila, ($Row['EstadoCabeza']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("Z".$Fila, ($Row['Pasador']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("AA".$Fila, ($Row['Manguera']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("AB".$Fila, ($Row['Junta']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("AC".$Fila, ($Row['Valvula']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("AD".$Fila, ($Row['Soporte']==1)?'OK':'NO OK');
			$objPHPExcel->getActiveSheet()->setCellValue("AE".$Fila, $Row['Materiales']);
			$objPHPExcel->getActiveSheet()->setCellValue("AF".$Fila, $Row['Observaciones']);
			
			$Fila ++;
		}

		unset($Row, $ListaCtrlExt);
	}

	// Titulo del libro y seguridad
	$objPHPExcel->getActiveSheet()->setTitle('Extintores');
	$objPHPExcel->getSecurity()->setLockWindows(true);
	$objPHPExcel->getSecurity()->setLockStructure(true);	
	// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="ImpCerE.xls"');
	header('Cache-Control: max-age=0');
	// Creamos el Archivo .xls
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save("php://output");
}
else
	echo "Error, no se ha podigo crear el Excel";

if ($conn)
	mysql_close($conn); 
?>