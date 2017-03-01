<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of ArchivematicaConnector
 *
 * @author André Bittencourt
 */
class ArchivematicaConnector extends \Kuink\Core\DataSourceConnector {

	private $response     = null;
	private $responseType = "json";
	private $server       = null;

	/**
	 * Connect using any strategy
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	function connect() {
		$this->server = $this->dataSource->getParam ( 'server', true );
		$this->username = $this->dataSource->getParam ('username', true);
		$this->api_key = $this->dataSource->getParam ('api_key', true);
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
		global $KUINK_TRACE;
		$this->connect();

		$entity = (string)$this->getParam($params, '_entity');
		$directory = (string)$this->getParam($params, 'directory');
		//entity is mandatory
		if (!$entity)
			return null;
		if($entity == 'unapproved') {
			$url = $this->server.$entity.'?username='.(string)$this->username.'&api_key='.(string)$this->api_key;
			$this->response = $this->httpRequest($url, null);
			$results = $this->decodeResponse();
			return $results['results'];
		}
		else if($entity == 'approved') {
			$url = $this->server.$entity;
			$data .= http_build_query(array('username' => (string)$this->username));
			$data .= http_build_query(array('api_key' => (string)$this->api_key));
			$data .= http_build_query(array('directory' => $directory));
			$this->response = $this->httpRequest($url, $data);
			$results = $this->decodeResponse();
			return $results;
		}
		else
			return null;
	}

	/** ########## Aux Functions ################## **/
	private function decodeResponse() {
		switch ($this->responseType) {
			case 'json':
				return json_decode($this->response, true);
		}
	}

	/** ########## CURL OPERATIONS ############## **/
	private function  httpRequest($url, $params) {
		$service_url = $url;
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if ($params != null)
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			throw new \Kuink\Core\Exception\HttpRequestFailed('Error during HTTP request execution');
		}
		curl_close($curl);
		return $curl_response;
	}

}

?>
