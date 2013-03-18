<?php
require APPPATH.'/libraries/REST_Controller.php';

class Context extends REST_Controller {

	public $ttl 		= 604800;
	public $phoneUtil	= null;

	function __construct()
	{
		parent::__construct();
		
	   	$this->load->helper('phone/PhoneNumberUtil');			
	   	$this->phoneUtil = PhoneNumberUtil::getInstance();			
		
	    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
	}

	public function index_get()	{
		
		 //$this->cache->clean();
		
		if (empty($_GET)) {
			$this->load->helper('url');			
			redirect('welcome');	
		}
		

			$data['input'] 						= $this->input->get('location', TRUE);
			
			if(!$data['input'] ) {
				
				$this->response('No location provided', 400);

			}
			
			$key 						= $data['input'] . '_context';


			if (!$this->test_latlong($data['input'])) {
				
				// Check in cache		
				if ( $cache = $this->cache->get( $key )) {
					$this->response($cache, 200);
				}				
				
			}
			
		
			
			// Geocode our address (if needed)
			
			if ($check_input = $this->test_latlong($data['input'])) {
				
				$data['latitude'] 			= $check_input['latitude'];
				$data['longitude'] 			= $check_input['longitude'];
				$latlong 					= $data['latitude'] . " " . $data['longitude'];									
							
			}
			else {
			
				$this->load->model('geocoder_model', 'geocoder');			
				
				if ($location 	= $this->geocoder->geocode(urlencode($data['input']))) {
					$data 		= array_merge($data, $location);			
					$latlong 	= $data['latitude'] . " " . $data['longitude'];					
				}			

			} 
			
			if (empty($latlong)) {
				$data_errors[] = 'The location could not be geocoded';
			}					


			if(!empty($latlong)) {
								
				$state_legislators = $this->state_legislators($data['latitude'], $data['longitude']);

				if(!empty($state_legislators)) {
					$state_chambers = $this->process_state_legislators($state_legislators);				
					$data['state_chambers'] = $state_chambers;
				}
				else {
					$data_errors[] = 'State legislature lookup failed'; // OpenStates API
				}
				
				$national_legislators = $this->national_legislators($data['latitude'], $data['longitude']);	
				
				if (!empty($national_legislators)) {	
					$national_chambers = $this->process_nat_legislators($national_legislators); 		
		  
					ksort($national_chambers);
		  
					$data['national_chambers'] = $national_chambers;				
				}
				else {
					$data_errors[] = 'National legislature lookup failed'; // Sunlight API
				}				

			}



			if (!empty($latlong)) {
				
							
				$data['census_city']			= $this->get_city($data['latitude'], $data['longitude']);
				
				

				if (!empty($data['census_city'])) {
										
						$data['state_id'] 	   		= 	$data['census_city']['STATE'];				
 						$data['place_id'] 	   		= 	$data['census_city']['PLACE'];									

						// Load City Model
						$this->load->model('city_model', 'cities');

						$city = $this->cities->get_city_data($data['state_id'], $data['place_id']);
						$data = array_merge($data, $city);
						

				} else {
						$data_errors[] = 'City could not be geocoded';					
				}
				
				
				// Get City/County data from SBA
				if (!empty($data['city']) && !empty($data['state'])) {
					$city_data = $this->get_city_links($data['city'], $data['state']);			
				}
			
				// County lookup 
				if (!empty($latlong)) {
				
					// Load County Model
					$this->load->model('county_model', 'counties');
				
					$data['county_data']			= $this->counties->get_county_geo($data['latitude'], $data['longitude']);				
					
					if($data['county_data']['COUNTY']) {			
						
						$data['counties'] 				= $this->counties->get_county_data($data['county_data']['COUNTY'], $data['county_data']['STATE']);
						
						if ($data['counties']['name']) {

							// County Representatives
							if (!empty($data['counties']['name']) && !empty($data['counties']['state'])) {

								$data['county_reps'] = $this->counties->get_county_reps($data['counties']['state'], $data['counties']['name']);

							}
							
						} else {
							unset($data['counties']);
						}
					} else {
						$data_errors[] = 'County could not be geocoded';
					}
				
				}				
												
			}
			
			
			// State data
			if (!empty($data['state_geocoded'])) {
				
				$data['state_data'] = $this->get_state($data['state_geocoded']);
								
				if(!empty($data['state_data']['phone_primary'])) {
					$data['state_data']['phone_primary'] = $this->format_phone($data['state_data']['phone_primary']);
				}				

				
								
				$governor = $this->get_governor($data['state_geocoded']);
				
				if(!empty($governor)) {
					$data['governor_data'] = $governor;				
				}	
				
				$governor_socialmedia = $this->get_governor_sm($data['state_geocoded']);			
				
				if(!empty($governor_socialmedia)) {
					$data['governor_sm'] = $governor_socialmedia;				
				}				
				
			}			
			
			
			// If we didn't get state abbreviation from the city lookup, see if we can get it elsewhere
			if (empty($data['state']) && !empty($data['counties']['state'])) {
				$data['state'] = $data['counties']['state'];
				// $data['state'] = $data['state_geocoded']; this is more reliable, but isn't the abbreviation	
			}	
			if (empty($data['state']) && !empty($data['governor_data']['address_state'])) {
				$data['state'] = $data['governor_data']['address_state'];
			}
			
			
			
			
			
					
			
			// get GeoJSON from GeoServer
			if ($this->input->get('geojson', TRUE) == 'true') {				
				$data['geojson'] = $this->get_geojson($data['fid']);
			}
			
			// Service Discovery
			if (!empty($data['service_discovery'])) {
				$data['service_discovery'] = $this->get_servicediscovery($data['service_discovery']);
			}
			
		
			
			
			// Mayor data
			if (!empty($data['city']) && !empty($data['state'])) {
				$mayor = $this->get_mayors($data['city'], $data['state']);
				
				if(!empty($mayor)) {
					$data['mayor_data'] = $mayor;	
					
					// See if we can get social media channels for this mayors
					$mayor_sm = $this->get_mayor_sm($data['city']);					
					if(!empty($mayor_sm)) $data['mayor_sm'] = $mayor_sm;
					
								
				}
			}		
			
			
			// Better links for municipal data from the SBA (I'm only pulling out the url, but other data might be usefull too)

			if(!empty($city_data)) {
				$data['place_url_updated'] = $city_data[0]['url'];

				$data['city_data'] = $city_data[0];
				
			} else {
				
				if(!empty($data['mayor_data']['url'])) {
					$data['place_url_updated'] = $data['mayor_data']['url'];	
				} 
				
				if (!empty($data['place_url'])) {
					$data['place_url_updated'] = $data['place_url'];	
				}
			}
			
			if(empty($data['place_url_updated'])) $data['place_url_updated'] = null;
		
			
			
			// DC Hyperlocal data - this should be totally decoupled, but including it here as a proof of concept
			if (!empty($data['state_id']) && ($data['state_id'] == '11') && ($data['place_id'] == '50000')) {
			
				$data['city_ward'] = $this->get_dc_ward($data['latitude'], $data['longitude']); 
			
				if(!empty($data['city_ward']['LABEL'])) {
					$data['council_reps'] = $this->get_dc_councilmembers($data['city_ward']['LABEL']);
				}
				
				
				$data['city_anc'] = $this->get_dc_anc($data['latitude'], $data['longitude']); 
				
				if(!empty($data['city_anc']['external_id'])) {
					$data['anc_reps'] = $this->get_dc_anc_members($data['city_anc']['external_id']);
				}		
				
				
				$data['city_smd'] = $this->get_dc_smd($data['latitude'], $data['longitude']); 

				if(!empty($data['city_smd']['external_id'])) {
					foreach ($data['anc_reps'] as $smd_rep) {
						if($smd_rep['smd'] == $data['city_smd']['external_id']) {
							$data['smd_rep'] = $smd_rep;
							reset($data['anc_reps']);
							break;							
						}				
					}
				}				
				
						

			}
			
			
			// NYC Hyperlocal data		
			//if (!empty($data['gnis_fid']) && $data['gnis_fid'] == '2395220') {		
			if (!empty($data['place_id']) && !empty($data['state_id']) && $data['place_id'] == '51000' && $data['state_id'] == '36') {
					$this->load->model('nyc_model', 'nyc');
					$this->load->model('democracymap_model', 'democracymap');
				
					$data['nyc_council'] 			= $this->nyc->get_city_council($latlong, $this->democracymap);
					$data['nyc_community_boards'] 	= $this->nyc->get_community_board($latlong, $this->democracymap);								
					$data['nyc_officials']			= $this->nyc->get_officials($this->democracymap);
			}			
			

			
			// City Data
			if(!empty($data['state'])) {		
			   	$cities_by_state = APPPATH . 'controllers/cities_by_state/cities_' . strtolower($data['state']) .'.php';
			    if(!empty($data['city']) && file_exists($cities_by_state)) {

					$data['city_reps'] = $this->get_city_reps($cities_by_state, $data['city'], $data['state']);
			
				}
			}
			
				
				

			// See if we have google analytics tracking code
			if($this->config->item('ganalytics_id')) {
				//$data['ganalytics_id'] = $this->config->item('ganalytics_id');
			}
			
			
			

			if (!empty($latlong)) {
				$new_data = $this->re_schema($data);
			} else {
				$new_data = array();
			}
				
			// basic error reporting
			if(!empty($data_errors)) {
				$new_data['errors'] = $data_errors;
			}
				
			// Save to cache	
			if (!empty($latlong)) {				
				$this->cache->save( $key, $new_data, $this->ttl);
			}
				
			$this->response($new_data, 200);


		
	}
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	
	
	function get_city($lat, $long) {	
		
		$url = "http://tigerweb.geo.census.gov/ArcGIS/rest/services/Census2010/tigerWMS/MapServer/58/query?text=&geometry=$long%2C$lat&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&relationParam=&objectIds=&where=&time=&returnCountOnly=false&returnIdsOnly=false&returnGeometry=false&maxAllowableOffset=&outSR=&outFields=*&f=json";

			$feature_data = curl_to_json($url);

			if(!empty($feature_data['features'])) return $feature_data['features'][0]['attributes'];			

	}	
	
	
	
	
	function get_geojson($feature_id) {	
		
		$url = $this->config->item('geoserver_root') . '/wfs?request=getFeature&outputFormat=json&layers=census:municipal&featureid=' . $feature_id; 
		
		
			$feature_data = curl_to_json($url);

			return $feature_data;

	}	
	
	
	function get_city_links($city, $state) {
		
		$key = md5( serialize( "$city, $state" )) . '_city_links';
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
			
			
		$city = urlencode(strtolower($city));
		$state = urlencode(strtolower($state));
	
		$url = "http://api.sba.gov/geodata/all_links_for_city_of/$city/$state.json";

		$data = curl_to_json($url);	
		
		// Save to cache
		$this->cache->save( $key, $data, $this->ttl);

		return $data;

	}	
	
	
	
	function get_servicediscovery($url) {	
		
			$data = curl_to_json($url);

			return $data;

	}	
	
	
	
	
	
	function get_mayors($city, $state) {
		
		$key = md5( serialize( "$city, $state" )) . '_city_mayor';
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$city = ucwords($city);
		$state = strtoupper($state);		
		
		$query = "select * from `swdata` where city = '$city' and state = '$state' limit 1";		
		$query = urlencode($query);
		
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=us_mayors&query=$query";		

		$mayors = curl_to_json($url);

		if(!empty($mayors)) {
			
			// Save to cache
			$this->cache->save( $key, $mayors[0], $this->ttl);
			
			return $mayors[0];			
		}

	}
	
	
	
	
	function get_mayor_sm($city) {
		
		$key = md5( serialize( $city )) . '_city_mayor_sm';
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$city = ucwords($city);		
		
		$query = "select * from `swdata` where city = '$city' limit 1";		
		$query = urlencode($query);
		
				
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=us_mayors_-_social_media_accounts&query=$query";		

		$mayor = curl_to_json($url);
				
		if(!empty($mayor)) {
			
			// Save to cache
			$this->cache->save( $key, $mayor[0], $this->ttl);
			
			return $mayor[0];			
		}		
				
		
	}	
	
	
	
	
		
	
function get_city_reps($cities_by_state, $city, $state) {
	
	$key = md5( serialize("$city $state")) . '_city_reps';

	// Check in cache
	if ( $cache = $this->cache->get( $key ) ) {
		return $cache;
	} 		
	
	include $cities_by_state ;
	
	if (!empty($electeds)) {
		// Save to cache
		$this->cache->save( $key, $electeds, $this->ttl);
   
		return $electeds;	
	} else {
		return null;
	}
	
}


	
// DC Specific 	

function get_dc_ward($lat, $long)	{
	

	$url ="http://maps.dcgis.dc.gov/DCGIS/rest/services/DCGIS_DATA/Administrative_Other_Boundaries_WebMercator/MapServer/26/query?text=&geometry=$long%2C+$lat&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&where=&returnGeometry=false&outSR=4326&outFields=NAME%2C+WARD_ID%2C+LABEL&f=json";		

	$data = curl_to_json($url);

	$data = $data['features'][0]['attributes'];

	return $data;
	
}


function get_dc_anc($lat, $long)	{
	

	//$url ="http://maps.dcgis.dc.gov/DCGIS/rest/services/DCGIS_DATA/Administrative_Other_Boundaries_WebMercator/MapServer/2/query?text=&geometry=$long%2C+$lat&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&where=&returnGeometry=false&outSR=4326&outFields=NAME,ANC_ID,WEB_URL&f=json";

	$url ="http://gis.govtrack.us/boundaries/dc-anc-2013/?contains=$lat,$long";

	$data = curl_to_json($url);

	$data = $data['objects'][0];

	return $data;
	
}



function get_dc_smd($lat, $long)	{
	
	$url ="http://gis.govtrack.us/boundaries/dc-smd-2013/?contains=$lat,$long";

	$data = curl_to_json($url);

	$data = $data['objects'][0];

	return $data;
	
}








// DC Specific 	

function get_dc_councilmembers($ward)	{
	
	$key = md5( serialize( $ward )) . '_dc_ward_members';
	
	// Check in cache
	if ( $cache = $this->cache->get( $key ) ) {
		return $cache;
	}	
	
	$ward = urlencode($ward);
	
	$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=washington_dc_wards_and_councilmembers&query=select%20*%20from%20%60swdata%60%20where%20ward_name%20%3D%20%22$ward%22%3B";		

	$response['my_rep'] = curl_to_json($url);
	
	
	$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=washington_dc_wards_and_councilmembers&query=select%20*%20from%20%60swdata%60%20where%20member_type%20!%3D%20%22Ward%20Members%22%3B";		


	$response['at_large'] = curl_to_json($url);

	// Save to cache
	$this->cache->save( $key, $response, $this->ttl);

	return $response;	
	
}

// DC Specific 	

function get_dc_anc_members($anc)	{
	
	$key = md5( serialize( $anc )) . '_dc_anc_members';
	
	// Check in cache
	if ( $cache = $this->cache->get( $key ) ) {
		return $cache;
	}	
	
	$query = "select * from `swdata` where anc = '$anc'";
	
	$query = urlencode($query);
	
	$url ="https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=city_representatives_hyperlocal_-_dc_anc_members&query=$query";		

	$response = curl_to_json($url);

	// Save to cache
	$this->cache->save( $key, $response, $this->ttl);

	return $response;	
	
}



	
	
	function get_state($state) {
		
		$key = $state . '_state_data';

		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$state = ucwords($state);		
		
		$query = "select * from `swdata` where state = '$state' limit 1";		
		$query = urlencode($query);
		
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=50_states_data&query=$query";		

		$state = curl_to_json($url);
		
		if(!empty($state[0])) {
		
			// Save to cache
			$this->cache->save( $key, $state[0], $this->ttl);
			
			return $state[0];		
		}

	}
	
	
	function get_governor($state) {
				
		$state = ucwords($state);		
		
		$key = $state . '_state_governor';		
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$query = "select * from `swdata` where state = '$state' limit 1";		
		$query = urlencode($query);
		
				
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=us_governors&query=$query";		

		$state = curl_to_json($url);

		if(!empty($state[0])) {

			// Save to cache
			$this->cache->save( $key, $state[0], $this->ttl);		
		
			return $state[0];
		}


	}	
	
	
	function get_governor_sm($state) {
		
		$state = ucwords($state);		
		
		$key = $state . '_state_governor_sm';				
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$query = "select * from `swdata` where state = '$state' limit 1";		
		$query = urlencode($query);
		
				
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=us_governors_-_social_media_accounts&query=$query";		

		$state = curl_to_json($url);
		
		if(!empty($state[0])) {
			
			// Save to cache
			$this->cache->save( $key, $state[0], $this->ttl);
			
			return $state[0];		
		}	

	}


	
	
	
	function state_legislators($lat, $long) {
		
		// $url = "http://openstates.org/api/v1/legislators/geo/?long=" . $long . "&lat=" . $lat . "&fields=state,chamber,district,full_name,url,photo_url&apikey=" . $this->config->item('sunlight_api_key');
		$url = "http://openstates.org/api/v1/legislators/geo/?long=" . $long . "&lat=" . $lat . "&apikey=" . $this->config->item('sunlight_api_key');

		$state_legislators = curl_to_json($url);

		if(!empty($state_legislators)) return $state_legislators;				
		else return false;
		
	}
	

	
	function state_boundaries($state, $chamber) {
		
		$key = $state . '_' . $chamber . '_state_boundaries';
		
		// Check in cache
		if ( $cache = $this->cache->get( $key ) ) {
			return $cache;
		}		
		
		$url = "http://openstates.org/api/v1/districts/" . $state . "/" . $chamber . "/?fields=name,boundary_id&apikey=" . $this->config->item('sunlight_api_key');

		$state_boundaries = curl_to_json($url);
		$state_boundaries = $this->process_boundaries($state_boundaries);

		$this->cache->save( $key, $state_boundaries, $this->ttl);

		return $state_boundaries;

	}

	
	
	
	function state_boundary_shape($boundary_id) {
		
		$url = "http://openstates.org/api/v1/districts/boundary/" . $boundary_id . "/?apikey=" . $this->config->item('sunlight_api_key');


		$geojson = curl_to_json($url);	

		$boundary_shape['coordinates'] = $geojson['shape'];
		$boundary_shape = json_encode($boundary_shape);

		$shape['shape'] = $boundary_shape;
		$shape['shape_center_lat'] = $geojson['region']['center_lat'];
		$shape['shape_center_long'] = $geojson['region']['center_lon'];		

		return $shape;

	}	
	
	
	
	function process_boundaries($boundary_array) {

		// Clean up data model
		foreach($boundary_array as $boundarydata){	

				$district = $boundarydata['name'];
				$boundary_id = $boundarydata['boundary_id'];

				$boundaries[$district]['boundary_id'] = $boundary_id;

		}
		
		if(isset($boundaries)) {
			return $boundaries;
		}
		else {
			return false;
		}

	}	
	
	
	
function process_state_legislators($representatives) {
	
		// Get our current state
		$current_state = $representatives[0]['state'];
	
		// Clean up data model
		foreach($representatives as $repdata){
			
				$rep = array(
							'full_name' => $repdata['full_name'], 
							'state' => $repdata['state'], 
							);
				
				// there are some missing fields on some entries, check for that
				if(isset($repdata['photo_url'])){
					$rep['photo_url'] = $repdata['photo_url'];
				}
				
				if(isset($repdata['url'])){
					$rep['url'] = $repdata['url'];
				}				
				
							
		
				$chamber = $repdata['chamber'];
				$district = $repdata['district'];
		
					
				$chambers[$chamber][$district]['reps'][] = $rep;	
			
		}	
		
		// Only do this if we want geospatial data in the response
		if ($this->input->get('geojson', TRUE) == 'true') {
			
			// Get the boundary_ids for this state
			$boundary_ids['upper'] = $this->state_boundaries($current_state, 'upper'); 
			$boundary_ids['lower'] = $this->state_boundaries($current_state, 'lower'); 	
		
			
			// Get shapes for each of the boundary ids we care about		
			while($districts = current($chambers)) {

				$this_chamber = key($chambers);
				if (!isset($current_chamber)) $current_chamber = '';
			
				// reset current district in case district ids are reused across chambers
				$current_district = '';			
				if ($current_chamber !== $this_chamber){
				
					while($district = current($districts)) {

						$this_district = key($districts);
						if (!isset($current_district)) $current_district = '';
					
						if ($current_district !== $this_district) {

							// get shape for this boundary id
							$boundary_id = $boundary_ids["$this_chamber"][$this_district]['boundary_id'];
											
							$shape = $this->state_boundary_shape($boundary_id);
						
							$chambers[$this_chamber][$this_district]['shape'] = $shape['shape'];
							$chambers[$this_chamber][$this_district]['centerpoint_latlong'] = $shape['shape_center_lat'] . ',' . $shape['shape_center_long'];					

						}	

					    $current_district = $this_district;
						next($districts);
					}

				}

			    $current_chamber = $this_chamber;
				next($chambers);
			}
					
		}
		
	
		return $chambers;
	
}	
	
	
	
function national_legislators($lat, $long) {

	$url = "http://services.sunlightlabs.com/api/legislators.allForLatLong.json?latitude=" . $lat . "&longitude=" . $long . "&apikey=" . $this->config->item('sunlight_api_key');

	$legislators = curl_to_json($url);
	$legislators = $legislators['response']['legislators'];

	return $legislators;	

}
	
	
	
function process_nat_legislators($representatives) {
	

	
		// Clean up data model
		foreach($representatives as $repdata){
			
			$repdata = $repdata['legislator'];
			
			$full_name = $repdata['firstname'] . ' ' . $repdata['lastname'];
			
			if ($repdata['phone']) {
				$phone = $this->format_phone($repdata['phone']);
			} else {
				$phone = null;
			}
			
		
				$rep = array(
							'district' 		=> $repdata['district'], 
							'full_name' 	=> $full_name, 
							'name_given' 	=> $repdata['firstname'], 
							'name_family' 	=> 	$repdata['lastname'], 
							'bioguide_id' 	=> $repdata['bioguide_id'], 
							'website' 		=> $repdata['website'], 
							'url_contact' 		=> $repdata['webform'], 							
							'title' 	=> $repdata['title'],								
							'phone' 	=> $phone, 
							'twitter_id' 	=> $repdata['twitter_id'],
							'youtube_url' 	=> $repdata['youtube_url'],																					
							'congress_office' 		=> $repdata['congress_office'], 
							'facebook_id' 	=> $repdata['facebook_id'], 
							'email' 	=> $repdata['email']														
							);
	
				$chamber = $repdata['chamber'];
				$district = $repdata['district'];
				$state = $repdata['state'];
				
				$chambers["$chamber"]['reps'][] = $rep;

				
				if(is_numeric($district)) {
					$boundary_district = $district;
					
					$chambers["$chamber"]['shape'] = 'http://www.govtrack.us/perl/wms/export.cgi?dataset=http://www.rdfabout.com/rdf/usgov/congress/' . $chamber . '/110&region=http://www.rdfabout.com/rdf/usgov/geo/us/' . $state . '/cd/110/' . $district . '&format=kml&maxpoints=1000';
				}	

			
		}	
		
	
		return $chambers;
	
}	

function format_phone($phone) {
	
	$phone = $this->phoneUtil->parse($phone, "US");
	
	if($this->phoneUtil->isValidNumber($phone)) {
		return $this->phoneUtil->format($phone, PhoneNumberFormat::RFC3966);	
	} else {
		return null;
	}
	
	
}

function re_schema($data) {
	
	
	
	$new_data['latitude']			= $data['latitude'];
	$new_data['longitude']			= $data['longitude'];
	$new_data['input_location']		= $data['input'];	

			

 // ############################################################################################################
 // Hyperlocal


 // DC




if (!empty($data['city_smd']) && !empty($data['smd_rep'])) {	

 // Elected

	$name_full = $data['smd_rep']['first_name'] . ' ' . $data['smd_rep']['last_name'];

	$elected = array();
	$elected[] = $this->elected_official_model('legislative', 'Commissioner', null, $data['smd_rep']['first_name'], $data['smd_rep']['last_name'], $name_full, null, null, null, null, $data['smd_rep']['email'], $data['smd_rep']['phone'], null, $data['smd_rep']['address'], null, null, null, $data['smd_rep']['zip'], null, null, null);


 // Jurisdiction

 	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'Single Member District', 'sub-municipal', 'Single Member District', $data['city_smd']['external_id'], $data['city_smd']['external_id'], null, null, $data['smd_rep']['email'], $data['smd_rep']['phone'], null, null, null, null, null, null, null, null, null, $elected, null);

 }



 // Elected
 
 if (!empty($data['anc_reps'])) {	
 
	$elected = array();
	foreach ($data['anc_reps'] as $anc_rep) {

		$name_full = $anc_rep['first_name'] . ' ' . $anc_rep['last_name'];
		$title = 'Commissioner SMD ' . $anc_rep['smd'];

		$elected[] = $this->elected_official_model('legislative', $title, null, $anc_rep['first_name'], $anc_rep['last_name'], $name_full, null, null, null, null, $anc_rep['email'], $anc_rep['phone'], null, $anc_rep['address'], null, null, null, $anc_rep['zip'], null, null, null);

	}

 
 // Jurisdiction

 	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'Advisory Neighborhood Commission', 'sub-municipal', 'Advisory Neighborhood Commission', $data['city_anc']['external_id'], $data['city_anc']['external_id'], null, null, null, null, null, null, null, null, null, null, null, null, null, $elected, null);
 }







 // ############################################################################################################
 // City Council  
 

// NYC 

if(!empty($data['nyc_community_boards'])) {

	$new_data['jurisdictions'][] = $data['nyc_community_boards'];
	
}


if(!empty($data['nyc_council'])) {

	$new_data['jurisdictions'][] = $data['nyc_council'];
	
}

	





 // DC

 // Elected
 
 if (!empty($data['council_reps']['my_rep'])) {	
 
	$myrep = $data['council_reps']['my_rep'][0];
 
 	$elected = array(
 			$this->elected_official_model('legislative', 'Councilmember', null, null, null, $myrep['name'], $myrep['website'], $myrep['url_photo'], null, null, $myrep['email'], $myrep['phone'], null, $myrep['address'], null, null, null, null, $myrep['term_end'], null, null)		
 	);

 
 // Jurisdiction

 	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'City Council', 'municipal', 'City', $myrep['ward_name'], $data['city_ward']['WARD_ID'], $myrep['ward_url'], null, null, null, null, null, null, null, null, null, null, null, null, $elected, null);
 }
 
 

// ############################################################################################################
// Municipal 

// Elected
	
$elected = null;

if (!empty($data['mayor_data'])) {	

	if (!empty($data['mayor_sm'])) {

		$social_media = null;

		if(!empty($data['mayor_sm']['twitter'])) {
		
			$twitter_username = substr($data['mayor_sm']['twitter'], strrpos($data['mayor_sm']['twitter'], '/')+1);
			$social_media[] = array("type" => "twitter","description" => "Twitter","username" => $twitter_username,"url" => $data['mayor_sm']['twitter'],"last_updated" => null);
		}

		if(!empty($data['mayor_sm']['facebook'])) {
			$social_media[] = array("type" => "facebook","description" => "Facebook","username" => null,"url" => $data['mayor_sm']['facebook'],"last_updated" => null);
		}

		if(!empty($data['mayor_sm']['youtube'])) {
			$social_media[] = array("type" => "youtube","description" => "Youtube","username" => null,"url" => $data['mayor_sm']['youtube'],"last_updated" => null);
		}	

		if(!empty($data['mayor_sm']['flickr'])) {
			$social_media[] = array("type" => "flickr","description" => "Flickr","username" => null,"url" => $data['mayor_sm']['flickr'],"last_updated" => null);
		}	
	}
	else {
		$social_media = null;
	}

	$mayor_name_full 			= isset($data['mayor_data']['name']) ? $data['mayor_data']['name'] : null;	
	$mayor_url 					= isset($data['mayor_data']['bio_url']) ? $data['mayor_data']['bio_url'] : $data['place_url_updated']; // the bio url isn't exactly what we want here, but it's close enough. Usually the bio is one page of the mayor's section of the website. Really we just want the main mayor section
	//$mayor_url 					= $data['place_url_updated']; 	// We're assuming that the place_url_updated will always match the mayor url, but be more updated. Hopefully this is a safe assumption. 	
	$mayor_url_photo 			= isset($data['mayor_data']['url_photo']) ? $data['mayor_data']['url_photo'] : null;
	$mayor_email 				= isset($data['mayor_data']['email']) ? $data['mayor_data']['email'] : null;
	$mayor_email 				= ($mayor_email == 'none reported') ? null : $mayor_email;
	$mayor_phone 				= isset($data['mayor_data']['phone']) ? $data['mayor_data']['phone'] : null;	
	$mayor_current_term_enddate = isset($data['mayor_data']['current_term_enddate']) ? date('c', strtotime($data['mayor_data']['next_election'])) : null;	
	



	$elected = array(
			$this->elected_official_model('executive', 'Mayor', null, null, null, $mayor_name_full, $mayor_url, $mayor_url_photo, null, null, $mayor_email, $mayor_phone, null, null, null, null, null, null, $mayor_current_term_enddate, null, $social_media)		
	);

} else {
	$elected = null;	
}


// State by state city rep data

if (!empty($data['city_reps'])) {
	
	// check to see if we have mayor in the general city reps data
	$count = 0;
	foreach($data['city_reps'] as $city_rep) {
		if (strtolower($city_rep['title']) == 'mayor' ) {
			$mayor_key = $count;
		} 		
		$count++;
	}
	
	// If we have a mayor, remove it if it's duplicative, otherwise put it at the top
	if (isset($mayor_key) && $mayor_key !== false) {
							
		$mayor_temp = $data['city_reps'][$mayor_key];
		unset($data['city_reps'][$mayor_key]);
		
		// if we don't already have mayor data then, move the mayor we found to the top of the array
		if (empty($data['mayor_data'])) {
			array_unshift($data['city_reps'], $mayor_temp);
		} else {
		// if we do already have mayor data then try to merge the data
		
			$this->load->helper('api');			
			$elected[0] = array_mash($elected[0], $mayor_temp);
		}
	}
	
	$elected = (!empty($elected)) ? array_merge($elected, $data['city_reps']) : $data['city_reps'];
		
		
}



// DC Specific
 if (!empty($data['council_reps']['at_large'])) {
	
	foreach ($data['council_reps']['at_large'] as $at_large) {

	$elected[] = $this->elected_official_model('legislative', 'Councilmember', $at_large['member_type'], null, null, $at_large['name'], $at_large['website'], $at_large['url_photo'], null, null, $at_large['email'], $at_large['phone'], null, $at_large['address'], null, null, null, null, $at_large['term_end'], null, null);
	
	}
	
}


// NYC Specific
if(!empty($data['nyc_officials'])) {

	$elected =  array_merge($elected, $data['nyc_officials']);
	
}




// Jurisdiction
if(!empty($data['zip'])) {
	
	$municipal_zip 		= ($data['zip4']) ? $data['zip'] . '-' . $data['zip4'] : $data['zip'];	

	$municipal_metadata = array(array("key" => "place_id", "value" => $data['place_id']), 
									array("key" => "gnis_fid", "value" => $data['gnis_fid']));											
	

	$new_data['jurisdictions'][] = $this->jurisdiction_model('government', 'City', 'municipal', 'City', $data['city'], null, $data['place_url_updated'], null, null, null, $data['title'], $data['address1'], $data['address2'], $data['city'], $data['state'], $municipal_zip, null, $municipal_metadata, null, $elected, $data['service_discovery']);
}

$elected = null;	
	
// ##########################################################################################################
// Counties Jurisdictions

if (!empty($data['counties'])) {
	
	if (!empty($data['county_reps'])) {
		
		foreach ($data['county_reps'] as $co_rep) {

			$elected[] = $this->elected_official_model('administrative', $co_rep['rep_position'], null, null, null, $co_rep['rep'], null, null, null, null, $co_rep['rep_email'], null, null, null, null, null, null, null, null, null, null);

		}		
		
	} else {
		$elected = null;
	}
		
		
	

	$county_zip 		=  ($data['counties']['zip4']) ? $data['counties']['zip'] . '-' . $data['counties']['zip4'] : $data['counties']['zip'];		

	$county_metadata = array(array("key" => "fips_id", "value" => $data['counties']['fips_county']), 
							array("key" => 'county_id', "value" => $data['counties']['county_id']), 
							array("key" => 'population', "value" => $data['counties']['population_2006']));										
	

	$new_data['jurisdictions'][] = 	$this->jurisdiction_model('government', 'County', 'sub_regional', 'County', $data['counties']['name'], $data['counties']['county_id'], $data['counties']['website_url'], null, null, null, $data['counties']['title'], $data['counties']['address1'], $data['counties']['address2'], $data['counties']['city'], $data['counties']['state'], $county_zip, null, $county_metadata, null, $elected, null);

	$elected = null;
}


// ##########################################################################################################
// State Chambers Lower

if (!empty($data['state_chambers']['lower'])) {

	$rep_id = key($data['state_chambers']['lower']);


	// elected office

	$slc_reps = $data['state_chambers']['lower'][$rep_id]['reps'];	
	foreach($slc_reps as $slc_rep) { 		
		$slc_rep['photo_url'] = (!empty($slc_rep['photo_url'])) ? $slc_rep['photo_url'] : null;
		$reps[] = $this->elected_official_model('legislative', 'Representative', null, null, null, $slc_rep['full_name'], $slc_rep['url'], $slc_rep['photo_url'], null, null, null, null, null, null, null, null, strtoupper($slc_rep['state']), null, null, null, null);		
	}

	# $this->jurisdiction_model($type, $type_name, $level, $level_name, $name, $id, $url, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $last_updated, $metadata, $social_media, $elected_office, $service_discovery);



	// jurisdiction 

	$district = 'District ' . $rep_id;
	$type_name = (strtoupper($slc_rep['state']) == 'NY') ? 'Assembly' : 'House of Representatives';		
	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', $type_name, 'regional', 'State', $district, $rep_id, null, null, null, null, null, null, null, null, strtoupper($slc_rep['state']), null, null, null, null, $reps, null);

}
	

// ##########################################################################################################
// State Chambers Upper							
		
// filtering for DC here
if (!empty($data['state_chambers']['upper']) && (!empty($data['national_chambers']['house']['reps'][0]['district'])) && ($data['national_chambers']['house']['reps'][0]['district'] !== "0")) {

	$rep_id = key($data['state_chambers']['upper']);


	// Elected Office						

	$slc_reps = null;
	$slc_reps = $data['state_chambers']['upper'][$rep_id]['reps'];
	$reps = null;

	foreach($slc_reps as $slc_rep) {
		$slc_rep['photo_url'] = (!empty($slc_rep['photo_url'])) ? $slc_rep['photo_url'] : null;	
		$reps[] = $this->elected_official_model('legislative', 'Senator', null, null, null, $slc_rep['full_name'], $slc_rep['url'], $slc_rep['photo_url'], null, null, null, null, null, null, null, null, null, null, null, null, null);

	}			
			
	// Jurisdiction						


	$district = 'District ' . $rep_id;

		$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'Senate', 'regional', 'State', $district, $rep_id, null, null, null, null, null, null, null, null, null, null, null, null, null, $reps, null);

}	



// ##########################################################################################################
// State

		
if (!empty($data['state_data'])) {


	$state_metadata = (!empty($data['state_id'])) ? array(array("key" => "state_id", "value" => $data['state_id'])) : null;
	
// Governor
	$elected = array($this->elected_official_model('executive', 'Governor', null, null, null, $data['state_data']['governor'], $data['state_data']['governor_url'], null, null, null, null, null, null, null, null, null, null, null, null, null, null));

if (!empty($data['governor_data'])) {
	
	
	if ($data['governor_data']['phone']) {
		$phone = $this->format_phone($data['governor_data']['phone']);
	} else {
		$phone = null;
	}	
	
	
	
	$elected[0]['url_photo'] = $data['governor_data']['url_photo'];
	$elected[0]['phone'] = $phone;
	$elected[0]['address_1'] = $data['governor_data']['address_1'];	
	$elected[0]['address_2'] = $data['governor_data']['address_2'];	
	$elected[0]['address_city'] = $data['governor_data']['address_city'];	
	$elected[0]['address_state'] = $data['governor_data']['address_state'];	
	$elected[0]['address_zip'] = $data['governor_data']['address_zip'];							
	$elected[0]['url'] = (empty($elected[0]['url'])) ? $data['governor_data']['url_governor'] : $elected[0]['url'];
}

if (!empty($data['governor_sm'])) {

	$social_media = null;

	if(!empty($data['governor_sm']['twitter'])) {
		$twitter_username = substr($data['governor_sm']['twitter'], strrpos($data['governor_sm']['twitter'], '/')+1);
		$social_media[] = array("type" => "twitter","description" => "Twitter","username" => $twitter_username,"url" => $data['governor_sm']['twitter'],"last_updated" => null);
	}
	
	if(!empty($data['governor_sm']['facebook'])) {
		$social_media[] = array("type" => "facebook","description" => "Facebook","username" => null,"url" => $data['governor_sm']['facebook'],"last_updated" => null);
	}
	
	if(!empty($data['governor_sm']['youtube'])) {
		$social_media[] = array("type" => "youtube","description" => "Youtube","username" => null,"url" => $data['governor_sm']['youtube'],"last_updated" => null);
	}	
	
	if(!empty($data['governor_sm']['flickr'])) {
		$social_media[] = array("type" => "flickr","description" => "Flickr","username" => null,"url" => $data['governor_sm']['flickr'],"last_updated" => null);
	}	


	$elected[0]['social_media'] = $social_media;
}

// NY Specific
if ($data['state'] == 'NY') {
	
	$this->load->model('state_ny_model', 'ny');
	if(!$this->democracymap) $this->load->model('democracymap_model', 'democracymap');

	 $elected[]			= $this->ny->get_ag($this->democracymap);
	

}



	$new_data['jurisdictions'][] = $this->jurisdiction_model('government', 'State', 'regional', 'State', $data['state_geocoded'], $data['state'], $data['state_data']['official_name_url'], $data['state_data']['information_url'], $data['state_data']['email'], $data['state_data']['phone_primary'], null, null, null, null, null, null, null, $state_metadata, null, $elected, null);
	

}



// ##########################################################################################################	
// US House of Reps
	
	
if (!empty($data['national_chambers']['house']['reps'])) {


$nhr = $data['national_chambers']['house']['reps'][0];


	// elected office
	
		$social_media = null;

		if(!empty($nhr['twitter_id']) || !empty($nhr['facebook_id'])) {
			$social_media = array();			
		} else {
			$social_media = null;
		}

		if(!empty($nhr['twitter_id'])) {
			$social_media[] = array("type" => "twitter","description" => "Twitter","username" => $nhr['twitter_id'],"url" => "http://twitter.com/{$nhr['twitter_id']}","last_updated" => null);
		}
		
		if(!empty($nhr['facebook_id'])) {
			$social_media[] = array("type" => "facebook","description" => "Facebook","username" => $nhr['facebook_id'],"url" => "http://facebook.com/{$nhr['facebook_id']}","last_updated" => null);
		}
		
		if(!empty($nhr['youtube_url'])) {
			$social_media[] = array("type" => "youtube","description" => "Youtube","username" => null,"url" => $nhr['youtube_url'],"last_updated" => null);
		}		
		
		
		$img_url = $this->config->item('democracymap_root') . '/img/headshot/us-congress/' . $nhr['bioguide_id'] . '.jpg';


	$title = ($nhr['title'] == 'Rep') ? 'Representative' : null;

	$elected = 	array($this->elected_official_model('legislative', $title, null, null, null, $nhr['full_name'], $nhr['website'], $img_url, null, null, null, $nhr['phone'], null, null, null, null, null, null, null, null, $social_media));
	
	$title = null;
	$district = "District " . $nhr['district'];

	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'House of Representatives', 'national', 'United States', $district, $nhr['district'], null, null, null, null, null, null, null, null, null, null, null, null, null, $elected, null);

}





// ############################################################################################################

// US Senators

// filtering out DC here (removed this for a moment, but it's  if lower district != 0)
if (!empty($data['state_chambers']['upper']) && (!empty($data['national_chambers']['house']['reps'][0]['district'])) && ($data['national_chambers']['house']['reps'][0]['district'] !== "0")) {
	// Make sure these are empty
	$elected = null;
	$social_media = null;


	// Elected Office

	foreach($data['national_chambers']['senate']['reps'] as $slc_rep) {
		
		
		$img_url = $this->config->item('democracymap_root') . '/img/headshot/us-congress/' . $slc_rep['bioguide_id'] . '.jpg';


		if(!empty($slc_rep['twitter_id']) || !empty($slc_rep['facebook_id'])) {
			$social_media = array();			
		} else {
			//$social_media = null;
		}

		if(!empty($slc_rep['twitter_id'])) {
			$social_media[] = array("type" => "twitter",
							  							"description" => "Twitter",
							  							"username" => $slc_rep['twitter_id'],
						 	  							"url" => "http://twitter.com/{$slc_rep['twitter_id']}",
							  							 "last_updated" => null);
		}
		

		if(!empty($slc_rep['facebook_id'])) {
			$social_media[] = array("type" => "facebook",
			 	  									"description" => "Facebook",
			 	  									"username" => $slc_rep['facebook_id'],
			 	  									"url" => "http://facebook.com/{$slc_rep['facebook_id']}",
			 	  									 "last_updated" => null);
		}		
		
		if(!empty($slc_rep['youtube_url'])) {
			$social_media[] = array("type" => "youtube","description" => "Youtube","username" => null,"url" => $slc_rep['youtube_url'],"last_updated" => null);
		}		
		
		$title = ($slc_rep['title'] == 'Sen') ? 'Senator' : null;
		
		$elected[] = $this->elected_official_model('legislative', $title, $slc_rep['district'], $slc_rep['name_given'], $slc_rep['name_family'], $slc_rep['full_name'], $slc_rep['website'], $img_url, null, $slc_rep['url_contact'], $slc_rep['email'], $slc_rep['phone'], null, $slc_rep['congress_office'], null, null, null, null, null, null, $social_media);					

		$title = null;
}
	
	
// Jurisdiction 
	
$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'Senate', 'national', 'United States', $data['state_geocoded'], $data['state'], null, null, null, null, null, null, null, null, null, null, null, null, null, $elected, null);	
	

}


// Hard coding national data for now

if (!empty($new_data['jurisdictions'])) {
	
	$elected = null;
	$social_media = null;
	
	$social_media[] = array("type" => "twitter",
	 	  									"description" => "Twitter",
	 	  									"username" => "whitehouse",
	 	  									"url" => "http://twitter.com/whitehouse",
	 	  									 "last_updated" => null);	
	
	$elected[] = $this->elected_official_model('executive', 'President', null, 'Barack', 'Obama', 'Barack Obama', 'http://www.whitehouse.gov/administration/president-obama', 'http://www.whitehouse.gov/sites/default/files/imagecache/admin_official_lowres/administration-official/ao_image/President_Official_Portrait_HiRes.jpg', 'http://www.whitehouse.gov/schedule', 'http://www.whitehouse.gov/contact/submit-questions-and-comments', null, '202-456-1111', 'The White House', '1600 Pennsylvania Avenue NW', null, 'Washington', 'DC', '20500', null, null, $social_media);

	$social_media = null;
	$social_media[] = array("type" => "twitter",
	 	  									"description" => "Twitter",
	 	  									"username" => "VP",
	 	  									"url" => "https://twitter.com/VP",
	 	  									 "last_updated" => null);


	$elected[] = $this->elected_official_model('executive', 'Vice-President', null, 'Joseph', 'Biden', 'Joe Biden', 'http://www.whitehouse.gov/administration/vice-president-biden', 'http://www.whitehouse.gov/sites/default/files/imagecache/admin_official_lowres/administration-official/ao_image/vp_portrait_hi-res.jpg', null, 'http://www.whitehouse.gov/contact-vp', null, null, 'The White House', '1600 Pennsylvania Avenue NW', null, 'Washington', 'DC', '20500', null, null, $social_media);



	$social_media = null;
	$social_media[] = array("type" => "twitter",
	 	  									"description" => "Twitter",
	 	  									"username" => "USAgov",
	 	  									"url" => "http://twitter.com/USAgov",
	 	  									 "last_updated" => null);
	
	$new_data['jurisdictions'][] = $this->jurisdiction_model('government', 'Country', 'national', 'Country', 'United States of America', 'US', 'http://usa.gov', 'http://answers.usa.gov/system/selfservice.controller?CONFIGURATION=1000&PARTITION_ID=1&CMD=STARTPAGE&SUBCMD=EMAIL&USERTYPE=1&LANGUAGE=en&COUNTRY=us', null, '800-333-4636', 'USA.gov, U.S. General Services Administration', '1275 First Street, NE', null, 'Washington', 'DC', '20417',	null, null, $social_media, $elected, null);
								//$this->jurisdiction_model($type, $type_name, 		$level, 		$level_name, $name, $id, $url, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $last_updated, $metadata, $social_media, $elected_office, $service_discovery);	
	# $this->elected_official_model($type, $title, $description, $name_given, $name_family, $name_full, $url, $url_photo, $url_schedule, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $current_term_enddate, $last_updated, $social_media);	
	# $this->jurisdiction_model($type, $type_name, $level, $level_name, $name, $id, $url, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $last_updated, $metadata, $social_media, $elected_office, $service_discovery);
	
	
}



	//$new_data['raw_data'] = $data;					
	
	return $new_data;
}
	
	

	
// TODO: Consider doing a dynamic data model instantiation by just naming the object/array key names based on the name of the variable through a foreach loop. 		
	
function jurisdiction_model($type, $type_name, $level, $level_name, $name, $id, $url, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $last_updated, $metadata, $social_media, $elected_office, $service_discovery) {
	
	
$data['type'] 		  			= $type;		  	
$data['type_name'] 	  			= $type_name; 	    		  
$data['level'] 		  			= $level; 		    		  	
$data['level_name'] 			= $level_name;     		  	  	
$data['name'] 		  			= $name; 		    		  	
$data['id'] 					= $id; 		    		  	  	
$data['url'] 		  			= $url; 		    		  	
$data['url_contact']   			= $url_contact;    		  	
$data['email'] 		  			= $email; 		    		  	
$data['phone'] 		  			= $phone; 		    		  	
$data['address_name']   		= $address_name;   		  	
$data['address_1'] 	  			= $address_1; 	    		  	
$data['address_2'] 	  			= $address_2; 	    		  	
$data['address_city']  			= $address_city;  		  	
$data['address_state'] 			= $address_state;  		  	
$data['address_zip']    		= $address_zip;    		  	
$data['last_updated']   		= $last_updated;   		  	
$data['metadata']				= $metadata;		
$data['social_media']           = $social_media;		
$data['elected_office']         = $elected_office;		
$data['service_discovery']      = $service_discovery;

return $data;

}


function elected_official_model($type, $title, $description, $name_given, $name_family, $name_full, $url, $url_photo, $url_schedule, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $current_term_enddate, $last_updated, $social_media) {
	
$data['type'] 					=  $type; 				  
$data['title'] 					=  $title; 			   		
$data['description'] 			=  $description; 		   		
$data['name_given'] 			=  $name_given;		   		
$data['name_family'] 			=  $name_family; 		   		
$data['name_full'] 				=  $name_full; 		   		
$data['url'] 					=  $url; 				   		
$data['url_photo'] 				=  $url_photo; 		   		
$data['url_schedule'] 			=  $url_schedule; 		   		
$data['url_contact'] 			=  $url_contact; 		   		
$data['email'] 					=  $email; 			   		
$data['phone'] 					=  $phone; 			   		
$data['address_name']			=  $address_name;		   		
$data['address_1'] 				=  $address_1; 		   		
$data['address_2'] 				=  $address_2; 		   		
$data['address_city'] 			=  $address_city; 		   		
$data['address_state'] 			=  $address_state; 	   		
$data['address_zip'] 			=  $address_zip; 		   		
$data['current_term_enddate']	=  $current_term_enddate;		
$data['last_updated'] 			=  $last_updated; 		   		
$data['social_media'] 			=  $social_media; 		   		

return $data;

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


}

# $this->elected_official_model($type, $title, $description, $name_given, $name_family, $name_full, $url, $url_photo, $url_schedule, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $current_term_enddate, $last_updated, $social_media);
# $this->jurisdiction_model($type, $type_name, $level, $level_name, $name, $id, $url, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $last_updated, $metadata, $social_media, $elected_office, $service_discovery);




?>