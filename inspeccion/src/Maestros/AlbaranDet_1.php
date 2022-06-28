<?php
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class
if (session_id() === "")
	session_start();
	
// Conexión PDO
$conn2 = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn2->query("SET NAMES utf8");
// Create the jqGrid instance
$grid2 = new jqGridRender($conn2);
$grid2->SelectCommand = 'SELECT * FROM AlbaranDET WHERE IdAlbaran = ?';
$grid2->table = 'AlbaranDET';
$grid2->encoding = 'utf-8';
$grid2->dataType = 'json';
$grid2->setColModel(null, ($params = array($_SESSION['AlbId'])));	  // Let the grid create the model
$grid2->setPrimaryKeyId("Id");
$grid2->setUrl('AlbaranDet_1.php');	  // Set the url from where we obtain the data
// Columnas
$grid2->setColProperty("Id", array("label"=>"Id","hidden"=>true));
$grid2->setColProperty("IdAlbaran", array("label"=>"Albaran","hidden"=>true,"editoptions"=>array("defaultValue"=>$_SESSION['AlbId'])));
$grid2->setColProperty("IdTorre", array("label"=>"Nº Torre","align"=>"center","width"=>50,"editoptions"=>array("maxlength"=>8)));
$grid2->setColProperty("Tipo", array("label"=>"Tipo","width"=>105));
//$grid2->setSelect("Tipo",array(0=>"Ins. Lineas Vida",1=>"Ins. Descensores",2=>"Ins. Extintores",3=>"Materiales",4=>"Trabajos",
$grid2->setSelect("Tipo",array(0=>"Ins. Lineas Vida",1=>"Ins. Descensores",3=>"Materiales",4=>"Trabajos",
	5=>"Desplazamientos",6=>"Inspección NO OK"), true, true, false);
$grid2->setColProperty("Concepto", array("label"=>"Concepto","width"=>400, "editoptions"=>array("size"=>"100","maxlength"=>100)));
$grid2->setColProperty("OT", array("label"=>"Orden Trabajo","hidden"=>($_SESSION['AlbTipo']==3)?false:true,"width"=>70,"editoptions"=>array("size"=>"25","maxlength"=>25)));
$grid2->setColProperty("Precio", array("label"=>"Precio","width"=>45,"align"=>"right","formatter"=>"number", "editoptions"=>array("onchange"=>"fCalcula(id);")));
$grid2->setColProperty("Unidades", array("label"=>"Cantidad","width"=>45,"align"=>"right","formatter"=>"number","editoptions"=>array("onchange"=>"fCalcula(id);","defaultValue"=>"1.00")));
$grid2->setColProperty("Importe", array("label"=>"Importe","width"=>45,"align"=>"right","formatter"=>"number"));
$grid2->setGridOptions(array(
    "caption"=>"",
    "sortable"=>false,
	"scroll"=>1,
	"rowNum"=>50,
    "width"=>900,
	"height"=>320,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Lineas Albaran",
	"emptyrecords"=>"Sin Lineas Asociadas",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>false
));

// Enable navigator
$grid2->navigator = true;
$grid2->getLastInsert = true;
$grid2->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>false,"excel"=>false,"refresh"=>false));
$grid2->inlineNav = true;	// Para poder Editar ó Añadir en la Fila...
$grid2->inlineNavOptions("navigator", array("edit"=>true));
// Refresh
$buttonoptions = array("#pager2",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function() {
		$('#grid2').trigger('reloadGrid');}"
	)
);
$grid2->callGridMethod("#grid2","navButtonAdd",$buttonoptions);

$grid2->callGridMethod("#grid2", "setFrozenColumns");
$grid2->renderGrid("#grid2","#pager2",true,null,$params,true,true);
$conn2 = null;
?>