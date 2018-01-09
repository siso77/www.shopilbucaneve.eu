
// Cycle plugin config
jQuery(document).ready(function($){
    /* Homepage Slider 2 params */
    $('#c1-slider').cycle({
	fx:		    'fade',
	speed:		    1000,
	timeout:	    5000,
	sync:		    1,
	randomizeEffects:   0,
	prev:		    '#slider-prev',
	next:		    '#slider-next',
	pager:		    '#c1-nav'
    });
    $('.c1-slideshow').hover(
	function() { $('#slider-prev').fadeIn(); },
	function() { $('#slider-prev').fadeOut(); }
    );
    $('.c1-slideshow').hover(
	function() { $('#slider-next').fadeIn(); },
	function() { $('#slider-next').fadeOut(); }
    );

    $('#c1-pauseButton').click(function() {
	$('#c1-slider').cycle('pause');
	return false;
    });

    $('#c1-resumeButton').click(function() {
	$('#c1-slider').cycle('resume', true);
	return false;
    });
});

