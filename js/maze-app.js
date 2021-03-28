console.log('init');

var canCaptureKeys = true;

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
	
	function launchItem($user,itemId) {
		
		if ( $user.data('item'+itemId)>0 ) {
			
			$user.data('item'+itemId,$user.data('item'+itemId)-1);
			
			switch (itemId) {
				case 1:
					$user.addClass('animBlinkGray');
					$user.data('through_walls',1);
				break;
				case 2:
					$user.addClass('animBlinkRed');
					$user.data('destroy_walls',1);
				break;
			}
		}
	}
		
	function movePlayer($player, xDif, yDif) {
		
		// cal new position
		xNew = $player.data('x')+xDif;
		yNew = $player.data('y')+yDif;
		
		// get new position block
		$oldCell = $('.maze .mcell[data-x='+$player.data('x')+'][data-y='+$player.data('y')+']').first();
		$cell = $('.maze .mcell[data-x='+xNew+'][data-y='+yNew+']').first();
		
		
		// calculate class addition for player icon
		let classMatix = {
			'0':{
				 '0': 'stop',
				'-1': 'up',
				 '1': 'down'
			},
			'1': {
				 '0': 'right'
			},
			'-1': {
				 '0': 'left'
			}
		}
		let curMoving = classMatix[xDif][yDif];
		let addClass = 'moving-'+curMoving;
		
		console.log(classMatix[xDif][yDif]);
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

		/*
		if ($oldCell.hasClass('withItem')) {
			$oldCell.removeClass('withItem');
		}
		*/
		
		if (
				( ($cell.data('isfree') >0) || (ignoreWalls==true) || (destroyWalls==true) ) 
				&&
				( !$cell.hasClass('mborder') )
			) {
		
			if (destroyWalls && $cell.hasClass('wall')) {
				$cell.removeClass('wall').addClass('space').data('isfree',1);
				// update neighbours:
				let cellX = $cell.data('x');
				let cellY = $cell.data('y');
				
				// top
				// console.log($('.cell[data-x='+cellX+'][data-y='+(cellY-1)+']'));
				$('.mcell[data-x='+cellX+'][data-y='+(cellY-1)+']').removeClass('bottom-wall').addClass('bottom-space');
				$('.mcell[data-x='+cellX+'][data-y='+(cellY+1)+']').removeClass('top-wall').addClass('top-space');
				$('.mcell[data-x='+(cellX-1)+'][data-y='+(cellY)+']').removeClass('right-wall').addClass('right-space');
				$('.mcell[data-x='+(cellX+1)+'][data-y='+(cellY)+']').removeClass('left-wall').addClass('left-space');
				
			}
		
			$player.data('x',xNew)
				.data('y',yNew);
				
			// clear old moving class
			$player.attr('class',$player.attr('class').replace(/\prev-moving-\w+/g, '') );
			
			// save prev moving
			$player.removeClass('moving-'+$player.data('moving'));
			$player.addClass('prev-moving-'+$player.data('moving'));
			
			// add new moving class
			$player.data('moving',curMoving);
			$player.addClass(addClass);
			
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
				
				$cell.removeClass('withItem');
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
		console.log( event.key, event.keyCode, canCaptureKeys);
		
		let myKey = true;
		if (!playerAnimation && canCaptureKeys) {
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
					launchItem($('.user'),2);
				break;
				case 49:
					launchItem($('.user'),1);
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
	
	$('.itemslist .item').bind('click',function(){
		if ($(this).data('itemid')) {
			launchItem($('.user'),$(this).data('itemid'));
		}
	});
	$('.itemslist .item').css('cursor','pointer');
	
});
