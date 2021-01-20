$(document).ready(function() {
	$(".tab-page").click(function(e) {
		e.preventDefault();

		currentId = $(".productTabs a.selected").attr('id').substr(5);
		id = $(this).attr('id').substr(5);

		if (id == 'ModuleTshirtecommerce') {
			$('#desc-product-save').hide();
			$('#desc-product-save-and-stay').hide();
		} else {
			$('#desc-product-save').show();
			$('#desc-product-save-and-stay').show();
		}
	});
});