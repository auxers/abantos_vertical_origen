<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../inc/function/funcs.php");
require_once("../db-config.php");
$_SESSION["mi_url"] = "Inspeccion/".fPageName();

$mensaje = "";
if (isset($_REQUEST['Borrar']))
{
	if (file_exists($File="../data/".$_REQUEST['Borrar']))
		unlink($File);
}
else if (isset($_REQUEST['Confirmar']))
{   // Iniciamos las variables
	$CanAEG = (isset($_REQUEST['CanAEG'])) ? $_REQUEST['CanAEG'] : 0;
	$CanOPE = (isset($_REQUEST['CanOPE'])) ? $_REQUEST['CanOPE'] : 0;
	$CableAdecuado = $PletinaAdecuada = $SoporteAdecuado = 
		$ListaControl = $ListaCtrlExt = $ListaCtrlDes = false;
	$TorreAnterior = 0; $CabTxtLV = $CabTxtMX = $TxtLV = "";
	$NumLV = $IdAlbLV = $IdAlbMX = $IdAlbaran = 0;		// ID's Albarán LV y MX...

	$FicherosXML = false;
	if ($_REQUEST['Confirmar'] != "Validar")
		$FicherosXML[] = $_REQUEST['Confirmar'];
	else
	{
		$IdParque = "";
		foreach($_REQUEST as $Campo=>$Valor)
		{
			if (substr($Campo, 0, 3) == "XML")
			{
				if (($Tmp=substr($Campo, 3, 4)) != $IdParque)
				{
					if ($IdParque == "")
						$IdParque = $Tmp;
					else
						$mensaje = "ERROR, los ficheros deben de ser del mismo Parque";
				}
				$FicherosXML[] = $Valor;
			}
		}
		
		if (!$FicherosXML)
			$mensaje = "ERROR, No ha seleccionado ningún fichero";
		unset($Tmp,$IdParque,$Campo,$Valor);
	}
	
	if (empty($mensaje))
	{   // Confirmar Archivo
		$Tipo = $IdParque = "";
		foreach($FicherosXML as $FileXML)
		{
			if (file_exists($File="../data/".$FileXML))
			{
				$doc = new DOMDocument();
				$doc->load($File);

				// Parque
				$Tipo = substr($FileXML,0, 1);
				$IdParque = fQuitaZeros(substr($FileXML, 1, 10));
				$IdOperario1 = $IdOperario2 = ""; $IdControl = 0;
				if (is_object($aux = $doc->getElementsByTagName("Control")->item(0))) {
					// Marcamos el XML como validado
					if (mysql_query(($Query = "INSERT INTO CtrlValidacion SET Control='".$aux->nodeValue."', Fecha='".date("Y-m-d")."'"), $conn))
						$IdControl = mysql_insert_id();
					else
						fDebug("../",mysql_error($conn),$Query);
				}
				if (is_object($aux = $doc->getElementsByTagName("Cliente")->item(0))) {
					if (!empty($aux->nodeValue))
						mysql_query("UPDATE Parques SET Cliente='".$aux->nodeValue."' WHERE Id=".$IdParque, $conn);
				}
				if (is_object($aux = $doc->getElementsByTagName("Operario1")->item(0)))
					$IdOperario1 = $aux->nodeValue;
				if (is_object($aux = $doc->getElementsByTagName("Operario2")->item(0)))
					$IdOperario2 = $aux->nodeValue;

				// Grupo Torres
				$OpeAdd = array(0,0,0);	// 0 - Líneas Vida, 1 - Extintores, 2 - Descensores
				foreach($doc->getElementsByTagName("Torre") as $torre)
				{
					$IdTorre = $Altura = ""; $IdLinea = $TipoAEG = 0; $NewTorre = true;
					if (is_object($aux = $torre->getElementsByTagName("Id")->item(0)))
						$IdLinea = $aux->nodeValue;
					if (is_object($aux = $torre->getElementsByTagName("NumeroTorre")->item(0)))
						$IdTorre = fNumTorre($aux->nodeValue);
					if (is_object($aux = $torre->getElementsByTagName("Altura")->item(0)))
						$Altura = addslashes($aux->nodeValue);
					if (is_object($aux = $torre->getElementsByTagName("IdTipoAEG")->item(0)))
						$TipoAEG = $aux->nodeValue;

					// Por cada linea grabamos una entrada en lista de control si está Revisada
					foreach($torre->getElementsByTagName("Linea") as $linea)
					{
						$IdLinPle = $TipoDeLinea = 0; $MarcaLV = 1;
						$OT = $Fecha = $NTrompa = $NAbsorbedor = $NTramo = $Serie = $Cable = "";
						if (is_object($aux = $linea->getElementsByTagName("OT")->item(0)))
							$OT = $aux->nodeValue;
						if (is_object($aux = $linea->getElementsByTagName("Fecha")->item(0)))
							$Fecha = ($aux->nodeValue != "") ? fFechaYMD($aux->nodeValue) : "";
							
						// ODG, 05.01.15 Se podrá modificar la Marca de la Línea de vida después de la asignación...
						if (is_object($aux = $linea->getElementsByTagName("IdMarca")->item(0)))
							$MarcaLV = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("MarcaLin",addslashes($aux->nodeValue),"",$conn);
						if (is_object($aux = $linea->getElementsByTagName("Cable")->item(0)))
							$Cable = addslashes($aux->nodeValue);
						if (is_object($aux = $linea->getElementsByTagName("Serie")->item(0)))
							$Serie = addslashes($aux->nodeValue);
						if (is_object($aux = $linea->getElementsByTagName("Absorbedor")->item(0)))
							$NAbsorbedor = addslashes($aux->nodeValue);
						if (is_object($aux = $linea->getElementsByTagName("Trompa")->item(0)))
							$NTrompa = addslashes($aux->nodeValue);
						if (is_object($aux = $linea->getElementsByTagName("Tramo")->item(0)))
							$NTramo = addslashes($aux->nodeValue);
						if (is_object($aux = $linea->getElementsByTagName("IdPletina")->item(0)))
							$IdLinPle = $aux->nodeValue;
						if (is_object($aux = $linea->getElementsByTagName("TipoDeLinea")->item(0)))
							$TipoDeLinea = $aux->nodeValue;	// 1 (Servicio), 2 (Nacelle)

						// Nodo Revisión
						$Resultado = $EstadoCable = $Tension = $ConfPletina = $CantidadCable = 0; $EstadoCableMotivo = "";
						$CampoPletina1 = $CampoPletina2 = $CampoPletina3 = $CampoPletina4 = $VarillasRoscadas = $Cartel = 0;
						$AnclajeInf1AIMotivo = ""; $AnclajeInf1 = $AnclajeInf1AI = $AnclajeInf1Tensor = $AnclajeInf1Perrillos = 0;
						$AnclajeInf1Guardacabos = $AnclajeInf1Tuercas = $AnclajeSup1 = $AnclajeSup1AS = 0;
						$AnclajeSup1ASMotivo = ""; $AnclajeSup1Pasador = $AnclajeSup1Bulon = $AnclajeSup2 = 0;
						$Amortiguador = $AmortiguadorMuelle = 0; $AmortiguadorMuelleMotivo = ""; $AmortiguadorPasador = 0;
						$AmortiguadorBulon = $TornilleriaPletina = $TornilleriaApriete = $Ensayo = $Escalera = 0;
						$Interferencia = $Oxidacion = 0; $Observaciones = $TrabajosPendientes = "";
						foreach($linea->getElementsByTagName("Revision") as $revision)
						{
							if (is_object($aux = $revision->getElementsByTagName("Resultado")->item(0)))
								$Resultado = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("EstadoCable")->item(0)))
								$EstadoCable = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("CantidadCable")->item(0)))
								$CantidadCable = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("EstadoCableMotivo")->item(0)))
								$EstadoCableMotivo = addslashes($aux->nodeValue);
							if (is_object($aux = $revision->getElementsByTagName("Tension")->item(0)))
								$Tension = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("ConfPletina")->item(0)))
								$ConfPletina = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("CampoPletina1")->item(0)))
								$CampoPletina1 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("CampoPletina2")->item(0)))
								$CampoPletina2 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("CampoPletina3")->item(0)))
								$CampoPletina3 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("CampoPletina4")->item(0)))
								$CampoPletina4 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("VarillasRoscadas")->item(0)))
								$VarillasRoscadas = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Cartel")->item(0)))
								$Cartel = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1")->item(0)))
								$AnclajeInf1 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1AI")->item(0)))
								$AnclajeInf1AI = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1AIMotivo")->item(0)))
								$AnclajeInf1AIMotivo = addslashes($aux->nodeValue);
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1Tensor")->item(0)))
								$AnclajeInf1Tensor = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1Perrillos")->item(0)))
								$AnclajeInf1Perrillos = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1Guardacabos")->item(0)))
								$AnclajeInf1Guardacabos = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeInferior1Tuercas")->item(0)))
								$AnclajeInf1Tuercas = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeSuperior1")->item(0)))
								$AnclajeSup1 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeSuperior1AS")->item(0)))
								$AnclajeSup1AS = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeSuperior1ASMotivo")->item(0)))
								$AnclajeSup1ASMotivo = addslashes($aux->nodeValue);
							if (is_object($aux = $revision->getElementsByTagName("AnclajeSuperior2")->item(0)))
								$AnclajeSup2 = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeSuperior1Pasador")->item(0)))
								$AnclajeSup1Pasador = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AnclajeSuperior1Bulon")->item(0)))
								$AnclajeSup1Bulon = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Amortiguador")->item(0)))
								$Amortiguador = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AmortiguadorMuelle")->item(0)))
								$AmortiguadorMuelle = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AmortiguadorMuelleMotivo")->item(0)))
								$AmortiguadorMuelleMotivo = addslashes($aux->nodeValue);
							if (is_object($aux = $revision->getElementsByTagName("AmortiguadorPasador")->item(0)))
								$AmortiguadorPasador = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("AmortiguadorBulon")->item(0)))
								$AmortiguadorBulon = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("TornilleriaPletina")->item(0)))
								$TornilleriaPletina = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("TornilleriaApriete")->item(0)))
								$TornilleriaApriete = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Ensayo")->item(0)))
								$Ensayo = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Escalera")->item(0)))
								$Escalera = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Interferencia")->item(0)))
								$Interferencia = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Oxidacion")->item(0)))
								$Oxidacion = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $revision->getElementsByTagName("Observaciones")->item(0)))
								$Observaciones = addslashes($aux->nodeValue);
							if (is_object($aux = $revision->getElementsByTagName("TrabajosPendientes")->item(0)))
								$TrabajosPendientes = addslashes($aux->nodeValue);
							break;	// Sólo un grupo de Revisión por Línea, aunque no es posible que haya más lo preveeo...
						}

						// Sólo en el caso de que la Línea haya sido Revisada.
						if ($Fecha != "")
						{   // Actualizo los Datos de las Líneas según lo que nos manda la Tablet
							//	OD, 14.02.13 Sea Montaje ó Revisión las LV ya estarán dadas de alta en los Parques...
							if (is_numeric($IdLinea) && is_numeric($IdLinPle))
							{   // Actualizo los Datos de la Torre, puede variar el Nº Torre y/o la Altura
								if ($NewTorre)
								{
									$Query = "UPDATE Lineas SET NumeroTorre='".$IdTorre."', IdAltura='".fBuscaTabla("Alturas",$Altura,"",$conn)."',
										TipoAerogeneradorGAMESA='".(fBuscaDato("Prefijo", "TAerogenerador", "Id=".$TipoAEG, $conn)." ".$Altura)."', IdMarca='".$MarcaLV."' WHERE Id=".$IdLinea;
									if (mysql_query($Query, $conn))
										$NewTorre = false;
								}

								$Query = "UPDATE LineasPletina SET NumeroSerie='".$Serie."', NumeroCable='".$Cable."',
									NTrompa='".$NTrompa."', NAbsorbedor='".$NAbsorbedor."', NTramo='".$NTramo."' WHERE Id=".$IdLinPle;
								mysql_query($Query, $conn);
							}

							// Añadimos la Lista de Control
							$Query = "INSERT INTO ListaControl SET IdLinea='".$IdLinea."', OT='".$OT."', Fecha='".$Fecha."',Tipo='".$Tipo."',LTipo='".$TipoDeLinea.
								"',Resultado='".$Resultado."',EstadoCable='".$EstadoCable."',CantidadCable='".$CantidadCable."',EstadoCableMotivo='".$EstadoCableMotivo.
								"',Tension='".$Tension."',ConfPletina='".$ConfPletina."',CampoPletina1='".$CampoPletina1."',CampoPletina2='".$CampoPletina2."',CampoPletina3='".$CampoPletina3."',CampoPletina4='".$CampoPletina4.
								"',VarillasRoscadas='".$VarillasRoscadas."',Cartel='".$Cartel."',AnclajeInferior1='".$AnclajeInf1."',AnclajeInferior1AI='".$AnclajeInf1AI."',AnclajeInferior1AIMotivo='".$AnclajeInf1AIMotivo.
								"',AnclajeInferior1Tensor='".$AnclajeInf1Tensor."',AnclajeInferior1Perrillos='".$AnclajeInf1Perrillos."',AnclajeInferior1Guardacabos='".$AnclajeInf1Guardacabos.
								"',AnclajeInferior1Tuercas='".$AnclajeInf1Tuercas."',AnclajeSuperior1='".$AnclajeSup1."',AnclajeSuperior1AS='".$AnclajeSup1AS."',AnclajeSuperior1ASMotivo='".$AnclajeSup1ASMotivo.
								"',AnclajeSuperior2='".$AnclajeSup2."',AnclajeSuperior1Pasador='".$AnclajeSup1Pasador."',AnclajeSuperior1Bulon='".$AnclajeSup1Bulon.
								"',Amortiguador='".$Amortiguador."',AmortiguadorMuelle='".$AmortiguadorMuelle."',AmortiguadorMuelleMotivo='".$AmortiguadorMuelleMotivo.
								"',AmortiguadorPasador='".$AmortiguadorPasador."',AmortiguadorBulon='".$AmortiguadorBulon."',TornilleriaPletina='".$TornilleriaPletina.
								"',TornilleriaApriete='".$TornilleriaApriete."',Ensayo='".$Ensayo."',Escalera='".$Escalera."',Interferencia='".$Interferencia."',Oxidacion='".$Oxidacion.
								"',Observaciones='".$Observaciones."',TrabajosPendientes='".$TrabajosPendientes."',IdControl='".$IdControl."',IdTrabajador='".$IdOperario1."'";
							if (mysql_query($Query, $conn))
							{
								$ListaControl[] = mysql_insert_id();

								// Añado la Línea para el 2do Operario si procede, ojo Resultado debe de ser 2, ya 
								//	que si es 1 ó 0, al imprimir los certificados saldrían líneas duplicadas, sólo me interesa éste
								//	registro en la búsqueda de los Trabajadores, que tendrá la opción de Resultado 1 y 2, ó 0 y 2...
								if ($IdOperario2 != "" && !$OpeAdd[0])
								{
									$Query = "INSERT INTO ListaControl SET IdLinea='".$IdLinea."', Fecha='".$Fecha."', OT='', 
										Tipo='".$Tipo."',Resultado='2',IdControl='".$IdControl."',IdTrabajador='".$IdOperario2."'";
									if (mysql_query($Query, $conn)) {
										$ListaControl[] = $OpeAdd[0] = mysql_insert_id();
									}
								}
							}
							else
								fDebug("../",mysql_error($conn),$Query);
						} // Fin Revisada

						unset ($OT, $Fecha, $MarcaLV,$Serie,$Cable, $NTrompa,$NAbsorbedor,$NTramo, $IdLinPle, $TipoDeLinea, $TipoPletina, $EstadoCable, $Tension, $ConfPletina,
 							$CampoPletina1, $CampoPletina2, $CampoPletina3, $CampoPletina4, $NombrePletina1, $NombrePletina2, $NombrePletina3, $NombrePletina4,
							$VarillasRoscadas, $Cartel, $AnclajeInf1, $AnclajeInf1AI, $AnclajeInf1Tensor, $AnclajeInf1Perrillos, $AnclajeInf1Guardacabos, $AnclajeInf1Tuercas,
							$AnclajeSup1, $AnclajeSup1AS, $AnclajeSup2, $AnclajeSup1Pasador, $AnclajeSup1Bulon, $TornilleriaPletina, $TornilleriaApriete, 
							$Ensayo, $Escalera, $Oxidacion, $Amortiguador, $AmortiguadorMuelle, $AmortiguadorMuelleMotivo, $AmortiguadorPasador, $AmortiguadorBulon,
							$Interferencia, $Resultado, $Observaciones, $TrabajosPendientes);
					} // Fin Grupo Lineas de Vida

					// Añadimos las Lista Control de Extintores si existen
					foreach ($torre->getElementsByTagName("Extintor") as $extintor)
					{
						$OT = $Fecha = ""; $Marca = $Modelo = $Localizacion = $Colocacion = 1;
						$IdExtintor = $Sustituido = $PrecintoSustitucion = $CartelLu = $PegatinaCarac = 0;
						$PegatinaRevi = $MarcadoCE = $PrecintoRetimbrado = $EstadoCuerpo = 0;
						$EstadoCabeza = $Pasador = $Valvula = $Manguera = $Soporte = $Junta = $Estado = $FaltaPeso = $Caducidad = 0;
						$NPlaca = $AgenteExtintor = $PesoAgExtintor = $Motivo = "";
						$PlacaSustitucion = $Materiales = $Otra = $ObservacionesExt = $FechaFabricacion = $FechaRetimbrado = "";
					
						if (is_object($aux = $extintor->getElementsByTagName("OT")->item(0)))
							$OT = $aux->nodeValue;
						if (is_object($aux = $extintor->getElementsByTagName("Fecha")->item(0)))
							$Fecha = ($aux->nodeValue != "") ? fFechaYMD($aux->nodeValue) : "";
						if (is_object($aux = $extintor->getElementsByTagName("IdExtintor")->item(0)))
							$IdExtintor = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							
						if (is_object($aux = $extintor->getElementsByTagName("Localizacion")->item(0)))
							$Localizacion = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("Localizacion",addslashes($aux->nodeValue),"",$conn);
						if (is_object($aux = $extintor->getElementsByTagName("Placa")->item(0)))
							$NPlaca = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("Marca")->item(0)))
							$Marca = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("MarcaExt",addslashes($aux->nodeValue),"",$conn);
						if (is_object($aux = $extintor->getElementsByTagName("Modelo")->item(0)))
							$Modelo = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("ModeloExt",addslashes($aux->nodeValue),"",$conn);
						if (is_object($aux = $extintor->getElementsByTagName("FechaFabricacion")->item(0)))
							$FechaFabricacion = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("FechaRetimbrado")->item(0)))
							$FechaRetimbrado = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("AgenteExtintor")->item(0)))
							$AgenteExtintor = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("PesoAgExtintor")->item(0)))
							$PesoAgExtintor = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("Colocacion")->item(0)))
							$Colocacion = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("Colocacion",addslashes($aux->nodeValue),"",$conn);
						if (is_object($aux = $extintor->getElementsByTagName("Movido")->item(0)))
							$Movido = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("Sustituido")->item(0)))
							$Sustituido = (is_numeric($aux->nodeValue)) ? (($aux->nodeValue)==1?1:0):0;
						if (is_object($aux = $extintor->getElementsByTagName("PlacaSustitucion")->item(0)))
							$PlacaSustitucion = addslashes($aux->nodeValue);
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
							$Materiales = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("EstadoExt")->item(0)))
							$Estado = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $extintor->getElementsByTagName("FaltaPeso")->item(0)))
							$FaltaPeso = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $extintor->getElementsByTagName("Caducidad")->item(0)))
							$Caducidad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $extintor->getElementsByTagName("Otra")->item(0)))
							$Otra = addslashes($aux->nodeValue);
						if (is_object($aux = $extintor->getElementsByTagName("ObservacionesExt")->item(0)))
							$ObservacionesExt = addslashes($aux->nodeValue);

						// Sólo en el caso que haya sido Revisado
						if ($Fecha != "")
						{
							$Query = "INSERT INTO ListaCtrlExt SET IdLinea='".$IdLinea."', Fecha='".$Fecha."', OT='".$OT."', Localizacion='".$Localizacion."', Colocacion='".$Colocacion."', 
								NPlaca='".$NPlaca."', Marca='".$Marca."', Modelo='".$Modelo."', Movido='".$Movido."', FechaFabricacion='".$FechaFabricacion."', FechaRetimbrado='".$FechaRetimbrado."',
								AgenteExtintor='".$AgenteExtintor."', PesoAgExtintor='".$PesoAgExtintor."', Sustituido='".$Sustituido."', PlacaSustitucion='".$PlacaSustitucion."', 
								PrecintoSustitucion='".$PrecintoSustitucion."', CartelLu='".$CartelLu."', PegatinaCaracUso='".$PegatinaCarac."', PegatinaRevision='".$PegatinaRevi."', MarcadoCE='".$MarcadoCE."', 
								PrecintoRetimbrado='".$PrecintoRetimbrado."', EstadoCuerpo='".$EstadoCuerpo."', EstadoCabeza='".$EstadoCabeza."', Pasador='".$Pasador."', 
								Valvula='".$Valvula."', Manguera='".$Manguera."', Soporte='".$Soporte."', Junta='".$Junta."', Estado='".$Estado."', FaltaPeso='".$FaltaPeso."', Caducidad='".$Caducidad."', 
								Otra='".$Otra."', Materiales='".$Materiales."', Observaciones='".$ObservacionesExt."', IdControl='".$IdControl."', IdTrabajador='".$IdOperario1."'";
							if (mysql_query($Query, $conn))
							{
								$ListaCtrlExt[] = mysql_insert_id();
							
								// Añado la Línea para el 2do Operario si procede
								if ($IdOperario2 != "" && !$OpeAdd[1])
								{
									$Query = "INSERT INTO ListaCtrlExt SET IdLinea='".$IdLinea."', Fecha='".$Fecha."', OT='', 
										IdControl='".$IdControl."', IdTrabajador='".$IdOperario2."', Estado='2'";
									if (mysql_query($Query, $conn)) {
										$ListaCtrlExt[] = $OpeAdd[1] = mysql_insert_id();
									}
								}
							}
							else
								fDebug("../",mysql_error($conn),$Query);

							// Actualizamos los datos del Extintor, prevalece el último Extintor puesto.
							if (!$Sustituido)
							{								
								$Query = "UPDATE LineasExtintor SET Localizacion='".$Localizacion."', 
									Marca='".$Marca."', Modelo='".$Modelo."',
									NPlaca='".$NPlaca."', FechaFabricacion='".$FechaFabricacion."', FechaRetimbrado='".$FechaRetimbrado."',
									AgenteExtintor='".$AgenteExtintor."' WHERE Id=".$IdExtintor;
								if (!mysql_query($Query, $conn))
									fDebug("../",mysql_error($conn),$Query);
							}
						} // Ha sido Revisado
						
						unset($Fecha, $OT, $Localizacion, $NPlaca, $Marca, $Modelo, $FechaFabricacion, $FechaRetimbrado, $AgenteExtintor, 
							$PesoAgExtintor, $Colocacion, $Movido, $Sustituido, $PlacaSustitucion, $PrecintoSustitucion, $CartelLu, $PegatinaCarac, 
							$PegatinaRevi, $MarcadoCE, $PrecintoRetimbrado, $EstadoCuerpo, $EstadoCabeza, $Pasador, $Valvula, $Manguera, 
							$Soporte, $Junta, $Materiales, $Estado, $FaltaPeso, $Caducidad, $Otra, $ObservacionesExt);
					} // Fin Grupo Extintores

					// Añadimos las Lista Control de Descensores si existe
					foreach ($torre->getElementsByTagName("Descensor") as $descensor)
					{   
						$IdDescensor = $Ubicacion = $DeslizamientoCuerda = 0;
						$OT = $Fecha = $NSerie = $Longitud = ""; $Fabricante = $ModeloDes = 1;
						$PrecintoViejo = $PrecintoNuevo = $Envasado = $NSerieSeguridad = $NSerieSeguridad2 = "";
						$AnyoFabricacion = $AnyoFabCuerdaPri = $AnyoFabCuerdaSeg = $AnyoFabCuerdaSeg2 = "";
						// PSA
						$Bolsa = $Sellado = $NumeroSello = $DescensorAG = $CaboAnclaje = 0;
						$Humedad = $EtiquetaLegible = $EstadoCarcasa = $CuerdaEntrada = 0;
						$CuerdaSalida = $MosquetonArgolla = $NecesarioAbrir = $Estado = 0;
						$RuedaDentada = $Dientes = $PoleaCuerda = $SuperficiePolea = $CajaFreno = $UnidadFreno = -1;
						$ProfundidadFreno = $GuarnicionFreno = $ZapatasFreno = $ControlMuelle = $FlancosArbol = $PuntosApoyo = -1;
						$EstadoCuerda = $FinDeCuerda = $Termoretractil = $EstMosquetonPri = $EstCuerdaSeguridad = 0;
						$MosquetonSeguridad = $CargaMinima = $VainaCuerda = $Mordazas = 0;
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
						
						if (is_object($aux = $descensor->getElementsByTagName("OT")->item(0)))
							$OT = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("Fecha")->item(0)))
							$Fecha = ($aux->nodeValue != "") ? fFechaYMD($aux->nodeValue) : "";
						if (is_object($aux = $descensor->getElementsByTagName("IdDescensor")->item(0)))
							$IdDescensor = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("NSerie")->item(0)))
							$NSerie = addslashes($aux->nodeValue);
							
						// ODG, 05.01.15, A partir de ahora puede venir de la tablet el Id del Fabricante y Modelo, dejo preparado para que valga
						//	para versiones anteriores, es decir, que venga el Nombre de la Marca y Modelo...
						if (is_object($aux = $descensor->getElementsByTagName("Fabricante")->item(0)))
							$Fabricante =(is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("MarcaDes",$aux->nodeValue,"",$conn);
						if (is_object($aux = $descensor->getElementsByTagName("ModeloDes")->item(0)))
							$ModeloDes = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : fBuscaTabla("ModeloDes",$aux->nodeValue,"IdMarca=".$Fabricante,$conn);

						if (is_object($aux = $descensor->getElementsByTagName("Longitud")->item(0)))
							$Longitud = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("PrecintoViejo")->item(0)))
							$PrecintoViejo = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("PrecintoNuevo")->item(0)))
							$PrecintoNuevo = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabricacion")->item(0)))
							$AnyoFabricacion = addslashes($aux->nodeValue);
							
						// ODG, 29.05.14 La tablet manda la ubicación como no debe, así que preveeo que pueda venir como numérico ó texto
						//	1 - Nacelle, 2 - Ground.
						if (is_object($aux = $descensor->getElementsByTagName("Ubicacion")->item(0)))
							$Ubicacion = ($aux->nodeValue=="1" || $aux->nodeValue=="Nacelle")?1:2;
						if (is_object($aux = $descensor->getElementsByTagName("Envasado")->item(0)))
							$Envasado = addslashes($aux->nodeValue);

						if ($Fabricante == 2)	// MITTELMANN
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
								$DeslizamientoCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;	// GlissadeCuerda
								
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
								$NSerieSeguridad = addslashes($aux->nodeValue);
							if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg1")->item(0)))
								$AnyoFabCuerdaSeg = addslashes($aux->nodeValue);
							if (is_object($aux = $descensor->getElementsByTagName("NSerieCuerdaSeg2")->item(0)))
								$NSerieSeguridad2 = addslashes($aux->nodeValue);
							if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg2")->item(0)))
								$AnyoFabCuerdaSeg2 = addslashes($aux->nodeValue);
						}
						else 		// Por defecto, siempre PSA
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
							{
								if (($NecesarioAbrir = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0))
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
							}
							
							if (is_object($aux = $descensor->getElementsByTagName("EstadoCuerda")->item(0)))
								$EstadoCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("FinDeCuerda")->item(0)))
								$FinDeCuerda = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;						
							if (is_object($aux = $descensor->getElementsByTagName("Termoretractil")->item(0)))
								$Termoretractil = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaPri")->item(0)))
								$AnyoFabCuerdaPri = addslashes($aux->nodeValue);
							if (is_object($aux = $descensor->getElementsByTagName("EstMosquetonPri")->item(0)))
								$EstMosquetonPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("EstCuerdaSeguridad")->item(0)))
								$EstCuerdaSeguridad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;					
							if (is_object($aux = $descensor->getElementsByTagName("MosquetonSeguridad")->item(0)))
								$MosquetonSeguridad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
							if (is_object($aux = $descensor->getElementsByTagName("NSerieSeguridad")->item(0)))
								$NSerieSeguridad = $aux->nodeValue;
							if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg")->item(0)))
								$AnyoFabCuerdaSeg = addslashes($aux->nodeValue);
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

						// Sólo en el caso que haya sido Revisado
						if ($Fecha != "")
						{   // 1ro. Modifico los datos del Descensor
							$Query = "UPDATE LineasDescensor SET NSerie='".$NSerie."', Marca='".$Fabricante."', Modelo='".$ModeloDes."',
								Longitud='".$Longitud."', NPrecintoOld='".$PrecintoViejo."', NPrecintoNew='".$PrecintoNuevo."',
								TEnvasado='".$Envasado."', AnyoFabricacion='".$AnyoFabricacion."', AnyoFabCuerdaPri='".$AnyoFabCuerdaPri."',
								AnyoFabCuerdaSeg='".$AnyoFabCuerdaSeg."', NSerieSeguridad='".$NSerieSeguridad."',
								AnyoFabCuerdaSeg2='".$AnyoFabCuerdaSeg2."', NSerieSeguridad2='".$NSerieSeguridad2."'";
							$Query .= " WHERE Id=".$IdDescensor;
							
							if (!mysql_query($Query, $conn))
								fDebug("../",mysql_error($conn),$Query);
	
							// 2do. Añado la Lista de Control
							$Query = "INSERT INTO ListaCtrlDes SET IdLinea='".$IdLinea."', Fecha='".$Fecha."', OT='".$OT."', NSerie='".$NSerie."', 
								Fabricante='".$Fabricante."', ModeloDes='".$ModeloDes."',	Longitud='".$Longitud."', NPrecintoOld='".$PrecintoViejo."', NPrecintoNew='".$PrecintoNuevo."', 
								AnyoFabricacion='".$AnyoFabricacion."', Ubicacion='".$Ubicacion."',	TEnvasado='".$Envasado."', 
								Estado='".$Estado."',	IdControl='".$IdControl."', IdTrabajador='".$IdOperario1."'";
							if (mysql_query($Query, $conn))
							{								
								$ListaCtrlDes[] = $IdLista = mysql_insert_id();																
								if ($Fabricante == 2)	// MITTELMANN
								{									
									$Query = "INSERT INTO DetLstCtrlMRG SET IdLista='".$IdLista."', MaletinEqu='".$MaletinEqu."', SisEnvaseEqu='".$SisEnvaseEqu."', SacaAzulTran='".$SacaAzulTran."',
										BolsaPlastico='".$BolsaPlastico."', DescensorMRG='".$DescensorMRG."', CuerdaDesMRG='".$CuerdaDesMRG."', CuerdasSeguridad='".$CuerdasSeguridad."', PegatinaPrecinto='".$PegatinaPrecinto."',
										BridaPrecinto='".$BridaPrecinto."', EtiquetaExterior='".$EtiquetaExterior."', LibroInspecciones='".$LibroInspecciones."', MaletinEmb='".$MaletinEmb."', SisEnvaseEmb='".$SisEnvaseEmb."',
										BolsaPlasEmb='".$BolsaPlasEmb."', SacaAzulEmb='".$SacaAzulEmb."', PegatinaPreEmb='".$PegatinaPreEmb."', BridaPreEmb='".$BridaPreEmb."', PreTornillTFreno='".$PreTornillTFreno."', 
										GrosorPasTFreno='".$GrosorPasTFreno."', EstMuelleTFreno='".$EstMuelleTFreno."', EjePinonTFreno='".$EjePinonTFreno."', LimPinonTFreno='".$LimPinonTFreno."', ZonaSurcosTFreno='".$ZonaSurcosTFreno."', 
										ZonaLimpiaTFreno='".$ZonaLimpiaTFreno."', EstTornillTFreno='".$EstTornillTFreno."', TorLoctiteTFreno='".$TorLoctiteTFreno."', MarcasTornTFreno='".$MarcasTornTFreno."', 
										PreTornillTPolea='".$PreTornillTPolea."', HolguraEjeTPolea='".$HolguraEjeTPolea."', EstNerviosTPolea='".$EstNerviosTPolea."', EstCarcasaTPolea='".$EstCarcasaTPolea."', 
										EstTornillTPolea='".$EstTornillTPolea."', TorLoctiteTPolea='".$TorLoctiteTPolea."', MarcasTornTPolea='".$MarcasTornTPolea."', PreTornillCFreno='".$PreTornillCFreno."',
										EstJuntaCFreno='".$EstJuntaCFreno."', EstDientesCFreno='".$EstDientesCFreno."', RuedaLimpiaCFreno='".$RuedaLimpiaCFreno."', EstTornillCFreno='".$EstTornillCFreno."', 
										TorLoctiteCFreno='".$TorLoctiteCFreno."', MarcasTornCFreno='".$MarcasTornCFreno."', AnyoCuerdaPri='".$AnyoFabCuerdaPri."', GlissadeCuerda='".$DeslizamientoCuerda."', 
										EstGenCuerdaPri='".$EstGenCuerdaPri."', EstProCuerdaPri='".$EstProCuerdaPri."',	LongitudCuerdaPri='".$LongitudCuerdaPri."', LongMedidaCuerdaPri='".$LongMedidaCuerdaPri."', 
										MosquetonCuerdaPri='".$MosquetonCuerdaPri."', EstGenCuerdaSeg='".$EstGenCuerdaSeg."',	SupSacaCuerdaSeg='".$SupSacaCuerdaSeg."', EstMosqueton='".$EstMosqueton."', FucMosqueton='".$FucMosqueton."', 
										NSerieCuerdaSeg1='".$NSerieSeguridad."', AnyoFabCuerdaSeg1='".$AnyoFabCuerdaSeg."',	NSerieCuerdaSeg2='".$NSerieSeguridad2."', AnyoFabCuerdaSeg2='".$AnyoFabCuerdaSeg2."'";
								}
								else 	// Por defecto, siempre PSA
								{
									$Query = "INSERT INTO DetLstCtrlPSA SET IdLista='".$IdLista."', Bolsa='".$Bolsa."', Sellado='".$Sellado."', NumSello='".$NumeroSello."', 
										DescensorAG='".$DescensorAG."', CaboAnclaje='".$CaboAnclaje."',	Humedad='".$Humedad."', EtiquetaLegible='".$EtiquetaLegible."', EstadoCarcasa='".$EstadoCarcasa."', 
										CuerdaEntrada='".$CuerdaEntrada."', CuerdaSalida='".$CuerdaSalida."',	MosquetonArgolla='".$MosquetonArgolla."', NecesarioAbrir='".$NecesarioAbrir."', 
										RuedaDentada='".$RuedaDentada."', Dientes='".$Dientes."', PoleaCuerda='".$PoleaCuerda."', 
										SuperficiePolea='".$SuperficiePolea."', CajaFreno='".$CajaFreno."', UnidadFreno='".$UnidadFreno."', ProfundidadFreno='".$ProfundidadFreno."', GuarnicionFreno='".$GuarnicionFreno."', 
										ZapatasFreno='".$ZapatasFreno."', ControlMuelle='".$ControlMuelle."', FlancosArbol='".$FlancosArbol."', PuntosApoyo='".$PuntosApoyo."', EstadoCuerda='".$EstadoCuerda."', 
										FinDeCuerda='".$FinDeCuerda."', Termoretractil='".$Termoretractil."', AnyoFabCuerdaPri='".$AnyoFabCuerdaPri."', EstMosquetonPri='".$EstMosquetonPri."', 
										EstCuerdaSeguridad='".$EstCuerdaSeguridad."', MosquetonSeguridad='".$MosquetonSeguridad."', NSerieSeguridad='".$NSerieSeguridad."', AnyoFabCuerdaSeg='".$AnyoFabCuerdaSeg."', 
										DeslizamientoCuerda='".$DeslizamientoCuerda."', CargaMinima='".$CargaMinima."', VainaCuerda='".$VainaCuerda."', Mordazas='".$Mordazas."'";
								}
								if (!mysql_query($Query, $conn))
									fDebug("../",mysql_error($conn),$Query);
									
								// Añado la Línea para el 2do Operario si procede
								if ($IdOperario2 != "" && !$OpeAdd[2])
								{
									$Query = "INSERT INTO ListaCtrlDes SET IdLinea='".$IdLinea."', Fecha='".$Fecha."', OT='',
										IdControl='".$IdControl."', IdTrabajador='".$IdOperario2."', Estado='2'";
									if (mysql_query($Query, $conn)) {
										$ListaCtrlDes[] = $OpeAdd[2] = mysql_insert_id();
									}
								}

								// Materiales utilizado en la revisión del Descensor si los hay
								for ($nMat = 1; $nMat < 5; $nMat ++)
								{
									$Material = $Motivo = ""; $Cantidad = 0;
									if (is_object($aux = $descensor->getElementsByTagName("Material".($Tmp = sprintf("%02d", $nMat)))->item(0)))
										$Material = addslashes($aux->nodeValue);
									if (is_object($aux = $descensor->getElementsByTagName("Motivo".$Tmp)->item(0)))
										$Motivo = addslashes($aux->nodeValue);
									if (is_object($aux = $descensor->getElementsByTagName("Cantidad".$Tmp)->item(0)))
										$Cantidad = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;

									if ($Cantidad != 0 && !empty($Material))
									{
										$Query = "INSERT INTO MaterialCtrlDes SET IdLista='".$IdLista."', Material='".$Material."', 
											Motivo='".$Motivo."', Cantidad='".$Cantidad."'";
										if (!mysql_query($Query, $conn))
											fDebug("../",mysql_error($conn),$Query);
									}
									unset($Material, $Motivo, $Cantidad);
								}
							}
							else
								fDebug("../",mysql_error($conn),$Query);
						}

						unset ($Fecha, $OT, $NSerie, $Fabricante, $Longitud, $PrecintoViejo, $PrecintoNuevo, $AnyoFabricacion, $Ubicacion,
							$Envasado, $Bolsa, $Sellado, $NumeroSello, $Descensor, $CaboAnclaje, $Humedad, $EtiquetaLegible, $EstadoCarcasa, $CuerdaEntrada, $CuerdaSalida,
							$MosquetonArgolla, $NecesarioAbrir, $RuedaDentada, $Dientes, $PoleaCuerda, $SuperficiePolea, $CajaFreno, $UnidadFreno, $ProfundidadFreno, $GuarnicionFreno,
							$ZapatasFreno, $ControlMuelle, $FlancosArbol, $PuntosApoyo, $EstadoCuerda, $FinDeCuerda, $Termoretractil, $AnyoFabCuerdaPri, $EstMosquetonPri,
							$EstCuerdaSeguridad, $MosquetonSeguridad, $NSerieSeguridad, $AnyoFabCuerdaSeg, $NSerieSeguridad2, $AnyoFabCuerdaSeg2, $DeslizamientoCuerda, $CargaMinima, $VainaCuerda, $Mordazas, $Estado);
						unset ($MaletinEqu, $SisEnvaseEqu, $SacaAzulTran, $BolsaPlastico, $DescensorMRG, $CuerdaDesMRG,
							$CuerdasSeguridad, $PegatinaPrecinto, $BridaPrecinto, $EtiquetaExterior, $LibroInspecciones,
							$MaletinEmb, $SisEnvaseEmb, $BolsaPlasEmb, $SacaAzulEmb, $PegatinaPreEmb, $BridaPreEmb, $PreTornillTFreno,
							$GrosorPasTFreno, $EstMuelleTFreno, $EjePinonTFreno, $LimPinonTFreno, $ZonaSurcosTFreno, $ZonaLimpiaTFreno,
							$EstTornillTFreno, $TorLoctiteTFreno, $MarcasTornTFreno, $PreTornillTPolea, $HolguraEjeTPolea, $EstNerviosTPolea,
							$EstCarcasaTPolea, $EstTornillTPolea, $TorLoctiteTPolea, $MarcasTornTPolea, $PreTornillCFreno, $EstJuntaCFreno,
							$EstDientesCFreno, $RuedaLimpiaCFreno,$EstTornillCFreno, $TorLoctiteCFreno, $MarcasTornCFreno,
							$EstGenCuerdaPri, $EstProCuerdaPri, $LongitudCuerdaPri, $LongMedidaCuerdaPri, $MosquetonCuerdaPri,
							$EstGenCuerdaSeg, $SupSacaCuerdaSeg, $EstMosqueton, $FucMosqueton);
					}
					
					unset($IdTorre, $IdLinea);
				}  // Fin foreach Torre

				// Archivo Procesado
				// ODG, 20.08.14 El archivo procesado lo intento pasar a una carpeta de históricos, sino es posible moverlo
				//	lo borramos de data para que no haya problemas...
				if (!file_exists($Tmp="../data/historico"))
					mkdir($Tmp);
				if (!rename($File, $Tmp."/".$FileXML))
					unlink($File);

				// Archivo Original
				if (file_exists($File="../data/".substr($FileXML,0,21)."0.xml"))
					unlink($File);
			} // Fin si existe XML
		} // Fin foreach $FicherosXML
		
		// Buscamos a qué Zona y País pertenece el Parque
		$Zona = 1; $Pais = "ESPAÑA";
		$Query = "SELECT PA.Zona, PA.Nombre FROM Paises PA JOIN Parques ON PA.Id = Parques.Pais WHERE Parques.Id=".$IdParque;
		if (($Consulta = mysql_query($Query, $conn)))
		{
			if (($Tmp = mysql_fetch_array($Consulta))) {
				$Zona = $Tmp['Zona'];
				$Pais = $Tmp['Nombre'];
			}
			unset($Consulta, $Tmp);
		}
	
		// Precios Materiales, Revisiones	
		$PvpAdeCable = $PvpAdePletina = $PvpAdeSoporte = $PvpEngaste = $PvpCable = 0;
		$PvpVarillas = $PvpTuercas = $PvpArandelas = $PvpCartel = $PvpTensor = $PvpAprietacable = 0;
		$PvpGuardacabos = $PvpPiezaInferior = $PvpPiezaSuperior = $PvpAbsorbedor = $PvpPasador = $PvpBulon = 0;
		$PvpRevDesNacelle = $PvpRevDesNacelleT1 = $PvpRevDesGround = $PvpRevExtintor = 0;
		if (($Consulta = mysql_query("SELECT * FROM PvpMaterial", $conn)))
		{
			if (($Tmp = mysql_fetch_array($Consulta)))
			{   // Adecuación Cables
				if ($Tipo == 'M')
					$PvpAdeCable = $PvpAdeSoporte = $Tmp['MonSustituirCable'];
				else
				{
					$PvpAdeCable = ($Pais == "ESPAÑA") ? $Tmp['RevSustituirCable'] : $Tmp['RevSustituirCableT2'];
					$PvpAdeSoporte = ($Pais == "ESPAÑA") ? $Tmp['RevSustituirSoporteSup'] : $Tmp['RevSustituirSoporteSupT2'];
				}

				$PvpAdePletina = $Tmp['MonRigidizadores'];  // Adecuación Pletina
				$PvpVarillas   = $Tmp['VarillaRoscada'];
				$PvpTuercas    = $Tmp['Tuercas'];
				$PvpArandelas  = $Tmp['Arandelas'];
				$PvpCartel     = $Tmp['Cartel'];
				$PvpCable      = $Tmp['Cable'];
				$PvpTensor     = $Tmp['Tensor'];
				$PvpPasador    = $Tmp['Pasador'];
				$PvpBulon      = $Tmp['Bulon'];

				$PvpEngaste       = $Tmp['Engaste'];
				$PvpAbosorbedor   = $Tmp['Absorbedor'];
				$PvpGuardacabos   = $Tmp['Guardacabo'];
				$PvpAprietacable  = $Tmp['Aprietacable'];
				$PvpPiezaInferior = $Tmp['PiezaInferior'];
				$PvpPiezaSuperior = $Tmp['SoporteSuperior'];
				
				$PvpRevDesNacelle   = ($Pais == "ESPAÑA") ? $Tmp['RevEvacuadorNacelle'] : $Tmp['RevEvacuadorNacelleT2'];
				$PvpRevDesNacelleT1 = $Tmp['RevEvacuadorNacelleT1'];
				$PvpRevDesGround    = ($Pais == "ESPAÑA") ? $Tmp['RevEvacuadorGround'] : $Tmp['RevEvacuadorGroundT2'];
				$PvpRevExtintor     = $Tmp['RevInsExtintor'];
			}
			unset($Consulta, $Tmp);
		}

		// Si hay alguna ListaControl, creamos los Albaranes
		if ($ListaControl)
		{   // 1ro. Obtenemos el total de Torres Revisadas, en sí lo que tendré en cuenta es el Grupo de Precios
			$Where = "LC.Id IN (";
			foreach ($ListaControl as $IdLC)
				$Where .= $IdLC.",";
			$Where = substr($Where, 0, strlen($Where)-1).")";

			$GrupoPvp = $NomAEG = array();
			if (($result = mysql_query("SELECT MAX(IdGrupo) AS Num FROM TAerogenerador", $conn)))
			{
				if (($row = mysql_fetch_array($result)))
				{
					for ($Count = 0; $Count <= $row['Num']; $Count ++)
						$GrupoPvp[] = 0;
				}
				else
					$GrupoPvp = array(0,0,0,0,0,0);
			}
		
			$Query = "SELECT DISTINCT LC.IdLinea, TA.IdGrupo,TA.Prefijo,TA.Sufijo FROM ListaControl LC
				JOIN Lineas L ON L.Id = LC.IdLinea JOIN TAerogenerador TA ON TA.Id = L.TipoAerogenerador WHERE ";
			if (($result = mysql_query($Query.$Where, $conn)))
			{
				while ($row = mysql_fetch_array($result))
				{
					$Erroneo = false;
					$GrupoPvp[$row['IdGrupo']] += 1;
					foreach ($NomAEG as $Valor)
					{
						if ($Valor == trim($row['Prefijo']." ".$row['Sufijo'])) {
							$Erroneo = true;
							break;
						}
					}
					if (!$Erroneo)
						$NomAEG[] = trim($row['Prefijo']." ".$row['Sufijo']);
				}
				unset($result, $row);
			}

			// Buscamos el Nº de Operarios
			$NumOpe = 1;
			if (($Consulta = mysql_query("SELECT DISTINCT LC.IdTrabajador FROM ListaControl LC WHERE ".$Where, $conn)))
				$NumOpe = mysql_num_rows($Consulta);
			unset($Consulta);
		
			// Recuperamos las Listas de Control para crear el Albarán
			$Query = "SELECT LC.*, L.NumeroTorre AS Torre, L.TipoAerogenerador AS TipoAEG, L.IdMarca AS MarcaLV, LP.IdPletina, LP.NumeroSerie, TA.IdGrupo 
				FROM ListaControl LC JOIN Lineas L ON L.Id = LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea 
				JOIN Pletinas P ON P.Id=LP.IdPletina JOIN TAerogenerador TA ON TA.Id = L.TipoAerogenerador
				WHERE P.Tipo=LC.LTipo AND LC.Resultado != 2 AND ".$Where;
			if (($result = mysql_query($Query, $conn)))
			{
				$TxtRango = array ("", "1 a 5", "6 a 10", "11 a 20", "21 a 30", "más de 30");
				while ($row = mysql_fetch_array($result))
				{   // Sacamos los precios que dependen de Pletinas
					$PrecioPletina1 = $PrecioPletina2 = $PrecioPletina3 = $PrecioPletina4 = 0;
					$NombrePletina1 = $NombrePletina2 = $NombrePletina3 = $NombrePletina4 = "";
					if (($Consulta = mysql_query("SELECT * FROM Pletinas WHERE Id=".$row['IdPletina'], $conn)))
					{
						if (($Tmp = mysql_fetch_array($Consulta)))
						{
							$PrecioPletina1 = $Tmp['PrecioPletina1'];
							$PrecioPletina2 = $Tmp['PrecioPletina2'];
							$PrecioPletina3 = $Tmp['PrecioPletina3'];
							$PrecioPletina4 = $Tmp['PrecioPletina4'];

							$NombrePletina1 = $Tmp['Campo1'];
							$NombrePletina2 = $Tmp['Campo2'];
							$NombrePletina3 = $Tmp['Campo3'];
							$NombrePletina4 = $Tmp['Campo4'];
						}

						unset($Consulta, $Tmp);
					}

					// Obtengo el Precio de la Inspección según el Num. de AEG's,
					$Rango = 1;
					$precioins = fPrecioIns($GrupoPvp[$row['IdGrupo']], $NumOpe);

					// Textos Cabecera Albaranes
					if ($Tipo == 'M')
					{
						$TiposAEG = "";
						foreach ($NomAEG as $Valor)
							$TiposAEG .= $Valor.",";
	
						$CabTxtLV = "Revisión ".substr($TiposAEG,0,strlen($TiposAEG)-1)." por ".
							(($NumOpe > 1)?$NumOpe." operarios":"un operario")." entre ".$TxtRango[$Rango]." AEG";
					}
					$CabTxtMX = "Extra por recolocación de rigidizadores";
				
					// Tipo 0 - Línea Vida, 1 - Descensores, 2 - Extintores, 
					//	3 - Materiales, 4 - Trabajos, 5 - Desplazamientos, 6 - NO OK
					if (($IdLinea = $row['IdLinea']) != $TorreAnterior)
					{   // ODG, 30.09.13 en el caso de que una torre tenga más de una línea de Vida, antes de crear la cabecera del 
						//	Albarán con el valor del 1er Resultado debo de comprobar si todas las líneas están OK, ó NO OK, y en el caso de que
						//	una esté OK y otra NO OK, directamente es NO OK...
						$Resultado = $row['Resultado']; $Erroneo = false;
						$Query = "SELECT LC.* FROM ListaControl LC WHERE LC.IdLinea=".$IdLinea." AND LC.Resultado != 2 AND ".$Where;
						if (($ConTmp = mysql_query($Query, $conn))) 
						{   // ODG 03.10.13, Obtengo el total de pletinas de ésta revisión, para saber si debo añadir
							//	un texto supletorio de información a los materiales del albarán "si es Nacelle ó Servicio"
							// Además sea Montaje ó Revisión, debemos de comprobar si en un AEG hay una Adecuación de Cable y/o
							//	Adecuación Pletina y/o Suporte Superior, sólo se cobrará una de ellas y la más cara...
							$NumLV = mysql_num_rows($ConTmp);
							$HayAdecuacion = array (0,0,0);
							while ($RowTmp = mysql_fetch_array($ConTmp))
							{
								if ($RowTmp['Resultado'] == 0)
									$Resultado = 0;

								if ($RowTmp['EstadoCable'] != 1 && ($RowTmp['CantidadCable'] > 0))
									$HayAdecuacion[0] = $PvpAdeCable;
								if ($RowTmp['ConfPletina'] != 1 && ($RowTmp['CampoPletina1'] >= 2 || $RowTmp['CampoPletina2'] >= 2 || $RowTmp['CampoPletina3'] >= 2 || $RowTmp['CampoPletina4'] >= 2))
									$HayAdecuacion[1] = $PvpAdePletina;
								if ($RowTmp['AnclajeSuperior1AS'] > 2)
									$HayAdecuacion[2] = $PvpAdeSoporte;
							}
							unset ($ConTmp, $RowTmp);
						
							if ($HayAdecuacion[0] || $HayAdecuacion[1] || $HayAdecuacion[2])
							{   // En Revisión, si hay alguna adecuación de Cable, Adecuación de Pletinas ó Corrección Soporte Superior
								//	en alguna de las líneas de vida, ya tiene incluido el precio de Certificación
								if ($Tipo == 'R')
									$Erroneo =  true;

								// Ahora, busco la más cara de las Adecuaciones que es la que se facturará.
								$Pos = 0; $Valor = (!is_array($Maximo = max($HayAdecuacion))) ? $Maximo : $Maximo[0];
								foreach ($HayAdecuacion as $Tmp)
								{
									if ($Tmp == $Valor)
									{
										if ($Pos == 0) {			// Activo Adecuar sólo el Cable.
											$CableAdecuado = false; $PletinaAdecuada = $SoporteAdecuado = true;
										} else if ($Pos == 1) {	 // Activo Adecuar sólo la Pletina.
											$PletinaAdecuada = false; $CableAdecuado = $SoporteAdecuado = true;
										} else {					// Activo Adecuar sólo el Anclaje Superior.
										$SoporteAdecuado = false; $CableAdecuado = $PletinaAdecuada = true;
										}
										break;
									}
									else
										$Pos ++;
								}
							}
							unset ($HayAdecuacion);
						}

						if ($Resultado != 1 && $Tipo == 'M')
						{   // Creo la Cabecera del Albarán MX
							if (!$IdAlbMX)
								$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);

							// Las Inspecciones NO Ok, cuestan un 15% Menos
							$TipoDet = "6";
							$IdAlbaran = $IdAlbMX;
							$ConceptoAlbaran = "NO OK, ".(($row['Observaciones']!="")?$row['Observaciones']:$row['TrabajosPendientes']);
							$Precio = $precioins - ($precioins * 0.15);
						}
						else
						{
							if ($CanAEG != 0 && $Tipo == 'M')
							{   // En el caso de ser 2da Revisión, debo de crear MX con la diferencia de precios, en total
								//	se cobrará lo que corresponde a ésta revisión, pero dividido en 2 Albaranes
								if (!$IdAlbMX)
									$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
							
								$TipoDet = "5";
								$ConceptoAlbaran = "";
								$Precio = fPrecioIns($CanAEG, $CanOPE);	// Importe 1ra Revisión
								$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbMX."',IdTorre='".$row['Torre']."',Tipo='".$TipoDet."',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
									"',Unidades='".($Unidades = 1)."',Precio='".($PvpAux = ($precioins - $Precio))."',Importe='".fRound2Dec($Unidades*$PvpAux)."'";
								mysql_query($Query, $conn);
							}
							else
								$Precio = $precioins;

							// Creo la Cabecera del Albarán LV, si procede
							if (!$IdAlbLV)
								$IdAlbLV = fCreaCabAlb(($Tipo=='M')?"1":"3", $CabTxtLV);
							$IdAlbaran = $IdAlbLV;
							$ConceptoAlbaran = "Revisión de Líneas de Vida";
							$TipoDet = "0";
						}

						// Línea para añadir el Tipo de Inspección
						if (!$Erroneo) {
							mysql_query($Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='".$TipoDet."',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".($Unidades = 1)."',Precio='".$Precio."',Importe='".fRound2Dec($Unidades*$Precio)."'", $conn);
						}
					
						// ODG, 01.10.13 Gonzalo quiere que aparezca en los Albaranes la fecha en que se hizo la inspección,
						//	y debo contemplar línea a línea, a qué Albarán ha ido, esto sólo es problemático en montaje...
						// Marcamos las Lista de Control como Facturada
						$Query = "UPDATE ListaControl LC SET LC.IdAlbaran=".$IdAlbaran.
							" WHERE LC.IdLinea=".$row['IdLinea']." AND LC.IdControl=".$row['IdControl'];
						if (!mysql_query($Query, $conn))
							fDebug("../",mysql_error($conn),$Query);
	
						$TorreAnterior = $IdLinea;
					}

					// ODG, 03.10.13 En el caso de ser una inspección de un AEG con más de una LV, añadiré un texto para
					//	indicar a qué línea de vida pertenece el material ó el trabajo...
					$TxtLV = ($NumLV > 1) ? ($row['LTipo']==1?", Nacelle.":", Servicio."): "";
					// ODG, 21.05.13 Gonzalo comenta que los Albaranes MX sólo se crean en Montaje, en revisión todo va 
					//	en el mismo Albarán (Revisión Líneas de Vida, Extintores, Descensores, Materiales, Trabajos)
					if ($row['EstadoCable'] != 1 && $row['CantidadCable'] > 0)
					{
						if (!$CableAdecuado)
						{   // Introducimos la línea de Albarán, La Adecuación sólo se hará una vez por torre
							if ($Tipo == 'M')
							{
								if (!$IdAlbMX)
									$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
								$IdAlbaran = $IdAlbMX;
							}
							else
								$IdAlbaran = $IdAlbLV;

							$ConceptoAlbaran = "Sustitución de Cable".(!empty($row['EstadoCableMotivo']) ? " [".$row['EstadoCableMotivo']."]" : "");
							if ($Tipo == 'R')
								$ConceptoAlbaran .= " (incluida certificación Línea de Vida)";
							$Unidades = 1;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='4',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpAdeCable)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							if (mysql_query($Query, $conn))
								$CableAdecuado = true;
						}

						// ODG, 10.06.13 Gonzalo comenta que cuando se cambia un cable hay que cobrar el Engaste más los metros de cable utilizado
						$Texto = "Engaste de cable más guardacabos".$TxtLV;
						$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
							"',Unidades='".$Unidades."',Precio='".($Precio = $PvpEngaste)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
						mysql_query($Query, $conn);

						// El cable se cobra por metros, los tendremos en 'CantidadCable'
						$Texto = "Cable 8mm galvanizado".$TxtLV;
						$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
							"',Unidades='".($Unidades = $row['CantidadCable'])."',Precio='".($Precio = $PvpCable)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
						mysql_query($Query, $conn);
					}
				
					if ($row['ConfPletina'] != 1)
					{   // Introducimos la línea de Albarán, 
						//	Sólo en el caso de que se haya hecho algún trabajo, y la adecuación sólo se hará una vez por torre.
						if (($row['CampoPletina1'] >= 2 || $row['CampoPletina2'] >= 2 ||
							$row['CampoPletina3'] >= 2 || $row['CampoPletina4'] >= 2) && !$PletinaAdecuada)
						{
							if ($Tipo == 'M')
							{
								if (!$IdAlbMX)
									$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
								$IdAlbaran = $IdAlbMX;
								$ConceptoAlbaran = "Adecuación Pletinas";
							} else {
								$IdAlbaran = $IdAlbLV;
								$ConceptoAlbaran = "Retrofit de línea de vida (incluida certificación Línea de Vida)";
							}

							$Unidades = 1;
							$PletinaAdecuada = true;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='4',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpAdePletina)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}

						// ODG, 05.08.13 Gonzalo comenta que añadir pletinas no son Trabajos '4' sino Materiales '3'
						if ($row['CampoPletina1'] > 2)  // 2 - Colocar, >=3 - Añadir
						{   // Introducimos la línea de Albarán
							$ConceptoAlbaran = "Pletina ".$NombrePletina1.$TxtLV;
							$Unidades = $row['CampoPletina1']-2;
							$Precio = $PrecioPletina1;	// Sacamos el precio de la pletina por pletina
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".$Precio."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
				
						if ($row['CampoPletina2'] > 2)  // 2 - Colocar, 3 ó 4 - Añadir
						{   //Introducimos la linea de Albarán
							$ConceptoAlbaran = "Pletina ".$NombrePletina2.$TxtLV;
							$Unidades = $row['CampoPletina2']-2;
							$Precio = $PrecioPletina2;	// Sacamos el precio de la pletina por pletina
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".$Precio."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					
						if ($row['CampoPletina3'] > 2)  // 2 - Colocar, 3 ó 4 - Añadir
						{   //Introducimos la línea de Albarán
							$ConceptoAlbaran = "Pletina ".$NombrePletina3.$TxtLV;
							$Unidades = $row['CampoPletina3']-2;
							$Precio = $PrecioPletina3;	//sacamos el precio de la pletina por pletina
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".$Precio."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}

						if ($row['CampoPletina4'] > 2)  // 2 - Colocar, 3 ó 4 - Añadir
						{   //Introducimos la línea de Albarán
							$ConceptoAlbaran = "Pletina ".$NombrePletina4.$TxtLV;
							$Unidades = $row['CampoPletina4']-2;
							$Precio = $PrecioPletina4;	//sacamos el precio de la pletina por pletina
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".$Precio."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					}

					if ($row['VarillasRoscadas'] > 1)
					{   //Introducimos la linea de Albarán
						if ($Tipo == 'M')
						{
							if (!$IdAlbMX)
								$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
							$IdAlbaran = $IdAlbMX;
						}
						else
							$IdAlbaran = $IdAlbLV;

						$ConceptoAlbaran = "Varillas Roscadas".$TxtLV;
						$Unidades = $row['VarillasRoscadas']-1;
						$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
							"',Unidades='".$Unidades."',Precio='".($Precio = $PvpVarillas)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
						mysql_query($Query, $conn);

						$Unidades *= 2;	// Las Unidades de Tuercas y Arandelas es el doble de las Varillas...
						$ConceptoAlbaran = "Tuercas de Varillas Roscadas".$TxtLV;
						$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
							"',Unidades='".$Unidades."',Precio='".($Precio = $PvpTuercas)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
						mysql_query($Query, $conn);

						$ConceptoAlbaran = "Arandelas de Varillas Roscadas".$TxtLV;
						$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
							"',Unidades='".$Unidades."',Precio='".($Precio = $PvpArandelas)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
						mysql_query($Query, $conn);
					}

					if ($row['Cartel'] != 1)
					{   //Introducimos la linea de Albarán
						if ($Tipo == 'M')
						{
							if (!$IdAlbMX)
								$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
							$IdAlbaran = $IdAlbMX;
						}
						else
							$IdAlbaran = $IdAlbLV;

						$Unidades = 1;
						$ConceptoAlbaran = "Cartel Informativo".$TxtLV;
						$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
							"',Unidades='".$Unidades."',Precio='".($Precio = $PvpCartel)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
						mysql_query($Query, $conn);
					}

					if ($row['AnclajeInferior1'] != 1)
					{   //Introducimos la linea de Albarán por cada cosa que se añada
						if ($Tipo == 'M')
						{
							if (!$IdAlbMX)
								$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
							$IdAlbaran = $IdAlbMX;
						}
						else
							$IdAlbaran = $IdAlbLV;

						$Unidades = 1;
						$ConceptoAlbaran = ($row['MarcaLV'] == 2)?"Soporte Inferior":"Anclaje Inferior";
						if ($row['AnclajeInferior1AI'] == 3)		   // 2 - Colocar, 3 - Añadir
						{   // ODG, 05.08.13 Gonzalo comenta que esto debe de ir en Materiales '3' y no en Trabajos Extras '4'.
							$Texto = "Sustitución del ".$ConceptoAlbaran;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpPiezaInferior)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					
						if ($row['AnclajeInferior1Tensor'] >= 3)		// 2 - Colocar, 3 - Añadir
						{
							$Texto = (($row['MarcaLV']==2)?"Carcasa Exterior":"Tensor")." del ".$ConceptoAlbaran.$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpTensor)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}

						if ($row['AnclajeInferior1Perrillos'] >= 3)	// >= 3 - Añadir
						{
							$Texto = (($row['MarcaLV']==2)?"Tornillos":"Perrillos")." del ".$ConceptoAlbaran.$TxtLV;					
							$Unidades = $row['AnclajeInferior1Perrillos'] - 2;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpAprietacable)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					
						$Unidades = 1;
						if ($row['AnclajeInferior1Guardacabos'] >= 3)	// 2 - Colocar, 3 - Añadir
						{
							$Texto = (($row['MarcaLV']==2)?"Recogida Cable":"Guardacabos")." del ".$ConceptoAlbaran.$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpGuardacabos)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}

						if ($row['AnclajeInferior1Tuercas'] >= 3)		// 2 - Colocar, 3 - Añadir
						{
							$Texto = (($row['MarcaLV']==2)?"Indicador Tensión":"Tuercas")." del ".$ConceptoAlbaran.$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpTuercas)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					}
					
					if ($row['AnclajeSuperior1'] != 1)
					{   // Introducimos la linea de Albarán
						if ($Tipo == 'M')
						{
							if (!$IdAlbMX)
								$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
							$IdAlbaran = $IdAlbMX;
						}
						else
							$IdAlbaran = $IdAlbLV;

						$Unidades = 1;
						$ConceptoAlbaran = "Soporte Superior";
						if ($row['AnclajeSuperior1AS'] == 3)		// 2 - Colocar, 3 - Añadir
						{
							if (!$SoporteAdecuado)
							{   // Trabajos Extras
								$Texto = "Sustitución de ".$ConceptoAlbaran;
								if ($Tipo == 'R')
									$Texto .= " (incluida certificación Línea de Vida)";
								$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
									"',Unidades='".$Unidades."',Precio='".($Precio = $PvpAdeSoporte)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
								if (mysql_query($Query, $conn))
									$SoporteAdecuado = true;
							}
						
							// Material
							$Texto = $ConceptoAlbaran." (EG1)".$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='4',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpPiezaSuperior)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}

						if ($row['AnclajeSuperior1Pasador'] >= 3)	// 2 - Colocar, 3 - Añadir
						{
							$Texto = "Pasador del ".$ConceptoAlbaran.$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpPasador)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					
						if ($row['AnclajeSuperior1Bulon'] >= 3)		// 2 - Colocar, 3 - Añadir
						{
							$Texto = (($row['MarcaLV']==2)?"Pestañas":"Bulón")." del ".$ConceptoAlbaran.$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpBulon)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						}
					}

					if ($row['Amortiguador'] != 1)
					{   //Introducimos la linea de Albarán
						if ($Tipo == 'M')
						{
							if (!$IdAlbMX)
								$IdAlbMX = fCreaCabAlb("2", $CabTxtMX);
							$IdAlbaran = $IdAlbMX;
						}
						else
							$IdAlbaran = $IdAlbLV;
						
						$Unidades = 1;
						if ($row['MarcaLV']==2)
						{   // ODG, 05.01.15 VECTALINE no tiene 'Absorbedor de Energia', sólo 'Punto de Anclaje' con un sólo elemento
							$ConceptoAlbaran = "Punto de Anclaje".$TxtLV;
							$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$ConceptoAlbaran."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpAbsorbedor)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
							mysql_query($Query, $conn);
						} else {  // Por defecto, Se tratan como SECURIFIL...
							$ConceptoAlbaran = "Absorbedor de energía".$TxtLV;
							if ($row['AmortiguadorMuelle'] != 1)
							{
								$Texto = "Muelle ".$row['AmortiguadorMuelleMotivo']." de ".$ConceptoAlbaran;
								$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
									"',Unidades='".$Unidades."',Precio='".($Precio = $PvpAbsorbedor)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
								mysql_query($Query, $conn);
							}

							if ($row['AmortiguadorPasador'] != 1)
							{
								$Texto = "Pasador de ".$ConceptoAlbaran;
								$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
									"',Unidades='".$Unidades."',Precio='".($Precio = $PvpPasador)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
								mysql_query($Query, $conn);
							}

							if ($row['AmortiguadorBulon'] != 1)
							{
								$Texto = "Bulón de ".$ConceptoAlbaran;
								$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbaran."',IdTorre='".$row['Torre']."',Tipo='3',Concepto='".$Texto."',OT='".$row['OT'].
								"',Unidades='".$Unidades."',Precio='".($Precio = $PvpBulon)."',Importe='".fRound2Dec($Unidades*$Precio)."'";
								mysql_query($Query, $conn);
							}						
						}
					}
				} // Fin while ListaControl

				unset ($result, $row);
			}
		} // Fin if ListaControl

		// Si hay alguna ListaCtrlDes, creamos los Albaranes
		if ($ListaCtrlDes)
		{   // 1ro. Obtenemos el total de Torres Revisadas
			$Where = "LC.Id IN (";
			foreach ($ListaCtrlDes as $IdLC)
				$Where .= $IdLC.",";
			$Where = substr($Where, 0, strlen($Where)-1).")";

			$Query = "SELECT LC.*, L.NumeroTorre AS Torre FROM ListaCtrlDes LC
				JOIN Lineas L ON L.Id = LC.IdLinea WHERE ".$Where." AND LC.Estado != 2";
			if (($result = mysql_query($Query, $conn)))
			{   // Creo la Cabecera del Albarán LV
				if (!$IdAlbLV) {
					$NumOpe = 1;
					if (($Consulta = mysql_query("SELECT DISTINCT LC.IdTrabajador FROM ListaCtrlDes LC WHERE ".$Where, $conn)))
						$NumOpe = mysql_num_rows($Consulta);
					unset($Consulta);				
				
					$CabTxtLV = "Revisión Descensores por ".$NumOpe." operarios";
					$IdAlbLV = fCreaCabAlb(($Tipo=='M')?"1":"3", $CabTxtLV);
				}

				while ($row = mysql_fetch_array($result))
				{   // Dependiendo de la Ubicación del Descensor tiene un precio u otro...
					// 1 - Nacelle, 2 - Ground
					if ($row['Ubicacion'] == 1) {
						$Precio = ($ListaControl || $ListaCtrlExt) ? $PvpRevDesNacelleT1 : $PvpRevDesNacelle;
					} else {
						$Precio = ($ListaControl || $ListaCtrlExt) ? $PvpRevDesNacelleT1 : $PvpRevDesGround;
					}

					$Unidades = 1; // ODG, 06.09.13 la Revisión de descensor pertenece al grupo '1' y no al '4'
					$ConceptoAlbaran = "Revisión Descensor";
					$Query = "INSERT INTO AlbaranDET SET IdAlbaran='".$IdAlbLV."',IdTorre='".$row['Torre']."',Tipo='1', Concepto='".$ConceptoAlbaran."',".
						"OT='".$row['OT']."',Unidades='".$Unidades."',Precio='".$Precio."',Importe='".fRound2Dec($Unidades*$Precio)."'";
					mysql_query($Query, $conn);
				}
				unset($result, $row);

				// Marcamos las Listas de Control como Facturadas
				if (!mysql_query(($Query = "UPDATE ListaCtrlDes LC SET LC.IdAlbaran=".$IdAlbLV." WHERE ".$Where), $conn))
					fDebug("../",mysql_error($conn),$Query);
			}
			else
				fDebug("../",mysql_error($conn),$Query);
		}
	
		unset($CableAdecuado, $ListaControl, $ListaCtrlExt, $ListaCtrlDes, $TxtLV, $NumLV,
			$IdAlbLV, $IdAlbMX, $IdAlbaran, $TorreAnterior, $SoporteAdecuado, $FicherosXML);
	}		
} // Fin Confirmar
?>
<!DOCTYPE html>
<html>
<head>
 	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>:: Inspección de instalaciones técnicas ::</title>
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/jq.ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/ui.multiselect.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../css/general.css" />
    <script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
    <script src="../js/inc/functions.js" type="text/javascript"></script>
    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
	<style>
		.titulo {font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099;font-weight:bold;}
		.txtNum {text-align:right;}
		select {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;color:#475767;font-size:13px;}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();

			$("#Tipo").change(function() {
				if ($("#Tipo").val() == 'M')
					$("#gpoVisita").show();
				else
				{
					$("#gpoVisita").hide();
					$("input[name$=CanAEG]").val("0");
				}
				
				$.ajax({
					type: "POST", url:"../ajax/inspeccion/Tabla02MyRa.php",
					data: {"Tipo":$("#Tipo").val()},
					success: function(sData) {
						$("#Lista").html(sData);
					},
					dataType: "text", async:false
				});
			});
			
			$("#Tipo").val("<?php echo (isset($_REQUEST['Tipo'])) ? $_REQUEST['Tipo'] : "";?>").trigger("change");
			$(".txtAviso").fadeIn("slow").delay(1000).fadeOut(2000);
		});
	</script>
</head>
  <body>
	<div align=center style="margin-top:5px;padding-bottom:10px;">
		<FORM ID="Control" ACTION="ValidarCLMyRLV.php" METHOD="post">
			<table class="table" cellpadding=0 cellspacing=0 width=90%>
				<tr>
					<td height=30>
						<table width=100% cellpading=0 cellspacing=0>
							<tr>
								<td class="header_L">&nbsp;</td>
								<td class="header_C">
                                	<span class='header_title'>VALIDAR LISTAS DE CONTROL DE MONTAJE Y REVISIÓN</span>
                                </td>
								<td class="header_R">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
                <tr>
					<td valign=top style="padding:10px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;border-bottom:1px solid #dddddd;background-color:#ffffff;">                    
                      <table width=100% cellpading=0 cellspacing=0>
						<tr>
                          <td>
  						  	<SELECT NAME="Tipo" ID="Tipo" SIZE="1">
							  <OPTION VALUE="">Seleccione ...</OPTION>
							  <OPTION VALUE="M">Montaje</OPTION>
							  <OPTION VALUE="R">Revisión</OPTION>
							</SELECT>
                          </td>
                          <td align="right">
							<div id="gpoVisita"> 
                              <INPUT VALUE=1 TYPE="checkbox" NAME="chkVisita" onclick='AlternarVisualizacion("optVisita");' />
                              <LABEL for="chkVisita">2da&nbsp;Visita</LABEL>
		                      <div id="optVisita" style="margin-left:25px;float:right;display:none;">
                              	 <LABEL for="CanAEG">Nº&nbsp;AEG's</LABEL>
        		                 <INPUT TYPE="text" NAME="CanAEG" CLASS="txtNum" VALUE="0" size="3" maxlength="3" style="margin-left:5px;"/>
                              	 <LABEL for="CanOPE" "margin-left:5px;">Nº&nbsp;Operarios</LABEL>
        		                 <INPUT TYPE="text" NAME="CanOPE" CLASS="txtNum" VALUE="1" size="2" maxlength="2" style="margin-left:5px;"/>
                              </div>
                            </div>
                          </td>
                       </tr>
                      </table>
					</td>
                </tr>
				<tr>
					<td height=auto style="padding:5px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;border-bottom:1px solid #dddddd;background-color:#ffffff;">
						<div id="Lista" align=center style="padding-bottom:5px;"></div>
					</td>
				</tr>
			</table>
		</FORM>
		<span class="txtAviso"><?php echo (isset($mensaje))?$mensaje:"";?></span>
	</div> 
  </body>
</html>
<?php
if ($conn)
	mysql_close($conn);

// Crea la Cabecera de los Albaranes
function fCreaCabAlb($Tipo, $Texto)
{
	global $IdParque, $conn;
	$Valor = 0; $Fecha = date("Y-m-d"); $Anyo = substr($Fecha,2,2);
	$Query = "INSERT INTO AlbaranCAB (Tipo,NAlb,Anyo,IdParque,Fecha,CabTxt) VALUES (".$Tipo.",".fAsignaNum("NAlb", "AlbaranCAB", "Tipo=".$Tipo." AND Anyo=".$Anyo, $conn).
		",".$Anyo.",".$IdParque.",'".$Fecha."','".$Texto."')";
	if (mysql_query($Query, $conn))
		$Valor = mysql_insert_id();
	else
		fDebug("../",mysql_error($conn),$Query);

	return $Valor;
}

// Cálcula el Importe de la Inspección según los AEG's y Operarios
function fPrecioIns($AEGS, $NumOpe)
{   // Sacamos los Precios del Grupo de PVP del AEG's y según la cantidad que haya
	global $Tipo,$Pais,$Zona,$Rango, $row,$conn; $Importe = 0.0;
	
	if ($AEGS < 6)
		$Rango = 1;
	else if ($AEGS >=  6 && $AEGS <= 10)
		$Rango = 2;
	else if ($AEGS >= 11 && $AEGS <= 20)
		$Rango = 3;
	else if ($AEGS >= 21 && $AEGS <= 30)
		$Rango = 4;
	else if ($AEGS > 30)
		$Rango = 5;

	if ($Tipo == 'M')
		$Query = "SELECT * FROM PvpMontaje WHERE IdGrupo=".$row['IdGrupo']." AND Rango=".$Rango;
	else
		$Query = "SELECT * FROM PvpRevision WHERE IdGrupo=".$row['IdGrupo'];
	if (($Consulta = mysql_query($Query, $conn)))
	{   if (($Tmp = mysql_fetch_array($Consulta)))
		{   if ($Tipo == 'M')	// Precios Montaje
			{  if ($Pais == "ESPAÑA")
					$Importe = ($NumOpe == 1) ? $Tmp['PrecioT11'] : $Tmp['PrecioT12'];
				else
				{
					if ($Zona == 1)												   // Europa
						$Importe = ($NumOpe == 1) ? $Tmp['PrecioT13'] : $Tmp['PrecioT14'];
					else if ($Zona == 2 || $Zona == 3 || $Zona == 5 || $Zona == 6)	// América, Asía
						$Importe = ($NumOpe == 1) ? $Tmp['PrecioT15'] : $Tmp['PrecioT16'];
					else if ($Zona == 4)				  							  // África
						$Importe = ($NumOpe == 1) ? $Tmp['PrecioT17'] : $Tmp['PrecioT18'];
				}
			}
			else				// Precios Revisión
				$Importe = ($Pais == "ESPAÑA") ? $Tmp['CerLinea'] : $Tmp['CerLineaT2'];
		}
		unset($Consulta, $Tmp);
	}

	return $Importe;
}
?>