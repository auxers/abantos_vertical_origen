<?php
require_once("../../inc/function/funcs.php");
require_once("../../db-config.php");

$Path = "../../data/"; $Html = "";
if (file_exists($Path.($Archivo = isset($_REQUEST['File']) ? $_REQUEST['File'] : "")))
{
	if (file_exists(($File = $Path.$Archivo)))
	{
		$doc = new DOMDocument();
		$doc->load($File);

		$LinCod = isset($_REQUEST['Linea'])?$_REQUEST['Linea']:0;
		$LinPle = isset($_REQUEST['LinPle'])?$_REQUEST['LinPle']:0;
		$MarcaLV = isset($_REQUEST['Marca'])?$_REQUEST['Marca']:1;
				
		// * Líneas de Vida *
		$NumeroCable = $NumeroSerie = $NAbsorbedor = $NTrompa = $NTramo = "";
		$EstadoCable = $CantidadCable = 0; $EstadoCableMotivo = $TipoPletina = "";
		$Tension = $ConfPletina = 0; $NombrePletina1 = $NombrePletina2 = $NombrePletina3 = $NombrePletina4 = "";
		$CampoPletina1 = $CampoPletina2 = $CampoPletina3 = $CampoPletina4 = 1; $VarillasRoscadas = $Cartel = 0;
		$AnclajeInf1AIMotivo = ""; $AnclajeInf1 = $AnclajeInf1AI = $AnclajeInf1Tensor = $AnclajeInf1Perrillos = 0;
		$AnclajeInf1Guardacabos = $AnclajeInf1Tuercas = $AnclajeSup1 = $AnclajeSup2 = $AnclajeSup1AS = 0;
		$AnclajeSup1ASMotivo = ""; $AnclajeSup1Pasador = $AnclajeSup1Bulon = 0;
		$AmortiguadorMuelleMotivo = ""; $Amortiguador = $AmortiguadorMuelle = $AmortiguadorPasador = $AmortiguadorBulon = 0;
		$TornilleriaPletina = $TornilleriaApriete = $Ensayo = $Escalera = $Interferencia = $Oxidacion = 0;
		
		foreach($doc->getElementsByTagName("Torre") as $torre)
		{
			if (is_object($aux = $torre->getElementsByTagName("Id")->item(0)))
			{
				if (($Id = $aux->nodeValue) == $LinCod)
				{   		
					foreach($torre->getElementsByTagName("Linea") as $linea)
					{   // Compruebo que la Línea de Vida es la que hemos seleccionado
						if (is_object($aux = $linea->getElementsByTagName("IdPletina")->item(0)))
						{
							if (($IdPletina = $aux->nodeValue) == $LinPle)
							{   // ODG, 05.01.15 para VECTALINE, 'Nº Cable' pasa a 'Nº Tramo'.
								// 	'Nº Trompa' pasa a 'Nº Sop. Superior'.
								//	'Nº Absorbedor' pasa a 'N Sop. Inferior'.
								//	'Nº Tramo' no se usa.
								if (is_object($aux = $linea->getElementsByTagName("Cable")->item(0)))
									$NumeroCable = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Serie")->item(0)))
									$NumeroSerie = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Absorbedor")->item(0)))
									$NAbsorbedor = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Trompa")->item(0)))
									$NTrompa = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Tramo")->item(0)))
									$NTramo = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("NombrePletina1")->item(0)))
									$NombrePletina1 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("NombrePletina2")->item(0)))
									$NombrePletina2 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("NombrePletina3")->item(0)))
									$NombrePletina3 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("NombrePletina4")->item(0)))
									$NombrePletina4 = $aux->nodeValue;

								if (is_object($aux = $linea->getElementsByTagName("EstadoCable")->item(0)))
									$EstadoCable = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("CantidadCable")->item(0)))
									$CantidadCable = is_numeric($aux->nodeValue) ? number_format($aux->nodeValue,2,'.','') : "1.00";
								if (is_object($aux = $linea->getElementsByTagName("EstadoCableMotivo")->item(0)))
									$EstadoCableMotivo = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Tension")->item(0)))
									$Tension = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("ConfPletina")->item(0)))
									$ConfPletina = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("TipoPletina")->item(0)))
									$TipoPletina = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("CampoPletina1")->item(0)))
									$CampoPletina1 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("CampoPletina2")->item(0)))
									$CampoPletina2 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("CampoPletina3")->item(0)))
									$CampoPletina3 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("CampoPletina4")->item(0)))
									$CampoPletina4 = $aux->nodeValue;
								
								if (is_object($aux = $linea->getElementsByTagName("VarillasRoscadas")->item(0)))
									$VarillasRoscadas = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Cartel")->item(0)))
									$Cartel = $aux->nodeValue;
							
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1")->item(0)))
									$AnclajeInf1 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1AI")->item(0)))
									$AnclajeInf1AI = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1AIMotivo")->item(0)))
									$AnclajeInf1AIMotivo = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Tensor")->item(0)))
									$AnclajeInf1Tensor = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Perrillos")->item(0)))
									$AnclajeInf1Perrillos = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Guardacabos")->item(0)))
									$AnclajeInf1Guardacabos = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeInferior1Tuercas")->item(0)))
									$AnclajeInf1Tuercas = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1")->item(0)))
									$AnclajeSup1 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1AS")->item(0)))
									$AnclajeSup1AS = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1ASMotivo")->item(0)))
									$AnclajeSup1ASMotivo = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior2")->item(0)))
									$AnclajeSup2 = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1Pasador")->item(0)))
									$AnclajeSup1Pasador = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AnclajeSuperior1Bulon")->item(0)))
									$AnclajeSup1Bulon = $aux->nodeValue;
							
								if (is_object($aux = $linea->getElementsByTagName("Amortiguador")->item(0)))
									$Amortiguador = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AmortiguadorMuelle")->item(0)))
									$AmortiguadorMuelle = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AmortiguadorMuelleMotivo")->item(0)))
									$AmortiguadorMuelleMotivo = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AmortiguadorPasador")->item(0)))
									$AmortiguadorPasador = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("AmortiguadorBulon")->item(0)))
									$AmortiguadorBulon = $aux->nodeValue;
									
								if (is_object($aux = $linea->getElementsByTagName("TornilleriaPletina")->item(0)))
									$TornilleriaPletina = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("TornilleriaApriete")->item(0)))
									$TornilleriaApriete = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Ensayo")->item(0)))
									$Ensayo = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Escalera")->item(0)))
									$Escalera = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Interferencia")->item(0)))
									$Interferencia = $aux->nodeValue;
								if (is_object($aux = $linea->getElementsByTagName("Oxidacion")->item(0)))
									$Oxidacion = $aux->nodeValue;
							}
						}
					}
				}
			}
		}
		
		// ODG, 05.01.15 Dependiendo la Marca de la Línea de Vida, los campos tendrán un nombre u otro, sólo se cambia el literal para 
		//	mantener la compatibilidad con el resto del código y la base de datos...
		$Texto = fGetLiterales(7,$MarcaLV,1, $conn);
		?>
							<tr>
								<td class="etiqueta_form" style="width:125px;">
									<LABEL FOR="NumeroCable"><?php echo $Texto[0];?></LABEL>
								</td>
								<td class="check_form" style="width:175px;">
                                	<INPUT TYPE="<?php echo ($Texto[0]!="")?"text":"hidden";?>" NAME="NumeroCable" value="<?php echo ($Texto[0]!="")?$NumeroCable:"";?>" size="25" maxlength="25" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr>
                                      <td style="width:100px;">
                                      	<LABEL for="NumeroSerie"><?php echo $Texto[1];?></LABEL>
                                      </td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="NumeroSerie" value="<?php echo $NumeroSerie;?>" size="40" maxlength="40"/>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL FOR="NAbsorbedor"><?php echo $Texto[2];?></LABEL>
								</td>
								<td>
                                	<INPUT TYPE="<?php echo ($Texto[2]!="")?"text":"hidden";?>" NAME="NAbsorbedor" value="<?php echo ($Texto[2]!="")?$NAbsorbedor:"";?>" size="25" maxlength="25" />
								</td>
								<td class="opcional_form">
                                  <table>
                                    <tr style="visibility:<?php echo ($Texto[3]!="")?"visible":"hidden";?>;">
                                      <td style="width:100px;">
                                      	<LABEL FOR="NTrompa"><?php echo $Texto[3];?></LABEL>
                                      </td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="NTrompa" value="<?php echo ($Texto[3]!="")?$NTrompa:"";?>" size="25" maxlength="25" />
                                      </td>
                                    </tr>
                                    <tr style="visibility:<?php echo ($Texto[4]!="")?"visible":"hidden";?>;">
                                      <td style="width:100px;">
										<LABEL FOR="NTramo"><?php echo $Texto[4];?></LABEL>
                                      </td>
                                      <td>
                                      	<INPUT TYPE="text" NAME="NTramo" value="<?php echo ($Texto[4]!="")?$NTramo:"";?>" size="25" maxlength="25" />
                                      </td>
                                    </tr>
                                  </table>
                                </td>
							</tr>
							<tr>
								<td class="etiqueta_form">
								  <LABEL for="EstadoCable"><?php echo $Texto[5];?></LABEL>
								</td>
								<td class="check_form">
                                  <INPUT VALUE=1 TYPE="checkbox" NAME="EstadoCable" <?php echo ($EstadoCable==1)?"checked":"";?> onclick='AlternarVisualizacion("optEstadoCable1,optEstadoCable2");' />
		                          <div id="optEstadoCable1" style="float:right;display:<?php echo ($EstadoCable==1)?"none":"block";?>;">
                                    <LABEL for="CantidadCable"><?php echo $Texto[6];?></LABEL>
        		                    <INPUT TYPE="text" NAME="CantidadCable" CLASS="txtDec" VALUE="<?php echo $CantidadCable;?>" size="6" maxlength="6" />
                                  </div>
                                </td>
								<td class="opcional_form">
								  <div id="optEstadoCable2" style="display:<?php echo ($EstadoCable==1)?"none":"block";?>;">
								    <?php $Tmp = "";?>
                                    <table>
                                  	  <tr>
                                       <td>
									  	<SELECT NAME="EstadoCableCB" SIZE=1 onchange='OptVisualizacion("optEstadoCableCB", $(this).val());'>
                                          <OPTION <?php echo ($EstadoCableMotivo == "Cable Corto")? ($Tmp = "SELECTED"):"";?> VALUE='Cable Corto'>Cable Corto</OPTION>
										  <OPTION <?php echo ($EstadoCableMotivo == "Aplastamiento")? ($Tmp = "SELECTED"):"";?> VALUE='Aplastamiento'>Aplastamiento</OPTION>
										  <OPTION <?php echo ($EstadoCableMotivo == "Deshilachado")? ($Tmp = "SELECTED"):"";?> VALUE='Deshilachado'>Deshilachado</OPTION>
										  <OPTION <?php echo ($EstadoCableMotivo == "Oxidado")?($Tmp = "SELECTED"):"";?> VALUE='Oxidado'>Oxidado</OPTION>
										  <OPTION <?php echo ($EstadoCableMotivo == "Cortado")?($Tmp = "SELECTED"):"";?> VALUE='Cortado'>Cortado</OPTION>
										  <OPTION <?php echo ($Tmp == "")?"SELECTED":"";?> VALUE="">Otro</OPTION>
										</SELECT>
                                       </td>
                                       <td>
										<div id="optEstadoCableCB" style="display:<?php echo ($Tmp == "")?"block":"none";?>;">
                                	      <input type="text" name="EstadoCableMotivo" value="<?php echo ($Tmp == "") ? $EstadoCableMotivo : "";?>" size="25" maxlength="25" />
                                  	  	</div>
                                       </td>
                                      </tr>
                                     </table>
									</div>
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[7]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="Tension"><?php echo $Texto[7];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Tension" <?php echo ($Tension == 1 || empty($Texto[7]))? "checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="ConfPletina"><?php echo $Texto[8];?><b><?php echo $TipoPletina;?></b></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="ConfPletina" <?php echo ($ConfPletina==1)?"checked":"";?> onclick='AlternarVisualizacion("optConfPletina");' /> 
								</td>
								<td class="opcional_form">
									<div id="optConfPletina" style="display:<?php echo ($ConfPletina==1)?"none":"block";?>;">
										<table>
											<tr style="visibility:<?php echo ($NombrePletina1 != "")?"visible":"hidden";?>">
												<td><?php echo $NombrePletina1;?>:</td>
												<td><input value=1 type="checkbox" name="CampoPletina1" <?php echo ($CampoPletina1==1)?"checked":"";?> onclick='AlternarVisualizacion("ConfPletina1");'/></td>
												<td>
                                                	<div id="ConfPletina1" style="display:<?php echo ($CampoPletina1==1)?"none":"block";?>;">
													<SELECT NAME="OpConfPletina1" SIZE=1>
                                                       	<OPTION <?php echo ($CampoPletina1==0)?"SELECTED":"";?> VALUE=0>NO OK</OPTION>
														<OPTION <?php echo ($CampoPletina1==2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
														<OPTION <?php echo ($CampoPletina1==3)?"SELECTED":"";?> VALUE=3>Añadir 1</OPTION>
														<OPTION <?php echo ($CampoPletina1==4)?"SELECTED":"";?> VALUE=4>Añadir 2</OPTION>
													</SELECT>
													</div>
                                                </td>
											</tr>
											<tr style="visibility:<?php echo ($NombrePletina2 != "")?"visible":"hidden";?>">
												<td><?php echo $NombrePletina2;?></td>
												<td><input value=1 type="checkbox" name="CampoPletina2" <?php echo ($CampoPletina2==1)?"checked":"";?> onclick='AlternarVisualizacion("ConfPletina2");'/></td>
												<td>
                                                	<div id="ConfPletina2" style="display:<?php echo ($CampoPletina2==1)?"none":"block";?>;">
													<SELECT NAME="OpConfPletina2" SIZE=1>
                                                       	<OPTION <?php echo ($CampoPletina2==0)?"SELECTED":"";?> VALUE=0>NO OK</OPTION>
														<OPTION <?php echo ($CampoPletina2==2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
														<OPTION <?php echo ($CampoPletina2==3)?"SELECTED":"";?> VALUE=3>Añadir 1</OPTION>
														<OPTION <?php echo ($CampoPletina2==4)?"SELECTED":"";?> VALUE=4>Añadir 2</OPTION>
													</SELECT>
													</div>
												</td>
											</tr>
											<tr style="visibility:<?php echo ($NombrePletina3 != "")?"visible":"hidden";?>">
												<td><?php echo $NombrePletina3;?></td>
												<td><input value=1 type="checkbox" name="CampoPletina3" <?php echo ($CampoPletina3==1)?"checked":"";?> onclick='AlternarVisualizacion("ConfPletina3");' /></td>
												<td>
                                                	<div id="ConfPletina3" style="display:<?php echo ($CampoPletina3==1)?"none":"block";?>;">
													<SELECT NAME="OpConfPletina3" SIZE=1> 
                                                       	<OPTION <?php echo ($CampoPletina3==0)?"SELECTED":"";?> VALUE=0>NO OK</OPTION>
														<OPTION <?php echo ($CampoPletina3==2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
														<OPTION <?php echo ($CampoPletina3==3)?"SELECTED":"";?> VALUE=3>Añadir 1</OPTION>
														<OPTION <?php echo ($CampoPletina3==4)?"SELECTED":"";?> VALUE=4>Añadir 2</OPTION>
													</SELECT>
													</div>
												</td>
											</tr>
                                            <tr style="visibility:<?php echo ($NombrePletina4 != "")?"visible":"hidden";?>">
                                            	<td><?php echo $NombrePletina4;?></td>
                                                <td><input value=1 type="checkbox" name="CampoPletina4" <?php echo ($CampoPletina4==1)?"checked":"";?> onclick='AlternarVisualizacion("ConfPletina4");' /></td>
												<td>
                                                   	<div id="ConfPletina4" style="display:<?php echo ($CampoPletina4==1)?"none":"block";?>;">
													<SELECT NAME="OpConfPletina4" SIZE=1>
                                                       	<OPTION <?php echo ($CampoPletina4==0)?"SELECTED":"";?> VALUE=0>NO OK</OPTION>
														<OPTION <?php echo ($CampoPletina4==2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
														<OPTION <?php echo ($CampoPletina4==3)?"SELECTED":"";?> VALUE=3>Añadir 1</OPTION>
														<OPTION <?php echo ($CampoPletina4==4)?"SELECTED":"";?> VALUE=4>Añadir 2</OPTION>
													</SELECT>
													</div>
                                                </td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[9]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="VarillasRoscadas"><?php echo $Texto[9];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="VarillasRoscadas" <?php echo ($VarillasRoscadas==1 || empty($Texto[9]))?"checked":"";?> onclick='AlternarVisualizacion("optVarillasRoscadas");' /> 
								</td>
								<td class="opcional_form">
									<div id="optVarillasRoscadas" style="display:<?php echo ($VarillasRoscadas==1)?"none":"block";?>;">
										<input type="text" name="NumeroVarillas" class="txtNum" value="<?php echo $VarillasRoscadas-1;?>" maxlength="4"/>
									</div>
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[10]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="Cartel"><?php echo $Texto[10];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Cartel" <?php echo ($Cartel==1 || empty($Texto[10]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="AnclajeInf1"><?php echo $Texto[11];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="AnclajeInf1" <?php echo ($AnclajeInf1==1)?"checked":"";?> onclick='AlternarVisualizacion("optAnclajeInf1");'/>
								</td>
								<td class="opcional_form">
								  <div id="optAnclajeInf1" style="display:<?php echo ($AnclajeInf1==1)?"none":"block";?>;">
									<table>
										<tr style="visibility:<?php echo ($Texto[12]!="")?"visible":"hidden";?>">
											<td style="width:105px;"><?php echo $Texto[12];?></td>
											<td>
											  <SELECT NAME="AnclajeInf1AI" SIZE=1 onchange='NotVisualizacion("optAnclajeInf1AIMotivo", $(this).val(), "1");'>
                                                <OPTION <?php echo ($AnclajeInf1AI == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1AI == 1 || empty($Texto[12]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1AI == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
												<OPTION <?php echo ($AnclajeInf1AI == 3)?"SELECTED":"";?> VALUE=3>Añadir</OPTION>
											  </SELECT>
											</td>
											<td>
                                              <div id="optAnclajeInf1AIMotivo" style="display:<?php echo ($AnclajeInf1AI == 1)?"none":"block";?>;">
	                                            <input type="text" name="AnclajeInf1AIMotivo" value="<?php echo $AnclajeInf1AIMotivo;?>"  maxlength="25" size="25"/>
                                              </div>
											</td>
										</tr>
										<tr style="visibility:<?php echo ($Texto[13]!="")?"visible":"hidden";?>">
											<td style="width:105px;"><?php echo $Texto[13];?></td>
											<td colspan="2">
											  <SELECT NAME="AnclajeInf1Tensor" SIZE=1>
                                                <OPTION <?php echo ($AnclajeInf1Tensor == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Tensor == 1 || empty($Texto[13]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Tensor == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
												<OPTION <?php echo ($AnclajeInf1Tensor == 3)?"SELECTED":"";?> VALUE=3>Añadir</OPTION>
											  </SELECT>
											</td>
										</tr>
										<tr style="visibility:<?php echo ($Texto[14]!="")?"visible":"hidden";?>">
											<td style="width:105px;"><?php echo $Texto[14];?></td>
											<td>
											  <SELECT NAME="AnclajeInf1Perrillos" SIZE=1 onchange='OptVisualizacion("optAnclajeInf1Perrillos", $(this).val());'>
                                                <OPTION <?php echo ($AnclajeInf1Perrillos == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
                                                <OPTION <?php echo ($AnclajeInf1Perrillos == 1 || empty($Texto[14]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Perrillos == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
												<OPTION <?php echo ($AnclajeInf1Perrillos >= 3)?"SELECTED":"";?> VALUE="">Añadir</OPTION>
											  </SELECT>
											</td>
											<td>
											  <div id="optAnclajeInf1Perrillos" style="display:<?php echo ($AnclajeInf1Perrillos < 3)?"none":"block";?>;">
                                              <?php if ($MarcaLV != 2) { // ODG, 05.01.15 Todos excepto VECTALINE ?>
												1:<input type="radio" name="AnclajeInf1PerrillosA" value="3" <?php echo ($AnclajeInf1Perrillos==3)?"checked":"";?>/>
												2:<input type="radio" name="AnclajeInf1PerrillosA" value="4" <?php echo ($AnclajeInf1Perrillos==4)?"checked":"";?>/>
												3:<input type="radio" name="AnclajeInf1PerrillosA" value="5" <?php echo ($AnclajeInf1Perrillos==5)?"checked":"";?>/>
                                              <?php } ?>
											  </div>
											</td>
										</tr>
										<tr style="visibility:<?php echo ($Texto[15]!="")?"visible":"hidden";?>">
											<td style="width:105px;"><?php echo $Texto[15];?></td>
											<td colspan="2">
											  <SELECT NAME="AnclajeInf1Guardacabos" SIZE=1>
                                                <OPTION <?php echo ($AnclajeInf1Guardacabos == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Guardacabos == 1 || empty($Texto[15]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Guardacabos == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
												<OPTION <?php echo ($AnclajeInf1Guardacabos == 3)?"SELECTED":"";?> VALUE=3>Añadir</OPTION>
											  </SELECT>
											</td>
										</tr>
										<tr style="visibility:<?php echo ($Texto[16]!="")?"visible":"hidden";?>">
											<td style="width:105px;"><?php echo $Texto[16];?></td>
											<td colspan="2">
                                              <SELECT NAME="AnclajeInf1Tuercas" SIZE=1>
												<OPTION <?php echo ($AnclajeInf1Tuercas == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Tuercas == 1 || empty($Texto[16]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
												<OPTION <?php echo ($AnclajeInf1Tuercas == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
												<OPTION <?php echo ($AnclajeInf1Tuercas == 3)?"SELECTED":"";?> VALUE=3>Añadir</OPTION>
											  </SELECT>
											</td>
										</tr>
									</table>
								  </div>
								</td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="AnclajeSup1"><?php echo $Texto[17];?></LABEL>
								</td>
								<td class="check_form">
									<INPUT value=1 type="checkbox" name="AnclajeSup1" <?php echo ($AnclajeSup1==1)?"checked":"";?> onclick='AlternarVisualizacion("optAnclajeSup1");'/>
									<SELECT NAME="AnclajeSup2" SIZE=1 style="margin-left:30px;">
										<OPTION <?php echo ($AnclajeSup2 == 1)?"SELECTED":"";?> VALUE=1>Delantero</OPTION>
										<OPTION <?php echo ($AnclajeSup2 == 2)?"SELECTED":"";?> VALUE=2>Trasero</OPTION>
									</SELECT>
								</td>
								<td class="opcional_form">
									<div id="optAnclajeSup1" style="display:<?php echo ($AnclajeSup1==1)?"none":"block";?>;">
										<table>
											<tr style="visibility:<?php echo ($Texto[18]!="")?"visible":"hidden";?>">
												<td style="width:105px;"><?php echo $Texto[18];?></td>
												<td>
												  <SELECT NAME="AnclajeSup1AS" SIZE=1 onchange='NotVisualizacion("optAnclajeSup1ASMotivo", $(this).val(), "1");'>
                                                    <OPTION <?php echo ($AnclajeSup1AS == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
													<OPTION <?php echo ($AnclajeSup1AS == 1 || empty($Texto[18]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
													<OPTION <?php echo ($AnclajeSup1AS == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
													<OPTION <?php echo ($AnclajeSup1AS == 3)?"SELECTED":"";?> VALUE=3>Añadir</OPTION>
												  </SELECT>
												</td>
                                                <td>
	                                             <div id="optAnclajeSup1ASMotivo" style="display:<?php echo ($AnclajeSup1AS == 1)?"none":"block";?>; padding-left:2px;">
												  <INPUT TYPE="text" NAME="AnclajeSup1ASMotivo" VALUE="<?php echo $AnclajeSup1ASMotivo;?>" maxlength="25" size="25"/>
    	                                         </div>
                                                </td>
											</tr>
											<tr style="visibility:<?php echo ($Texto[19]!="")?"visible":"hidden";?>">
												<td style="width:105px;"><?php echo $Texto[19];?></td>
												<td colspan="2">
												  <SELECT NAME="AnclajeSup1Pasador" SIZE=1>
                                                    <OPTION <?php echo ($AnclajeSup1Pasador == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
													<OPTION <?php echo ($AnclajeSup1Pasador == 1 || empty($Texto[19]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
													<OPTION <?php echo ($AnclajeSup1Pasador == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
													<OPTION <?php echo ($AnclajeSup1Pasador == 3)?"SELECTED":"";?> VALUE=3>Añadir</OPTION>
												  </SELECT>
												</td>
											</tr>
											<tr style="visibility:<?php echo ($Texto[20]!="")?"visible":"hidden";?>">
												<td style="width:105px;"><?php echo $Texto[20];?></td>
												<td>
                                                  <SELECT NAME="AnclajeSup1Bulon" SIZE=1 onchange='OptVisualizacion("optAnclajeSup1Bulon", $(this).val());'>
                                                    <OPTION <?php echo ($AnclajeSup1Bulon == 0)?"SELECTED":"";?> VALUE=0>No OK</OPTION>
													<OPTION <?php echo ($AnclajeSup1Bulon == 1 || empty($Texto[20]))?"SELECTED":"";?> VALUE=1>OK</OPTION>
													<OPTION <?php echo ($AnclajeSup1Bulon == 2)?"SELECTED":"";?> VALUE=2>Colocar</OPTION>
													<OPTION <?php echo ($AnclajeSup1Bulon >= 3)?"SELECTED":"";?> VALUE="">Añadir</OPTION>
												  </SELECT>
												</td>
												<td>
												  <div id="optAnclajeSup1Bulon" style="display:<?php echo ($AnclajeSup1Bulon < 3)?"none":"block";?>;">                                                  
                                                  <?php if ($MarcaLV==2) { // ODG, 05.01.15 Sólo para VECTALINE ?>
													1:<input type="radio" name="AnclajeSup1BulonA" value="3" <?php echo ($AnclajeSup1Bulon==3)?"checked":"";?>/>
													2:<input type="radio" name="AnclajeSup1BulonA" value="4" <?php echo ($AnclajeSup1Bulon==4)?"checked":"";?>/>
													3:<input type="radio" name="AnclajeSup1BulonA" value="5" <?php echo ($AnclajeSup1Bulon==5)?"checked":"";?>/>
                                                  <?php } ?>
												  </div>
												</td>                                                
											</tr>
										</table>
									</div>
								</td>
							</tr>
							<tr>
								<td class="etiqueta_form">
									<LABEL for="Amortiguador"><?php echo $Texto[21];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Amortiguador" <?php echo ($Amortiguador==1)?"checked":"";?> onclick='AlternarVisualizacion("optAmortiguador");' />
								</td>
								<td class="opcional_form">
									<div id="optAmortiguador" style="display:<?php echo ($Amortiguador==1)?"none":"block";?>;">
										<table>
											<tr style="visibility:<?php echo ($Texto[22]!="")?"visible":"hidden";?>">
												<td style="width:105px;"><?php echo $Texto[22];?></td>
												<td>
													<input value=1 type="checkbox" name="AmortiguadorMuelle" <?php echo ($AmortiguadorMuelle==1 || empty($Texto[22]))?"checked":"";?> onclick='AlternarVisualizacion("optAmortiguadorMuelleMotivo");' />
												</td>
                                                <td>
												  <div id="optAmortiguadorMuelleMotivo" style="display:<?php echo ($AmortiguadorMuelle==1 || empty($Texto[22]))?"none":"block";?>;">
													<input type="text" name="AmortiguadorMuelleMotivo" value="<?php echo $AmortiguadorMuelleMotivo;?>" />
												  </div>
                                                </td>
											</tr>
											<tr style="visibility:<?php echo ($Texto[23]!="")?"visible":"hidden";?>">
												<td style="width:105px;"><?php echo $Texto[23];?></td>
												<td>
													<input value=1 type="checkbox" name="AmortiguadorPasador" <?php echo ($AmortiguadorPasador==1 || empty($Texto[23]))?"checked":"";?> />
												</td>
                                                <td></td>
											</tr>
											<tr style="visibility:<?php echo ($Texto[24]!="")?"visible":"hidden";?>">
												<td style="width:105px;"><?php echo $Texto[24];?></td>
												<td>
													<input value=1 type="checkbox" name="AmortiguadorBulon" <?php echo ($AmortiguadorBulon==1 || empty($Texto[24]))?"checked":"";?> />
												</td>
                                                <td></td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[25]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="TornilleriaPletina"><?php echo $Texto[25];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="TornilleriaPletina" <?php echo ($TornilleriaPletina==1 || empty($Texto[25]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[26]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="TornilleriaApriete"><?php echo $Texto[26];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="TornilleriaApriete" <?php echo ($TornilleriaApriete==1 || empty($Texto[26]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[27]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="Ensayo"><?php echo $Texto[27];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Ensayo" <?php echo ($Ensayo==1 || empty($Texto[27]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[28]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="Escalera"><?php echo $Texto[28];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Escalera" <?php echo ($Escalera==1 || empty($Texto[28]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[29]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="Interferencia"><?php echo $Texto[29];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Interferencia" <?php echo ($Interferencia==1 || empty($Texto[29]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
							<tr style="visibility:<?php echo ($Texto[30]!="")?"visible":"hidden";?>">
								<td class="etiqueta_form">
									<LABEL for="Oxidacion"><?php echo $Texto[30];?></LABEL>
								</td>
								<td class="check_form">
									<input value=1 type="checkbox" name="Oxidacion" <?php echo ($Oxidacion==1 || empty($Texto[30]))?"checked":"";?> /> 
								</td>
								<td class="opcional_form">
								</td>
							</tr>
		<?php
				
		unset ($LinCod,$LinPle,$MarcaLV,$Texto, $NumeroCable,$NumeroSerie,$NAbsorbedor,$NTrompa,$NTramo,$EstadoCable,$CantidadCable,$EstadoCableMotivo,
			$TipoPletina,$Tension,$ConfPletina,$NombrePletina1,$NombrePletina2,$NombrePletina3,$NombrePletina4,$CampoPletina1,
			$CampoPletina2,$CampoPletina3,$CampoPletina4,$VarillasRoscadas,$Cartel,$AnclajeInf1AIMotivo,$AnclajeInf1,$AnclajeInf1AI,
			$AnclajeInf1Tensor,$AnclajeInf1Perrillos,$AnclajeInf1Guardacabos,$AnclajeInf1Tuercas,$AnclajeSup1,$AnclajeSup2,$AnclajeSup1AS,
			$AnclajeSup1ASMotivo,$AnclajeSup1Pasador,$AnclajeSup1Bulon,$AmortiguadorMuelleMotivo,$Amortiguador,$AmortiguadorMuelle,
			$AmortiguadorPasador,$AmortiguadorBulon,$TornilleriaPletina,$TornilleriaApriete,$Ensayo,$Escalera,$Interferencia,$Oxidacion);

	}	
}
unset($Path, $Archivo);
if ($conn)
	mysql_close($conn);
?>