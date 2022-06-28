<?php
// Obtiene el nombre de la página
function fPageName()
{
	return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}
// Comprueba si la Sesión está Iniciada ó No
function fSessionStarted()
{
	if (php_sapi_name() !== "cli")
	{
		if (version_compare(phpversion(), "5.4.0", ">=")) {
			return (session_status() === PHP_SESSION_ACTIVE) ? true : false;
        } else {
       		return (session_id() === "") ? false : true;
        }
	}
	
	return false;
}

if (fSessionStarted() === false) 
	session_start();
if (!isset($_SESSION["ROL"]) || $_SESSION["ROL"] < 1)
{   // Redirigimos al usuario al inicio de sesión
    $_SESSION["ERROR"] = "No tiene permiso para acceder";
    header("Location:../empty.php");
    exit;
}
?>