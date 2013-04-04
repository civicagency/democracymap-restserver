<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>


<?php if(!empty($messages)): ?>

	<div class="span9">
	
		<?php if(isset($messages['error'])):?>	
		<div class="alert alert-error">
			<?php echo $messages['error']; ?>
		</div>
		<?php endif; ?>

		<?php if(isset($messages['success'])):?>	
		<div class="alert alert-success">
			<?php echo $messages['success']; ?>
		</div>
		<?php endif; ?>

	</div>

<?php endif; ?>



<form method="post" action="/editor/official" class="span9">

  <!-- User Info -->
  <div id="infoForm" class="tab-pane active">
    <h3>Official Information</h3>
    <div id="newInfo" class="form-horizontal">
	
      <fieldset>      
	
	<?php
	
	foreach ($official as $fieldname => $value) {
		
		$fieldtitle = ucwords(str_replace('_', ' ', $fieldname));
		
	?>
	
    <div class="control-group">
      <label class="control-label" for="input-<?php echo $fieldname?>"><?php echo $fieldtitle?></label>
      <div class="controls"><input type="text" id="input<?php echo $fieldname?>" name="<?php echo $fieldname?>" value="<?php echo $value ?>"></div>
    </div>		
		
	<?php
	}	
	?>

     <input type="submit" class="btn btn-success" value="Save" /> 
    
    </fieldset>
  </div>
</div>


</form>





<?php include 'footer_inc_view.php';?>