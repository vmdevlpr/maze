<?php

if (!empty($_POST['maze'])) {
	$source = $_POST['maze'];
	
	$filename = 'maze-'.$_POST['theme'].'-'.md5($source).'.txt';

	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . $filename); 
	echo $source;

} else {
	
	header("HTTP/1.0 404 Not Found");

}



?>