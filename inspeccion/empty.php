<?php
header("Content-Type:text/html; charset=UTF-8");
session_start();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
 		<meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  		<title>:: Inspección de instalaciones técnicas ::</title>
		<!--[if lt IE 9]>
		<script type="text/javascript" src="js/jquery/html5shiv.js"></script>
		<![endif]-->
    	<script src="js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				document.cookie = 'xscreen=' + window.parent.$("#WindowDatos").innerWidth();
				document.cookie = 'yscreen=' + window.parent.$("#WindowDatos").innerHeight();
				
				$(".message").fadeIn("slow").delay(1000).fadeOut(2000);
			});
		</script>
        <style>
			.message {font-family:'Helvetica Neue',Arial,Verdana,sans-serif; font-size:15px; font-weight:bold; color:#F00; line-height:20px; }
		</style>
	</HEAD>
	<BODY style="padding:0px; margin:0px;">
      <div align="center">
        <span class="message"><?php echo (isset($_SESSION["ERROR"])) ? $_SESSION["ERROR"] : "";?></span>
      </div>
	</BODY>
</HTML>