var quicksetup = {
	submit: function(method) {
		var layout = $('select[name=layout]').val();
		var language = $('select[name=language]').val();
		var currency = $('select[name=currency]').val();
		$.ajax({
			url: quicksetup_url+'/modules/tshirtecommerce/ajax.php',
			type: 'POST',
			dataType: 'json',
			data: {
				method: method,
				layout: layout,
				language: language,
				currency: currency
			},
			cache: false
		})
		.done(function(json) {
			if (json != '') {
				if (json.error == 1) {
					alert(json.msg);
					return;
				} else {
					console.log(json.msg);
					$('#'+method).addClass('e-step-active');
						if (method == 'quicksetup2') {
						$('#step-quicksetup1').css('display', 'none');
						$('#step-quicksetup2').css('display', 'block');
						$('#progress-bar-step').attr('aria-valuenow', 66.6666);
						$('#progress-bar-step').css('width', '66.6666%');
					}
				}
			}
		})
		.fail(function() {
			console.log("error");
		});
	},
	verify: function(e) {
		var purchased_code = $('#purchased_code').val();
		$.ajax({
			type: 'post',
			url: quicksetup_url+'/modules/tshirtecommerce/ajax_quicksetup.php',
			dataType: 'json',
			data: {
				'task': 'verify',
				'code': purchased_code
			},
			cache: false,
			success: function(json) {
				if (json.error == 1) {
					alert(json.msg);
					return;
				} else {
					$('#quicksetup3').addClass('e-step-active');
					$('#step-quicksetup2').css('display', 'none');
					$('#step-quicksetup3').css('display', 'block');
					$('#progress-bar-step').attr('aria-valuenow', 100);
					$('#progress-bar-step').css('width', '100%');
				}
			}
		});
	},
	download: function(e) {
		var api = jQuery('#store-api').val();
		if(api == '') {
			alert('Please enter your API');
			return false;
		}
		$(e).attr('disabled', 'true');
		$('.text-status').html('Processing...');
		$('#download-store2').show('slow');
		var width = 1;
    	var id = setInterval(frame, 100);
    	function frame() {
	        if (width >= 100) {
	            clearInterval(id);
	        } else {
	            width++; 
	            //$('.download-store .progress-bar').css('width', width + '%'); 
	            $('#progress-bar2').css('width', width + '%'); 
	        }
	    }
	    var api_key = $('#store-api').val();
	    $.ajax({
	    	url: quicksetup_url+'/modules/tshirtecommerce/ajax.php',
	    	type: 'POST',
	    	dataType: 'json',
	    	data: {
	    		method: 'quicksetup3',
	    		api_key: api_key,
	    	}
	    })
	    .done(function(json) {
	    	console.log("success");
	    	if (json != '') {
	    		clearInterval(id);
	    		if (json.error == 1) {
	    			$('#text-status2').html(json.msg);
					$(e).removeAttr('disabled');
	    		} else {
	    			$('#step-quicksetup2').css('display', 'none');
	    			$('#step-quicksetup3').css('display', 'block');
	    			$('#progress-bar-step').attr('aria-valuenow', 75);
					$('#progress-bar-step').css('width', '75%');
	    		}
	    	}
	    })
	    .fail(function() {
	    	clearInterval(id);
			$('#text-status2').html('Your API invalid, please check and try again.');
			$(e).removeAttr('disabled');
	    });
	    
	},
	import: function(e) {
		$(e).attr('disabled', 'true');
		$('#download-store3').show('slow');
		var width = 1;
	    var id = setInterval(frame, 300);
	    function frame() {
	        if (width >= 100) {
	            clearInterval(id);
	        } else {
	            width++; 
	           // $('.download-store .progress-bar').css('width', width + '%'); 
	           $('#progress-bar3').css('width', width + '%'); 
	        }
	    }

	    $.ajax({
	    	url: quicksetup_url+'/modules/tshirtecommerce/ajax.php',
	    	type: 'POST',
	    	dataType: 'json',
	    	data: {
	    		method: 'quicksetup4'
	    	},
	    })
	    .done(function(json) {
	    	console.log("success");
	    	if (json != '') {
	    		clearInterval(id);
	    		if (json.error == 1) {
	    			$('#text-status3').html(json.msg);
					$(e).removeAttr('disabled');
	    		} else {
	    			$('#step-quicksetup3').css('display', 'none');
	    			$('#step-quicksetup4').css('display', 'block');
	    			$('#progress-bar-step').attr('aria-valuenow', 100);
					$('#progress-bar-step').css('width', '100%');
	    		}
	    	}
	    })
	    .fail(function() {
	    	clearInterval(id);
			$('#text-status3').html('Import data error!');
			$(e).removeAttr('disabled');
	    });
	    
	},
	finish: function() {

	},
	skip: function(b, a, p) {
		$('#step-'+b).css('display', 'block');
		$('#step-'+a).css('display', 'none');
		$('#progress-bar-step').attr('aria-valuenow', p);
		$('#progress-bar-step').css('width', p+'%');
	},
	back: function(b, a, p) {
		$('#step-'+b).css('display', 'block');
		$('#step-'+a).css('display', 'none');
		$('#progress-bar-step').attr('aria-valuenow', p);
		$('#progress-bar-step').css('width', p+'%');
	}
}