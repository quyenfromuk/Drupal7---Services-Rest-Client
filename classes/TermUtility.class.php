<?php

class TermUtility {

	public function termInsert($entity, $type){
		// drupal_set_message('<pre>'.print_r($entity, true).'</pre>');

		// get vocabulary entity
		$client_vid = $entity->vid;
		$vocabulary = taxonomy_vocabulary_load($client_vid);
		if(empty($vocabulary))
			return false;

		$action_resource_nid = $this->getActionResource($vocabulary, $type);
		if($action_resource_nid)
		{
		  $action_resource_node = node_load($action_resource_nid);
		  $service_site_id = $action_resource_node->field_src_site_id[LANGUAGE_NONE][0]['value'];
		  $services_endpoint = $action_resource_node->field_src_services_end_point[LANGUAGE_NONE][0]['value'];
		  $services_username = $action_resource_node->field_src_services_username[LANGUAGE_NONE][0]['value'];
		  $services_password = $action_resource_node->field_src_services_password[LANGUAGE_NONE][0]['password_field'];
		  $services_password = password_field_decrypt($services_password); // function provided by password_field module

		  $client_connection = $this->getClientConnection($services_endpoint, $services_username, $services_password);
		  try
			{
				// post entity to services
				$path_suffix = 'term.json';
				$method = 'POST';
				$client_tid = $entity->tid;
				$entity->tid = null;
				// unset($entity->tid); // remove tid from entity (can't post client tid to services)
				$entity_operation = new EntityOperation($client_connection, $method, $entity);
				$response = $entity_operation->exec($path_suffix);
				if(!$response){
					throw new Exception('Failed to create remote vocabulary "'.$entity->name.'" .  No remote term created');
				}else{
					drupal_set_message('<b>Remote term "'.$entity->name.'" created</b>', 'services');	
				}

				// create entity link between client and services
				$responseObj = json_decode($response);
				// drupal_set_message('<pre>'.print_r($responseObj, true).'</pre>');
				$entity->tid = $client_tid;
				$service_tid = $responseObj->tid;
				// drupal_set_message('service tid: '.$service_tid);
				$entityLink = $this->createEntityLink($service_site_id, $client_tid, $client_vid, $service_tid);
				if(!$entityLink)
					throw new Exception('Failed to create entity link');

			} catch (Exception $e) {
			drupal_set_message($e->getMessage(),'error');
			}			
		}
	}

	public function termUpdate($entity, $type){
		// drupal_set_message('<pre>'.print_r($entity, true).'</pre>');

		// get vocabulary entity
		$client_tid = $entity->tid;
		$client_vid = $entity->vid;
		$client_original = $entity->original;
		$vocabulary = taxonomy_vocabulary_load($client_vid);
		if(empty($vocabulary))
			return false;

		$action_resource_nid = $this->getActionResource($vocabulary, $type);
		if($action_resource_nid)
		{
		  $action_resource_node = node_load($action_resource_nid);
		  $service_site_id = $action_resource_node->field_src_site_id[LANGUAGE_NONE][0]['value'];
		  $services_endpoint = $action_resource_node->field_src_services_end_point[LANGUAGE_NONE][0]['value'];
		  $services_username = $action_resource_node->field_src_services_username[LANGUAGE_NONE][0]['value'];
		  $services_password = $action_resource_node->field_src_services_password[LANGUAGE_NONE][0]['password_field'];
		  $services_password = password_field_decrypt($services_password); // function provided by password_field module

	  		$client_connection = $this->getClientConnection($services_endpoint, $services_username, $services_password);
			try
			{
			      // get entity id on services
			      $client_vid = $entity->vid;
			      $service_tid = $this->getLinkedEntityIDOnServices($service_site_id, $client_tid, $client_vid);
			      if(!$service_tid){
					$this->termInsert($entity, $type);
			      }else{

			      	// continue here
			          $entity->tid = $service_tid;
			          $path_suffix = 'term/'.$service_tid.'.json';
			          $method = 'PUT';
			          // drupal_set_message('<pre>'.print_r($entity, true).'</pre>');
			          unset($entity->original);
			          $entity_operation = new EntityOperation($client_connection, $method, $entity);
			          $response = $entity_operation->exec($path_suffix);
			          if(!$response){
			            throw new Exception('<b>Error updating term "'.$entity->name.'".  No remote term updated</b>');
			          }else{
			          	
			          	// // upload files here ?

			          	// reset client attribute values
			          	$entity->tid = $client_tid;
			          	$entity->original = $client_original;
			          	drupal_set_message('<b>Remote term "'.$entity->name.'" updated</b>', 'services');
			          }
			       }
			} catch (Exception $e) {
				drupal_set_message($e->getMessage(),'error');
			}
		}else {
			drupal_set_message('fsddfs');
		}

	}

	public function termDelete($entity, $type){

		// get vocabulary entity
		$client_tid = $entity->tid;
		$client_vid = $entity->vid;
		$vocabulary = taxonomy_vocabulary_load($client_vid);
		if(empty($vocabulary))
			return false;		

		$action_resource_nid = $this->getActionResource($vocabulary, $type);
		if($action_resource_nid)
		{
		  $action_resource_node = node_load($action_resource_nid);
		  $service_site_id = $action_resource_node->field_src_site_id[LANGUAGE_NONE][0]['value'];
		  $services_endpoint = $action_resource_node->field_src_services_end_point[LANGUAGE_NONE][0]['value'];
		  $services_username = $action_resource_node->field_src_services_username[LANGUAGE_NONE][0]['value'];
		  $services_password = $action_resource_node->field_src_services_password[LANGUAGE_NONE][0]['password_field'];
		  $services_password = password_field_decrypt($services_password); // function provided by password_field module

	  		$client_connection = $this->getClientConnection($services_endpoint, $services_username, $services_password);
			try
			{
		      // get entity id on services
		      $client_vid = $entity->vid;
		      $service_vid = $this->getLinkedEntityIDOnServices($service_site_id, $client_tid, $client_vid);
		      if($service_vid){
		          $entity->vid = $service_vid;
		          $path_suffix = 'term/'.$service_vid.'.json';
		          $method = 'DELETE';
		          $entity_operation = new EntityOperation($client_connection, $method, $entity);
		          $response = $entity_operation->exec($path_suffix);
		          if(!$response){
		            throw new Exception('<b>Failed to delete term "'.$entity->name.'" on remote site.  No remote term deleted</b>');
		          }else{
		            drupal_set_message('<b>Remote term "'.$entity->name.'" deleted</b>', 'services');
		            // remove record entry in link table
		            $remove_entity_link = $this->removeLinkedEntityOnServices($service_site_id, $client_tid, $client_vid);
		            if(!$remove_entity_link){
		            	throw new Exception('Failed to remove entity link');
		            }
		          }

		          // remove files here ?

		          $entity->tid = $client_tid;	          	
		      }	          
		  } catch (Exception $e) {
		    drupal_set_message($e->getMessage(),'error');
		  }
		}
	
	}

	/*
	** Get client connection
	*/
	public function getClientConnection($services_endpoint, $services_username, $services_password){
	  // create new services connection
	  $client_connection = new ClientConnection($services_endpoint, $services_username, $services_password);
	  if(!$client_connection){
	    drupal_set_message('Failed to connect to services','services');
	    return false;    
	  }
	  return $client_connection;
	}

	public function getActionResource($entity){
		$query = new EntityFieldQuery();
		$query->entityCondition('entity_type', 'node')
		->entityCondition('bundle', 'src_actionresource')
		->fieldCondition('field_src_active', 'value', 1, '=')
		->fieldCondition('field_src_resource_type', 'value', $entity->machine_name); // taxonomy_vocabulary
		
		$result = $query->execute();
		// drupal_set_message('<pre>'.print_r($result, true).'</pre>');
		if (isset($result['node'])) {
			return current($result['node'])->nid;
		}else{
			return false;
		}

	}

	/*
	** create entity link between client and services
	*/
	public function createEntityLink($service_site_id, $client_tid, $client_vid, $service_tid){
	    $entityLinkExists = $this->entityLinkExists($service_site_id, $client_tid, $client_vid, $service_tid);
	    if(!$entityLinkExists){
		    $fields_arr = array('service_site_id' => $service_site_id, 'client_tid' => $client_tid, 'client_vid' => $client_vid, 'service_tid' => $service_tid);
		    $db_insert = db_insert('services_rest_client_term_link')->fields($fields_arr)->execute();
		    if($db_insert){
		      return true;
		    }else{
		      return false;
		    }
		}
	}

	/*
	** Whether entity link exists
	*/
	public function entityLinkExists($service_site_id, $client_tid, $client_vid, $service_tid){
		$recordExists = db_query('SELECT 1 FROM services_rest_client_term_link WHERE service_site_id = :service_site_id AND client_tid = :client_tid AND client_vid = :client_vid AND service_tid = :service_tid', array(':service_site_id' => $service_site_id, ':client_tid' => $client_tid, ':client_vid' => $client_vid, ':service_tid' => $service_tid));
		if($recordExists->rowCount() > 0){
	    	return true;
	    }else{
	    	return false;
	    }
	}

	/*
	** Get link entity id on services
	*/
	public function getLinkedEntityIDOnServices($service_site_id, $client_tid, $client_vid) {
	  $entity_db_record = db_query('SELECT service_tid FROM services_rest_client_term_link WHERE service_site_id = :service_site_id AND client_tid = :client_tid AND client_vid = :client_vid', array(':service_site_id' => $service_site_id, ':client_tid' => $client_tid, ':client_vid' => $client_vid))->fetchObject();
	  if($entity_db_record){
	    return $entity_db_record->service_tid;
	  }else{
	  	return false;
	  }     
	}

	/*
	** Remove link entity record
	*/
	public function removeLinkedEntityOnServices($service_site_id, $client_tid, $client_vid) {
		try
		{
			$entity_db_record_delete = db_delete('services_rest_client_term_link')
			->condition('service_site_id', $service_site_id)
			->condition('client_tid', $client_tid)
			->condition('client_vid', $client_vid)
			->execute();  
			if($entity_db_record_delete){
				return true;
			}else{
				return false;
			}
		} catch(Exception $e){
			drupal_set_message($e->getMessage(), 'error');
			return false;
		}
	}

	/*
	** Devel testing function
	*/
	public function services_rest_client_query_alter($query) {
	  if ($query->hasTag('efq_debug') && module_exists('devel')) {
	    dpm((string) $query);
	    dpm($query->arguments());
	  }
	}


}

