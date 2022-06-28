<?php
require_once("../../db-config.php");

$Html = "<OPTION VALUE=''></OPTION>";
// Selecciono la Lista de Fecha de Revisión
if (($Tipo = $_REQUEST['Tipo']) == "R")
	$Query = "SELECT DISTINCT LC.Fecha FROM ListaControl LC WHERE LC.Tipo='R' ORDER BY LC.Fecha ASC;";
else if ($Tipo == "D")
	$Query = "SELECT DISTINCT LC.Fecha FROM ListaCtrlDes LC ORDER BY LC.Fecha ASC;";
else
	$Query = "SELECT DISTINCT LC.Fecha FROM ListaCtrlExt LC ORDER BY LC.Fecha ASC;";
if (($result = mysql_query($Query,$conn)))
{
	while($row = mysql_fetch_row($result))
		 $Html .= "<OPTION VALUE='".$row[0]."'>".date("d/m/Y",strtotime($row[0]))."</OPTION>";
}

if ($conn)
	mysql_close($conn);

echo $Html;
?>