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

This is the Account Dashboard view

<?php if($this->session->userdata('email')): ?>

<?php echo $this->session->userdata('email'); ?>

<?php endif; ?>







<?php include 'footer_inc_view.php';?>