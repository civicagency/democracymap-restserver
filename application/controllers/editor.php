<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Editor extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// Load the Library
		$this->load->library(array('user', 'user_manager'));
				
	}

	function index()
	{
		$this->load->helper('url');
		
		
		redirect('dashboard');	
	}
	
	
	function jurisdiction($jurisdiction_id = null) {

		// Must be logged in
		if(!$this->user->validate_session()) {		
			$this->session->set_flashdata('error_message', 'You must be logged in to view this.');			
			redirect('login');			
		}


		// TODO Check to see if the person is logged in
	
		if ($jurisdiction_id) {
			// TODO: load existing baseline data for this jurisdiction id
			$data = array();			
		} else {
			// Construct the Jurisdiction Model
			$this->load->model('democracymap_model', 'democracymap');
			$jurisdiction = $this->democracymap->jurisdictions[0];
			
			// TODO sanitize non-editable fields
			$jurisdiction = $this->sanitize_jurisdiction($jurisdiction);
			
			$data = array('jurisdiction' => $jurisdiction);					
		}
		
		$this->load->view('edit_jurisdiction', $data);		

	}	
		
		
	function sanitize_jurisdiction($jurisdiction) {
		$this->load->model('democracymap_model', 'democracymap');
		$protected = $this->democracymap->protected_fields;
		
		foreach($protected as $field) {
			unset($jurisdiction[$field]);
		}
				
		return $jurisdiction;
	}
	
	function official($official_id = null) {

		// TODO Check to see if the person is logged in
	
		if ($official_id) {
			// TODO: load existing baseline data for this jurisdiction id
			$data = array();			
		} else {
			// Construct the Jurisdiction Model
			$this->load->model('democracymap_model', 'democracymap');
			$official = $this->democracymap->officials[0];
			
			// TODO sanitize non-editable fields
			//$official = $this->sanitize_official($official);
			
			$data = array('official' => $official);					
		}
		
		$this->load->view('edit_official', $data);		

	}	
	
	
	
	
	
	
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */