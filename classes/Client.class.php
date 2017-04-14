<?php

/*
Example usage

Single web service  URL

$curl = CURL::getInstance();
$curl->retry = 2; // attempt to download twice
$opts = array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true  ); // array of curl options: output return data, follow redirects
$curl->addSession( 'http://webserviceurl1/', $opts ); // web service URL, options
$result = $curl->exec(); // execute curl
$curl->clear(); // clear connection sessions (can now make fresh connections)


Multiple web service URLs (will make multiple web service calls and combine results into one array)

$curl = CURL::getInstance();
$opts = array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true  );
$curl->addSession( 'http://webserviceurl1/', $opts );
$curl->addSession( 'http://webserviceurl2/', $opts );
$curl->addSession( 'http://webserviceurl3/', $opts );
$result = $curl->exec();
$curl->clear();

*/
class Client
{

	protected static $instance = null;
	public $sessions 	=	array();
	public $retry = 0;
	
	protected function __construct(){}
	protected function __clone(){}
	public static function getInstance()
	{
		if(!isset(static::$instance)){
			static::$instance = new static;
		}
		return static::$instance;
	}
	

	public function addSession( $url, $opts = false )
	{
		$this->sessions[] = curl_init( $url );
		if( $opts != false )
		{
			$key = count( $this->sessions ) - 1;
			$this->setOpts( $opts, $key );
		}
	}
	

	public function setOpt( $option, $value, $key = 0 )
	{
		curl_setopt( $this->sessions[$key], $option, $value );
	}
	

	public function setOpts( $options, $key = 0 )
	{
		curl_setopt_array( $this->sessions[$key], $options );
	}
	

	public function exec( $key = false )
	{
		$no = count( $this->sessions );
		
		if( $no == 1 )
			$res = $this->execSingle();
		elseif( $no > 1 ) {
			if( $key === false )
				$res = $this->execMulti();	
			else
				$res = $this->execSingle( $key );
		}
		
		if( $res )
			return $res;
	}
	

	public function execSingle( $key = 0 )
	{
		if( $this->retry > 0 )
		{
			$retry = $this->retry;
			$code = 0;
			while( $retry >= 0 && ( $code[0] == 0 || $code[0] >= 400 ) )
			{
				$res = curl_exec( $this->sessions[$key] );
				$code = $this->info( $key, CURLINFO_HTTP_CODE );
				
				$retry--;
			}
		}
		else
			$res = curl_exec( $this->sessions[$key] );
		
		return $res;
	}
	
	
	public function execMulti()
	{
		$mh = curl_multi_init();
		
		#Add all sessions to multi handle
		foreach ( $this->sessions as $i => $url )
			curl_multi_add_handle( $mh, $this->sessions[$i] );
		
		do
			$mrc = curl_multi_exec( $mh, $active );
		while ( $mrc == CURLM_CALL_MULTI_PERFORM );
		
		while ( $active && $mrc == CURLM_OK )
		{
			if ( curl_multi_select( $mh ) != -1 )
			{
				do
					$mrc = curl_multi_exec( $mh, $active );
				while ( $mrc == CURLM_CALL_MULTI_PERFORM );
			}
		}

		if ( $mrc != CURLM_OK )
			echo "Curl multi read error $mrc\n";
		
		#Get content foreach session, retry if applied
		foreach ( $this->sessions as $i => $url )
		{
			$code = $this->info( $i, CURLINFO_HTTP_CODE );
			if( $code[0] > 0 && $code[0] < 400 )
				$res[] = curl_multi_getcontent( $this->sessions[$i] );
			else
			{
				if( $this->retry > 0 )
				{
					$retry = $this->retry;
					$this->retry -= 1;
					$eRes = $this->execSingle( $i );
					
					if( $eRes )
						$res[] = $eRes;
					else
						$res[] = false;
						
					$this->retry = $retry;
					echo '1';
				}
				else
					$res[] = false;
			}

			curl_multi_remove_handle( $mh, $this->sessions[$i] );
		}

		curl_multi_close( $mh );
		
		return $res;
	}
	

	public function close( $key = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $session )
				curl_close( $session );
		}
		else
			curl_close( $this->sessions[$key] );
	}
	

	public function clear()
	{
		foreach( $this->sessions as $session )
			curl_close( $session );
		unset( $this->sessions );
	}
	

	public function info( $key = false, $opt = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $key => $session )
			{
				if( $opt )
					$info[] = curl_getinfo( $this->sessions[$key], $opt );
				else
					$info[] = curl_getinfo( $this->sessions[$key] );
			}
		}
		else
		{
			if( $opt )
				$info[] = curl_getinfo( $this->sessions[$key], $opt );
			else
				$info[] = curl_getinfo( $this->sessions[$key] );
		}
		
		return $info;
	}
	

	public function error( $key = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $session )
				$errors[] = curl_error( $session );
		}
		else
			$errors[] = curl_error( $this->sessions[$key] );
			
		return $errors;
	}
	

	public function errorNo( $key = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $session )
				$errors[] = curl_errno( $session );
		}
		else
			$errors[] = curl_errno( $this->sessions[$key] );
			
		return $errors;
	}
	
}

