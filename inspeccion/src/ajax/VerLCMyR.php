<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");

$Pendiente = (isset($_REQUEST['Pendiente'])) ? true : false;
if (($Editar = isset($_GET["Editar"]) ? $_GET["Editar"]:"") != "")
{
	$Tipo = substr($Editar, 0, 1);
	$LinCod = isset($_GET['Linea']) ? $_GET['Linea'] : 0;
	$LinPle = isset($_GET['Pletina']) ? $_GET['Pletina'] : 0;
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
    <script src="../js/jquery/jq.ui.datepicker-es.js" type="text/javascript"></script>
    <script src="../js/jquery/jq.maskedinput-min.js" type="text/javascript"></script>
	<script src="../js/inc/functions.js" type="text/javascript"></script>
    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
		
			$("#grabar").button({
				icons: { primary: "ui-icon-disk" }
			});
			$("#verpdf").button({
				icons: { primary: "ui-icon-pdf" }
			});
			$("#cerrar").button({
				icons: { primary: "ui-icon-close" }
			});

			$("#grabar").click(function(event) {
				var sError = "";
				event.preventDefault();

				$("select").each(function() {
					var sName = $(this).attr("NAME");
					
					if (sName.substr(0,8) == 'MarcaExt')
					{   // Comprobamos que se haya seleccionado alguna Marca de Extintor
						if ($("select[name$="+sName+"]").val().substr(0,10) == "Seleccione")
							sError = "Debe de seleccionar la Marca del Extintor "+(parseInt(sName.substr(8,2)) + 1);
					}
					else if (sName.substr(0,9) == 'ModeloExt')
					{   // Comprobamos que se haya seleccionado algun Modelo de Extintor
						if ($("select[name$="+sName+"]").val().substr(0,10) == "Seleccione")
							sError = "Debe de seleccionar el Modelo del Extintor "+(parseInt(sName.substr(9,2)) + 1);
					}
					else if (sName.substr(0,10) == 'Colocacion')
					{   // Comprobamos que se haya seleccionado alguna Colocación
						if ($("select[name$="+sName+"]").val().substr(0,12) == "0")
							sError = "Debe de seleccionar la Colocación del Extintor "+(parseInt(sName.substr(10,2)) + 1);
					}
				
					if (sError != "")
						return false;
				});
				
				if (sError == "")
					$("#Control").submit();
				else
					jAlert(sError, "Atención", null);
			});

			$("#verpdf").click(function(event) {
				event.preventDefault();
				
				var sFichero = "ImpLCMyR.php?Linea=" + $("#LinCod").val() + 
					"&Pletina=" + $("#LinPle").val() + "&FileXml=" + $("#Editar").val();									
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
			
			$("#cerrar").click(function(event) {
				event.preventDefault();
				window.location = "EditarLCMyR.php?Editar=<?php echo $Editar; echo ($Pendiente)?'&Pendiente=1':'';?>";
			});

			$("#MarcaLV").change(function() {
				$("#optMarcaLin").css('display',($(this).val() == "")?'block':'none');

				$.ajax({
					type: "POST", url:"../ajax/inspeccion/validaLIN.php",
					data: {"File":"<?php echo $Editar;?>", "Linea":"<?php echo $LinCod;?>", 
						   "LinPle":"<?php echo $LinPle;?>", "Marca":$(this).val()},
					success: function(sData) {
						$("#gpoDetLIN").html(sData);
					},
					dataType: "text", async:false
				});
			});

			$("#Fabricante").change(function() {
				$("#optMarcaDes").css('display',($(this).val() == "")?'block':'none');

				// Marcas
				$.ajax({
					type: "POST", url:"../ajax/inspeccion/validaDES.php",
					data: {"File":"<?php echo $Editar;?>", "Linea":"<?php echo $LinCod;?>", "Marca":$(this).val()},
					success: function(sData) {
						$("#gpoDetDES").html(sData);
					},
					dataType: "text", async:false
				});
				
				// Modelos
				$.ajax({
					type: "POST", url:"../ajax/maestros/validaMOD.php",
					data: {"Marca":$(this).val(),"Modelo":$("#ModeloDes").val()},
					success: function(sData) {
						$("#ModeloDes").html(sData);
						$("#optModeloDes").css('display',($("#ModeloDes").val() == "")?'block':'none');
					},
					dataType: "text", async:false
				});
			});			
		});

		$(function() {
			$("#tabs").tabs();
			$(".Fecha").datepicker().mask("99/99/9999");
		});
	</script>
	<style>
		.titulo {font-family:Calibri, Arial, sans-serif;font-size:22px;color:#000099;font-weight:bold;}
		.txtNum {text-align:right; width:50px;}
		.txtDec {text-align:right;}
		select {font-size:13px;color:#475767;}
		textarea {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;font-size:12px;width:300px;height:80px;color:#475767; resize:none;text-align:left;}
		input {font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;color:#475767;font-size:12px;}
		input[type="text"]:focus, textarea:focus { background-color: #FFC; }
	</style>
  </head>
  <body>
<?php 
	if (file_exists(($File = "../data/".$Editar)))
	{
		$doc = new DOMDocument();
		$doc->load($File);
		
		$NombreParque = $Cliente = "";
		if (is_object($aux = $doc->getElementsByTagName("Nombre")->item(0)))
			$NombreParque = $aux->nodeValue;
		if (is_object($aux = $doc->getElementsByTagName("Cliente")->item(0)))
			$Cliente = $aux->nodeValue;

		// Torre
		$IdTorre = ""; $nExt = 0;
		$TipoAerogenerador = $TipoAegGAMESA = $Altura = "";
		// * Líneas de Vida *
		$OTLV = $FechaLV = ""; $MarcaLV = 1 ; $TipoDeLinea = $Resultado = 0;
		$Observaciones = $TrabajosPendientes = "";
		// * Extintores *
		$Localizacion = $NPlaca = $Marca = $Modelo = $FechaFabricacion = $FechaRetimbrado = $AgenteExtintor = false;
		$PesoAgExtintor = $Colocacion = $MovidoA = $Sustituido = $PlacaSustitucion = $PrecintoSustitucion = false;
		$CartelLu = $PegatinaCarac = $PegatinaRevi = $MarcadoCE = $PrecintoRetimbrado = $EstadoCuerpo = $EstadoCabeza = false;
		$Pasador = $Valvula = $Manguera = $Soporte = $Junta = $Materiales = $EstadoExt = $FaltaPeso = false;
		$Caducidad = $Otra = $ObservacionesExt = $OTExt = $FechaExt = false;
		// * Descensores *
		$OTDes = $FechaDes = $Envasado = $AnyoFabCuerdaPri = $NSerie = $Longitud = $PrecintoViejo = $PrecintoNuevo = 
		$AnyoFabricacion = ""; $Ubicacion = $IdDescensor = 0; $Fabricante = $ModeloDes = 1;
		$EstadoDes = $Material = $Motivo = $Cantidad = false;
		
		foreach($doc->getElementsByTagName("Torre") as $torre)
		{
			if (($Id = (is_object($aux=$torre->getElementsByTagName("Id")->item(0)))?$aux->nodeValue:0) == $LinCod)
			{
				if (is_object($aux = $torre->getElementsByTagName("NumeroTorre")->item(0)))
					$IdTorre=$aux->nodeValue;
				if (is_object($aux = $torre->getElementsByTagName("TipoAerogenerador")->item(0)))
					$TipoAerogenerador = $aux->nodeValue;
				if (is_object($aux = $torre->getElementsByTagName("TipoAegGAMESA")->item(0)))
					$TipoAegGAMESA = $aux->nodeValue;
				if (is_object($aux = $torre->getElementsByTagName("Altura")->item(0)))
					$Altura = $aux->nodeValue;

				foreach($torre->getElementsByTagName("Linea") as $linea)
				{   // Compruebo que la Línea de Vida es la que hemos seleccionado
					if (is_object($aux = $linea->getElementsByTagName("IdPletina")->item(0)))
					{												
						if (($IdPletina = $aux->nodeValue) == $LinPle)
						{   // Registro a Modificar
							if (is_object($aux = $linea->getElementsByTagName("OT")->item(0)))
								$OTLV = $aux->nodeValue;
							if (is_object($aux = $linea->getElementsByTagName("Fecha")->item(0)))
								$FechaLV = $aux->nodeValue;
							if ($FechaLV == "" && $Pendiente)
								$FechaLV = date('d/m/Y');
							if (is_object($aux = $linea->getElementsByTagName("IdMarca")->item(0)))
								$MarcaLV = $aux->nodeValue;
							if (is_object($aux = $linea->getElementsByTagName("Resultado")->item(0)))
								$Resultado = $aux->nodeValue;
							if (is_object($aux = $linea->getElementsByTagName("TipoDeLinea")->item(0)))
								$TipoDeLinea = $aux->nodeValue;
							if (is_object($aux = $linea->getElementsByTagName("Observaciones")->item(0)))
								$Observaciones = $aux->nodeValue;
							if (is_object($aux = $linea->getElementsByTagName("TrabajosPendientes")->item(0)))
								$TrabajosPendientes = $aux->nodeValue;
						}
					}
				}
		
				// Extintores
				foreach($torre->getElementsByTagName("Extintor") as $extintor)
				{
					if (is_object($aux = $extintor->getElementsByTagName("OT")->item(0)))
						$OTExt[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Fecha")->item(0)))
						$FechaExt[] = $aux->nodeValue;
					if ($FechaExt[$nExt] == "" && $Pendiente)
						$FechaExt[$nExt] = date('d/m/Y');
						
					if (is_object($aux = $extintor->getElementsByTagName("Localizacion")->item(0)))
						$Localizacion[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Placa")->item(0)))
						$NPlaca[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Marca")->item(0)))
						$Marca[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Modelo")->item(0)))
						$Modelo[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("FechaFabricacion")->item(0)))
						$FechaFabricacion[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("FechaRetimbrado")->item(0)))
						$FechaRetimbrado[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("AgenteExtintor")->item(0)))
						$AgenteExtintor[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("PesoAgExtintor")->item(0)))
						$PesoAgExtintor[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Colocacion")->item(0)))
						$Colocacion[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Movido")->item(0)))
						$MovidoA[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("Sustituido")->item(0)))
						$Sustituido[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PlacaSustitucion")->item(0)))
						$PlacaSustitucion[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("PrecintoSustitucion")->item(0)))
						$PrecintoSustitucion[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("CartelLu")->item(0)))
						$CartelLu[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PegatinaCarac")->item(0)))
						$PegatinaCarac[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PegatinaRevi")->item(0)))
						$PegatinaRevi[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("MarcadoCE")->item(0)))
						$MarcadoCE[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PrecintoRetimbrado")->item(0)))
						$PrecintoRetimbrado[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoCuerpo")->item(0)))
						$EstadoCuerpo[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoCabeza")->item(0)))
						$EstadoCabeza[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Pasador")->item(0)))
						$Pasador[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Valvula")->item(0)))
						$Valvula[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Manguera")->item(0)))
						$Manguera[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Soporte")->item(0)))
						$Soporte[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Junta")->item(0)))
						$Junta[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Materiales")->item(0)))
						$Materiales[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoExt")->item(0)))
						$EstadoExt[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("FaltaPeso")->item(0)))
						$FaltaPeso[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;						
					if (is_object($aux = $extintor->getElementsByTagName("Caducidad")->item(0)))
						$Caducidad[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Otra")->item(0)))
						$Otra[] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("ObservacionesExt")->item(0)))
						$ObservacionesExt[] = $aux->nodeValue;
					$nExt ++;
				} // Fin foreach Extintor

				// Descensores
				foreach($torre->getElementsByTagName("Descensor") as $descensor)
				{
					if (is_object($aux = $descensor->getElementsByTagName("OT")->item(0)))
						$OTDes = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("Fecha")->item(0)))
						$FechaDes = $aux->nodeValue;
					if ($FechaDes == "" && $Pendiente)
						$FechaDes = date('d/m/Y');

					if (is_object($aux = $descensor->getElementsByTagName("IdDescensor")->item(0)))
						$IdDescensor = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("NSerie")->item(0)))
						$NSerie = $aux->nodeValue;
						
					// ODG, 05.01.15, Puede venir el ID del Frabricante y del Modelo, así que lo dejo preparado para que venga una cosa u otra
					if (is_object($aux = $descensor->getElementsByTagName("Fabricante")->item(0))) {
						if (is_numeric($aux->nodeValue))
							$Fabricante = $aux->nodeValue;
						elseif (($Fabricante = fBuscaDato("Id","MarcaDes", "Nombre='".$aux->nodeValue."'", $conn)) == 0)
							$Fabricante = $aux->nodeValue;
					}
					if (is_object($aux = $descensor->getElementsByTagName("ModeloDes")->item(0))) {
						if (is_numeric($aux->nodeValue))
							$ModeloDes = $aux->nodeValue;
						else if (($ModeloDes = fBuscaDato("Id","ModeloDes", "Nombre='".$aux->nodeValue."'", $conn)) == 0)
							$ModeloDes = $aux->nodeValue;
					}
					
					if (is_object($aux = $descensor->getElementsByTagName("Longitud")->item(0)))
						$Longitud = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("PrecintoViejo")->item(0)))
						$PrecintoViejo = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("PrecintoNuevo")->item(0)))
						$PrecintoNuevo = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("AnyoFabricacion")->item(0)))
						$AnyoFabricacion = $aux->nodeValue;
					// ODG, 14.07.14 La tablet manda la ubicación como no debe, así que preveeo que pueda venir como numérico ó texto
					//	1 - Nacelle, 2 - Ground.						
					if (is_object($aux = $descensor->getElementsByTagName("Ubicacion")->item(0)))
						$Ubicacion = ($aux->nodeValue=="1" || $aux->nodeValue=="Nacelle")?1:2;
					if (is_object($aux = $descensor->getElementsByTagName("Envasado")->item(0)))
						$Envasado = $aux->nodeValue;
					if (is_object($aux = $descensor->getElementsByTagName("EstadoDes")->item(0)))
						$EstadoDes = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						
					// Materiales
					for ($nMat = 1; $nMat < 5; $nMat ++)
					{
						if (is_object($aux = $descensor->getElementsByTagName("Material".($Tmp = sprintf("%02d", $nMat)))->item(0)))
							$Material[] = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("Motivo".$Tmp)->item(0)))
							$Motivo[] = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("Cantidad".$Tmp)->item(0)))
							$Cantidad[] = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					}
				}  // Fin foreach Descensor
			}  // Fin es Línea
		} // Fin foreach Torres
		?>
	<div width=100% align=center style="margin-top:5px;">
	  <FORM id="Control" action="VerLCMyR.php" method="post">
	    <input type="hidden" name="LinCod" id="LinCod" value="<?php echo $LinCod;?>">
        <input type="hidden" name="LinPle" id="LinPle" value="<?php echo $LinPle;?>">
		<input type="hidden" name="Editar" id="Editar" value="<?php echo $Editar;?>">
        <?php 
		if ($Pendiente)
			echo '<input type="hidden" name="Pendiente" id="Pendiente" value="1">';
		?>
		<table class="table" width=60%>
			<tr>
				<td height=25>
					<table cellpading=0 cellspacing=0>
						<tr>
							<td class="header_L">&nbsp;</td>
							<td class="header_C"><span class='header_title'>VALIDAR LISTAS DE CONTROL <?php echo ($Tipo=="M")?"MONTAJE":"REVISIÓN"; ?> : Ver</span></td>
                            <td class="header_C" style="padding-top:3.5px;padding-right:5px;">
                            	<a href="EditarLCMyR.php?Editar=<?php echo $Editar; echo ($Pendiente) ? "&Pendiente=1": "";?>" title="Salir"><img src="../img/b_drop.png" alt="Salir"></a>
                            </td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align=center bgcolor='#fff' style="border-left:1px solid #ddd;border-right:1px solid #ddd;padding-top:5px;padding-bottom:0px;">
					<table cellpadding=4 cellspacing=0 width="80%" style="border-left:1px solid #e9bc0c;border-right:1px solid #e9bc0c;border-top:1px solid #e9bc0c;border-bottom:1px solid #e9bc0c;">
						<tr>
							<td class="columnaTabla" width="80%">Parque&nbsp;:&nbsp;<span><?php echo $NombreParque;?></span></td>
                            <td class="columnaTabla" width="20%"><?php echo $TipoAerogenerador;?></td>
						</tr>
						<tr>
							<td class="columnaTabla">
                              <table>
								<tr>
                                   <td>Torre&nbsp;:&nbsp;</td>
                                   <td>
									<INPUT TYPE="text" NAME="IdTorre" value="<?php echo $IdTorre ?>" size="8" maxlength="8" style="text-align:right;"/>
                                   </td>
                                   <td>Altura&nbsp;:&nbsp;</td>
                                   <td>
									<SELECT NAME="Altura" SIZE=1 onchange='OptVisualizacion("optAltura", $(this).val()); OptInicializa("OtraAltura", $(this).val());'>
    	                         	  <?php
									  $Tmp = "";
									  if (($result = mysql_query("SELECT * FROM Alturas",$conn))) {
										while($row = mysql_fetch_row($result))
											echo "<OPTION ".(($Altura == $row[1]) ? ($Tmp = "SELECTED") : "")." VALUE='".$row[1]."'>".$row[1]."</OPTION>";
									  }
									  ?>
                                  	  <OPTION VALUE="" <?php echo ($Tmp == "")?"SELECTED":"";?>>Otra</OPTION>
									</SELECT>
                                  </td>
                                  <td>
	                                <div id="optAltura" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
    	                              <INPUT TYPE="text" NAME="OtraAltura" value="<?php echo $Altura;?>" size="15" maxlength="10" />
        	                        </div>
                                  </td>
                            	</tr>
                              </table>
                            </td>
                            <td class="columnaTabla"><?php echo $TipoAegGAMESA;?></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align=center bgcolor='#fff' style="border-left:1px solid #ddd;border-right:1px solid #ddd;border-bottom:1px solid #ddd;padding-top:5px;padding-bottom:5px;">
					<div id="tabs">
						<ul>
                        <?php
						if ($FechaLV != "")	// Líneas de vida, será Tab 1
	                       	echo '<li><a href="#tabs-1">Línea</a></li>';
						if ($FechaDes != "")   // Descensores, será Tab 2
							echo '<li><a href="#tabs-2">Descensor</a></li>';
						$nTab=3;				// Extintores, serán desde el Tab 3
						for ($Count = 0; $Count < $nExt; $Count ++)
                           	echo '<li><a href="#tabs-'.($nTab++).'">Extintor ('.($Count+1).')</a></li>';
						?>
                        </ul>
                        <?php
						if ($FechaLV != "")
                        {
						?>
                        <div id="tabs-1">
						  <table id="gpoCabLIN" width="100%">
							<tr>
								<td class="etiqueta_form" style="width:125px;">
									<LABEL for="Resultado">Resultado Final</LABEL>
								</td>
								<td class="check_form" style="width:175px;">
									<input value=1 type="checkbox" name="Resultado" <?php echo ($Resultado==1)?"checked":"";?> />
                                	<span style="padding-left:50px;font-size:18px;"><?php echo ($TipoDeLinea==1)?"Nacelle":"Servicio";?></span>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:65px;"><LABEL for="MarcaLV">Marca</LABEL></td>
                                      <td>
								  		<SELECT ID="MarcaLV" NAME="MarcaLV" SIZE=1 onchange='OptVisualizacion("optMarcaLin", $(this).val());'>
    	                               	  <?php
										  $Tmp = "";
										  if (($result = mysql_query("SELECT Id, Nombre FROM MarcaLin",$conn)))
										  {
											while($row = mysql_fetch_row($result))
												echo "<OPTION ".(($MarcaLV == $row[0]) ? ($Tmp = "SELECTED") : "")." VALUE='".$row[0]."'>".$row[1]."</OPTION>";
										  }
									  	  ?>
                               			  <OPTION VALUE="" <?php echo ($Tmp == "")?"SELECTED":"";?>>Otra</OPTION>
								  		</SELECT>
    	                           		<div id="optMarcaLin" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
        	                       		  <INPUT TYPE="text" NAME="OtraMarcaLV" value="<?php echo (!is_numeric($MarcaLV))?$MarcaLV:"";?>" size="25" maxlength="25" />
            	                   		</div>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
                            		<LABEL FOR="FechaLV">Fecha</LABEL>									
								</td>
								<td class="check_form">
									<INPUT TYPE="text" NAME="FechaLV" class="Fecha" value="<?php echo $FechaLV;?>" size="10" maxlength="10" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:100px;">
                                        <LABEL for="OTLV">Orden&nbsp;de&nbsp;Trabajo</LABEL>
                                      </td>
                                      <td >
									    <INPUT TYPE="text" NAME="OTLV" value="<?php echo $OTLV;?>" size="25" maxlength="25"/>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
                         </table>
                         <table id="gpoDetLIN" width="100%">
                         </table>
                         <table id="gpoPieLIN" width="100%">
							<tr>
								<td colspan="2">Observaciones<br/>
									<TEXTAREA name="Observaciones" ROWS=2 COLS=30><?php echo $Observaciones;?></TEXTAREA>
								</td>
								<td>Trabajos&nbsp;Pendientes<br/>
									<TEXTAREA name="TrabajosPendientes" ROWS=2 COLS=30><?php echo $TrabajosPendientes;?></TEXTAREA>
								</td>
							</tr>
						  </table>
                        </div>
                        <?php
						}  // Fin grupo Línea de Vida
						
						if ($FechaDes != "")
						{
						?>
                        <div id="tabs-2">
						  <table id="gpoCabDES" width="100%">
                          	<tr>
								<td class="etiqueta_form">
									<LABEL for="EstadoDes">Apto&nbsp;para&nbsp;su&nbsp;uso</LABEL>
								</td>
								<td class="check_form">
                                    <INPUT VALUE=1 TYPE="checkbox" NAME="EstadoDes" <?php echo ($EstadoDes==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
                          	<tr>
								<td class="etiqueta_form">
                                	<LABEL for="FechaDes">Fecha</LABEL>
                                </td>
								<td class="check_form">
                                  	<INPUT TYPE="text" NAME="FechaDes" class="Fecha" value="<?php echo $FechaDes;?>" size="10" maxlength="10" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td><LABEL for="OTDes">O.T.</LABEL></td>
                                      <td>
                                        <INPUT TYPE="text" NAME="OTDes" value="<?php echo $OTDes;?>" size="25" maxlength="25" /> 
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Cliente">Cliente</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT TYPE="text" NAME="Cliente" value="<?php echo $Cliente;?>" size="25" maxlength="25" />
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="NSerie">Nº&nbsp;Serie</LABEL>
								</td>
								<td class="check_form">
                                    <INPUT TYPE="hidden" NAME="IdDescensor" value="<?php echo $IdDescensor;?>" />
                                	<INPUT TYPE="text" NAME="NSerie" value="<?php echo $NSerie;?>" size="40" maxlength="40" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:70px;"><LABEL for="Fabricante">Fabricante</LABEL></td>
                                      <td>
								  		<SELECT ID="Fabricante" NAME="Fabricante" SIZE=1>
	                                   	  <?php
											$Tmp = "";
											if (($result = mysql_query("SELECT Id, Nombre FROM MarcaDes",$conn)))
											{
												while($row = mysql_fetch_row($result))
													echo "<OPTION ".(($Fabricante == $row[0]) ? ($Tmp = "SELECTED") : "")." VALUE='".$row[0]."'>".$row[1]."</OPTION>";
											}
									  	  ?>
                                  		  <OPTION VALUE="" <?php echo ($Tmp == "")?"SELECTED":"";?>>Otro</OPTION>
								  		</SELECT>
                                  		<div id="optMarcaDes" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
                                		  <INPUT TYPE="text" NAME="OtroFabricante" value="<?php echo (!is_numeric($Fabricante))?$Fabricante:"";?>" size="20" maxlength="20" />
                                  		</div>
                                      </td>
									</tr>
                                    <tr>
                                      <td style="width:70px;"><LABEL for="ModeloDes">Modelo</LABEL></td>
                                      <td>
								  		<SELECT ID="ModeloDes" NAME="ModeloDes" SIZE=1 onchange='OptVisualizacion("optModeloDes", $(this).val()); OptInicializa("OtroModeloDes", $(this).val());'>
	                                   	  <?php
											$Tmp = "";
											if (($result = mysql_query("SELECT Id, Nombre FROM ModeloDes WHERE IdMarca=".$Fabricante,$conn)))
											{
												while($row = mysql_fetch_row($result))
													echo "<OPTION ".(($ModeloDes == $row[0]) ? ($Tmp = "SELECTED") : "")." VALUE='".$row[0]."'>".$row[1]."</OPTION>";
											}
									  	  ?>
                                          <OPTION VALUE="" <?php echo ($Tmp == "")?"SELECTED":"";?>>Otro</OPTION>
								  		</SELECT>
                                  		<div id="optModeloDes" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
                                		  <INPUT TYPE="text" NAME="OtroModeloDes" value="<?php echo (!is_numeric($ModeloDes))?$ModeloDes:"";?>" size="20" maxlength="20" />
                                  		</div>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Longitud">Longitud</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT TYPE="text" NAME="Longitud" value="<?php echo $Longitud;?>" size="20" maxlength="20" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td><LABEL for="PrecintoViejo">Nº&nbsp;Precinto&nbsp;Viejo</LABEL></td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="PrecintoViejo" value="<?php echo $PrecintoViejo;?>" size="25" maxlength="25" />
                                      </td>
									</tr>
                                    <tr>
                                      <td><LABEL for="PrecintoNuevo">Nº&nbsp;Precinto&nbsp;Nuevo</LABEL></td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="PrecintoNuevo" value="<?php echo $PrecintoNuevo;?>" size="25" maxlength="25" />
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="AnyoFabricacion">Año&nbsp;Fabricación</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT TYPE="text" NAME="AnyoFabricacion" value="<?php echo $AnyoFabricacion;?>" size="10" maxlength="10" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:70px;"><LABEL for="Ubicacion">Ubicación</LABEL></td>
                                      <td>
	                                  	<SELECT NAME="Ubicacion" SIZE=1>
                                        	<OPTION <?php echo ($Ubicacion==1)?"SELECTED":"";?> VALUE=1>Nacelle</OPTION>
											<OPTION <?php echo ($Ubicacion==2)?"SELECTED":"";?> VALUE=2>Ground</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                    <tr>
                                      <td style="width:70px;"><LABEL for="Envasado">Envasado</LABEL></td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="Envasado" value="<?php echo $Envasado;?>" size="15" maxlength="15" />
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
                          </table>
                          <table id="gpoDetDES" width="100%">
                          </table>                          
                          <table id="gpoPieDES" width="100%">
                            <tr>
	                            <td class="etiqueta_form" colspan="3">
                                  <table align="center">
                                    <tr>
                                    	<td align="center"><b>Material</b></td>
                                    	<td align="center"><b>Motivo</b></td>
                                    	<td align="center"><b>Cantidad</b></td>
									</tr>
                                  <?php
								  for ($nMat=0;$nMat<4;$nMat++)
								  {									  
								  ?>
                                    <tr>
                                      <td><INPUT TYPE="text" NAME="Material<?php echo ($Tmp = sprintf ("%02d", $nMat + 1));?>" value="<?php echo $Material[$nMat];?>" size="30" maxlength="30" /></td>
                                      <td><INPUT TYPE="text" NAME="Motivo<?php echo $Tmp;?>" value="<?php echo $Motivo[$nMat];?>" size="20" maxlength="20" /></td>
                                      <td><INPUT TYPE="text" NAME="Cantidad<?php echo $Tmp;?>" value="<?php echo $Cantidad[$nMat];?>" size="8" maxlength="6" style="text-align:right;"/></td>
									</tr>
                                  <?php
								  }
								  ?>
                                  </table>                                
                                </td>
                            </tr>
                          </table>
                        </div>
                        <?php 
						}  // Fin grupo Descensor
						
						$nTab = 3;
						for ($Count=0;$Count<$nExt;$Count ++)
						{
							if (isset($EstadoExt[$Count]))
							{
						?>
                        <div id="tabs-<?php echo $nTab++;?>">
						  <table cellpadding=2 width="100%">
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstadoExt<?php echo $Count;?>">Estado</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="EstadoExt<?php echo $Count;?>" <?php echo ($EstadoExt[$Count]==1)?"checked":"";?> onclick='AlternarVisualizacion("optEstadoExt<?php echo $Count;?>");'/>
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
                                	<LABEL FOR="FechaExt<?php echo $Count;?>">Fecha</LABEL>
								</td>
								<td class="check_form">
                                   	<INPUT TYPE="text" NAME="FechaExt<?php echo $Count;?>" class="Fecha" value="<?php echo $FechaExt[$Count];?>" size="10" maxlength="10" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:75px;">
										<LABEL for="OTExt<?php echo $Count;?>">Orden&nbsp;de&nbsp;Trabajo</LABEL>
                                      </td>
                                      <td>
	                                	<INPUT TYPE="text" NAME="OTExt<?php echo $Count;?>" value="<?php echo $OTExt[$Count];?>" size="25" maxlength="25" />
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
								  <LABEL FOR="Localizacion<?php echo $Count;?>">Localización</LABEL>
								</td>
								<td class="check_form">
                                  <?php $Tmp = "";?>
                                  <SELECT NAME="Localizacion<?php echo $Count;?>" SIZE=1 onchange='OptVisualizacion("optLocalizacion<?php echo $Count;?>", $(this).val()); OptInicializa("OtraLocalizacion<?php echo $Count;?>", $(this).val());'>
									<OPTION <?php echo ($Localizacion[$Count]=="Ground")?($Tmp = "SELECTED"):"";?> VALUE="Ground">Ground</OPTION>
									<OPTION <?php echo ($Localizacion[$Count]=="Nacelle")?($Tmp = "SELECTED"):"";?> VALUE="Nacelle">Nacelle</OPTION>
									<OPTION <?php echo ($Localizacion[$Count]=="SubEstacion")?($Tmp = "SELECTED"):"";?> VALUE="SubEstacion">SubEstación</OPTION>
									<OPTION <?php echo ($Tmp == "")?"SELECTED":"";?> VALUE="">Otra</OPTION>
								  </SELECT>
                                  <div id="optLocalizacion<?php echo $Count;?>" style="display:<?php echo ($Tmp == "")?'block;':'none;';?>">
                                   	<INPUT TYPE="text" NAME="OtraLocalizacion<?php echo $Count;?>" value="<?php echo $Localizacion[$Count];?>" size="20" maxlength="20" />
                                  </div>
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL FOR="NPlaca<?php echo $Count;?>">Nº&nbsp;Placa</LABEL>
								</td>
								<td class="check_form">
                                  	<INPUT TYPE="text" NAME="NPlaca<?php echo $Count;?>" VALUE="<?php echo $NPlaca[$Count];?>" size="40" maxlength="40" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:75px;">
                                		<LABEL FOR="Colocacion<?php echo $Count;?>">Colocación</LABEL>
                                      </td>
                                      <td>
                                        <?php $Tmp = "";?>
	                                   	<SELECT NAME="Colocacion<?php echo $Count;?>" SIZE=1 onchange='OptVisualizacion("optColocacion<?php echo $Count;?>", $(this).val()); OptInicializa("OtraColocacion<?php echo $Count;?>", $(this).val());'>
                                        	<OPTION <?php echo ($Colocacion[$Count]=="" || $Colocacion[$Count]=="0")?($Tmp = "SELECTED"):"";?> VALUE="0">Seleccione ...</OPTION>
											<OPTION <?php echo ($Colocacion[$Count]=="Suelo")?($Tmp = "SELECTED"):"";?> VALUE="Suelo">Suelo</OPTION>
											<OPTION <?php echo ($Colocacion[$Count]=="Colgado")?($Tmp = "SELECTED"):"";?> VALUE="Colgado">Colgado</OPTION>
											<OPTION <?php echo ($Tmp == "")?"SELECTED":"";?> VALUE="">Otra</OPTION>
										</SELECT>
                                      </td>
                                      <td>
                                        <div id="optColocacion<?php echo $Count;?>" style="display:<?php echo ($Tmp == "")?'block;':'none;';?>">
                                    	  <INPUT TYPE="text" NAME="OtraColocacion<?php echo $Count;?>" value="<?php echo $Colocacion[$Count];?>" size="20" maxlength="20" />
                                        </div>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="MarcaExt<?php echo $Count;?>">Marca&nbsp;Extintor</LABEL>
								</td>
								<td class="check_form">
								  <SELECT NAME="MarcaExt<?php echo $Count;?>" SIZE=1 onchange='OptVisualizacion("optMarcaExt<?php echo $Count;?>", $(this).val()); OptInicializa("OtraMarca<?php echo $Count;?>", $(this).val());'>
                                  	<?php
										$Tmp = "";
										if (($result = mysql_query("SELECT * FROM MarcaExt",$conn)))
										{
											while($row = mysql_fetch_row($result))
												echo "<OPTION ".(($Marca[$Count] == $row[1]) ? ($Tmp = "SELECTED") : "")." VALUE='".$row[1]."'>".$row[1]."</OPTION>";
										}
								  	?>
                                  	<OPTION VALUE="" <?php echo ($Tmp == "") ? "SELECTED": "";?>>Otra</OPTION>
								  </SELECT>
                                  <div id="optMarcaExt<?php echo $Count;?>" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
                                	<INPUT TYPE="text" NAME="OtraMarca<?php echo $Count;?>" value="<?php echo $Marca[$Count];?>" size="25" maxlength="25" />
                                  </div>
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="ModeloExt<?php echo $Count;?>">Modelo</LABEL>
								</td>
								<td class="check_form">
								  <SELECT NAME="ModeloExt<?php echo $Count;?>" SIZE=1 onchange='OptVisualizacion("optModeloExt<?php echo $Count;?>", $(this).val()); OptInicializa("OtroModelo<?php echo $Count;?>", $(this).val());'>
                                  	<?php
										$Tmp = "";
										if (($result = mysql_query("SELECT * FROM ModeloExt",$conn)))
										{
											while($row = mysql_fetch_row($result))
												echo "<OPTION ".(($Modelo[$Count] == $row[1]) ? ($Tmp = "SELECTED") : "")." VALUE='".$row[1]."'>".$row[1]."</OPTION>";
										}																				
								  	?>
                                  	<OPTION VALUE="" <?php echo ($Tmp == "") ? "SELECTED": "";?>>Otro</OPTION>
								  </SELECT>
                                  <div id="optModeloExt<?php echo $Count;?>" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
                                	<INPUT TYPE="text" NAME="OtroModelo<?php echo $Count;?>" value="<?php echo $Modelo[$Count];?>" size="25" maxlength="25" />
                                  </div>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:75px;">
                                		<LABEL FOR="MovidoA<?php echo $Count;?>">Movido&nbsp;A</LABEL>
                                      </td>
                                      <td>
                                        <?php $Tmp = "";?>
	                                   	<SELECT NAME="MovidoA<?php echo $Count;?>" SIZE=1 onchange='OptVisualizacion("optMovidoA<?php echo $Count;?>", $(this).val()); OptInicializa("OtroMovidoA<?php echo $Count;?>", $(this).val());'>
                                            <OPTION <?php echo ($MovidoA[$Count]=="" || $MovidoA[$Count]=="0")?($Tmp = "SELECTED"):"";?> VALUE="0">Seleccione ...</OPTION>
											<OPTION <?php echo ($MovidoA[$Count]=="Ground")?($Tmp = "SELECTED"):"";?> VALUE="Ground">Ground</OPTION>
											<OPTION <?php echo ($MovidoA[$Count]=="Nacelle")?($Tmp = "SELECTED"):"";?> VALUE="Nacelle">Nacelle</OPTION>
											<OPTION <?php echo ($MovidoA[$Count]=="SubEstacion")?($Tmp = "SELECTED"):"";?> VALUE="SubEstacion">SubEstación</OPTION>
                                            <OPTION <?php echo ($MovidoA[$Count]=="Abantos Vertical")?($Tmp = "SELECTED"):"";?> VALUE="Abantos Vertical">Abantos Vertical</OPTION>
											<OPTION <?php echo ($Tmp == "")?"SELECTED":"";?> VALUE="">Otra</OPTION>
										</SELECT>
                                      
                                  		<div id="optMovidoA<?php echo $Count;?>" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
                                    	  <INPUT TYPE="text" NAME="OtroMovidoA<?php echo $Count;?>" value="<?php echo $MovidoA[$Count];?>" size="25" maxlength="25" />
                            	        </div>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="FechaFabricacion<?php echo $Count;?>">Fecha&nbsp;Fabricación</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT TYPE="text" NAME="FechaFabricacion<?php echo $Count;?>" value="<?php echo $FechaFabricacion[$Count];?>" size="10" maxlength="10" />
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="FechaRetimbrado<?php echo $Count;?>">Último&nbsp;Retimbrado</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT TYPE="text" NAME="FechaRetimbrado<?php echo $Count;?>" value="<?php echo $FechaRetimbrado[$Count];?>" size="10" maxlength="10" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:75px;">
                                        <LABEL for="Sustituido<?php echo $Count;?>">Sustituído</LABEL>
                                      </td>
                                      <td>
                                        <INPUT VALUE=1 TYPE="checkbox" NAME="Sustituido<?php echo $Count;?>" <?php echo ($Sustituido[$Count]==1)?"checked":"";?>
                                        	onchange='AlternarVisualizacion("optSustituido<?php echo $Count;?>");' <?php echo ($Sustituido[$Count] == 2)?"disabled=disabled":"";?>/>
                                        <INPUT TYPE="hidden" NAME="Sustituto<?php echo $Count;?>" value="<?php echo $Sustituido[$Count];?>" />
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="AgenteExtintor<?php echo $Count;?>">Agente&nbsp;Extintor</LABEL>
								</td>
								<td class="check_form">
                                	<?php $Tmp = "";?>
                                  	<SELECT NAME="AgenteExtintor<?php echo $Count;?>" SIZE=1 onchange='OptVisualizacion("optAgenteExtintor<?php echo $Count;?>", $(this).val()); OptInicializa("OtroAgExtintor<?php echo $Count;?>", $(this).val());'>
										<OPTION <?php echo ($AgenteExtintor[$Count]=="CO2")?($Tmp = "SELECTED"):"";?> VALUE="CO2">CO2</OPTION>
										<OPTION <?php echo ($Tmp == "")?"SELECTED":"";?> VALUE="">Otro</OPTION>
									</SELECT>
                                    <div id="optAgenteExtintor<?php echo $Count;?>" style="display:<?php echo ($AgenteExtintor[$Count] != "CO2")?'block':'none';?>;">
                                      <INPUT TYPE="text" NAME="OtroAgExtintor<?php echo $Count;?>" value="<?php echo $AgenteExtintor[$Count];?>" size="15" maxlength="15" />
                                    </div>
								</td>
								<td class="opcional_form">
                                  <div id="optSustituido<?php echo $Count;?>" style="display:<?php echo ($Sustituido[$Count]==1)?'block':'none';?>;">
                                  <table>
                                    <tr>
                                      <td>
                                      	<LABEL for="PlacaSustitucion<?php echo $Count;?>">Placa&nbsp;Sustitución</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="PlacaSustitucion<?php echo $Count;?>" value="<?php echo $PlacaSustitucion[$Count];?>" size="40" maxlength="40" />
                                      </td>
                                    </tr>
                                  </table>
                                  </div>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PesoAgExtintor<?php echo $Count;?>">Peso&nbsp;Agente&nbsp;Extintor</LABEL>
								</td>
								<td class="check_form">
                                    <INPUT TYPE="text" NAME="PesoAgExtintor<?php echo $Count;?>" value="<?php echo $PesoAgExtintor[$Count];?>" size="15" maxlength="15" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td>
                                      	<LABEL for="PrecintoSustitucion<?php echo $Count;?>">Presencia&nbsp;Precinto&nbsp;Retimbrado</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="PrecintoSustitucion<?php echo $Count;?>" <?php echo ($PrecintoSustitucion[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="CartelLu<?php echo $Count;?>">Cartel&nbsp;Luminiscente</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="CartelLu<?php echo $Count;?>" <?php echo ($CartelLu[$Count]==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:135px;">
                                      	<LABEL for="EstadoCuerpo<?php echo $Count;?>">Estado&nbsp;Cuerpo&nbsp;Extintor</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="EstadoCuerpo<?php echo $Count;?>" <?php echo ($EstadoCuerpo[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>  
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PegatinaCarac<?php echo $Count;?>">Pegatina&nbsp;Características&nbsp;Uso</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="PegatinaCarac<?php echo $Count;?>" <?php echo ($PegatinaCarac[$Count]==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:135px;">
                                      	<LABEL for="EstadoCabeza<?php echo $Count;?>">Estado&nbsp;Cabeza</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="EstadoCabeza<?php echo $Count;?>" <?php echo ($EstadoCabeza[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>  
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PegatinaRevi<?php echo $Count;?>">Pegatina&nbsp;Revisión&nbsp;Anual</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="PegatinaRevi<?php echo $Count;?>" <?php echo ($PegatinaRevi[$Count]==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:135px;">
                                      	<LABEL for="Pasador<?php echo $Count;?>">Pasador</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="Pasador<?php echo $Count;?>" <?php echo ($Pasador[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>  
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="MarcadoCE<?php echo $Count;?>">Marcado&nbsp;CE&nbsp;>&nbsp;2002</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="MarcadoCE<?php echo $Count;?>" <?php echo ($MarcadoCE[$Count]==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:135px;">
                                      	<LABEL for="Valvula<?php echo $Count;?>">Válvula</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="Valvula<?php echo $Count;?>" <?php echo ($Valvula[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>  
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PrecintoRetimbrado<?php echo $Count;?>">Precinto&nbsp;Retimbrado</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="PrecintoRetimbrado<?php echo $Count;?>" <?php echo ($PrecintoRetimbrado[$Count]==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:135px;">
                                      	<LABEL for="Manguera<?php echo $Count;?>">Manguera</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="Manguera<?php echo $Count;?>" <?php echo ($Manguera[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>  
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Junta<?php echo $Count;?>">Junta&nbsp;de&nbsp;RACCORD</LABEL>
								</td>
								<td class="check_form">
                                	<INPUT VALUE=1 TYPE="checkbox" NAME="Junta<?php echo $Count;?>" <?php echo ($Junta[$Count]==1)?"checked":"";?> />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:135px;">
                                      	<LABEL for="Soporte<?php echo $Count;?>">Soporte</LABEL>
                                      </td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="Soporte<?php echo $Count;?>" <?php echo ($Soporte[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                  </table>  
                                </td>
							</tr>
							<tr>
								<td colspan="2" class="etiqueta_form">
                                	Materiales&nbsp;Colocados<br/>
									<TEXTAREA name="Materiales<?php echo $Count;?>" ROWS=2 COLS=30 class="MaterialExt"><?php echo $Materiales[$Count];?></TEXTAREA>
								</td>
								<td class="opcional_form">
                                  <div id="optEstadoExt<?php echo $Count;?>" style="display:<?php echo (!$EstadoExt[$Count])?'block':'none';?>;">
                                  Causa&nbsp;:<br/>
                                  <table>
                                    <tr>
                                      <td><LABEL for="FaltaPeso<?php echo $Count;?>">Falta&nbsp;de&nbsp;Peso</LABEL></td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="FaltaPeso<?php echo $Count;?>" <?php echo ($FaltaPeso[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td><LABEL for="Caducidad<?php echo $Count;?>">Caducidad</LABEL></td>
                                      <td>
                                      	<INPUT VALUE=1 TYPE="checkbox" NAME="Caducidad<?php echo $Count;?>" <?php echo ($Caducidad[$Count]==1)?"checked":"";?> />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td><LABEL for="Otra<?php echo $Count;?>">Otra</LABEL></td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="Otra<?php echo $Count;?>" value="<?php echo $Otra[$Count];?>" size="20" maxlength="20" />
                                      </td>
                                    </tr>
                                  </table>
                                  </div>                                  
								</td>
							</tr>
                            <tr>
								<td class="etiqueta_form" colspan="3">
                                	Observaciones<br/>
									<TEXTAREA name="ObservacionesExt<?php echo $Count;?>" style="width:100%;"><?php echo $ObservacionesExt[$Count];?></TEXTAREA>
								</td>
                            </tr>
                          </table>
                        </div>
                        <?php
							}
						} // Fin grupo Extintores
						?>
                     </div>
				</td>
			</tr>
			<tr>
				<td align="center">
				  <span style='padding-right:20px'>
					<button id='grabar'>Guardar</button>
				  </span>
				  <span style='padding-right:20px'>
					<button id='verpdf'>VerPDF</button>
				  </span>
				  <span style='padding-right:20px'>
					<button id='cerrar'>Cerrar</button>
				  </span>
                </td>
			</tr>
      	</table>
	  </FORM>
	</div>
	<script type="text/javascript">
		$(document).ready(function() {
		  <?php 
		  if ($FechaLV != "")
			  echo '$("#MarcaLV").val("'.$MarcaLV.'").trigger("change");';
		  if ($FechaDes != "")
			  echo '$("#Fabricante").val("'.$Fabricante.'").trigger("change");';
		  ?>
		});
	</script>
  </body>
</html>
	<?php
		// Libero Variables Memoría
		unset($Localizacion, $NPlaca, $Marca, $Modelo, $FechaFabricacion, $FechaRetimbrado, $AgenteExtintor, $PesoAgExtintor,
			$Colocacion, $MovidoA, $Sustituido, $PlacaSustitucion, $PrecintoSustitucion, $CartelLu, $PegatinaCarac, $PegatinaRevi,
			$MarcadoCE, $PrecintoRetimbrado, $EstadoCuerpo, $EstadoCabeza, $Pasador, $Valvula, $Manguera, $Soporte,
			$Junta, $Materiales, $EstadoExt, $FaltaPeso, $Caducidad, $Otra, $ObservacionesExt);
		unset($Altura, $NSerie, $Fabricante, $ModeloDes, $Longitud, $PrecintoViejo, $PrecintoNuevo, $AnyoFabricacion, $Ubicacion, $Envasado, 
			$EstadoDes, $Material, $Motivo, $Cantidad);			
	}  // Fin file_exists
} // Fin Editar
else if (isset($_POST['LinCod']))
{ // Grabar registro
	if (file_exists(($File = "../data/".$_POST["Editar"])))
	{   // Procedemos a abrir el xml
		$doc = new DOMDocument();
		$doc->load($File);

		// OD, 06.06.13 Rosa comenta que se debe de cambiar el valor del Cliente
		if (isset($_POST['Cliente']))
		{
			if (is_object($aux = $doc->getElementsByTagName("Cliente")->item(0)))
				$aux->nodeValue = $_POST['Cliente'];
		}

		foreach($doc->getElementsByTagName("Torre") as $torre)
		{   // Registro a guardar
			if (($Id = (is_object($aux=$torre->getElementsByTagName("Id")->item(0))) ? $aux->nodeValue : 0) == $_POST['LinCod'])
			{   // Nº Torre
				if (is_object($aux = $torre->getElementsByTagName("NumeroTorre")->item(0)))
					$aux->nodeValue = $_POST['IdTorre'];
				// Altura, ODG 30.09.13 Modifico para componer el nombre de GAMESA al grabar la línea
				if (is_object($aux = $torre->getElementsByTagName("Altura")->item(0))) {
					$aux->nodeValue = $Altura = ($_POST['Altura'] != "") ? $_POST['Altura'] : $_POST['OtraAltura'];

					if (is_object($aux = $torre->getElementsByTagName("IdTipoAEG")->item(0))) {
						$TipoGAMESA = fBuscaDato("Prefijo", "TAerogenerador", "Id=".$aux->nodeValue, $conn)." ".$Altura;

						if (is_object($aux = $torre->getElementsByTagName("TipoAegGAMESA")->item(0)))
							$aux->nodeValue = $TipoGAMESA;
						unset($TipoGAMESA);
					}
					unset($Altura);
				}

				// Líneas de vida
				foreach($torre->getElementsByTagName("Linea") as $linea)
				{   // Registro a guardar
					if (($IdPletina = (is_object($aux=$linea->getElementsByTagName("IdPletina")->item(0))) ? $aux->nodeValue : 0) == $_POST['LinPle'])
					{   // OD, 14.03.13 Añado que se pueda modificar más campos
						if (($ConfPletina = $_POST['ConfPletina']) == 1)
							$CampoPletina1 = $CampoPletina2 = $CampoPletina3 = $CampoPletina4 = 1;
						else
						{
							$CampoPletina1 = $_POST['CampoPletina1']==1?1:$_POST['OpConfPletina1'];
							$CampoPletina2 = $_POST['CampoPletina2']==1?1:$_POST['OpConfPletina2'];
							$CampoPletina3 = $_POST['CampoPletina3']==1?1:$_POST['OpConfPletina3'];
							$CampoPletina4 = $_POST['CampoPletina4']==1?1:$_POST['OpConfPletina4'];
						}

						if (($VarillasRoscadas = $_POST['VarillasRoscadas']) != 1)
							$VarillasRoscadas = 1 + $_POST['NumeroVarillas'];

						// Anclaje Inferior
						$AnclajeInf1AIMotivo = ""; $AnclajeInf1AI = $AnclajeInf1Tensor = 1;
						$AnclajeInf1Perrillos = $AnclajeInf1Guardacabos = $AnclajeInf1Tuercas = 1;
						if (($AnclajeInf1 = $_POST['AnclajeInf1']==1?1:0) != 1)
						{
							$AnclajeInf1AI = $_POST['AnclajeInf1AI'];
							$AnclajeInf1AIMotivo = ($AnclajeInf1AI != 1) ? $_POST['AnclajeInf1AIMotivo'] : "";
							$AnclajeInf1Tensor = $_POST['AnclajeInf1Tensor'];
							$AnclajeInf1Guardacabos = $_POST['AnclajeInf1Guardacabos'];
							$AnclajeInf1Tuercas = $_POST['AnclajeInf1Tuercas'];
							
							// ODG, 05.01.15, En la nueva línea de vida Perrillos pasa a ser Tornillo
							if (($AnclajeInf1Perrillos = $_POST['AnclajeInf1Perrillos']) == "")
								$AnclajeInf1Perrillos = isset($_POST['AnclajeInf1PerrillosA']) ? $_POST['AnclajeInf1PerrillosA'] : 3;
						}
						
						// Anclaje Superior
						$AnclajeSup1ASMotivo = "";
						$AnclajeSup1AS = $AnclajeSup1Pasador = $AnclajeSup1Bulon = 1;
						if (($AnclajeSup1 = $_POST['AnclajeSup1']==1?1:0) != 1)
						{   // 0 - No Ok, 1 - OK, 2 - Colocar, 3 - Añadir
							$AnclajeSup1AS = $_POST['AnclajeSup1AS'];
							$AnclajeSup1ASMotivo = ($AnclajeSup1AS != 1) ? $_POST['AnclajeSup1ASMotivo'] : "";
							$AnclajeSup1Pasador = $_POST['AnclajeSup1Pasador'];
							
							// ODG, 05.01.15, En la nueva línea de vida el Bulón pasa a ser Pestañas y tiene cantidad como los Perrillos
							if (($AnclajeSup1Bulon = $_POST['AnclajeSup1Bulon']) == "")
								$AnclajeSup1Bulon = isset($_POST['AnclajeSup1BulonA']) ? $_POST['AnclajeSup1BulonA'] : 3;
						}
						
						// Absorbedor Energia
						$AmortiguadorMuelleMotivo = "";
						$AmortiguadorMuelle = $AmortiguadorPasador = $AmortiguadorBulon = 1;
						if (($Amortiguador = $_POST['Amortiguador']==1?1:0) != 1)
						{
							$AmortiguadorMuelle  = $_POST['AmortiguadorMuelle']==1?1:0;							
							$AmortiguadorPasador = $_POST['AmortiguadorPasador']==1?1:0;
							$AmortiguadorBulon   = $_POST['AmortiguadorBulon']==1?1:0;
							$AmortiguadorMuelleMotivo =
								($AmortiguadorMuelle != 1) ? $_POST['AmortiguadorMuelleMotivo'] : "";
						}
						
						// ODG, 08.04.13 Gonzalo pide que siempre se pueda modificar los datos del CheckList
						if (is_object($aux = $linea->getElementsByTagName("OT")->item(0)))
							$aux->nodeValue = $_POST['OTLV'];
						if (is_object($aux = $linea->getElementsByTagName("Fecha")->item(0)))
							$aux->nodeValue = $_POST['FechaLV'];
						// ODG, 05.01.15 Poder modificar la marca de la Línea de Vida
						if (is_object($aux = $linea->getElementsByTagName("IdMarca")->item(0)))
							$aux->nodeValue = ($_POST['MarcaLV'] != "") ? $_POST['MarcaLV'] : $_POST['OtraMarcaLV'];
						
						if (is_object($aux = $linea->getElementsByTagName("Cable")->item(0)))
							$aux->nodeValue = $_POST['NumeroCable'];
						if (is_object($aux = $linea->getElementsByTagName("Serie")->item(0)))
							$aux->nodeValue = $_POST['NumeroSerie'];
						if (is_object($aux = $linea->getElementsByTagName("Absorbedor")->item(0)))
							$aux->nodeValue = $_POST['NAbsorbedor'];
						if (is_object($aux = $linea->getElementsByTagName("Trompa")->item(0)))
							$aux->nodeValue = $_POST['NTrompa'];
						if (is_object($aux = $linea->getElementsByTagName("Tramo")->item(0)))
							$aux->nodeValue = $_POST['NTramo'];

						if (is_object($aux = $linea->getElementsByTagName("Resultado")->item(0)))
							$aux->nodeValue = $_POST['Resultado'];
						if (is_object($aux = $linea->getElementsByTagName("EstadoCable")->item(0)))
							$aux->nodeValue = $_POST['EstadoCable'];
						if (is_object($aux = $linea->getElementsByTagName("CantidadCable")->item(0)))
							$aux->nodeValue = ($_POST['EstadoCable'] != 1) ? $_POST['CantidadCable'] : "0.00";
						if (is_object($aux = $linea->getElementsByTagName("EstadoCableMotivo")->item(0)))
							$aux->nodeValue = ($_POST['EstadoCable'] != 1) ? (($_POST['EstadoCableCB'] != "") ? $_POST['EstadoCableCB'] : $_POST['EstadoCableMotivo']) : "";
						if (is_object($aux = $linea->getElementsByTagName("Tension")->item(0)))
							$aux->nodeValue = ($_POST['Tension']==1)?1:0;
						if (is_object($aux = $linea->getElementsByTagName("ConfPletina")->item(0)))
							$aux->nodeValue = $ConfPletina;
						if (is_object($aux = $linea->getElementsByTagName("CampoPletina1")->item(0)))
							$aux->nodeValue = $CampoPletina1;
						if (is_object($aux = $linea->getElementsByTagName("CampoPletina2")->item(0)))
							$aux->nodeValue = $CampoPletina2;
						if (is_object($aux = $linea->getElementsByTagName("CampoPletina3")->item(0)))
							$aux->nodeValue = $CampoPletina3;
						if (is_object($aux = $linea->getElementsByTagName("CampoPletina4")->item(0)))
							$aux->nodeValue = $CampoPletina4;
						if (is_object($aux = $linea->getElementsByTagName("VarillasRoscadas")->item(0)))
							$aux->nodeValue = $VarillasRoscadas;
						if (is_object($aux = $linea->getElementsByTagName("Cartel")->item(0)))
							$aux->nodeValue = ($_POST['Cartel']==1)?1:0;
							
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1")->item(0)))
							$aux->nodeValue=$AnclajeInf1;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1AI")->item(0)))
							$aux->nodeValue=$AnclajeInf1AI;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1AIMotivo")->item(0)))
							$aux->nodeValue=$AnclajeInf1AIMotivo;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Tensor")->item(0)))
							$aux->nodeValue=$AnclajeInf1Tensor;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Perrillos")->item(0)))
							$aux->nodeValue=$AnclajeInf1Perrillos;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Guardacabos")->item(0)))
							$aux->nodeValue=$AnclajeInf1Guardacabos;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Tuercas")->item(0)))
							$aux->nodeValue=$AnclajeInf1Tuercas;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1")->item(0)))
							$aux->nodeValue=$AnclajeSup1;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1AS")->item(0)))
							$aux->nodeValue=$AnclajeSup1AS;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1ASMotivo")->item(0)))
							$aux->nodeValue=$AnclajeSup1ASMotivo;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior2")->item(0)))
							$aux->nodeValue=$_POST['AnclajeSup2'];
						if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1Pasador")->item(0)))
							$aux->nodeValue=$AnclajeSup1Pasador;
						if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1Bulon")->item(0)))
							$aux->nodeValue=$AnclajeSup1Bulon;

						if (is_object($aux = $linea->getElementsByTagName("Amortiguador")->item(0)))
							$aux->nodeValue=$Amortiguador;
						if (is_object($aux = $linea->getElementsByTagName("AmortiguadorMuelle")->item(0)))
							$aux->nodeValue=$AmortiguadorMuelle;
						if (is_object($aux = $linea->getElementsByTagName("AmortiguadorMuelleMotivo")->item(0)))
							$aux->nodeValue=$AmortiguadorMuelleMotivo;
						if (is_object($aux = $linea->getElementsByTagName("AmortiguadorPasador")->item(0)))
							$aux->nodeValue=$AmortiguadorPasador;
						if (is_object($aux = $linea->getElementsByTagName("AmortiguadorBulon")->item(0)))
							$aux->nodeValue=$AmortiguadorBulon;
						if (is_object($aux = $linea->getElementsByTagName("TornilleriaPletina")->item(0)))
							$aux->nodeValue=$_POST['TornilleriaPletina']==1?1:0;
						if (is_object($aux = $linea->getElementsByTagName("TornilleriaApriete")->item(0)))
							$aux->nodeValue=$_POST['TornilleriaApriete']==1?1:0;
						if (is_object($aux = $linea->getElementsByTagName("Ensayo")->item(0)))
							$aux->nodeValue=$_POST['Ensayo']==1?1:0;
						if (is_object($aux = $linea->getElementsByTagName("Escalera")->item(0)))
							$aux->nodeValue=$_POST['Escalera']==1?1:0;
						if (is_object($aux = $linea->getElementsByTagName("Interferencia")->item(0)))
							$aux->nodeValue=$_POST['Interferencia']==1?1:0;
						if (is_object($aux = $linea->getElementsByTagName("Oxidacion")->item(0)))
							$aux->nodeValue=$_POST['Oxidacion']==1?1:0;
						if (is_object($aux = $linea->getElementsByTagName("Observaciones")->item(0)))
							$aux->nodeValue=$_POST['Observaciones'];
						if (is_object($aux = $linea->getElementsByTagName("TrabajosPendientes")->item(0)))
							$aux->nodeValue=$_POST['TrabajosPendientes'];
					}
				}  // Fin foreah Lineas

				// Revisión Extintores
				$OT = $Fecha = $IdTorre = $IdExtintor = $Localizacion = $Marca = $Modelo = 
				$Sustituido = $PlacaSustitucion = $Colocacion = false; $nExt = 0;
				foreach($torre->getElementsByTagName("Extintor") as $extintor)
				{   // Registro a guardar
					$OT[$nExt] = $_POST['OTExt'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("OT")->item(0)))
						$aux->nodeValue = $OT[$nExt];
					$Fecha[$nExt] = $_POST['FechaExt'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Fecha")->item(0)))
						$aux->nodeValue = $Fecha[$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("IdTorre")->item(0)))
						$IdTorre[$nExt] = $aux->nodeValue;
					if (is_object($aux = $extintor->getElementsByTagName("IdExtintor")->item(0)))
						$IdExtintor[$nExt] = $aux->nodeValue;
						
					$Localizacion[$nExt] = ($_POST['Localizacion'.$nExt] != "") ? $_POST['Localizacion'.$nExt] : $_POST['OtraLocalizacion'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Localizacion")->item(0)))
						$aux->nodeValue = $Localizacion[$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Placa")->item(0)))
						$aux->nodeValue = $_POST['NPlaca'.$nExt];
					$Marca[$nExt] = ($_POST['MarcaExt'.$nExt] != "")?$_POST['MarcaExt'.$nExt]:$_POST['OtraMarca'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Marca")->item(0)))
						$aux->nodeValue = $Marca[$nExt];
					$Modelo[$nExt] = ($_POST['ModeloExt'.$nExt] != "")?($_POST['ModeloExt'.$nExt] != "0" ? $_POST['ModeloExt'.$nExt]:"") : $_POST['OtroModelo'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Modelo")->item(0)))
						$aux->nodeValue = $Modelo[$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("FechaFabricacion")->item(0)))
						$aux->nodeValue = $_POST['FechaFabricacion'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("FechaRetimbrado")->item(0)))
						$aux->nodeValue = $_POST['FechaRetimbrado'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("AgenteExtintor")->item(0)))
						$aux->nodeValue = ($_POST['AgenteExtintor'.$nExt] != "") ? $_POST['AgenteExtintor'.$nExt] : $_POST['OtroAgExtintor'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("PesoAgExtintor")->item(0)))
						$aux->nodeValue = $_POST['PesoAgExtintor'.$nExt];
					$Colocacion[$nExt] = ($_POST['Colocacion'.$nExt] != "") ? ($_POST['Colocacion'.$nExt] != "0" ? $_POST['Colocacion'.$nExt] : "") : $_POST['OtraColocacion'.$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Colocacion")->item(0)))
						$aux->nodeValue = $Colocacion[$nExt];
					if (is_object($aux = $extintor->getElementsByTagName("Movido")->item(0)))
						$aux->nodeValue = ($_POST['MovidoA'.$nExt] != "") ? ($_POST['MovidoA'.$nExt] != "0" ? $_POST['MovidoA'.$nExt] : "") : $_POST['OtroMovidoA'.$nExt];
		
					$Sustituido[$nExt] = ($_POST['Sustituido'.$nExt]==1)?1:0;
					if ($_POST['Sustituto'.$nExt] == 2)
						$Sustituido[$nExt] = 2;
					if (is_object($aux = $extintor->getElementsByTagName("Sustituido")->item(0)))
						$aux->nodeValue = $Sustituido[$nExt];
					$PlacaSustitucion[$nExt] = ($_POST['Sustituido'.$nExt]==1) ? $_POST['PlacaSustitucion'.$nExt] : "";
					if (is_object($aux = $extintor->getElementsByTagName("PlacaSustitucion")->item(0)))
						$aux->nodeValue = $PlacaSustitucion[$nExt];

					if (is_object($aux = $extintor->getElementsByTagName("PrecintoSustitucion")->item(0)))
						$aux->nodeValue = ($_POST['PrecintoSustitucion'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("CartelLu")->item(0)))
						$aux->nodeValue = ($_POST['CartelLu'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PegatinaCarac")->item(0)))
						$aux->nodeValue = ($_POST['PegatinaCarac'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PegatinaRevi")->item(0)))
						$aux->nodeValue = ($_POST['PegatinaRevi'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("MarcadoCE")->item(0)))
						$aux->nodeValue = ($_POST['MarcadoCE'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("PrecintoRetimbrado")->item(0)))
						$aux->nodeValue = ($_POST['PrecintoRetimbrado'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoCuerpo")->item(0)))
						$aux->nodeValue = ($_POST['EstadoCuerpo'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("EstadoCabeza")->item(0)))
						$aux->nodeValue = ($_POST['EstadoCabeza'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Pasador")->item(0)))
						$aux->nodeValue = ($_POST['Pasador'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Valvula")->item(0)))
						$aux->nodeValue = ($_POST['Valvula'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Manguera")->item(0)))
						$aux->nodeValue = ($_POST['Manguera'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Soporte")->item(0)))
						$aux->nodeValue = ($_POST['Soporte'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Junta")->item(0)))
						$aux->nodeValue = ($_POST['Junta'.$nExt] == 1) ? 1 : 0;
					if (is_object($aux = $extintor->getElementsByTagName("Materiales")->item(0)))
						$aux->nodeValue = $_POST['Materiales'.$nExt];
					
					$FaltaPeso = $Caducidad = 0; $Otra = "";
					if (($EstadoExt = ($_POST['EstadoExt'.$nExt]==1)?1:0)==0)
					{
						$FaltaPeso = ($_POST['FaltaPeso'.$nExt]== 1)?1:0;
						$Caducidad = ($_POST['Caducidad'.$nExt]== 1)?1:0;
						$Otra = $_POST['Otra'.$nExt];
					}
					
					if (is_object($aux = $extintor->getElementsByTagName("EstadoExt")->item(0)))
						$aux->nodeValue = $EstadoExt;
					if (is_object($aux = $extintor->getElementsByTagName("FaltaPeso")->item(0)))
						$aux->nodeValue = $FaltaPeso;
					if (is_object($aux = $extintor->getElementsByTagName("Caducidad")->item(0)))
						$aux->nodeValue = $Caducidad;
					if (is_object($aux = $extintor->getElementsByTagName("Otra")->item(0)))
						$aux->nodeValue = $Otra;
					if (is_object($aux = $extintor->getElementsByTagName("ObservacionesExt")->item(0)))
						$aux->nodeValue = $_POST['ObservacionesExt'.$nExt];
					unset($EstadoExt, $FaltaPeso, $Caducidad, $Otra);
					
					$nExt ++;
				}

				// Compruebo si hay algún extintor sustuido, para crear el CheckList de sustitución
				for ($Count = 0; $Count < $nExt; $Count ++)
				{   // Si debe de ser sustituido, y además no lo hemos creado, añado el extintor de de sustituición
					if ($Sustituido[$Count] == 1)
					{   // Compruebo que no haya sido ya sustituido
						$Sustituye = true;
						$TmpIdExt = $TmpSusExt = 0;
						foreach($torre->getElementsByTagName("Extintor") as $extintor)
						{
							if (is_object($aux = $extintor->getElementsByTagName("IdExtintor")->item(0)))
								$TmpIdExt = $aux->nodeValue;
							if (is_object($aux = $extintor->getElementsByTagName("Sustituido")->item(0)))
								$TmpSusExt = $aux->nodeValue;
							
							if ($TmpIdExt == $IdExtintor[$Count] && $TmpSusExt == 2) {
								$Sustituye = false; break;
							}
						}
						unset($TmpIdExt,$TmpSusExt, $extintor);

						if ($Sustituye)
						{
							$extintor = $torre->appendChild($doc->createElement("Extintor"));
							$extintor->appendChild($doc->createElement("OT", $OT[$Count]));
							$extintor->appendChild($doc->createElement("Fecha", $Fecha[$Count]));
							$extintor->appendChild($doc->createElement("IdTorre", $IdTorre[$Count]));
							$extintor->appendChild($doc->createElement("IdExtintor", $IdExtintor[$Count]));

							$extintor->appendChild($doc->createElement("Localizacion", $Localizacion[$Count]));
							$extintor->appendChild($doc->createElement("Placa", $PlacaSustitucion[$Count]));
							$extintor->appendChild($doc->createElement("Marca", "Seleccione ..."));
							$extintor->appendChild($doc->createElement("Modelo", $Modelo[$Count]));
							$extintor->appendChild($doc->createElement("FechaFabricacion"));
							$extintor->appendChild($doc->createElement("FechaRetimbrado"));
							$extintor->appendChild($doc->createElement("AgenteExtintor", "CO2"));
							$extintor->appendChild($doc->createElement("PesoAgExtintor"));
							$extintor->appendChild($doc->createElement("Colocacion", $Colocacion[$Count]));
							$extintor->appendChild($doc->createElement("Movido"));
							$extintor->appendChild($doc->createElement("Sustituido", "2"));	// 0 - No, 1 - Si, 2 - Sustitución
							$extintor->appendChild($doc->createElement("PlacaSustitucion"));
							$extintor->appendChild($doc->createElement("PrecintoSustitucion", "1"));
							$extintor->appendChild($doc->createElement("CartelLu", "1"));
							$extintor->appendChild($doc->createElement("PegatinaCarac", "1"));
							$extintor->appendChild($doc->createElement("PegatinaRevi", "1"));
							$extintor->appendChild($doc->createElement("MarcadoCE", "1"));
							$extintor->appendChild($doc->createElement("PrecintoRetimbrado", "1"));
							$extintor->appendChild($doc->createElement("EstadoCuerpo", "1"));
							$extintor->appendChild($doc->createElement("EstadoCabeza", "1"));
							$extintor->appendChild($doc->createElement("Pasador", "1"));
							$extintor->appendChild($doc->createElement("Valvula", "1"));
							$extintor->appendChild($doc->createElement("Manguera", "1"));
							$extintor->appendChild($doc->createElement("Soporte", "1"));
							$extintor->appendChild($doc->createElement("Junta", "1"));
							$extintor->appendChild($doc->createElement("Materiales"));
							$extintor->appendChild($doc->createElement("EstadoExt", "1"));
							$extintor->appendChild($doc->createElement("FaltaPeso"));
							$extintor->appendChild($doc->createElement("Caducidad"));
							$extintor->appendChild($doc->createElement("Otra"));
							$extintor->appendChild($doc->createElement("ObservacionesExt"));

							unset($extintor);
						}
					}
					else if (!$Sustituido[$Count])
					{   // Sino ha sido Sustituido compruebo por si a caso si antes sí lo ha sido, y eliminamos el de sustitución
						$TmpIdExt = $TmpSusExt = 0;
						foreach($torre->getElementsByTagName("Extintor") as $extintor)
						{
							if (is_object($aux = $extintor->getElementsByTagName("IdExtintor")->item(0)))
								$TmpIdExt = $aux->nodeValue;
							if (is_object($aux = $extintor->getElementsByTagName("Sustituido")->item(0)))
								$TmpSusExt = $aux->nodeValue;

							if ($TmpIdExt == $IdExtintor[$Count] && $TmpSusExt == 2) {
								$torre->removeChild($extintor); break;
							}
						}
						unset($TmpIdExt,$TmpSusExt, $extintor);
					}
				}
				unset($nExt, $OT, $Fecha, $IdTorre, $IdExtintor, $Localizacion, $Marca, $Modelo,
					$Sustituido, $PlacaSustitucion, $Colocacion);

				// * * Descensores * * 
				// 1ro, Borro el nodo Descensor de la torre, si procede
				$IdDescensor = 0;
				foreach($torre->getElementsByTagName("Descensor") as $descensor)
				{
					if (is_object($aux = $descensor->getElementsByTagName("IdDescensor")->item(0))) {
						if (($IdDescensor = is_numeric($aux->nodeValue)?$aux->nodeValue:0) == $_POST['IdDescensor']) {
							$torre->removeChild($descensor); break;
						}
						else
							$IdDescensor = 0;
					}
				}

				// 2do, Creo el Descensor con los valores, esto lo hago por si han cambiado de tipo de Descensor
				if ($IdDescensor != 0)
				{
					$descensor = $torre->appendChild($doc->createElement("Descensor"));
					$descensor->appendChild($doc->createElement("OT", $_POST['OTDes']));
					$descensor->appendChild($doc->createElement("Fecha", $_POST['FechaDes']));
					$descensor->appendChild($doc->createElement("IdTorre", $_POST['LinCod']));
					$descensor->appendChild($doc->createElement("IdDescensor", $IdDescensor));
					$descensor->appendChild($doc->createElement("NSerie", $_POST['NSerie']));

					$descensor->appendChild(($aux = $doc->createElement("Fabricante")));
					$aux->appendChild($doc->createTextNode((($Fabricante = is_numeric($_POST['Fabricante'])?$_POST['Fabricante']:0) != 0? 
						fBuscaDato("Nombre","MarcaDes","Id=".$Fabricante,$conn) : $_POST['OtroFabricante'])));
					$descensor->appendChild(($aux = $doc->createElement("ModeloDes")));
					$aux->appendChild($doc->createTextNode((($ModeloDes = is_numeric($_POST['ModeloDes'])?$_POST['ModeloDes']:0) != 0 ? 
						fBuscaDato("Nombre","ModeloDes","Id=".$ModeloDes,$conn) : $_POST['OtroModeloDes'])));

					$descensor->appendChild($doc->createElement("Longitud", $_POST['Longitud']));
					$descensor->appendChild($doc->createElement("PrecintoViejo", $_POST['PrecintoViejo']));
					$descensor->appendChild($doc->createElement("PrecintoNuevo", $_POST['PrecintoNuevo']));
					$descensor->appendChild($doc->createElement("AnyoFabricacion", $_POST['AnyoFabricacion']));
					$descensor->appendChild($doc->createElement("Ubicacion", $_POST['Ubicacion']));
					$descensor->appendChild($doc->createElement("Envasado", $_POST['Envasado']));

					if ($Fabricante == 2)	// MITTELMANN
					{
						$descensor->appendChild($doc->createElement("MaletinEqu", $_POST['MaletinEqu']));
						$descensor->appendChild($doc->createElement("SisEnvaseEqu", $_POST['SisEnvaseEqu']));
						$descensor->appendChild($doc->createElement("SacaAzulTran", $_POST['SacaAzulTran']));
						$descensor->appendChild($doc->createElement("BolsaPlastico", $_POST['BolsaPlastico']));
						$descensor->appendChild($doc->createElement("DescensorMRG", $_POST['DescensorMRG']));
						$descensor->appendChild($doc->createElement("CuerdaDesMRG", $_POST['CuerdaDesMRG']));
						$descensor->appendChild($doc->createElement("CuerdasSeguridad", $_POST['CuerdasSeguridad']));
						$descensor->appendChild($doc->createElement("PegatinaPrecinto", $_POST['PegatinaPrecinto']));
						$descensor->appendChild($doc->createElement("BridaPrecinto", $_POST['BridaPrecinto']));
						$descensor->appendChild($doc->createElement("EtiquetaExterior", $_POST['EtiquetaExterior']));
						$descensor->appendChild($doc->createElement("LibroInspecciones", $_POST['LibroInspecciones']));
						$descensor->appendChild($doc->createElement("MaletinEmb", $_POST['MaletinEmb']));
						$descensor->appendChild($doc->createElement("SisEnvaseEmb", $_POST['SisEnvaseEmb']));
						$descensor->appendChild($doc->createElement("BolsaPlasEmb", $_POST['BolsaPlasEmb']));
						$descensor->appendChild($doc->createElement("SacaAzulEmb", $_POST['SacaAzulEmb']));
						$descensor->appendChild($doc->createElement("PegatinaPreEmb", $_POST['PegatinaPreEmb']));
						$descensor->appendChild($doc->createElement("BridaPreEmb", $_POST['BridaPreEmb']));
						$descensor->appendChild($doc->createElement("PreTornillTFreno", $_POST['PreTornillTFreno']));
						$descensor->appendChild($doc->createElement("GrosorPasTFreno", $_POST['GrosorPasTFreno']));
						$descensor->appendChild($doc->createElement("EstMuelleTFreno", $_POST['EstMuelleTFreno']));
						$descensor->appendChild($doc->createElement("EjePinonTFreno", $_POST['EjePinonTFreno']));
						$descensor->appendChild($doc->createElement("LimPinonTFreno", $_POST['LimPinonTFreno']));
						$descensor->appendChild($doc->createElement("ZonaSurcosTFreno", $_POST['ZonaSurcosTFreno']));
						$descensor->appendChild($doc->createElement("ZonaLimpiaTFreno", $_POST['ZonaLimpiaTFreno']));
						$descensor->appendChild($doc->createElement("EstTornillTFreno", $_POST['EstTornillTFreno']));
						$descensor->appendChild($doc->createElement("TorLoctiteTFreno", $_POST['TorLoctiteTFreno']));
						$descensor->appendChild($doc->createElement("MarcasTornTFreno", $_POST['MarcasTornTFreno']));
						$descensor->appendChild($doc->createElement("PreTornillTPolea", $_POST['PreTornillTPolea']));
						$descensor->appendChild($doc->createElement("HolguraEjeTPolea", $_POST['HolguraEjeTPolea']));
						$descensor->appendChild($doc->createElement("EstNerviosTPolea", $_POST['EstNerviosTPolea']));
						$descensor->appendChild($doc->createElement("EstCarcasaTPolea", $_POST['EstCarcasaTPolea']));
						$descensor->appendChild($doc->createElement("EstTornillTPolea", $_POST['EstTornillTPolea']));
						$descensor->appendChild($doc->createElement("TorLoctiteTPolea", $_POST['TorLoctiteTPolea']));
						$descensor->appendChild($doc->createElement("MarcasTornTPolea", $_POST['MarcasTornTPolea']));
						$descensor->appendChild($doc->createElement("PreTornillCFreno", $_POST['PreTornillCFreno']));
						$descensor->appendChild($doc->createElement("EstJuntaCFreno", $_POST['EstJuntaCFreno']));
						$descensor->appendChild($doc->createElement("EstDientesCFreno", $_POST['EstDientesCFreno']));
						$descensor->appendChild($doc->createElement("RuedaLimpiaCFreno", $_POST['RuedaLimpiaCFreno']));
						$descensor->appendChild($doc->createElement("EstTornillCFreno", $_POST['EstTornillCFreno']));
						$descensor->appendChild($doc->createElement("TorLoctiteCFreno", $_POST['TorLoctiteCFreno']));
						$descensor->appendChild($doc->createElement("MarcasTornCFreno", $_POST['MarcasTornCFreno']));
						$descensor->appendChild($doc->createElement("DeslizamientoCuerda", $_POST['DeslizamientoCuerda']));
						$descensor->appendChild($doc->createElement("EstGenCuerdaPri", $_POST['EstGenCuerdaPri']));
						$descensor->appendChild($doc->createElement("EstProCuerdaPri", $_POST['EstProCuerdaPri']));
						$descensor->appendChild($doc->createElement("LongitudCuerdaPri", $_POST['LongitudCuerdaPri']));
						$descensor->appendChild($doc->createElement("LongMedidaCuerdaPri", $_POST['LongMedidaCuerdaPri']));
						$descensor->appendChild($doc->createElement("MosquetonCuerdaPri", $_POST['MosquetonCuerdaPri']));
						$descensor->appendChild($doc->createElement("AnyoFabCuerdaPri", $_POST['AnyoFabCuerdaPri']));
						$descensor->appendChild($doc->createElement("EstGenCuerdaSeg", $_POST['EstGenCuerdaSeg']));
						$descensor->appendChild($doc->createElement("SupSacaCuerdaSeg", $_POST['SupSacaCuerdaSeg']));
						$descensor->appendChild($doc->createElement("NSerieCuerdaSeg1", $_POST['NSerieCuerdaSeg1']));
						$descensor->appendChild($doc->createElement("AnyoFabCuerdaSeg1", $_POST['AnyoFabCuerdaSeg1']));
						$descensor->appendChild($doc->createElement("NSerieCuerdaSeg2", $_POST['NSerieCuerdaSeg2']));
						$descensor->appendChild($doc->createElement("AnyoFabCuerdaSeg2", $_POST['AnyoFabCuerdaSeg2']));
						$descensor->appendChild($doc->createElement("EstMosqueton", $_POST['EstMosqueton']));
						$descensor->appendChild($doc->createElement("FucMosqueton", $_POST['FucMosqueton']));
					}
					else					 // Por defecto, PSA
					{
						$descensor->appendChild($doc->createElement("Bolsa", $_POST['Bolsa']));
						$descensor->appendChild($doc->createElement("Sellado", $_POST['Sellado']));
						$descensor->appendChild($doc->createElement("NumeroSello", $_POST['NumeroSello']));
						$descensor->appendChild($doc->createElement("DescensorAG", $_POST['DescensorAG']));
						$descensor->appendChild($doc->createElement("CaboAnclaje", $_POST['CaboAnclaje']));
						$descensor->appendChild($doc->createElement("Humedad", $_POST['Humedad']));
						$descensor->appendChild($doc->createElement("EtiquetaLegible", $_POST['EtiquetaLegible']));
						$descensor->appendChild($doc->createElement("EstadoCarcasa", $_POST['EstadoCarcasa']));
						$descensor->appendChild($doc->createElement("CuerdaEntrada", $_POST['CuerdaEntrada']));
						$descensor->appendChild($doc->createElement("CuerdaSalida", $_POST['CuerdaSalida']));
						$descensor->appendChild($doc->createElement("MosquetonArgolla", $_POST['MosquetonArgolla']));

						$descensor->appendChild($doc->createElement("NecesarioAbrir", ($_POST['NecesarioAbrir']==1)?"1":"0"));
						$descensor->appendChild($doc->createElement("RuedaDentada", $_POST['RuedaDentada']));
						$descensor->appendChild($doc->createElement("Dientes", $_POST['Dientes']));
						$descensor->appendChild($doc->createElement("PoleaCuerda", $_POST['PoleaCuerda']));
						$descensor->appendChild($doc->createElement("SuperficiePolea", $_POST['SuperficiePolea']));
						$descensor->appendChild($doc->createElement("CajaFreno", $_POST['CajaFreno']));
						$descensor->appendChild($doc->createElement("UnidadFreno", $_POST['UnidadFreno']));
						$descensor->appendChild($doc->createElement("ProfundidadFreno", $_POST['ProfundidadFreno']));
						$descensor->appendChild($doc->createElement("GuarnicionFreno", $_POST['GuarnicionFreno']));
						$descensor->appendChild($doc->createElement("ZapatasFreno", $_POST['ZapatasFreno']));
						$descensor->appendChild($doc->createElement("ControlMuelle", $_POST['ControlMuelle']));
						$descensor->appendChild($doc->createElement("FlancosArbol", $_POST['FlancosArbol']));
						$descensor->appendChild($doc->createElement("PuntosApoyo", $_POST['PuntosApoyo']));
						
						$descensor->appendChild($doc->createElement("EstadoCuerda", $_POST['EstadoCuerda']));
						$descensor->appendChild($doc->createElement("FinDeCuerda", $_POST['FinDeCuerda']));
						$descensor->appendChild($doc->createElement("Termoretractil", $_POST['Termoretractil']));
						$descensor->appendChild($doc->createElement("AnyoFabCuerdaPri", $_POST['AnyoFabCuerdaPri']));
						$descensor->appendChild($doc->createElement("EstMosquetonPri", $_POST['EstMosquetonPri']));
						$descensor->appendChild($doc->createElement("EstCuerdaSeguridad", $_POST['EstCuerdaSeguridad']));
						$descensor->appendChild($doc->createElement("MosquetonSeguridad", $_POST['MosquetonSeguridad']));
						$descensor->appendChild($doc->createElement("NSerieSeguridad", $_POST['NSerieSeguridad']));
						$descensor->appendChild($doc->createElement("AnyoFabCuerdaSeg", $_POST['AnyoFabCuerdaSeg']));
						$descensor->appendChild($doc->createElement("DeslizamientoCuerda", $_POST['DeslizamientoCuerda']));
						$descensor->appendChild($doc->createElement("CargaMinima", $_POST['CargaMinima']));
						$descensor->appendChild($doc->createElement("VainaCuerda", $_POST['VainaCuerda']));
						$descensor->appendChild($doc->createElement("Mordazas", $_POST['Mordazas']));
					}

					$descensor->appendChild($doc->createElement("EstadoDes", $_POST['EstadoDes']));

					// Creamos las Líneas de Materiales
					for ($nMat = 1; $nMat < 5; $nMat ++)
					{
						$descensor->appendChild($doc->createElement("Material".($Tmp = sprintf("%02d",$nMat)), $_POST['Material'.$Tmp]));
						$descensor->appendChild($doc->createElement("Motivo".$Tmp, $_POST['Motivo'.$Tmp]));
						$descensor->appendChild($doc->createElement("Cantidad".$Tmp, 
							(is_numeric($_POST['Cantidad'.$Tmp])?$_POST['Cantidad'.$Tmp]:0)));
					}
				}
			} // Es Linea
		}  // Fin foreach Torres
		
		if (($FileXml = fopen("../data/".$_POST['Editar'], "w"))) {
			fputs ($FileXml, $doc->saveXML());
			fclose($FileXml);
		}
		
		header('Location:EditarLCMyR.php?Editar='.$_POST['Editar'].(($Pendiente)?'&Pendiente=1':''));
	}  // Fin file_exists
}
if ($conn)
	mysql_close($conn);
?>