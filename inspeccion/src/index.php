<?php
header("Content-Type: text/html; charset=UTF-8"); 
session_start(); $Texto = "";
if (isset($_POST["txtusername"]) && isset($_POST["txtpassword"]))
{   // Comprobamos al Usuario para la pasarela, si es correcto lo enviamos a la pantalla de Inspecciones...
	if ($_POST["txtusername"] == "Abantos Vertical" && $_POST["txtpassword"] == "Abantos2014")
		header("Location:aplicacion.php");
	else
		$Texto = "Login incorrecto!!";
}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
  		<meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  		<title>:: Inspección de instalaciones técnicas ::</title>
        <link rel="stylesheet" type="text/css" media="screen" href="themes/lightness/jq.ui-1.8.22.custom.css" />
		<link rel="stylesheet" type="text/css" href="css/general.css" />
		<!--[if lt IE 9]>
		<script type="text/javascript" src="js/jquery/html5shiv.js"></script>
		<![endif]-->
 		<script src="js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
        <script src="js/jquery/jq.ui-1.9.2.custom.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$(function() {
					$("#btnlogin").button();
				});
				
				$(".message").html("<p><?php echo $Texto;?></p>").fadeIn("slow").delay(1000).fadeOut(1000);
				$("#txtusername").focus();
			});
		</script>
		<style>
			a:link img, a:hover img, a:visited img, a:active img{border:none;} 
			a {border:none;}
			ul {border:none;}
			ul li {border:none;}
			h1 {font-size:1.2em;margin:.6em 0;}
			.message {font-family:'Helvetica Neue',Arial,Verdana,sans-serif;font-size:13px;font-weight:bold;color:#ff0000;line-height:20px;text-align:center;}
		</style>
		<link type="text/css" href="css/login.css" rel="stylesheet" />
	</HEAD>
    <BODY style="padding:0px; margin:0px;">
		<div id="login">
			<div style="width:675px;margin-left:25%;text-align:center;">
				<div id="imglogin"></div>
				<div id="wrapper">
					<div class="header">
						<h2>Acceso a Inspecciones</h2>
					</div>
					<div class="content">
						<form action="index.php" id="frmlogin" method="post" enctype="application/x-www-form-urlencoded">
							<label>Usuario</label>
                            <input id="txtusername" name="txtusername" type="text" maxlength="20"/>
							<label>Contraseña</label>
							<input id="txtpassword" name="txtpassword" type="password" maxlength="15"/>
							<button type="submit" id="btnlogin" class="buttongrey">Acceder</button>
						</form>
					</div>
					<div class="message"></div>
				</div>
			</div>
			<div id="footer_loogin">
			  <p id="pie_login">
                <span style="height:90px!important;line-height:44px;vertical-align:bottom;padding-right:15px;">&copy;<b>MorellConsultor.es</b></span>
			  </p>
			</div>
		</div>
	</BODY>
</HTML>