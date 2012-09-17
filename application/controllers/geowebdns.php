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
									      	        		
		
			$input 						= $this->input->get('location', TRUE);
			
			if(!$input) {
				
				$this->response('No location provided', 400);

			}
			
			$fullstack 					= $this->input->get('fullstack', TRUE);
			$location 					= $this->geocode(urlencode($input));

			if(!empty($location['ResultSet']['Result'])) {
			
				$data['latitude'] 			= $location['ResultSet']['Result'][0]['latitude'];
				$data['longitude'] 			= $location['ResultSet']['Result'][0]['longitude'];
				$data['city_geocoded'] 		= $location['ResultSet']['Result'][0]['city'];
				$data['state_geocoded'] 	= $location['ResultSet']['Result'][0]['state'];

				$latlong 					= $data['latitude'] . " " . $data['longitude'];									
			}
			
			// If we're not using a geocoder, but we have a lat,long directly...
			// there should be validation here to see if provided latlong is actually valid and well-formed
			if (!$latlong) $latlong = $input;



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
			
			
			// See if we have google analytics tracking code
			if($this->config->item('ganalytics_id')) {
				//$data['ganalytics_id'] = $this->config->item('ganalytics_id');
			}
			
			
			if ($fullstack == 'true') {
				$this->response($data, 200);
			} else
			{
						
			$endpoint['url'] = $data['place_url'];
			
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
			//curl_setopt($ch, CURLOPT_HEADER, TRUE);		

			$fid_data=curl_exec($ch);			
			$feature_data = json_decode($fid_data, true);	
			curl_close($ch);

			return $feature_data;

	}	
	
	
	
	function get_servicediscovery($url) {	
		
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			//curl_setopt($ch, CURLOPT_HEADER, TRUE);		

			$sd_data=curl_exec($ch);			
			$data = json_decode($sd_data, true);	
			curl_close($ch);

			return $data;

	}	
	
	

	


	function geocode($location) {

		$url = "http://where.yahooapis.com/geocode?q=" . $location . "&appid=" . $this->config->item('yahoo_api_key') . "&flags=p&count=1";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
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
	
	
	
	
	
	

	
	
	
	


}
?>