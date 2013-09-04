<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class democracymap_model extends CI_Model {


	//var $pagination	 		= NULL;
	var $jurisdictions 		= array();


	var $protected_field	= null;


	public function __construct(){
		parent::__construct();
		
		// construct officials components first
		//$office_social_media			= array($this->office_social_media());
		//$office_metadata				= array($this->office_metadata());		
		
		
		
		// Then attach the officials to the jurisdiction			                    	
		//$officials			= array($this->officials());
		//$social_media			= array($this->social_media());
		//$service_discovery 	= array($this->service_discovery());
		//$metadata				= array($this->metadata());		
		
		//$this->jurisdictions	= array($this->jurisdiction($metadata, $social_media, $officials, $service_discovery));
			
		$this->protected_fields				= $this->restricted();	
		$this->protected_fields_officials	= $this->restricted_officials();		
			
		$this->jurisdictions				= array($this->jurisdiction());
		$this->jurisdiction					= $this->jurisdiction();
		
		$this->juris						= $this->jurisdiction_new(); // TODO: temporary until we refactor context code	
				
				
		$this->officials					= array($this->official());		
		$this->official						= $this->official_new(); // TODO: temporary until we refactor context code

	}
	
	public function jurisdiction($metadata = null, $social_media = null, $officials = null, $service_discovery = null) {
		
		$jurisdiction = array(
			'type' 		  			=> NULL,
			'type_name' 	  		=> NULL,	  
			'level' 		  		=> NULL,	  	
			'level_name' 			=> NULL,	  	  	
			'name' 		  			=> NULL,	  	
			'id' 					=> NULL,	  	  	
			'url' 		  			=> NULL,	  	
			'url_contact'   		=> NULL,	  	
			'email' 		  		=> NULL,	  	
			'phone' 		  		=> NULL,	  	
			'address_name'   		=> NULL,	  	
			'address_1' 	  		=> NULL,	  	
			'address_2' 	  		=> NULL,	  	
			'address_city'  		=> NULL,  	
			'address_state' 		=> NULL,	  	
			'address_zip'    		=> NULL,	  	
			'last_updated'   		=> NULL,	  	
			'metadata'				=> $metadata,
			'social_media'          => $social_media,
			'elected_office'        => $officials,
			'service_discovery'     => $service_discovery
		);

		return $jurisdiction;
		
	}
	
	public function jurisdiction_new($other_data = null, $social_media = null, $service_discovery = null) {
		
		$jurisdiction = array(
			'ocd_id'				=> NULL,
			'uid'					=> NULL,
			'type' 		  			=> NULL,
			'type_name' 	  		=> NULL,	  
			'level' 		  		=> NULL,	  	
			'level_name' 			=> NULL,	  	  	
			'name' 		  			=> NULL,	  	
			'id' 					=> NULL,	  	  	
			'url' 		  			=> NULL,	  	
			'url_contact'   		=> NULL,	  	
			'email' 		  		=> NULL,	  	
			'phone' 		  		=> NULL,	  	
			'address_name'   		=> NULL,	  	
			'address_1' 	  		=> NULL,	  	
			'address_2' 	  		=> NULL,	  				
			'address_locality' 		=> NULL,         
			'address_region' 		=> NULL,        
			'address_postcode' 		=> NULL,   
			'address_country' 		=> NULL,
			'service_discovery'     => $service_discovery,
			'last_updated'   		=> NULL,	  	
			'social_media' 			=> $social_media, 
			'other_data' 			=> $other_data, 
			'conflicting_data'		=> NULL, 
			'sources'				=> NULL
		);

		return $jurisdiction;
		
	}	
	
	
	
	// ########################################################
	// Starting with some empty scaffolding 
	// ########################################################
	
	// non-editable fields
	public function restricted() {
		
		$restricted = array('type', 'type_name', 'level_name', 'level', 'id', 'last_updated', 'metadata', 'social_media', 'elected_office', 'service_discovery');
		
		return $restricted;
		
	}		
		
		
	// non-editable fields
	public function restricted_officials() {
		
		$restricted = array('type','last_updated');
		
		return $restricted;
		
	}		
		
		
	public function metadata() {
		
		$metadata = null; //array();
		
		return $metadata;
		
	}
	
	public function social_media() {
		
		$social_media = array(			
	    	'type'                  => NULL,
	    	'description'           => NULL,
	    	'username'              => NULL,
	    	'url'                   => NULL,
	    	'last_updated'          => NULL				
		);

		return $social_media;
		
	}	
	
	public function official() {
		
		$official = array(
			'type' 					=> NULL,                 		
			'title' 				=> NULL,                		
			'description' 			=> NULL,          		
			'name_given' 			=> NULL,           		
			'name_family' 			=> NULL,          			
			'name_full' 			=> NULL,            			
			'url' 					=> NULL,                  
			'url_photo' 			=> NULL,            
			'url_schedule' 			=> NULL,         
			'url_contact' 			=> NULL,          
			'email' 				=> NULL,                
			'phone' 				=> NULL,                
			'address_name' 			=> NULL,         
			'address_1' 			=> NULL,            
			'address_2' 			=> NULL,            
			'address_city' 			=> NULL,         
			'address_state' 		=> NULL,        
			'address_zip' 			=> NULL,          
			'current_term_enddate' 	=> NULL, 
			'last_updated' 			=> NULL,         			
			'social_media' 			=> NULL         			
		);
		
		return $official;
		
	}
	
	public function official_new() {
		
		$official = array(
			'meta_ocd_id'			=> NULL,
			'uid'					=> NULL,			
			'type' 					=> NULL,                 		
			'title' 				=> NULL,                		
			'description' 			=> NULL,          		
			'name_given' 			=> NULL,           		
			'name_family' 			=> NULL,          			
			'name_full' 			=> NULL,            			
			'url' 					=> NULL,                  
			'url_photo' 			=> NULL,            
			'url_schedule' 			=> NULL,         
			'url_contact' 			=> NULL,          
			'email' 				=> NULL,                
			'phone' 				=> NULL,                
			'address_name' 			=> NULL,         
			'address_1' 			=> NULL,            
			'address_2' 			=> NULL,            
			'address_locality' 		=> NULL,         
			'address_region' 		=> NULL,        
			'address_postcode' 		=> NULL,   
			'address_country' 		=> NULL,        			       
			'current_term_enddate' 	=> NULL, 
			'last_updated' 			=> NULL,         			
			'social_media' 			=> NULL, 
			'other_data' 			=> NULL, 
			'conflicting_data'		=> NULL, 
			'sources'				=> NULL         			
		);
		
		return $official;
		
	}	
		
	
	
	public function service_discovery() {
		
		$service_discovery = null; //array();
		
		return $service_discovery;
		
	}	

}

?>