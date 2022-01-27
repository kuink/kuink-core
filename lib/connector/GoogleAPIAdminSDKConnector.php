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
 *				<Param name="scopes">https://www.googleapis.com/auth/admin.directory.user,https://www.googleapis.com/auth/calendar,https://www.googleapis.com/auth/admin.reports.audit.readonly</Param>
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

/**
 * All differenciatied entities handled by this connector
 */
class GoogleAPIAdminSDKEntity {
	const USER = 'user';
	const CALENDAR_EVENT = 'calendar.event';
	const AUDIT_ACTIVITIES = 'audit.activities';
}

/**
 * googleAPIAdminSDKConnector is a generic connector for accessing google api based on the project
 * google-api-php-client
 *
 * @author paulo.tavares
 */
class GoogleAPIAdminSDKConnector extends \Kuink\Core\DataSourceMultiEntityConnector{
	var $connector; //The object holding the connection to the service
	var $accessToken;						//The access token to call the api
	var $keyfile; 							//Datasource Param "keyfile", the path to the keyfile json containing connection properties
	var $applicationName; 			//Datasource Param "application", a name to identify the application
	var $delegatedAdmin;				//Datasource Param "delegatedAdmin", the administration user act in name of all users
	var $domain;								//Datasource Param "domain", the company dmain
	var $scopes; 								//Datasource Param "scopes", the scopes that this connection will use
	
	function __construct($dataSource) {
		parent::__construct($dataSource);

		//Load datasource params
		$this->scopes = $this->dataSource->getParam ( 'scopes', true );
		$this->applicationName = $this->dataSource->getParam ( 'application', true );
		$this->delegatedAdmin = $this->dataSource->getParam ( 'delegatedAdmin', true );
		$this->domain = $this->dataSource->getParam ( 'domain', true );

		//Setup handler class names. The configEntityHandlers variable 
		$this->configEntityHandlers = [
			googleAPIAdminSDKEntity::USER => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKUserHandler",
			googleAPIAdminSDKEntity::CALENDAR_EVENT => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKCalendarEventHandler",
			googleAPIAdminSDKEntity::AUDIT_ACTIVITIES => "\Kuink\Core\DataSourceConnector\googleAPIAdminSDKAuditActivitiesHandler"
		];
	}

	/**
	 * Instantiate all params in order to establish a connection
	 */
	function connect($entity=null) {
		//Key file containing connection credentials
		$keyfilePath = $this->getKeyfilePath(); 

		//Create an array with the scopes
		$scopes = explode(',', $this->scopes);

		//Set generic google client configuration
		$this->connector = new \Google_Client();		
		$this->connector->setApplicationName($this->applicationName);
		$this->connector->setAuthConfig($keyfilePath);
		$this->connector->setAccessType('offline');
		$this->connector->setScopes($scopes );
		$this->connector->useApplicationDefaultCredentials();

		//Setup specific entity connection properties
		if (isset($entity)) {
			$handler = $this->getEntityHandler($entity);
			$handler->connect();
		}
	}

	/**
	 * Returns the path to the file that holds access data
	 */
	private function getKeyfilePath( ) {
		global $KUINK_CFG;		
		//Get the keyfile (credentials) full path
		$this->keyfile = $this->dataSource->getParam ( 'keyfile', true );
		if (! file_exists ( $KUINK_CFG->appRoot . '/apps/' . $this->keyfile ))
			throw new \Exception ( __CLASS__ . ': invalid key file ' . $this->keyfile );
		return ($KUINK_CFG->appRoot . '/apps/' . $this->keyfile);
	}
}

/******************************************************************************
 *  Entity Handlers
 ******************************************************************************/
/**
 * Class to handle all basic user operations
 */
class googleAPIAdminSDKUserHandler implements \Kuink\Core\ConnectorEntityHandlerInterface {
	var $connector; //The parent connector object to get all the context
	var $translator; //Array that will contain all mandatory fields for insert and update users

	public function __construct($connector) {
		$this->connector = $connector;

		$this->translator[\Kuink\Core\PersonProperty::UID] = array('translate'=>'', 'insertMandatory'=>true, 'updateMandatory'=>true);		;
		$this->translator[\Kuink\Core\PersonProperty::EMAIL] = array('translate'=>'primaryEmail', 'insertMandatory'=>true, 'updateMandatory'=>false);		
		$this->translator[\Kuink\Core\PersonProperty::GIVEN_NAME] = array('translate'=>'name->givenName', 'insertMandatory'=>true, 'updateMandatory'=>false);
		$this->translator[\Kuink\Core\PersonProperty::SURNAME] = array('translate'=>'name->familyName', 'insertMandatory'=>true, 'updateMandatory'=>false);
		$this->translator[\Kuink\Core\PersonProperty::PASSWORD] = array('translate'=>'password', 'insertMandatory'=>true, 'updateMandatory'=>false);
		$this->translator[\Kuink\Core\PersonProperty::RECOVERY_EMAIL] = array('translate'=>'recoveryEmail', 'insertMandatory'=>false, 'updateMandatory'=>false);
		$this->translator[\Kuink\Core\PersonProperty::CHANGE_PASSWORD] = array('translate'=>'changePasswordAtNextLogin', 'insertMandatory'=>false, 'updateMandatory'=>false);
	}

	/**
	 * Handler specific connection properties
	 */
	public function connect() {
		//In this entity we must set the delegation admin user
		$this->connector->connector->setSubject($this->connector->delegatedAdmin);		
	}

	/**
	 * Load a user
	 */
	public function load($params, $operators) {
		$uid = isset ( $params ['uid'] ) ? ( string ) $params ['uid'] : '';
		$dir = new \Google_Service_Directory ( $this->connector->connector );
		
		try {
			\Kuink\Core\TraceManager::add ( 'Google Query: '.$uid . '@' . $this->connector->domain, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
			$account = $dir->users->get ( $uid . '@' . $this->connector->domain );
		} catch ( \Exception $e ) {
			$entity = (string) $this->getParam ( $params, '_entity', true );
			\Kuink\Core\TraceManager::add ( 'ERROR GOOGLE on entity '.$entity, \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			$account = null;
		}
		$user = array ();
		if ($account !== null) {
			$user [\Kuink\Core\PersonProperty::UID] = ( string ) $account->primaryEmail;
			$user [\Kuink\Core\PersonProperty::EMAIL] = ( string ) $account->primaryEmail;
			$user [\Kuink\Core\PersonProperty::GIVEN_NAME] = ( string ) $account->getName ()->givenName;
			$user [\Kuink\Core\PersonProperty::SURNAME] = ( string ) $account->getName ()->familyName;
			$user [\Kuink\Core\PersonProperty::ID] = ( string ) $account->id;
		}
		return $user;
	}

	/**
	 * Get all users
	 */
	public function getAll($params, $operators) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Insert a user
	 */
	public function insert($params) {
		$uid = ( string ) $this->connector->getParam ( $params, \Kuink\Core\PersonProperty::UID, true );
		$dir = new \Google_Service_Directory ( $this->connector->connector );
		//Override email parameter with this uid@domain
		$params[\Kuink\Core\PersonProperty::EMAIL] = $uid.'@'.$this->connector->domain;

		//Validate insert params
		$user = $this->getUserFromParams($params);
		
		try {
			$result = $dir->users->insert ( $user );
		} catch ( \Exception $e ) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR inserting user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			\Kuink\Core\TraceManager::add ($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
		}
		return 0;
	}

	/**
	 * Update a user
	 */
	public function update($params) {
		$uid = ( string ) $this->connector->getParam ( $params, \Kuink\Core\PersonProperty::UID, true );
		$email = $uid.'@'.$this->connector->domain;

		$dir = new \Google_Service_Directory ( $this->connector->connector );
		//Get the current data
		$user = $dir->users->get ( $email );

		//Take the current user and set in the $user object all entries defined in the params
		$user = $this->getUserFromParams($params, $user);

		try {
			$result = $dir->users->update ( $email, $user );
		} catch ( \Exception $e ) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
		}
		
		return 0;
	}

	/**
	 * Save a user
	 */
	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Delete a user
	 */
	public function delete($params) {
		$uid = ( string ) $this->connector->getParam ( $params, \Kuink\Core\PersonProperty::UID, true );
		$email = $uid.'@'.$this->connector->domain;

		$dir = new \Google_Service_Directory ( $this->connector->connector );
		$user = new \Google_Service_Directory_User ();

		try {
			\Kuink\Core\TraceManager::add ( 'Deleting user: '.$email, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
			$result = $dir->users->delete( $email );
		} catch ( \Exception $e ) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR deleting user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			\Kuink\Core\TraceManager::add ($e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
		}
		return 0;		
	}

	/**
	 * Completes a user from params or create one if no user is supplyed
	 * @type - insert | update
	 */
	protected function getUserFromParams($params, $user = null) {
		$type = 'update';
		//If no user info is supplied, then create a new one
		if ($user === null) {
			$user = new \Google_Service_Directory_User ();
			$userName = new \Google_Service_Directory_UserName ();
			$user->name = $userName;
			$type = 'insert'; //After all this is an insert
		}

		//Try to get all the param values from the translator object
		foreach ($this->translator as $translatorKey=>$translatorObject) {
			$userKeyToSet = $translatorObject['translate'];
			$userKeyMandatory = $translatorObject[$type.'Mandatory'];
			if ($userKeyMandatory && !(isset($params[$translatorKey])))
				throw new \Exception('Cannot build users from params. Mandatory user field ('.$translatorKey.') not set.');
			
			//If $userKeyToSet is empty then this param is only to validatem, don't set it 
			if (($userKeyToSet != '') && (isset($params[$translatorKey]))) {
				$this->setObjectProperty($user, $userKeyToSet, $params[$translatorKey]); 				
			}				
		}

		//At this point the $user object should have all $params defined in its properties so return it
		return $user;
	}

	protected function getObjectProperty($object, $pathString) {
		$delimiter = '->';
		//split the string into an array
		$pathArray = explode($delimiter, $pathString);
	
		//get the first and last of the array
		$first = array_shift($pathArray);
		$last = array_pop($pathArray);
	
		//if the array is now empty, we can access simply without a loop
		if(count($pathArray) == 0){
			if ($last === null)
				return $object->{$first};
			else
				return $object->{$first}->{$last};
		}
	
		//we need to go deeper
		//$tmp = $this->Foo
		$tmp = $object->{$first};
	
		foreach($pathArray as $deeper) {
			//re-assign $tmp to be the next level of the object
			// $tmp = $Foo->Bar --- then $tmp = $tmp->baz
			$tmp = $tmp->{$deeper};
		}
	
		//now we are at the level we need to be and can access the property
		return $tmp->{$last};
	}

	protected function setObjectProperty(&$object, $pathString, $value) {
		$delimiter = '->';
		//split the string into an array
		$pathArray = explode($delimiter, $pathString);
	
		//get the first and last of the array
		$first = array_shift($pathArray);
		$last = array_pop($pathArray);
	
		//if the array is now empty, we can access simply without a loop
		if(count($pathArray) == 0){
			if ($last === null)
				$object->{$first} = $value;
			else
				$object->{$first}->{$last} = $value;
		} else {
			throw new \Exception(__CLASS__.' Cannot get a deeper property ',$pathString);
		}
	
		return $object;
	}	

}

/**
 * Class to handle all basic calendar event operations
 */
class googleAPIAdminSDKCalendarEventHandler implements \Kuink\Core\ConnectorEntityHandlerInterface {
	var $connector; //The parent connector object to get all the context

	public function __construct($connector) {
		$this->connector = $connector;
	}

	/**
	 * Handler specific connection properties
	 */
	public function connect() {
		//Nothing to do in this handler
	}

	/**
	 * Load a calendar event
	 */
	public function load($params, $operators) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Get all calendar events
	 */
	public function getAll($params, $operators) {
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
	 * Insert a calendar event
	 */
	public function insert($params) {
			$entity = (string) $this->connector->getParam ( $params, '_entity', true );
			$calendarId = (string) $this->connector->getParam ( $params, 'calendarId', false, 'primary');
			$summary = (string) $this->connector->getParam ( $params, 'summary', true);
			$description = (string) $this->connector->getParam ( $params, 'description', true);
			$organizer = (string) $this->connector->getParam ( $params, 'organizer', true);
			$start = (string) $this->connector->getParam ( $params, 'start', true);
			$end = (string) $this->connector->getParam ( $params, 'end', true);
			$attendees = $this->connector->getParam ( $params, 'attendees', true);
			$conference = (string) $this->connector->getParam ( $params, 'conference', false, 'false');			
			$visibility = (string) $this->connector->getParam ( $params, 'visibility', false, 'default');			

			//Impersonate the organizer so the event is set in it's calendar
				//Check if the organizer has the domain or append it
			if (strpos($organizer, '@') === false)
				$organizer .= '@'.$this->connector->domain;
			$this->connector->connector->setSubject($organizer);			
			
			$service = new \Google_Service_Calendar($this->connector->connector);

			$attendeesArray = array();
			//foreach ( $organizers as $organizer)
			//	$attendeesArray[] = array('email' => $organizer, 'organizer' => true);
			foreach ( $attendees as $attendee) {
				if (strpos($attendee, '@') === false)
					$attendee .= '@'.$this->connector->domain;
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
			\Kuink\Core\TraceManager::add ( 'Google API: '.$entity.' | '.__METHOD__, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( json_encode($event), \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
				  
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
	 * Update a calendar event
	 */
	public function update($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Save a calendar event
	 */
	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Delete a calendar event
	 */
	public function delete($params) {
		$calendarId = (string) $this->connector->getParam ( $params, 'calendarId', true );;
		$id = (string) $this->connector->getParam ( $params, 'id', true );
		$organizer = (string) $this->connector->getParam ( $params, 'organizer', true );

		if (strpos($organizer, '@') === false)
			$organizer .= '@'.$this->connector->domain;
		$this->connector->connector->setSubject($organizer);			
		
		$service = new \Google_Service_Calendar($this->connector->connector);
		$result = $service->events->delete($calendarId, $id);
	}
}

/**
 * Class to handle all basic audit activities operations
 */
class googleAPIAdminSDKAuditActivitiesHandler implements \Kuink\Core\ConnectorEntityHandlerInterface {
	var $connector; //The parent connector object to get all the context

	public function __construct($connector) {
		$this->connector = $connector;
	}

	/**
	 * Handler specific connection properties
	 */
	public function connect() {
		//In this entity we must set the delegation admin user
		$this->connector->connector->setSubject($this->connector->delegatedAdmin);		
	}

	/**
	 * Load a audit event
	 */
	public function load($params, $operators) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Get all audit events
	 */
	public function getAll($params, $operators) {
		$service = new \Google_Service_Reports( $this->connector->connector );

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
	 * Insert a audit event
	 */
	public function insert($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  			
	}

	/**
	 * Update a audit event
	 */
	public function update($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Save a audit event
	 */
	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

	/**
	 * Delete a audit event
	 */
	public function delete($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}

}