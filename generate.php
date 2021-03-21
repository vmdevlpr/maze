<?php


function getDistance($x,$y,$x2,$y2) {
	return abs($x-$x2)+abs($y-$y2);
}

function getDigCoordinates($x,$y) {
	
	return array(
		array('x'=>$x+1, 'y'=>$y),
		array('x'=>$x-1, 'y'=>$y),
		array('x'=>$x,   'y'=>$y+1),
		array('x'=>$x,   'y'=>$y-1),
	);
	
}

function getMaze($maze) {
	$out='';
	
	// output maze
	for ($i=0; $i<count($maze); $i++) {
		// $out .= ''.str_pad($i,2,'0',STR_PAD_LEFT).': ';
		for ($j=0; $j<count($maze[$i]); $j++) {
			$out .= ''.$maze[$i][$j]['type'].'';
		}
		$out .= "\n";
	}
	
	return $out;
}


function countFreeCells($maze,$x,$y,$x2,$y2) {
	
	$freeCells = 0;
		
	for ($i=min($y,$y2); $i<=max($y,$y2); $i++) {
		for ($j=min($x,$x2); $j<=max($x,$x2); $j++) {
			if ($maze[$i][$j]['free'] === true) {
				$freeCells ++;
			}
		}
	}
	
	echo '<!-- ['.$x.','.$y.'],['.$x2.','.$y2.'] = '.$freeCells."-->\n";
	
	return $freeCells;
}


$maze = array();
$wallCell = array(
			'type' => 'X',
			'free' => false,
			'start' => false,
			'finish' => false,
		);
$emptyCell = array(
			'type' => ' ',
			'free' => true,
			'start' => false,
			'finish' => false,
		);		
		
$maxX = 40+1;
$maxY = 20+1;

$startX = 1;
$startY = 1;

$finishX = $maxX-1;
$finishY = $maxY-1;

// fill maze with defaults
for ($i=1;$i<$maxY;$i++) {
	for ($j=1;$j<$maxX;$j++) {
		$maze[$i][$j]=$wallCell;
	}
}

// make borders 
	// top and bottom borders
	for ($i=0;$i<=$maxX;$i++) {
		$maze[0][$i] = $wallCell;
		$maze[$maxY][$i] = $wallCell;
	}

	// left and right borders
	for ($i=0;$i<=$maxY;$i++) {
		$maze[$i][0] = $wallCell;
		$maze[$i][$maxX] = $wallCell;
	}

// mark start and finish
$maze[$startY][$startX]['type'] = 'S';
$maze[$startY][$startX]['free'] = true;
$maze[$startY][$startX]['start'] = true;

$maze[$finishY][$finishX]['type'] = 'F';
$maze[$finishY][$finishX]['free'] = true;
$maze[$finishY][$finishX]['finish'] = true;



// generate maze
function digMaze($maze, $genPosX, $genPosY, $params = array()) {
	
	$defParams = array(
		'useRelocations' => true,
		'emptyCell' => $GLOBALS['emptyCell'],
		'startX' => $GLOBALS['startX'],
		'startY' => $GLOBALS['startY'],
	);
	
	$params = array_merge($defParams,$params);
	


	// $genPosX = $finishX;
	// $genPosY = $finishY;
	$genMinX = 1;
	$genMinY = 1;
	$genMaxX = count($maze[0])-2;
	$genMaxY = count($maze)-2;
	$genDone = false;

	$genClosest = array(
					'x'=>$genPosX,
					'y'=>$genPosY,
					'distance'=>getDistance($params['startX'],$params['startY'],$genPosX,$genPosY), 
					'relocations' => 0
				);


	$steps = 0;
	$allSteps = array();
	$relocationErorrs = 0;
	$success = false;

	while ( !$genDone ) {
		
		$steps++;
		
		// check directions we can "dig" to from (left, right, up, down)
		$digCoords = getDigCoordinates($genPosX,$genPosY);
		
		$digAvailable = array();
		$newCoord = false;
		foreach ($digCoords as $coord) {
			// if it is start
			if ($maze[$coord['y']][$coord['x']]['start'] === true) {
				
				$digAvailable[] = $coord;
				$newCoord = $coord;
				break;
				
			}
			
			// check if coord is over minmums
			if ( 
					($coord['x']>$genMaxX) or
					($coord['x']<$genMinX) or
					($coord['y']>$genMaxY) or
					($coord['y']<$genMinY)
				) {
					
					continue;
					
				}
				
			// if it is finished
			if ($maze[$coord['y']][$coord['x']]['finish'] === true) {
				
				continue;
				
			}
			
			// if it is already free
			if (
					($maze[$coord['y']][$coord['x']]['start'] !== true) and
					($maze[$coord['y']][$coord['x']]['free'] === true)
				){
				
				continue;
				
			}
			
			
			// don't make "fields";
			if (
					( (countFreeCells($maze,$coord['x'],$coord['y'],$coord['x']+1,$coord['y']-1))==3 ) or
					( (countFreeCells($maze,$coord['x'],$coord['y'],$coord['x']+1,$coord['y']+1))==3 ) or
					( (countFreeCells($maze,$coord['x'],$coord['y'],$coord['x']-1,$coord['y']-1))==3 ) or
					( (countFreeCells($maze,$coord['x'],$coord['y'],$coord['x']-1,$coord['y']+1))==3 )
				) {
					
				continue;
				
			}

			$digAvailable[] = $coord;
				
		}
		
		
		// make a dig if we can
		if (count($digAvailable)>0) {

			// choose coord
			if ($newCoord === false) {
				$digChoice = rand(0,count($digAvailable)-1);
				$newCoord = $digAvailable[$digChoice];
			}
			
			if (
					($maze[$newCoord['y']][$newCoord['x']]['start'] === true) 
				) {
				
				echo 'Done!<BR>';
				$success = true;
				$genDone = true;
				
			} else {
				
				// make changes in maze
				$maze[$newCoord['y']][$newCoord['x']] = $params['emptyCell'];
				// $maze[$newCoord['y']][$newCoord['x']]['type'] = ($steps % 10);
				
				// change genPos
				$genPosX = $newCoord['x'];
				$genPosY = $newCoord['y'];
				
				$newDistance = getDistance($params['startX'],$params['startY'],$genPosX,$genPosY);
				if ($genClosest['distance']>$newDistance) {
					$genClosest = array('x'=>$genPosX, 'y'=>$genPosY, 'distance'=>$newDistance, 'relocations'=>0 );
				}
				
				
				echo '<!-- '.$genPosX.', '.$genPosY.' -->'."\n";
				
				$allSteps[] = $newCoord;
				// echo ''.getMaze($maze).'';
				
			}
			
		} else {
			
			// $genDone = true;
			echo 'Nowhere to dig! ['.$genPosX.','.$genPosY.'] :-( - relocate pointer<BR>';

			if ($params['useRelocations'] === true) {
				if ($genClosest['relocations'] > 10) {
					
					// too many relocations -- choose another place to relocate
					$relocationErorrs++;
					$oldStep = count($allSteps)-$relocationErorrs-3;
					if (isset($allSteps[$oldStep])) {
						$c = $allSteps[$oldStep];
					} else {
						$c = $allSteps[0];
					}
					
					$genPosX = $c['x'];
					$genPosY = $c['y'];
					
					echo 'Too many relocations, goto ['.$genPosX.','.$genPosY.'] <BR>';
					// $genClosest['relocations']++;
					
					
				} else {
					$genPosX = $genClosest['x'];
					$genPosY = $genClosest['y'];
					$genClosest['relocations']++;
				}
				
				echo 'Restart from ['.$genPosX.','.$genPosY.'] <BR>';
				// $maze[$genPosY][$genPosX]['type'] = '!';
			} else {
				$genDone = true;
			}
		}
		
		
		if ($steps>$genMaxX*$genMaxY / 2) {
			$genDone = true;
		}
			
		
	}

	return array(
		'maze' => $maze,
		'success' => $success,
	);

}

$r=array(
	'success' => false,
);

while (!$r['success']) {
	$r = digMaze($maze,$finishX,$finishY);
}

if ($r['success']) {
	
	// add extra paths
	$r = digMaze($r['maze'],1,$maxY, array('useRelocations' => false));
	$r = digMaze($r['maze'],$maxX,1, array('useRelocations' => false));
	
	$maze = $r['maze'];
	
}


echo 'steps: '.$steps.'<BR>';
echo '<PRE>'.getMaze($maze).'</PRE>';
// echo '<PRE>'.var_export($allSteps,true).'</PRE>';
// print_r($maze);



?>