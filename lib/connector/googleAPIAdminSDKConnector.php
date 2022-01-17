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


interface EntityHandlerInterface {
	public function connect($connector);
	public function load($params, $operators);
	public function insert($params);
	public function update($params);
	public function delete($params);
	public function getAll($params, $operators);
}


/**
 * googleAPIAdminSDKConnector is a generic connector for accessing google api based on the project
 * google-api-php-client
 *
 * @author paulo.tavares
 */
class googleAPIAdminSDKConnector extends \Kuink\Core\DataSourceConnector{
	var $connector; //The object holding the connection to the service
	var $accessToken;
	var $clientID;
	var $serviceAccountName;
	var $keyfile;
	var $applicationName;
	var $delegatedAdmin;
	var $domain;
	var $scopes; //The scopes that this connection will use
	var $configEntityHandlers; //Config all the entity handlers
	
	function __construct() {
		$this->configEntityHandlers = [
			"user" => "googleAPIAdminSDKUserHandler",
			"calendar.event" => "googleAPIAdminSDKCalendarEventHandler",
			"audit.activities" => "googleAPIAdminSDKAuditActivitiesHandler"
		];
	}


	function connect($entity=null) {
		$this->connector = isset ( $this->connector ) ? $this->connector : '';
		if (! $this->connector) {	
			$this->scopes = $this->dataSource->getParam ( 'scopes', true );
			$scopes = explode(',', $this->scopes);
			//print_object($scopes);
			
			$keyfilePath = $this->getKeyfilePath(); //Key file containing credentials
			$this->delegatedAdmin = $this->dataSource->getParam ( 'delegatedAdmin', true );
			$this->domain = $this->dataSource->getParam ( 'domain', true );

			$this->connector = new \Google_Client();		

			//Set generic google client configuration
			$this->connector->setApplicationName('GeCol');
			$this->connector->setAuthConfig($keyfilePath);
			$this->connector->setAccessType('offline');
			$this->connector->setScopes($scopes );
			$this->connector->useApplicationDefaultCredentials();
			if ($entity != 'calendar.event')
				$this->connector->setSubject($this->delegatedAdmin);
		}
	}

	/*
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
	*/

	function load($params) {
		global $KUINK_TRACE;
		$this->connect ();
		
		$uid = isset ( $params ['uid'] ) ? ( string ) $params ['uid'] : '';
		$dir = new \Google_Service_Directory ( $this->connector );
		
		try {
			$KUINK_TRACE [] = 'Google Query: '.$uid . '@' . $this->domain;
			$account = $dir->users->get ( $uid . '@' . $this->domain );
		} catch ( \Exception $e ) {
			$entity = (string) $this->getParam ( $params, '_entity', true );
			//print_object($e->getMessage());
			$KUINK_TRACE [] = 'ERROR GOOGLE'; // print_object($e->getMessage());
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

	/*
	function load($params, $operators=null) {
		//From what entity do we want to load the record
		$entity = (string) $this->getParam ( $params, '_entity', true );

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the melthod from the handler
		$result = $handler->load($params, $operators);

		//Send the result back to the caller
		return $result;
	}
	*/

	function insert($params) {
		global $KUINK_TRACE;
		$entity = (string) $this->getParam ( $params, '_entity', true );
		$this->connect($entity);

		if ($entity == 'calendar.event') {
			$calendarId = (string) $this->getParam ( $params, 'calendarId', false, 'primary');
			$summary = (string) $this->getParam ( $params, 'summary', true);
			$description = (string) $this->getParam ( $params, 'description', true);
			$organizer = (string) $this->getParam ( $params, 'organizer', true);
			$start = (string) $this->getParam ( $params, 'start', true);
			$end = (string) $this->getParam ( $params, 'end', true);
			$attendees = $this->getParam ( $params, 'attendees', true);
			$conference = (string) $this->getParam ( $params, 'conference', false, 'false');			
			$visibility = (string) $this->getParam ( $params, 'visibility', false, 'default');			

			//Impersonate the organizer so the event is set in it's calendar
				//Check if the organizer has the domain or append it
			if (strpos($organizer, '@') === false)
				$organizer .= '@'.$this->domain;
			$this->connector->setSubject($organizer);			
			
			$service = new \Google_Service_Calendar($this->connector);

			$attendeesArray = array();
			//foreach ( $organizers as $organizer)
			//	$attendeesArray[] = array('email' => $organizer, 'organizer' => true);
			foreach ( $attendees as $attendee) {
				if (strpos($attendee, '@') === false)
					$attendee .= '@'.$this->domain;
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
			$KUINK_TRACE[] = 'Google API: '.$entity.' | insert';
			$KUINK_TRACE[] = json_encode($event);
				  
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
			//kuink_mydebugobj('Event', $event);
			$eventArr = $this->object_to_array($event);
			//kuink_mydebugobj('Event', $eventArr);

			return $eventArr;
		} else {		
			$entity = ( string ) $this->getParam ( $params, '_entity', true ); // isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
			$id = ( string ) $this->getParam ( $params, 'id', false ); // isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
																// $type = (string)$this->getParam($params, 'type', true);//isset($params['_entity']) ? (string)$params['_entity']: 'user' ;
			$givenName = ( string ) $this->getParam ( $params, 'given_name', true );
			$surname = ( string ) $this->getParam ( $params, 'surname', true );
			$password = ( string ) $this->getParam ( $params, 'password', true );
			$email = ( string ) $this->getParam ( $params, 'email', true );
			$recoveryEmail = ( string ) $this->getParam ( $params, 'recoveryEmail', false, '');
			$changePasswordAtNextLogin = ( string ) $this->getParam ( $params, 'changePasswordAtNextLogin', false, 'false');
			
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
				if ($recoveryEmail != '') {
					$user->recoveryEmail = $recoveryEmail;
				}
				if ($changePasswordAtNextLogin == 'true') {
					$user->changePasswordAtNextLogin = true;
				}
				//var_dump($user);
				try {
					$result = $dir->users->insert ( $user );
					//var_dump($result);
				} catch ( \Exception $e ) {
					// var_dump($e);
					return 0;
				}
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

		if ($entity == 'calendar.event') {
			$calendarId = (string) $this->getParam ( $params, 'calendarId', true );;
			$id = (string) $this->getParam ( $params, 'id', true );
			$organizer = (string) $this->getParam ( $params, 'organizer', true );

			if (strpos($organizer, '@') === false)
				$organizer .= '@'.$this->domain;
			$this->connector->setSubject($organizer);			
			
			$service = new \Google_Service_Calendar($this->connector);
			$result = $service->events->delete($calendarId, $id);
		}

		if ($entity == 'user') {
			$uid = ( string ) $this->getParam ( $params, 'uid', true );
		
			$dir = new \Google_Service_Directory ( $this->connector );
	
			$user = new \Google_Service_Directory_User ();
			$userName = new \Google_Service_Directory_UserName ();
			
			//$userName->familyName = $surname;
			//$userName->givenName = $givenName;
			// print_object($userName);
			$user->name = $userName;
			//$user->password = $password;
			//$user->primaryEmail = $email;
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
		$entity = ( string ) $this->getParam ( $params, '_entity', true ); 		
		$this->connect ($entity);

		/*
		$this->connector->setApprovalPrompt('force');
		if ($this->connector->isAccessTokenExpired())
			if ($this->connector->getRefreshToken())
				$this->connector->fetchAccessTokenWithRefreshToken($this->connector->getRefreshToken());*/

		if ($entity == 'calendar.event') 	{
			$service = new \Google_Service_Calendar($this->connector);
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
		} else if ($entity == 'user') {
			$dir = new \Google_Service_Directory ( $this->connector );
		} else if ($entity == 'audit.activities') {
			$service = new \Google_Service_Reports( $this->connector );

			$userKey = $params['userKey'];
			$applicationName = $params['applicationName'];
			$filters = $params['filters'];
			$optParams = array(
				'filters' => $filters,
			);
			$results = $service->activities->listActivities($userKey, $applicationName, $optParams);
			$items = $this->object_to_array($results->getItems());			

			return ($items);
		}
	}

	public function getSchemaName($params) {
  		return null;
  	}

	// convert object to array
  	protected function object_to_array($obj) {
	$arrObj = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
	$arr=array();
    foreach ( $arrObj as $key => $val ) {
      $val = (is_array ( $val ) || is_object ( $val )) ? $this->object_to_array ( $val ) : $val;
      $arr [$key] = $val;
    }
    return $arr;
  }

	/**
	 * Get the entity Service Object
	 */
	private function getEntityHandler($entity) {
		if (!isset($this->configEntityHandlers[$entity]))
			throw new \Exception(__CLASS__.': Invalid entity handler for entity:'. $entity);

		$handler = $this->configEntityHandlers[$entity];
		
		return new $handler($this, );
	}


	private function getKeyfilePath( ) {
		global $KUINK_CFG;		
		//Get the keyfile (credentials) full path
		$this->keyfile = $this->dataSource->getParam ( 'keyfile', true );
		if (! file_exists ( $KUINK_CFG->appRoot . '/apps/' . $this->keyfile ))
			throw new \Exception ( __CLASS__ . ': invalid key file ' . $this->keyfile );
		return ($KUINK_CFG->appRoot . '/apps/' . $this->keyfile);
	}

}

?>
