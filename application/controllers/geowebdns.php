<?php
require APPPATH.'/libraries/REST_Controller.php';

class Geowebdns extends REST_Controller {


	public function index_get()
	{
		$this->load->view('welcome_message');
	}

	
	function endpoints_get()	{
		
		
		$data['latitude'] 			  = '';
		$data['longitude'] 			  = '';
		$data['city_geocoded'] 		  = '';
		$data['state_geocoded'] 	  = '';          				

		$data['council_district'] 	  = '';
		$data['community_district']   = '';

		$data['fid'] 				  = '';
		$data['state_id'] 	   		  = '';
		$data['place_id'] 	   		  = '';

		$data['gnis_fid']		      = '';
		$data['place_name'] 	      = '';
		$data['political_desc']       = '';
		$data['title']		          = '';
		$data['address1']  	          = '';
		$data['address2']  	          = '';
		$data['city']		 	      = '';
		$data['zip']		 	      = '';
		$data['zip4']		 	      = '';
		$data['state']	 	          = '';
		$data['place_url'] 	          = '';
		$data['population'] 	      = '';
		$data['county']		          = '';

		$data['community_district']	  = '';
		$data['community_district_fid'] = '';
		$data['community_board']      = '';
		$data['cd_address']		      = '';
		$data['borough']			  = ''; 
		
		$latlong					  = ''; 
									      	        		
		
			$data['input'] 						= $this->input->get('location', TRUE);
			
			if(!$data['input'] ) {
				
				$this->response('No location provided', 400);

			}
			
			$fullstack 					= $this->input->get('fullstack', TRUE);
			$location 					= $this->geocode(urlencode($data['input']));

			if(!empty($location['ResultSet']['Result'])) {
			
				$data['latitude'] 			= $location['ResultSet']['Result'][0]['latitude'];
				$data['longitude'] 			= $location['ResultSet']['Result'][0]['longitude'];
				$data['city_geocoded'] 		= $location['ResultSet']['Result'][0]['city'];
				$data['state_geocoded'] 	= $location['ResultSet']['Result'][0]['state'];

				$latlong 					= $data['latitude'] . " " . $data['longitude'];									
			}
			
			// If we're not using a geocoder, but we have a lat,long directly...
			// there should be validation here to see if provided latlong is actually valid and well-formed
			if (!$latlong) $latlong = $data['input'];



			// nytimes district API 
			/*
			if($latlong) {
				$districts = $this->districts($data['latitude'], $data['longitude']);
				
				
				if(!empty($districts['results'][1])) {
					
					$data['state_senate_id'] = $districts['results'][1]['district'];
					$data['state_senate_kml'] = $districts['results'][1]['kml_url'];
				
					$data['state_assembly_id'] = $districts['results'][2]['district'];
					$data['state_assembly_kml'] = $districts['results'][2]['kml_url'];
				
					$data['us_house_id'] = $districts['results'][4]['district'];
					$data['us_house_kml'] = $districts['results'][4]['kml_url'];				
				}
				
			}
			*/


			if($latlong && $fullstack == 'true') {
				$state_legislators = $this->state_legislators($data['latitude'], $data['longitude']);
				$state_chambers = $this->process_state_legislators($state_legislators);

				$data['state_chambers'] = $state_chambers;

				
				$national_legislators = $this->national_legislators($data['latitude'], $data['longitude']);		
				$national_chambers = $this->process_nat_legislators($national_legislators); 		
				
				ksort($national_chambers);
				
				$data['national_chambers'] = $national_chambers;				
				
			}



			if ($latlong) {
				
							
				$census 					= $this->layer_data('census:municipal', 'placefp,state,uid', $latlong);
				
				if ($fullstack == 'true') {
					
					$council 					= $this->layer_data('census:city_council', 'coundist,gid', $latlong);
					$community_d 				= $this->layer_data('census:community_district', 'borocd,gid', $latlong);

					$data['council_district'] 	= (!empty($council['features'])) ? $council['features'][0]['properties']['coundist'] : '';
					$data['council_district_fid'] 	= (!empty($council['features'])) ? $council['features'][0]['properties']['gid'] : '';			
		
			
					$data['community_district'] = (!empty($community_d['features'])) ? $community_d['features'][0]['properties']['borocd'] : '';
					$data['community_district_fid'] = (!empty($community_d['features'])) ? "community_district." . $community_d['features'][0]['properties']['gid'] : '';

				}
				

				if (!empty($census['features'])) {
					
					$data['fid'] 				= 'municipal.' . $census['features'][0]['properties']['uid'];
					$data['state_id'] 	   		= $census['features'][0]['properties']['state'];    
					$data['place_id'] 	   		= $census['features'][0]['properties']['placefp'];   


					$sql = "SELECT municipalities.GOVERNMENT_NAME, 
					       	 	 	 municipalities.POLITICAL_DESCRIPTION, 
									 municipalities.TITLE, 
									 municipalities.ADDRESS1,
									 municipalities.ADDRESS2, 
									 municipalities.CITY, 
									 municipalities.STATE_ABBR, 
									 municipalities.ZIP, 
									 municipalities.ZIP4, 
									 municipalities.WEB_ADDRESS,
									 municipalities.POPULATION_2005, 
									 municipalities.COUNTY_AREA_NAME, 
		 							 municipalities.MAYOR_NAME, 
									 municipalities.MAYOR_TWITTER, 
									 municipalities.SERVICE_DISCOVERY, 
									 gnis.FEATURE_ID, 
									 gnis.PRIMARY_LATITUDE, 
									 gnis.PRIMARY_LONGITUDE
					   		  	FROM gnis, municipalities
										      WHERE (municipalities.FIPS_PLACE = gnis.CENSUS_CODE
										      	    )
											     AND (municipalities.FIPS_STATE = gnis.STATE_NUMERIC
											     	 )
												 AND (municipalities.FIPS_PLACE = '{$data['place_id']}'
												      )
												 AND (municipalities.FIPS_STATE = '{$data['state_id']}')
												";


					$query = $this->db->query($sql);


					if ($query->num_rows() > 0) {
					   foreach ($query->result() as $rows)  {
					      $data['gnis_fid']			=  ucwords(strtolower($rows->FEATURE_ID));
					      $data['place_name'] 		=  ucwords(strtolower($rows->GOVERNMENT_NAME));
					      $data['political_desc'] 	=  ucwords(strtolower($rows->POLITICAL_DESCRIPTION));
					      $data['title']			=  ucwords(strtolower($rows->TITLE));
					      $data['address1']  		=  ucwords(strtolower($rows->ADDRESS1));
					      $data['address2']  		=  ucwords(strtolower($rows->ADDRESS2));
					      $data['city']		 		=  ucwords(strtolower($rows->CITY));
						  $data['mayor_name']		=  $rows->MAYOR_NAME;
						  $data['mayor_twitter']	=  $rows->MAYOR_TWITTER;
						  $data['service_discovery'] =  $rows->SERVICE_DISCOVERY;	
					      $data['zip']		 		=  $rows->ZIP;
					      $data['zip4']		 		=  $rows->ZIP4;
					      $data['state']	 		=  $rows->STATE_ABBR;
					      $data['place_url'] 	   =  $rows->WEB_ADDRESS;
					      $data['population'] 	   =  $rows->POPULATION_2005;
					      $data['county'] 		   =  ucwords(strtolower($rows->COUNTY_AREA_NAME));
					   }
					}
				}	
				
				
			
				// Better links for municipal data from the SBA (I'm only pulling out the url, but other data might be usefull too)
				if (!empty($data['city']) && !empty($data['state'])) {
					$city_data = $this->get_city_links($data['city'], $data['state']);

					if(!empty($city_data)) {
						$data['place_url_updated'] = $city_data[0]['url'];

						if ($fullstack == 'true') $data['city_data'] = $city_data[0];
					}
				}			
			
			
				// County lookup - this should be based on a geospatial query, but just faking it until the layers are available
				if (!empty($data['city_data']['county_name'])) {
				
				
				$county_name = strtoupper($data['city_data']['county_name']);
				
					$sql = "SELECT * FROM counties
							WHERE name = '$county_name' and state = '{$data['state']}'";
							
				
					$query = $this->db->query($sql);				
						
					if ($query->num_rows() > 0) {
					   foreach ($query->result() as $rows)  {	
							$data['counties']['county_id']					=  $rows->county_id; 	
							$data['counties']['name']							=  ucwords(strtolower($rows->name));			
							$data['counties']['political_description']		=  $rows->political_description;
							$data['counties']['title']						=  ucwords(strtolower($rows->title)); 			
							$data['counties']['address1']						=  ucwords(strtolower($rows->address1)); 	    
							$data['counties']['address2']						=  ucwords(strtolower($rows->address2)); 	
							$data['counties']['city']							=  ucwords(strtolower($rows->city));   
							$data['counties']['state']						=  $rows->state;  
							$data['counties']['zip']							=  $rows->zip;    
							$data['counties']['zip4']							=  $rows->zip4;   
							$data['counties']['website_url']					=  $rows->website_url; 
							$data['counties']['population_2006']				=  $rows->population_2006;
							$data['counties']['fips_state']					=  $rows->fips_state; 
							$data['counties']['fips_county']					=  $rows->fips_county; 	        			      			      	                               
					   }
					}				


				
				}				
				



				
				
			
				if (is_numeric($data['community_district']) && $fullstack == 'true') {
				
				
					$sql = "SELECT * FROM community_boards
							WHERE city_id = {$data['community_district']}";


					$query = $this->db->query($sql);				
						
					if ($query->num_rows() > 0) {
					   foreach ($query->result() as $rows)  {	
							$data['community_board']		=  $rows->community_board;			
							$data['cd_address']				=  $rows->address;	
							$data['borough']				=  $rows->borough;
							$data['board_meeting']			=  $rows->board_meeting;
							$data['cabinet_meeting']		=  $rows->cabinet_meeting;
							$data['chair']					=  $rows->chair;
							$data['district_manager']		=  $rows->district_manager;
							$data['website']				=  $rows->website;	
							$data['email']					=  $rows->email;	
							$data['phone']					=  $rows->phone;	
							$data['fax']					=  $rows->fax;		
							$data['neighborhoods']			=  $rows->neighborhoods;		      	
									      	
					   }
					}				
				
				}



				if (is_numeric($data['council_district']) && $fullstack == 'true') {
				
				
					$sql = "SELECT * FROM council_districts
							WHERE district = {$data['council_district']}";


					$query = $this->db->query($sql);				
						
					if ($query->num_rows() > 0) {
					   foreach ($query->result() as $rows)  {	
							$data['c_address']					=  $rows->address;	
							$data['c_committees']				=  $rows->committees;	
							$data['c_term_expiration']			=  $rows->term_expiration;
							$data['c_district_fax']			=  $rows->district_fax;
							$data['c_district_phone']			=  $rows->district_phone;	
							$data['c_email']					=  $rows->email;
							$data['c_council_member_since']	=  $rows->council_member_since;
							$data['c_headshot_photo']			=  $rows->headshot_photo;	
							$data['c_legislative_fax']			=  $rows->legislative_fax;	
							$data['c_legislative_address']		=  $rows->legislative_address;	
							$data['c_legislative_phone']		=  $rows->legislative_phone;
							$data['c_name']					=  $rows->name;		
							$data['c_twitter_user']					=  $rows->twitter_user;								      	
									      	
					   }
					}				
				
				}

			
				
								
			}
			
			// get GeoJSON from GeoServer
			if ($this->input->get('geojson', TRUE) == 'true') {				
				$data['geojson'] = $this->get_geojson($data['fid']);
			}
			
			// Service Discovery
			if (strlen($data['service_discovery']) > 0) {
				$data['service_discovery'] = $this->get_servicediscovery($data['service_discovery']);
			}
			
		
			
			
			// Mayor data
			if (!empty($data['city']) && !empty($data['state'])) {
				$mayor = $this->get_mayors($data['city'], $data['state']);
				
				if(!empty($mayor)) {
					$data['mayor_data'] = $mayor[0];				
				}
			}			
			
			
			// State data
			if (!empty($data['state_geocoded'])) {
				$state = $this->get_state($data['state_geocoded']);
				
				if(!empty($state)) {
					$data['state_data'] = $state[0];				
				}
			}			
			

			// See if we have google analytics tracking code
			if($this->config->item('ganalytics_id')) {
				//$data['ganalytics_id'] = $this->config->item('ganalytics_id');
			}
			
			
			if ($fullstack == 'true') {
				
				$new_data = $this->re_schema($data);
				
				$this->response($new_data, 200);
			} else
			{
						
			$endpoint['url'] = (!empty($data['place_url_updated'])) ? $data['place_url_updated'] : $data['place_url'];
			
			// In this case we're just publishing service discovery and geojson
			$endpoint['service_discovery'] 	= $data['service_discovery'];
			
			// only return geojson if requested
			if (isset($data['geojson'])) $endpoint['geojson'] = $data['geojson'];
			
			$this->response($endpoint, 200);
			}
			
			
	}
	

	
	function data()
	{
		$this->db->where('id', $this->uri->segment(3));
		$data['query'] = $this->db->get('dataset');
		$data['agencies'] = $this->db->get('agency');

		$this->load->view('map_view', $data);
	}	
	
	function dataset_add()
	{
		$data['query'] = $this->db->get('agency');
		
		$this->load->view('dataset_add', $data);
	}	


	function dataset_insert()
	{
			$this->db->insert('dataset', $_POST);
				
			redirect('dataset/data/'.$this->db->insert_id());
	}
	
	
	
	
	function layer_data($layer, $properties, $latlong) {


		$gid_url = $this->config->item('geoserver_root') . 
			"/wfs?request=GetFeature&service=WFS&typename=" . 
			rawurlencode($layer) . 
			"&propertyname=" . 
			rawurlencode($properties) .
			"&CQL_FILTER=" . 
			rawurlencode("INTERSECT(the_geom, POINT (" . $latlong . "))") . 
			"&outputformat=JSON";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $gid_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);		
		
		//curl_setopt($ch, CURLOPT_HEADER, TRUE);		
		
		$gid_data=curl_exec($ch);			
		$feature_data = json_decode($gid_data, true);	
		curl_close($ch);

		return $feature_data;

	}
	
	
	function get_geojson($feature_id) {	
		
		$fid_url = $this->config->item('geoserver_root') . '/wfs?request=getFeature&outputFormat=json&layers=census:municipal&featureid=' . $feature_id; 
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $fid_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			//curl_setopt($ch, CURLOPT_HEADER, TRUE);		

			$fid_data=curl_exec($ch);			
			$feature_data = json_decode($fid_data, true);	
			curl_close($ch);

			return $feature_data;

	}	
	
	
	function get_city_links($city, $state) {	
			
			$city = urlencode(strtolower($city));
			$state = urlencode(strtolower($state));
		
			$url = "http://api.sba.gov/geodata/all_links_for_city_of/$city/$state.json";
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			//curl_setopt($ch, CURLOPT_HEADER, TRUE);		

			$sd_data=curl_exec($ch);			
			$data = json_decode($sd_data, true);	
			curl_close($ch);

			return $data;

	}	
	
	
	
	function get_servicediscovery($url) {	
		
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			//curl_setopt($ch, CURLOPT_HEADER, TRUE);		

			$sd_data=curl_exec($ch);			
			$data = json_decode($sd_data, true);	
			curl_close($ch);

			return $data;

	}	
	
	
	
	
	
	function get_mayors($city, $state) {
		
		$city = ucwords($city);
		$state = strtoupper($state);		
		
		$query = "select * from `swdata` where city = '$city' and state = '$state' limit 1";		
		$query = urlencode($query);
		
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=us_mayors&query=$query";		

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$mayors=curl_exec($ch);
		curl_close($ch);

		$mayors = json_decode($mayors, true);

		return $mayors;

	}



	
	
	function get_state($state) {
		
		$state = ucwords($state);		
		
		$query = "select * from `swdata` where state = '$state' limit 1";		
		$query = urlencode($query);
		
		$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=50_states_data&query=$query";		

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$state=curl_exec($ch);
		curl_close($ch);

		$state = json_decode($state, true);

		return $state;

	}




	function geocode($location) {

		$url = "http://where.yahooapis.com/geocode?q=" . $location . "&appid=" . $this->config->item('yahoo_api_key') . "&flags=p&count=1";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$locations=curl_exec($ch);
		curl_close($ch);


		$location = unserialize($locations);

		return $location;

	}	
	
	
	
	function districts($lat, $long) {
		
		$url = "http://api.nytimes.com/svc/politics/v2/districts.json?&lat=" . $lat . "&lng=" . $long . "&api-key=" . $this->config->item('nytimes_api_key');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$districts=curl_exec($ch);
		curl_close($ch);


		$districts = json_decode($districts, true);

		return $districts;

	}	
	
	
	function state_legislators($lat, $long) {
		
		$url = "http://openstates.org/api/v1/legislators/geo/?long=" . $long . "&lat=" . $lat . "&fields=state,chamber,district,full_name,url,photo_url&apikey=" . $this->config->item('sunlight_api_key');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$state_legislators=curl_exec($ch);
		curl_close($ch);


		$state_legislators = json_decode($state_legislators, true);

		return $state_legislators;				
		
	}
	

	
	function state_boundaries($state, $chamber) {
		
		$url = "http://openstates.org/api/v1/districts/" . $state . "/" . $chamber . "/?fields=name,boundary_id&apikey=" . $this->config->item('sunlight_api_key');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$state_boundaries=curl_exec($ch);
		curl_close($ch);

		$state_boundaries = json_decode($state_boundaries, true);
		$state_boundaries = $this->process_boundaries($state_boundaries);

		return $state_boundaries;

	}

	
	
	
	function state_boundary_shape($boundary_id) {
		
		$url = "http://openstates.org/api/v1/districts/boundary/" . $boundary_id . "/?apikey=" . $this->config->item('sunlight_api_key');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$shape_data=curl_exec($ch);
		curl_close($ch);

		$geojson = json_decode($shape_data, true);	

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
							'full_name' => $repdata['full_name']
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
		
		
	
		return $chambers;
	
}	
	
	
	
function national_legislators($lat, $long) {
	
	$url = "http://services.sunlightlabs.com/api/legislators.allForLatLong.json?latitude=" . $lat . "&longitude=" . $long . "&apikey=" . $this->config->item('sunlight_api_key');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$legislators=curl_exec($ch);
	curl_close($ch);


	$legislators = json_decode($legislators, true);
	$legislators = $legislators['response']['legislators'];

	return $legislators;				
	
}
	
	
	
function process_nat_legislators($representatives) {
	

	
		// Clean up data model
		foreach($representatives as $repdata){
			
			$repdata = $repdata['legislator'];
			
			$full_name = $repdata['firstname'] . ' ' . $repdata['lastname'];
			
		
				$rep = array(
							'district' 		=> $repdata['district'], 
							'full_name' 	=> $full_name, 
							'bioguide_id' 	=> $repdata['bioguide_id'], 
							'website' 		=> $repdata['website'], 
							'title' 	=> $repdata['title'],								
							'phone' 	=> $repdata['phone'], 
							'twitter_id' 	=> $repdata['twitter_id'],														
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


function re_schema($data) {
	
	
	
	$new_data['latitude']			= $data['latitude'];
	$new_data['longitude']			= $data['longitude'];
	$new_data['input_location']		= $data['input'];	

			

// ############################################################################################################
// Municipal 

// Elected
	
if (!empty($data['mayor_twitter'])) {

		$mayor_socialmedia = array(array("type" => "twitter",
						  "description" => "twitter",
						  "username" => $data['mayor_twitter'],
					 	  "url" => "http://twitter.com/{$data['mayor_twitter']}",
						   "last_updated" => '2007-06-22T20:59:09Z'));	

} else {
		$mayor_socialmedia = null;
}
	
$mayor_name_full 			= isset($data['mayor_data']['name']) ? $data['mayor_data']['name'] : null;	
$mayor_url 					= isset($data['mayor_data']['url']) ? $data['mayor_data']['url'] : null;
$mayor_url_photo 			= isset($data['mayor_data']['url_photo']) ? $data['mayor_data']['url_photo'] : null;
$mayor_email 				= isset($data['mayor_data']['email']) ? $data['mayor_data']['email'] : null;
$mayor_phone 				= isset($data['mayor_data']['phone']) ? $data['mayor_data']['phone'] : null;	
$mayor_current_term_enddate = isset($data['mayor_data']['current_term_enddate']) ? date('c', strtotime($data['mayor_data']['next_election'])) : null;	
	

$elected = array(
		$this->elected_official_model('executive', 'Mayor', null, null, null, $mayor_name_full, $mayor_url, $mayor_url_photo, null, null, $mayor_email, $mayor_phone, $data['title'], null, null, null, null, null, $mayor_current_term_enddate, null, $mayor_socialmedia)		
);



// Jurisdiction


$municipal_zip 		= ($data['zip4']) ? $data['zip'] . '-' . $data['zip4'] : $data['zip'];	

$municipal_metadata = array(array("key" => "place_id", "value" => $data['place_id']), 
								array("key" => "gnis_fid", "value" => $data['gnis_fid']));											
	

$new_data['jurisdictions'][] = $this->jurisdiction_model('government', 'City', 'municipal', 'City', $data['city'], null, $data['place_url_updated'], null, null, null, $data['title'], $data['address1'], $data['address2'], $data['city'], $data['state'], $municipal_zip, null, $municipal_metadata, null, $elected, $data['service_discovery']);

	
	
// ##########################################################################################################
// Counties Jurisdictions

if (!empty($data['counties'])) {

	$county_zip 		=  ($data['counties']['zip4']) ? $data['counties']['zip'] . '-' . $data['counties']['zip4'] : $data['counties']['zip'];		

	$county_metadata = array(array("key" => "fips_id", "value" => $data['counties']['fips_county']), 
							array("key" => 'county_id', "value" => $data['counties']['county_id']), 
							array("key" => 'population', "value" => $data['counties']['population_2006']));										
	

	$new_data['jurisdictions'][] = 	$this->jurisdiction_model('government', 'County', 'sub_regional', 'county', $data['counties']['name'], $data['counties']['county_id'], $data['counties']['website_url'], null, null, null, $data['counties']['title'], $data['counties']['address1'], $data['counties']['address2'], $data['counties']['city'], $data['counties']['state'], $county_zip, null, $county_metadata, null, null, null);

}


// ##########################################################################################################
// State Chambers Lower

if (!empty($data['state_chambers']['lower'])) {

	$rep_id = key($data['state_chambers']['lower']);


	// elected office

	$slc_reps = $data['state_chambers']['lower'][$rep_id]['reps'];	
	foreach($slc_reps as $slc_rep) {		
		$reps[] = $this->elected_official_model('legislative', 'Representative', null, null, null, $slc_rep['full_name'], $slc_rep['url'], $slc_rep['photo_url'], null, null, null, null, null, null, null, null, null, null, null, null, null);		
	}


	// jurisdiction 

	$district = 'District ' . $rep_id;		
	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'House of Representatives', 'regional', 'State', $district, $rep_id, null, null, null, null, null, null, null, null, null, null, null, null, null, $reps, null);
	

}
	

// ##########################################################################################################
// State Chambers Upper							
		
if (!empty($data['state_chambers']['upper'])) {

	$rep_id = key($data['state_chambers']['upper']);


	// Elected Office						

	$slc_reps = null;
	$slc_reps = $data['state_chambers']['upper'][$rep_id]['reps'];
	$reps = null;

	foreach($slc_reps as $slc_rep) {
	
		$reps[] = $this->elected_official_model('legislative', 'Senator', null, null, null, $slc_rep['full_name'], $slc_rep['url'], $slc_rep['photo_url'], null, null, null, null, null, null, null, null, null, null, null, null, null);

	}			
			
	// Jurisdiction						

		$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'Senate', 'regional', 'State', $district, $rep_id, null, null, null, null, null, null, null, null, null, null, null, null, null, $reps, null);

}	



// ##########################################################################################################
// State

		
if (!empty($data['state_data'])) {

	$state_metadata = array(array("key" => "state_id", "value" => $data['state_id']));										
	

	$elected = array($this->elected_official_model('executive', 'Governor', null, null, null, $data['state_data']['governor'], $data['state_data']['governor_url'], null, null, null, null, null, null, null, null, null, null, null, null, null, null));

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


	$elected = 	array($this->elected_official_model('legislative', $nhr['title'], null, null, null, $nhr['full_name'], $nhr['website'], null, null, null, null, $nhr['phone'], null, null, null, null, null, null, null, null, $social_media));
	

	$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'House of Representatives', 'national', 'United States', $district, $nhr['district'], null, null, null, null, null, null, null, null, null, null, null, null, null, $elected, null);

}





// ############################################################################################################

// US Senators


if (!empty($data['national_chambers']['senate'])) {

	// Make sure these are empty
	$elected = null;
	$social_media = null;


	// Elected Office

	foreach($data['national_chambers']['senate']['reps'] as $slc_rep) {
		

		if(!empty($slc_rep['twitter_id']) || !empty($slc_rep['facebook_id'])) {
			$social_media = array();			
		} else {
			$social_media = null;
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
		
		
		$elected[] = $this->elected_official_model('legislative', $slc_rep['title'], $slc_rep['district'], null, null, $slc_rep['full_name'], $slc_rep['website'], null, null, null, $slc_rep['email'], $slc_rep['phone'], null, $slc_rep['congress_office'], null, null, null, null, null, null, null);					

}
	
	
// Jurisdiction 
	
$new_data['jurisdictions'][] = $this->jurisdiction_model('legislative', 'Senate', 'national', 'United States', $data['state_geocoded'], $data['state'], null, null, null, null, null, null, null, null, null, null, null, null, null, $elected, null);	
	

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

}

# $this->elected_official_model($type, $title, $description, $name_given, $name_family, $name_full, $url, $url_photo, $url_schedule, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $current_term_enddate, $last_updated, $social_media);
# $this->jurisdiction_model($type, $type_name, $level, $level_name, $name, $id, $url, $url_contact, $email, $phone, $address_name, $address_1, $address_2, $address_city, $address_state, $address_zip, $last_updated, $metadata, $social_media, $elected_office, $service_discovery);


?>