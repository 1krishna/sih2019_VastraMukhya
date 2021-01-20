// active link color
function loadProductDesign(e)
{
	/*if (typeof jQuery(e).data('color') != 'undefined')
	{
		var color 	= jQuery(e).data('color');
		var href 	= jQuery(e).attr('href');
		if(color != '') href = href + '&color='+color;
		window.location.href = href;
		return false;
	}
	return true;*/
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
	jQuery('#button-design').attr('data-color', jQuery(e).attr('data-color'));
	jQuery('#e_color_hex').val(jQuery(e).attr('data-color'));
}
function e_tshirt_attributes(e, index)
{
	var quantity = 0;
	jQuery('.dg-poduct-fields').find('input.size-number').each( function() {
		quantity = quantity + parseInt(jQuery(this).val()=='' ? 0 : jQuery(this).val());
	});
	document.getElementsByName("quantity")[0].value = quantity;
}
function e_validate_num(evt, e)
{
	var charCode = ( evt.which ) ? evt.which : event.keyCode;
	if(charCode != 43 && charCode != 45 && (charCode == 46 || jQuery( e ).val().indexOf('.') == -1) && (charCode  < 48 || charCode > 57)) 
	{
		return false;
	}
	return true
}