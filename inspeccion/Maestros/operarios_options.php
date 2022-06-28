<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$Nombre = $Login = $Password = $Firma = ""; $Nivel = 1;
if (($action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "") == "add")
	$Id = $_SESSION['ae_id'];
else
{
	$Id = $_SESSION['ae_id'] = isset($_REQUEST["Id"]) ? $_REQUEST["Id"] : 0;
	if (($result = mysql_query("SELECT * FROM Trabajadores WHERE Id=".$Id, $conn)))
	{
		if (($row = mysql_fetch_array($result)))
		{
			$Nombre = $row['Nombre'];
			$Login  = $row['Login'];
			$Password = $row['Password'];
			$Nivel = $row['Nivel'];
			$Firma = $row['Firma'];
		}	
		unset($result, $row);
	}
}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
  		<meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  		<title>:: Inspección de instalaciones técnicas ::</title>
        <link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/jq.ui-1.8.22.custom.css" />
		<link rel="stylesheet" type="text/css" href="../css/general.css"/>
		<!--[if lt IE 9]>
		<script type="text/javascript" src="../js/jquery/html5shiv.js"></script>
		<![endif]-->
		<script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
        <script src="../js/jquery/jq.AjaxUpload-min.js" language="javascript"></script>
     	<script src="../js/inc/functions.js" type="text/javascript"></script>
	    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
		<script type="text/javascript">
			$(document).ready(function() {
				$("#bUpload").button({
					icons: { primary: "ui-icon-image" }
				});
				$("#bUnLock").button({
					icons: { primary: "ui-icon-check" }
				});
				
				new AjaxUpload("#bUpload", {
       				action: "../ajax/principal/upload.php?param=" + $("#Id").val(),
					onSubmit : function(file, ext) {
						if (!(ext && /^(jpg|png|jpeg|gif)$/.test(ext)))
						{   // Extensiones permitidas
							jAlert("Archivo no permitido", null);
							return false;		// Cancela upload
						} else
							this.disable();
					},
					onComplete: function(file, response) {
						$("#Firma").val(response).select();
						this.enable();		// Enable upload button
					}
				});

				$("#bUnLock").click(function(event) {
					event.preventDefault();
					
					$.ajax({
						type: "POST", url:"../ajax/principal/unlock.php",
						data: {"User":$("#Id").val()},
						success: function(sData) {
							jAlert(sData, null);
						}, dataType:"text", async:false
					});
				});
				
				$("#Nombre").focus();
			});
		</script>
		<style>
			span {
				color:#29384C;
				font-family: Segoe UI, Calibri, Helvetica, Arial, sans-serif; 
				font-size: 12px; font-weight:bold; line-height:20px; margin-right:0px;
			}
			input, select {
				font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;
				font-size:12px; color:#003;
			}
			input[type="text"]:focus, textarea:focus {
				background-color: #FFC;
			}
		</style>
	</HEAD>
	<BODY>
		<FORM action="" method="post" enctype="application/x-www-form-urlencoded">
			<div style="border:1px solid #e9bc0c;width:500px;height:140px;margin-left:20px;background-color:#f9f9f9;">
				<table>
					<tr>
						<td><span>Nombre</span></td>
						<td colspan="4">
                        	<input id="Id" name="Id" type="hidden" value="<?php echo $Id;?>" />
                        	<input id="Nombre" name="Nombre" type="text" size="50" maxlength="50" value="<?php echo $Nombre;?>" />
                        </td>
					</tr>
					<tr>
						<td><span>Login</span></td>
						<td><input id="Login" name="Login" type="text" size="8"  maxlength="8" value="<?php echo $Login;?>" /></td>
						<td><span>Password</span></td>
						<td colspan="2"><input id="Password" name="Password" type="text" size="15"  maxlength="15" value="<?php echo $Password;?>" /></td>
                	</tr>
					<tr>
						<td><span>Privilegios</span></td>
                        <td>
                        	<SELECT id="Nivel" name="Nivel" SIZE=1>
								<OPTION <?php echo ($Nivel == 1) ? "SELECTED" : ""; ?> VALUE='1'>Inspector</OPTION>
								<OPTION <?php echo ($Nivel == 2) ? "SELECTED" : ""; ?> VALUE='2'>Avanzado</OPTION>
								<OPTION <?php echo ($Nivel == 5) ? "SELECTED" : ""; ?> VALUE='5'>Administrador</OPTION>
							</SELECT>
                        </td>                    
						<td><span>Firma</span></td>
						<td><input id="Firma" name="Firma" type="text" size="15" maxlength="15" value="<?php echo $Firma;?>" /></td>
                        <td><div id="bUpload"><span>Imagen</span></div></td>
					</tr>
					<tr>
						<td colspan="5" align="center"><BUTTON id="bUnLock">Desbloquear</BUTTON></td>
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