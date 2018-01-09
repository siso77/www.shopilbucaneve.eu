jQuery(document).ready(function() {
    jQuery("#contactform").submit(function(){
        jQuery("#contact_form_responce").hide();
        jQuery("#contact_form_responce ul").html("");
        jQuery("#contact_form_responce ul").removeClass();

        var data = {
            action: "contact_form_request",
            values: jQuery("#contactform").serialize()
        };
        //send data to server
        jQuery.post(ajaxurl, data, function(response) {
            response=jQuery.parseJSON(response);
            jQuery(".wrong_data").removeClass("wrong_data");

            if(response.is_errors){
                jQuery("#contact_form_responce ul").addClass("contact_form_responce_error");
                jQuery.each(response.info,function(input_name, input_label) {
                    jQuery("[name="+input_name+"]").addClass("wrong_data");
                    jQuery("#contact_form_responce ul").append('<li>'+lang_enter_correctly+' "'+input_label+'"!</li>');
                });
            }else{
                jQuery("#contact_form_responce ul").addClass("contact_form_responce_succsess");
                if(response.info == 'succsess'){
                    jQuery("#contact_form_responce ul").append('<li>'+lang_sended_succsessfully+'!</li>');
                }

                if(response.info == 'server_fail'){
                    jQuery("#contact_form_responce ul").append('<li>'+lang_server_failed+'!</li>');
                }

                jQuery("[type=text],textarea").val("");

            }

            jQuery("#contact_form_responce").show(300);
            update_capcha();
        });
        return false;
    });
});
