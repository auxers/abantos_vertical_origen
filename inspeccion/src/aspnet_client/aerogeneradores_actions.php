<?php
date_default_timezone_set('Europe/Madrid');
require_once("../db-config.php");
if (session_id() === "")
	session_start();

if (($action = isset($_GET["act"]) ? $_GET["act"] : "") == 'preadd')
{
	$Query = "INSERT INTO TAerogenerador (Nombre, Prefijo, Sufijo, IdGrupo) VALUES ('','','',0)";
	if (mysql_query($Query, $conn))
		$_SESSION['ae_id'] = mysql_insert_id();
}
else if ($action == 'unadd')
{
	if (mysql_query("DELETE FROM TAerogenerador WHERE Id=".$_SESSION['ae_id'], $conn))
		unset($_SESSION['ae_id']);
}
else if ($action == 'add' || $action == 'edit')
{
	$id = ($action=='add') ? $_SESSION['ae_id'] : $_GET["v0"];
	$nombre = trim($_GET["v1"]);
	if (empty($nombre))
		$nombre = "TAero ".$id;

	$prefijo = trim($_GET["v2"]);
	$sufijo  = trim($_GET["v3"]);
	$extintores = $_GET["v4"];
	$idGrupo = $_GET["v5"];

	$Query = "UPDATE TAerogenerador SET Nombre='".$nombre."', Prefijo='".$prefijo."', Sufijo='".$sufijo.
		"', Extintores='".$extintores."', IdGrupo='".$idGrupo."' WHERE Id=".$id;
	mysql_query($Query, $conn);
	if ($action == 'add')
		echo $Query;
}

if ($conn)
	mysql_close($conn);
?>