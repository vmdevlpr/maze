<?php

error_reporting(E_ALL);
ini_set('display_errors','On');


include_once ('include/settings.php');



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


if ( (empty($source)) and (empty($_GET['rnd'])) ) {
	$_GET['rnd'] = 1;
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



// load theme if set
if ( (!empty($_GET['theme'])) and ($styleThemes[$_GET['theme']]) ) {
	$curTheme = $styleThemes[$_GET['theme']];
} else {
	$curTheme = $styleThemes['default'];
}
$themesOptions = array();
foreach ($styleThemes as $theme) {
	$themesOptions[] = '<option value="'.$theme['slug'].'" '.(($theme['slug'] == $curTheme['slug'])?'selected':'').'>'.$theme['title'].'</option>';
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
	'height' => null,
	'width' => null,
	'start' => array('x'=>false,'y'=>false),
	'fnish' => array('x'=>false,'y'=>false),
);

$emptyCells = array();
$mazeCells = array();
$maze_params['width'] = (count($maze)+1) / 2 - 1;
$maze_params['height'] = (count($maze[0])+1) / 2 - 1;
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
				$maze_params['finish']=array('x'=>$curX,'y'=>$curY);
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
				$maze_params['start']=array('x'=>$curX,'y'=>$curY);
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
		<div id="item'.$key.'" class="item" data-itemid="'.$key.'"><span class="icon"></span>: <span class="amount">0</span></div>
	';

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
	<meta charset="utf-8">
</head>
<body>
<div class="topmenu">
	<!-- <a href="?rnd=1" class="btn btn-outline-primary btn-sm">Случайный</A> -->
	<a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paramBox">Параметры</A>
	<a href="#helpBox" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#helpBox">Помощь</A>
</div>
<div class="modal fade" id="paramBox" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Параетры лабиринта</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
		<form action="" method="GET" id="mazeParamsForm">
			<input type="hidden" name="rnd" value="1">
			<DIV class="">
				<label for="xSize" class="form-label">Ширина</label>
				<div class="row"> 
					<div class="col-2">
						<input type="text" name="x" id="xValue" class="w-100" value="<?php echo $maze_params['width']; ?>">
					</div>
					<div class="col-10">
						<input type="range" class="form-range" min="5" max="30" step="1" id="xSize" value="<?php echo $maze_params['width']; ?>" data-range-value="xValue">
					</div>
				</div>
			</DIV>
			<DIV class="">
				<label for="ySize" class="form-label">Высота</label>
				<div class="row"> 
					<div class="col-2">
						<input type="text" name="y" id="yValue" class="w-100" value="<?php echo $maze_params['height']; ?>">
					</div>
					<div class="col-10">
						<input type="range" class="form-range" min="5" max="20" step="1" id="ySize"  value="<?php echo $maze_params['height']; ?>" data-range-value="yValue">
					</div>
				</div>
			</DIV>
			<DIV class="">
				<label for="ySize" class="form-label">Оформление</label>
				<select name="theme" class="form-select">
					<?php
					
					echo implode("\n",$themesOptions);
					
					?>
				</select>
			</DIV>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <button type="submit" form="mazeParamsForm" class="btn btn-primary">Сгенерировать</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="helpBox" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Лабиринт</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
		<P>Для перемещения по лабиринту используйте стрелки на клавиатуре.</P>
		<P>Собирая предметы в лабиринте вы можете использовать их для того, чтобы разрушать стены или проходить сквозь них.</P>
		<P>Для использования собранного предмета, встаньте рядом со стеной, которую хотите разрушить или преодолеть, активируйте предмет в левом меню, двиньтесь в нужную сторону.</P>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
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
<link media="all" rel="stylesheet" href="css/<?php echo $curTheme['cssFile']; ?>" />
<script src="js/maze-app.js"></script>
<script src="js/interface.js"></script>

</body>
</html>