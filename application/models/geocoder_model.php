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
		
$states = '		[
		    {
		        "abbreviation": "AL",
		        "state": "Alabama"
		    },
		    {
		        "abbreviation": "AK",
		        "state": "Alaska"
		    },
		    {
		        "abbreviation": "0",
		        "state": "American Samoa"
		    },
		    {
		        "abbreviation": "AZ",
		        "state": "Arizona"
		    },
		    {
		        "abbreviation": "AR",
		        "state": "Arkansas"
		    },
		    {
		        "abbreviation": "CA",
		        "state": "California"
		    },
		    {
		        "abbreviation": "CO",
		        "state": "Colorado"
		    },
		    {
		        "abbreviation": "CT",
		        "state": "Connecticut"
		    },
		    {
		        "abbreviation": "DE",
		        "state": "Delaware"
		    },
		    {
		        "abbreviation": "FL",
		        "state": "Florida"
		    },
		    {
		        "abbreviation": "GA",
		        "state": "Georgia"
		    },
		    {
		        "abbreviation": "GU",
		        "state": "Guam"
		    },
		    {
		        "abbreviation": "HI",
		        "state": "Hawaii"
		    },
		    {
		        "abbreviation": "ID",
		        "state": "Idaho"
		    },
		    {
		        "abbreviation": "IL",
		        "state": "Illinois"
		    },
		    {
		        "abbreviation": "IN",
		        "state": "Indiana"
		    },
		    {
		        "abbreviation": "IA",
		        "state": "Iowa"
		    },
		    {
		        "abbreviation": "KS",
		        "state": "Kansas"
		    },
		    {
		        "abbreviation": "KY",
		        "state": "Kentucky"
		    },
		    {
		        "abbreviation": "LA",
		        "state": "Louisiana"
		    },
		    {
		        "abbreviation": "ME",
		        "state": "Maine"
		    },
		    {
		        "abbreviation": "MD",
		        "state": "Maryland"
		    },
		    {
		        "abbreviation": "MA",
		        "state": "Massachusetts"
		    },
		    {
		        "abbreviation": "MI",
		        "state": "Michigan"
		    },
		    {
		        "abbreviation": "MN",
		        "state": "Minnesota"
		    },
		    {
		        "abbreviation": "MS",
		        "state": "Mississippi"
		    },
		    {
		        "abbreviation": "MO",
		        "state": "Missouri"
		    },
		    {
		        "abbreviation": "0",
		        "state": "Montana"
		    },
		    {
		        "abbreviation": "NE",
		        "state": "Nebraska"
		    },
		    {
		        "abbreviation": "NV",
		        "state": "Nevada"
		    },
		    {
		        "abbreviation": "0",
		        "state": "New Hampshire"
		    },
		    {
		        "abbreviation": "NJ",
		        "state": "New Jersey"
		    },
		    {
		        "abbreviation": "NM",
		        "state": "New Mexico"
		    },
		    {
		        "abbreviation": "NY",
		        "state": "New York"
		    },
		    {
		        "abbreviation": "0",
		        "state": "North Carolina"
		    },
		    {
		        "abbreviation": "ND",
		        "state": "North Dakota"
		    },
		    {
		        "abbreviation": "MP",
		        "state": "Northern Mariana Islands"
		    },
		    {
		        "abbreviation": "OH",
		        "state": "Ohio"
		    },
		    {
		        "abbreviation": "OK",
		        "state": "Oklahoma"
		    },
		    {
		        "abbreviation": "OR",
		        "state": "Oregon"
		    },
		    {
		        "abbreviation": "PA",
		        "state": "Pennsylvania"
		    },
		    {
		        "abbreviation": "0",
		        "state": "Puerto Rico"
		    },
		    {
		        "abbreviation": "RI",
		        "state": "Rhode Island"
		    },
		    {
		        "abbreviation": "SC",
		        "state": "South Carolina"
		    },
		    {
		        "abbreviation": "SD",
		        "state": "South Dakota"
		    },
		    {
		        "abbreviation": "TN",
		        "state": "Tennessee"
		    },
		    {
		        "abbreviation": "TX",
		        "state": "Texas"
		    },
		    {
		        "abbreviation": "UT",
		        "state": "Utah"
		    },
		    {
		        "abbreviation": "VT",
		        "state": "Vermont"
		    },
		    {
		        "abbreviation": "VI",
		        "state": "Virgin Islands"
		    },
		    {
		        "abbreviation": "VA",
		        "state": "Virginia"
		    },
		    {
		        "abbreviation": "WA",
		        "state": "Washington"
		    },
		    {
		        "abbreviation": "WV",
		        "state": "West Virginia"
		    },
		    {
		        "abbreviation": "WI",
		        "state": "Wisconsin"
		    },
		    {
		        "abbreviation": "WY",
		        "state": "Wyoming"
		    }
		]';		
		
		
		$states = json_decode($states);
			
		foreach ($states as $state) {
			if ($abbr == $state->abbreviation) {
				return $state->state;
			}
				
		}
		
	}
	
	

}

?>