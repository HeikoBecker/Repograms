<?php 
	session_start();
	if (isset($_SESSION['finish']) && $_SESSION['finish']) {
		header('Location: image.php');
	}
?>

<html !DOCTYPE HTML>
<head>
	<?php include('header.php')?>
</head>

<body>
	<!-- Menu -->
	<?php include('menu.php'); ?>
	
	<!-- Content -->
	<div class="container" id="wrap">
		<a href="index.php"><img class="title" title="Repograms" src="img/title.png"></a>
		<br>
    	<img class="center" src="img/progress.gif">
    	<p class="center"><?php if (isset($_SESSION['loading_info'])) echo $_SESSION['loading_info']; else echo 'Loading...'?></p>
		<div id="push"></div>
	</div>
	
	<!-- Footer -->	
	<?php include('footer.php')?>
</body>
</html>

<?php 
	
?>