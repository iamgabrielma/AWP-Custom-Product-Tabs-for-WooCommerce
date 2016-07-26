jQuery(document).ready(function($){
	
	// Info messages
	$('.custom_tabs_ids').click(function(){
		//$('#custom_tabs_ids').html('You can select multiple tabs by clicking on them.');

	});
	$('.exclude_custom_tabs_ids').click(function(){
		//$('#exclude_custom_tabs_ids').html('You can exclude multiple tabs just clicking on them.');
	});
	
	// Easy selection/deselection
	$('select option').on('mousedown', function (e) {
    	this.selected = !this.selected;
    	e.preventDefault();
	});

});