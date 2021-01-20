var quicksetup = {
	submit: function(method) {
		var layout = jQuery('select[name=layout]').val();
		var language = jQuery('select[name=language]').val();
		$.ajax({
			url: ajax_tool,
			type: 'POST',
			dataType: 'json',
			data: {
				method: method,
				layout: layout,
				language: language
			},
			cache: false
		})
		.done(function(json) {
			jQuery('#'+method).addClass('e-step-active');
				if (method == 'quicksetup2') {
				jQuery('#e-design-tool').css('display', 'none');
				jQuery('#e-cliparts-store').css('display', 'block');
				jQuery('#progress-bar-step').attr('aria-valuenow', 50);
				jQuery('#progress-bar-step').css('width', '50%');
			}
		})
		.fail(function() {
			console.log("error");
		});
	},
	download: function(e) {
		var api = jQuery('#store-api').val();
		if(api == '') {
			alert('Please enter your API');
			return false;
		}
		jQuery(e).attr('disabled', 'true');
		jQuery('.text-status').html('Processing...');
		jQuery('#download-store2').show('slow');
		var width = 1;
    	var id = setInterval(frame, 100);
    	function frame() {
	        if (width >= 100) {
	            clearInterval(id);
	        } else {
	            width++;
	            jQuery('#progress-bar2').css('width', width + '%'); 
	        }
	    }
	    var api_key = jQuery('#store-api').val();
	    $.ajax({
	    	url: ajax_clipart,
	    	type: 'POST',
	    	dataType: 'json',
	    	data: {
	    		method: 'quicksetup3',
	    		api_key: api_key,
	    	}
	    })
	    .done(function(json) {
	    	if (json != '') {
	    		clearInterval(id);
	    		if (json.error == 1) {
	    			jQuery('#text-status2').html(json.msg);
					jQuery(e).removeAttr('disabled');
	    		} else {
	    			jQuery('#e-cliparts-store').css('display', 'none');
	    			jQuery('#e-import-products').css('display', 'block');
	    			jQuery('#progress-bar-step').attr('aria-valuenow', 75);
					jQuery('#progress-bar-step').css('width', '75%');
	    		}
	    	}
	    })
	    .fail(function() {
	    	clearInterval(id);
			jQuery('#text-status2').html('Your API invalid, please check and try again.');
			jQuery(e).removeAttr('disabled');
	    });
	    
	},
	import: function(e) {
		jQuery(e).attr('disabled', 'true');
		jQuery('#download-store3').show('slow');
		var width = 1;
	    var id = setInterval(frame, 300);
	    function frame() {
	        if (width >= 100) {
	            clearInterval(id);
	        } else {
	            width++;
	           jQuery('#progress-bar3').css('width', width + '%'); 
	        }
	    }

	    $.ajax({
	    	url: ajax_import,
	    	type: 'POST',
	    	dataType: 'json',
	    	data: { method: 'quicksetup4' },
	    })
	    .done(function(json) {
	    	if (json != '') {
	    		clearInterval(id);
	    		if (json.error == 1) {
	    			jQuery('#text-status3').html(json.msg);
					jQuery(e).removeAttr('disabled');
	    		} else {
	    			jQuery('#e-import-products').css('display', 'none');
	    			jQuery('#e-finished').css('display', 'block');
	    			jQuery('#progress-bar-step').attr('aria-valuenow', 100);
					jQuery('#progress-bar-step').css('width', '100%');
	    		}
	    	}
	    })
	    .fail(function() {
	    	clearInterval(id);
			jQuery('#text-status3').html('Import data error!');
			jQuery(e).removeAttr('disabled');
	    });
	    
	},
	finish: function() {

	},
	skip: function(b, a, p) {
		jQuery('#e-'+b).css('display', 'block');
		jQuery('#e-'+a).css('display', 'none');
		jQuery('#progress-bar-step').attr('aria-valuenow', p);
		jQuery('#progress-bar-step').css('width', p+'%');
	},
	back: function(b, a, p) {
		jQuery('#e-'+b).css('display', 'block');
		jQuery('#e-'+a).css('display', 'none');
		jQuery('#progress-bar-step').attr('aria-valuenow', p);
		jQuery('#progress-bar-step').css('width', p+'%');
	}
}