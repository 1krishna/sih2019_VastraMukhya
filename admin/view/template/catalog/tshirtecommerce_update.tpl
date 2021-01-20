<?php
function openURL($url) {		
	if(ini_get('allow_url_fopen')) {
		$data = @file_get_contents($url);
		if ($data == false) return false;
	} else {
		$ch = @curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = @curl_exec($ch);
		@curl_close($ch);
	}
	return $data;
}

echo $header;
echo $column_left; 
?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">     
      <h1><?php echo $updata_head; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li>
			<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php if ( $version != '') { echo ' <small>('.$updata_version . $version .')</small>'; } ?>
		</li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
  	<style>.alert a:hover {text-decoration: underline;}</style>
  	<?php if(isset($permission_warning)) { ?>
  	<div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $permission_warning; ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
  	<?php } ?>

	<?php if (isset($error_warning)) { ?>
	<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	<?php } ?>

	<?php if (isset($success)) { ?>
	<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	<?php }
	if (isset($versions)) {
		foreach($versions as $version) { ?>
			<div class="panel panel-default">		
				<div class="panel-heading">
					<h3 class="panel-title"><span>Version <?php echo $version->version; ?> <small><?php echo $version->date; ?></small></span></h3>
					<span class="pull-right">
						<form action="<?php echo $link; ?>" method="post" id="form-attribute">
								<?php if(!empty($code) && str_replace(' ', '', $code) != '' && isset($tshirtecommerce_verify['error']) && $tshirtecommerce_verify['error'] == 0) : ?>
								<button type="submit" class="btn btn-primary btn-sm">Update</button>
								<a target="_blank" href="http://updates.tshirtecommerce.com/api.php?code=<?php echo $code; ?>&version=<?php echo str_replace('.','',$version->version); ?>&platform=opencart&opencart=<?php echo $oc_version; ?>" class="btn btn-default btn-sm">Download</a>
								<input type="hidden" name="update" value="<?php echo $version->version; ?>" />
								<?php endif; ?>
						</form>
					</span>
				</div>
				<div class="panel-body">
					<?php echo openURL('http://updates.tshirtecommerce.com/opencart/'.$version->info); ?>
				</div>
			</div>
		<?php }
	} else { ?>
		<h2><?php echo $updata_fontd; ?></h2>
	<?php } ?>	
  </div>
</div>
<?php echo $footer; ?>