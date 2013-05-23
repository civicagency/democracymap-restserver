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

		// Save a Jurisdiction

		// collect post data
		// Validation
		// insert into db
		
		if($this->input->post('name', TRUE)) {
			
			$post = $this->input->post();
						

			$posted_jurisdiction = $post;

			// adjust for current datamodel mismatch with db. TODO: Fix this
			if(!empty($post['address_city']))	$posted_jurisdiction['address_locality'] 	= $post['address_city']; unset($posted_jurisdiction['address_city']);
			if(!empty($post['address_state']))  $posted_jurisdiction['address_region'] 		= $post['address_state']; unset($posted_jurisdiction['address_state']);
			if(!empty($post['address_zip']))  $posted_jurisdiction['address_postcode'] 		= $post['address_zip']; unset($posted_jurisdiction['address_zip']);
			$posted_jurisdiction['address_country'] 	= 'US';
			
			//$posted_jurisdiction['meta_internal_id'] 	= '';			
			$posted_jurisdiction['meta_validated_source'] 	= 'false';			
			$posted_jurisdiction['uid'] 					= 'ocd:temp';			
			$posted_jurisdiction['type'] 					= 'government';			
			$posted_jurisdiction['level'] 					= 'municipal';									
			$posted_jurisdiction['last_updated']			= gmdate("Y-m-d H:i:s");
			
																								
			

			if ( $this->db->insert('jurisdictions', $posted_jurisdiction) ) {		
				$this->session->set_flashdata('success_message', 'You successfully updated that jurisdiction');
				redirect('dashboard');				
			}
			else {
				$this->session->set_flashdata('error_message', 'There was an error updating that jurisdiction');
				redirect('dashboard');
			}			
			

		}
		

		
		// redirect to dashboard with success message. 
	
		// Edit a Jurisdiction
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