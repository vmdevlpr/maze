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
		let callButtonId = $(this).data('hidefocus');
		if (callButtonId) {
			setTimeout(function () {
				$('#'+callButtonId).blur();
			},1);
		}
	});
	$('.modal.hidefocus').on('show.bs.modal', function(event) { 
		console.log('show modal');
		canCaptureKeys = false;
	});
	
	$('#copyMaze').bind('click', function(event) { 
		$("textarea#mazeText").select();
		document.execCommand('copy');
		console.log($("textarea#mazeText"));
	});
	
	$('#downloadMaze').bind('click', function(event) { 
		$("#mazeInputForm").attr('action','download.php').attr('target','_blank').submit();
		$("#mazeInputForm").attr('action','?').attr('target','');
	});
	
	
	setTimeout(function() {
		$('.loader').remove();
	}, 300);
	
});
