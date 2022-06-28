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
	<script src="../js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
	<script src="../js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
	<script src="../js/jqgrid/i18n/grid.locale-es.js" type="text/javascript"></script>
	<script src="../js/jqgrid/jquery.jqGrid.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
			document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
			
			$("#Marca").change(function() {
				if ($(this).val() != "")
				{
					$.ajax({
						type: "POST", url:"modDescensor_1.php",
						data: {"Marca":$("#Marca").val()},
						success: function(sData) {
							$("#Lista").html(sData);
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
  <div align="center">
	<select id="Marca" style="width:275px;">
    	<option value=''>Seleccione...</option>
	    <?php
		if (($result = mysql_query("SELECT * FROM MarcaDes",$conn)))
		{
			while($row = mysql_fetch_row($result))
				echo "<option value='".$row[0]."'>".$row[1]."</option>";
		}
		unset ($result, $row);
		?>
	</select>  
  	<div id="Lista" style="margin-top:10px;"></div>
  </div>
</body>
</html>
<?php
if ($conn)
	mysql_close($conn);
?>