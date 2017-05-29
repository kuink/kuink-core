<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class RestConnector extends \Kuink\Core\DataSourceConnector {
	private $response = null;
	private $responseType = "json";
	private $server = null;
	private $port = null;
	
	/**
	 * Connect using any strategy
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	function connect() {
		$this->server = $this->dataSource->getParam ( 'server', true );
		$this->port = $this->dataSource->getParam ( 'port', true );
		$server = $this->dataSource->getParam ( 'server', true );
		var_dump ( $server );
		$responseType = $this->dataSource->getParam ( 'responseType', true );
		if ($responseType != '')
			$this->responseType = $responseType;
	}
	
	/**
	 * Wrapper to put
	 *
	 * @see \Kuink\Core\DataSourceConnector::insert()
	 */
	function insert($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to post
	 *
	 * @param unknown $params        	
	 */
	function update($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to post
	 *
	 * @param unknown $params        	
	 */
	function save($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to delete
	 *
	 * @param unknown $params        	
	 */
	function delete($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to any rest operation
	 */
	function execute($params) {
		global $KUINK_TRACE;
		return $records;
	}
	
	/**
	 * Wrapper to get
	 *
	 * @param unknown $params        	
	 * @return Ambigous <NULL, unknown>
	 */
	function load($params) {
		// kuink_mydebug(__CLASS__, __METHOD__);
		global $KUINK_TRACE;
		$this->connect ();
		
		$entity = ( string ) $this->getParam ( $params, '_entity' );
		
		// entity is mandatory
		if (! $entity)
			return null;
		$url = $this->server . ':' . $this->port . $entity . '?status=pending';
		var_dump ( $url );
		$this->response = $this->httpGet ( $url, null );
		
		return $this->decodeResponse ();
	}
	
	/**
	 * ########## Aux Functions ################## *
	 */
	private function decodeResponse() {
		switch ($this->responseType) {
			case 'json' :
				return json_decode ( $this->response, true );
		}
	}
	
	/**
	 * ########## CURL OPERATIONS ############## *
	 */
	private function httpGet($url, $params) {
		// next example will recieve all messages for specific conversation
		$service_url = $url;
		$curl = curl_init ( $service_url );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		$curl_response = curl_exec ( $curl );
		if ($curl_response === false) {
			$info = curl_getinfo ( $curl );
			curl_close ( $curl );
			throw new \Kuink\Core\Exception\HttpRequestFailed ( 'Error during HTTP request execution' );
		}
		curl_close ( $curl );
		return $curl_response;
	}
}

?>
