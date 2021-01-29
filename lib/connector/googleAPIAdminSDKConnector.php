<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kuink\Core\DataSourceConnector;

use Kuink\Core\Exception\NotImplementedException;
use Kuink\Core\Exception\ParameterNotFound;
/**
 * Description of googleAPIAdminSDKConnector
 *
 * @author paulo.tavares
 */
class googleAPIAdminSDKConnector extends \Kuink\Core\DataSourceConnector{
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

	
	function connect($entity=null) {
		//$this->service = $this->dataSource->getParam ('service', true );
		$this->connector = isset ( $this->connector ) ? $this->connector : '';
		if (! $this->connector) {	
			//Set common stuff to all connectors
			//$this->connector->setAccessToken($accessToken);
			//$accessToken = json_decode(file_get_contents($keyfilePath), true);
			//var_dump($accessToken);
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
	
			//Set service specific configuration
			/*
			switch ($this->service) {
				case 'directory':
					$this->connectDirectoryService();
					break;
				case 'calendar':
					$this->connectCalendarService();
					break;
			}*/
		}
	}

	function connectDirectoryService() {
		//Directory Service specific configuration data
		$this->delegatedAdmin = $this->dataSource->getParam ( 'delegatedAdmin', true );
		$this->domain = $this->dataSource->getParam ( 'domain', true );
		$this->connector->useApplicationDefaultCredentials();
		$this->connector->setSubject($this->delegatedAdmin);
	}

	function connectCalendarService() {
		//Directory Service specific configuration data
		$this->delegatedAdmin = $this->dataSource->getParam ( 'delegatedAdmin', true );
		//$this->connector->setPrompt('select_account consent');
		//$this->delegatedAdmin = $this->dataSource->getParam ( 'delegatedAdmin', true );
		$this->connector->useApplicationDefaultCredentials();
		//$this->connector->setSubject($this->delegatedAdmin);	
		//$this->connector->setPrompt('select_account consent');
		//$this->connector->setSubject($this->delegatedAdmin);
		//$this->connector->setSubject('gecol-84@api-project-376783883374.in.cscm-lx.pt.iam.gserviceaccount.com');	
	}

	function getKeyfilePath( ) {
		global $KUINK_CFG;		
		//Get the keyfile (credentials) full path
		$this->keyfile = $this->dataSource->getParam ( 'keyfile', true );
		if (! file_exists ( $KUINK_CFG->appRoot . '/apps/' . $this->keyfile ))
			throw new \Exception ( __CLASS__ . ': invalid key file ' . $this->keyfile );
		return ($KUINK_CFG->appRoot . '/apps/' . $this->keyfile);
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
			$eventArr = $this->object_to_array($event);

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
		$this->connect ();
		$entity = ( string ) $this->getParam ( $params, '_entity', true ); 
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
		} else {
			$dir = new \Google_Service_Directory ( $this->connector );
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

}

?>
