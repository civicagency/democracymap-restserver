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

<script type="text/javascript" src="/contact-files/contact-form.js"></script>


<!-- Activate responsiveness in the "child" page -->
<script src="https://raw.github.com/npr/responsiveiframe/master/dist/jquery.responsiveiframe.js"></script>
<script>
var ri = responsiveIframe();
ri.allowResponsiveEmbedding();

/** since this is an iframe and we have a base target of _parent for all the links, we need to override that for the forms **/
$(document).ready(function(){
	$('form').attr("target", "_self");
});

</script>


<?php  if(empty($jurisdictions)): ?>
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
	<form action="/gotham/" method="get">
		<input type="text" name="location" id="addressid" placeholder="250 Broadway, New York, NY 10007" />
	</form>
</div>

<?php

if (!empty($jurisdictions)) {
	
	// Provide geocoder accuracy feedback
	if (!empty($geocoded_address)) :
		
		if($geocoded_precision !== 'POINT' &&
		   $geocoded_precision !== 'ADDRESS' &&  
		   $geocoded_precision !== 'INTERSECTION' && 
		   $geocoded_precision !== 'STREET') {
			
			$geocoded_precision = 'warn';
		} else {
			$geocoded_precision = 'good';
		}
	?>	
		
		<div class="geocoder-accuracy <?php echo $geocoded_precision;?>">
			You entered "<?php echo $input_location;?>" which was interpreted as "<?php echo $geocoded_address;?>" 
			
			<?php
			if($geocoded_precision == 'warn'):
			?>	

				<span class="geocoder-feedback">
					The location you entered may not be complete enough to provide accurate results. Be sure to included a full street address with postcode.
				</span>

			<?php	
			endif;?>			
			
			
		</div>
		
	<?php	
	endif;
	?>

<div class="disclaimer">
	This tool is a beta release and is still in development. If you find any errors or incorrect information, <span id="feedback-button"><a data-toggle="modal" href="#feedbackBox">please let us know</a></span> 
</div>


<div class="jurisdictions">
	<ul class="jurisdiction-list">
	

<?php
foreach ($jurisdictions as $region_name => $region) {
?>	


<?php
$region_count = 1;
foreach ($region as $jurisdiction) {
?>

<li class="jurisdiction"> 
	
	
	<?php 
	
	
	$jurisdiction['name'] = $jurisdiction['type_name'] . ': ' . $jurisdiction['name'];
	
	if ($jurisdiction['type'] == 'legislative' && $jurisdiction['level'] == 'regional') { 
		$jurisdiction['name'] = $jurisdiction['level_name'] . ' ' . $jurisdiction['name'];
	}
	

	$region_class = ($region_count == 1) ? "region-heading $region_name" : $region_name;

	?>
	<?php echo "<h2 class=\"$region_class\">"; ?>
		
		<?php
			if (!empty($jurisdiction['url'])) { 			
		?>
				<a href="<?php echo $jurisdiction['url']?>"> <!-- data->jurisdictions->elected_office->url OR data->jurisdictions->elected_office->url_contact -->
					<?php echo $jurisdiction['name']?> <!-- data->jurisdictions->elected_office->title -->
				</a>
			
			<?php
			} else {
			 echo $jurisdiction['name'];
			}?>			
	</h2>
	
	
	<?php if (!empty($jurisdiction['phone'])) : ?>
		<div class="phone"><?php echo $jurisdiction['phone']; ?></div>
	<?php endif; ?>
	
	
	

	
	<!-- Again, limit to data->jurisdictions->elected_office->type = executive -->

<?php
if (!empty($jurisdiction['elected_office'])) {	
?>

<ul class="elected-list">

	
<?php 


$rep_count = count($jurisdiction['elected_office']);

foreach ($jurisdiction['elected_office'] as $elected) {
?>	
	
		<li>
			<div class="text-box">													
				<h3>
					
					<?php 
					
					$single_rep = ($rep_count == 1 || $elected['title'] == 'Senator') ? true : false;
					
					if($single_rep) $elected['title'] = $elected['name_full'];
					
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
				
				<?php
				if(!$single_rep) {
				?>												
					<h4>
							<?php echo $elected['name_full']?> <!-- data->jurisdictions->elected_office->name_full -->
					</h4> 
				<?php } ?>
				
				
				<?php 
				if (!empty($elected['url'])) : 
				?>

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
						<h5 class="twitter-heading">Recent Tweets</h5>
						<div id="<?php echo $twitter_id?>" class="twitter-feed"></div>

				
					<?php 
						unset($twitter_id);
						endif; 
					?>		
							
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
$region_count++;
}
?>
	
	
<?php
}
?>
	</ul>
</div>

<?php
}
?>



<div class="modal hide" id="feedbackBox">
    <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal">×</button>
	    <h3>Contact Us</h3>
	    </div>
	    <div class="modal-body">

			<?php
			$contact_form = 1; // set desired form number.
			$contact_form_path = './contact-files/'; // set path to /contact-files/ with slash on end.
			require $contact_form_path . 'contact-form-run.php';
			?>	

	    </div>
	    <div class="modal-footer">
	    <a href="#" class="btn" data-dismiss="modal">Close</a>
    </div>
</div>





<?php include 'gotham_footer_inc_view.php';?>