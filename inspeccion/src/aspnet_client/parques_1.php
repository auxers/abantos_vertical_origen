<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");		// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");	 // include the SQL Server driver class
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

// Obtengo variable del ancho
$ancho_grid = 680;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn); 
// Write the SQL Query, ODG 14.01.12, Hago que la exportación se visualice los datos igual que en el jqGrid
$grid->SelectCommand = 'SELECT Id, Nombre, Cliente, Pais FROM Parques';
$grid->ExportCommand = 'SELECT PA.Id, PA.Nombre, PA.Cliente, P.Nombre AS Pais, I.Nombre AS Idioma FROM Parques PA 
	JOIN Paises P ON P.Id = PA.Pais JOIN Idiomas I ON I.Id=P.IdiomaCer';
// set the table where add-del-edit data
$grid->table = 'Parques';
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';
$grid->dataType = 'json';
$grid->setColModel();				// Let the grid create the model
$grid->setUrl('parques_1.php');	  // Set the url from where we obtain the data
// Columna, para Editar y Borrar Línea.
/* ODG, 31.01.13 Activo que se Borre y Edite desde la botonera principal
$grid->addCol(array(
	"name"=>"",
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
$grid->setColProperty("Nombre", array("label"=>"Nombre","width"=>165,"editoptions"=>array("maxlength"=>50)));
$grid->setColProperty("Cliente", array("label"=>"Cliente","align"=>"left","width"=>90,"editable"=>true,"edittype"=>"text","editoptions"=>array("defaultValue"=>"GAMESA","maxlength"=>25)));
$grid->setColProperty("Pais", array("label"=>"País","width"=>95));
$grid->setSelect("Pais","SELECT Id, Nombre FROM Paises ORDER BY Id", true, true, false);
$grid->setGridOptions(array(
    "caption"=>"Parques",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
	"width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Parques",
	"emptyrecords"=>"No hay Parques",
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
$grid->inlineNavOptions("navigator", array("edit"=>true));
$grid->callGridMethod("#grid", 'setFrozenColumns');
// Exportar Excel
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'excel', url:'parques_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
// Exportar PDF
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'parques_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->callGridMethod("#grid", "navSeparatorAdd", array("#pager"));
// Administrar Parque
$viewevent = <<< ONVIEW
function() {
	var getIDs = $("#grid").jqGrid('getDataIDs');				 // Obtenemos todos los ID que estamos viendo
	var rowid  = $("#grid").jqGrid('getGridParam', 'selrow'); 	 // Obtenemos columna seleccionada
	var parque = $("#grid").jqGrid('getCell',rowid,'Nombre');
	
	if (rowid > 0)
	{
		var title = "ADMINISTRACIÓN DE PARQUE: ";
		window.parent.$("#ModalHis").html("<iframe allowtransparency='allowtransparency' id='vent_info' src='Maestros/parques_his_0.php?id="+rowid+"' width='100%' height='505' frameborder='0' scrolling='no'></iframe>");
		window.parent.$("#ModalHis").dialog({
			title: title + parque,
			closeText: 'hide',
			resizable: false,
			width:1015,
			height:610,
			bgiframe: true,
			modal: true,
			closeOnEscape: false,
			draggable: false,
			stack: true,
			open: function(event, ui) { 
				window.parent.$(".ui-dialog-titlebar-close").hide(); 
				window.parent.$("body").css("overflow","hidden");
				window.parent.$(".ui-dialog-buttonpane").css("cursor","default");
				window.parent.$("#Prev-Event").button({ icons:{primary:"ui-icon-circle-triangle-w"},text:false });
				window.parent.$("#Next-Event").button({ icons:{primary:"ui-icon-circle-triangle-e"},text:false });
				window.parent.$("#separator1").removeClass("ui-button ui-widget ui-button-text-only ui-button-text").html("<span></span>").prepend('<span style="width:10px!important;">&nbsp;&nbsp;</span>');
				window.parent.$("#separator1").css("background","#ffffff").css("border","0px solid #ffffff").css("cursor","default");
				window.parent.$("#separator2").removeClass("ui-button ui-widget ui-button-text-only ui-button-text").html("<span></span>").prepend('<span style="width:10px!important;">&nbsp;&nbsp;</span>');
				window.parent.$("#separator2").css("background","#ffffff").css("border","0px solid #ffffff").css("cursor","default");
				window.parent.$("#separator3").removeClass("ui-button ui-widget ui-button-text-only ui-button-text").html("<span></span>").prepend('<span style="width:10px!important;">&nbsp;&nbsp;</span>');
				window.parent.$("#separator3").css("background","#ffffff").css("border","0px solid #ffffff").css("cursor","default");
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

// Botón Recargar
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
			"author"=>"Abantos Vertical",
			"creator"=>"Abantos Vertical",
			"title"=>"Parques",
			"subject"=>"Abantos Vertical, S.L.",
			"keywords"=>"Parques",
			"font_monospaced"=>"dejavusans",
			"font_name_main"=>"dejavusans",
			"font_data_main"=>"dejavusans",
			//logo
			"header_logo"=>"logoAV.jpg",
			"header_logo_width"=>20,
			"header_title"=>"Parque",
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
else if ($oper == "del")
{   // Buscamos y Borrarmos, las Líneas y LineasPletina de éste parque
	$IdTmp = jqGridUtils::GetParam("Id");
	$Query = $conn->query("SELECT Id FROM Lineas WHERE IdParque=".$IdTmp);
	while ($row = $Query->fetch (PDO::FETCH_ASSOC))
		$grid->setAfterCrudAction($oper, "DELETE FROM LineasPletina WHERE IdLinea=".$row["Id"]);

	$grid->setAfterCrudAction($oper, "DELETE FROM Lineas WHERE IdParque=".$IdTmp);
}

$grid->renderGrid("#grid","#pager",true, null, null, true,true);
$conn = null;
?>