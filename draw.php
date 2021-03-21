<?php

error_reporting(E_ALL);
ini_set('display_errors','On');


/* check security of filenames!!! preg_match can be used*/
if (!empty($_GET['src'])) {
	$srcFileMask = '|^[\w\d\.\-]+$|i';
	if ( 
			(!empty($_GET['src'])) and 
			(preg_match($srcFileMask,$_GET['src'])) and 
			(file_exists('./mazes/'.$_GET['src'])) 
		) {
		$source = file_get_contents('./mazes/'.$_GET['src']);
	} else {
		echo 'Error in filename: "'.$_GET['src'].'"<BR>';
	}
}


if (!empty($_GET['rnd'])) {
	// generate random maze
	if (isset($_GET['x'])) {
		$rndX = $_GET['x'];
	} else {
		$rndX = rand(10,20);
	}
	
	if (isset($_GET['y'])) {
		$rndY = $_GET['y'];
	} else {
		$rndY = rand(10,15);
	}
	
	include_once('include/Maze.php');

	$m = new Maze((int) $rndX, (int) $rndY);
	// $m = new Maze();
	$m->generate();
	$source = $m->getTextMaze();
	
	// add start and finish
/*	
	echo '<PRE>
'.$source.'
</PRE>';
*/
	// die();

}

if (empty($source)) {
	die('No source?');
}










// convert source to array
$maze=array();
$sourceLines = explode("\n",trim($source));
foreach ($sourceLines as $line) {
	$line = trim($line);
	$maze[] = str_split($line);
}

// print_r($maze);

// parse array to maze params
$maze_params = array(
	'cell' => ['width'=>20,'height'=>20],
	'start' => ['x'=>false,'y'=>false],
	'fnish' => ['x'=>false,'y'=>false],
);

$emptyCells = array();
$mazeCells = array();
foreach ($maze as $curY=>$maze_line) {
	foreach ($maze_line as $curX=>$maze_cell) {
		switch ($maze_cell) {
			case 'X':
				// build a wall
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'items' => array(),
					'intag' => array(
						'class'=>'mcell wall',
						'data-isfree'=>0,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
			break;
			case '#':
				// build a side wall (unbrekable, ancrossable)
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'items' => array(),
					'intag' => array(
						'class'=>'mcell wall mborder',
						'data-isfree'=>0,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
			break;
			case 'F':
				$maze_params['finish']=['x'=>$curX,'y'=>$curY];
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'items' => array(),
					'intag' => array(
						'class'=>'mcell space finish',
						'data-isfree'=>1,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
			break;
			case 'S':
				// mark a start point
				$maze_params['start']=['x'=>$curX,'y'=>$curY];
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'items' => array(),
					'intag' => array(
						'class'=>'mcell space',
						'data-isfree'=>1,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
			break;
			default:
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'items' => array(),
					'intag' => array(
						'class'=>'mcell space',
						'data-isfree'=>1,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
				$emptyCells[] = array('x'=>$curX,'y'=>$curY);
			
		}
	}
}



$playerItems = array(
	array(
		'title' => 'Серый',
		// 'class' => 'itemGrayPlayer',
		// 'icon' => 'img/player-car.svg',
		// 'color' => '#999999',
		'percent' => 0.01,
		'amount' => 0,
	),
	array(
		'title' => 'Красный',
		// 'class' => 'itemRedPlayer',
		// 'icon' => 'img/player-car.svg',
		// 'color' => '#FF0000',
		'percent' => 0.05,
		'amount' => 0,
	),
);


foreach ($playerItems as $key=>$item) {
	$playerItems[$key]['amount'] = round($item['percent']*count($emptyCells));
	for ( $i=0; $i<$playerItems[$key]['amount']; $i++) {
		// choose line to put item on
		$done = false;
		while (!$done) {
			// choose a random line 
			$cellId = rand(0,count($emptyCells)-1);
			
			$x = $emptyCells[$cellId]['x'];
			$y = $emptyCells[$cellId]['y'];
			if (count($mazeCells[$y][$x]['items'])==0) {
				// add item to cell
				$mazeCells[$y][$x]['items'][]=$key;
				$mazeCells[$y][$x]['intag']['class'].=' withItem item'.$key;
				$mazeCells[$y][$x]['intag']['data-hasitem']=$key;
				$done = true;
			}
			
		}
	}
}



$itemsList='';
$itemsClasses='';
foreach ($playerItems as $key=>$item) {
	
	$itemsList.='
		<div id="item'.$key.'" class="item"><span class="icon"></span>: <span class="amount">0</span></div>
	';

/*
	$itemsClasses.='
	.cell.withItem.'.$item['class'].' {
		background-color: '.$item['color'].';
		mask: url(\''.$item['icon'].'\');
	}
	.leftmenu .icon.'.$item['class'].' {
		display: inline-block;
		width: 1rem;
		height: 1rem;
		background-color: '.$item['color'].';
		mask: url(\''.$item['icon'].'\');
	}
	';
*/

}





// output html lab for params
$maze_html = array();
foreach ($mazeCells as $curY=>$maze_line) {
	$curLine = array();
	foreach ($maze_line as $curX=>$cell) {
			$intag = array();
			foreach ($cell['intag'] as $elName => $elValue) {
				$intag[]=''.$elName.'="'.$elValue.'"';
			}
			$curLine[]='<'.$cell['tag'].' '.implode(' ',$intag).'></'.$cell['tag'].'>';
	}
	
	$maze_html[]='
		<div class="line">
			'.implode("\n",$curLine).'
		</div>
	';
}


















?>
<!doctype html>
<html>
<head>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="topmenu">
	<a href="?rnd=1">Случайный</A>
	<a href="#paramBox" id="paramsLink">Параметры</A>
	<div id="paramBox" class="popup"></div>
</div>

<div class="leftmenu">
	<div class="itemslist">
	<?php
		echo $itemsList;
	?>
	</div>
</div>
<div class="content">

<div class="maze" style="width: <?php echo count($maze[0]); ?>rem">
<?php echo implode("\n",$maze_html); ?>
<div 
	class="user" 
	id="player" 
	data-x="<?php echo $maze_params['start']['x'];?>" 
	data-y="<?php echo $maze_params['start']['y'];?>"
	data-through_walls="0"
	data-destroy_walls="0"
></div>
<div id="finishMessage" class="popup d-none"><h2>Молодец!</h2></div>
</div>
</div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

<link media="all" rel="stylesheet" href="css/style-main.css" />
<link media="all" rel="stylesheet" href="css/maze-default.css" />
<script src="js/maze-app.js"></script>

</body>
</html>