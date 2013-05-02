function set_map(map_id, lat, long, zoom, url) {

    var map = L.map(map_id).setView([lat, long], zoom);

	L.tileLayer("http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {
        maxZoom: 18,
        subdomains: ["otile1", "otile2", "otile3", "otile4"],
        attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>. Map data (c) <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
    }).addTo(map);


    function style(feature) {
        return {
            weight: 1,
            opacity: 1,
            color: 'blue',
            dashArray: '',
            fillOpacity: 0.25  };
    }

    function zoomToFeature(e) {
        map.fitBounds(e.target.getBounds());
    }

    function onEachFeature(feature, layer) {
	
		if ( feature.geometry.type === "MultiPolygon" ) {
		  // get the bounds for the first polygon that makes up the multipolygon
		  var bounds = layer.getBounds();
		  // loop through coordinates array
		  // skip first element as the bounds var represents the bounds for that element
		  for ( var i = 1, il = feature.geometry.coordinates[0].length; i < il; i++ ) {
		    var ring = feature.geometry.coordinates[0][i];
		    var latLngs = ring.map(function(pair) {
		      return new L.LatLng(pair[1], pair[0]);
		    });
		    var nextBounds = new L.LatLngBounds(latLngs);
		    bounds.extend(nextBounds);
		  }
		  map.fitBounds(bounds);
		}				
	
	
        layer.on({
			click: zoomToFeature
        });
    }


    $.getJSON(url, function(data){
        geojsonLayer = L.geoJson(data, {
            style: style, 
        	onEachFeature: onEachFeature
        });
        geojsonLayer.addTo(map);
    });		

	// map.fitBounds(map.getBounds());

	map.dragging.disable();			
	map.scrollWheelZoom.disable();


}