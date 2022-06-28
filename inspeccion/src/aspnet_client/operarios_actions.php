<?php
require_once("../db-config.php");
if (session_id() === "")
	session_start();

$Error = "";
if (($action = isset($_GET["act"]) ? $_GET["act"] : "") == 'preadd')
{
	$Query = "INSERT INTO Trabajadores (Nombre, Login,Password,Nivel,Firma) VALUES ('','','',1,'')";
	if (mysql_query($Query, $conn))
		$_SESSION['ae_id'] = mysql_insert_id();
}
else if ($action == 'unadd')
{
	if (mysql_query("DELETE FROM Trabajadores WHERE Id=".$_SESSION['ae_id'], $conn))
		unset($_SESSION['ae_id']);
}
else if ($action == 'add' || $action == 'edit')
{
	$Id = ($action=='add') ? $_SESSION['ae_id'] : $_GET["v0"];
	$Nombre = trim($_GET["v1"]);
	if (empty($Nombre))
		$Nombre = "Usuario ".$Id;

	if (($Login = trim($_GET["v2"])) == "")
		$Login = "k".sprintf("%07d",$Id);
	if (($Password = trim($_GET["v3"])) == "")
		$Password = $Id;
	$Nivel = $_GET["v4"];
	$Firma = $_GET["v5"];

	$Query = "SELECT Id FROM Trabajadores WHERE Login='".$Login."'";
	if (($result = mysql_query($Query, $conn)))
	{
		if (($row = mysql_fetch_array($result))) {
			if ($Id != $row['Id'])
				$Error = "ERROR, El login ya existe para el Usuario ".$row['Id'];
		}
		unset ($result, $row);
	}

	if (empty($Error)) {
		$Query = "UPDATE Trabajadores SET Nombre='".$Nombre."', Login='".$Login."', Password='".$Password.
			"', Nivel='".$Nivel."', Firma='".$Firma."' WHERE Id=".$Id;
		if (!mysql_query($Query, $conn))
			$Error = "ERROR en ".$Query;
	}
}

if ($conn)
	mysql_close($conn);
echo $Error;
?>