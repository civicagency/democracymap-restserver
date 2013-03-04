<?php

//$city = ($city);

$query = "select * from `swdata` where jurisdiction = '$city' and (position like '%council%'
		 or position like '%mayor%' 
		 or position like '%member%' 
		 or position like '%district%' 
		 or position like '%city administrator%' 
		 or position like '%administrative coordinator%' 		
		 or position like '%chief executive officer%' 
		 or position like '%town manager%' 	
		 or position like '%selectman%' 	
		 or position like '%selectboard%' 	
		 or position like '%alderman%' 								
		 or position like '%city manager%' 	
		 or position like '%council%' 																												
		)";





/* THIS IS A FUZZIER SEARCH
$query = "select swdata.* from `swdata` where municipality_name like '%$city%' and municipality_name not like '%county%'and (title like '%council%'
													 or title like '%mayor%' 
													 or title like '%member%' 
													 or title like '%district%' 
													 or title like '%city administrator%' 
													 or title like '%chief executive officer%' 
													 or title like '%city manager%' 																										
													)";

*/

$query = urlencode($query);

$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=city_representatives_-_massachusetts&query=$query";		

$officials = curl_to_json($url);

// normalized data
foreach ($officials as $official) {
		 			
	if (strripos($official['position'], 'mayor') !== false) { // Had considered these tests too, but don't think they're elected: OR strripos($official['title'], 'city manager') !== false OR strripos($official['title'], 'executive') !== false
		$elected_type = 'executive';
	} 
	else if (strripos($official['position'], 'council') !== false 
			OR strripos($official['position'], 'member') !== false 
			OR strripos($official['position'], 'district') !== false 
			OR strripos($official['position'], 'selectman') !== false 
			OR strripos($official['position'], 'selectboard') !== false 
			OR strripos($official['position'], 'alderman') !== false) {
		$elected_type = 'legislative';
	} else {
		$elected_type = 'administrative';
	}
		
		$name_middle = ($official['middle_name']) ? ' ' . $official['middle_name'] . ' ' : ' '; 
		$name_full = $official['first_name'] . $name_middle . $official['last_name'];
		
	$electeds[] = $this->elected_official_model($type = $elected_type, 
											  $title = $official['position'], 
											  $description = $official['functional_role'], 
											  $name_given = $official['first_name'], 
											  $name_family = $official['last_name'],
											  $name_full, 
											  $url = null, 
											  $url_photo = null, 
											  $url_schedule = null, 
											  $url_contact = null, 
											  $email = $official['email'], 
											  $phone = $official['phone'], 
											  $address_name = null, 
											  $address_1 = $official['address_line1'], 
											  $address_2 = $official['address_line2'], 
											  $address_city = $official['city'], 
											  $address_state = $official['state'], 
											  $address_zip = $official['zipcode'], 
											  $current_term_enddate = null, 
											  $last_updated = null,
											  $social_media = null);
	
}


?>