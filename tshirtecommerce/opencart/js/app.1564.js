var design_id = 0;
var app = {
	admin:{
		ini: function(){
			$('#designer-products .tab-content a.modal-link').click(function(){
				var link = $(this).attr('href');
				if($(this).hasClass('add-link')) {
					app.admin.add(this);
				} else {
					app.admin.load(link);
				}
				return false;
			});
		},
		product: function(e, index){
			if (document.getElementById('designer-products') == null) {
				var div = '<div id="designer-products" class="modal">'
						+ '<div class="modal-dialog">'
						+ 	'<div class="modal-content">'
						+		'<div class="modal-header">'
						+			'<a data-dismiss="modal" class="close close-list-design" onclick="$(\'#designer-products\').css(\'display\', \'none\')">'
						+				'<i class="material-icons">close</i>'
						+			'</a>'
						+		'</div>'
						+ 		'<div class="modal-body">'
						+		'&#65279;<center><h3>Please wait some time. loading...</h3></center>'
						+		'</div>'
						+	'</div>'
						+ '</div></div>';
				//$('body').append(div);
				$('body').append(div);
			}
			if(index != 4) {
				//$('#designer-products').modal('show');
				$('#designer-products').css('display', 'block');
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
				var url = tshirtURL+'admin/index.php?/product/viewmodal';				
				$('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
				return;
			} else {
				var url = tshirtURL+'admin.php';
			}
			$.post(url, data, function(response) {
				$('#designer-products .modal-body').html(response);
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
			$('#designer-products .modal-body').html('&#65279;<center><h3>Please wait some time. loading...</h3></center>');
			$.post(ajaxurl, data, function(response) {
				$('#designer-products .modal-body').html(response);				
				app.admin.ini();
			});
			return false; 
		},
		add: function(e) {
			var id = $(e).data('id');
			id = String(id);
			if ($(e).hasClass('design-idea') == true) {
				var url = tshirtURL+'admin-template.php?product='+id+'&lightbox=1';
			} else {
				if (id.indexOf(':') == -1) {
					var title = $(e).data('title');
					var img = $(e).children('img').attr('src');
					document.getElementById('_product_id').value = id;
					document.getElementById('_product_title_img').value = title+'::'+img;
					var url = tshirtURL + 'admin/index.php?/product/viewmodal/'+id;
				} else {
					var params = id.split(':');
					var url = tshirtURL+'admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1';
				}
			}
			var html = '<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>';
			$('#add_designer_product').html(html);
			$('#designer-products').hide();
		},
		product_detail: function(){
			var product = {};
			product.title = $('input[name="product_description['+ts_lang_id+'][name]"]').val();

			if ($('#input-description'+ts_lang_id).length) {
				product.description = ($('#input-description'+ts_lang_id).val()).replace('"', '\"');
			} else {
				if ($('#description'+ts_lang_id).length) {
					product.description = ($('#description'+ts_lang_id).val()).replace('"', '\"');
				} else {
					product.description = '';
				}
			}
			product.shortdescription = '';
			var img = $('#input-image');
			if (img.length > 0) {
				product.thumb = siteUrl+'/image/'+$('#input-image').val();
			} else {
				var img_src = '';
				if (typeof $('#image') !== 'undefined') {
					img_src = siteUrl + '/image/' + $('#image').val();
				}
				product.thumb = img_src;
			}
			product.sku = $('input[name="sku"]').val();
			product.price = $('input[name="price"]').val();
			product.sale_price = '';
			product.prices = {};

			product.min_order = $('input[name="minimum"]').val();
			product.max_order = $('input[name="quantity"]').val();

			return product;
		},
		clear: function(){
			if ($('#tshirtecommerce-designer').length > 0) {
				var check = confirm('You sure want clear data design of this product?');
				if (check == true) {
					document.getElementById('_product_id').value = '';
					document.getElementById('_product_title_img').value = '';
					$('#add_designer_product').html('');
				}
			}
		},
		save: function(data, type){
			$('#tshirtecommerce-wapper').hide();
			if (type == 'product') {
				document.getElementById('_product_id').value = data;
			} else if(type == 'idea') {
				var ids = data.designer_id+':'+data.design_id+':'+ data.product_id+':'+ data.productColor;
				document.getElementById('_product_id').value = ids;
			}
			if ($('#form-product').length) {
				$('#form-product').submit();
			}
			if ($('#form').length) {
				$('#form').submit();
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
			$.ajax({
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + ocedit,
				dataType: 'json',
				success: function(json) {
					$.ajax({
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
			$.ajax({
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
		var div = $(elm);
		
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
	$('#modal-designer').parents('body').css({'height':height+'px', 'max-height':height+'px'});
}

function getWidth()
{
	var width = $(window).width();
	var sizeZoom = width/500;
	if (sizeZoom < 1) {
		$('meta[name*="viewport"]').attr('content', 'width=device-width, initial-scale='+sizeZoom+', maximum-scale=1');
	}
}

// active link color
function loadProductDesign(e)
{
	if (typeof $(e).data('color') != 'undefined') {
		var color = $(e).data('color');
		var href = $(e).attr('href');
		href = href + '&color='+color;
		window.location.href = href;
		return false;
	}
	return true;
}

// click change color in page product detail
function e_productColor(e)
{
	var parent = $(e).parent();
	parent.children('.bg-colors').removeClass('active');
	
	// add data
	var elm = $(e);
	$('.designer_color_index').attr('name', 'colors['+elm.data('index')+']').val(elm.data('color'));
	$('.designer_color_hex').val(elm.data('color'));
	$('.designer_color_title').val(elm.attr('title'));
	
	$('.e-custom-product').data('color', elm.data('color'));
	elm.addClass('active');
	$(document).triggerHandler( "product.color.images", e);
}

function tshirt_attributes(e, index)
{
	var elm = $(e);
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
	var width = $(document).width();
	var height = $(document).height();
	var width_body = jQuery('body').width();

	if (width_body < 510 || width < 510 || height < 510) {
		var url = urlDesignload.replace('index.php', 'mobile.php');
			$('body').append('<div id="modal-design-bg"></div><div id="modal-designer"><a href="'+urlBack+'" class="btn btn-dange btn-xs">Close</a><iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" width="100%" height="100%" src="'+url+'"></iframe></div>');
			$('body').addClass('tshirt-mobile');
	} else {
		$('.row-designer').html('<iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="100%" src="'+urlDesignload+'"></iframe>');
	}
	
	var url_option = urlDesignload.split('tshirtecommerce/');
	var mainURL = url_option[0];
	
	if (logo_loading.indexOf('http') == - 1) {
		logo_loading = mainURL + logo_loading;
	}
	
	$('.row-designer').append('<div class="mask-loading">'
		+ '<div class="mask-main-loading">'
		+	'<img class="mask-icon-loading" src="'+mainURL+'tshirtecommerce/assets/images/logo-loading.gif" alt="">'
		+	'<img class="mask-logo-loading" src="'+logo_loading+'" alt="">'
		+ '</div>'
		+ '<p>'+text_loading+'</p>'
		+ '</div>'
	);
	
	$("#tshirtecommerce-designer").load( function() {
		setTimeout(function(){
			$('.row-designer .mask-loading').remove();
		}, 1000);
	});
}

function tshirt_close(){
	var href = $('#modal-designer a').attr('href');
	window.location.href = href;
}
$(document).ready(function(){
	design_id = $('#_product_id').val();
	if (design_id == '') {
		design_id = 0;
	}
	var product_type = $('#product-type').val();
	if (product_type == 'variable') {
		$('.variations_options').children('a').trigger('click');
		$('#tshirtecommerce_product a').trigger('click');
	} else {
		$('#tshirtecommerce_product a').trigger('click');
	}
	
	if ($('.row-designer').length > 0) {
		viewBoxdesign();
	}
	
	// active product color
	if ($('.designer-attributes .list-colors .bg-colors').length > 0) {
		if ($('.designer-attributes .list-colors .bg-colors.active').length == 0) {
			var a = $('.designer-attributes .list-colors .bg-colors');
			e_productColor(a[0]);
		} else {
			var a = $('.designer-attributes .list-colors .bg-colors.active');
			e_productColor(a[0]);
		}
	}
	
	// product size
	if (typeof min_order != 'undefined' && $('.quantity .input-text.qty').length > 0) {		
		// check add to cart
		$( document ).on( 'click', '.single_add_to_cart_button', function() {
			var value = $('.quantity .input-text.qty').val();
			if (value < min_order) {
				alert(txt_min_order+' '+min_order);
				return false;
			}
		});
	}
	
	// change size
	$('.p-color-sizes .size-number').on('change', function(){
		var value = $(this).val();
		filter = /^[0-9]+$/;
		if (filter.test(value)) {
			if (value.indexOf('0') == 0) {
				$(this).val(0);
			}
		} else {
			$(this).val(0);
		}
		
		var quantity = 0;
		$('.p-color-sizes .size-number').each(function(){
			quantity = quantity + Math.round($(this).val());
		});
		$('.quantity .input-text.qty').val(quantity);
	});
	
	// save product in wooommerce
	$('input#publish').click(function(){
		if ($('#tshirtecommerce-designer').length > 0) {
			var iframe = document.getElementById("tshirtecommerce-designer");
			if (typeof iframe.contentWindow.productCategory !== 'undefined' && $.isFunction(iframe.contentWindow.productCategory)) {
				// add product categories.
				function categories(evt, category, cate_id, parent_id, i, j){
					evt.children('li').each(function(){
						var text = $(this).children('.selectit').text();
						var checked = $(this).children('.selectit').children('input').is(':checked');
						var val = $(this).children('.selectit').children('input').val();
						
						category[i] = {id:val, parent_id: parent_id, title:text};
						if (checked) {
							cate_id[j] = val;
							j++;
						}
						i++;
						
						if ($(this).children('ul').hasClass('children')) {
							categories($(this).children('.children'), category, cate_id, val, i, j);
						}
					});
				};
				
				var cate_id = [],
				category = [];
				categories($('#product_catchecklist'), category, cate_id, 0, 0, 0);
				iframe.contentWindow.productCategory(cate_id, category);
			};
			
			// save products.
			var name = $('#publish').attr('name');
			var value = $('#publish').val();
			$('#publishing-action').append('<input type="hidden" value="'+value+'" name="'+name+'"/>');
			
			var product_type = $('#product-type').val();
			if ($('#_regular_price').length > 0) {
				var price = $('#_regular_price').val();
				if (price == '' && product_type == 'simple') {
					alert('Please add price of product');
					$('.general_options').children('a').trigger('click');
					return false;
				} else if (product_type == 'variable') {
					if ($('.variable_pricing .wc_input_price').length == 0) {
						alert('Please add variations of this product');
						$('.variations_options').children('a').trigger('click');
						return false;
					} else {
						var input = $('.variable_pricing .wc_input_price');
						var price = $(input[0]).val();
						$('#_regular_price').val(price);
						var prices = '';
						$('.variable_pricing').each(function(){
							var input = $(this).find('.wc_input_price');
							var price = 0;
							if (typeof input[0] != 'undefined') {
								price = $(input[0]).val();
							}
							if (typeof input[1] != 'undefined' && $(input[1]).val() != '') {
								price = $(input[1]).val();
							}
							if (price != 0) {
								var index = $(this).parents('.woocommerce_variation').find("input[name^='variable_post_id']").val();
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
			if ($('#title').length > 0) {
				var title = $('#title').val();
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
				$('#tshirtecommerce_product a').trigger('click');
				$(this).addClass('disabled');
				$(this).parent().find('.spinner').addClass('is-active');
				$(this).val('Saving...');
			} else {
				$('#tshirtecommerce_product').children('a').trigger('click');
			}
			return false;
		}
	});
	
	// add box of product design
	if ($('#_product_id').length > 0 && $('#_disabled_product_design').val() != 1) {
		var id = $('#_product_id').val();
		if (id != '') {
			var url = tshirtURL + 'admin/index.php?/product/viewmodal';
			if (id.indexOf(':') == -1) {
				url = url + '/' + id;
			} else {
				var params = id.split(':');
				var url = tshirtURL+'admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1';
			}
			
			$('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
		}
	}
});

function resizePageDesign(e){
	var check = $(e).hasClass('button-resize-full');
	if (check) {
		$(e).removeClass('button-resize-full');
		$(e).addClass('button-resize-small');
		$(e).parent('#add_designer_product').addClass('e-full-screen');
		var height = $('#add_designer_product').height();
		$(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', height+'px');
		$('body').css('overflow', 'hidden');
	} else {
		$('body').css('overflow', 'auto');
		$(e).removeClass('button-resize-small');
		$(e).addClass('button-resize-full');
		$(e).parent('#add_designer_product').removeClass('e-full-screen');
		$(e).parent('#add_designer_product').attr('height', height);
		$(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', '800px');
	}
};

function getfullWidth() {
	if ($('#modal-designer').length > 0) {
		var width = $('#modal-designer').width();
	} else {
		var width = $('#tshirtecommerce-designer').width();
	}	
	
	return width;
}

function dg_full_screen()
{
	if ($('body').hasClass('dg_screen')) {
		$('body').removeClass('dg_screen');
		return 0;
	} else {
		$('body').addClass('dg_screen');
		return 1;
	}
}

$(document).ready(function(){
	if (typeof e_remove_cart_item != 'undefined' && e_remove_cart_item == true) {
		$('.woocommerce-message').hide();
	}
	
	$('.e_tshirt_add').click(function(){
		$(this).children('.dropdown-menu').toggle();
	});
});