<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$CabeceraImpresa = NULL;
$Tipo = $NombreParque = "";
$Pendiente = (isset($_REQUEST['Pendiente']) ) ? true : false;
if (($Archivo = isset($_REQUEST['Editar']) ? $_REQUEST['Editar'] : "") != "")
	$Tipo = substr($Archivo, 0, 1);
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
	<script src="../js/inc/functions.js" type="text/javascript"></script>
    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
	<style>
		.titulo{font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
			
			$("#validar").button({
				icons: { primary: "ui-icon-check" }
			});
			$("#verpdf").button({
				icons: { primary: "ui-icon-pdf" }
			});
			$("#cerrar").button({
				icons: { primary: "ui-icon-close" }
			});

			$("#validar").click(function(event) {
				event.preventDefault();
				
				jConfirm("Seguro que desea que se pase a Validar?", "Atención", function(nRes) {
					if (nRes)
					{
						$.ajax(
						{							
					        type: "POST",
					        url: "../ajax/inspeccion/validaXML.php?File=<?php echo $Archivo;?>",
							dataType: "text", async:false,
							success: function(sDato) {
								if (sDato != "")
									jAlert (sDato, null);
								else
									window.location = "InsPendientes.php";
							}
						});
					}
				});
			});

			$("#verpdf").click(function(event) {
				event.preventDefault();
				
				var sFichero = "ImpLCMyR.php?FileXml=<?php echo $Archivo;?>";
				window.parent.$("#ModalPdf").html("<iframe allowtransparency='allowtransparency' id='vent_info' name='vent_info' src='Inspeccion/"+sFichero+"' width='100%' height='99%' frameborder='0' scrolling='no'></iframe>");
				window.parent.$("#ModalPdf").attr("title", "LISTA CONTROL");
				window.parent.$("#ModalPdf").dialog({
					resizable: false,
					height: 750,
					width: 950,
					modal: true,
					buttons: {
						"Cerrar": function() {
							window.parent.$(this).dialog("close");
						}
					}
				});
			});
			
			$("#cerrar").click(function() {
				window.location = "<?php echo ($Pendiente)? "InsPendientes.php":"ValidarCLMyRLV.php?Tipo=".$Tipo;?>";
			});
		});
	</script>
</head>
<body>
  <div width=90%>
	<div width=100% align=center style="margin-top:5px;">
		<table class="table" cellpadding=0 cellspacing=0 width=60%>
			<tr>
				<td height=27>
					<table width=100% cellpading=0 cellspacing=0>
						<tr>
							<td class="header_L">&nbsp;</td>
							<td class="header_C"><span class='header_title'>VALIDAR LISTAS DE CONTROL <?php echo ($Tipo=="M")?"MONTAJE":"REVISIÓN";?>: Editar</span></td>
							<td class="header_C" style="padding-top:3.5px;padding-right:5px;">
                            	<a href="<?php echo ($Pendiente)? "InsPendientes.php":"ValidarCLMyRLV.php?Tipo=".$Tipo;?>" title="Salir"><img src="../img/b_drop.png" alt="Salir"></a>
                            </td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td height=auto valign=top style="padding:0px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;border-bottom:1px solid #dddddd;background-color:#ffffff;">
					<div id="LCarriba" style="border-bottom:1px solid #475767;margin-bottom:8px;">
						<span id="tituloarriba"><?php echo $NombreParque;?></span>
					</div>
					<div align=center style="padding-bottom:5px;">
						<table class="tablanaranja">
						<?php
						if (file_exists(($File = "../data/".$Archivo)))
						{
							$doc = new DOMDocument();
							$doc->load($File);
		
							if (is_object($aux = $doc->getElementsByTagName("Nombre")->item(0)))
								$NombreParque = $aux->nodeValue;

							$Datos = array($Archivo,"","","","","","","","");
							foreach($doc->getElementsByTagName("Torre") as $torre)
							{
								if (is_object($aux = $torre->getElementsByTagName("Id")->item(0)))
									$Datos[1] = $aux->nodeValue;
								if (is_object($aux = $torre->getElementsByTagName("NumeroTorre")->item(0)))
									$Datos[2] = $aux->nodeValue;

								// 1ro. Buscamos si hay Extintores, y si hay alguno No OK, es el que se muestra
								$Datos[7] = $Datos[8] = "";
								foreach($torre->getElementsByTagName("Extintor") as $extintor)
								{
									if (is_object($aux = $extintor->getElementsByTagName("Fecha")->item(0)))
									{   if ($aux->nodeValue != "" || $Pendiente)
										{
											$Datos[4] = $aux->nodeValue; $Datos[7] = 1;
											if (is_object($aux = $extintor->getElementsByTagName("EstadoExt")->item(0)))
											{
												if ($aux->nodeValue != 1)
													$Datos[7] = 0;
											}
										}
									}
								}
								// 2do. Buscamos si hay Descensores, y si hay alguno No OK, es el que se muestra
								foreach($torre->getElementsByTagName("Descensor") as $descensor)
								{
									if (is_object($aux = $descensor->getElementsByTagName("Fecha")->item(0)))
									{   if ($aux->nodeValue != "" || $Pendiente)
										{
											$Datos[4] = $aux->nodeValue;
											if (is_object($aux = $descensor->getElementsByTagName("EstadoDes")->item(0)))
												$Datos[8] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
										}
									}
								}
								// 3ro. Busco si hay Líneas de Vida
								$HayLinea = false; $Datos[3] = $Datos[5] = $Datos[6] = "";
								foreach($torre->getElementsByTagName("Linea") as $linea)
								{
									if (is_object($aux = $linea->getElementsByTagName("Fecha")->item(0)))
									{   // Sólo las Líneas que han sido Revisadas
									    if ($aux->nodeValue != "" || $Pendiente)
										{
											$HayLinea = true;
											$Datos[4] = $aux->nodeValue;
											if (is_object($aux = $linea->getElementsByTagName("Serie")->item(0)))
												$Datos[5] = $aux->nodeValue;
											if (is_object($aux = $linea->getElementsByTagName("IdPletina")->item(0)))
												$Datos[3] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
											if (is_object($aux = $linea->getElementsByTagName("Resultado")->item(0)))
												$Datos[6] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						
											if (!$CabeceraImpresa)
												$CabeceraImpresa = fCabecera();
											echo fDetalle($Datos, $Pendiente);
										}
									}
								}  // Fin foreach Linea

								if (!$HayLinea)
								{   // Si no hay ninguna Linea de vida, compruebo que al menos haya una revisión
									//	de Extintores ó Descensores
									if (is_numeric($Datos[7]) || is_numeric($Datos[8]))
									{
										if (!$CabeceraImpresa)
											$CabeceraImpresa = fCabecera();
										echo fDetalle($Datos, $Pendiente);
									}
								}
							}  // Fin foreach Torre
						} // Fin FileExist

						if (!$CabeceraImpresa)
							echo fNoHayDatos();
						?>
						</table>                        
                        <br/>
                        <?php
						if ($Pendiente)
							echo "<span style='padding-right:20px'><button id='validar'>Validar</button></span>";
						?>
						  <span style='padding-right:20px'>
							<button id='verpdf'>VerPDF</button>
						  </span>
						  <span style='padding-right:20px'>
							<button id='cerrar'>Cerrar</button>
						  </span>
					</div>
				</td>
			</tr>             	
		</table>
	</div>        
  </div>
</body>
</html>
<?php
if ($conn)
	mysql_close($conn);
	
function fCabecera()
{
	echo '
		<tr>
			<th width=75><b>Fecha</b></th>
			<th width=50><b>Torre</b></th>
			<th width=145><b>Nº Línea</b></th>
			<th width=40><b>LV</b></th>
			<th width=40><b>EX</b></th>
			<th width=40><b>DE</b></th>
			<th width=25><b>Ver</b></th>
		</tr>';
		
	return true;
}
function fDetalle($Array, $pendiente)
{	 
	$Html = '
		<tr>
			<td align="center">'.$Array[4].'</td>
			<td align="center">'.$Array[2].'</td>
			<td>'.$Array[5].'</td>
			<td align="center">'.fValorCheck($Array[6]).'</td>
			<td align="center">'.fValorCheck($Array[7]).'</td>
			<td align="center">'.fValorCheck($Array[8]).'</td>
			<td>
				<a href="VerLCMyR.php?Editar='.$Array[0].'&Linea='.$Array[1].'&Pletina='.$Array[3].(($pendiente) ? '&Pendiente=1':'').'" 
					title="Ver"><img src="../img/bt_view.png" alt="Ver"></a>
			</td>
		</tr>';

	return $Html;
}
function fNoHayDatos()
{
	return '
		<tr>
			<td style="border:0px solid #475767;font-size:12px;color:#ff0000;">
				<b>No hay ninguna línea válida en el fichero</b>
			</td>
		</tr>';
}
function fValorCheck($Valor)
{
	if (is_numeric($Valor))
		$Valor = ($Valor == 1) ? "OK" : "NO OK";
	else 
		$Valor = "";
		
	return $Valor;
}
?>