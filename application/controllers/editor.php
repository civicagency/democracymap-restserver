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

		if(!$jurisdiction_id) {			
			$jurisdiction_id = $this->input->get('id', TRUE);			
		}

		// Must be logged in
		if(!$this->user->validate_session()) {		
			$this->session->set_flashdata('error_message', 'You must be logged in to view this.');			
			redirect('login');			
		}

		// Save a Jurisdiction

		// collect post data
		// Validation
		// insert into db
		
		// Add sanitized data back from cache
		// $this->get_jurisdiction($jurisdiction_id)
		// add real metadata rather than placeholder
		
	
		if($this->input->post('name', TRUE)) {
			
			$post = $this->input->post();
						

			// Get original details for jurisdiction
			$pre_jurisdiction = $this->get_jurisdiction($post['uid']);

			$posted_jurisdiction = $post;

			// adjust for current datamodel mismatch with db. TODO: Fix this
			if(!empty($post['address_city']))	$posted_jurisdiction['address_locality'] 	= $post['address_city']; unset($posted_jurisdiction['address_city']);
			if(!empty($post['address_state']))  $posted_jurisdiction['address_region'] 		= $post['address_state']; unset($posted_jurisdiction['address_state']);
			if(!empty($post['address_zip']))  $posted_jurisdiction['address_postcode'] 		= $post['address_zip']; unset($posted_jurisdiction['address_zip']);
			$posted_jurisdiction['address_country'] 	= 'US';
			
			//$posted_jurisdiction['meta_internal_id'] 	= '';	
					
			$posted_jurisdiction['meta_last_author_id'] 	= $this->user->get_login();			
			$posted_jurisdiction['meta_validated_source'] 	= 'false';	// this shouldn't be hardcoded		
			$posted_jurisdiction['type'] 					= $pre_jurisdiction['type'];	// this shouldn't be hardcoded				
			$posted_jurisdiction['level'] 					= $pre_jurisdiction['level']; // this shouldn't be hardcoded											
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
			
			$jurisdiction = $this->get_jurisdiction($jurisdiction_id);
			
			if($jurisdiction) {
				$jurisdiction = $this->sanitize_jurisdiction($jurisdiction);
				
				$metadata['uid'] = $jurisdiction_id;
					
				$data = array('jurisdiction' => $jurisdiction, 
							  'metadata' => $metadata);												
			} else {
				
				// Should set an error message somewhere
				$data = array();
			}

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
	
	
	
	
	function get_jurisdiction($jurisdiction_id) {
		
		$url = $this->config->item('democracymap_root') . '/context/ocd/?id=' . $jurisdiction_id;
		
		$this->load->helper('api');			
		
		$jurisdiction = curl_to_json($url);
	
		
		if ($jurisdiction == 'Not found') {
			return false;		
		} else {
			return $jurisdiction;
		}
	
	}
	
	
	function get_official($jurisdiction_id, $official_name) {
		
		$url = $this->config->item('democracymap_root') . '/context/ocd/?id=' . $jurisdiction_id;
		
		$this->load->helper('api');			
		
		$jurisdiction = curl_to_json($url);
		$officials = $jurisdiction['elected_office'];
		
		foreach ($officials as $official) {
			if ($official['name_full'] == $official_name) {
				$official_selected = $official;
				break;
			}
		}
	
//		var_dump($official_selected); exit;
		
		
		if (!empty($official_selected)) {
			return $official_selected;		
		} else {
			return false;
		}
	
	}	
	
	
	
	function sanitize_official($official) {
		$this->load->model('democracymap_model', 'democracymap');
		$protected = $this->democracymap->protected_fields_officials;
		
		foreach($protected as $field) {
			unset($official[$field]);
		}
				
		return $official;
	}	
	
	
	
	function official($jurisdiction_id = null, $official_name = null) {

		if(!$jurisdiction_id && !$official_name) {			
			$jurisdiction_id = $this->input->get('id', TRUE);			
			$official_name   = $this->input->get('official_name', TRUE);						
		}

		// Must be logged in
		if(!$this->user->validate_session()) {		
			$this->session->set_flashdata('error_message', 'You must be logged in to view this.');			
			redirect('login');			
		}

		// Save a Jurisdiction

		// collect post data
		// Validation
		// insert into db
		
		// Add sanitized data back from cache
		// $this->get_jurisdiction($jurisdiction_id)
		// add real metadata rather than placeholder
		
	
		if($this->input->post('name', TRUE)) {
			
			$post = $this->input->post();
						
			// Get original details for jurisdiction
			$pre_official = $this->get_jurisdiction($post['uid'], $post['official_name']);

			$posted_official = $post;

			// adjust for current datamodel mismatch with db. TODO: Fix this
			if(!empty($post['address_city']))	$posted_jurisdiction['address_locality'] 	= $post['address_city']; unset($posted_jurisdiction['address_city']);
			if(!empty($post['address_state']))  $posted_jurisdiction['address_region'] 		= $post['address_state']; unset($posted_jurisdiction['address_state']);
			if(!empty($post['address_zip']))  $posted_jurisdiction['address_postcode'] 		= $post['address_zip']; unset($posted_jurisdiction['address_zip']);
			$posted_jurisdiction['address_country'] 	= 'US';
			
			//$posted_jurisdiction['meta_internal_id'] 	= '';	
					
			$posted_jurisdiction['meta_last_author_id'] 	= $this->user->get_login();			
			$posted_jurisdiction['meta_validated_source'] 	= 'false';	// this shouldn't be hardcoded		
			$posted_jurisdiction['type'] 					= $pre_jurisdiction['type'];	// this shouldn't be hardcoded				
			$posted_jurisdiction['level'] 					= $pre_jurisdiction['level']; // this shouldn't be hardcoded											
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
		if ($jurisdiction_id && $official_name) {
			
			$official = $this->get_official($jurisdiction_id, $official_name);
			
			if($official) {
				$official = $this->sanitize_official($official);
				
				$metadata['uid'] = $jurisdiction_id;
					
				$data = array('official' => $official, 
							  'metadata' => $metadata);												
			} else {
				
				// Should set an error message somewhere
				$data = array();
			}

		} else {
			// Construct the Jurisdiction Model
			$this->load->model('democracymap_model', 'democracymap');
			$official = $this->democracymap->officials[0];
			
			// TODO sanitize non-editable fields
			$official = $this->sanitize_jurisdiction($official);
			
			$data = array('official' => $official);					
		}
		
		$this->load->view('edit_official', $data);		

	}	
	
	
	
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */