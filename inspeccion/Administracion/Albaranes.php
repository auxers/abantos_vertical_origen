<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$_SESSION["mi_url"] = "Administracion/".fPageName();
$Mes = (isset($_REQUEST['Mes'])) ? $_REQUEST['Mes'] : "";
?>
<!DOCTYPE html>
<html>
  <head>
 	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>:: Inspección de instalaciones técnicas ::</title>
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/jq.ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/ui.multiselect.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/ui.jqgrid.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../css/general.css" />
    <script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
    <script src="../js/jquery/jq.searchabledropdown-min.js" type="text/javascript"></script>
	<script src="../js/jqgrid/i18n/grid.locale-es.js" type="text/javascript"></script>
	<script src="../js/jqgrid/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="../js/inc/functions.js" type="text/javascript"></script>
    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
			$("#VerPDF").button({
				icons: {primary: "ui-icon-pdf"}
			});

			$("#Tipo").change(function() {
				$(".radio-toolbar").hide();				// Oculto los botones de Opción entre LV ó MX.
				$("#ChkExcel").removeAttr("checked");	  // Deshabilito Opcion Excel...
				
				if ($(this).val() == "M")
					$(".radio-toolbar").show();
				else if ($(this).val() == "R")
					$("#ChkExcel").attr("checked", true);
			});

			$("#Tipo,#Parque,#Mes,#Anyo").change(function() {
				var sExcel = ($("#ChkExcel").attr("checked")) ? "1" : "0";			
				if (!$.isNumeric($("#Anyo").val()))
					$("#Anyo").val("<?php echo date('Y');?>");

				if ($("#Tipo").val() != "")
				{
					$.ajax({
						type: "POST", url:"Albaranes_1.php",
						data: {"Tipo":$("#Tipo").val(),"Parque":$("#Parque").val(),"Mes":$("#Mes").val(),"Anyo":$("#Anyo").val(),"Excel":sExcel},
						success: function(sData) {
							$("#Lista").html(sData);
						},
						dataType: "text", async:false
					});
				}
				else
					$("#Lista").html("");
			});
			
			$("#VerPDF").click(function(event) {
				event.preventDefault();
				
				if ($("#Tipo").val() != "")
				{
					var sExcel = ($("#ChkExcel").attr("checked")) ? "1" : "0";
					var sFichero = "ImpAlbaran.php?Parque="+$("#Parque").val()+
						"&Mes="+$("#Mes").val()+"&Anyo="+$("#Anyo").val()+"&Excel="+sExcel;

					if ($("#Tipo").val() == "M")
					{
						if ($("#radio1").attr("checked"))
							sFichero += "&Tipo=1";
						else if ($("#radio2").attr("checked"))
							sFichero += "&Tipo=2";
					}
					else
						sFichero += "&Tipo=3";
		
					window.parent.$("#ModalPdf").html("<iframe allowtransparency='allowtransparency' id='vent_info' name='vent_info' src='Administracion/"+sFichero+"' width='100%' height='99%' frameborder='0' scrolling='no'></iframe>");
					if (sExcel != "1")
					{
						window.parent.$("#ModalPdf").attr("title", "ALBARANES");
						window.parent.$("#ModalPdf").dialog({
							resizable: false,
							height: 650,
							width: 950,
							modal: true,
							buttons: {
								"Cerrar": function() {
									window.parent.$(this).dialog("close");
								}
							}
						});
					}
					
					if ($("#Tipo").val() == "M")
						$("#radio1,#radio2").removeAttr("checked");	  // Deshabilito Opciones...
				}
				else
					jAlert("Debe seleccionar el Tipo Albarán", "Atención",$("#Tipo"));
			});
			
			$.jgrid.no_legacy_api = true;
			$.jgrid.useJSON = true;
			$("#Parque").searchable().val("<?php echo (isset($_REQUEST['Parque'])) ? $_REQUEST['Parque']:"";?>");
			$("#Tipo").val("<?php echo (isset($_REQUEST['Tipo'])) ? $_REQUEST['Tipo']:"";?>").trigger("change");
		});
	</script>
	<style>
		select {font-size:13px;color:#475767;}
		input  {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:12px;color:#475767;border:1px solid #000;}
		.titulo {font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
		.radio-toolbar input[type="radio"] {display:none;}
		.radio-toolbar label {
			border:1px solid #cccccc;
			display:inline-block;
			height: 18px;
			background-color:#fafafa;
			padding:0px 11px;
			font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;
			font-size:12px;
			cursor:pointer;
		}
		.radio-toolbar input[type="radio"]:checked + label { 
			background-color:#fdf9e1; border:1px solid #e9bc0c; color: #ebbc0c;
		}​
	</style>
  </head>
  <body>
	<div align=center style="margin-top:5px;padding-bottom:5px;">
		<form name="Control" id="Form" method="post">
			<table class="table" cellpadding=0 cellspacing=0 width="95%">
				<tr>
					<td height=25>
						<table width=100% cellpading=0 cellspacing=0>
							<tr>
								<td class="header_L">&nbsp;</td>
								<td class="header_C"><span class='header_title'>ALBARANES DE MONTAJE Y REVISIÓN</span></td>
								<td class="header_R">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height=45 valign=top style="padding:10px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;border-bottom:1px solid #dddddd;background-color:#ffffff;">
						<table width=100% cellpadding=0 cellspacing=0 class='table' bgcolor='#ffffff'>
							<tr>
                                <td>
									<SELECT NAME="Tipo" ID="Tipo" SIZE=1>
                                    	<OPTION VALUE="">Seleccione...</OPTION>
                                    	<OPTION VALUE="M">Montaje</OPTION>
                                        <OPTION VALUE="R">Revisión</OPTION>
									</SELECT>
                                </td>
								<td>Parque&nbsp;:&nbsp;</td>
								<td>
									<SELECT NAME="Parque" ID="Parque" SIZE=1>
                                    	<OPTION VALUE=''>Seleccione un parque...</OPTION>
										<?php
//										if (($result = mysql_query("SELECT * FROM Parques ORDER BY Nombre ASC;", $conn)))
//										{   // Selecciono la Lista de Parques
//											while($row = mysql_fetch_row($result))
//												echo "<OPTION VALUE='".$row[0]."'>".$row[1]."</OPTION>";
//											unset($result, $row);
//										}

$res = $mysqli->query("SELECT * FROM Parques ORDER BY Nombre ASC;");
while ($row=$res->fetch_assoc()){
	echo "<OPTION VALUE='".$row["Id"]."'>".$row["Nombre"]."</OPTION>";
}
										?>
									</SELECT>
								</td>
								<td style='padding-left:5px;'>Mes:&nbsp;</td>
								<td>
									<SELECT NAME="Mes" ID="Mes" SIZE=1>
                                    	<OPTION VALUE=''>Seleccione el mes...</OPTION>
										<OPTION VALUE='01' <?php echo $Mes=='01'?'SELECTED':'';?>>Enero</OPTION>
										<OPTION VALUE='02' <?php echo $Mes=='02'?'SELECTED':'';?>>Febrero</OPTION>
										<OPTION VALUE='03' <?php echo $Mes=='03'?'SELECTED':'';?>>Marzo</OPTION>
										<OPTION VALUE='04' <?php echo $Mes=='04'?'SELECTED':'';?>>Abril</OPTION>
										<OPTION VALUE='05' <?php echo $Mes=='05'?'SELECTED':'';?>>Mayo</OPTION>
										<OPTION VALUE='06' <?php echo $Mes=='06'?'SELECTED':'';?>>Junio</OPTION>
										<OPTION VALUE='07' <?php echo $Mes=='07'?'SELECTED':'';?>>Julio</OPTION>
										<OPTION VALUE='08' <?php echo $Mes=='08'?'SELECTED':'';?>>Agosto</OPTION>
										<OPTION VALUE='09' <?php echo $Mes=='09'?'SELECTED':'';?>>Septiembre</OPTION>
										<OPTION VALUE='10' <?php echo $Mes=='10'?'SELECTED':'';?>>Octubre</OPTION>
										<OPTION VALUE='11' <?php echo $Mes=='11'?'SELECTED':'';?>>Noviembre</OPTION>
										<OPTION VALUE='12' <?php echo $Mes=='12'?'SELECTED':'';?>>Diciembre</OPTION>
									</SELECT>
								</td>
								<td style='padding-left:2.5px;'>
                                	Año&nbsp;:&nbsp;
									<INPUT type="text" id="Anyo" name="Anyo" maxlength="4" value="<?php echo (isset($_REQUEST['Anyo']))?$_REQUEST['Anyo']:date('Y');?>" style="text-align:right;width:30px;">
								</td>
								<td style='padding-left:2.5px;' class="radio-toolbar">
									<input type="radio" id="radio1" name="GTipoAlb" value="1">
									<label for="radio1">Líneas Vida</label>
                                    <br/>
									<input type="radio" id="radio2" name="GTipoAlb" value="2">
									<label for="radio2">Materiales&nbsp;</label>
								</td>
								<td style='padding-left:5px;'>
                                	Excel&nbsp;:&nbsp;
                                	<input type="checkbox" id="ChkExcel" name="ChkExcel" value="1">
                                </td>
								<td style='padding-left:5px;'>
                                	<BUTTON id="VerPDF">Ver</BUTTON>
                                </td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<div id="Lista" align=center style="padding-bottom:5px;"></div>
  </body>
</html>
<?php
if ($conn)
	mysql_close($conn);
?>