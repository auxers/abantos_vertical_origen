<?php
// Se encargará por AJAX rellenar los datos de la Tabla Validar MyR
header("Content-Type:text/html; charset=UTF-8");
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$Html = '<TABLE class="tablanaranja" cellpadding=0 cellspacing=0>';
$CabeceraImpresa = false; $ParqueAnt = "";
if (($Tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : "") != "")
{
	$directorio = dir("../../data/");
	
	while ($Archivo = $directorio->read())
	{   // Seleccionamos sólo los archivos que estén marcados
		if (substr($Archivo, 21, 1) === "1")
		{   if (substr($Archivo, 0, 1) === $Tipo)
			{   if (!$CabeceraImpresa)
				{
					$Html .= '
						<tr>
							<th width=300><b>TRABAJADOR</b></th>
							<th width=175><b>PARQUE</b></th>
							<th width=50><b>TABLET</b></th>
							<th width=75>&nbsp;</th>
						</tr>';

					$CabeceraImpresa = true;
				}

				// Cód. Parque
				if (($CodParque = fQuitaZeros(substr($Archivo,1,10))) != $ParqueAnt)
				{
					$ParqueAnt = $CodParque;
					$Count = 1;
				}
				else
					$Count ++;

				// Obtengo el Parque y los Trabajadores
				$doc = new DOMDocument();
				$doc->load("../../data/".$Archivo);
				
				$Parque = $Operario1 = $Operario2 = "";
				foreach($doc->getElementsByTagName("Parque") as $parque)
				{
					if (is_object($aux = $parque->getElementsByTagName("Nombre")->item(0)))
						$Parque = $aux->nodeValue;
					if (is_object($aux = $parque->getElementsByTagName("Operario1")->item(0)))
						$Operario1 = $aux->nodeValue;
					if (is_object($aux = $parque->getElementsByTagName("Operario2")->item(0)))
						$Operario2 = $aux->nodeValue;
				}

				if (is_numeric($Operario1))
				{
					if (($result = mysql_query("SELECT Nombre FROM Trabajadores WHERE Id=".$Operario1, $conn)))
					{
						if (($row = mysql_fetch_array($result)))
							$Operario1 = $row['Nombre'];
					}
				}				
				if (is_numeric($Operario2))
				{
					if (($result = mysql_query("SELECT Nombre FROM Trabajadores WHERE Id=".$Operario2, $conn)))
					{
						if (($row = mysql_fetch_array($result)))
							$Operario2 = $row['Nombre'];
					}
				}

				$CodTablet = substr($Archivo, 16, 5);
				$Operarios = $Operario1.'<br/>'.$Operario2;
				if ($CabeceraImpresa)
				{   // OD, 06.03.13 Modifico para que se pueda Validar más de un XML a la vez...
					$Html .= '
						<tr>
							<td style="padding-left:5px;line-height:20px;">'.$Operarios.'</td>
							<td style="padding-left:5px;line-height:20px;">'.$Parque.'</td>
							<td align="center" style="padding-left:5px;line-height:20px;">'.fQuitaZeros($CodTablet).'</td>
							<td align="center" style="line-height:20px;">
								<input type="checkbox" name="XML'.sprintf("%04s",$CodParque).sprintf("%02s",$Count).'" VALUE='.$Archivo.'>
								<a href="EditarLCMyR.php?Editar='.$Archivo.'"><img src="../img/bt_edit.png" alt="Editar"></a>
								<a href="javascript:;" onclick="fConfirmar(\'ValidarCLMyRLV.php?Tipo='.$Tipo.'&Borrar='.$Archivo.'\',\'eliminar\'); return false;"><img src="../img/bt_del.png" alt="Borrar"></a>
							</td>
						</tr>';
				}
			}
		}
	}
	$directorio->close();
}
if (!$CabeceraImpresa)
    $Html .= '<tr><td><b>No hay ningún fichero pendiente</b></td></tr>';
$Html .= '</TABLE>';

if ($CabeceraImpresa)
{
	$Html .= '<br/>
	<button id="Assign">Validar</button>
	<input type="hidden" id="Confirmar" name="Confirmar" value="Validar"/>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#Assign").button({
				icons: { primary: "ui-icon-check" }
			});
		
			// Al Hacer click en Confirmar, hago el submit del Form
			$("#Assign").click(function(event) {				
				var sError = "";
				event.preventDefault();
				
				$("input[type=checkbox]:checked").each(function() {
					if ($(this).attr("name").substr(0,3) == "XML")
					{
						$.ajax(
						{
					        type: "POST",
				    	    url: "../ajax/inspeccion/validaCTRL.php?File="+$(this).val(),
							dataType: "text", async:false,
							success: function(sDato) {							
								if ((sError = sDato) != "")
									jAlert (sError, null);
							}
						});
					}
				});

				if (sError == "")
					$("#Control").submit();
			});
		});
	</script>';
}

echo $Html;
if ($conn)
	mysql_close($conn);
?>