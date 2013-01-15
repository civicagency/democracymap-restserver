<?php

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
 * Retrieve data from Alternative PHP Cache (APC).
 */
function cache_get( $key ) {
	
	if ( !extension_loaded('apc') || (ini_get('apc.enabled') != 1) ) {
		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}
	}
	else {
		return apc_fetch( $key );
	}

	return false;

}

/**
 * Store data in Alternative PHP Cache (APC).
 */
function cache_set( $key, $value, $ttl = null ) {

	if ( $ttl == null ) {
		$ttl = ($this->config->item('cache_ttl')) ? $this->config->item('cache_ttl') : $this->ttl;
	}

	$key = 'db_api_' . $key;

	if ( extension_loaded('apc') && (ini_get('apc.enabled') == 1) ) {
		return apc_store( $key, $value, $ttl );
	}

	$this->cache[$key] = $value;

}

?>