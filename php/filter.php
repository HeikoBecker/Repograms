<?php
require_once("./newAlgorithm.php");

if (isset($_GET['filter1'])) {
	$mode = $_GET['filter1'];
	switch ($mode) {
		case "0": reRender(0, 0);break;
		case "1": reRender(0, 1);break;
		case "2": reRender(0, 2);break;
		case "3": reRender(0, 3);break;
		case "4": reRender(0, 4);break;
	}
}	

function reRender($fmode = null, $smode = null) {
	error_log($fmode.' is set to render');
	$alg = new Algorithm();
	$ses = unserialize($_SESSION['repo']);
	$_SESSION['image'] = $alg->render($ses,$fmode,$smode,$_SESSION['width'], $_SESSION['height']);
	error_log("Rendered");
	header('Location: ../image.php');
}
	
?>
