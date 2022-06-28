<?php
// Obtiene el nombre de la p�gina
function fPageName()
{
	return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}
// Comprueba si la Sesi�n est� Iniciada � No
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
{   // Redirigimos al usuario al inicio de sesi�n
    $_SESSION["ERROR"] = "No tiene permiso para acceder";
    header("Location:../empty.php");
    exit;
}
?>