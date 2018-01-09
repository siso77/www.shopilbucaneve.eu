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