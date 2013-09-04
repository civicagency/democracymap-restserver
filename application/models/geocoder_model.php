<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Geocoder_model extends CI_Model {


	public function __construct(){
		parent::__construct();
		
		// State name - initialize because this can come from several possible sources.
		$this->region_name 	= null;
		
		// Placeholders for lat/long
		$this->latitude 	= null;
		$this->longitude 	= null;
		
		$this->latlong 		= null;
		
		// to store all raw data from the geocoder
		$this->metadata		= null;
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
    
			$data['latitude'] 				= $location['results'][0]['locations'][0]['latLng']['lat'];
			$data['longitude'] 				= $location['results'][0]['locations'][0]['latLng']['lng'];
		 
			$data['geocoded_city'] 			= $location['results'][0]['locations'][0]['adminArea5'];
			$data['geocoded_state'] 		= $location['results'][0]['locations'][0]['adminArea3'];

			// Convert state abbreviation to full name
			$data['geocoded_state']		= $this->state_abbr($data['geocoded_state']);
			
			$data['geocoded_postalcode'] 	= $location['results'][0]['locations'][0]['postalCode'];
			$data['geocoded_street'] 		= $location['results'][0]['locations'][0]['street'];


			// These are probably pretty specific to MapQuest; consider abstracting?

				$data['geocoded_map_url'] 		= $location['results'][0]['locations'][0]['mapUrl'];									
				$data['geocoded_precision'] 		= $location['results'][0]['locations'][0]['geocodeQuality'];									
				
				// This should be parsed out. Explained here: http://www.mapquestapi.com/geocoding/geocodequality.html
				$data['geocoded_quality'] 		= $location['results'][0]['locations'][0]['geocodeQualityCode'];									
				


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
			$data['geocoded_city'] 		= $location->city;
			$data['geocoded_state'] 	= $location->state;

			return $data;		
			
		} else {
			return false;
		}
		
		
	}
	

	function state_abbr($abbr = null) {
		
	    $state["AL"] = "Alabama";
	    $state["AK"] = "Alaska";
	    $state["AS"] = "American Samoa";
	    $state["AZ"] = "Arizona";
	    $state["AR"] = "Arkansas";
	    $state["CA"] = "California";
	    $state["CO"] = "Colorado";
	    $state["CT"] = "Connecticut";
	    $state["DC"] = "Washington D.C.";
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
		
		if(!empty($abbr)) {
			return $state[$abbr];					
		} else {
			return $state;
		}
	

	}
	
	
	function test_latlong($query) {

		$result = explode(",", $query);  // Split the string by commas
		if (count($result) > 1) {
			$lat = trim($result[0]);         // Clean whitespace
			$lon = trim($result[1]);         // Clean whitespace

			if ((is_numeric($lat)) and (is_numeric($lon))) {
				$response = array('latitude' => $lat, 'longitude' => $lon);
				return $response;
			} else {
				return false;
			}		

		} else {
			return false;
		}


	}	
	
	
	public function ocd_from_geoid($geoid){
		
		$query = $this->db->get_where('ocd', array('GEOID' => $geoid));
		
		if ($query->num_rows() > 0) {
		   $row = $query->row(); 		
		   return $row->OCDID; 
		} else {
			return false;
		}
		
				
		
	}
	
	
	public function county_geo($lat, $long) {	


		$url = "http://tigerweb.geo.census.gov/ArcGIS/rest/services/Census2010/tigerWMS/MapServer/115/query?text=&geometry=$long%2C$lat&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&relationParam=&objectIds=&where=&time=&returnCountOnly=false&returnIdsOnly=false&returnGeometry=false&maxAllowableOffset=&outSR=&outFields=COUNTY,BASENAME,NAME,STATE,COUNTYNS,GEOID&f=json";	

			$feature_data = curl_to_json($url);

			if(!empty($feature_data['features'])) { 
				return $feature_data['features'][0]['attributes'];			
			}

	}	
	
	function city_geo($lat, $long) {	
		
		$url = "http://tigerweb.geo.census.gov/ArcGIS/rest/services/Census2010/tigerWMS/MapServer/58/query?text=&geometry=$long%2C$lat&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&relationParam=&objectIds=&where=&time=&returnCountOnly=false&returnIdsOnly=false&returnGeometry=false&maxAllowableOffset=&outSR=&outFields=*&f=json";

			$feature_data = curl_to_json($url);

			if(!empty($feature_data['features'])) { 
				return $feature_data['features'][0]['attributes'];			
			}
	}	
	
	
	
	
	

}

?>