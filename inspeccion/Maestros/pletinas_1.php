<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");		// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");	 // include the SQL Server driver class
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

// Obtengo variable del ancho
$ancho_grid = 1004;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn); 
// Write the SQL Query, ODG 15.01.12, Quito el Campo4 y PrecioPletina4
$grid->SelectCommand = 'SELECT * FROM Pletinas';
$grid->ExportCommand = 'SELECT Id,Nombre,Tipo,Gamesa, Campo1,Campo2,Campo3, PrecioPletina1,PrecioPletina2,PrecioPletina3 FROM Pletinas';
// set the table where add-del-edit data
$grid->table = 'Pletinas';
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';			// set the encoding
$grid->dataType = 'json';			 // set the ouput format to json
$grid->setColModel();			     // Let the grid create the model
$grid->setUrl("pletinas_1.php");	  // Set the url from where we obtain the data

$grid->setColProperty("Id", array("label"=>"Código","editable"=>false,"hidden"=>true,"align"=>"right","width"=>50));
$grid->setColProperty("Tipo", array("label"=>"Tipo","width"=>85));
$grid->setSelect("Tipo",array(1=>"Nacelle",2=>"Servicio"), true, true, false);
$grid->setColProperty("Nombre", array("label"=>"Nombre","width"=>170, "editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid->setColProperty("Gamesa", array("label"=>"Prefijo","width"=>115, "editoptions"=>array("size"=>15,"maxlength"=>15)));
$grid->setColProperty("Campo1", array("label"=>"Pletina 1","width"=>190, "classes"=>"pletinas-nombre", "editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid->setColProperty("Campo2", array("label"=>"Pletina 2","width"=>190, "classes"=>"pletinas-nombre", "editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid->setColProperty("Campo3", array("label"=>"Pletina 3","width"=>190, "classes"=>"pletinas-nombre", "editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid->setColProperty("Campo4", array("label"=>"Pletina 4","width"=>190, "hidden"=>true,"classes"=>"pletinas-nombre", "editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid->setColProperty("PrecioPletina1", array("label"=>"Pletina 1","width"=>90,"align"=>"right","classes"=>"pletinas-precio","formatter"=>"number"));
$grid->setColProperty("PrecioPletina2", array("label"=>"Pletina 2","width"=>90,"align"=>"right","classes"=>"pletinas-precio","formatter"=>"number"));
$grid->setColProperty("PrecioPletina3", array("label"=>"Pletina 3","width"=>90,"align"=>"right","classes"=>"pletinas-precio","formatter"=>"number"));
$grid->setColProperty("PrecioPletina4", array("label"=>"Pletina 4","width"=>90,"align"=>"right","hidden"=>true, "classes"=>"pletinas-precio","formatter"=>"number"));
$grid->setGridOptions(array(
    "caption"=>"Pletinas",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
    "width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Pletinas",
	"emptyrecords"=>"No hay Pletinas",
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
		$('#grid').jqGrid('excelExport',{tag:'excel', url:'pletinas_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
// Exportar PDF
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'pletinas_1.php'});}"
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

if(($oper = jqGridUtils::GetParam("oper")) == "pdf")
{
	ob_clean();
	$grid->setPdfOptions(array(
		"header"=>true,
		"margin_top"=>20,
		"margin_left"=>12,
		"page_orientation"=>"L",
		"author"=>"Abantos",
		"creator"=>"Abantos",
		"title"=>"Pletinas",
		"subject"=>"Abantos Vertical, S.L.",
		"keywords"=>"Pletinas",
		"font_monospaced"=>"dejavusans",
		"font_name_main"=>"dejavusans",
		"font_data_main"=>"dejavusans",
		//logo
		"header_logo"=>"logoAV.jpg",
		"header_logo_width"=>20,
		"header_title"=>"Pletinas",
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
// Set grouping header using callGridMethod
$grid->callGridMethod("#grid", "setGroupHeaders", array( 
	array("useColSpanStyle"=>true,
		"groupHeaders"=>array(
			array("startColumnName"=>'Campo1', "numberOfColumns"=>4, "titleText"=>'Nombres'),
			array("startColumnName"=>'PrecioPletina1', "numberOfColumns"=>4,"titleText"=>'Precios')
		)
	)
));

$grid->renderGrid("#grid","#pager",true, null, null, true,true);
$conn = null;
?>