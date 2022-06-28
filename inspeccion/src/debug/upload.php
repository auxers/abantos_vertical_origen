<?php
if (isset($_GET["param"]))
{
	$Tmp = explode(".", basename($_FILES['userfile']['name']));
	$uploadfile = "../../img/firmas/fir".sprintf("%05s", $_GET["param"]).".".end($Tmp);
}
else
	$uploadfile = "../../data/".basename($_FILES['userfile']['name']);

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
  echo basename($uploadfile);
else
  echo "Error";
?>