<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>



	<div class="span9">
	
		<?php if($this->session->flashdata('error_message')):?>	
		<div class="alert alert-error">
			<?php echo $this->session->flashdata('error_message'); ?>
		</div>
		<?php endif; ?>

		<?php if($this->session->flashdata('success_message')):?>	
		<div class="alert alert-success">
			<?php echo $this->session->flashdata('success_message'); ?>
		</div>
		<?php endif; ?>

	</div>



<form action="<?php echo site_url('account/validate') ?>" method="post" id="login_form" class="span9">

  <!-- User Info -->
  <div id="infoForm" class="tab-pane active">
	
	<?php echo $this->user->get_id(); ?>	
	
    <h3>Login</h3>
    <div id="newInfo" class="form-horizontal">
	
      <fieldset>              
        <div class="control-group">
          <label class="control-label" for="inputContactEmail">Email: </label>
          <div class="controls"><input type="text" id="inputContactEmail" name="login" value="<?php if (!empty($status['contact_point_email'])) echo $status['contact_point_email']; ?>"></div>
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