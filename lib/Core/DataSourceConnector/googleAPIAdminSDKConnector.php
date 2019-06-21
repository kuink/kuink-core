<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

use Kuink\Core\Exception\NotImplementedException;
use Kuink\Core\Exception\ParameterNotFound;

/**
 * Description of GoogleAPIConnector
 *
 * @author paulo.tavares
 */
class GoogleAPIAdminSDKConnector extends \Kuink\Core\DataSourceConnector {
	var $connector;
	var $service;
	var $accessToken;
	var $clientID;
	var $serviceAccountName;
	var $keyfile;
	var $applicationName;
	var $delegatedAdmin;
	var $domain;
	var $scopes;
	function connect() {
		global $KUINK_CFG;
		
		$this->connector = isset ( $this->connector ) ? $this->connector : '';
		if (! $this->connector) {
			// Connect to the server
			$this->keyfile = $this->dataSource->getParam ( 'keyfile', true );
			$this->delegatedAdmin = $this->dataSource->getParam ( 'delegatedAdmin', true );
			$this->domain = $this->dataSource->getParam ( 'domain', true );
			$this->scopes = $this->dataSource->getParam ( 'scopes', true );
			
			if (! file_exists ( $KUINK_CFG->appRoot . '/apps/' . $this->keyfile ))
				throw new \Exception ( __CLASS__ . ': invalid key file ' . $this->keyfile );
			
			$this->connector = new \Google_Client();
			$this->connector->useApplicationDefaultCredentials();
			$this->connector->setAuthConfig($KUINK_CFG->appRoot . '/apps/' . $this->keyfile);
			$this->connector->setAccessType('offline');
			$this->connector->setScopes($this->scopes );
			$this->connector->setSubject($this->delegatedAdmin);
		}
	}
	function userExists($params) {
		$this->connect ();
		
		$uid = isset ( $params ['uid'] ) ? ( string ) $params ['uid'] : '';
		$dir = new \Google_Service_Directory ( $this->connector );
		
		try {
			$account = $dir->users->get ( $uid );
		} catch ( \Exception $e ) {
			return 0;
		}
		
		return 1;
	}
	function load($params) {
		$this->connect ();
		
		$uid = isset ( $params ['uid'] ) ? ( string ) $params ['uid'] : '';
		$dir = new \Google_Service_Directory ( $this->connector );
		
		try {
			$account = $dir->users->get ( $uid . '@' . $this->domain );
		} catch ( \Exception $e ) {
			$KUINK_TRACE [] = 'ERROR GOOGLE ADMIN SDK'; // print_object($e->getMessage());
			$KUINK_TRACE [] = $e->getMessage(); // print_object($e->getMessage());
			$KUINK_TRACE [] = __CLASS__ . '::' . $entity . '::' . $e->getMessage (); // print_object($e->getMessage());
			                                                               // throw new \Exception($e);
			$account = null;
		}
		$user = array ();
		if ($account !== null) {
			$user ['uid'] = ( string ) $account->primaryEmail;
			$user ['email'] = ( string ) $account->primaryEmail;
			$user ['givenname'] = ( string ) $account->getName ()->givenName;
			$user ['surname'] = ( string ) $account->getName ()->familyName;
			$user ['id'] = ( string ) $account->id;
		}
		return $user;
	}
	function insert($params) {
		$this->connect ();
		
		$entity = ( string ) $this->getParam ( $params, '_entity', true ); // isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
		$id = ( string ) $this->getParam ( $params, 'id', false ); // isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
		                                                     // $type = (string)$this->getParam($params, 'type', true);//isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
		$givenName = ( string ) $this->getParam ( $params, 'given_name', true );
		$surname = ( string ) $this->getParam ( $params, 'surname', true );
		$password = ( string ) $this->getParam ( $params, 'password', true );
		$email = ( string ) $this->getParam ( $params, 'email', true );
		
		$dir = new \Google_Service_Directory ( $this->connector );
		
		if ($entity == 'user') {
			$user = new \Google_Service_Directory_User ();
			$userName = new \Google_Service_Directory_UserName ();
			
			$userName->familyName = $surname;
			$userName->givenName = $givenName;
			// var_dump($userName);
			$user->name = $userName;
			$user->password = $password;
			$user->primaryEmail = $email;
			// var_dump($user);
			try {
				$result = $dir->users->insert ( $user );
				// var_dump($result);
			} catch ( \Exception $e ) {
				// var_dump($e);
				return 0;
			}
		}
		
		return 1;
	}
	function update($params) {
		$this->connect ();
		
		$entity = isset ( $params ['_entity'] ) ? ( string ) $params ['_entity'] : 'user';
		$uid = isset ( $params ['uid'] ) ? ( string ) $params ['uid'] : '';
		if ($uid == '')
			throw new ParameterNotFound ( __CLASS__ . '.' . __METHOD__, 'uid' );
		
		if (strpos ( $uid, '@' ) === false)
			$uid = $uid . '@' . $this->domain;
		
		if (strpos ( $uid, $this->domain ) === false)
			return 0;
		
		if (isset ( $params ['_entity'] ))
			unset ( $params ['_entity'] );
		if (isset ( $params ['uid'] ))
			unset ( $params ['uid'] );
		
		$dir = new \Google_Service_Directory ( $this->connector );
		
		if ($entity == 'user') {
			$user = new \Google_Service_Directory_User ();
			
			foreach ( $params as $key => $value )
				$user->$key = is_array ( $value ) ? $value : ( string ) $value;
			try {
				$result = $dir->users->update ( $uid, $user );
			} catch ( \Exception $e ) {
				return 0;
			}
		}
		
		return 1;
	}
	function delete($params) {
		$this->connect ();
		
		$entity = ( string ) $this->getParam ( $params, '_entity', true ); // isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
		$uid = ( string ) $this->getParam ( $params, 'uid', true );
		
		$dir = new \Google_Service_Directory ( $this->connector );
		
		if ($entity == 'user') {
			$user = new \Google_Service_Directory_User ();
			$userName = new \Google_Service_Directory_UserName ();
			
			$userName->familyName = $surname;
			$userName->givenName = $givenName;
			// print_object($userName);
			$user->name = $userName;
			$user->password = $password;
			$user->primaryEmail = $email;
			// print_object($user);
			try {
				$result = $dir->users->insert ( $user );
				// print_object($result);
			} catch ( \Exception $e ) {
				// print_object($e);
				return 0;
			}
		}
		
		return 1;
	}
	function getAll($params) {
		throw new NotImplementedException ( __CLASS__, __METHOD__ );
	}

	public function getSchemaName($params) {
  	return null;
  }
}

?>
