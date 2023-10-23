<?php

/*
 * This file is a sample file for a connector
 * 
 * To use it:
 *    Change the classname
 *    Place it in the parent directory
 * 
 *    Create a datasource for it in either:
 *      - framework.xml (file in the root of kuink-apps for a framework wide datasource)
 *      - fw_datasource (table for company global datasource. It will always be loaded for every request in that company)
 *      - application.xml (file with the application definition to be used in that application context)
 *      - node.xml (inline with the code on a node. For a specific usage of an application)
 * 
 *    Xml Definition to define a datasource using this connector
 * 
 * 			<DataSource name="googleAPIAdminSDK" connector="googleAPIAdminSDKConnector">	
 * 				<Param name="keyfile">credentials/googleAPI.json</Param>
 *				<Param name="domain">your.domain.com</Param>
 *				<Param name="application">ApplicationName</Param>
 *				<Param name="delegatedAdmin">admin@your.domain.com</Param>		
 *				<Param name="scopes">https://www.googleapis.com/auth/admin.directory.user,https://www.googleapis.com/auth/admin.directory.group,https://www.googleapis.com/auth/calendar,https://www.googleapis.com/auth/admin.reports.audit.readonly</Param>
 *			</DataSource>
 * 
 * 		Getting the Client ID (https://developers.google.com/identity/protocols/OAuth2ServiceAccount#creatinganaccount)
 * 		Available scopes list (https://developers.google.com/identity/protocols/googlescopes)
 * 
 * 		To configure access to google API
 * 			1 - Access Google Admin with your domain administrator (https://admin.google.com/)
 * 			2 - Security > API controls
 * 
 *    Dependencies: 
 * 			This connector depends on:
 * 				kuink-core/lib/tools/googleClientApi   (https://github.com/googleapis/google-api-php-client)
 */

namespace Kuink\Core\DataSourceConnector;

use Kuink\Core\Exception\NotImplementedException;
use Kuink\Core\Exception\ParameterNotFound;
use Kuink\UI\Formatter\Person;

/**
 * All differenciatied entities handled by this connector
 */
class GoogleAPIAdminSDKEntity {
	const USER = 'user';
	const GROUP = 'group';
	const CALENDAR_EVENT = 'calendar.event';
	const AUDIT_ACTIVITIES = 'audit.activities';
}



/**
 * GoogleAPIAdminSDKConnector is a generic connector for accessing Google API based on the project
 * google-api-php-client
 *
 * @author paulo.tavares
 */
class GoogleAPIAdminSDKConnector extends \Kuink\Core\DataSourceMultiEntityConnector 
{
	var $connector; 				//The object holding the connection to the service
	var $accessToken;				//The access token to call the api
	var $keyfile; 					//Datasource Param "keyfile", the path to the keyfile json containing connection properties
	var $applicationName; 	//Datasource Param "application", a name to identify the application
	var $delegatedAdmin;		//Datasource Param "delegatedAdmin", the administration user act in name of all users
	var $domain;						//Datasource Param "domain", the company dmain
	var $scopes; 						//Datasource Param "scopes", the scopes that this connection will use

	function __construct($dataSource) 
	{
		parent::__construct($dataSource);

		//Load datasource params
		$this->scopes = $this->dataSource->getParam('scopes', true);
		$this->applicationName = $this->dataSource->getParam('application', true);
		$this->delegatedAdmin = $this->dataSource->getParam('delegatedAdmin', true);
		$this->domain = $this->dataSource->getParam('domain', true);

		//Setup handler class names. The configEntityHandlers variable 
		$this->configEntityHandlers = [
			googleAPIAdminSDKEntity::USER => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKUserHandler",
			googleAPIAdminSDKEntity::GROUP => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKGroupHandler",
			googleAPIAdminSDKEntity::CALENDAR_EVENT => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKCalendarEventHandler",
			googleAPIAdminSDKEntity::AUDIT_ACTIVITIES => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKAuditActivitiesHandler"
		];
	}


	/**
	 * Instantiates all params in order to establish a connection
	 */
	function connect($entity = null) 
	{
		//Key file containing connection credentials
		$keyfilePath = $this->getKeyfilePath();

		//Create an array with the scopes
		$scopes = explode(',', $this->scopes);

		//Set generic google client configuration
		$this->connector = new \Google_Client();
		$this->connector->setApplicationName($this->applicationName);
		$this->connector->setAuthConfig($keyfilePath);
		$this->connector->setAccessType('offline');
		$this->connector->setScopes($scopes);
		$this->connector->useApplicationDefaultCredentials();

		//Setup specific entity connection properties
		if(isset($entity)) {
			$handler = $this->getEntityHandler($entity);
			$handler->connect();
		}
	}


	/**
	 * Returns the path to the file that holds access data
	 */
	private function getKeyfilePath() 
	{
		global $KUINK_CFG;

		//Get the keyfile (credentials) full path
		$this->keyfile = $this->dataSource->getParam('keyfile', true);
		if(!file_exists($KUINK_CFG->appRoot . '/apps/' . $this->keyfile))
			throw new \Exception(__CLASS__ . ': invalid key file ' . $this->keyfile);
		
		return ($KUINK_CFG->appRoot . '/apps/' . $this->keyfile);
	}
}



/**
 * Description of GoogleAPIAdminSDKConnectorCommon
 * Common methods for GoogleAPIAdminSDKConnector implementation.
 *
 * @author catarina.fernandes
 */
abstract class GoogleAPIAdminSDKConnectorCommon 
{
	/**
	 * Gets the object property
	 */
	protected function getObjectProperty($object, $pathString) 
	{
		//Split the string into an array by the '->'
		$delimiter = '->';
		$pathArray = explode($delimiter, $pathString);

		//Get the first and last of the array
		$first = array_shift($pathArray);
		$last = array_pop($pathArray);

		//If the array is now empty, we can access simply without a loop
		if(count($pathArray) == 0) {
			if($last === null)
				return $object->{$first};
			else
				return $object->{$first}->{$last};
		}

		//We need to go deeper
		//$tmp = $this->Foo
		$tmp = $object->{$first};

		foreach ($pathArray as $deeper) {
			//Re-assign $tmp to be the next level of the object
			// $tmp = $Foo->Bar --- then $tmp = $tmp->baz
			$tmp = $tmp->{$deeper};
		}

		//Now we are at the level we need to be and can access the property
		return $tmp->{$last};
	}


	/**
	 * Sets the object property
	 */
	protected function setObjectProperty($object, $pathString, $value) 
	{
		//Split the string into an array by the '->'
		$delimiter = '->';
		$pathArray = explode($delimiter, $pathString);

		//Get the first and last of the array
		$first = array_shift($pathArray);
		$last = array_pop($pathArray);

		//If the array is now empty, we can access simply without a loop
		if(count($pathArray) == 0) {
			if ($last === null)
				$object->{$first} = $value;
			else
				$object->{$first}->{$last} = $value;
		}
		else {
			throw new \Exception(__CLASS__.' Cannot get a deeper property ', $pathString);
		}

		return $object;
	}


	/**
	 * Converts an object to an array
	 */
	protected function objectToArrayTranslated($object)
	{
		$objectArr = is_object($object) ? get_object_vars($object) : $object;
		$arr = array();
		
		foreach($objectArr as $key => $val) {
			$val = (is_array($val) || is_object($val)) ? $this->objectToArrayTranslated($val) : $val;
			$translatedKey = isset($this->reversedTranslator[$key]) ?  $this->reversedTranslator[$key] : $key;
			$arr[$translatedKey] = $val;
		}

		return $arr;
	}


	/**
	 * Takes a USER or a GROUP and returns an array with the kuink fields translated
	 */
	protected function getFormattedObject($object, $attributes = null)
	{
		$result = array();
		
		if(isset($object)) {
			//If we have attributes to get, only get those. Else, get all of them
			if(!empty($attributes)) {
				$attributesArr = explode(',', $attributes);

				foreach($attributesArr as $attribute) {
					$attribute = trim($attribute);
					$translator = $this->translator[$attribute];
					$translatedKey = isset($translator) ? $translator['translate'] : $attribute;
					$translatedKey = ($translatedKey != '') ? $translatedKey : $attribute;
					
					$result[$attribute] = $this->getObjectProperty($object, $translatedKey);
				}
			}
			else {
				$result = $objectArr = $this->objectToArrayTranslated($object);
			}
		}

		return $result;
	}
}




/********************************************************************************************
 *  
 * Entity Handlers
 * 
 ********************************************************************************************/
/**
 * Class to handle all basic USER operations
 * 
 *  @author paulo.tavares | catarina.fernandes
 */
class googleAPIAdminSDKUserHandler extends GoogleAPIAdminSDKConnectorCommon
																	 implements \Kuink\Core\ConnectorEntityHandlerInterface
{
	var $connector; 							//The parent connector object to get all the context
	var $translator; 							//Array that will contain all mandatory fields for insert and update users
	var $reversedTranslator; 			//Calculated reversed translator

	public function __construct($connector) 
	{
		$this->connector = $connector;

		$this->translator[\Kuink\Core\PersonProperty::UID] = array('translate' => '', 'insertMandatory' => true, 'updateMandatory' => true);
		$this->translator[\Kuink\Core\PersonProperty::EMAIL] = array('translate' => 'primaryEmail', 'insertMandatory' => true, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonProperty::GIVEN_NAME] = array('translate' => 'name->givenName', 'insertMandatory' => true, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonProperty::SURNAME] = array('translate' => 'name->familyName', 'insertMandatory' => true, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonProperty::PASSWORD] = array('translate' => 'password', 'insertMandatory' => true, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonProperty::RECOVERY_EMAIL] = array('translate' => 'recoveryEmail', 'insertMandatory' => false, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonProperty::CHANGE_PASSWORD] = array('translate' => 'changePasswordAtNextLogin', 'insertMandatory' => false, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonProperty::_CREATION] = array('translate' => 'creationTime', 'insertMandatory' => false, 'updateMandatory' => false);
		$this->translator['suspend'] = array('translate' => 'suspended', 'insertMandatory' => false, 'updateMandatory' => false);

		$this->reversedTranslator = array();
		foreach($this->translator as $translatorKey => $translator) {
			if($translator['translate'] != '') {
				$newValue = $translator['translate'];
				$newValueArr = explode('->', $newValue);
				if(count($newValueArr) > 0)
					$newValue = array_pop($newValueArr);

				$this->reversedTranslator[$newValue] = $translatorKey;
			}
		}
	}


	/**
	 * Handler specific connection properties
	 */
	public function connect()	{
		//In this entity we must set the delegation admin user
		$this->connector->connector->setSubject($this->connector->delegatedAdmin);
	}


	/**
	 * Gets a USER
	 * 
	 * Example: Get the user "dummy.xpto" with the following attributes: id, uid, email, given name and surname
	 * 		<DataAccess method="load" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 * 			<Param name="_attributes">id,uid,email,given_name,surname</Param>
	 * 		</DataAccess>
	 */
	public function load($params, $operators)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);
		
		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonProperty::UID, true);
		$email = $uid.'@'.$this->connector->domain;
		$attributes = $params['_attributes']; //List of attributes to return

		try {
			\Kuink\Core\TraceManager::add(' Loading user: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$user = $dir->users->get($email);
			//var_dump($user);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR loading user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			$user = null;
		}

		//Get user data already translated and then return it
		$user = $this->getFormattedObject($user, $attributes);
		return $user;
	}


	/**
	 * Gets all USERS 
	 * 
	 * Example: Get the first 200 users
   * 		<DataAccess method="getAll" datasource="googleAPIAdminSDK">
   *			<Param name="_entity">user</Param>
   *  	</DataAccess>
	 * 
	 * The result array has:
	 * 	. total 			   ::  The total number of records is -1 because we cannot know the real value
	 * 	. nextPageToken  ::  The token to be used to get the next page of records
	 *	. records				 ::  An array with the user data
	 */
	public function getAll($params, $operators)
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$maxResults = isset($params['_pageSize']) ? (int) $params['_pageSize'] : 500;
		$nextPageToken = isset($params['_pageNum']) ? (int) $params['_pageNum'] : 0;
		$attributes = $params['_attributes'];
		
		unset($params); //Remove the standard params
		$params['domain'] = $this->connector->domain;
		$params['orderBy'] = \Kuink\Core\PersonProperty::EMAIL;
		$params['maxResults'] = $maxResults;
		$params['pageToken'] = $nextPageToken;
		
		//Make the call and format the result
		$users = $dir->users->listUsers($params);
		$resultUsers = array();
		foreach($users as $user) {
			$resultUsers[] = $this->getFormattedObject($user, $attributes);
		}

		//Return a paginated array
		$result = array();
		//If the total is -1, then we cannot know the total records and the next page token is define as an array element of this result	
		$result['total'] = -1;
		$result['nextPageToken'] = $users->nextPageToken;
		$result['records'] = $resultUsers;

		return $result;
	}


	/**
	 * Gets all USERS based on a filter (search)
	 * 
	 * Example 1: Get all users with a given name that starts with 'Paul*'
   * 		<DataAccess method="execute" datasource="googleAPIAdminSDK">
   *			<Param name="_entity">user</Param>
   *			<Param name="_method">search</Param>
	 *			<Param name="given_name" wildcard="right">Paul</Param>
	 *			<Param name="_pageSize">50</Param>
   *  	</DataAccess>
	 * 
	 * 
	 * Example 2: Get all users with the name 'Jorge'
   * 		<DataAccess method="execute" datasource="googleAPIAdminSDK">
   *			<Param name="_entity">user</Param>
   *			<Param name="_method">search</Param>
	 *			<Param name="name">Jorge</Param>
	 *			<Param name="_pageSize">50</Param>
   *  	</DataAccess>
	 * 
	 * The result array has:
	 * 	. total 			   ::  The total number of records is -1 because we cannot know the real value
	 * 	. nextPageToken  ::  The token to be used to get the next page of records
	 *	. records				 ::  An array with the user data
	 */
	public function search($params, $operators)	
	{
		$maxResults = isset($params['_pageSize']) ? (int) $params['_pageSize'] : 10;
		$nextPageToken = isset($params['_pageNum']) ? (int) $params['_pageNum'] : 0;
		
		//Remove the standard params
		unset($params['_entity']);
		unset($params['_pk']);
		unset($params['_pageNum']);
		unset($params['_pageSize']);
		
		//Retrieve the attributes to get and remove them from params to prevent query error
		$attributes = isset($params['_attributes']) ? $params['_attributes'] : null;
		unset($params['_attributes']);

		//Determine the query to filter
		$queryArr = array();
		foreach($params as $paramKey => $paramValue) {
			//Check for wildcards
			$pos = strpos($paramValue, '%');
			if($pos !== FALSE) {
				//There's a wildcard, so set the operator directly to : and ignore user operator
				//Replace the default wildcard char '%' by the wildcard of this connector which is '*'
				$paramValue = str_replace('%', '*', $paramValue);
				$stringEnclosure = '';
				$operator = ':';
			} 
			else {
				//Default the operator to =
				$stringEnclosure = "'";
				$operator = isset($operators[$paramKey]) ? $operators[$paramKey] : '=';
			}
			//Try to translate the key.
			$paramKeyTranslated = (isset($this->translator[$paramKey])) ? $this->translator[$paramKey]['translate'] : $paramKey;
			//The translated key can have a path like name->givenName. In this case we must get only the last value
			$paramKeyTranslatedComposite = explode('->', $paramKeyTranslated);
			if (count($paramKeyTranslatedComposite) > 0)
				$paramKeyTranslated = array_pop($paramKeyTranslatedComposite);

			$queryArr[] = $paramKeyTranslated . $operator . $stringEnclosure . $paramValue . $stringEnclosure;
		}
		$query = implode(' ', $queryArr);

		//Build the query
		$listParams = array();
		$listParams['domain'] = $this->connector->domain;
		$listParams['maxResults'] = $maxResults;
		if($nextPageToken != 0)
			$listParams['nextPageToken'] = $nextPageToken;
		if($query != '')
			$listParams['query'] = $query;

		//Make the call
		\Kuink\Core\TraceManager::add(__METHOD__." Getting users with the filter: ", \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
		\Kuink\Core\TraceManager::add($query, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
		$dir = new \Google_Service_Directory($this->connector->connector);
		$users = $dir->users->listUsers($listParams);

		$resultUsers = array();
		foreach($users as $user) {
			$resultUsers[] = $this->getFormattedUser($user, $attributes);
		}

		//Return a paginated array
		$result = array();
		//If the total is -1, then we cannot know the total records and the next page token is define as an array element of this result	
		$result['total'] = -1;
		$result['nextPageToken'] = $users->nextPageToken;
		$result['records'] = $resultUsers;

		return $result;
	}


	/**
	 * Inserts a USER
	 * 
	 * Example: Insert the user "dummy.xpto"
	 * 		<DataAccess method="insert" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 * 			<Param name="given_name">Dummy</Param>
	 *			<Param name="surname">Xpto</Param>
	 *			<Param name="recovery_email">_dummy@dummy.com</Param>
	 *			<Param name="change_password">0</Param>
	 *			<Param name="password">password1234</Param>
	 * 		</DataAccess>
	 */
	public function insert($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		//Override email parameter with 'uid@domain'
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonProperty::UID, true);
		$params[\Kuink\Core\PersonProperty::EMAIL] = $uid.'@'.$this->connector->domain;

		//Validate insert params
		$user = $this->getUserFromParams($params);

		try {
			\Kuink\Core\TraceManager::add(' Inserting user: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$result = $dir->users->insert($user);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR inserting user', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}

		return 0;
	}


	/**
	 * Updates a USER
	 * 
	 * Example: Updates the surname of user "dummy.xpto" to 'Test' 
	 * 		<DataAccess method="update" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 *			<Param name="surname">Test</Param>
	 * 		</DataAccess>
	 */
	public function update($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonProperty::UID, true);
		$email = $uid.'@'.$this->connector->domain;
		
		//Get the current data and, then, set in the $user object all entries defined in the params
		$user = $dir->users->get($email);
		$user = $this->getUserFromParams($params, $user);

		try {
			\Kuink\Core\TraceManager::add(' Updating user: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$result = $dir->users->update($email, $user);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}

		return 0;
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Saves a USER
	 */
	public function save($params)	
	{
		\Kuink\Core\TraceManager::add(__METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}


	/**
	 * Deletes a USER
	 * 
	 * Example: Delete the user "dummy.xpto" 
	 * 		<DataAccess method="delete" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 * 		</DataAccess>
	 */
	public function delete($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonProperty::UID, true);
		$email = $uid.'@'.$this->connector->domain;

		try {
			\Kuink\Core\TraceManager::add(' Deleting user: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$result = $dir->users->delete($email);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR deleting user', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}
		
		return 0;
	}


	/** AUXILIARY FUNCTIONS **/
	/**
	 * Completes a USER from params or create one if no user is supplied
	 */
	protected function getUserFromParams($params, $user = null)	
	{
		$type = 'update';
		
		//If no user info is supplied, then create a new one
		if($user === null) {
			$user = new \Google_Service_Directory_User();
			$userName = new \Google_Service_Directory_UserName();
			$user->name = $userName;
			$type = 'insert'; //After all this is an insert
		}

		//Try to get all the param values from the translator object
		foreach($this->translator as $translatorKey => $translatorObject) {
			$userKeyToSet = $translatorObject['translate'];
			$userKeyMandatory = $translatorObject[$type.'Mandatory'];
			if($userKeyMandatory && !(isset($params[$translatorKey]))) {
				throw new \Exception('Cannot build users from params. Mandatory user field (' . $translatorKey . ') not set.');
			}

			//If $userKeyToSet is empty then this param is only to validatem, don't set it 
			if(($userKeyToSet != '') && (isset($params[$translatorKey]))) {
				$this->setObjectProperty($user, $userKeyToSet, $params[$translatorKey]);
				unset($params[$translatorKey]);
			}
		}

		//Unset unnecessary params
		unset($params['_entity']);
		unset($params['_pk']);
		unset($params['uid']);

		//Check to see if there are some params left that cannot be translated and try to set them
		foreach($params as $paramKey => $paramValue) {
			$user->$paramKey = $paramValue;
		}

		//At this point the $user object should have all $params defined in its properties so return it
		return $user;
	}
}




/**
 * Class to handle all basic GROUP operations
 *
 * @author catarina.fernandes
 */
class googleAPIAdminSDKGroupHandler extends GoogleAPIAdminSDKConnectorCommon
																		implements \Kuink\Core\ConnectorEntityHandlerInterface
{
	var $connector; 							//The parent connector object to get all the context
	var $translator; 							//Array that will contain all mandatory fields for insert and update users
	var $reversedTranslator; 			//Calculated reversed translator

	public function __construct($connector)
	{
		$this->connector = $connector;

		$this->translator[\Kuink\Core\PersonGroupProperty::ID] = array('translate' => 'id', 'insertMandatory' => false, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonGroupProperty::UID] = array('translate' => '', 'insertMandatory' => true, 'updateMandatory' => true);
		$this->translator[\Kuink\Core\PersonGroupProperty::CODE] = array('translate' => 'name', 'insertMandatory' => true, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonGroupProperty::NAME] = array('translate' => 'description', 'insertMandatory' => true, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonGroupProperty::EMAIL] = array('translate' => 'email', 'insertMandatory' => false, 'updateMandatory' => false);
		$this->translator[\Kuink\Core\PersonGroupProperty::TOTAL_MEMBERS] = array('translate' => 'directMembersCount', 'insertMandatory' => false, 'updateMandatory' => false);
		$this->translator['admin_created'] = array('translate' => 'adminCreated', 'insertMandatory' => false, 'updateMandatory' => false);

		$this->reversedTranslator = array();
		foreach($this->translator as $translatorKey => $translator)
			if($translator['translate'] != '')
			{
				$newValue = $translator['translate'];
				$newValueArr = explode('->', $newValue);
				if(count($newValueArr) > 0)
					$newValue = array_pop($newValueArr);

				$this->reversedTranslator[$newValue] = $translatorKey;
			}
	}


	/**
	 * Handler specific connection properties
	 */
	public function connect()	
	{
		//In this entity we must set the delegation admin user
		$this->connector->connector->setSubject($this->connector->delegatedAdmin);
	}


	/**
	 * Gets a GROUP
	 * 
	 * Example: Get the group "_xpto" with the following attributes: id, email, code (name) and name (description)
	 * 		<DataAccess method="load" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">group</Param>
	 * 			<Param name="uid">_xpto</Param>
	 * 			<Param name="_attributes">id,email,code,name</Param>
	 * 		</DataAccess>
	 */
	function load($params, $operators = null)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$email = $uid.'@'.$this->connector->domain;
		$attributes = $params['_attributes'];

		try {
			\Kuink\Core\TraceManager::add(' Loading group: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);	
			$group = $dir->groups->get($email);
			//var_dump($group);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR loading group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			$group = null;
		}

		//Get group data already translated and then return it
		$group = $this->getFormattedObject($group, $attributes);
		return $group;
	}


	/**
	 * Gets all GROUPS 
	 * 
	 * Example: Get all groups with the following attributes: id, code (name) and name (description)
   * 		<DataAccess method="getAll" datasource="googleAPIAdminSDK">
   *			<Param name="_entity">group</Param>
	 *			<Param name="_attributes">id,code,name</Param>
   *  	</DataAccess>
	 * 
	 * The result array has:
	 * 	. total 			   ::  The total number of records is -1 because we cannot know the real value
	 * 	. nextPageToken  ::  The token to be used to get the next page of records
	 *	. records				 ::  An array with the user data
	 */
	public function getAll($params, $operators)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Remove the standard params
		$maxResults = isset($params['_pageSize']) ? (int) $params['_pageSize'] : 200;
		//$nextPageToken = isset($params['_pageNum']) ? (int) $params['_pageNum'] : 0;
		$attributes = $params['_attributes'];
		
		unset($params);
		$params['domain'] = $this->connector->domain;
		$params['orderBy'] = \Kuink\Core\PersonGroupProperty::EMAIL;
		$params['maxResults'] = $maxResults;
		//$params['pageToken'] = $nextPageToken;
		
		//Make the call and format the result
		$groups = $dir->groups->listGroups($params);
		$resultGroups = array();
		foreach($groups as $group) {
			$resultGroups[] = $this->getFormattedObject($group, $attributes);
		}

		//Return a paginated array
		$result = array();
		//If the total is -1, then we cannot know the total records and the next page token is define as an array element of this result	
		$result['total'] = -1;
		//$result['nextPageToken'] = $groups->nextPageToken;
		$result['records'] = $resultGroups;

		return $result;
	}


	/**
	 * Inserts a GROUP
	 * 
	 * Example: Insert the group "_xpto"
	 * 		<DataAccess method="insert" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">group</Param>
	 * 			<Param name="uid">_xpto</Param>
	 * 			<Param name="code">XPTO</Param>
	 *			<Param name="name">Xpto Group</Param>
	 *			<Param name="admin_created">1</Param>
	 * 		</DataAccess>
	 */
	public function insert($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		//Override email parameter with 'uid@domain'
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$params[\Kuink\Core\PersonGroupProperty::EMAIL] = $uid.'@'.$this->connector->domain;

		//Validate insert params
		$group = $this->getGroupFromParams($params);

		try {
			\Kuink\Core\TraceManager::add(' Inserting group: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$result = $dir->groups->insert($group);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR inserting group', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}

		return 0;
	}


	/**
	 * Updates a GROUP
	 * 
	 * Example: Update the name (description) of the group "_xpto"
	 * 		<DataAccess method="update" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">group</Param>
	 * 			<Param name="uid">_xpto</Param>
	 *			<Param name="name">Xpto Test Group</Param>
	 * 		</DataAccess>
	 */
	public function update($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$email = $uid.'@'.$this->connector->domain;
		
		//Get the current data and, then, set in the $group object all entries defined in the params
		$group = $dir->groups->get($email);
		$group = $this->getGroupFromParams($params, $group);

		try {
			\Kuink\Core\TraceManager::add(' Updating group: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$result = $dir->groups->update($email, $group);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR updating group', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}

		return 0;
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Saves a GROUP
	 */
	public function save($params)	{
		\Kuink\Core\TraceManager::add(__METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}


	/**
	 * Deletes a GROUP
	 * 
	 * Example: Delete the group "_xpto"
	 * 		<DataAccess method="delete" datasource="googleAPIAdminSDK">
	 * 			<Param name="_entity">group</Param>
	 * 			<Param name="uid">_xpto</Param>
	 * 		</DataAccess>
	 */
	public function delete($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$email = $uid.'@'.$this->connector->domain;

		try {
			\Kuink\Core\TraceManager::add(' Deleting group: '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
			$result = $dir->groups->delete($email);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR deleting group', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}
		
		return 0;
	}


  /**
   * Adds a USER to a GROUP
   * 
   * Example: Add user "dummy" to group "_xpto" as a member
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">addUser</Param>
   *    <Param name="uid">_xpto</Param>
   *    <Param name="uid_user">dummy</Param>
	 * 		<Param name="is_owner">0</Param>
   *   </DataAccess>
   */
	function addUser($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$groupKey = $uid.'@'.$this->connector->domain;
		$userEmail = $params['uid_user'].'@'.$this->connector->domain;
		if($params[\Kuink\Core\PersonGroupProperty::IS_OWNER] == 1)
			$userRole = 'OWNER';
		else
			$userRole = 'MEMBER';
		$memberParams[\Kuink\Core\PersonGroupProperty::EMAIL] = $userEmail;
		$memberParams['role'] = $userRole;
		$member = new \Google_Service_Directory_Member($memberParams);
		
		try {
			\Kuink\Core\TraceManager::add(' Adding user '.$params['uid_user'].' to group '.$params['uid'], \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);		
			$result = $dir->members->insert($groupKey, $member);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR adding user to group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}

		return 0;
	}


  /**
   * Removes a USER from a GROUP
   * 
   * Example: Remove user "dummy" from group "_xpto"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">removeUser</Param>
   *    <Param name="uid">_xpto</Param>
   *    <Param name="uid_user">dummy</Param>
   *   </DataAccess>
   */
	function removeUser($params)	
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$groupKey = $uid.'@'.$this->connector->domain;
		$memberKey = $params['uid_user'].'@'.$this->connector->domain;
		
		try {
			\Kuink\Core\TraceManager::add(' Removing user '.$params['uid_user'].' from group '.$params['uid'], \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);		
			$result = $dir->members->delete($groupKey, $memberKey);
			//var_dump($result);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR removing user from group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			return 1;
		}

		return 0;
	}


  /**
   * Lists all USERS of a GROUP
   * 
   * Example: List users of group "_xpto"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">listUsers</Param>
   *    <Param name="uid">_xpto</Param>
   *   </DataAccess>
   */
  function listUsers($params) 
	{
		$dir = new \Google_Service_Directory($this->connector->connector);

		//Set the necessary params
		$uid = (string) $this->connector->getParam($params, \Kuink\Core\PersonGroupProperty::UID, true);
		$groupKey = $uid.'@'.$this->connector->domain;
		$attributes = $params['_attributes'];
		
		try {
			\Kuink\Core\TraceManager::add(' Listing members of group '.$params['uid'], \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);		
			$members = $dir->members->listMembers($groupKey);
			//var_dump($members);
		}
		catch (\Exception $e) {
			\Kuink\Core\TraceManager::add(__METHOD__.' ERROR listing members of group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__);
			
			$members = null;
		}

		//Get members data already translated and return it
		$resultMembers = array();
		foreach($members as $member) {
			$resultMembers[] = $this->getFormattedObject($member, $attributes);
		}
		return $resultMembers;
	}


	/** AUXILIARY FUNCTIONS **/
	/**
	 * Completes a GROUP from params or create one if no group is supplied
	 */
	protected function getGroupFromParams($params, $group = null) 
	{
		$type = 'update';
		
		//If no group info is supplied, then create a new one
		if($group === null) {
			$group = new \Google_Service_Directory_Group();
			$type = 'insert'; //After all this is an insert
		}

		//Try to get all the param values from the translator object
		foreach($this->translator as $translatorKey => $translatorObject) {
			$groupKeyToSet = $translatorObject['translate'];
			$groupKeyMandatory = $translatorObject[$type.'Mandatory'];
			if($groupKeyMandatory && !(isset($params[$translatorKey])))
				throw new \Exception('Cannot build groups from params. Mandatory group field (' . $translatorKey . ') not set.');

			//If $groupKeyToSet is empty then this param is only to validatem, don't set it 
			if(($groupKeyToSet != '') && (isset($params[$translatorKey]))) {
				$this->setObjectProperty($group, $groupKeyToSet, $params[$translatorKey]);
				unset($params[$translatorKey]);
			}
		}

		//Unset unnecessary params
		unset($params['_entity']);
		unset($params['_pk']);
		unset($params['uid']);

		//Check to see if there are some params left that cannot be translated and try to set them
		foreach($params as $paramKey => $paramValue)
			$group->$paramKey = $paramValue;

		//At this point the $group object should have all $params defined in its properties so return it
		return $group;
	}
}




/**
 * Class to handle all basic CALENDAR EVENT operations
 * 
 * @author paulo.tavares
 */
class googleAPIAdminSDKCalendarEventHandler implements \Kuink\Core\ConnectorEntityHandlerInterface
{
	var $connector; //The parent connector object to get all the context

	public function __construct($connector)
	{
		$this->connector = $connector;
	}


	/**
	 * Handler specific connection properties
	 */
	public function connect()
	{
		//Nothing to do in this handler
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Loads a calendar event
	 */
	public function load($params, $operators)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}

	/**
	 * Gets all calendar events
	 */
	public function getAll($params, $operators)
	{
		$service = new \Google_Service_Calendar($this->connector->connector);
		$calendarId = 'primary';
		$optParams = array(
			'maxResults' => 10,
			'orderBy' => 'startTime',
			'singleEvents' => true,
			'timeMin' => date('c'),
		);
		$results = $service->events->listEvents($calendarId, $optParams);
		if ($results)
			$events = $results->getItems();
		return (array)$events;
	}


	/**
	 * Inserts a calendar event
	 */
	public function insert($params)
	{
		$entity = (string) $this->connector->getParam($params, '_entity', true);
		$calendarId = (string) $this->connector->getParam($params, 'calendarId', false, 'primary');
		$summary = (string) $this->connector->getParam($params, 'summary', true);
		$description = (string) $this->connector->getParam($params, 'description', true);
		$organizer = (string) $this->connector->getParam($params, 'organizer', true);
		$start = (string) $this->connector->getParam($params, 'start', true);
		$end = (string) $this->connector->getParam($params, 'end', true);
		$attendees = $this->connector->getParam($params, 'attendees', true);
		$conference = (string) $this->connector->getParam($params, 'conference', false, 'false');
		$visibility = (string) $this->connector->getParam($params, 'visibility', false, 'default');

		//Impersonate the organizer so the event is set in it's calendar
		//Check if the organizer has the domain or append it
		if (strpos($organizer, '@') === false)
			$organizer .= '@' . $this->connector->domain;
		$this->connector->connector->setSubject($organizer);

		$service = new \Google_Service_Calendar($this->connector->connector);

		$attendeesArray = array();
		//foreach ( $organizers as $organizer)
		//	$attendeesArray[] = array('email' => $organizer, 'organizer' => true);
		foreach ($attendees as $attendee) {
			if (strpos($attendee, '@') === false)
				$attendee .= '@' . $this->connector->domain;
			$attendeesArray[] = array('email' => $attendee);
		}

		$event = new \Google_Service_Calendar_Event(array(
			'anyoneCanAddSelf' => false,
			'guestsCanInviteOthers' => false,
			'guestsCanModify' => false,
			'summary' => $summary,
			'description' => $description,
			'start' => array('dateTime' => $start),
			'end' => array('dateTime' => $end),
			'visibility' => $visibility,
			'attendees' => $attendeesArray
		));

		$organizerData = new \Google_Service_Calendar_EventOrganizer();
		$organizerData->setEmail($organizer);
		$event->setOrganizer($organizerData);
		$event->setICalUID(uniqid());
		\Kuink\Core\TraceManager::add('Google API: ' . $entity . ' | ' . __METHOD__, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
		\Kuink\Core\TraceManager::add(json_encode($event), \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);

		$event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

		if ($conference != 'false') {
			$conferenceData = new \Google_Service_Calendar_ConferenceData();
			$conferenceRequest = new \Google_Service_Calendar_CreateConferenceRequest();
			$conferenceRequestSolutionKey = new \Google_Service_Calendar_ConferenceSolutionKey();
			$conferenceRequestSolutionKey->setType($conference);
			$conferenceRequest->setRequestId(uniqid());
			$conferenceRequest->setConferenceSolutionKey($conferenceRequestSolutionKey);
			$conferenceData->setCreateRequest($conferenceRequest);
			$event->setConferenceData($conferenceData);
			$event = $service->events->patch($calendarId, $event->id, $event, ['conferenceDataVersion' => 1]);
		}
		$eventArr = $this->connector->object_to_array($event);

		return $eventArr;
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Updates a calendar event
	 */
	public function update($params)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Saves a calendar event
	 */
	public function save($params)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}

	/**
	 * Deletes a calendar event
	 */
	public function delete($params)
	{
		$calendarId = (string) $this->connector->getParam($params, 'calendarId', true);;
		$id = (string) $this->connector->getParam($params, 'id', true);
		$organizer = (string) $this->connector->getParam($params, 'organizer', true);

		if (strpos($organizer, '@') === false) {
			$organizer .= '@' . $this->connector->domain;
		}
		$this->connector->connector->setSubject($organizer);

		$service = new \Google_Service_Calendar($this->connector->connector);
		$result = $service->events->delete($calendarId, $id);
	}
}




/**
 * Class to handle all basic AUDIT ACTIVITIES operations
 * 
 * @author paulo.tavares
 */
class googleAPIAdminSDKAuditActivitiesHandler implements \Kuink\Core\ConnectorEntityHandlerInterface
{
	var $connector; //The parent connector object to get all the context

	public function __construct($connector)
	{
		$this->connector = $connector;
	}


	/**
	 * Handler specific connection properties
	 */
	public function connect()
	{
		//In this entity we must set the delegation admin user
		$this->connector->connector->setSubject($this->connector->delegatedAdmin);
	}


	/**
	 * Loads an audit event
	 */
	public function load($params, $operators)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}


	/**
	 * Gets all audit events
	 */
	public function getAll($params, $operators)
	{
		$service = new \Google_Service_Reports($this->connector->connector);

		$userKey = $params['userKey'];
		$applicationName = $params['applicationName'];
		$filters = $params['filters'];
		$optParams = array(
			'filters' => $filters,
		);
		$results = $service->activities->listActivities($userKey, $applicationName, $optParams);
		$items = $this->connector->object_to_array($results->getItems());

		return ($items);
	}


	/**
	 * NOT IMPLEMENTED 
	 * 
	 * Inserts an audit event
	 */
	public function insert($params)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}

	/**
	 * NOT IMPLEMENTED
	 * 
	 * Updates an audit event
	 */
	public function update($params)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Saves an audit event
	 */
	public function save($params)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}


	/**
	 * NOT IMPLEMENTED
	 * 
	 * Deletes an audit event
	 */
	public function delete($params)
	{
		\Kuink\Core\TraceManager::add(__METHOD__ . ' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__);
	}
}
