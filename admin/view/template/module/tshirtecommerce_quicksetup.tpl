
<link rel="stylesheet" href="<?php echo $site_url; ?>/tshirtecommerce/opencart/quicksetup/css/setup.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?php echo $site_url; ?>/tshirtecommerce/opencart/quicksetup/css/quicksetup.css" type="text/css" media="all" />

<script type="text/javascript" src="<?php echo $site_url; ?>/tshirtecommerce/opencart/quicksetup/js/quicksetup.js"></script>
<script type="text/javascript">
	var ajax_tool 		= '<?php echo $quicksetup["ajax_tool"] ?>';
	var ajax_clipart 	= '<?php echo $quicksetup["ajax_clipart"] ?>';
	var ajax_import 	= '<?php echo $quicksetup["ajax_import"] ?>';
	var ajax_finish 	= '';
</script>

<div class="modal-quicksetup" id="modal-quicksetup" style="display: none; overflow: hidden;">
	<div class="modal-content">
	  	<p class="p-close-quicksetup"><span class="close-quicksetup">&times;</span></p>
	  	<div class="modal-body">
	    	<h1 class="logo text-center">
				<a href="http://tshirtecommerce.com/" target="_blank">
					<img src="https://tshirtecommerce.com/wp-content/uploads/2015/09/small-logo1.png" alt="tshirtecommerce.com">
				</a>
			</h1> 
			<div class="e-steps">
				<div class="e-steps-label">
					<div class="e-step-1 e-step e-step-active" id="quicksetup1">Design Tool <span>1</span></div>
					<div class="e-step-2 e-step" id="quicksetup2">Cliparts Store <span>2</span></div>
					<div class="e-step-3 e-step" id="quicksetup3">Import Products <span>3</span></div>
					<div class="e-step-4 e-step" id="quicksetup4">Finished! <span>4</span></div>
				</div>
				<div class="progress">
				  <div id="progress-bar-step" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%">
						<span class="sr-only">25%  Complete (success)</span>
				  </div>
				</div>
			</div> <!-- \End Steps -->

			<div id="e-design-tool" class="page-content" style="display: block;">
				<h2 class="title">Setting Design Tool</h2>
				<p>Thank you for choosing T-Shirt eCommerce. This quick setup wizard will help you configure the basic settings. It's completely optional and shouldn't take longer than five minutes.</p>
				<hr/>
				<form class="form-horizontal" method="post" action="">
					<div class="form-group">
						<label class="col-xs-4">Layout of design tool</label>
						<div class="col-xs-5">
							<?php if (isset($quicksetup['layouts']) && count($quicksetup['layouts'])) { ?>
								<select class="form-control" name="layout">
									<?php foreach ($quicksetup['layouts'] as $layout => $val) { ?>
									<option value="<?php echo $layout; ?>" <?php if ($val['theme'] === 'default') echo 'selected'; ?>><?php echo $val['title']; ?></option>
									<?php } ?>
								</select>
							<?php } else { echo 'Data Not Found'; } ?>
							<div class="col-xs-ofsset-5 col-xs-12 row">
								<div class="center-block text-muted">Choose your language display in page design tool or <a href="http://docs.tshirtecommerce.com/knowledgebase/setting-lanuages/" target="_blank">add new language.</a></div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-4">Your language</label>
						<div class="col-xs-5">
							<?php if (isset($quicksetup['languages']) && count($quicksetup['languages'])) { ?>
							<select class="form-control" name="language">
								<?php foreach ($quicksetup['languages'] as $key => $val) { ?>
								<option value="<?php echo $val['code']; ?>" <?php if ($val['default'] == 1) echo 'selected'; ?>><?php echo $val['title']; ?></option>
								<?php } ?>
							</select>
							<?php } else { echo 'Data Not Found'; } ?>
							<div class="col-xs-ofsset-5 col-xs-12 row">
								<div class="center-block text-muted">You can <a style="color:#0049b0;text-decoration:underline;" href="http://docs.tshirtecommerce.com/knowledgebase/customize-layout-with-color-background-of-design-tool/" target="_blank">add new or customize layout</a></div>
							</div>
						</div>
					</div>
					<hr/>
					<p class="text-right">
						<a class="btn btn-default pull-left" href="http://docs.tshirtecommerce.com/kb/opencart-custom-product-designer/" target="_blank">Document Online</a>
						<a href="javascript:void(0)" class="btn btn-default" onclick="quicksetup.skip('cliparts-store','design-tool',50)">Skip this step</a>
						<a href="javascript:void(0)" class="btn btn-success" onclick="quicksetup.submit('quicksetup2')">Save &amp; Continue</a>
					</p>
				</form>
			</div> <!-- \End Design Tool -->

			<div id="e-cliparts-store" class="page-content" style="display: none;">
				<h2 class="title">Cliparts and Design Template</h2>
				<p>We give to you librarie clipart and design template. You can active <a href="http://9file.net/" target="_blank">T-Shirt eCommerce Store</a> to use design from our community.</p>
				<hr/>
				<form class="form-horizontal" method="post" action="">
					<p>1. Create account on website <a href="http://store.9file.net/users/register" target="_blank">http://store.9file.net</a></p>
					<div class="form-group" style="margin-bottom: 0px;">
						<label class="col-xs-7 row">2. Enter your API</label>
						<div class="col-xs-7 row"><input type="text" class="form-control" id="store-api"></div>
					</div>
					<p><small>Video create account and get your API <a href="https://youtu.be/cPvRpXdVe0s?t=23s" target="_blank">https://youtu.be/cPvRpXdVe0s</a></small></p>
					<p>3. Click button "<strong>Active &amp; Download Design</strong>"</p>
					<div id="download-store2" class="download-store">
						<p class="help-block">Status: <strong class="text-danger text-status">Checking API</strong></p>
						<div class="progress">
						  <div id="progress-bar2" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
							<span class="sr-only">1% Complete (success)</span>
						  </div>
						</div>
						<p id="text-status2" class="text-danger">Please don't navigate away while importing</p>
					</div>
					<hr>
					<p class="text-right">
						<a class="btn btn-default pull-left" href="javascript:void(0)" onclick="quicksetup.back('design-tool','cliparts-store',25)">Back</a>
						<a class="btn btn-default" href="javascript:void(0)" onclick="quicksetup.skip('import-products','cliparts-store',75)">Skip this step</a>
						<button type="button" onclick="quicksetup.download(this)" class="btn btn-success"">Active &amp; Download Design</button>
					</p>
				</form>
			</div> <!-- \End Clipart Store -->

			<div id="e-import-products" class="page-content" style="display: none;">
				<h2 class="title">Import Product Demo</h2>
				<p>Please check button "Import Products" to automatic import products demo.</p>
				<hr/>
				<div id="download-store3" class="download-store">
					<p class="help-block">Status: <strong class="text-danger text-status">Downloading data</strong></p>
					<div class="progress">
					  	<div id="progress-bar3" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
							<span class="sr-only">1% Complete (success)</span>
					  	</div>
					</div>
					<p id="text-status3" class="text-danger">Please don't navigate away while importing</p>
				</div>
				<p class="text-right">
					<a class="btn btn-default pull-left" href="javascript:void(0)" onclick="quicksetup.back('cliparts-store','import-products',50)">Back</a>
					<a class="btn btn-default" href="javascript:void(0)" onclick="quicksetup.skip('finished','import-products',100)">Skip this step</a>
					<button type="button" onclick="quicksetup.import(this)" class="btn btn-success ">Import Products</button>
				</p>
			</div> <!-- \End Import Products -->

			<div id="e-finished" class="page-content" style="display: none;">
				<h2 class="title">Finished!</h2>
				<p class="des-addon">Congratulations! You have successfully installed plugin.</p>
				<hr/>
				<div class="alert alert-info">
					We provide some useful addon for this plugin that add many functions awesome on your site.
					<br>
					<br>
					<a class="btn btn-default btn-sm" target="_blank" href="https://tshirtecommerce.com/add-ons">View Addons</a>
				</div>
				<div class="form-horizontal">
					<div class="form-group">
						<div class="col-xs-6">
							<p><strong>Help &amp; Support</strong></p>
							<ul>
								<li>Please read <a target="_blank" href="http://docs.tshirtecommerce.com/kb/opencart-custom-product-designer/">documentation</a> of module</li>
								<li>If you need help, please <a target="_blank" href="https://tshirtecommerce.com/submit-ticket">submit a ticket</a></li>
							</ul>
							<div class="fb-share-button fb_iframe_widget" data-href="https://tshirtecommerce.com" data-layout="button" data-size="small" data-mobile-iframe="true" fb-xfbml-state="rendered" fb-iframe-plugin-query="app_id=468877319808630&amp;container_width=319&amp;href=https%3A%2F%2Ftshirtecommerce.com%2F&amp;layout=button&amp;locale=en_US&amp;mobile_iframe=true&amp;sdk=joey&amp;size=small"><span style="vertical-align: bottom; width: 58px; height: 20px;"><iframe name="f2096c6ca7362e8" width="1000px" height="1000px" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="fb:share_button Facebook Social Plugin" src="https://www.facebook.com/v2.8/plugins/share_button.php?app_id=468877319808630&amp;channel=http%3A%2F%2Fstaticxx.facebook.com%2Fconnect%2Fxd_arbiter%2Fr%2F0F7S7QWJ0Ac.js%3Fversion%3D42%23cb%3Df3dde5e2ef84a9%26domain%3Dlocalhost%26origin%3Dhttp%253A%252F%252Flocalhost%252Ff20e0bbc6d225b4%26relation%3Dparent.parent&amp;container_width=319&amp;href=https%3A%2F%2Ftshirtecommerce.com%2F&amp;layout=button&amp;locale=en_US&amp;mobile_iframe=true&amp;sdk=joey&amp;size=small" style="border: none; visibility: visible; width: 58px; height: 20px;" class=""></iframe></span></div>
							<br>
							<br>
							<iframe id="twitter-widget-0" scrolling="no" frameborder="0" allowtransparency="true" class="twitter-share-button twitter-share-button-rendered twitter-tweet-button" title="Twitter Tweet Button" src="http://platform.twitter.com/widgets/tweet_button.5b6375bb17bd9edb2f4e7f8f12971999.en.html#dnt=false&amp;id=twitter-widget-0&amp;lang=en&amp;original_referer=http%3A%2F%2Flocalhost%2Fiwp%2Fwp-admin%2Findex.php%3Fpage%3Dtshirtecommerce-setting%26step%3Dcomplete&amp;size=m&amp;text=complete%20business%20solution%20for%20selling%20custom%20printing%20products&amp;time=1495266290315&amp;type=share&amp;url=https%3A%2F%2Ftshirtecommerce.com%2F" style="position: static; visibility: visible; width: 60px; height: 20px;" data-url="https://tshirtecommerce.com/"></iframe><script async="" src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
						</div>
						
						<div class="col-xs-6">
							<p><strong>Popular articles</strong></p>
							<ul>
								<li><a target="_blank" href="http://docs.tshirtecommerce.com/kb/opencart-custom-product-designer/">Setting Module</a></li>
								<li><a target="_blank" href="http://docs.tshirtecommerce.com/knowledgebase/update-module/">Where I can updating plugin</a></li>
								<li><a target="_blank" href="http://docs.tshirtecommerce.com/kb/opencart-custom-product-designer/prices-and-printing-method-opencart-custom-product-designer/">Prices and printing method</a></li>
								<li><a target="_blank" href="">Add new product design</a></li>
							</ul>
						</div>
					</div>
				</div>
				<p class="text-right">
					<a class="btn btn-default pull-left" href="">Create your first product!</a>
					<a class="btn btn-default btn-success" href="">View Design Tool</a>		
				</p>
			</div> <!-- \End Finish -->
	  	</div> <!-- \End Modal body -->
	</div> <!-- \End Modal content -->
</div> <!-- \End Modal Quicksetup -->