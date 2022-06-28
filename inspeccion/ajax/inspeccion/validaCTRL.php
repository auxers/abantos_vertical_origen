<?php
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$Path = "../../data/"; $Error = "";
$Archivo = isset($_REQUEST['File']) ? $_REQUEST['File'] : "";
if (file_exists($Path.$Archivo))
{
	$doc = new DOMDocument();
	$doc->load($Path.$Archivo);

	$Parque = "";
	if (is_object($aux = $doc->getElementsByTagName("Nombre")->item(0)))
		$Parque = $aux->nodeValue;
	$Tablet = fQuitaZeros(substr($Archivo,16,5));

	if (is_object($aux = $doc->getElementsByTagName("Control")->item(0)))
	{
		if (($result = mysql_query("SELECT Fecha FROM CtrlValidacion WHERE Control='".$aux->nodeValue."'", $conn)))
		{
			if (mysql_num_rows($result) > 0)
				$Error = "Error, fichero de '$Parque' y Tablet '$Tablet', ya Validado...";
		}
	}
}
else
	$Error = "Error, no existe el fichero ($Archivo)";
	
if ($conn)
	mysql_close($conn);
	
echo $Error;
?>