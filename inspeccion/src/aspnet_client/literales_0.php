<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
$_SESSION["mi_url"] = "Maestros/".fPageName();
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
	<script src="../js/jqgrid/i18n/grid.locale-es.js" type="text/javascript"></script>
	<script src="../js/jqgrid/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="../js/inc/functions.js" type="text/javascript"></script>
    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
			
			$("#Tipo").change(function() {
				$.ajax({
					type: "POST", url:"../ajax/maestros/CargaGrupos.php",
					data: {"Tipo":$("#Tipo").val()},
					success: function(sData) {
						$("#Grupo").html(sData);
					},
					dataType: "text", async:false
				});								
			});
			
			$("#Tipo,#Grupo,#Idioma").change(function() {
				if ($("#Tipo").val() != "" && $("#Grupo").val() != "" && $("#Idioma").val() != "")
				{
					$.ajax({
						type: "POST", url:"literales_1.php",
						data: {"Tipo":$("#Tipo").val(),"Grupo":$("#Grupo").val(),"Idioma":$("#Idioma").val()},
						success: function(sData) {
							if (sData != "")
								$("#Lista").html(sData);
							else
							{
								jConfirm("No existen Literales para éste Idioma, Desea copiar los de Español?", "Atención", function(nRes) {
									if (nRes)
									{
										$.ajax(
										{
									        type: "POST",
									        url: "../ajax/maestros/CreaLiteral.php",
											data: {"Tipo":$("#Tipo").val(),"Grupo":$("#Grupo").val(),"Idioma":$("#Idioma").val()},
											dataType: "text", async:false,
											success: function(sDato) {
												if (sDato == "")
													$("#Tipo").trigger("change");
											}
										});
									}
									else
										$("#Lista").html("");
								});
							}
						},
						dataType: "text", async:false
					});
				}
				else
					$("#Lista").html("");
			});
			
			$.jgrid.no_legacy_api = true;
			$.jgrid.useJSON = true;
		});
	</script>
	<style>
		.titulo {font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
		select {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:12px;border:1px solid #000;color:#475767;}
		input {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:12px;color:#475767;margin:0px; border:1px solid #000;}
	</style>
  </head>
  <body>
	<div align=center style="margin-top:5px;padding-bottom:5px;">
		<FORM ID="Control" ACTION="" METHOD="post">
			<table class="table" cellpadding=0 cellspacing=0 width=90%>
				<tr>
					<td height=25>
						<table width=100% cellpading=0 cellspacing=0>
							<tr>
								<td class="header_L">&nbsp;</td>
								<td class="header_C"><span class='header_title'>Literales</span></td>
								<td class="header_R">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height=auto valign=top style="padding:10px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;border-bottom:1px solid #dddddd;background-color:#ffffff;">
						<table width=100% cellpadding=0 cellspacing=0 class='table' bgcolor='#ffffff'>
							<tr>
								<td>Tipo&nbsp;:&nbsp;</td>
                                <td>
									<SELECT NAME="Tipo" ID="Tipo" SIZE=1>
                                    	<OPTION VALUE="">Seleccione ...</OPTION>
                                    	<OPTION VALUE="1">CheckList LV</OPTION>
                                        <OPTION VALUE="2">CheckList DE</OPTION>
                                        <OPTION VALUE="3">CheckList EX</OPTION>
                                        <OPTION VALUE="4">Certificado LV</OPTION>
                                        <OPTION VALUE="5">Certificado DE</OPTION>
                                        <OPTION VALUE="6">Meses Año</OPTION>
                                        <OPTION VALUE="7">TxtCheck LV</OPTION>
									</SELECT>
                                </td>
                                <td>Grupo&nbsp;:&nbsp;</td>
                                <td>
                                	<SELECT NAME="Grupo" ID="Grupo" SIZE=1>
                                    	<OPTION VALUE="">Seleccione ...</OPTION>
									</SELECT>                                    
                                </td>
								<td>Idioma&nbsp;:&nbsp;</td>
								<td>
									<SELECT NAME="Idioma" ID="Idioma" SIZE=1>
                                    	<OPTION VALUE=''>Seleccione un idioma...</OPTION>
										<?php
										if (($result = mysql_query("SELECT * FROM Idiomas ORDER BY Nombre ASC;",$conn))) {
											while($row = mysql_fetch_row($result))
												echo "<OPTION VALUE='".$row[0]."'>".$row[1]."</OPTION>";
										}
										?>
									</SELECT>
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