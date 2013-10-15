<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sync extends CI_Controller {

	public $nameUtil = null;

	function __construct()
	{
		parent::__construct();
		
		// Load the Library
		// $this->load->library(array('user', 'user_manager'));
		
		
	   	$this->load->helper('names/nameutil');			
		$this->nameUtil = new HumanNameParser_Parser();
	   	
				
	}

	function index()
	{
		$this->load->helper('url');
		
		// Determine the environment we're run from for debugging/output 
		if (php_sapi_name() == 'cli') {   
			if (isset($_SERVER['TERM'])) {   
				$this->environment = 'terminal';  
			} else {   
				$this->environment = 'cron';
			}   
		} else { 
			$this->environment = 'server';
		}

		// sometimes this takes a while, make sure it doesn't timeout
		ini_set("max_execution_time", "1000");

		// if config != initialize mode then redirect otherwise run through init script. 
		// todo password protect this process
		
		
		
		if(!$this->config->item('sync_active')) {
			redirect('docs');				
		}


		// Construct the Jurisdiction Model
		//$this->load->model('democracymap_model', 'democracymap');
		//$jurisdiction = $this->democracymap->jurisdictions[0];




		$scraper_rate = $this->config->item('scraper_rate');
		$scheduled_scrapers = $this->scheduled_scrapers($scraper_rate);

		// foreach scheduled scrapers as scraper
		foreach ($scheduled_scrapers as $scraper) {
			$this->sync_data($scraper);
		}

	}
	
	function scheduled_scrapers($scraper_rate = null) {
		
		
		$due_date = gmdate("Y-m-d H:i:s", strtotime("-$scraper_rate hours"));

		// get overdue scrapers
		
		$this->db->select('id, scraperwiki_name, last_sync');	
		$this->db->where('mode', 'enabled');			
		$this->db->where('last_sync < ', $due_date);
		
		$query = $this->db->get('sync_scheduler');
			
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	
	}


	function sync_data($scraper) {
		
		$this->load->helper('api');
		
		// get scraper metadata, tables, number of rows, last run date		
		// This used to be so easy: $metadata = 'https://api.scraperwiki.com/api/1.0/scraper/getinfo?format=jsondict&name=' . $scraper['scraperwiki_name'] . '&version=-1';		
		
		// baseline metadata from scraperwiki new
		$query = 'https://premium.scraperwiki.com/' . $scraper['scraperwiki_name'] . '/sql/meta';		
		$metadata = curl_to_json($query);

		// custom metadata from scraperwiki new		
		$query = 'https://premium.scraperwiki.com/' . $scraper['scraperwiki_name'] . '/sql/?q=' . urlencode('select "name", "value_blob" from swvariables where name = "last_finish" or name = "last_start"');
		$metadata_custom = curl_to_json($query);
		
		if(!empty($metadata_custom)) {			
			foreach($metadata_custom as $data) {
				$metadata[$data['name']] = $data['value_blob'];
			}			
		}

		
		if(!empty($metadata)) {

			// make sure the last start date is earlier than the last finish date. Anything otherwise indicates it failed to complete a full run. 
			// TODO: this needs to be reconciled with scrapers that allow graceful partial runs. 
			if ( date(strtotime($metadata['last_finish'])) < date(strtotime($metadata['last_start'])) ) {
				
				// log this
				$description = "Scraper didn't complete last run, not syncing";
				$sources = 'https://premium.scraperwiki.com/' . $scraper['scraperwiki_name'];
				$data = array (
								'source' => $sources,
								'type' => 'scraper', 
								'description' => $description,
								'timestamp' => gmdate("Y-m-d\TH:i:s\Z")
								);

				$this->db->insert('sync_log', $data);				
								
				return false;
			}
			
			
			// make sure it's run since we last pulled it
			if (date(strtotime($scraper['last_sync'])) < date(strtotime($metadata['last_finish']))) {
				
				if(!empty($metadata['table'])) {

					$tables = $metadata['table'];

					// if(!empty($tables['jurisdictions'])) 
					
					if(!empty($tables['officials'])) {
						
						// Construct API call to scraper datastore with pagination
						
						// get total rows
						$query = 'https://premium.scraperwiki.com/' . $scraper['scraperwiki_name'] . '/sql/?q=' . urlencode('select count(*) as total_rows from "officials"');
						$metadata_custom = curl_to_json($query);		
												
						// total
						$total = $metadata_custom[0]['total_rows'];						
						$count = 0;
						$pagesize = 1000;
						
						while (($count * $pagesize) <= $total) {
							$offset = $count * $pagesize;							
							$url = 'https://premium.scraperwiki.com/' . $scraper['scraperwiki_name'] . '/sql/?q=select%20*%20from%20%60officials%60%20limit%20' . $offset . '%2C%20' . $pagesize;						
														
							if ($this->environment == 'terminal') {
								echo 'Calling ' . $url . PHP_EOL;
							}							
																												
							$officials = curl_to_json($url);
							
							if(!empty($officials)) {

								$ocdid = array();
								$skip = array();	

								if ($this->environment == 'terminal') {
									echo "Syncing " . count($officials) . ' officials' . PHP_EOL;	
								}

								foreach($officials as $official) {
									
									
									unset($official['id']);
									
									// probably unneeded, but just to make array key names more accessible/consistent
									$gov_name = urlify($official['government_name']);
									
									// if this gov's ocdid lookup has already failed and been logged, skip it
									if(!empty($skip[$gov_name])) {
										continue;
									}										
									
									// if we don't have an ocdid yet, get one
									if(empty($ocdid[$gov_name])) {	
										
										
										if ($this->environment == 'terminal') {
											echo "looking up " . $official['government_name'] . PHP_EOL;	
										}										
										
																			
										// Get the OCDID for this jurisdiction
										$ocdid[$gov_name] = $this->determine_ocdid($official);										
										
										
										if ($this->environment == 'terminal') {
											echo "looked up " . $ocdid[$gov_name] .  PHP_EOL;	
										}										
										
										
										// if the lookup failed, skip it
										if ($ocdid[$gov_name] === false) {
											$skip[$gov_name] = true;
											continue;
										}

									}
									
									// ensure we actually have the ocdid for this gov
									if(!empty($ocdid[$gov_name])) {
										
										// check for current data from db to merge
										
										// add the ocdid to the object for the official
										$official['meta_ocd_id'] = $ocdid[$gov_name];
										
										// remove temporary fields
										unset($official['government_name']);
										unset($official['government_level']);
																				
										// split up the name
										// nameUtil keys: leadingInit, first, nicknames, middle, last, suffix

										$names = null;										
										
										try {
											$this->nameUtil->setName($official['name_full']);
											$names = $this->nameUtil->getArray();											
										} 
										catch (Exception $e) {
											// report / log this
											if ($this->environment == 'terminal') {
												echo "Failed parsing name: " . PHP_EOL;
											}											
										}
										
										if(empty($names)) {
											$names = array();
											$names['first'] = $official['name_full'];
											$names['last'] = '';											
										}
										
										
										if ($this->environment == 'terminal') {
											echo "Attempting lookup of existing data for " . $official['name_full'] . PHP_EOL;
										}									
																				 																																					
										// get existing entry
										$this->db->select('*');		
										$this->db->where('meta_ocd_id', $official['meta_ocd_id']);			
										$this->db->where('title', $official['title']);		
										//$this->db->where('name_full', $official['name_full']);
										
										$this->db->like('name_full', $names['first']);
										$this->db->like('name_full', $names['last']);									

										$query = $this->db->get('scraped_officials');									

										// If existing entry found, run a comparison
										if ($query->num_rows() > 0) {
										   $official_db = $query->row_array(); 		
																														
											// remove any existing fields not used in new copy as to only compare changes 
											foreach ($official_db as $field => $value) {												
												if(empty($official[$field])) {
													unset($official[$field]);													
												}												
											}
											
											// temporarily remove source field before comparison
											$official_source = json_decode($official['sources'], true);										
											unset($official['sources']);
										
											// temporarily remove other_data field before comparison (currently just adding any other_data fields, not comparing)
											if(!empty($official['other_data'])) $official_other_data = json_decode($official['other_data'], true);																						
											unset($official['other_data']);
											
											if(!empty($official_db['other_data'])) $official_db_other_data = json_decode($official_db['other_data'], true);																						
											unset($official_db['other_data']);
																		
											// check to see if there are any differences between existing and new data
											$diff = array_diff_assoc($official, $official_db);
																						
											// if there are no differences, then skip
											if(empty($diff)) {
												
												// output for debugging
												if ($this->environment == 'terminal') {
													echo "skipping " . $official_db['name_full'] . ' for ' . $official_db['meta_ocd_id'] . PHP_EOL;
												}												
												
												continue;
												
											// if there are differences
											} else {
												
												// add conflicts from diff to conflicts field
												if(!empty($official_db['conflicting_data'])) {
													
													$conflicts = json_decode($official_db['conflicting_data'], true);
													
													// check to see if any of these conflicts already exist													
													foreach ($diff as $diff_field => $diff_value) {
														
														
														$duplicate = array();
														
														foreach ($conflicts as $conflict) {

															// see if this same key/value from the new data was already logged
															if($diff_field == $conflict['field'] && $official[$diff_field] == $conflict['value'] ) {
																$duplicate['new'] = true;
															}
															
															// see if this same key/value from the old data was already logged															
															if($diff_field == $conflict['field'] && $official_db[$diff_field] == $conflict['value'] ) {
																$duplicate['old'] = true;
															}															
															
																											
														}														
														
														// only add diffs if neither of them was empty/null
														if(!empty($official[$diff_field]) && !empty($official_db[$diff_field])) {
																														
															// if the new value isn't already listed, add it
															if(empty($duplicate['new'])) {
																$conflicts[] = array(
																					"field" => $diff_field,
																					"value" => $official[$diff_field], 
																					"source" => $official_source[0]['url'],
																					"timestamp" => gmdate("Y-m-d H:i:s")
																					);																		

															} 

															// if the old value isn't already listed, add it														
															if(empty($duplicate['old'])) {
																$conflicts[] = array(
																					"field" => $diff_field,
																					"value" => $official_db[$diff_field], 
																					"source" => NULL,
																					"timestamp" => gmdate("Y-m-d H:i:s")
																					);															
															}															
															
														}
														
														
														
														
													}
													

													
													
												// otherwise create a fresh entry for conflicts	
												} else {
													
													$conflicts = array();
																																							
													foreach($diff as $diff_field => $value) {
														
														if(!empty($official[$diff_field]) && !empty($official_db[$diff_field])) {

															$conflicts[] = array(
																				"field" => $diff_field,
																				"value" => $official[$diff_field], 
																				"source" => $official_source[0]['url'],
																				"timestamp" => gmdate("Y-m-d H:i:s")
																				);

															$conflicts[] = array(
																				"field" => $diff_field,
																				"value" => $official_db[$diff_field], 
																				"timestamp" => gmdate("Y-m-d H:i:s")
																				);																												
															
														}
														
													}
													
												}												
												
												
												// merge (really just overwrite) old values with new
												foreach($diff as $diff_field => $value) {
													$official_db[$diff_field] = $value;
												}
												
												// combine or re-insert other_data
												// TODO: if we're really smart we'd also compare within other_data
												if(!empty($official_db_other_data) && !empty($official_other_data)) {
													$official_db['other_data'] = array_merge($official_db_other_data, $official_other_data);
													$official_db['other_data'] = json_encode($official_db['other_data']);
												} else if (!empty($official_other_data)) {
													$official_db['other_data'] = json_encode($official_other_data);
												} else if (!empty($official_db_other_data)) {
													$official_db['other_data'] = json_encode($official_db_other_data);													
												} else {
													$official_db['other_data'] = null;
												}
												
												// add in conflicts field data
												if(!empty($conflicts)) {
													$official_db['conflicting_data'] = json_encode($conflicts);													
												}

												// add new source or update timestamp on existing entry
												if(!empty($official_db['sources'])) {
													
													$official_db['sources'] = json_decode($official_db['sources'], true);													
													$output_sources = array();
													$duplicate = false;
													
													foreach($official_db['sources'] as $source) {
														
														// if found and already listed, update timestamp
														if($source['url'] == $official_source[0]['url']) {
															$source['timestamp'] = gmdate("Y-m-d H:i:s");															
															$output_sources[] = $source;
															$duplicate = true;
														} else {
															$output_sources[] = $source;
														}
														
													}
													
													if(!$duplicate) {
														
														// Set timestamp if not already
														if(empty($official_source[0]['timestamp'])) {
															$official_source[0]['timestamp'] =  gmdate("Y-m-d H:i:s");
														} 
														
														$output_sources[] = $official_source[0];
													}
													
													$official_db['sources'] = json_encode($output_sources);
												
												// if no existing entries in sources field, just add what we have from scraper (this should never happen)	
												} else {
													
													$official_db['sources'] = json_encode($official_source);
													
												}
												
												
												// todo: insert/merge anything from the other_data field
												
												// set current timestamp
												$official_db['last_updated'] = gmdate("Y-m-d H:i:s");
															
												// unset internal id
												unset($official_db['meta_internal_id']);
																																			
												// update db
												$this->db->where('title', $official_db['title']);																								
												$this->db->where('meta_ocd_id', $official_db['meta_ocd_id']);
																		
												// unfortunately this simpler syntax is not supported in the current version of CI													
												//$this->db->like('name_full', $names['first']);
												//$this->db->like('name_full', $names['last']);																										
												
												$where = $this->db->escape_like_str($names['first']);
											 	$where = "name_full LIKE '%$where%'";
											   	$this->db->where($where);												
												
												$where = $this->db->escape_like_str($names['last']);
											 	$where = "name_full LIKE '%$where%'";
											   	$this->db->where($where);												
												
												$this->db->update('scraped_officials', $official_db);												
														
												// output for debugging
												if ($this->environment == 'terminal') {
													echo "just updated " . $official_db['name_full'] . ' for ' . $official_db['meta_ocd_id'] . PHP_EOL;
												}											
											
											
											}
											

										
										} else {											
											
											// add as brand new entry in the database
											
											// set current timestamp
											$official['last_updated'] = gmdate("Y-m-d H:i:s");																								
																																										
											// add to the db															
											$this->db->insert('scraped_officials', $official);
											
											// output for debugging
											if ($this->environment == 'terminal') {
												echo "just added " . $official['name_full'] . ' for ' . $official['meta_ocd_id'] . PHP_EOL;
											}

											
										}									
										
										

										
										
									} 
									
									
									
								}
								
							}
									
							$count++;
						}
						
					}
								

				} else {
					// scraper data not available. eg scraperwiki not responding to this api call

					// log this error	
					// echo "scraper data not available. eg scraperwiki not responding to this api call";
				}					
				
						

			}			
			
	
		} else {
			// scraper not found
			
			// log this error
			
			return false;
		}


	
		
		
	}
	
	
	function determine_ocdid($gov) {
		
		// make sure this works for officials and jurisdictions using slighty different field names (todo: possibly fix naming)
		if((empty($gov['level']) && !empty($gov['government_level'])) && (empty($gov['name']) && !empty($gov['government_name']))) {			
			$gov['level'] = $gov['government_level'];
			$gov['name'] = $gov['government_name'];						
		}
		
		if (!empty($gov['level']) && !empty($gov['name']) && !empty($gov['address_region'])) {

			// lookup ocdid		

			$this->db->select('ocd_id');		
			$this->db->where('name', $gov['name']);			
			$this->db->where('level', $gov['level']);
			$this->db->where('address_region', $gov['address_region']);		

			$query = $this->db->get('scraped_jurisdictions');

			// if successful return ocdid
			if ($query->num_rows() > 0) {
			   $row = $query->row(); 		
			   return $row->ocd_id;
			// if lookup fails, log it
			} else {

				$description = "Unable to lookup OCD ID for {$gov['level']} {$gov['name']} {$gov['address_region']}";

				$sources = json_decode($gov['sources'], true);

				// log this
				$data = array (
								'source' => $sources[0]['url'],
								'type' => 'jurisdiction', 
								'description' => $description,
								'timestamp' => gmdate("Y-m-d H:i:s")								
								);

				$this->db->insert('sync_log', $data);			

				// output for debugging
				if ($this->environment == 'terminal') {
					echo $description . PHP_EOL;
				}


				return false;
			}			
			
			
			
		} else {
			
			// lookup failed
			
			// log this 'unable to lookup ocdid, not enough info. Supplied info was: level: $level, name: $name, address_region: $address_region'
			
			return false;
		}
		
		

	
		
		
	}
	
	
	
	
}
