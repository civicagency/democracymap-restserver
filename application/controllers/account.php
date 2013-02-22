<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		//$this->load->helper('url');
		redirect('account/register');		
		
		//$this->load->view('demo', $data);
	}
	
	
	function register() {
		
		if($this->input->post('email', TRUE)) {
			$user_form = $this->input->post();
			
			
			// $user_form['password']
			// $user_form['name'] 		
			// $user_form['role'] 	
			// $user_form['email'] 							
		
			//Check to see if all fields are complete
		
			//Check to see if this user is already in the system
			
			// If they're not add them				 	
			
				$this->new_user($user_form);
				
				// load success message
				$data['messages']['success'] = 'User added';
				$this->load->view('register', $data);
			
			if(isset($data['messages']['error'])) {
				$this->load->view('register', $data);				
			}
			
				
		} 
		
		else {
			$this->load->view('register');					
		}		
		
	}
	
	
	
	function login() {
		
		// TODO make sure they provided both username and password
		
		
		// Assume they provided both email and pass, authenticate it
		if($this->check_user($this->input->post('email', TRUE), $this->input->post('password', TRUE))) {
							
							
				// Load details of account
				$user['email'] = $this->input->post('email', TRUE);
				//$user['name'] = ;
				
				// Set session
				$this->session->set_userdata($user); // email retreivable with $this->session->userdata('email')
								
				$data['user']['email'] = $user['email'];											
							
				// load success message
				$data['messages']['success'] = 'Logged in';
				$this->load->view('login', $data);
				
		} 
		
		else {
			$data['messages']['success'] = 'Login failed';
			$this->load->view('login');					
		}		
		
	}	

	
	
	function user_update() {

		if($this->input->post('email', TRUE)) {
			
			$user = $this->check_user($this->input->post('email', TRUE), $this->input->post('password', TRUE));

			// Make sure editor has admin privileges
			if ($user) {			
				
				$user = $this->input->post();
				$user['password'] = $user['new_password'];
				unset($user['new_password']);

				if($this->update_user($user)) {
					// load success message
					$data['messages']['success'] = 'Password updated';
					$this->load->view('user_update', $data);					
				} else {				
					// updating user failed
					$data['messages']['error'] = 'Updating user failed';											
				}

			} else {				
				// submitted user/pass didn't validate
				$data['messages']['error'] = "Email or password didn't validate";		
			}
			
			// submitted fields didn't validate			
			if (empty($data['messages'])) {
				$data['messages']['error'] = "Fields didn't validate";
			}
			
			if(isset($data['messages']['error'])) {
				$this->load->view('user_update', $data);
			}											

		}
		
		else {
			$this->load->view('user_update');
		}			
		
	}
	
	
	function update_user($user) {
	
		$user = $this->pass_to_hash($user);
		
		$this->db->where('email', $user['email']);		
		if($this->db->update('user', $user)) {
			return true;
		} else {
			return false;
		}
	
		

		
	}
	
	
	function new_user($user) {

		// $user['name'] = 'John Doe';
		// $user['email'] = 'me@john.com';
		// $user['password'] = 'password';
		// $user['role'] = 'admin';

		$user = $this->pass_to_hash($user);
		
		$this->db->insert('user', $user);		
		
	}
	
	
	function pass_to_hash($user) {
		// borrowed from: http://alias.io/2010/01/store-passwords-safely-with-php-and-mysql/

		// Create a 256 bit (64 characters) long random salt
		// Let's add 'something random' and the username
		// to the salt as well for added security
		$salt = hash('sha256', uniqid(mt_rand(), true) . 'sandy school finder abracadabra' . strtolower($user['email']));

		// Prefix the password with the salt
		$hash = $salt . $user['password'];

		// Hash the salted password a bunch of times
		for ( $i = 0; $i < 100000; $i ++ ) {
		  $hash = hash('sha256', $hash);
		}

		// Prefix the hash with the salt so we can find it back later
		$hash = $salt . $hash;	
		
		$user['hash'] = $hash;
		
		//remove original password from array
		unset($user['password']);
		
		return $user;		
	}
	
	
	function check_user($email, $password) {

		$search = array('email' => $email);
		$query = $this->db->get_where('user', $search);

		if ($query->num_rows() > 0) {
		   foreach ($query->result() as $rows)  {	

				$user['id']							= $rows->id		 ;
				$user['name']							= $rows->name		 ;
				$user['email']							= $rows->email		 ;
				$user['hash']							= $rows->hash		 ;
				$user['role']							= $rows->role		 ;				


		   }
		
			// The first 64 characters of the hash is the salt
			$salt = substr($user['hash'], 0, 64);

			$hash = $salt . $password;

			// Hash the password as we did before
			for ( $i = 0; $i < 100000; $i ++ ) {
			  $hash = hash('sha256', $hash);
			}

			$hash = $salt . $hash;

			if ( $hash == $user['hash'] ) {
				return $user;
			} else {
				return false;
			}		
		
		
		} else {
			return false;
		}
		
		
	}
	
	
	function logout() {
		$this->session->sess_destroy();
		$this->load->view('logout_view');
	}	
	

	
	
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */