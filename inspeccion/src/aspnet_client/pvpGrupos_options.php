<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
// Iniciar Variables
$Nombre = "";
$CerLinea = $CerLineaT2 = $InsEscaleras = $InsEscalerasT1 = $InsEscalerasT2 = "0.00";
$PrecioT1 = $PrecioT2 = $PrecioT3 = $PrecioT4 = 
$PrecioT5 = $PrecioT6 = $PrecioT7 = $PrecioT8 = array("0.00","0.00","0.00","0.00","0.00");
if (($action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "") == "add")
	$Id = $_SESSION['ae_id'];
else
{   // Grupo
	$Id = $_SESSION['ae_id'] = isset($_REQUEST["id"]) ? $_REQUEST["id"] : 0;
	if (($result = mysql_query("SELECT Nombre FROM PvpGrupos WHERE Id=".$Id, $conn)))
	{
		if (($row = mysql_fetch_array($result)))
			$Nombre = $row['Nombre'];
		unset ($result, $row);
	}
	
	// Precios Montaje	
	if (($result = mysql_query("SELECT * FROM PvpMontaje WHERE IdGrupo=".$Id." ORDER BY Rango ASC;", $conn)))
	{
		$Count = 0;
		while ($row = mysql_fetch_array($result))
		{
			$PrecioT1[$Count] = number_format($row['PrecioT11'],2,'.','');
			$PrecioT2[$Count] = number_format($row['PrecioT12'],2,'.','');
			$PrecioT3[$Count] = number_format($row['PrecioT13'],2,'.','');
			$PrecioT4[$Count] = number_format($row['PrecioT14'],2,'.','');
			$PrecioT5[$Count] = number_format($row['PrecioT15'],2,'.','');
			$PrecioT6[$Count] = number_format($row['PrecioT16'],2,'.','');
			$PrecioT7[$Count] = number_format($row['PrecioT17'],2,'.','');
			$PrecioT8[$Count] = number_format($row['PrecioT18'],2,'.','');
			$Count ++;
		}
		unset ($result, $row);
	}
	
	// Precios Revisión
	if (($result = mysql_query("SELECT * FROM PvpRevision WHERE IdGrupo=".$Id, $conn)))
	{
		if (($row = mysql_fetch_array($result)))
		{
			$CerLinea = number_format($row['CerLinea'],2,'.','');
			$CerLineaT2 = number_format($row['CerLineaT2'],2,'.','');
			$InsEscaleras = number_format($row['InsEscaleras'],2,'.','');
			$InsEscalerasT1 = number_format($row['InsEscalerasT1'],2,'.','');
			$InsEscalerasT2 = number_format($row['InsEscalerasT2'],2,'.','');
		}
		unset ($result, $row);
	}
}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
  		<meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  		<title>:: Inspección de instalaciones técnicas ::</title>
		<link rel="stylesheet" type="text/css" media="screen" href="../themes/smoothness/jq.ui-1.8.18.custom.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../themes/smoothness/ui.multiselect.css" />
		<link rel="stylesheet" type="text/css" href="../css/general.css"/>
		<!--[if lt IE 9]>
		<script type="text/javascript" src="../js/jquery/html5shiv.js"></script>
		<![endif]-->
		<script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
     	<script src="../js/inc/functions.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#Nombre").focus();
			});
		</script>
		<style>
			span {
				color:#29384C;
				font-family: Segoe UI, Calibri, Helvetica, Arial, sans-serif; 
				font-size: 12px;
				font-weight:bold;
				line-height:20px;
				margin-right:0px;
			}
			input, select {
				font-family: Segoe UI, Calibri, Helvetica, Arial, sans-serif; 
				font-size: 12px;
				color: #003;
			}
			input[type="text"]:focus, textarea:focus
			{
				background-color: #FFC;
			}
			.txtDec {text-align:right; width: 65px;}
		</style>
	</HEAD>
	<BODY>
		<FORM action="" method="post" enctype="application/x-www-form-urlencoded">
			<div style="border:1px solid #e9bc0c;width:675px;margin-left:25px;background-color:#f9f9f9;">
				<table>
					<tr>
						<td><span style="line-height:28px;">Nombre :</span></td>
						<td>
                        	<input id="Id" name="Id" type="hidden" value="<?php echo $Id;?>" />
                        	<input id="Nombre" name="Nombre" type="text" size="30" maxlength="30" value="<?php echo $Nombre;?>" />
                        </td>
					</tr>
				</table>
			</div>
            <div style="height:4px;"></div>
			<div style="width:675px;margin-left:25px;background-color:#f9f9f9;">
				<table id='preciosmontaje' width=100% cellpadding=0 cellspacing=0>
					<tr>
						<td height=25>
							<table width=100% cellpading=0 cellspacing=0>
								<tr>
									<td class="header_L">&nbsp;</td>
									<td class="header_C"><span class='header_title'>PRECIOS DE MONTAJE</span></td>
									<td class="header_R">&nbsp;</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height=auto valign=top>
							<table width=100% cellpadding=0 cellspacing=0 class='table' bgcolor='#ffffff'>
								<tr>
									<td class="td2_title">&nbsp;</td>
									<td class="td3_title" colspan=2 align="center">ESPAÑA</td>
									<td class="td3_title" colspan=2 align="center">EUROPA</td>
									<td class="td3_title" colspan=2 align="center">AMERICA / ASIA</td>
									<td class="td3_title" colspan=2 align="center">AFRICA</td>
								</tr>
								<tr>
									<td class="td_sub_1">Aerogeneradores</td>
									<td class="td_sub_2a" align="center" style='background-color:#dfefff'>1 Operario</td>
									<td class="td_sub_2b" align="center" style='background-color:#dfefff'>2 Operarios</td>
									<td class="td_sub_2a" align="center" style='background-color:#fff0b3'>1 Operario</td>
									<td class="td_sub_2b" align="center" style='background-color:#fff0b3'>2 Operarios</td>
									<td class="td_sub_2a" align="center" style='background-color:#fde9e1'>1 Operario</td>
									<td class="td_sub_2b" align="center" style='background-color:#fde9e1'>2 Operarios</td>
									<td class="td_sub_2a" align="center" style='background-color:#efefef'>1 Operario</td>
									<td class="td_sub_2b" align="center" style='background-color:#efefef'>2 Operarios</td>
								</tr>
                                <?php
								for ($Count=0; $Count < 5; $Count ++)
								{
									$Rango = "";
									switch ($Count + 1)
									{
										case 1: $Rango = "de 1 a 5"; break;
										case 2: $Rango = "de 6 a 10"; break;
										case 3: $Rango = "de 11 a 20"; break;
										case 4: $Rango = "de 21 a 30"; break;
										case 5: $Rango = "más de 30"; break;
									}
								?>
								<tr>
									<td class="td_left" style='border-right:1px solid #dddddd;padding-left:15px;'><?php echo $Rango;?></td>
									<td class="td_con_1a" align="center"><INPUT type="<?php echo ($Count>1)?"hidden":"text";?>" id="PrecioT<?php echo $Count;?>1" name="PrecioT<?php echo $Count;?>1" class="txtDec" maxlength="8" value="<?php echo $PrecioT1[$Count];?>"></td>
									<td class="td_con_1b" align="center"><INPUT type="text" id="PrecioT<?php echo $Count;?>2" name="PrecioT<?php echo $Count;?>2" class="txtDec" maxlength="8" value="<?php echo $PrecioT2[$Count];?>"></td>
									<td class="td_con_2a" align="center"><INPUT type="<?php echo ($Count>1)?"hidden":"text";?>" id="PrecioT<?php echo $Count;?>3" name="PrecioT<?php echo $Count;?>3" class="txtDec" maxlength="8" value="<?php echo $PrecioT3[$Count];?>"></td>
									<td class="td_con_2b" align="center"><INPUT type="text" id="PrecioT<?php echo $Count;?>4" name="PrecioT<?php echo $Count;?>4" class="txtDec" maxlength="8" value="<?php echo $PrecioT4[$Count];?>"></td>
									<td class="td_con_3a" align="center"><INPUT type="<?php echo ($Count>1)?"hidden":"text";?>" id="PrecioT<?php echo $Count;?>5" name="PrecioT<?php echo $Count;?>5" class="txtDec" maxlength="8" value="<?php echo $PrecioT5[$Count];?>"></td>
									<td class="td_con_3b" align="center"><INPUT type="text" id="PrecioT<?php echo $Count;?>6" name="PrecioT<?php echo $Count;?>6" class="txtDec" maxlength="8" value="<?php echo $PrecioT6[$Count];?>"></td>
									<td class="td_con_4a" align="center"><INPUT type="<?php echo ($Count>1)?"hidden":"text";?>" id="PrecioT<?php echo $Count;?>7" name="PrecioT<?php echo $Count;?>7" class="txtDec" maxlength="8" value="<?php echo $PrecioT7[$Count];?>"></td>
									<td class="td_con_4b" align="center"><INPUT type="text" id="PrecioT<?php echo $Count;?>8" name="PrecioT<?php echo $Count;?>8" class="txtDec" maxlength="8" value="<?php echo $PrecioT8[$Count];?>"></td>
								</tr>
                                <?php
								}
								?>
							</table>
						</td>
					</tr>
            	</table>
			</div>
            <div style="height:4px;"></div>
			<div style="width:675px;margin-left:25px;background-color:#f9f9f9;">
				<table id='pvpRevision' width=100% cellpadding=0 cellspacing=0>
					<tr>
						<td height=25>
							<table width=100% cellpading=0 cellspacing=0>
								<tr>
									<td class="header_L">&nbsp;</td>
									<td class="header_C"><span class='header_title'>PRECIOS DE REVISIÓN</span></td>
									<td class="header_R">&nbsp;</td>
								</tr>
							</table>
						</td>
					</tr>
                    <tr>
                    	<td>
							<table width=100% cellpadding=0 cellspacing=0 class="table" bgcolor="#ffffff">
								<tr>
									<td class="td2_title">&nbsp;</td>
									<td class="td3_title" align="center">ESPAÑA</td>
									<td class="td3_title" align="center">RESTO</td>
								</tr>
								</tr>
									<td class="td_left" style="border-right:1px solid #ddd;padding-left:15px;">Certificación</td>
									<td class="td_con_1ae" align="right"><INPUT type="text" id="CerLinea" name="CerLinea" class="txtDec" maxlength="8" value="<?php echo $CerLinea;?>"></td>
									<td class="td_con_1be" align="right"><INPUT type="text" id="CerLineaT2" name="CerLineaT2" class="txtDec" maxlength="8" value="<?php echo $CerLineaT2;?>"></td>
								</tr>
								</tr>
									<td class="td_left" style="border-right:1px solid #ddd;padding-left:15px;">Inspeción Escaleras</td>
									<td class="td_con_1ae" align="right"><INPUT type="text" id="InsEscaleras" name="InsEscaleras" class="txtDec" maxlength="8" value="<?php echo $InsEscaleras;?>"></td>
									<td class="td_con_1be" align="right"><INPUT type="text" id="InsEscalerasT2" name="InsEscalerasT2" class="txtDec" maxlength="8" value="<?php echo $InsEscalerasT2;?>"></td>
								</tr>
								</tr>
									<td class="td_left" style="border-right:1px solid #ddd;padding-left:15px;">Inspeción Escaleras junto a otra inspección</td>
									<td class="td_con_1be" align="right" colspan=2><INPUT type="text" id="InsEscalerasT1" name="InsEscalerasT1" class="txtDec" maxlength="8" value="<?php echo $InsEscalerasT1;?>"></td>
								</tr>
							</table>
						</td>
                	</tr>
				</table>
			</div>
		</FORM>
	</BODY>
</HTML>
<?php
if ($conn)
	mysql_close($conn);
?>