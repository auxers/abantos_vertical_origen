<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
$_SESSION["mi_url"] = "Maestros/".fPageName();

$SoporteSuperior = $Absorbedor = $Engaste = $Cable = $Guardacabo = $Aprietacable = $Tensor = "0.00";
$PiezaInferior = $Cartel = $VarillaRoscada = $Tuercas = $Arandelas = $Pasador = $Bulon = $PrecioHora = "0.00";
$MonSustituirCable = $MonRigidizadores = "0.00";
$RevSustituirCable = $RevSustituirCableT2 = $RevSustituirSoporteSup = $RevSustituirSoporteSupT2 = "0.00";
$RevEvacuadorNacelle = $RevEvacuadorNacelleT1 = $RevEvacuadorNacelleT2 = $RevEvacuadorGround = "0.00";
$RevEvacuadorGroundT2 = $RevRailesSeguridad = $RevRailesSeguridadT1 = "0.00";
$RevRailesSeguridadT2 = $RevInsExtintor = "0.00";
if (($result = mysql_query("SELECT * FROM PvpMaterial", $conn)))
{
	if (($row = mysql_fetch_array($result)))
	{   // Materiales
		$SoporteSuperior = number_format($row['SoporteSuperior'],2,'.','');
		$Absorbedor      = number_format($row['Absorbedor'],2,'.','');
		$Engaste         = number_format($row['Engaste'],2,'.','');
		$Cable           = number_format($row['Cable'],2,'.','');
		$Guardacabo      = number_format($row['Guardacabo'],2,'.','');
		$Aprietacable    = number_format($row['Aprietacable'],2,'.','');
		$Tensor          = number_format($row['Tensor'],2,'.','');
		$PiezaInferior   = number_format($row['PiezaInferior'],2,'.','');
		$Cartel          = number_format($row['Cartel'],2,'.','');
		$VarillaRoscada  = number_format($row['VarillaRoscada'],2,'.','');
		$Tuercas         = number_format($row['Tuercas'],2,'.','');
		$Arandelas       = number_format($row['Arandelas'],2,'.','');
		$Bulon           = number_format($row['Bulon'],2,'.','');
		$Pasador         = number_format($row['Pasador'],2,'.','');
		$PrecioHora      = number_format($row['PrecioHora'],2,'.','');
		// Pvp. Montajes
		$MonSustituirCable = number_format($row['MonSustituirCable'],2,'.','');
		$MonRigidizadores  = number_format($row['MonRigidizadores'],2,'.','');
		// Pvp. Revision
		$RevSustituirCable = number_format($row['RevSustituirCable'],2,'.','');
		$RevSustituirCableT2 = number_format($row['RevSustituirCableT2'],2,'.','');
		$RevSustituirSoporteSup = number_format($row['RevSustituirSoporteSup'],2,'.','');
		$RevSustituirSoporteSupT2 = number_format($row['RevSustituirSoporteSupT2'],2,'.','');
		$RevEvacuadorNacelle = number_format($row['RevEvacuadorNacelle'],2,'.','');
		$RevEvacuadorNacelleT1 = number_format($row['RevEvacuadorNacelleT1'],2,'.','');
		$RevEvacuadorNacelleT2 = number_format($row['RevEvacuadorNacelleT2'],2,'.','');
		$RevEvacuadorGround = number_format($row['RevEvacuadorGround'],2,'.','');
		$RevEvacuadorGroundT2 = number_format($row['RevEvacuadorGroundT2'],2,'.','');
		$RevRailesSeguridad = number_format($row['RevRailesSeguridad'],2,'.','');
		$RevRailesSeguridadT1 = number_format($row['RevRailesSeguridadT1'],2,'.','');
		$RevRailesSeguridadT2 = number_format($row['RevRailesSeguridadT2'],2,'.','');
		$RevInsExtintor = number_format($row['RevInsExtintor'],2,'.','');
	}
	
	unset ($result, $row);
}

if ($conn)
	mysql_close($conn);
?>
<!DOCTYPE html>
<html>
  <head>
 	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>:: Inspección de instalaciones técnicas ::</title>
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/jq.ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../css/general.css" />
	<script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
	<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
    <script src="../js/inc/functions.js" type="text/javascript"></script>
	<style>
		.titulo{font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
		input {font-family:Segoe UI,Calibri,Helvetica,Arial,sans-serif; font-size:12px; color:#003;}
		input[type="text"]:focus, textarea:focus {background-color: #FFC;}
		
		.txtDec {text-align:right; width:120px;}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			var x = window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'xscreen=' + x;
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
			
			$("#header_C").css("width", x-30);
			$("#guardar").button({
    	        icons: { primary: "ui-icon-disk" }
			});
			$("#cancelar").button({
    	        icons: { primary: "ui-icon-cancel" }
			});

			$("#guardar").click(function(event){
				event.preventDefault();

				$.ajax(
				{
			        type: "POST",
			        url: "pvpMaterial_actions.php",
					data: {Absorbedor:$('#Absorbedor').val(), Engaste:$('#Engaste').val(), Cable:$('#Cable').val(),
						Guardacabo:$('#Guardacabo').val(), Aprietacable:$('#Aprietacable').val(),
						Tensor:$('#Tensor').val(), PiezaInferior:$('#PiezaInferior').val(), Cartel:$('#Cartel').val(),
						VarillaRoscada:$('#VarillaRoscada').val(), Tuercas:$('#Tuercas').val(), Arandelas:$('#Arandelas').val(), 
						Bulon:$('#Bulon').val(), Pasador:$('#Pasador').val(), PrecioHora:$('#PrecioHora').val(),
						
						MonSustituirCable:$('#MonSustituirCable').val(), MonRigidizadores:$('#MonRigidizadores').val(), 
						
						RevSustituirCable:$('#RevSustituirCable').val(), RevSustituirCableT2:$('#RevSustituirCableT2').val(), 
						RevSustituirSoporteSup:$('#RevSustituirSoporteSup').val(), RevSustituirSoporteSupT2:$('#RevSustituirSoporteSupT2').val(),
						RevEvacuadorNacelle:$('#RevEvacuadorNacelle').val(), RevEvacuadorNacelleT1:$('#RevEvacuadorNacelleT1').val(),
						RevEvacuadorNacelleT2:$('#RevEvacuadorNacelleT2').val(), RevEvacuadorGround:$('#RevEvacuadorGround').val(),
						RevEvacuadorGroundT1:$('#RevEvacuadorGroundT1').val(), RevEvacuadorGroundT2:$('#RevEvacuadorGroundT2').val(), 
						RevRailesSeguridad:$('#RevRailesSeguridad').val(), RevRailesSeguridadT1:$('#RevRailesSeguridadT1').val(), 
						RevRailesSeguridadT2:$('#RevRailesSeguridadT2').val(), RevInsExtintor:$('#RevInsExtintor').val()
					},
					dataType: "text", async:false,
					success: function(sDatos) {
						if (sDatos != "")
							alert (sDatos);
						else
						{
							window.parent.$('#PreciosUpdate').dialog({
								resizable: false,
								height:150,
								modal: true,
								open: function(event, ui) { window.parent.$(".ui-dialog-titlebar-close").hide(); },
								buttons: {
									'Aceptar': function() { window.parent.$(this).dialog('close'); }
								}
							});
							return false;
						}
					},
			        error: function(obj1, e, obj2) {
     					alert ("Error " + e);
					}
				});			
			});
		
			$("#cancelar").click(function() {
				window.parent.$("#WindowDatos").attr("src","empty.php");
			});

			$("#SoporteSuperior").focus();
		});
	</script>
  </head>
  <body>
	<table width=100% cellpading=0 cellspacing=0>
		<tr>
			<td height=25>
				<table width=100% cellpading=0 cellspacing=0>
					<tr>
						<td class="header_L">&nbsp;</td>
						<td class="header_C"><span class='header_title'>PRECIOS MATERIALES</span></td>
						<td class="header_R">&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height=auto valign=top>
				<table width=100% cellpading=0 cellspacing=0 class='table' bgcolor='#ffffff'>
					<tr>
						<td class="td_title">Tarifas Materiales</td>
                        <td class='td_espana'>Referencia</td>
                        <td class='td_resto'>Precio</td>
					</tr>
					<tr>
						<td class="td_left">Soporte superior:</td>
						<td align="center">EG1</td>
						<td align="center" class="td_right"><input id="SoporteSuperior" name="SoporteSuperior" type="text" class="txtDec" maxlength="8" value="<?php echo $SoporteSuperior;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Absorbedor de energía:</td>
						<td align="center">EG3</td>
						<td align="center" class="td_right"><input id="Absorbedor" name="Absorbedor" type="text" class="txtDec" maxlength="8" value="<?php echo $Absorbedor;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Engaste cable + guardacabo:</td>
						<td align="center">EG11</td>
						<td align="center" class="td_right"><input id="Engaste" name="Engaste" type="text" class="txtDec" maxlength="8" value="<?php echo $Engaste;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Cable 8m galva Somain:</td>
						<td align="center">EG18</td>
						<td align="center" class="td_right"><input id="Cable" name="Cable" type="text" class="txtDec" maxlength="8"  value="<?php echo $Cable;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">1 ud. guardacabo:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="Guardacabo" name="Guardacabo" type="text" class="txtDec" maxlength="8" value="<?php echo $Guardacabo;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">1 ud. aprietacable:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="Aprietacable" name="Aprietacable" type="text" class="txtDec" maxlength="8" value="<?php echo $Aprietacable;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Tensor:</td>
						<td align="center">EG5</td>
						<td align="center" class="td_right"><input id="Tensor" name="Tensor" type="text" class="txtDec" maxlength="8" value="<?php echo $Tensor;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Pieza Inferior:</td>
						<td align="center" >EG2</td>
						<td align="center" class="td_right"><input id="PiezaInferior" name="PiezaInferior" type="text" class="txtDec" maxlength="8" value="<?php echo $PiezaInferior;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Cartel Informativo:</td>
						<td align="center">EG9</td>
						<td align="center" class="td_right"><input id="Cartel" name="Cartel" type="text" class="txtDec" maxlength="8" value="<?php echo $Cartel;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Varilla roscada M-16 galva:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="VarillaRoscada" name="VarillaRoscada" type="text" class="txtDec" maxlength="8" value="<?php echo $VarillaRoscada;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Tuercas:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="Tuercas" name="Tuercas" type="text" class="txtDec" maxlength="8" value="<?php echo $Tuercas;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Arandelas:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="Arandelas" name="Arandelas" type="text" class="txtDec" maxlength="8" value="<?php echo $Arandelas;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Bulón de Anclaje y Absorbedor:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="Bulon" name="Bulon" type="text" class="txtDec" maxlength="8" value="<?php echo $Bulon;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Pasador de Anclaje y Absorbedor:</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="td_right"><input id="Pasador" name="Pasador" type="text" class="txtDec" maxlength="8" value="<?php echo $Pasador;?>" /></td>
					</tr>
					<tr>
						<td class="td_left_end">Precio Hora:</td>
						<td class="td_center_end" align="center">&nbsp;</td>
						<td class="td_right_end"  align="center"><input id="PrecioHora" name="PrecioHora" type="text" class="txtDec" maxlength="8" value="<?php echo $PrecioHora;?>" /></td>
					</tr>				
				</table>
			</td>
		</tr>
		<tr>
			<td height=auto valign=top>
				<table width=100% cellpading=0 cellspacing=0 class='table' bgcolor='#ffffff'>
					<tr>
						<td class='td_title'>Tarifas Montaje</td>
                        <td class='td_espana'>España</td>
                        <td class='td_resto'>Resto</td>
					</tr>
					<tr>
						<td class="td_left">Adecuación Cables y/o Soporte Superior :</td>
						<td align="center"></td>
						<td class="td_right" align="center"><input id="MonSustituirCable" name="MonSustituirCable" type="text" class="txtDec" maxlength="8" value="<?php echo $MonSustituirCable;?>" /></td>
					</tr>
					<tr>
						<td class="td_left_end">Adecuación Pletina :</td>
						<td class="td_center_end" align="center"></td>
						<td class="td_right_end" align="center"><input id="MonRigidizadores" name="MonRigidizadores" type="text" class="txtDec" maxlength="8" value="<?php echo $MonRigidizadores;?>" /></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height=auto valign=top>
				<table width=100% cellpading=0 cellspacing=0 class='table' bgcolor='#ffffff'>
					<tr>
						<td class="td_title">Tarifas Revisión</td>
                        <td class='td_espana'>España</td>
                        <td class='td_resto'>Resto</td>
					</tr>
					<tr>
						<td class="td_left">Sustitución de cables (incluida certificación de línea de vida) :</td>
						<td class="td_center" align="center"><input id="RevSustituirCable" name="MonSustituirCable" type="text" class="txtDec" maxlength="8" value="<?php echo $RevSustituirCable;?>" /></td>
             			<td class="td_right" align="center"><input id="RevSustituirCableT2" name="RevSustituirCableT2" type="text" class="txtDec" maxlength="8" value="<?php echo $RevSustituirCableT2;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Sustitución de soporte superior (incluida certificación de línea de vida) :</td>
						<td class="td_center" align="center"><input id="RevSustituirSoporteSup" name="RevSustituirSoporteSup" type="text" class="txtDec" maxlength="8" value="<?php echo $RevSustituirSoporteSup;?>" /></td>
             			<td class="td_right" align="center"><input id="RevSustituirSoporteSupT2" name="RevSustituirSoporteSupT2" type="text" class="txtDec" maxlength="8" value="<?php echo $RevSustituirSoporteSupT2;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Certificación evacuador en nacelle :</td>
						<td class="td_center" align="center"><input id="RevEvacuadorNacelle" name="RevEvacuadorNacelle" type="text" class="txtDec" maxlength="8" value="<?php echo $RevEvacuadorNacelle;?>" /></td>
             			<td class="td_right" align="center"><input id="RevEvacuadorNacelleT2" name="RevEvacuadorNacelleT2" type="text" class="txtDec" maxlength="8" value="<?php echo $RevEvacuadorNacelleT2;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Certificación evacuador en ground :</td>
						<td class="td_center" align="center"><input id="RevEvacuadorGround" name="RevEvacuadorGround" type="text" class="txtDec" maxlength="8" value="<?php echo $RevEvacuadorGround;?>" /></td>
             			<td class="td_right" align="center"><input id="RevEvacuadorGroundT2" name="RevEvacuadorGroundT2" type="text" class="txtDec" maxlength="8" value="<?php echo $RevEvacuadorGround;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Certificación evacuador Ground/Nacelle junto a otra inspección :</td>
             			<td class="td_right" align="center" colspan=2><input id="RevEvacuadorNacelleT1" name="RevEvacuadorNacelleT1" type="text" class="txtDec" maxlength="8" value="<?php echo $RevEvacuadorNacelleT1;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Certificación 2 raíles de seguridad horizontal Soll + (2-6) puntos de anclaje + 4 carros de raíl G8x ó G8xHT :</td>
						<td class="td_center" align="center"><input id="RevRailesSeguridad" name="RevRailesSeguridad" type="text" class="txtDec" maxlength="8" value="<?php echo $RevRailesSeguridad;?>" /></td>
             			<td class="td_right" align="center"><input id="RevRailesSeguridadT2" name="RevRailesSeguridadT2" type="text" class="txtDec" maxlength="8" value="<?php echo $RevRailesSeguridadT2;?>" /></td>
					</tr>
					<tr>
						<td class="td_left">Certificación 2 raíles de seguridad horizontal Soll + (2-6) 
                        	puntos de anclaje + 4 carros de raíl G8x ó G8xHT junto a otra inspección :</td>
             			<td class="td_right" align="center" colspan=2><input id="RevRailesSeguridadT1" name="RevRailesSeguridadT1" type="text" class="txtDec" maxlength="8" value="<?php echo $RevRailesSeguridadT1;?>" /></td>
					</tr>
					<tr>
						<td class="td_left_end">Inspección Extintor :</td>
             			<td class="td_right_end" align="center" colspan=2><input id="RevInsExtintor" name="RevInsExtintor" type="text" class="txtDec" maxlength="8" value="<?php echo $RevInsExtintor;?>" /></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width=100% cellpading=0 cellspacing=0 class='table'>
					<tr>
						<td class='td_guardar'>
						  <span style='padding-right:20px'>
							<button id='guardar'>Guardar</button>
						  </span>
						  <span style='padding-right:30px'>
							<button id='cancelar'>Cancelar</button>
						  </span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
  </body>
</html>