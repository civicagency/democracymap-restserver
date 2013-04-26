<?php

/**
 * Retrives json at GET from specified URL and decodes to php. 
 * Should probably be called 'curl_from_json' or 'decode_json_curl'
 */

function curl_to_json($url) {
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$data=curl_exec($ch);
	curl_close($ch);


	return json_decode($data, true);	
	
}



/**
 * This mashes together two arrays with the same keys
 * It fills in any empty values, but gives precedence to the $primary array
 */

function array_mash($primary, $secondary) {
	$primary = (array)$primary;
	$secondary = (array)$secondary;
	$out = array();
	foreach($primary as $name => $value) {
		if ( array_key_exists($name, $secondary) && !empty($secondary[$name]) && empty($value)) {
			$out[$name] = $secondary[$name];
		}
		else {
			$out[$name] = $value;
		}
	}
	return $out;
}



function layer_data($server, $layer, $properties, $latlong) {



	$gid_url = $server . 
		"/wfs?request=GetFeature&service=WFS&typename=" . 
		rawurlencode($layer) . 
		"&propertyname=" . 
		rawurlencode($properties) .
		"&CQL_FILTER=" . 
		rawurlencode("INTERSECT(the_geom, POINT (" . $latlong . "))") . 
		"&outputformat=JSON";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $gid_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	//curl_setopt($ch, CURLOPT_HEADER, TRUE);		
	
	$gid_data=curl_exec($ch);			
	curl_close($ch);

	if($gid_data) {
		return json_decode($gid_data, true);
	} else {
		return false;
	}

}



function get_cartodb_layer ($return_type, $cartodb_domain, $table, $columns, $format, $latlong) {
	
	$latlong = explode(' ', $latlong);
	$latlong = $latlong[1] . '%20' . $latlong[0];
	
	$url = $cartodb_domain . '/api/v2/sql?format=' . $format . '&q=SELECT%20' . $columns . '%20FROM%20' . $table . "%20WHERE%20ST_Contains(the_geom%2C%20%20ST_GeomFromText('POINT(" . $latlong . ")'%2C%204326))";
	
	if ($return_type == 'url') {
		return $url;
	} else {
		$layer_data = curl_to_json($url);
		return $layer_data;		
	}

	
}

function urlify($str) {                                                                                                                        
     // FIXME: unicode                                                                                                    
     // String gets converted to all lowercase                                                                            
     // Everything that isnt a-Z 0-9 space, tab or - gets replaced by nothing                                              
     // space and tab gets replaced by -                                                                                  
     // multiple --s get replaced by a single -                                                                                        
	 // - at the start of a string gets replaced by nothing                                                                
     // - at the end of a string gets replaced by nothing
     return preg_replace(array('/[^a-zA-Z0-9 \t-]/', '/[ \t]/', '/-+/', '/^-/', '/-$/'), array('', '-', '-', '', ''), strtolower($str));                                                                                                                    
 }





?>