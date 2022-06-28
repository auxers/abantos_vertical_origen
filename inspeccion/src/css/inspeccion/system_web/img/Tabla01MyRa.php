<?php
// Se encargará mediante AJAX rellenar los datos de la Tabla de ficheros Existentes de Montaje ó Revisión
header("Content-Type:text/html; charset=UTF-8");
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$CabeceraImpresa = false;
$Html = '<TABLE class="tablanaranja" cellpadding=0 cellspacing=0>';

if (($Tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : "") != "")
{
	$directorio = dir("../../data/");
	while ($Archivo = $directorio->read())
	{   // Seleccionamos sólo los archivos asignados
		if (substr($Archivo, 21, 1) === "0")
		{   // La compración tiene que ser extricta porque por alguna razon la cadena vacia puede ser igual 0....
			if (substr($Archivo, 0, 1) === $Tipo)
			{
				if (!$CabeceraImpresa)
				{
					$Html .= '
						<tr>
							<th width=300><b>TRABAJADOR</b></th>
							<th width=175><b>PARQUE</b></th>
							<th width=50><b>TABLET</b></th>
							<th width=65>&nbsp;</th>
						</tr>';
					$CabeceraImpresa=true;
				}

				// Obtengo el Parque y los Trabajadores
				$doc = new DOMDocument();
				$doc->load("../../data/".$Archivo);
				
				$Parque = $Operario1 = $Operario2 = "";
				foreach($doc->getElementsByTagName("Parque") as $parque)
				{
					if (is_object($aux = $parque->getElementsByTagName("Nombre")->item(0)))
						$Parque = $aux->nodeValue;
					if (is_object($aux = $parque->getElementsByTagName("Operario1")->item(0)))
						$Operario1 = $aux->nodeValue;
					if (is_object($aux = $parque->getElementsByTagName("Operario2")->item(0)))
						$Operario2 = $aux->nodeValue;
				}

				if (is_numeric($Operario1))
				{
					if (($result = mysql_query("SELECT Nombre FROM Trabajadores WHERE Id=".$Operario1, $conn)))
					{
						if (($row = mysql_fetch_array($result)))
							$Operario1 = $row['Nombre'];
					}
				}				
				if (is_numeric($Operario2))
				{
					if (($result = mysql_query("SELECT Nombre FROM Trabajadores WHERE Id=".$Operario2, $conn)))
					{
						if (($row = mysql_fetch_array($result)))
							$Operario2 = $row['Nombre'];
					}
				}

				$CodTablet = substr($Archivo, 16, 5);
				$Operarios = $Operario1.'<br/>'.$Operario2;
				$Html .= '
					<tr>
						<td>'.$Operarios.'</td>
						<td>'.$Parque.'</td>
						<td align="center">'.fQuitaZeros($CodTablet).'</td>
						<td align="center" style="padding-top:2px;">
							<a href="javascript:;" onclick="fConfirmar(\'AsignarCLMyRLV.php?Tipo='.$Tipo.'&Borrar='.$Archivo.'\',\'Eliminar\'); return false;"><img src="../img/bt_del.png" alt="Borrar"></a>
						</td>
					</tr>';
			}
		}
	}
	$directorio->close();
	if (!$CabeceraImpresa)
    	$Html .= '<tr><td><b>No hay ningún parque asignado</b></td></tr>';
}
$Html .= '</TABLE>';
echo $Html;

if ($conn)
	mysql_close($conn);
?>