<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../../db-config.php");

$NumReg = 0;
$TipoAEG = (is_numeric($_REQUEST['TipoAEG'])) ? $_REQUEST['TipoAEG'] : 0;
if (($result = mysql_query("SELECT Id FROM TAeroPletinas WHERE IdTipoAEG=".$TipoAEG, $conn)))
	$NumReg = mysql_num_rows($result);

if ($conn)
	mysql_close($conn);
	
echo $NumReg;
?>