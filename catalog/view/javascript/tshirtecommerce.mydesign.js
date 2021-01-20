/* Tshirtecommerce : @since 4.2.1 */
function fntshirtecommercemydesign(e) {
    var tshirtecommere_panel = document.getElementById('tshirtecommerce-mydesign');
    tshirtecommere_panel.classList.toggle("active");
    if (tshirtecommere_panel.style.maxHeight) {
        tshirtecommere_panel.style.maxHeight = null;
        document.getElementById('material_add').style.display = 'inline-block';
        document.getElementById('material_remove').style.display = 'none';
    } else {
        document.getElementById('material_add').style.display = 'none';
        document.getElementById('material_remove').style.display = 'inline-block';
        tshirtecommere_panel.style.maxHeight = tshirtecommere_panel.scrollHeight + "px";
    }
}

function fnmydesignmore(e) {
    $.ajax({
        type: 'post',
        url: mydesign_ajax_link,
        data: { page: mydesign_page },
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
                $('#tshirtecommercemydesign > div').size();
                console.log($('#tshirtecommerce-mydesign').outerHeight());
                $('#tshirtecommerce-mydesign').css('max-height', $('#tshirtecommerce-mydesign').outerHeight() * mydesign_page + 320);
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
            url: mydesign_ajax_del_link,
            data: { key: key },
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