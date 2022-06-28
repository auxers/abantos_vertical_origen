<?php
	// Cabecera
	$pdf->AddPage('P','A4');
	$pdf->Image("../img/cab_pdf.jpg",$pdf->getX(),$pdf->getY());

	$pdf->SetFillColor($Red = 185, $Green = 185, $Blue = 45);
	$pdf->SetFont('DejaVu','B', 12);
	$pdf->SetXY(25,$PosY = 15);
	$pdf->MultiCell(150, 5, $Literal[0]." ".$ModeloDes,0,'C');

	$pdf->SetFont('DejaVu','', 9);
	$pdf->SetXY(10,$PosY += 18);
	$pdf->Cell(15,4,$Literal[1],0,0,'L');
	$pdf->Cell(45,4,$NombreParque,'',0,'L', true,'',25);

	$pdf->SetX (70);
	$pdf->Cell(15,4,$Literal[2],0,0,'L');
	$pdf->Cell(35,4,$ClienteParque,'',0,'L', true,'',20);
	$pdf->SetX (120);
	$pdf->Cell(16.5,4,$Literal[3],0,0,'L');
	$pdf->Cell(10,4,$IdTorre,'',0,'C', true);	
	$pdf->SetX (148);
	$pdf->Cell(12,4,$Literal[4],0,0,'L');
	$pdf->Cell(10,4,$Altura,'',0,'C', true);
	$pdf->SetX (172);
	$pdf->Cell(13.5,4,$Literal[5],0,0,'L');
	$pdf->Cell(18,4,$Fecha,'',0,'C', true);
		
	$pdf->SetXY(10,$PosY += 5);
	$pdf->Cell(17,4,$Literal[6],0,0,'L');	// Técnicos
	$pdf->Cell(60,4,isset($EmpChk[0]) ? $EmpChk[0] : "",'',0,'L', true,'',38);
	$pdf->SetX (88);
	$pdf->Cell(60,4,isset($EmpChk[1]) ? $EmpChk[1] : "",'',0,'L', true,'',38);
	$pdf->Cell(20,4,$Literal[7],0,0,'L');	// O.T.
	$pdf->Cell(32,4,$OT,'',0,'L', true,'',20);
	
	// Grupo 0
	$pdf->SetXY(7.5,$PosY += 6);
	$pdf->Cell (197.5,5,$Literal[8],1,0,'C', true);
	$pdf->SetXY(7.5,$PosY += 6.5);
	$pdf->Cell (15,8,$Literal[9],0,0,'L');
	if (strlen($NSerie) < 15)
		$pdf->Cell (27.5,7,$NSerie,0,0,'L', true);
	else {
		$pdf->SetFont('DejaVu','', 5.5);
		$NSerie .= str_repeat(" ", 41 - strlen($NSerie));
		$pdf->MultiCell (27.5,3.5, $NSerie,0,'L',true);
		$pdf->SetFont('DejaVu','', 9);
	}
		
	$pdf->SetXY(50, $PosY);
	$pdf->Cell (18,7,$Literal[10],0,0,'L');
	if (strlen($Fabricante) <= 15)
		$pdf->Cell (30,7,$Fabricante,0,0,'L', true,'',15);
	else
		$pdf->MultiCell (30,3.5,$Fabricante,0,'L',true);
	$pdf->SetXY(100, $PosY);
	$pdf->Cell (15,7,$Literal[11],0,0,'L');
	if (strlen($Longitud) <= 15)
		$pdf->Cell (30,7,$Longitud,0,0,'L', true,'',15);
	else
		$pdf->MultiCell (30,3.5,$Longitud,0,'L',true);
	$pdf->SetXY(150, $PosY);
	$pdf->Cell (25,3.5,$Literal[12],0,0,'R');
	$pdf->Cell (30,3.5,$PrecintoViejo,0,0,'L', true,'',15);
	$pdf->SetXY(150, $PosY += 4);
	$pdf->Cell (25,3.5,$Literal[13],0,0,'R');
	$pdf->Cell (30,3.5,$PrecintoNuevo,0,0,'L', true,'',15);
	
	$pdf->SetXY(7.5, $PosY += 4);
	$pdf->MultiCell (20,3.5,$Literal[14],0,'C');
	$pdf->SetXY(30,$PosY);
	$pdf->Cell (20,7,$AnyoFabricacion,0,0,'L', true);
	$pdf->SetX (52.5);
	$pdf->Cell (20,7,$Literal[15],0,0,'L');
	$pdf->Cell (30,7,($Ubicacion == 1) ? "Nacelle" : "Ground",0,0,'L', true);
	$pdf->SetX (105);
	$pdf->MultiCell (25,3.5,$Literal[16],0,'C');
	$pdf->SetXY(130,$PosY);
	$pdf->Cell (30,7,$Envasado,0,0,'L', true,'',15);
	
	// Grupo 1
	$YesNo = "SI, NO";
	$pdf->SetFont('DejaVu','',8);
	$pdf->SetFillColor($Red, $Green, $Blue);
	$PosY = fCreaCabGrupo($Literal[17], $PosY+10, $pdf, ($MarcaDes == 2)?'ALR':'LR');
	if ($MarcaDes == 2)	 // MITTELMANN
	{
		fCreaLinGrupo($Literal[18], $MaletinEqu, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[24], $CuerdasSeguridad, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[19], $SisEnvaseEqu, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[25], $PegatinaPrecinto, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[20], $SacaAzulTran, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[26], $BridaPrecinto, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[21], $BolsaPlastico, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[27], $EtiquetaExterior, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[22], $DescensorMRG, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[28], $LibroInspecciones, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[23], $CuerdaDesMRG, 10, $PosY += 3.5, $pdf, 1);
		
		// Grupo 2
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[29], $PosY+3, $pdf, 'ALR');
		fCreaLinGrupo($Literal[30], $MaletinEmb, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[33], $SacaAzulEmb, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[31], $SisEnvaseEmb, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[34], $PegatinaPreEmb, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[32], $BolsaPlasEmb, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[35], $BridaPreEmb, 110, $PosY, $pdf, 1);
		
		// Grupo 3
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[36], $PosY+3, $pdf, '');
		
		$pdf->SetFillColor($Red, $Green+25, $Blue);
		$PosY = fCreaCabGrupo($Literal[37], $PosY, $pdf, 'ALR');
		fCreaLinGrupo($Literal[38], $PreTornillTFreno, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[43], $ZonaSurcosTFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[39], $GrosorPasTFreno, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[44], $ZonaLimpiaTFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[40], $EstMuelleTFreno, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[45], $EstTornillTFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[41], $EjePinonTFreno, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[46], $TorLoctiteTFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[42], $LimPinonTFreno, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[47], $MarcasTornTFreno, 110, $PosY, $pdf, 1);

		$pdf->SetFillColor($Red, $Green+25, $Blue);
		$PosY = fCreaCabGrupo($Literal[48], $PosY+3, $pdf, 'ALR');
		fCreaLinGrupo($Literal[49], $PreTornillTPolea, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[53], $EstTornillTPolea, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[50], $HolguraEjeTPolea, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[54], $TorLoctiteTPolea, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[51], $EstNerviosTPolea, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[55], $MarcasTornTPolea, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[52], $EstCarcasaTPolea, 10, $PosY += 3.5, $pdf, 1);
		
		$pdf->SetFillColor($Red, $Green+25, $Blue);
		$PosY = fCreaCabGrupo($Literal[56], $PosY+3, $pdf, 'ALR');
		fCreaLinGrupo($Literal[57], $PreTornillCFreno, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[61], $EstTornillCFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[58], $EstJuntaCFreno, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[62], $TorLoctiteCFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[59], $EstDientesCFreno, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[63], $MarcasTornCFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[60], $RuedaLimpiaCFreno, 10, $PosY += 3.5, $pdf, 1);

		$pdf->SetFillColor($Red, $Green+25, $Blue);
		$PosY = fCreaCabGrupo($Literal[64], $PosY+3, $pdf, 'AR');
		fCreaLinGrupo($Literal[65], $DeslizamientoCuerda, 10, $PosY += 4, $pdf, 3);

		// Grupo 4
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[66], $PosY+3, $pdf, 'ALR');
		fCreaLinGrupo($Literal[67], $EstGenCuerdaPri, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[70], $LongMedidaCuerdaPri, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[68], $EstProCuerdaPri, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[71], $MosquetonCuerdaPri, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[69], $LongitudCuerdaPri, 10, $PosY += 3.5, $pdf, 1);
		fCreaLinGrupo($Literal[72], $AnyoFabCuerdaPri, 110, $PosY, $pdf, 2);

		// Grupo 5
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[73], $PosY+3, $pdf, 'AL');
		fCreaLinGrupo($Literal[74], $EstGenCuerdaSeg, 10, $PosY += 4, $pdf, 1);
		// Nº Serie de Cuerdas Seguridad
		$pdf->Text(110, $PosY, $Literal[76]);
		$pdf->SetFillColor(185,185,45);
		$pdf->SetXY(110+70, $PosY-2.5);
		$pdf->Cell (25,3, $NSerieCuerdaSeg1,0,0,"L",true,'',15);
		$pdf->SetXY(110+70, $PosY+1);
		$pdf->Cell (25,3, $NSerieCuerdaSeg2,0,0,"L",true,'',15);
		fCreaLinGrupo($Literal[75], $SupSacaCuerdaSeg, 10, $PosY += 7, $pdf, 1);
		// Años Fabricación cuerdas
		$pdf->Text(110, $PosY, $Literal[77]);
		$pdf->SetFillColor(185,185,45);
		$pdf->SetXY(110+70, $PosY-2.5);
		$pdf->Cell (11,3, $AnyoFabCuerdaSeg1,0,0,"L",true,'',10);
		$pdf->Cell (2.5,3, "",0,0);
		$pdf->Cell (11,3, $AnyoFabCuerdaSeg2,0,0,"L",true,'',10);

		// Grupo 6
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[78], $PosY+3, $pdf, 'ALR');
		fCreaLinGrupo($Literal[79], $EstMosqueton, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[80], $FucMosqueton, 110, $PosY, $pdf, 1);
		
		$PosL = 81;
		$YesNo = $Literal[86];
	}
	else					// Por Defecto, PSA
	{
		fCreaLinGrupo($Literal[18], $Bolsa, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[21], $DescensorAG, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[19], $Sellado, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[22], $CaboAnclaje, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[20], $NumeroSello, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[23], $Humedad, 110, $PosY, $pdf, 1);

		// Grupo 2
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[24], $PosY+3, $pdf, 'LR');
		fCreaLinGrupo($Literal[25], $EtiquetaLegible, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[28], $CuerdaSalida, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[26], $EstadoCarcasa, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[29], $MosquetonArgolla, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[27], $CuerdaEntrada, 10, $PosY += 4, $pdf, 1);
	
		// Necesario Abrir
		$pdf->Text (40, $PosY += 5, $Literal[30]);
		$pdf->Rect (75, $PosY-2.5,2.5,2.5,($NecesarioAbrir == 1)?"F":"");
		$pdf->Text (80, $PosY, $Literal[31]);	// SI
		$pdf->Rect (85, $PosY-2.5,2.5,2.5,($NecesarioAbrir == 0)?"F":"");
		$pdf->Text (90, $PosY, $Literal[32]);	// NO
		$pdf->Text (100,$PosY, $Literal[33]);
	
		$pdf->SetFillColor($Red, $Green+25, $Blue);
		$PosY = fCreaCabGrupo($Literal[34], $PosY+3, $pdf, 'LR');
		fCreaLinGrupo($Literal[35], $RuedaDentada, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[37], $PoleaCuerda, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[36], $Dientes, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[38], $SuperficiePolea, 110, $PosY, $pdf, 1);
	
		$pdf->SetFillColor($Red, $Green+25, $Blue);
		$PosY = fCreaCabGrupo($Literal[39], $PosY+3, $pdf, 'LR');
		fCreaLinGrupo($Literal[40], $CajaFreno, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[44], $ZapatasFreno, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[41], $UnidadFreno, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[45], $ControlMuelle, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[42], $ProfundidadFreno, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[46], $FlancosArbol, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[43], $GuarnicionFreno, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[47], $PuntosApoyo, 110, $PosY, $pdf, 1);

		// Grupo 3
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[48], $PosY+3, $pdf, 'LR');
		fCreaLinGrupo($Literal[49], $EstadoCuerda, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[52], $AnyoFabCuerdaPri, 110, $PosY, $pdf, 2);
		fCreaLinGrupo($Literal[50], $FinDeCuerda, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[53], $EstMosquetonPri, 110, $PosY, $pdf, 1);
		fCreaLinGrupo($Literal[51], $Termoretractil, 10, $PosY += 4, $pdf, 1);
	
		// Grupo 4
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[54], $PosY+3, $pdf, 'L');
		fCreaLinGrupo($Literal[55], $EstCuerdaSeguridad, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[58], $AnyoFabCuerdaSeg, 110, $PosY, $pdf, 2);
		fCreaLinGrupo($Literal[56], $MosquetonSeguridad, 10, $PosY += 4, $pdf, 1);		
		$pdf->Text (10, $PosY += 4, $Literal[57]);
		$pdf->SetFillColor(185,185,45);
		$pdf->SetXY(80, $PosY-3);
		$pdf->Cell (60,3.5, $NSerieSeguridad,0,0,"L",true);
	
		// Grupo 5
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[59], $PosY+3, $pdf, 'L');
		fCreaLinGrupo($Literal[60], $DeslizamientoCuerda, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[61], $CargaMinima, 10, $PosY += 4, $pdf, 1);
	
		// Grupo 6
		$pdf->SetFillColor($Red, $Green, $Blue);
		$PosY = fCreaCabGrupo($Literal[62], $PosY+3, $pdf, 'L');
		fCreaLinGrupo($Literal[63], $VainaCuerda, 10, $PosY += 4, $pdf, 1);
		fCreaLinGrupo($Literal[64], $Mordazas, 10, $PosY += 4, $pdf, 1);
		
		$PosL = 65;
		$YesNo = $Literal[31].",".$Literal[32];
	}

	// Pie
	$pdf->SetFont('DejaVu','',9);	
	$pdf->SetXY(10, $PosY += 4);
	$pdf->SetFillColor($Red, $Green, $Blue);
	$pdf->Cell (70,4,$Literal[$PosL],0,0,'L', true);
	$pdf->Cell (30,4,$Literal[$PosL+1],0,0,'C', true);
	$pdf->Cell (20,4,$Literal[$PosL+2],0,0,'C', true);
	// Apto para su uso
	$pdf->SetX (140);
	$pdf->Cell (30,4,$Literal[$PosL+3],0,0,'L');
	$aux = $PosY + 3.5; $Texto = explode(",", $YesNo);
	$pdf->SetFillColor(0);
	$pdf->Rect (175,$aux-2.5,2.5,2.5,($Estado == 1)?"F":"");
	$pdf->Text (180,$aux, trim($Texto[0]));	// SI
	$pdf->Rect (185,$aux-2.5,2.5,2.5,($Estado == 0)?"F":"");
	$pdf->Text (190,$aux, trim($Texto[1]));	// NO

	$PosY += 1;
	$pdf->SetFillColor($Red, $Green+25, $Blue);
	for ($aux = 0; $aux < 4; $aux ++)
	{
		$pdf->SetXY(10, $PosY += 4);
		$pdf->Cell (70,3.5,isset($Material[$aux]) ? $Material[$aux] : "",0,0,'L', true);
		$pdf->Cell (30,3.5,isset($Motivo[$aux]) ? $Motivo[$aux] : "",0,0,'L', true);
		$Unidades = isset($Cantidad[$aux]) ? $Cantidad[$aux] : "";
		if ($Unidades < 1)
			$Unidades = "";
		$pdf->Cell (20,3.5,$Unidades,0,0,'C', true);

		$pdf->SetX (132);
		if ($aux == 0)
		{   // Firmas Operarios
			if (isset($FirChk[0]) || isset($FirChk[1]))
			{
				if (file_exists(($File = '../img/firmas/'.($Firma = isset($FirChk[0]) ? $FirChk[0] : ""))) && $Firma != "")
					$pdf->Cell(35,4, $pdf->Image($File, $pdf->GetX(), $pdf->GetY(), 45, 10));
				else
					$pdf->Cell(35,4,'',0,0,'L');

				if (file_exists(($File = '../img/firmas/'.($Firma = isset($FirChk[1]) ? $FirChk[1] : ""))) && $Firma != "")
					$pdf->Cell(35,4, $pdf->Image($File, $pdf->GetX(), $pdf->GetY(), 45, 10));
				else
					$pdf->Cell(35,4,'',0,0,'R');
			}
		}
		else
			$pdf->Cell (70, 4, ($aux == 3) ? $Literal[$PosL+4] : "",0,0,'L');
	}
	$pdf->Image('../img/sello_firma1.png',150,$PosY);
?>