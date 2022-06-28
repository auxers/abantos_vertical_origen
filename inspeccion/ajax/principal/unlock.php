<?php
require_once("../../db-config.php");

$Result = "ERROR, NO desbloqueado...";
if (isset($_POST['User'])) { 
	if (mysql_query("DELETE FROM LoginAttempts WHERE User='".$_POST['User']."'", $conn))
		$Result = "Usuario Desbloqueado!!";
}
if ($conn)
	mysql_close($conn);
echo $Result;
?>