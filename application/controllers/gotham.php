<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gotham extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->helper('url');
		
		$data = array();
		
		$location = $this->input->get('location', TRUE);
		
		if(!empty($location)){
			
			$jurisdictions = $this->get_jurisdictions($location);
						 
			if (!empty($jurisdictions)) {

				// Custom ordering for GG	
				$jurisdictions = array_reverse($jurisdictions['jurisdictions']);

				foreach ($jurisdictions as $jurisdiction) {			
					$level = ($jurisdiction['level'] == 'sub-municipal') ? 'municipal' : $jurisdiction['level'];			
					$temp_order[$level][] = $jurisdiction;
				}

				$temp_order = array_reverse($temp_order);
				
				foreach ($temp_order as $key => $region) {
					
					$region = array_reverse($region);
					$executive = array_pop($region);
					
					array_unshift($region, $executive);
					
					$custom_order[$key] = $region;
				
				}
				
				$data['jurisdictions'] = $custom_order;
			}
			else {
				$data['jurisdictions'] = null;								
			}

			
		}
		
		
			
		
		// See if we have google analytics tracking code
		if($this->config->item('ganalytics_id')) {
			$data['ganalytics_id'] = $this->config->item('ganalytics_id');
		}	
		

			
		
		$this->load->view('gotham', $data);
	}
	
	
	function get_jurisdictions($location) {

		$location = urlencode($location);

		$url = $this->config->item('democracymap_root') . "/context?location=" . $location . "&fullstack=true";
		
		$jurisdictions = $this->curl_to_json($url);

		return $jurisdictions;				

	}	
	
	function curl_to_json($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$data=curl_exec($ch);
		curl_close($ch);


		return json_decode($data, true);	

	}	
	
	
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */