function product_submit_link_click(param) {
    var validate_data = true;
    if (jQuery("#tshirtecommerce-designer").length > 0) {
        var iframe = document.getElementById("tshirtecommerce-designer");
        var product = app.admin.product_detail();
        // get product
        var check_validate = iframe.contentWindow.productInfo2(product);
        // save data to tshirtecommerce
        if (check_validate) {
            validate_data = true;
        } else {
            validate_data = false;
        }
    }
    if (validate_data == true) {
        if (param == 1) {
            jQuery("#_submitAddproductAndStay").trigger("click");
        }
        if (param == 2) {
            jQuery("#_submitAddproduct").trigger("click");
        }
        if (param == 151) {
            jQuery('#desc-product-save-and-stay').trigger('click');
        }
        if (param == 152) {
            jQuery('#desc-product-save').trigger('click');
        }
    }
    return validate_data;
}