<?php
require_once("../db-config.php");

	$values = "";
	if (is_numeric($_REQUEST["Absorbedor"]))						// Materiales
		$values .= ",Absorbedor=".$_REQUEST["Absorbedor"];
	if (is_numeric($_REQUEST["Engaste"])) 
		$values .= ",Engaste=".$_REQUEST["Engaste"];
	if (is_numeric($_REQUEST["Cable"])) 
		$values .= ",Cable=".$_REQUEST["Cable"];
	if (is_numeric($_REQUEST["Guardacabo"])) 
		$values .= ",Guardacabo=".$_REQUEST["Guardacabo"];
	if (is_numeric($_REQUEST["Aprietacable"])) 
		$values .= ",Aprietacable=".$_REQUEST["Aprietacable"];
	if (is_numeric($_REQUEST["Tensor"])) 
		$values .= ",Tensor=".$_REQUEST["Tensor"];
	if (is_numeric($_REQUEST["PiezaInferior"])) 
		$values .= ",PiezaInferior=".$_REQUEST["PiezaInferior"];
	if (is_numeric($_REQUEST["Cartel"])) 
		$values .= ",Cartel=".$_REQUEST["Cartel"];
	if (is_numeric($_REQUEST["VarillaRoscada"])) 
		$values .= ",VarillaRoscada=".$_REQUEST["VarillaRoscada"];
	if (is_numeric($_REQUEST["Tuercas"]))
		$values .= ",Tuercas=".$_REQUEST["Tuercas"];
	if (is_numeric($_REQUEST["Arandelas"])) 
		$values .= ",Arandelas=".$_REQUEST["Arandelas"];
	if (is_numeric($_REQUEST["Bulon"])) 
		$values .= ",Bulon=".$_REQUEST["Bulon"];
	if (is_numeric($_REQUEST["Pasador"]))
		$values .= ",Pasador=".$_REQUEST["Pasador"];
	if (is_numeric($_REQUEST["PrecioHora"])) 
		$values .= ",PrecioHora=".$_REQUEST["PrecioHora"];

	if (is_numeric($_REQUEST["MonSustituirCable"])) 					// Pvp en Montajes
		$values .= ",MonSustituirCable=".$_REQUEST["MonSustituirCable"];
	if (is_numeric($_REQUEST["MonRigidizadores"])) 
		$values .= ",MonRigidizadores=".$_REQUEST["MonRigidizadores"];

	if (is_numeric($_REQUEST["RevSustituirCable"]))					// Pvp en Revisión 
		$values .= ",RevSustituirCable=".$_REQUEST["RevSustituirCable"];
	if (is_numeric($_REQUEST["RevSustituirCableT2"])) 
		$values .= ",RevSustituirCableT2=".$_REQUEST["RevSustituirCableT2"];
	if (is_numeric($_REQUEST["RevSustituirSoporteSup"])) 
		$values .= ",RevSustituirSoporteSup=".$_REQUEST["RevSustituirSoporteSup"];
	if (is_numeric($_REQUEST["RevSustituirSoporteSupT2"])) 
		$values .= ",RevSustituirSoporteSupT2=".$_REQUEST["RevSustituirSoporteSupT2"];
	if (is_numeric($_REQUEST["RevEvacuadorNacelle"])) 
		$values .= ",RevEvacuadorNacelle=".$_REQUEST["RevEvacuadorNacelle"];
	if (is_numeric($_REQUEST["RevEvacuadorNacelleT1"])) 
		$values .= ",RevEvacuadorNacelleT1=".$_REQUEST["RevEvacuadorNacelleT1"];
	if (is_numeric($_REQUEST["RevEvacuadorNacelleT2"])) 
		$values .= ",RevEvacuadorNacelleT2=".$_REQUEST["RevEvacuadorNacelleT2"];
	if (is_numeric($_REQUEST["RevEvacuadorGround"]))
		$values .= ",RevEvacuadorGround=".$_REQUEST["RevEvacuadorGround"];
	if (is_numeric($_REQUEST["RevEvacuadorGroundT2"]))
		$values .= ",RevEvacuadorGroundT2=".$_REQUEST["RevEvacuadorGroundT2"];
	if (is_numeric($_REQUEST["RevRailesSeguridad"]))
		$values .= ",RevRailesSeguridad=".$_REQUEST["RevRailesSeguridad"];
	if (is_numeric($_REQUEST["RevRailesSeguridadT1"]))
		$values .= ",RevRailesSeguridadT1=".$_REQUEST["RevRailesSeguridadT1"];
	if (is_numeric($_REQUEST["RevRailesSeguridadT2"]))
		$values .= ",RevRailesSeguridadT2=".$_REQUEST["RevRailesSeguridadT2"];
	if (is_numeric($_REQUEST["RevInsExtintor"]))
		$values .= ",RevInsExtintor=".$_REQUEST["RevInsExtintor"];
		
	if (($Tam = strlen($values)) > 0)
	{
		$Query = "UPDATE PvpMaterial SET ".substr($values,1,$Tam);
		if (!mysql_query($Query))
			echo $Query;
	}
