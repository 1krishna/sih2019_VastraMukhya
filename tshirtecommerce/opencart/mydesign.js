var tsemydesign = {
	delete: function(id) {
		var confirm_del = confirm(tshirtecommerce_design_confirm_delete);
		if (confirm_del == true) {
			xjQuery.ajax({
				type: 'post',
				url: mydesign_ajax_del_link,
				data: { id: id },
				success: function(msg) {
					xjQuery('#my-design-' + id).remove();
					//alert(msg);
				},
				error: function(jqXHR, exception) {
					console.log(jqXHR);
				}
			});
		}
	},
	search: function() {
		var tsesearch = encodeURI(xjQuery('#tsedesign').val());
		tshirtecommerce_design_search_link = tshirtecommerce_design_search_link.replace('&amp;' , '&');
		location.href = tshirtecommerce_design_search_link + tsesearch;
	}
};

xjQuery(document).ready(function() {
	xjQuery("#tsedesign").keyup(function(event) {
	    if (event.keyCode === 13) {
	        tsemydesign.search();
	    }
	});
});