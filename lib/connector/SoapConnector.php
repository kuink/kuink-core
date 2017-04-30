<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Soap Client using Microsoft's NTLM Authentication.
 */
class NTLMSoapClient extends \SoapClient {
	/**
	 * cURL resource used to make the SOAP request
	 *
	 * @var resource
	 */
	protected $ch;
	
	/**
	 * Whether or not to validate ssl certificates
	 *
	 * @var boolean
	 */
	protected $validate = false;
	
	/**
	 * Performs a SOAP request
	 *
	 * @link http://php.net/manual/en/function.soap-soapclient-dorequest.php
	 *      
	 * @param string $request
	 *        	the xml soap request
	 * @param string $location
	 *        	the url to request
	 * @param string $action
	 *        	the soap action.
	 * @param integer $version
	 *        	the soap version
	 * @param integer $one_way        	
	 * @return string the xml soap response.
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0, $user, $passwd) {
		global $KUINK_TRACE;
		$headers = array (
				'Method: POST',
				// 'Connection: Keep-Alive',
				'User-Agent: PHP-SOAP-CURL',
				'Content-Type: text/xml; charset=utf-8',
				'SOAPAction: "' . $action . '"' 
		);
		
		$this->__last_request_headers = $headers;
		$this->ch = curl_init ( $location );
		
		// SSL Stuff
		// curl_setopt($this->ch, CURLOPT_VERBOSE, true);
		// $verbose = fopen('php://temp', 'rw+');
		// curl_setopt($this->ch, CURLOPT_STDERR, $verbose);
		
		curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYPEER, true );
		curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYHOST, true );
		curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $this->ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $this->ch, CURLOPT_POST, true );
		curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, trim ( $request ) );
		curl_setopt ( $this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt ( $this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM );
		curl_setopt ( $this->ch, CURLOPT_USERPWD, $user . ':' . $passwd );
		
		$response = curl_exec ( $this->ch );
		
		// $KUINK_TRACE[] = $response;
		// die(var_dump($response));
		// rewind($verbose);
		// $verboseLog = stream_get_contents($verbose);
		// var_dump($verboseLog);
		
		// TODO: Add some real error handling.
		// If the response if false than there was an error and we should throw
		// an exception.
		if ($response === false) {
			throw new \Exception ( 'Curl error: ' . curl_error ( $this->ch ), curl_errno ( $this->ch ) );
		}
		curl_close ( $this->ch );
		
		$resultData = array ();
		if ($response != '' && strpos ( $response, 'soap:Fault' ) === false) {
			// Parse the response
			$xml = simplexml_load_string ( htmlspecialchars_decode ( $response ) );
			$ns = $xml->getNamespaces ( true );
			
			$soap = $xml->children ( $ns ['soap'] );
			$result1 = ($soap->Body->children () [0]);
			$result2 = ($result1->children () [0]);
			$rows = $result2->children ();
			$resultData = array ();
			foreach ( $rows as $row ) {
				$attrs = array ();
				foreach ( $row->attributes () as $key => $value )
					$attrs [( string ) $key] = ( string ) $value;
				$resultData [] = $attrs;
			}
		} else {
			$KUINK_TRACE [] = $response;
		}
		
		return $resultData;
	}
	
	/**
	 * Returns last SOAP request headers
	 *
	 * @link http://php.net/manual/en/function.soap-soapclient-getlastrequestheaders.php
	 *      
	 * @return string the last soap request headers
	 */
	public function __getLastRequestHeaders() {
		return implode ( 'n', $this->__last_request_headers ) . "\n";
	}
	
	/**
	 * Sets whether or not to validate ssl certificates
	 *
	 * @param boolean $validate        	
	 */
	public function validateCertificate($validate = true) {
		$this->validate = $validate;
		
		return true;
	}
}

/**
 * Description of SoapConnector
 *
 * @author paulo.tavares
 */
class SoapConnector extends \Kuink\Core\DataSourceConnector {
	var $soapClient; // The soapClient object
	function connect() {
		global $KUINK_CFG;
		// neon_mydebug(__CLASS__, __METHOD__);
		
		if (! $this->soapClient) {
			$wsdl = $KUINK_CFG->appRoot . '/apps/' . $this->dataSource->getParam ( 'wsdl', true );
			$user = $this->dataSource->getParam ( 'user', true );
			$passwd = $this->dataSource->getParam ( 'passwd', true );
			
			$authParams = array (
					'login' => $user,
					'password' => $passwd 
			);
			
			$this->soapClient = new NTLMSoapClient ( $wsdl, $authParams );
		}
	}
	
	/**
	 *
	 * @see \Kuink\Core\DataSourceConnector::insert()
	 */
	function insert($params) {
		global $KUINK_TRACE;
		
		$resultData = $this->execute ( $params );
		
		return $resultData;
	}
	function update($params) {
		global $KUINK_TRACE;
		
		$resultData = $this->execute ( $params );
		
		return $resultData;
	}
	function save($params) {
		global $KUINK_TRACE;
		
		$this->execute ( $params );
		
		return;
	}
	function delete($params) {
		global $KUINK_TRACE;
		
		$resultData = $this->execute ( $params );
		
		return $resultData;
	}
	
	/**
	 * *
	 */
	function execute($params) {
		global $KUINK_TRACE;
		
		// Never execute when this datasource is configured as bypass
		if ($this->dataSource->bypass) {
			$NEON_TRACE [] = $this->dataSource->name . ' BYPASS=TRUE';
			$NEON_TRACE [] = 'Bypassing call to the server';
			return null;
		}
		
		$wsFunction = $params ['_entity'];
		$this->connect ();
		
		$request = $this->prepareRequestToExecute ( $params );
		
		$prefix = $this->dataSource->getParam ( 'prefix', false );
		$location = $this->dataSource->getParam ( 'server', true );
		$action = $prefix . $wsFunction;
		$version = $this->dataSource->getParam ( 'version', true );
		;
		$user = $this->dataSource->getParam ( 'user', true );
		;
		$passwd = $this->dataSource->getParam ( 'passwd', true );
		;
		$oneWay = 0;
		
		$rawXMLresponse = $this->soapClient->__doRequest ( $request, $location, $action, $version, $oneWay, $user, $passwd );
		
		$NEON_TRACE [] = '<pre>' . htmlspecialchars ( $request ) . '</pre>';
		$NEON_TRACE [] = '<pre>' . htmlspecialchars ( $rawXMLresponse ) . '</pre>';
		
		return $rawXMLresponse;
	}
	function getEntities($params) {
		global $KUINK_TRACE;
		
		$this->connect ();
		
		$entities = array ();
		$result = $this->soapClient->__getFunctions ();
		foreach ( $result as $function ) {
			$comp = explode ( '(', $function );
			$comp2 = explode ( ' ', $comp [0] );
			$entity = array ();
			$entity ['entity'] = $comp2 [1];
			$entity ['input'] = str_replace ( ' $parameters)', '', $comp [1] );
			$entity ['output'] = $comp2 [0];
			
			$entities [$comp2 [1]] = $entity;
		}
		return $entities;
	}
	function getAttributes($params) {
		global $KUINK_TRACE;
		
		$type = $params ['_entity'];
		$this->connect ();
		
		$result = $this->parseTypes ( $this->soapClient->__getTypes () );
		// var_dump($result);
		/*
		 * foreach ($result as $function) {
		 * $comp=explode('(', $function);
		 * $comp2=explode(' ',$comp[0]);
		 * $entity=array();
		 * $entity['entity']=$comp2[1];
		 * $entity['input']=str_replace(' $parameters)', '', $comp[1]);
		 * $entity['output']=$comp2[0];
		 *
		 * $entities[]=$entity;
		 * }
		 */
		
		// $type='ConsultaDefault';
		return $result [$type];
	}
	private function parseTypes($types) {
		$attributes = array ();
		foreach ( $types as $type ) {
			$comp = explode ( '{', $type );
			$typeName = trim ( str_replace ( 'struct ', '', $comp [0] ) );
			$cleanType = str_replace ( '; }', '', $comp [1] );
			$cleanType = str_replace ( ' }', '', $cleanType );
			$comp2 = explode ( ';', $cleanType );
			$attrs = array ();
			foreach ( $comp2 as $attribute ) {
				$comp3 = explode ( ' ', $attribute );
				// var_dump($comp3);
				$attr = array ();
				$attr ['type'] = $comp3 [1];
				$attr ['name'] = $comp3 [2];
				if ($comp3 [2] != '')
					$attrs [] = $attr;
			}
			// $entity=array();
			// $entity['entity']=$comp2[1];
			// $entity['input']=str_replace(' $parameters)', '', $comp[1]);
			// $entity['output']=$comp2[0];
			
			// $entities[]=$entity;
			$attributes [$typeName] = $attrs;
			// var_dump($comp2);
		}
		// var_dump($attributes);
		return $attributes;
	}
	function load($params) {
		global $KUINK_TRACE;
		
		$resultData = $this->execute ( $params );
		
		return isset ( $resultData [0] ) ? $resultData [0] : null;
	}
	function getAll($params) {
		global $KUINK_TRACE;
		
		$resultData = $this->execute ( $params );
		
		return $resultData;
	}
	
	/**
	 * This will receive the params of the statement and will transform
	 * the preparedStatementXml into a PDO ready string
	 * 
	 * @param unknown $params        	
	 * @return unknown
	 */
	private function prepareRequestToExecute($params) {
		$prefix = $this->dataSource->getParam ( 'prefix', false );
		$entity = $params ['_entity'];
		$call = '<' . $entity . ' xmlns="' . $prefix . '">';
		
		unset ( $params ['_pk'] );
		unset ( $params ['_entity'] );
		
		$soapParams = '';
		foreach ( $params as $paramName => $paramValue ) {
			if (is_array ( $paramValue )) {
				$value = '';
				foreach ( $paramValue as $pKey => $pValue )
					$value .= '<' . $pKey . '>' . $pValue . '</' . $pKey . '>';
			} else {
				$value = $paramValue;
			}
			$soapParams .= '<' . $paramName . '>' . $value . '</' . $paramName . '>';
		}
		$call .= $soapParams . '</' . $entity . '>';
		
		$envelope = '
  		<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
		  <soap:Body>' . $call . '
		  </soap:Body>
		</soap:Envelope> 
  	';
		
		return $envelope;
	}
}

?>
