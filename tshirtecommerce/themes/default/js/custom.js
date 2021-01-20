var theme_default = true;
function mobile_arrow(){
	if( jQuery('body').hasClass('hiden-right') == true)
	{
		jQuery('body').removeClass('hiden-right');
	}
	else
	{
		jQuery('body').addClass('hiden-right');
	}
}
function layers_toggle(show){
	if(show == true)
	{
		design.item.unselect();
		jQuery('.div-layers').show('slow');
	}
	else
	{
		jQuery('.div-layers').hide('slow');
	}
}

jQuery(document).on('price.addtocart.design', function(event, data){
	setTimeout(function(){
		var height = jQuery('#right-options').height();
		var price_h = jQuery('.product-prices').outerHeight();
		height = height - price_h - 30;
		jQuery('#product-details').css('height', height+ 'px');
	}, 2000);
});

function set_size_thumb()
{
	var w = 0;
	jQuery('#product-thumbs a').each(function(){
		var width 	= jQuery(this).outerWidth();
		w = parseInt(width) + w;
	});
	w = w +2;
	jQuery('#product-thumbs').css('width', w+'px');

}