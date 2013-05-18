<?php

/**
 * User Controller
 * This controller fully demonstrates the user class.
 *
 * @package User
 * @author Waldir Bertazzi Junior
 * @link http://waldir.org/
 **/
class Account extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		// Load the Library
		$this->load->library(array('user', 'user_manager'));
        $this->load->helper('url');

	}
	
	function index()
	{		
		// If user is already logged in, send it to main
		$this->user->on_valid_session('account/private_page');
		
		// Loads the login view
		$this->load->view('login');
	}
	
	function private_page(){
		// if user tries to direct access it will be sent to index
		$this->user->on_invalid_session('login');
		
		// ... else he will view home
		$this->load->view('docs');
	}
	
	function validate()
	{
		// Receives the login data
		$login = $this->input->post('login');
		$password = $this->input->post('password');
		
		/* 
		 * Validates the user input
		 * The user->login returns true on success or false on fail.
		 * It also creates the user session.
		*/
		if($this->user->login($login, $password)){
			// Success
			redirect('dashboard');
		} else {
			// Oh, holdon sir.
			$this->session->set_flashdata('error_message', 'Invalid login or password.');
			redirect('login');
		}
	}
	
	

	
	function register() {
		
		// Redirect to dashboard if they're already logged in
		if ($this->session->userdata('login')) {
			redirect('dashboard');		
		}		
		
		if($this->input->post('email', TRUE)) {
			$user_form = $this->input->post();
			
			$fullname 		= $user_form['name'];
			$login 			= $user_form['email'];
			$password 		= $user_form['password'];
			$active 		= true;
			$permissions 	= array(1, 3);
						
			//Check to see if all fields are complete
		
			//Check to see if this user is already in the system
			
			// If they're not add them				 	
			
			$new_user_id 	= $this->user_manager->save_user($fullname, $login, $password, $active, $permissions);						
			
			// Success
			if($new_user_id) {
				$this->session->set_flashdata('success_message', 'User added');				
			}
			
			$this->load->view('register', $data);

				
		} 
		
		else {
			$this->session->set_flashdata('error_message', 'Error submitting form');				
			
			$this->load->view('register');					
		}		
		
	}
	
	
	function dashboard() {
		
		// load user data
		// anything to load?
		$data = null;
		
		
		// load view
		if($this->user->validate_session()) {		
			$this->load->view('dashboard', $data);		
		} 
		else {
			$this->session->set_flashdata('error_message', 'You must be logged in to view this.');			
			$this->load->view('login');
		}
		
		
	}	
	
	
	
	// Simple logout function
	function logout()
	{
		// Remove user session.
		$this->user->destroy_user();
		
		// Bye, thanks! :)
		$this->session->set_flashdata('success_message', 'You are now logged out.');
		redirect('login');
	}
}
?>
