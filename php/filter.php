<?php
require_once("./newAlgorithm.php");
session_start();
error_log('HELP');
//if (isset($_GET['filter1'])) {
	$mode = $_POST['filter1'];
	$mode2 = $_POST['filter1'];
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
	$length = 0;
	switch ($mode2) {
		case "0": $length = 0;break;
		case "1": $length = 1;break;
		case "2": $length = 2;break;
		default:
			break;
	}	
	error_log($fmode.' is set to render');
	$alg = new Algorithm();
	$ses = unserialize($_SESSION['repo']);
	$_SESSION['image'] = $alg->render($ses,$length,$color,$_SESSION['width'], $_SESSION['height']);
	error_log("Rendered");
	header('Location: ../image.php');
//}
	
?>
