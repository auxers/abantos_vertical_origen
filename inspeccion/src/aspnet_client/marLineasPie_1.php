<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");			// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class	
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

if (isset($_GET["grupo"]))
	$_SESSION['Grupo'] = $_GET["grupo"]; 

// Obtengo variable del ancho
$ancho_grid = 500;
$alto_grid = 100;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn); 
// Write the SQL Query
$grid->SelectCommand = 'SELECT * FROM LiteralPie WHERE Tipo=4 AND Grupo='.$_SESSION['Grupo'];
$grid->table='LiteralPie';		  	 // set the table where add-del-edit data
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';			 // set the encoding
$grid->dataType = 'json';			  // set the ouput format to json
$grid->setColModel();				  // Let the grid create the model
$grid->setUrl('marLineasPie_1.php');  // Set the url from where we obtain the data

$grid->setColProperty("Id", array("label"=>"Id", "hidden"=>true,"width"=>25));
$grid->setColProperty("Tipo", array("label"=>"Tipo", "hidden"=>true,"width"=>30));
$grid->setColProperty("Grupo", array("label"=>"Grupo", "hidden"=>true,"width"=>30));
$grid->setColProperty("Texto", array("label"=>"Texto", "width"=>450, "editoptions"=>array("maxlength"=>40)));
$grid->setGridOptions(array(
    "caption"=>"Pie Certificado",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
    "width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Pies",
	"emptyrecords"=>"No hay Pies",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>true
));

// Enable navigator
$grid->navigator = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>false,"view"=>false,"search"=>true,"excel"=>false,"refresh"=>false));
$grid->setNavOptions("search",array("multipleSearch"=>false));
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
$grid->inlineNav = true; 
$grid->inlineNavOptions("navigator", array("add"=>false,"edit"=>true));
$grid->callGridMethod("#grid", 'setFrozenColumns');
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
// Recargar
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function(){
		$('#grid').trigger('reloadGrid');}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->renderGrid('#grid','#pager',true, null, null, true,true);
$conn = null;
?>