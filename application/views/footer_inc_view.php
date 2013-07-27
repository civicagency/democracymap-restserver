
	<div class="footer-colophon">
		Please help improve this project by providing feedback &amp; comments on the <a href="https://www.newschallenge.org/open/open-government/submission/democracymap/">News Challenge proposal</a>
	</div>		
			

  </div>



<?php
if (!empty($load_open311)):	
	$load_open311 = json_encode($load_open311);				
?>

<script id="open311" type="text/html">
  <li>
	    <strong>{{ service_name }}</strong>
    <p>{{ description }}</p>
  </li>
</script>

<script type="text/javascript">
  // when the dom's ready

	function set_open311(feed_id, url) {

      var open311_id, user_data, user, service_requests;

	  service_requests = '';

      // build a simple user object, in a real app this
      // would probably come from a server somewhere.
      // Otherwise hardcoding here is just silly.
	
	console.log('hello' + url);
	
	
	
	//	open311data = {
	//	    "service_request_id": "101000865574",
	//	    "token": "51c733cb0882cf637500053a",
	//	    "status": "open",
	//	    "service_name": "Other",
	//	    "service_code": "4e658b2821ecf3e0a900003e",
	//	    "description": "cars constantly blocking crosswalk, with police cruisers going by constantly",
	//	    "requested_datetime": "2013-06-23T13:43:39-04:00",
	//	    "updated_datetime": "2013-06-23T13:43:41-04:00",
	//	    "address": "310 Bowdoin St Boston, MA 02125",
	//	    "lat": 42.30796,
	//	    "long": -71.065308,
	//	    "media_url": "https://mayors24.cityofboston.gov/attachments/report/51c733cb0882cf637500053a/photo/Report.jpg"
	//	}	
	//	
  	//	service_requests = ich.open311(open311data);
    //	
  	//	// append it to the list, tada!
  	//	//Now go do something more useful with this.
  	//	open311_id = '#' + feed_id;
  	//	$(open311_id).append(service_requests);	
 
	
	
	$.getJSON(url, function(open311_data) {
		
      // Here's all the magic.

		if (open311_data.length < 10) {
			var max = open311_data.length;
		} else {
			var max = 10;
		}

		open311_id = '#' + feed_id;	  

		for (var i = 0; i < max; i++) {			
			service_requests = ich.open311(open311_data[i]);
			
			$(open311_id).append(service_requests);		
	      
		}

		
	});
	

  }


	var open311data = <?php echo $load_open311; ?>;
	
  $(document).ready(function() {
		for (var i = 0; i < open311data.length; i++) {		
			set_open311(open311data[i]['uid'], open311data[i]['url']);			
		}
  });

</script>


<?php
endif;
?>


  <script type="text/javascript" src="/js/load_tweets.js"></script>


<?php
if (!empty($mapdata)):	
	$mapdata = json_encode($mapdata);				
?>	

	<script src="http://cdn.leafletjs.com/leaflet-0.4.5/leaflet.js"type="text/javascript"></script>
    <script type="text/javascript" src="/js/set_map.js"></script>


	<script type="text/javascript">
		var mapdata = <?php echo $mapdata; ?>;
		
        $(document).ready(function() {
			for (var i = 0; i < mapdata.length; i++) {		
				set_map(mapdata[i]['map_id'], mapdata[i]['lat'], mapdata[i]['long'], mapdata[i]['zoom'], mapdata[i]['url']);			
			}
        });
		
	</script>
	
<?php endif; ?>
	

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