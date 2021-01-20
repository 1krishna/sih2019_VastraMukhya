
if(typeof window.orientation !== 'undefined') {
	var disable_mobile_layout = "1"; // desktop layout
}
else {
	var disable_mobile_layout = "0"; // mobile layout
}

var logo_loading = 'tshirtecommerce/assets/images/logo-loading.png';
var text_loading = 'The Design Tool is Loading...';
function setHeigh(height){
	height = height + 10;
	document.getElementById('tshirtecommerce-designer').setAttribute('height', height + 'px');
	
	height = height + 20;
	jQuery('#modal-designer').parents('body').css({'height':height+'px', 'max-height':height+'px'});
}

function getWidth()
{
	var width = jQuery(window).width();
	var sizeZoom = width/500;
	if (sizeZoom < 1)
	{
		jQuery('meta[name*="viewport"]').attr('content', 'width=device-width, initial-scale='+sizeZoom+', maximum-scale=1');
	}
}
// active link color
function loadProductDesign(e)
{
	if (typeof jQuery(e).data('color') != 'undefined')
	{
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
	if (typeof type == 'undefined')
	{
		var value = elm.find('option:selected').data('id');
		obj.val(value);
	}
	else if (type == 'checkbox' || type == 'radio')
	{
		if (elm.is(':checked') == true)
		{
			obj.prop('checked', true);
		}
		else
		{
			obj.prop('checked', false);
		}
	}
	else
	{
		obj.val(elm.val());
	}
}

function viewBoxdesign(){
	var width = jQuery(document).width();
	var height = jQuery(document).height();
	if (width < 510 || height < 510)
	{
		var url = urlDesignload.replace('index.php', 'mobile.php');
		jQuery('#modal-designer').remove();
		jQuery('#modal-design-bg').remove();
		jQuery('body').append('<div id="modal-design-bg"></div><div id="modal-designer"><a href="'+urlBack+'" class="btn btn-dange btn-xs">Close</a><iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" width="100%" height="100%" src="'+url+'"></iframe></div>');
		jQuery('body').addClass('tshirt-mobile');
	}
	else
	{
		jQuery('.row-designer').html('<iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="100%" src="'+urlDesignload+'"></iframe>');
	}
	
	var url_option = urlDesignload.split('tshirtecommerce/');
	var mainURL = url_option[0];
	
	if (logo_loading.indexOf('http') == - 1)
	{
		logo_loading = mainURL + logo_loading;
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

function tshirt_close(){
	var href = jQuery('#modal-designer a').attr('href');
	window.location.href = href;
}
jQuery(document).ready(function(){
	design_id = jQuery('#_product_id').val();
	if(design_id == '')
		design_id = 0;
	var product_type = jQuery('#product-type').val();
	if(product_type == 'variable')
	{
		jQuery('.variations_options').children('a').trigger('click');
		jQuery('#tshirtecommerce_product a').trigger('click');
	}else
	{
		jQuery('#tshirtecommerce_product a').trigger('click');
	}
	
	if (jQuery('.row-designer').length > 0)
	{
		viewBoxdesign();
	}
	
	// active product color
	if (jQuery('.designer-attributes .list-colors .bg-colors').length > 0)
	{
		if (jQuery('.designer-attributes .list-colors .bg-colors.active').length == 0)
		{
			var a = jQuery('.designer-attributes .list-colors .bg-colors');
			e_productColor(a[0]);
		}
		else
		{
			var a = jQuery('.designer-attributes .list-colors .bg-colors.active');
			e_productColor(a[0]);
		}
	}
	
	// product size
	if (typeof min_order != 'undefined' && jQuery('.quantity .input-text.qty').length > 0)
	{		
		// check add to cart
		jQuery( document ).on( 'click', '.single_add_to_cart_button', function() {
			var value = jQuery('.quantity .input-text.qty').val();
			if (value < min_order)
			{
				alert(txt_min_order + ' '+min_order);
				return false;
			}
		});
	}
	
	// change size
	jQuery('.p-color-sizes .size-number').on('change', function(){
		var value = jQuery(this).val();
		filter = /^[0-9]+$/;
		if (filter.test(value))
		{
			if (value.indexOf('0') == 0)
				jQuery(this).val(0);
		}
		else
		{
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
		if (jQuery('#tshirtecommerce-designer').length > 0)
		{
			var iframe = document.getElementById("tshirtecommerce-designer");
			
			if (typeof iframe.contentWindow.productCategory !== 'undefined' && jQuery.isFunction(iframe.contentWindow.productCategory))
			{
				// add product categories.
				function categories(evt, category, cate_id, parent_id, i, j){
					evt.children('li').each(function(){
						var text = jQuery(this).children('.selectit').text();
						var checked = jQuery(this).children('.selectit').children('input').is(':checked');
						var val = jQuery(this).children('.selectit').children('input').val();
						
						category[i] = {id:val, parent_id: parent_id, title:text};
						if(checked)
						{
							cate_id[j] = val;
							j++;
						}
						i++;
						
						if(jQuery(this).children('ul').hasClass('children'))
							categories(jQuery(this).children('.children'), category, cate_id, val, i, j);
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
			if(jQuery('#_regular_price').length > 0)
			{
				var price = jQuery('#_regular_price').val();
				if(price == '' && product_type == 'simple')
				{
					alert('Please add price of product');
					jQuery('.general_options').children('a').trigger('click');
					return false;
				}
				else if(product_type == 'variable')
				{
					if(jQuery('.variable_pricing .wc_input_price').length == 0)
					{
						alert('Please add variations of this product');
						jQuery('.variations_options').children('a').trigger('click');
						return false;
					}
					else
					{
						var input = jQuery('.variable_pricing .wc_input_price');
						var price = jQuery(input[0]).val();
						jQuery('#_regular_price').val(price);
					}
				}
			}			
			if(jQuery('#title').length > 0)
			{
				var title = jQuery('#title').val();
				if(title == '')
				{
					alert('Please add product name.');
					return false;
				}
			}
			var product = app.admin.product_detail();
			
			var check_validate = iframe.contentWindow.productInfo(product);
			if(check_validate)
			{
				jQuery('#tshirtecommerce_product a').trigger('click');
				jQuery(this).addClass('disabled');
				jQuery(this).parent().find('.spinner').addClass('is-active');
				jQuery(this).val('Saving...');
			}else
			{
				jQuery('#tshirtecommerce_product').children('a').trigger('click');
			}
			return false;
		}
	});
	
	// add box of product design
	if(jQuery('#_product_id').length > 0 && jQuery('#_disabled_product_design').val() != 1)
	{
		var id = jQuery('#_product_id').val();
		if (id != '')
		{
			var url = site_e_url + '/tshirtecommerce/admin/index.php?/product/viewmodal';
			if (id.indexOf(':') == -1)
			{
				url = url + '/' + id;
			}
			else
			{
				var params = id.split(':');
				var url = site_e_url + '/tshirtecommerce/admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1';
			}
			
			jQuery('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
		}
	}
});

function resizePageDesign(e){
	var check = jQuery(e).hasClass('button-resize-full');
	if(check)
	{
		jQuery(e).removeClass('button-resize-full');
		jQuery(e).addClass('button-resize-small');
		jQuery(e).parent('#add_designer_product').addClass('e-full-screen');
		var height = jQuery('#add_designer_product').height();
		jQuery(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', height+'px');
		jQuery('body').css('overflow', 'hidden');
	}
	else
	{
		jQuery('body').css('overflow', 'auto');
		jQuery(e).removeClass('button-resize-small');
		jQuery(e).addClass('button-resize-full');
		jQuery(e).parent('#add_designer_product').removeClass('e-full-screen');
		jQuery(e).parent('#add_designer_product').attr('height', height);
		jQuery(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', '800px');
	}
};

function getfullWidth() {
	var width = jQuery('#modal-designer').width();
	return width;
}

jQuery(document).ready(function(){
	if(typeof e_remove_cart_item != 'undefined' && e_remove_cart_item == true)
		jQuery('.woocommerce-message').hide();
});