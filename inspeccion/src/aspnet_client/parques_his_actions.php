<?php
require_once("../db-config.php");
if (session_id() === "")
	session_start();

if (($action = isset($_GET["act"]) ? $_GET["act"] : "") == 'preadd')
{
	$Query = "INSERT INTO Lineas (IdParque,NumeroTorre,TipoAerogenerador,IdAltura,TipoAerogeneradorGAMESA, IdMarca) 
		VALUES (".$_SESSION['IdParque'].",'',0,0, '',1)";
	if (mysql_query($Query, $conn))
		$_SESSION['ae_id'] = mysql_insert_id();
}
else if ($action == 'unadd')
{
	if (mysql_query("DELETE FROM Lineas WHERE Id=".$_SESSION['ae_id'], $conn))
		unset($_SESSION['ae_id']);
}
else if ($action == 'add' || $action == 'edit')
{
	$IdLinea = ($action=='add') ? $_SESSION['ae_id'] : $_GET["v0"];
	$NTorre = trim($_GET["v1"]);
	if (is_numeric($NTorre))
		$NTorre = sprintf("%03s", $NTorre);
	$TipoAEG = $_GET["v2"];
	$IdAltura  = $_GET["v3"];
	$IdMarca   = $_GET["v4"];
	$LineaServicio = $_GET["v5"];
	$CableServicio = $_GET["v6"];
	$LineaNacelle  = $_GET["v7"];
	$CableNacelle  = $_GET["v8"];
	// Obtenemos el TipoGAMESA.
	$TipoGAMESA = "";	
	if (($result = mysql_query("SELECT TA.Prefijo, A.Nombre FROM TAerogenerador TA, Alturas A WHERE 
		TA.Id=".$TipoAEG." AND A.Id=".$IdAltura, $conn)))
	{
		if (($row = mysql_fetch_assoc($result)))
			$TipoGAMESA = $row["Prefijo"]." ".$row["Nombre"];
	}

	$Query = "UPDATE Lineas SET NumeroTorre='".$NTorre."', TipoAerogenerador='".$TipoAEG."', IdAltura='".$IdAltura.
		"', TipoAerogeneradorGAMESA='".$TipoGAMESA."', IdMarca='".$IdMarca."' WHERE Id=".$IdLinea;
	mysql_query($Query, $conn);
	
	// Se puede dar el caso de que podamos modificar la Pletina de un AEG, pero también cambiar el Tipo de AEG y poder tener
	//	Línea de Servicio y Nacelle ó sólo Nacelle, por ello lo que hago cada vez que se edite es buscar qué pletinas exiten
	//	para obtener sus ID y prodecer a añadir ó borrar según sea el caso...
	$IdPle = $Trompa = $Tramo = $Absorbedor = array();
	if ($action == "edit")
	{   // Siempre irá 1ro Nacelle, y 2da Servicio.
		if (($result = mysql_query("SELECT * FROM LineasPletina WHERE IdLinea=".$IdLinea." ORDER BY Id ASC;", $conn)))
		{
			while ($row = mysql_fetch_assoc($result)) {
				$IdPle[] = $row['Id'];
				$Tramo[] = $row['NTramo'];
				$Trompa[] = $row['NTrompa'];
				$Absorbedor[] = $row['NAbsorbedor'];
			}

			mysql_query("DELETE FROM LineasPletina WHERE IdLinea=".$IdLinea, $conn);
		}
	}
	
	// Creamos Línea Nacelle
	//	Comprobamos en TAeroPletinas si éste AEG debe de tener Línea de Nacelle
	$LVt1 = $LVt2 = 0;
	if (($result = mysql_query("SELECT TA.IdPletina FROM TAeroPletinas TA JOIN Pletinas p ON p.Id = TA.IdPletina
		WHERE TA.IdTipoAEG=".$TipoAEG." AND p.Tipo=1", $conn)))
	{
		if (($row = mysql_fetch_assoc($result)))
			$LVt1 = $row["IdPletina"];
	}
	if ($LVt1 > 0)
	{
		if (isset($IdPle[0])) {
			$Query = "INSERT INTO LineasPletina (Id,IdLinea,IdPletina, NumeroSerie,NumeroCable,NTrompa,NAbsorbedor,NTramo) VALUES (".
				$IdPle[0].",".$IdLinea.",".$LVt1.",'".$LineaNacelle."','".$CableNacelle."','".$Trompa[0]."','".$Absorbedor[0]."','".$Tramo[0]."')";
		} else
			$Query = "INSERT INTO LineasPletina (IdLinea,IdPletina, NumeroSerie,NumeroCable) VALUES (".$IdLinea.",".$LVt1.",'".$LineaNacelle."','".$CableNacelle."')";
		mysql_query($Query, $conn);
	}

	// Creamos Línea Servicio, si procede
	//	Comprobamos en TAeroPletinas si éste AEG debe de tener Línea de Servicio
	if (($result = mysql_query("SELECT TA.IdPletina FROM TAeroPletinas TA JOIN Pletinas p ON p.Id = TA.IdPletina
		WHERE TA.IdTipoAEG=".$TipoAEG." AND p.Tipo=2", $conn)))
	{
		if (($row = mysql_fetch_assoc($result)))
			$LVt2 = $row["IdPletina"];
	}
	if ($LVt2 > 0)
	{
		if (isset($IdPle[1])) {
			$Query = "INSERT INTO LineasPletina (Id,IdLinea,IdPletina, NumeroSerie,NumeroCable,NTrompa,NAbsorbedor,NTramo) VALUES (".
				$IdPle[1].",".$IdLinea.",".$LVt2.",'".$LineaServicio."','".$CableServicio."','".$Trompa[1]."','".$Absorbedor[1]."','".$Tramo[1]."')";
		} else
			$Query = "INSERT INTO LineasPletina (IdLinea,IdPletina, NumeroSerie,NumeroCable) VALUES (".$IdLinea.",".$LVt2.",'".$LineaServicio."','".$CableServicio."')";
		mysql_query($Query, $conn);
	}
	unset($LVt1,$LVt2, $IdPle,$Trompa,$Tramo,$Absorbedor);
}

if ($conn)
	mysql_close($conn);
?>