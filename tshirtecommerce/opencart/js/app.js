var design_id = 0;
var app = {
	admin:{
		ini: function(){
			jQuery('#designer-products .tab-content a.modal-link').click(function(){
				var link = jQuery(this).attr('href');
				if(jQuery(this).hasClass('add-link')) {
					app.admin.add(this);
				} else {
					app.admin.load(link);
				}
				return false;
			});
		},
		product: function(e, index){
			if (document.getElementById('designer-products') == null) {
				var div = '<div class="modal fade" id="designer-products" tabindex="-1" role="dialog" style="z-index:10520;" aria-labelledby="myModalLabel" aria-hidden="true">'
						+ '<div class="modal-dialog modal-lg" style="width: 95%;">'
						+ 	'<div class="modal-content">'
						+		'<div class="modal-header">'
						+			'<button type="button" data-dismiss="modal" class="close close-list-design">'
						+				'<span aria-hidden="true">×</span>'
						+				'<span class="sr-only">Close</span>'
						+			'</button>'
						+		'</div>'
						+ 		'<div class="modal-body">'
						+		'&#65279;<center><h3>Please wait some time. loading...</h3></center>'
						+		'</div>'
						+	'</div>'
						+ '</div></div>';
				jQuery('body').append(div);
			}
			if(index != 4) {
				jQuery('#designer-products').modal('show');			
			}
			var key = e.getAttribute('key');			
			var data = {};
			data.key = key;
			data.action = 'designer_action';
			var link = ajaxurl.split('wp-admin');
			
			if (index == 0) { // show list product design 
				var url = tshirtURL+'admin-blank.php?/'+design_id;
			} else if (index == 2) { // show list product design template 
				var url = tshirtURL+'admin-users.php';
			} else if (index == 3) { // show list product design template 
				var url = tshirtURL+'admin-create.php';
			} else if (index == 4) { // create new design
				var url = tshirtURL+'admin/index.php?/product/viewmodal&session_id='+session_id;				
				jQuery('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
				return;
			} else {
				var url = tshirtURL+'admin.php';
			}
			jQuery.post(url, data, function(response) {
				jQuery('#designer-products .modal-body').html(response);
				app.admin.ini();
			});
			return false; 
		},		
		load: function(link)
		{
			var data = {};
			data.key = '1';
			data.action = 'designer_action';
			data.link = link;
			var link = ajaxurl.split('wp-admin');
			var url = link[0]+'tshirtecommerce/admin.php';
			jQuery('#designer-products .modal-body').html('&#65279;<center><h3>Please wait some time. loading...</h3></center>');
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#designer-products .modal-body').html(response);				
				app.admin.ini();
			});
			return false; 
		},
		add: function(e) {
			var id = jQuery(e).data('id');
			id = String(id);
			if (jQuery(e).hasClass('design-idea') == true) {
				var url = tshirtURL+'admin-template.php?product='+id+'&lightbox=1&session_id='+session_id;
			} else {
				if (id.indexOf(':') == -1) {
					var title = jQuery(e).data('title');
					var img = jQuery(e).children('img').attr('src');
					document.getElementById('_product_id').value = id;
					document.getElementById('_product_title_img').value = title+'::'+img;
					var url = tshirtURL + 'admin/index.php?/product/viewmodal/'+id+'&session_id='+session_id;
				} else {
					var params = id.split(':');
					var url = tshirtURL+'admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1&session_id='+session_id;
				}
			}
			var html = '<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>';
			jQuery('#add_designer_product').html(html);
			jQuery('#designer-products').modal('hide');
		},
		product_detail: function(){
			var product = {};
			if (jQuery('#input-name'+ts_lang_id).length) {
				product.title = jQuery('#input-name'+ts_lang_id).val();
			}

			if (jQuery('#input-description'+ts_lang_id).length) {
				product.description = (jQuery('#input-description'+ts_lang_id).val()).replace('"', '\"');
			} else {
				if (jQuery('#description'+ts_lang_id).length) {
					product.description = (jQuery('#description'+ts_lang_id).val()).replace('"', '\"');
				} else {
					product.description = '';
				}
			}
			product.shortdescription = '';
			var img = jQuery('#input-image');
			if (img.length > 0) {
				product.thumb = siteUrl+'/image/'+jQuery('#input-image').val();
			} else {
				var img_src = '';
				if (typeof jQuery('#image') !== 'undefined') {
					img_src = siteUrl + '/image/' + jQuery('#image').val();
				}
				product.thumb = img_src;
			}
			product.sku = jQuery('#input-sku').val();
			product.price = jQuery('#input-price').val();
			product.sale_price = '';
			product.prices = {};

			product.min_order = jQuery('#input-minimum').val();
			product.max_order = jQuery('#input-quantity').val();

			return product;
		},
		clear: function(){
			if (jQuery('#tshirtecommerce-designer').length > 0) {
				var check = confirm('You sure want clear data design of this product?');
				if (check == true) {
					document.getElementById('_product_id').value = '';
					document.getElementById('_product_title_img').value = '';
					jQuery('#add_designer_product').html('');
				}
			}
		},
		save: function(data, type){
			jQuery('#tshirtecommerce-wapper').hide();
			if (type == 'product') {
				document.getElementById('_product_id').value = data;
			} else if(type == 'idea') {
				var ids = data.designer_id+':'+data.design_id+':'+ data.product_id+':'+ data.productColor;
				document.getElementById('_product_id').value = ids;
			}
			if (jQuery('#form-product').length) {
				jQuery('#form-product').submit();
			}
			if (jQuery('#form').length) {
				jQuery('#form').submit();
			}
		}
	},
	cart: function(content){
		var data = {			
			product_id: content.product_id,
			quantity: content.quantity,
			design: content,
			//option_oc: option_oc,
		};
		var ocedit = window.parent.ocedit;
		if(ocedit !== 'undefined' && ocedit != '0' && parseInt(ocedit) != 'NaN' && parseInt(ocedit) > 0)
		{
			jQuery.ajax({
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + ocedit,
				dataType: 'json',
				success: function(json) {
					jQuery.ajax({
						url: 'index.php?route=checkout/cart/add',
						type: 'post',
						data: data,
						dataType: 'json',
						success: function (json) {
							window.location.href = 'index.php?route=checkout/cart';
						}
					});
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
		else
		{
			jQuery.ajax({
				url: 'index.php?route=checkout/cart/add',
				type: 'post',
				data: data,
				dataType: 'json',
				success: function (json) {
					window.location.href = 'index.php?route=checkout/cart';
				}
			});
		}
	}
}

var designer = {
	show: function(elm)
	{
		var div = jQuery(elm);
		
		var check = div.data('active');
		if (typeof check != 'undefined' && check == 1) {
			div.hide('slow');
			div.data('active', 0);
		} else {
			div.show('slow');
			div.data('active', 1);
		}
	},
	mobile: function(added){
		var div = jQuery('#tshirtecommerce-designer').parent();
		if(added == false)
		{
			jQuery('body').removeClass('tshirt-mobile');
			div.removeClass('design-mobile');
		}
		else
		{
			jQuery('body').addClass('tshirt-mobile');
			div.addClass('design-mobile');
		}
	},
	setSize: function(type){
		var div = jQuery('.row-designer-tool');
		if (div.length) {
			var postion = div.offset();
			var width = jQuery(document).width();
			div.css({
				'left': '-'+postion.left+'px',
				'width': width+'px',
			});
		}
	}
}

// save product of woocommerce
function wooSave(data, type)
{
	app.admin.save(data, type);
}

function setHeigh(height){
	height = height + 20;
	document.getElementById('tshirtecommerce-designer').setAttribute('height', height + 'px');
	
	height = height + 20;
	jQuery('#modal-designer').parents('body').css({'height':height+'px', 'max-height':height+'px'});
}

function getWidth()
{
	var width = jQuery(window).width();
	var sizeZoom = width/500;
	if (sizeZoom < 1) {
		jQuery('meta[name*="viewport"]').attr('content', 'width=device-width, initial-scale='+sizeZoom+', maximum-scale=1');
	}
}

// active link color
function loadProductDesign(e)
{
	if (typeof jQuery(e).data('color') != 'undefined') {
		var color = jQuery(e).data('color');
		var href = jQuery(e).attr('href');
		href = href + '&color='+color;
		window.location.href = href;
		return false;
	}
	return true;
}

// click change color in page product detail
function e_productColor(e)
{
	var parent = jQuery(e).parent();
	parent.children('.bg-colors').removeClass('active');
	
	// add data
	var elm = jQuery(e);
	jQuery('.designer_color_index').attr('name', 'colors['+elm.data('index')+']').val(elm.data('color'));
	jQuery('.designer_color_hex').val(elm.data('color'));
	jQuery('.designer_color_title').val(elm.attr('title'));
	
	jQuery('.e-custom-product').data('color', elm.data('color'));
	elm.addClass('active');
	jQuery(document).triggerHandler( "product.color.images", e);
}

function tshirt_attributes(e, index)
{
	var elm = jQuery(e);
	var type = elm.attr('type');
	var obj = elm.parent().children('.attribute_'+index);
	if (typeof type == 'undefined') {
		var value = elm.find('option:selected').data('id');
		obj.val(value);
	} else if (type == 'checkbox' || type == 'radio') {
		if (elm.is(':checked') == true) {
			obj.prop('checked', true);
		} else {
			obj.prop('checked', false);
		}
	} else {
		obj.val(elm.val());
	}
}

function viewBoxdesign(){
	var width = jQuery(document).width();
	var height = jQuery(document).height();
	if (width < 510 || height < 510) {
		var url = urlDesignload.replace('index.php', 'mobile.php');
			jQuery('body').append('<div id="modal-design-bg"></div><div id="modal-designer"><a href="'+urlBack+'" class="btn btn-dange btn-xs">Close</a><iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" width="100%" height="100%" src="'+url+'"></iframe></div>');
			jQuery('body').addClass('tshirt-mobile');
	} else {
		jQuery('.row-designer').html('<iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="100%" src="'+urlDesignload+'"></iframe>');
	}
	
	var url_option = urlDesignload.split('tshirtecommerce/');
	var mainURL = url_option[0];
	
	if (logo_loading.indexOf('http') == - 1) {
		logo_loading = mainURL + logo_loading;
	}
	
	jQuery('.row-designer').append('<div class="mask-loading">'
		+ '<div class="mask-main-loading">'
		+	'<img class="mask-icon-loading" src="'+mainURL+'tshirtecommerce/assets/images/logo-loading.gif" alt="">'
		+	'<img class="mask-logo-loading" src="'+logo_loading+'" alt="">'
		+ '</div>'
		+ '<p>'+text_loading+'</p>'
		+ '</div>'
	);
	
	jQuery("#tshirtecommerce-designer").load( function() {
		setTimeout(function(){
			jQuery('.row-designer .mask-loading').remove();
		}, 1000);
	});
}

function tshirt_close(){
	var href = jQuery('#modal-designer a').attr('href');
	window.location.href = href;
}
jQuery(document).ready(function(){
	design_id = jQuery('#_product_id').val();
	if (design_id == '') {
		design_id = 0;
	}
	var product_type = jQuery('#product-type').val();
	if (product_type == 'variable') {
		jQuery('.variations_options').children('a').trigger('click');
		jQuery('#tshirtecommerce_product a').trigger('click');
	} else {
		jQuery('#tshirtecommerce_product a').trigger('click');
	}
	
	if (jQuery('.row-designer').length > 0) {
		viewBoxdesign();
	}
	
	// active product color
	if (jQuery('.designer-attributes .list-colors .bg-colors').length > 0) {
		if (jQuery('.designer-attributes .list-colors .bg-colors.active').length == 0) {
			var a = jQuery('.designer-attributes .list-colors .bg-colors');
			e_productColor(a[0]);
		} else {
			var a = jQuery('.designer-attributes .list-colors .bg-colors.active');
			e_productColor(a[0]);
		}
	}
	
	// product size
	if (typeof min_order != 'undefined' && jQuery('.quantity .input-text.qty').length > 0) {		
		// check add to cart
		jQuery( document ).on( 'click', '.single_add_to_cart_button', function() {
			var value = jQuery('.quantity .input-text.qty').val();
			if (value < min_order) {
				alert(txt_min_order+' '+min_order);
				return false;
			}
		});
	}
	
	// change size
	jQuery('.p-color-sizes .size-number').on('change', function(){
		var value = jQuery(this).val();
		filter = /^[0-9]+$/;
		if (filter.test(value)) {
			if (value.indexOf('0') == 0) {
				jQuery(this).val(0);
			}
		} else {
			jQuery(this).val(0);
		}
		
		var quantity = 0;
		jQuery('.p-color-sizes .size-number').each(function(){
			quantity = quantity + Math.round(jQuery(this).val());
		});
		jQuery('.quantity .input-text.qty').val(quantity);
	});
	
	// save product in wooommerce
	jQuery('input#publish').click(function(){
		if (jQuery('#tshirtecommerce-designer').length > 0) {
			var iframe = document.getElementById("tshirtecommerce-designer");
			if (typeof iframe.contentWindow.productCategory !== 'undefined' && jQuery.isFunction(iframe.contentWindow.productCategory)) {
				// add product categories.
				function categories(evt, category, cate_id, parent_id, i, j){
					evt.children('li').each(function(){
						var text = jQuery(this).children('.selectit').text();
						var checked = jQuery(this).children('.selectit').children('input').is(':checked');
						var val = jQuery(this).children('.selectit').children('input').val();
						
						category[i] = {id:val, parent_id: parent_id, title:text};
						if (checked) {
							cate_id[j] = val;
							j++;
						}
						i++;
						
						if (jQuery(this).children('ul').hasClass('children')) {
							categories(jQuery(this).children('.children'), category, cate_id, val, i, j);
						}
					});
				};
				
				var cate_id = [],
				category = [];
				categories(jQuery('#product_catchecklist'), category, cate_id, 0, 0, 0);
				iframe.contentWindow.productCategory(cate_id, category);
			};
			
			// save products.
			var name = jQuery('#publish').attr('name');
			var value = jQuery('#publish').val();
			jQuery('#publishing-action').append('<input type="hidden" value="'+value+'" name="'+name+'"/>');
			
			var product_type = jQuery('#product-type').val();
			if (jQuery('#_regular_price').length > 0) {
				var price = jQuery('#_regular_price').val();
				if (price == '' && product_type == 'simple') {
					alert('Please add price of product');
					jQuery('.general_options').children('a').trigger('click');
					return false;
				} else if (product_type == 'variable') {
					if (jQuery('.variable_pricing .wc_input_price').length == 0) {
						alert('Please add variations of this product');
						jQuery('.variations_options').children('a').trigger('click');
						return false;
					} else {
						var input = jQuery('.variable_pricing .wc_input_price');
						var price = jQuery(input[0]).val();
						jQuery('#_regular_price').val(price);
						var prices = '';
						jQuery('.variable_pricing').each(function(){
							var input = jQuery(this).find('.wc_input_price');
							var price = 0;
							if (typeof input[0] != 'undefined') {
								price = jQuery(input[0]).val();
							}
							if (typeof input[1] != 'undefined' && jQuery(input[1]).val() != '') {
								price = jQuery(input[1]).val();
							}
							if (price != 0) {
								var index = jQuery(this).parents('.woocommerce_variation').find("input[name^='variable_post_id']").val();
								if (prices == '') {
									prices = '"'+index +'":"'+ price +'"';
								} else {
									prices = prices +','+ '"'+ index +'":"'+ price +'"';								
								}
							}							
						});
					}
				}
			}
			if (jQuery('#title').length > 0) {
				var title = jQuery('#title').val();
				if (title == '') {
					alert('Please add product name.');
					return false;
				}
			}
			var product = app.admin.product_detail();
			if (typeof prices != 'undefined' && prices != '') {
				product.prices	= '{'+prices+'}';
			}
			var check_validate = iframe.contentWindow.productInfo(product);
			if (check_validate) {
				jQuery('#tshirtecommerce_product a').trigger('click');
				jQuery(this).addClass('disabled');
				jQuery(this).parent().find('.spinner').addClass('is-active');
				jQuery(this).val('Saving...');
			} else {
				jQuery('#tshirtecommerce_product').children('a').trigger('click');
			}
			return false;
		}
	});
	
	// add box of product design
	if (jQuery('#_product_id').length > 0 && jQuery('#_disabled_product_design').val() != 1) {
		var id = jQuery('#_product_id').val();
		if (id != '') {
			var url = tshirtURL + 'admin/index.php?/product/viewmodal';
			if (id.indexOf(':') == -1) {
				url = url + '/' + id +'&session_id='+session_id;
			} else {
				var params = id.split(':');
				var url = tshirtURL+'admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1&session_id='+session_id;
			}
			
			jQuery('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
		}
	}
});

function resizePageDesign(e){
	var check = jQuery(e).hasClass('button-resize-full');
	if (check) {
		jQuery(e).removeClass('button-resize-full');
		jQuery(e).addClass('button-resize-small');
		jQuery(e).parent('#add_designer_product').addClass('e-full-screen');
		var height = jQuery('#add_designer_product').height() + 20;
		jQuery(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', height+'px');
		jQuery('body').css('overflow', 'hidden');
	} else {
		jQuery('body').css('overflow', 'auto');
		jQuery(e).removeClass('button-resize-small');
		jQuery(e).addClass('button-resize-full');
		jQuery(e).parent('#add_designer_product').removeClass('e-full-screen');
		jQuery(e).parent('#add_designer_product').attr('height', height);
		jQuery(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', '800px');
	}
};

function getfullWidth() {
	if (jQuery('#modal-designer').length > 0) {
		var width = jQuery('#modal-designer').width();
	} else {
		var width = jQuery('#tshirtecommerce-designer').width();
	}	
	
	return width;
}

function dg_full_screen()
{
	if (jQuery('body').hasClass('dg_screen')) {
		jQuery('body').removeClass('dg_screen');
		return 0;
	} else {
		jQuery('body').addClass('dg_screen');
		return 1;
	}
}

jQuery(document).ready(function(){
	if (typeof e_remove_cart_item != 'undefined' && e_remove_cart_item == true) {
		jQuery('.woocommerce-message').hide();
	}
	
	jQuery('.e_tshirt_add').click(function(){
		jQuery(this).children('.dropdown-menu').toggle();
	});
});