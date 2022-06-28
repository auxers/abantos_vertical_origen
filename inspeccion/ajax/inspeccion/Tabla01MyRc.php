<?php
// Se encargará mediante AJAX obtener todas las Torres de un Parque
header("Content-Type:text/html; charset=UTF-8");
require_once("../../db-config.php");

$Parque = isset($_REQUEST['Parque']) ? $_REQUEST['Parque'] : "0";
$Tipo   = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : "";

$Html = '<TABLE CLASS="table" cellpadding=1 cellspacing=0 width=100%>';

// Selecionamos los AEG's del Parque
$Query = "SELECT L.Id, L.NumeroTorre AS IdTorre, L.TipoAerogeneradorGAMESA AS TipoAegGAMESA, TA.IdGrupo FROM Lineas L 
	JOIN TAerogenerador TA ON TA.Id=L.TipoAerogenerador WHERE L.IdParque=".$Parque." ORDER BY L.NumeroTorre ASC;";
if (($result = mysql_query($Query, $conn)))
{
	$NumCol = 0;
	while($row = mysql_fetch_array($result))
	{
		if ($NumCol < 1)
		{
			$NumCol = 4;
			$Html .= '  <TR>';
		}

		$Id = sprintf("%05s",$row['Id']);
		$Texto = ($row['IdGrupo'] != 0) ? "Torre (".$row['IdTorre'].") ".$row['TipoAegGAMESA'] : "SubEstación";
		$Html .= '	<TD width="25%">
					   <table>
					     <tr>
						   <td>
						   </td>
						   <td>
   							<INPUT TYPE="checkbox" NAME="AEG'.$Id.'" CLASS="Torre" VALUE=1 checked />
							<span class="txtLabel">'.$Texto.'</span>
						   </td>
						 </tr>';
		if ($Tipo != 'M')
		{
			$Html .= '   <tr class="gpoLV" id="LVAEG'.$Id.'">
						   <td><INPUT TYPE="checkbox" NAME="ChkLV'.$Id.'" CLASS="ChkLV" VALUE=1 checked />OT.LV</td>
						   <td>
						   	<INPUT TYPE="text" ID="OrdLV'.$Id.'" NAME="OrdLV'.$Id.'" class="txtLV" value="" maxlength="25" style="width:178px;"/>
						   </td>
						 </tr>
						 <tr class="gpoEX" id="EXAEG'.$Id.'" style="display:none;">
						   <td><INPUT TYPE="checkbox" NAME="ChkEX'.$Id.'" CLASS="ChkEX" VALUE=1 />OT.EX</td>
						   <td>
						    <INPUT TYPE="text" ID="OrdEX'.$Id.'" NAME="OrdEX'.$Id.'" class="txtEX" value="" maxlength="25" style="width:178px;"/>
						   </td>
						 </tr>
						 <tr class="gpoDE" id="DEAEG'.$Id.'" style="display:none;">
						   <td><INPUT TYPE="checkbox" NAME="ChkDE'.$Id.'" CLASS="ChkDE" VALUE=1 />OT.DE</td>
						   <td>
							<INPUT TYPE="text" ID="OrdDE'.$Id.'" NAME="OrdDE'.$Id.'" class="txtDE" value="" maxlength="25" style="width:178px;"/>
						   </td>
						 </tr>';
		}
		else
		{
			$Html .= '	<tr>
						   <td></td>
						   <td>
						   	<INPUT TYPE="hidden" NAME="ChkLV'.$Id.'" CLASS="ChkLV" VALUE=1 />
						   </td>
						</tr>';
		}
		$Html .= '    </table>
					 </TD>';

		if ($NumCol < 1)
			$Html .= '  </TR>';
		$NumCol -= 1;
	}
}

$Html .= '</TABLE>';
$Html .= '<TABLE CLASS="table" style="padding-top:5px;">
			<TR '.(($Tipo == 'M' )?"style='display:none'":"").'>
				<TD>
                	<INPUT TYPE="checkbox" id="LineasVida" name="LineasVida" VALUE=1 checked /><span class="txtLabel">Líneas Vida</span>
				</TD>
				<TD>
                	<INPUT TYPE="checkbox" id="Extintores" name="Extintores" VALUE=1 /><span class="txtLabel">Extintores</span>
				</TD>
                <TD>
					<INPUT TYPE="checkbox" id="Descensor" name="Descensor" VALUE=1 /><span class="txtLabel">Descensor</span>
				</TD>
			</TR>
			<TR>
				<TD></TD>
				<TD>
					<BUTTON id="Assign">Asignar</BUTTON>
				</TD>
				<TD>
					<BUTTON id="Cerrar">Cerrar</BUTTON>
				</TD>
			</TR>
		 </TABLE>';
$Html .= '
<script type="text/javascript">
	$(document).ready(function() {
		$("#Assign").button({
			icons: { primary: "ui-icon-disk" }
		});
		$("#Cerrar").button({
			icons: { primary: "ui-icon-close" }
		});		
		
		$(".Torre").click(function() {
			var Grupo = $(this).attr("name");
			
			if ($(this).attr("checked"))
			{
				if ($("#LineasVida").attr("checked"))
					$("#LV"+Grupo).show();
				if ($("#Extintores").attr("checked"))
					$("#EX"+Grupo).show();
				if ($("#Descensor").attr("checked"))
					$("#DE"+Grupo).show();
			}
			else
				$("#LV"+Grupo+", #EX"+Grupo+", #DE"+Grupo).hide();
		});
		
		$(".ChkLV, .ChkEX, .ChkDE").click(function() {
			var Grupo = $(this).attr("name").substr(3,7);

			if ($(this).attr("checked"))
				$("#Ord"+Grupo).show();
			else
				$("#Ord"+Grupo).hide();
		});

		// Check Líneas de Vida
		$("#LineasVida").click(function() {
			if ($(this).attr("checked"))
				fProcesar("LV");
			else
				$(".gpoLV").hide();
		});
		// Check Extintores
		$("#Extintores").click(function() {
			if ($(this).attr("checked"))
				fProcesar("EX");
			else
				$(".gpoEX").hide();
		});
		// Check Descensores
		$("#Descensor").click(function() {
			if ($(this).attr("checked"))
				fProcesar("DE");
			else
				$(".gpoDE").hide();
		});

		// Al Hacer click en Asignar, hago el submit del Form
		$("#Assign").click(function(event) {
			event.preventDefault();
			
			if (fValidarForm())
				$("#Control").submit();
		});
		
		$("#Cerrar").click(function() {
			$("#Tipo").trigger("change");
		});
	});
	
	// Función que habilita el grupo sólo en el caso que la torre esté chequeda
	function fProcesar(Grupo)
	{
		$(".Torre").each(function() {
			var sNum = $(this).attr("name").substr(3,5);

			if ($(this).attr("checked"))
				$("#"+Grupo+"AEG"+sNum+", #Ord"+Grupo+sNum).show();
			$("input[name$=Chk"+Grupo+sNum+"]").attr("checked",$(this).attr("checked"));
		});
	}
</script>';

echo $Html;
if ($conn)
	mysql_close($conn);
?>