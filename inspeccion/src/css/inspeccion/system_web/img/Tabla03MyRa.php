<?php
// Se encargará por AJAX obtener los Datos del Seguimiento Diario "Líneas de Vida"
header("Content-Type:text/html; charset=UTF-8");
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$Query = "SELECT Parques.Nombre, L.NumeroTorre AS Torre, L.TipoAerogeneradorGAMESA AS TipoAEG, PL.Tipo, LP.NumeroSerie, 
	LC.Fecha, LC.OT, LC.Observaciones, LC.TrabajosPendientes, LC.Resultado
	FROM ListaControl LC JOIN Lineas L ON L.Id=LC.IdLinea JOIN LineasPletina LP ON LP.IdLinea=LC.IdLinea JOIN Pletinas PL ON PL.Id=LP.IdPletina JOIN Parques ON Parques.Id=L.IdParque
	WHERE LC.Tipo='R' AND PL.Tipo=LC.LTipo";
	
if ($_REQUEST['Fecha'] != "")
	$Query .= " AND LC.Fecha = '".$_REQUEST['Fecha']."'";
if ($_REQUEST['Parque'] != "")
	$Query .= " AND L.IdParque = ".$_REQUEST['Parque'];
if ($_REQUEST['Torre'] != "")
	$Query .= " AND L.NumeroTorre = '".fNumTorre($_REQUEST['Torre'])."'";
if ($_REQUEST['TipoAEG'] != "")
	$Query .= " AND L.TipoAerogenerador = ".$_REQUEST['TipoAEG'];
if ($_REQUEST['NSerie'] != "")
	$Query .= " AND LP.NumeroSerie LIKE ('%".$_REQUEST['NSerie']."%')";
if ($_REQUEST['OT'] != "")
	$Query .= " AND LC.OT ='".$_REQUEST['OT']."'";
$Query .= " AND LC.Resultado ".(($_REQUEST['Resultado'] != "")?"=".$_REQUEST['Resultado']:"!=2");
$Query .= " ORDER BY L.IdParque, L.NumeroTorre ASC;";

$Html = '
<TABLE class="tablanaranja" cellpadding=0 cellspacing=0>
  <tr>
	<th width=55><b>Fecha</b></th>
	<th width=175><b>Parque</b></th>
	<th width=35><b>Torre</b></th>
	<th width=125><b>Tipo de AEG</b></th>
	<th width=175><b>Nº Serie</b></th>
	<th width=50><b>Tipo</b></th>
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
		$Html .= '	<td align="center">'.$row['NumeroSerie'].'</td>';
		$Html .= '	<td align="center">'.(($row['Tipo']=="1")?"Nacelle":"Servicio").'</td>';
		$Html .= '	<td align="center">'.$row['OT'].'</td>';
		$Html .= '	<td align="center">'.(($row['Resultado'] == 1)?"OK":"No OK").'</td>';
		$Html .= '</tr>';
	}
	
	unset ($result, $row);
}
$Html .= '</TABLE>';
echo $Html;

if ($conn)
	mysql_close($conn);
?>