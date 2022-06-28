<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("db-config.php");
session_start();
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
		<script src="js/inc/functions.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				// Cálculamos el tamaño del iframe para que se ajuste al area
				var user = "<?php echo (isset($_COOKIE["usuario"]))?$_COOKIE["usuario"]:"";?>";
				var alto_iframe = $(window).height() - ($("#menu").height() + 50);
				$("#Area_Frames, #WindowDatos").css('height', alto_iframe);
				document.cookie = 'xscreen=' + $(window).width();
				document.cookie = 'yscreen=' + alto_iframe;
				
				$("#login,#main, .message").hide();
				if (user != "") {
					$("#main").show();
					fActMenu("<?php echo (isset($_SESSION["ROL"]))?$_SESSION["ROL"]:1;?>");
				} else {
					$("#login").show();
					$("#uname").focus();
				}
												
				$("#frmlogin").submit(function() {
					$.ajax({
						type: "POST", url: "ajax/principal/login.php",
						data: {<?php echo (!isset($_GET["login"]))?"uname":"login";?>:$('#uname').val(), pword:$('#pword').val()},
						success: function(sData) {
							if ($.isNumeric(sData))
							{
								document.cookie = 'usuario=' + $("#uname").val();
								fActMenu(sData);
								$("#login").hide();
								$("#main").show();
							} else {
								$(".message").html("<p>" + sData + "</p>").fadeIn("slow").delay(1000).fadeOut(1000);
							}
						},
						dataType:"text", async:false
					});
					return false;
				});
				
				$(".unlogged").bind("click", function() {
					$.ajax({
						type: "POST", url: "ajax/principal/unlogin.php",
						success: function() {
							$("#WindowDatos").attr('src', 'empty.php');
							$("#main").hide(); $("#login").show();
							$("#uname").val("").focus();
							$("#pword").val("");
							
							document.cookie = 'usuario=;expires=Thu, 01-Jan-1970 00:00:01 GMT';
							document.cookie = 'xscreen=;expires=Thu, 01-Jan-1970 00:00:01 GMT';
							document.cookie = 'yscreen=;expires=Thu, 01-Jan-1970 00:00:01 GMT';
						},
						dataType:"text", async:false
					});
				});
				
				$(function() {
					$("#btnlogin").button();
				});
				
				function fActMenu(nivel)
				{
					$(".usuAdmin, .usuAvanzado, .usuInspector").hide();
					if (nivel == '1')
						$(".usuInspector").show();
					else if (nivel == '2')
						$(".UsuAvanzado").show();
					else
						$(".usuAdmin, .UsuAvanzado").show();
				}
			});
		</script>
		<style>
			a:link img, a:hover img, a:visited img, a:active img {border:none;} 
			a, ul, ul li {border:none;}
			h1 {font-size:1.2em;margin:.6em 0;}
			.ui-dialog .ui-state-error {padding:.3em;}
			.message {font-family:'Helvetica Neue',Arial,Verdana,sans-serif;font-size:13px;font-weight:bold;color:#ff0000;line-height:20px;text-align:center;}
			
			div#menu {
			  top:0px;left:0px;width:100%;margin-top:30px;
			  background:transparent url(css/navigator/header_bg.gif) repeat-x 0 0;
			}
		</style>
		<link type="text/css" href="css/login.css" rel="stylesheet" />
		<link type="text/css" href="css/menu.css" rel="stylesheet" />
		<script type="text/javascript" src="js/menu/menu.js"></script>
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
						<form action="" id="frmlogin" method="post" enctype="application/x-www-form-urlencoded">
							<label>Usuario</label>
                            <?php if (!isset($_GET["login"])) { ?>
                            <select id="uname" name="uname">
                              	<option value="0">Seleccione un Usuario...</option>
								<?php
								if (($result = mysql_query("SELECT Id, Nombre FROM Trabajadores ORDER BY Nombre ASC;",$conn)))
								{
									while ($row = mysql_fetch_row($result))
										echo "<option value='".$row[0]."'>".$row[1]."</option>";
								}
								?>
							</select>
                            <?php } else { ?>
                            <input id="uname" name="uname" type="text" maxlength="8"/>
                            <?php } ?>
							<label>Contraseña</label>
							<input id="pword" name="pword" type="password" maxlength="15"/>
							<button id="btnlogin" class="buttongrey" type="submit">Acceder</button>
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
		<div id="main" style="background:#fafeff url(img/bg_main.jpg) top left no-repeat;overflow:auto;cursor:pointer;">
			<?php include "inc/principal/menu.php";?>
			<div id="Area_Frames" style="background-color:transparent;padding-left:0px;padding-top:15px;width:100%;overflow:hidden;cursor:default;">
				<iframe allowtransparency="allowtransparency" id="WindowDatos" name="WindowDatos" src="<?php echo (isset($_SESSION["mi_url"]))?$_SESSION["mi_url"]:"empty.php";?>" width="100%" height="100%" frameborder="0"></iframe>
			</div>
		</div>
		<!-- MODALES -->
		<div id="ModalHis" style="display:none;"></div>
		<div id="ModalPdf" style="display:none;"></div>
        <!-- ALERTA -->
		<div id="AlertHistorico" title="Atención" style="display:none;">
			<p>Debe seleccionar un <b>PARQUE</b> para poder visualizar su histórico.</p>
		</div>
		<div id="PreciosUpdate" style="display:none;">Se han modificado los precios</div>
	</BODY>
</HTML>
<?php
if ($conn)
	mysql_close($conn);
?>