<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");			// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class	
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

//Obtengo variable del ancho
$ancho_grid = 450;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn); 
// Write the SQL Query
$grid->SelectCommand = 'SELECT * FROM Alturas';
$grid->table = 'Alturas';			  // set the table where add-del-edit data
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';			 // set the encoding
$grid->dataType = 'json';			  // set the ouput format to json
$grid->setColModel();				  // Let the grid create the model
$grid->setUrl('alturas_1.php');	    // Set the url from where we obtain the data
// Columna, para Editar y Borrar Línea.
/* ODG, 31.01.13 Activo que se Borre y Edite desde la botonera principal
$grid->addCol(array(
	"name"=>" ",
	"formatter"=>"actions",
	"editable"=>false,
	"sortable"=>false,
	"resizable"=>false,
	"fixed"=>true,
	"width"=>60,
	"formatoptions"=>array("keys"=>true)
), "first");
*/

$grid->setColProperty("Id", array("label"=>"Codigo","align"=>"right","editable"=>false,"width"=>35));
$grid->setColProperty("Nombre", array("label"=>"Nombre"));
$grid->setGridOptions(array(
    "caption"=>"ALTURAS",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
    "width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Alturas",
	"emptyrecords"=>"No hay Alturas",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>true
));

// ODG, 31.01.13, Activo poder Borrar y Editar desde la botonera principal y no desde la Columna
// Enable navigator
$grid->navigator = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>true,"excel"=>false,"refresh"=>false));
$grid->setNavOptions("search",array("multipleSearch"=>false));
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
$grid->inlineNav = true; 
$grid->inlineNavOptions("navigator", array("edit"=>true));		//array("edit"=>false));
$grid->callGridMethod("#grid", 'setFrozenColumns');

$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'excel', url:'alturas_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);

$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'alturas_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));

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
		"title"=>"Alturas",
		"subject"=>"Abantos Vertical, S.L.",
		"keywords"=>"alturas",
		"font_monospaced"=>"dejavusans",
		"font_name_main"=>"dejavusans",
		"font_data_main"=>"dejavusans",
		//logo
		"header_logo"=>"logoAV.jpg",
		"header_logo_width"=>20,
		"header_title"=>"Alturas",
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

$grid->renderGrid('#grid','#pager',true, null, null, true,true);
$conn = null;
?>