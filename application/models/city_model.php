<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class City_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}

	
	public function get_city_data($state_id, $place_id) {
		
		$geoid = str_pad($state_id, 2, "0", STR_PAD_LEFT) . str_pad($place_id, 5, "0", STR_PAD_LEFT);
		
		$sql = "SELECT municipalities.GOVERNMENT_NAME, 
		       	 	 	 municipalities.POLITICAL_DESCRIPTION, 
						 municipalities.TITLE, 
						 municipalities.ADDRESS1,
						 municipalities.ADDRESS2, 
						 municipalities.CITY, 
						 municipalities.STATE_ABBR, 
						 municipalities.ZIP, 
						 municipalities.ZIP4, 
						 municipalities.WEB_ADDRESS,
						 municipalities.POPULATION_2005, 
						 municipalities.COUNTY_AREA_NAME,  
						 municipalities.SERVICE_DISCOVERY,
						 municipalities.GEOID, 
						 gnis.FEATURE_ID, 
						 gnis.PRIMARY_LATITUDE, 
						 gnis.PRIMARY_LONGITUDE, 
						 ocd.OCDID
		   		  	FROM gnis, municipalities, ocd
							      WHERE (municipalities.FIPS_PLACE = gnis.CENSUS_CODE
							      	    )
								     AND (municipalities.FIPS_STATE = gnis.STATE_NUMERIC
								     	 )
									 AND (municipalities.GEOID 		= ocd.GEOID
										 )
									 AND (municipalities.GEOID = '$geoid'
									      )
									";

		$query = $this->db->query($sql);


		if ($query->num_rows() > 0) {
		   foreach ($query->result() as $rows)  {
			
		      $city['gnis_fid']			=  ucwords(strtolower($rows->FEATURE_ID));
		      $city['ocd']				=  strtolower($rows->OCDID);
		      $city['geoid']			=  $rows->GEOID;

		      $city['place_name'] 		=  ucwords(strtolower($rows->GOVERNMENT_NAME));
		      $city['political_desc'] 	=  ucwords(strtolower($rows->POLITICAL_DESCRIPTION));
		      $city['title']			=  ucwords(strtolower($rows->TITLE));
		      $city['address1']  		=  ucwords(strtolower($rows->ADDRESS1));
		      $city['address2']  		=  ucwords(strtolower($rows->ADDRESS2));
		      $city['city']		 		=  ucwords(strtolower($rows->CITY));
			  $city['service_discovery'] =  $rows->SERVICE_DISCOVERY;	
		      $city['zip']		 		=  $rows->ZIP;
		      $city['zip4']		 		=  $rows->ZIP4;
		      $city['state']	 		=  $rows->STATE_ABBR;
		      $city['place_url'] 	   =  $rows->WEB_ADDRESS;
		      $city['population'] 	   =  $rows->POPULATION_2005;
		      $city['county'] 		   =  ucwords(strtolower($rows->COUNTY_AREA_NAME));
		   }
		}		

		if(strlen($city['zip']) < 5) {
			$city['zip'] = str_pad($city['zip'], 5, "0", STR_PAD_LEFT);
		}
		
		if(!empty($city['zip4']) && strlen($city['zip4']) < 4) {
			$city['zip4'] = str_pad($city['zip4'], 4, "0", STR_PAD_LEFT);
		}
		
		return $city;
		
	}	
	
	
	

}

?>