// JavaScript Document
(function( $ ) {
jQuery(document).on("change keyup paste keydown","#cleverreach_api_key", function(e) {
	var val = jQuery(this).val();
	if( val !== "" )
		jQuery("#auth-cleverreach").removeAttr('disabled');
	else
		jQuery("#auth-cleverreach").attr('disabled','true');
}); 

jQuery("body").on( "click", "#auth-cleverreach", function(e){
	e.preventDefault();
	jQuery(".smile-absolute-loader").css('visibility','visible');
	var cleverreach_api_key = jQuery('#cleverreach_api_key').val();
	
	var action = 'update_cleverreach_authentication';
	var data = {	action : action,
					cleverreach_api_key : cleverreach_api_key
				};
	
	//alert('before ajax');
	jQuery.ajax({
		url: ajaxurl,
		data: data,
		type: 'POST',
		dataType: 'JSON',
		success: function(result){
			
			//console.log(result.message);

			if(result.status == "success" ){
				//alert();
				jQuery(".bsf-cnlist-mailer-help").hide();
				jQuery("#save-btn").removeAttr('disabled');
				jQuery("#cleverreach_api_key").closest('.bsf-cnlist-form-row').hide();
				jQuery("#auth-cleverreach").closest('.bsf-cnlist-form-row').hide();
				jQuery(".cleverreach-list").html(result.message);

			} else {
				jQuery(".cleverreach-list").html('<span class="bsf-mailer-error">'+result.message+'</span>');
				// jQuery(".cleverreach-list-empty").closest('.bsf-cnlist-form-row').remove();

			}
			jQuery(".smile-absolute-loader").css('visibility','hidden');
		}
	});
	e.preventDefault();
});
jQuery(document).on( "click", "#disconnect-cleverreach", function(){
															
	if(confirm("Are you sure? If you disconnect, your previous campaigns syncing with cleverreach will be disconnected as well.")) {
		var action = 'disconnect_cleverreach';
		var data = {action:action};
		jQuery(".smile-absolute-loader").css('visibility','visible');
		jQuery.ajax({
			url: ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(result){

				jQuery("#save-btn").attr('disabled','true');
				if(result.message == "disconnected" ){
					jQuery("#cleverreach_api_key").val('');
					jQuery(".cleverreach-list").html('');
					jQuery(".cleverreach-list-empty").closest('.bsf-cnlist-form-row').remove();

					jQuery("#disconnect-cleverreach").replaceWith('<button id="auth-cleverreach" class="button button-secondary auth-button" disabled="true">Authenticate cleverreach</button><span class="spinner" style="float: none;"></span>');
					jQuery("#auth-cleverreach").attr('disabled','true');
				}

				jQuery('.bsf-cnlist-form-row').fadeIn('300');
				jQuery(".bsf-cnlist-mailer-help").show();
				jQuery(".smile-absolute-loader").css('visibility','hidden');
			}
		});
	}
	else {
		return false;
	}
});

jQuery(document).on( "change", "#smile_cn_form_display", function() {
	cp_display_form_dropdown();
});

jQuery(document).on( "change", "#cleverreach-list", function() {
	cp_display_form_dropdown();
});

/* 
 * Displays form dropdown list 	
*/
function cp_display_form_dropdown() {

	var current_list = jQuery("#cleverreach-list").val();
	var list_count = 0;
	if( jQuery("#smile_cn_form_display").val() != 1 ) {
		jQuery(".bsf-cnform-select").hide();
		jQuery(".cp-form-notice").hide();
	} else {

		var flag = 0;
		jQuery("#cleverreach-form > option").each(function() {
		    if( jQuery(this).data('list') == current_list ) {
		    	jQuery(this).show();

		    	// dont select more than one option
		    	if( jQuery(this).attr( "selected") !== 'selected' && flag == 0 ) {
		    		jQuery(this).attr( "selected", "selected" );
		    		flag = 1;
		    	}
		    	list_count++;
		    } else {
		    	jQuery(this).hide();
		    }
		});

		// if there are not forms available for the list
		if( list_count == 0 ) {
			jQuery(".cp-form-notice").show();
			jQuery("#cleverreach-form").attr('disabled','disabled').hide();
		} else {
			jQuery("#cleverreach-form").show().removeAttr('disabled');
			jQuery(".cp-form-notice").hide();
		}
	}

}

})( jQuery );