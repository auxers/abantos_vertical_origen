<?php
require_once("../../db-config.php");

if (($Tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : "") != "" && 
	($Grupo = isset($_REQUEST['Grupo']) ? $_REQUEST['Grupo'] : "") != "" && 
	($Idioma = isset($_REQUEST['Idioma']) ? $_REQUEST['Idioma'] : "") != "")
{   // Sino existe el Literal seleccionado del idioma, copiamos el de por defecto a éste, y ya se encargarán
	//	de traducirlo, siempre el idioma '1' será Español...
	$Query = "INSERT INTO Literales (Tipo,Grupo,Idioma, Texto)
		SELECT ".$Tipo.",".$Grupo.",".$Idioma.", L.Texto FROM Literales L WHERE L.Tipo=".$Tipo;
	$Query .= " AND L.Grupo=".((($Tipo == "2" || $Tipo == "5") && $Grupo == "2")?$Grupo:"1")." AND L.Idioma=1";
	if (!mysql_query($Query))
		echo "Error";
	unset($Query);
}
unset($Tipo,$Grupo,$Idioma);

if ($conn)
	mysql_close($conn);
?>