<?php


class ClientConnection {

	public $services_servicepoint;
	private $username;
	private $password;
	public $client = array();

	public function __construct($services_endpoint, $services_username, $services_password){
		$services_servicepoint = $services_endpoint;
		$username = $services_username;
		$password = $services_password;
		if(empty($services_servicepoint) || empty($username) || empty($password)){
			return false;
		}
		$this->services_servicepoint = $services_servicepoint;
		$this->username = $username;
		$this->password = $password;
		return $this->connect();
	}
	public function connect(){
		$token = $this->getToken();
		if($token){
			if($this->userLogin($token)){ 
				return $this->client;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function getToken(){
		// get CSRF token
		$opts = array(
			CURLOPT_CUSTOMREQUEST => "POST", 
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_HTTPHEADER => array ("Content-Type: application/json")
		);
		$curl = Client::getInstance();
		$curl->addSession($this->services_servicepoint.'user/token.json', $opts);
		$result = $curl->exec();
		if(!$result){
			drupal_set_message('Failed to get user token', 'services');
			return false;
		}
		$curl->clear();
		$tokenObj = json_decode($result); // change to php object format
		$csrf_token = $tokenObj->token;
		// drupal_set_message('User token retreived','status');
		return $csrf_token;
	}

	public function userLogin($token){
		$data = array('username' => $this->username, 'password' => $this->password);
		$jsonData =  json_encode($data); // change to json string format

		$opts = array(
			CURLOPT_CUSTOMREQUEST => "POST", 
			CURLOPT_POSTFIELDS => $jsonData, 
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_HTTPHEADER => array ("Content-Type: application/json", "X-CSRF-Token: " . $token)
		);

		$curl = Client::getInstance();
		$curl->addSession($this->services_servicepoint.'user/login.json', $opts);
		$result = $curl->exec();
		if(!$result){
			drupal_set_message('Failed to login user', 'services');
			return false;
		}
		$curl->clear();
		$userObj = json_decode($result);

		if($userObj->user->name != $this->username){
			drupal_set_message('Error accessing services', 'services');
			return false;
		}
		$session_id = $userObj->sessid;
		$session_name = $userObj->session_name;
		$token = $userObj->token;

		// create user session array
		$request_headers = array("Content-Type"=>"application/json", "Session Name"=>$session_name, "Session ID"=>$session_id, "X-CSRF-Token"=>$token);
		foreach($request_headers as $key=>$value){
			$this->client[$key]=$value;
		}
		// drupal_set_message('User logged in','status');
		return true;
	}

}

