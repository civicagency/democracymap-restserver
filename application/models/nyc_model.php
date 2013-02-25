<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Nyc_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}

	
	public function get_city_council($latlong, $democracymap) {
			
		$this->load->helper('api');					
		$council = layer_data($this->config->item('geoserver_root'), 'census:city_council', 'coundist,gid', $latlong);


		$data['council_district'] 	= (!empty($council['features'])) ? $council['features'][0]['properties']['coundist'] : '';
		$data['council_district_fid'] 	= (!empty($council['features'])) ? $council['features'][0]['properties']['gid'] : '';


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
		
		$council = $this->get_councilmember($data['council_district']);		

		// Map returned values over to our data model for officials
		$official['title']					=  'City Councilmember';
		$official['type']					=  'legislative';
        
		$official['name_given']				=  $council['first_name'];	
		$official['name_family']			=  $council['last_name'];	
		
		$middle 							= (!empty($council['mid_name'])) ? ' ' . $council['mid_name'] . ' ' : ' ';
		$official['name_full']				=  $council['first_name'] . $middle . $council['last_name'];					
        
        
		$official['url']					=  $council['website'];	
		$official['url_photo']				=  $council['image_url'];					
		$official['email']					=  $council['email'];									
		$official['phone']					=  $council['city_hall_telephone'];									
        
        
		$official['address_1']				=  $council['street_address_1'];	
		$official['address_2']				=  $council['street_address_2'];					
		$official['address_city']			=  $council['city'];					
		$official['address_state']			=  $council['state'];					
		$official['address_zip']			=  $council['zip'];																	
        
		$end_date							= (substr($council['end_date'], 0, 1) == '9') ? null : $council['end_date'];
		$official['current_term_enddate']	=  $end_date;							      	
        
		$official['extra']					=  array(
															array("key" => 'committees', "value" => $council['committees']),
															array("key" => 'party', "value" => $council['party'])															 
															);

		// Map to our data model for jurisdiction												
		$jurisdiction['type'] 			= 'legislative';	
		$jurisdiction['type_name'] 		= 'City Council';
		$jurisdiction['level'] 			= 'municipal';
		$jurisdiction['level_name'] 	= 'City';
		$jurisdiction['name'] 			= 'Council District ' . $council['district'];
		$jurisdiction['url'] 			= $council['website'];	

		$jurisdiction['phone'] 			= $council['district_telephone'];	


		$jurisdiction['address_1']	    =  $council['street_address_1'];	
		$jurisdiction['address_2']	    =  $council['street_address_2'];					
		$jurisdiction['address_city']   =  $council['city'];					
		$jurisdiction['address_state']  =  $council['state'];					
		$jurisdiction['address_zip']    =  $council['zip'];

		
		$jurisdiction['id'] 			= $council['district'];	
		
		// Attach official to jurisdiction
		$jurisdiction['elected_office'] = array($official);
		
		return $jurisdiction;
		
	}	
	
	
	
	function get_councilmember($id) {
			
		$key = 'nyc_council' . $id;				
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$url = "http://www.databeam.org/philipashlock/democracymap-gotham/citylaws_councilmember.json?column=district&value=$id&order_by=start_date&direction=DESC&limit=1&api_key=api-key";		

		$council = curl_to_json($url);
		
		if(!empty($council[0])) {
			
			// Save to cache
			$this->cache->save( $key, $council[0], $this->ttl);
			
			return $council[0];		
		}	

	}	
	
	
	
	// ###########################################################################
	// Community Board
	// ###########################################################################
	
	
	
	function get_community_board($latlong, $democracymap) {
		
		$this->load->helper('api');					
		$community_board = layer_data($this->config->item('geoserver_root'), 'census:community_district', 'borocd,gid', $latlong);

		$data['community_board'] = (!empty($community_board['features'])) ? $community_board['features'][0]['properties']['borocd'] : '';
		$data['community_board_fid'] = (!empty($community_board['features'])) ? "community_district." . $community_board['features'][0]['properties']['gid'] : '';

		$jurisdiction = $democracymap->jurisdiction();
		$official 	  = $democracymap->official();
		$official2 	  = $democracymap->official();		
		
		
		$community_board = $this->get_community_board_data($data['community_board']);


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
			$jurisdiction['name'] 			= $community_board['borough'] . ' ' . $community_board['community_board'];
			$jurisdiction['url'] 			= $community_board['website'];	

			$jurisdiction['phone'] 			= $community_board['phone'];	
			$jurisdiction['email'] 			= $community_board['email'];	


			$address = explode(',', $community_board['address']);
			$location = explode(' ', trim($address[2]));			

			$jurisdiction['address_1']	    =  trim($address[0]);	
			$jurisdiction['address_city']   =  trim($address[1]);					
			$jurisdiction['address_state']  =  trim($location[0]);				
			$jurisdiction['address_zip']    =  trim($location[1]);	
			
			$jurisdiction['metadata']					=  array(
																array("key" => 'neighborhoods', "value" => $community_board['neighborhoods']),
																array("key" => 'cabinet_meeting', "value" => $community_board['cabinet_meeting']),																
																array("key" => 'board_meeting', "value" => $community_board['board_meeting'])															 
																);			


			$jurisdiction['id'] 			= $community_board['community_board'];	

			// Attach official to jurisdiction
			$jurisdiction['elected_office'] = array($official, $official2);


			return $jurisdiction;


	}	
	
	
	
	function get_community_board_data($id) {
		
		$key = 'nyc_cb' . $id;				
    
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
    
		$url = "http://www.databeam.org/philipashlock/democracymap-gotham/community_boards.json?column=city_id&value=$id&api_key=api-key";		
    
		$cb = curl_to_json($url);
    
		if(!empty($cb[0])) {
    
			// Save to cache
			$this->cache->save( $key, $cb[0], $this->ttl);
    
			return $cb[0];		
		}	
    
	}	
	

	
	
	

}

?>