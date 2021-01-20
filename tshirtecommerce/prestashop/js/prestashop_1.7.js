var tps17 = {
	saveDesignClick: function() {
		$('.product-footer').prop('disabled', true);
		var check_validate = false;
	    if ($("#tshirtecommerce-designer").length > 0) {
	        var iframe = document.getElementById("tshirtecommerce-designer");
	        var product = app.admin.product_detail();
	        check_validate = iframe.contentWindow.productInfo2(product);
	    }
	    
	    if (check_validate == true) $('#submit').click();
		$('.product-footer').prop('disabled', false);
	}
};
