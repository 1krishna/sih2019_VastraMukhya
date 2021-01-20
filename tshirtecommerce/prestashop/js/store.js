var designer = {
    show: function(elm)
    {
        var div = jQuery(elm);
        
        var check = div.data('active');
        if ( typeof check != 'undefined' && check == 1)
        {
            div.hide('slow');
            div.data('active', 0);
        }
        else
        {
            div.show('slow');
            div.data('active', 1);
        }
    }
}
var tshirtecommerce_payment = {
    key: function(design_id) {
        if(jQuery('#arts-store-mask').length == 0) {
            jQuery('body').append('<div id="arts-store-mask"><span>Creating File Output...</span></div>');
        }

        jQuery('body').css('overflow', 'hidden');
        jQuery('#arts-store-mask').show();  

        var url = tshirtecommerce_ajax_url_store + 'ajax.php';
        jQuery.ajax({
            type: 'post',
            url: url,
            data: { method: 'store', order_id: design_id },
            dataType: 'json'
        }).done(function(response) {
            check_return = response.error;

            jQuery('#arts-store-mask').hide();
            jQuery('body').css('overflow', 'visible');
        });
    },
    removeLink: function(design_id) {
        /*jQuery('a.tshirtecommerce-link-download').each(function(){
            var href = jQuery(this).attr('data-id');
            if(href == design_id)
               jQuery(this).remove();
        });*/
    },
    load: function(e) {
        var id = jQuery(e).data('id');
        jQuery('.arts-store-payment').html('<a href="javascript:void(0);" onclick="tshirtecommerce_payment.cancel()">x</a>'+
            '<iframe id="store-art-payment" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="600px" '+
            'src="http://store.9file.net/api/index/'+id+'"></iframe>');
        if(jQuery('#arts-store-mask').length == 0)
        {
            jQuery('body').append('<div id="arts-store-mask"></div>');
        }
        jQuery('body').css('overflow', 'hidden');
        jQuery('#arts-store-mask').show();
        jQuery('.arts-store-payment').show('slow');         
        jQuery('#arts-store-mask').css('display', 'none');
    },
    cancel: function() {
        jQuery('#arts-store-mask').hide();
        jQuery('.arts-store-payment').hide('slow');
        jQuery('body').css('overflow', 'auto');
        jQuery('.arts-store-payment').html('');
    }
};

window.addEventListener("message", function(event) {
    var txt = event.data;
    window.location.href = txt;
});