<?php

/*
 * To change this template, choose Tools | Templates and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of SoapConnector
 *
 * @author paulo.tavares
 */
class LdapConnector extends \Kuink\Core\DataSourceConnector {
	var $client; // The ldap client
	function connect() {
		global $KUINK_CFG;
		
		if (! $this->client) {
			$server = $this->dataSource->getParam ( 'server', true );
			
			$this->client = ldap_connect ( $server );
			ldap_set_option ( $this->client, LDAP_OPT_PROTOCOL_VERSION, 3 );
			ldap_set_option ( $this->client, LDAP_OPT_REFERRALS, 0 );
			
			$errno = ldap_errno ( $this->client );
			// var_dump($errno);
			if ($errno) {
				throw new \Exception ( ldap_error ( $this->client ) );
			}
		}
		return true;
	}
	public function getDn($entity, $uid) {
		// get dn
		$server = $this->dataSource->getParam ( 'server', true );
		$filter = '(uid=' . $uid . ')';
		$sr = ldap_search ( $this->client, $entity, $filter );
		// var_dump($sr);
		$entries = ldap_get_entries ( $this->client, $sr );
		// var_dump($entries);
		if ($entries ['count'] == 0)
			return false;
		
		return $entries [0] ['dn'];
	}
	
	function bind($params) {
		$this->connect ();
		$user = (! isset ( $params ['_user'] )) ? $this->dataSource->getParam('user', true ) : ( string ) $params ['_user'];
		$passwd = (! isset ( $params ['_passwd'] )) ? $this->dataSource->getParam('passwd', true ) : ( string ) $params ['_passwd'];
		$entity = (! isset ( $params ['_entity'] )) ? $this->dataSource->getParam('entity', true ) : ( string ) $params ['_entity'];
		
		if (isset($params ['_user']))
			$dn = $this->getDn ( $entity, $user );
		else
			$dn = $user;
		//var_dump($dn);
		$bind = ldap_bind ( $this->client, $dn, $passwd );
		if ($bind)
			return 1;
		else
			return 0;
	}
	
	private function getUserInfoFromParams($user) {
		$info['cn'] = (string)$this->getParam($user, 'name', true);
		$info['gidNumber'] = 513;
		$info['givenName']= (string)$this->getParam($user, 'name', true);
		$info['homeDirectory']='/home/'.$this->getParam($user, 'uid', true);
		$info['objectclass'][0] = 'top';
		$info['objectclass'][1] = 'person';
		$info['objectclass'][2] = 'organizationalPerson';
		$info['objectclass'][3] = 'inetOrgPerson';
		$info['objectclass'][4] = 'sambaSamAccount';
		
		//Samba Stuff
		$info['sambaAcctFlags'] = '[U]';
		$info['sambaSID'] = '<SambaSID>';
		$info['sambaDomainName'] = '<Domain>';
		$info['sambaHomeDrive'] = '<Drive>';
		$info['sambaHomePath'] = "\\\\<Server>\\".$info['uid'];
		
		$userPassword = $this->getUserPassword($this->getParam($user, 'password', true));
		foreach($userPassword as $key=>$value)
			$info[$key] = $value;
		
		$info['sn'] = (string)$this->getParam($user, 'surname', true);
		$info['uidNumber'] = 1002;
		$info['uid'] = (string)$this->getParam($user, 'uid', true);

		$info['mail']= (string)$this->getParam($user, 'email', true);
		$info['displayName']= (string)$this->getParam($user, 'display_name', true);
		$info['gecos'] = (string)$this->getParam($user, 'gecos', true);
				
		return $info;
	}
	
	/**
	 *
	 * @see \Kuink\Core\DataSourceConnector::insert()
	 */
	function insert($params) {
		global $KUINK_TRACE;
		
		$this->connect();
		$bind = $this->bind($params); //bind as admin by default
		if (!$bind)
			return 0;

		$entity = (string)$this->getParam($params, '_entity', true);
		$id =  (string)$this->getParam($params, 'uid', true);
		$type =  (string)$this->getParam($params, 'type', true);
		$name = (string)$this->getParam($params, 'name', true);
		
		//get the OU
		$ou = $this->dataSource->getParam('config.ouByType.'.$type, false );
		if (trim($ou) == '')
			$ou = $this->dataSource->getParam('config.ouByType._default', false );
		
		if (trim($ou) == '') 
			throw new \Exception('LDAP: OU not found for type '.$type);

		//build the dn
		$dn = $ou; //'cn='.$name.','.$ou;
		$info = $this->getUserInfoFromParams($params);
		
		//var_dump($dn);
		//var_dump($info);

		$ldapAddError = ( string ) ldap_error ( $this->client );
		$KUINK_TRACE [] = $ldapAddError;
		//var_dump($ldapAddError);
		
		$add = ldap_add($this->client, $dn, $info);
		
		$ldapAddError = ( string ) ldap_error ( $this->client );
		$KUINK_TRACE [] = $ldapAddError; 
		//var_dump($ldapAddError);
		
		ldap_close($this->client);
		return $add;
	}
	
	function update($params) {
		global $KUINK_TRACE;
		
		$this->connect ();
		// Get the dn, can only update given the dn
		$dn = '';
		if (isset ( $params ['dn'] ))
			$dn = ( string ) $params ['dn'];
		else
			throw new \Exception ( 'To update please set the dn' );
			
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
		$passwd = (! isset ( $params ['_passwd'] )) ? $this->dataSource->getParam ( 'passwd', true ) : ( string ) $params ['_passwd'];
		
		//$bind = ldap_bind ( $this->client, $dn, $passwd );
		
		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_user'] );
		unset ( $params ['_passwd'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_query'] );
		unset ( $params ['_pk'] );
		unset ( $params ['dn'] );
		// $resultData=$this->execute($params);
		// var_dump($params);
		
		$replace = ldap_mod_replace ( $this->client, $dn, $params );
		$KUINK_TRACE [] = ( string ) ldap_error ( $this->client );
		//print_object ( ldap_error ( $this->client ) );
		if ($replace)
			return 1;
		else
			return 0;
	}

	private function getUserPassword($password) {
		//Get this from configs
		$sambaMode = $this->dataSource->getParam ( 'sambaMode', false, 'false' );
		$adMode = ($this->dataSource->getParam ( 'adMode', false, 'false' ));
		
		$samba_mode = ($sambaMode == 'true');
		$ad_mode = ($adMode == 'true');
		
		$hash =  $this->dataSource->getParam ( 'hash', false, 'MD5' );
		
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
	
	/*
	 * Assumes that a connect and bind was allready made
	 * Errors: 0 - No errors 1 - wrong old password 2 - password validation failed
	*/
	private function ldapChangePassword($dn, $password) {	
		//Get this from configs
		$sambaMode = $this->dataSource->getParam ( 'sambaMode', false, 'false' );
		$adMode = ($this->dataSource->getParam ( 'adMode', false, 'false' ));
	
		$samba_mode = ($sambaMode == 'true');
		$ad_mode = ($adMode == 'true');
	
		$hash =  $this->dataSource->getParam ( 'hash', false, 'MD5' );
	
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
	
		// Commit modification on directory
	
		// Special case: AD mode with password changed as user
		// Not possible with PHP, because requires add/delete modification
		// in a single operation
		if ($ad_mode and $who_change_password === "user") {
			$result = "passworderror";
			error_log ( "Cannot modify AD password as user" );
			return $result;
		}
		// Else just replace with new password
		$replace = ldap_mod_replace ( $this->client, $dn, $userdata );
		$errno = ldap_errno ( $ldap );
		if ($errno)
			return 2;
	
		// return $result;
		return 0;
	}	
	
	/*
	 * Errors: 0 - No errors 1 - wrong old password 2 - password validation failed
	 */
	function changePassword($params) {
		$this->connect ();
		$dn = $this->getParam ( $params, 'dn', true );
		$oldPasswd = $this->getParam ( $params, 'oldPassword', true );
		$password = $this->getParam ( $params, 'newPassword', true );
		$bind = ldap_bind ( $this->client, $dn, $oldPasswd );
		if (!$bind)
			return 1;
		$result = $this->ldapChangePassword($dn, $password);
		
		return $result;
	}

	/*
	 * Errors: 0 - No errors 1 - wrong old password 2 - password validation failed
	*/
	function setPassword($params) {
		$this->connect();
		$bind = $this->bind($params); //bind as admin by default 
		//var_dump($params);
		//var_dump($bind);
		if (!$bind)
			return 1;

		$dn = $this->getParam ( $params, 'dn', true );
		$password = $this->getParam ( $params, 'newPassword', true );		
		
		return $this->ldapChangePassword($dn, $password);
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
		
		$entity = (! isset ( $params ['_entity'] )) ? $this->dataSource->getParam ( 'entity', true ) : ( string ) $params ['_entity'];
		$attrs = isset ( $params ['_attributes'] ) ? ( string ) $params ['_attributes'] : '';
		$query = isset ( $params ['_query'] ) ? ( string ) $params ['_query'] : '';//(cn=*)';
		$sort = isset ( $params ['_sort'] ) ? ( string ) $params ['_sort'] : '';
		$user = isset ( $params ['_user'] ) ? ( string ) $params ['_user'] : '';
		$passwd = isset ( $params ['_passwd'] ) ? ( string ) $params ['_passwd'] : '';
		// $pageSize = isset($params['_pageSize']) ? (string) $params['_pageSize'] : '';
		// $pageNum = isset($params['_pageNum']) ? (string) $params['_pageNum'] : '';
		
		$KUINK_TRACE[] = 'ldap query: '.$query;
		
		$this->connect ( $entity, $user, $passwd );
		
		$attributes = ($attrs != '') ? explode ( ',', $attrs ) : null;
		
		// $cookie = '';
		// ldap_control_paged_result($this->client, $pageSize, true, $pageNum);
		
		if ($attributes)
			$search = ldap_search ( $this->client, $entity, $query, $attributes );
		else
			$search = ldap_search ( $this->client, $entity, $query );
		
		if ($sort != '')
			ldap_sort ( $this->client, $search, $sort );
		
		$result = ldap_get_entries ( $this->client, $search );
		
		// $a = ldap_control_paged_result_response($this->client, $search, $cookie);
		
		$return = array ();
		foreach ( $result as $row ) {
			$returnRow = array ();
			foreach ( $row as $key => $value )
				if (($key != 'count') && ($key != 'objectclass') && ((is_array ( $value )) || ($key == 'dn')))
					$returnRow [$key] = is_array ( $value ) ? ( string ) $value [0] : ( string ) $value;
			if (! empty ( $returnRow ))
				$return [] = $returnRow;
		}
		// var_dump($return);
		
		return $return;
	}
	
	function getEntities($params) {
		global $KUINK_TRACE;
		
		$this->connect ();
		
		return null;
	}
	
	function getAttributes($params) {
		global $KUINK_TRACE;
		
		$type = isset ( $params [0] ) ? ( string ) $params [0] : '';
		$this->connect ();
		
		return null;
	}
	
	function load($params) {
		global $KUINK_TRACE;

		//Build or complete the query
		$queryParams = array();
		foreach($params as $key=>$value) {
			if ($key[0]!='_') {
				$queryParams[$key]=$value;
			}
		}
		if (count($queryParams) > 0) {
			$query .= '(&';
			foreach($queryParams as $key=>$value)
				$query .= '('.$key.'='.$value.')';
			
			$query .= ')';
			
			$params['_query'].=$query;
		}
		
		$resultData = $this->execute ( $params );
		
		//var_dump($resultData);
		
		return isset ( $resultData [0] ) ? $resultData [0] : null;
	}
	
	function getAll($params) {
		$resultData = $this->execute ( $params );
		
		return $resultData;
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
			$adpassword .= "{$password{$i}}\000";
		}
		return $adpassword;
	}
}

?>
