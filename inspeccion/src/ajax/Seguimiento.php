<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
$_SESSION["mi_url"] = "Inspeccion/".fPageName();
?>
<!DOCTYPE html>
<html>
  <head>
 	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>:: Inspección de instalaciones técnicas ::</title>
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/jq.ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/ui.multiselect.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../css/general.css" />
    <script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
	<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
    <script src="../js/jquery/jq.searchabledropdown-min.js" type="text/javascript"></script>
    <script src="../js/inc/functions.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();

			$("#VerPDF").button({
				icons: { primary: "ui-icon-pdf" }
			});
			$("#VerExcel").button({
				icons: { primary: "ui-icon-excel" }
			});
			
			$("#Tipo").change(function() {
				if ($(this).val() == "D")
					$(".gDescensor").show();
				else
					$(".gDescensor").hide();
					
				$.ajax({
					type: "POST", url:"../ajax/inspeccion/validaFEC.php",
					data: {"Tipo":$("#Tipo").val()},
					success: function(sData) {
						$("#Fecha").html(sData);
					},
					dataType: "text", async:false
				});					
			});

			$("#Tipo, #Fecha, #Parque, #Torre, #TipoAEG, #NSerie, #Fabricante, #OT, #Resultado").change(function() {
				if ($("#Tipo").val() == 'R')		 // Líneas de Vida
					sFichero = "Tabla03MyRa.php";
				else if ($("#Tipo").val() == 'D')	// Descensores
					sFichero = "Tabla03MyRb.php";
				else								 // Extintores
					sFichero = "Tabla03MyRc.php";

				$.ajax({
					type: "POST", url:"../ajax/inspeccion/" + sFichero,
					data: $("form").serialize(),
					success: function(sData) {
						$("#Lista").html(sData);
					},
					dataType: "text", async:false
				});
			});

			$("#VerPDF").click(function(event) {
				event.preventDefault();
				
				var sFichero = "Inspeccion/";				
				if ($("#Tipo").val() == 'R')		 // Líneas de Vida
					sFichero += "ImpSegRev.php?";
				else if ($("#Tipo").val() == 'D')	// Descensores
					sFichero += "ImpSegDes.php?";
				else								 // Extintores
					sFichero += "ImpSegExt.php?";
				sFichero += $("form").serialize();
				
				window.parent.$("#ModalPdf").html("<iframe allowtransparency='allowtransparency' id='vent_info' name='vent_info' src='"+sFichero+"' width='100%' height='99%' frameborder='0' scrolling='no'></iframe>");
				window.parent.$("#ModalPdf").attr("title", "SEGUIMIENTO DIARIO");
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

			$("#VerExcel").click(function(event) {
				event.preventDefault();
				var sFichero = "Inspeccion/GenSegDiario.php?" + $("form").serialize();

				window.parent.$("#ModalPdf").html("<iframe allowtransparency='allowtransparency' id='vent_info' name='vent_info' src='"+sFichero+"' width='100%' height='99%' frameborder='0' scrolling='no'></iframe>");
				return false;
			});

			$("#Tipo").trigger("change");
		});
		
		$(function() {
			$("#Parque, #TipoAEG").searchable();
		});
	</script>
	<style>
		.titulo{font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
		select {font-size:13px;color:#475767;}
		input  {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;color:#475767;font-size:13px;}
		input[type="text"]:focus, textarea:focus, select:focus
		{
			background-color: #FFC;
		}
	</style>
  </head>
  <body>
  	<div align=center style="margin-top:5px;padding-bottom:5px;">
		<FORM NAME="control" ACTION="" METHOD="post">
			<table class="table" cellpadding=0 cellspacing=0 width="90%">
				<tr>
					<td height=30>
						<table width="100%" cellpading=0 cellspacing=0>
							<tr>
								<td class="header_L">&nbsp;</td>
								<td class="header_C">
                                	<span class='header_title'>SEGUIMIENTO DIARIO</span>
                                </td>
								<td class="header_R">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
                <tr>
					<td style="padding-left:5px;border-left:1px solid #ddd;border-right:1px solid #ddd;border-bottom:1px solid #ddd;background-color:#fff;">
                    	<table width="100%" cellpading=0 cellspacing=0>
                        	<tr>
                            	<td>
								  <SELECT NAME="Tipo" ID="Tipo" SIZE="1">
        		                    <OPTION VALUE="D">Descensores</OPTION>
									<OPTION VALUE="R">Revisiones</OPTION>
                                    <OPTION VALUE="E">Extintores</OPTION>
								  </SELECT>
                                </td>
            			     	<td style='padding-left:2px;'>Fecha&nbsp;:&nbsp;</td>
								<td>
									<SELECT NAME="Fecha" ID="Fecha" SIZE=1>
										<OPTION VALUE=''></OPTION>
									</SELECT>
			                    </td>
								<td style='padding-left:2px;'>Parque&nbsp;:&nbsp;</td>
								<td>
									<SELECT NAME="Parque" ID="Parque" SIZE=1>
										<OPTION VALUE=''>Seleccione un Parque...</OPTION>
										<?php
										// Selecciono la Lista de Parques
										if (($result = mysql_query("SELECT * FROM Parques ORDER BY Nombre ASC;",$conn)))
										{
											while($row = mysql_fetch_row($result))
												echo "<OPTION VALUE='".$row[0]."'>".$row[1]."</OPTION>";
										}
										?>
									</SELECT>
								</td>
                                <td style='padding-left:2px;'>Torre&nbsp;:&nbsp;</td>
								<td>
            			        	<INPUT TYPE="text" ID="Torre" name="Torre" maxlength="8" style="width:60px;text-align:right;"/>
			                    </td>
                                <td style='padding-left:2px;'>Tipo&nbsp;AEG&nbsp;:&nbsp;</td>
								<td>
									<SELECT NAME="TipoAEG" ID="TipoAEG" SIZE=1>
										<OPTION VALUE=''>Seleccione un AEG...</OPTION>
										<?php
										// Selecciono la Lista de Aerogeneradores
										if (($result = mysql_query("SELECT DISTINCT TipoAerogenerador, TipoAerogeneradorGAMESA FROM Lineas",$conn)))
										{
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
                <tr>
					<td style="padding-left:5px;border-left:1px solid #ddd;border-right:1px solid #ddd;border-bottom:1px solid #ddd;background-color:#fff;">
						<table width="100%" cellpading=0 cellspacing=0>
                        	<tr>
                                <td style='padding-left:2px;'>Nº&nbsp;Serie&nbsp;:&nbsp;</td>
								<td>
            			        	<INPUT TYPE="text" ID="NSerie" name="NSerie" size="40" maxlength="40" />
			                    </td>
        		            	<td class="gDescensor" style='padding-left:2px;'>Fabricante:</td>
								<td class="gDescensor">
									<SELECT NAME="Fabricante" ID="Fabricante" SIZE=1>
									  <OPTION VALUE=''>Todos</OPTION>
									  <?php
									  // Selecciono la Lista Fabricante Descensor
									  if (($result = mysql_query("SELECT DISTINCT LC.Fabricante, A.Nombre FROM ListaCtrlDes LC JOIN MarcaDes A ON A.Id=LC.Fabricante",$conn)))
									  {
										while($row = mysql_fetch_row($result))
											echo "<OPTION VALUE='".$row[0]."'>".$row[1]."</OPTION>";
									  }
									  ?>
									</SELECT>
								</td>
            			     	<td style='padding-left:2px;'>OT&nbsp;:&nbsp;</td>
								<td>
            			        	<INPUT TYPE="text" ID="OT" name="OT" maxlength="20" style="width:145px;"/>
			                    </td>
            			     	<td style='padding-left:2px;'>Resultado&nbsp;:&nbsp;</td>
								<td>
									<SELECT ID="Resultado" name="Resultado" SIZE="1">
                                    	<OPTION VALUE="">Todos</OPTION>
										<OPTION VALUE="1">Ok</OPTION>
										<OPTION VALUE="0">NO Ok</OPTION>
									</SELECT>
			                    </td>
                            </tr>
                    	</table>
					</td>
            	</tr>
			  <tr>
                <td style="height:35px;padding-left:5px;border-left:1px solid #ddd;border-right:1px solid #ddd;border-bottom:1px solid #ddd;background-color:#fff;">
				  <table width="100%">
					<tr align="center">
                      <td><button id="VerPDF">PDF</button></td>
                      <td><button id="VerExcel">Excel</button></td>
					</tr>
				  </table>
               	</td>
			  </tr>
			</table>
		</FORM>
        <div id="Lista" align=center style="padding-top:5px;">
        </div>
	</div>
  </body>
</html>
<?php
if ($conn)
	mysql_close($conn);
?>