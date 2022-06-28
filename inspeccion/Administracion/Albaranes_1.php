<?php
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");	  // include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");  // include the SQL Server driver class
if (session_id() === "")
	session_start();
	
// Obtengo variable del ancho
$ancho_grid = 975;
$alto_grid = $_COOKIE['yscreen']-185;
if (isset($_REQUEST['Mes']))
	$_SESSION['Mes'] = $_REQUEST['Mes'];
if (isset($_REQUEST['Tipo']))
	$_SESSION['Tipo'] = $_REQUEST['Tipo'];
if (isset($_REQUEST['Anyo']))
	$_SESSION['Anyo'] = $_REQUEST['Anyo'];
if (isset($_REQUEST['Parque']))
	$_SESSION['Parque'] = $_REQUEST['Parque'];

$Query = "SELECT * FROM AlbaranCAB WHERE Tipo".(($_SESSION['Tipo'] == "M") ? "<3" : "=3");
if (is_numeric($_SESSION['Parque']))
	$Query .= " AND IdParque=".$_SESSION['Parque'];
if (is_numeric($_SESSION['Mes']))
	$Query .= " AND Fecha >='".$_SESSION['Anyo']."-".$_SESSION['Mes']."-01' AND Fecha <= '".$_SESSION['Anyo']."-".$_SESSION['Mes']."-31'";
else if (is_numeric($_SESSION['Anyo']))
	$Query .= " AND Fecha >='".$_SESSION['Anyo']."-01-01' AND Fecha <= '".$_SESSION['Anyo']."-12-31'";

// Conexión PDO
$conn2 = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn2->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn2);
$grid->SelectCommand = $Query;
$grid->table = 'AlbaranCAB';
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';
$grid->dataType = 'json';
$grid->setColModel();	  // Let the grid create the model
$grid->setUrl('Albaranes_1.php');	   // Set the url from where we obtain the data

// Columnas
$grid->setColProperty("Id", array("label"=>"Id","hidden"=>true));
$grid->setColProperty("Tipo", array("label"=>" ","width"=>23.5, "editoptions"=>array("defaultValue"=>($_SESSION['Tipo'] == "M") ? '1' :'3')));
$grid->setSelect("Tipo",(($_SESSION['Tipo'] == "M")?array(1=>"LV",2=>"MX"):array(3=>"RE")), true, true, false);
$grid->setColProperty("NAlb", array("label"=>" ","width"=>30,"align"=>"right","formatter"=>"integer"));
$grid->setColProperty("Anyo", array("label"=>" ","width"=>10,"editable"=>false, "align"=>"center","editoptions"=>array("defaultValue"=>date('y'))));
$grid->setColProperty("IdParque", array("label"=>"Parque","width"=>150));
$grid->setSelect("IdParque", "SELECT Id, Nombre FROM Parques");
$grid->setColProperty("Fecha",
	array("label"=>"Fecha","width"=>35, "formatter"=>"date",
		"formatoptions"=>array("srcformat"=>"Y-m-d", "newformat"=>"d/m/Y"),
		"editoptions"=>array("dataInit"=>
		"js:function(elm){setTimeout(function() {
			jQuery(elm).datepicker({dateFormat:'dd/mm/yy'});
			jQuery('.ui-datepicker').css({'font-size':'75%'});
		},200);}", "defaultValue"=>date('Y/m/d'))
));
$grid->setColProperty("Pedido", array("label"=>"Pedido","width"=>75,"align"=>"left","edittype"=>"text","editoptions"=>array("size"=>25,"maxlength"=>25)));
$grid->setColProperty("CabTxt", array("label"=>"Texto Cabecera","width"=>200,"align"=>"left","edittype"=>"text","editoptions"=>array("size"=>100,"maxlength"=>100)));
$grid->setGridOptions(array(
    "caption"=>"",
    "sortable"=>false,
	"scroll"=>1,
	"rowNum"=>100,
	"width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Albaranes",
	"emptyrecords"=>"Sin Albaranes",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>false
));

// Enable navigator
$grid->navigator = true;
$grid->getLastInsert = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>false,"excel"=>false,"refresh"=>false));
$grid->inlineNav = true;		// Para poder Editar ó Añadir en la Fila...
$grid->inlineNavOptions("navigator", array("edit"=>true));
$grid->callGridMethod("#grid", "setFrozenColumns");

// Modificar Albarán
$ViewEvent = <<< ONVIEW
function() {
	var getIDs = $("#grid").jqGrid('getDataIDs');				 // Obtenemos todos los ID que estamos viendo
	var rowid  = $("#grid").jqGrid('getGridParam', 'selrow'); 	 // Obtenemos columna seleccionada
	var sExcel = ($("#ChkExcel").attr("checked")) ? "1" : "0";
	
	if (rowid > 0)
	{		
		window.parent.$("#ModalHis").html("<iframe allowtransparency='allowtransparency' id='vent_info' src='Administracion/AlbaranDet.php?Id="+rowid+
			"&Excel="+sExcel+"' width='100%' height='420' frameborder='0' scrolling='no'></iframe>");
		window.parent.$("#ModalHis").dialog({
			title: "Modificar Albarán",
			closeText: 'hide',
			resizable: false,
			width:950,
			height:530,
			bgiframe: true,
			modal: true,
			closeOnEscape: false,
			draggable: false,
			stack: true,
			open: function(event, ui) {
				window.parent.$(".ui-dialog-titlebar-close").hide();
				window.parent.$("#ui-dialog-title-vent_uic").html("Modificar Albarán <i>");
			},
			buttons: [
				{   // Imprimir Albarán visualizado
					text:'Imprimir',
					click: function() {
						window.parent.$("#ModalPdf").html("<iframe allowtransparency='allowtransparency' id='vent_info' src='Administracion/ImpAlbaran.php?Id="+rowid+
							"&Excel="+sExcel+"' width='100%' height='99%' frameborder='0' scrolling='no'></iframe>");
						if (sExcel != "1")
						{   // Sólo en el caso de mostrar el PDF por pantalla
							window.parent.$("#ModalPdf").attr("title", "ALBARANES");
							window.parent.$("#ModalPdf").dialog({
								resizable: false,
								height: 650,
								width: 950,
								modal: true,
								buttons: {
									"Cerrar": function() {window.parent.$(this).dialog("close");}
								}
							});
						}
					}
				}, {
					text:'Cerrar',
					id: 'Closedialog',
					click: function() {window.parent.$(this).dialog('close');}
				}
			]
		});
	}
}
ONVIEW;
$grid->setGridEvent("ondblClickRow", $ViewEvent);

// Refresh
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-refresh", "title"=>"Recargar", "onClickButton"=>"js: function() {
		$('#grid').trigger('reloadGrid');}"
	)
);
$grid->callGridMethod("#grid","navButtonAdd",$buttonoptions);

if (($oper = jqGridUtils::GetParam("oper")) == "del")
{
	$grid->setAfterCrudAction($oper, "DELETE FROM AlbaranDET WHERE IdAlbaran=".($IdTmp = jqGridUtils::GetParam("Id")));
	$grid->setAfterCrudAction($oper, "UPDATE ListaControl SET IdAlbaran=0 WHERE IdAlbaran=".$IdTmp);
	$grid->setAfterCrudAction($oper, "UPDATE ListaCtrlDes SET IdAlbaran=0 WHERE IdAlbaran=".$IdTmp);
	$grid->setAfterCrudAction($oper, "UPDATE ListaCtrlExt SET IdAlbaran=0 WHERE IdAlbaran=".$IdTmp);
}
else if ($oper == "add")
{   // Al añadir un Albarán debo de asignar algunos campos a mano...
	$Query = $conn2->query("SHOW TABLE STATUS LIKE 'AlbaranCab'");
	if (($row = $Query->fetch (PDO::FETCH_ASSOC)))
		$lastid = $row["Auto_increment"];
		
	// Sino he introducido el Nº de Albarán, le asigno el siguiente al último
	if (($NAlb = jqGridUtils::GetParam("NAlb")) == 0) {
		$Query = jqGridDB::query($conn2, "SELECT MAX(NAlb) FROM AlbaranCAB WHERE Tipo=".jqGridUtils::GetParam("Tipo")." AND Anyo=".date('y'));
		if (($row = jqGridDB::fetch_num($Query, true, $conn2)))
			$NAlb = $row[0]+1;
	}

	$grid->setAfterCrudAction($oper, "UPDATE AlbaranCAB SET NAlb=".$NAlb.",Anyo=".date('y')." WHERE Id=".$lastid);
}

$grid->renderGrid("#grid","#pager",true,null,null,true,true);
$conn2 = null;
?>