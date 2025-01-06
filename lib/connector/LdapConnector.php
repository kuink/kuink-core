<?php

/*
 * To change this template, choose Tools | Templates and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Connect to a LDAP Server
 *
 * @author paulo.tavares
 */
class LdapConnector extends \Kuink\Core\DataSourceConnector {
	var $client; // The ldap client

	/***
	 * Initialize the object to connect to LDAP server
	 */
	function connect($noBind = false) {
		global $KUINK_CFG;
		
		if (! $this->client) {
			$server = $this->dataSource->getParam ( 'server', true );
			$this->client = ldap_connect ( $server );
			ldap_set_option ( $this->client, LDAP_OPT_PROTOCOL_VERSION, 3 );
			ldap_set_option ( $this->client, LDAP_OPT_REFERRALS, 0 );
			
			$anonymousBind = $this->dataSource->getParam ( 'anonymousBind', true );
			if ($anonymousBind != 'true' && ! $noBind)
				$bind = ldap_bind ( $this->client, $this->dataSource->getParam ( 'user', true ), $this->dataSource->getParam ( 'passwd', true ) );
			$errno = ldap_errno ( $this->client );
			// print_object($errno);
			if ($errno) {
				throw new \Exception ( ldap_error ( $this->client ) );
			}
		}
		return true;
	}

	/**
	 * Performs an ldap_close
	 */
	function disconnect() {
		ldap_close ( $this->client );
		$this->client = null;
	}

	/* Generic connector functions */

	/**
	 * Loads a user from LDAP
	 */	
	function load($params) {
		global $KUINK_TRACE;
		
		$this->connect ();
		// Build or complete the query
		$query = '';
		$queryParams = array ();
		foreach ( $params as $key => $value ) {
			if ($key [0] != '_') {
				if ($key == 'id')
					$key = ( string ) $this->dataSource->getParam ( 'loginField', true );
				$queryParams [$key] = $value;
			}
		}
		if (count ( $queryParams ) > 0) {
			$query .= '(&';
			foreach ( $queryParams as $key => $value )
				$query .= '(' . $key . '=' . $value . ')';
			
			$query .= ')';
			
			if (isset($params ['_query']))
				$params ['_query'] .= $query;
			else
				$params ['_query'] = $query;
		}
		// print_object($params);
		// die();
		
		$resultData = $this->execute ( $params );
		
		// print_object($resultData);
		$this->disconnect ();
		
		return isset ( $resultData [0] ) ? $resultData [0] : null;
	}

	/**
	 * GetAll users matching from LDAP
	 */	
	function getAll($params) {
		$resultData = $this->execute ( $params );
		
		return $resultData;
	}

	/**
	 * Inserts a user in LDAP
	 */
	function insert($params) {
		global $KUINK_TRACE;
		$this->connect ();
		
		$bind = $this->bind ( $params ); // bind as admin by default
		
		if (! $bind)
			return 0;
			
			// $entity = (string)$this->getParam($params, '_entity', true);
		$id = ( string ) $this->getParam ( $params, 'uid', true );
		$type = ( string ) $this->getParam ( $params, 'type', true );
		$name = ( string ) $this->getParam ( $params, 'name', true );
		// print_object($params);
		// get the OU
		$ou = $this->dataSource->getParam ( 'config.ouByType.' . $type, false );
		if (trim ( $ou ) == '')
			$ou = $this->dataSource->getParam ( 'config.ouByType._default', false );
			// var_dump($ou);
		if (trim ( $ou ) == '')
			throw new \Exception ( 'LDAP: OU not found for type ' . $type );
			
			// build the dn
		$dn = 'cn=' . $id . ',' . $ou; // 'cn='.$name.','.$ou;
		                         // var_dump('AA');
		$info = $this->getUserInfoFromParams ( $params );
		$KUINK_TRACE [] = $info;
		foreach ( $info as $infoKey => $infoValue )
			$KUINK_TRACE [] = $infoKey . ' : ' . $infoValue;
			// print_object($info);
		
		$ldapAddError = ( string ) ldap_error ( $this->client );
		// print_object($ldapAddError);
		$KUINK_TRACE [] = $ldapAddError;
		$KUINK_TRACE [] = ( string ) ldap_errno ( $this->client );
		// print_object($dn);
		$add = ldap_add ( $this->client, $dn, $info );
		// var_dump($add);
		$ldapAddError = ( string ) ldap_error ( $this->client );
		// print_object($ldapAddError);
		$KUINK_TRACE [] = $ldapAddError;
		
		$this->disconnect ();
		return $add;
	}

	/**
	 * Updates a user in LDAP
	 * This method don't update the password
	 */
	function update($params) {
		global $KUINK_TRACE;
		
		$this->connect ();
		// Get the dn, can only update given the dn
		
		// CN=5,CN=Users,DC=ds,DC=cscm-lx,DC=pt
		
		$entity = isset ( $params ['_entity'] ) ? ( string ) $params ['_entity'] : $this->dataSource->getParam ( 'entity', true );
		
		$dn = $this->getDn ( $entity, $params ['uid'] );
		// print_object($dn);
		/*
		 * if (isset ( $params ['dn'] ))
		 * $dn = ( string ) $params ['dn'];
		 * else
		 * throw new \Exception ( 'To update please set the dn' );
		 */
		// Try to bind with the user
		if (isset ( $params ['_passwd'] )) {
			$passwd = $params ['_passwd'];
			$bind = ldap_bind ( $this->client, $dn, $passwd );
			if (! $bind)
				throw new \Exception ( 'Cannot login with user: ' . $dn );
		} else {
			// Get the admin information from configuration
			$user = $this->dataSource->getParam ( 'user', true );
			$passwd = $this->dataSource->getParam ( 'passwd', true );
			$bind = ldap_bind ( $this->client, $user, $passwd );
			if (! $bind)
				throw new \Exception ( 'Cannot login with user: ' . $user );
		}
		// print_object('passed');
		// $passwd = (! isset ( $params ['_passwd'] )) ? $this->dataSource->getParam ( 'passwd', true ) : ( string ) $params ['_passwd'];
		
		// $bind = ldap_bind ( $this->client, $dn, $passwd );
		
		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_user'] );
		unset ( $params ['_passwd'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_query'] );
		unset ( $params ['_pk'] );
		unset ( $params ['dn'] );
		// $resultData=$this->execute($params);
		// print_object($params);
		// print_object($params);
		$info = $this->getUserInfoFromParams ( $params, true ); // true for update purpose
		                                                     // print_object($info);
		
		$replace = ldap_mod_replace ( $this->client, $dn, $info );
		$KUINK_TRACE [] = 'ldapUpdate: ' . $replace;
		$ldapMessage = ( string ) ldap_error ( $this->client );
		$KUINK_TRACE [] = 'ldapMessage: ' . $ldapMessage;
		$KUINK_TRACE [] = 
		// print_object ( ldap_error ( $this->client ) );
		$this->disconnect ();
		if ($replace)
			return 1;
		else
			return 0;
	}

	/**
	 * Saves a user in LDAP
	 */	
	function save($params) {
		global $KUINK_TRACE;
		
		$this->execute ( $params );
		
		return;
	}

	/**
	 * Deletes a user in LDAP
	 */
	function delete($params) {
		global $KUINK_TRACE;
		
		$resultData = $this->execute ( $params );
		
		return $resultData;
	}
	
	/**
	 * Generic Execute in LDAP
	 */
	function execute($params) {
		global $KUINK_TRACE;
		
		$entity = (! isset ( $params ['_entity'] )) ? $this->dataSource->getParam ( 'entity', true ) : ( string ) $params ['_entity'];
		$attrs = isset ( $params ['_attributes'] ) ? ( string ) $params ['_attributes'] : '';
		$query = isset ( $params ['_query'] ) ? ( string ) $params ['_query'] : ''; // (cn=*)';
		$sort = isset ( $params ['_sort'] ) ? ( string ) $params ['_sort'] : '';
		$user = isset ( $params ['_user'] ) ? ( string ) $params ['_user'] : '';
		$passwd = isset ( $params ['_passwd'] ) ? ( string ) $params ['_passwd'] : '';
		// $pageSize = isset($params['_pageSize']) ? (string) $params['_pageSize'] : '';
		// $pageNum = isset($params['_pageNum']) ? (string) $params['_pageNum'] : '';
		$KUINK_TRACE [] = 'entity: ' . $entity;
		$KUINK_TRACE [] = 'ldap query: ' . $query;
		// $this->connect ( $entity, $this->dataSource->getParam('user', true ), $this->dataSource->getParam('passwd', true ) );
		$this->connect ();
		
		$attributes = ($attrs != '') ? explode ( ',', $attrs ) : null;
		if ($attributes)
			$search = ldap_search ( $this->client, $entity, $query, $attributes );
		else
			$search = ldap_search ( $this->client, $entity, $query );
		
		if ($sort != '')
			ldap_sort ( $this->client, $search, $sort );
			
			// print_object($query);
		
		$result = ldap_get_entries ( $this->client, $search );
		
		// $a = ldap_control_paged_result_response($this->client, $search, $cookie);
		
		$return = array ();
		foreach ( $result as $row ) {
			$returnRow = array ();
			if (is_array($row))
				foreach ( $row as $key => $value )
					if (($key != 'count') && ($key != 'objectclass') && ((is_array ( $value )) || ($key == 'dn')))
						$returnRow [$key] = is_array ( $value ) ? ( string ) $value [0] : ( string ) $value;
			if (! empty ( $returnRow ))
				$return [] = $returnRow;
		}
		// print_object($return);
		
		return $return;
	}

	/**
	 * Get a param from the datasource
	 */
	public function getDataSourceParam($params) {
		$name = (isset ( $params ['name'] )) ? ( string ) $params ['name'] : '';
		
		return $this->dataSource->getParam ( $name, false, '' );
	}

	/**
	 * Get dn
	 */
	public function getDn($entity, $uid) {
		// get dn
		$server = $this->dataSource->getParam ( 'server', true );
		$loginField = $this->dataSource->getParam ( 'loginField', true );
		$filter = '(' . $loginField . '=' . $uid . ')';
		$sr = ldap_search ( $this->client, $entity, $filter );
		$errno = ldap_errno ( $this->client );
		$entries = ldap_get_entries ( $this->client, $sr );
		// print_object($entries);
		// print_object($entries);
		// die();
		
		if ($entries ['count'] == 0)
			return false;
		
		return $entries [0] ['dn'];
	}

	private function getUserInfoFromParams($user, $forUpdate = false) {
		$info = array ();
		
		if (! $forUpdate)
			$info ['cn'] = ( string ) $this->getParam ( $user, 'uid', true );
		
		$info ['gidNumber'] = ( int ) $this->dataSource->getParam ( 'gidNumber', true );
		$info ['givenName'] = ( string ) $this->getParam ( $user, 'name', true );
		
		// $homeDirectoryBase = (string)$this->dataSource->getParam('homeDirectoryBase', true );
		$info ['unixHomeDirectory'] = ( string ) $this->dataSource->getParam ( 'unixHomeDirectory', true );
		$info ['unixHomeDirectory'] .= ( string ) $this->getParam ( $user, 'uid', true );
		
		$defaultPager = (string)$this->dataSource->getParam('pager.default', false, '');
		if ($defaultPager != '')
			$info['pager'] = (string)$this->getParam($user, 'pager', false, $defaultPager);

		$homeDirectoryKey = 'homeDirectory.' . $this->getParam ( $user, 'personTypeCode', true );
		$homeDirectory = ( string ) $this->dataSource->getParam ( $homeDirectoryKey, false );
		
		// If not found then get the default
		if ($homeDirectory == '')
			$homeDirectory = ( string ) $this->dataSource->getParam ( 'homeDirectory.default', true );
		$homeDirectory .= ( string ) $this->getParam ( $user, 'uid', true );
		
		$info ['homeDirectory'] = $homeDirectory;
		$info ['objectclass'] = array ();
		$info ['objectclass'] [0] = 'top';
		$info ['objectclass'] [1] = 'person';
		$info ['objectclass'] [2] = 'organizationalPerson';
		$info ['objectclass'] [3] = 'user';
		$info ['objectclass'] [4] = 'posixAccount';
		// $info['objectclass'][4] = 'sambaSamAccount';
		$info ['uid'] = ( string ) $this->getParam ( $user, 'uid', true );
		
		$id = ( int ) $this->getParam ( $user, 'id', true );
		$uidNumberBase = ( int ) $this->dataSource->getParam ( 'uidNumberBase', true );
		$uidNumber = $id + $uidNumberBase;
		$info ['uidNumber'] = $uidNumber;
		
		// $info['homeDirectory'] = '\\\\saturno.cscm-lx.pt\\'.$info['uid'];
		$info ['homeDrive'] = ( string ) $this->dataSource->getParam ( 'homeDrive', true );
		$info ['loginShell'] = ( string ) $this->dataSource->getParam ( 'loginShell', true );
		$info ['msSFU30NisDomain'] = ( string ) $this->dataSource->getParam ( 'nisDomain', true );
		
		$domain = ( string ) $this->dataSource->getParam ( 'domain', true );
		// $info['pager'] = ''; //get id_card from person
		$info ['userPrincipalName'] = $this->getParam ( $user, 'uid', true ) . '@' . $domain;
		$info ['userAccountControl'] = ( int ) $this->dataSource->getParam ( 'userAccountControl', true );
		
		$setPassword = $this->getParam ( $user, 'password', false );

		//Get encrypted password
		$userPassword = $this->getUserPassword ( $setPassword );
		// print_object($user['password']);
		if ($setPassword != '')
			foreach ( $userPassword as $key => $value )
				$info [$key] = $value;
		
		$info ['sn'] = ( string ) $this->getParam ( $user, 'surname', true );
		// $info['uidNumber'] = 1002;
		
		$info ['mail'] = ( string ) $this->getParam ( $user, 'email', true );
		$info ['displayName'] = ( string ) $this->getParam ( $user, 'display_name', true );
		$info ['gecos'] = ( string ) $this->getParam ( $user, 'gecos', true );
		
		$info ['sAMAccountName'] = ( string ) $this->getParam ( $user, 'uid', true );
		// print_object($user);
		// print_object($info);
		return $info;
	}
	
	/**
	 * Get all entities supported by this connector
	 */
	function getEntities($params) {
		global $KUINK_TRACE;
		
		$entities = array();
		$entities[] = 'user';
		
		return $entities
		;
	}

	/**
	 * Get attributes defined for an entity
	 */
	function getAttributes($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  
		
		return null;
	}

	 /** Authentication related functions  */
	/*
	 * Change the password by validating first the old one
	 * Errors: 0 - No errors 1 - wrong old password 2 - password validation failed
	 */
	function changePassword($params) {
		$this->connect ();
		$dn = $this->getParam ( $params, 'dn', true );
		$oldPasswd = $this->getParam ( $params, 'oldPassword', true );
		$password = $this->getParam ( $params, 'newPassword', true );
		$bind = ldap_bind ( $this->client, $dn, $oldPasswd );
		if (! $bind)
			return 1;
		$result = $this->ldapChangePassword ( $dn, $password );
		
		return $result;
	}

	/*
	 * Forces an update of the password without validations
	 * Errors: 0 - No errors 1 - wrong old password 2 - password validation failed
	 */
	function setPassword($params) {
		// print_object($params);
		$this->connect ();
		$bind = $this->bind ( $params ); // bind as admin by default
		                              
		// print_object($bind);
		if (! $bind)
			return 1;
		
		$dn = $this->getParam ( $params, 'dn', true );
		$password = $this->getParam ( $params, 'newPassword', true );
		
		$result = $this->ldapChangePassword ( $dn, $password );
		$this->disconnect ();
		return $result;
	}

	/**
	 * Check if the user and password are valid
	 */
	function bind($params) {
		$this->connect ( false );
		$user = (! isset ( $params ['_user'] )) ? $this->dataSource->getParam ( 'user', true ) : ( string ) $params ['_user'];
		$passwd = (! isset ( $params ['_passwd'] )) ? $this->dataSource->getParam ( 'passwd', true ) : ( string ) $params ['_passwd'];
		$entity = (! isset ( $params ['_entity'] )) ? $this->dataSource->getParam ( 'entity', true ) : ( string ) $params ['_entity'];
		
		if (isset ( $params ['_user'] ))
			$dn = $this->getDn ( $entity, $user );
		else
			$dn = $user;
			// var_dump($dn);
		$bind = ldap_bind ( $this->client, $dn, $passwd );
		if ($bind)
			return 1;
		else
			return 0;
	}

	/** PRIVATE auxiliary functions */

	/**
	 * Encrypt a user password to store in LDAP
	 */	
	private function getUserPassword($password) {
		// Get this from configs
		$sambaMode = $this->dataSource->getParam ( 'sambaMode', false, 'false' );
		$adMode = ($this->dataSource->getParam ( 'adMode', false, 'false' ));
		
		$samba_mode = ($sambaMode == 'true');
		$ad_mode = ($adMode == 'true');
		
		$hash = $this->dataSource->getParam ( 'hash', false, 'MD5' );
		
		// change the password
		// Set Samba password value
		if ($samba_mode) {
			$userdata ["sambaNTPassword"] = $this->make_md4_password ( $password );
			$userdata ["sambaPwdLastSet"] = time ();
		}
		
		// Transform password value
		if ($ad_mode) {
			$password = $this->make_ad_password ( $password );
		} else {
			// Hash password if needed
			if ($hash == "SSHA") {
				$password = $this->make_ssha_password ( $password );
			}
			if ($hash == "SHA") {
				$password = $this->make_sha_password ( $password );
			}
			if ($hash == "SMD5") {
				$password = $this->make_smd5_password ( $password );
			}
			if ($hash == "MD5") {
				$password = $this->make_md5_password ( $password );
			}
			if ($hash == "CRYPT") {
				$password = $this->make_crypt_password ( $password );
			}
		}
		
		// Set password value
		if ($ad_mode) {
			$userdata ["unicodePwd"] = $password;
			if ($ad_options ['force_unlock']) {
				$userdata ["lockoutTime"] = 0;
			}
			if ($ad_options ['force_pwd_change']) {
				$userdata ["pwdLastSet"] = 0;
			}
		} else {
			$userdata ["userPassword"] = $password;
		}
		
		// Shadow options
		if ($shadow_options ['update_shadowLastChange']) {
			$userdata ["shadowLastChange"] = floor ( time () / 86400 );
		}
		
		return $userdata;
	}

	private function ldapChangePassword($dn, $password) {
		// Get this from configs
		$adVersion = ($this->dataSource->getParam ( 'adVersion', true ));
		$ldapMode = ($this->dataSource->getParam ( 'ldapMode', true ));
		
		if ($adVersion != 'Samba.3' && $adVersion != 'Samba.4' && $adVersion != 'AD')
			throw new \Exception ( 'Invalid authentication service: ' . $adVersion );
		if ($ldapMode != 'true' && $ldapMode != 'false')
			throw new \Exception ( 'Invalid authentication ldapMode: ' . $ldapMode );
		
		$hash = $this->dataSource->getParam ( 'hash', false, 'MD5' );
		
		// change the password
		// Set Samba password value
		if ($adVersion == 'Samba.3') {
			$userdata ["sambaNTPassword"] = $this->make_md4_password ( $password );
			$userdata ["sambaPwdLastSet"] = time ();
		} else if ($adVersion == 'AD' || $adVersion == 'Samba.4') {
			$password = $this->make_ad_password ( $password );
		}
		
		if ($ldapMode == 'true') {
			// Hash password if needed
			if ($hash == "SSHA") {
				$password = $this->make_ssha_password ( $password );
			}
			if ($hash == "SHA") {
				$password = $this->make_sha_password ( $password );
			}
			if ($hash == "SMD5") {
				$password = $this->make_smd5_password ( $password );
			}
			if ($hash == "MD5") {
				$password = $this->make_md5_password ( $password );
			}
			if ($hash == "CRYPT") {
				$password = $this->make_crypt_password ( $password );
			}
		}
		
		// Set password value
		if ($adVersion == 'AD' || $adVersion == 'Samba.4') {
			$userdata ["unicodePwd"] = $password;
			if ($ad_options ['force_unlock']) {
				$userdata ["lockoutTime"] = 0;
			}
			if ($ad_options ['force_pwd_change']) {
				$userdata ["pwdLastSet"] = 0;
			}
		} else if ($ldapMode == 'true') {
			$userdata ["userPassword"] = $password;
		}
		
		// Shadow options
		if ($shadow_options ['update_shadowLastChange']) {
			$userdata ["shadowLastChange"] = floor ( time () / 86400 );
		}
		
		// Commit modification on directory
		// Just replace with new password
		$replace = ldap_mod_replace ( $this->client, $dn, $userdata );
		$errno = ldap_errno ( $this->client );
		if ($errno)
			return 2;
		
		return 0;
	}

	// Create SSHA password
	private function make_ssha_password($password) {
		mt_srand ( ( double ) microtime () * 1000000 );
		$salt = pack ( "CCCC", mt_rand (), mt_rand (), mt_rand (), mt_rand () );
		$hash = "{SSHA}" . base64_encode ( pack ( "H*", sha1 ( $password . $salt ) ) . $salt );
		return $hash;
	}
	
	// Create SHA password
	private function make_sha_password($password) {
		$hash = "{SHA}" . base64_encode ( pack ( "H*", sha1 ( $password ) ) );
		return $hash;
	}
	
	// Create SMD5 password
	private function make_smd5_password($password) {
		mt_srand ( ( double ) microtime () * 1000000 );
		$salt = pack ( "CCCC", mt_rand (), mt_rand (), mt_rand (), mt_rand () );
		$hash = "{SMD5}" . base64_encode ( pack ( "H*", md5 ( $password . $salt ) ) . $salt );
		return $hash;
	}
	
	// Create MD5 password
	private function make_md5_password($password) {
		$hash = "{MD5}" . base64_encode ( pack ( "H*", md5 ( $password ) ) );
		return $hash;
	}
	
	// Create CRYPT password
	private function make_crypt_password($password) {
		
		// Generate salt
		$possible = '0123456789' . 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . './';
		$salt = "";
		
		mt_srand ( ( double ) microtime () * 1000000 );
		
		while ( strlen ( $salt ) < 2 )
			$salt .= substr ( $possible, (rand () % strlen ( $possible )), 1 );
		
		$hash = '{CRYPT}' . crypt ( $password, $salt );
		return $hash;
	}
	
	// Create MD4 password (Microsoft NT password format)
	private function make_md4_password($password) {
		if (function_exists ( 'hash' )) {
			$hash = strtoupper ( hash ( "md4", iconv ( "UTF-8", "UTF-16LE", $password ) ) );
		} else {
			$hash = strtoupper ( bin2hex ( mhash ( MHASH_MD4, iconv ( "UTF-8", "UTF-16LE", $password ) ) ) );
		}
		return $hash;
	}
	
	// Create AD password (Microsoft Active Directory password format)
	private function make_ad_password($password) {
		$password = "\"" . $password . "\"";
		$len = strlen ( utf8_decode ( $password ) );
		$adpassword = "";
		for($i = 0; $i < $len; $i ++) {
			// PHP 8.0, fix from		$adpassword .= "{$password{$i}}\000";
			$adpassword .= "{$password[$i]}\000";
		}
		return $adpassword;
	}
	
	/**
	 * Get the schema name
	 */
	public function getSchemaName($params) {
		return null;
	}
	
}
