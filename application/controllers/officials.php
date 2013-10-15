<?php
require APPPATH.'/libraries/REST_Controller.php';

class Officials extends REST_Controller {

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
		
			// grab the officials model
			$official_model = $this->democracymap->official;		
			$meta_model = array("fields" => null, "limit" => null, "page" => null);

			// limit to the fields that can be used for an sql where clause (based on officials model)
			$where_params = array_intersect_key($query_params, $official_model);

			// extract remaining fields
			$diff = array_diff_key($query_params, $official_model);

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
									
					if (!array_key_exists($return_field, $official_model)) {
						unset($return_fields[$key]);
					} else {
						$return_fields_sanitized[] = $return_field;  // this is apply the trim function
					}		
				}
			}
		}
		

		// Construct the select clause
		if(!empty($return_fields_sanitized)) {
			
			$select_fields = '';
      
			foreach ($return_fields_sanitized as $select_field) {
				$select_fields .= $select_field . ', ';
			}
      
			$select_fields = trim($select_fields);			
		

			
			$this->db->select($select_fields);	
		}
        
		// Construct the where clause		
		if(!empty($where_params)) {
			$this->db->like($where_params);
		}
        
		// Construct the pagination	
		
		$default_limit = 10; // this should be in configuration
		$max_limit = 100; // this should be in configuration
				
		if(!empty($meta_params['limit'])) {							
			$limit = ($meta_params['limit'] < $max_limit) ? $meta_params['limit'] : $max_limit;					
			$offset = (!empty($meta_params['page'])) ? $limit * $meta_params['page'] : 0;			
			$this->db->limit($limit, $offset);		
		} else {			
			$offset = (!empty($meta_params['page'])) ? $default_limit * $meta_params['page'] : 0;									
			$this->db->limit($default_limit, $offset);	
		}
        
		
		
		$query = $this->db->get('scraped_officials');
        
		if ($query->num_rows() > 0) {
			
			$response = $query->result_array();
			
			foreach($response as $row) {
				if(!empty($row['social_media'])) 		$row['social_media'] = json_decode($row['social_media']);
				if(!empty($row['other_data'])) 			$row['other_data'] = json_decode($row['other_data']);
				if(!empty($row['conflicting_data'])) 	$row['conflicting_data'] = json_decode($row['conflicting_data']);
				if(!empty($row['sources'])) 			$row['sources'] = json_decode($row['sources']);		
				
				$officials[] = $row;						
			}
			
			
			$this->response($officials, 200);	
		} else {			
			$response = array("error" => "no results found");
			$this->response($response, 404);				
		}
		
		
		$data = array();
		
		$data['where_params'] = $where_params;
		$data['meta_params'] = $meta_params;		
		if(!empty($return_fields_sanitized)) 	$data['return_fields'] = $return_fields_sanitized;
        
		if(!empty($data)) {
			$this->response($data, 200);			
		}		
		
		
		
		
		
		
		
		
		
	}
	
	
}