function swapTextField(obj, text)
{
	if(obj.value == '')
		obj.value = text;
}

function showHiddenDiv(idDiv)
{
	var el = document.getElementById(idDiv);
	if(el.style.visibility == 'hidden')
		el.style.visibility = 'visible';
	else
		el.style.visibility = 'hidden';
}

function MyPopup(tpl, name_page)
{
	var w = '800';
	var h = '600';
	var top = (screen.availHeight/2)-(h/2);
	var left = (screen.availWidth/2)-( w/2);
	window.open(tpl, '', "width="+w+", height="+h+", left=0, top=0, menubar=no, status=no, location=no, toolbar=no, scrollbars=yes, resizable=yes,top="+top+",left="+left);
}
function PopupPrint(tpl, name_page)
{
	var w = '800';
	var h = '600';
	var top = (screen.availHeight/2)-(h/2);
	var left = (screen.availWidth/2)-( w/2);
	window.open(tpl, name_page, "width="+w+", height="+h+", left=0, top=0, menubar=no, status=no, location=no, toolbar=no, scrollbars=yes, resizable=yes,top="+top+",left="+left);
}
function PopupAttachment(tpl, name_page)
{
	var w = '400';
	var h = '100';
	var top = (screen.availHeight/2)-(h/2);
	var left = (screen.availWidth/2)-( w/2);
	window.open(tpl, name_page, "width="+w+", height="+h+", left=0, top=0, menubar=no, status=no, location=no, toolbar=no, scrollbars=no, resizable=no,top="+top+",left="+left);
}
function CustomPopup(tpl, ww, hh, name_page)
{
	var w = ww;
	var h = hh;
	var top = (screen.availHeight/2)-(h/2);
	var left = (screen.availWidth/2)-( w/2);
	window.open(tpl, name_page, "width="+w+", height="+h+", left=0, top=0, menubar=no, status=no, location=no, toolbar=no, scrollbars=yes, resizable=no,top="+top+",left="+left);
}
function replaceNewLine(value)
{
	return value.replace(/\r/g, "").replace(/\n/g, "");
}

function show_hidden_div(id)
{
	var el = document.getElementById(id);
	
	if(el.style.visibility == 'visible')
		el.style.visibility = 'hidden';
	else
		el.style.visibility = 'visible';
}

function show_div(id, id_to_focus)
{
	var el = document.getElementById(id);
	var el_to_focus = document.getElementById(id_to_focus);
	
	if(el.style.visibility == 'visible')
	{
		el.style.visibility = 'hidden';
	}
	else
	{
		el.style.visibility = 'visible';
		el_to_focus.focus();
	}	
}

function show_div_cerca_ajax(id, id_to_focus, id_libro, id_div_message, textMessage, action_from, WWW_ROOT)
{
	var el = document.getElementById(id);
	var el_to_focus = document.getElementById(id_to_focus);

	var el_id_libro = document.getElementById('id');
	el_id_libro.value = id_libro;

	$('#'+id_div_message).html(textMessage);

	if(action_from == 'vendita')
		$('#div_cerca_libro_submit_button').html('<input type="button" class="submit" value="VENDI" onclick="javascript:var el = document.getElementById(\'id\');document.location.href=\''+WWW_ROOT+'/?act=NuovaVendita&id_cliente=\'+el.value;"/>');
	else if(action_from == 'visione')
		$('#div_cerca_libro_submit_button').html('<input type="button" class="submit" value="PORTA IN VISIONE" onclick="javascript:var el = document.getElementById(\'id\');document.location.href=\''+WWW_ROOT+'/?act=PortaVisione&id_cliente=\'+el.value;"/>');
	else if(action_from == 'fatturazione')
		$('#div_cerca_libro_submit_button_fatturazione').html('<input type="button" class="submit" value="GENERA FATTURA" onclick="javascript:var el = document.getElementById(\'id\');document.location.href=\''+WWW_ROOT+'/?act=Fatturazione\';"/>');
	
	if(id =='id_div_porta_visione_rapp')
	{
		if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))
		{
			var ieversion=new Number(RegExp.$1)
			if (ieversion>=9 || ieversion>=8 || ieversion>=7 || ieversion>=6 || ieversion>=5)
			{
				if(el.style.position == 'relative')
					el.style.position = 'absolute';
				else
					el.style.position = 'relative';
			}
		}
	}
	
	if(el.style.visibility == 'visible')
		el.style.visibility = 'hidden';
	else
	{
		el.style.visibility = 'visible';
		el_to_focus.focus();
	}	
}

function swapField(obj, defaultValue)
{
	if(obj.value == defaultValue)
		obj.value = '';
}
function resetField(obj, defaultValue)
{
	if(obj.value == '')
	obj.value = defaultValue;
}
function resetFieldById(id, value)
{
	var el = document.getElementById(id);
	el.value = value;
}

/**
 * jQuery integration
*/
function ajaxSendAction(actionType, action, arrayParams, id_div_to_display, outputResponse)
{
	var params = '';
	if(arrayParams != '')
	{
		for(var i=0;i<arrayParams.length;i++)
		{
			var el = document.getElementById(arrayParams[i]);
			if(el.value != '')
				params += el.name+'='+el.value+'&';
		}
		params = params.substring(0,params.length-1);
	}
	else
		params = '';

	$.ajax({
		  url: action,
		  type: actionType,
		  dataType: "html",
		  data: params,
		  success: function(html){
//			  var el = document.getElementById(id_div_to_display);
			  $('#'+id_div_to_display).html(html);
		  }
		});
}

function html_entity_decode (string, quote_style) 
{
    var hash_map = {},
        symbol = '',
        tmp_str = '',
        entity = '';    tmp_str = string.toString();
 
    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    } 
    // fix &amp; problem
    // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
    delete(hash_map['&']);
    hash_map['&'] = '&amp;'; 
    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }    tmp_str = tmp_str.split('&#039;').join("'");
 
    return tmp_str;
}

function ajaxSendActionPaging(actionType, action, params, id_div_to_display, outputResponse)
{
	  var el = document.getElementById(id_div_to_display);
	  //$('#'+id_div_to_display).html('');
	$.ajax({
		  url: action,
		  type: actionType,
		  dataType: "html",
		  data: params,
		  success: function(html){
			  $('#'+id_div_to_display).html(html);
		  }
		});
}

function ajaxSendBookToBasket(action, actionType, params, id_div_to_display, id_to_hidden, id_to_display)
{
	var elToHidden = document.getElementById(id_to_hidden);
	var elToDisplay = document.getElementById(id_to_display);

	elToHidden.style.visibility = 'hidden';
	elToHidden.style.position = 'absolute';

	elToDisplay.style.visibility = 'visible';
	elToDisplay.style.position = 'relative';

	$.ajax({
		  url: action,
		  type: actionType,
		  dataType: "html",
		  data: params,
		  success: function(html){
			  //alert(html);
			  $('#'+id_div_to_display).html(html);
		  }
		});
}

function sendLibroTo(url, id_hidden)
{
	var el = document.getElementById(id_hidden);
	document.location.href = url+'&id_magazzino='+el.value;
}

function selezionaRappresentantePerVisione(id)
{
	var el_id_rappresentante = document.getElementById('id_rappresentante');
	el_id_rappresentante.value = id;

	var result_search = document.getElementById('result_search');
	result_search.style.visibility = 'hidden';

	var cerca_rappresentante = document.getElementById('cerca_rappresentante');
	cerca_rappresentante.style.visibility = 'hidden';

	var submit_button = document.getElementById('submit_porta_visione');
	submit_button.style.visibility = 'visible';
	
}