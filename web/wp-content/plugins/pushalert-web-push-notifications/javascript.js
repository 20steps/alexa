jQuery(document).ready(function() {
    if(jQuery('#publish').attr('name')==="publish" && jQuery('#pushalert_notification_enable').length>0){
        jQuery('#publish').click(function() {
            if(jQuery('#pushalert_notification_enable').is(":checked")){
                if(jQuery('#pushalert_notification_title').val()==="" || jQuery('#pushalert_notification_message').val()===""){
                    alert("PushAlert: Notification title and message cannot be empty!");
                    return false;
                }
            }
        });
    }
});