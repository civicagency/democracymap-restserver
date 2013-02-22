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

<?php if($this->session->userdata('email')): ?>

<?php echo $this->session->userdata('email'); ?>

<?php endif; ?>

<form method="post" action="/account/login" class="span9">

  <!-- User Info -->
  <div id="infoForm" class="tab-pane active">
    <h3>Login</h3>
    <div id="newInfo" class="form-horizontal">
	
      <fieldset>              
        <div class="control-group">
          <label class="control-label" for="inputContactEmail">Email: </label>
          <div class="controls"><input type="text" id="inputContactEmail" name="email" value="<?php if (!empty($status['contact_point_email'])) echo $status['contact_point_email']; ?>"></div>
        </div>
  
      
        <div class="control-group info-warning">
          <label class="control-label" for="inputTeacherOpen">Password </label>
          <div class="controls"><input type="password" name="password" placeholder="*********" value=""></div>
        </div>

     <input type="submit" class="btn btn-success" value="Login" /> 
    
    </fieldset>
  </div>
</div>


</form>





<?php include 'footer_inc_view.php';?>