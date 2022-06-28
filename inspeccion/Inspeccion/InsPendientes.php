<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/funcs.php");
require_once("../inc/function/fCtrlAcceso.php");
require_once("../db-config.php");
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
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
		});
	</script>
	<style>
		.titulo{font-family: Calibri, Arial, sans-serif; font-size: 22px; color:#000099; font-weight:bold;}
	</style>
  </head>
  <body>
  	<div align=center style="margin-top:5px;padding-bottom:5px;">
        <div id="Lista" align=center style="padding-top:5px;">
	        <TABLE class="tablanaranja" cellpadding=0 cellspacing=0>
            <?php
			$CabeceraImpresa = false; $Html = "";
			$directorio = dir("../data/");
			while ($Archivo = $directorio->read())
			{   // Seleccionamos sólo los archivos Pendientes
				if (substr($Archivo, 21, 1) === "0")
				{	// Obtengo el Parque y los Trabajadores
					$doc = new DOMDocument();
					$doc->load("../data/".$Archivo);
			
					$NombreParque = $Operario1 = $Operario2 = "";
					foreach($doc->getElementsByTagName("Parque") as $parque)
					{
						if (is_object($aux = $parque->getElementsByTagName("Nombre")->item(0)))
							$NombreParque = $aux->nodeValue;
						if (is_object($aux = $parque->getElementsByTagName("Operario1")->item(0)))
							$Operario1 = $aux->nodeValue;
						if (is_object($aux = $parque->getElementsByTagName("Operario2")->item(0)))
							$Operario2 = $aux->nodeValue;
					}

					// Mostramos los trabajos pendientes del Trabajador, ó si es Administrador 
					if (($Operario1 == $_COOKIE["usuario"] || 
						$Operario2 == $_COOKIE["usuario"]) || $_SESSION["ROL"] == 5)
					{
						if (!$CabeceraImpresa)
						{
							$Html .= '
								<tr>
									<th width=75><b>Inspección</b></th>
									<th width=300><b>Trabajador</b></th>
									<th width=175><b>Parque</b></th>
									<th width=50><b>Tablet</b></th>
									<th width=65>&nbsp;</th>
								</tr>';
							$CabeceraImpresa=true;
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

						$Tipo = substr($Archivo,0,1);
						$CodTablet = substr($Archivo, 16, 5);
						$Operarios = $Operario1.'<br/>'.$Operario2;
						$Html .= '
							<tr>
								<td>'.(($Tipo == 'M')?"Montaje":"Revisión").'</td>
								<td>'.$Operarios.'</td>
								<td>'.$NombreParque.'</td>
								<td align="center">'.fQuitaZeros($CodTablet).'</td>
								<td align="center" style="padding-top:2px;">
									<a href="EditarLCMyR.php?Editar='.$Archivo.'&Pendiente=1"><img src="../img/bt_edit.png" alt="Editar"></a>
								</td>
							</tr>';
					}
					
				}
			}
			$directorio->close();
			if (!$CabeceraImpresa)
    			$Html .= '<tr><td><b>No hay ningún trabajo pendiente</b></td></tr>';

			echo $Html;
			?>
            </TABLE>
        </div>
	</div>
  </body>
</html>
<?php
if ($conn)
	mysql_close($conn);
?>