
	<div class="footer-colophon">
		Please help this project get support from the Knight News Challenge by "applauding" its <a href="https://www.newschallenge.org/open/open-government/submission/democracymap/">entry page</a>
	</div>		
			

  </div>

  <script type="text/javascript" src="/js/load_tweets.js"></script>


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