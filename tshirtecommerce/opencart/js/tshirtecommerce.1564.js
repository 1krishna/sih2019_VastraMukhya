function product_submit_link_click() {
	if (!$('#link-tab-tshirtecommerce').parent().hasClass('active')) {
		$('#link-tab-tshirtecommerce').click();
	}

	var validate_data = true;
	if ($('#tshirtecommerce-designer').length > 0) {
		var iframe = document.getElementById("tshirtecommerce-designer");
		var product = app.admin.product_detail();
		var check_validate = iframe.contentWindow.productInfo2(product);
		if (check_validate == true) {
			validate_data = true;
			if ($('#_product_id').val() == '' && $('#_product_title_img').val() == '') {
				$.ajax({
					type: 'POST',
					processData: false,
					url: siteUrl + 'index.php?route=tshirtecommerce/designer_api_oc/get_product_id_last',
					dataType: 'text',
					async: false,
				}).done( function(data) {
					if (data != '' && data != '0') {
						$('#_product_id').val(data);
						$('#_product_title_img').val(product.title + '::' + product.thumb);
						if (validate_data == true) {
							//$('#product-submit-button').trigger('click');
							$('#form').submit();
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
		//$('#product-submit-button').trigger('click');
		$('#form').submit();
	}
	return validate_data;
};

function wooSave(data, type) {
	app.admin.save(data, type);
}