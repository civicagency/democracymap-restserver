<?php


$query = "select * from `rep` where city = '$city' and (title like '%council%'  
													 or title like '%mayor%' 
													 or title like '%member%' 
													 or title like '%district%' 
													 or title like '%city administrator%' 
													 or title like '%chief executive officer%' 
													 or title like '%city manager%' 																										
													)";

$query = urlencode($query);

$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=city_representatives&query=$query";		

$officials = curl_to_json($url);

if(!empty($officials)) {
	
	// normalized data
	foreach ($officials as $official) {
			 			
		if (strripos($official['title'], 'mayor') !== false) { // Had considered these tests too, but don't think they're elected: OR strripos($official['title'], 'city manager') !== false OR strripos($official['title'], 'executive') !== false
			$elected_type = 'executive';
		} 
		else if (strripos($official['title'], 'council') !== false OR strripos($official['title'], 'member') !== false OR strripos($official['title'], 'district') !== false) {
			$elected_type = 'legislative';
		} else {
			$elected_type = 'administrative';
		}
			
		$electeds[] = $this->elected_official_model($type = $elected_type, 
												  $title = $official['title'], 
												  $description = null, 
												  $name_given = null, 
												  $name_family = null, 
												  $name_full = $official['name_full'], 
												  $url = null, 
												  $url_photo = null, 
												  $url_schedule = null, 
												  $url_contact = null, 
												  $email = null, 
												  $phone = $official['phone'], 
												  $address_name = null, 
												  $address_1 = $official['address_1'], 
												  $address_2 = $official['address_2'], 
												  $address_city = $official['city'], 
												  $address_state = $official['state'], 
												  $address_zip = $official['zip'], 
												  $current_term_enddate = null, 
												  $last_updated = null, 
												  $social_media = null);
		
	}

}


?>