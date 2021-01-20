<style type="text/css">.filename div{white-space: nowrap;} #design-imports, #tool-imports{display: none!important;}</style>
<h2 class="wp-heading-inline">Import cliparts & design template</h2>
<h4 class="notice-warning notice" style="padding: 8px;background-color: #fff8e5;color: #ff0000;">Please don't navigate away while importing</h4>
<div class="import-progress" style="display: inline-block;padding: 25px 5px;font-size: 18px;">
	<img src="view/image/tshirtspin_light-2x.gif" alt="loading" style="vertical-align: middle;padding-right: 10px;"> Loading
</div>

<div class="import-help">
	<h4>Step by step import via FTP:</h4>
	<ol>
		<li>Download file <a href="<?php echo $link; ?>" target="_blank">store.zip</a></li>
		<li>Unzip file store.zip on your computer</li>
		<li>Upload all files to folder <strong><?php echo $folder; ?></strong></li>
		<li>Reload this page again</li>
	</ol>
</div>

<?php if($link != '') { ?>
<script type="text/javascript">
jQuery(document).ready(function(){
	import_store_design();
});
function import_store_design()
{
	var ajaxurl = '<?php echo $import_url; ?>';
	var data = {
		'link': '<?php echo $link; ?>',
	};
	jQuery.post(ajaxurl, data, function(response){
		var data = jQuery.parseJSON(response);
		if(data.error == '0')
		{
			window.location.href = "<?php echo $success_url; ?>";
		}
		else
		{
			jQuery('.import-progress').html(data.mgs);
		}
	});
}
</script>
<?php } ?>