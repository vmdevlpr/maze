$(document).ready(function() {

	$('input.form-range').bind('input ready load', function() {
		let valueBoxId = $(this).data('range-value');
		console.log($('#'+valueBoxId));
		
		if ( ( valueBoxId ) && ($('#'+valueBoxId)) ) {
			$('#'+valueBoxId).val($(this).val());
		}
	}).trigger('input');
});
