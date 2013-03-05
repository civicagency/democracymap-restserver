<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Geocoder_model extends CI_Model {


	public function __construct(){
		parent::__construct();
	}

	
	
	
	function geocode($location) {
		
		if ($this->config->item('geocoder') == 'yahoo') {
			return $this->yahoo_geocode($location);
		}
		
		
		if ($this->config->item('geocoder') == 'mapquest') {
			return $this->mapquest_geocode($location);
		}		

	}	
	
	
	
	function mapquest_geocode($input) {
		
		//$input = urlencode($input);

		$url = 'http://www.mapquestapi.com/geocoding/v1/address?key=' . $this->config->item('mapquest_api_key') . '&inFormat=kvp&outFormat=json&maxResults=3&location=' . $input;		
    
		$location = curl_to_json($url);	
		
		if(!empty($location['results'][0]['locations'][0])) {
    
			$data['latitude'] 			= $location['results'][0]['locations'][0]['latLng']['lat'];
			$data['longitude'] 			= $location['results'][0]['locations'][0]['latLng']['lng'];
			$data['city_geocoded'] 		= $location['results'][0]['locations'][0]['adminArea5'];
			$data['state_geocoded'] 	= $location['results'][0]['locations'][0]['adminArea3'];

			// Convert state abbreviation to full name
			$data['state_geocoded']		= $this->state_abbr($data['state_geocoded']); 

			return $data;

		}	else {
		 	return false;
		 }	
    
	}	
	
	
	function yahoo_geocode($location) {
		
		$this->load->helper('oauth.php');
		
		$url = "http://query.yahooapis.com/v1/yql/";
		$args = array();
		$args["q"] = 'select * from geo.placefinder where text="' . $location . '"';
		$args["format"] = "json";

		$consumer = new OAuthConsumer($this->config->item('yahoo_oauth_key'), $this->config->item('yahoo_oauth_secret'));
		$request = OAuthRequest::from_consumer_and_token($consumer, NULL,"GET", $url, $args);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);
		$url = sprintf("%s?%s", $url, OAuthUtil::build_http_query($args));
		$ch = curl_init();
		$headers = array($request->to_header());
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$rsp = curl_exec($ch);
		$location = json_decode($rsp);

		if ($location = $location->query->results->Result) {
		
			$data['latitude'] 			= $location->latitude;
			$data['longitude'] 			= $location->longitude;
			$data['city_geocoded'] 		= $location->city;
			$data['state_geocoded'] 	= $location->state;

			return $data;		
			
		} else {
			return false;
		}
		
		
	}
	

	function state_abbr($abbr) {
		
	    $state["AL"] = "Alabama";
	    $state["AK"] = "Alaska";
	    $state["AS"] = "American Samoa";
	    $state["AZ"] = "Arizona";
	    $state["AR"] = "Arkansas";
	    $state["CA"] = "California";
	    $state["CO"] = "Colorado";
	    $state["CT"] = "Connecticut";
	    $state["DE"] = "Delaware";
	    $state["FL"] = "Florida";
	    $state["GA"] = "Georgia";
	    $state["GU"] = "Guam";
	    $state["HI"] = "Hawaii";
	    $state["ID"] = "Idaho";
	    $state["IL"] = "Illinois";
	    $state["IN"] = "Indiana";
	    $state["IA"] = "Iowa";
	    $state["KS"] = "Kansas";
	    $state["KY"] = "Kentucky";
	    $state["LA"] = "Louisiana";
	    $state["ME"] = "Maine";
	    $state["MD"] = "Maryland";
	    $state["MA"] = "Massachusetts";
	    $state["MI"] = "Michigan";
	    $state["MN"] = "Minnesota";
	    $state["MS"] = "Mississippi";
	    $state["MO"] = "Missouri";
	    $state["MT"] = "Montana";
	    $state["NE"] = "Nebraska";
	    $state["NV"] = "Nevada";
	    $state["NH"] = "New Hampshire";
	    $state["NJ"] = "New Jersey";
	    $state["NM"] = "New Mexico";
	    $state["NY"] = "New York";
	    $state["NC"] = "North Carolina";
	    $state["ND"] = "North Dakota";
	    $state["MP"] = "Northern Mariana Islands";
	    $state["OH"] = "Ohio";
	    $state["OK"] = "Oklahoma";
	    $state["OR"] = "Oregon";
	    $state["PA"] = "Pennsylvania";
	    $state["PR"] = "Puerto Rico";
	    $state["RI"] = "Rhode Island";
	    $state["SC"] = "South Carolina";
	    $state["SD"] = "South Dakota";
	    $state["TN"] = "Tennessee";
	    $state["TX"] = "Texas";
	    $state["UT"] = "Utah";
	    $state["VT"] = "Vermont";
	    $state["VI"] = "Virgin Islands";
	    $state["VA"] = "Virginia";
	    $state["WA"] = "Washington";
	    $state["WV"] = "West Virginia";
	    $state["WI"] = "Wisconsin";
	    $state["WY"] = "Wyoming";	
		
		
		return $state[$abbr];		
	}
	
	

}

?>