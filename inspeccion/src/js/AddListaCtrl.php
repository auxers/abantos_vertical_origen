<?php
// Añadimos la Listas de Control al Certificado
$HayDatos = $EmpChk = $FirChk = false; 
$IdControl = $IdMarca = 0;
if ($TipoCer == "M" || $TipoCer == "R")
{
	$Query = "SELECT LC.*, L.NumeroTorre, L.TipoAerogeneradorGAMESA AS TipoAegGAMESA, LP.NumeroSerie, LP.NumeroCable, LP.NTrompa, LP.NAbsorbedor, LP.NTramo, 
		TA.Nombre AS TipoAerogenerador, PL.Gamesa AS TipoPletina, PL.Campo1,PL.Campo2,PL.Campo3,PL.Campo4, L.IdMarca FROM ListaControl LC JOIN Lineas L ON L.Id = LC.IdLinea
		JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea JOIN Pletinas PL ON PL.Id=LP.IdPletina JOIN TAerogenerador TA ON TA.Id=L.TipoAerogenerador
		WHERE L.IdParque=".$Parque." AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."' AND LC.Tipo='".$TipoCer."' AND PL.Tipo=LC.LTipo";
	if (!$Resultado)
		$Query .= " AND (LC.Resultado=0 OR LC.Resultado=1)";
	else if (is_numeric($Resultado))
		$Query .= " AND LC.Resultado=".(($Resultado == 1) ? "1" : "0");
	$Query .= " ORDER BY L.NumeroTorre, LC.LTipo ASC;";

	if (($Consulta = mysql_query($Query, $conn)))
	{
		while ($Row = mysql_fetch_array($Consulta))
		{
			if ($IdMarca != $Row['IdMarca'])
				$Literal = fGetLiterales(1, ($IdMarca = $Row['IdMarca']),$IdiomaChk,$conn);
			if (!$HayDatos)
				$HayDatos = true;
			$OT = $Row['OT'];
			$Fecha = fFechaDMY($Row['Fecha']);
			$IdTorre = fQuitaZeros($Row['NumeroTorre']);
			$Tipo = $Row['Tipo'];
			$NTramo = stripslashes($Row['NTramo']);
			$NTrompa = stripslashes($Row['NTrompa']);
			$NumeroSerie = stripslashes($Row['NumeroSerie']);
			$NumeroCable = stripslashes($Row['NumeroCable']);
			$NAbsorbedor = stripslashes($Row['NAbsorbedor']);
			$TipoAerogenerador = $Row['TipoAerogenerador'];
			$TipoAegGAMESA = stripslashes($Row['TipoAegGAMESA']);

			$EstadoCable = $Row['EstadoCable'];
			$EstadoCableMotivo = stripslashes($Row['EstadoCableMotivo']);
			$Tension = $Row['Tension'];
			$ConfPletina = $Row['ConfPletina'];
			$TipoPletina = $Row['TipoPletina'].
				($Row['LTipo'] == 1)?" Nacelle":" Servicio";
			$TipoDeLinea = " .".$Row['LTipo'];
			$CampoPletina1 = $Row['CampoPletina1'];
			$NombrePletina1 = stripslashes($Row['Campo1']);
			$CampoPletina2 = $Row['CampoPletina2'];
			$NombrePletina2 = stripslashes($Row['Campo2']);
			$CampoPletina3 = $Row['CampoPletina3'];
			$NombrePletina3 = stripslashes($Row['Campo3']);
			$CampoPletina4 = $Row['CampoPletina4'];
			$NombrePletina4 = stripslashes($Row['Campo4']);
			
			$VarillasRoscadas = $Row['VarillasRoscadas'];
			$Cartel = $Row['Cartel'];
			$AnclajeInf1 = $Row['AnclajeInferior1'];
			$AnclajeInf1AI = $Row['AnclajeInferior1AI'];
			$AnclajeInf1AIMotivo = stripslashes($Row['AnclajeInferior1AIMotivo']);
			$AnclajeInf1Tensor = $Row['AnclajeInferior1Tensor'];
			$AnclajeInf1Perrillos = $Row['AnclajeInferior1Perrillos'];
			$AnclajeInf1Guardacabos = $Row['AnclajeInferior1Guardacabos'];
			$AnclajeInf1Tuercas = $Row['AnclajeInferior1Tuercas'];
			$AnclajeSup1 = $Row['AnclajeSuperior1'];
			$AnclajeSup1AS = $Row['AnclajeSuperior1AS'];
			$AnclajeSup1ASMotivo = stripslashes($Row['AnclajeSuperior1ASMotivo']);
			$AnclajeSup2 = $Row['AnclajeSuperior2'];
			$AnclajeSup1Pasador = $Row['AnclajeSuperior1Pasador'];
			$AnclajeSup1Bulon = $Row['AnclajeSuperior1Bulon'];
			
			$Amortiguador = $Row['Amortiguador'];
			$AmortiguadorMuelle = $Row['AmortiguadorMuelle'];
			$AmortiguadorMuelleMotivo = stripslashes($Row['AmortiguadorMuelleMotivo']);
			$AmortiguadorPasador = $Row['AmortiguadorPasador'];
			$AmortiguadorBulon = $Row['AmortiguadorBulon'];

			$TornilleriaPletina = $Row['TornilleriaPletina'];
			$TornilleriaApriete = $Row['TornilleriaApriete'];
			$Ensayo = $Row['Ensayo'];
			$Escalera = $Row['Escalera'];
			$Oxidacion = $Row['Oxidacion'];
			$Interferencia = $Row['Interferencia'];
			$Resultado = $Row['Resultado'];
			$Observaciones = stripslashes($Row['Observaciones']);
			$TrabajosPendientes = stripslashes($Row['TrabajosPendientes']);
			
			// Buscamos los Operarios de ésta Inspeción	
			fBuscaOperarios("ListaControl", $Row['IdControl']);
			// Crea el PDF de la Lista de Control
			include("DetListaCtrl.php");
			
			unset ($Tipo, $IdTorre, $NTramo, $NTrompa, $NumeroSerie, $NumeroCable, $NAbsorbedor, $TipoAerogenerador, $EstadoCable, $Tension, $ConfPletina, $TipoPletina,
				 $TipoDeLinea, $CampoPletina1, $CampoPletina2, $CampoPletina3, $CampoPletina4, $NombrePletina1, $NombrePletina2, $NombrePletina3, $NombrePletina4,
				 $VarillasRoscadas, $Cartel, $AnclajeInf1, $AnclajeInf1AI, $AnclajeInf1AIMotivo, $AnclajeInf1Tensor, $AnclajeInf1Perrillos, $AnclajeInf1Guardacabos, $AnclajeInf1Tuercas, $AnclajeSup2,
				 $AnclajeSup1, $AnclajeSup1AS, $AnclajeSup1ASMotivo, $AnclajeSup1Pasador, $AnclajeSup1Bulon, $AnclajeSup1AS, $TornilleriaPletina, $TornilleriaApriete, $Ensayo, $Escalera, $Oxidacion,
				 $Amortiguador, $AmortiguadorMuelle, $AmortiguadorMuelleMotivo, $AmortiguadorPasador, $AmortiguadorBulon, $Interferencia, $Resultado, $Observaciones, $TrabajosPendientes);
		}
		
		unset ($Consulta, $Row, $Literal);
	}
}
else if ($TipoCer == "D")
{   // Lista Control Descensores
	$Query = "SELECT LD.Id AS IdLD, LD.OT, LD.Fecha, L.NumeroTorre, Al.Nombre AS NomAltura, LD.NSerie, LD.Fabricante,A.Nombre AS DesMarca,B.Nombre AS DesModel, 
		LD.Longitud, LD.NPrecintoOld, LD.NPrecintoNew, LD.AnyoFabricacion, LD.Ubicacion, LD.TEnvasado, LD.Estado, LD.IdControl, PSA.*, MRG.*
		FROM ListaCtrlDes LD LEFT JOIN DetLstCtrlPSA PSA ON PSA.IdLista = LD.Id LEFT JOIN DetLstCtrlMRG MRG ON MRG.IdLista = LD.Id 
		JOIN Lineas L ON L.Id = LD.IdLinea JOIN MarcaDes A ON A.Id = LD.Fabricante JOIN ModeloDes B ON B.Id = LD.ModeloDes
		JOIN Alturas Al ON Al.Id=L.IdAltura WHERE L.IdParque=".$Parque." AND LD.Fecha >= '".$FechaIni."' AND LD.Fecha <= '".$FechaFin."'";
	if (!$Resultado)
		$Query .= " AND (LD.Estado=0 OR LD.Estado=1)";
	else if (is_numeric($Resultado))
		$Query .= " AND LD.Estado=".(($Resultado == 1) ? "1" : "0");
	$Query .= " ORDER BY L.NumeroTorre ASC";

	if (($Consulta = mysql_query($Query, $conn)))
	{
		while ($Row = mysql_fetch_array($Consulta))
		{
			if ($IdMarca != $Row['Fabricante'])
				$Literal = fGetLiterales(2, ($IdMarca = $Row['Fabricante']),$IdiomaChk,$conn);
			if (!$HayDatos)
				$HayDatos = true;
			$OT = $Row['OT'];
			$Fecha = fFechaDMY($Row['Fecha']);
			$IdTorre = fQuitaZeros($Row['NumeroTorre']);
			$Altura = stripslashes($Row['NomAltura']);
			
			$NSerie = stripslashes($Row['NSerie']);
			$Fabricante = stripslashes($Row['DesMarca']);
			$ModeloDes = stripslashes($Row['DesModel']);
			$Longitud = stripslashes($Row['Longitud']);
			$PrecintoViejo = stripslashes($Row['NPrecintoOld']);
			$PrecintoNuevo = stripslashes($Row['NPrecintoNew']);
			$AnyoFabricacion = stripslashes($Row['AnyoFabricacion']);
			$Ubicacion = $Row['Ubicacion'];
			$Envasado = stripslashes($Row['TEnvasado']);
			$Estado = $Row['Estado'];

			if (($MarcaDes = $Row['Fabricante']) == 2)
			{
				$MaletinEqu = $Row['MaletinEqu'];
				$SisEnvaseEqu = $Row['SisEnvaseEqu'];
				$SacaAzulTran = $Row['SacaAzulTran'];
				$BolsaPlastico = $Row['BolsaPlastico'];
				$DescensorMRG = $Row['DescensorMRG'];
				$CuerdaDesMRG = $Row['CuerdaDesMRG'];
				$CuerdasSeguridad = $Row['CuerdasSeguridad'];
				$PegatinaPrecinto = $Row['PegatinaPrecinto'];
				$BridaPrecinto = $Row['BridaPrecinto'];
				$EtiquetaExterior = $Row['EtiquetaExterior'];
				$LibroInspecciones = $Row['LibroInspecciones'];
				$MaletinEmb = $Row['MaletinEmb'];
				$SisEnvaseEmb = $Row['SisEnvaseEmb'];
				$BolsaPlasEmb = $Row['BolsaPlasEmb'];
				$SacaAzulEmb = $Row['SacaAzulEmb'];
				$PegatinaPreEmb = $Row['PegatinaPreEmb'];
				$BridaPreEmb = $Row['BridaPreEmb'];
				$PreTornillTFreno = $Row['PreTornillTFreno'];
				$GrosorPasTFreno = $Row['GrosorPasTFreno'];
				$EstMuelleTFreno = $Row['EstMuelleTFreno'];
				$EjePinonTFreno = $Row['EjePinonTFreno'];
				$LimPinonTFreno = $Row['LimPinonTFreno'];
				$ZonaSurcosTFreno = $Row['ZonaSurcosTFreno'];
				$ZonaLimpiaTFreno = $Row['ZonaLimpiaTFreno'];
				$EstTornillTFreno = $Row['EstTornillTFreno'];
				$TorLoctiteTFreno = $Row['TorLoctiteTFreno'];
				$MarcasTornTFreno = $Row['MarcasTornTFreno'];
				$PreTornillTPolea = $Row['PreTornillTPolea'];
				$HolguraEjeTPolea = $Row['HolguraEjeTPolea'];
				$EstNerviosTPolea = $Row['EstNerviosTPolea'];
				$EstCarcasaTPolea = $Row['EstCarcasaTPolea'];
				$EstTornillTPolea = $Row['EstTornillTPolea'];
				$TorLoctiteTPolea = $Row['TorLoctiteTPolea'];
				$MarcasTornTPolea = $Row['MarcasTornTPolea'];
				$PreTornillCFreno = $Row['PreTornillCFreno'];
				$EstJuntaCFreno = $Row['EstJuntaCFreno'];
				$EstDientesCFreno = $Row['EstDientesCFreno'];
				$RuedaLimpiaCFreno = $Row['RuedaLimpiaCFreno'];
				$EstTornillCFreno = $Row['EstTornillCFreno'];
				$TorLoctiteCFreno = $Row['TorLoctiteCFreno'];
				$MarcasTornCFreno = $Row['MarcasTornCFreno'];
				$DeslizamientoCuerda = $Row['GlissadeCuerda'];				
				$EstGenCuerdaPri = $Row['EstGenCuerdaPri'];
				$EstProCuerdaPri = $Row['EstProCuerdaPri'];
				$LongitudCuerdaPri = $Row['LongitudCuerdaPri'];
				$LongMedidaCuerdaPri = $Row['LongMedidaCuerdaPri'];
				$MosquetonCuerdaPri = $Row['MosquetonCuerdaPri'];
				$AnyoFabCuerdaPri = stripslashes($Row['AnyoCuerdaPri']);
				$EstGenCuerdaSeg = $Row['EstGenCuerdaSeg'];
				$SupSacaCuerdaSeg = $Row['SupSacaCuerdaSeg'];
				$EstMosqueton = $Row['EstMosqueton'];
				$FucMosqueton = $Row['FucMosqueton'];
				$NSerieCuerdaSeg1 = stripslashes($Row['NSerieCuerdaSeg1']);
				$AnyoFabCuerdaSeg1 = stripslashes($Row['AnyoFabCuerdaSeg1']);
				$NSerieCuerdaSeg2 = stripslashes($Row['NSerieCuerdaSeg2']);
				$AnyoFabCuerdaSeg2 = stripslashes($Row['AnyoFabCuerdaSeg2']);
			}
			else	// Por Defecto, PSA
			{			
				$Bolsa = $Row['Bolsa'];
				$Sellado = $Row['Sellado'];
				$NumeroSello = $Row['NumSello'];
				$DescensorAG = $Row['DescensorAG'];
				$CaboAnclaje = $Row['CaboAnclaje'];
				$Humedad = $Row['Humedad'];
				$EtiquetaLegible = $Row['EtiquetaLegible'];
				$EstadoCarcasa = $Row['EstadoCarcasa'];
				$CuerdaEntrada = $Row['CuerdaEntrada'];
				$CuerdaSalida  = $Row['CuerdaSalida'];
				$MosquetonArgolla = $Row['MosquetonArgolla'];				
				$NecesarioAbrir = $Row['NecesarioAbrir'];
				$RuedaDentada = $Row['RuedaDentada'];
				$Dientes = $Row['Dientes'];
				$PoleaCuerda = $Row['PoleaCuerda'];
				$SuperficiePolea = $Row['SuperficiePolea'];
				$CajaFreno = $Row['CajaFreno'];
				$UnidadFreno = $Row['UnidadFreno'];
				$ProfundidadFreno = $Row['ProfundidadFreno'];
				$GuarnicionFreno = $Row['GuarnicionFreno'];
				$ZapatasFreno = $Row['ZapatasFreno'];
				$ControlMuelle = $Row['ControlMuelle'];
				$FlancosArbol = $Row['FlancosArbol'];
				$PuntosApoyo = $Row['PuntosApoyo'];
				$EstadoCuerda = $Row['EstadoCuerda'];
				$FinDeCuerda = $Row['FinDeCuerda'];
				$Termoretractil = $Row['Termoretractil'];
				$AnyoFabCuerdaPri = stripslashes($Row['AnyoFabCuerdaPri']);
				$EstMosquetonPri = $Row['EstMosquetonPri'];
				$EstCuerdaSeguridad = $Row['EstCuerdaSeguridad'];
				$MosquetonSeguridad = $Row['MosquetonSeguridad'];
				$NSerieSeguridad = stripslashes($Row['NSerieSeguridad']);
				$AnyoFabCuerdaSeg = stripslashes($Row['AnyoFabCuerdaSeg']);
				$DeslizamientoCuerda = $Row['DeslizamientoCuerda'];
				$CargaMinima = $Row['CargaMinima'];
				$VainaCuerda = $Row['VainaCuerda'];
				$Mordazas = $Row['Mordazas'];
			}
														
			$Material = $Motivo = $Cantidad = false;
			if (($Result1 = mysql_query("SELECT * FROM MaterialCtrlDes WHERE IdLista=".$Row['IdLD'], $conn)))
			{
				while ($Row1 = mysql_fetch_array($Result1)) {
					$Material[] = stripslashes($Row1['Material']);
					$Motivo[]   = stripslashes($Row1['Motivo']);
					$Cantidad[] = $Row1['Cantidad'];
				}
				unset ($Row1, $Result1);
			}

			// Buscamos los Operarios de ésta Inspeción	
			fBuscaOperarios("ListaCtrlDes", $Row['IdControl']);
			// Crea el PDF Lista de Control Descensores
			include("DetCtrlDes.php");

			unset ($OT, $Fecha, $IdTorre, $Altura, $NSerie, $Fabricante, $ModeloDes, $Longitud, $PrecintoViejo, $PrecintoNuevo, $AnyoFabricacion, $Ubicacion,
				$Envasado, $Bolsa, $Sellado, $NumeroSello, $DescensorAG, $CaboAnclaje, $Humedad, $EtiquetaLegible, $EstadoCarcasa, $CuerdaEntrada, $CuerdaSalida, 
				$MosquetonArgolla, $NecesarioAbrir, $RuedaDentada, $Dientes, $PoleaCuerda, $SuperficiePolea, $CajaFreno, $UnidadFreno, $ProfundidadFreno, $GuarnicionFreno,
				$ZapatasFreno, $ControlMuelle, $FlancosArbol, $PuntosApoyo, $EstadoCuerda, $FinDeCuerda, $Termoretractil, $AnyoFabCuerdaPri, $EstMosquetonPri,
				$EstCuerdaSeguridad, $MosquetonSeguridad, $NSerieSeguridad, $AnyoFabCuerdaSeg, $DeslizamientoCuerda, $CargaMinima, $VainaCuerda, $Mordazas, $Estado,
				$Material, $Motivo, $Cantidad);
			unset ($MaletinEqu, $SisEnvaseEqu, $SacaAzulTran, $BolsaPlastico, $DescensorMRG, $CuerdaDesMRG,
				$CuerdasSeguridad, $PegatinaPrecinto, $BridaPrecinto, $EtiquetaExterior, $LibroInspecciones,
				$MaletinEmb, $SisEnvaseEmb, $BolsaPlasEmb, $SacaAzulEmb, $PegatinaPreEmb, $BridaPreEmb, $PreTornillTFreno,
				$GrosorPasTFreno, $EstMuelleTFreno, $EjePinonTFreno, $LimPinonTFreno, $ZonaSurcosTFreno, $ZonaLimpiaTFreno,
				$EstTornillTFreno, $TorLoctiteTFreno, $MarcasTornTFreno, $PreTornillTPolea, $HolguraEjeTPolea, $EstNerviosTPolea,
				$EstCarcasaTPolea, $EstTornillTPolea, $TorLoctiteTPolea, $MarcasTornTPolea, $PreTornillCFreno, $EstJuntaCFreno,
				$EstDientesCFreno, $RuedaLimpiaCFreno,$EstTornillCFreno, $TorLoctiteCFreno, $MarcasTornCFreno,
				$EstGenCuerdaPri, $EstProCuerdaPri, $LongitudCuerdaPri, $LongMedidaCuerdaPri, $MosquetonCuerdaPri,
				$EstGenCuerdaSeg, $SupSacaCuerdaSeg, $EstMosqueton, $FucMosqueton,
				$NSerieCuerdaSeg1, $AnyoFabCuerdaSeg1, $NSerieCuerdaSeg2, $AnyoFabCuerdaSeg2);
		}
		unset ($Consulta, $Row, $Literal);
	}
}
else if ($TipoCer == "E")
{   // Lista Control Extintores
	$Query = "SELECT LE.*, L.NumeroTorre, A.Nombre AS ExtLocal, B.Nombre AS ExtColocacion, C.Nombre AS ExtMarca, D.Nombre AS ExtModelo FROM ListaCtrlExt LE JOIN Lineas L ON L.Id = LE.IdLinea 
		JOIN Localizacion A ON A.Id=LE.Localizacion JOIN Colocacion B ON B.Id=LE.Colocacion JOIN MarcaExt C ON C.Id=LE.Marca JOIN ModeloExt D ON D.Id=LE.Modelo
		WHERE L.IdParque=".$Parque." AND LE.Fecha >= '".$FechaIni."' AND LE.Fecha <= '".$FechaFin."'";
	if (!$Resultado)
		$Query .= " AND (LE.Estado=0 OR LE.Estado=1)";
	else if (is_numeric($Resultado))
		$Query .= " AND LE.Estado=".(($Resultado == 1) ? "1" : "0");
	$Query .= " ORDER BY L.NumeroTorre ASC";

	if (($Consulta = mysql_query($Query, $conn)))
	{   
		$nExt = 1;
		$Literal = fGetLiterales(3, 1,$IdiomaChk,$conn);
		while ($Row = mysql_fetch_array($Consulta))
		{
			if (!$HayDatos)
				$HayDatos = true;
			$OT = $Row['OT'];
			$Fecha = fFechaDMY($Row['Fecha']);
			$IdTorre = fQuitaZeros($Row['NumeroTorre']);

			$Localizacion = $Row['ExtLocal'];
			$Colocacion = ($Row['ExtColocacion']);
			$NPlaca = stripslashes($Row['NPlaca']);
			$Marca = $Row['ExtMarca'];
			$Modelo = $Row['ExtModelo'];
			$FechaFabricacion = stripslashes($Row['FechaFabricacion']);
			$FechaRetimbrado = stripslashes($Row['FechaRetimbrado']);
			$AgenteExtintor = stripslashes($Row['AgenteExtintor']);
			$PesoAgExtintor = stripslashes($Row['PesoAgExtintor']);
			$Movido = stripslashes($Row['Movido']);
			$Sustituido = stripslashes($Row['Sustituido']);
			$PlacaSustitucion = stripslashes($Row['PlacaSustitucion']);
			$PrecintoSustitucion = $Row['PrecintoSustitucion'];
			$CartelLu = $Row['CartelLu'];
			$PegatinaCarac = $Row['PegatinaCaracUso'];
			$PegatinaRevi = $Row['PegatinaRevision'];
			$MarcadoCE = $Row['MarcadoCE'];
			$PrecintoRetimbrado = $Row['PrecintoRetimbrado'];
			$EstadoCuerpo = $Row['EstadoCuerpo'];
			$EstadoCabeza = $Row['EstadoCabeza'];
			$Pasador = $Row['Pasador'];
			$Valvula = $Row['Valvula'];
			$Manguera = $Row['Manguera'];
			$Soporte = $Row['Soporte'];
			$Junta = $Row['Junta'];
			$Materiales = $Row['Materiales'];
			$Estado = $Row['Estado'];
			$FaltaPeso = $Row['FaltaPeso'];
			$Caducidad = $Row['Caducidad'];
			$Otra = stripslashes($Row['Otra']);
			$Observaciones = stripslashes($Row['Observaciones']);
					
			// Buscamos los Operarios de ésta Inspeción	
			fBuscaOperarios("ListaCtrlExt", $Row['IdControl']);
			// Crea el PDF Lista de Control Extintores
			include("DetCtrlExt.php");
			$nExt ++;
			
			unset($OT, $Fecha, $IdTorre, $Localizacion, $NPlaca, $Marca, $Modelo, $FechaFabricacion, $FechaRetimbrado, $AgenteExtintor, $PesoAgExtintor,
				$Colocacion, $Movido, $Sustituido, $PlacaSustitucion, $PrecintoSustitucion, $CartelLu, $PegatinaCarac, $PegatinaRevi,
				$MarcadoCE, $PrecintoRetimbrado, $EstadoCuerpo, $EstadoCabeza, $Pasador, $Valvula, $Manguera,
				$Soporte, $Junta, $Materiales, $Estado, $FaltaPeso, $Caducidad, $Otra, $Observaciones);
		}
		unset ($Consulta, $Row, $Literal);
	}
}
unset ($IdControl,$EmpChk,$FirChk);

function fBuscaOperarios($Tabla, $Control)
{
	global $IdControl,$EmpChk,$FirChk, $Trabajadores,$Firmas,$conn;
	if ($IdControl != $Control)
	{
		$IdControl = $Control;
		$EmpChk = $FirChk = false;
		$Query = "SELECT DISTINCT T.Nombre, T.Firma FROM ".$Tabla." LC JOIN Trabajadores T ON T.Id = LC.IdTrabajador 
			WHERE LC.IdControl='".$Control."'";
		if (($ConTmp = mysql_query($Query, $conn)))
		{
			while($RowTmp = mysql_fetch_row($ConTmp))
			{
				$EmpChk[] = $RowTmp[0];
				$FirChk[] = $RowTmp[1];
			}
			unset($ConTmp, $RowRmp);
		}
	
		for ($Pos=0; $Pos<2; $Pos ++)
		{
			if (!isset($EmpChk[$Pos]) && isset($Trabajadores[$Pos])) {
				$EmpChk[$Pos] = $Trabajadores[$Pos];
				$FirChk[$Pos] = $Firmas[$Pos];
			}
		}
	}
}
?>