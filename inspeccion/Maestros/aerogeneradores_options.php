<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../jq-config.php");

// Iniciar Variables
$nombre = $prefijo = $sufijo = ""; $extintores = 2; $idGrupo = 1;
// Conexión PDO
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
if (($action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "") == "add")
	$id = $_SESSION['ae_id'];
else
{
	$id = $_SESSION['ae_id'] = isset($_REQUEST["id"]) ? $_REQUEST["id"] : 0;
	if (($query = $conn->query("SELECT * FROM TAerogenerador WHERE Id=".$id)))
	{
		if (($row = $query->fetch(PDO::FETCH_ASSOC)))
		{
			$nombre = $row['Nombre'];
			$sufijo = $row['Sufijo'];
			$prefijo = $row['Prefijo'];
			$extintores = $row['Extintores'];
			$idGrupo = $row['IdGrupo'];
		}
		unset ($query, $row);
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
		<link rel="stylesheet" type="text/css" media="screen" href="../themes/smoothness/ui.jqgrid.css" />
		<link rel="stylesheet" type="text/css" href="../css/general.css" />
		<!--[if lt IE 9]>
		<script type="text/javascript" src="../js/jquery/html5shiv.js"></script>
		<![endif]-->
		<script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
		<script src="../js/jqgrid/i18n/grid.locale-es.js" type="text/javascript"></script>
		<script src="../js/jqgrid/jquery.jqGrid.min.js" type="text/javascript"></script>
     	<script src="../js/inc/functions.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$.jgrid.no_legacy_api = true;
				$.jgrid.useJSON = true;

				$("#idGrupo").change(function() {
					// Cargo los Precios del Grupo por AJAX
					$.ajax({
						type: "POST", url:"../ajax/maestros/Tabla01PvPa.php",
						data: {"idGrupo":$("#idGrupo").val()},
						success: function(sData) {
							$("#Precios").html(sData);
						},
						dataType: "text", async:false
					});
				});
				
				$("#idGrupo").val("<?php echo $idGrupo;?>").trigger("change");
				$("#nombre").focus();
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
		</style>
	</HEAD>
	<BODY>
		<FORM action="" method="post" enctype="application/x-www-form-urlencoded">
			<div style="border:1px solid #e9bc0c;width:675px;margin-left:25px;background-color:#f9f9f9;">
				<table>
					<tr>
						<td width="115px"><span style="line-height:20px;">Tipo Aerogenerador:</span></td>
						<td colspan="7">
                        	<input id="id" name="id" type="hidden" value="<?php echo $id;?>" />
                        	<input id="nombre" name="nombre" type="text" size="30" maxlength="60" value="<?php echo $nombre;?>" />
                        </td>
					</tr>
					<tr>
						<td><span style="line-height:28px;">Tipo GAMESA:</span></td>
						<td><input id="prefijo" name="prefijo" type="text" maxlength="10" size="10" value="<?php echo $prefijo;?>" /></td>
						<td style="padding-left:15px;"><span>Nº Serie:</span></td>
						<td><input id="sufijo" name="sufijo" type="text" maxlength="10" size="10" value="<?php echo $sufijo;?>" /></td>
                        <td style="padding-left:15px;"><span>Precios:</span></td>
                        <td>
                        	<SELECT id="idGrupo" name="idGrupo" SIZE=1>
                            	<OPTION VALUE='0'>Seleccione Precios...</OPTION>
                            <?php
							if (($query = $conn->query("SELECT * FROM PvpGrupos")))
							{
								while ($row = $query->fetch(PDO::FETCH_ASSOC))
									echo "<OPTION ".(($row['Id']==$idGrupo) ? "SELECTED" : "")." VALUE='".$row['Id']."'>".$row['Nombre']."</OPTION>";
								unset ($query, $row);
							}
							$conn = null;
                            ?>
							</SELECT>
                        </td>
						<td style="padding-left:15px;"><span>Extintores:</span></td>
						<td><input id="extintores" name="extintores" type="text" maxlength="2" size="2" style="text-align:right;" value="<?php echo $extintores;?>" /></td>
                	</tr>
				</table>
			</div>
            <div style="height:7.5px;"></div>
            <div id="Precios" style="width:675px;margin-left:25px;background-color:#f9f9f9;"></div>
            <div style="height:7.5px;"></div>
			<div style="width:675px;height:90px;margin-left:25px;background-color:#f9f9f9;">
              <?php include "aerogeneradores_pletinas.php";?>
			</div>
		</FORM>
	</BODY>
</HTML>