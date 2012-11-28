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
 font-size: 1.5em;
 font-weight: bold;
 margin: 24px 0 2px 0;
 padding: 5px 0 6px 0;
}

h2 {
font-size : 1.25em;
margin-top : 2em;
}

h3 {
font-size : 1em;
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
</head>
<body>

<h1>Welcome to the experimental DemocracyMap API</h1>

<p>Currently this demo is limited to the United States</p>

<h2>Formats</h2>

<p>
	The default format returned is <strong>json</strong>, but <strong>xml</strong> and <strong>csv</strong> are also supported. You can specify the format by 
	appending the format extension after the resource, eg getting xml would be "/context.xml" 
	or you can specify format as another query parameter, eg "/context?format=xml"
</p>

<h2>Resources</h2>

<h3>Context</h3>

<pre>URL: http://api.democracymap.org/context</pre>

<p>There are just a few parameters</p>

<ul>
	<li><strong>location</strong> <em>(options: location string or lat, long).</em> This is any string that can be geocoded</li>
	<li><strong>format</strong> <em>(options: json, xml).</em> This specifies the format of the response.</li>
	<li><del><strong>geojson</strong> <em>(options: true, false).</em> This specifies whether you want a geojson representation of the boundary returned</del></li>
</ul>

<h4>Example Call</h4>
<p>
	<a href="http://api.democracymap.org/context?location=chicago">http://api.democracymap.org/context?location=chicago</a>
</p>	

<h1>Source and More information</h1>
<p>You can find a description of <a href="http://wiki.open311.org/GeoWeb_DNS">GeoWeb DNS on the Open311 wiki</a>. The source code for this is <a href="https://github.com/GSA-OCSIT/democracymap-restserver">available on github</a>. This is experimental, don't expect it to be accurate or to stick around</p>

<h2>Credits</h2>
<p>
This is built on the same data as the main <a href="http://beta.democracymap.org">DemocracyMap demo</a>, so I'm repeating those credits here. 
City and County data from U.S. Census (including the <a href="http://www.census.gov/govs/cog/">2007 Census of Governments</a> and <a href="http://tigerweb.geo.census.gov/tigerwebmain/TIGERweb_restmapservice.html">TigerWeb</a>) with updated URLs 
provided by the <a href="http://api.sba.gov/doc/geodata.html">SBA U.S. City &amp; County Web Data API</a>. City mayor and contact data from the 
<a href="http://www.usmayors.org/meetmayors/mayorsatglance.asp">US Conference of Mayors</a>.  
State data from the <a href="http://www.nga.org/cms/governors/bios">National Governors Association</a> and <a href="http://answers.usa.gov/system/selfservice.controller?CONFIGURATION=1000&PARTITION_ID=1&CMD=VIEW_ARTICLE&ARTICLE_ID=9902&USERTYPE=1&LANGUAGE=en&COUNTRY=US">USA.gov</a>. 
US Congressional data from the <a href="http://services.sunlightlabs.com/docs/Sunlight_Congress_API/">Sunlight Congress API</a>. 
Congressional district boundaries from <a href="http://www.govtrack.us/congress/findyourreps.xpd">GovTrack.us</a>. 
State legislative data from the <a href="http://openstates.org/api/">OpenStates API</a>. 
All geocoding courtesy of <a href="http://developer.yahoo.com/geo/placefinder/">Yahoo! PlaceFinder</a>. 
Tweet stream uses the <a href="http://tweet.seaofclouds.com/">Tweet! jQuery plugin from seaofclouds</a>. 
</p>

<!--
Either not in use or deprecated:

NYC data <em>scraped</em> from <a href="http://nyc.gov">nyc.gov</a>. NYC boundary data from <a href="http://www.nyc.gov/data">NYC OpenData</a>. 
City-level geospatial data served by <a href="http://geoserver.org">GeoServer</a>. 

 -->

<p> 
	Please check out the  <a href="http://lists.open311.org/groups/discuss/">Open311 mailing list</a> and the <a href="http://forums.e-democracy.org/groups/democracymap">DemocracyMap mailing list</a>.
</p>


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