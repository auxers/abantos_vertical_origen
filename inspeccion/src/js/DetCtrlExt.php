<?php
	// Cabecera
	if (($nExt % 2) != 0)
	{
		$pdf->AddPage('P','A4');
		$pdf->Image("../img/cab_pdf.jpg",$pdf->getX(),$pdf->getY());

		$pdf->SetFillColor($Red = 185, $Green = 185, $Blue = 45);
		$pdf->SetFont('DejaVu','B', 12);
		$pdf->SetXY(25,$PosY = 20);
		$pdf->Cell (150,5, $Literal[0],0,0,'C');
	
		$pdf->SetFont('DejaVu','', 12);
		$pdf->SetXY(10,$PosY += 12.5);
		$pdf->Cell(20,5,$Literal[1],0,0,'L');
		$pdf->SetFont('DejaVu','B', 12);
		$pdf->Cell(80,5,$NombreParque,'B',0,'L',true);
	
		$pdf->SetX (120);
		$pdf->SetFont('DejaVu','', 12);
		$pdf->Cell(20,5,$Literal[2],0,0,'L');
		$pdf->SetFont('DejaVu','B', 12);
		$pdf->Cell(25,5,$Fecha,'B',0,'L');
	
		$pdf->SetX (170);
		$pdf->SetFont('DejaVu','', 12);
		$pdf->Cell(20,5,$Literal[3],0,0,'L');
		$pdf->SetFont('DejaVu','B', 12);
		$pdf->Cell(10,5,$IdTorre,'B',0,'C',true);
	}
	else
		$PosY += 7.5;
		
	$PosY += 12;
	$pdf->SetFont('DejaVu','', 12);
	$pdf->SetFillColor(0);
	$pdf->Text(10,$PosY,$Literal[4]);
	$Array = explode(',', $Literal[5]);
	$pdf->Rect(40,$PosY-2.5,2.5,2.5,($Localizacion == "Ground")?"F":"");
	$pdf->Text(45,$PosY,$Array[0]);		// GROUND
	$pdf->Rect(70,$PosY-2.5,2.5,2.5,($Localizacion == "Nacelle")?"F":"");
	$pdf->Text(75,$PosY,$Array[1]);		// NACELLE
	$pdf->Rect(105,$PosY-2.5,2.5,2.5,($Localizacion == "SubEstacion")?"F":"");
	$pdf->Text(110,$PosY,$Array[2]);	   // SUBESTACIÓN	
	$pdf->Rect(150,$PosY-2.5,2.5,2.5,($Localizacion != "Ground" && $Localizacion != "Nacelle" && $Localizacion != "SubEstacion")?"F":"");
	$pdf->Text(155,$PosY,($Localizacion != "Ground" && $Localizacion != "Nacelle" && $Localizacion != "SubEstacion") ? $Localizacion : $Array[3]);
	$pdf->SetFillColor($Red,$Green,$Blue);
	
	$pdf->SetFont('DejaVu','',10);
	$pdf->SetXY(10,$PosY += 4);
	$pdf->Cell (90, 5, $Literal[6]." ".$NPlaca,'B',0,'L',true);
	$pdf->SetX (110);
	$pdf->Cell (90, 5, $Literal[7]." ".$Colocacion,'B',0,'L');
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (90, 5, $Literal[8]." ".$Marca,'B',0,'L');
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (85, 5, $Literal[9]." ".$Modelo,'B',0,'L');
	$pdf->SetXY (110, $PosY -= 2.5);
	$pdf->Cell (90, 5, $Literal[10]." ".$Movido,'B',0,'L');
	
	$pdf->SetXY(10,$PosY += 7.5);
	$pdf->Cell (90, 5, $Literal[11]." ".$FechaFabricacion,'B',0,'L', true);	
	$pdf->SetX (110);	// Sustituido
	$pdf->Cell (90, 5, $Literal[13],0,0,'L');
	$yAux = $PosY + 3.5;
	$pdf->SetFillColor(0);
	$pdf->Rect (145,$yAux-2.5,2.5,2.5,($Sustituido == 1)?"F":"");
	$pdf->Text (150,$yAux,$Literal[14]);	// SI
	$pdf->Rect (155,$yAux-2.5,2.5,2.5,($Sustituido != 1)?"F":"");
	$pdf->Text (160,$yAux,$Literal[15]);	// NO

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (90, 5, $Literal[12]." ".$FechaRetimbrado,'B',0,'L');
	
	$pdf->SetFillColor($Red,$Green,$Blue);
	$pdf->SetX (110);	
	$pdf->Cell (90, 5, $Literal[18]." ".$PlacaSustitucion,'B',0,'L', true);
	$pdf->SetFillColor(0);
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (90, 5, $Literal[16],0,0,'L');
	$yAux = $PosY + 3.5;
	$pdf->Rect (50,$yAux-2.5,2.5,2.5,($AgenteExtintor == 'CO2')?"F":"");
	$pdf->Text (55,$yAux,"CO2");
	$pdf->Rect (65,$yAux-2.5,2.5,2.5,($AgenteExtintor != 'CO2')?"F":"");
	$pdf->Text (70,$yAux,$Literal[17]." ".(($AgenteExtintor != 'CO2') ? $AgenteExtintor : ""));

	$yAux = $PosY + 3.5;
	$pdf->SetX (110);	
	$pdf->SetFillColor(0);
	$pdf->Cell (90, 5, $Literal[20],0,0,'L');
	$pdf->Rect (180,$yAux-2.5,2.5,2.5,($PrecintoSustitucion == 1)?"F":"");
	$pdf->Text (185,$yAux,$Literal[14]);	// SI
	$pdf->Rect (190,$yAux-2.5,2.5,2.5,($PrecintoSustitucion == 0)?"F":"");
	$pdf->Text (195,$yAux,$Literal[15]);	// NO
	$pdf->SetFillColor($Red,$Green,$Blue);

	$pdf->SetXY(10,$PosY += 5);	
	$pdf->Cell (90, 5, $Literal[19]." ".$PesoAgExtintor,'B',0,'L', true);

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 5, $Literal[21],1,0,'L');
	$pdf->Cell (15, 5, ($CartelLu == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->SetX (110);
	$pdf->Cell (75, 5, $Literal[22],1,0,'L');
	$pdf->Cell (15, 5, ($EstadoCuerpo == 1)?"OK":"NO Ok",1,0,'C');
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 5, $Literal[23],1,0,'L');
	$pdf->Cell (15, 5, ($PegatinaCarac == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->SetX (110);
	$pdf->Cell (75, 5, $Literal[24],1,0,'L');
	$pdf->Cell (15, 5, ($EstadoCabeza == 1)?"OK":"NO Ok",1,0,'C');
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 5, $Literal[25],1,0,'L');
	$pdf->Cell (15, 5, ($PegatinaRevi == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->SetX (110);
	$pdf->Cell (75, 5, $Literal[26],1,0,'L');
	$pdf->Cell (15, 5, ($Pasador == 1)?"OK":"NO Ok",1,0,'C');

	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 5, $Literal[27],1,0,'L');
	$pdf->Cell (15, 5, ($MarcadoCE == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->SetX (110);
	$pdf->Cell (75, 5, $Literal[28],1,0,'L');
	$pdf->Cell (15, 5, ($Valvula == 1)?"OK":"NO Ok",1,0,'C');
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 5, $Literal[29],1,0,'L');
	$pdf->Cell (15, 5, ($PrecintoRetimbrado == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->SetX (110);
	$pdf->Cell (75, 5, $Literal[30],1,0,'L');
	$pdf->Cell (15, 5, ($Manguera == 1)?"OK":"NO Ok",1,0,'C');
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 5, $Literal[31],1,0,'L');	// Junta de RACCORD
	$pdf->Cell (15, 5, ($Junta == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->SetX (110);
	$pdf->Cell (75, 5, $Literal[32],1,0,'L');
	$pdf->Cell (15, 5, ($Soporte == 1)?"OK":"NO Ok",1,0,'C');
	
	$pdf->SetXY(10,$PosY += 6.5);
	$pdf->Cell (75, 5, $Literal[33],1,0,'C', true);	// Materiales Colocados
	$pdf->Cell (20, 5, $Literal[34],1,0,'C', true);	// Estado
	$pdf->Cell (30, 5, $Literal[35],1,0,'C', true);	// Causa
	$pdf->Cell (65, 5, $Literal[36],1,0,'C', true);	// Firma de los Técnicos
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (75, 16, "",1,0,'L');
	$pdf->Cell (20, 16, ($Estado == 1)?"OK":"NO Ok",1,0,'C');
	$pdf->Cell (30, 16, "",1,0,'L');
	// Firmas
	if (isset($FirChk[0]) || isset($FirChk[1]))
	{
		if (file_exists(($File = '../img/firmas/'.($Firma = isset($FirChk[0]) ? $FirChk[0] : ""))) && $Firma != "")
			$pdf->Cell(65/2,16, $pdf->Image($File, $pdf->GetX(), $pdf->GetY()+4.5, 32, 10),'LTB');
		else
			$pdf->Cell (65/2, 16, "",'LTB');

		if (file_exists(($File = '../img/firmas/'.($Firma = isset($FirChk[1]) ? $FirChk[1] : ""))) && $Firma != "")
			$pdf->Cell(65/2,16, $pdf->Image($File, $pdf->getX(), $pdf->getY()+4, 32, 10),'RTB');
		else
			$pdf->Cell (65/2, 16, "",'RTB');
	}
	else
		$pdf->Cell (65, 16, "",1,0,'L');
	
	$pdf->SetXY(10,$PosY);
	$pdf->MultiCell (65, 4, $Materiales,0,'L');
	$pdf->SetFillColor(0);
	$PosY += 4;
	$pdf->Text (107,$PosY,$Literal[37]);
	$pdf->Rect (130,$PosY-2.5,2.5,2.5,($FaltaPeso == 1)?"F":"");
	$PosY += 4;
	$pdf->Text (107,$PosY,$Literal[38]);
	$pdf->Rect (130,$PosY-2.5,2.5,2.5,($Caducidad == 1)?"F":"");
	$PosY += 4;
	$pdf->Text (107,$PosY,$Literal[39].$Otra);
	$pdf->SetFillColor($Red,$Green,$Blue);
	
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (190, 5, $Literal[40],1,0,'C', true);
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell (190, 12, "",1,0,'L');
	$pdf->SetXY(10,$PosY);
	$pdf->MultiCell (190, 4, $Observaciones,0,'L');
?>