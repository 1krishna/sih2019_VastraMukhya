jQuery('.date').datetimepicker({
	pickTime: false
});

jQuery('.datetime').datetimepicker({
	pickDate: true,
	pickTime: true
});

jQuery('.time').datetimepicker({
	pickDate: false
});

jQuery('button[id^=\'button-upload\']').on('click', function() {
	var node = this;

	jQuery('#form-upload').remove();

	jQuery('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

	jQuery('#form-upload input[name=\'file\']').trigger('click');

	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}

	timer = setInterval(function() {
		if (jQuery('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);

			jQuery.ajax({
				url: _site_url + '/index.php?route=product/product/upload',
				type: 'post',
				dataType: 'json',
				data: new FormData(jQuery('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					jQuery(node).button('loading');
				},
				complete: function() {
					jQuery(node).button('reset');
				},
				success: function(json) {
					jQuery('.text-danger').remove();

					if (json['error']) {
						jQuery(node).parent().find('input').after('<div class="text-danger">' + json['error'] + '</div>');
					}

					if (json['success']) {
						alert(json['success']);

						jQuery(node).parent().find('input').attr('value', json['file']);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});

/* Validate when click Buy Now button */
function check_validation_oc() {
	var form_valid = true;

	// Validate each control
	jQuery('#oc_tool_cart').find('div.required').each( function() {
		// text area
		if(jQuery('.required textarea').length) {
			var textarea_val = jQuery('.required textarea').val();
			if(textarea_val.trim() == '') form_valid = false;
		}

		// input text
		if(jQuery('.required input[type="text"]').length) {
			var input_text_val = jQuery('.required input[type="text"]').val();
			if(jQuery('.required input[type="text"]').length > 0 && input_text_val == '') form_valid = false;
		}

		if(jQuery('.required > div').length) {
			var div_id = jQuery('.required > div').attr('id');
			if(div_id != '') {
				// Check radio
				if(jQuery('.required #'+div_id+' input[type="radio"]:checked').length) {
					var radio_val = jQuery('.required #'+div_id+' input[type="radio"]:checked').val();
					if(radio_val == '') form_valid = false;
				}

				// Check checkbox
				if(jQuery('.required #'+div_id+' input[type="checkbox"]:checked').length) {
					var checkbox_val = jQuery('.required #'+div_id+' input[type="checkbox"]:checked').val();
					if(checkbox_val == '') form_valid = false;
				}
			}
		}

		// Check upload file
		if(jQuery('.required input[type="hidden"]').length) {
			var input_upload_val = jQuery('.required input[type="hidden"]').val();
			if(input_upload_val.trim() == '') form_valid = false;
		}

		if(jQuery('.required select').length) {
			var select_id = jQuery('.required select').attr('id');
			var select_obj = jQuery(select_id);
			var select_val = jQuery("#" + select_id + " option:selected").val();
			if(select_val == null || select_val <= 0) form_valid = false;
		}
	});

	if(form_valid == false) alert('Product Options: (*) is required.');

	return form_valid;
}