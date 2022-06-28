<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

// Obtengo variable del ancho
$ancho_grid = 575;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
if (($conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD)))
	$conn->query("SET NAMES utf8");

// Create the jqGrid instance
$grid = new jqGridRender($conn);
// Write the SQL Query
$grid->SelectCommand = 'SELECT Id, Nombre,Login,Nivel FROM Trabajadores';
$grid->table='Trabajadores';
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';			// set the encoding
$grid->dataType = 'json';			 // set the ouput format to json
$grid->setColModel();				 // Let the grid create the model
$grid->setUrl('operarios_1.php');	 // Set the url from where we obtain the data

$grid->setColProperty("Id", array("label"=>"Código","width"=>50,"align"=>"right","editable"=>false));
$grid->setColProperty("Nombre", array("label"=>"Nombre","align"=>"left","width"=>275,"edittype"=>"text","editrules"=>array("required"=>true),"editoptions"=>array("size"=>50,"maxlength"=>50)));
$grid->setColProperty("Login", array("label"=>"Login","align"=>"left","editable"=>false,"width"=>50,"editoptions"=>array("size"=>8,"maxlength"=>8)));
$grid->setColProperty("Nivel", array("label"=>"Tipo","align"=>"center","width"=>75));
$grid->setSelect("Nivel",array(1=>"Inspector",2=>"Avanzado",5=>"Administrador"), true, true, false);

$grid->setGridOptions(array(
    "caption"=>"Operarios",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
    "width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Operarios",
	"emptyrecords"=>"No hay Operarios",
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
$grid->callGridMethod("#grid", 'setFrozenColumns');

// Botón Añadir
$form_add = <<< FORMADD
	function(e){
		var x_w = $(window).width();
		var y_w = $(window).height();
		var dx=Math.round((x_w-575)/2);
		var dy=Math.round((y_w-150)/2);
		e.preventDefault();
		$.ajax({
			type: "GET", url:"operarios_actions.php",
			data: {act:'preadd'}, dataType: "text", async:false
		});
		
		var url = "Maestros/operarios_options.php?action=add";
		var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
		iframe.dialog({
			autoOpen: true,
			title: "Añadir Usuario",
			width: 575,
			height: 150,
			bgiframe: true,
			modal: true,
			position: [dx,dy],
			closeOnEscape: false,
			closeText: "",
			draggable: false,
			resizable: false,
			stack: true,
			zIndex: 3999,
			open: function(event, ui) {
				window.parent.$(".ui-dialog-titlebar-close").hide();
				window.parent.$("#ui-dialog-title-vent_uic").html('Añadir Usuario <i>');
			},
			buttons: {
				"Guardar": function() {
					var d0=$(this).contents().find('#Id').val();
					var d1=$(this).contents().find('#Nombre').val();
					var d2=$(this).contents().find('#Login').val();
					var d3=$(this).contents().find('#Password').val();
					var d4=$(this).contents().find('#Nivel').val();
					var d5=$(this).contents().find('#Firma').val();
					
					$.ajax({
						type: "GET", url: "operarios_actions.php",
						data: {act:"add", v0:d0, v1:d1, v2:d2, v3:d3, v4:d4, v5:d5},
						success: function(sRes) {
							if (sRes == "") {
								$("#grid").trigger("reloadGrid");
							} else
								alert(sRes);
						},
						dataType: "text", async:false
					});
					window.parent.$(this).dialog('close');
				},
				"Cancelar": function() {
					$.get("operarios_actions.php", {act:'unadd'});
					window.parent.$(this).dialog('close');
				}
			}
	   }).width(575).height(150);
	}
FORMADD;

$buttonoptions = array("#pager",
	array(
		"caption"=>"",
		"buttonicon"=>"ui-icon-plus", 
		"title"=>"Crear Usuario",
		"onClickButton"=>"js:".$form_add
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
$grid->callGridMethod("#grid", "navSeparatorAdd",array("#pager"));
// Exportar Excel
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-excel", "title"=>"Exportar a Excel", "onClickButton"=>"js: function(){
			$('#grid').jqGrid('excelExport',{tag:'excel', url:'operarios_1.php'});}"
	)
);
$grid->callGridMethod("#grid", "navButtonAdd", $buttonoptions);
// Exportar PDF
$buttonoptions = array("#pager",
	array("caption"=>"", "buttonicon"=>"ui-icon-pdf", "title"=>"Generar PDF", "onClickButton"=>"js: function(){
		$('#grid').jqGrid('excelExport',{tag:'pdf', url:'operarios_1.php'});}"
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

// Editar Doble Click
$form_edit = <<< FORMEDIT
	function() {
		var x_w = $(window).width();
		var y_w = $(window).height();
		var dx=Math.round((x_w-575)/2);
		var dy=Math.round((y_w-150)/2);
		var col=$("#grid").jqGrid('getGridParam', 'selrow');
		var rowid=$("#grid").jqGrid('getCell', col, 'Id');
		var url = "Maestros/operarios_options.php?action=edit&Id="+rowid;
		var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
		iframe.dialog({
			autoOpen: true,
			title: "Modificar Usuario",
			width: 575,
			height: 150,
			bgiframe: true,
			modal: true,
			position: [dx,dy],
			closeOnEscape: false,
			closeText: "",
			draggable: false,
			resizable: false,
			stack: true,
			zIndex: 3999,
			open: function(event, ui) {
				window.parent.$(".ui-dialog-titlebar-close").hide();
				window.parent.$("#ui-dialog-title-vent_uic").html('Modificar Usuarios <i>');
			},
			buttons: {
				"Guardar": function() {
					var d0=$(this).contents().find('#Id').val();
					var d1=$(this).contents().find('#Nombre').val();
					var d2=$(this).contents().find('#Login').val();
					var d3=$(this).contents().find('#Password').val();
					var d4=$(this).contents().find('#Nivel').val();
					var d5=$(this).contents().find('#Firma').val();
					
					$.ajax({
						type: "GET", url: "operarios_actions.php",
						data: {act:"edit", v0:d0, v1:d1, v2:d2, v3:d3, v4:d4, v5:d5},
						success: function(sRes) {
							if (sRes == "") {
								$("#grid").trigger("reloadGrid");
							} else
								alert(sRes);
						},
						dataType:"text", async:false
					});
					window.parent.$(this).dialog('close');
				},
				"Cancelar": function() {
					window.parent.$(this).dialog('close');
				}
			}
	   }).width(575).height(150);
	}
FORMEDIT;
$grid->setGridEvent("ondblClickRow", $form_edit);

if(($oper = jqGridUtils::GetParam("oper")) == "pdf")
{
	ob_clean();
	$grid->setPdfOptions(array(
			"header"=>true,
			"margin_top"=>20,
			"margin_left"=>12,
			"page_orientation"=>"P",
			"author"=>"Abantos",
			"creator"=>"Abantos",
			"title"=>"Operarios",
			"subject"=>"Abantos Vertical, S.L.",
			"keywords"=>"trabajadores,operarios",
			"font_monospaced"=>"dejavusans",
			"font_name_main"=>"dejavusans",
			"font_data_main"=>"dejavusans",
			//logo
			"header_logo"=>"logoAV.jpg",
			"header_logo_width"=>20,
			"header_title"=>"Operarios",
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

$grid->renderGrid("#grid","#pager",true, null, null, true,true);
$conn = null;
?>