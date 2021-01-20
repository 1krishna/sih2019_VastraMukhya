<?php @ini_set("zlib.output_compression", "Off"); ?>
<?php echo $header; ?>
<div id="container" class="container" style="padding-top: 15px;margin-top: 15px;">
	<?php echo $content; ?>
</div>
<link href="<?php echo $url.'tshirtecommerce/opencart/css/opencart.css'; ?>" rel="stylesheet">
<link href="<?php echo $url.'tshirtecommerce/opencart/css/mobile.css'; ?>" rel="stylesheet">
<script type="text/javascript">
	var urlBack = '<?php echo $url.'index.php?route=product/product&product_id='.$parent_id; ?>';	
	var urlDesign = '<?php echo $url.'index.php?route=tshirtecommerce/designer'; ?>';
	var urlDesignload = '<?php echo $urlDesignload; ?>';
	var _product_id_oc = '<?php echo $parent_id; ?>';
	var ocedit = '<?php echo $edit; ?>';
	var logo_loading = '<?php echo $tshirtecommerce_logo_loading; ?>';
	var text_loading = '<?php echo $tshirtecommerce_text_loading; ?>';
</script>
<script src="<?php echo $url.'tshirtecommerce/opencart/js/app.js'; ?>" type="text/javascript"></script>
<?php echo $footer; ?>