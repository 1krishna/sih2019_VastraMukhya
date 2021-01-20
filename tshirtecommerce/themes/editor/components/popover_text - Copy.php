<?php
$addons = $GLOBALS['addons'];
?>
<div id="options-add_item_text" class="dg-options">
	<div class="dg-options-toolbar">
		<button class="btn btn-default toolbar-text" type="button" data-type="text">
			<textarea class="form-control text-update" data-event="keyup" data-label="text" id="enter-text"></textarea> <i class="fa fa-pencil"></i>
		</button>
		<button class="btn btn-default toolbar-font" data-target="#dg-fonts" data-toggle="modal">
			<a id="txt-fontfamily" class="pull-left" href="javascript:void(0)">
				<?php echo lang('designer_clipart_edit_arial'); ?>
			</a>
			<i class="fa fa-angle-down" aria-hidden="true"></i>
		</button>
		
		<button class="btn btn-default">
			<div class="list-colors">
				<a class="dropdown-color" id="txt-color" href="javascript:void(0)" data-color="black" data-label="color" style="background-color:black"></a>
			</div>
		</button>
		
		<div id="text-align">
			<span id="text-align-left" class="text-update btn btn-sm glyphicons align_left glyphicons-12" data-event="click" data-label="alignL"></span>
			<span id="text-align-center" class="text-update btn btn-sm glyphicons align_center glyphicons-12" data-event="click" data-label="alignC"></span>
			<span id="text-align-right" class="text-update btn btn-sm glyphicons align_right glyphicons-12" data-event="click" data-label="alignR"></span>
		</div>
		
		<div id="text-style">
			<span id="text-style-i" class="text-update btn btn-sm glyphicons italic glyphicons-12" data-event="click" data-label="styleI"></span>
			<span id="text-style-b" class="text-update btn btn-sm glyphicons bold glyphicons-12" data-event="click" data-label="styleB"></span>							
			<span id="text-style-u" class="text-update btn btn-sm glyphicons text_underline glyphicons-12" data-event="click" data-label="styleU"></span>
		</div>
				
		<div class="rotate">
			<span class="rotate-values"><input type="text" value="0" class="input-small rotate-value" id="text-rotate-value" /></span>			
			<span class="rotate-deg"></span>
		</div>
			
		<div class="toolbar-size">
			<input type="text" size="2" id="text-width" readonly disabled> 
			x
			<input type="text" size="2" id="text-height" readonly disabled>
			<a href="#" class="icon-ui-lock" title="<?php echo lang('designer_clipart_edit_unlock_proportion'); ?>">
				<i class="fa fa-lock" aria-hidden="true"></i>
			</a>
			<input type="checkbox" style="display:none;" class="ui-lock" id="text-lock" />
		</div>
		
		<?php $addons->view('text-mobile'); ?>
	</div>
	
	<div class="dg-options-content">
		<?php $addons->text(); ?>		
	</div>
</div>