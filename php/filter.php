<?php

if (isset($_GET['filter1'])) {
	$mode = $_GET['filter1'];
	renderImage(0, $mode);
} else if (isset($_GET['filter2'])) {
	$secmode = $_GET['filter2'];
	renderImage(1, $mode);
}	

function renderImage($fmode = null, $smode = null) {
	$alg = new Algorithm();
	$_SESSION['image'] = $alg->render($ses,$fmode,$smode,$_SESSION['width'], $_SESSION['height']);
	header('location: image.php');
}
	
?>