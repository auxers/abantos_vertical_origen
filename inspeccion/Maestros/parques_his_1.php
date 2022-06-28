<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once("../inc/function/funcs.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");		// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");	 // include the SQL Server driver class
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "") 
	session_start();

// Obtengo variable del ancho
$ancho_grid = 965;
$alto_grid = 420;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");

// Create the jqGrid instance
$grid = new jqGridRender($conn);
//$grid->debug = true;
// OD, 04.07.13 Gonzalo pide que al exportar en excel deben de salir todos los campos, por ello incluyo Tramo, Trompa y Absorbedor
$Query = "(SELECT MAX(DISTINCT(LC0.Fecha)) FROM ListaControl LC0 WHERE LC0.Tipo='M' AND LC0.IdLinea=L.Id) AS Fecha_Montaje,
	(SELECT LA.Id FROM LineasPletina LA INNER JOIN Pletinas pA ON pA.Id = LA.IdPletina WHERE pA.Tipo=2 AND LA.IdLinea=L.Id) AS IdServicio,
	(SELECT L0.NumeroSerie FROM LineasPletina L0 INNER JOIN Pletinas p0 ON p0.Id = L0.IdPletina WHERE p0.Tipo=2 AND L0.IdLinea=L.Id) AS LineaServicio,
	(SELECT L1.NumeroCable FROM LineasPletina L1 INNER JOIN Pletinas p1 ON p1.Id = L1.IdPletina WHERE p1.Tipo=2 AND L1.IdLinea=L.Id) AS CableServicio,
	(SELECT L2.NTramo FROM LineasPletina L2 INNER JOIN Pletinas p2 ON p2.Id = L2.IdPletina WHERE p2.Tipo=2 AND L2.IdLinea=L.Id) AS TramoServicio,
	(SELECT L3.NTrompa FROM LineasPletina L3 INNER JOIN Pletinas p3 ON p3.Id = L3.IdPletina WHERE p3.Tipo=2 AND L3.IdLinea=L.Id) AS TrompaServicio,
	(SELECT L4.NAbsorbedor FROM LineasPletina L4 INNER JOIN Pletinas p4 ON p4.Id = L4.IdPletina WHERE p4.Tipo=2 AND L4.IdLinea=L.Id) AS AbsorbedorServicio,
	(SELECT CONCAT(YEAR(LC1.Fecha)+1,'-',MONTH(LC1.Fecha)) FROM ListaControl LC1
		WHERE LC1.Tipo='R' AND LC1.IdLinea=L.Id AND LC1.LTipo=2 ORDER BY LC1.Fecha DESC LIMIT 0,1) AS Prox_Rev_Servicio,
	(SELECT LA.Id FROM LineasPletina LA INNER JOIN Pletinas pA ON pA.Id = LA.IdPletina WHERE pA.Tipo=1 AND LA.IdLinea=L.Id) AS IdNacelle,
	(SELECT L5.NumeroSerie FROM LineasPletina L5 INNER JOIN Pletinas p5 ON p5.Id = L5.IdPletina WHERE p5.Tipo=1 AND L5.IdLinea=L.Id) AS LineaNacelle,
	(SELECT L6.NumeroCable FROM LineasPletina L6 INNER JOIN Pletinas p6 ON p6.Id = L6.IdPletina WHERE p6.Tipo=1 AND L6.IdLinea=L.Id) AS CableNacelle,
	(SELECT L7.NTramo FROM LineasPletina L7 INNER JOIN Pletinas p7 ON p7.Id = L7.IdPletina WHERE p7.Tipo=1 AND L7.IdLinea=L.Id) AS TramoNacelle,
	(SELECT L8.NTrompa FROM LineasPletina L8 INNER JOIN Pletinas p8 ON p8.Id = L8.IdPletina WHERE p8.Tipo=1 AND L8.IdLinea=L.Id) AS TrompaNacelle,
	(SELECT L9.NAbsorbedor FROM LineasPletina L9 INNER JOIN Pletinas p9 ON p9.Id = L9.IdPletina WHERE p9.Tipo=1 AND L9.IdLinea=L.Id) AS AbsorbedorNacelle,
	(SELECT CONCAT(YEAR(LC2.Fecha)+1,'-',MONTH(LC2.Fecha)) FROM ListaControl LC2
		WHERE LC2.Tipo='R' AND LC2.IdLinea=L.Id AND LC2.LTipo=1 ORDER BY LC2.Fecha DESC LIMIT 0,1) AS Prox_Rev_Nacelle";
$grid->SelectCommand = "SELECT L.Id, L.IdParque, L.NumeroTorre, L.TipoAerogenerador, L.IdAltura,".$Query.
	" FROM Lineas L WHERE L.IdParque=?";
$grid->ExportCommand = "SELECT L.Id, L.IdParque, L.NumeroTorre, TA.Nombre, AL.Nombre,".$Query.
	" FROM Lineas L JOIN TAerogenerador TA ON TA.Id=L.TipoAerogenerador JOIN Alturas AL ON AL.Id=L.IdAltura WHERE L.IdParque=?";

// Set the table where add-del-edit data
$grid->table = 'Lineas';
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';
$grid->dataType = 'json';
$grid->setColModel(null, ($param = array($_SESSION['IdParque'] = isset($_GET["id"]) ? $_GET["id"] : 0)));	// Let the grid create the model
$grid->setUrl("parques_his_1.php");	  // Set the url from where we obtain the data
// ODG, 14.01.13, Hago que el Número de Torre sea Editable, y que el valor que nos de sea según el IdParque...
//	Debo de tener en cuenta que si estamos modificando una Línea, Podemos cambiar el Nº Torre y Tipo AEG
if (($oper = jqGridUtils::GetParam("oper")) != "edit")
{   // Consulta, Añadir, Borrar, ...
	$Query = jqGridDB::query($conn, "SELECT MAX(L.NumeroTorre) AS MaxTorre, L.TipoAerogenerador, L.IdAltura FROM Lineas L WHERE L.IdParque=".$_SESSION['IdParque']);
	if (($row = jqGridDB::fetch_num($Query, true, $conn)))
	{
		$NumTorre = (is_numeric($row[0])) ? sprintf("%03s",(int)$row[0]+1) : "";
		$TipoAEG = $row[1];
		$Altura = $row[2];
	}
	else
		$NumTorre = $TipoAEG = $Altura = 1;
}
else 
{   // Modifico
	$NumTorre = jqGridUtils::Strip($_REQUEST["NumeroTorre"]);
	$TipoAEG = jqGridUtils::Strip($_REQUEST["TipoAerogenerador"]);
	$Altura = jqGridUtils::Strip($_REQUEST["Altura"]);
}

// ID
$grid->setColProperty("Id", array("label"=>"Id","hidden"=>true));
// ID Parque
$grid->setColProperty("IdParque", array("label"=>"Parque","hidden"=>true,"editoptions"=>array("defaultValue"=>$_SESSION['IdParque'])));
// Núm. Torre
$grid->setColProperty("NumeroTorre", array("label"=>"Torre","align"=>"right","width"=>50,"editoptions"=>array("defaultValue"=>$NumTorre,"maxlength"=>8)));
// Tipo AEG
$grid->setColProperty("TipoAerogenerador", array("label"=>"Tipo de AEG","align"=>"center","width"=>175,"editoptions"=>array("defaultValue"=>$TipoAEG,"onchange"=>"fCompruebaAEG(id);")));
$grid->setSelect("TipoAerogenerador", "SELECT Id, Nombre FROM TAerogenerador");
// Altura
$grid->setColProperty("IdAltura", array("label"=>"Altura","align"=>"center","width"=>55,"editoptions"=>array("defaultValue"=>$Altura)));
$grid->setSelect("IdAltura", "SELECT Id, Nombre FROM Alturas");
// Fecha Montaje
$grid->setColProperty("Fecha_Montaje", array("label"=>"Montaje","align"=>"center","formatter"=>"date","formatoptions"=>array("srcformat"=>"Y-m-d","newformat"=>"d-m-y"),"width"=>70,"editable"=>false));
// Servicio, ODG 14.01.12 Hago que Nº Línea y Nº Cable no sea obligatorio introducir datos
$grid->setColProperty("IdServicio", array("label"=>"Id","hidden"=>true));
$grid->setColProperty("LineaServicio", array("label"=>"Nº Línea","align"=>"left","width"=>140,"edittype"=>"text","editoptions"=>array("maxlength"=>40)));
$grid->setColProperty("CableServicio", array("label"=>"Nº Cable","align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("TramoServicio", array("label"=>"Nº Tramo","hidden"=>($Hidden = ($oper == "excel") ? false : true ),"align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("TrompaServicio", array("label"=>"Nº Trompa","hidden"=>$Hidden,"align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("AbsorbedorServicio", array("label"=>"Nº Absorbedor","hidden"=>$Hidden,"align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("Prox_Rev_Servicio", array("label"=>"Prox. Rev.","align"=>"center","formatter"=>"date", "formatoptions"=>array("srcformat"=>"Y-m","newformat"=>"M-Y"),"width"=>70,"editable"=>false));
// Nacelle
$grid->setColProperty("IdNacelle", array("label"=>"Id","hidden"=>true));
$grid->setColProperty("LineaNacelle", array("label"=>"Nº Línea","align"=>"left","width"=>140,"edittype"=>"text","editrules"=>array("required"=>false),"editoptions"=>array("maxlength"=>40)));
$grid->setColProperty("CableNacelle", array("label"=>"Nº Cable","align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("TramoNacelle", array("label"=>"Nº Tramo","hidden"=>$Hidden,"align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("TrompaNacelle", array("label"=>"Nº Trompa","hidden"=>$Hidden,"align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("AbsorbedorNacelle", array("label"=>"Nº Absorbedor","hidden"=>$Hidden,"align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("maxlength"=>25)));
$grid->setColProperty("Prox_Rev_Nacelle", array("label"=>"Prox. Rev.","align"=>"center","formatter"=>"date", "formatoptions"=>array("srcformat"=>"Y-m","newformat"=>"M-Y"),"width"=>70,"editable"=>false));
$grid->setGridOptions(array(
    "caption"=>"",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>200,			// ODG, 17.03.14, Se amplia el máximo de registros
	"width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_yellow",
	"recordtext"=>"{2} torres",
	"emptyrecords"=>"No hay registros",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>true,
	"postData"=>array("id"=>$_SESSION['IdParque'])
));

// Enable navigator
$grid->navigator = true;
$grid->getLastInsert = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>false,"excel"=>false,"refresh"=>false));
$grid->setNavOptions("search",array("multipleSearch"=>false));
$grid->inlineNav = true;
$grid->inlineNavOptions("navigator", array("edit"=>true));
$grid->inlineNavOptions("add", array("addRowParams"=>array("oneditfunc"=>"js:function(id){ fCompruebaAEG(id); }")));
$grid->inlineNavOptions("edit", array("oneditfunc"=>"js:function(id){ fCompruebaAEG(id); }"));
$grid->callGridMethod("#grid", "setFrozenColumns");
// Evento para que después de grabar recargue el Grid
//$MyEvent = <<<AFTEREDIT
//$("#grid").bind("jqGridInlineAfterSaveRow",
//	function (event,rowid,response)
//	{
//		$("#grid").trigger("reloadGrid");
//	});
//AFTEREDIT;
//$grid->setJSCode($MyEvent);

// Exportar Excel
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'excel', url:'parques_his_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
// Exportar PDF
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'parques_his_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
// Refresh
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function(){
		$('#grid').trigger('reloadGrid');}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);

// Doble Click, Añadir/Modificar Extintores y Descensores
$form_edit = <<< FORMEDIT
function() {
	var x_w = $(window).width();
	var y_w = $(window).height();
	var dx = Math.round((x_w-875)/2);
	var dy = Math.round((y_w-415)/2);
	var col=$("#grid").jqGrid('getGridParam', 'selrow');
	var rowid=$("#grid").jqGrid('getCell', col, 'Id');
	var url = "Maestros/parques_his_options.php?action=edit&Id="+rowid;
	var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
	iframe.dialog({
		autoOpen: true,
		title: "Modificar Torre del Parque",
		width: 875,
		height: 415,
		bgiframe: true,
		modal: true,
		position: [dx,dy],
		closeOnEscape: false,
		closeText: '',
		draggable: false,
		resizable: false,
		stack: true,
		zIndex: 3999,
		open: function(event, ui) {
			window.parent.$(".ui-dialog-titlebar-close").hide();
			window.parent.$("#ui-dialog-title-vent_uic").html('Modificar Torre del Parque <i>');
		},
		buttons: {
			"Guardar": function() {
				var d0=$(this).contents().find('#Id').val();
				var d1=$(this).contents().find('#NTorre').val();
				var d2=$(this).contents().find('#TipoAEG').val();
				var d3=$(this).contents().find('#Altura').val();
				var d4=$(this).contents().find('#Marca').val();
				var d5=$(this).contents().find('#LineaServicio').val();
				var d6=$(this).contents().find('#CableServicio').val();
				var d7=$(this).contents().find('#LineaNacelle').val();
				var d8=$(this).contents().find('#CableNacelle').val();
					
				$.ajax({
					type: "GET", url:"parques_his_actions.php",
					data: {act:'edit', v0:d0, v1:d1, v2:d2, v3:d3, v4:d4, v5:d5, v6:d6, v7:d7, v8:d8},
					success: function() {
						$("#grid").trigger("reloadGrid");
					},
					dataType: "text", async:false
				});
				window.parent.$(this).dialog('close');
			},
			"Cancelar": function() {
				window.parent.$(this).dialog('close');
			}
		}
   }).width(875).height(415);
}
FORMEDIT;
$grid->setGridEvent("ondblClickRow", $form_edit);

// ODG, 14.01.13, Aceptamos que se pueda modificar el Nº Torre
//	Además optimizo el Código, para que "add" y "edit" se haga en la misma parte "porque tiene campos comunes"
if ($oper == "add" || $oper == "edit")
{
	if ($oper == "add")
	{   // En el Alta, debo de obtener el Último ID de la BBDD
		$Query = $conn->query("SHOW TABLE STATUS LIKE 'Lineas'");
		if (($row = $Query->fetch (PDO::FETCH_ASSOC)))
			$lastid = $row["Auto_increment"];
	}
	else
		$lastid = jqGridUtils::GetParam("Id");
	
	$N_Parque = jqGridUtils::Strip($_REQUEST["IdParque"]);
	$N_Torre = fNumTorre(jqGridUtils::Strip($_REQUEST["NumeroTorre"]));
	$TipoAEG = jqGridUtils::Strip($_REQUEST["TipoAerogenerador"]);
	$Altura = jqGridUtils::Strip($_REQUEST["IdAltura"]);
	$IdPle = $Trompa = $Tramo = $Absorbedor = array();
	$N_ln = jqGridUtils::Strip($_REQUEST["LineaNacelle"]);	// Línea Nacelle
	$C_ln = jqGridUtils::Strip($_REQUEST["CableNacelle"]);
	$IdPle[] = jqGridUtils::Strip($_REQUEST["IdNacelle"]);
	$Tramo[] = jqGridUtils::Strip($_REQUEST["TramoNacelle"]);
	$Trompa[] = jqGridUtils::Strip($_REQUEST["TrompaNacelle"]);
	$Absorbedor[] = jqGridUtils::Strip($_REQUEST["AbsorbedorNacelle"]);
	$N_ls = jqGridUtils::Strip($_REQUEST["LineaServicio"]);	// Línea Servicio
	$C_ls = jqGridUtils::Strip($_REQUEST["CableServicio"]);
	$IdPle[] = jqGridUtils::Strip($_REQUEST["IdServicio"]);
	$Tramo[] = jqGridUtils::Strip($_REQUEST["TramoServicio"]);
	$Trompa[] = jqGridUtils::Strip($_REQUEST["TrompaServicio"]);
	$Absorbedor[] = jqGridUtils::Strip($_REQUEST["AbsorbedorNacelle"]);
	
	// Obtenemos el TipoAerogeneradorGAMESA.
	$TipoGAMESA = "";
	$Query = $conn->query("SELECT TA.Prefijo, A.Nombre FROM TAerogenerador TA, Alturas A WHERE 
		TA.Id=".$TipoAEG." AND A.Id=".$Altura);
	if (($row = $Query ->fetch (PDO::FETCH_ASSOC)))
		$TipoGAMESA = $row["Prefijo"]." ".$row["Nombre"];
	$grid->setAfterCrudAction($oper, 
		"UPDATE Lineas SET NumeroTorre='".$N_Torre."', TipoAerogeneradorGAMESA='".$TipoGAMESA."' WHERE Id=".$lastid);

	// Se puede dar el caso de que podamos modificar la Pletina de un AEG, pero también cambiar el Tipo de AEG y poder tener
	//	Línea de Servicio y Nacelle ó sólo Nacelle, por ello lo que hago cada vez que se edite es borrar las pletinas que tengan
	//	y según proceda añadir Nacelle y Servicio
	if ($oper == "edit")
		$grid->setAfterCrudAction($oper, "DELETE FROM LineasPletina WHERE IdLinea=".$lastid);
	
	// 1ro. Creamos Línea Nacelle
	//	Comprobamos en TAeroPletinas si éste AEG debe de tener Línea de Nacelle
	$LVt1 = $LVt2 = 0;
	$Query = $conn->query ("SELECT TA.IdPletina FROM TAeroPletinas TA JOIN Pletinas p ON p.Id = TA.IdPletina
		WHERE TA.IdTipoAEG=".$TipoAEG." AND p.Tipo=1");
	if (($row = $Query->fetch (PDO::FETCH_ASSOC)))
		$LVt1 = $row["IdPletina"];
	// OD, 14.02.13, En un Montaje $N_ln y $C_ln puede ir en blanco, por eso admito que pueda ser blanco
	if ($LVt1 > 0)
	{
		if (is_numeric($IdPle[0]))
			$grid->setAfterCrudAction($oper, "INSERT INTO LineasPletina (Id,IdLinea,IdPletina, NumeroSerie,NumeroCable,NTrompa,NAbsorbedor,NTramo) VALUES (?,?,?, ?,?,?,?,?)",array($IdPle[0],$lastid,$LVt1, $N_ln,$C_ln,$Trompa[0],$Absorbedor[0],$Tramo[0]));
		else
			$grid->setAfterCrudAction($oper, "INSERT INTO LineasPletina (IdLinea,IdPletina, NumeroSerie,NumeroCable) VALUES (?,?,?,?)",array($lastid,$LVt1,$N_ln,$C_ln));
	}
	
	// 2do. Creamos Línea Servicio, si procede
	//	Comprobamos en TAeroPletinas si éste AEG debe de tener Línea de Servicio
	$Query = $conn->query ("SELECT TA.IdPletina FROM TAeroPletinas TA JOIN Pletinas p ON p.Id = TA.IdPletina
		WHERE TA.IdTipoAEG=".$TipoAEG." AND p.Tipo=2");
	if (($row = $Query->fetch (PDO::FETCH_ASSOC)))
		$LVt2 = $row["IdPletina"];
	// OD, 14.02.13, En un Montaje $N_ls y $C_ls puede ir en blanco, por eso admito que pueda ser blanco
	if ($LVt2 > 0)
	{
		if (is_numeric($IdPle[1]))
			$grid->setAfterCrudAction($oper, "INSERT INTO LineasPletina (Id,IdLinea,IdPletina, NumeroSerie,NumeroCable,NTrompa,NAbsorbedor,NTramo) VALUES (?,?,?, ?,?,?,?,?)",array($IdPle[1],$lastid,$LVt2, $N_ls,$C_ls,$Trompa[1],$Absorbedor[1],$Tramo[1]));
		else
			$grid->setAfterCrudAction($oper, "INSERT INTO LineasPletina (IdLinea,IdPletina, NumeroSerie,NumeroCable) VALUES (?,?,?,?)",array($lastid,$LVt2,$N_ls,$C_ls));
	}
	
	unset($LVt1,$LVt2, $IdPle,$Trompa,$Tramo,$Absorbedor);
}
else if($oper == "del")
{   // Borramos las Pletinas de la Línea
	$grid->setAfterCrudAction($oper, "DELETE FROM LineasPletina WHERE IdLinea=".($IdTmp = jqGridUtils::GetParam("Id")));
	$grid->setAfterCrudAction($oper, "DELETE FROM LineasExtintor WHERE IdLinea=".$IdTmp);
	$grid->setAfterCrudAction($oper, "DELETE FROM LineasDescensor WHERE IdLinea=".$IdTmp);
}
else if($oper == "pdf")
{
	ob_clean();
	$grid->setPdfOptions(array(
		"header"=>true,
		"margin_top"=>20,
		"margin_left"=>12,
		"page_orientation"=>"L",
		"author"=>"Abantos Vertical",
		"creator"=>"Abantos Vertical",
		"title"=>"Historico de Parque",
		"subject"=>"Abantos Vertical, S.L.",
		"keywords"=>"parques",
		"font_monospaced"=>"dejavusans",
		"font_name_main"=>"dejavusans",
		"font_data_main"=>"dejavusans",
		//logo
		"header_logo"=>"logoAV.jpg",
		"header_logo_width"=>20,
		"header_title"=>"Administración de Parque",
		"header"=>true,
		"font_size_main"=>18,
		//grid
		"reprint_grid_header"=>true,
		"grid_head_color"=>"#ffd50c",
		"grid_header_height"=>8,
		"grid_head_text_color"=>"#111112",
		"grid_draw_color"=>"#ffd50c",
		"grid_alternate_rows"=>true
	));
}

// Set grouping header Servicio using callGridMethod
$grid->callGridMethod("#grid", "setGroupHeaders", array (
	array (
		"useColSpanStyle"=>true,
		"groupHeaders"=>array (
			array(
				"startColumnName"=>"LineaServicio",
				"numberOfColumns"=>6,
				"titleText"=>"<em>Línea de Servicio</em>"
			),
			array(
				"startColumnName"=>"LineaNacelle",
				"numberOfColumns"=>6,
				"titleText"=>"<em>Línea Nacelle</em>"
			)
		)
)));

$grid->renderGrid("#grid","#pager",true, null,$param, true,true);
$conn = null;
?>