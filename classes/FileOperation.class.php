<?php

class FileOperation {

	public $file_connection;
	public $method;

	public function __construct($file_connection, $method, $entity){
		// init
		$this->file_connection = $file_connection;
		$this->method = $method;
		$this->entity = $entity;	
	}

	public function exec($path_suffix, $direct_path=null){
		switch($this->method){
			case 'GET':
				return $this->getFile($path_suffix, $direct_path);
			break;
			case 'POST':
				return $this->postFile($path_suffix);
			break;
			case 'PUT':
				return $this->putFile($path_suffix);
			break;
			case 'DELETE':
				return $this->deleteFile($path_suffix);
			break;
			default:
				return false;
			break;
		}
	}


	public function getFile($path_suffix, $direct_path){

		if(!empty($path_suffix))
		{

		    $opts = array(
		      CURLOPT_CUSTOMREQUEST => "GET", 
		      CURLOPT_FAILONERROR => true,
		      CURLOPT_RETURNTRANSFER => true, 
		      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->file_connection->client['Session Name']."=".$this->file_connection->client['Session ID'], "X-CSRF-Token: ".$this->file_connection->client['X-CSRF-Token'])
		    );			

			$curl = Client::getInstance();                                                                   
			$curl->addSession($this->file_connection->services_servicepoint.$path_suffix, $opts);                                                                                                                  
			$response = $curl->exec();
			$curl->clear();
		    if($response){
		        return $response;
		    }else{
		      return false;
		    }
		}elseif(!empty($direct_path))
		{
		    $opts = array(
		      CURLOPT_CUSTOMREQUEST => "GET", 
		      CURLOPT_FAILONERROR => true,
		      CURLOPT_RETURNTRANSFER => true, 
		      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->file_connection->client['Session Name']."=".$this->file_connection->client['Session ID'], "X-CSRF-Token: ".$this->file_connection->client['X-CSRF-Token'])
		    );			

			$curl = Client::getInstance();                                                                   
			$curl->addSession($direct_path, $opts);                                                                                                                  
			$response = $curl->exec();
			$curl->clear();
		    if($response){
		        return $response;
		    }else{
		      return false;
		    }		
		}else{
			return false;
		} 
	}	

	public function postFile($path_suffix) {



		/**
		* services module version
		**/

		$file_fid  = $this->entity->fid;
		$file_name = $this->entity->filename;
		$file_path = $this->entity->uri;
		$file_mime = $this->entity->filemime;
		$file_size = $this->entity->filesize;
		
		$this->entity->status = 1;
		$base64 = base64_encode(file_get_contents($file_path));

		$file_data = array(
			"filename" => $file_name,
			"filepath" => $file_path,
			"filemime" => $file_mime,
			"filesize" => $file_size,
		    "file" => $base64,
		    "fid" => $file_fid
		);

		$json_string = json_encode($file_data);

	    $opts = array(
	      CURLOPT_CUSTOMREQUEST => "POST", 
	      CURLOPT_FAILONERROR => true,
	      CURLOPT_POSTFIELDS => $json_string, 
	      CURLOPT_RETURNTRANSFER => true, 
	      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->file_connection->client['Session Name']."=".$this->file_connection->client['Session ID'], "X-CSRF-Token: ".$this->file_connection->client['X-CSRF-Token'],'Content-Length: ' . strlen($json_string))
	    );
	    $curl = Client::getInstance();
	    $curl->addSession($this->file_connection->services_servicepoint.$path_suffix, $opts);
	    $response = $curl->exec();
	    $curl->clear();
	    if($response){
	        return $response;
	    }else{
	      return false;
	    } 


		/**
		* Custom version
		**/


		// $file_fid  = $this->entity->fid;
		// $file_name = $this->entity->filename;
		// $file_localpath = $this->entity->filelocalpath;
		// $file_externalpath = $this->entity->fileexternalpath;
		// $file_mime = $this->entity->filemime;
		// $file_size = $this->entity->filesize;		

		// // store image on file server
	 // 	$ch = curl_init();
	 // 	$fp = fopen($file_localpath, 'r');
	 // 	curl_setopt($ch, CURLOPT_URL, 'ftp://portal:000000@web.shanghai.nyu.edu/'.$path_suffix.'/'.$file_name);
	 // 	curl_setopt($ch, CURLOPT_UPLOAD, 1);
	 // 	curl_setopt($ch, CURLOPT_INFILE, $fp);
	 // 	curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_localpath));
		// curl_setopt($ch, CURLOPT_HEADER, false); 
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);				
	 // 	curl_exec ($ch);
	 // 	$error_no = curl_errno($ch);
	 // 	curl_close ($ch);
  //       if ($error_no != 0) {
  //       	return false;
  //       }else{
  //       	return true;
  //       }



     //    $fp = fopen($file_localpath, 'r');
	    // $opts = array(
	    //   CURLOPT_URL => 'ftp://portal:000000@web.shanghai.nyu.edu/'.$file_name,
	    //   CURLOPT_UPLOAD => 1,
	    //   CURLOPT_INFILE => $fp,
	    //   CURLOPT_INFILESIZE => filesize($file_localpath),
	    //   CURLOPT_HEADER => false,
	    //   CURLOPT_RETURNTRANSFER => false
	    // );
	    // $curl = Client::getInstance();
	    // $curl->addSession($this->file_connection.$path_suffix, $opts);
	    // $response = $curl->exec();
	    // $curl->clear();
	    // if($response){
	    //     return $response;
	    // }else{
	    //   return false;
	    // } 


		// // store image in local website
		// $newFileImage = time().'.'.$theFileExtension;
		// $newFileImageDest = "tmpImages/".$newFileImage;
		// $fileFieldsArr[] = file_get_contents($theFile['tmp_name']);
		// $copyNewImage = move_uploaded_file($theFile['tmp_name'], $newFileImageDest);
		// if (!$copyNewImage) 
		// {
		// 	$errors = 1;
		// }

		
		/**
		* services module version
		**/

		// $file_fid  = $this->entity->fid;
		// $file_name = $this->entity->filename;
		// $file_path = $this->entity->uri;
		// $file_mime = $this->entity->filemime;
		// $file_size = $this->entity->filesize;
		
		// $this->entity->status = 1;
		// $base64 = base64_encode(file_get_contents($file_path));

		// $file_data = array(
		// 	"filename" => $file_name,
		// 	"filepath" => $file_path,
		// 	"filemime" => $file_mime,
		// 	"filesize" => $file_size,
		//     "file" => $base64,
		//     "fid" => $file_fid
		// );

		// $json_string = json_encode($file_data);

	 //    $opts = array(
	 //      CURLOPT_CUSTOMREQUEST => "POST", 
	 //      CURLOPT_FAILONERROR => true,
	 //      CURLOPT_POSTFIELDS => $json_string, 
	 //      CURLOPT_RETURNTRANSFER => true, 
	 //      CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Cookie: ".$this->client_connection->client['Session Name']."=".$this->client_connection->client['Session ID'], "X-CSRF-Token: ".$this->client_connection->client['X-CSRF-Token'],'Content-Length: ' . strlen($json_string))
	 //    );
	 //    $curl = Client::getInstance();
	 //    $curl->addSession($this->client_connection->services_servicepoint.$path_suffix, $opts);
	 //    $response = $curl->exec();
	 //    $curl->clear();
	 //    if($response){
	 //        return $response;
	 //    }else{
	 //      return false;
	 //    } 




	}


}
