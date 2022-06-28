<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");		// include the jqGrid Class
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");	 // include the SQL Server driver class
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

// Obtengo variable del ancho
$ancho_grid = 638;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn);
// Write the SQL Query
$grid->SelectCommand = 'SELECT Id,Nombre,Prefijo,Sufijo FROM TAerogenerador';
$grid->table = 'TAerogenerador';
$grid->dataType = 'json';			// set the ouput format to json
$grid->encoding = 'utf-8';
$grid->setColModel();				// set the table where add-del-edit data
$grid->setPrimaryKeyId("Id");
$grid->setUrl('aerogeneradores_1.php');

// Let the grid create the model
// Set the url from where we obtain the data
$grid->setGridOptions(array(
	"caption"=>"Aerogeneradores",
	"sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
	"width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Aerogeneradores",
	"emptyrecords"=>"No hay Aerogeneradores",		
	"cellLayout"=>7,
	"shrinkToFit"=>false,
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>true
));
$grid->setColProperty("Id", array("label"=>"Codigo","align"=>"right","editable"=>false,"width"=>55));
$grid->setColProperty("Nombre", array("label"=>"Tipo Aerogenerador","width"=>335));
$grid->setColProperty("Prefijo", array("label"=>"Tipo GAMESA","align"=>"center","width"=>100));
$grid->setColProperty("Sufijo", array("label"=>"Nº de Serie","align"=>"center","width"=>100));

// Enable navigator
$grid->navigator = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>true,"excel"=>false,"refresh"=>false));
$grid->setNavOptions("search",array("multipleSearch"=>false));
// Botón Añadir
$form_add = <<< FORMADD
	function(e){
		var x_w = $(window).width();
		var y_w = $(window).height();
		var dx=Math.round((x_w-750)/2);
		var dy=Math.round((y_w-500)/2);
		e.preventDefault();
		$.ajax({
			type: "GET", url:"aerogeneradores_actions.php",
			data: {act:'preadd'}, dataType: "text", async:false
		});
		
		var url = "Maestros/aerogeneradores_options.php?action=add";
		var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
		iframe.dialog({
			autoOpen: true,
			title: "Añadir Aerogenerador",
			width: 750,
			height: 500,
			bgiframe: true,
			modal: true,
			position: [dx,dy],
			closeOnEscape: false,
			closeText: '',
			draggable: false,
			resizable: false,
			stack: true,
			zIndex: 3999,
			open: function(event, ui) {
				window.parent.$(".ui-dialog-titlebar-close").hide();
				window.parent.$("#ui-dialog-title-vent_uic").html('Añadir Aerogenerador <i>');
			},
			buttons: {
				"Crear": function() {
					// ODG, 28.02.13 Unifico tabla precios
					var d0=$(this).contents().find('#id').val();
					var d1=$(this).contents().find('#nombre').val();
					var d2=$(this).contents().find('#prefijo').val();
					var d3=$(this).contents().find('#sufijo').val();
					var d4=$(this).contents().find('#extintores').val();
					var d5=$(this).contents().find('#idGrupo').val();
					
					$.ajax({
						type: "GET", url:"aerogeneradores_actions.php",
						data: {act:'add', v0:d0, v1:d1, v2:d2, v3:d3, v4:d4, v5:d5},
						success: function() {
							$("#grid").trigger("reloadGrid");
						},
						dataType: "text", async:false
					});
					window.parent.$(this).dialog('close');
				},
				"Cancelar": function() {
					$.get("aerogeneradores_actions.php", {act:'unadd'});
					window.parent.$(this).dialog('close');
				}
			}
	   }).width(750).height(500);
	}
FORMADD;

$buttonoptions = array("#pager",
	array(
		"caption"=>"",
		"buttonicon"=>"ui-icon-plus", 
		"title"=>"Crear Aerogenerador",
		"onClickButton"=>"js:".$form_add
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));

// Exportar Excel
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'excel', url:'aerogeneradores_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
// Exportar PDF
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'aerogeneradores_1.php'});}"
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

$form_edit = <<< FORMEDIT
	function() {
		var x_w = $(window).width();
		var y_w = $(window).height();
		var dx=Math.round((x_w-750)/2);
		var dy=Math.round((y_w-500)/2);
		var col=$("#grid").jqGrid('getGridParam', 'selrow');
		var rowid=$("#grid").jqGrid('getCell', col, 'Id');
		var url = "Maestros/aerogeneradores_options.php?action=edit&id="+rowid;
		var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
		iframe.dialog({
			autoOpen: true,
			title: "Modificar Aerogenerador",
			width: 750,
			height: 505,
			bgiframe: true,
			modal: true,
			position: [dx,dy],
			closeOnEscape: false,
			closeText: '',
			draggable: false,
			resizable: false,
			stack: true,
			zIndex: 3999,
			open: function(event, ui) {
				window.parent.$(".ui-dialog-titlebar-close").hide();
				window.parent.$("#ui-dialog-title-vent_uic").html('Modificar Aerogenerador <i>');
			},
			buttons: {
				"Guardar": function() {
					// ODG, 28.02.13 Unifico tabla precios
					var d0=$(this).contents().find('#id').val();
					var d1=$(this).contents().find('#nombre').val();
					var d2=$(this).contents().find('#prefijo').val();
					var d3=$(this).contents().find('#sufijo').val();
					var d4=$(this).contents().find('#extintores').val();
					var d5=$(this).contents().find('#idGrupo').val();
					
					$.ajax({
						type: "GET", url:"aerogeneradores_actions.php",
						data: {act:'edit', v0:d0, v1:d1, v2:d2, v3:d3, v4:d4, v5:d5},
						success: function() {
							$("#grid").trigger("reloadGrid");
						},
						dataType: "text", async:false
					});
					window.parent.$(this).dialog('close');
				},
				"Cancelar": function() {
					window.parent.$(this).dialog('close');
				}
			}
	   }).width(750).height(500);
	}
FORMEDIT;
$grid->setGridEvent("ondblClickRow", $form_edit);

if (($oper = jqGridUtils::GetParam("oper")) == "pdf")
{
	ob_clean();
	$grid->setPdfOptions(array(
			"header"=>true,
			"margin_top"=>20,
			"margin_left"=>12,
			"page_orientation"=>"L",
			"author"=>"Abantos",
			"creator"=>"Abantos",
			"title"=>"Aerogeneradores",
			"subject"=>"Abantos Vertical, S.L.",
			"keywords"=>"aerogeneradores",
			"font_monospaced"=>"dejavusans",
			"font_name_main"=>"dejavusans",
			"font_data_main"=>"dejavusans",
			//logo
			"header_logo"=>"logoAV.jpg",
			"header_logo_width"=>20,
			"header_title"=>"Aerogeneradores",
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

$grid->callGridMethod("#grid", "setFrozenColumns");
$grid->renderGrid("#grid","#pager",true, null, null, true, true);
$conn = null;
?>