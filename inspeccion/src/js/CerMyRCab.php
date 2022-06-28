<?php
$Parque = isset($_REQUEST['Parque']) ? $_REQUEST['Parque'] : 0;
$NombreParque = $ClienteParque = ""; $IdiomaCer = $IdiomaChk = 1;
$FechaIni = isset($_REQUEST['FechaIni']) ? fFechaYMD($_REQUEST['FechaIni']) : date('Y-01-01');
$FechaFin = isset($_REQUEST['FechaFin']) ? fFechaYMD($_REQUEST['FechaFin']) : date('Y-12-31');
if (($result = mysql_query("SELECT P.Nombre, P.Cliente, PA.IdiomaCer, PA.IdiomaChk FROM Parques P JOIN Paises PA ON PA.Id=P.Pais WHERE P.Id=".$Parque, $conn)))
{
	if (($row = mysql_fetch_array($result)))
	{
		$NombreParque = $row['Nombre'];
		$ClienteParque = $row['Cliente'];
		$IdiomaCer = $row['IdiomaCer'];
		$IdiomaChk = $row['IdiomaChk'];
	}
	unset ($result, $row);
}

// Inicio Variables	
$array_meses = explode(",", "Ene,Feb,Mar,Abr,May,Jun,Jul,Ago,Sep,Oct,Nov,Dic");
if (($result = mysql_query("SELECT Texto FROM Literales WHERE Tipo=6 AND Grupo=1 AND Idioma=".$IdiomaCer, $conn))) {
	if (($row = mysql_fetch_array($result)))
		$array_meses = explode(",", $row['Texto']);
}

$Trabajadores = $Firmas = $Torres = array(); $PiePag = true;
$Resultado = (isset($Resultado)) ? $Resultado : false;
$cabecera = $cliente = $titulo = $subtit1 = $subtit2 = $subtit3 = $tit_parque = "";
$titulo2 = $htabla1 = $htabla2 = $htabla3 = $htabla4 = $htabla5 = $htabla6 = $htabla7 = "";
$hsubtabla6a = $hsubtabla6b = $detal1 = $detal2 = $detal3 = $detal4 = "";
$obs1 = $obs2= $obs3 = $pie1 = $pie2 = $pie3 = ""; $NomPDF = "Certificado ".$NombreParque.".pdf";
// Seleccionamos los trabajadores
if ($TipoCer == "D")
	$Query = "SELECT DISTINCT T.Nombre, T.Firma FROM ListaCtrlDes LC";
else if ($TipoCer == "E")
	$Query = "SELECT DISTINCT T.Nombre, T.Firma FROM ListaCtrlExt LC";
else 
	$Query = "SELECT DISTINCT T.Nombre, T.Firma FROM ListaControl LC";
$Query .= " JOIN Lineas L ON L.Id = LC.IdLinea LEFT JOIN Trabajadores T ON T.Id = LC.IdTrabajador
	WHERE L.IdParque=".$Parque." AND LC.Fecha >= '".$FechaIni."' AND LC.Fecha <= '".$FechaFin."'";
if ($TipoCer == "M" || $TipoCer == "R")
{   // El 2do Operario estará en el Resultado 2, sea OK ó NO OK.
	$Query .= " AND LC.Tipo ='".$TipoCer."'";
	if (!$Resultado)					// Todos
		$Query .= " AND (LC.Resultado >= 0 AND LC.Resultado <= 2)";
	else if (is_numeric($Resultado))	// Los OK, ó NO OK
		$Query .= ($Resultado == 1) ? " AND (LC.Resultado=1 OR LC.Resultado=2)" : " AND (LC.Resultado=0 OR LC.Resultado=2)";
}
else
{   // El 2do Operario estará en el Estado 2, sea OK ó NO OK.
	if (!$Resultado)					// Todos
		$Query .= " AND (LC.Estado >= 0 AND LC.Estado <= 2)";
	else if (is_numeric($Resultado))	// Los OK, ó NO OK
		$Query .= ($Resultado == 1) ? " AND (LC.Estado=1 OR LC.Estado=2)" : " AND (LC.Estado=0 OR LC.Estado=2)";
}

if (($Consulta = mysql_query($Query, $conn)))
{
	while($rowx = mysql_fetch_row($Consulta))
	{
		$Trabajadores[] = $rowx[0];
		$Firmas[] = $rowx[1];
	}
	unset($Consulta, $rowx);
}
?>