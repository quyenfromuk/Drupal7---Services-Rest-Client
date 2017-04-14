<?php

class EntityDataLink
{

	/**
	* Link entity data between client and service
	**/
	public function linkData($site_id, $entity_type, $resource_type)
	{
		$entities_linked = '';
		$terms_linked ='';

		try
		{
			switch($entity_type)
			{
				case 'node':
					// loop through node link table to finding matching nids
					$node_matches = db_query('SELECT nid, title FROM node WHERE type = :type', array(':type' => $resource_type));
					if($node_matches->rowCount() > 0)
					{
						// for each match check if link data doesn't exist
						foreach($node_matches as $node_match)
						{
							$record_exists = db_query('SELECT 1 FROM services_rest_client_node_link WHERE service_site_id = :service_site_id AND client_nid = :client_nid', array(':service_site_id' => $site_id, ':client_nid' => $node_match->nid));
							if($record_exists->rowCount() > 0){
							}else{

								// check if entity exists on services
								$node = node_load($node_match->nid);
								$node_utility = new NodeUtility();

								$node_type = node_type_get_name($node);
								$client_term_reference_values = $node_utility->getClientNodeTermReferenceValues($node);
								$action_resource_nid = $node_utility->getActionResource($node_type, $client_term_reference_values);

								if($action_resource_nid)
								{
									$action_resource_node = node_load($action_resource_nid);
									$service_site_id = $action_resource_node->field_src_site_id[LANGUAGE_NONE][0]['value'];
									$services_endpoint = $action_resource_node->field_src_services_end_point[LANGUAGE_NONE][0]['value'];
									$services_username = $action_resource_node->field_src_services_username[LANGUAGE_NONE][0]['value'];
									$services_password = $action_resource_node->field_src_services_password[LANGUAGE_NONE][0]['password_field'];
									$services_password = password_field_decrypt($services_password); // function provided by password_field module

									$client_connection = $node_utility->getClientConnection($services_endpoint, $services_username, $services_password);
									$service_term_reference_values = $node_utility->getServiceNodeTermReferenceValues($service_site_id, $client_term_reference_values);
									// build query url
									$path_suffix = 'node.json?parameters[type]='.$resource_type;
									$term_reference_values = array();
									foreach($service_term_reference_values as $tname => $tids){
										// drupal_set_message('<pre>'.print_r($tids, true).'</pre>');
										$path_suffix .= '&amp;parameters['.$tname.']=';
										$numTerms = count($tids)-1;
										$i=0;
										foreach($tids as $key => $tid){
											$path_suffix .= $tid;
											if($i !== $numTerms){
												$path_suffix .=',';
											}
											$i++;
										}
									}
									// drupal_set_message($path_suffix);
									$method = 'GET';
									$entity_operation = new EntityOperation($client_connection, $method, $node);
									$response = $entity_operation->exec($path_suffix);
									if(!$response){
										throw new Exception('Failed to retrieve remote content "'.$node->title.'"');
									}else{
										// drupal_set_message('Remote content retreived: <br/><pre>'.print_r($response, true).'"</pre>', 'services');
										$service_nodes = json_decode($response);	
										foreach($service_nodes as $service_node)
										{
											if($node->title == $service_node->title)
											{		
											  	// create link 	
											    $fields_arr = array('service_site_id' => $service_site_id, 'client_nid' => $node_match->nid, 'service_nid' => $service_node->nid);
											    $node_link = db_update('services_rest_client_node_link')->fields($fields_arr)->execute();					    
											}else{
											  	// create link 	
											    $fields_arr = array('service_site_id' => $service_site_id, 'client_nid' => $node_match->nid, 'service_nid' => $node_match->nid);
											    $node_link = db_insert('services_rest_client_node_link')->fields($fields_arr)->execute();					
											}
											if($node_link){
												$entities_linked .= $node_match->title.', ';
											}	
										
										}
									}

								}
							}
						}
					}

					if(!empty($entities_linked)){
						drupal_set_message("The following nodes have been linked to site id:$site_id<br/>$entities_linked", 'services');
					}else{
						drupal_set_message('No data to link','services');
					}				

				break;
				case 'taxonomy_vocabulary':
					// find matching machine name for vocabulary
					$vocab = db_query('SELECT vid, name FROM taxonomy_vocabulary WHERE machine_name = :machine_name', array(':machine_name' => $resource_type));
					if($vocab->rowCount() > 0)
					{
						foreach($vocab as $v)
						{
							// drupal_set_message('<pre>'.print_r($v, true).'</pre>');
							$vocab_exists = db_query('SELECT 1 FROM services_rest_client_vocab_link WHERE service_site_id = :service_site_id AND client_vid = :client_vid', array(':service_site_id' => $site_id, ':client_vid' => $v->vid));
							if($vocab_exists->rowCount() > 0){
							}else{
								// create vocabulary link
								$vocab_fields_arr = array('service_site_id' => $site_id, 'client_vid' => $v->vid, 'service_vid' => $v->vid);
								$vocab_link = db_insert('services_rest_client_vocab_link')->fields($vocab_fields_arr)->execute();
								if($vocab_link)
								{
									$entities_linked .= $v->name;
								}

								// loop through each term of this vocabulary and check if link exists
								$terms = taxonomy_get_tree($v->vid);
								foreach ($terms as $term) {
								 	$client_tid = $term->tid;
								 	$client_vid = $v->vid;
								 	$term_exits = db_query('SELECT service_site_id, client_tid, client_vid FROM services_rest_client_term_link WHERE service_site_id = :service_site_id AND client_tid = :client_tid AND client_vid = :client_vid AND service_tid <> :service_tid', array(':service_site_id' => $site_id, ':client_tid' => $client_tid, ':client_vid' => $client_vid, ':service_tid' => $client_tid));
									if($term_exits->rowCount() > 0){
									}else{
										// create terms link
										$term_field_arr = array('service_site_id' => $site_id, 'client_tid' => $client_tid, 'client_vid' => $client_vid, 'service_tid' => $client_tid);
										$term_link = db_insert('services_rest_client_term_link')->fields($term_field_arr)->execute();
										if($term_link){
											$terms_linked .= $term->tid.', ';
										}
										
									}									
								}
							}
						}
					}

					if(!empty($entities_linked)){
						drupal_set_message("The vocabulary <b>$entities_linked</b> has been linked to site id: <b>$site_id</b><br/><br/>The following terms have also been linked:<br/>$terms_linked", 'services');
					}else{
						rupal_set_message('No data to link','services');
					}				

				break;
				default:
					return;
				break;
			}

		  
		} catch(Exception $e){
		  drupal_set_message($e->getMessage(),'error');
		}


	}
}