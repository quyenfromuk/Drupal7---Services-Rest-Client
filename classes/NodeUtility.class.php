<?php

class NodeUtility {


	public function nodeInsert($node){

		// drupal_set_message('<pre>'.print_r($node, true).'</pre>');
		$node_type = node_type_get_name($node);
		$client_term_reference_values = $this->getClientNodeTermReferenceValues($node);
		// drupal_set_message('<pre>'.print_r($client_term_reference_values, true).'</pre>');
		$action_resource_nid = $this->getActionResource($node_type, $client_term_reference_values);
		
		
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
		  	// // upload files to file server
		  //   if(!empty($node->field_image[LANGUAGE_NONE]))
		  //   {
		  //   	$file_connection = $this->getFileConnection();

		  //   	foreach($node->field_image[LANGUAGE_NONE] as $index => $image)
		  //   	{
			 //    	$fid = $image['fid'];
			 //    	$file = file_load($fid);
			 //    	$file_uri_arr = explode("/", $file->uri);
				// 	$file_name = end($file_uri_arr);				

				// 	try
				// 	{

				//     	$path_suffix = 'file.json';
				//     	$method = 'POST';
				// 		$file_operation = new FileOperation($file_connection, $method, $file);
				// 		$response = $file_operation->exec($path_suffix);
				// 	    if(!$response){
				// 	    	throw new Exception('Failed to upload file "'.$file_name.'" .  No remote file uploaded');
				// 	    }else {
				// 	    	// record file upload path in node fields
				// 	    	$response_obj = json_decode($response);
				// 	    	$method = 'GET'; 
				// 	    	$file_operation = new FileOperation($file_connection, $method, null);
				// 	    	$path = (String)($response_obj->uri.'.json');
				// 	    	$xml_json = $file_operation->exec(null, $path);
				// 	    	$file_obj = json_decode($xml_json);
				//     		if(!$file_obj)
				//     			throw new Exception('Failed to create object from XML document');

				// 	    	$node->field_external_image_url[LANGUAGE_NONE][$index]['value'] = (String)$file_obj->uri_full;
				// 	    	$node->field_external_image_url[LANGUAGE_NONE][$index]['safe_value'] = (String)$file_obj->uri_full;
					 	    	
				// 	    }
				// 	} catch (Exception $e) {
				//     	drupal_set_message($e->getMessage(),'error');
				//   	}
				// }
		  //   }

		    $node_field_term_ref_arr = $this->getServiceNodeTermReferenceValues($service_site_id, $client_term_reference_values);
		    // drupal_set_message('<pre>'.print_r($node_field_term_ref_arr, true).'</pre>');
		    $node = $this->setServiceFieldTermReferenceValues($node, $node_field_term_ref_arr);		 

		    // drupal_set_message('<pre>'.print_r($node, true).'</pre>');   

		    // save new field data for existing node
		    field_attach_update('node', $node);
		    // drupal_set_message('<pre>'.print_r($node, true).'</pre>');

		    $client_nid = $node->nid;
		    $node->nid = null;

		    // post entity to services
		    $path_suffix = 'node.json';
		    $method = 'POST';
		    $entity_operation = new EntityOperation($client_connection, $method, $node);
		    $response = $entity_operation->exec($path_suffix);
		    if(!$response){
		    	throw new Exception('Failed to create remote content "'.$node->title.'" .  No remote content created');
		    }else{
		    	drupal_set_message('<b>Remote content "'.$node->title.'" created</b>', 'services');	
		    }

		    // create entity link between client and services
		    $responseObj = json_decode($response);
		    // drupal_set_message('<pre>'.print_r($responseObj, true).'</pre>');

		    $service_nid = $responseObj->nid;
		    $entityLink = $this->createEntityLink($service_site_id, $client_nid, $service_nid);
		    if(!$entityLink)
		    	throw new Exception('Failed to create entity link');

		    $node->nid = $client_nid;

		  } catch (Exception $e) {
		    drupal_set_message($e->getMessage(),'error');
		  }
		}

	}

	public function nodeUpdate($node){
		$node_type = node_type_get_name($node);	
	  	$client_term_reference_values = $this->getClientNodeTermReferenceValues($node);	

		$action_resource_nid = $this->getActionResource($node_type, $client_term_reference_values);
		
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
				$client_nid = $node->nid;
				$service_nid = $this->getLinkedEntityNIDOnServices($service_site_id, $client_nid);
				if(!$service_nid){
					$this->nodeInsert($node);
				}else{
	
					// upload files to file server

					// unset($node->field_external_image_url['und']);

					// save new field data for existing node
					field_attach_presave('node', $node);
					field_attach_update('node', $node);						


					// $file_connection = $this->getFileConnection();

					// if(!empty($node->field_image[LANGUAGE_NONE]))
					// {
					// 	foreach($node->field_image[LANGUAGE_NONE] as $index => $image)
					// 	{
					// 		// check if file already exists ??


					//     	$fid = $image['fid'];
					//     	$file = file_load($fid);
					//     	// print_r($file);
					//     	$file_uri_arr = explode("/", $file->uri);
					// 		$file_name = end($file_uri_arr);				

					// 		try
					// 		{
					// 	    	$path_suffix = 'file.json';
					// 	    	$method = 'POST';
					// 			$file_operation = new FileOperation($file_connection, $method, $file);
					// 			$response = $file_operation->exec($path_suffix);
					// 		    if(!$response){
					// 		    	throw new Exception('Failed to upload file "'.$file_name.'" .  No remote file uploaded');
					// 		    }else {
					// 		    	// record file upload path in node fields
					// 		    	$response_obj = json_decode($response);
					// 		    	// print_r($response_obj);exit;
					// 		    	$method = 'GET'; 
					// 		    	$file_operation = new FileOperation($file_connection, $method, null);
					// 		    	$path = (String)($response_obj->uri.'.json');
					// 		    	$xml_json = $file_operation->exec(null, $path);
					// 		    	$file_obj = json_decode($xml_json);
					// 	    		if(!$file_obj)
					// 	    			throw new Exception('Failed to create object from XML document');

					// 		    	$node->field_external_image_url[LANGUAGE_NONE][$index]['value'] = (String)$file_obj->uri_full;
					// 		    	$node->field_external_image_url[LANGUAGE_NONE][$index]['safe_value'] = (String)$file_obj->uri_full;

									    	
					// 		    }
					// 		} catch (Exception $e) {
					// 	    	drupal_set_message($e->getMessage(),'error');
					// 	  	}
					// 	}
					// 	// reindex array
					// 	array_values($node->field_external_image_url['und']);
					// }

					// save new field data for existing node
					// field_attach_presave('node', $node);
					// field_attach_update('node', $node);	

				    $node_field_term_ref_arr = $this->getServiceNodeTermReferenceValues($service_site_id, $client_term_reference_values);
				    // drupal_set_message('<pre>'.print_r($node_field_term_ref_arr, true).'</pre>');
				    $node = $this->setServiceFieldTermReferenceValues($node, $node_field_term_ref_arr);		 

				    // drupal_set_message('<pre>'.print_r($node, true).'</pre>');   

				    // save new field data for existing node
				    field_attach_update('node', $node);

					$node->nid = $service_nid;
					$path_suffix = 'node/'.$service_nid.'.json';
					$method = 'PUT';
					$entity_operation = new EntityOperation($client_connection, $method, $node);
					$response = $entity_operation->exec($path_suffix);
					if(!$response){
						throw new Exception('<b>Content "'.$node->title.'" doesn\'t exist on remote site.  No remote content updated</b>');
					}else{
						drupal_set_message('<b>Remote content "'.$node->title.'" updated</b>', 'services');		
					}
					// reset client attribute values								
					$node->nid = $client_nid;				
				}
	
			} catch(Exception $e) {
		    	drupal_set_message($e->getMessage(),'error');
		    }

		}


	}

	public function nodeDelete($node){
		$node_type = node_type_get_name($node);
	  	$client_term_reference_values = $this->getClientNodeTermReferenceValues($node);	

		$action_resource_nid = $this->getActionResource($node_type, $client_term_reference_values);
		
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
		      $client_nid = $node->nid;
		      $service_nid = $this->getLinkedEntityNIDOnServices($service_site_id, $client_nid);
		      if($service_nid){
		          $node->nid = $service_nid;
		          $path_suffix = 'node/'.$service_nid.'.json';
		          $method = 'DELETE';
		          $entity_operation = new EntityOperation($client_connection, $method, $node);
		          $response = $entity_operation->exec($path_suffix);
		          if(!$response){
		            throw new Exception('<b>Content "'.$node->title.'" doesn\'t exist on remote site.  No remote content deleted</b>');
		          }else{
		            drupal_set_message('<b>Remote content "'.$node->title.'" deleted</b>', 'services');
		            // remove record entry in link table
		            $remove_entity_link = $this->removeLinkedEntityOnServices($service_site_id, $client_nid, $service_nid);
		            if(!$remove_entity_link){
		            	throw new Exception('Failed to remove entity link');
		            }
		          }

		          // remove files here ?

		          $node->nid = $client_nid; 	
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

	  // login using global settings or one set by the action resource
	  if(empty($services_endpoint) || empty($services_username) || empty($services_password))
		  return;

	  // create new services connection
	  $client_connection = new ClientConnection($services_endpoint, $services_username, $services_password);
	  if(!$client_connection){
	    drupal_set_message('Failed to connect to services','services');
	    return false;    
	  }
	  return $client_connection;
	}

	public function getFileConnection(){
	  // create file connection
	  $file_connection = new FileConnection();
	  if(!$file_connection){
	    drupal_set_message('Failed to connect to file services','services');
	    return false;    
	  }
	  return $file_connection;
	}

	/*
	** Get node term reference values on client
	*/
	public function getClientNodeTermReferenceValues($node)
	{
	  	$term_reference_values = array();
		foreach($node as $name => $value)
		{
			$field_info = field_info_field($name);
			$type = $field_info['type'];
			if($type == 'taxonomy_term_reference')
			{	
				$wrapper = entity_metadata_wrapper('node', $node);
				$term_reference = $wrapper->{$name}->raw();
				if(is_array($term_reference))
				{
					foreach($term_reference as $term)
					{
						$term_reference_values[$name][] = $term;
					}
				}else{
					$term_reference_values[$name][] = $term_reference;
				}
			}
		}	
		return $term_reference_values;	
	}

	/**
	* get term reference values on service
	**/
	public function getServiceNodeTermReferenceValues($service_site_id, $client_term_reference_values)
	{
		$term_reference_values = array();
		foreach($client_term_reference_values as $tname => $tids){
			foreach($tids as $tid){
				$service_term_result = db_query('SELECT service_tid FROM services_rest_client_term_link WHERE service_site_id = :service_site_id AND client_tid = :client_tid', array(':service_site_id' => $service_site_id, ':client_tid' => $tid));
				if($service_term_result->rowCount() > 0){
					$term_reference_values[$tname][] = array('tid' => $tid);
				}
			}
		}
		return $term_reference_values;
	}

	public function setServiceFieldTermReferenceValues($node, $node_field_term_ref_arr)
	{
		// drupal_set_message('<pre>'.print_r($node_field_term_ref_arr, true).'</pre>');
		// modify node term reference field values
		foreach($node_field_term_ref_arr as $field_name => $tids)
		{
			// foreach($tids as $tid){
			for($i=0; $i<count($tids); $i++){
				$node->{$field_name}[$node->language][$i] = $tids[$i];
			}
		}
		// drupal_set_message('<pre>'.print_r($node, true).'</pre>');
		return $node;
	}



	/**
	* Get action resource for this node type
	**/
	public function getActionResource($node_type, $term_reference_values){

		// drupal_set_message('<pre>'.print_r($term_reference_values, true).'</pre>');

		// get conditional field dependencies for src_actionresource of resource type $node_type
		$action_resource_term_references = array();
		if (module_exists('conditional_fields') && function_exists('conditional_fields_load_dependencies')) { 
			$dependencies = conditional_fields_load_dependencies('node', 'src_actionresource');
			if(!empty($dependencies))
			{
				foreach($dependencies['dependents'] as $dependent_name => $dependent)
				{
					foreach($dependent as $k => $v)
					{
						$dependency = $v['options']['values'][0];
						// store all field dependencies for the node type
						if($dependency === strtolower($node_type))
						{
							$action_resource_term_references[$dependent_name] = $dependency;
						}
					}
				}
			}
		}else{
			drupal_set_message('conditional fields module not installed!','error');
		}

		// do the query
		$query = new EntityFieldQuery();
		$query->entityCondition('entity_type', 'node')
		->entityCondition('bundle', 'src_actionresource')
		->fieldCondition('field_src_active', 'value', 1, '=')
		// ->addTag('efq_debug')
		->fieldCondition('field_src_entity_type', 'value', 'node', '=')
		->fieldCondition('field_src_resource_type', 'value', $node_type);

		// each term reference field from action resource should be a field condition
		if(!empty($term_reference_values))
		{			
			foreach($action_resource_term_references as $term_reference_name => $term_reference_arr)
			{
				$query->fieldCondition($term_reference_name, 'tid', $term_reference_values, 'IN');
			}
		}
		// drupal_set_message('<pre>'.print_r($term_values, true).'</pre>');
		$result = $query->execute();
		if (isset($result['node'])) {
			return current($result['node'])->nid;
		}else{
			return false;
		}
	}

	/*
	** create entity link between client and services
	*/
	public function createEntityLink($service_site_id, $client_nid, $service_nid){
	    $entityLinkExists = $this->entityLinkExists($service_site_id, $client_nid, $service_nid);
	    if(!$entityLinkExists){
		    $fields_arr = array('service_site_id' => $service_site_id, 'client_nid' => $client_nid, 'service_nid' => $service_nid);
		    $db_insert = db_insert('services_rest_client_node_link')->fields($fields_arr)->execute();
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
	public function entityLinkExists($service_site_id, $client_nid, $service_nid){
		$recordExists = db_query('SELECT 1 FROM services_rest_client_node_link WHERE service_site_id = :service_site_id AND client_nid = :client_nid AND service_nid = :service_nid', array(':service_site_id' => $service_site_id, ':client_nid' => $client_nid, ':service_nid' => $service_nid));
		if($recordExists->rowCount() > 0){
	    	return true;
	    }else{
	    	return false;
	    }
	}

	/*
	** Get link entity id on services
	*/
	public function getLinkedEntityNIDOnServices($service_site_id, $client_nid) {
	  $entity_db_record = db_query('SELECT service_nid FROM services_rest_client_node_link WHERE service_site_id = :service_site_id AND client_nid = :client_nid', array(':service_site_id' => $service_site_id, ':client_nid' => $client_nid))->fetchObject();
	  if($entity_db_record){
	    return $entity_db_record->service_nid;
	  }else{
	  	return false;
	  }     
	}

	/*
	** Remove link entity record
	*/
	public function removeLinkedEntityOnServices($service_site_id, $client_nid, $service_nid) {
	  $entity_db_record_delete = db_delete('services_rest_client_node_link')
	  	->condition('service_site_id', $service_site_id)
	    ->condition('client_nid', $client_nid)
	    ->condition('service_nid', $service_nid)
	    ->execute();  
	    if($entity_db_record_delete){
	    	return true;
	   	}else{
	   		return false;
	   	}
	}

	/*
	** Devel testing function
	*/
	public function services_rest_client_query_alter($query) {
	  if ($query->hasTag('efq_debug') && module_exists('devel')) {
	    print dpm((string) $query);
	    print dpm($query->arguments());
	  }
	}



}

