<?php

class EntityOperation {

	public $client_connection;
	public $method;

	public function __construct($client_connection, $method, $entity){
		// init
		$this->client_connection = $client_connection;
		$this->method = $method;
		$this->entity = $entity;	
	}

	public function exec($path_suffix){
		switch($this->method){
			case 'GET':
				return $this->getEntity($path_suffix);
			break;
			case 'POST':
				return $this->postEntity($path_suffix);
			break;
			case 'PUT':
				return $this->putEntity($path_suffix);
			break;
			case 'DELETE':
				return $this->deleteEntity($path_suffix);
			break;
			default:
				return false;
			break;
		}
	}

	public function getEntity($path_suffix){

	    $opts = array(
	      CURLOPT_CUSTOMREQUEST => "GET", 
	      CURLOPT_FAILONERROR => true,
	      CURLOPT_RETURNTRANSFER => true, 
	      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->client_connection->client['Session Name']."=".$this->client_connection->client['Session ID'], "X-CSRF-Token: ".$this->client_connection->client['X-CSRF-Token'])
	    );
	    $curl = Client::getInstance();
	    $curl->addSession($this->client_connection->services_servicepoint.$path_suffix, $opts);
	    $response = $curl->exec();
		$httpStatus = curl_getinfo($curl->sessions[0], CURLINFO_HTTP_CODE);
		// drupal_set_message('http response code: '.$httpStatus);	
	    $curl->clear();
	    if($response && $httpStatus == '200'){
	        return $response;
	    }else{
	      return false;
	    } 		    

	}	

	public function postEntity($path_suffix){

	    $json_data = (array)$this->entity;
	    // drupal_set_message('<pre>'.print_r($json_data, true.'</pre>'));
	    $json_string = json_encode($json_data);
	    // drupal_set_message($json_string);
	    $opts = array(
	      CURLOPT_CUSTOMREQUEST => "POST", 
	      CURLOPT_FAILONERROR => true,
	      CURLOPT_POSTFIELDS => $json_string, 
	      CURLOPT_RETURNTRANSFER => true, 
	      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->client_connection->client['Session Name']."=".$this->client_connection->client['Session ID'], "X-CSRF-Token: ".$this->client_connection->client['X-CSRF-Token'],'Content-Length: ' . strlen($json_string))
	    );
	    $curl = Client::getInstance();
	    $curl->addSession($this->client_connection->services_servicepoint.$path_suffix, $opts);
	    $response = $curl->exec();
		$httpStatus = curl_getinfo($curl->sessions[0], CURLINFO_HTTP_CODE);
		// drupal_set_message('http response code: '.$httpStatus);
	    $curl->clear();
	    if($response && $httpStatus == '200'){
	        return $response;
	    }else{
	      return false;
	    } 
	}	

	public function putEntity($path_suffix){

	    $json_data = (array)$this->entity;
	    $json_string = json_encode($json_data);
	    // drupal_set_message(print_r($json_string, 1));
	    $opts = array(
	      CURLOPT_CUSTOMREQUEST => "PUT", 
	      CURLOPT_FAILONERROR => true,
	      CURLOPT_POSTFIELDS => $json_string, 
	      CURLOPT_RETURNTRANSFER => true, 
	      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->client_connection->client['Session Name']."=".$this->client_connection->client['Session ID'], "X-CSRF-Token: ".$this->client_connection->client['X-CSRF-Token'],'Content-Length: ' . strlen($json_string))
	    );
	    $curl = Client::getInstance();

	    $curl->addSession($this->client_connection->services_servicepoint.$path_suffix, $opts);
	    $response = $curl->exec();
		$httpStatus = curl_getinfo($curl->sessions[0], CURLINFO_HTTP_CODE);
		// drupal_set_message('http response code: '.$httpStatus);	    
	    $curl->clear();
	    if($response && $httpStatus == '200'){
	        return $response;
	    }else{
	      return false;
	    } 
	}	
	
	public function deleteEntity($path_suffix){

	    $json_data = (array)$this->entity;
	    $json_string = json_encode($json_data);
	    // drupal_set_message(print_r($json_string, 1));
	    $opts = array(
	      CURLOPT_CUSTOMREQUEST => "DELETE", 
	      CURLOPT_FAILONERROR => true,
	      CURLOPT_POSTFIELDS => $json_string, 
	      CURLOPT_RETURNTRANSFER => true, 
	      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->client_connection->client['Session Name']."=".$this->client_connection->client['Session ID'], "X-CSRF-Token: ".$this->client_connection->client['X-CSRF-Token'],'Content-Length: ' . strlen($json_string))
	    );
	    $curl = Client::getInstance();

	    $curl->addSession($this->client_connection->services_servicepoint.$path_suffix, $opts);
	    $response = $curl->exec();
		$httpStatus = curl_getinfo($curl->sessions[0], CURLINFO_HTTP_CODE);
		// drupal_set_message('http response code: '.$httpStatus);		    
	    $curl->clear();
	    if($response && $httpStatus == '200'){
	        return $response;
	    }else{
	      return false;
	    } 	    
	}

}
