<?php

$playerItems = array(
	1=>array(
		'title' => 'Сквозь стены',
		// 'class' => 'itemGrayPlayer',
		// 'icon' => 'img/player-car.svg',
		// 'color' => '#999999',
		'percent' => 0.01,
		'minPercent' => 0,
		'maxPercent' => 15,
		'amount' => 0,
	),
	array(
		'title' => 'Разрушить стену',
		// 'class' => 'itemRedPlayer',
		// 'icon' => 'img/player-car.svg',
		// 'color' => '#FF0000',
		'percent' => 0.05,
		'minPercent' => 0,
		'maxPercent' => 15,
		'amount' => 0,
	),
);


$styleThemes = array(
	'default' => array(
		'title' => 'По умолчанию',
		'slug' => 'default',
		'cssFile' => 'maze-default.css',
	),
	'car' => array(
		'title' => 'Машина',
		'slug' => 'car',
		'cssFile' => 'maze-car.css',
	),
);

?>