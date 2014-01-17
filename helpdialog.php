<?php

session_start();
require_once("php/language.php");

?>

<div class="modal fade" id="help" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 	<div class="modal-dialog">
    	<div class="modal-content">
      		<div class="modal-header">
       			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
       			<h4 class="modal-title"><?php print msg('Repograms - Quick Help');?></h4>
     		</div>
      		<div class="modal-body">
				<?php print msg('This website renders chromograms of your git repository.');?><br>
				<?php print msg('To start just enter your repository URL and click on visualize.');?><br>
				<?php print msg('To see some examples, just choose one from below and then click');?>
				<button class="btn btn-default" title="<?php print msg('Visualize the provided repository');?>">
       				<span class="glyphicon glyphicon-indent-left"></span><?php print msg('Visualize!');?>
				</button>
				<br><br>
				<div class="alert alert-info inforepo">
					<?php print msg('Large repository may need some time to be downloaded and processed.');?><br>
					<?php print msg('So please be patient and get some coffee while waiting ;)');?>
				</div>
      		</div>
      		<div class="modal-footer">
        		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print msg('Close');?></button>
      		</div>
    	</div>
  	</div>
</div>
