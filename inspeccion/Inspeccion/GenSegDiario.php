<?php
require_once("../lib/excel/PHPExcel.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

// Revisión AEG's ó Descensores y Extintores
$Where = "";
if ($_REQUEST['Fecha'] != "")
	$Where .= " AND LC.Fecha = '".$_REQUEST['Fecha']."'";
if ($_REQUEST['Parque'] != "")
	$Where .= " AND L.IdParque = ".$_REQUEST['Parque'];
if ($_REQUEST['Torre'] != "")
	$Where .= " AND L.NumeroTorre = '".fNumTorre($_REQUEST['Torre'])."'";
if ($_REQUEST['TipoAEG'] != "")
	$Where .= " AND L.TipoAerogenerador = ".$_REQUEST['TipoAEG'];
if ($_REQUEST['OT'] != "")
	$Where .= " AND LC.OT ='".$_REQUEST['OT']."'";

if (($objPHPExcel = new PHPExcel()))
{
	$Meses = array("", "Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	$Hoja = -1;
	
	$objPHPExcel->getProperties()->setCreator("Abantos Vertical");
	$objPHPExcel->getProperties()->setLastModifiedBy("Abantos");
	$objPHPExcel->getProperties()->setTitle("Seguimiento Diario");
	$objPHPExcel->getProperties()->setSubject("")->setDescription("Seguimiento Diario Revisiones");
	//fCabecera($objPHPExcel);
	//$objPHPExcel->getActiveSheet()->setTitle('Trabajos Diarios');
	
	// Buscamos si hay Revisión Líneas de Vida
	$Query = "SELECT LC.*, P.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG, PL.Tipo, LP.NumeroSerie AS NSerie
		FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea JOIN Pletinas PL ON PL.Id=LP.IdPletina 
		JOIN Parques P ON P.Id=L.IdParque WHERE LC.Tipo='R' AND PL.Tipo=LC.LTipo".$Where;
	if ($_REQUEST['NSerie'] != "")
		$Query .= " AND LP.NumeroSerie LIKE ('%".$_REQUEST['NSerie']."%')";
	$Query .= " AND LC.Resultado ".(($_REQUEST['Resultado']!="")?"=".$_REQUEST['Resultado']:"!=2");
	$Query .= " ORDER BY L.IdParque, L.NumeroTorre ASC;";
	
	if (($ListaControl = mysql_query($Query, $conn)))
	{   
		$IdControl = 0;
		fCabecera($objPHPExcel);
		$objPHPExcel->getActiveSheet()->setTitle('Líneas');

		while($Row = mysql_fetch_array($ListaControl))
		{
			fFilaComun("ListaControl");
			$objPHPExcel->getActiveSheet()->setCellValue("J".$Fila, "Revisión de líneas de Vida");
			$Fila ++;
		}
		
		unset($Row, $ListaControl);
	}

	// Buscamos si hay Revisión de Extintores
	$Query = "SELECT LC.*, P.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG
		FROM ListaCtrlExt LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN Parques P ON P.Id=L.IdParque WHERE ";
	$Query .= "LC.Estado ".(($_REQUEST['Resultado']!="")?"=".$_REQUEST['Resultado']:"!=2");
	$Query .= $Where." ORDER BY L.IdParque, L.NumeroTorre ASC;";
	
	if (($ListaCtrlExt = mysql_query($Query, $conn)))
	{
		$IdControl = 0;
		fCabecera($objPHPExcel);
		$objPHPExcel->getActiveSheet()->setTitle('Extintores');
		
		while($Row = mysql_fetch_array($ListaCtrlExt))
		{
			fFilaComun("ListaCtrlExt");

			// Observaciones Extintor, debe de incluir los datos de Falta Peso, Causas, etc
			$Texto = $objPHPExcel->getActiveSheet()->getCell("H".$Fila)->getValue().", ";
			if ($Row['FaltaPeso'] == 1)
				$Texto .= "Falta de Peso, ";
			if ($Row['Caducidad'] == 1)
				$Texto .= "Caducado, ";
			if ($Row['Otra'] != "")
				$Texto .= $Row['Otra'];
			$objPHPExcel->getActiveSheet()->setCellValue("H".$Fila, $Texto);
						
			$objPHPExcel->getActiveSheet()->setCellValue("F".$Fila, $Row['AgenteExtintor']);
			$objPHPExcel->getActiveSheet()->setCellValue("J".$Fila, "Revisión de extintores");
			$objPHPExcel->getActiveSheet()->setCellValue("K".$Fila, $Row['Materiales']);
			$objPHPExcel->getActiveSheet()->setCellValue("N".$Fila, $Row['NPlaca']);
			$Fila ++;
		}
					
		unset($Row, $ListaCtrlExt);
	}

	// Buscamos si hay Revisión de Descensores
	$Query = "SELECT LC.*, P.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG
		FROM ListaCtrlDes LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN Parques P ON P.Id=L.IdParque JOIN Trabajadores ON Trabajadores.Id=LC.IdTrabajador
		WHERE ";
	$Query .= "LC.Estado ".(($_REQUEST['Resultado']!="")?"=".$_REQUEST['Resultado']:"!=2");
	if ($_REQUEST['Fabricante'] != "")
		$Query .= " AND LC.Fabricante = ".$_REQUEST['Fabricante'];
	$Query .= $Where." ORDER BY L.IdParque, L.NumeroTorre ASC;";
	
	if (($ListaCtrlDes = mysql_query($Query, $conn)))
	{
		$IdControl = 0;
		fCabecera($objPHPExcel);
		$objPHPExcel->getActiveSheet()->setTitle('Descensores');
		
		while($Row = mysql_fetch_array($ListaCtrlDes))
		{
			fFilaComun("ListaCtrlDes");
			$objPHPExcel->getActiveSheet()->setCellValue("J".$Fila, "Revisión de descensores");
			$objPHPExcel->getActiveSheet()->setCellValue("N".$Fila, $Row['NSerie']);
			$Fila ++;
		}

		unset($Row, $ListaCtrlDes);
	}
	else
		fDebug("../", $Query,"");

	// Titulo del libro y seguridad
	$objPHPExcel->getSecurity()->setLockWindows(true);
	$objPHPExcel->getSecurity()->setLockStructure(true);
	// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Seguimiento.xls"');
	header('Cache-Control: max-age=0');
	//Creamos el Archivo .xls
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
	$objWriter->save("php://output");
}

if ($conn)
	mysql_close($conn);
	
function fCabecera($objExcel)
{
	global $Hoja, $Fila;
	$Colum = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N");
	$Texto = array ("Fecha","Mes","Parque","O.T.","Nº Torre","Tipo","Acción","Observación","Operarios",
		"Observaciones","Materiales Abantos","Materiales GAMESA","Cantidad Material","Nº Referencia");

	if (($Hoja += 1) > 0)
		$objExcel->createSheet($Hoja);
	$objExcel->setActiveSheetIndex($Hoja);
	$objExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

	// Logo Empresa
	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName("Logo");
	$objDrawing->setDescription("Logo");
	$objDrawing->setPath("../img/img_pdf.jpg");
	$objDrawing->setWidth(55);
	$objDrawing->setHeight(72.5);
	$objDrawing->setCoordinates('A1');
	$objDrawing->setWorksheet($objExcel->getActiveSheet());

	$styleArray = array(
		'font' => array(
			'bold' => true,
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		),
		'borders' => array(
			'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
		),
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor' => array(
				'argb' => 'FF808000',
			),
		),
	);

	$Fila = 6; $Count = 0;
	foreach ($Colum as $col)
	{
		$objExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		$objExcel->getActiveSheet()->getStyle($col.$Fila)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
		$objExcel->getActiveSheet()->getStyle($col.$Fila)->applyFromArray($styleArray);
		$objExcel->getActiveSheet()->setCellValue($col.$Fila, $Texto[$Count]);
		$Count ++;
	}
	$Fila ++;
}

function fFilaComun($Tabla)
{
	global $Meses, $Row,$Fila,$Trabajadores,$objPHPExcel;
	
	$objPHPExcel->getActiveSheet()->setCellValue("A".$Fila, date("d/m/Y",strtotime($Row['Fecha'])));
	$objPHPExcel->getActiveSheet()->setCellValue("B".$Fila, $Meses[intval(date("m",strtotime($Row['Fecha'])))]);
	$objPHPExcel->getActiveSheet()->setCellValue("C".$Fila, $Row['Nombre']);
	$objPHPExcel->getActiveSheet()->setCellValue("D".$Fila, $Row['OT']);
	$objPHPExcel->getActiveSheet()->setCellValue("E".$Fila, $Row['Torre']);
	$objPHPExcel->getActiveSheet()->setCellValue("F".$Fila, $Row['TipoAEG']);
	$objPHPExcel->getActiveSheet()->setCellValue("G".$Fila, "Preventivo");
	$objPHPExcel->getActiveSheet()->setCellValue("H".$Fila, isset($Row['Observaciones'])?$Row['Observaciones']:"");
	
	// Técnicos
	fBuscaOperarios($Tabla, $Row['IdControl']);
	$Texto = isset($Trabajadores[0]) ? $Trabajadores[0] : "";
	$Texto .= isset($Trabajadores[1]) ? " y ".$Trabajadores[1] : "";
	$objPHPExcel->getActiveSheet()->setCellValue("I".$Fila, $Texto);
}

function fBuscaOperarios($Tabla, $Control)
{
	global $IdControl,$Trabajadores,$conn;

	if ($IdControl != $Control)
	{
		$IdControl = $Control;
		$Trabajadores = false;
		$Query = "SELECT DISTINCT Trabajadores.Nombre, Trabajadores.Firma FROM ".$Tabla." LC
			JOIN Trabajadores ON Trabajadores.Id = LC.IdTrabajador WHERE LC.IdControl='".$Control."'";
		if (($ConTmp = mysql_query($Query, $conn)))
		{
			while($RowTmp = mysql_fetch_row($ConTmp))
				$Trabajadores[] = $RowTmp[0];
			unset($ConTmp, $RowRmp);
		}
	}
}