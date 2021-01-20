<div class="product-elm-list"></div>
<center>
<button type="button" class="btn btn-primary btn-sm" onclick="productElm.reset()" data-toggle="modal" data-target=".product-elm-modal">Add Elment Of Product</button>
</center>


<div class="modal fade product-elm-modal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Product Elment</h4>
			</div>
			
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">Title</div>
						</div>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" class="form-control product-elm-title" placeholder="Enter elment name">
							</div>
						</div>						
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>List Colors</label>
								<p class="text-muted">Please click to choose color of element.</p>
								<br />
								
								<div class="product-elm-colors">
									<?php
									$dg 	= new dg();
									$colors = $dg->getColors();
									if (count($colors))
									{
										foreach($colors as $color) {
									?>
										<a href="javascript:void(0)" class="color" onclick="productElm.addColor(this)" title="<?php echo $color->title; ?>" data-color="<?php echo $color->hex; ?>" style="background-color:#<?php echo $color->hex; ?>"></a>
									<?php }} ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="product-elm-modal-save" onclick="productElm.save()">Save</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var elements = {};
</script>
<style>
.color.active {
  border: 1px solid #428bca;
  border-radius: 50%;
  box-shadow: 0 0 3px #333;
}
</style>