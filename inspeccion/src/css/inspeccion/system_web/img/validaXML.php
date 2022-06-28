<?php
	$Path = "../../data/"; $Error = "";
	$Archivo = isset($_REQUEST['File']) ? $_REQUEST['File'] : "";
	if (file_exists($Path.$Archivo))
	{
		$File = substr($Archivo, 0, 21)."1.xml";
		if (!rename($Path.$Archivo, $Path.$File))
			$Error = "Error, no se puede pasar a Validar";
	}
	else
		$Error = "Error, no existe el fichero ($Archivo)";
	echo $Error;
?>