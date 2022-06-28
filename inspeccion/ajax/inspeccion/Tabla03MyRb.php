<?php
// Se encargará por AJAX obtener los Datos del Seguimiento Diario "Descensores"
header("Content-Type:text/html; charset=UTF-8");
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$Query = "SELECT P.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG, LD.NSerie, A.Nombre AS DesMarca, LD.Fecha AS Fecha,
	LD.OT, LD.Estado FROM ListaCtrlDes LD JOIN Lineas L ON L.Id=LD.IdLinea JOIN Parques P ON P.Id=L.IdParque 
	JOIN MarcaDes A ON A.Id = LD.Fabricante WHERE LD.Estado != 2";
	
if ($_REQUEST['Fecha'] != "")
	$Query .= " AND LD.Fecha = '".$_REQUEST['Fecha']."'";
if ($_REQUEST['Parque'] != "")
	$Query .= " AND L.IdParque = ".$_REQUEST['Parque'];
if ($_REQUEST['Torre'] != "")
	$Query .= " AND L.NumeroTorre = '".fNumTorre($_REQUEST['Torre'])."'";
if ($_REQUEST['TipoAEG'] != "")
	$Query .= " AND L.TipoAerogenerador = ".$_REQUEST['TipoAEG'];
if ($_REQUEST['Fabricante'] != "")
	$Query .= " AND LD.Fabricante = ".$_REQUEST['Fabricante'];
if ($_REQUEST['NSerie'] != "")
	$Query .= " AND LD.NSerie LIKE ('%".$_REQUEST['NSerie']."%')";
if ($_REQUEST['OT'] != "")
	$Query .= " AND LD.OT ='".$_REQUEST['OT']."'";
$Query .= " AND LD.Estado ".(($_REQUEST['Resultado'] != "") ? "=".$_REQUEST['Resultado']:"!=2");
$Query .= " ORDER BY L.IdParque, L.NumeroTorre ASC;";

$Html = '
<TABLE class="tablanaranja" cellpadding=0 cellspacing=0>
  <tr>
	<th width=55><b>Fecha</b></th>
	<th width=175><b>Parque</b></th>
	<th width=35><b>Torre</b></th>
	<th width=100><b>Tipo de AEG</b></th>
	<th width=120><b>Nº Serie</b></th>
	<th width=75><b>Fabricante</b></th>
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
		$Html .= '	<td align="center">'.$row['NSerie'].'</td>';
		$Html .= '	<td align="center">'.$row['DesMarca'].'</td>';
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