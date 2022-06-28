<?php
date_default_timezone_set('Europe/Madrid');
require_once("../jq-config.php");
require_once(ABSPATH."lib/jqgrid/jqGrid.php");
require_once(ABSPATH."lib/jqgrid/jqGridPdo.php");
require_once(ABSPATH."lib/jqgrid/tcpdf/config/lang/spa.php");
if (session_id() === "")
	session_start();

//Obtengo variable del ancho
$ancho_grid = 450;
$alto_grid = $_COOKIE['yscreen']-170;
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
$conn->query("SET NAMES utf8");
// Create the jqGrid instance
$grid = new jqGridRender($conn);
$grid->SelectCommand = "SELECT Id, Nombre FROM PvpGrupos";
$grid->table = 'PvpGrupos';
$grid->setPrimaryKeyId("Id");
$grid->encoding = 'utf-8';			// set the encoding
$grid->dataType = 'json';			 // set the ouput format to json
$grid->setColModel();				 // Let the grid create the model
$grid->setUrl('pvpGrupos_1.php');  // Set the url from where we obtain the data

$grid->setColProperty("Id", array("label"=>"Código","width"=>25,"align"=>"right","editable"=>false));
$grid->setColProperty("Nombre", array("label"=>"Nombre","align"=>"left","width"=>150,"editable"=>true,"edittype"=>"text","editrules"=>array("required"=>true),"editoptions"=>array("maxlength"=>30)));
$grid->setGridOptions(array(
    "caption"=>"Grupos de Precios",
    "sortable"=>true,
	"scroll"=>1,
	"rowNum"=>100,
    "width"=>$ancho_grid,
	"height"=>$alto_grid,
	"sortname"=>"Id",
	"altRows"=>true,
	"altclass"=>"alternate_row_grey",
	"recordtext"=>"{2} Grupos",
	"emptyrecords"=>"No hay Grupos",
	"autowidth"=>false,
	"cellLayout"=>7,
	"hiddengrid"=>false,
	"hidegrid"=>false,
	"hoverrows"=>true
   ));

// Enable navigator
$grid->navigator = true;
$grid->setNavOptions("navigator", array("add"=>false,"edit"=>false,"del"=>true,"view"=>false,"search"=>false,"excel"=>false,"refresh"=>false));
$grid->setNavOptions("search",array("multipleSearch"=>false));

// Botón Añadir
$form_add = <<< FORMADD
	function(e){
		var x_w = $(window).width();
		var y_w = $(window).height();
		var dx=Math.round((x_w-750)/2);
		var dy=Math.round((y_w-400)/2);
		e.preventDefault();
		$.ajax({
			type: "GET", url:"pvpGrupos_actions.php",
			data: {act:'preadd'}, dataType: "text", async:false
		});
		
		var url = "Maestros/pvpGrupos_options.php?action=add";
		var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
		iframe.dialog({
			autoOpen: true,
			title: "Añadir Grupo Precios",
			width: 750,
			height: 400,
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
				window.parent.$("#ui-dialog-title-vent_uic").html('Añadir Grupo Precios <i>');
			},
			buttons: {
				"Guardar": function() {
					var d0  = $(this).contents().find('#Id').val();
					var d1  = $(this).contents().find('#Nombre').val();
					var d2  = $(this).contents().find('#CerLinea').val();
					var d3  = $(this).contents().find('#CerLineaT2').val();
					var d4  = $(this).contents().find('#InsEscaleras').val();
					var d5  = $(this).contents().find('#InsEscalerasT1').val();
					var d6  = $(this).contents().find('#InsEscalerasT2').val();
					var d11 = $(this).contents().find('#PrecioT01').val();
					var d12 = $(this).contents().find('#PrecioT02').val();
					var d13 = $(this).contents().find('#PrecioT03').val();
					var d14 = $(this).contents().find('#PrecioT04').val();
					var d15 = $(this).contents().find('#PrecioT05').val();
					var d16 = $(this).contents().find('#PrecioT06').val();
					var d17 = $(this).contents().find('#PrecioT07').val();
					var d18 = $(this).contents().find('#PrecioT08').val();
					var d21 = $(this).contents().find('#PrecioT11').val();
					var d22 = $(this).contents().find('#PrecioT12').val();
					var d23 = $(this).contents().find('#PrecioT13').val();
					var d24 = $(this).contents().find('#PrecioT14').val();
					var d25 = $(this).contents().find('#PrecioT15').val();
					var d26 = $(this).contents().find('#PrecioT16').val();
					var d27 = $(this).contents().find('#PrecioT17').val();
					var d28 = $(this).contents().find('#PrecioT18').val();
					var d32 = $(this).contents().find('#PrecioT22').val();
					var d34 = $(this).contents().find('#PrecioT24').val();
					var d36 = $(this).contents().find('#PrecioT26').val();
					var d38 = $(this).contents().find('#PrecioT28').val();
					var d42 = $(this).contents().find('#PrecioT32').val();
					var d44 = $(this).contents().find('#PrecioT34').val();
					var d46 = $(this).contents().find('#PrecioT36').val();
					var d48 = $(this).contents().find('#PrecioT38').val();
					var d52 = $(this).contents().find('#PrecioT42').val();
					var d54 = $(this).contents().find('#PrecioT44').val();
					var d56 = $(this).contents().find('#PrecioT46').val();
					var d58 = $(this).contents().find('#PrecioT48').val();

					$.ajax({
						type: "GET", url:"pvpGrupos_actions.php",
						data: {act:'add', Id:d0, Nombre:d1, CerLinea:d2, CerLineaT2:d3,
							InsEscaleras:d4, InsEscalerasT1:d5, InsEscalerasT2:d6,
							PrecioT11:d11, PrecioT12:d12, PrecioT13:d13, PrecioT14:d14,
							PrecioT15:d15, PrecioT16:d16, PrecioT17:d17, PrecioT18:d18,
							PrecioT21:d21, PrecioT22:d22, PrecioT23:d23, PrecioT24:d24,
							PrecioT25:d25, PrecioT26:d26, PrecioT27:d27, PrecioT28:d28,
							PrecioT32:d32, PrecioT34:d34, PrecioT36:d36, PrecioT38:d38,
							PrecioT42:d42, PrecioT44:d44, PrecioT46:d46, PrecioT48:d48,
							PrecioT52:d52, PrecioT54:d54, PrecioT56:d56, PrecioT58:d58
						},
						success: function(sData) {
							$("#grid").trigger("reloadGrid");
						},
						dataType: "text", async:false
					});
					window.parent.$(this).dialog('close');
				},
				"Cancelar": function() {
					$.get("pvpGrupos_actions.php", {act:'unadd'});
					window.parent.$(this).dialog('close');
				}
			}
	   }).width(750 - 12).height(400);
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

$form_edit = <<< FORMEDIT
	function() {
		var x_w = $(window).width();
		var y_w = $(window).height();
		var dx=Math.round((x_w-750)/2);
		var dy=Math.round((y_w-400)/2);
		var col=$("#grid").jqGrid('getGridParam', 'selrow');
		var rowid=$("#grid").jqGrid('getCell', col, 'Id');
		var url = "Maestros/pvpGrupos_options.php?action=edit&id="+rowid;
		var iframe = window.parent.$('<iframe allowtransparency="allowtransparency" id="vent_uic" name="vent_uic" src="'+ url +'" frameborder="0" scrolling="no" style="margin:0px;padding:5px;overflow:hidden;" />');
		iframe.dialog({
			autoOpen: true,
			title: "Modificar Grupo Precios",
			width: 750,
			height: 400,
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
				window.parent.$("#ui-dialog-title-vent_uic").html('Modificar Grupo Precios <i>');
			},
			buttons: {
				"Guardar": function() {
					var d0  = $(this).contents().find('#Id').val();
					var d1  = $(this).contents().find('#Nombre').val();
					var d2  = $(this).contents().find('#CerLinea').val();
					var d3  = $(this).contents().find('#CerLineaT2').val();
					var d4  = $(this).contents().find('#InsEscaleras').val();
					var d5  = $(this).contents().find('#InsEscalerasT1').val();
					var d6  = $(this).contents().find('#InsEscalerasT2').val();
					var d11 = $(this).contents().find('#PrecioT01').val();
					var d12 = $(this).contents().find('#PrecioT02').val();
					var d13 = $(this).contents().find('#PrecioT03').val();
					var d14 = $(this).contents().find('#PrecioT04').val();
					var d15 = $(this).contents().find('#PrecioT05').val();
					var d16 = $(this).contents().find('#PrecioT06').val();
					var d17 = $(this).contents().find('#PrecioT07').val();
					var d18 = $(this).contents().find('#PrecioT08').val();
					var d21 = $(this).contents().find('#PrecioT11').val();
					var d22 = $(this).contents().find('#PrecioT12').val();
					var d23 = $(this).contents().find('#PrecioT13').val();
					var d24 = $(this).contents().find('#PrecioT14').val();
					var d25 = $(this).contents().find('#PrecioT15').val();
					var d26 = $(this).contents().find('#PrecioT16').val();
					var d27 = $(this).contents().find('#PrecioT17').val();
					var d28 = $(this).contents().find('#PrecioT18').val();
					var d32 = $(this).contents().find('#PrecioT22').val();
					var d34 = $(this).contents().find('#PrecioT24').val();
					var d36 = $(this).contents().find('#PrecioT26').val();
					var d38 = $(this).contents().find('#PrecioT28').val();
					var d42 = $(this).contents().find('#PrecioT32').val();
					var d44 = $(this).contents().find('#PrecioT34').val();
					var d46 = $(this).contents().find('#PrecioT36').val();
					var d48 = $(this).contents().find('#PrecioT38').val();
					var d52 = $(this).contents().find('#PrecioT42').val();
					var d54 = $(this).contents().find('#PrecioT44').val();
					var d56 = $(this).contents().find('#PrecioT46').val();
					var d58 = $(this).contents().find('#PrecioT48').val();

					$.ajax({
						type: "GET", url:"pvpGrupos_actions.php",
						data: {act:'edit', Id:d0, Nombre:d1, CerLinea:d2, CerLineaT2:d3,
							InsEscaleras:d4, InsEscalerasT1:d5, InsEscalerasT2:d6,
							PrecioT11:d11, PrecioT12:d12, PrecioT13:d13, PrecioT14:d14,
							PrecioT15:d15, PrecioT16:d16, PrecioT17:d17, PrecioT18:d18,
							PrecioT21:d21, PrecioT22:d22, PrecioT23:d23, PrecioT24:d24,
							PrecioT25:d25, PrecioT26:d26, PrecioT27:d27, PrecioT28:d28,
							PrecioT32:d32, PrecioT34:d34, PrecioT36:d36, PrecioT38:d38,
							PrecioT42:d42, PrecioT44:d44, PrecioT46:d46, PrecioT48:d48,
							PrecioT52:d52, PrecioT54:d54, PrecioT56:d56, PrecioT58:d58
						},
						success: function(sData) {
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
	   }).width(750 - 12).height(400);
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
		"keywords"=>"usuarios",
		"font_monospaced"=>"dejavusans",
		"font_name_main"=>"dejavusans",
		"font_data_main"=>"dejavusans",
		//logo
		"header_logo"=>"logoAV.jpg",
		"header_logo_width"=>20,
		"header_title"=>"Usuarios",
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
else if($oper == "del")
{   // Borramos Grupo Precios, Pvp Montaje, Pvp Revisión
	$IdGrupo = jqGridUtils::GetParam("Id");
	$grid->setAfterCrudAction("del", "DELETE FROM PvpGrupos WHERE Id=".$IdGrupo);
	$grid->setAfterCrudAction("del", "DELETE FROM PvpMontaje WHERE IdGrupo=".$IdGrupo);
	$grid->setAfterCrudAction("del", "DELETE FROM PvpRevision WHERE IdGrupo=".$IdGrupo);
}

$grid->renderGrid("#grid","#pager",true, null, null, true,true);
$conn = null;
?>