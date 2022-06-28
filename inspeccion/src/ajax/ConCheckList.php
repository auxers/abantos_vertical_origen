<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
$_SESSION["mi_url"] = "Inspeccion/".fPageName();

$Tipo = (isset($_REQUEST['Tipo'])) ? $_REQUEST['Tipo'] : "";
$Parque = (isset($_REQUEST['Parque'])) ? $_REQUEST['Parque'] : "";
$FechaIni = (isset($_REQUEST['FechaIni'])) ? $_REQUEST['FechaIni'] : date("01/01/Y");
$FechaFin = (isset($_REQUEST['FechaFin'])) ? $_REQUEST['FechaFin'] : date("31/12/Y");
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
    <script src="../js/jquery/jq.ui.datepicker-es.js" type="text/javascript"></script>
    <script src="../js/jquery/jq.maskedinput-min.js" type="text/javascript"></script>
   	<script src="../js/jquery/jq.searchabledropdown-min.js" type="text/javascript"></script>
	<style>
		.titulo{font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
		select {font-size:13px;color:#475767;}
		input  {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:13px;color:#475767;text-align:right;}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
			$("#ver").button({
				icons: { primary: "ui-icon-pdf"}
			});

			$("#Form").submit(function() {
				var sFichero = "Inspeccion/ImpCheckList.php?Tipo="+$("#Tipo").val()+"&Parque="+$("#Parque").val()+
					"&FechaIni="+$("#FechaIni").val()+"&FechaFin="+$("#FechaFin").val();
				if ($("#Resultado").val() != "")
					sFichero += "&Resultado="+$("#Resultado").val();

				window.parent.$("#ModalPdf").html("<iframe allowtransparency='allowtransparency' id='vent_info' name='vent_info' src='"+sFichero+"' width='100%' height='99%' frameborder='0' scrolling='no'></iframe>");
				window.parent.$("#ModalPdf").attr("title", "LISTAS DE CONTROL");
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
			});
			
			$("#Parque").searchable();
		});
		
		$(function() {
			$(".Fecha").datepicker().mask("99/99/9999");
		});
	</script>
</head>
<body>
	<div align=center style="margin-top:5px;padding-bottom:10px;" >
		<form name="control" id="Form" method="post">
			<table class="table" cellpadding=0 cellspacing=0 width="60%">
				<tr>
					<td height=30>
						<table width=100% cellpading=0 cellspacing=0>
							<tr>
								<td class="header_L">&nbsp;</td>
								<td class="header_C"><span class='header_title'>Consulta LISTAS DE CONTROL</span></td>
								<td class="header_R">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height=auto valign=top style="padding:10px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;border-bottom:1px solid #dddddd;background-color:#ffffff;">
						<table width="100%" cellpadding=0 cellspacing=0 class="table" bgcolor='#ffffff'>
							<tr>
                                <td>
									<SELECT id="Tipo" name="Tipo" SIZE="1">
                                    	<OPTION VALUE="M" <?php echo ($Tipo=="M") ? "SELECTED" : "";?>>Montaje</OPTION>
                                        <OPTION VALUE="R" <?php echo ($Tipo=="R") ? "SELECTED" : "";?>>Revisión</OPTION>
                                        <OPTION VALUE="E" <?php echo ($Tipo=="E") ? "SELECTED" : "";?>>Extintores</OPTION>
                                        <OPTION VALUE="D" <?php echo ($Tipo=="D") ? "SELECTED" : "";?>>Descensores</OPTION>
									</SELECT>
                                </td>
								<td style="padding-left:20px;">Parque&nbsp;:&nbsp;</td>
                                <td>
									<SELECT NAME="Parque" ID="Parque" SIZE="1">
										<?php
										// Cargamos la Lista de Parques existentes.
										if (($Result = mysql_query("SELECT * FROM Parques ORDER BY Nombre ASC;", $conn)))
										{
											while($row = mysql_fetch_row($Result))
												echo "<OPTION VALUE='".$row[0]."' ".(($Parque == $row[0]) ? "SELECTED":"").">".$row[1]."</OPTION>";
										}
										?>
									</SELECT>
								</td>
                                <td style="padding-left:20px;">Fecha&nbsp;:&nbsp;</td>
								<td>
                                    <INPUT type="text" id="FechaIni" name="FechaIni" class="Fecha" size="8" maxlength="10" value="<?php echo $FechaIni;?>">
                                    <INPUT type="text" id="FechaFin" name="FechaFin" class="Fecha" size="8" maxlength="10" value="<?php echo $FechaFin;?>">
                                </td>
                                <td style="padding-left:20px;">Resultado&nbsp;:&nbsp;</td>
                                <td>
									<SELECT id="Resultado" name="Resultado" SIZE="1">
                                        <OPTION VALUE="1">Ok</OPTION>
                                        <OPTION VALUE="2">No OK</OPTION>
                                    	<OPTION VALUE="">Todos</OPTION>
									</SELECT>
                                </td>
								<td style="padding-left:25px;">
                                	<BUTTON id="ver">Ver</BUTTON>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>
<?php
if ($conn)
	mysql_close($conn);
?>