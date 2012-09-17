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


<h1>GeoWebDNS Endpoints</h1>

URL: http://api.democracymap.org/geowebdns/endpoints

<p>There are just a few parameters</p>

<ul>
	<li><strong>location</strong> <em>(options: location string or lat, long).</em> This is any string that can be geocoded</li>
	<li><strong>format</strong> <em>(options: json, xml).</em> This specifies the format of the response.</li>
	<li><strong>geojson</strong> <em>(options: true, false).</em> This specifies whether you want a geojson representation of the boundary returned</li>
</ul>

<h2>Example Call</h2>
<p>
	<a href="http://api.democracymap.org/geowebdns/endpoints?location=chicago&format=json&geojson=true">http://api.democracymap.org/geowebdns/endpoints?location=chicago&amp;format=json&amp;geojson=true</a>
</p>	


</body>
</html>