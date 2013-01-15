<?php


$query = "select * from `rep` where city = '$city' and (title like '%council%' or title like '%mayor%')";
$query = urlencode($query);

$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=city_representatives_-_washington&query=$query";		

$officials = curl_to_json($url);

// normalized data
foreach ($officials as $official) {
		 			
	if (strripos($official['title'], 'mayor') !== false) {
		$elected_type = 'executive';
	} 
	else if (strripos($official['title'], 'council') !== false) {
		$elected_type = 'legislative';
	} else {
		$elected_type = 'administrative';
	}
		
	$electeds[] = $this->elected_official_model($type = $elected_type, 
											  $title = $official['title'], 
											  $description = null, 
											  $name_given = null, 
											  $name_family = null, 
											  $name_full = $official['name'], 
											  $url = null, 
											  $url_photo = null, 
											  $url_schedule = null, 
											  $url_contact = null, 
											  $email = null, 
											  $phone = null, 
											  $address_name = null, 
											  $address_1 = null, 
											  $address_2 = null, 
											  $address_city = null, 
											  $address_state = null, 
											  $address_zip = null, 
											  $current_term_enddate = null, 
											  $last_updated = null, 
											  $social_media = null);
	
}



?>