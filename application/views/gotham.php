<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title> What's Your District</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Le styles -->
  <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
  <link href="/css/gotham.css" rel="stylesheet">

  <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->

  <!-- Le fav and touch icons 
  <link rel="shortcut icon" href="assets/ico/favicon.ico">
  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/mockup/assets/ico/apple-touch-icon-144-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/mockup/assets/ico/apple-touch-icon-114-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/mockup/assets/ico/apple-touch-icon-72-precomposed.png">
  <link rel="apple-touch-icon-precomposed" href="/mockup/assets/ico/apple-touch-icon-57-precomposed.png">
  -->

  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.2.2/bootstrap.min.js"></script>

  <script type="text/javascript" src="/js/jquery.tweet.js"></script>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

<?php  if(empty($jurisdictions['jurisdictions'])): ?>
  <script type="text/javascript" src="/js/set_location.js"></script>
<?php endif;?>

<!-- this is just because we're expecting this to be in an iframe -->
<base target="_parent" />

</head>
  <body>
    <div class="dmap container-fluid">
   
	<header class="page-header row-fluid">
	      <!--header     <h1 class="pull-left">What's Your District</h1>	 -->
	Please enter a full address including the five digit zip-code to find your districts and representatives.
    </header>





<div id="searchbox">
	<form action="/gotham/" method="get" target="_self">
		<input type="text" name="location" id="addressid" placeholder="250 Broadway, New York, NY 10008" />
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