<?php
require_once("../db-config.php");
if (session_id() === "")
	session_start();

if (($action = isset($_REQUEST["act"]) ? $_REQUEST["act"] : "") == 'preadd')
{
	$Query = "INSERT INTO PvpGrupos (Nombre) VALUES ('')";
	if (mysql_query($Query, $conn))
		$_SESSION['ae_id'] = mysql_insert_id();
}
else if ($action == 'unadd')
{
	if (mysql_query("DELETE FROM PvpGrupos WHERE Id=".$_SESSION['ae_id'], $conn))
		unset($_SESSION['ae_id']);
}
else if ($action == 'add' || $action == 'edit')
{
	$Id = ($action=='add') ? $_SESSION['ae_id'] : $_REQUEST["Id"];
	$Nombre = trim($_REQUEST["Nombre"]);
	if (empty($Nombre))
		$Nombre = "Grupo ".$id;

	// Añadimos ó Modificamos 
	// 1ro. PvpGrupo
	if (!mysql_query("UPDATE PvpGrupos SET Nombre='".$Nombre."' WHERE Id=".$Id, $conn))
	{
		echo "Error";
		exit;
	}
	
	// 2do. PvpMontaje
	for ($Count = 1; $Count < 6; $Count ++)
	{
		$values = "";
		if (isset($_REQUEST["PrecioT".$Count."1"])) 
			$values .= ",PrecioT11=".(is_numeric($_REQUEST["PrecioT".$Count."1"])?$_REQUEST["PrecioT".$Count."1"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."2"])) 
			$values .= ",PrecioT12=".(is_numeric($_REQUEST["PrecioT".$Count."2"])?$_REQUEST["PrecioT".$Count."2"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."3"])) 
			$values .= ",PrecioT13=".(is_numeric($_REQUEST["PrecioT".$Count."3"])?$_REQUEST["PrecioT".$Count."3"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."4"])) 
			$values .= ",PrecioT14=".(is_numeric($_REQUEST["PrecioT".$Count."4"])?$_REQUEST["PrecioT".$Count."4"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."5"])) 
			$values .= ",PrecioT15=".(is_numeric($_REQUEST["PrecioT".$Count."5"])?$_REQUEST["PrecioT".$Count."5"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."6"])) 
			$values .= ",PrecioT16=".(is_numeric($_REQUEST["PrecioT".$Count."6"])?$_REQUEST["PrecioT".$Count."6"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."7"])) 
			$values .= ",PrecioT17=".(is_numeric($_REQUEST["PrecioT".$Count."7"])?$_REQUEST["PrecioT".$Count."7"]:"0");
		if (isset($_REQUEST["PrecioT".$Count."8"])) 
			$values .= ",PrecioT18=".(is_numeric($_REQUEST["PrecioT".$Count."8"])?$_REQUEST["PrecioT".$Count."8"]:"0");

		if (($Tam = strlen($values)) > 0)
		{
			$values = substr($values,1,$Tam);
			if ($action == 'add')
				$Query = "INSERT INTO PvpMontaje SET IdGrupo=".$Id.", Rango=".$Count.",".$values;
			else
				$Query = "UPDATE PvpMontaje SET ".$values." WHERE IdGrupo=".$Id." AND Rango=".$Count;
			mysql_query($Query, $conn);
		}
	}

	// 3ro. PvpRevisión
	$values = "";
	if (isset($_REQUEST["CerLinea"])) 
		$values .= ",CerLinea=".(is_numeric($_REQUEST["CerLinea"])?$_REQUEST["CerLinea"]:"0");
	if (isset($_REQUEST["CerLineaT2"])) 
		$values .= ",CerLineaT2=".(is_numeric($_REQUEST["CerLineaT2"])?$_REQUEST["CerLineaT2"]:"0");
	if (isset($_REQUEST["InsEscaleras"])) 
		$values .= ",InsEscaleras=".(is_numeric($_REQUEST["InsEscaleras"])?$_REQUEST["InsEscaleras"]:"0");
	if (isset($_REQUEST["InsEscalerasT1"])) 
		$values .= ",InsEscalerasT1=".(is_numeric($_REQUEST["InsEscalerasT1"])?$_REQUEST["InsEscalerasT1"]:"0");
	if (isset($_REQUEST["InsEscalerasT2"])) 
		$values .= ",InsEscalerasT2=".(is_numeric($_REQUEST["InsEscalerasT2"])?$_REQUEST["InsEscalerasT2"]:"0");
	if (($Tam = strlen($values)) > 0)
	{
		$values = substr($values,1,$Tam);
		if ($action == 'add')
			$Query = "INSERT INTO PvpRevision SET IdGrupo=".$Id.", ".$values;
		else
			$Query = "UPDATE PvpRevision SET ".$values." WHERE IdGrupo=".$Id;
		mysql_query($Query, $conn);
	}
}

if ($conn)
	mysql_close($conn);
?>