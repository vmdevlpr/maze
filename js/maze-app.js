console.log('init');
$(document).ready(function() {

	console.log('run');
	
	function renewUserPosition(selector = ".user") {
		$(selector).each(function(){
			// console.log(this);
			$(this).css('top',$(this).data('y')+'rem')
				.css('left',$(this).data('x')+'rem')
				.show();
		})
	}
	renewUserPosition();
	
	function updateCounters(selector = ".user") {
		
		$('.itemslist .item').each(function() {
			id = $(this).attr('id');
			amount  = $(selector).data(id);
			$('.amount',this).text(amount);
		});
		
	}
		
	function movePlayer($player, xDif, yDif) {
		
		// cal new position
		xNew = $player.data('x')+xDif;
		yNew = $player.data('y')+yDif;
		
		// get new position block
		$oldCell = $('.maze .mcell[data-x='+$player.data('x')+'][data-y='+$player.data('y')+']').first();
		$cell = $('.maze .mcell[data-x='+xNew+'][data-y='+yNew+']').first();
		
		console.log($player);

		/* additional actions */
		if ($player.data('through_walls')==1) {
			ignoreWalls = true;
		} else {
			ignoreWalls = false;
		}
		if ($player.data('destroy_walls')==1) {
			destroyWalls = true;
		} else {
			destroyWalls = false;
		}


		if ($oldCell.hasClass('withItem')) {
			$oldCell.removeClass('withItem');
		}
		
		if (
				( ($cell.data('isfree') >0) || (ignoreWalls==true) || (destroyWalls==true) ) 
				&&
				(!$cell.hasClass('border'))
			) {
		
			if (destroyWalls && $cell.hasClass('wall')) {
				$cell.removeClass('wall').addClass('space').data('isfree',1);
			}
		
			$player.data('x',xNew)
				.data('y',yNew);
			
			renewUserPosition($player);
			
			if ($cell.hasClass('withItem')) {
				// cell has some item - take it!
				itemKey = $cell.data('hasitem');
				amount = 1;
				
				if ($player.data('item'+itemKey)) {
					$player.data('item'+itemKey,$player.data('item'+itemKey)+amount);
				} else {
					$player.data('item'+itemKey,amount);
				}
				
				// console.log($("#item_"+itemKey+" .amount"));
				
			}
			
			if (ignoreWalls) {
				// switch off wall ignorance
				$player.removeClass('animBlinkGray');
				$player.data('through_walls',0);
			}
			if (destroyWalls) {
				// switch off wall destroing
				$player.removeClass('animBlinkRed');
				$player.data('destroy_walls',0);
			}
			
			if ($cell.hasClass('finish')) {
				// alert(' Молодец! ');
				$("#finishMessage").show();
				setTimeout(function(){
					$("#finishMessage").hide();
				},3000);
			}
			
			updateCounters();
		}
	}
	
	var playerAnimation = false;
	var playerAnimationTimer = 0;
	
	var stAnimationLength = 300;
	$('.user').css('transition-duration',(stAnimationLength+200)+'ms');
	
	$( document ).bind('keyup',function( event ) {
		clearTimeout(playerAnimationTimer);
		playerAnimation = false;
		console.log('keyup');
	});
	$( document ).bind('keydown',function( event ) {
		console.log( event.key, event.keyCode);
		
		let myKey = true;
		if (!playerAnimation) {
			switch (event.keyCode) {
				case 37:
					// arrow left
					movePlayer($('#player'),-1,0);
				break;
				case 38:
					// arrow up
					movePlayer($('#player'),0,-1);
				break;
				case 40:
					// arrow down
					movePlayer($('#player'),0,1);
				break; 
				case 39:
					// arrow right
					movePlayer($('#player'),1,0);
				break;
				case 50:
					itemId = 1;
					if ( $('.user').data('item'+itemId)>0 ) {
						
						$('.user').data('item'+itemId,$('.user').data('item'+itemId)-1);
						
						$('.user').addClass('animBlinkRed');
						$('.user').data('destroy_walls',1);
						/*
						setTimeout(function(){
							$('.user').removeClass('animBlinkRed');
						}, 4000);
						*/
					}
				break;
				case 49:
					itemId = 0;
					if ( $('.user').data('item'+itemId)>0 ) {
						
						$('.user').data('item'+itemId,$('.user').data('item'+itemId)-1);
						$('.user').addClass('animBlinkGray');
						$('.user').data('through_walls',1);
						/*
						setTimeout(function(){
							$('.user').removeClass('animBlinkGray');
							$('.user').data('through_walls',0);
						}, 4000);
						*/
					}
				break;
				default: 
					myKey = false;
			}
		} else {
			myKey = false;
		}
		
		if (myKey) {
			playerAnimation=true;
			playerAnimationTimer = setTimeout(function(){
				playerAnimation=false;
			},stAnimationLength);
			event.preventDefault();
		}
	});
	
});
