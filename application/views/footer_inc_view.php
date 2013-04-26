
	<div class="footer-colophon">
		Please help improve this project by providing feedback &amp; comments on the <a href="https://www.newschallenge.org/open/open-government/submission/democracymap/">News Challenge proposal</a>
	</div>		
			

  </div>

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