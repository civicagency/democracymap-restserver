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