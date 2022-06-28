<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$_SESSION['AlbTipo'] = 0;
$_SESSION['AlbId'] = isset($_REQUEST["Id"]) ? $_REQUEST["Id"] : 0;
$NAlb = $Parque = $Fecha = "";
if (($result = mysql_query("SELECT Alb.*, P.Nombre FROM AlbaranCAB Alb
	JOIN Parques P ON P.Id = Alb.IdParque WHERE Alb.Id='".$_SESSION['AlbId']."'", $conn)))	
{
	if (($row = mysql_fetch_assoc($result)))
	{
		$NAlb = (($row['Tipo'] == 1) ? 'LV':(($row['Tipo'] == 2) ?'MX' : "")).'&nbsp;'.sprintf("%05d",$row['NAlb']).'/'.$row['Anyo'];
		$Parque = $row['Nombre'];
		$Fecha  = date('d/m/Y', strtotime($row['Fecha']));
		
		$_SESSION['AlbTipo'] = $row['Tipo'];
	}
	unset($result, $row);
}

if ($conn)
	mysql_close($conn);
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
  		<meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  		<title>:: Inspección de instalaciones técnicas ::</title>
		<link rel="stylesheet" type="text/css" media="screen" href="../themes/smoothness/jq.ui-1.8.18.custom.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../themes/smoothness/ui.multiselect.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../themes/smoothness/ui.jqgrid.css" />
		<link rel="stylesheet" type="text/css" href="../css/general.css" />
        <script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../js/jqgrid/i18n/grid.locale-es.js" type="text/javascript"></script>
		<script src="../js/jqgrid/jquery.jqGrid.min.js" type="text/javascript"></script>
     	<script src="../js/inc/functions.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$.jgrid.no_legacy_api = true;
				$.jgrid.useJSON = true;
			});
			
			function fCalcula(id)
			{   // Función que cálcula el Importe total con los datos Precio y Unidades de la malla
				var sId = "#"+fExtraer(id);
				var nPvp = $(sId+"_Precio").val();
				var nCan = $(sId+"_Unidades").val();
				
				$(sId+"_Precio").val(nPvp = ($.isNumeric(nPvp)) ? fNumber_format(parseFloat(nPvp), 2, '.', '') : '0.00');
				$(sId+"_Unidades").val(nCan = ($.isNumeric(nCan)) ? fNumber_format(parseFloat(nCan), 2, '.', '') : '1.00');
				$(sId+"_Importe").val(fNumber_format((nPvp * nCan),2,'.',''));
			}
		</script>
		<style>
			span {color:#29384C; font-family:Segoe UI,Calibri,Helvetica,Arial,sans-serif; font-size:14px; line-height:20px;}
		</style>
	</HEAD>
	<BODY>
        <div style="width:900px;height:30px;border:1px solid #e9bc0c;background-color:#f9f9f9;">
			<table>
				<tr>
					<td width="60"><span><b>Albarán :</b></span></td>
					<td width="80"><span><?php echo $NAlb;?></span></td>
					<td width="45"><span><b>Fecha:</b></span></td>
					<td width="80"><span><?php echo $Fecha;?></span></td>
					<td width="50"><span><b>Parque :</b></span></td>
					<td><span><?php echo $Parque;?></span></td>
				</tr>
			</table>
		</div>
		<div style="height:7.5px;"></div>
		<div style="width:900px;">
		<?php include("AlbaranDet_1.php");?>
		</div>
	</BODY>
</HTML>