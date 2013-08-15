<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sync extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// Load the Library
		$this->load->library(array('user', 'user_manager'));
				
	}

	function index()
	{
		$this->load->helper('url');
		

		// Construct the Jurisdiction Model
		//$this->load->model('democracymap_model', 'democracymap');
		//$jurisdiction = $this->democracymap->jurisdictions[0];




		$scraper_rate = $this->config->item('scraper_rate');
		$scheduled_scrapers = $this->scheduled_scrapers($scraper_rate);

		// foreach scheduled scrapers as scraper
		foreach ($scheduled_scrapers as $scraper) {
			$this->sync_data($scraper);
		}

	}
	
	function scheduled_scrapers($scraper_rate = null) {
		
		
		$due_date = gmdate("Y-m-d H:i:s", strtotime("-$scraper_rate hours"));

		// get overdue scrapers
		
		$this->db->select('id, scraperwiki_name, last_sync');		
		$this->db->where('last_sync < ', $due_date);
		
		$query = $this->db->get('sync_scheduler');
		
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	
	}


	function sync_data($scraper) {
		
		$this->load->helper('api');
		
		// get scraper metadata, tables, number of rows, last run date		
		$metadata = 'https://api.scraperwiki.com/api/1.0/scraper/getinfo?format=jsondict&name=' . $scraper['scraperwiki_name'] . '&version=-1';		
		$metadata = curl_to_json($metadata);
		
		if(!empty($metadata)) {

			$last_run = $metadata[0]['last_run'] . 'Z';

			// make sure it's run since we last pulled it
			if (date(strtotime($scraper['last_sync'])) < date(strtotime($last_run))) {
			
				
				if(!empty($metadata[0]['datasummary']['tables'])) {

					$tables = $metadata[0]['datasummary']['tables'];	

					// if(!empty($tables['jurisdictions'])) 
					
					if(!empty($tables['officials'])) {
						
						// Construct API call to scraper datastore with pagination
						
						// total
						$total = $tables['officials']['count'];
						$count = 1;
						$pagesize = 1000;
						
						while (($count * $pagesize) <= $total) {
							$offset = $count * $pagesize;							
							$url = 'https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=dmap2_city_representatives_-_california&query=select%20*%20from%20%60officials%60%20limit%20' . $offset . '%2C%20' . $pagesize;
							
							$officials = curl_to_json($url);
							
							if(!empty($officials)) {

								$ocdid = array();
								$skip = array();	

								foreach($officials as $official) {
									
									$gov_name = urlify($official['government_name']);
									
									// if this gov has already failed and been logged, skip it
									if(!empty($skip[$gov_name])) {
										continue;
									}										
									
									if(empty($ocdid[$gov_name])) {										
										// Get the OCDID for this jurisdiction
										$ocdid[$gov_name] = $this->determine_ocdid($official['government_name'], $official['government_level'], $official['address_region']);										

										if ($ocdid[$gov_name] === false) {
											$skip[$gov_name] = true;
											continue;
										}

									}
									
									
									if(!empty($ocdid[$gov_name])) {
										//
									} 
									
									
									
								}
								
							}
									
							$count++;
						}
						
					}
								

				} else {
					// scraper data not available

					// log this error	
				}					
				
						

			}			
			
	
		} else {
			// scraper not found
			
			// log this error
			
			return false;
		}


		
		
		//https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=dmap2_city_representatives_-_california
		//https://api.scraperwiki.com/api/1.0/scraper/getinfo?format=jsondict&name=dmap2_city_representatives_-_california&version=-1
		

		// make sure it's run since we last pulled it
		
		// if there's a jurisdiction table start there
		
		// officials table
		
		// paginate - 1000 at a time?
		
		
		
	}
	
	
	
	
}
