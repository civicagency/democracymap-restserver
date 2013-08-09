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


	}
	

		
		

	
	
}
