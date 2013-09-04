<?php
require APPPATH.'/libraries/REST_Controller.php';

class Represents extends REST_Controller {

	public $ttl 		= 604800;

	function __construct()
	{
		parent::__construct();
		
		$this->load->model('geocoder_model', 'geocoder');	
		$this->load->model('democracymap_model', 'democracymap');
				
	    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
	}

	public function index_get()	{
		
		$query_params = $this->input->get();
		


		if(!empty($query_params)) {
		
		
			// Geocode our address (if needed)			
			if(!empty($query_params['location'])) {
				if ($check_input = $this->geocoder->test_latlong($query_params['location'])) {

					$this->geocoder->latitude   = $check_input['latitude'];
					$this->geocoder->longitude  = $check_input['longitude'];					
					$this->geocoder->latlong	= $this->geocoder->latitude . " " . $this->geocoder->longitude;									

				}
				else {

					if ($location 	= $this->geocoder->geocode(urlencode($query_params['location']))) {
						$this->geocoder->metadata 			= $location;
						
						$this->geocoder->latitude   = $location['latitude'];
						$this->geocoder->longitude  = $location['longitude'];								
						$this->geocoder->latlong	= $this->geocoder->latitude . " " . $this->geocoder->longitude;									
					}			

				} 

				if (!empty($this->geocoder->latlong)) {
					
					// Get data from each level
					$city_geo = $this->geocoder->city_geo($this->geocoder->latitude, $this->geocoder->longitude);
					$ocd_ids[] = $this->geocoder->ocd_from_geoid($city_geo['GEOID']);

					$county_geo = $this->geocoder->county_geo($this->geocoder->latitude, $this->geocoder->longitude);
					$ocd_ids[] = $this->geocoder->ocd_from_geoid($county_geo['GEOID']);					
					
				} else {
					$data_errors[] = 'The location could not be geocoded';
				}		
				
				
			
					
			}
			
		
		
		
		
		
		
		
		
			// grab the jurisdiction model
			$jurisdiction_model = $this->democracymap->juris;		
			
			// grab the officials model
			$official_model = $this->democracymap->official;			
			
			$meta_model = array("fields" => null, "limit" => null, "page" => null, "location" => null);

			// limit to the fields that can be used for an sql where clause (based on officials model)
			$where_params = array_intersect_key($query_params, $jurisdiction_model);

			// extract remaining fields
			$diff = array_diff_key($query_params, $jurisdiction_model);

			if(!empty($diff)) {			
				$meta_params = array_intersect_key($diff, $meta_model);
			}		
			
		}
		

		
		
		// sanitize the requested return fields
		if(!empty($query_params['fields'])) {

			$return_fields = explode(',', $query_params['fields']);
					
			if(!empty($return_fields)) {
				foreach ($return_fields as $key => $return_field) {	
					
					$return_field = trim($return_field);
									
					if (!array_key_exists($return_field, $jurisdiction_model)) {
						unset($return_fields[$key]);
					} else {
						$return_fields_sanitized[] = $return_field;  // this is apply the trim function
					}		
				}
			}
		}
		

		// Construct the select clause
		if(!empty($return_fields_sanitized)) {
			
			// if we expect to get officials, we have to include this for that query
			if (array_search('ocd_id', $return_fields_sanitized) === false) {
				$return_fields_sanitized[] = 'ocd_id';
			}

			$select_fields = '';
      
			foreach ($return_fields_sanitized as $select_field) {
				$select_fields .= $select_field . ', ';
			}
      
			$select_fields = trim($select_fields);			
		

			
			$this->db->select($select_fields);	
		}
        
		
		
		// if we geocoded, base the query on that
		if (!empty($ocd_ids)) {
			
			$count = 0;
			foreach ($ocd_ids as $ocd_id) {
				
				if ($count == 0) {
					$this->db->where('ocd_id', $ocd_id);
				} else {
					$this->db->or_where('ocd_id', $ocd_id);					
				}

				$count++;
			}
			
		} else {
		
			// Otherwise construct the where clause with provided parameters		
			if(!empty($where_params)) {
				$this->db->like($where_params);
			}		
			
		}
		

        
		// Construct the pagination	
		
		$default_limit = 10; // this should be in configuration
		$max_limit = 100; // this should be in configuration
		
		if(!empty($meta_params['limit'])) {				
			
			$limit = ($meta_params['limit'] < $max_limit) ? $meta_params['limit'] : $max_limit;					
			$this->db->limit($limit);		
		} else {
			$this->db->limit($default_limit);	
		}
        
		
		
		$query = $this->db->get('scraped_jurisdictions');
        
		if ($query->num_rows() > 0) {
			
			$response = $query->result_array();
			
			
			foreach($response as $row) {

				// convert json
				$row = $this->convert_json($row);
				
				// Get officials matching this ocd_id
				if($officials = $this->get_officials($row['ocd_id'])) {
					$row['officials'] = $officials;
				} else {
					$row['officials'] = null;
				}
				
				
				$jurisdictions[] = $row;						
			}
			
			
			$this->response($jurisdictions, 200);	
		} else {			
			$response = array("error" => "no results found");
			$this->response($response, 404);				
		}
		
		
		// For debugging
		
		// $data = array();		
		// $data['where_params'] = $where_params;
		// $data['meta_params'] = $meta_params;		
		// if(!empty($return_fields_sanitized)) 	$data['return_fields'] = $return_fields_sanitized;
        // 
		// if(!empty($data)) {
		// 	$this->response($data, 200);			
		// }		
		
	
		
	}
	
function convert_json($row) {
	
	if(!empty($row['social_media'])) 		$row['social_media'] = json_decode($row['social_media']);
	if(!empty($row['other_data'])) 			$row['other_data'] = json_decode($row['other_data']);
	if(!empty($row['conflicting_data'])) 	$row['conflicting_data'] = json_decode($row['conflicting_data']);
	if(!empty($row['sources'])) 			$row['sources'] = json_decode($row['sources']);	
	
	return $row;
	
}	
	
function get_officials($ocd_id) {

	$query = $this->db->get_where('scraped_officials', array('meta_ocd_id' => $ocd_id));
	$result = $query->result_array();	
	
	
	if ($result) {
		
		foreach($result as $row) {
			$row = $this->convert_json($row);			
			
			$rows[] = $row;			
		}
		
		return $rows;
	} else {
		return false;
	}
}


	
}