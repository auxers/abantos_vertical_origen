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
// Create the jqGrid instance
$grid2 = new jqGridRender($conn2);
$grid2->SelectCommand = 'SELECT * FROM LineasExtintor WHERE IdLinea = ?';
$grid2->table = 'LineasExtintor';
$grid2->encoding = 'utf-8';
$grid2->dataType = 'json';
$grid2->setColModel(null, ($params = array($_SESSION['ae_id'])));  // Let the grid create the model
$grid2->setPrimaryKeyId("Id");
$grid2->setUrl('parques_his_extintores.php');	// Set the url from where we obtain the data
// Columnas
$grid2->setColProperty("Id", array("hidden"=>true));
$grid2->setColProperty("IdLinea", array("hidden"=>true, "editoptions"=>array("defaultValue"=>$_SESSION['ae_id'])));
$grid2->setColProperty("Localizacion", array("label"=>"Localizacion", "width"=>80));
$grid2->setSelect("Localizacion", "SELECT Id, Nombre FROM Localizacion");
$grid2->setColProperty("NPlaca", array("label"=>"Nº Placa","align"=>"left","width"=>200,"editoptions"=>array("size"=>40,"maxlength"=>40)));
$grid2->setColProperty("Marca", array("label"=>"Marca","width"=>115));
$grid2->setSelect("Marca", "SELECT Id, Nombre FROM MarcaExt WHERE Id > 1");
$grid2->setColProperty("Modelo", array("label"=>"Modelo","width"=>50));
$grid2->setSelect("Modelo", "SELECT Id, Nombre FROM ModeloExt");
$grid2->setColProperty("FechaFabricacion", array("label"=>"Fabricacion","align"=>"left","width"=>50,"editoptions"=>array("size"=>10,"maxlength"=>10)));
$grid2->setColProperty("FechaRetimbrado", array("label"=>"Retimbrado","align"=>"left","width"=>50,"editoptions"=>array("size"=>10,"maxlength"=>10)));
$grid2->setColProperty("AgenteExtintor", array("label"=>"Agente","align"=>"left","width"=>50,
	"editoptions"=>array("size"=>15,"maxlength"=>15,"defaultValue"=>"CO2")));
$grid2->setGridOptions(array(
    "caption"=>"",
    "sortable"=>false,
	"scroll"=>1,
	"rowNum"=>20,
    "width"=>850,
	"height"=>90,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} extintores",
	"emptyrecords"=>"Sin extintores asociados",
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
$grid2->callGridMethod("#grid2", "navSeparatorAdd", array("#pager2"));
// Refresh
$buttonoptions = array("#pager2",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function() {
		$('#grid2').trigger('reloadGrid');}"
	)
);
$grid2->callGridMethod("#grid2","navButtonAdd",$buttonoptions);
$grid2->renderGrid("#grid2","#pager2",true,null,$params,true,true);
$conn2=null;
?>