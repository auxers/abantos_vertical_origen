<?php
// Función LOG's
function fDebug($raiz, $texto, $valor)
{
	if (($fp=fopen($raiz."debug/debug.txt","a")))
	{
		fwrite($fp, "Hora =>".strftime("%d/%m/%Y %H:%M:%S", time()).PHP_EOL);
		fwrite($fp, utf8_decode($texto).": ".utf8_decode($valor).PHP_EOL);
		fclose($fp);
	}
}
// Función Redondear a 2 Decimales
function fRound2Dec($valor)
{
	return round($valor * 100) / 100;
}
// Eliminamos los '0' que hay a la izquierda de un Numero
function fQuitaZeros($Num)
{	
	if (is_numeric($Valor=$Num))	
	{   
		for ($Pos = 0; $Pos < ($Len = strlen($Num)); $Pos ++)
		{
			if (substr($Num, $Pos, 1) != '0') {
				$Valor = substr($Num, $Pos, $Len);
				break;
			}
		}
	}
	
	return $Valor;
}
// Asigna el siguiente Numero disponible en una tabla
function fAsignaNum($Campo, $Tabla, $Cmd, $ObjCon)
{
	$Valor = 1;
	$Query = "SELECT MAX(".$Campo.") AS nIndice FROM ".$Tabla;
	if ($Cmd != "")
		$Query .= " WHERE ".$Cmd;
	if (($result = mysql_query($Query, $ObjCon)))
	{   // Al Asignar un Nº siempre será + 1
		if (($row = mysql_fetch_array($result)))
			$Valor = (is_numeric($row["nIndice"])) ? $row["nIndice"]+1:1;
	}

	return $Valor;
}
// Cambia de Y-M-D a D/M/Y
function fFechaDMY($Fecha)
{
	$Fecha = ($Fecha != "") ? str_replace("-","/", $Fecha) : "1970/01/01";
	$sYMD = explode("/", $Fecha);
	return $sYMD[2]."/".sprintf("%02s",$sYMD[1])."/".sprintf("%02s",$sYMD[0]);
}
// Cambia de D/M/Y a Y-M-D
function fFechaYMD($Fecha)
{
	$Fecha = ($Fecha != "") ? str_replace("-","/", $Fecha) : "01/01/1970";
	$sYMD = explode("/", $Fecha);
	return $sYMD[2]."-".sprintf("%02s",$sYMD[1])."-".sprintf("%02s",$sYMD[0]);
}
// Obtiene el Array de los Literales del grupo que le indiquemos y el idioma...
function fGetLiterales($Tipo, $Grupo, $Idioma, $ObjCon)
{
	$Valor = false;
	if ($Idioma != 0)
		$Query = "SELECT Texto FROM Literales WHERE Tipo=".$Tipo." AND Grupo=".$Grupo." AND Idioma=".$Idioma;
	else
		$Query = "SELECT Texto FROM LiteralPie WHERE Tipo=".$Tipo." AND Grupo=".$Grupo;
	if (($result = mysql_query($Query, $ObjCon))) {
		while ($row = mysql_fetch_array($result))
			$Valor[] = $row["Texto"];
	}
	
	if (!$Valor)
	{   // Textos predeterminados en Castellano
		if ($Idioma != 0)
		{	
			if ($Tipo == 1)
			{   // Listas Control
				$Valor = array("LISTA DE CONTROL PARA EL MONTAJE/REVISIÓN ANUAL DE LÍNEAS DE VIDA\nSECURIFIL VERTICAL Y SISTEMA DE ANCLAJE",
					"Parque : ","Fecha : ","Nº Torre : ","Nº Trompa : ","Nº Cable : ", "Nº Línea : ", "Nº Absorbedor : ",
					"MONTAJE","Tipo de Aerogenerador : ","Nº Tramo : ","REVISIÓN ANUAL","Resultado de la verificación positiva (OK) - Resultado de la verificación negativa (NO OK)",
					"Estado del Cable : ","cable corto, aplastamientos, deshilachamientos, oxidación, cortado","Tensión del cable 60 dna < T < 100 dna",
					"Configuración correcta pletinas","Colocar ","Añadir ","TRABAJOS REALIZADOS","Varillas roscadas","Faltan : ",
					"Cartel Informativo","Anclaje inferior : ","Tensor, Perrillos, Guardacabos, Tuercas","Anclaje superior : ",
					"Pasador, Bulón","Anclaje superior : Tipo ","Delantero, Trasero","Comprobar configuración de la tornillería de las pletinas rigidizadoras",
					"Comprobar apriete de la tornillería de las pletinas rigidizadoras, pieza inferior y absorbedor",
					"Ensayo funcionamiento del anticaidas SOMAIN SKC H04 por el cable",
					"Configuración correcta de la escalera y estado - últimos 10 peldaños",
					"Interferencia con cables de potencia, cables de electricidad de luminarias, etc",
					"Estado del amortiguador : ","Muelle, Tuercas, Golpes, Bulón, Pasador",
					"Estado de oxidación de los elementos que no son aluminio"," Observaciones :",
					"Resultado : ","Trabajos Pendientes : ","Trabajadores : ", "Firma : (firma y sello empresa)");
			} 
			else if ($Tipo == 2)
			{   // Descensores
				if ($Grupo == 2) {
					$Valor = array("REGISTRO DE INSPECCIÓN\nRevisión descensor de Emergencia",
						"Parque :", "Cliente :", "Nº Torre :", "Altura :", "Fecha :", "Técnicos :", "Nº de O.T. :",
						"LISTA DE CHEQUEO DE DESENSOR DE EMERGENCIA", "Nº Serie", "Fabricante", "Longitud",
						"Nº Precinto Viejo", "Nº Precinto Nuevo", "Año\nFabricación", "Ubicación", "Tipo\nEnvasado",
						"1. Composición del Equipo",
						"1.1 Maletin Metálico.", "1.2 Sistema de envase y lacrado.",
						"1.3 Saca azúl de transporte.", "1.4 Bolsa de plástico.",
						"1.5 Descensor MRG-9 + 1 mosquetón.", "1.6 Cuerda descensor + 2 mosquetones.",
						"1.7 Cuerdas de seguridad de1-2m + mosquetón.", "1.8 Pegatina de precinto.",
						"1.9 Brida de precinto.", "1.10 Etiqueta identificativa exterior.",
						"1.11 Libro de inspecciones.",				 					
						"2. Inspección de los Embalajes",
						"2.1 Maletín metálico en perfecto estado.", "2.2 Sistema de envase y lacrado en perfecto estado.",
						"2.3 Bolsa de plático interior en perfecto estado.", "2.4 Saca azúl en perfecto estado.",
						"2.5 Colocada pegatina de precinto.", "2.6 Colocada brida precinto.",
						"3. Descensor",
						"3.1. Tapa de Freno",
						"3.1.1 Correcto precintado inicial de los tornillos.", "3.1.2 Grosor de las pastillas de freno (>=6 mm).",
						"3.1.3 Estado del muelle / clavijas.", "3.1.4 Estado de los dientes del eje del piñón.",
						"3.1.5 Limpieza y engrase del eje del piñon.", "3.1.6 Zona de freno sin surcos ni estrías.",				
						"3.1.7 Zona de freno limpia y desengrasada.", "3.1.8 Estado de los tornillos.",
						"3.1.9 Tornillos con loctite 246 antes del apriete.", "3.1.10 Colocadas marcas en tornillos.",
						"3.2. Tapa de acceso a la Polea",
						"3.2.1 Correcto precintado inicial de los tornillos.", "3.2.2 Holguras del eje de polea.",

						"3.2.3 Estado de los nervios de la polea.", "3.2.4 Estado de la carcasa de cuerda.", 					
						"3.2.5 Estado de los Tornillos.", "3.2.6 Tornillos con loctite 246 antes del apriete.",
						"3.2.7 Colocadas marcas en tornillos.",
						"3.3. Carcasa de Freno",
						"3.3.1 Correcto precintado inicial de los tornillos.", "3.3.2 Estado de la junta de la carcasa.",
						"3.3.3 Estado de los dientes de la rueda.", "3.3.4 Rueda dentada limpia y engrasada.",
						"3.3.5 Estado de los Tornillos.", "3.3.6 Tornillos con loctite 246 antes del apriete.",
						"3.3.7 Colocadas marcas en tornillos.",
						"3.4. Deslizamiento de la cuerda",
						"3.4.1 Deslizamiento correcto de la cuerda unos 3m para cada ladon (sin apreciar ruidos extraños).", 
						"4. Cuerda Principal",
						"4.1 Estado General (nudos, pegotes, cortes, etc).", "4.2 Estado del protector termo retractil.",
						"4.3 Logitud Cuerda.", "4.4 Longitud de la cuerda = medida de la torre.",
						"4.5 Mosquetón encima de los 3 metros de cuerda.", "4.6 Año fabricación de la cuerda.",
						"5. Cuerda Seguridad",
						"5.1 Estado visual de las cuerdas.", "5.2 Cuerdas en la parte superior de la saca.",
						"5.3 Nº Serie de las cuerdas.", "5.4 Años de fabricación de las cuerdas.",
						"6. Mosquetones",
						"6.1 Estado visual de los elementos.", "6.2 Correcto funcionamiento.",
						"Material Colocado", "Motivo", "Unidades", "APTO PARA SU USO", "Firmado :", "SI,NO");
				} else  {	// Por defecto, PSA
					$Valor = array("REGISTRO DE INSPECCIÓN\nEquipo de Descenso",
						"Parque :", "Cliente :", "Nº Torre :", "Altura :", "Fecha :", "Técnicos :", "Nº de O.T. :",
						"LISTA DE CHEQUEO DE DESENSOR DE EMERGENCIA", "Nº Serie", "Fabricante", "Longitud",
						"Nº Precinto Viejo", "Nº Precinto Nuevo", "Año\nFabricación", "Ubicación", "Tipo\nEnvasado",
						"1. Bolsa del dispositivo y componentes",
						"1.1 Bolsa del dispositivo sin daños.", "1.2 Sellado y Lacrado completo.",
						"1.3 Numeración del sello legible y completa.", "1.4 Descensor AG10K + Mosquetón.",
						"1.5 Cablo de Anclaje + un Mosquetón.", "1.6 Libre de Humedad/Corrosión.",
						"2. Inspección AG10K",
						"2.1 Etiqueta indentificativa legible.", "2.2 Estado de la Carcasa.",
						"2.3 Desgaste de la zona de entrada de la cuerda.", "2.4 Desgaste de la zona de salida de la cuerda.",
						"2.5 Estado Mosquetón y Argolla.", "* NECESARIO ABRIR", "SI", "NO",
						"En caso negativo, los siguientes puntos del apartado 2 no se aplican.",
						"2.1 Inspección visual de la rueda dentada y de la polea de cuerda",
						"2.1.1 Inspección visual de la rueda dentada", "2.1.2 Dientes sin roturas ni fisuras",
						"2.1.3 Inspección visual de la polea de cuerda", "2.1.4 Superficie de la polea (abrasión/desgaste)",
						"2.2 Control / Limpieza de la unidad y caja de freno",
						"2.2.1 Control de la caja de freno (corrosión)", "2.2.2 Control de la unidad de freno (corrosión)",
						"2.2.3 Profundidad estrías caja de freno > 2mm", "2.2.4 Guarnición del freno < 31,5mm",
						"2.2.5 Marcha sueve zapatas freno", "2.2.6 Control del muelle", "2.2.7 Flancos del diente del árbol del piñón",
						"2.2.8 Puntos de apoyo de la carcasa", "3. Cuerda Principal",
						"3.1 Estado general (cortes, nudos, decoloración).", "3.2 Fin de cuerda (nudo ó cosido).",
						"3.3 Protector termoretráctil.", "3.4 Año de fabricación de la cuerda.", "3.5 Estado mosquetón.",
						"4. Cuerda Seguridad", "4.1 Estado General.", "4.2 Mosquetón.", "4.3 Número de serie cuerda.",
						"4.4 Año de fabricación de la cuerda.", "5. Comprobación del freno",
						"5.1 Deslizamiento correcto de cuerda.", "5.2 Comprobar funcionamiento carga mínima 30kg.",
						"6. Vaina de sujeción de la cuerda",
						"6.1 Estado general (fisuras,golpes,roturas).", "6.2 Mordazas de sujección.",
						"Material Colocado", "Motivo", "Unidades", "APTO PARA SU USO", "Firmado :");
				}
			}
			else if ($Tipo == 3)
			{   // Extintores
				$Valor = array("REVISIÓN DE EXTINTORES EN AEROGENERADORES",
					"Parque : ", "Fecha : ", "Nº Torre :", "Localización : ", "GROUND, NACELLE, SUBESTACIÓN, OTRA",
					"Nº PLACA :", "COLOCACIÓN :", "MARCA EXTINTOR :", "MODELO :", "MOVIDO A :", "FECHA FABRICACIÓN :",
					"ULTIMO RETIMBRADO :", "SUSTITUÍDO :", "SI", "NO", "AGENTE EXTINTOR :", "OTRO :",
					"Nº PLACA EXTINTOR SUSTITUCIÓN :", "PESO AGENTE EXTINTOR :", "PRESENCIA PRECINTO RETIMBRADO :",
					"Cartel Luminiscente", "Estado Cuerpo Extintor", "Pegatinas Características Uso",
					"Estado Cabeza", "Pegatina Revisión Anual", "Pasador", "Marcado CE > 2002", "Válvula",
					"Precinto Retimbrado", "Manguera", "Junta RACCORD", "Soporte",
					"MATERIALES COLOCADOS", "ESTADO", "CAUSA", "FIRMA DE LOS TÉCNICOS",
					"Falta Peso", "Caducidad", "Otra : ", "OBSERVACIONES");
			}
			else if ($Tipo == 4)
			{   // Certificados LV
				$Valor = array('Abantos Vertical S.L. certifica que el conjunto de la línea de seguridad en altura SOMAIN SECURIFIL y su soporte superior, fabricados por Somain Securité, está conforme a los puntos críticos determinados por el procedimiento de verificación de Somain Securité, en cumplimiento a las exigencias recogidas en la norma EN 365:2004 "Equipos de protección individual sobre caídas en altura. Requisitos generales mínimos para las instrucciones de uso, mantenimiento, revisión periódica, reparación, marcado y embalaje" y a los requerimientos recogidos tanto por el fabricante, Somain Securité, como por el Organismo Notificado que ha validado el diseño mediante el correspondiente examen CE de tipo y que estos se encuentran libres de defectos, siendo correcto su estado e instalación.',
					'CLIENTE: GAMESA EÓLICA', 'CERTIFICADO DE CORRECTO ESTADO - INSTALACIÓN DE SISTEMAS DE SEGURIDAD VERTICAL', 'DENOMINACIÓN PRODUCTO: SECURIFIL VERTICAL',
					'FABRICANTE: SOMAIN', 'DISTRIBUIDOR: PROINLOSA', 'PARQUE EÓLICO:', 'HISTÓRICO DE REVISIONES PERIÓDICAS, MANTENIMIENTO Y REPARACIONES',
					'Fecha', 'Torre', 'Tipo de AEG', 'Nº de serie', 'Nº de cable', 'Línea de acceso a nacelle', 'Tipo de Línea', 'Acceso', 'Servicio',
					'Próxima revisión', 'CONFIGURACIÓN DE RIGIDIZADORES:', 'MATERIAL: CABLE GALVANIZADO (7x19) DE 8MM DE DIÁMETRO', 'Nº MAXIMO DE USUARIOS: 2',
					'ANTICAIDAS: SOMAIN SKC H04',
					'* La manipulación o mal uso de cualquier componente, conexión o instrucción especificada por el fabricante eximirá a Abantos vertical de cualquier responsabilidad',
					'* El distribuidor o fabricante debe entregar el manual de uso y especificaciones necesarias de la línea de seguridad',
					'* La verificación se ha realizado siguiendo las instrucciones de revisión suministradas por los fabricantes',
					'EMPRESA CERTIFICADORA:', 'SELLO NOMBRE Y FIRMA DE LOS TÉCNICOS', 'DATOS DEL FABRICANTE:');
			}
			else if ($Tipo == 5)
			{   // Certificados DE, ODG 28.02.14, Se añaden 3 literales más
				if ($Grupo == 2) {  // MITTELMANN
					$Valor = array('Abantos Vertical S.L. certifica que los descensores Emergencia MR69 / MR9HUB de la firma Mittlelmann, están conforme a los puntos determinados por el procedimiento de verificación "INSTRUCCIONES DE COMPROBACIÓN PARA PERITOS DEL APARATO DE SALVAMENTO DE DESCENSO MEDIANTE CUERDA MRG9/MRG9 HUB 2ª edicíón" editado por Mittelmann Sicherheitstechnik GmbH & Co. KG"',
						'CLIENTE: GAMESA EÓLICA', 'CERTIFICADO DE CORRECTO ESTADO - EVACUADOR MRG 9/MRG 9 HUB', 'DENOMINACIÓN PRODUCTO: DESCENSOR EMERGENCIA MRG-9',
						'FABRICANTE: MITTELMANN', 'PARQUE EÓLICO:', 'HISTÓRICO DE REVISIONES PERIÓDICAS, MANTENIMIENTO Y REPARACIONES',
						'Fecha', 'Torre', 'Tipo de AEG', 'Nº de serie del descensor', 'Año fabricación de la cuerda','Fabricante', 'Modelo', 'Cuerda de Seguridad','Nº de Serie','Año Fabricación','Próxima revisión', 
						'Nº MAXIMO DE USUARIOS: 2','* La manipulación o mal uso de cualquier componente, conexión o instrucción especificada por el fabricante eximirá a Abantos vertical de cualquier responsabilidad.',
						'* El distribuidor o fabricante debe entregar el manual de uso y especificaciones necesarias para el uso del evacuador.',
						'* La verificación se ha realizado siguiendo las instrucciones de revisión suministradas por MITTELMANN "INSTRUCCIONES DE COMPROBACIÓN PARA PERITOS APARATO DE SALVAMENTO DE DESCENSO MEDIANTE CUERDA MRG9/MRG9 HUB 2ª edición"',
						'EMPRESA CERTIFICADORA:', 'SELLO NOMBRE Y FIRMA DE LOS TÉCNICOS', 'DATOS DEL FABRICANTE:');
				} else {			// Por defecto, PSA
					$Valor = array('Abantos Vertical S.L. certifica que los descensores de Emergencia Modelo AG10 KT de la firma PSA, están conforme a los puntos determinados por el punto 11 de las "Instrucciones para el control visual para los aparatos de salvamento de descenso por cuerda tipo AG10 KT"',
						'CLIENTE: GAMESA EÓLICA', 'CERTIFICADO DE CORRECTO ESTADO - EVACUADOR PSA', 'DENOMINACIÓN PRODUCTO: DESCENSOR EMERGENCIA AG10 KT',
						'FABRICANTE: PSA', 'PARQUE EÓLICO:', 'HISTÓRICO DE REVISIONES PERIÓDICAS, MANTENIMIENTO Y REPARACIONES',
						'Fecha', 'Torre', 'Tipo de AEG', 'Nº de serie del descensor', 'Año fabricación de la cuerda','Fabricante', 'Modelo', 'Cuerda de Seguridad','Nº de Serie','Año Fabricación','Próxima revisión', 
						'Nº MAXIMO DE USUARIOS: 2','* La manipulación o mal uso de cualquier componente, conexión o instrucción especificada por el fabricante eximirá a Abantos vertical de cualquier responsabilidad',
						'* El distribuidor o fabricante debe entregar el manual de uso y especificaciones necesarias para el uso del evacuador',
						'* La verificación se ha realizado siguiendo el punto 11 del manual de manejo del Descensor de Emergencia AG10 KT de PSA',
						'EMPRESA CERTIFICADORA:', 'SELLO NOMBRE Y FIRMA DE LOS TÉCNICOS', 'DATOS DEL FABRICANTE:');
				}
			}
			else if ($Tipo == 7)
			{   // ODG, 05.01.15 Literales para la Tablet Virtual en Líneas de Vida
				if ($Grupo == 2) {  // VECTALINE
					$Valor = array("Nº Tramo", "Nº Abs/Serie", "Nº Sop. Inferior", "Nº Sop. Superior", "", "Estado del Cable", "Cantidad Cable",
						"Tensión", "Pletina","Varillas Roscadas","Cartel Informativo", 
						"Soporte Inferior","Soporte Inferior","Carcasa Exterior","Tornillo","Recogida Cable","Indicador Tensión", 
						"Soporte Superior","Soporte Superior","Dañado","Pestañas consumidas", "Punto Anclaje","Aprite Tornillería","Daños","",
						"Tornillería de las pletinas","Apriete de la tornillería","Ensayo SKC BLOCK","Últimos 10 peldaños","Interferencías","Oxidación");
				} else {			// Por defecto, SECURIFIL VERTICAL
					$Valor = array("Nº Cable", "Nº Serie", "Nº Absorbedor", "Nº Trompa", "Nº Tramo", "Estado del Cable", "Cantidad Cable",
						"Tensión", "Pletina","Varillas Roscadas","Cartel Informativo", 
						"Anclaje Inferior","Anclaje Inferior","Tensor","Perrillos","Guardacabos","Tuercas", 
						"Anclaje Superior","Anclaje Superior","Pasador","Bulón", "Amortigüador","Muelle","Pasador","Bulón",
						"Tornillería de las pletinas","Apriete de la tornillería","Ensayo SKC H04","Últimos 10 peldaños","Interferencías","Oxidación");
				}
			}
		}
		else
			$Valor = array ("","","","");
	}

	return $Valor;
}
// Crea la cabecera del Grupo del CheckList de Descensores
//	Si en $Tipo viene una "A" significa que los Check irán dentro del grupo de encabezado
function fCreaCabGrupo($Texto, $posY, $ObjPDF, $Tipo = "L")
{
	$ObjPDF->SetXY(7.5, $posY);
	$ObjPDF->Cell (200,5,$Texto,1,0,"L",true);
	$posY += (strpos($Tipo,"A") === false)?7.5:3.5;

	// Check Columna 1
	if (strpos($Tipo,"L") !== false)
	{
		$ObjPDF->Text (84,$posY,"Ok");
		$ObjPDF->Text (90,$posY,"No Ok");
		$ObjPDF->Text (101,$posY,"N/A");
	}
	// Check Columna 2
	if (strpos($Tipo,"R") !== false)
	{
		$ObjPDF->Text (184,$posY,"Ok");
		$ObjPDF->Text (190,$posY,"No Ok");
		$ObjPDF->Text (201,$posY,"N/A");
	}
	
	return (strpos($Tipo,"A") !== false)?$posY += 1:$posY;
}
// Crea la Lína del Grupo del Checklist de Descensores	
function fCreaLinGrupo($Texto, $Valor, $posX, $posY, $ObjPDF, $Tipo = 0)
{   // Check Columna 1
	$ObjPDF->Text ($posX, $posY, $Texto);
	if ($Tipo == 3 || $Tipo == 4)
		$posX = 110;

	if ($Tipo == 1 || $Tipo == 3)
	{   // Check
		$ObjPDF->SetFillColor(0);
		$ObjPDF->Rect ($posX+75,$posY-2.5,2.5,2.5,($Valor == 1)?"F":"");
		$ObjPDF->Rect ($posX+84,$posY-2.5,2.5,2.5,($Valor == 0)?"F":"");
		$ObjPDF->Rect ($posX+92,$posY-2.5,2.5,2.5,($Valor == 2)?"F":"");
	}
	else if ($Tipo == 2 || $Tipo == 4)
	{   // Texto
		$ObjPDF->SetFillColor(185,185,45);
		$ObjPDF->SetXY($posX+70, $posY-3);
		$ObjPDF->Cell (25,3.5, $Valor,0,0,"L",true);
	}
}
// Busca ó Añade a la tabla el/por el campo Nombre
function fBuscaTabla($Tabla, $Nombre, $Cmd, $ObjCon)
{
	$Valor = 1;
	if (!empty($Nombre))
	{
		$Query = "SELECT Id FROM ".$Tabla." WHERE Nombre='".$Nombre."'";
		if ($Cmd != "")
			$Query .= " AND ".$Cmd;
		if (($Consulta = mysql_query($Query, $ObjCon)))
		{
			if (mysql_num_rows($Consulta) == 0)
			{
				$Query = "INSERT INTO ".$Tabla." SET Nombre='".$Nombre."'";
				if ($Cmd != "")
					$Query .= ",".$Cmd;
				if (mysql_query($Query, $ObjCon))
					$Valor = mysql_insert_id();
			}
			else if (($Tmp = mysql_fetch_array($Consulta)))
				$Valor = $Tmp['Id'];
		}
	}

	return $Valor;
}
// Devuelve el valor del campo de la tabla que le indiquemos
function fBuscaDato($Campo, $Tabla, $Cmd, $ObjCon)
{
	$Valor = 0;
	if (($Consulta = mysql_query("SELECT ".$Campo." FROM ".$Tabla." WHERE ".$Cmd, $ObjCon)))
	{
		if (($Tmp = mysql_fetch_row($Consulta)))
			$Valor = ($Campo == "Id")? ((is_numeric($Tmp[0]))?$Tmp[0]:0) : $Tmp[0];
	}

	return $Valor;
}
// Devuelve el Texto sin los controles de Salto de Línea, y otros...
function fLimpiaTxt($sTexto)
{
	return str_replace(array("\n","\r"), "", str_replace("'","''", $sTexto));
}
function fIsDigit($Num)
{
	return (strpos("0123456789",$Num) !== false) ? true : false;
}
// Añade '0' a la Izquierda a una cadena numérica para que se ordenen de menor a mayor correctamente
function fNumero($Num, $Dig = 3)
{
	if (is_numeric($Valor = $Num))
	{
		if (strpos($Num, '.') !== false)
		{   // Máximo 99.99
			$Tmp = explode('.',$Num);
			$Valor = sprintf("%02s.%02s", $Tmp[0],$Tmp[1]);
		}
		else // Máximo 99 ó 999
			$Valor = sprintf("%0".$Dig."s", $Num);
	}
	
	return $Valor;
}
// Realiza el formato automático para la numeración de las Torres...
function fNumTorre($Torre, $CharNum = false)
{   // Numéricos
	if (is_numeric($Valor = ($Torre = trim($Torre))))
		$Valor = fNumero($Torre, 3);
	else if ($CharNum)
	{   // Letras y Numeros, Numeros y Letras, ó 99 - 99
		if (strpos($Valor, '-') !== false &&
			(fIsDigit($Valor[0]) && fIsDigit($Valor[strlen($Valor)-1])) )
		{
			$Tmp = explode('-', $Valor);
			$Valor = sprintf("%02s-%02s",$Tmp[0],$Tmp[1]);
		}
		else
		{
			$Valor = $Tmp = "";
			for($Pos=0; $Pos<strlen($Torre); $Pos++)
			{
  				if (!fIsDigit($Torre[$Pos]) && $Torre[$Pos] != '.')
					$Valor .= $Torre[$Pos];
				else
					$Tmp .= $Torre[$Pos];
			}

			// Si es Texto más Número, hay que limitar los caracteres para que no supere el máximo
			if (strlen($Tmp = fNumero($Tmp, (strlen($Valor) == 1)?2:3)) > 0)
			{
				if (!fIsDigit($Torre[0]))		   // Letras y Números
					$Valor = sprintf((strlen($Tmp) < 5)?"%.5s%s":"%.3s%s",$Valor,$Tmp);
				else								// Números y Letras
					$Valor = sprintf((strlen($Tmp) < 5)?"%s%.5s":"%s%.3s",$Tmp,$Valor);
			}
		}
	}

	return strtoupper($Valor);
}
?>