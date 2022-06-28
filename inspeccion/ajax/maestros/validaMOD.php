<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../../db-config.php");

$Html = "";
$Marca = (is_numeric($_REQUEST['Marca'])) ? $_REQUEST['Marca'] : 0;
$Modelo = (isset($_REQUEST['Modelo'])) ? $_REQUEST['Modelo'] : 0;
if (($result = mysql_query("SELECT Id, Nombre FROM ModeloDes WHERE IdMarca=".$Marca, $conn))) {
	while($row = mysql_fetch_array($result))
		$Html .= "<OPTION ".(($Modelo == $row['Id'])?"SELECTED":"")." VALUE='".$row['Id']."' >".$row['Nombre']."</OPTION>";
}
if ($Modelo != 0)
	$Html .= "<OPTION VALUE=''>Otro</OPTION>";
if ($conn)
	mysql_close($conn);

echo $Html;
?>