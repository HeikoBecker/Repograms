<?php 
	session_start();
	if (isset($_POST['repourl'])) $_SESSION['repourl'] = $_POST['repourl'];
?>

<html !DOCTYPE HTML>
<head>
	<?php include('header.php')?>
	<meta http-equiv="refresh" content="5">	
</head>

<body>
	<!-- Menu -->
	<?php include('menu.php'); ?>

	<!-- Content -->
	<div class="container" id="wrap">
		<a href="index.php"><img class="title" title="Repograms" src="img/title.png"></a>
		<br>
		<div class="progress progress-striped active">
			<div class="progress-bar"  role="progressbar" 
					aria-valuenow="<?php echo $_SESSION['progress']; ?>" 
					aria-valuemin="0" aria-valuemax="100" 
					style="width: <?php echo $_SESSION['progress']; ?>%">
     		</div>
		</div>
		<p class="center">
			<?php if (isset($_SESSION['loading_info']))
						echo $_SESSION['loading_info']; 
				  else echo 'Cloning repository...(This may take a while)'?>
			<br><br>
		</p>
		<div class="center">
			<form role="form" action="./php/abort.php" method="POST">
				<div class="btn-group btn-group-lg">
		  			<button class="btn btn-danger" type="Submit" >Abort</button>
				</div>
		    </form>
		</div>
		<div id="push"></div>
	</div>
	
	<!-- Footer -->	
	<?php include('footer.php')?>
			
	<!--include the action.php functions -->
	<script type="text/javascript">
		console.log("calling ajax");
		jQuery.ajax("php/action.php");
		console.log("ajax called");
	</script>
</body>
</html>
