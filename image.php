<?php session_start(); 
require_once("php/language.php");
if (!isset($_SESSION['image']) ) header('location: index.php');?>

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
    	<div class="hero-unit">
    		<!-- Filtereinstellungen -->
 	   		<form role="form" action="" method="POST" class="form-inline" style="text-align:center;">
 	   			<div class="form-group">
    				<label for="filter1">Commit</label>
    				<select id="filter1" name="filter1" class="form-control">
  						<option><?php msg('Autor') ?></option>              <!-- 2 -->
  						<option><?php msg('Commmit message') ?></option>     <!-- 1 -->
 	 					<option selected><?php msg('First 3 letters') ?></option>    <!-- 0 -->
  						<option><?php msg('Time') ?></option>               <!-- 3 -->
	  					<option><?php msg('Date') ?></option>               <!-- 4 -->
					</select>
  					</div>		

  					<div class="form-group">
    				<label for="filter1"><?php msg('Commit') ?></label>
					<label for="filter2"><?php msg('Operation') ?></label>
    				<select id="filter2" name="filter2" class="form-control">
						<option selected><?php msg('All') ?></option>                <!-- 0 -->
  						<option><?php msg('Add') ?></option>                <!-- 1 -->
  						<option><?php msg('Delete') ?></option>             <!-- 2 -->
					</select>
  					</div>
    		</form>
    		
    		<!-- Repo-Visualization -->
    		<!-- Legend -->
    		<div class="color-legend" style="float:left; width: 10%;">
				<div class="legend-title"><?php print msg('Legend'); ?></div>
					<div class="legend-scale">
  						<ul class="legend-labels">
  							<?php 
								if (isset($_GET['legend'])) {
									foreach ($_GET['legend'] as $entry) {
										echo '<li><span style="background:rgb('.$entry['c'].');"></span>'.$entry['t'].'</li>';
									}
		               			}
		            		?>
  						</ul>
					</div>
			</div>
			<!-- Repo-Image -->
			<script type="text/javascript" src="js/jquery.overscroll.min.js"></script>
			<div class="custom" style="width:<?php echo $_SESSION['width']+1;?>; boder-style:solid; display:inline-block;">
				<ul style="display:inline-block; list-style-type:none !important;">
					<?php
						require_once('php/functions.php');
						renderImage();
					?>
				</ul>
			</div>
			<div class="clear"></div>
			<!-- Download image buttons -->
			<div style="float:right;">
				<div class="btn-group">
  					<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
  						<span class="glyphicon glyphicon-download"></span><?php msg('Download image') ?><span class="caret"></span>
  					</button>
  					<ul class="dropdown-menu" role="menu">
    					<li><a href="<?php echo 'download.php?file=php/visualization-'.session_id().'.svg'?>">as .svg</a></li>
   						<li><a href="<?php echo 'download.php?file=php/visualization-'.session_id().'.png&mode=png'?>">as .png</a></li>
   						<li><a href="<?php echo 'download.php?file=php/visualization-'.session_id().'.jpg&mode=jpg'?>">as .jpg</a></li>
  					</ul>
				</div>
			</div>
			<div id="push" class="clear"></div>
			<br><br>
		</div>
	</div>
	
	<?php include('footer.php')?>

</body>
</html>
<?php
require_once('php/functions.php');
	initSession(false);
?>
