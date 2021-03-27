$(document).ready(function() {

	$('input.form-range').bind('input ready load', function() {
		let valueBoxId = $(this).data('range-value');
		console.log($('#'+valueBoxId));
		
		if ( ( valueBoxId ) && ($('#'+valueBoxId)) ) {
			$('#'+valueBoxId).val($(this).val());
		}
	}).trigger('input');
	
	$('.modal.hidefocus').on('hidden.bs.modal', function(event) { 
		canCaptureKeys = true;
		event.relatedTarget.blur(); 
	});
	$('.modal.hidefocus').on('show.bs.modal', function(event) { 
		console.log('show modal');
		canCaptureKeys = false;
	});
	
	setTimeout(function() {
		$('.loader').remove();
	}, 300);
	
});
