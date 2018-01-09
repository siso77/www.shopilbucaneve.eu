function ptShowMenuPopup(objMenu, popupId, block1Id, block2Id)
{
    objMenu = $(objMenu.id); var popup = $(popupId); if (!popup) return;
    objMenu.addClassName('active');
    var popupWidth = CUSTOMMENU_POPUP_WIDTH;
    if (!popupWidth) popupWidth = popup.getWidth();
    popup.style.display = 'inline-block';
    var block1 = $(block1Id);
    var block2 = $(block2Id);
    var new_popup_width = 0;
    
    if(block1 && !block2){
        new_popup_width = block1.getWidth();
    }
    if(!block1 && block2){
        new_popup_width = block2.getWidth();
    }
    if(block1 && block2){
        if(block1.getWidth() >= block2.getWidth()){
            new_popup_width = block1.getWidth();
        }
        if(block1.getWidth() < block2.getWidth()){
            new_popup_width = block2.getWidth();
        }
    }
    new_popup_width = new_popup_width + 22;
//    alert(new_popup_width);
    var pos = ptPopupPos(objMenu, new_popup_width);
    popup.style.top = pos.top + 'px';
    popup.style.left = pos.left + 'px';
    if (CUSTOMMENU_POPUP_WIDTH) popup.style.width = CUSTOMMENU_POPUP_WIDTH + 'px';
//    doSlide(popupId);
}

function ptPopupPos(objMenu, w)
{
    var pos = objMenu.cumulativeOffset();
    var wraper = $('pt_custommenu');
    var posWraper = wraper.cumulativeOffset();
    var wWraper = wraper.getWidth();
    var xTop = pos.top - posWraper.top + CUSTOMMENU_POPUP_TOP_OFFSET;
    var xLeft = pos.left - posWraper.left;
    if ((xLeft + w) > wWraper) xLeft = wWraper - w;
    return {'top': xTop, 'left': xLeft};
}

function ptHideMenuPopup(element, event, popupId, menuId)
{
    element = $(element.id); var popup = $(popupId); if (!popup) return;
    var current_mouse_target = null;
    if (event.toElement)
    {
        current_mouse_target = event.toElement;
    }
    else if (event.relatedTarget)
    {
        current_mouse_target = event.relatedTarget;
    }
    if (!ptIsChildOf(element, current_mouse_target) && element != current_mouse_target)
    {
        if (!ptIsChildOf(popup, current_mouse_target) && popup != current_mouse_target)
        {
//            popup.style.display = 'none';
            $(menuId).removeClassName('active');
        }
    }
}

function ptIsChildOf(parent, child)
{
    if (child != null)
    {
        while (child.parentNode)
        {
            if ((child = child.parentNode) == parent)
            {
                return true;
            }
        }
    }
    return false;
}

