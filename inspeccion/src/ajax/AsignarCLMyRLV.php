<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../inc/function/funcs.php");
require_once("../db-config.php");
$_SESSION["mi_url"] = "Inspeccion/".fPageName();

$Parque    = (isset($_REQUEST['Parque'])) ? $_REQUEST['Parque'] : "";			// Cód. Parque
$Operario1 = (isset($_REQUEST['Operario1'])) ? $_REQUEST['Operario1'] : "";	  // Cód. Trabajador 1
$Operario2 = (isset($_REQUEST['Operario2'])) ? $_REQUEST['Operario2'] : "";	  // Cód. Trabajador 2
$Tablet    = (isset($_REQUEST['Tablet'])) ? $_REQUEST['Tablet'] : "1";		   // Cód. Tablet
$Tipo      = (isset($_REQUEST['Tipo'])) ? $_REQUEST['Tipo'] : "";		        // Tipo de Asignación

// Acción a Realizar
if (isset($_REQUEST['Borrar']))
{
	if (file_exists(($File = "../data/".$_REQUEST['Borrar'])))
		unlink($File);
}
else if (is_numeric($Parque))
{   // Comprobamos que se haya elegido al menos un Trabajador
	if (is_numeric($Operario1) || is_numeric($Operario2))
	{   // Extraemos las Torres que han sido Asignadas
		$Torres = "";
		foreach($_REQUEST as $Campo=>$Valor)
		{
			if (substr($Campo, 0, 3) == "AEG")
			{
				if ($Valor == 1)
			    	$Torres .= fQuitaZeros(substr($Campo, 3, 5)).",";
			}
		}
		$Torres .= "0";
	
		// Obtenemos el Nombre del Parque
		$Nombre = $Cliente = "";
		if (($result = mysql_query("SELECT Nombre, Cliente FROM Parques WHERE Id=".$Parque, $conn)))
		{
			if (($row = mysql_fetch_assoc($result))) {
				$Nombre = $row['Nombre'];
				$Cliente = $row['Cliente'];
			}		
			unset($row, $result);
		}

		// Cargamos la estructura del xml, Página de Código UTF8
		$xml = new DOMDocument("1.0", "utf-8");
		$raiz = $xml->appendChild($xml->createElement("Parque"));
		
		// Control, Éste campo lo usaremos para controlar si se ha validado éste fichero...
		$raiz->appendChild($xml->createElement("Control", 
			$Tipo.sprintf("%05d",$Parque).sprintf("%05d",$Tablet).date('dmy')));
		// Nom. Parque
		$raiz->appendChild($xml->createElement("Nombre", $Nombre));
		// Cliente Parque
		$raiz->appendChild($xml->createElement("Cliente", $Cliente));
		// Trabajadores Asignados
		$raiz->appendChild($xml->createElement("Operario1", ($Operario1 != "")?$Operario1:$Operario2));
		$raiz->appendChild($xml->createElement("Operario2", ($Operario1 != "")?$Operario2:""));
		unset($Nombre);

		// Nodo Alturas
		$NodoAux = $raiz->appendChild($xml->createElement("Alturas"));
		if (($result = mysql_query("SELECT * FROM Alturas", $conn)))
		{
			while($row = mysql_fetch_array($result)) {
				$NodoAux->appendChild(($aux = $xml->createElement("Nombre", $row['Nombre'])));
				$aux->setAttribute("id", $row['Id']);
			}
			unset($result, $row);
		}
		
		// ODG, 05.01.15 Nodo Marcas Líneas de Vida
		if (($LineasVida=(isset($_REQUEST['LineasVida']))?true:false) == true)
		{
			$NodoAux = $raiz->appendChild($xml->createElement("MarcaLin"));
			if (($result = mysql_query("SELECT * FROM MarcaLin", $conn)))
			{
				while($row = mysql_fetch_array($result)) {
					$NodoAux->appendChild(($aux = $xml->createElement("Nombre", $row['Nombre'])));
					$aux->setAttribute("id", $row['Id']);
				}
				unset($result, $row);
			}
		}

		// Nodo Marcas Y Modelos Extintores
		if (($Extintores=(isset($_REQUEST['Extintores']))?true:false) == true)
		{
			$NodoAux = $raiz->appendChild($xml->createElement("MarcaExt"));
			if (($result = mysql_query("SELECT * FROM MarcaExt", $conn)))
			{
				while($row = mysql_fetch_array($result)) {
					$NodoAux->appendChild(($aux = $xml->createElement("Nombre", $row['Nombre'])));
					$aux->setAttribute("id", $row['Id']);
				}
				unset($result, $row);
			}
		
			$NodoAux = $raiz->appendChild($xml->createElement("ModeloExt"));
			if (($result = mysql_query("SELECT * FROM ModeloExt", $conn)))
			{
				while($row = mysql_fetch_array($result)) {
					$NodoAux->appendChild(($aux = $xml->createElement("Nombre", $row['Nombre'])));
					$aux->setAttribute("id", $row['Id']);
				}
				unset($result, $row);
			}
			unset($aux, $NodoAux);
		}
		
		// Nodo Marcas Y Modelos Descensores
		if (($Descensores=(isset($_REQUEST['Descensor']))?true:false) == true)
		{
			$NodoAux = $raiz->appendChild($xml->createElement("MarcaDes"));
			if (($result = mysql_query("SELECT * FROM MarcaDes", $conn)))
			{
				while($row = mysql_fetch_array($result)) {
					$NodoAux->appendChild(($aux = $xml->createElement("Nombre", $row['Nombre'])));
					$aux->setAttribute("id", $row['Id']);
				}
				unset($result, $row);
			}
			
			$NodoAux = $raiz->appendChild($xml->createElement("ModeloDes"));
			if (($result = mysql_query("SELECT * FROM ModeloDes", $conn)))
			{
				while($row = mysql_fetch_array($result)) {
					$NodoAux->appendChild(($aux = $xml->createElement("Nombre", $row['Nombre'])));
					$aux->setAttribute("id", $row['Id']);
					$aux->setAttribute("marca", $row['IdMarca']);
				}
				unset($result, $row);
			}
			unset($aux, $NodoAux);
		}

		// Grabamos las Torres que han sido Asignadas
		for ($Paso = 1; $Paso <= ((isset($_REQUEST['NoAsignada']))?2:1); $Paso ++)
		{
			$Query = "SELECT L.Id AS IdLinea, L.NumeroTorre AS IdTorre, TA.Id AS IdAerogenerador,
				TA.Nombre AS NombreAEG, TA.Extintores, A.Nombre AS NomAltura, L.TipoAerogeneradorGAMESA, L.IdMarca
				FROM Lineas L JOIN Alturas A ON A.Id=L.IdAltura JOIN TAerogenerador TA ON TA.Id=L.TipoAerogenerador WHERE L.IdParque=".$Parque;
			$Query .= " AND L.Id ".(($Paso == 1)?"IN":"NOT IN")." ($Torres) ORDER BY L.NumeroTorre ASC";
			
			if (($result = mysql_query($Query, $conn)))
			{   // Grupo de Torres
				while($row = mysql_fetch_array($result))
				{   // Grupo Torre
					$NodoTorre = $raiz->appendChild($xml->createElement("Torre"));
					$NodoTorre->appendChild($xml->createElement("Id", $row['IdLinea']));			// Id Línea Torre.
					$NodoTorre->appendChild($xml->createElement("Asignada", ($Paso == 1)?1:0));	 // Torre Asignada (1 - Si, 0 - No)
					$NodoTorre->appendChild($xml->createElement("NumeroTorre", $row['IdTorre']));  // Nº Torre.
					$NodoTorre->appendChild($xml->createElement("Altura", $row['NomAltura']));	  // Altura
					$NodoTorre->appendChild($xml->createElement("IdTipoAEG", $row['IdAerogenerador']));			  // Id AEG
					$NodoTorre->appendChild($xml->createElement("TipoAerogenerador", $row['NombreAEG']));			// Nombre AEG
					$NodoTorre->appendChild($xml->createElement("TipoAegGAMESA", $row['TipoAerogeneradorGAMESA'])); // Nombre GAMESA

					// Grupo Líneas de Vida (Pletinas)
					//	Si hay que chequear línas de vida y además en ésta torre se debe de realizar creamos el grupo
					$txtId = sprintf("%05s", $row['IdLinea']);
					if ($LineasVida && isset($_REQUEST['ChkLV'.$txtId]))
					{
						$Query = "SELECT LP.Id, LP.NumeroSerie, LP.NumeroCable, LP.Id AS IdPletina, LP.NTrompa, LP.NAbsorbedor, 
							LP.NTramo, p.Tipo AS TipoLinea, p.Gamesa AS Nombre, p.Campo1, p.Campo2, p.Campo3, p.Campo4
							FROM LineasPletina AS LP INNER JOIN Pletinas AS p ON p.Id = LP.IdPletina WHERE LP.IdLinea=".$row["IdLinea"]." ORDER BY p.Tipo DESC;";
						if (($Pletinas = mysql_query($Query, $conn)))
						{
							while($row3 = mysql_fetch_array($Pletinas))
							{   // Líneas de Vida
								$NodoLinea = $NodoTorre->appendChild($xml->createElement("Linea"));
								$NodoLinea->appendChild($xml->createElement("OT",                         // Orden Trabajo 
									(isset($_REQUEST['OrdLV'.$txtId]))?$_REQUEST['OrdLV'.$txtId]:""));
								$NodoLinea->appendChild($xml->createElement("Fecha"));                	 // Fecha 
								$NodoLinea->appendChild($xml->createElement("IdTorre", $row['IdLinea'])); // Id Torre 
								$NodoLinea->appendChild($xml->createElement("IdMarca", $row['IdMarca'])); // Id Marca, OD 31.03.14

								$NodoLinea->appendChild($xml->createElement("Cable", $row3['NumeroCable']));
								$NodoLinea->appendChild($xml->createElement("Serie", $row3['NumeroSerie']));
								$NodoLinea->appendChild($xml->createElement("Absorbedor", $row3['NAbsorbedor']));
								$NodoLinea->appendChild($xml->createElement("Trompa", $row3['NTrompa']));
								$NodoLinea->appendChild($xml->createElement("Tramo", $row3['NTramo']));
						
								$NodoLinea->appendChild($xml->createElement("IdPletina", $row3['IdPletina']));
								$NodoLinea->appendChild($xml->createElement("TipoPletina", $row3['Nombre']));	   // Nombre GAMESA 	
								$NodoLinea->appendChild($xml->createElement("TipoDeLinea", $row3['TipoLinea']));	// 1 Nacelle, 2 Servicio
								$NodoLinea->appendChild($xml->createElement("NombrePletina1", $row3['Campo1']));
								$NodoLinea->appendChild($xml->createElement("NombrePletina2", $row3['Campo2']));
								$NodoLinea->appendChild($xml->createElement("NombrePletina3", $row3['Campo3']));
								//$NodoLinea->appendChild($xml->createElement("NombrePletina4", $row3['Campo4']));
						
								// * * Creamos los nodos vacios para la Revisión * *
								$NodoRevision = $NodoLinea->appendChild($xml->createElement("Revision"));
								$NodoRevision->appendChild($xml->createElement("Resultado", "1"));
								$NodoRevision->appendChild($xml->createElement("EstadoCable", "1"));
								$NodoRevision->appendChild($xml->createElement("CantidadCable", "0"));
								$NodoRevision->appendChild($xml->createElement("EstadoCableMotivo"));
								
								$NodoRevision->appendChild($xml->createElement("Tension", "1"));
								$NodoRevision->appendChild($xml->createElement("ConfPletina", "1"));
								$NodoRevision->appendChild($xml->createElement("CampoPletina1", "1"));	// 0 - NO OK, 1 - OK, 2 - Colocar, 3 y 4 - Añadir								
								$NodoRevision->appendChild($xml->createElement("CampoPletina2", "1"));
								$NodoRevision->appendChild($xml->createElement("CampoPletina3", "1"));
								//$NodoRevision->appendChild($xml->createElement("CampoPletina4", "1"));
								
								$NodoRevision->appendChild($xml->createElement("VarillasRoscadas", "1"));
								$NodoRevision->appendChild($xml->createElement("Cartel", "1"));					   // 0 - NO OK, 1 - OK
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1", "1"));			 // 0 - NO OK, 1 - OK
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1AI", "1")); 	  	   // 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1AIMotivo"));	 	  // Texto para Colocar/Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1Tensor", "1")); 	   // 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1Perrillos", "1"));	// 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añade '1', 4 - Añade '2', 5 - Añade '3'
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1Guardacabos", "1")); // 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeInferior1Tuercas", "1"));	  // 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								
								$NodoRevision->appendChild($xml->createElement("AnclajeSuperior1", "1"));			 // 0 - NO OK, 1 - OK
								$NodoRevision->appendChild($xml->createElement("AnclajeSuperior1AS", "1"));		   // 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeSuperior1ASMotivo"));	  	  // Texto para Colocar/Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeSuperior2", "1"));			 // 1 - Delantero, 2 - Trasero, 3 - Centrado
								$NodoRevision->appendChild($xml->createElement("AnclajeSuperior1Pasador", "1"));	  // 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								$NodoRevision->appendChild($xml->createElement("AnclajeSuperior1Bulon", "1"));		// 0 - NO OK, 1 - OK, 2 - Colocar, 3 - Añadir
								
								$NodoRevision->appendChild($xml->createElement("Amortiguador", "1"));
								$NodoRevision->appendChild($xml->createElement("AmortiguadorMuelle", "1"));
								$NodoRevision->appendChild($xml->createElement("AmortiguadorMuelleMotivo"));
								$NodoRevision->appendChild($xml->createElement("AmortiguadorPasador", "1"));
								$NodoRevision->appendChild($xml->createElement("AmortiguadorBulon", "1"));
								$NodoRevision->appendChild($xml->createElement("TornilleriaPletina", "1"));
								$NodoRevision->appendChild($xml->createElement("TornilleriaApriete", "1"));
								$NodoRevision->appendChild($xml->createElement("Ensayo", "1"));
								$NodoRevision->appendChild($xml->createElement("Escalera", "1"));
								$NodoRevision->appendChild($xml->createElement("Interferencia", "1"));
								$NodoRevision->appendChild($xml->createElement("Oxidacion", "1"));
								$NodoRevision->appendChild($xml->createElement("Observaciones"));
								$NodoRevision->appendChild($xml->createElement("TrabajosPendientes"));

								unset($NodoRevision, $NodoLinea);
							}  // Fin Grupo Pletinas Linea
						
							unset($Pletinas, $row3);
						}  // Fin Pletinas
					} // Fin Líneas de Vida
					
					// * * * Extintores y Descensores * * *
					//	Si hay que chequear extintores y además en ésta torre se debe de realizar creamos el grupo
					if ($Extintores && isset($_REQUEST['ChkEX'.$txtId]))
					{   // Dependiendo del AEG tendremos X Extintores
						$Query = "SELECT LE.*, MarcaExt.Nombre AS NomMarca, ModeloExt.Nombre AS NomModelo, Localizacion.Nombre AS NomLocal 
							FROM LineasExtintor LE JOIN MarcaExt ON MarcaExt.Id=LE.Marca JOIN ModeloExt ON ModeloExt.Id=LE.Modelo
							JOIN Localizacion ON Localizacion.Id=LE.Localizacion WHERE LE.IdLinea=".$row['IdLinea'];
						if (($Consulta = mysql_query($Query, $conn)))
						{
							if (mysql_num_rows($Consulta) == 0)
							{   // Sino hay Extintores, los damos de alta...
								for ($nExt=1;$nExt <= $row['Extintores']; $nExt ++)
									mysql_query("INSERT INTO LineasExtintor (IdLinea, Localizacion, AgenteExtintor) VALUES (".$row['IdLinea'].",'".(($nExt > 3)?3:$nExt)."','CO2')", $conn);
								unset($nExt);
								$Consulta = mysql_query($Query, $conn);
							}

							while($Tmp = mysql_fetch_array($Consulta))
							{
								$NodoExtintor = $NodoTorre->appendChild($xml->createElement("Extintor"));
								$NodoExtintor->appendChild($xml->createElement("OT", 	// Orden de Trabajo
									(isset($_REQUEST['OrdEX'.$txtId]))?$_REQUEST['OrdEX'.$txtId]:""));
								$NodoExtintor->appendChild($xml->createElement("Fecha"));
								$NodoExtintor->appendChild($xml->createElement("IdTorre", $row['IdLinea']));
								$NodoExtintor->appendChild($xml->createElement("IdExtintor", $Tmp['Id']));

								$NodoExtintor->appendChild($xml->createElement("Localizacion", $Tmp['NomLocal']));
								$NodoExtintor->appendChild($xml->createElement("Placa", $Tmp['NPlaca']));
								$NodoExtintor->appendChild($xml->createElement("Marca", $Tmp['NomMarca']));
								$NodoExtintor->appendChild($xml->createElement("Modelo", $Tmp['NomModelo']));
								$NodoExtintor->appendChild($xml->createElement("FechaFabricacion", $Tmp['FechaFabricacion']));
								$NodoExtintor->appendChild($xml->createElement("FechaRetimbrado", $Tmp['FechaRetimbrado']));
								$NodoExtintor->appendChild($xml->createElement("AgenteExtintor", $Tmp['AgenteExtintor']));
								
								$NodoExtintor->appendChild($xml->createElement("PesoAgExtintor"));
								$NodoExtintor->appendChild($xml->createElement("Colocacion"));	// 1 - Colgado, 2 - Suelo
								$NodoExtintor->appendChild($xml->createElement("Movido"));
								$NodoExtintor->appendChild($xml->createElement("Sustituido"));	// 0 - No, 1 - Sustituido, 2 - Sustitución.
								$NodoExtintor->appendChild($xml->createElement("PlacaSustitucion"));
								$NodoExtintor->appendChild($xml->createElement("PrecintoSustitucion"));
								$NodoExtintor->appendChild($xml->createElement("CartelLu", "1"));
								$NodoExtintor->appendChild($xml->createElement("PegatinaCarac", "1"));
								$NodoExtintor->appendChild($xml->createElement("PegatinaRevi", "1"));
								$NodoExtintor->appendChild($xml->createElement("MarcadoCE", "1"));
								$NodoExtintor->appendChild($xml->createElement("PrecintoRetimbrado", "1"));
								$NodoExtintor->appendChild($xml->createElement("EstadoCuerpo", "1"));
								$NodoExtintor->appendChild($xml->createElement("EstadoCabeza", "1"));
								$NodoExtintor->appendChild($xml->createElement("Pasador", "1"));
								$NodoExtintor->appendChild($xml->createElement("Valvula", "1"));
								$NodoExtintor->appendChild($xml->createElement("Manguera", "1"));
								$NodoExtintor->appendChild($xml->createElement("Soporte", "1"));
								$NodoExtintor->appendChild($xml->createElement("Junta", "1"));
								$NodoExtintor->appendChild($xml->createElement("Materiales", ""));
								$NodoExtintor->appendChild($xml->createElement("EstadoExt", "1"));
								$NodoExtintor->appendChild($xml->createElement("FaltaPeso"));
								$NodoExtintor->appendChild($xml->createElement("Caducidad"));
								$NodoExtintor->appendChild($xml->createElement("Otra"));
								$NodoExtintor->appendChild($xml->createElement("ObservacionesExt"));

								unset($NodoExtintor);
							}
						
							unset($Consulta, $Tmp);
						}
					}
					
					// Si hay que chequear descensores y además en ésta torre se debe de realizar creamos el grupo	
					if ($Descensores && isset($_REQUEST['ChkDE'.$txtId]))
					{
						$Query = "SELECT DES.*, A.Nombre AS NomMarca, B.Nombre AS NomModelo FROM LineasDescensor DES 
							JOIN MarcaDes A ON A.Id=DES.Marca JOIN ModeloDes B ON B.Id=DES.Modelo WHERE DES.IdLinea=".$row['IdLinea'];
						if (($Consulta = mysql_query($Query, $conn)))
						{
							if (mysql_num_rows($Consulta) == 0)
							{   // Sino hay Descensores, los damos de alta...
								mysql_query("INSERT INTO LineasDescensor (IdLinea) VALUES (".$row['IdLinea'].")", $conn);								
								$Consulta = mysql_query($Query, $conn);
							}

							while($Tmp = mysql_fetch_array($Consulta))
							{
								$NodoDescensor = $NodoTorre->appendChild($xml->createElement("Descensor"));
								$NodoDescensor->appendChild($xml->createElement("OT", 	// Orden Trabajo
									(isset($_REQUEST['OrdDE'.$txtId]))?$_REQUEST['OrdDE'.$txtId]:""));
								$NodoDescensor->appendChild($xml->createElement("Fecha"));
								$NodoDescensor->appendChild($xml->createElement("IdTorre", $row['IdLinea']));
								$NodoDescensor->appendChild($xml->createElement("IdDescensor", $Tmp['Id']));
								
								$NodoDescensor->appendChild($xml->createElement("NSerie", $Tmp['NSerie']));
								$NodoDescensor->appendChild($xml->createElement("Fabricante", $Tmp['NomMarca']));
								$NodoDescensor->appendChild($xml->createElement("ModeloDes", $Tmp['NomModelo']));
								$NodoDescensor->appendChild($xml->createElement("Longitud", $Tmp['Longitud']));
								$NodoDescensor->appendChild($xml->createElement("PrecintoViejo", $Tmp['NPrecintoOld']));
								$NodoDescensor->appendChild($xml->createElement("PrecintoNuevo", $Tmp['NPrecintoNew']));
								$NodoDescensor->appendChild($xml->createElement("Envasado", $Tmp['TEnvasado']));
								$NodoDescensor->appendChild($xml->createElement("AnyoFabricacion", $Tmp['AnyoFabricacion']));
								$NodoDescensor->appendChild($xml->createElement("Ubicacion"));	// 1 - Nacelle, 2 - Ground

								if ($Tmp['Marca'] == 2)	// MITTELMANN
								{
									$NodoDescensor->appendChild($xml->createElement("MaletinEqu", "1"));
									$NodoDescensor->appendChild($xml->createElement("SisEnvaseEqu", "2"));
									$NodoDescensor->appendChild($xml->createElement("SacaAzulTran", "1"));
									$NodoDescensor->appendChild($xml->createElement("BolsaPlastico", "1"));
									$NodoDescensor->appendChild($xml->createElement("DescensorMRG", "1"));
									$NodoDescensor->appendChild($xml->createElement("CuerdaDesMRG", "1"));
									$NodoDescensor->appendChild($xml->createElement("CuerdasSeguridad", "1"));
									$NodoDescensor->appendChild($xml->createElement("PegatinaPrecinto", "1"));
									$NodoDescensor->appendChild($xml->createElement("BridaPrecinto", "1"));
									$NodoDescensor->appendChild($xml->createElement("EtiquetaExterior", "1"));
									$NodoDescensor->appendChild($xml->createElement("LibroInspecciones", "1"));
									$NodoDescensor->appendChild($xml->createElement("MaletinEmb", "1"));
									$NodoDescensor->appendChild($xml->createElement("SisEnvaseEmb", "2"));
									$NodoDescensor->appendChild($xml->createElement("BolsaPlasEmb", "1"));
									$NodoDescensor->appendChild($xml->createElement("SacaAzulEmb", "1"));
									$NodoDescensor->appendChild($xml->createElement("PegatinaPreEmb", "1"));
									$NodoDescensor->appendChild($xml->createElement("BridaPreEmb", "1"));
									$NodoDescensor->appendChild($xml->createElement("PreTornillTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("GrosorPasTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstMuelleTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("EjePinonTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("LimPinonTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("ZonaSurcosTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("ZonaLimpiaTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstTornillTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("TorLoctiteTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("MarcasTornTFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("PreTornillTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("HolguraEjeTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstNerviosTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstCarcasaTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstTornillTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("TorLoctiteTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("MarcasTornTPolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("PreTornillCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstJuntaCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstDientesCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("RuedaLimpiaCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstTornillCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("TorLoctiteCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("MarcasTornCFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("DeslizamientoCuerda", "1"));	// GlissadeCuerda
									$NodoDescensor->appendChild($xml->createElement("EstGenCuerdaPri", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstProCuerdaPri", "1"));
									$NodoDescensor->appendChild($xml->createElement("LongitudCuerdaPri", "1"));
									$NodoDescensor->appendChild($xml->createElement("LongMedidaCuerdaPri", "1"));
									$NodoDescensor->appendChild($xml->createElement("MosquetonCuerdaPri", "1"));
									$NodoDescensor->appendChild($xml->createElement("AnyoFabCuerdaPri", $Tmp['AnyoFabCuerdaPri']));
									$NodoDescensor->appendChild($xml->createElement("EstGenCuerdaSeg", "1"));
									$NodoDescensor->appendChild($xml->createElement("SupSacaCuerdaSeg", "1"));
									$NodoDescensor->appendChild($xml->createElement("NSerieCuerdaSeg1", $Tmp['NSerieSeguridad']));
									$NodoDescensor->appendChild($xml->createElement("AnyoFabCuerdaSeg1", $Tmp['AnyoFabCuerdaSeg']));
									$NodoDescensor->appendChild($xml->createElement("NSerieCuerdaSeg2", $Tmp['NSerieSeguridad2']));
									$NodoDescensor->appendChild($xml->createElement("AnyoFabCuerdaSeg2", $Tmp['AnyoFabCuerdaSeg2']));
									$NodoDescensor->appendChild($xml->createElement("EstMosqueton", "1"));
									$NodoDescensor->appendChild($xml->createElement("FucMosqueton", "1"));
								}
								else 		// Por defecto siempre PSA
								{
									$NodoDescensor->appendChild($xml->createElement("Bolsa", "1"));
									$NodoDescensor->appendChild($xml->createElement("Sellado", "1"));
									$NodoDescensor->appendChild($xml->createElement("NumeroSello", "1"));
									$NodoDescensor->appendChild($xml->createElement("DescensorAG", "1"));
									$NodoDescensor->appendChild($xml->createElement("CaboAnclaje", "1"));
									$NodoDescensor->appendChild($xml->createElement("Humedad", "1"));
									$NodoDescensor->appendChild($xml->createElement("EtiquetaLegible", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstadoCarcasa", "1"));
									$NodoDescensor->appendChild($xml->createElement("CuerdaEntrada", "1"));
									$NodoDescensor->appendChild($xml->createElement("CuerdaSalida", "1"));
									$NodoDescensor->appendChild($xml->createElement("MosquetonArgolla", "1"));

									$NodoDescensor->appendChild($xml->createElement("NecesarioAbrir"));
									$NodoDescensor->appendChild($xml->createElement("RuedaDentada", "1"));
									$NodoDescensor->appendChild($xml->createElement("Dientes", "1"));
									$NodoDescensor->appendChild($xml->createElement("PoleaCuerda", "1"));
									$NodoDescensor->appendChild($xml->createElement("SuperficiePolea", "1"));
									$NodoDescensor->appendChild($xml->createElement("CajaFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("UnidadFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("ProfundidadFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("GuarnicionFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("ZapatasFreno", "1"));
									$NodoDescensor->appendChild($xml->createElement("ControlMuelle", "1"));
									$NodoDescensor->appendChild($xml->createElement("FlancosArbol", "1"));
									$NodoDescensor->appendChild($xml->createElement("PuntosApoyo", "1"));
						
									$NodoDescensor->appendChild($xml->createElement("EstadoCuerda", "1"));
									$NodoDescensor->appendChild($xml->createElement("FinDeCuerda", "1"));
									$NodoDescensor->appendChild($xml->createElement("Termoretractil", "1"));
									$NodoDescensor->appendChild($xml->createElement("AnyoFabCuerdaPri", $Tmp['AnyoFabCuerdaPri']));
									$NodoDescensor->appendChild($xml->createElement("EstMosquetonPri", "1"));
									$NodoDescensor->appendChild($xml->createElement("EstCuerdaSeguridad", "1"));
									$NodoDescensor->appendChild($xml->createElement("MosquetonSeguridad", "1"));
									$NodoDescensor->appendChild($xml->createElement("NSerieSeguridad", $Tmp['NSerieSeguridad']));
									$NodoDescensor->appendChild($xml->createElement("AnyoFabCuerdaSeg", $Tmp['AnyoFabCuerdaSeg']));
									$NodoDescensor->appendChild($xml->createElement("DeslizamientoCuerda", "1"));
									$NodoDescensor->appendChild($xml->createElement("CargaMinima", "1"));
									$NodoDescensor->appendChild($xml->createElement("VainaCuerda", "1"));
									$NodoDescensor->appendChild($xml->createElement("Mordazas", "1"));
								}								
								$NodoDescensor->appendChild($xml->createElement("EstadoDes", "1"));
						
								// Creamos las Líneas de Materiales
								for ($nMat = 1; $nMat < 5; $nMat ++) {									
									$NodoDescensor->appendChild($xml->createElement("Material".($Tmp = sprintf("%02d",$nMat))));
									$NodoDescensor->appendChild($xml->createElement("Motivo".$Tmp));
									$NodoDescensor->appendChild($xml->createElement("Cantidad".$Tmp, "0"));
								}

								unset($aux, $nMat, $Tmp, $NodoDescensor);
							}
						}
					} // Fin Grupo Descensor
				} // Fin while Torres
			
				unset($result, $row);
			}  // Fin Líneas (Torres)
		}  // Fin for Paso

		// ODG, 12.03.13 se cambía el Formato del XML (F PPPPPPPPPP TTTTTTTTTT 0.xml)
		$FileXml = "../data/".$Tipo.sprintf('%010s',$Parque).sprintf("%010s",$Tablet)."0.xml";
		if (($File = fopen($FileXml,"w")))
		{
			fputs($File, $xml->saveXML());
			fclose($File);

			unset($FileXml, $File, $xml);
		}
	
		$mensaje = "Parque asignado correctamente";
	}
	else
		$mensaje = "Debe seleccionar a un Trabajador";
}
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
   	<script src="../js/jquery/jq.searchabledropdown-min.js" type="text/javascript"></script>
    <script src="../js/inc/functions.js" type="text/javascript"></script>
    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
	<style>
		.titulo{font-family:Calibri, Arial, sans-serif; font-size:22px; color:#000099; font-weight:bold;}
		.txtLV {background-color:#CFF}
		.txtEX {background-color:#CFC}
		.txtDE {background-color:#FC9}
		select {font-size:13px;color:#475767;}
		input  {font-family: Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:12px;color:#475767;}
		input[type="text"]:focus, textarea:focus {background-color: #FFC;}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();

			$("#Validar").button({
				icons: { primary: "ui-icon-check" }
			});
			
			// Tipo Asignación
			$("#Tipo").change(function() {
				// Cargo la Lista de Archivos con AJAX
				$.ajax({
					type: "POST", url:"../ajax/inspeccion/Tabla01MyRa.php",
					data: {"Tipo":$("#Tipo").val()},
					success: function(sData) {
						$("#Lista").html(sData);
					},
					dataType: "text", async:false
				});
			});
			// Parque
			$("#Parque").change(function() {
				if ($("#Assign").length)
					fCargaTorres();
			});
			
			// Al asignar una Revisión debemos primeramente Seleccionar las Torres que vamos a Revisar
			$("#Validar").click(function(event) {
				event.preventDefault();
				// Cargamos las Torres del Parque
				if (fValidarForm())
					fCargaTorres();
			});

			// Hago que en los Combos se pueda buscar si se escribe en ellos
			$("#Parque, #Operario1, #Operario2").searchable();
			$("#Tipo").val("<?php echo $Tipo;?>").trigger("change");
			$("#Parque").val("<?php echo $Parque;?>");
			$(".txtAviso").fadeIn("slow").delay(1000).fadeOut(2000);
		});

		function fValidarForm()
		{
			var nRet = false;

			if ($("#Tipo").val() == "")
				jAlert("Debe seleccionar el Tipo de Asignación", "Atención", $("#Tipo"));
			else if ($("#Parque").val() == "")
				jAlert("Debe seleccionar el Parque", "Atención", $("#Parque"));
			else if ($("#Operario1").val() == "" && $("#Operario2").val() == "")
				jAlert("Debe seleccionar a un Trabajador", "Atención", $("#Operario1"));
			else if ($("#Operario1").val() == $("#Operario2").val())
				jAlert("Debe seleccionar distintos trabajadores ó sólo uno", "Atención", $("#Operario2"));
			else
				nRet = true;

			return nRet;
		}
		
		function fCargaTorres()
		{
			$.ajax({
				type: "POST", url:"../ajax/inspeccion/Tabla01MyRc.php",
				data: {"Tipo":$("#Tipo").val(), "Parque":$("#Parque").val()},
				success: function(sData) {
					$("#Lista").html(sData);
				},
				dataType: "text", async:false
			});
		}
	</script>
</head>
  <body>	
	<div align="center" style="margin-top:5px;padding-bottom:10px;">
		<FORM ID="Control" ACTION="AsignarCLMyRLV.php" METHOD="post">
			<table class="table" cellpadding=0 cellspacing=0 width="95%">
				<tr>
					<td height=30>
						<table cellpading=0 cellspacing=0>
							<tr>
								<td class="header_L">&nbsp;</td>
								<td class="header_C">
                                	<span class='header_title'>ASIGNAR LISTAS DE CONTROL DE MONTAJE Y REVISIÓN</span>
                                </td>
								<td class="header_R">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>		
				<tr>
					<td valign=top style="padding:5px;border-left:1px solid #ddd;border-right:1px solid #ddd;border-bottom:1px solid #ddd;background-color:#fff;">
						<table width="100%" class='table' cellpadding=0 cellspacing=0 bgcolor='#fff'>
							<tr>
                                <td>
									<SELECT NAME="Tipo" ID="Tipo" SIZE="1">
                                    	<OPTION VALUE="">Seleccione...</OPTION>
                                    	<OPTION VALUE="M">Montaje</OPTION>
                                        <OPTION VALUE="R">Revisión</OPTION>
									</SELECT>
                                </td>
								<td style="padding-left:10px;">&nbsp;Parque&nbsp;:&nbsp;</td>
								<td>
									<SELECT NAME="Parque" ID="Parque" SIZE=1> 
										<OPTION VALUE=''>Seleccione un parque...</OPTION>
										<?php
										if (($result = mysql_query("SELECT * FROM Parques ORDER BY Nombre ASC;",$conn)))
										{   // Selecciono la Lista de Parques
											while($row = mysql_fetch_row($result))
												echo "<OPTION ".(($Parque == $row[0]) ? "SELECTED" : "")." VALUE='".$row[0]."'>".$row[1]."</OPTION>";
										}
										?>
									</SELECT>
								</td>
								<td style="padding-left:10px;">Trabajadores&nbsp;:</td>
								<td style="padding-left:5px;">
									<SELECT NAME="Operario1" ID="Operario1" SIZE=1>
                                    	<OPTION VALUE=''>Seleccione un trabajador...</OPTION> 
										<?php
										if (($result = mysql_query("SELECT * FROM Trabajadores WHERE Nivel < 5 ORDER BY Nombre ASC;",$conn)))
										{   // Selecciono la Lista de Trabajadores
											while($row = mysql_fetch_row($result))
												echo "<OPTION ".(($Operario1 == $row[0]) ? "SELECTED" : "")." VALUE='".$row[0]."'>".$row[1]."</OPTION>";
										}
										?>
									</SELECT>
                                    <br/>
									<SELECT NAME="Operario2" ID="Operario2" SIZE=1>
                                    	<OPTION VALUE=''>Seleccione un trabajador...</OPTION> 
										<?php
										if (($result = mysql_query("SELECT * FROM Trabajadores WHERE Nivel < 5 ORDER BY Nombre ASC;",$conn)))
										{   // Selecciono la Lista de Trabajadores
											while($row = mysql_fetch_row($result))
												echo "<OPTION ".(($Operario2 == $row[0]) ? "SELECTED" : "")." VALUE='".$row[0]."'>".$row[1]."</OPTION>";
										}
										?>
									</SELECT>
								</td>
								<td style="padding-left:5px;">
                               		Tablet&nbsp;:&nbsp;
                                </td>
								<td>
                                	<INPUT type="text" id="Tablet" name="Tablet" maxlength="5" value="1" style="width:40px;text-align:right;"/>
                                </td>
								<td style="padding-left:15px;">
                                    <BUTTON id="Validar">Validar</BUTTON>
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
?>