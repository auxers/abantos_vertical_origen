<?php
$Path = "../../data/"; $Html = "";
if (file_exists($Path.($Archivo = isset($_REQUEST['File']) ? $_REQUEST['File'] : "")))
{
	if (file_exists(($File = $Path.$Archivo)))
	{
		$doc = new DOMDocument();
		$doc->load($File);

		// PSA
		$Bolsa = $Sellado = $NumeroSello = $DescensorAG = $CaboAnclaje = $Humedad = $EtiquetaLegible = $EstadoCarcasa = 
		$CuerdaEntrada = $CuerdaSalida = $MosquetonArgolla = $NecesarioAbrir = $RuedaDentada = $Dientes = $PoleaCuerda = $SuperficiePolea = $CajaFreno = 
		$UnidadFreno = $ProfundidadFreno = $GuarnicionFreno = $ZapatasFreno = $ControlMuelle = $FlancosArbol = $PuntosApoyo = $EstadoCuerda = $FinDeCuerda = 
		$Termoretractil = $EstMosquetonPri = $EstCuerdaSeguridad = $MosquetonSeguridad = $NSerieSeguridad = $AnyoFabCuerdaSeg = $DeslizamientoCuerda = 
		$CargaMinima = $VainaCuerda = $Mordazas = 1;
		// MITTELMANN
		$SisEnvaseEqu = 2; $MaletinEqu = $SacaAzulTran = $BolsaPlastico = $DescensorMRG = $CuerdaDesMRG = 1;
		$CuerdasSeguridad = $PegatinaPrecinto = $BridaPrecinto = $EtiquetaExterior = $LibroInspecciones = 1;
		$SisEnvaseEmb = 2; $MaletinEmb = $BolsaPlasEmb = $SacaAzulEmb = $PegatinaPreEmb = $BridaPreEmb = $PreTornillTFreno = 1;
		$GrosorPasTFreno = $EstMuelleTFreno = $EjePinonTFreno = $LimPinonTFreno = $ZonaSurcosTFreno = $ZonaLimpiaTFreno = 1;
		$EstTornillTFreno = $TorLoctiteTFreno = $MarcasTornTFreno = $PreTornillTPolea = $HolguraEjeTPolea = $EstNerviosTPolea = 1;
		$EstCarcasaTPolea = $EstTornillTPolea = $TorLoctiteTPolea = $MarcasTornTPolea = $PreTornillCFreno = $EstJuntaCFreno = 1;
		$EstDientesCFreno = $RuedaLimpiaCFreno = $EstTornillCFreno = $TorLoctiteCFreno = $MarcasTornCFreno = 1;
		$EstGenCuerdaPri = $EstProCuerdaPri = $LongitudCuerdaPri = $LongMedidaCuerdaPri = $MosquetonCuerdaPri = 1;
		$EstGenCuerdaSeg = $SupSacaCuerdaSeg = $EstMosqueton = $FucMosqueton = 1; 
		$NSerieCuerdaSeg1 = $AnyoFabCuerdaSeg1 = $NSerieCuerdaSeg2 = $AnyoFabCuerdaSeg2 = "";

		$Linea = isset($_REQUEST['Linea'])?$_REQUEST['Linea']:0;
		$Fabricante = isset($_REQUEST['Marca'])?(is_numeric($_REQUEST['Marca'])?$_REQUEST['Marca']:1):1;
		foreach($doc->getElementsByTagName("Torre") as $torre)
		{
			$Id = 0;
			if (is_object($aux = $torre->getElementsByTagName("Id")->item(0)))
				$Id = $aux->nodeValue;
			
			if ($Id == $Linea)
			{   // Descensores
				foreach($torre->getElementsByTagName("Descensor") as $descensor)
				{
					if ($Fabricante == 2)	// MITTELMANN
					{
						if (is_object($aux = $descensor->getElementsByTagName("MaletinEqu")->item(0)))
							$MaletinEqu = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SisEnvaseEqu")->item(0)))
							$SisEnvaseEqu = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SacaAzulTran")->item(0)))
							$SacaAzulTran = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BolsaPlastico")->item(0)))
							$BolsaPlastico = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("DescensorMRG")->item(0)))
							$DescensorMRG = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdaDesMRG")->item(0)))
							$CuerdaDesMRG = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdasSeguridad")->item(0)))
							$CuerdasSeguridad = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PegatinaPrecinto")->item(0)))
							$PegatinaPrecinto = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BridaPrecinto")->item(0)))
							$BridaPrecinto = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EtiquetaExterior")->item(0)))
							$EtiquetaExterior = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LibroInspecciones")->item(0)))
							$LibroInspecciones = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MaletinEmb")->item(0)))
							$MaletinEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SisEnvaseEmb")->item(0)))
							$SisEnvaseEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BolsaPlasEmb")->item(0)))
							$BolsaPlasEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SacaAzulEmb")->item(0)))
							$SacaAzulEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PegatinaPreEmb")->item(0)))
							$PegatinaPreEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("BridaPreEmb")->item(0)))
							$BridaPreEmb = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PreTornillTFreno")->item(0)))
							$PreTornillTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("GrosorPasTFreno")->item(0)))
							$GrosorPasTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstMuelleTFreno")->item(0)))
							$EstMuelleTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EjePinonTFreno")->item(0)))
							$EjePinonTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LimPinonTFreno")->item(0)))
							$LimPinonTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ZonaSurcosTFreno")->item(0)))
							$ZonaSurcosTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ZonaLimpiaTFreno")->item(0)))
							$ZonaLimpiaTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstTornillTFreno")->item(0)))
							$EstTornillTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("TorLoctiteTFreno")->item(0)))
							$TorLoctiteTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MarcasTornTFreno")->item(0)))
							$MarcasTornTFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PreTornillTPolea")->item(0)))
							$PreTornillTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("HolguraEjeTPolea")->item(0)))
							$HolguraEjeTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstNerviosTPolea")->item(0)))
							$EstNerviosTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstCarcasaTPolea")->item(0)))
							$EstCarcasaTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstTornillTPolea")->item(0)))
							$EstTornillTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("TorLoctiteTPolea")->item(0)))
							$TorLoctiteTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MarcasTornTPolea")->item(0)))
							$MarcasTornTPolea = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PreTornillCFreno")->item(0)))
							$PreTornillCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstJuntaCFreno")->item(0)))
							$EstJuntaCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstDientesCFreno")->item(0)))
							$EstDientesCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("RuedaLimpiaCFreno")->item(0)))
							$RuedaLimpiaCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstTornillCFreno")->item(0)))
							$EstTornillCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("TorLoctiteCFreno")->item(0)))
							$TorLoctiteCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MarcasTornCFreno")->item(0)))
							$MarcasTornCFreno = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("DeslizamientoCuerda")->item(0)))
							$DeslizamientoCuerda = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;							
						if (is_object($aux = $descensor->getElementsByTagName("EstGenCuerdaPri")->item(0)))
							$EstGenCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstProCuerdaPri")->item(0)))
							$EstProCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LongitudCuerdaPri")->item(0)))
							$LongitudCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("LongMedidaCuerdaPri")->item(0)))
							$LongMedidaCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MosquetonCuerdaPri")->item(0)))
							$MosquetonCuerdaPri = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaPri")->item(0)))
							$AnyoFabCuerdaPri = addslashes($aux->nodeValue);	// AnyoCuerdaPri
						if (is_object($aux = $descensor->getElementsByTagName("EstGenCuerdaSeg")->item(0)))
							$EstGenCuerdaSeg = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SupSacaCuerdaSeg")->item(0)))
							$SupSacaCuerdaSeg = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstMosqueton")->item(0)))
							$EstMosqueton = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("FucMosqueton")->item(0)))
							$FucMosqueton = (is_numeric($aux->nodeValue)) ? $aux->nodeValue : 0;

						if (is_object($aux = $descensor->getElementsByTagName("NSerieCuerdaSeg1")->item(0)))
							$NSerieCuerdaSeg1 = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg1")->item(0)))
							$AnyoFabCuerdaSeg1 = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("NSerieCuerdaSeg2")->item(0)))
							$NSerieCuerdaSeg2 = addslashes($aux->nodeValue);
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg2")->item(0)))
							$AnyoFabCuerdaSeg2 = addslashes($aux->nodeValue);
					}
					else 	// Por Defecto, siempre PSA
					{
						if (is_object($aux = $descensor->getElementsByTagName("Bolsa")->item(0)))
							$Bolsa = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Sellado")->item(0)))
							$Sellado = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("NumeroSello")->item(0)))
							$NumeroSello = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("DescensorAG")->item(0)))
							$DescensorAG = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CaboAnclaje")->item(0)))
							$CaboAnclaje = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Humedad")->item(0)))
							$Humedad = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EtiquetaLegible")->item(0)))
							$EtiquetaLegible = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstadoCarcasa")->item(0)))
							$EstadoCarcasa = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdaEntrada")->item(0)))
							$CuerdaEntrada = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CuerdaSalida")->item(0)))
							$CuerdaSalida = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MosquetonArgolla")->item(0)))
							$MosquetonArgolla = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					
						if (is_object($aux = $descensor->getElementsByTagName("NecesarioAbrir")->item(0)))
							$NecesarioAbrir = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("RuedaDentada")->item(0)))
							$RuedaDentada = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Dientes")->item(0)))
							$Dientes = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PoleaCuerda")->item(0)))
							$PoleaCuerda = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("SuperficiePolea")->item(0)))
							$SuperficiePolea = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CajaFreno")->item(0)))
							$CajaFreno = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("UnidadFreno")->item(0)))
							$UnidadFreno = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ProfundidadFreno")->item(0)))
							$ProfundidadFreno = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("GuarnicionFreno")->item(0)))
							$GuarnicionFreno = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ZapatasFreno")->item(0)))
							$ZapatasFreno = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("ControlMuelle")->item(0)))
							$ControlMuelle = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("FlancosArbol")->item(0)))
							$FlancosArbol = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("PuntosApoyo")->item(0)))
							$PuntosApoyo = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;

						if (is_object($aux = $descensor->getElementsByTagName("EstadoCuerda")->item(0)))
							$EstadoCuerda = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("FinDeCuerda")->item(0)))
							$FinDeCuerda = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Termoretractil")->item(0)))
							$Termoretractil = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaPri")->item(0)))
							$AnyoFabCuerdaPri = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("EstMosquetonPri")->item(0)))
							$EstMosquetonPri = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("EstCuerdaSeguridad")->item(0)))
							$EstCuerdaSeguridad = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("MosquetonSeguridad")->item(0)))
							$MosquetonSeguridad = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("NSerieSeguridad")->item(0)))
							$NSerieSeguridad = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("AnyoFabCuerdaSeg")->item(0)))
							$AnyoFabCuerdaSeg = $aux->nodeValue;
						if (is_object($aux = $descensor->getElementsByTagName("DeslizamientoCuerda")->item(0)))
							$DeslizamientoCuerda = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("CargaMinima")->item(0)))
							$CargaMinima = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("VainaCuerda")->item(0)))
							$VainaCuerda = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
						if (is_object($aux = $descensor->getElementsByTagName("Mordazas")->item(0)))
							$Mordazas = is_numeric($aux->nodeValue) ? $aux->nodeValue : 0;
					}					
				}
			}
		}
		
		// Genero el HTML con los Datos leídos sobre el Descensor
		?>
        <?php
		if ($Fabricante == 2)	// MITTELMANN
		{
		?>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="MaletinEqu">Maletín&nbsp;Metálico</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="MaletinEqu" SIZE=1>
										<OPTION <?php echo ($MaletinEqu==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($MaletinEqu==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($MaletinEqu==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;">
                                        <LABEL for="CuerdasSeguridad">Cuerdas&nbsp;de&nbsp;Seguridad&nbsp;de&nbsp;1-2m&nbsp;+&nbsp;1&nbsp;Mosquetón</LABEL>
                                      </td>
	                                  <td>
    	                              	<SELECT NAME="CuerdasSeguridad" SIZE=1>
										  <OPTION <?php echo ($CuerdasSeguridad==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($CuerdasSeguridad==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($CuerdasSeguridad==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="SisEnvaseEqu">Sistema&nbsp;de&nbsp;Envase&nbsp;y&nbsp;Lacrado</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="SisEnvaseEqu" SIZE=1>
										<OPTION <?php echo ($SisEnvaseEqu==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($SisEnvaseEqu==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($SisEnvaseEqu==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="PegatinaPrecinto">Pegatina&nbsp;de&nbsp;Precinto</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="PegatinaPrecinto" SIZE=1>
										  <OPTION <?php echo ($PegatinaPrecinto==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($PegatinaPrecinto==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($PegatinaPrecinto==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="SacaAzulTran">Saca&nbsp;Azúl&nbsp;de&nbsp;Transporte</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="SacaAzulTran" SIZE=1>
										<OPTION <?php echo ($SacaAzulTran==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($SacaAzulTran==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($SacaAzulTran==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="BridaPrecinto">Brida&nbsp;de&nbsp;Precinto</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="BridaPrecinto" SIZE=1>
										  <OPTION <?php echo ($BridaPrecinto==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($BridaPrecinto==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($BridaPrecinto==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>                            
							<tr>
								<td class="etiqueta_form">
									<LABEL for="BolsaPlastico">Bolsa&nbsp;de&nbsp;plástico</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="BolsaPlastico" SIZE=1>
										<OPTION <?php echo ($BolsaPlastico==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($BolsaPlastico==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($BolsaPlastico==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="EtiquetaExterior">Etiqueta&nbsp;Identificativa&nbsp;Exterior</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="EtiquetaExterior" SIZE=1>
										  <OPTION <?php echo ($EtiquetaExterior==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($EtiquetaExterior==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($EtiquetaExterior==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="DescensorMRG">Descensor&nbsp;MRG-9&nbsp;más&nbsp;1&nbsp;mosquetón</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="DescensorMRG" SIZE=1>
										<OPTION <?php echo ($DescensorMRG==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($DescensorMRG==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($DescensorMRG==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="LibroInspecciones">Libro&nbsp;de&nbsp;Inspecciones</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="LibroInspecciones" SIZE=1>
										  <OPTION <?php echo ($LibroInspecciones==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($LibroInspecciones==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($LibroInspecciones==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>                            
							<tr>
								<td class="etiqueta_form">
									<LABEL for="CuerdaDesMRG">Cuerda&nbsp;descensor&nbsp;más&nbsp;2&nbsp;mosquetones</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="CuerdaDesMRG" SIZE=1>
										<OPTION <?php echo ($CuerdaDesMRG==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($CuerdaDesMRG==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($CuerdaDesMRG==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                </td>
							</tr>                            
							<tr>
								<td class="etiqueta_form">
									<LABEL for="MaletinEmb">Maletín&nbsp;metálico&nbsp;en&nbsp;perfecto&nbsp;estado</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="MaletinEmb" SIZE=1>
										<OPTION <?php echo ($MaletinEmb==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($MaletinEmb==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($MaletinEmb==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="SacaAzulEmb">Saca&nbsp;azúl&nbsp;en&nbsp;perfecto&nbsp;estado</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="SacaAzulEmb" SIZE=1>
										  <OPTION <?php echo ($SacaAzulEmb==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($SacaAzulEmb==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($SacaAzulEmb==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="SisEnvaseEmb">Sistema&nbsp;de&nbsp;envase&nbsp;y&nbsp;lacrado&nbsp;perfecto&nbsp;estado</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="SisEnvaseEmb" SIZE=1>
										<OPTION <?php echo ($SisEnvaseEmb==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($SisEnvaseEmb==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($SisEnvaseEmb==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="PegatinaPreEmb">Colocada&nbsp;pegatina&nbsp;precinto</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="PegatinaPreEmb" SIZE=1>
										  <OPTION <?php echo ($PegatinaPreEmb==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($PegatinaPreEmb==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($PegatinaPreEmb==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="BolsaPlasEmb">Bolsa&nbsp;de&nbsp;plástico&nbsp;interior&nbsp;en&nbsp;perfecto&nbsp;estado</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="BolsaPlasEmb" SIZE=1>
										<OPTION <?php echo ($BolsaPlasEmb==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($BolsaPlasEmb==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($BolsaPlasEmb==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="BridaPreEmb">Colocada&nbsp;brida&nbsp;de&nbsp;precinto</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="BridaPreEmb" SIZE=1>
										  <OPTION <?php echo ($BridaPreEmb==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($BridaPreEmb==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($BridaPreEmb==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PreTornillTFreno">Correcto&nbsp;precintado&nbsp;inicial&nbsp;de&nbsp;los&nbsp;tornillos</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="PreTornillTFreno" SIZE=1>
										<OPTION <?php echo ($PreTornillTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($PreTornillTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($PreTornillTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="ZonaSurcosTFreno">Zona&nbsp;de&nbsp;freno&nbsp;sin&nbsp;surcos&nbsp;ni&nbsp;estrías</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="ZonaSurcosTFreno" SIZE=1>
										  <OPTION <?php echo ($ZonaSurcosTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($ZonaSurcosTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($ZonaSurcosTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="GrosorPasTFreno">Grosor&nbsp;de&nbsp;las&nbsp;pastillas&nbsp;de&nbsp;freno&nbsp;(>=&nbsp;6mm)</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="GrosorPasTFreno" SIZE=1>
										<OPTION <?php echo ($GrosorPasTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($GrosorPasTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($GrosorPasTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="ZonaLimpiaTFreno">Zona&nbsp;de&nbsp;freno&nbsp;limpia&nbsp;y&nbsp;desengrasada</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="ZonaLimpiaTFreno" SIZE=1>
										  <OPTION <?php echo ($ZonaLimpiaTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($ZonaLimpiaTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($ZonaLimpiaTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstMuelleTFreno">Estado&nbsp;muelle&nbsp;/&nbsp;clavijas</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstMuelleTFreno" SIZE=1>
										<OPTION <?php echo ($EstMuelleTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstMuelleTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstMuelleTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="EstTornillTFreno">Estado&nbsp;de&nbsp;los&nbsp;tornillos</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="EstTornillTFreno" SIZE=1>
										  <OPTION <?php echo ($EstTornillTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($EstTornillTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($EstTornillTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EjePinonTFreno">Estado&nbsp;de&nbsp;los&nbsp;dientes&nbsp;eje&nbsp;piñón</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EjePinonTFreno" SIZE=1>
										<OPTION <?php echo ($EjePinonTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EjePinonTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EjePinonTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="TorLoctiteTFreno">Tornillos&nbsp;con&nbsp;loctite&nbsp;246&nbsp;antes&nbsp;apriete</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="TorLoctiteTFreno" SIZE=1>
										  <OPTION <?php echo ($TorLoctiteTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($TorLoctiteTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($TorLoctiteTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="LimPinonTFreno">Limpieza&nbsp;y&nbsp;engrase&nbsp;del&nbsp;eje&nbsp;del&nbsp;piñón</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="LimPinonTFreno" SIZE=1>
										<OPTION <?php echo ($LimPinonTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($LimPinonTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($LimPinonTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="MarcasTornTFreno">Colocadas&nbsp;marcas&nbsp;en&nbsp;tornillos</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="MarcasTornTFreno" SIZE=1>
										  <OPTION <?php echo ($MarcasTornTFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($MarcasTornTFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($MarcasTornTFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PreTornillTPolea">Correcto&nbsp;precintado&nbsp;inicial&nbsp;de&nbsp;tornillos</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="PreTornillTPolea" SIZE=1>
										<OPTION <?php echo ($PreTornillTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($PreTornillTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($PreTornillTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="EstTornillTPolea">Estado&nbsp;de&nbsp;los&nbsp;tornillos</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="EstTornillTPolea" SIZE=1>
										  <OPTION <?php echo ($EstTornillTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($EstTornillTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($EstTornillTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="HolguraEjeTPolea">Holguras&nbsp;del&nbsp;eje&nbsp;de&nbsp;Polea</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="HolguraEjeTPolea" SIZE=1>
										<OPTION <?php echo ($HolguraEjeTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($HolguraEjeTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($HolguraEjeTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="TorLoctiteTPolea">Tornillos&nbsp;con&nbsp;loctite&nbsp;246&nbsp;antes&nbsp;apriete</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="TorLoctiteTPolea" SIZE=1>
										  <OPTION <?php echo ($TorLoctiteTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($TorLoctiteTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($TorLoctiteTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstNerviosTPolea">Estado&nbsp;de&nbsp;los&nbsp;nervios&nbsp;de&nbsp;la&nbsp;Polea</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstNerviosTPolea" SIZE=1>
										<OPTION <?php echo ($EstNerviosTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstNerviosTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstNerviosTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="MarcasTornTPolea">Colocadas&nbsp;marcas&nbsp;en&nbsp;tornillos</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="MarcasTornTPolea" SIZE=1>
										  <OPTION <?php echo ($MarcasTornTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($MarcasTornTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($MarcasTornTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstCarcasaTPolea">Estado&nbsp;de&nbsp;la&nbsp;carcada&nbsp;de&nbsp;Cuerda</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstCarcasaTPolea" SIZE=1>
										<OPTION <?php echo ($EstCarcasaTPolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstCarcasaTPolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstCarcasaTPolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="PreTornillCFreno">Correcto&nbsp;precintado&nbsp;inicial&nbsp;de&nbsp;los&nbsp;Tornillos</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="PreTornillCFreno" SIZE=1>
										<OPTION <?php echo ($PreTornillCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($PreTornillCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($PreTornillCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="EstTornillCFreno">Estado&nbsp;de&nbsp;los&nbsp;tornillos</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="EstTornillCFreno" SIZE=1>
										  <OPTION <?php echo ($EstTornillCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($EstTornillCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($EstTornillCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstJuntaCFreno">Estado&nbsp;de&nbsp;la&nbsp;junta&nbsp;la&nbsp;Carcasa</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstJuntaCFreno" SIZE=1>
										<OPTION <?php echo ($EstJuntaCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstJuntaCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstJuntaCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="TorLoctiteCFreno">Tornillos&nbsp;con&nbsp;loctite&nbsp;246&nbsp;antes&nbsp;apriete</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="TorLoctiteCFreno" SIZE=1>
										  <OPTION <?php echo ($TorLoctiteCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($TorLoctiteCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($TorLoctiteCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstDientesCFreno">Estado&nbsp;de&nbsp;los&nbsp;dientes&nbsp;de&nbsp;la&nbsp;rueda</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstDientesCFreno" SIZE=1>
										<OPTION <?php echo ($EstDientesCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstDientesCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstDientesCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="MarcasTornCFreno">Colocadas&nbsp;marcas&nbsp;en&nbsp;tornillos</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="MarcasTornCFreno" SIZE=1>
										  <OPTION <?php echo ($MarcasTornCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($MarcasTornCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($MarcasTornCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="RuedaLimpiaCFreno">Rueda&nbsp;dentada&nbsp;limpia&nbsp;y&nbsp;engrasada</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="RuedaLimpiaCFreno" SIZE=1>
										<OPTION <?php echo ($RuedaLimpiaCFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($RuedaLimpiaCFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($RuedaLimpiaCFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;">
                                      	<LABEL for="DeslizamientoCuerda">Deslizamiento&nbsp;correcto&nbsp;de&nbsp;la&nbsp;cuerda&nbsp;unos&nbsp;3m&nbsp;
                                        <br/>para&nbsp;cada&nbsp;lado&nbsp;(sin&nbsp;apreciar&nbsp;ruidos&nbsp;extraños)</LABEL>
                                      </td>
                                      <td>
	 	                                <SELECT NAME="DeslizamientoCuerda" SIZE=1>
										 <OPTION <?php echo ($DeslizamientoCuerda==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										 <OPTION <?php echo ($DeslizamientoCuerda==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										 <OPTION <?php echo ($DeslizamientoCuerda==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
	 	                                </SELECT>
                                      </td>
                                    </tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstGenCuerdaPri">Estado&nbsp;general&nbsp;(nudos,&nbsp;pegotes,&nbsp;cortes,&nbsp;etc)</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstGenCuerdaPri" SIZE=1>
										<OPTION <?php echo ($EstGenCuerdaPri==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstGenCuerdaPri==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstGenCuerdaPri==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="LongMedidaCuerdaPri">Longitud&nbsp;de&nbsp;la&nbsp;cuerda&nbsp;=&nbsp;medida&nbsp;de&nbsp;la&nbsp;torre</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="LongMedidaCuerdaPri" SIZE=1>
										  <OPTION <?php echo ($LongMedidaCuerdaPri==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($LongMedidaCuerdaPri==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($LongMedidaCuerdaPri==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstProCuerdaPri">Estado&nbsp;del&nbsp;protector&nbsp;termo,&nbsp;retractil</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstProCuerdaPri" SIZE=1>
										<OPTION <?php echo ($EstProCuerdaPri==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstProCuerdaPri==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstProCuerdaPri==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="MosquetonCuerdaPri">Mosquetón&nbsp;encima&nbsp;de&nbsp;los&nbsp;3m&nbsp;de&nbsp;la&nbsp;cuerda</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="MosquetonCuerdaPri" SIZE=1>
										  <OPTION <?php echo ($MosquetonCuerdaPri==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($MosquetonCuerdaPri==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($MosquetonCuerdaPri==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="LongitudCuerdaPri">Longitud&nbsp;cuerda</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="LongitudCuerdaPri" SIZE=1>
										<OPTION <?php echo ($LongitudCuerdaPri==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($LongitudCuerdaPri==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($LongitudCuerdaPri==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="AnyoFabCuerdaPri">Año&nbsp;fabricación&nbsp;de&nbsp;la&nbsp;cuerda</LABEL></td>
	                                  <td>
                                        <INPUT TYPE="text" NAME="AnyoFabCuerdaPri" class="Fecha" value="<?php echo $AnyoFabCuerdaPri;?>" size="10" maxlength="10" />
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstGenCuerdaSeg">Estado&nbsp;visual&nbsp;de&nbsp;las&nbsp;cuerda</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstGenCuerdaSeg" SIZE=1>
										<OPTION <?php echo ($EstGenCuerdaSeg==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstGenCuerdaSeg==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstGenCuerdaSeg==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="NSerieCuerdaSeg1">Nº&nbsp;serie&nbsp;de&nbsp;las&nbsp;cuerdas</LABEL></td>
	                                  <td>
                                      	<table>
                                          <tr>
                                           <td>
                                            <INPUT TYPE="text" NAME="NSerieCuerdaSeg1" value="<?php echo $NSerieCuerdaSeg1;?>" size="15" maxlength="15" />
                                           </td>
                                          </tr>
                                          <tr>
                                           <td>
                                            <INPUT TYPE="text" NAME="NSerieCuerdaSeg2" value="<?php echo $NSerieCuerdaSeg2;?>" size="15" maxlength="15" />
                                           </td>
                                          </tr>
                                        </table>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="SupSacaCuerdaSeg">Cuerdas&nbsp;en&nbsp;la&nbsp;parte&nbsp;superior&nbsp;de&nbsp;la&nbsp;saca</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="SupSacaCuerdaSeg" SIZE=1>
										<OPTION <?php echo ($SupSacaCuerdaSeg==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($SupSacaCuerdaSeg==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($SupSacaCuerdaSeg==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="AnyoFabCuerdaSeg1">Años&nbsp;fabricación&nbsp;de&nbsp;las&nbsp;cuerdas</LABEL></td>
	                                  <td>
                                      	<table>
                                          <tr>
                                          	<td>
                                            <INPUT TYPE="text" NAME="AnyoFabCuerdaSeg1" value="<?php echo $AnyoFabCuerdaSeg1;?>" size="10" maxlength="10" />
                                            </td>
                                          </tr>
                                          <tr>
                                          	<td>
                                            <INPUT TYPE="text" NAME="AnyoFabCuerdaSeg2" value="<?php echo $AnyoFabCuerdaSeg2;?>" size="10" maxlength="10" />
                                            </td>
                                          </tr>
                                        </table>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstMosqueton">Estado&nbsp;visual&nbsp;de&nbsp;los&nbsp;elementos</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstMosqueton" SIZE=1>
										<OPTION <?php echo ($EstMosqueton==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstMosqueton==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstMosqueton==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:375px;"><LABEL for="FucMosqueton">Correcto&nbsp;funcionamiento</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="FucMosqueton" SIZE=1>
										  <OPTION <?php echo ($FucMosqueton==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($FucMosqueton==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($FucMosqueton==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>
                                </td>
							</tr>                            
        <?php
		}
		else 	// Por Defecto, siempre PSA
		{
		?>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Bolsa">Bolsa</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="Bolsa" SIZE=1>
										<OPTION <?php echo ($Bolsa==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($Bolsa==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($Bolsa==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="DescensorAG">Descensor&nbsp;AG10K&nbsp;+&nbsp;Mosquetón</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="DescensorAG" SIZE=1>
										  <OPTION <?php echo ($DescensorAG==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($DescensorAG==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($DescensorAG==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Sellado">Sellado</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="Sellado" SIZE=1>
										<OPTION <?php echo ($Sellado==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($Sellado==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($Sellado==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="CaboAnclaje">Cabo&nbsp;Anclaje&nbsp;+&nbsp;un&nbsp;Mosquetón</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="CaboAnclaje" SIZE=1>
										  <OPTION <?php echo ($CaboAnclaje==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($CaboAnclaje==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($CaboAnclaje==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="NumeroSello">Nº&nbsp;del&nbsp;sello&nbsp;legible&nbsp;y&nbsp;completa</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="NumeroSello" SIZE=1>
										<OPTION <?php echo ($NumeroSello==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($NumeroSello==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($NumeroSello==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="Humedad">Libre&nbsp;de&nbsp;Humedad/Corrosión</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="Humedad" SIZE=1>
										  <OPTION <?php echo ($Humedad==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($Humedad==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($Humedad==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EtiquetaLegible">Etiqueta&nbsp;identificativa&nbsp;legible</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EtiquetaLegible" SIZE=1>
										<OPTION <?php echo ($EtiquetaLegible==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EtiquetaLegible==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EtiquetaLegible==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="CuerdaSalida">Desgaste&nbsp;cuerda&nbsp;salida</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="CuerdaSalida" SIZE=1>
										  <OPTION <?php echo ($CuerdaSalida==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($CuerdaSalida==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($CuerdaSalida==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstadoCarcasa">Estado&nbsp;de&nbsp;la&nbsp;Carcasa</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstadoCarcasa" SIZE=1>
										<OPTION <?php echo ($EstadoCarcasa==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstadoCarcasa==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstadoCarcasa==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="MosquetonArgolla">Estado&nbsp;Mosquetón&nbsp;y&nbsp;Argolla</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="MosquetonArgolla" SIZE=1>
										  <OPTION <?php echo ($MosquetonArgolla==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($MosquetonArgolla==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($MosquetonArgolla==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="CuerdaEntrada">Desgaste&nbsp;cuerda&nbsp;entrada</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="CuerdaEntrada" SIZE=1>
										<OPTION <?php echo ($CuerdaEntrada==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($CuerdaEntrada==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($CuerdaEntrada==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form" colspan="3" align="center">
									<LABEL for="NecesarioAbrir">Necesario&nbsp;Abrir</LABEL>
                                    <INPUT VALUE=1 TYPE="checkbox" NAME="NecesarioAbrir" <?php echo ($NecesarioAbrir==1)?"checked":"";?> onclick='AlternarVisualizacion("optNecesarioAbrir");'/>
								</td>
							</tr>
                            <tr>
                            	<td class="etiqueta_form" colspan="3">
                                  <table id="optNecesarioAbrir" style="display:<?php echo ($NecesarioAbrir==1)?'block;':'none;';?>">
									<tr>
									  <td>
										<LABEL for="RuedaDentada">Inspección&nbsp;visual&nbsp;de&nbsp;la&nbsp;rueda&nbsp;dentada</LABEL>
									  </td>
									  <td>
		                               	<SELECT NAME="RuedaDentada" SIZE=1>
										  <OPTION <?php echo ($RuedaDentada==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($RuedaDentada==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($RuedaDentada==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
									  </td>
									  <td>
                                         <LABEL for="PoleaCuerda">Inspección&nbsp;visual&nbsp;de&nbsp;la&nbsp;polea&nbsp;de&nbsp;cuerda</LABEL>
                                      </td>
                            	      <td>
		    	                        <SELECT NAME="PoleaCuerda" SIZE=1>
										  <OPTION <?php echo ($PoleaCuerda==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($PoleaCuerda==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($PoleaCuerda==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										 </SELECT>
		                              </td>
        	                        </tr> 
									<tr>
									  <td>
										<LABEL for="Dientes">Dientes&nbsp;sin&nbsp;roturas&nbsp;ni&nbsp;fisuras</LABEL>
									  </td>
									  <td>
		                               	<SELECT NAME="Dientes" SIZE=1>
											<OPTION <?php echo ($Dientes==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($Dientes==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($Dientes==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
									  </td>
									  <td>
                        		         <LABEL for="SuperficiePolea">Superficie&nbsp;Polea&nbsp;(abrasión/desgaste)</LABEL>
                                      </td>
	                            	  <td>
		    	                       	 <SELECT NAME="SuperficiePolea" SIZE=1>
											<OPTION <?php echo ($SuperficiePolea==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($SuperficiePolea==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($SuperficiePolea==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										  </SELECT>
		                              </td>
        	                        </tr>
									<tr>
									  <td>
										<LABEL for="CajaFreno">Control&nbsp;caja&nbsp;de&nbsp;freno</LABEL>
									  </td>
									  <td>
		                               	<SELECT NAME="CajaFreno" SIZE=1>
											<OPTION <?php echo ($CajaFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($CajaFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($CajaFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
									  </td>
									  <td>
                        		         <LABEL for="ZapatasFreno">Marcha&nbsp;suave&nbsp;Zapatas&nbsp;freno</LABEL>
                                      </td>
	                            	  <td>
		    	                       	 <SELECT NAME="ZapatasFreno" SIZE=1>
											<OPTION <?php echo ($ZapatasFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($ZapatasFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($ZapatasFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										  </SELECT>
		                              </td>
        	                        </tr>
									<tr>
									  <td>
										<LABEL for="UnidadFreno">Control&nbsp;unidad&nbsp;de&nbsp;freno</LABEL>
									  </td>
									  <td>
		                               	<SELECT NAME="UnidadFreno" SIZE=1>
											<OPTION <?php echo ($UnidadFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($UnidadFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($UnidadFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
									  </td>
									  <td>
                        		         <LABEL for="ControlMuelle">Control&nbsp;del&nbsp;Muelle</LABEL>
                                      </td>
	                            	  <td>
		    	                       	 <SELECT NAME="ControlMuelle" SIZE=1>
											<OPTION <?php echo ($ControlMuelle==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($ControlMuelle==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($ControlMuelle==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										  </SELECT>
		                              </td>
        	                        </tr>
									<tr>
									  <td>
										<LABEL for="ProfundidadFreno">Profundidad&nbsp;estrías&nbsp;caja&nbsp;freno&nbsp;>&nbsp;2mm</LABEL>
									  </td>
									  <td>
		                               	<SELECT NAME="ProfundidadFreno" SIZE=1>
											<OPTION <?php echo ($ProfundidadFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($ProfundidadFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($ProfundidadFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
									  </td>
									  <td>
                        		         <LABEL for="FlancosArbol">Flancos&nbsp;del&nbsp;diente&nbsp;árbol&nbsp;del&nbsp;piñon</LABEL>
                                      </td>
	                            	  <td>
		    	                       	 <SELECT NAME="FlancosArbol" SIZE=1>
											<OPTION <?php echo ($FlancosArbol==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($FlancosArbol==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($FlancosArbol==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										  </SELECT>
		                              </td>
        	                        </tr>
									<tr>
									  <td>
										<LABEL for="GuarnicionFreno">Guarnición&nbsp;del&nbsp;freno&nbsp;&lt;&nbsp;31,5mm</LABEL>
									  </td>
									  <td>
		                               	<SELECT NAME="GuarnicionFreno" SIZE=1>
											<OPTION <?php echo ($GuarnicionFreno==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($GuarnicionFreno==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($GuarnicionFreno==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
									  </td>
									  <td>
                        		         <LABEL for="PuntosApoyo">Puntos&nbsp;apoyo&nbsp;de&nbsp;la&nbsp;carcasa</LABEL>
                                      </td>
	                            	  <td>
		    	                       	 <SELECT NAME="PuntosApoyo" SIZE=1>
											<OPTION <?php echo ($PuntosApoyo==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
											<OPTION <?php echo ($PuntosApoyo==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
											<OPTION <?php echo ($PuntosApoyo==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										  </SELECT>
		                              </td>
        	                        </tr>
                                  </table>
                                </td>
                            </tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstadoCuerda">Estado&nbsp;cuerda&nbsp;Principal</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstadoCuerda" SIZE=1>
										<OPTION <?php echo ($EstadoCuerda==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstadoCuerda==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstadoCuerda==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="AnyoFabCuerdaPri">Año&nbsp;fabricación&nbsp;de&nbsp;la&nbsp;cuerda</LABEL></td>
	                                  <td>
										<INPUT TYPE="text" NAME="AnyoFabCuerdaPri" value="<?php echo $AnyoFabCuerdaPri;?>" size="10" maxlength="10" />
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="FinDeCuerda">Fin&nbsp;de&nbsp;cuerda&nbsp;(nudo&nbsp;ó&nbsp;cosido)</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="FinDeCuerda" SIZE=1>
										<OPTION <?php echo ($FinDeCuerda==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($FinDeCuerda==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($FinDeCuerda==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="EstMosquetonPri">Estado&nbsp;Mosquetón</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="EstMosquetonPri" SIZE=1>
										  <OPTION <?php echo ($EstMosquetonPri==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($EstMosquetonPri==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($EstMosquetonPri==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Termoretractil">Protector&nbsp;Termoretráctil</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="Termoretractil" SIZE=1>
										<OPTION <?php echo ($Termoretractil==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($Termoretractil==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($Termoretractil==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="EstCuerdaSeguridad">Estado&nbsp;cuerda&nbsp;Seguridad</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="EstCuerdaSeguridad" SIZE=1>
										<OPTION <?php echo ($EstCuerdaSeguridad==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($EstCuerdaSeguridad==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($EstCuerdaSeguridad==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="AnyoFabCuerdaSeg">Año&nbsp;fabricación&nbsp;cuerda&nbsp;Seguridad</LABEL></td>
	                                  <td>
										<INPUT TYPE="text" NAME="AnyoFabCuerdaSeg" value="<?php echo $AnyoFabCuerdaSeg;?>" size="10" maxlength="10" />
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="MosquetonSeguridad">Mosquetón&nbsp;Seguridad</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="MosquetonSeguridad" SIZE=1>
										<OPTION <?php echo ($MosquetonSeguridad==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($MosquetonSeguridad==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($MosquetonSeguridad==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:65px;"><LABEL for="NSerieSeguridad">Nº&nbsp;Serie&nbsp;cuerda</LABEL></td>
	                                  <td>
										<INPUT TYPE="text" NAME="NSerieSeguridad" value="<?php echo $NSerieSeguridad;?>" size="40" maxlength="40" />
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="DeslizamientoCuerda">Deslizamiento&nbsp;correcto&nbsp;de&nbsp;la&nbsp;cuerda</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="DeslizamientoCuerda" SIZE=1>
										<OPTION <?php echo ($DeslizamientoCuerda==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($DeslizamientoCuerda==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($DeslizamientoCuerda==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="CargaMinima">Funcionamiento&nbsp;carga&nbsp;mínima&nbsp;30kg</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="CargaMinima" SIZE=1>
										  <OPTION <?php echo ($CargaMinima==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($CargaMinima==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($CargaMinima==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="VainaCuerda">Estado&nbsp;Vaina&nbsp;cuerda</LABEL>
								</td>
								<td class="check_form">
                                  	<SELECT NAME="VainaCuerda" SIZE=1>
										<OPTION <?php echo ($VainaCuerda==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										<OPTION <?php echo ($VainaCuerda==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										<OPTION <?php echo ($VainaCuerda==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:210px;"><LABEL for="Mordazas">Mordazas&nbsp;de&nbsp;sujección</LABEL></td>
	                                  <td>
    	                              	<SELECT NAME="Mordazas" SIZE=1>
										  <OPTION <?php echo ($Mordazas==0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
										  <OPTION <?php echo ($Mordazas==1)?"SELECTED":"";?> VALUE=1>OK</OPTION>
										  <OPTION <?php echo ($Mordazas==2)?"SELECTED":"";?> VALUE=2>N/A</OPTION>
										</SELECT>
                                      </td>
									</tr>
                                  </table>                                
                                </td>
							</tr>
        <?php
		}
		?>
		<?php
		unset($Bolsa, $Sellado, $NumeroSello, $DescensorAG, $CaboAnclaje, $Humedad, $EtiquetaLegible, $EstadoCarcasa, $CuerdaEntrada, $CuerdaSalida, 
			$MosquetonArgolla, $NecesarioAbrir, $RuedaDentada, $Dientes, $PoleaCuerda, $SuperficiePolea, $CajaFreno, $UnidadFreno, $ProfundidadFreno, $GuarnicionFreno,
			$ZapatasFreno, $ControlMuelle, $FlancosArbol, $PuntosApoyo, $EstadoCuerda, $FinDeCuerda, $Termoretractil, $AnyoFabCuerdaPri, $EstMosquetonPri,
			$EstCuerdaSeguridad, $MosquetonSeguridad, $NSerieSeguridad, $AnyoFabCuerdaSeg, $DeslizamientoCuerda, $CargaMinima, $VainaCuerda, $Mordazas);					
		unset ($MaletinEqu, $SisEnvaseEqu, $SacaAzulTran, $BolsaPlastico, $DescensorMRG, $CuerdaDesMRG,
			$CuerdasSeguridad, $PegatinaPrecinto, $BridaPrecinto, $EtiquetaExterior, $LibroInspecciones,
			$MaletinEmb, $SisEnvaseEmb, $BolsaPlasEmb, $SacaAzulEmb, $PegatinaPreEmb, $BridaPreEmb, $PreTornillTFreno,
			$GrosorPasTFreno, $EstMuelleTFreno, $EjePinonTFreno, $LimPinonTFreno, $ZonaSurcosTFreno, $ZonaLimpiaTFreno,
			$EstTornillTFreno, $TorLoctiteTFreno, $MarcasTornTFreno, $PreTornillTPolea, $HolguraEjeTPolea, $EstNerviosTPolea,
			$EstCarcasaTPolea, $EstTornillTPolea, $TorLoctiteTPolea, $MarcasTornTPolea, $PreTornillCFreno, $EstJuntaCFreno,
			$EstDientesCFreno, $RuedaLimpiaCFreno,$EstTornillCFreno, $TorLoctiteCFreno, $MarcasTornCFreno,
			$EstGenCuerdaPri, $EstProCuerdaPri, $LongitudCuerdaPri, $LongMedidaCuerdaPri, $MosquetonCuerdaPri,
			$EstGenCuerdaSeg, $SupSacaCuerdaSeg, $EstMosqueton, $FucMosqueton, 
			$NSerieCuerdaSeg1, $AnyoFabCuerdaSeg1, $NSerieCuerdaSeg2, $AnyoFabCuerdaSeg2);		
	}	
}
unset ($Path, $Archivo);
?>