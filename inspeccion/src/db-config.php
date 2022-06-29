<?php
// Creamos Conexión

// mysql_connect deprecado

//$dbhost="localhost:3306";
//$dbhost="qpq363.abantosvertical.com";
//if (($conn = mysql_connect($dbhost, ($db = $dbusuario = "qpq363"), "2013abantoS")))
//{   // Seleccionamos la BBDDD
//      if (mysql_select_db($db, $conn))
//              mysql_query("SET NAMES 'utf8'", $conn);
//}

$mysqli = new mysqli('localhost', 'qpq363', '2013abantoS', 'qpq363');
//$mysqli = new mysqli('qpq363.abantosvertical.com', 'qpq363', '2013abantoS', 'qpq363');

$mysqli->set_charset("utf8");
?>
