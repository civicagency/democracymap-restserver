<?php

$city = strtoupper($city);

$query = "select swdata.* from `swdata` where (municipality_name = '$city'
 											or municipality_name = '$city CITY'
 											or municipality_name = '$city TWP') and municipality_name not like '%county%'and (title like '%council%'
													 or title like '%mayor%' 
													 or title like '%member%' 
													 or title like '%district%' 
													 or title like '%city administrator%' 
													 or title like '%chief executive officer%' 
													 or title like '%city manager%' 																										
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

$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=city_representatives_-_pennsylvania&query=$query";		

$officials = curl_to_json($url);

// normalized data
foreach ($officials as $official) {
		 			
	if (strripos($official['TITLE'], 'mayor') !== false) { // Had considered these tests too, but don't think they're elected: OR strripos($official['title'], 'city manager') !== false OR strripos($official['title'], 'executive') !== false
		$elected_type = 'executive';
	} 
	else if (strripos($official['TITLE'], 'council') !== false OR strripos($official['TITLE'], 'member') !== false OR strripos($official['TITLE'], 'district') !== false) {
		$elected_type = 'legislative';
	} else {
		$elected_type = 'administrative';
	}
		
	if ($official['textbox11']) {	
		$last_updated = $official['textbox11'];
		$last_updated = strtotime($last_updated);
		$last_updated = date(DATE_ATOM, $last_updated);
	} else 
	{
	 	$last_updated = null;
	}
	
	$current_term_enddate = ($official['YTE']) ? $official['YTE'] : null;
		
	$electeds[] = $this->elected_official_model($type = $elected_type, 
											  $title = $official['TITLE'], 
											  $description = null, 
											  $name_given = null, 
											  $name_family = null, 
											  $name_full = $official['NAME'], 
											  $url = $official['WEB_SITE'], 
											  $url_photo = null, 
											  $url_schedule = null, 
											  $url_contact = null, 
											  $email = $official['EMAIL'], 
											  $phone = $official['PHONE'], 
											  $address_name = null, 
											  $address_1 = $official['ADDRESS1'], 
											  $address_2 = $official['ADDRESS2'], 
											  $address_city = $official['CITY'], 
											  $address_state = $official['STATE'], 
											  $address_zip = $official['ZIP_CODE'], 
											  $current_term_enddate, 
											  $last_updated, 
											  $social_media = null);
	
}



?>