jQuery(window).load(function() {
	/* Fix for IE */
    	if (jQuery.browser.msie && jQuery.browser.version >= 9) {
		 jQuery.support.noCloneEvent = true;
		}
	/* End fix for IE */
	
	jQuery('.block:not(.block-related) .block-title').prepend('<div class="icon"></div>')
	jQuery('.block:odd').addClass('odd')

	jQuery('button.button').hover(function() {
		jQuery(this).toggleClass('active')
	}, function () {
		jQuery(this).toggleClass('active')
	})
	
	/* Top Cart */
	jQuery('.top-cart .block-title').click(function(){
		jQuery(this).toggleClass('active');
		jQuery('#topCartContent').slideToggle(500).toggleClass('active')
	})
	/* Top Cart */
	
	/* Header Search */
	jQuery('.search-block-title').click(function(){
		jQuery(this).toggleClass('active');
		jQuery('.header .form-search').slideToggle(500).toggleClass('active')
	})
	/* Header Search */
	
	jQuery('.header .quick-access-right .links li a').prepend('<span class="top-links-arr"></span>');
	
	/* Product Page Tabs */
	jQuery ('.catalog-product-view #tabs li a').click (function () {
		jQuery("ul#tabs li a").removeClass("active");
		jQuery(this).addClass("active")
		jQuery(".box-collateral").hide();

		var activeTab = jQuery(this).attr("href");
		jQuery(activeTab).fadeIn();
		return false;
		})
	/* Product Page Tabs */
	
	
	/* Hover Image on product list */
	if(jQuery('.hover-image').length) {
		jQuery ('.col-main li.item').hover(function(){
			jQuery(this).find('span.hover-image').stop(true, true).fadeIn(500)
		}, function(){
			jQuery(this).find('span.hover-image').stop(true, true).fadeOut(500)
		})
	}
	/* Hover Image on product list */
	
	/* Lightbox */
	if(jQuery('.lightbox').length) {
		jQuery(".lightbox").fancybox({
				'opacity'		: true,
				'overlayShow'	: true,
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic'
			});
	}
	/* Lightbox */
	
	/* Pimg */
	if(jQuery('.pimg').length) {
		pimg();
	}
	/* Pimg */
	
	
	/* Menu */
	if(jQuery('#superfish-menu ul#nav').length) {
	  jQuery("#superfish-menu ul#nav").superfish({
		  autoArrows: false,
		  dropShadows: false,
		  animation:   {opacity:'show',height:'show'},
		  delay: 50
	  });
	}
	
	if(jQuery('#menu-wide ul#nav').length) {
	  jQuery ('#menu-wide #nav li.level-top.parent').hover (function(){
		      jQuery(this).addClass("hover").find('ul:first').stop(true, true).fadeIn (500)
		  }, function (){
			   jQuery(this).removeClass("hover").find('ul:first').stop(true, true).fadeOut (500)
			  })
	}
	/* Menu */
	
	/* My cart page accordion */
	if(jQuery('#accordion').length) {
		  //ACCORDION BUTTON ACTION	
		jQuery('dt.accordion_toggle').click(function() {
			jQuery('dd.accordion_content').slideUp('normal');	
			jQuery(this).next().slideDown('normal');
		});
	 
		//HIDE THE DIVS ON PAGE LOAD	
		jQuery("dd.accordion_content").hide();
	}
	/* My cart page accordion */
	
	/* Grid Small Dark */
	if(jQuery('.grid-small').length) {
	  jQuery ('.grid-small li.item').hover (function(){
		  var blockHeight = jQuery(this).find('a.product-image').css('height')
		  jQuery(this).addClass("hover").find('.grid-info').css('height', blockHeight).stop(true, true).fadeTo (500, 0.9)
	  }, function (){
		   jQuery(this).removeClass("hover").find('.grid-info').stop(true, true).fadeOut (500)
		  })
	}
	/* Grid Small Dark */
	
	/* List */
	if(jQuery('.products-list').length) {
	  var blockHeightList = jQuery('.products-list li.item').css('height')
	  jQuery('.products-list li.item .list-col').css('height', blockHeightList)
	}
	/* List */
	
	/* Grid Small Light */
	if(jQuery('.grid-small-second').length) {
	  jQuery ('.grid-small-second li.item').hover (function(){
		  jQuery(this).find('a.product-image').css('opacity', 0.3)
		  var blockHeight = jQuery(this).find('.grid-info').css('height')
		  jQuery(this).addClass("hover").find('.grid-info').stop(true, true).animate ({top:10, opacity : 1}, 500)
		  jQuery(this).find('.grid-info-2').stop(true, true).animate ({bottom: 0}, 500)
	  }, function (){
		  jQuery(this).find('a.product-image').css('opacity', 1)
		  jQuery(this).removeClass("hover").find('.grid-info').stop(true, true).animate ({top:-200, opacity : 0}, 1000)
		  jQuery(this).find('.grid-info-2').stop(true, true).animate ({bottom: -200}, 500)
		  })
	}
	/* Grid Small Light */
	
	/* Grid Small With Hover */
	if(jQuery('.grid-small-hover').length) {
		jQuery('.grid-small-hover li.item').hover(function(){
			jQuery(this).css('z-index','9999').toggleClass('active') 
			jQuery('.grid-small-hover li.item:not(.active)').stop(true, true).fadeTo(400, 0.5)
			jQuery(this).find('.product-grid-details').stop(true, true).fadeToggle(100)
		}, function() {
			jQuery(this).css('z-index','1').toggleClass('active')
			jQuery('.grid-small-hover li.item').delay(200).fadeTo(400, 1)
			jQuery(this).find('.product-grid-details').fadeToggle(100)
		})
	}
	/* Grid Small With Hover */
	
	if(jQuery('#nav_vert').length) {
		jQuery('#nav_vert li').prepend('<div class="icon"></div>');
	}
	
	if(jQuery('#cs-navigation-coin-slider a').length) {
		jQuery('#cs-navigation-coin-slider a').prepend('<span class="shdow"></span ><span class="bg-coin-button"></span >');
	}
	
	jQuery('.header .links li').each(function(index){
		jQuery(this).addClass('item-' + (index+1));
	});
	
	var colorValue = '#b9b9b9'//jQuery('#nav li a').css('color')
	jQuery ('#nav li:not(.active)').hover (function(){
		Cufon.replace(jQuery (this).find('a.level-top'), {
		  hover: true,
		  fontFamily: 'Helvetica',
		  fontWeight: 'normal',
		  color: '#fff'
		});
		Cufon.now();
		}, function (){
			Cufon.replace(jQuery (this).find('a.level-top'), {
			  fontFamily: 'Helvetica',
			  fontWeight: 'normal',
		  	  color: colorValue
			});
			Cufon.now();
	})
	
	jQuery ('#superfish-menu #nav li.level-top:not(.active)').hover (function(){
			jQuery (this).find('a.level-top').stop(true, true).animate({paddingBottom:'+=7', paddingTop:'+=5'})
		}, function (){
			jQuery (this).find('a.level-top').stop(true, true).animate({paddingBottom:'-=7', paddingTop:'-=5'})
	}) 

	jQuery('#accordion ul.level0, .block-account .block-content li a, .block-account .block-content li strong').before('<span class="arrow"></span>'); 
	jQuery('#accordion ul.level0 li a').before('<span class="sub-arrow"></span>');         
    jQuery('#accordion li.level0.nav-1').addClass('active');

               
    jQuery("#accordion li span.arrow").click(function(){
        if(false == jQuery(this).next('ul').is(':visible')) {
            jQuery('#accordion ul.level0').slideUp(300);
        }
        jQuery(this).next('.level0').slideToggle(300);
        
        if(jQuery(this).parent().hasClass('active')) {
            jQuery(this).parent().addClass('not-active');
        }
        
        jQuery('#accordion li.active').each(function() {
                jQuery(this).removeClass('active');
        });
        if(!jQuery(this).parent().hasClass('not-active')) {
            jQuery(this).parent().addClass('active');
        }
        jQuery('#accordion li.not-active').each(function() {
                jQuery(this).removeClass('not-active');
        });
    });
		
});