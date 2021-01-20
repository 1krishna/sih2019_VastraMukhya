// From Tshirtecommerce version 4.1.5

var url_ajax_options_oc = 'index.php?route=tshirtecommerce/designer_api_oc/render_options';
var url_ajax_get_prices_oc = 'index.php?route=tshirtecommerce/designer_api_oc/get_prices';
var url_ajax_min_max_oc = 'index.php?route=tshirtecommerce/designer_api_oc/get_mim_max';
var url_ajax_product_changed = 'index.php?route=tshirtecommerce/products/get_product_changed';
var ocdatas;

design.ajax.getPrice = function() {
	var datas = this.form();
	var _product_id_oc = window.parent._product_id_oc;
	var options_oc = jQuery('#oc_tool_cart').serializeObject();
	var datas_oc = jQuery.extend(datas, options_oc);
	datas_oc._product_id_oc = _product_id_oc;

	var str_option_oc = '{';
	if (datas_oc.option_oc != undefined && datas_oc.option_oc != '') {
		for (var rw in datas_oc.option_oc) {
			if (datas_oc.option_oc.hasOwnProperty(rw)) {
				str_option_oc += '"'+rw+'":"'+datas_oc.option_oc[rw]+'",';
			}
		}
	}
	str_option_oc += '}';
	datas_oc.option_oc = str_option_oc;

	jQuery.ajax({
		type: "POST",
		dataType: "json",
		url: mainURL + url_ajax_get_prices_oc,
		data: { 'dataoc' : JSON.stringify(datas_oc) }
	}).done(function(data_oc) {
		var price_option_obj = { 
			price_option_oc: data_oc.price, 
			price_old_oc: data_oc.price_old, 
			price_sale_oc: data_oc.price_sale, 
			price_taxes: data_oc.taxes, 
			price_eco: data_oc.eco
		};
		datas = jQuery.extend(datas, price_option_obj);
		var lable = jQuery('#product-price .product-price-title');
		var div = jQuery('#product-price .product-price-list');
		var title = '';
		lable.html('Updating...');
		jQuery.ajax({
			type: "POST",
			processData: false,
			dataType: "json",
			url: siteURL + "ajax.php?type=prices",
			data: JSON.stringify(datas),
			contentType: "application/json; charset=utf-8",
		}).done(function( data ) {
			if (data != '') {
				if (typeof data.sale != 'undefined') {
					jQuery(document).triggerHandler("price.addtocart.design", data);
					jQuery('.price-sale-number').html(data.sale);
					jQuery('.price-old-number').html(data.old);
					if (data.sale == data.old) {
						jQuery('#product-price-old').css('display', 'none');
					} else {
						jQuery('#product-price-old').css('display', 'inline-block');
					}
				}
			}
		}).always(function(){
			lable.html(title);				
			design.print.colors();
		});
	});
};

design.ajax.addJs = function() {
	if (this.isBlank() == false) return false;
	
	jQuery.ajax({
		url: mainURL + 'index.php?route=tshirtecommerce/designer_api_oc/validate1564',
		type: 'post',
		data: jQuery('#quantity, #oc_tool_cart input[type=\'text\'], #oc_tool_cart input[type=\'hidden\'], #oc_tool_cart input[type=\'radio\']:checked, #oc_tool_cart input[type=\'checkbox\']:checked, #oc_tool_cart select, #oc_tool_cart textarea'),
		dataType: 'json',
		success: function(json) {
			if (json == '' || json['error'] == 1) {
				alert(json['msg']);
				return;
			} else {
				if (jQuery('.labView.active .design-area').hasClass('zoom')) {
					design.tools.zoom();
				}
				if (json['config_stock_checkout'] == 0) {
					var quantity = jQuery('#quantity').val();
						quantity = parseInt(quantity);
					if (isNaN(quantity) == true || quantity < 1) {
						alert(lang.designer.quantity);
						scrollQuantity();
						return false;
					}
					if (quantity < min_order) {
						alert(lang.designer.quantityMin + ' ' + min_order + '. ' + lang.designer.quantity);
						scrollQuantity();
						return false;
					}
					if (quantity > max_order) {
						alert(lang.designer.quantityMax + ' ' + max_order + '. ' + lang.designer.checkquantity);
						scrollQuantity();
						return false;
					}
				}
				design.mask(true);
				design.ajax.active = 'back';
				design.svg.items('front', design.ajax.save);
			}
		}
	});
};

design.ajax.admindesign = function(e) {
	if (admin_id == 0) {
		is_save = 0;
		jQuery('#f-login').modal();
	} else {				
		jQuery('#dg-admindesign').modal();
		var div = jQuery('#dg-admindesign .list-design-saved');
		div.addClass('loading');
		
		var datas = {};
		jQuery(document).triggerHandler( "before.save.design", datas);
		
		if (typeof e != 'undefined') {
			var page = jQuery(e).data('page');
			var $btn = jQuery(e).button('loading');
		} else {
			var page = 0;
		}
		
		datas.page = page;
		jQuery.ajax({
			type: "POST",
			url: siteURL + "ajax.php?type=adminDesign",
			data: {url: urlDesign, datas: datas},
			cache: false
		}).done(function( data ){
			jQuery(document).triggerHandler( "after.save.design", data);
			div.removeClass('loading');
			if (typeof $btn != 'undefined') {
				$btn.button('reset');
				var html = div.html();
				div.html(html + data);
			} else {
				div.html(data);
			}
			
			page = page + 1;
			if (jQuery('#dg-admindesign img').length > (page * 9) - 1) {
				jQuery('#dg-admindesign .modal-footer').css('display', 'block');
				jQuery('#dg-admindesign .modal-footer button').data('page', page);
			} else {
				jQuery('#dg-admindesign .modal-footer').css('display', 'none');
			}
				
			jQuery('#dg-admindesign .design-box a').each(function(){
				if (typeof jQuery(this).data('added') == 'undefined') {
					var href = jQuery(this).attr('href');
					if (href.indexOf('http') == -1) {
						href = urlDesign + href;
						jQuery(this).attr('href', href);
						jQuery(this).bind('click', function(event){
							event.preventDefault();
							window.parent.location = href;
						});
						jQuery(this).data('added', 1);
					}
				}
			});
		});
	}
};

design.ajax.removeDesignAdmin = function(e) {
	jQuery(document).triggerHandler( "before.remove.admindesign.design", e);
	jQuery(e).parents('.design-box').remove();
	var id = jQuery(e).data('id');
	jQuery.ajax({
		url: siteURL + "ajax.php?type=removeDesignAdmin&id=" + id
	}).done(function( data ){
		jQuery(document).triggerHandler( "after.remove.admindesign.design", data);
	});
};

design.products.changeDesign = function(e, id) {
	jQuery('#app-wrap .product-design').html('');
	product_id = id;
	
	if (typeof this.product[product_id] == 'undefined') return;
	
	var product = this.product[product_id];
	jQuery(e).button('loading');
	// Get product of opencart info
	jQuery.ajax({
		url: mainURL + url_ajax_product_changed,
		type: 'POST',
		dataType: 'json',
		data: {id: product_id}
	}).done(function(res){
		c_parent_id = res.parent_id;
		product.parent_id = res.parent_id;
		window.parent._product_id_oc = res.parent_id;
		
		product.min_order = res.min_order;
		product.max_order = res.max_order;
		product.id = res.design_id;
		product.price = res.price;
		jQuery('#quantity').val(product.min_order);

		items['design'] = {};
		parent_id = product.parent_id;
		print_type = product.print_type;
		min_order = product.min_order;
		max_order = product.max_order;
		if (typeof max_order == 'undefined' || max_order < min_order) max_order = 99999;
		var list_color = jQuery('#product-list-colors');
		list_color.html('');
		jQuery.each(product.design.color_hex, function(i, color){
			/* add color */
			var span = document.createElement('span');
				if (i == 0)	span.className = 'bg-colors dg-tooltip active';
				else span.className = 'bg-colors dg-tooltip';
				span.setAttribute('data-original-title', product.design.color_title[i]);
				span.setAttribute('data-placement', 'top');
				span.setAttribute('data-color', color);
				span.setAttribute('onclick', 'design.products.changeColor(this, ' + i + ')');
				
				var colors_hex = color.split(';');
				var a_width = 23 / colors_hex.length;
				for(jc = 0; jc < colors_hex.length; jc++) {
					var a_color = document.createElement('a');
					a_color.setAttribute('href', 'javascript:void(0);');
					a_color.style.backgroundColor = '#' + colors_hex[jc];
					a_color.style.width = a_width + 'px';
					span.appendChild(a_color);
				}
			list_color.append(span);
			
			items['design'][i] = {};
			items['design'][i]['color'] = color;
			items['design'][i]['title'] = product.design.color_title[i];
			if (typeof product.design.front != 'undefined' && typeof product.design.front[i] != 'undefined') {
				items['design'][i]['front'] = product.design.front[i];
			} else {
				items['design'][i]['front'] = '';
			}
			
			if (typeof product.design.back != 'undefined' && typeof product.design.back[i] != 'undefined') {
				items['design'][i]['back'] = product.design.back[i];
			} else {
				items['design'][i]['back'] = '';
			}
			
			if (typeof product.design.left != 'undefined' && typeof product.design.left[i] != 'undefined') {
				items['design'][i]['left'] = product.design.left[i];
			} else {
				items['design'][i]['left'] = '';
			}
			
			if (typeof product.design.right != 'undefined' && typeof product.design.right[i] != 'undefined') {
				items['design'][i]['right'] = product.design.right[i];
			} else {
				items['design'][i]['right'] = '';
			}
			
			if (i > 0) {
				jQuery('#e-change-product-color').show();
			} else {
				jQuery('#e-change-product-color').hide();
			}
			
			if (i > 5) {
				jQuery('#e-button-product-color').show();
				jQuery('#e-label-product-color').hide();
				jQuery('#product-list-colors').removeClass('in');
			} else {
				jQuery('#e-button-product-color').hide();
				jQuery('#e-label-product-color').css('display', 'block');
				jQuery('#product-list-colors').addClass('in');
			}
			if (i > 5) { // mobile. 
				jQuery('#e-button-product-color-mobile').show();
				jQuery('#e-label-product-color-mobile').hide();
				jQuery('.product-list-colors-mobile').removeClass('in');
			} else {
				jQuery('#e-button-product-color-mobile').hide();
				jQuery('#e-label-product-color-mobile').show();
				jQuery('.product-list-colors-mobile').addClass('in');
			}
		});
		items['area'] = product.design.area;
		items['params'] = product.design.params;
		jQuery('#product-attributes').html(product.attribute);
		
		design.item.designini(items);
		jQuery('#dg-products').modal('hide');
		jQuery('.dg-tooltip').tooltip();

		var view_active = design.products.viewActive();
		if (jQuery('#product-thumbs a.view-'+view_active).length)
			jQuery('#product-thumbs a.view-'+view_active).click();
		else
			jQuery('#product-thumbs a.view-front').click();
		
		if (jQuery('#product-attributes .size-number').length > 0) {
			//var min_quantity = jQuery('#quantity').val();
			var min_quantity = min_order;
			jQuery('#product-attributes .size-number').val('');
			var size = jQuery('#product-attributes .size-number');
			setTimeout(function(){
				size[0].setAttribute('value', min_quantity);
				jQuery(size[0]).val(min_quantity);
				jQuery('#quantity').val(min_quantity);
				design.ajax.getPrice();
			}, 100);
		}

		jQuery('#product-attributes .size-number').keyup(function(){
			design.products.sizes();
		});
		
		jQuery('#quantity').keyup(function(e){
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
		if(jQuery('#quantity').val() == 0) {
			jQuery('#quantity').val(min_order);
		}

		design.team.changeSize();
		jQuery('#product-attributes .size-number').click(function(){
			design.team.changeSize();
		});
		
		//design.ajax.getPrice(); // get price after add quantity.

		jQuery('#modal-product-info .product-detail-image').attr('src', baseURL + product.image);
		jQuery('#modal-product-info .product-detail-description').html(product.description);
		jQuery('#modal-product-info .product-detail-description').html(product.description);
		jQuery('#modal-product-info .product-detail-title').html(product.title);
		jQuery('#modal-product-info .product-detail-id').html(product.id);
		jQuery('#modal-product-info .product-detail-sku').html(product.sku);
		jQuery('#modal-product-info .product-detail-short_description').html(product.short_description);
		jQuery('.product-detail-size').html(product.size);

		var width = jQuery(window).width();
        if (width < 510) {
            design.mobile.areaDesign();
        }

		jQuery('#oc_tool_cart').remove();
		if (typeof window.parent.opencart_designer_tool === 'undefined') {
			jQuery.ajax({
				type: "POST",
				dataType: "html",
				url: mainURL + url_ajax_options_oc,
				data: { id: product.parent_id },
			}).done(function(res_html){
				jQuery('#product-details').append(res_html);
				window.parent.setHeigh(jQuery('#dg-wapper').height());
				jQuery(e).button('reset');
			});
		}
		
		design.ajax.getPrice();
	});
};

design.products.childCate = function(e) {
	var seft = this;
	if (typeof seft.categories != 'undefined' & typeof seft.categories[e.value] != 'undefined'){
		seft.addCatogory(e, seft.categories[e.value]);
		return;
	}
	jQuery(e).addClass('loading_sm');
	jQuery.ajax({
		type: "POST",
		dataType: "json",
		url: mainURL + 'index.php?route=tshirtecommerce/designer/categories',
		data: { parent_id: e.value }
	}).done(function(data) {
		if (data.error == 0) {
			seft.categories[e.value] = data.categories;
			seft.addCatogory(e, seft.categories[e.value]);
		}
	}).always(function(){
		jQuery(e).removeClass('loading_sm');
	});
}

design.saveDesignAdmin = function() {
	if (design.view != 'done') {
		if (jQuery('#view-' + design.view + ' .product-design').html() != '') {
			if (design.view == 'back') {
				design.view = 'left';
				design.svg.items('back', design.saveDesignAdmin);
				return false;
			}
			else if (design.view == 'left') {
				design.view = 'right';
				design.svg.items('left', design.saveDesignAdmin);
				return false;
			} else if (design.view == 'right') {
				design.view = 'done';
				design.svg.items('right', design.saveDesignAdmin);
				return false;
			}
		}			
	} else {
		var leftViewFlg = true, rightViewFlg = true;
		if (jQuery('#view-left .product-design').html() == '') {
			leftViewFlg  = false;
		}
		if (jQuery('#view-right .product-design').html() == '') {
			rightViewFlg = false;
		}
		if (design.view == 'back') {
			if (leftViewFlg) {
				design.view = 'right';
				design.svg.items('left', design.saveDesignAdmin);
				return false;
			} else {
				if (rightViewFlg) {
					design.view = 'done';
					design.svg.items('right', design.saveDesignAdmin);
					return false;
				}
			}
		} else if(design.view == 'left') {
			if (rightViewFlg) {
				design.view = 'done';
				design.svg.items('right', design.saveDesignAdmin);
				return false;
			}
		}
	}
	var data = design.ajax.form();
	data.images = {};
	
	if (jQuery('#view-front .product-design').html() != '') {
		if (design.isIE()) {
			data.images.front = design.front.svgThum;
		} else {
			data.images.front = design.output.front.toDataURL();
		}
	} else {
		data.images.front = '';
	}
	
	if (jQuery('#view-back .product-design').html() != '') {
		if (design.isIE()) {
			data.images.back = design.back.svgThum;
		} else {
			data.images.back = design.output.back.toDataURL();
		}
	} else {
		data.images.back = '';
	}

	if (jQuery('#view-left .product-design').html() != '') {
		if (design.isIE()) {
			data.images.left = design.left.svgThum;
		} else {
			data.images.left = design.output.left.toDataURL();
		}
	} else {
		data.images.left = '';
	}

	if (jQuery('#view-right .product-design').html() != '') {
		if (design.isIE()) {
			data.images.right = design.right.svgThum;
		} else {
			data.images.right = design.output.right.toDataURL();
		}
	} else {
		data.images.right = '';
	}
	
	var vectors	= JSON.stringify(design.exports.vector());
	var teams = JSON.stringify(design.teams);
	var productColor = design.exports.productColor();
	
	var thumb = '';	
	if (data.images.front != '') {
		thumb = data.images.front;
	} else if(data.images.back != '') {
		thumb = data.images.back;
	} else if(data.images.left != '') {
		thumb = data.images.left;
	} else if(data.images.right != '') {
		thumb = data.images.right;
	}

	data.image			= thumb;
	data.vectors		= vectors;
	data.teams			= teams;
	data.fonts			= design.fonts;
	data.product_id		= product_id;
	data.parent_id		= parent_id;
	data.design_id		= design.design_id;
	data.design_file	= design.design_file;
	data.designer_id	= design.designer_id;
	data.design_key		= design.design_key;
	data.product_color	= productColor;	
	
	data.isIE			= design.isIE();
	data.title			= jQuery('#design-save-title-admin').val();		
	data.description	= jQuery('#design-save-description-admin').val();		
	
	jQuery(document).triggerHandler( "before.save.design", data);
	
	jQuery.ajax({
		url: siteURL + "ajaxo.php?type=saveDesignAdmin",
		type: "POST",
		contentType: 'application/json',
		data: JSON.stringify(data),
	}).done(function( msg ) {
		var results = eval ("(" + msg + ")");
		if (results.error == 1) {
			alert(results.msg);
		} else {
			design.design_id = results.content.design_id;
			design.design_file = results.content.design_file;
			design.designer_id = results.content.designer_id;
			design.design_key = results.content.design_key;
			design.productColor = productColor;
			design.product_id = product_id;

			results.content.productColor	= productColor;
			results.content.product_id		= product_id;
			jQuery(document).triggerHandler( "done.save.design", results.content);
			
			var linkEdit 	= siteURL + 'sharing.php/'+results.content.user_id+':'+results.content.design_key+':'+product_id+':'+productColor+':'+parent_id;			
			jQuery('#link-design-saved').val(linkEdit);
			jQuery('#dg-share').modal();				
		}
		
		jQuery('#dg-mask').css('display', 'none');
		jQuery('#dg-designer').css('opacity', '1');
	});	
};

design.saveadmin = function(check) {
	if (design.ajax.isBlank() == false) return false;
	if (admin_id == 0) {
		is_save = 1;
		jQuery('#f-login').modal();			
	} else {
		var view = 'front';
		jQuery('.labView').each(function() {
			if (jQuery(this).find('.product-design').html() != '') {
				view = jQuery(this).attr('id').replace('view-', '');
				return false;
			}
		});
		if (jQuery('.labView.active .design-area').hasClass('zoom')) {
			design.tools.zoom();
		}
		if (admin_id == design.designer_id) {
			if (typeof check != 'undefined' && check == 1) {
				jQuery('#save-design-info-admin').modal('hide');
			} else {
				jQuery('#save-design-info-admin').modal('show');
				return false;
			}
			design.svg.items(view, design.saveDesignAdmin);
		} else {
			if (typeof check != 'undefined' && check == 1) {
				jQuery('#save-design-info-admin').modal('hide');
				jQuery('#dg-mask').css('display', 'block');
				jQuery('#dg-designer').css('opacity', '0.3');
				design.svg.items(view, design.saveDesignAdmin);
			} else {
				jQuery('#save-design-info-admin').modal('show');
			}
		}
	}
};

jQuery(document).on('ini.design', function(event) {
	url_ajax_product = 'index.php?route=tshirtecommerce/designer/ajax';

	if (typeof window.parent.opencart_designer_tool === 'undefined') {
		if (typeof window.parent.setHeigh !== 'undefined') {
			jQuery.ajax({
				type: "POST",
				dataType: "html",
				url: mainURL + url_ajax_options_oc,
				data: { id: parent_id }
			}).done(function(data) {
				if (data != '') {
					jQuery('#product-details').append(data);
					window.parent.setHeigh(jQuery('#dg-wapper').height());
				}

				getNewHeightChange();
			});
		}
	}
});

jQuery(document).on('form.addtocart.design', function(event, datas) {
	var options_oc = jQuery('#oc_tool_cart').serializeObject();
	datas = jQuery.extend(datas, options_oc);
	ocdatas = datas;
});

jQuery(document).on('after.addtocart.design', function(event, data) {
	if (data != '') {
		var content = data;
		var str = '';
		if (ocdatas.option_oc) {
			for (var i = 0; i < ocdatas.option_oc.length; i++) {
				if (typeof ocdatas.option_oc[i] != 'undefined' && ocdatas.option_oc[i] != null) {
					str += i + '::' + ocdatas.option_oc[i] + ';;';
				}
			}
		}
		if (typeof ocdatas.product_id !== 'undefined') {
			content.product.t_product_id = ocdatas.product_id;
		}
		if (typeof ocdatas.otp !== 'undefined') {
			content.product.otp = ocdatas.otp;
		}
		if (typeof ocdatas.swap !== 'undefined') {
			content.product.swap = ocdatas.swap;
		}
		content.product.option_oc = str;
		content.product.tattributes = jQuery('#tool_cart').serializeObject();
		if (content.error == 0) {
			content.product.product_id = parent_id;
			content.product.refer = jQuery('#oc_tool_cart input[name=refer]').val();
		} else {
			alert(content.msg);
		}
	}
});

// Fixed size of big design
jQuery(document).on('after.load.design', function(event, data) {
    getNewHeightChange();
});
jQuery(document).on('product.change.design', function(event, product) {
    getNewHeightChange();
});
function getNewHeightChange() {
	var width = jQuery(window).width();
    if (width > 510) {
	    var new_width = 500, new_height = 500;

	    var area_height = jQuery('.design-area').height() + parseInt(jQuery('.design-area').css('top'));
	    if (area_height > new_height) {
	    	new_height = area_height;
	    }
	    var area_width = jQuery('.design-area').width() + parseInt(jQuery('.design-area').css('left'));
	    if (area_width > new_width) {
	    	new_width = area_width;
	    }

	    jQuery('.product-design img').each(function() {
	        img_width = jQuery(this).width();
	        if (img_width > new_width) {
	            new_width = img_width;
	        }
	        img_height = jQuery(this).height();
	        if (img_height > new_height) {
	            new_height = img_height;
	        }
	    });

	    jQuery('.div-design-area .labView').css({
	        'width': new_width+'px',
	        'height': new_height+'px',
	    });
	}
}