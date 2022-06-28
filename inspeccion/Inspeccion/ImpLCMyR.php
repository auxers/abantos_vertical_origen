<?php
// Creamos el PDF de Lista de Control a partir del XML
require_once("../lib/fpdf/tfpdf.php");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$LinCod = isset($_REQUEST["Linea"]) ? $_REQUEST["Linea"]: "";
$LinPle = isset($_REQUEST["Pletina"]) ? $_REQUEST["Pletina"]: 0;
$FileXml = isset($_REQUEST["FileXml"]) ? $_REQUEST["FileXml"]: "";

if (file_exists(($File = "../data/".$FileXml)))
{
	$doc = new DOMDocument();
	$doc->load($File);
	
	$Tipo = substr($FileXml,0,1);
	$NombreParque = $ClienteParque = "";
	if (is_object($aux = $doc->getElementsByTagName("Nombre")->item(0)))
		$NombreParque = $aux->nodeValue;
	if (is_object($aux = $doc->getElementsByTagName("Cliente")->item(0)))
		$ClienteParque = $aux->nodeValue;

	$IdMarca = $GrupoChk = $IdiomaChk = 1;	// Idioma Español
	if (($result = mysql_query("SELECT PA.IdiomaChk FROM Parques P JOIN Paises PA ON PA.Id=P.Pais WHERE P.Id=".substr($FileXml,1,10), $conn)))
	{
		if (($row = mysql_fetch_array($result)))
			$IdiomaChk = $row['IdiomaChk'];
		unset($result, $row);
	}

	// Nom. Trabajadores
	$EmpChk = $FirChk = false;
	if (is_object($aux = $doc->getElementsByTagName("Operario1")->item(0)))
	{   if (($result = mysql_query("SELECT Nombre, Firma FROM Trabajadores WHERE Id=".$aux->nodeValue,$conn)))
		{
			if ($row = mysql_fetch_array($result)) {
				$EmpChk[] = $row["Nombre"];
				$FirChk[] = $row["Firma"];
			}
		}
	}

	if (is_object($aux = $doc->getElementsByTagName("Operario2")->item(0)))
	{   if (is_numeric($aux->nodeValue))
		{   if (($result = mysql_query("SELECT Nombre, Firma FROM Trabajadores WHERE Id=".$aux->nodeValue,$conn)))
			{
				if ($row = mysql_fetch_array($result)) {
					$EmpChk[] = $row["Nombre"];
					$FirChk[] = $row["Firma"];
				}
			}
		}
	}

	// Creamos el PDF
	$pdf = new tFPDF('P','mm','A4');
	$pdf->SetTitle ('Lista de Control Montaje/Revisión');
	$pdf->SetAuthor('Abantos Vertical');
	$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
	$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf',true);

	$HayDatos = false;
	foreach($doc->getElementsByTagName("Torre") as $torre)
	{   
		$Id = 0; $IdTorre=$TipoAerogenerador=$TipoAegGAMESA=$Altura = "";
		if (is_object($aux = $torre->getElementsByTagName("Id")->item(0)))
			$Id = $aux->nodeValue;
		if (is_object($aux = $torre->getElementsByTagName("NumeroTorre")->item(0)))
			$IdTorre = $aux->nodeValue;
		if (is_object($aux = $torre->getElementsByTagName("Altura")->item(0)))
			$Altura = $aux->nodeValue;
		if (is_object($aux = $torre->getElementsByTagName("TipoAerogenerador")->item(0)))
			$TipoAerogenerador = $aux->nodeValue;
		if (is_object($aux = $torre->getElementsByTagName("TipoAegGAMESA")->item(0)))
			$TipoAegGAMESA = $aux->nodeValue;

		if ($Id == $LinCod || empty($LinCod))
		{   // Leemos todas las Líneas del XML
			$Literal = fGetLiterales(1, $GrupoChk,$IdiomaChk,$conn);
			foreach($torre->getElementsByTagName("Linea") as $linea)
			{
				$IdPletina = 0; $OT = $Fecha = "";
				if (is_object($aux = $linea->getElementsByTagName("OT")->item(0)))
					$OT = $aux->nodeValue;
				if (is_object($aux = $linea->getElementsByTagName("Fecha")->item(0)))
					$Fecha = $aux->nodeValue;
				if (is_object($aux = $linea->getElementsByTagName("IdPletina")->item(0)))
					$IdPletina = $aux->nodeValue;
				if (is_object($aux = $linea->getElementsByTagName("IdMarca")->item(0)))
					$IdMarca = $aux->nodeValue;
				if ($IdMarca != $GrupoChk)
					$Literal = fGetLiterales(1, ($GrupoChk=$IdMarca),$IdiomaChk,$conn);

				// Compruebo que la Línea de Vida es la que hemos seleccionado y esté Revisada
				if (($IdPletina == $LinPle || empty($LinCod)) && $Fecha != "")
				{   
					$NTrompa = $NAbsorbedor = $NumeroCable = $NumeroSerie = $TipoPletina = $TipoDeLinea = $NTramo = "";
					$Serie = $Cable = $NombrePletina1 = $NombrePletina2 = $NombrePletina3 = $NombrePletina4 = "";
					
					if (!$HayDatos)
						$HayDatos = true;
					if (is_object($aux = $linea->getElementsByTagName("Cable")->item(0)))
						$NumeroCable = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Serie")->item(0)))
						$NumeroSerie = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Absorbedor")->item(0)))
						$NAbsorbedor = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Trompa")->item(0)))
						$NTrompa = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Tramo")->item(0)))
						$NTramo = $aux->nodeValue;
				
					if (is_object($aux = $linea->getElementsByTagName("TipoPletina")->item(0)))
						$TipoPletina = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("TipoDeLinea")->item(0)))
						$TipoDeLinea = $aux->nodeValue;
					$TipoPletina .= ($TipoDeLinea == 1) ? " Nacelle" : " Servicio";
					$TipoDeLinea  = ".".$TipoDeLinea;
					if (is_object($aux = $linea->getElementsByTagName("NombrePletina1")->item(0)))
						$NombrePletina1 = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("NombrePletina2")->item(0)))
						$NombrePletina2 = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("NombrePletina3")->item(0)))
						$NombrePletina3 = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("NombrePletina4")->item(0)))
						$NombrePletina4 = $aux->nodeValue;
				
					// Revisión
					$Resultado = $EstadoCable = $Tension = $ConfPletina = $CampoPletina1 = 0; $EstadoCableMotivo = "";
					$CampoPletina2 = $CampoPletina3 = $CampoPletina4 = $VarillasRoscadas = $Cartel = 0;
					$AnclajeInf1 = $AnclajeInf1AI = $AnclajeInf1Tensor = $AnclajeInf1Perrillos = 0;
					$AnclajeInf1Guardacabos = $AnclajeInf1Tuercas = $AnclajeSup1 = $AnclajeSup1AS = 0;
					$AnclajeSup2 = $AnclajeSup1Pasador = $AnclajeSup1Bulon = 0;
					$Amortiguador = $AmortiguadorMuelle = 0; $AmortiguadorMuelleMotivo = ""; $AmortiguadorPasador = 0;
					$AmortiguadorBulon = $TornilleriaPletina = $TornilleriaApriete = $Ensayo = $Escalera = 0;
					$Interferencia = $Oxidacion = 0; $Observaciones = $TrabajosPendientes = "";
					$AnclajeInf1AIMotivo = $AnclajeSup1ASMotivo = "";

					if (is_object($aux = $linea->getElementsByTagName("Resultado")->item(0)))
						$Resultado = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("EstadoCable")->item(0)))
						$EstadoCable = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("EstadoCableMotivo")->item(0)))
						$EstadoCableMotivo = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Tension")->item(0)))
						$Tension = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("ConfPletina")->item(0)))
						$ConfPletina = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $linea->getElementsByTagName("CampoPletina1")->item(0)))
						$CampoPletina1 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $linea->getElementsByTagName("CampoPletina2")->item(0)))
						$CampoPletina2 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $linea->getElementsByTagName("CampoPletina3")->item(0)))
						$CampoPletina3 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $linea->getElementsByTagName("CampoPletina4")->item(0)))
						$CampoPletina4 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $linea->getElementsByTagName("VarillasRoscadas")->item(0)))
						$VarillasRoscadas = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $linea->getElementsByTagName("Cartel")->item(0)))
						$Cartel = $aux->nodeValue;
						
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1")->item(0)))
						$AnclajeInf1 = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1AI")->item(0)))
						$AnclajeInf1AI = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1AIMotivo")->item(0)))
						$AnclajeInf1AIMotivo = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Tensor")->item(0)))
						$AnclajeInf1Tensor = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Perrillos")->item(0)))
						$AnclajeInf1Perrillos = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Guardacabos")->item(0)))
						$AnclajeInf1Guardacabos = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Tuercas")->item(0)))
						$AnclajeInf1Tuercas = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1")->item(0)))
						$AnclajeSup1 = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1AS")->item(0)))
						$AnclajeSup1AS = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1ASMotivo")->item(0)))
						$AnclajeSup1ASMotivo = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior2")->item(0)))
						$AnclajeSup2 = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1Pasador")->item(0)))
						$AnclajeSup1Pasador = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1Bulon")->item(0)))
						$AnclajeSup1Bulon = $aux->nodeValue;
						
					if (is_object($aux = $linea->getElementsByTagName("Amortiguador")->item(0)))
						$Amortiguador = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AmortiguadorMuelle")->item(0)))
						$AmortiguadorMuelle = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AmortiguadorMuelleMotivo")->item(0)))
						$AmortiguadorMuelleMotivo = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AmortiguadorPasador")->item(0)))
						$AmortiguadorPasador = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("AmortiguadorBulon")->item(0)))
						$AmortiguadorBulon = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("TornilleriaPletina")->item(0)))
						$TornilleriaPletina = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("TornilleriaApriete")->item(0)))
						$TornilleriaApriete = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Ensayo")->item(0)))
						$Ensayo = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Escalera")->item(0)))
						$Escalera = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Interferencia")->item(0)))
						$Interferencia = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Oxidacion")->item(0)))
						$Oxidacion = $aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("Observaciones")->item(0)))
						$Observaciones = "  ".$aux->nodeValue;
					if (is_object($aux = $linea->getElementsByTagName("TrabajosPendientes")->item(0)))
						$TrabajosPendientes = $aux->nodeValue;
				
					// Crea el PDF de la Lista de Control
					include("../inc/inspeccion/DetListaCtrl.php");

					unset($NTramo, $NTrompa, $NumeroSerie, $NumeroCable, $NAbsorbedor, $TipoAerogenerador, $EstadoCable, $Tension, $ConfPletina, $TipoPletina, 
						 $TipoDeLinea, $CampoPletina1, $CampoPletina2, $CampoPletina3, $CampoPletina4, $NombrePletina1, $NombrePletina2, $NombrePletina3, $NombrePletina4,
						 $VarillasRoscadas, $Cartel, $AnclajeInf1, $AnclajeInf1AI, $AnclajeInf1AIMotivo, $AnclajeInf1Tensor, $AnclajeInf1Perrillos, $AnclajeInf1Guardacabos, $AnclajeInf1Tuercas,
						 $AnclajeSup1, $AnclajeSup1AS, $AnclajeSup1ASMotivo, $AnclajeSup2, $AnclajeSup1Pasador, $AnclajeSup1Bulon, $TornilleriaPletina, $TornilleriaApriete, $Ensayo, $Escalera, $Oxidacion, 
						 $Amortiguador, $AmortiguadorMuelle, $AmortiguadorMuelleMotivo, $AmortiguadorPasador, $AmortiguadorBulon, $Interferencia, $Resultado, $Observaciones, $TrabajosPendientes);
				} // Fin Revisada
			}  // Fin Grupo Líneas Vida
			unset ($Literal);
			
			// ODG, 20.02.13 Revisión de Extintores
			$nExt = 1; $Literal = fGetLiterales (3, 1,$IdiomaChk,$conn);
			foreach ($torre->getElementsByTagName("Extintor") as $extintor)
			{
				$Fecha = $OT = "";
				if (is_object($aux = $extintor->getElementsByTagName("OT")->item(0)))
					$OT = $aux->nodeValue;
				if (is_object($aux = $extintor->getElementsByTagName("Fecha")->item(0)))
					$Fecha = $aux->nodeValue;

				// Compruebo que esté Revisada
				if ($Fecha != "")
				{
					$Localizacion = ""; $AgenteExtintor = $Colocacion = $Sustituido = $PrecintoSustitucion = $CartelLu = 0;
					$PegatinaCarac = $PegatinaRevi = $MarcadoCE = $PrecintoRetimbrado = $EstadoCuerpo = 0;
					$EstadoCabeza = $Pasador = $Valvula = $Manguera = $Soporte = $Junta = $Estado = $FaltaPeso = $Caducidad = 0;
					$NPlaca = $Marca = $Modelo = $FechaFabricacion = $FechaRetimbrado = $PesoAgExtintor = $Motivo = "";
					$PlacaSustitucion = $Materiales = $Otra = $Observaciones = "";

					if (!$HayDatos)
						$HayDatos = true;
					if (is_object($aux = $extintor->getElementsByTagName("Localizacion")->item(0)))
						$Localizacion = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Placa")->item(0)))
						$NPlaca = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Marca")->item(0)))
						$Marca = (substr($aux->nodeValue,0,10) != "Seleccione") ? $aux->nodeValue : "";
					if (is_object($aux = $extintor->getElementsByTagName("Modelo")->item(0)))
						$Modelo = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("FechaFabricacion")->item(0)))
						$FechaFabricacion = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("FechaRetimbrado")->item(0)))
						$FechaRetimbrado = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("AgenteExtintor")->item(0)))
						$AgenteExtintor = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("PesoAgExtintor")->item(0)))
						$PesoAgExtintor = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Colocacion")->item(0)))
						$Colocacion = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Movido")->item(0)))
						$Movido = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Sustituido")->item(0)))
						$Sustituido = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PlacaSustitucion")->item(0)))
						$PlacaSustitucion = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("PrecintoSustitucion")->item(0)))
						$PrecintoSustitucion = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("CartelLu")->item(0)))
						$CartelLu = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PegatinaCarac")->item(0)))
						$PegatinaCarac = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PegatinaRevi")->item(0)))
						$PegatinaRevi = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("MarcadoCE")->item(0)))
						$MarcadoCE = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PrecintoRetimbrado")->item(0)))
						$PrecintoRetimbrado = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoCuerpo")->item(0)))
						$EstadoCuerpo = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoCabeza")->item(0)))
						$EstadoCabeza = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Pasador")->item(0)))
						$Pasador = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Valvula")->item(0)))
						$Valvula = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Manguera")->item(0)))
						$Manguera = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Soporte")->item(0)))
						$Soporte = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Junta")->item(0)))
						$Junta = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;						
					if (is_object($aux = $extintor->getElementsByTagName("Materiales")->item(0)))
						$Materiales = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoExt")->item(0)))
						$Estado = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("FaltaPeso")->item(0)))
						$FaltaPeso = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Caducidad")->item(0)))
						$Caducidad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Otra")->item(0)))
						$Otra = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("ObservacionesExt")->item(0)))
						$Observaciones = $aux->nodeValue;
		
					// Crea el PDF de la Lista Control Extintor
					include("../inc/inspeccion/DetCtrlExt.php");
					$nExt ++;

					unset($Localizacion, $NPlaca, $Marca, $Modelo, $FechaFabricacion, $FechaRetimbrado, $AgenteExtintor, $PesoAgExtintor, $Colocacion, 
						$Movido, $Sustituido, $PlacaSustitucion, $PrecintoSustitucion, $CartelLu, $PegatinaCarac, $PegatinaRevi, $MarcadoCE,
						$PrecintoRetimbrado, $EstadoCuerpo, $EstadoCabeza, $Pasador, $Valvula, $Manguera, $Soporte,
						$Junta, $Materiales, $Estado, $FaltaPeso, $Caducidad, $Otra, $Observaciones);
				}

				unset ($Fecha, $OT);
			}
			unset($Literal);

			// Descensores/Evacuadores
			$Literal = fGetLiterales(2, ($MarcaDes=$GrupoChk=1),$IdiomaChk,$conn);
			foreach ($torre->getElementsByTagName("Descensor") as $descensor)
			{
				$Fecha = $OT = "";
				if (is_object($aux = $descensor->getElementsByTagName("OT")->item(0)))
					$OT = $aux->nodeValue;
				if (is_object($aux = $descensor->getElementsByTagName("Fecha")->item(0)))
					$Fecha = $aux->nodeValue;
				if (is_object($aux = $descensor->getElementsByTagName("Fabricante")->item(0)))
					$MarcaDes = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaDato("Id","MarcaDes", "Nombre='".$aux->nodeValue."'", $conn);
				if ($MarcaDes != $GrupoChk)
					$Literal = fGetLiterales(2, ($GrupoChk=$MarcaDes),$IdiomaChk,$conn);
										
				// Compruebo que esté Revisada
				if ($Fecha != "")
				{
					$NSerie = $Fabricante = $ModeloDes = $Longitdud = $PrecintoViejo = $PrecintoNuevo = "";
					$AnyoFabricacion = $Envasado = $AnyoFabCuerdaPri = $NSerieSeguridad = $AnyoFabCuerdaSeg = "";
					$Ubicacion = $Estado = $DeslizamientoCuerda = 0;
					// PSA
					$Bolsa = $Sellado = $NumeroSello = $DescensorAG = $CaboAnclaje = $Humedad = 0;
					$EtiquetaLegible = $EstadoCarcasa = $CuerdaEntrada = $CuerdaSalida = $MosquetonArgolla = 0;
					$NecesarioAbrir = 0; $RuedaDentada = $Dientes = $PoleaCuerda = $SuperficiePolea = $CajaFreno = $UnidadFreno = -1;
					$ProfundidadFreno = $GuarnicionFreno = $ZapatasFreno = $ControlMuelle = $FlancosArbol = $PuntosApoyo = -1;
					$EstadoCuerda = $FinDeCuerda = $Termoretractil = $EstMosquetonPri = $EstCuerdaSeguridad = $MosquetonSeguridad = 0;
					$CargaMinima = $VainaCuerda = $Mordazas = 0;
					// MITTELMANN
					$MaletinEqu = $SisEnvaseEqu = $SacaAzulTran = $BolsaPlastico = $DescensorMRG = $CuerdaDesMRG = 0;
					$CuerdasSeguridad = $PegatinaPrecinto = $BridaPrecinto = $EtiquetaExterior = $LibroInspecciones = 0;
					$MaletinEmb = $SisEnvaseEmb = $BolsaPlasEmb = $SacaAzulEmb = $PegatinaPreEmb = $BridaPreEmb = $PreTornillTFreno = 0;
					$GrosorPasTFreno = $EstMuelleTFreno = $EjePinonTFreno = $LimPinonTFreno = $ZonaSurcosTFreno = $ZonaLimpiaTFreno = 0;
					$EstTornillTFreno = $TorLoctiteTFreno = $MarcasTornTFreno = $PreTornillTPolea = $HolguraEjeTPolea = $EstNerviosTPolea = 0;
					$EstCarcasaTPolea = $EstTornillTPolea = $TorLoctiteTPolea = $MarcasTornTPolea = $PreTornillCFreno = $EstJuntaCFreno = 0;
					$EstDientesCFreno = $RuedaLimpiaCFreno = $EstTornillCFreno = $TorLoctiteCFreno = $MarcasTornCFreno = 0;
					$EstGenCuerdaPri = $EstProCuerdaPri = $LongitudCuerdaPri = $LongMedidaCuerdaPri = $MosquetonCuerdaPri = 0;
					$EstGenCuerdaSeg = $SupSacaCuerdaSeg = $EstMosqueton = $FucMosqueton = 0; 
					$NSerieCuerdaSeg1 = $AnyoFabCuerdaSeg1 = $NSerieCuerdaSeg2 = $AnyoFabCuerdaSeg2 = "";				

					if (!$HayDatos)
						$HayDatos = true;
					if (is_object($aux = $descensor->getElementsByTagName("NSerie")->item(0)))
						$NSerie = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("Fabricante")->item(0)))
						$Fabricante = (!is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaDato("Nombre","MarcaDes", "Id='".$aux->nodeValue."'", $conn);
					if (is_object($aux = $descensor->getElementsByTagName("ModeloDes")->item(0)))
						$ModeloDes  = (!is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaDato("Nombre","ModeloDes", "Id='".$aux->nodeValue."'", $conn);
					if (is_object($aux = $descensor->getElementsByTagName("Longitud")->item(0)))
						$Longitud = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("PrecintoViejo")->item(0)))
						$PrecintoViejo = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("PrecintoNuevo")->item(0)))
						$PrecintoNuevo = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("AnyoFabricacion")->item(0)))
						$AnyoFabricacion = $aux->nodeValue;
					// ODG, 23.09.14 La tablet manda la ubicación como no debe, así que preveeo que pueda venir como numérico ó texto
					//	1 - Nacelle, 2 - Ground.
					if (is_object($aux = $descensor->getElementsByTagName("Ubicacion")->item(0)))
						$Ubicacion = ($aux->nodeValue=="1" || $aux->nodeValue=="Nacelle")?1:2;
					if (is_object($aux = $descensor->getElementsByTagName("Envasado")->item(0)))
						$Envasado = $aux->nodeValue;
						
					if ($MarcaDes == 2)
					{
						if (is_object($aux = $descensor->getElementsByTagName("MaletinEqu")->item(0)))
							$MaletinEqu = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SisEnvaseEqu")->item(0)))
							$SisEnvaseEqu = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SacaAzulTran")->item(0)))
							$SacaAzulTran = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BolsaPlastico")->item(0)))
							$BolsaPlastico = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("DescensorMRG")->item(0)))
							$DescensorMRG = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdaDesMRG")->item(0)))
							$CuerdaDesMRG = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdasSeguridad")->item(0)))
							$CuerdasSeguridad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PegatinaPrecinto")->item(0)))
							$PegatinaPrecinto = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BridaPrecinto")->item(0)))
							$BridaPrecinto = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EtiquetaExterior")->item(0)))
							$EtiquetaExterior = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LibroInspecciones")->item(0)))
							$LibroInspecciones = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MaletinEmb")->item(0)))
							$MaletinEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SisEnvaseEmb")->item(0)))
							$SisEnvaseEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BolsaPlasEmb")->item(0)))
							$BolsaPlasEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SacaAzulEmb")->item(0)))
							$SacaAzulEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PegatinaPreEmb")->item(0)))
							$PegatinaPreEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BridaPreEmb")->item(0)))
							$BridaPreEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PreTornillTFreno")->item(0)))
							$PreTornillTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("GrosorPasTFreno")->item(0)))
							$GrosorPasTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstMuelleTFreno")->item(0)))
							$EstMuelleTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EjePinonTFreno")->item(0)))
							$EjePinonTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LimPinonTFreno")->item(0)))
							$LimPinonTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ZonaSurcosTFreno")->item(0)))
							$ZonaSurcosTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ZonaLimpiaTFreno")->item(0)))
							$ZonaLimpiaTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstTornillTFreno")->item(0)))
							$EstTornillTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("TorLoctiteTFreno")->item(0)))
							$TorLoctiteTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MarcasTornTFreno")->item(0)))
							$MarcasTornTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PreTornillTPolea")->item(0)))
							$PreTornillTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("HolguraEjeTPolea")->item(0)))
							$HolguraEjeTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstNerviosTPolea")->item(0)))
							$EstNerviosTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstCarcasaTPolea")->item(0)))
							$EstCarcasaTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstTornillTPolea")->item(0)))
							$EstTornillTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("TorLoctiteTPolea")->item(0)))
							$TorLoctiteTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MarcasTornTPolea")->item(0)))
							$MarcasTornTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PreTornillCFreno")->item(0)))
							$PreTornillCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstJuntaCFreno")->item(0)))
							$EstJuntaCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstDientesCFreno")->item(0)))
							$EstDientesCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("RuedaLimpiaCFreno")->item(0)))
							$RuedaLimpiaCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstTornillCFreno")->item(0)))
							$EstTornillCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("TorLoctiteCFreno")->item(0)))
							$TorLoctiteCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MarcasTornCFreno")->item(0)))
							$MarcasTornCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("DeslizamientoCuerda")->item(0)))
							$DeslizamientoCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstGenCuerdaPri")->item(0)))
							$EstGenCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstProCuerdaPri")->item(0)))
							$EstProCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LongitudCuerdaPri")->item(0)))
							$LongitudCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LongMedidaCuerdaPri")->item(0)))
							$LongMedidaCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MosquetonCuerdaPri")->item(0)))
							$MosquetonCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaPri")->item(0)))
							$AnyoFabCuerdaPri = addslashes($aux->nodeValue);	// AnyoCuerdaPri
						if (is_object($aux = $descensor->getElementsByTagName("EstGenCuerdaSeg")->item(0)))
							$EstGenCuerdaSeg = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SupSacaCuerdaSeg")->item(0)))
							$SupSacaCuerdaSeg = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstMosqueton")->item(0)))
							$EstMosqueton = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("FucMosqueton")->item(0)))
							$FucMosqueton = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							
						if (is_object($aux = $descensor->getElementsByTagName("NSerieCuerdaSeg1")->item(0)))
							$NSerieCuerdaSeg1 = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg1")->item(0)))
							$AnyoFabCuerdaSeg1 = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("NSerieCuerdaSeg2")->item(0)))
							$NSerieCuerdaSeg2 = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg2")->item(0)))
							$AnyoFabCuerdaSeg2 = addslashes($aux->nodeValue);						
					}
					else		// Por Defecto, siempre PSA
					{
						if (is_object($aux = $descensor->getElementsByTagName("Bolsa")->item(0)))
							$Bolsa = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Sellado")->item(0)))
							$Sellado = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("NumeroSello")->item(0)))
							$NumeroSello = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("DescensorAG")->item(0)))
							$DescensorAG = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CaboAnclaje")->item(0)))
							$CaboAnclaje = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Humedad")->item(0)))
							$Humedad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EtiquetaLegible")->item(0)))
							$EtiquetaLegible = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstadoCarcasa")->item(0)))
							$EstadoCarcasa = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdaEntrada")->item(0)))
							$CuerdaEntrada = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdaSalida")->item(0)))
							$CuerdaSalida = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MosquetonArgolla")->item(0)))
							$MosquetonArgolla = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("NecesarioAbrir")->item(0)))
							$NecesarioAbrir = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
				
						if ($NecesarioAbrir)
						{
							if (is_object($aux = $descensor->getElementsByTagName("RuedaDentada")->item(0)))
								$RuedaDentada = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("Dientes")->item(0)))
								$Dientes = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("PoleaCuerda")->item(0)))
								$PoleaCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("SuperficiePolea")->item(0)))
								$SuperficiePolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("CajaFreno")->item(0)))
								$CajaFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("UnidadFreno")->item(0)))
								$UnidadFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("ProfundidadFreno")->item(0)))
								$ProfundidadFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("GuarnicionFreno")->item(0)))
								$GuarnicionFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("ZapatasFreno")->item(0)))
								$ZapatasFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("ControlMuelle")->item(0)))
								$ControlMuelle = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("FlancosArbol")->item(0)))
								$FlancosArbol = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("PuntosApoyo")->item(0)))
								$PuntosApoyo = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						}

						if (is_object($aux = $descensor->getElementsByTagName("EstadoCuerda")->item(0)))
							$EstadoCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("FinDeCuerda")->item(0)))
							$FinDeCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Termoretractil")->item(0)))
							$Termoretractil = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaPri")->item(0)))
							$AnyoFabCuerdaPri = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("EstMosquetonPri")->item(0)))
							$EstMosquetonPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstCuerdaSeguridad")->item(0)))
							$EstCuerdaSeguridad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MosquetonSeguridad")->item(0)))
							$MosquetonSeguridad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("NSerieSeguridad")->item(0)))
							$NSerieSeguridad = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg")->item(0)))
							$AnyoFabCuerdaSeg = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("DeslizamientoCuerda")->item(0)))
							$DeslizamientoCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CargaMinima")->item(0)))
							$CargaMinima = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("VainaCuerda")->item(0)))
							$VainaCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Mordazas")->item(0)))
							$Mordazas = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
					}
					
					if (is_object($aux = $descensor->getElementsByTagName("EstadoDes")->item(0)))
						$Estado = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;

					$Material = $Motivo = $Cantidad = false;
					for ($nMat = 1; $nMat < 5; $nMat ++)
					{						
						if (is_object($aux = $descensor->getElementsByTagName("Material".($Tmp = sprintf("%02d", $nMat)))->item(0)))
							$Material[] = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("Motivo".$Tmp)->item(0)))
							$Motivo[] = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("Cantidad".$Tmp)->item(0)))
							$Cantidad[] = $aux->nodeValue;
					}

					// Crea el PDF de la Lista Control Descensores
					include("../inc/inspeccion/DetCtrlDes.php");
					
					unset ($NSerie, $Fabricante, $ModeloDes, $Longitud, $PrecintoViejo, $PrecintoNuevo, $AnyoFabricacion, $Ubicacion, 
						$Envasado, $Bolsa, $Sellado, $NumeroSello, $Descensor, $CaboAnclaje, $Humedad, $EtiquetaLegible, $EstadoCarcasa, $CuerdaEntrada,  $CuerdaSalida, 
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
				} // Fin Revisada
				
				unset($Fecha, $OT);
			} // Fin Grupo Descensores
			
			unset($Literal, $MarcaDes);
		}  // Fin es Línea
	} // Fin Grupo Torre
	
	unset($Tipo,$NombreParque,$ClienteParque, $IdiomaChk,$EmpChk,$FirChk);
	
	// Visulizo el PDF
	if ($HayDatos)
		$pdf->Output();
	else
		echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No hay CheckList Validadas</b></span>';
}  // Fin file_exists
else
	echo '<span style="font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#455565;font-weight:normal;"><b>No existe Lista Control ('.$FileXml.')</b></span>';
	
unset($LinCod, $LinPle, $FileXml);
if ($conn)
	mysql_close($conn);
?>