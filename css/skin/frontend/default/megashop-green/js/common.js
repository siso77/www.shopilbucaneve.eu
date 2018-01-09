jQuery.noConflict();

  jQuery(document).ready(function () {

	 jQuery('.tooltip , [data-tooltip]').tipsy({

		fade:true,

		html: true

	});

	  //img rotetor

	  jQuery('.image_rotate .image_rotate_inner').hover(function(){

			//var imgCount = jQuery(this).find('img').size();

			jQuery(this).find('img').filter(':first').css({'zindex':'1500'});

			var imgPath = jQuery(this).find('img').filter(':last').attr("src");

			if(imgPath.search('placeholder') < 0){

				jQuery(this).find('img').filter(':first').fadeOut('slow');

			}

		},

		function(){

			var imgPath = jQuery(this).find('img').filter(':last').attr("src");

			//if(imgCount>1){

				if(imgPath.search('placeholder') < 0){

				jQuery(this).find('img').filter(':first').fadeIn('slow');

			}

		}

	  );

	  //quick view

	  jQuery('.fancybox').fancybox(

			{

			   hideOnContentClick : true,

			   width: 580,

			   autoDimensions: true,

               type : 'iframe',

			   showTitle: false,

			   scrolling: 'no',

			   onComplete: function(){

				jQuery('#fancybox-frame').load(function() {

					 // wait for frame to load and then gets it's height

					jQuery('#fancybox-content').height(jQuery(this).contents().find('body').height()+0);

					jQuery.fancybox.resize();

				 });



			   }

			}

		);

	  

	

	  // hide #back-top first

	jQuery("#back-top").hide();

	

	// fade in #back-top

	jQuery(function () {

		jQuery(window).scroll(function () {

			if (jQuery(this).scrollTop() > 100) {

				jQuery('#back-top').fadeIn();

			} else {

				jQuery('#back-top').fadeOut();

			}

		});



		// scroll body to 0px on click

		jQuery('#back-top a').click(function () {

			jQuery('body,html').animate({

				scrollTop: 0

			}, 800);

			return false;

		});

	});


	jQuery("a[rel=example_group]").fancybox({

				'transitionIn'		: 'none',

				'transitionOut'		: 'none',

				'titlePosition' 	: 'over',

				'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {

					return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';

				}

			});

	jQuery('#carousel').elastislide({

				imageW 	:300,

				minItems	:1

			});		

	

	});

	



jQuery.noConflict();

	function showOptions(id){

		jQuery('#fancybox'+id).trigger('click');

	}

	function setAjaxData(data,iframe){

		if(data.status == 'ERROR'){

			alert(data.message);

		}else{

			if(jQuery('.block-cart')){

	            jQuery('.block-cart').replaceWith(data.sidebar);

	        }

	        if(jQuery('.header .links')){

	            jQuery('.header .links').replaceWith(data.toplink);

	        }

			jQuery('#messageBox').fadeIn();

				setTimeout(function(){jQuery('#messageBox').fadeOut();},2000);

	        jQuery.fancybox.close();

		}

	}

	function setLocationAjax(url,id){

		url += 'isAjax/1';

		url = url.replace("checkout/cart","ajax/index");

		jQuery('#ajax_loader'+id).show();

		try {

			jQuery.ajax( {

				url : url,

				dataType : 'json',

				success : function(data) {

					jQuery('#ajax_loader'+id).hide();

         			setAjaxData(data,false);           

				}

			});

		} catch (e) {

		}

	}


