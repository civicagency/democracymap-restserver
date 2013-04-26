<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Nyc_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}

	
	public function get_city_council($latlong, $democracymap) {
			
		$this->load->helper('api');		
		if($community_board = get_cartodb_layer('output', $this->config->item('cartodb_domain'), 'nyc_council_districts', 'coundist', 'json', $latlong)) {
		
			$data['council_district'] = (!empty($community_board['rows'])) ? $community_board['rows'][0]['coundist'] : '';
			$geojson = get_cartodb_layer('url', $this->config->item('cartodb_domain'), 'nyc_council_districts', '*', 'geoJSON', $latlong);
			
			
		} else {
			return null;
		}





//		$sql = "SELECT * FROM citylaws_councilmember 
//						WHERE district = {} 
//					 ORDER BY start_date DESC LIMIT 1;";
//
//
//		$query = $this->db->query($sql);		
		
//   	if ($query->num_rows() > 0) {
//   	   foreach ($query->result() as $rows)  {		

//				$something = $rows->column_name;

//		   }
//		}			
		
		$jurisdiction = $democracymap->jurisdiction();
		$official 	  = $democracymap->official();
		$social_media = $democracymap->social_media();	
		
		if ($council = $this->get_councilmember($data['council_district'])) {		

			// Map returned values over to our data model for officials
			$official['title']					=  'Councilmember';
			$official['type']					=  'legislative';
        
			//$official['name_given']				=  $council['first_name'];	
			//$official['name_family']			=  $council['last_name'];	
		
			//$middle 							= (!empty($council['mid_name'])) ? ' ' . $council['mid_name'] . ' ' : ' ';
			$official['name_full']				=  $council['name'];					
        
        
			$official['url']					=  $council['source'];	
			$official['url_photo']				=  $council['image_url'];					
			$official['email']					=  $council['email'];									
			$official['phone']					=  $council['legislative_office_phone'];									
        
        
			//$official['address_1']				=  $council['street_address_1'];	
			//$official['address_2']				=  $council['street_address_2'];					
			//$official['address_city']			=  $council['city'];					
			//$official['address_state']			=  $council['state'];					
			//$official['address_zip']			=  $council['zip'];																	
        
			//$end_date							= (substr($council['end_date'], 0, 1) == '9') ? null : $council['end_date'];
			//$official['current_term_enddate']	=  $end_date;							      	
        
			$official['extra']					=  array(
																//array("key" => 'committees', "value" => $council['committees']),
																array("key" => 'party', "value" => $council['party'])															 
																);

			// Social Media
			if ($council['twitter']) {
				$twitter							= trim($council['twitter'], '@');
				$social_media['type'] 				= 'twitter';
				$social_media['description'] 		= 'Twitter';
				$social_media['username'] 			= $twitter;	
				$social_media['url'] 				= 'https://twitter.com/' . $twitter;				
																					

				$official['social_media']			= array($social_media);
			}													
																
																

			// Map to our data model for jurisdiction												
			$jurisdiction['type'] 			= 'legislative';	
			$jurisdiction['type_name'] 		= 'City Council';
			$jurisdiction['level'] 			= 'municipal';
			$jurisdiction['level_name'] 	= 'City';
			$jurisdiction['name'] 			= 'District ' . $council['district'];
			$jurisdiction['url'] 			= $council['source'];	

			$jurisdiction['phone'] 			= $council['district_office_phone'];	


			//$jurisdiction['address_1']	    =  $council['street_address_1'];	
			//$jurisdiction['address_2']	    =  $council['street_address_2'];					
			//$jurisdiction['address_city']   =  $council['city'];					
			//$jurisdiction['address_state']  =  $council['state'];					
			//$jurisdiction['address_zip']    =  $council['zip'];

			$jurisdiction['metadata']					=  array(
														array("key" => 'geojson', "value" => $geojson)																													 
														);


		
			$jurisdiction['id'] 			= $council['district'];	
		
			// Attach official to jurisdiction
			$jurisdiction['elected_office'] = array($official);
		
			return $jurisdiction;
		} else {
		  return false;
		}
		
		
	}	
	
	
	
	function get_councilmember($id) {
	
		$key = 'nyc_council' . $id;		
		
	 	// Check in cache
	 	if ( $cache = $this->cache->get( $key ) ) {
	 		return $cache;
	 	}		
	
		$query = "select * from 'swdata' where (district = '$id')";
    	
		$query = urlencode($query);
    	
		$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=new_york_city_council&query=$query";		
    	
		$council = curl_to_json($url);	

		if(!empty($council[0])) {
	
			// Save to cache
			$this->cache->save( $key, $council[0], $this->ttl);
	
			return $council[0];		                           	
		}	else {                                             	
			return false;                                      	
		}                                                      	
	
	
	}
	
	
	
	
	
	// ###########################################################################
	// Community Board
	// ###########################################################################
	
	
	
	function get_community_board($latlong, $democracymap) {
		
		// $this->load->helper('api');					
		// if($community_board = layer_data($this->config->item('geoserver_root'), 'census:community_district', 'borocd,gid', $latlong)) {
		// 	$data['community_board'] = (!empty($community_board['features'])) ? $community_board['features'][0]['properties']['borocd'] : '';
		// 	$data['community_board_fid'] = (!empty($community_board['features'])) ? "community_district." . $community_board['features'][0]['properties']['gid'] : '';			
		// 


		$this->load->helper('api');		
		if($community_board = get_cartodb_layer('output', $this->config->item('cartodb_domain'), 'nyc_community_districts', 'borocd', 'json', $latlong)) {
		
			$data['community_board'] = (!empty($community_board['rows'])) ? $community_board['rows'][0]['borocd'] : '';
			$geojson = get_cartodb_layer('url', $this->config->item('cartodb_domain'), 'nyc_community_districts', '*', 'geoJSON', $latlong);
			
			
		} else {
			return null;
		}

		$jurisdiction = $democracymap->jurisdiction();
		$official 	  = $democracymap->official();
		$official2 	  = $democracymap->official();		
		
		
		if($community_board = $this->get_community_board_data($data['community_board'])) {


			// Map returned values over to our data model for officials
			$official['title']					=  'Chair';
			$official['type']					=  'executive';
			$official['name_full']				=  $community_board['chair'];										      	
			$official['email'] 					= $community_board['email'];	

			$official2['title']					=  'District Manager';
			$official2['type']					=  'administrative';
			$official2['name_full']				=  $community_board['district_manager'];						


			// Map to our data model for jurisdiction												
			$jurisdiction['type'] 			= 'government';	
			$jurisdiction['type_name'] 		= 'Community Board';
			$jurisdiction['level'] 			= 'sub-municipal';
			$jurisdiction['level_name'] 	= 'Community Board';
			$jurisdiction['name'] 			= $community_board['borough'] . ' ' . $community_board['community_board_number'];
			$jurisdiction['url'] 			= $community_board['website'];	

			$jurisdiction['phone'] 			= $community_board['phone'];	
			$jurisdiction['email'] 			= $community_board['email'];	

			$jurisdiction['address_title']	=  $community_board['address_title']; 

			$jurisdiction['address_1']	    =  $community_board['address_1']; 
			$jurisdiction['address_2']	    =  $community_board['address_2'];  
			 
			$jurisdiction['address_city']   =  $community_board['address_city'];  				
			$jurisdiction['address_state']  =  $community_board['address_state'];				
			$jurisdiction['address_zip']    =  $community_board['address_zip'];	

			$jurisdiction['metadata']					=  array(
														array("key" => 'neighborhoods', "value" => $community_board['neighborhoods']),
														array("key" => 'cabinet_meeting', "value" => $community_board['cabinet_meeting']),																
														array("key" => 'board_meeting', "value" => $community_board['board_meeting']),
														array("key" => 'geojson', "value" => $geojson)																													 
														);			


			$jurisdiction['id'] 			= urlify($jurisdiction['name']);	

			// Attach official to jurisdiction
			$jurisdiction['elected_office'] = array($official, $official2);


			return $jurisdiction;
			
		} else {
			return false;
		}

	}	
	
	
	
	function get_community_board_data($id) {
		
		$key = 'nyc_cb' . $id;				
    
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
    
   
		$query = "select * from `community_board` where city_id = '$id'";
    	
		$query = urlencode($query);
    	
		$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=city_representatives_-_nyc_community_boards_2&query=$query";		

		$cb = curl_to_json($url);
    
		if(!empty($cb[0])) {
    
			// Save to cache
			$this->cache->save( $key, $cb[0], $this->ttl);
    
			return $cb[0];		
		}	else {
		 	return false;
		 }	
    
	}	
	

	function get_pubadvocate($democracymap) {	
		
		$official 	  = $democracymap->official();

		$official['title']					=  'Public Advocate';
		$official['type']					=  'advocate';
		$official['name_given'] 			=  'Bill';	
		$official['name_family'] 			=  'de Blasio';
		$official['name_full']				=  'Bill de Blasio';											      	
		$official['description'] 			=  '';	


		$official['url'] 					=  'http://pubadvocate.nyc.gov/';	
		$official['url_photo'] 				=  'https://twimg0-a.akamaihd.net/profile_images/2431823933/jwygymtec2srad1qj4r0.jpeg'; //'http://pubadvocate.nyc.gov/sites/advocate.nyc.gov/files/imagecache/picture_resized/deBlasio_0.jpg';	
		$official['url_schedule'] 			=  '';	
		$official['url_contact'] 			=  '';	
		$official['email'] 					=  'GetHelp@pubadvocate.nyc.gov';	
		$official['phone'] 					=  '+1-212-669-7200';	
		$official['address_name'] 			=  '';	
		$official['address_1'] 				=  '1 Centre Street';
		$official['address_2'] 				=  '15th Floor';	
		$official['address_city'] 			=  'New York';	
		$official['address_state'] 			=  'NY';	
		$official['address_zip'] 			=  '10007';	
		$official['current_term_enddate'] 	=  '';
		
		// Twitter
		$social_media_account = $democracymap->social_media();		

		$social_media_account['type']			= 'twitter';
		$social_media_account['description']	= 'Twitter';
		$social_media_account['username']		= 'BilldeBlasio';				
		$social_media_account['url']			= 'https://twitter.com/BilldeBlasio';							

		$social_media[] 						= $social_media_account;
		
		// Facebook				
		$social_media_account 	= $democracymap->social_media();		
	
		$social_media_account['type']			= 'facebook';
		$social_media_account['description']	= 'Facebook';
		$social_media_account['username']		= '';				
		$social_media_account['url']			= 'http://www.facebook.com/pabilldeblasio';							

		$social_media[] 						= $social_media_account;		
		
		$official['social_media'] 			=  $social_media;	
		
		return $official;
	
	}
	
	
	function get_comptroller($democracymap) {	
		
		$official 	  = $democracymap->official();

		$official['title']					=  'Comptroller';
		$official['type']					=  'comptroller';
		$official['name_given'] 			=  'John';	
		$official['name_family'] 			=  'Liu';
		$official['name_full']				=  'John C. Liu';											      	
		$official['description'] 			=  '';	


		$official['url'] 					=  'http://www.comptroller.nyc.gov/';	
		$official['url_photo'] 				=  'http://www.comptroller.nyc.gov/images/headshots/John_Liu2-thumb.jpg';	
		$official['url_schedule'] 			=  '';	
		$official['url_contact'] 			=  '';	
		$official['email'] 					=  '';	
		$official['phone'] 					=  '+1-212-669-3916';	
		$official['address_name'] 			=  'Municipal Building';	
		$official['address_1'] 				=  '1 Centre Street';
		$official['address_2'] 				=  '';	
		$official['address_city'] 			=  'New York';	
		$official['address_state'] 			=  'NY';	
		$official['address_zip'] 			=  '10007';	
		$official['current_term_enddate'] 	=  '';
		
		// Twitter
		$social_media_account = $democracymap->social_media();		

		$social_media_account['type']			= 'twitter';
		$social_media_account['description']	= 'Twitter';
		$social_media_account['username']		= 'JohnCLiu';				
		$social_media_account['url']			= 'https://twitter.com/JohnCLiu';							

		$social_media[] 						= $social_media_account;
		
		// Facebook				
		$social_media_account 	= $democracymap->social_media();		
	
		$social_media_account['type']			= 'facebook';
		$social_media_account['description']	= 'Facebook';
		$social_media_account['username']		= '';				
		$social_media_account['url']			= 'http://www.facebook.com/Liu.NYC';							

		$social_media[] 						= $social_media_account;		
		
		$official['social_media'] 			=  $social_media;	
		
		return $official;
	
	}	
	
	
	
	
	
	
	
	function get_officials($democracymap) {	
		
		$elected[] = $this->get_pubadvocate($democracymap);		
		$elected[] = $this->get_comptroller($democracymap);
		
		return $elected;
		
	}	
	
	
	

}

?>