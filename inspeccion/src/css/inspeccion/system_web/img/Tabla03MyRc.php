<?php
// Se encargará por AJAX obtener los Datos del Seguimiento Diario "Extinores"
header("Content-Type:text/html; charset=UTF-8");
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$Query = "SELECT P.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG, B.Nombre AS Localizacion, LE.NPlaca, A.Nombre AS Marca, LE.AgenteExtintor, LE.Fecha AS Fecha,
	LE.OT, LE.Estado FROM ListaCtrlExt LE JOIN Lineas L ON L.Id=LE.IdLinea JOIN Parques P ON P.Id=L.IdParque JOIN MarcaExt A ON A.Id = LE.Marca
	JOIN Localizacion B ON B.Id = LE.Localizacion WHERE LE.Estado != 2";
	
if ($_REQUEST['Fecha'] != "")
	$Query .= " AND LE.Fecha = '".$_REQUEST['Fecha']."'";
if ($_REQUEST['Parque'] != "")
	$Query .= " AND L.IdParque = ".$_REQUEST['Parque'];
if ($_REQUEST['Torre'] != "")
	$Query .= " AND L.NumeroTorre = '".fNumTorre($_REQUEST['Torre'])."'";
if ($_REQUEST['TipoAEG'] != "")
	$Query .= " AND L.TipoAerogenerador = ".$_REQUEST['TipoAEG'];
if ($_REQUEST['NSerie'] != "")
	$Query .= " AND LE.NPlaca LIKE ('%".$_REQUEST['NSerie']."%')";
if ($_REQUEST['OT'] != "")
	$Query .= " AND LE.OT ='".$_REQUEST['OT']."'";
$Query .= " AND LE.Estado ".(($_REQUEST['Resultado'] != "") ? "=".$_REQUEST['Resultado']:"!=2");
$Query .= " ORDER BY L.IdParque, L.NumeroTorre, LE.Localizacion ASC;";

$Html = '
<TABLE class="tablanaranja" cellpadding=0 cellspacing=0>
  <tr>
	<th width=55><b>Fecha</b></th>
	<th width=175><b>Parque</b></th>
	<th width=35><b>Torre</b></th>
	<th width=100><b>Tipo de AEG</b></th>
	<th width=80><b>Localización</b></th>
	<th width=80><b>Nº Placa</b></th>
	<th width=50><b>Marca</b></th>
	<th width=65><b>Agente</b></th>
	<th width=90><b>OT</b></th>
	<th width=55><b>Resultado</b></th>
  </tr>';

if (($result = mysql_query($Query, $conn)))
{
	while($row = mysql_fetch_array($result))
	{
		$Html .= '<tr>';
		$Html .= '	<td align="center">'.date("d/m/y",strtotime($row['Fecha'])).'</td>';
		$Html .= '	<td align="left">'.$row['Nombre'].'</td>';
		$Html .= '	<td align="center">'.$row['Torre'].'</td>';
		$Html .= '	<td align="center">'.$row['TipoAEG'].'</td>';
		$Html .= '	<td align="center">'.$row['Localizacion'].'</td>';
		$Html .= '	<td align="center">'.$row['NPlaca'].'</td>';
		$Html .= '	<td align="center">'.$row['Marca'].'</td>';
		$Html .= '	<td align="center">'.$row['AgenteExtintor'].'</td>';
		$Html .= '	<td align="center">'.$row['OT'].'</td>';
		$Html .= '	<td align="center">'.(($row['Estado'] == 1)?"OK":"No OK").'</td>';
		$Html .= '</tr>';
	}
	
	unset ($result, $row);
}
$Html .= '</TABLE>';
echo $Html;

if ($conn)
	mysql_close($conn);
?>