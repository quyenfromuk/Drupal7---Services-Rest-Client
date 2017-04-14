<?php

class VocabularyUtility {

	public function vocabularyInsert($entity, $type){
		// drupal_set_message('<pre>'.print_r($entity, true).'</pre>');
		$action_resource_nid = $this->getActionResource($entity, $type);
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
				$path_suffix = 'vocabulary.json';
				$method = 'POST';
				$client_vid = $entity->vid;
				// unset($entity->vid); // remove vid from entity (can't post client vid to services)
				$entity->vid = null;
				$entity_operation = new EntityOperation($client_connection, $method, $entity);
				$response = $entity_operation->exec($path_suffix);
				if(!$response){
					throw new Exception('Failed to create remote vocabulary "'.$entity->name.'" .  No remote vocabulary created');
				}else{
					drupal_set_message('<b>Remote vocabulary "'.$entity->name.'" created</b>', 'services');	
				}

				// create entity link between client and services
				$responseObj = json_decode($response);
				$entity->vid = $client_vid;
				$service_vid = $responseObj->vid;
				$entityLink = $this->createEntityLink($service_site_id, $client_vid, $service_vid);
				if(!$entityLink)
					throw new Exception('Failed to create entity link');

			} catch (Exception $e) {
			drupal_set_message($e->getMessage(),'error');
			}			
		}
	}

	public function vocabularyUpdate($entity, $type){

		$action_resource_nid = $this->getActionResource($entity, $type);
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
			      $service_vid = $this->getLinkedEntityIDOnServices($service_site_id, $client_vid);
			      if(!$service_vid){
					$this->vocabularyInsert($entity, $type);
			      }else{

			      	// continue here
			          $entity->vid = $service_vid;
			          $path_suffix = 'vocabulary/'.$service_vid.'.json';
			          $method = 'PUT';
			          $entity_operation = new EntityOperation($client_connection, $method, $entity);
			          $response = $entity_operation->exec($path_suffix);
			          if(!$response){
			            throw new Exception('<b>Error updating vocabulary "'.$entity->name.'" on remote site.  No remote vocabulary updated</b>');
			          }else{
			          	
			          	// // upload files here ?

			          	// reset client attribute values
			          	$entity->vid = $client_vid;
			          	drupal_set_message('<b>Remote vocabulary "'.$entity->name.'" updated</b>', 'services');
			          }
			       }
			} catch (Exception $e) {
				drupal_set_message($e->getMessage(),'error');
			}
		}

	}

	public function vocabularyDelete($entity, $type){

		$action_resource_nid = $this->getActionResource($entity, $type);
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
		      $service_vid = $this->getLinkedEntityIDOnServices($service_site_id, $client_vid);
		      if($service_vid){
		          $entity->vid = $service_vid;
		          $path_suffix = 'vocabulary/'.$service_vid.'.json';
		          $method = 'DELETE';
		          $entity_operation = new EntityOperation($client_connection, $method, $entity);
		          $response = $entity_operation->exec($path_suffix);
		          if(!$response){
		            throw new Exception('<b>Error deleting vocabulary "'.$entity->name.'" on remote site.  No remote vocabulary deleted</b>');
		          }else{
		            drupal_set_message('<b>Remote vocabulary "'.$entity->name.'" deleted</b>', 'services');
		            // remove record entry in link table
		            $remove_entity_link = $this->removeLinkedEntityOnServices($service_site_id, $client_vid, $service_vid);
		            if(!$remove_entity_link){
		            	throw new Exception('Failed to remove entity link');
		            }
		          }

		          // remove files here ?

		          $entity->vid = $client_vid;	          	
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

	public function getActionResource($entity, $type){
		$query = new EntityFieldQuery();
		$query->entityCondition('entity_type', 'node')
		->entityCondition('bundle', 'src_actionresource')
		->fieldCondition('field_src_active', 'value', 1, '=')
		->fieldCondition('field_src_entity_type', 'value', 'taxonomy_vocabulary', '=')
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
	public function createEntityLink($service_site_id, $client_vid, $service_vid){
	    $entityLinkExists = $this->entityLinkExists($service_site_id, $client_vid, $service_vid);
	    if(!$entityLinkExists){
		    $fields_arr = array('service_site_id' => $service_site_id, 'client_vid' => $client_vid, 'service_vid' => $service_vid);
		    $db_insert = db_insert('services_rest_client_vocab_link')->fields($fields_arr)->execute();
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
	public function entityLinkExists($service_site_id, $client_vid, $service_vid){
		$recordExists = db_query('SELECT 1 FROM services_rest_client_vocab_link WHERE service_site_id = :service_site_id AND client_vid = :client_vid AND service_vid = :service_vid', array(':service_site_id' => $service_site_id, ':client_vid' => $client_vid, ':service_vid' => $service_vid));
		if($recordExists->rowCount() > 0){
	    	return true;
	    }else{
	    	return false;
	    }
	}

	/*
	** Get link entity id on services
	*/
	public function getLinkedEntityIDOnServices($service_site_id, $client_vid) {
	  $entity_db_record = db_query('SELECT service_vid FROM services_rest_client_vocab_link WHERE service_site_id = :service_site_id AND client_vid = :client_vid', array(':service_site_id' => $service_site_id, ':client_vid' => $client_vid))->fetchObject();
	  if($entity_db_record){
	    return $entity_db_record->service_vid;
	  }else{
	  	return false;
	  }     
	}

	/*
	** Remove link entity record
	*/
	public function removeLinkedEntityOnServices($service_site_id, $client_vid, $service_vid) {
		try
		{
			$entity_db_record_delete = db_delete('services_rest_client_vocab_link')
			->condition('service_site_id', $service_site_id)
			->condition('client_vid', $client_vid)
			->condition('service_vid', $service_vid)
			->execute();  
			if($entity_db_record_delete){
				// delete all term records where site id and vid match
				$db_terms_delete = db_delete('services_rest_client_term_link')
				->condition('service_site_id', $service_site_id)
				->condition('client_vid', $client_vid)
				->execute();
			}
			return true;
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

