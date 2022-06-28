<?php
session_start();
if (isset($_SERVER['HTTP_COOKIE']))
{
	foreach(explode(';', $_SERVER['HTTP_COOKIE']) as $cookie) {
		RemoveCookie(trim(strtok($cookie, "=")));
	}
} else {
	RemoveCookie("usuario");
	RemoveCookie("xscreen");
	RemoveCookie("yscreen");
}
unset($_SESSION["ae_id"],$_SESSION["AlbId"],$_SESSION["AlbTipo"], $_SESSION["ROL"],$_SESSION["ERROR"],$_SESSION["mi_url"]);
session_destroy();

function RemoveCookie($Name) {
	unset($_COOKIE[$Name]);
	return setcookie($Name, NULL, -1);
}
?>