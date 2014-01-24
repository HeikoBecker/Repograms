<?php session_start(); 
require_once("php/language.php");
if (!isset($_SESSION['image']) ) header('location: index.php');?>

<html !DOCTYPE HTML>
<head>
	<?php include('header.php') ?>
</head>

<body>
	<noscript>
    	<p style="text-align:center; color: white; background-color: red;">
    		Looks like javascript is disabled in your browser. Please activate it to be able to use all the functions of this page.
    	</p>
	</noscript>
	
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
  						<option value="2"><?php print msg('Autor') ?></option>         
  						<option value="1"><?php print msg('Commmit message') ?></option>  
 	 					<option value="0" selected><?php print msg('First 3 letters') ?></option>  
  						<option value="3"><?php print msg('Time') ?></option>        
	  					<option value="4"><?php print msg('Date') ?></option>               
					</select>

  				</div>		
  				<div class="form-group">
					<label for="filter2">Operation</label>
    				<select id="filter2" name="filter2" class="form-control">
						<option value="0"><?php print msg('All') ?></option>     
  						<option value="1"><?php print msg('Add') ?></option>              
  						<option value="2"><?php print msg('Delete') ?></option>           
					</select>
  				</div>
  				<button class="btn btn-default" type="submit" title="Apply filters">
       				<span class="glyphicon glyphicon-indent-left"></span><?php print msg('Go'); ?>
				</button>
    		</form>
    		
    		<!-- Repo-Visualization -->
    		<!-- Legend -->
    		<div class="color-legend" style="float:left; width: 10%;">
				<div class="legend-title"><?php print msg('Legend'); ?></div>
					<?php
						require_once('php/functions.php');
						renderLegend();
					?>
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
  						<span class="glyphicon glyphicon-download"></span><?php print msg('Download image') ?><span class="caret"></span>
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
	
	<?php include('footer.php') ?>

</body>
</html>
