jQuery(document).ready(function($){
    
    //************ media *******************************

    var $player = $('audio');

    if($player.length) {
        $player.mediaelementplayer({
            audioWidth  : '100%',
            audioHeight : '34px',
            videoWidth  : '100%',
            videoHeight : '100%'
        });
    }
   
   


    /* Detect Touch Device --> Begin */
	
    (function() {

        if(Modernizr.touch)
            jQuery('body').addClass('touch-device');

    })();

    /* end Detect Touch Device */

    /* Main Navigation --> Begin */

    (function() {

        var $mainNav    = $('#navigation').find('.menu');
		
        // Regular nav
        $mainNav.on('mouseenter', 'li', function() {
            var $this    = $(this),
            $subMenu = $this.children('ul');
            if( $subMenu.length ) $this.addClass('hover');
            $subMenu.hide().stop(true, true).fadeIn(200);
        }).on('mouseleave', 'li', function() {
            $(this).removeClass('hover').children('ul').stop(true, true).fadeOut(50);
        });

    })();

    /* Main Navigation --> End */


    /* Image wrapper --> Begin */
    function handle_image(img) {
        var curtain = jQuery('<span class="curtain">&nbsp</span>');
        img.after(curtain);
    }

    var img_collection = jQuery('.zoomer img, .workPanelLink img');
    img_collection.each(function() {
        handle_image(jQuery(this));
    });
    /* Image wrapper --> End */
	
    /* Workpanel --> Begin */
    if(jQuery('.workPanelLink').length) {
        var $filterType = jQuery('#filter a');
        var $list = jQuery('#list');
        $list.css('height', jQuery(this).height()/2.5);
        var $data = $list.clone();
			
        $filterType.click(function() {
            if (jQuery(this).attr('data-rel') == 'everyone') {
                var $sortedData = $data.find('li');
            } else {
                var $sortedData = $data.find('.'+ jQuery(this).attr('data-rel'));
            }
			
            jQuery('#filter li').removeClass('active');
            jQuery(this).parent('li').addClass('active');

            $list.quicksand($sortedData, {
                attribute: 'id',
                duration: 500,
                adjustHeight: 'auto',
                useScaling: 'true'
            });
            return false;
        });	
    }

    /* Workpanel --> End */



    jQuery('.gallery_album_cover').live('click',function() {
        var panel=jQuery('#workPanel');
        var data = {
            action: "render_gall",
            id: jQuery(this).attr('album_id')            
        };
        jQuery.post(ajaxurl, data, function(response) {
            jQuery("#workPanel").show();
            jQuery('#grid-wrapper1 .responsed_content').html("");
            if(panel.height()>0) {
                jQuery('#ajax_panel').fadeOut(200, function() {
                    jQuery('#ajax_panel').fadeIn(500);
                    jQuery('#grid-wrapper1 .responsed_content').html(response);
                });
            } else {
                jQuery('#ajax_panel').animate({
                    opacity:1
                }, 500, function(){
                    jQuery('#ajax_panel').fadeIn(500);
                    jQuery('#grid-wrapper1 .responsed_content').html(response);
                });
            }

            var panel_height=jQuery('input#gallery_height').val();

            panel.stop().animate({
                height:panel_height,
                opacity:1
            },500, function() {
                jQuery('.pics').cycle({
                    fx:     'scrollHorz',
                    timeout: 0,
                    next:   '.next',
                    prev:   '.prev'
                });
                jQuery(".pics").css('height', panel_height);
                jQuery('body').animate({
                    scrollTop : panel.offset().top
                }, 1000);
            });
        });
        return false;
    });
	
	
    /* Portfolio Close --> Begin */
	
    jQuery(".close").click(function(){
        jQuery("#workPanel").animate({
            height:0,
            opacity:0
        },777,function(){
            jQuery("#workPanel").hide();
        });
    });
	
    /* Portfolio Close --> End */
	
    /* Notification Close --> Begin */
	
    $notifications = jQuery('.success, .info, .error, .warning');
    $notifications.each(function(){
        jQuery(this).append('<span class="close-box">&times;</span>').wrap('<div class="custom-box-wrap">');
			
        var closeBox = jQuery('.close-box', this);
		
        closeBox.on('click',function(e) {	
            var $this = jQuery(this); 
            var box = $this.parent().parent('.custom-box-wrap');
            box.animate({
                opacity:0
            },500, function() {
                box.animate({
                    height:0, 
                    margin: 0
                },500).queue(function(){
                    jQuery(this).remove()
                    });
            });
            e.preventDefault();
        });
			
    });
		
		
    /* Notification Close --> End */
	
	
    /* Portfolio Single --> Begin */

    if(jQuery('.single-pics').length && jQuery('.single-pics img').length > 1) {
        jQuery('.single-pics').cycle({
            fx:     'scrollHorz',
            timeout: 0,
            next:   '.next',
            prev:   '.prev',
            easing: 'easeOutQuint'
        });		
    }

    /* Portfolio Single --> End */
	

    /* Prepare loading fancybox --> Begin */
    if(jQuery('.zoomer').length) {
        jQuery('.zoomer').fancybox({
            'overlayShow'	: false,
            'transitionIn'	: 'elastic',
            'transitionOut'	: 'elastic'
        });
    }
    /* Prepare loading fancybox --> End */
	
    /* Back To Top --> Begin */
	
    (function() {

        var extend = {
            button      : '#back-top',
            separator   : '.divider-top a',
            text        : 'Back to Top',
            min         : 200,
            fadeIn      : 400,
            fadeOut     : 400,
            speed		: 800
        }

        jQuery(window).scroll(function() {
            var pos = jQuery(window).scrollTop();

            if (pos > extend.min) {
                jQuery(extend.button).fadeIn(extend.fadeIn);
            }
            else {
                jQuery(extend.button).fadeOut (extend.fadeOut);
            }

        });

        jQuery(extend.button).add(extend.separator).click(function(e){
            jQuery('html, body').animate({
                scrollTop : 0
            }, extend.speed);
            e.preventDefault();
        });

    })();

    /* Back To Top --> End */

    /* Toggle --> Begin */
    if(jQuery('.toggle_container').length) {
        jQuery(".toggle_container").hide(); //Hide (Collapse) the toggle containers on load
        //Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
        jQuery("b.trigger").click(function(){
            jQuery(this).toggleClass("active").next().slideToggle("slow");
            return false; //Prevent the browser jump to the link anchor
        });
    }
    /* Toggle --> End */
    //**** functions for comments
    if(parseInt(jQuery("[name=is_user_logged_in]").val(),10)){
        var comments=jQuery(".comment-reply-link");
        jQuery.each(comments, function(index, value) {
            jQuery(value).removeAttr("onclick");
            jQuery(value).live('click',function(){
                
                if(jQuery(value).parents(".comment-body").find(".add-comment").length>0){
                    return false;
                }
                
                var comment_id=jQuery(value).closest("li").attr("comment-id");
                var html=jQuery("#addcomments_template").html();
                html=html.replace(/__INDEX__/gi, comment_id);
                jQuery("#commentform .add-comment").hide(300);
                jQuery(value).parent().after(html);
                return false;
            });

        });

        jQuery(".reset").live('click',function(){
            var form=jQuery(this).closest(".add-comment");
            jQuery(form).hide(300,function(){
                jQuery(form).remove();
            });
        });

        jQuery(".reply").live('click',function(){
            jQuery(this).closest(".add-comment").hide(300,function(){
                var comment_parent=jQuery(this).closest(".add-comment").attr("id-reply");
                var comment_content=jQuery(this).parent().find("textarea").eq(0).val();
                if(!comment_content.length){
                    alert("Write text a little please!!");
                    jQuery(this).closest(".add-comment").show();
                    return false;
                }
                var data = {
                    action: "add_comment",
                    comment_parent: comment_parent,
                    comment_post_ID:jQuery("[name=current_post_id]").val(),
                    comment_content:comment_content
                };
                //send data to server
                jQuery.post(ajaxurl, data, function(response) {
                    window.location.href=jQuery("[name=current_post_url]").val()+"?new_comment="+response;
                });

            });
        });
    }


//***************************
//update_capcha();
});

function submit_widget_contact_form(form_object){
    jQuery(form_object).next(".contact_form_responce").find("ul").eq(0).html("");
    jQuery(form_object).next(".contact_form_responce").find("ul").eq(0).removeClass();

    var data = {
        action: "contact_form_request",
        values: jQuery(form_object).serialize()
    };
    //send data to server
    jQuery.post(ajaxurl, data, function(response) {
        response=jQuery.parseJSON(response);
        jQuery(form_object).find(".wrong_data").removeClass("wrong_data");
        if(response.is_errors){
            jQuery(form_object).closest(".contact_form_responce").addClass("contact_form_responce_error");
            jQuery.each(response.info,function(input_name, input_label) {
                jQuery(form_object).find("[name="+input_name+"]").eq(0).addClass("wrong_data");
                jQuery(form_object).next(".contact_form_responce").find("ul").eq(0).append('<li>'+lang_enter_correctly+' "'+input_label+'"!</li>');
            });
        }else{
            jQuery(form_object).next(".contact_form_responce").find("ul").eq(0).addClass("contact_form_responce_succsess");
            if(response.info == 'succsess'){
                jQuery(form_object).next(".contact_form_responce").find("ul").eq(0).append('<li>'+lang_sended_succsessfully+'!</li>');
            }

            if(response.info == 'server_fail'){
                jQuery(form_object).next(".contact_form_responce").find("ul").eq(0).append('<li>'+lang_server_failed+'!</li>');
            }

            jQuery(form_object).find("[type=text],textarea").val("");

        }
        //*****
        update_capcha();
        jQuery(form_object).next(".contact_form_responce").show(300);
    });


    return false;
}


function update_capcha(){
    if(jQuery("[name=capcha_image_frame]").length>0){
        window.frames["capcha_image_frame"].location.reload();
    }
    if(jQuery("[name=capcha_image_frame_widget]").length>0){
        window.frames["capcha_image_frame_widget"].location.reload();
    }
}


function getElementsByClass(searchClass,node,tag) {
    var classElements = new Array();
    if ( node == null )
        node = document;
    if ( tag == null )
        tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
    for (i = 0, j = 0; i < elsLen; i++) {
        if ( pattern.test(els[i].className) ) {
            classElements[j] = els[i];
            j++;
        }
    }
    return classElements;
}



function gmt_init_map(Lat,Lng, map_canvas_id, zoom, maptype,info,show_marker,show_popup,scrollwheel,custom_controls) {
    var latLng = new google.maps.LatLng(Lat, Lng);
    var homeLatLng = new google.maps.LatLng(Lat, Lng);

    switch(maptype){
        case "SATELLITE":
            maptype=google.maps.MapTypeId.SATELLITE;
            break;

        case "HYBRID":
            maptype=google.maps.MapTypeId.HYBRID;
            break;

        case "TERRAIN":
            maptype=google.maps.MapTypeId.TERRAIN;
            break;

        default:
            maptype=google.maps.MapTypeId.ROADMAP;
            break;

    }

    scrollwheel=parseInt(scrollwheel,10);
    var map;
    if(custom_controls.length>0){
        
        var options=merge_objects_options({
            zoom: zoom,
            center: latLng,
            mapTypeId: maptype,
            scrollwheel: scrollwheel,
            disableDefaultUI: true
        },custom_controls);
        
        map = new google.maps.Map(document.getElementById(map_canvas_id), options);
    }else{
        map = new google.maps.Map(document.getElementById(map_canvas_id), {
            zoom: zoom,
            center: latLng,
            mapTypeId: maptype,
            scrollwheel: scrollwheel
        });
    }
   

    show_marker=parseInt(show_marker,10);
    if(show_marker){
        var marker = new MarkerWithLabel({
            position: homeLatLng,
            draggable: false,
            map: map
        });


        if(show_popup && info!=""){
            google.maps.event.addListener(marker, "click", function (e) {
                iw.open(map, marker);
            });
            var iw = new google.maps.InfoWindow({
                content: "<span>"+info+"</span>"
            });
        }
    }

}

function merge_objects_options(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) {
        obj3[attrname] = obj1[attrname];
    }
    for (var attrname in obj2) {
        obj3[attrname] = obj2[attrname];
    }
    return obj3;
}

