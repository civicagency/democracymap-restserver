<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Boston_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}

	
	public function get_city_council($cartodb, $latlong, $democracymap) {
			
		$this->load->helper('api');		
		if($community_board = get_cartodb_layer('output', $cartodb, 'council_districts', '*', 'json', $latlong)) {
				
			$data['council_district'] = (!empty($community_board['rows'])) ? $community_board['rows'][0]['district'] : null;			
			$data['council_member'] = (!empty($community_board['rows'])) ? $community_board['rows'][0]['councillor'] : null;						
			$geojson = get_cartodb_layer('url', $cartodb, 'council_districts', '*', 'geoJSON', $latlong);
			
			
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
		
		if (!empty($data['council_district']) && !empty($data['council_member'])) {		

			// Map returned values over to our data model for officials
			$official['title']					=  'Councilmember';
			$official['type']					=  'legislative';
        
			//$official['name_given']				=  $council['first_name'];	
			//$official['name_family']			=  $council['last_name'];	
		
			//$middle 							= (!empty($council['mid_name'])) ? ' ' . $council['mid_name'] . ' ' : ' ';
			$official['name_full']				=  $data['council_member'];					
        
												
																

			// Map to our data model for jurisdiction												
			$jurisdiction['type'] 			= 'legislative';	
			$jurisdiction['type_name'] 		= 'City Council';
			$jurisdiction['level'] 			= 'municipal';
			$jurisdiction['level_name'] 	= 'City';
			$jurisdiction['name'] 			= 'District ' . $data['council_district'];	



			//$jurisdiction['address_1']	    =  $council['street_address_1'];	
			//$jurisdiction['address_2']	    =  $council['street_address_2'];					
			//$jurisdiction['address_city']   =  $council['city'];					
			//$jurisdiction['address_state']  =  $council['state'];					
			//$jurisdiction['address_zip']    =  $council['zip'];

			$jurisdiction['metadata']					=  array(
														array("key" => 'geojson', "value" => $geojson)																													 
														);


		
//			$jurisdiction['id'] 			= $council['district'];	
		
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
	
	
	

	
	
	
	

}

?>