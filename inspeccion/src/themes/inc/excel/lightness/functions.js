// JavaScript Document
$(document).ready(function() {
	// * * * * * Evento KeyPress, KeyUp * * * * *
	$("input, textarea, select").keypress(function(event) {
		if (event.which == 13)
		{   // Hacer Tab al pulsar Enter en todos los elementros del Form
			var inputs = $(this).parents("form, table").eq(0).find(":input:visible:enabled").not(":button");
			var idx = inputs.index(this);

			if (idx < inputs.length - 1)
			{   // Se Cancela el Submit al Pulsar Enter
				event.preventDefault();
				inputs[idx+1].focus();
				inputs[idx+1].select();
			}
		}
	});
	// Para la Clase Textbox Numéricos
	$(".txtDec").keyup(function(event) {
		if(event.which == 188)
			this.value = this.value.split(",").join(".");
	});
	$(".txtDec").focusout(function() {
		if (!$.isNumeric($(this).val()))
			$(this).val("0.00");
		else
			$(this).val(fNumber_format(parseFloat($(this).val()), 2, '.', ''));
	});
	$(".txtNum").focusout(function() {
		if (!$.isNumeric($(this).val()))
			$(this).val("0");
	});
});

function fTrim(myString)
{
	return myString.replace(/^\s+/g,'').replace(/\s+$/g,'');
}

function fDialog(capa)
{
	$(function() {
		window.parent.$("#"+capa).dialog({
			modal: true,
			buttons: {
				'Aceptar': function() {
					window.parent.$(this).dialog( "close" );
				}
			}
		});	
	});
}

function fConfirmar(url, accion)
{
	if (!confirm("\u00BFEst\u00e1 seguro de que desea "+accion+" el registro?"))
		return false;
	else
	{
		document.location= url;
		return true;
	}
}

function AlternarVisualizacion(NomCapa)
{
	var Grupo = NomCapa.split(","), Pos = 0;
	for (;Pos<Grupo.length;Pos ++) {
		$('#'+Grupo[Pos]).css("display", ($('#'+Grupo[Pos]).css("display") == 'none')?'block':'none');
	}
}
function OptVisualizacion(NomCapa, sDato)
{
	$("#"+NomCapa).css("display", (sDato != "") ? 'none' : 'block');
}
function NotVisualizacion(NomCapa, sDato, sValor)
{
	$("#"+NomCapa).css("display", (sDato == sValor) ? 'none' : 'block');
}
function OptInicializa(NomCapa, sDato)
{
	if (sDato == "")
		$("input[name$="+NomCapa+"]").val("");
}

// =================================================================================================
//	\fn fNumber_format (number, decimals, dec_point, thousands_sep)
//	\brief	Devuelve un string con formato de un número.
// =================================================================================================
function fNumber_format(number, decimals, dec_point, thousands_sep)
{
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite( +number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals), 
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec)
		{
            var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
        };
		
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		
    if (s[0].length > 3) 
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	
    if ((s[1] || '').length < prec)
	{
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	
    return s.join(dec);
}

function fExtraer(sValor)
{
	var nNum = sValor.split("_");
	
	return nNum[0];
}