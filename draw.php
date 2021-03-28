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

$sourceIsPosted = false;
if (isset($_POST['maze'])) {
	$source = $_POST['maze'];
	$sourceIsPosted = true;
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
if ( (!empty($_REQUEST['theme'])) and ($styleThemes[$_REQUEST['theme']]) ) {
	$curTheme = $styleThemes[$_REQUEST['theme']];
} else {
	$curTheme = $styleThemes['default'];
}
$themesOptions = array();
foreach ($styleThemes as $theme) {
	$themesOptions[] = '<option value="'.$theme['slug'].'" '.(($theme['slug'] == $curTheme['slug'])?'selected':'').'>'.$theme['title'].'</option>';
}


// load theme if set
$settingsOptions = array();
foreach ($playerItems as $itemId=>$item) {
	if ( 
			(isset($_GET['i'][$itemId])) and 
			($_GET['i'][$itemId]>=$item['minPercent']) and
			($_GET['i'][$itemId]<=$item['maxPercent'])
		) {
			
			$playerItems[$itemId]['percent'] = $item['percent'] = $_GET['i'][$itemId]/100;
	}
	
	$settingsOptions[] = '
			<div class="row">
				<div class="col-8">
					<label for="ySize" class="form-label">'.$item['title'].'</label>
				</div>
				<div class="col-4">
					<input class="form-input w-100" name="i['.$itemId.']" value="'.($item['percent']*100).'">
				</div>
			</div>
	';
}





// convert source to array
$emptyCells = array();
$maze=array();
$sourceLines = explode("\n",trim($source));
foreach ($sourceLines as $y=>$line) {
	$line = trim($line);
	$row = str_split($line);
	
	foreach ($row as $x=>$cell) {
		if ($cell==' ') {
			$emptyCells[] = array('x'=>$x,'y'=>$y);
		}
		
	}
	
	$maze[] = $row;
}

// print_r($maze);
// die();

if (!$sourceIsPosted) {
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
				if ($maze[$y][$x]==' ') {
					// add item to cell
					$maze[$y][$x]=$key;
					$done = true;
				}
				
			}
		}
	}
}





// print_r($maze);
// die();

// parse array to maze params
$maze_params = array(
	'height' => null,
	'width' => null,
	'start' => array('x'=>false,'y'=>false),
	'fnish' => array('x'=>false,'y'=>false),
);

$emptyCells = array();
$mazeCells = array();
$maze_params['height'] = (count($maze)+1) / 2 - 1;
$maze_params['width'] = (count($maze[0])+1) / 2 - 1;
foreach ($maze as $curY=>$maze_line) {
	foreach ($maze_line as $curX=>$maze_cell) {
		switch ($maze_cell) {
			case 'X':
				// build a wall
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'asNeighbor' => 'wall',
					'items' => array(),
					'intag' => array(
						'class'=>array('mcell','wall'),
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
					'asNeighbor' => 'border',
					'items' => array(),
					'intag' => array(
						'class'=>array('mcell','wall','mborder'),
						'data-isfree'=>0,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
			break;
			case '1':
			case '2':
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'asNeighbor' => 'space',
					'items' => array($maze_cell),
					'intag' => array(
						'class'=>array('mcell','space','withItem','item'.$maze_cell),
						'data-isfree'=>1,
						'data-x'=>$curX,
						'data-y'=>$curY,
						'data-hasitem'=>$maze_cell,
					),
				);
			break;
			case 'F':
				$maze_params['finish']=array('x'=>$curX,'y'=>$curY);
				$mazeCells[$curY][$curX]=array(
					'type' => $maze_cell,
					'tag' => 'div',
					'asNeighbor' => 'space',
					'items' => array(),
					'intag' => array(
						'class'=>array('mcell','space','finish'),
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
					'asNeighbor' => 'space',
					'items' => array(),
					'intag' => array(
						'class'=>array('mcell','space'),
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
					'asNeighbor' => 'space',
					'items' => array(),
					'intag' => array(
						'class'=>array('mcell','space'),
						'data-isfree'=>1,
						'data-x'=>$curX,
						'data-y'=>$curY,
					),
				);
				$emptyCells[] = array('x'=>$curX,'y'=>$curY);
			
		}
	}
}


/*
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
				$mazeCells[$y][$x]['intag']['class'][]='withItem';
				$mazeCells[$y][$x]['intag']['class'][]='item'.$key;
				$mazeCells[$y][$x]['intag']['data-hasitem']=$key;
				$done = true;
			}
			
		}
	}
}
*/

// calc walls classes
$typeToClass = array(
	'X' => 'wall',
	'#' => 'border',
	' ' => 'space',
);

function getNeighborClass ($mazeCells,$nX,$nY,$prefix) {
	if (isset($mazeCells[$nY][$nX])) {
		return ''.$prefix.'-'.$mazeCells[$nY][$nX]['asNeighbor'].'';
	} else {
		return '';
	}
}

for ($y=0;$y<count($mazeCells);$y++) {
	for ($x=0;$x<count($mazeCells[$y]);$x++) {
		$curCell = $mazeCells[$y][$x];

		$mazeCells[$y][$x]['intag']['class'][]=getNeighborClass($mazeCells,$x,$y-1,'top');
		$mazeCells[$y][$x]['intag']['class'][]=getNeighborClass($mazeCells,$x,$y+1,'bottom');
		$mazeCells[$y][$x]['intag']['class'][]=getNeighborClass($mazeCells,$x-1,$y,'left');
		$mazeCells[$y][$x]['intag']['class'][]=getNeighborClass($mazeCells,$x+1,$y,'right');
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
				if (is_array($elValue)) {
					// $elValue = array_filter($elValue);
					$intag[]=''.$elName.'="'.implode(' ',$elValue).'"';
				} else {
					$intag[]=''.$elName.'="'.$elValue.'"';
				}
			}
			$curLine[]='<'.$cell['tag'].' '.implode(' ',$intag).'><div></div></'.$cell['tag'].'>';
	}
	
	$maze_html[]='
		<div class="line">
			'.implode("\n",$curLine).'
		</div>
	';
}

// set text variant of a maze
$maze_txt = '';
foreach ($maze as $line) {
	// $maze_txt.=str_replace(' ','&nbsp;',implode('',$line))."\n";
	$maze_txt.=implode('',$line)."\n";
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
	<a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mazeBox">Лабиринт</A>
	<a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paramBox">Параметры</A>
	<a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#helpBox">Помощь</A>
</div>


<div class="modal fade hidefocus" id="mazeBox" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Лабиринт</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
		<div class="row">
			<div class="col-4">
				<a href="#" id="copyMaze" class="btn btn-primary btn-sm">Скопировать</a>
				<a href="#" id="downloadMaze" class="btn btn-primary btn-sm">Скачать</a>
			</div>
			<div class="col-8">
				<form action="?" method="POST" id="mazeInputForm" class="h-100">
					<input type="hidden" name="theme" value="<?php echo @$_REQUEST['theme']; ?>">
					<textarea id="mazeText" name="maze" class="w-100 h-100 d-block font-monospace lh-sm" style="overflow:auto; font-size: 0.5rem"><?php echo $maze_txt; ?></textarea>
				</form>
			</div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <button type="submit" form="mazeInputForm" class="btn btn-primary">Открыть</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade hidefocus" id="paramBox" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
			
			<DIV class="">
				<label class="form-label">Предметы</label>
			<?php
			
				echo implode("\n",$settingsOptions);
			
			?>
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

<div class="modal fade hidefocus" id="helpBox" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
	  <hr>
	  <div class="row m-2">
			<div class="col-6 text-center">
        <A HREF="https://github.com/vmdevlpr/maze" target=_blank class="btn btn-secondary">GitHub</A>
			</div>
			<div class="col-6 text-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
			</div>
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
<div class="maze" style="width: <?php echo count($maze[0]); ?>rem; height: <?php echo count($maze); ?>rem">
<div class="loader">
	<div class="spinner-border" role="status">
	  <span class="visually-hidden">Loading...</span>
	</div>
</div>
<?php echo implode("\n",$maze_html); ?>
<div 
	class="user" 
	id="player" 
	data-x="<?php echo $maze_params['start']['x'];?>" 
	data-y="<?php echo $maze_params['start']['y'];?>"
	data-through_walls="0"
	data-destroy_walls="0"
></div>
<div id="finishMessage" style="display:none;"><h2>Молодец!</h2></div>
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