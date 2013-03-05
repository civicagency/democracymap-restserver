<?php

$query = "select Title, Name from `reps` where City = '$city'";

$query = urlencode($query);

$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=nc_cities_towns&query=$query";		

$officials = curl_to_json($url);

// normalized data
foreach ($officials as $official) {
		 			
	if (strripos($official['Title'], 'mayor') !== false) { // Had considered these tests too, but don't think they're elected: OR strripos($official['title'], 'city manager') !== false OR strripos($official['title'], 'executive') !== false
		$elected_type = 'executive';
	} 
	else if (strripos($official['Title'], 'council') !== false 
		  OR strripos($official['Title'], 'member') !== false 
		  OR strripos($official['Title'], 'district') !== false 
		  OR strripos($official['Title'], 'selectman') !== false 
		  OR strripos($official['Title'], 'selectboard') !== false 
		  OR strripos($official['Title'], 'alderman') !== false) {
		$elected_type = 'legislative';
	} else {
		$elected_type = 'administrative';
	}

		
	$electeds[] = $this->elected_official_model($type = $elected_type, 
											  $title = $official['Title'], 
											  $description = null, 
											  $name_given = null, 
											  $name_family = null,
											  $name_full = $official['Name'], 
											  $url = null, 
											  $url_photo = null, 
											  $url_schedule = null, 
											  $url_contact = null, 
											  $email = null, 
											  $phone = null, 
											  $address_name = null, 
											  $address_1 = null, 
											  $address_2 = null, 
											  $address_city  = $city, 
											  $address_state = 'NC', 
											  $address_zip   = null, 
											  $current_term_enddate = null, 
											  $last_updated = null,
											  $social_media = null);
	
}


?>