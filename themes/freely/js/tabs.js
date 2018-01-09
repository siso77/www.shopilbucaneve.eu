(function(jQuery) {
    jQuery(function() {

        jQuery('ul.tabs').delegate('li:not(.active)', 'click', function(e) {
            jQuery(this).addClass('active').siblings().removeClass('active')
            .parent().next('.tab_container').find(".tab_content").hide().eq(jQuery(this).index()).fadeIn(150);  
            e.preventDefault();
        });
        
        
        jQuery('.tabs').find("li:first").addClass("active");
        jQuery('.tab_container').find("div:first").show();
        
    })   
    
    
})(jQuery)


