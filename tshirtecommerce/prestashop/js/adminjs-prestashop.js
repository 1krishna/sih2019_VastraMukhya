var url_ajax_product = "/modules/tshirtecommerce/ajax.php?method=products";
var url_ajax_product_changed = "/modules/tshirtecommerce/ajax.php?method=changeproduct";
var url_ajax_options_ps = "/modules/tshirtecommerce/ajax.php?method=attributes";
//var url_ajax_categories = "/modules/tshirtecommerce/ajax?method=categories";

design.ajax.getPrice = function() {
    if(jQuery("#ps_color_pick_hidden").val() === 'undefined' || jQuery("#ps_color_pick_hidden").val() == "") {
        jQuery("#ps_color_pick_hidden").val(jQuery("#product-list-colors .bg-colors.active").attr("id-attribute"));
    }

    var datas = this.form();
    var options_ps = jQuery("#tool_cart_ps").serializeObject();
    var datas_ps = jQuery.extend(datas, options_ps);
    jQuery.ajax({
        url: mainURL + "modules/tshirtecommerce/ajax.php",
        type: "POST",
        data: {
            method: "prices",
            id: window.parent.ps_product_id,
            lang: window.parent.ps_language_id,
            shop: window.parent.ps_shop_id,
            attributes: datas_ps,
            id_customer: window.parent.ps_id_customer,
            id_currency: window.parent.ps_id_currency,
            id_country: window.parent.ps_id_country,
            id_group: window.parent.ps_id_group,
            quantity: jQuery("#quantity").val(),
        },
        dataType: "json",
        success: function(json) {
            datas.ps_price_sale = json.price;
            datas.ps_price_old = json.price;
            datas.ps_taxes = json.taxes;
            min_order = json.min_order;
            max_order = json.max_order;

            jQuery("#idCombination").val(json.ipa);
            //datas.ps_taxes = json.taxes;
            var lable = jQuery("#product-price .product-price-title");
            var div = jQuery("#product-price .product-price-list");
            var title = "";
            lable.html("Updating...");
            jQuery.ajax({
                type: "POST",
                processData: false,
                dataType: "json",
                url: siteURL + "ajax.php?type=prices",
                data: JSON.stringify(datas),
                contentType: "application/json; charset=utf-8"
            }).done(function(data) {
                if (data != "") {
                    if (typeof data.sale != "undefined") {
                        jQuery(document).triggerHandler("price.addtocart.design", data);
                        jQuery(".price-sale-number").html(data.sale);
                        jQuery(".price-old-number").html(data.old);
                        if (data.sale == data.old) {
                        	jQuery("#product-price-old").css("display", "none"); 
                        } else {
                       		jQuery("#product-price-old").css("display", "inline-block");
                       	}
                    }
                }
            }).always(function() {
                lable.html(title);
                design.print.colors();
            });
        }
    });
};

design.products.changeDesign = function(e, id) {
    jQuery('#app-wrap .product-design').html('');
    var ps_id = jQuery(e).attr('ps-product-id');
    product_id = id;
    if (typeof this.product[product_id] == "undefined") return;
    var product = this.product[product_id];
    jQuery(e).button("loading");
    // Get product of prestashop info
    jQuery.ajax({
        url: mainURL + url_ajax_product_changed,
        type: "POST",
        dataType: "json",
        data: {
            id: ps_id,
            //id: window.parent.ps_product_id,
            shop: window.parent.ps_shop_id,
            lang: window.parent.ps_language_id,
        }
    }).done(function(res) {
        c_parent_id = res.parent_id;
        product.parent_id = res.parent_id;
        window.parent.ps_product_id = res.parent_id;
        product.min_order = res.min_order;
        product.max_order = res.max_order;
        product.id = res.design_id;
        product.price = res.price;
        jQuery("#quantity").val(product.min_order);
        items["design"] = {};
        parent_id = product.parent_id;
        print_type = product.print_type;
        min_order = product.min_order;
        max_order = product.max_order;
        if (typeof max_order == "undefined" || max_order < min_order) max_order = 99999;
        var list_color = jQuery("#product-list-colors");
        list_color.html("");

        //if (typeof product.design.color_hex === 'undefined' && typeof product.design.design.color_hex !== 'undefined') {
        //   product.design = product.design.design;
        //}

        jQuery.each(product.design.color_hex, function(i, color) {
            // add color
            var span = document.createElement("span");
            if (i == 0) span.className = "bg-colors dg-tooltip active"; else span.className = "bg-colors dg-tooltip";
            span.setAttribute("data-original-title", product.design.color_title[i]);
            span.setAttribute("data-placement", "top");
            span.setAttribute("data-color", color);
            //id-attribute="<?php echo $product->design->id_attribute[$i]; ?>"
            //span.setAttribute("id-attribute", product.design.id_attribute[i])
            span.setAttribute("onclick", "design.products.changeColor(this, " + i + ")");
            var colors_hex = color.split(";");
            var a_width = 23 / colors_hex.length;
            for (jc = 0; jc < colors_hex.length; jc++) {
                var a_color = document.createElement("a");
                a_color.setAttribute("href", "javascript:void(0);");
                a_color.style.backgroundColor = "#" + colors_hex[jc];
                a_color.style.width = a_width + "px";
                span.appendChild(a_color);
            }
            list_color.append(span);
            items["design"][i] = {};
            items["design"][i]["color"] = color;
            items["design"][i]["title"] = product.design.color_title[i];
            if (typeof product.design.front != "undefined" && typeof product.design.front[i] != "undefined") {
                items["design"][i]["front"] = product.design.front[i]; 
            } else {
                items["design"][i]["front"] = "";
            }
            if (typeof product.design.back != "undefined" && typeof product.design.back[i] != "undefined")  {
                items["design"][i]["back"] = product.design.back[i]; 
            } else {
                items["design"][i]["back"] = "";
            }
            if (typeof product.design.left != "undefined" && typeof product.design.left[i] != "undefined")  {
                items["design"][i]["left"] = product.design.left[i]; 
            } else {
                items["design"][i]["left"] = "";
            }
            if (typeof product.design.right != "undefined" && typeof product.design.right[i] != "undefined")  {
                items["design"][i]["right"] = product.design.right[i]; 
            } else {
                items["design"][i]["right"] = "";
            }
            if (i > 0) jQuery("#e-change-product-color").show(); else jQuery("#e-change-product-color").hide();
            if (i > 5) {
                jQuery("#e-button-product-color").show();
                jQuery("#e-label-product-color").hide();
                jQuery("#product-list-colors").removeClass("in");
            } else {
                jQuery("#e-button-product-color").hide();
                jQuery("#e-label-product-color").css("display", "block");
                jQuery("#product-list-colors").addClass("in");
            }
            if (i > 5) // mobile.
            {
                jQuery("#e-button-product-color-mobile").show();
                jQuery("#e-label-product-color-mobile").hide();
                jQuery(".product-list-colors-mobile").removeClass("in");
            } else {
                jQuery("#e-button-product-color-mobile").hide();
                jQuery("#e-label-product-color-mobile").show();
                jQuery(".product-list-colors-mobile").addClass("in");
            }
        });
        items["area"] = product.design.area;
        items["params"] = product.design.params;
        jQuery("#product-attributes").html(product.attribute);
        design.item.designini(items);
        jQuery("#dg-products").modal("hide");
        jQuery(".dg-tooltip").tooltip();

        var view_active = design.products.viewActive();
        if (jQuery('#product-thumbs a.view-'+view_active).length)
            jQuery('#product-thumbs a.view-'+view_active).click();
        else
            jQuery('#product-thumbs a.view-front').click();

        if (jQuery("#product-attributes .size-number").length > 0) {
            //var min_quantity = jQuery('#quantity').val();
            var min_quantity = min_order;
            jQuery("#product-attributes .size-number").val("");
            var size = jQuery("#product-attributes .size-number");
            setTimeout(function() {
                size[0].setAttribute("value", min_quantity);
                jQuery(size[0]).val(min_quantity);
                jQuery("#quantity").val(min_quantity);
            }, 100);
        }
        jQuery("#product-attributes .size-number").keyup(function() {
            design.products.sizes();
        });
        jQuery("#quantity").keyup(function(e) {
            design.ajax.getPrice();
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });
        jQuery(document).triggerHandler("change.product.design", product);
        jQuery(document).triggerHandler("product.change.design", product);
        design.team.setup();
        design.team.save();
        // fix add quantity when change product.
        if (jQuery("#quantity").val() == 0) jQuery("#quantity").val(min_order);
        design.team.changeSize();
        jQuery("#product-attributes .size-number").click(function() {
            design.team.changeSize();
        });
        //design.ajax.getPrice(); // get price after add quantity.
        jQuery("#modal-product-info .product-detail-image").attr("src", baseURL + product.image);
        jQuery("#modal-product-info .product-detail-description").html(product.description);
        //jQuery("#modal-product-info .product-detail-description").html(product.description);
        jQuery("#modal-product-info .product-detail-title").html(product.title);
        jQuery("#modal-product-info .product-detail-id").html(product.id);
        jQuery("#modal-product-info .product-detail-sku").html(product.sku);
        jQuery("#modal-product-info .product-detail-short_description").html(product.short_description);
        jQuery(".product-detail-size").html(product.size);
        jQuery("#tool_cart_ps").remove();

        if (typeof window.parent.setHeigh !== 'undefined') {
            jQuery.ajax({
                type: "POST",
                dataType: "html",
                url: mainURL + url_ajax_options_ps,
                data: {
                    id: product.parent_id,
                    shop: window.parent.ps_shop_id,
                    lang: window.parent.ps_language_id
                }
            }).done(function(res_html) {
                jQuery("#product-details").append(res_html);
                jQuery(e).button("reset");
                window.parent.setHeigh(jQuery('#dg-wapper').height());
            });
        }
        design.ajax.getPrice();
    });
};

design.saveadmin = function(check) {
    if (design.ajax.isBlank() == false) return false;
    if (admin_id == 0) {
        is_save = 1;
        jQuery("#f-login").modal();
    } else {
        var view = "front";
        jQuery(".labView").each(function() {
            if (jQuery(this).find(".product-design").html() != "") {
                view = jQuery(this).attr("id").replace("view-", "");
                return false;
            }
        });
        if (jQuery(".labView.active .design-area").hasClass("zoom")) {
            design.tools.zoom();
        }
        if (admin_id == design.designer_id) {
            if (typeof check != "undefined" && check == 1) {
                jQuery("#save-design-info-admin").modal("hide");
            } else {
                jQuery("#save-design-info-admin").modal("show");
                return false;
            }
            design.svg.items(view, design.saveDesignAdmin);
        } else {
            if (typeof check != "undefined" && check == 1) {
                jQuery("#save-design-info-admin").modal("hide");
                jQuery("#dg-mask").css("display", "block");
                jQuery("#dg-designer").css("opacity", "0.3");
                design.svg.items(view, design.saveDesignAdmin);
            } else {
                jQuery("#save-design-info-admin").modal("show");
            }
        }
    }
};

design.saveDesignAdmin = function() {
	if (design.view != "done") {
	    //if (jQuery('#view-'+design.view+' .product-design').html() != '')
	    //{
	    if (design.view == "back") {
	        design.view = "left";
	        design.svg.items("back", design.saveDesignAdmin);
	        return false;
	    } else if (design.view == "left") {
	        design.view = "right";
	        design.svg.items("left", design.saveDesignAdmin);
	        return false;
	    } else if (design.view == "right") {
	        design.view = "done";
	        design.svg.items("right", design.saveDesignAdmin);
	        return false;
	    }
	} else {
	    var leftViewFlg = true, rightViewFlg = true;
	    if (jQuery("#view-left .product-design").html() == "") {
	        leftViewFlg = false;
	    }
	    if (jQuery("#view-right .product-design").html() == "") {
	        rightViewFlg = false;
	    }
	    if (design.view == "back") {
	        if (leftViewFlg) {
	            design.view = "right";
	            design.svg.items("left", design.saveDesignAdmin);
	            return false;
	        } else {
	            if (rightViewFlg) {
	                design.view = "done";
	                design.svg.items("right", design.saveDesignAdmin);
	                return false;
	            }
	        }
	    } else if (design.view == "left") {
	        if (rightViewFlg) {
	            design.view = "done";
	            design.svg.items("right", design.saveDesignAdmin);
	            return false;
	        }
	    }
	}
	var data = design.ajax.form();
	data.images = {};
	if (jQuery("#view-front .product-design").html() != "") {
	    if (design.isIE()) {
	        data.images.front = design.front.svgThum;
	    } else {
	        data.images.front = design.output.front.toDataURL();
	    }
	} else {
	    data.images.front = "";
	}
	if (jQuery("#view-back .product-design").html() != "") {
	    if (design.isIE()) {
	        data.images.back = design.back.svgThum;
	    } else {
	        data.images.back = design.output.back.toDataURL();
	    }
	} else {
	    data.images.back = "";
	}
	if (jQuery("#view-left .product-design").html() != "") {
	    if (design.isIE()) {
	        data.images.left = design.left.svgThum;
	    } else {
	        data.images.left = design.output.left.toDataURL();
	    }
	} else {
	    data.images.left = "";
	}
	if (jQuery("#view-right .product-design").html() != "") {
	    if (design.isIE()) {
	        data.images.right = design.right.svgThum;
	    } else {
	        data.images.right = design.output.right.toDataURL();
	    }
	} else {
	    data.images.right = "";
	}
	var vectors = JSON.stringify(design.exports.vector());
	var teams = JSON.stringify(design.teams);
	var productColor = design.exports.productColor();
	var thumb = "";
	if (data.images.front != "") {
	    thumb = data.images.front;
	} else if (data.images.back != "") {
	    thumb = data.images.back;
	} else if (data.images.left != "") {
	    thumb = data.images.left;
	} else if (data.images.right != "") {
	    thumb = data.images.right;
	}
	data.image = thumb;
	data.vectors = vectors;
	data.teams = teams;
	data.fonts = design.fonts;
	data.product_id = product_id;
    if (typeof window.parent.ps_id_product !== 'undefined') {
        parent_id = window.parent.ps_id_product;
    }
    data.parent_id = parent_id;
	data.design_id = design.design_id;
	data.design_file = design.design_file;
	data.designer_id = design.designer_id;
	data.design_key = design.design_key;
	data.product_color = productColor;
	data.isIE = design.isIE();
	data.title = jQuery("#design-save-title-admin").val();
	data.description = jQuery("#design-save-description-admin").val();
	jQuery(document).triggerHandler("before.save.design", data);
	jQuery.ajax({
	    url: siteURL + "ajaxps.php?type=saveDesignAdmin",
	    type: "POST",
	    contentType: "application/json",
	    data: JSON.stringify(data)
	}).done(function(msg) {
	    var results = eval("(" + msg + ")");
	    if (results.error == 1) {
	        alert(results.msg);
	    } else {
	        design.design_id = results.content.design_id;
	        design.design_file = results.content.design_file;
	        design.designer_id = results.content.designer_id;
	        design.design_key = results.content.design_key;
	        design.productColor = productColor;
	        design.product_id = product_id;
	        results.content.productColor = productColor;
	        results.content.product_id = product_id;
	        jQuery(document).triggerHandler("done.save.design", results.content);
	        var linkEdit = siteURL + "sharing.php?/" + results.content.user_id + ":" + results.content.design_key + ":" + product_id + ":" + productColor + ":" + parent_id;
	        jQuery("#link-design-saved").val(linkEdit);
	        jQuery("#dg-share").modal();
	    }
	    jQuery("#dg-mask").css("display", "none");
	    jQuery("#dg-designer").css("opacity", "1");
	});
};

jQuery(document).on('ini.design', function(event) {
	if (typeof window.parent.tshirtecommerce_admin === "undefined" && window.parent.tshirtecommerce_admin != 1) {
        $jd.ajax({
            url: mainURL + "modules/tshirtecommerce/ajax.php",
            type: "POST",
            data: {
                method: "attributes",
                id: window.parent.ps_product_id,
                lang: window.parent.ps_language_id,
                shop: window.parent.ps_shop_id
            },
            dataType: "html",
            success: function(json) {
                $jd("#product-details").append(json);
                jQuery(".price-restart").trigger("click");
            }
        });
    }
});

jQuery(document).on('after.load.design', function(event, data) {
    jQuery('#design-save-title-admin').val(data.design.title);
    jQuery('#design-save-description-admin').val(data.design.description);
});

jQuery(document).on('form.addtocart.design', function(event, datas) {
	var options_ps = jQuery("#tool_cart_ps").serializeObject();
    datas = jQuery.extend(datas, options_ps);
});

jQuery(document).on('after.addtocart.design', function(event, data) {
	if (data != "") {
	    var content = data;
	    if (content.error == 0) {
	        content.product.product_id = parent_id;
	        content.product.token = window.parent.token;
	        content.product.id_combination = datas.id_product_attribute;
	        content.product.design_imgs = "";
	        if (typeof data.product.images !== "undefined") {
	            content.product.design_imgs = data.product.images;
	        }
	        content.product.tshirtecommerce_design_cart_id = "";
	        if (typeof data.product.rowid !== "undefined") {
	            content.product.tshirtecommerce_design_cart_id = data.product.rowid;
	        }
	        window.parent.app.cart(content.product);
	    } else {
	        alert(content.msg);
	    }
	}
	return;
});

jQuery(document).on('price.addtocart.design', function(event, data) {
	var obj_old = '', obj_sale = '';
	jQuery('#product-price-old').html('');
	jQuery('#product-price-sale').html('');

	if (typeof data.old_symbol !== 'undefined' && typeof data.currency_symbol !== 'undefined') {
		if (typeof data.currency_postion !== 'undefined' && data.currency_postion == 'right') {
			obj_old = '<span class="price-sale-number">' + data.old + '</span>' + data.currency_symbol;
			obj_sale = '<span class="price-sale-number">' + data.sale + '</span>' + data.currency_symbol;
		} else {
			obj_old = data.currency_symbol + '<span class="price-sale-number">' + data.old + '</span>';
			obj_sale = data.currency_symbol + '<span class="price-sale-number">' + data.sale + '</span>';
		}
		
		jQuery('#product-price-old').html(obj_old);
		jQuery('#product-price-sale').html(obj_sale);
	}
});