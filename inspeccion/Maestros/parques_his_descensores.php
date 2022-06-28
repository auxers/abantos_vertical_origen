<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class	
if (session_id() === "")
	session_start();

// Connection to the server
$conn3 = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn3->query("SET NAMES utf8");
// Create the jqGrid instance
$grid3 = new jqGridRender($conn3);
$grid3->SelectCommand = 'SELECT * FROM LineasDescensor WHERE IdLinea = ?';
$grid3->table = 'LineasDescensor';
$grid3->encoding = 'utf-8';
$grid3->dataType = 'json';
$grid3->setColModel(null, ($params = array($_SESSION['ae_id'])));  // Let the grid create the model
$grid3->setPrimaryKeyId("Id");
$grid3->setUrl('parques_his_descensores.php');	   // Set the url from where we obtain the data
// Columnas
$grid3->setColProperty("Id", array("hidden"=>true));
$grid3->setColProperty("IdLinea", array("hidden"=>true, "editoptions"=>array("defaultValue"=>$_SESSION['ae_id'])));
$grid3->setColProperty("NSerie", array("label"=>"Nº Serie","align"=>"left","width"=>195,"edittype"=>"text","editoptions"=>array("size"=>40,"maxlength"=>40)));
$grid3->setColProperty("Marca", array("label"=>"Marca","width"=>95,"editoptions"=>array("onchange"=>"fCompruebaDES(id);")));
$grid3->setSelect("Marca", "SELECT Id, Nombre FROM MarcaDes");
$grid3->setColProperty("Modelo", array("label"=>"Modelo","width"=>85));
$grid3->setSelect("Modelo", "SELECT Id, Nombre FROM ModeloDes");
$grid3->setColProperty("Longitud", array("label"=>"Longitud","align"=>"left","width"=>80,"edittype"=>"text","editoptions"=>array("size"=>20,"maxlength"=>20)));
$grid3->setColProperty("NPrecintoOld", array("label"=>"Precinto Viejo","align"=>"left","width"=>90,"edittype"=>"text","editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid3->setColProperty("NPrecintoNew", array("label"=>"Precinto Nuevo","align"=>"left","width"=>90,"edittype"=>"text","editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid3->setColProperty("TEnvasado", array("label"=>"Envasado","align"=>"left","width"=>65,"edittype"=>"text","editoptions"=>array("size"=>15,"maxlength"=>15)));
$grid3->setColProperty("AnyoFabricacion", array("label"=>"Fabricación","align"=>"left","width"=>75,"edittype"=>"text","editoptions"=>array("size"=>10,"maxlength"=>10)));
$grid3->setColProperty("AnyoFabCuerdaPri", array("label"=>"Año Cuerda Pri.","align"=>"left","width"=>90,"edittype"=>"text","editoptions"=>array("size"=>10,"maxlength"=>10)));
$grid3->setColProperty("AnyoFabCuerdaSeg", array("label"=>"Año 1ª Cuerda Seg.","align"=>"left","width"=>100,"edittype"=>"text","editoptions"=>array("size"=>10,"maxlength"=>10)));
$grid3->setColProperty("NSerieSeguridad", array("label"=>"Nº 1ª Seguridad","align"=>"left","width"=>195,"edittype"=>"text","editoptions"=>array("size"=>40,"maxlength"=>40)));
$grid3->setColProperty("AnyoFabCuerdaSeg2", array("label"=>"Año 2ª Cuerda Seg.","align"=>"left","width"=>100,"edittype"=>"text","editoptions"=>array("size"=>10,"maxlength"=>10)));
$grid3->setColProperty("NSerieSeguridad2", array("label"=>"Nº 2ª Seguridad","align"=>"left","width"=>195,"edittype"=>"text","editoptions"=>array("size"=>40,"maxlength"=>40)));
$grid3->setGridOptions(array(
    "caption"=>"",
    "sortable"=>false,
	"scroll"=>1,
	"rowNum"=>20,
    "width"=>850,
	"height"=>60,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} descensores",
	"emptyrecords"=>"Sin descensores asociados",
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>false,
	"shrinkToFit"=>false,
	"autowidth"=>false
));

// Enable navigator
$grid3->navigator = true;
$grid3->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>false,"excel"=>false,"refresh"=>false));
$grid3->inlineNav = true;	// Para poder Editar ó Añadir en la Fila...
$grid3->inlineNavOptions("navigator", array("edit"=>true));
$grid3->callGridMethod("#grid3", "setFrozenColumns");
$grid3->callGridMethod("#grid3", "navSeparatorAdd", array("#pager3"));
// Refresh
$buttonoptions = array("#pager3",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function() {
		$('#grid3').trigger('reloadGrid');}"
	)
);
$grid3->callGridMethod("#grid3","navButtonAdd",$buttonoptions);

$grid3->renderGrid("#grid3","#pager3",true,null,$params,true,true);
$conn3=null;
?>