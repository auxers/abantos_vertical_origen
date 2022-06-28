<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../jq-config.php");

// Conexión PDO
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Iniciar Variables
$NTorre = $TipoGAMESA = $NLV1 = $NLV2 = $NLC1 = $NLC2 = "";
$IdTipoAEG = $IdAltura = $IdMarca = 1;
if (($action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "") == "add")
	$Id = $_SESSION['ae_id'];
else
{
	$Id = $_SESSION['ae_id'] = isset($_REQUEST["Id"]) ? $_REQUEST["Id"] : 0;
	$sqlTmp = "SELECT L.*,
		(SELECT L0.NumeroSerie FROM LineasPletina L0 INNER JOIN Pletinas p0 ON p0.Id = L0.IdPletina WHERE p0.Tipo=2 AND L0.IdLinea=L.Id) AS LineaServicio, 
		(SELECT L1.NumeroCable FROM LineasPletina L1 INNER JOIN Pletinas p1 ON p1.Id = L1.IdPletina WHERE p1.Tipo=2 AND L1.IdLinea=L.Id) AS CableServicio, 
		(SELECT L2.NumeroSerie FROM LineasPletina L2 INNER JOIN Pletinas p2 ON p2.Id = L2.IdPletina WHERE p2.Tipo=1 AND L2.IdLinea=L.Id) AS LineaNacelle,
		(SELECT L3.NumeroCable FROM LineasPletina L3 INNER JOIN Pletinas p3 ON p3.Id = L3.IdPletina WHERE p3.Tipo=1 AND L3.IdLinea=L.Id) AS CableNacelle 
		FROM Lineas L WHERE L.Id=".$Id;
	if (($query = $conn->query($sqlTmp)))
	{
		if (($row = $query->fetch(PDO::FETCH_ASSOC)))
		{
			$NTorre = $row['NumeroTorre'];
			$IdAltura = $row['IdAltura'];
			$IdTipoAEG = $row['TipoAerogenerador'];
			$TipoGAMESA = $row['TipoAerogeneradorGAMESA'];
			$IdMarca = $row['IdMarca'];

			$NLV1 = $row['LineaNacelle'];
			$NLC1 = $row['CableNacelle'];
			$NLV2 = $row['LineaServicio'];
			$NLC2 = $row['CableServicio'];
		}
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

				$("#TipoAEG").change(function() {
					$.ajax({
						type: "POST", url:"../ajax/maestros/validaAEG.php",
						data: {"TipoAEG":$("#TipoAEG").val()},
						success: function(sData) {
							if (sData < 2)
								$(".Servicio").hide();
							else
								$(".Servicio").show();
						},
						dataType: "text", async:false
					});
				});
		
				$("#TipoAEG").val("<?php echo $IdTipoAEG;?>").trigger("change");
				$("#NTorre").focus();
			});
			
			function fCompruebaDES(id)
			{   // Función comprueba el Tipo de AEG, para habilitar ó no la línea de Servicio...
				var sId = "#"+fExtraer(id);
				
				$.ajax({
					type: "POST", url:"../ajax/maestros/validaMOD.php",
					data: {"Marca":$(sId+"_Marca").val()},
					success: function(sData) {
						$(sId+"_Modelo").html(sData);
					},
					dataType: "text", async:false
				});
			}
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
		<div style="border:1px solid #e9bc0c;width:750px;margin-left:45px;background-color:#f9f9f9;">
			<FORM action="" method="post" enctype="application/x-www-form-urlencoded">
				<table>
					<tr>
					  <td align="center">
						<table>
                        	<td><span>Nº Torre</span></td>
							<td>
    	                    	<input id="Id" name="Id" type="hidden" value="<?php echo $Id;?>" />
        	                	<input id="NTorre" name="NTorre" type="text" size="5" maxlength="5" value="<?php echo $NTorre;?>" style="text-align:right;"/>
            	            </td>
                	        <td><span>Tipo Aerogenerador</span></td>
                    	    <td>
                        		<SELECT id="TipoAEG" name="TipoAEG" SIZE=1>
	                            <?php
								if (($query = $conn->query("SELECT Id,Nombre FROM TAerogenerador")))
								{
									while ($row = $query->fetch(PDO::FETCH_ASSOC))
										echo "<OPTION ".(($row['Id']==$IdTipoAEG) ? "SELECTED" : "")." VALUE='".$row['Id']."'>".$row['Nombre']."</OPTION>";
								}
                        	    ?>
								</SELECT>
	                        </td>
    	                    <td><span>Tipo Altura</span></td>
        	                <td>
            	            	<SELECT id="Altura" name="Altura" SIZE=1>
                	            <?php
								if (($query = $conn->query("SELECT * FROM Alturas")))
								{
									while ($row = $query->fetch(PDO::FETCH_ASSOC))
										echo "<OPTION ".(($row['Id']==$IdAltura) ? "SELECTED" : "")." VALUE='".$row['Id']."'>".$row['Nombre']."</OPTION>";
								}
    	                        ?>
								</SELECT>
	                        </td>
    	                    <td><span>Marca LV</span></td>
        	                <td>
            	            	<SELECT id="Marca" name="Marca" SIZE=1>
                	            <?php
								if (($query = $conn->query("SELECT * FROM MarcaLin")))
								{
									while ($row = $query->fetch(PDO::FETCH_ASSOC))
										echo "<OPTION ".(($row['Id']==$IdMarca) ? "SELECTED" : "")." VALUE='".$row['Id']."'>".$row['Nombre']."</OPTION>";
								}
    	                        ?>
								</SELECT>
	                        </td>                            
                        </table>
                      </td>
					</tr>
					<tr>
					  <td align="left">
						<table>
                           <tr class="Servicio">
                        	<td width="100" align="center"><span>Línea de Servicio</span></td>
							<td><span>Nº Línea</span></td>
                            <td>
                        	  <input id="LineaServicio" name="LineaServicio" type="text" size="40" maxlength="40" value="<?php echo $NLV2;?>" />
                        	</td>
							<td><span>Nº Cable</span></td>
                            <td>
                        	  <input id="CableServicio" name="CableServicio" type="text" size="25" maxlength="25" value="<?php echo $NLC2;?>" />
                        	</td>
                           </tr>
                           <tr class="Nacelle">
                        	<td width="100" align="center"><span>Línea de Nacelle</span></td>
							<td><span>Nº Línea</span></td>
                            <td>
                        	  <input id="LineaNacelle" name="LineaNacelle" type="text" size="40" maxlength="40" value="<?php echo $NLV1;?>" />
                        	</td>
							<td><span>Nº Cable</span></td>
                            <td>
                        	  <input id="CableNacelle" name="CableNacelle" type="text" size="25" maxlength="25" value="<?php echo $NLC1;?>" />
                        	</td>
                           </tr>
                        </table>
                      </td>
					</tr>
				</table>
			</FORM>
		</div>
		<div style="height:5px;"></div>
		<span>Extintores</span>
        <div style="height:5px;"></div>
		<div style="width:852px;height:90px;margin-left:5px;background-color:#f9f9f9;">
		  <?php include "parques_his_extintores.php";?>
		</div>
		<div style="height:55px;"></div>
		<span>Descensores</span>
        <div style="height:5px;"></div>
		<div style="width:852px;height:112px;margin-left:5px;background-color:#f9f9f9;overflow:auto;">
		  <?php include "parques_his_descensores.php";?>
		</div>
	</BODY>
</HTML>
<?php 
$conn=null;
?>