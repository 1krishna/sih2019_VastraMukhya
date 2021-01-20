<?php
/**
 * @author tshirtecommerce - www.tshirtecommerce.com
 * @date: 2015-11-26
 *
 *
 * @copyright  Copyright (C) 2015 tshirtecommerce.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 *
 */
	$addons 	= $GLOBALS['addons'];
	$settings 	= $GLOBALS['settings'];
	$lang 		= $GLOBALS['lang'];
	$product 	= $GLOBALS['product'];	
?>
<div class="modal fade" id="dg-designer-store" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo $addons->__('addon_designer_store'); ?></h4>
			</div>
			
			<div class="modal-body">				
				<div class="form-group">
					<label><?php echo $addons->__('addon_designer_store_product_title'); ?></label>
					<p class="text-muted"><small><?php echo $addons->__('addon_designer_store_product_title_description'); ?></small></p>
					<input type="text" class="form-control" id="store_product_title" placeholder="<?php echo $addons->__('addon_designer_store_product_title'); ?>">
				</div>
				
				<div class="form-group">
					<label><?php echo $addons->__('addon_designer_store_product_description'); ?></label>
					<p class="text-muted"><small><?php echo $addons->__('addon_designer_store_product_description_description'); ?></small></p>
					<textarea class="form-control" id="store_product_description" rows="3"></textarea>
				</div>
				
				<div class="form-group">
					<label for="form-tags"><?php echo $addons->__('addon_designer_store_product_tags'); ?></label>
					<p class="text-muted"><small><?php echo $addons->__('addon_designer_store_product_tags_description'); ?></small></p>
					<input type="text" class="form-control tags-input" placeholder="<?php echo $addons->__('addon_designer_store_product_tags'); ?>" id="e-form-tags">
				</div>
				
				<div class="form-group">
					<label><?php echo $addons->__('addon_designer_store_product_url'); ?></label>
					<p class="text-muted"><small><?php echo $addons->__('addon_designer_store_product_url_description'); ?></small></p>
					<div class="input-group">
						<div class="input-group-addon"><?php echo $settings->site_url; ?></div>
						<input type="text" class="form-control" id="store_product_url" onkeyup="design.admin.product.url(this)">
					</div>
					<p class="text-danger"><small></small></p>
				</div>
				
				<div class="form-group">
					<label><?php echo $addons->__('addon_designer_store_product_length'); ?></label>
					<p class="text-muted"><small><?php echo $addons->__('addon_designer_store_product_length_description'); ?></small></p>
					<div class="row">						
						<div class="col-md-4">
							<div class="input-group">
								<span class="input-group-addon label-input-group"><?php echo $addons->__('addon_designer_store_product_length_end'); ?></span>
								<input type="text" class="form-control e-input-date" id="store-campaign-end">
							</div>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<label><?php echo $addons->__('addon_designer_store_product_selling'); ?></label>
					<p class="text-muted"><small><?php echo $addons->__('addon_designer_store_product_selling_description'); ?></small></p>
					<div class="row">
						<div class="col-md-4">
							<div class="input-group">
								<span class="input-group-addon label-input-group"><?php echo $addons->__('addon_designer_store_product_selling_profit'); ?></span>
								<input type="text" onkeyup="design.admin.product.iniPrice()" class="form-control" value="2" id="store-profit">
							</div>
							<br />
							<p class="text-muted"><?php echo $lang['min_quantity']; ?> <strong id="store-product-minorder"></strong></p>
						</div>
						
						<div class="col-md-3 col-sm-6 text-list-price">
							<p><?php echo $addons->__('addon_designer_store_price_base'); ?> <?php echo $settings->currency_symbol; ?> <strong><span class="store-price-product-base"></span></strong></p>
							<p><?php echo $addons->__('addon_designer_store_price_sale'); ?> <?php echo $settings->currency_symbol; ?> <strong><span class="store-price-product-sale"></span></strong></p>
						</div>
						
						<div class="col-md-5 col-sm-6">						
							<div class="list-options-price">
								
								<?php if (isset($product->prices) && count($product->prices) > 0 && isset($product->prices->price)){ ?>
								<div class="btn-group">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<small><?php echo $addons->__('addon_designer_store_product_selling_price'); ?></small> <span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										<?php 
										$prices 		= $product->prices->price;
										$min_quantity 	= $product->prices->min_quantity;
										$max_quantity 	= $product->prices->max_quantity;
										?>
											<?php for($ij=0; $ij < count($min_quantity); $ij++){ ?>
											<li>
												<a href="javascript:void(0);">
													<span class="full-left"><?php echo $min_quantity[$ij]; ?> - <?php echo $max_quantity[$ij]; ?> <?php echo $addons->__('addon_designer_store_product_sales'); ?></span>
													<span class="full-right"><?php echo $settings->currency_symbol; ?><strong data-value="<?php echo $prices[$ij]; ?>" class="options-price-value"><?php echo $prices[$ij]; ?></strong></span>
												</a>
											</li>
											<?php } ?>
										</ul>
									</ul>
								</div>
								<?php } ?>						
							</div>
						</div>
					</div>
				</div>
			
				<div class="form-group">
					<label><input type="checkbox" class="agree-store-terms"> <small><?php printf($addons->__('addon_designer_store_terms'), $settings->site_upload_terms); ?></small></label>
				</div>
			
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-sm-9">
						<div class="dg-progress">
							<div class="progress">
								<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
									<span class="sr-only">0% Complete (success)</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-sm-3">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary create-product" onclick="design.admin.product.save(this);"><?php echo $addons->__('addon_designer_store_product_sale'); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>