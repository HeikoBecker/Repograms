<?php
error_reporting(-1);
require_once("./algorithm.php");
require_once("./functions.php");
session_start();
error_log('HELP');
//if (isset($_GET['filter1'])) {
	$mode = $_POST['filter1'];
	$color = 3;
	switch ($mode) {
		case "0": $color = 0;break;
		case "1": $color = 1;break;
		case "2": $color = 2;break;
		case "3": $color = 3;break;
		case "4": $color = 4;break;
		default:
			break;
	}	
	error_log($mode.' is set to render');
	$alg = new Algorithm();
	$ses = unserialize($_SESSION['repo']);
	$_SESSION['image'] = $alg->render($ses,$color,$_SESSION['width'], $_SESSION['height']);
	error_log("Rendered");
	header('Location: ../image.php');
//}
	
?>
