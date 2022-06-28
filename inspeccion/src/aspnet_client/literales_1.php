<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");			// include Connection Class
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

// Obtengo variable del ancho
$ancho_grid = 775;
$alto_grid = $_COOKIE['yscreen']-170;
if (isset($_REQUEST['Tipo']))
	$_SESSION['Tipo'] = $_REQUEST['Tipo'];
if (isset($_REQUEST['Grupo']))
	$_SESSION['Grupo'] = $_REQUEST['Grupo'];	
if (isset($_REQUEST['Idioma']))
	$_SESSION['Idioma'] = $_REQUEST['Idioma'];
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
if (($Consulta = $conn->query(($Query = "SELECT * FROM Literales WHERE Tipo=".$_SESSION['Tipo']." AND Grupo=".$_SESSION['Grupo']." AND Idioma=".$_SESSION['Idioma']))))
{   if (($row = $Consulta->fetch(PDO::FETCH_ASSOC)))
	{   // Create the jqGrid instance
		$grid = new jqGridRender($conn);	
		// Write the SQL Query
		$grid->SelectCommand = $Query;
		$grid->table='Literales';			   // set the table where add-del-edit data
		$grid->setPrimaryKeyId("Id");
		$grid->encoding = 'utf-8';			 // set the encoding
		$grid->dataType = 'json';			  // set the ouput format to json
		$grid->setColModel();				  // Let the grid create the model
		$grid->setUrl('literales_1.php');	  // Set the url from where we obtain the data

		$grid->setColProperty("Id", array("label"=>"Id","align"=>"right","hidden"=>true));
		$grid->setColProperty("Tipo", array("label"=>"Tipo","align"=>"right","hidden"=>true,"editoptions"=>array("defaultValue"=>$_SESSION['Tipo'])));
		$grid->setSelect("Tipo",array(1=>"CheckList LV",2=>"CheckList DE",3=>"CheckList EX",4=>"Certificado LV", 
			5=>"Certificado DE", 6=>"Meses Año", 7=>"TxtCheck LV"), true, true, false);
		$grid->setColProperty("Grupo", array("label"=>"Grupo","align"=>"right","hidden"=>true,"editoptions"=>array("defaultValue"=>$_SESSION['Grupo'])));
		$grid->setColProperty("Idioma", array("label"=>"Idioma","align"=>"right","hidden"=>true, "editoptions"=>array("defaultValue"=>$_SESSION['Idioma'])));
		$grid->setColProperty("Texto", array("label"=>"Texto","editoptions"=>array("maxlength"=>1024)));
		$grid->setGridOptions(array(
    		"caption"=>"Literales",
		    "sortable"=>true,
			"scroll"=>1,
			"rowNum"=>150,
		    "width"=>$ancho_grid,
			"height"=>$alto_grid,
			"sortname"=>"Id",
			"altRows"=>true,
			"altclass"=>"alternate_row_grey",
			"recordtext"=>"{2} Literales",
			"emptyrecords"=>"No hay Literales",
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
		$grid->inlineNavOptions("navigator", array("edit"=>true, "add"=>false));
		$grid->callGridMethod("#grid", 'setFrozenColumns');			
		// Exportar Excel
		$buttonoptions = array("#pager",
			array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
				$('#grid').jqGrid('excelExport',{tag:'excel', url:'literales_1.php'});}"
			)
		);
		$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
		// Botón Refresh
		$buttonoptions = array("#pager",
			array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function(){
				$('#grid').trigger('reloadGrid');}"
			)
		);
		$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
		$grid->renderGrid("#grid","#pager",true, null, null, true,true);
	}
	
	unset($Consulta, $row);
}
$conn = null;
?>