<style type="text/css">
body{overflow: hidden;}
.tshirt-mask {position: fixed; top: 0;background-color: rgba(0, 0, 0, 0.7);left: 0;bottom: 0;right: 0;z-index: 100000;}
.tshirt-update {position: fixed;z-index: 100002;top: 0;right: 0;bottom: 0;left: 0;width: 500px;height: 250px;margin: auto; background-color: #fff;border-radius: 4px;padding: 15px;}
.tshirt-note-footer {position: absolute;bottom: 0;padding: 15px 0;}
.tshirt-note-footer p.notice {margin-left: 0;margin-bottom: 15px;}
.tshirtbutton{color: #555 !important;display: inline-block;font-size: 13px;line-height: 26px;height: 28px;padding: 0 10px 1px;border: 1px solid #ccc;border-radius: 3px;} 
.tshirtbutton-primary{background: #0085ba;border-color: #0073aa; color: #fff !important;}
.tshirtnotice{border-left: 4px solid #dc3232;margin-bottom: 15px;box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);padding: 1px 12px;
.tshirt-note-title{font-size: 2em;font-weight: 600;color: #23282d;}
}
</style>
<div class="tshirt-update">
	<div class="tshirt-note-head">
		<h1 class="tshirt-note-title">Product designer data update</h1>
	</div>
	<div class="tshirt-note-content">
		<p><?php echo $version['about']; ?></p>
        <a href="<?php echo $version['changelog']; ?>" target="_bank" class="tshirtbutton">ChangeLog</a>
	</div>
	<div class="tshirt-note-footer">
        <p class="tshirtnotice">It is strongly recommended that you backup your site before proceeding. Are you sure you wish to run the updater now?</p>
        <a href="javascript:void(0);" style="float: left;" onclick="designer_updater(this)" class="tshirtbutton tshirtbutton-primary">Run the updater</a> <span style="float: left;" class="spinner"></span>
	</div>
</div>
<div class="tshirt-mask"></div>
<script type="text/javascript">
function designer_updater(e){
  if(jQuery(e).hasClass('disabled')) return false;
  jQuery(e).addClass('disabled');
  jQuery(e).parent().find('.spinner').addClass('is-active');

    var data = {
        'action': 'designer_syn',
    };
	var updateUrl = '<?php echo $updateUrl; ?>';
	ajaxurl = updateUrl.replace(/&amp;/g, '&');
    jQuery.post(ajaxurl, data, function(response) {
		alert('Updater completed');
		var designUrl = '<?php echo $designUrl; ?>';
		designUrl = designUrl.replace(/&amp;/g, '&');
      window.location.href = designUrl;
    });
}
</script>