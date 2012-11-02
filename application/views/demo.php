<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DemocracyMap API</title>

<style type="text/css">

body {
 background-color: #fff;
 margin: 40px;
 font-family: Lucida Grande, Verdana, Sans-serif;
 font-size: 14px;
 color: #4F5155;
}

a {
 color: #003399;
 background-color: transparent;
 font-weight: normal;
}

h1 {
 color: #444;
 background-color: transparent;
 border-bottom: 1px solid #D0D0D0;
 font-size: 16px;
 font-weight: bold;
 margin: 24px 0 2px 0;
 padding: 5px 0 6px 0;
}

code {
 font-family: Monaco, Verdana, Sans-serif;
 font-size: 12px;
 background-color: #f9f9f9;
 border: 1px solid #D0D0D0;
 color: #002166;
 display: block;
 margin: 14px 0 14px 0;
 padding: 12px 10px 12px 10px;
}

</style>

		  <link rel="stylesheet" type="text/css" href="/css/style.css" />

</head>
<body>

<h1>Your Government</h1>


<div class="row" id="searchbox">
	<form action="/demo/" method="get">
		<label for="addressid">Address or Location</label>

		<input type="text" name="location" id="addressid" />
		<input type="submit" value="Go!" id="address-submit" />
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
	}
	?>	
	
	
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
			</div>										
			<img class="elected-photo" src="<?php echo $elected['url_photo']?>" /> <!-- data->jurisdictions->elected_office->url_photo -->
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











<?php
if (isset($ganalytics_id)):
?>

	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', '<?php echo $ganalytics_id;?>']);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	</script>


	<?php
	endif;		
	?>

</body>
</html>