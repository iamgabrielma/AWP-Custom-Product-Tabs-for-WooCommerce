jQuery(document).ready(function($){
	console.log('hello from jquery');

	jQuery('.awp-selected-option').click(function(e){
		console.log('clicked!');
		jQuery('.test-div').html('hello');
	});
});