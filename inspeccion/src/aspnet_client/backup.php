<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../inc/function/fCtrlAcceso.php");

if ($_REQUEST['param']==1)
{
	require_once("../db-config.php");
	$drop = true;
	$compresion = false; 		// Puede ser "gz", "bz2", o false

	/* Se busca las tablas en la base de datos */
	$tablas = false;
	if (($Result = mysql_query("SHOW TABLES FROM $db;",$conn)))
	{
		while ($fila = mysql_fetch_row($Result, MYSQL_NUM))
			$tablas[] = $fila[0];
	}

	/* Se crea la cabecera del archivo */ 
	$info['version'] = "1.0.1";
	$info['fecha'] = date("d-m-Y");
	$info['hora'] = date("h:m:s A");
	$info['mysqlver'] = mysql_get_server_info();
	$info['phpver'] = phpversion();
	ob_start();
		print_r($tablas);
		$representacion = ob_get_contents();
	ob_end_clean();
	preg_match_all('/(\[\d+\] => .*)\n/', $representacion, $matches);
	$info['tablas'] = implode("; ", $matches[1]);

	$dump = <<<TEXTO
# +===================================================================
# | Backup! {$info['version']}
# | Generado el {$info['fecha']} a las {$info['hora']}
# | Servidor: {$_SERVER['HTTP_HOST']}
# | MySQL Version: {$info['mysqlver']}
# | PHP Version: {$info['phpver']}
# | Base de datos: '$db'
# | Tablas: {$info['tablas']}
# +-------------------------------------------------------------------\n
TEXTO;

	foreach ($tablas as $Tabla)
	{   /* Se halla el query que será capaz vaciar la tabla. */
		if ($drop)
			$drop_table_query = "DROP TABLE IF EXISTS `$Tabla`;";

		/* Se halla el query que será capaz de recrear la estructura de la tabla. */
   		$create_table_query = "";
		if (($Result = mysql_query("SHOW CREATE TABLE `$Tabla`;",$conn)))
		{
			while ($fila = mysql_fetch_array($Result, MYSQL_NUM))
				$create_table_query = $fila[1].";";
		}

		/* Se halla el query que será capaz de insertar los datos. */
		$insert_into_query = "";
		if (($Result = mysql_query("SELECT * FROM `$Tabla`;", $conn)))
		{
			if (mysql_num_rows($Result) > 0)
			{
				$Tmp = "INSERT INTO `$Tabla` VALUES ";
				while ($fila = mysql_fetch_array($Result, MYSQL_ASSOC))
				{
					foreach (array_keys($fila) as $columna)
					{
						if (gettype($fila[$columna]) == "NULL")
							$values[] = "NULL";
						else
							$values[] = "'".mysql_real_escape_string($fila[$columna])."'";
					}
				
					$Tmp .= "(".implode(", ", $values)."),\n";	// Ojo, al fichero va con UTF8
					unset($values);
				}
		
				// Pongo el ";" en el último Insert
				$insert_into_query = substr($Tmp, 0, strlen($Tmp)-2).";";
			}
		}
	
		if (!empty($create_table_query))
		{
			$dump .= "# | Estructura de la tabla '$Tabla'\n";
			$dump .= "# +------------------------------------->\n";
			if (!empty($drop_table_query))
				$dump .= "$drop_table_query\n";
			$dump .= "$create_table_query\n";
		}
		if (!empty($insert_into_query))
		{
			$dump .= "# | Carga de datos de la tabla '$Tabla'\n";
			$dump .= "# +------------------------------------->\n";
			$dump .= "$insert_into_query\n";
		}
	}
	unset($Tabla);

	/* Envio */
	if (!headers_sent())
	{
    	header("Pragma: no-cache");
	    header("Expires: 0");
	    header("Content-Transfer-Encoding: binary");
			
		$File = "Backup".date("ymd");
	    switch ($compresion)
		{
	    	case "gz":
	   			header("Content-Disposition: attachment; filename=$File.gz");
		       	header("Content-type: application/x-gzip");
	    	    echo gzencode($dump, 9);
    	    	break;
		    case "bz2":
    		    header("Content-Disposition: attachment; filename=$File.bz2");
	    	    header("Content-type: application/x-bzip2");
    	    	echo bzcompress($dump, 9);
	        	break;
		    default:
    		    header("Content-Disposition: attachment; filename=$File.sql");
        		header("Content-type: application/force-download");
		        echo $dump;
				break;
		}
	}

	if ($conn)
		mysql_close($conn);
}
else
{
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
  		<meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  		<title>:: Inspección de instalaciones técnicas ::</title>
        <link rel="stylesheet" type="text/css" media="screen" href="../themes/lightness/jq.ui-1.8.22.custom.css" />
		<link rel="stylesheet" type="text/css" href="../css/general.css"/>
		<!--[if lt IE 9]>
		<script type="text/javascript" src="../js/jquery/html5shiv.js"></script>
		<![endif]-->
		<script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
        <script src="../js/jquery/jq.AjaxUpload-min.js" language="javascript"></script>
     	<script src="../js/inc/functions.js" type="text/javascript"></script>
	    <script src="../js/jAlerts/jq.Alerts.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="../js/jAlerts/jq.Alerts.css" />
		<script type="text/javascript">
			// * Iniciar Formulario *
			$(document).ready(function() {
				$("#bUpload").button({
					icons: {primary:"ui-icon-image"}
				});

				new AjaxUpload("#bUpload", {
       				action: "../ajax/principal/upload.php",
					onSubmit : function(file , ext) {
						if (!(ext && /^(gz|bz2|sql)$/.test(ext)))
						{   // Extensiones permitidas
							jAlert("Archivo no permitido", null);
							return false;		// Cancela upload
						} else
							this.disable();
					},
					onComplete: function(file, response) {
						$("#Fichero").val(response);
						$("#Accion").html("<p>Restaurando Copia de Seguridad...</p>");

						$.ajax({
							type: "POST", url:"../ajax/principal/restaurar.php",
							data: {"Fichero":$("#Fichero").val()},
							success: function(sData) {
								$("#Accion").html(sData);
							},
							dataType: "text", async:false
						});						
						this.enable();	// Enable upload button
					}
				});
			});
		</script>
		<style>
			span {
				color:#29384C;
				font-family:Segoe UI, Calibri, Helvetica, Arial, sans-serif;
				font-size:12px;
				font-weight:bold;
				line-height:20px;
				margin-right:0px;
			}
		</style>
	</HEAD>
	<BODY>
      <div align="center">
      	<input id="Fichero" type="text" size="20" maxlength="20" />
		<div id="bUpload"><span>Buscar</span></div>
        <br/>
        <div id="Accion"></div>
      </div>
	</BODY>
</HTML>
<?php
}
?>