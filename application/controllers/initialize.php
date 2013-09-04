<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Initialize extends CI_Controller {

	function __construct()
	{
		parent::__construct();
				
	}

	function index($level = null)
	{
		
		// Determine the environment we're run from for debugging/output 
		if (php_sapi_name() == 'cli') {   
			if (isset($_SERVER['TERM'])) {   
				$this->environment = 'terminal';  
			} else {   
				$this->environment = 'cron';
			}   
		} else { 
			$this->environment = 'server';
		}
		
		
		
		// Specify which level of jursidictions we want to initialize - basically either municipal or county
		if(empty($level)) {
			$query_params = $this->input->get();
			
			if (!empty($query_params['level'])) {
				$level = $query_params['level'];
			} else {
				$level = 'municipal';
			}
		}
			
		$this->load->helper('url');

		// if config != initialize mode then redirect otherwise run through init script. 
		// todo password protect this process
		
		if(!$this->config->item('initialize_active')) {
			redirect('welcome');				
		}

		
		
		// get GID data for states, counties, municipalities
		// filter into scraped_jursidictions table

		$sql = "SELECT COUNT( * ) AS count FROM $level";

		$query = $this->db->query($sql);			
		if ($query->num_rows() > 0) {
		   $row = $query->row(); 		
		   $total_rows = $row->count;
		}


		$sources = array(array('description' => 'US Census 2007 Governments Integrated Directory', 'url' => 'http://harvester.census.gov/gid/gid_07/options.html'));
		$sources = json_encode($sources);

		$jurisdiction_query ="SELECT 
						ocd.OCDID as ocd_id,
						ocd.GEOID as uid,
						$level.GOVERNMENT_NAME AS name,
						\"government\" as type,
						$level.POLITICAL_DESCRIPTION AS type_name,
						\"$level\" as level,
						$level.POLITICAL_DESCRIPTION AS level_name,
						$level.CITY AS address_locality, 
						$level.TITLE AS address_name,
						$level.ADDRESS1 AS address_1,
						$level.ADDRESS2 AS address_2,
						\"USA\" AS address_country,
						$level.STATE_ABBR AS address_region,
						$level.ZIP + \"-\" + $level.ZIP4 AS address_postcode,
						$level.WEB_ADDRESS AS url 
						FROM $level 
						JOIN ocd ON $level.GEOID = ocd.GEOID 
						ORDER BY GOVERNMENT_NAME, STATE_ABBR DESC";



	
		$query_count = 0;
		$limit = 100;
		$offset = 0;					
		
		//$total_rows = 2;
		
		while($offset < $total_rows) {
			
			$output = array();
			
			if($query_count > 0) {
				$offset = $query_count * $limit;
				$complete_query = $jurisdiction_query . " LIMIT $offset, $limit ";				
			} else {
				$complete_query = $jurisdiction_query . " LIMIT $limit";
			}
			
			$query = $this->db->query($complete_query);			

			if ($query->num_rows() > 0)
			{
			   foreach ($query->result() as $row)
			   {
				
				$row = (array) $row;
				 
				$row['name'] 			   	= ucwords(strtolower($row['name']));	
				$row['type_name']        	= ucwords(strtolower($row['type_name']));
				$row['level_name']       	= ucwords(strtolower($row['level_name']));
				$row['address_locality'] 	= ucwords(strtolower($row['address_locality'])); 
				$row['address_name']     	= ucwords(strtolower($row['address_name']));
				$row['address_1']        	= ucwords(strtolower($row['address_1']));
				$row['address_2']	       	= ucwords(strtolower($row['address_2']));				
				$row['sources']				= $sources;
				$row['last_updated']		= gmdate("Y-m-d H:i:s");
				$row['other_data']			= null;
				$row['conflicting_data']	= null;				
				
				// this should be done with batch inserts instead - $this->db->insert_batch();
				//$this->db->insert('scraped_jurisdictions', $row); 
				 
				$output[] = $row;
				
			   }
			}
			
					
			// output for debugging
			$max = count($output) - 1;
			$description = "Inserting {$output[0]['name']} {$output[0]['address_region']} thru {$output[$max]['name']} {$output[$max]['address_region']}";
			
			if ($this->environment == 'terminal') {
				echo $description . PHP_EOL;
			}			
			
			
			// insert batch
			$this->db->insert_batch('scraped_jurisdictions', $output); 

			
			$query_count++;
		}



	}
	
	
	


function add_urls(){
	
	$this->load->model('geocoder_model', 'geocoder');	
	$this->load->helper('api');			
	
	$states = $this->geocoder->state_abbr();
	
	
	
	// http://api.sba.gov/geodata/city_links_for_state_of/al.json
	
 	$count = 1;

	// for states as state
	foreach ($states as $abbr => $state) {
		
		//if($count > 1) exit;
		
		$url = 'http://api.sba.gov/geodata/city_links_for_state_of/' . $abbr . '.json';
		
		$cities = curl_to_json($url);
		
		foreach ($cities as $city) {
			
			$city_name 	= $city['name'];
			$city_url 	= $city['url'];
			
			
			$this->db->select('ocd_id, url, sources, conflicting_data');		
			$this->db->where('name', $city_name);
			$this->db->where('address_region', $abbr);			
			
			$query = $this->db->get('scraped_jurisdictions');


			if ($query->num_rows() > 0) {
			   	$row = $query->row(); 		
				
				// To add new sources
				$sources = json_decode($row->sources);							
				
		
				// If there's already a URL listed
				if(!empty($row->url)) {
					
					// ensure this isn't roughly the same URL already being used
					if (($row->url !== $city_url && strpos($row->url, $city_url) === false && strpos($city_url, $row->url) === false)) {
						
						
						// Add new URL
						$data = array('url' => $city_url);
						$this->db->where('ocd_id', $row->ocd_id);
						$this->db->update('scraped_jurisdictions', $data);						
						
						if(!empty($row->conflicting_data)) {
							$conflicting_data = json_decode($row->conflicting_data);							
						} else {
							$conflicting_data = array();
						}
						
						// add new source							
						$conflicting_data[] = array('field' => 'url', 'value' => $city_url, 'source' => 'http://api.sba.gov/doc/geodata.html');
						
						// add old source							
						$conflicting_data[] = array('field' => 'url', 'value' => $row->url, 'source' => null);
																																									
						$conflicting_data = json_encode($conflicting_data);			
						
						// add new sources
						$sources[] = array('description' => 'SBA U.S. City & County Web Data API', 'url' => 'http://api.sba.gov/doc/geodata.html');								
						$sources = json_encode($sources);
						
						// Add both URLs to conflicting_data 
						$data = array('url' => $city_url, 'conflicting_data' => $conflicting_data, 'sources' => $sources);
						$this->db->where('ocd_id', $row->ocd_id);
						$this->db->update('scraped_jurisdictions', $data);						
						
						
					} 
					
					
				} else {
					
					// add new sources
					$sources[] = array('description' => 'SBA U.S. City & County Web Data API', 'url' => 'http://api.sba.gov/doc/geodata.html');					
					$sources = json_encode($sources); 
					
					// Add this URL
					$data = array('url' => $city_url, 'sources' => $sources);
					$this->db->where('ocd_id', $row->ocd_id);
					$this->db->update('scraped_jurisdictions', $data);					
				}
			
			
	
				
				
			} else {
				// log this
				
				$data = array (
								'source' => 'http://api.sba.gov/doc/geodata.html',
								'type' => 'jurisdiction', 
								'description' => "SBA API for city URLs didn't find match for $city_name, $abbr",
								'timestamp' => gmdate("Y-m-d H:i:s")								
								);
				
				$this->db->insert('sync_log', $data); 
				
				
			}	
			
			
			
		}	
		
	    //header('Content-type: application/json');
	    //print json_encode($cities);		
		//exit;
		
		$count++;
		
	}
	
	
}
		
		

	
	
}
