if (typeof baseUri === "undefined" && typeof baseDir !== "undefined") {
	baseUri = baseDir;
}

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
				var div = '<div class="modal fade bootstrap" id="designer-products" tabindex="-1" role="dialog" style="z-index:10520;" aria-labelledby="myModalLabel" aria-hidden="true">'
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
			if (index != 4) {
				jQuery('#designer-products').modal('show');			
			}
			var key = e.getAttribute('key');			
			var data = {};
			data.key = key;
			data.action = 'designer_action';
			
			if (index == 0) {
				var url = tshirtURL + 'admin-blank.php';
			} else if (index == 2) {
				var url = tshirtURL + 'admin-users.php';
				jQuery("#tshirtecommerce_design_type").val("design");
			} else if (index == 3) {
				var url = tshirtURL + 'admin-create.php';
				jQuery("#tshirtecommerce_design_type").val("admin");
			} else if (index == 4) {
				var url = tshirtURL + 'admin/index.php?/product/viewmodal';
				jQuery('#add_designer_product').html('<iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
				return;
			} else {
				var url = tshirtURL + 'admin.php';
				jQuery("#tshirtecommerce_design_type").val("admin");
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
			var link = tshirtURL;
			var url = link + 'admin.php';
			data.link = link;

			jQuery("#tshirtecommerce_design_type").val("admin");
			jQuery('#designer-products .modal-body').html('&#65279;<center><h3>Please wait some time. loading...</h3></center>');
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#designer-products .modal-body').html(response);				
				app.admin.ini();
			});
			return false; 
		},
		add: function(e)
		{
			var id = jQuery(e).data('id');
			id = String(id);
			if (jQuery(e).hasClass('design-idea') == true) {
				var url = tshirtURL + '/admin-template.php?product='+id+'&lightbox=1';
			} else {
				if (id.indexOf(':') == -1) {
					var title = jQuery(e).data('title');
					var img = jQuery(e).children('img').attr('src');

					document.getElementById('_product_id').value = id;
					document.getElementById('_product_title_img').value = title +'::'+ img;

					var url = tshirtURL + '/admin/index.php?/product/viewmodal/'+id;
				} else {
					var params = id.split(':');
					var url = tshirtURL + '/admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1';
				}
			}
			var html = '<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>';
			jQuery('#add_designer_product').html(html);
			jQuery('#designer-products').modal('hide');
		},
		product_detail: function(){
			var product = {};
			product.title = product_title;
			product.description = product_description;
			product.shortdescription = product_short_description;
			product.thumb = product_thumb;
			product.sku = product_sku;
			product.price = product_price;
			product.sale_price = '';
			product.prices = {};
			product.min_order = product_min_order;
			product.max_order = 99999;
			
			return product;
		},
		clear: function(){
			if (jQuery('#tshirtecommerce-designer').length > 0)
			{
				var check = confirm('You sure want clear data design of this product?');
				if (check == true)
				{
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
				var ids = data.designer_id +':'+ data.design_id +':'+ data.product_id +':'+ data.productColor;
				document.getElementById('_product_id').value = ids;
			}
			if (jQuery('#desc-product-save').length) {
				jQuery('#desc-product-save').trigger('click');
			} else {
				jQuery('#_submitAddproduct').trigger('click');
			}
		}
	},
	cart: function(content){
		var data = {
			product_id: content.product_id,
			quantity: ((content.quantity && content.quantity != null) ? content.quantity : 1),
			design: content,
			id_combination: content.id_combination,
			attributes: content.attributes,
			tshirtecommerce_color: content.tshirtecommerce_color,
			tshirtecommerce_product_id: content.tshirtecommerce_product_id,
		};

		if(parseInt(data.id_combination) && data.id_combination != null) {
			var _data = {
				'controller': 'cart',
				'add': 1,
				'ajax': true,
				'qty': data.quantity,
				'id_product': data.product_id,
				'tshirtecommerce_design_cart_img': content.design_imgs,
				'tshirtecommerce_design_cart_id': content.tshirtecommerce_design_cart_id,
				'token': token,
				'design_tool' : 1,
				'ipa': parseInt(data.id_combination),
				'tattributes': data.attributes,
				'tshirtecommerce_color': data.tshirtecommerce_color,
				'tshirtecommerce_product_id': data.tshirtecommerce_product_id,
			};
		}
		else
		{
			var _data = {
				'controller': 'cart',
				'add': 1,
				'ajax': true,
				'qty': data.quantity,
				'id_product': data.product_id,
				'tshirtecommerce_design_cart_img': content.design_imgs,
				'tshirtecommerce_design_cart_id': content.tshirtecommerce_design_cart_id,
				'design_tool' : 1,
				'token': token,
				'tattributes': data.attributes,
				'tshirtecommerce_color': data.tshirtecommerce_color,
				'tshirtecommerce_product_id': data.tshirtecommerce_product_id,
			};
		}

		jQuery.ajax({
			headers: { "cache-control": "no-cache" },
			url: baseUri + '?rand=' + new Date().getTime(),
			type: 'POST',
			async: true,
			cache: false,
			data: _data,
			dataType: 'json',
			success: function (json) {
				if(json.hasError == true) {
					alert(json.errors[0]);
					var iframe = document.getElementById("tshirtecommerce-designer");
					iframe.contentWindow.design.mask(false);
				} else {
					if (typeof window.parent.ps_link_shopping_cart !== 'undefined') {
						window.location.href = window.parent.ps_link_shopping_cart;
					} else {
						window.location.href = 'index.php?controller=order';
					}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert("Impossible to add the product to the cart.\n\ntextStatus: '" + textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText);
			}
		});
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

function wooSave(data, type)
{
	app.admin.save(data, type);
}


function setHeigh(height){
	height = height + 10;
	if (document.getElementById('modal-designer') != null) {
		var height_old = document.getElementById('modal-designer').clientHeight;
		if (height_old < height) {
			height = height_old;
		}
	}
	document.getElementById('tshirtecommerce-designer').setAttribute('height', height + 'px');
	
	height = height + 20;
	jQuery('#modal-designer').parents('body').css({'height':height+'px', 'max-height':height+'px'});
}

function getWidth()
{
	var width = jQuery(window).width();
	var sizeZoom = width / 500;
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
	var elm = jQuery(e);
	jQuery('.designer_color_index').attr('name', 'colors['+elm.data('index')+']').val(elm.data('color'));
	jQuery('.designer_color_hex').val(elm.data('color'));
	jQuery('.designer_color_title').val(elm.attr('title'));
	
	jQuery('.e-custom-product').data('color', elm.data('color'));
	elm.addClass('active');
	jQuery(document).triggerHandler( "product.color.images", e);
}

function setupPrice(e)
{
	var price = e.value;
	var img = document.getElementById('_product_title_img').value;
	var product_id = document.getElementById('_product_id').value;
	
	if (product_id == '' || img == '') {
		alert('Please choose product design.');
		return false;
	}
	
	var params = img.split('::');
	if (typeof params[1] == 'undefined') {
		params[1] = '';
	}
	
	params[2] = price;
	img = params[0] +'::'+ params[1] +'::'+ params[2];
	document.getElementById('_product_title_img').value = img;
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
	var width = jQuery(window).width();
	var height = jQuery(window).height();
	if (width < 510 || height < 510) {
		var url = urlDesignload.replace('index.php', 'mobile.php');
		jQuery('body').append('<div id="modal-design-bg"></div><div id="modal-designer"><a style="display:none" href="'+urlBack+'"></a><iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" width="100%" height="100%" src="'+url+'"></iframe></div>');
		jQuery('body').addClass('tshirt-mobile');
	} else {
		jQuery('.row-designer').html('<iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="100%" src="'+urlDesignload+'"></iframe>');
	}
	
	var url_option = urlDesignload.split('tshirtecommerce/');
	var mainURL = url_option[0];
	
	if (typeof logo_loading === 'undefined') {
		logo_loading = mainURL+'tshirtecommerce/assets/images/logo-loading.png';
	}

	if (logo_loading.indexOf('http') == - 1) {
		logo_loading = mainURL + logo_loading;
	}

	if (typeof text_loading === 'undefined') {
		text_loading = '';
	}
	
	jQuery('.row-designer').append('<div class="mask-loading">'
									+ '<div class="mask-main-loading">'
									+	'<img class="mask-icon-loading" src="'+mainURL+'tshirtecommerce/assets/images/logo-loading.gif" alt="">'
									+	'<img class="mask-logo-loading" src="'+logo_loading+'" alt="">'
									+ '</div>'
									+ '<p>'+text_loading+'</p>'
									+ '</div>');
	
	jQuery("#tshirtecommerce-designer").load( function() {
		setTimeout(function(){
			jQuery('.row-designer .mask-loading').remove();
		}, 1000);
	});
}

function tshirt_close() {
	var href = jQuery('#modal-designer a').attr('href');
	window.location.href = href;
}

jQuery(document).ready(function() {
	design_id = jQuery('#_product_id').val();
	if(design_id == '') design_id = 0;
	
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
				alert(txt_min_order + ' '+min_order);
				return false;
			}
		});
	}
	
	// change size
	jQuery('.p-color-sizes .size-number').on('change', function(){
		var value = jQuery(this).val();
		filter = /^[0-9]+$/;
		if (filter.test(value)) {
			if (value.indexOf('0') == 0) jQuery(this).val(0);
		} else {
			jQuery(this).val(0);
		}
		
		var quantity = 0;
		jQuery('.p-color-sizes .size-number').each(function(){
			quantity = quantity + Math.round(jQuery(this).val());
		});
		jQuery('.quantity .input-text.qty').val(quantity);
	});

	jQuery('.e_tshirt_add').click(function() {
		jQuery(this).children('.dropdown-menu').toggle();
	});
});

function resizePageDesign(e){
	var check = jQuery(e).hasClass('button-resize-full');
	if (check) {
		jQuery(e).removeClass('button-resize-full');
		jQuery(e).addClass('button-resize-small');
		jQuery(e).parent('#add_designer_product').addClass('e-full-screen');
		var height = jQuery('#add_designer_product').height();
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