<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../../db-config.php");

$idGrupo = (is_numeric($_REQUEST['idGrupo'])) ? $_REQUEST['idGrupo'] : 0;

// Obtener los Precios del Grupo.
$Html = '
<TABLE width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td>
			<table width=100% cellpading=0 cellspacing=0>
				<tr>
					<td class="header_L">&nbsp;</td>
					<td class="header_C"><span>Precios de Montaje</span></td>
					<td class="header_R">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height=auto valign=top>
			<table width=100% cellpadding=0 cellspacing=0 class="table" bgcolor="#ffffff">
				<tr>
					<td class="td2_title">&nbsp;</td>
					<td class="td3_title" colspan=2 align="center">ESPAÑA</td>
					<td class="td3_title" colspan=2 align="center">EUROPA</td>
					<td class="td3_title" colspan=2 align="center">AMERICA / ASIA</td>
					<td class="td3_title" colspan=2 align="center">AFRICA</td>
				</tr>
				<tr>
					<td class="td_sub_1">Aerogeneradores&nbsp;</td>
					<td class="td_sub_2a" align="center" style="background-color:#dfefff">1 Operario</td>
					<td class="td_sub_2b" align="center" style="background-color:#dfefff">2 Operarios</td>
					<td class="td_sub_2a" align="center" style="background-color:#fff0b3">1 Operario</td>
					<td class="td_sub_2b" align="center" style="background-color:#fff0b3">2 Operarios</td>
					<td class="td_sub_2a" align="center" style="background-color:#fde9e1">1 Operario</td>
					<td class="td_sub_2b" align="center" style="background-color:#fde9e1">2 Operarios</td>
					<td class="td_sub_2a" align="center" style="background-color:#efefef">1 Operario</td>
					<td class="td_sub_2b" align="center" style="background-color:#efefef">2 Operarios</td>
				</tr>';

if (($result = mysql_query("SELECT * FROM PvpMontaje WHERE idGrupo=".$idGrupo, $conn)))
{
	while($row = mysql_fetch_array($result))
	{
		$Texto = "";
		switch ($row['Rango'])
		{
			case 1: $Texto = "de 1 a 5"; break;
			case 2: $Texto = "de 6 a 10"; break;
			case 3: $Texto = "de 11 a 20"; break;
			case 4: $Texto = "de 21 a 30"; break;
			case 5: $Texto = "más de 30"; break;
		}

		$Html .='
				<tr>
					<td class="td_left" style="border-right:1px solid #dddddd;padding-left:15px;">'.$Texto.'</td>
					<td class="td_con_1a" align="right"><span style="display:'.($row['Rango'] > 2 ? "none;":"block;").'">'.number_format($row['PrecioT11'], 2).'&nbsp;</span></td>
					<td class="td_con_1b" align="right"><span >'.number_format($row['PrecioT12'], 2).'&nbsp;</span></td>
					<td class="td_con_2a" align="right"><span style="display:'.($row['Rango'] > 2 ? "none;":"block;").'">'.number_format($row['PrecioT13'], 2).'&nbsp;</span></td>
					<td class="td_con_2b" align="right"><span>'.number_format($row['PrecioT14'], 2).'&nbsp;</span></td>
					<td class="td_con_3a" align="right"><span style="display:'.($row['Rango'] > 2 ? "none;":"block;").'">'.number_format($row['PrecioT15'], 2).'&nbsp;</span></td>
					<td class="td_con_3b" align="right"><span>'.number_format($row['PrecioT16'], 2).'&nbsp;</span></td>
					<td class="td_con_4a" align="right"><span style="display:'.($row['Rango'] > 2 ? "none;":"block;").'">'.number_format($row['PrecioT17'], 2).'&nbsp;</span></td>
					<td class="td_con_4b" align="right"><span>'.number_format($row['PrecioT18'], 2).'&nbsp;</span></td>
				</tr>';
	}
	
	unset ($result, $row);
}

$CerLinea = $CerLineaT2 = $InsEscaleras = $InsEscalerasT1 = $InsEscalerasT2 = 0;
if (($result = mysql_query("SELECT * FROM PvpRevision WHERE idGrupo=".$idGrupo, $conn)))
{
	if (($row = mysql_fetch_array($result)))
	{
		$CerLinea = $row['CerLinea'];
		$CerLineaT2 = $row['CerLineaT2'];
		$InsEscaleras = $row['InsEscaleras'];
		$InsEscalerasT1 = $row['InsEscalerasT1'];
		$InsEscalerasT2 = $row['InsEscalerasT2'];
	}
	
	unset ($result, $row);
}

$Html .= '
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width=100% cellpading=0 cellspacing=0>
				<tr>
					<td class="header_L">&nbsp;</td>
					<td class="header_C"><span>Precios de Revisión</span></td>
					<td class="header_R">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height=auto valign=top>
			<table width=100% cellpadding=0 cellspacing=0 class="table" bgcolor="#ffffff">
				<tr>
					<td class="td2_title">&nbsp;</td>
					<td class="td3_title" align="center">ESPAÑA</td>
					<td class="td3_title" align="center">RESTO</td>
				</tr>
				</tr>
					<td class="td_left" style="border-right:1px solid #ddd;padding-left:15px;">Certificación</td>
					<td class="td_con_1ae" align="right"><span>'.number_format($CerLinea, 2).'&nbsp;</span></td>
					<td class="td_con_1be" align="right"><span>'.number_format($CerLineaT2, 2).'&nbsp;</span></td>
				</tr>
				</tr>
					<td class="td_left" style="border-right:1px solid #ddd;padding-left:15px;">Inspeción Escaleras</td>
					<td class="td_con_1ae" align="right"><span>'.number_format($InsEscaleras, 2).'&nbsp;</span></td>
					<td class="td_con_1be" align="right"><span>'.number_format($InsEscalerasT2, 2).'&nbsp;</span></td>
				</tr>
				</tr>
					<td class="td_left" style="border-right:1px solid #ddd;padding-left:15px;">Inspeción Escaleras junto a otra inspección</td>
					<td class="td_con_1be" align="center" colspan=2><span>'.number_format($InsEscalerasT1, 2).'</span></td>
				</tr>
			</table>
		</tr>
	</tr>
</TABLE>';
echo $Html;

unset ($CerLinea, $CerLineaT2, $InsEscaleras, $InsEscalerasT1, $InsEscalerasT2, $idGrupo, $Html);
if ($conn)
	mysql_close($conn);
?>