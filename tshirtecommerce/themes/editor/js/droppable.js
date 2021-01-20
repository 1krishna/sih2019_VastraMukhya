jQuery( function() {
	design.drop.init();
});

design.drop = {
	init: function(){
		this.upload();
		jQuery( ".design-area .content-inner" ).droppable({
			accept: '#uploaded-art .view-thumb',
			drop: function( event, ui ) {
			}
		});
	},
	upload: function(){
		jQuery('#uploaded-art .view-thumb').draggable({
			revert: "invalid",
      		containment: "document",
     			cursor: "move"
		});
	}
}