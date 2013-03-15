<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class State_Ny_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}

	
	function get_ag($democracymap) {	
		
		$official 	  = $democracymap->official();

		$official['title']					=  'Attorney General';
		$official['type']					=  'attorney-general';
		$official['name_given'] 			=  'Eric';	
		$official['name_family'] 			=  'Schneiderman';
		$official['name_full']				=  'Eric T. Schneiderman';											      	
		$official['description'] 			=  '';	

		$official['url'] 					=  'http://www.ag.ny.gov/';	
		$official['url_photo'] 				=  'http://www.ag.ny.gov/sites/default/files/images/Attorny_General_Eric_T_Schneiderman.jpg';	
		$official['url_schedule'] 			=  '';	
		$official['url_contact'] 			=  'http://www.ag.ny.gov/questions-comments-attorney-general-eric-t-schneiderman';	
		$official['email'] 					=  '';	
		$official['phone'] 					=  '+1-800-771-7755';	
		$official['address_name'] 			=  'Office of the Attorney General';	
		$official['address_1'] 				=  'The Capitol';
		$official['address_2'] 				=  '';	
		$official['address_city'] 			=  'Albany';	
		$official['address_state'] 			=  'NY';	
		$official['address_zip'] 			=  '12224-0341';	
		$official['current_term_enddate'] 	=  '';
		
		// Twitter
		$social_media_account = $democracymap->social_media();		

		$social_media_account['type']			= 'twitter';
		$social_media_account['description']	= 'Twitter';
		$social_media_account['username']		= 'AGSchneiderman';				
		$social_media_account['url']			= 'https://twitter.com/AGSchneiderman';							

		$social_media[] 						= $social_media_account;
		
		// Facebook				
		$social_media_account 	= $democracymap->social_media();		
	
		$social_media_account['type']			= 'facebook';
		$social_media_account['description']	= 'Facebook';
		$social_media_account['username']		= '';				
		$social_media_account['url']			= 'http://www.facebook.com/eric.schneiderman';							

		$social_media[] 						= $social_media_account;		
		
		$official['social_media'] 			=  $social_media;	
		
		return $official;
	
	}	
	
	

}

?>