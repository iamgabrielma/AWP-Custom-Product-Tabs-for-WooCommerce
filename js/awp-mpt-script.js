jQuery(document).ready(function($){
	
	$('select option').on('mousedown', function (e) {
    	this.selected = !this.selected;
    	e.preventDefault();
	});

});