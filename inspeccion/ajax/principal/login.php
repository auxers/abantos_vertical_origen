<?php
session_start();
require_once("../../db-config.php");

$_SESSION["ROL"] = 0; $Result = "Login Erróneo!!";
if ((isset($_POST['uname']) || isset($_POST['login'])) && isset($_POST['pword']))
{   // OD, 24.03.15 Modifico para tener un control de máximo intentos fallidos...
	$Query = "SELECT * FROM Trabajadores WHERE ".((isset($_POST['uname']))?"Id='".trim($_POST['uname'])."'":"Login='".trim($_POST['login'])."'");
	if (($result = mysql_query($Query, $conn)))
	{
		if (($row = mysql_fetch_array($result)))
		{
       		if (fCheckBrute($row['Id']) == true)
				$Result = "Login Bloqueado!!";
			else if ($row['Password'] == trim($_POST['pword']))
			{   // 1ro. Elimino control de conexiones fallidas.
				mysql_query("DELETE FROM LoginAttempts WHERE User='".$row['Id']."'", $conn);
				// 2do. Inicio sesión...
				$_SESSION["ERROR"] = $_SESSION["mi_url"] = "";
				$_SESSION["ROL"] = $Result = $row['Nivel'];
				$_COOKIE["usuario"] = $row['Id'];

				if ($row['Nivel'] < 5)
					$_SESSION["mi_url"] = "empty.php";
			} else {
				$Result = "Login incorrecto!!";
				mysql_query("INSERT INTO LoginAttempts(User, lTime) VALUES ('".$row['Id']."', '".time()."')", $conn);
			}
		}
		unset($result, $row);
	}	
}
if ($conn)
	mysql_close($conn);
echo $Result;

function fCheckBrute($User)
{   // Todos los intentos de inicio de sesión se cuentan desde las 2 horas anteriores.
	global $conn; $Ret = false;
	
    $ValidAttempts = time() - (2 * 60 * 60);
	if (($result = mysql_query("SELECT lTime FROM LoginAttempts WHERE User = '".$User."' AND lTime > '".$ValidAttempts."'", $conn))) {
		$Ret = (mysql_num_rows($result) > 5) ? true : false;
	}
	
	return $Ret;
}
?>