<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
  	<div class="page-header">
    	<div class="container-fluid">
    		<div class="pull-right">
    			<button type="submit" form="form-attribute" data-toggle="tooltip" data-original-title="Save" class="btn btn-primary">
    				<i class="fa fa-floppy-o" aria-hidden="true"></i> Save
    			</button>
    		</div>
      		<h1><?php echo $heading_title; ?> - Settings</h1>
      		<ul class="breadcrumb">
        		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
	        		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
	        	<?php } ?>
      		</ul>
    	</div>
  	</div>
  	<div class="container-fluid">
		<?php if (isset($error_warning)) { ?>
			<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php } ?>
		<?php if (isset($success)) { ?>
			<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php } ?>
	    <div class="panel panel-default">
	    	<div class="panel-heading">
	    		<h3 class="panel-title">
	    			<i class="fa fa-list"></i> <?php echo $heading_title; ?>
	    			<a id="btn-quicksetup" class="btn btn-success btn-sm" href="javascript:void(0)" style="color: #fff;"><strong>Quick Setup</strong></a>
	    		</h3>
	    	</div>
	      	<div class="panel-body">	
				<form action="<?php echo $link; ?>" method="post" id="form-attribute" class="form-horizontal">
					<fieldset>
						<legend>General</legend>
						<div class="form-group">
							<label class="col-sm-2 control-label">
								<span data-toggle="tooltip" data-original-title="Choose product default. It is very important. This product will show in designer.">
									Product default
								</span>
							</label>
							<div class="col-sm-10">
								<?php if ($products == 0) { ?>
									<label class="control-label"><strong style="color:red;"><i>Please add product design.</i></strong></label>
								<?php } else { ?>
									<select name="tshirtecommerce_product" class="form-control">
									<?php foreach ($products as $product) {
										if ($product_id == $product['product_id']) {
											$select = 'selected="selected"';
										} else {
											$select = '';
										} ?>
										<option <?php echo $select; ?> value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
									<?php } ?>
									</select>
								<?php } ?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" for='tshirtecommerce_allow_images'>
								<span data-toggle="tooltip" data-original-title="Allow alternative product images by design images on front-office.">
									Product images
								</span>
							</label>
							<div class="col-sm-10">
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_allow_images" value="2" <?php if (isset($tshirtecommerce_allow_images) && $tshirtecommerce_allow_images == 2) echo 'checked' ?> /> Yes
								</label>
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_allow_images" value="1" <?php if (!isset($tshirtecommerce_allow_images) || $tshirtecommerce_allow_images == 1) echo 'checked' ?> /> No
								</label>
								<!--
								<label class="radio-inline" style="display: none;">
									<input type="radio" name="tshirtecommerce_allow_images" value="3" <?php if (isset($tshirtecommerce_allow_images) && $tshirtecommerce_allow_images == 3) echo 'checked' ?> /> Both 
								</label>
								-->
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" for='tshirtecommerce_allow_taxes'>
								<span data-toggle="tooltip" data-original-title="Allow calc taxes for price on designer tool.">
									Enable taxes
								</span>
							</label>
							<div class="col-sm-10">
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_allow_taxes" value="1" <?php if (!isset($tshirtecommerce_allow_taxes) || $tshirtecommerce_allow_taxes == 1) echo 'checked' ?> /> Yes 
								</label>
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_allow_taxes" value="0" <?php if (isset($tshirtecommerce_allow_taxes) && $tshirtecommerce_allow_taxes == 0) echo 'checked' ?> /> No 
								</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" for='tshirtecommerce_hide_addtocart'>
								<span data-toggle="tooltip" data-original-title="Allow hide Add To Cart button from product page when to use Custom Design button.">
									Hide Add To Cart
								</span>
							</label>
							<div class="col-sm-10">
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_hide_addtocart" value="1" <?php if (isset($tshirtecommerce_hide_addtocart) && $tshirtecommerce_hide_addtocart == 1) echo 'checked' ?> /> Yes 
								</label>
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_hide_addtocart" value="0" <?php if (!isset($tshirtecommerce_hide_addtocart) || $tshirtecommerce_hide_addtocart == 0) echo 'checked' ?> /> No 
								</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" for='design_downloadable'>
								<span data-toggle="tooltip" data-original-title="Allow clients to download design on order history and e-mail payments">
									Downloadable design
								</span>
							</label>
							<div class="col-sm-10">
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_downloadable" value="1" <?php if (isset($tshirtecommerce_downloadable) && $tshirtecommerce_downloadable == 1) echo 'checked' ?> /> Yes 
								</label>
								<label class="radio-inline">
									<input type="radio" name="tshirtecommerce_downloadable" value="0" <?php if (!isset($tshirtecommerce_downloadable) || $tshirtecommerce_downloadable == 0) echo 'checked' ?> /> No 
								</label>
							</div>
						</div>
						<div class="form-group">
							<label for='tshirtecommerce_code' class='col-sm-2 control-label'>
								<span data-toggle="tooltip" data-original-title="Purchase Code must be verified before update new version.">
									Purchase Code
								</span>
							</label>
							<div class='col-sm-10'>
								<div class="row">
									<div class="col-xs-12 col-sm-9">
										<input type='text' class='form-control' id='tshirtecommerce_code' name='tshirtecommerce_code' value='<?php echo (isset($tshirtecommerce_code) ? $tshirtecommerce_code : ''); ?>' />
									</div>
									<div class="col-xs-12 col-sm-3">
										<?php if (isset($tshirtecommerce_verify) && count($tshirtecommerce_verify) > 0) : ?>
											<?php if (isset($tshirtecommerce_verify['error']) && $tshirtecommerce_verify['error'] == 0) : ?>
												<span id="status-verify" style="background-color:#dff0d8;color:#3c763d;line-height: 1.42857;" class="input-group-addon" aria-hidden="true"><strong><i class="fa fa-check" aria-hidden="true"></i> Verified </strong></span>
											<?php else : ?>
												<span id="status-verify" style="background-color:#f2dede;color:#a94442;line-height: 1.42857;" class="input-group-addon" aria-hidden="true"><strong><i class="fa fa-times" aria-hidden="true"></i> Not verified </strong></span>
											<?php endif; ?>
										<?php endif; ?>
									</div>
									<div class="col-sm-12">
										<p class="text-muted"><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"><i>Where is my Purchase Code?</i></a></p>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Custom Your Design text</label>
							<div class='col-sm-10'>
								<div class="form-inline">
									<?php if (count($languages) > 0) { ?>
										<?php foreach ($languages as $language) { ?>
											<?php if ($language['code'] == $code) { ?>
												<input type="text" name="tshirtecommerce_customize_design_text[<?php echo $language['code']; ?>]" value="<?php echo $language['tshirtecommerce_custom_text']; ?>" class="form-control" style="width: 65%; display: inline-block;" />
											<?php } else { ?>
												<input type="text" name="tshirtecommerce_customize_design_text[<?php echo $language['code']; ?>]" value="<?php echo $language['tshirtecommerce_custom_text']; ?>" class="form-control" style="width: 65%; display: none;" />
											<?php } ?>
										<?php } ?>
										<select id="opt_tshirtecommerce_design_btn_lang" class="form-control" name="tshirtecommerce_customize_design_text_code" style="max-width: 25%; display: inline-block;">
											<?php foreach ($languages as $language) { ?>
											<?php if ($language['code'] == $code) { ?>
											<option value="<?php echo $language['code']; ?>" selected><?php echo $language['name']; ?></option>
											<?php } else { ?>
											<option value="<?php echo $language['code']; ?>"><?php echo $language['name']; ?></option>
											<?php } ?>
											<?php } ?>
										</select>
									<?php } ?>
								</div>
								<div class="text-muted">
									<span><i>Allow translate text of Custom Your Design Button on Front-Office</i></span><br />
								</div>
							</div>
						</div>
						<script type="text/javascript">
							$('#opt_tshirtecommerce_design_btn_lang').change(function () {
								var opt_code = $('#opt_tshirtecommerce_design_btn_lang').val();
								<?php foreach ($languages as $language) { ?>
								$('input[name="tshirtecommerce_customize_design_text['+"<?php echo $language['code']; ?>"+']"]').css('display', 'none');
								<?php } ?>
								$('input[name="tshirtecommerce_customize_design_text['+opt_code+']"]').css('display', 'inline-block');
							});
						</script>
						<div class="form-group">
							<label class="col-sm-2 control-label">Custom Your Design button style</label>
							<div class='col-sm-10'>
								<textarea id="tshirtecommerce_customize_design_btn" class="form-control" rows="3" name="tshirtecommerce_customize_design_btn"><?php if(isset($tshirtecommerce_customize_design_btn)) echo $tshirtecommerce_customize_design_btn; ?></textarea>
								<div class="text-muted">
									<span><i>Allow change style of Custom Your Design Button on Front-Office</i></span><br />
									<span><i>Please enter CSS class name (e.g. "btn-group", "btn button-custom") or CSS style code (e.g. "padding:6px 8px", "padding 6px 8px;color: #fff;" )</i></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Logo loading</label>
							<div class="col-sm-10">
								<a id="thumb_tshirtecommerce_logo_loading" data-toggle="image" class="img-thumbnail">
									<img alt='logo loading' title="logo loading" src="<?php echo $tshirtecommerce_logo_loading; ?>" data-placeholder="<?php echo $tshirtecommerce_logo_loading; ?>" style="max-width: 100px; max-height: 100px;" />
								</a>
								 <input type="hidden" name="tshirtecommerce_logo_loading" value="<?php echo $tshirtecommerce_logo_loading; ?>" id="tshirtecommerce_logo_loading" />
								<div class="text-muted">
									<span>Allow change logo loading on designer page</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Text loading</label>
							<div class='col-sm-10'>
								<input class="form-control" type="text" name="tshirtecommerce_text_loading" value="<?php echo $tshirtecommerce_text_loading; ?>" />
								<div class="text-muted">
									<span>Allow change text loading on designer page</span>
								</div>
							</div>
						</div>
					</fieldset>
				<?php if (isset($setting_store_tshirtecommerce) && $setting_store_tshirtecommerce == 1) { ?>
				<fieldset>
					<legend>Store's Schedule</legend>
					<div class="alert alert-info"><i class="fa fa-check-circle"></i>
						Must enable Store in Tshirtecommerce Configuration Manager.
						<button type="button" class="close" data-dismiss="alert">&times;</button>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">
							<span data-toggle="tooltip" data-original-title="Allow auto sync clipart from Store to website.">
							Enable Schedule
							</span>
						</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_jobs" value="1" <?php if (isset($tshirtecommerce_jobs) && $tshirtecommerce_jobs == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_jobs" value="0" <?php if (!isset($tshirtecommerce_jobs) || $tshirtecommerce_jobs == 0) echo 'checked' ?> /> No 
							</label>	
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">Schedule plan </label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_jobs_plan" value="0" <?php if (isset($tshirtecommerce_jobs_plan) && $tshirtecommerce_jobs_plan == 0) echo 'checked' ?> />
								Run every 
								<select name="tshirtecommerce_jobs_plan_hours">
									<?php for ($i = 1; $i < 24; $i++) {
										if ($tshirtecommerce_jobs_plan_hours == $i) {
											if ($i < 10)
												echo "<option value='".$i."' selected>0".$i."</option>";
											else
												echo "<option value='".$i."' selected>".$i."</option>";
										} else {
											if ($i < 10)
												echo "<option value='".$i."'>0".$i."</option>";
											else
												echo "<option value='".$i."'>".$i."</option>";
										}
									} ?>
								</select>
								hour(s)
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"></label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_jobs_plan" value="1" <?php if (isset($tshirtecommerce_jobs_plan) && $tshirtecommerce_jobs_plan == 1 || !isset($tshirtecommerce_jobs_plan)) echo 'checked' ?> />
								Run every 
								<select name="tshirtecommerce_jobs_plan_daily">
									<?php for ($i = 0; $i < 7; $i++) {
										if ($tshirtecommerce_days == $i) {
											if ($i < 10)
												echo "<option value='".$i."' selected>0".$i."</option>";
											else
												echo "<option value='".$i."' selected>".$i."</option>";
										} else {
											if ($i < 10)
												echo "<option value='".$i."'>0".$i."</option>";
											else
												echo "<option value='".$i."'>".$i."</option>";
										}
									} ?>
								</select>
								day(s) 
							</label>
							<label class="radio-inline">
								at <select name="tshirtecommerce_jobs_plan_daily_hours">
									<?php for ($i = 0; $i < 24; $i++) {
										if ($tshirtecommerce_hours == $i) {
											if ($i < 10)
												echo "<option value='".$i."' selected>0".$i."</option>";
											else
												echo "<option value='".$i."' selected>".$i."</option>";
										}
										else {
											if ($i < 10)
												echo "<option value='".$i."'>0".$i."</option>";
											else
												echo "<option value='".$i."'>".$i."</option>";
										}
									} ?>
								</select> hour(s) -
								<select name="tshirtecommerce_jobs_plan_daily_minutes">
									<?php for ($i = 0; $i < 60; $i++) {
										if ($tshirtecommerce_minutes == $i) {
											if ($i < 10)
												echo "<option value='".$i."' selected>0".$i."</option>";
											else
												echo "<option value='".$i."' selected>".$i."</option>";
										} else {
											if ($i < 10)
												echo "<option value='".$i."'>0".$i."</option>";
											else
												echo "<option value='".$i."'>".$i."</option>";
										}
									} ?>
								</select>
								minute(s)
							</label>
						</div>
					</div>
				</fieldset>
				<?php } ?>
				<fieldset style="display: none!important;">
					<legend>Display Custom Button on</legend>
					<div class="alert alert-info"><i class="fa fa-check-circle"></i>
						We have not support for all themes. So maybe you need to modify xml file in vqmod. Please contact to supporter for custom.
						<button type="button" class="close" data-dismiss="alert">&times;</button>
					</div>
					<!-- Adding cusstom design button on Home page -->
					<div class="form-group">
						<label class="col-sm-2 control-label">Home page</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_home" value="1" <?php if (isset($tshirtecommerce_home) && $tshirtecommerce_home == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_home" value="0" <?php if (!isset($tshirtecommerce_home) || $tshirtecommerce_home == 0) echo 'checked' ?> /> No 
							</label>	
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_search'>Search page</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_search" value="1" <?php if (isset($tshirtecommerce_search) && $tshirtecommerce_search == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_search" value="0" <?php if (!isset($tshirtecommerce_search) || $tshirtecommerce_search == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_category'>Categories List page</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_category" value="1" <?php if (isset($tshirtecommerce_category) && $tshirtecommerce_category == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_category" value="0" <?php if (!isset($tshirtecommerce_category) || $tshirtecommerce_category == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_compare'>Comapre page</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_compare" value="1" <?php if (isset($tshirtecommerce_compare) && $tshirtecommerce_compare == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_compare" value="0" <?php if (!isset($tshirtecommerce_compare) || $tshirtecommerce_compare == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_special'>Special page</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_special" value="1" <?php if (isset($tshirtecommerce_special) && $tshirtecommerce_special == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_special" value="0" <?php if (!isset($tshirtecommerce_special) || $tshirtecommerce_special == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_manufacturer'>Manufacturer page</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_manufacturer" value="1" <?php if (isset($tshirtecommerce_manufacturer) && $tshirtecommerce_manufacturer == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_manufacturer" value="0" <?php if (!isset($tshirtecommerce_manufacturer) || $tshirtecommerce_manufacturer == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_feature'>
							<span data-toggle="tooltip" data-original-title="If select Yes, you need active Featured Extension.">
							Featured extension
							</span>
						</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_feature" value="1" <?php if (isset($tshirtecommerce_feature) && $tshirtecommerce_feature == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_feature" value="0" <?php if (!isset($tshirtecommerce_feature) || $tshirtecommerce_feature == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_bestseller'>
							<span data-toggle="tooltip" data-original-title="If select Yes, you need active Bestseller Extension.">
							Bestseller extension
							</span>
						</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_bestseller" value="1" <?php if (isset($tshirtecommerce_bestseller) && $tshirtecommerce_bestseller == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_bestseller" value="0" <?php if (!isset($tshirtecommerce_bestseller) || $tshirtecommerce_bestseller == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_lastest'>
							<span data-toggle="tooltip" data-original-title="If select Yes, you need active Lastest Extension.">
							Latest extension
							</span>
						</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_lastest" value="1" <?php if (isset($tshirtecommerce_lastest) && $tshirtecommerce_lastest == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_lastest" value="0" <?php if (!isset($tshirtecommerce_lastest) || $tshirtecommerce_lastest == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for='tshirtecommerce_special_m'>
							<span data-toggle="tooltip" data-original-title="If select Yes, you need active Special Extension.">
							Special extension
							</span>
						</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_special_m" value="1" <?php if (isset($tshirtecommerce_special_m) && $tshirtecommerce_special_m == 1) echo 'checked' ?> /> Yes 
							</label>
							<label class="radio-inline">
								<input type="radio" name="tshirtecommerce_special_m" value="0" <?php if (!isset($tshirtecommerce_special_m) || $tshirtecommerce_special_m == 0) echo 'checked' ?> /> No 
							</label>
						</div>
					</div>
				</fieldset>
			</form>		
	      </div>
	    </div>
  	</div>
</div>
<?php include 'tshirtecommerce_quicksetup.tpl'; ?>
<script>
	function ts_preview_css() {
		var css = document.getElementById('tshirtecommerce_customize_design_btn').value;
		document.getElementById('btn-preview').style = css;
	}

	// Quicksetup
	var btnQicksetup = document.getElementById('btn-quicksetup');
	var modalQicksetup = document.getElementById('modal-quicksetup');
	var spanQuicksetup = document.getElementsByClassName("close-quicksetup")[0];
	btnQicksetup.onclick = function() {
	    modalQicksetup.style.display = "block";
	}
	spanQuicksetup.onclick = function() {
	    modalQicksetup.style.display = "none";
	}
</script>
<?php echo $footer; ?>