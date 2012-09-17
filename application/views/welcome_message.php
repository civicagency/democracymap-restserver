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
</head>
<body>

<h1>Welcome to the experimental DemocracyMap API</h1>

<p>Currently this demo is limited to the United States</p>


<h1>GeoWeb DNS Endpoints</h1>

<pre>URL: http://api.democracymap.org/geowebdns/endpoints</pre>

<p>There are just a few parameters</p>

<ul>
	<li><strong>location</strong> <em>(options: location string or lat, long).</em> This is any string that can be geocoded</li>
	<li><strong>format</strong> <em>(options: json, xml).</em> This specifies the format of the response.</li>
	<li><strong>geojson</strong> <em>(options: true, false).</em> This specifies whether you want a geojson representation of the boundary returned</li>
</ul>

<h3>Example Call</h3>
<p>
	<a href="http://api.democracymap.org/geowebdns/endpoints?location=chicago&format=json&geojson=true">http://api.democracymap.org/geowebdns/endpoints?location=chicago&amp;format=json&amp;geojson=true</a>
</p>	

<h1>Source and More information</h1>
<p>You can find a description of <a href="http://wiki.open311.org/GeoWeb_DNS">GeoWeb DNS on the Open311 wiki</a>. The source code for this is <a href="https://github.com/philipashlock/democracymap-restserver">available on github</a>. This is experimental, don't expect it to stick around</p>

<h1>Credits</h1>
<p>
This is built on the same data as the main DemocracyMap site, so I repeating that here. City data from U.S. Census including the <a href="http://www.census.gov/govs/cog/">2007 Census of Governments</a>. 
US Congressional data from the <a href="http://services.sunlightlabs.com/docs/Sunlight_Congress_API/">Sunlight Congress API</a>. 
Congressional district boundaries from <a href="http://www.govtrack.us/congress/findyourreps.xpd">GovTrack.us</a>. 
State legislative data from the <a href="http://openstates.org/api/">OpenStates API</a>. 
NYC data <em>scraped</em> from <a href="http://nyc.gov">nyc.gov</a>. NYC boundary data from <a href="http://www.nyc.gov/data">NYC OpenData</a>. 
City-level geospatial data served by <a href="http://geoserver.org">GeoServer</a>. 
All geocoding courtesy of <a href="http://developer.yahoo.com/geo/placefinder/">Yahoo! PlaceFinder</a>. 
Tweet stream uses the <a href="http://tweet.seaofclouds.com/">Tweet! jQuery plugin from seaofclouds</a>. 
</p>

<p> 
	Please check out the  <a href="http://lists.open311.org/groups/discuss/">Open311 mailing list</a> and the <a href="http://forums.e-democracy.org/groups/democracymap">DemocracyMap mailing list</a>.
</p>

</body>
</html>