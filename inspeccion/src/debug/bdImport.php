<?php
header("Content-Type:text/html; charset=UTF-8");
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

if ($_REQUEST['Accion'] == "Lineas")
	$Fichero = "Lineas.csv";
else if ($_REQUEST['Accion'] == "Descensores")
	$Fichero = "Descensores.csv";
else if ($_REQUEST['Accion'] == "Extintores")
 	$Fichero = "Extintores.csv";
else
	$Fichero = "";

if ($Fichero != "")
{
	set_time_limit(750);
	$Parque = $Torre = ""; $IdParque = $IdLinea = 0;
	if ($Fichero == "Lineas.csv")
	{   // Inicializamos Tablas
		if (mysql_query("DELETE FROM Alturas", $conn))
			mysql_query("INSERT INTO Alturas SET Id='1', Nombre=''", $conn);
		mysql_query("DELETE FROM Parques", $conn);
		mysql_query("DELETE FROM Lineas", $conn);
		mysql_query("DELETE FROM LineasPletina", $conn);
	}
	else if ($Fichero == "Descensores.csv")
		mysql_query("DELETE FROM LineasDescensor", $conn);
	else
		mysql_query("DELETE FROM LineasExtintor", $conn);
				
	if (($file = fopen("../../data/".$Fichero, "r")))
	{
		while(!feof($file))
		{
			if (($Linea = fgets($file)) != "")
			{   // Optenemos todos los Campos a importar y elimianos los controles de ESC...							
				$Campo = explode(';', fLimpiaTxt($Linea));
				for ($Pos = 0; $Pos < count($Campo); $Pos ++) {
					if (($Campo[$Pos] = utf8_encode(trim($Campo[$Pos]))) == '-')
						$Campo[$Pos] = '';
				}
				
				// Comprobamos el Parque
				if ($Campo[0] !== $Parque)
				{
					$Torre = "";
					if ($Fichero == "Lineas.csv")
						$NomPais = $Campo[11];
					else if ($Fichero == "Descensores.csv")
						$NomPais = $Campo[7];
					else
						$NomPais = $Campo[8];

					if ($NomPais == "REP. DOMINICANA")
						$NomPais = "REPUBLICA DOMINICANA";
					else if ($NomPais == "REINO UNIDO")
						$NomPais = "INGLATERRA";
					else if ($NomPais == "POLONA")
						$NomPais = "POLONIA";

					if (!($IdParque = fBuscaDato("Id","Parques","Nombre='".($Parque = $Campo[0])."'",$conn)))
					{   // Sino existe, lo damos de Alta
						if (!($Pais = fBuscaDato("Id","Paises","Nombre='".$NomPais."'",$conn)))
						{   // Sino Existe el Pais, lo damos de alta.
							mysql_query("INSERT INTO Paises SET Nombre='".$NomPais."'", $conn);
							$Pais = mysql_insert_id();
						}

						$Query = "INSERT INTO Parques SET Nombre='".$Parque."', Cliente='GAMESA', Pais='".$Pais."'";
						if (mysql_query($Query, $conn))
							$IdParque = mysql_insert_id();
						
						unset ($Consulta, $NomPais, $Query);
					}
				}

				if ($Fichero == "Lineas.csv")
				{   // Líneas
					if ($Campo[4] !== $Torre)
					{
						if (!($TipoAEG = fBuscaDato("Id","TAerogenerador","Nombre='".$Campo[3]."'",$conn)))
						{
							mysql_query("INSERT INTO TAerogenerador SET Nombre='".$Campo[3]."', Prefijo='".$Campo[1]."'", $conn);
							$TipoAEG = mysql_insert_id();
						}
						if (!($Altura = fBuscaDato("Id","Alturas","Nombre='".$Campo[2]."'",$conn)))
						{
							mysql_query("INSERT INTO Alturas SET Nombre='".$Campo[2]."'", $conn);
							$Altura = mysql_insert_id();
						}
	
						$Query = "INSERT INTO Lineas SET IdParque='".$IdParque."', NumeroTorre='".fNumTorre(($Torre = $Campo[4]), true)."', 
							TipoAerogenerador='".$TipoAEG."', IdAltura='".$Altura."', TipoAerogeneradorGAMESA='".$Campo[1]." ".$Campo[2]."'";

						if (mysql_query($Query, $conn))
							$IdLinea = mysql_insert_id();
					}
			
					// LineasPletina, Doy por hecho que ya se han dado de Alta los AEG's y sus Líneas de Vida
					$IdPletina = fBuscaDato("TAP.IdPletina",
						"TAeroPletinas TAP JOIN Pletinas P ON P.Id=TAP.IdPletina",
						"TAP.IdTipoAEG=".$TipoAEG." AND P.Tipo=".(($Campo[5]=="NACELLE") ? "1" : "2"),$conn);
					$Query = "INSERT INTO LineasPletina SET IdLinea='".$IdLinea."', IdPletina='".$IdPletina."', 
						NumeroSerie='".substr($Campo[6],0,40)."', NumeroCable='".substr($Campo[10],0,25)."',
						NTrompa='".substr($Campo[7],0,25)."', NAbsorbedor='".substr($Campo[8],0,25)."', NTramo='".substr($Campo[9],0,25)."'";
					mysql_query($Query, $conn);
				}
				else if ($Fichero == "Descensores.csv")
				{   // Descensores
					if ($Campo[1] !== $Torre)
					{   // Buscamos a qué torre pertenece el Descensor						
						if (!($IdLinea = fBuscaDato("Id","Lineas","IdParque=".$IdParque." AND NumeroTorre='".fNumTorre(($Torre = $Campo[1]), true)."'",$conn)))
						{
							if (!($TipoAEG = fBuscaDato("Id","TAerogenerador","Prefijo='".$Campo[2]."'",$conn)))
							{
								mysql_query("INSERT INTO TAerogenerador SET Nombre='".$Campo[2]."', Prefijo='".$Campo[2]."'", $conn);
								$TipoAEG = mysql_insert_id();
							}
							if (!($Altura = fBuscaDato("Id","Alturas","Nombre='".$Campo[3]."'",$conn)))
							{
								mysql_query("INSERT INTO Alturas SET Nombre='".$Campo[3]."'", $conn);
								$Altura = mysql_insert_id();
							}

							$Query = "INSERT INTO Lineas SET IdParque='".$IdParque."', NumeroTorre='".fNumTorre($Torre, true)."', 
								TipoAerogenerador='".$TipoAEG."', IdAltura='".$Altura."', TipoAerogeneradorGAMESA='".$Campo[2]." ".$Campo[3]."'";
							if (mysql_query($Query, $conn))
								$IdLinea = mysql_insert_id();

							unset ($Query, $TipoAEG, $Altura);
						}
					}
						
					// Fabricante
					if (empty($Campo[5]))
						$Campo[5] = "PSA";
					if (!($Marca = fBuscaDato("Id","MarcaDes","Nombre='".$Campo[5]."'",$conn)))
					{
						mysql_query("INSERT INTO MarcaDes SET Nombre='".$Campo[5]."'", $conn);
						$Marca = mysql_insert_id();
					}
					// Modelo
					if (empty($Campo[6]))
						$Campo[6] = "AG 10 KT";
					if (!($Modelo = fBuscaDato("Id","ModeloDes","Nombre='".$Campo[6]."'",$conn)))
					{
						mysql_query("INSERT INTO ModeloDes SET Nombre='".$Campo[6]."', IdMarca=".$Marca, $conn);
						$Modelo = mysql_insert_id();
					}
											
					$Query = "INSERT INTO LineasDescensor SET IdLinea='".$IdLinea."', 
						NSerie='".substr($Campo[4],0,40)."', Marca='".$Marca."', Modelo='".$Modelo."'";
					mysql_query($Query, $conn);
					
					unset ($Query, $Marca, $Modelo);
				}
				else	// Extintores
				{
					if ($Campo[2] === "GROUND")
						$Campo[2] = "Ground";
					else if ($Campo[2] === "NACELLE")
						$Campo[2] = "Nacelle";
					else if ($Campo[2] === "SUBESTACIÓN")
						$Campo[2] = "SubEstacion";

					if ($Campo[1] !== $Torre)
					{   // Buscamos a qué torre pertenece el Extintor
						if (!($IdLinea = fBuscaDato("Id","Lineas","IdParque=".$IdParque." AND NumeroTorre='".($Tmp = fNumTorre(($Torre = $Campo[1]),true))."'",$conn)))
						{
							$Query = "INSERT INTO Lineas SET IdParque='".$IdParque."', NumeroTorre='".$Tmp."',
								TipoAerogenerador='".(($Campo[2] == "SubEstacion") ? "19" : "1")."', IdAltura='1'";
							if (mysql_query($Query, $conn))
								$IdLinea = mysql_insert_id();
						}
					}
					
					// Localización
					if (!($Localizacion = fBuscaDato("Id","Localizacion","Nombre='".$Campo[2]."'", $conn)))
					{
						mysql_query("INSERT INTO Localizacion SET Nombre='".$Campo[2]."'", $conn);
						$Localizacion = mysql_insert_id();
					}
					// Marca
					if (empty($Campo[4]) || $Campo[4] === 'NO CONSTA')
						$Campo[4] = "Seleccione ...";
					else if ($Campo[4] === "BILI 5")
						$Campo[4] = "BILI";
					if (!($Marca = fBuscaDato("Id","MarcaExt","Nombre='".$Campo[4]."'",$conn)))
					{
						mysql_query("INSERT INTO MarcaExt SET Nombre='".$Campo[4]."'", $conn);
						$Marca = mysql_insert_id();
					}
					// Modelo
					if (empty($Campo[5]))
						$Campo[5] = "89B";
					if (!($Modelo = fBuscaDato("Id","ModeloExt","Nombre='".$Campo[5]."'", $conn)))
					{
						mysql_query("INSERT INTO ModeloExt SET Nombre='".$Campo[5]."'", $conn);
						$Modelo = mysql_insert_id();
					}

					$Query = "INSERT INTO LineasExtintor SET IdLinea='".$IdLinea."', Localizacion='".$Localizacion."', Marca='".$Marca."',
						Modelo='".$Modelo."', NPlaca='".substr($Campo[3],0,40)."', FechaFabricacion='".$Campo[6]."', FechaRetimbrado='".$Campo[7]."', 
						AgenteExtintor='CO2'";
					mysql_query($Query, $conn);
					
					unset ($Query, $Marca, $Modelo);
				}
			}
		}
		
		fclose($file);
	}
}

if ($conn)
	mysql_close($conn);
?>