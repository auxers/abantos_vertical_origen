<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class	
if (session_id() === "")
	session_start();

// Connection to the server
$conn2 = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn2->query("SET NAMES utf8");

$grid2 = new jqGridRender($conn2);  // Create the jqGrid instance
$grid2->SelectCommand = 'SELECT Id, IdTipoAEG, IdPletina, Refuerzo FROM TAeroPletinas WHERE IdTipoAEG = ?';
$grid2->table = 'TAeroPletinas';
$grid2->encoding = 'utf-8';
$grid2->dataType = 'json';
$grid2->setColModel(null, ($params = array($_SESSION['ae_id'])));	// Let the grid create the model
$grid2->setPrimaryKeyId("Id");
$grid2->setUrl('aerogeneradores_pletinas.php');	// Set the url from where we obtain the data

// Columnas
$grid2->setColProperty("Id", array("label"=>"Id","align"=>"right","hidden"=>true,"editable"=>false,"width"=>15));
$grid2->setColProperty("IdTipoAEG", array("label"=>"Aerogenerador","editable"=>true,"hidden"=>true, "editoptions"=>array("defaultValue"=>$_SESSION['ae_id'])));
$grid2->setColProperty("IdPletina", array("label"=>"Pletina"));
$grid2->setSelect("IdPletina", "SELECT Id, Nombre FROM Pletinas");
$grid2->setColProperty("Refuerzo", array("label"=>"GD", "align"=>"left", "width"=>175, "editable"=>true, "edittype"=>"text", "editoptions"=>array("size"=>45,"maxlength"=>20)) );
$grid2->setGridOptions(array(
    "caption"=>"",
    "sortable"=>false,
	"scroll"=>1,
	"rowNum"=>20,
    "width"=>675,
	"height"=>60,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} pletina",
	"emptyrecords"=>"Sin pletinas asociadas",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>false
));

// Enable navigator
$grid2->navigator = true;
$grid2->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>false,"excel"=>false,"refresh"=>false));
$grid2->inlineNav = true;	// Para poder Editar ó Añadir en la Fila...
$grid2->inlineNavOptions("navigator", array("edit"=>true));
$grid2->callGridMethod("#grid2", "setFrozenColumns");
$grid2->callGridMethod("#grid2", "navSeparatorAdd", array("#pager"));

// Refresh
$buttonoptions = array("#pager2",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function() {
		$('#grid2').trigger('reloadGrid');}"
	)
);

$grid2->callGridMethod("#grid2","navButtonAdd",$buttonoptions);
$grid2->renderGrid("#grid2","#pager2",true,null,$params,true,true);
$conn2 = null;
?>