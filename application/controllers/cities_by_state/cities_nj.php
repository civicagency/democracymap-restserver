<?php

$today = date("Y-m-d");

$query = "select * from `swdata` where city = '$city' AND date(term_end) > date('$today')";




$query = urlencode($query);

$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=mayors_-_new_jersey&query=$query";		

$officials = curl_to_json($url);

if($officials) {

// Sloppy hack - assume we just want the first one if there are multiple
$official = $officials[0];
		 			
	$elected_type = 'executive';
	
	$phone = ($official['phone']) ? $this->format_phone($official['phone']) : null;
	
	$electeds[] = $this->elected_official_model($type = $elected_type, 
											  $title = 'Mayor', 
											  $description = null, 
											  $name_given = null, 
											  $name_family = null,
											  $name_full = $official['mayor_name'], 
											  $url = null, 
											  $url_photo = null, 
											  $url_schedule = null, 
											  $url_contact = null, 
											  $email = null, 
											  $phone, 
											  $address_name = $official['address_name'], 
											  $address_1 = $official['address_1'], 
											  $address_2 = $official['address_2'], 
											  $address_city = $official['city'], 
											  $address_state = 'NJ', 
											  $address_zip = $official['zip'], 
											  $current_term_enddate = $official['term_end'], 
											  $last_updated = null,
											  $social_media = null);

}

?>