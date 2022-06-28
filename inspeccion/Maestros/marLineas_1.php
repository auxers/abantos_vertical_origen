<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");			// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class	
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

// Obtengo variable del ancho
$ancho_grid = 300;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn); 
// Write the SQL Query
$grid->SelectCommand = 'SELECT * FROM MarcaLin';
$grid->table='MarcaLin';		  	   // set the table where add-del-edit data
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';			 // set the encoding
$grid->dataType = 'json';			  // set the ouput format to json
$grid->setColModel();				  // Let the grid create the model
$grid->setUrl('marLineas_1.php');     // Set the url from where we obtain the data

$grid->setColProperty("Id", array("label"=>"Codigo","align"=>"right","editable"=>false,"width"=>40));
$grid->setColProperty("Nombre", array("label"=>"Nombre", "width"=>75, "editoptions"=>array("maxlength"=>25)));
$grid->setGridOptions(array(
    "caption"=>"Marcas Líneas de Vida",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
    "width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Marcas",
	"emptyrecords"=>"No hay Marcas",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>true
));

// Enable navigator
$grid->navigator = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>true,"excel"=>false,"refresh"=>false));
$grid->setNavOptions("search",array("multipleSearch"=>false));
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
$grid->inlineNav = true; 
$grid->inlineNavOptions("navigator", array("edit"=>true));
$grid->callGridMethod("#grid", 'setFrozenColumns');

// Exportar Excel
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'excel', url:'marLineas_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
// Exportar PDF
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'marLineas_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
// Administrar PIE Certificados Líneas
$viewevent = <<< ONVIEW
function() {
	var getIDs = $("#grid").jqGrid('getDataIDs');				 // Obtenemos todos los ID que estamos viendo
	var rowid  = $("#grid").jqGrid('getGridParam', 'selrow'); 	 // Obtenemos columna seleccionada
	var nombre = $("#grid").jqGrid('getCell',rowid,'Nombre');
	
	if (rowid > 0)
	{
		var title = "Pie Certificado ";
		window.parent.$("#ModalHis").html("<iframe allowtransparency='allowtransparency' id='vent_info' src='Maestros/marLineasPie_0.php?grupo="+rowid+"' width='100%' height='200' frameborder='0' scrolling='no'></iframe>");
		window.parent.$("#ModalHis").dialog({
			title: title + nombre,
			closeText: 'hide',
			resizable: false,
			width:575,
			height:305,
			bgiframe: true,
			modal: true,
			closeOnEscape: false,
			draggable: false,
			stack: true,
			open: function(event, ui) { 
			},
			buttons: [
				{
					text:'Cerrar',
					id: 'Closedialog',
					click: function() {window.parent.$(this).dialog('close');}
				}
			]
		});
	}
	else
	{
		window.parent.$("#AlertHistorico").dialog({
			resizable: false,
			height:150,
			bgiframe: true,
			modal: true,
			closeOnEscape: false,
			closeText: '',
			draggable: false,
			resizable: false,
			stack: true,
			open: function(event, ui) { 
				window.parent.$(".ui-dialog-titlebar-close").hide(); 
				window.parent.$(".ui-dialog-buttonpane").css("cursor","default");
			},
			buttons: {
				'Aceptar': function() {
					window.parent.$(this).dialog('close');
				}
			}
		});
		
		return false;
	}
}
ONVIEW;
$grid->setGridEvent("ondblClickRow", $viewevent);

// Recargar
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function(){
		$('#grid').trigger('reloadGrid');}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);

if(($oper = jqGridUtils::GetParam("oper")) == "pdf")
{
	ob_clean();
	$grid->setPdfOptions(array(
		"header"=>true,
		"margin_top"=>20,
		"margin_left"=>12,
		"page_orientation"=>"P",
		"author"=>"Abantos",
		"creator"=>"Abantos",
		"title"=>"Marcas Lineas de Vida",
		"subject"=>"Abantos Vertical, S.L.",
		"keywords"=>"Líneas",
		"font_monospaced"=>"dejavusans",
		"font_name_main"=>"dejavusans",
		"font_data_main"=>"dejavusans",
		//logo
		"header_logo"=>"logoAV.jpg",
		"header_logo_width"=>20,
		"header_title"=>"Líneas",
		"header"=>true,
		"font_size_main"=>18,
		//grid
		"reprint_grid_header"=>true,
		"grid_head_color"=>"#ffd50c",
		"grid_head_text_color"=>"#111112",
		"grid_draw_color"=>"#ffd50c",
		"grid_alternate_rows"=>true
	));
}
else if ($oper == "add")
{   // Al añadir creamos las líneas del Pie Genéricas
	$IdTmp = jqGridUtils::GetParam("Id");
	$grid->setAfterCrudAction($oper, "INSERT INTO LiteralPie (Tipo,Grupo,Texto) VALUES (4,".$IdTmp.",'')");
	$grid->setAfterCrudAction($oper, "INSERT INTO LiteralPie (Tipo,Grupo,Texto) VALUES (4,".$IdTmp.",'')");
	$grid->setAfterCrudAction($oper, "INSERT INTO LiteralPie (Tipo,Grupo,Texto) VALUES (4,".$IdTmp.",'')");
	$grid->setAfterCrudAction($oper, "INSERT INTO LiteralPie (Tipo,Grupo,Texto) VALUES (4,".$IdTmp.",'')");
}
else if ($oper == "del")
{   // Buscamos y Borrarmos, los Literales de PIE	
	$grid->setAfterCrudAction($oper, "DELETE FROM LiteralPie WHERE Tipo=4 AND Grupo=".jqGridUtils::GetParam("Id"));
}

$grid->renderGrid('#grid','#pager',true, null, null, true,true);
$conn = null;
?>