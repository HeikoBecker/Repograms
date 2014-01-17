<?php session_start(); 
require_once("php/language.php");
if (!isset($_SESSION['image']) ) header('location: index.php');?>

<html !DOCTYPE HTML>
<head>
	<?php include('header.php')?>
	<meta http-equiv="Content-Type" content="img/svg+xml; charset=UTF-8">
</head>

<body>
	<!-- Menu -->	
	<?php include('menu.php'); ?>
	
	<!-- Content -->
	<div class="container" id="wrap">
		<a href="index.php"><img class="title" title="Repograms" src="img/title.png"></a>
		<br>
    	<div class="hero-unit">
    		<script type="text/javascript" src="js/jquery.overscroll.min.js"></script>
    		<div class="color-legend">
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
			<div class="panel panel-default" style="overflow: hidden; height: 555px; width: 768px; margin: auto auto 0;">
  				<div class="panel-heading"><?php if (isset($_SESSION['title'])) echo $_SESSION['title'];?></div>
				<div id="overscroll"> 
					<ul><li>
						<svg id="repograms" height="512" width="768" 
     						xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  							<image x="0" y="0" height="512" width="768"  xlink:href="php/visualization-<?php echo session_id();?>.svg"/>
						</svg>
					</li></ul>
				</div>
			</div>
			<br><br>
    		<div id="push"></div>
		</div>
	</div>
	
	<?php include('footer.php')?>

	<script>
    	$(function () {
        	$("[rel='tooltip']").tooltip();
    	});
	
		$(function(o){
			o = $("#overscroll").overscroll({
				cancelOn: '.no-drag',
				scrollLeft: 200,
				scrollTop: 100
			}).on('overscroll:dragstart overscroll:dragend overscroll:driftstart overscroll:driftend', function(event){
				console.log(event.type);
			});
			$(nr).click(function(){
				if(!o.data("dragging")) {
					document.getElementById(nr).innerHTML = '<a href="" data-title="'
						+<?php if (isset($_GET['infos'])) { echo $_GET['infos'][nr];}?>
						+'" data-placement="right" rel="tooltip"></a>';
				} else {
					return false;
				}
			});
		});
	</script>
</body>
</html>
<?php
require_once('php/functions.php');
	initSession(true);
?>
