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


?>