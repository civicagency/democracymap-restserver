<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class County_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}


	public function get_county_geo($lat, $long) {	


		$url = "http://tigerweb.geo.census.gov/ArcGIS/rest/services/Census2010/tigerWMS/MapServer/115/query?text=&geometry=$long%2C$lat&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&relationParam=&objectIds=&where=&time=&returnCountOnly=false&returnIdsOnly=false&returnGeometry=false&maxAllowableOffset=&outSR=&outFields=COUNTY,BASENAME,NAME,STATE&f=json";	

			$feature_data = curl_to_json($url);

			if(!empty($feature_data['features'])) return $feature_data['features'][0]['attributes'];			

	}


	
	public function get_county_data($county, $state) {
		
		$sql = "SELECT * FROM counties
				WHERE fips_county = '$county' and fips_state = '$state'";
				
	
		$query = $this->db->query($sql);				
			
		if ($query->num_rows() > 0) {
		   foreach ($query->result() as $rows)  {	
			
			$county_data = array(
					
				'county_id'						=>  $rows->county_id, 	
				'name'							=>  ucwords(strtolower($rows->name)),		
				'political_description'			=>  $rows->political_description,
				'title'							=>  ucwords(strtolower($rows->title)), 			
				'address1'						=>  ucwords(strtolower($rows->address1)), 	    
				'address2'						=>  ucwords(strtolower($rows->address2)), 	
				'city'							=>  ucwords(strtolower($rows->city)),   
				'state'							=>  $rows->state,  
				'zip'							=>  $rows->zip,    
				'zip4'							=>  $rows->zip4,   
				'website_url'					=>  $rows->website_url,
				'population_2006'				=>  $rows->population_2006,
				'fips_state'					=>  $rows->fips_state,
				'fips_county'					=>  $rows->fips_county
			
			);	  
			
			
			if(strlen($county_data['zip']) < 5) {
				$county_data['zip'] = str_pad($county_data['zip'], 5, "0", STR_PAD_LEFT);
			}

			if(!empty($county_data['zip4']) && strlen($county_data['zip4']) < 4) {
				$county_data['zip4'] = str_pad($county_data['zip4'], 4, "0", STR_PAD_LEFT);
			}			
			
			      			      			      	                               
			
		   }
		}	
	
		
		if (!empty($county_data)) {	
			return $county_data;
		}
		else {
			return false;
		}
		
	}	
	
	
	public function get_county_reps($state, $county) {
		
		$key = md5( serialize( "$state$county" )) . '_county_rep';
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$county = ucwords($county);	
		$state = strtoupper($state);	
				
		$query = "select rep, rep_email, rep_position from `swdata` where county = '$county' and state = '$state'";		
		$query = urlencode($query);
							
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=us_county_representatives&query=$query";		

		$county_reps = curl_to_json($url);
				
		if(!empty($county_reps)) {
			
			// Save to cache
			$this->cache->save( $key, $county_reps, $this->ttl);
			
			return $county_reps;			
		}		
						
	}	
	
	
	

}

?>