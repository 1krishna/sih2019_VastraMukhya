function fnmydesignmore(e) {
    $.ajax({
        type: 'post',
        cache: false,
        url: mydesign_ajax_link,
        data: { type: 'more', page: mydesign_page },
        dataType: 'json',
        beforeSend: function() {
            $('.tshirtecommerce-loading').css('display', 'block');
        },
        complete: function() {
            $('.tshirtecommerce-loading').css('display', 'none');
        },
        success: function(res) {
            if (res.html != '') {
                $('#tshirtecommercemydesign').append(res.html);
            }

            if (res.continue == 0) {
                $('#mydesign_continue').css('display', 'none');
            }

            mydesign_page++;
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

function removemydesign(key) {
    var removemydesignConfirm = confirm(tshirtecommerce_design_confirm_delete);
    if (removemydesignConfirm) {
        $.ajax({
            type: 'post',
            cache: false,
            url: mydesign_ajax_link,
            data: { type: 'delete', key: key },
            dataType: 'json',
            beforeSend: function() {
                $('.tshirtecommerce-loading').css('display', 'block');
            },
            complete: function() {
                $('.tshirtecommerce-loading').css('display', 'none');
            },
            success: function(res) {
                if (res.error == 0) {
                    $('#mydesign-item-'+key).remove();
                }
                alert(res.msg);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}