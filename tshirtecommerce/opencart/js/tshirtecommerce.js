function product_submit_link_click() {
	if (!jQuery('#link-tab-tshirtecommerce').parent().hasClass('active')) {
		jQuery('#link-tab-tshirtecommerce').click();
	}

	var validate_data = true;
	if (jQuery('#tshirtecommerce-designer').length > 0) {
		var iframe = document.getElementById("tshirtecommerce-designer");
		if (jQuery('#input-name'+ts_lang_id).length > 0) {
			var title = jQuery('#input-name'+ts_lang_id).val();
			if (title == '') {
				alert('Please add product name.');
				validate_data = false;
			}
		}
		if (jQuery('#input-meta-title'+ts_lang_id).length > 0) {
			var tag = jQuery('#input-meta-title'+ts_lang_id).val();
			if (tag == '') {
				alert('Please add meta tag title of product.');
				validate_data = false;
			}
		}
		if (jQuery('#input-model').length > 0) {
			var model = jQuery('#input-model').val();
			if (model == '') {
				alert('Please add model of product.');
				validate_data = false;
			}
		}
		var product = app.admin.product_detail();
		var check_validate = iframe.contentWindow.productInfo2(product);
		if (check_validate == true) {
			validate_data = true;
			if (jQuery('#_product_id').val() == '' && jQuery('#_product_title_img').val() == '') {
				jQuery.ajax({
					type: 'POST',
					processData: false,
					url: siteUrl + 'index.php?route=tshirtecommerce/designer_api_oc/get_product_id_last',
					dataType: 'text',
					async: false,
				}).done( function(data) {
					if (data != '' && data != '0') {
						jQuery('#_product_id').val(data);
						jQuery('#_product_title_img').val(product.title + '::' + product.thumb);
						if (validate_data == true) {
							jQuery('#product-submit-button').trigger('click');
						}
						return validate_data;
					}
				});
			}
		} else {
			validate_data = false;
		}
	}
	if (validate_data == true) {
		jQuery('#product-submit-button').trigger('click');
	}
	return validate_data;
};

function wooSave(data, type) {
	app.admin.save(data, type);
}