
// Cycle plugin config
jQuery(document).ready(function($){

    var c2TextTransitionOn = 1; // 1 (true) or 0 (false). If '0' then the text will disapear for the duration of the transition

    /* Homepage Slider 3 params */
    $('#c2-slider').cycle({
	fx:		    'fade',
	before:		    onBefore,
	after:		    onAfter,
	speed:		    1000,
	timeout:	    5000,
	sync:		    1,
	randomizeEffects:   0,
	prev:		    '#slider-prev',
	next:		    '#slider-next',
	pager:		    '#c2-nav'
    });
    function onBefore(curr, next, opts) {
	if (!c2TextTransitionOn){
	    $(curr).find('.slide-desc').css({display:'none'});
	    $(next).find('.slide-desc').css({display:'none'});
	}
    }
    function onAfter(curr, next, opts) {
	if (!c2TextTransitionOn){
	    $(this).find('.slide-desc').css({display:'block'});
	}
    }
    $('.c2-slideshow').hover(
	function() { $('#slider-prev').fadeIn(); },
	function() { $('#slider-prev').fadeOut(); }
    );
    $('.c2-slideshow').hover(
	function() { $('#slider-next').fadeIn(); },
	function() { $('#slider-next').fadeOut(); }
    );

    $('#c2-pauseButton').click(function() {
	$('#c2-slider').cycle('pause');
	return false;
    });

    $('#c2-resumeButton').click(function() {
	$('#c2-slider').cycle('resume', true);
	return false;
    });
});




