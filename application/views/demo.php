<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>


<div id="searchbox">
	<form action="/demo/" method="get">
		<input type="text" name="location" id="addressid" placeholder="Address or Location" />
	</form>
</div>

<?php

if (!empty($jurisdictions['jurisdictions'])) {
	
?>

<div class="jurisdictions">
	<ul class="jurisdiction-list">
	

<?php
foreach ($jurisdictions['jurisdictions'] as $jurisdiction) {
?>	


<li class="jurisdiction"> 
	
	
	<?php 
	
	
	$jurisdiction['name'] = $jurisdiction['type_name'] . ': ' . $jurisdiction['name'];
	
	if ($jurisdiction['type'] == 'legislative' && $jurisdiction['level'] == 'regional') { 
		$jurisdiction['name'] = $jurisdiction['level_name'] . ' ' . $jurisdiction['name'];
	}
	
	
	if (!empty($jurisdiction['url'])) { 
	?>
	<h2>
		<a href="<?php echo $jurisdiction['url']?>"> <!-- data->jurisdictions->elected_office->url OR data->jurisdictions->elected_office->url_contact -->
			<?php echo $jurisdiction['name']?> <!-- data->jurisdictions->elected_office->title -->
		</a>
	</h2>
		<a href="<?php echo $jurisdiction['url']?>"> <!-- data->jurisdictions->elected_office->url OR data->jurisdictions->elected_office->url_contact -->
			<?php echo $jurisdiction['url']?> <!-- data->jurisdictions->elected_office->title -->
		</a>	
	<?php
	} else {
	?>	
		
	<h2>		
	<?php echo $jurisdiction['name']?> 		
	</h2>		
		
	<?php
	}?>
	
	
	<?php if (!empty($jurisdiction['phone'])) : ?>
		<div class="phone"><?php echo $jurisdiction['phone']; ?></div>
	<?php endif; ?>
	
	
	

	
	<!-- Again, limit to data->jurisdictions->elected_office->type = executive -->

<?php
if (!empty($jurisdiction['elected_office'])) {	
?>

<ul class="elected-list">

	
<?php 
foreach ($jurisdiction['elected_office'] as $elected) {
?>	
	
		<li>
			<div class="text-box">													
				<h3>
					
					<?php 
					if (!empty($elected['url'])) { 
					?>
					<a href="<?php echo $elected['url']?>"> <!-- data->jurisdictions->elected_office->url OR data->jurisdictions->elected_office->url_contact -->
						<?php echo $elected['title']?> <!-- data->jurisdictions->elected_office->title -->
					</a>
					<?php
					} else {
					 echo $elected['title'];
					}
					?>
				</h3>													
				<h4>
						<?php echo $elected['name_full']?> <!-- data->jurisdictions->elected_office->name_full -->
				</h4> 
				
				
				<?php 
				if (!empty($elected['url'])) : 
				?>
				<div class="website">
					<a href="<?php echo $elected['url']?>">
					<?php echo $elected['url']?> </a>
				</div>
				<?php endif;?>				
				
				<?php if (!empty($elected['phone'])) : ?>
					<div class="phone"><?php echo $elected['phone']; ?></div>
				<?php endif; ?>


				<?php if (!empty($elected['email'])) : ?>
					<div class="email"><?php echo $elected['email']; ?></div>
				<?php endif; ?>	
				
		
				<?php if (!empty($elected['social_media'])) : 
			
						foreach ($elected['social_media'] as $account) {
							?>
							
							<div class="social-media" id="<?php echo $account['type']?>"><a href="<?php echo $account['url']?>"><?php echo $account['description']?></a></div>
							
							<?php
						}
				

					?>

				<?php endif; ?>				
			
			</div>										
			<img class="elected-photo" src="<?php echo $elected['url_photo']?>" /> <!-- data->jurisdictions->elected_office->url_photo -->
			
			
			<?php if (!empty($elected['social_media'])) : 
				
					foreach ($elected['social_media'] as $account) {
						if ($account['type'] == 'twitter') {
							$twitter_id = $account['username'];
							break;
						}
					}
					
					if(!empty($twitter_id)):
				?>
						<div id="<?php echo $twitter_id?>" class="twitter-feed"></div>

				
					<?php endif; ?>			
				
			<?php endif; ?>			
			
			
		</li>
	
<?php
}	
?>	

</ul>

<?php
}	
?>										
	
</li>

	
	
<?php	
}
?>
	</ul>
</div>

<?php
}
?>







<?php include 'footer_inc_view.php';?>