<?php
header("Content-Type:text/html; charset=UTF-8");

if (($Fichero = $_REQUEST['Fichero']) != "")
{
	require_once("../../db-config.php");
	set_time_limit(750);
	
	echo file_get_contents("../../data/".$Fichero);
	exit;

	if (mysql_query(file_get_contents("../../data/".$Fichero), $conn))
	{
		unlink("../../data/".$Fichero);
		echo "<p>Copia de Seguridad Restaurada</p>";
	}
	else
		echo "<p>ERROR, no se ha podido Restaurar (".mysql_error().")</p>";
	if ($conn)
		mysql_close($conn);
}
?>