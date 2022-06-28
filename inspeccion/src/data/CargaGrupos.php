<?php
require_once("../../db-config.php");

$Html = '<OPTION VALUE="">Seleccione ...</OPTION>';
if (($Tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : "") != "")
{
	if ($Tipo == 3)
		$Html .= '<OPTION VALUE="1" SELECTED>Extintor</OPTION>';
	if ($Tipo == 6)
		$Html .= '<OPTION VALUE="1" SELECTED>Abreviado</OPTION>';
	else 
	{
		$Query = ($Tipo == 1 || $Tipo == 4 || $Tipo == 7) ? "SELECT * FROM MarcaLin" : "SELECT * FROM MarcaDes";
		if (($result = mysql_query($Query, $conn)))
		{
			while($row = mysql_fetch_array($result))
				$Html .= '<OPTION VALUE="'.$row["Id"].'">'.$row["Nombre"].'</OPTION>';
			unset ($result, $row);
		}
	}
}
unset($Tipo);

if ($conn)
	mysql_close($conn);
echo $Html;
?>