<?php
	// Con los Datos obtenidos del XML ó de ListaControl, creamos el PDF de la Lista de Control
	// Logotipo
	$pdf->AddPage('P','A4');
	$pdf->Image("../img/img_pdf.jpg",$pdf->getX(),$pdf->getY());
	// Cabecera
	$pdf->SetFont('DejaVu','B',9);
	$pdf->SetXY(15,35);
	$pdf->MultiCell(180,5,$Literal[0],1,'C',false);

	$pdf->SetFont('DejaVu','',9);
	$pdf->SetXY(10,$PosY=50);
	$pdf->Text(10,$PosY,$Literal[1].$NombreParque, 52);
	$pdf->Text(105,$PosY,$Literal[2].$Fecha);
	$PosY += 5;
	$pdf->Text(10,$PosY,$Literal[3].$IdTorre.$TipoDeLinea, 52);
	$pdf->Text(105,$PosY,$Literal[4].$NTrompa, 35);
	// ODG, 05.01.15 para VECTALINE 'Nº de Cable' va donde 'Nº de Tramo'
	$pdf->Text(158.5,$PosY, $Literal[5].(($IdMarca != 2)?$NumeroCable:""), 30);
	$PosY += 5;
	$pdf->Text(10,$PosY, $Literal[6].$NumeroSerie,52);
	$pdf->Text(105,$PosY,$Literal[7].$NAbsorbedor,35);
	$pdf->SetFillColor(0);
	$pdf->Rect(165,$PosY-2.5,2.5,2.5,($Tipo == "M")?"F":"");
	$pdf->Text(170,$PosY,$Literal[8]);
	$PosY += 5;
	$pdf->Text(10,$PosY, $Literal[9].$TipoAegGAMESA, 52);
	// ODG, 05.01.15 para VECTALINE 'Nº de Cable' va donde 'Nº de Tramo'
	$pdf->Text(105,$PosY, $Literal[10].(($IdMarca != 2)?$NTramo:$NumeroCable), 35);
	$pdf->Rect(165,$PosY-2.5,2.5,2.5,($Tipo == "R")?"F":"");
	$pdf->Text(170,$PosY,$Literal[11]);
	$PosY += 5;
	$pdf->Text(10, $PosY,$Literal[12]);

	// Estado Cable
	$pdf->SetXY(10,$PosY += 5);
	$Texto = $Literal[13];
	$Texto .= ($EstadoCable == 1) ? $Literal[14] : $EstadoCableMotivo;
	$pdf->Cell(160,5,$Texto,1,0,'L');
	$pdf->Cell(30,5,($EstadoCable == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[15],1,0,'L');
	$pdf->Cell(30,5,($Tension == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[16],1,0,'L');
	$pdf->Cell(30,5,($ConfPletina == 1)?"OK":"NO Ok",1,1,'C');
				
	// Grupo Configuración Pletinas 
	$TxtJob = array("","",$Literal[17],$Literal[18]." 1",$Literal[18]." 2");

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(80,25,"",1,0,'L');
	$pdf->Cell(80,25,"",1,0,'L');
	// Trabajos Realizados
	$pdf->MultiCell(30,12.5,sprintf(strlen(($Texto = trim($Literal[19])))?"%24.24s":"%48.48s", $Texto), 1,'C');
	
	$pdf->Text(11.5,($PosY += 3.5),$TipoPletina);
	// ODG, 28.02.14, Gonzalo comenta que las pletinas deben de aparecer NO OK, siempre que esté NO OK
	//	ó se haya hecho algún trabajo en ellas "Colocar ó Añadir", con lo cual OK sólo cuando = 1.
	// Pletina 1
	$pdf->Text(15,($PosY += 5),$NombrePletina1,20);
	if (!empty($NombrePletina1)) {
		$pdf->Text(55,$PosY,($CampoPletina1 != 1)?"NO Ok":"Ok");
		$pdf->Text(68,$PosY,$TxtJob[$CampoPletina1], 12);
	}
	// Pletina 2
	$pdf->Text(15,($PosY += 5),$NombrePletina2,20);
	if (!empty($NombrePletina2)) {
		$pdf->Text(55,$PosY,($CampoPletina2 != 1)?"NO Ok":"Ok");
		$pdf->Text(68,$PosY,$TxtJob[$CampoPletina2], 12);
	}
	// Pletina 3
	$pdf->Text(15,($PosY += 5),$NombrePletina3,20);
	if (!empty($NombrePletina3)) {
		$pdf->Text(55,$PosY,($CampoPletina3 != 1)?"NO Ok":"Ok");
		$pdf->Text(68,$PosY,$TxtJob[$CampoPletina3], 12);
	}
	// Pletina 4
	$pdf->Text(15,($PosY += 5),$NombrePletina4,20);
	if (!empty($NombrePletina4)) {
		$pdf->Text(55,$PosY,($CampoPletina4 != 1)?"NO Ok":"Ok");
		$pdf->Text(68,$PosY,$TxtJob[$CampoPletina4], 12);
	}

	$pdf->SetXY(10,$PosY += 1.5);
	$pdf->Cell(160,5,$Literal[20],1,0,'L');
	if ($VarillasRoscadas > 1)
		$pdf->Text(125,$PosY+3.5,$Literal[21].($VarillasRoscadas-1));
	$pdf->Cell(30,5,($VarillasRoscadas == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[22],1,0,'L');
	$pdf->Cell(30,5,($Cartel == 1)?"OK":"NO Ok",1,1,'C');

	// Anclaje Inferior
	$pdf->SetXY(10,$PosY += 5);
	$Texto = $Literal[23];
	if ($AnclajeInf1 == 1)
		$Texto .= $Literal[24]; // "Tensor, Perrillos, Guardacabos, Tuercas"
	else if (count($Array = explode(',', $Literal[24])) > 0)
	{
		if ($AnclajeInf1AI == 2 || $AnclajeInf1Tensor == 2 || 
			$AnclajeInf1Perrillos == 2 || $AnclajeInf1Guardacabos == 2 || $AnclajeInf1Tuercas == 2)
		{   // Colocar
			$Texto .= " ".$Literal[17]." - ";

			if ($AnclajeInf1AI == 2)
				$Texto .= trim(str_replace(':', '',$Literal[23])).",";
			if ($AnclajeInf1Tensor == 2)
				$Texto .= $Array[0].",";
			if ($AnclajeInf1Perrillos == 2)
				$Texto .= $Array[1].",";
			if ($AnclajeInf1Guardacabos == 2)
				$Texto .= $Array[2].",";
			if ($AnclajeInf1Tuercas == 2)
				$Texto .= $Array[3].",";
			$Texto = substr($Texto, 0, strlen($Texto) - 1).". ";
		}

		if ($AnclajeInf1AI == 3 || $AnclajeInf1Tensor >= 3 || 
			$AnclajeInf1Perrillos >= 3 || $AnclajeInf1Guardacabos >= 3 || $AnclajeInf1Tuercas >= 3)
		{   // Añadir
			$Texto .= " ".$Literal[18]." - ";

			if ($AnclajeInf1AI == 3)
				$Texto .= trim(str_replace(':','',$Literal[23])).",";
			if ($AnclajeInf1Tensor >= 3)
				$Texto .= $Array[0].",";
			if ($AnclajeInf1Perrillos >= 3)
				$Texto .= $Array[1].",";
			if ($AnclajeInf1Guardacabos >= 3)
				$Texto .= $Array[2].",";
			if ($AnclajeInf1Tuercas >= 3)
				$Texto .= $Array[3].",";
			$Texto = substr($Texto, 0, strlen($Texto) - 1);
		}
	}
	$pdf->Cell(160,5,trim($Texto),1,0,'L');
	$pdf->Cell(30,5,($AnclajeInf1 == 1)?"OK":"NO Ok",1,1,'C');

	// Anclaje Superior
	$pdf->SetXY(10,$PosY += 5);
	$Texto = $Literal[25];
	if ($AnclajeSup1 == 1)
		$Texto .= $Literal[26];
	else if (count($Array = explode(',', $Literal[26])) > 0)
	{
		if ($AnclajeSup1AS == 2 || $AnclajeSup1Pasador == 2 || $AnclajeSup1Bulon == 2)
		{   // Colocar
			$Texto .= $Literal[17]." - ";
			if ($AnclajeSup1AS == 2)
				$Texto .= trim(str_replace(':', '',$Literal[25])).($AnclajeSup1ASMotivo != ""?" (".$AnclajeSup1ASMotivo.")": "").",";
			if ($AnclajeSup1Pasador == 2)
				$Texto .= $Array[0].",";
			if ($AnclajeSup1Bulon == 2)
				$Texto .= $Array[1].",";
			$Texto = substr($Texto, 0, strlen($Texto) - 1).". ";
		}

		if ($AnclajeSup1AS == 3 || $AnclajeSup1Pasador >= 3 || $AnclajeSup1Bulon >= 3)
		{   // Añadir
			$Texto .= $Literal[18]." - ";
			if ($AnclajeSup1AS == 3)
				$Texto .= trim(str_replace(':', '',$Literal[25])).($AnclajeSup1ASMotivo != ""?" (".$AnclajeSup1ASMotivo.")": "").",";
			if ($AnclajeSup1Pasador >= 3)
				$Texto .= $Array[0].",";
			if ($AnclajeSup1Bulon >= 3)
				$Texto .= $Array[1].",";
			$Texto = substr($Texto, 0, strlen($Texto) - 1);
		}
	}
	$pdf->Cell(160,5,$Texto,1,0,'L');
	$pdf->Cell(30,5,($AnclajeSup1 == 1)?"OK":"NO Ok",1,1,'C');

	// Tipo Anclaje Superior
	$pdf->SetXY(10,$PosY += 5);
	$Texto = $Literal[27];
	// Delantero - Trasero
	if (count($Array = explode(',', $Literal[28])) > 0)
	{
		if ($AnclajeSup2 == 1)
			$Texto .= $Array[0];		// Delantero
		else if ($AnclajeSup2 == 2)
			$Texto .= $Array[1];		// Trasero
	}

	$pdf->Cell(160,5,$Texto,1,0,'L');
	$pdf->Cell(30,5,($AnclajeSup1AS == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[29],1,0,'L');
	$pdf->Cell(30,5,($TornilleriaPletina == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[30],1,0,'L');
	$pdf->Cell(30,5,($TornilleriaApriete == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[31],1,0,'L');
	$pdf->Cell(30,5,($Ensayo == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[32],1,0,'L');
	$pdf->Cell(30,5,($Escalera == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[33],1,0,'L');
	$pdf->Cell(30,5,($Interferencia == 1)?"OK":"NO Ok",1,1,'C');

	// Estado Amortiguador
	$pdf->SetXY(10,$PosY += 5);
	$Texto = $Literal[34];
	if ($Amortiguador == 1)
		$Texto .= $Literal[35];
	else if (count($Array = explode(',', $Literal[35])) > 0)
	{   // Muelle, Tuercas, Golpes, Bulón, Pasador		
		if ($AmortiguadorMuelle != 1)
			$Texto .= " ".$Array[0]." (".$AmortiguadorMuelleMotivo."), ";
		if ($AmortiguadorPasador != 1)
			$Texto .= " ".$Array[4].",";
		if ($AmortiguadorBulon != 1)
			$Texto .= " ".$Array[3];
	}
	$pdf->Cell(160,5,$Texto,1,0,'L');
	$pdf->Cell(30,5,($Amortiguador == 1)?"OK":"NO Ok",1,1,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[36],1,0,'L');
	$pdf->Cell(30,5,($Oxidacion == 1)?"OK":"NO Ok",1,1,'C');

	$PosY += 8;
	$pdf->Rect(10,$PosY-3,190,15);
	$pdf->Text(11,$PosY,$Literal[37]);
	$pdf->SetXY(10,$PosY += 2);
	$pdf->MultiCell(190,3,$Observaciones,0,'L',false);

	// Pie
	$Tecnicos = isset($EmpChk[0]) ? $EmpChk[0] : "";
	$Tecnicos .= isset($EmpChk[1]) ? ", ".$EmpChk[1] : "";
	$pdf->SetXY(10,$PosY += 15);
	$pdf->Cell(160,5,$Literal[38].(($Resultado == 1)?"OK":"NO Ok"),0,0,'L');
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[39].$TrabajosPendientes,0,0,'L');
	$pdf->SetXY(10,$PosY += 5);
	$pdf->MultiCell(190,5,$Literal[40].$Tecnicos,0,'L',false);
	$PosY = $pdf->GetY();
	$pdf->SetX(10,$PosY += 5);
	$pdf->Cell(160,5,$Literal[41],0,1,'L');
	
	// Sello
	$pdf->Cell(65,20, $pdf->Image("../img/sello_firma1.png",$pdf->GetX()+5, $pdf->GetY()));
	// Firmas
	if (isset($FirChk[0]) || isset($FirChk[1]))
	{
		if (file_exists(($File = '../img/firmas/'.($Firma = isset($FirChk[0]) ? $FirChk[0] : ""))) && $Firma != "")
			$pdf->Cell(65,20, $pdf->Image($File, $pdf->GetX(), $pdf->GetY()+5, 45, 10));
			
		if (file_exists(($File = '../img/firmas/'.($Firma = isset($FirChk[1]) ? $FirChk[1] : ""))) && $Firma != "")
			$pdf->Cell(65,20, $pdf->Image($File, $pdf->getX(), $pdf->getY()+5, 45, 10));
	}
?>