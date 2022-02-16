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
 *      <DataSource name="sampleDataSource" connector="MicrosoftAPIAdminSDKConnector">
 *        <Param name="type">SampleType</Param>
 *      </DataSource>
 * 
 *    Example of a data access to load the record with the id "1" from entity "SampleEntity"
 * 
 *      <Var name="record" dump="true">
 *	      <DataAccess method="load" datasource="sampleDataSource">
 *          <Param name="_entity">SampleEntity</Param>
 *          <Param name="id">1</Param>
 * 	      </DataAccess>
 *      </Var>
 * 
 *    Note: In order to access the datasource if we need extra PHP code, like an external library, place
 *          the code in kuink-core/lib/tools directory
 *          Include those libraries in the file kuink-core/bootstrap/autoload 
 *          example: require_once ($KUINK_INCLUDE_PATH . 'lib/tools/zend_libs/autoload.php');
 * 
 * Information:
 *    APP registration:
 *       .https://portal.azure.com
 *       .https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade
 * 
 *    Microsoft Graph | msgraph-sdk-php:
 *       .https://github.com/microsoftgraph
 *       .https://github.com/microsoftgraph/msgraph-sdk-php
 * 
 *    Documentation:
 *       .https://docs.microsoft.com/pt-br/graph
 *       .https://developer.microsoft.com/en-us/graph/graph-explorer
 * 
 *    Implementation Example (Kickoff)
 *       .https://itd.sog.unc.edu/knowledge-base/article/simple-php-microsoft-graph-application
 */


namespace Kuink\Core\DataSourceConnector;

use Kuink\Core\Exception\NotImplementedException;
use Kuink\Core\Exception\ParameterNotFound;

// Include the Microsoft Graph classes
use \Microsoft\Graph\Graph;
use \Microsoft\Graph\Model;

/**
 * Description of MicrosoftAPIAdminSDKConnector
 * Use Microsoft Graph gateway to manage data and intelligence in Microsoft 365.
 *
 * @author jose.feio
 */
class MicrosoftAPIAdminSDKConnector extends \Kuink\Core\DataSourceConnector{
  var $accessToken = '';     // The object holding the connection
  var $connector;
  var $domain;               // Default Domain
  var $licenceSkuId;         // Default Licence SKU Id
    
  var $entity = 'users';

  var $translator = [
        'givenName' => 'given_name',
        'surname' => 'surname',
        'displayName' => 'display_name',
        'name' => 'name',
        'mail' => 'email',
        'otherMails' => 'recovery_email',
        'mobilePhone' => 'mobile',
        'streetAddress' => 'street_address',
        'postalCode' => 'postal_code',
        'city' => 'postal_address',
        'jobTitle' => 'job_title',
        'officeLocation' => 'office_location',
        'mailNickname' => 'uid',
        'preferredLanguage' => 'language',
        'usageLocation' => 'usage_location',
        'ageGroup' => 'age_group',
        'password' => 'password',
        'changePasswordAtNextLogin' => 'change_password',
        'extensionAttribute1' => 'attribute1',
        'extensionAttribute2' => 'attribute2',
        'extensionAttribute3' => 'attribute3',
        'extensionAttribute4' => 'attribute4',
        'extensionAttribute5' => 'attribute5',
        'extensionAttribute6' => 'attribute6',
        'extensionAttribute7' => 'attribute7',
        'extensionAttribute8' => 'attribute8',
        'createdDateTime' => '_creation',
        'description' => 'description',
        'groupType' => 'group_type',
        'mailEnabled' => 'mail_enabled',
        'securityEnabled' => 'security_enabled',
        'visibility' => 'visibility',
        'collaborative' => 'is_collaborative',
        'userID' => 'id_user',
        'groupID' => 'id_group',
        'isOwner' => 'is_owner',
        'owner' => 'master',
      ];

  var $rTranslator;


  function __construct($dataSource) {
    parent::__construct($dataSource);

    if (isset($this->translator)){
      $this->rTranslator = array();
      foreach ( $this->translator as $key => $value )
        $this->rTranslator[$value] = $key;
    }
  }

  function connect( ) {
  
    if (! $this->connector) {
      $url          = $this->dataSource->getParam ('url', true );          //'https://login.microsoftonline.com/' . TENANTID . '/oauth2/token?api-version=1.0';
      $clientId     = $this->dataSource->getParam ('clientId', true );     //'94a19cfd-7ac0-4ce1-b010-b297b98142fc';
      $clientSecret = $this->dataSource->getParam ('clientSecret', true ); //'JAM7Q~-O1ryKQAvbB4Ub_27B~7WYGmg.DfyiW';
      $tenantId     = $this->dataSource->getParam ('tenantId', true );     //'9f249c92-ae09-4e5c-8637-06a01dbcaeb9';
      $resource     = $this->dataSource->getParam ('resource', true );     //'https://graph.microsoft.com/'
      
      $guzzle = new \GuzzleHttp\Client();
      $url = str_replace ('TENANTID', $tenantId, $url);
      $token = json_decode($guzzle->post($url, [
          'form_params' => [
              'client_id' => $clientId,
              'client_secret' => $clientSecret,
              'resource' => $resource,
              'grant_type' => 'client_credentials',
          ],
      ])->getBody()->getContents());
      $this->accessToken = $token->access_token;

      $this->connector = new Graph();
      $this->connector->setAccessToken($this->accessToken);

      // Set generic Microsoft client configuration
      $this->domain = $this->dataSource->getParam ( 'domain', true );
      $this->alternativeDomain = $this->dataSource->getParam ( 'alternativeDomain', true );
      $this->licenceSkuId = $this->dataSource->getParam ( 'licenceSkuId', true );

      // TMP Set Entity...
      // Put this into constructor
      $this->entity = $this->dataSource->getParam ( 'entity.user', true );

      \Kuink\Core\TraceManager::add ( 'Connecting to the datasource Token:'.$this->accessToken, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ ); 
    }

  }

  /**
   * ----------------------------------------------------------------------------------------------
   * USER Stuff
   */


  /**
   * Inserts a USER
   * 
   */
  function insert($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $givenName = (string)$this->getParam($params, $this->translator['givenName'], true);
    $surname = (string)$this->getParam($params, $this->translator['surname'], true);
    $displayName = (string)$this->getParam($params, $this->translator['displayName'], false, $givenName." ".$surname);

    $jobTitle = isset ($params['jobTitle']) ? (string)$this->getParam($params, 'jobTitle', false) : null;
    $mobilePhone = isset ($params['jobTitle']) ? (string)$this->getParam($params, 'mobilePhone', false) : null;
    $officeLocation = isset ($params['jobTitle']) ? (string)$this->getParam($params, 'officeLocation', false) : null;

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], true);
    $mail = (string)$this->getParam($params, $this->translator['email'], false, $mailNickname.'@'.$this->domain);
    $userPrincipalName = $mailNickname.'@'.$this->domain;

    $otherMails = (string)$this->getParam($params, $this->translator['otherMails'], false, 
                    $mailNickname.'@'.$this->alternativeDomain);

    $password = (string)$this->getParam($params, $this->translator['password'], true);
    $passwordPolicies = (string)$this->getParam($params, 'passwordPolicies', false, "DisablePasswordExpiration,DisableStrongPassword");
    $changePasswordAtNextLogin = (string)$this->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
    $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

    $preferredLanguage = (string)$this->getParam($params, $this->translator['preferredLanguage'], false, 
                           (string)$this->dataSource->getParam ('preferredLanguage', true ));
    $usageLocation = (string)$this->getParam($params, $this->translator['usageLocation'], false,
                       (string)$this->dataSource->getParam ('usageLocation', true ));

    $userType = isset ($params['userType']) ? (string)$this->getParam($params, 'userType', false) : "Member";
    $ageGroup = (string)$this->getParam($params, $this->translator['ageGroup'], false, null);


    $data = [
      'accountEnabled' => 'true',
      'displayName' => $displayName,
      'givenName' => $givenName,
      'surname' => $surname,
      'jobTitle' => $jobTitle,
      'mail' => $mail,
      'otherMails' => [$otherMails],
      'mobilePhone' => $mobilePhone,
      'officeLocation' => $officeLocation,
      'mailNickname' => $mailNickname,
      'preferredLanguage' => $preferredLanguage,
      'userPrincipalName' => $userPrincipalName,
      'usageLocation' => $usageLocation,
      'passwordPolicies' => $passwordPolicies,
      'passwordProfile' => [
          'password' => $password,
          'forceChangePasswordNextSignIn' => $changePasswordAtNextLogin,
      ],
      'userType' => $userType,
      'ageGroup' => $ageGroup,
    ];

    //var_dump($data);
    try {
      $result = $this->connector->createRequest("POST", "/$entity")
                                ->attachBody($data)
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $result->getProperties();
}


  /**
   * Updates a USER
   * @param array $params The params that are passed to update an entity record
   */
  function update($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);

    if (isset ( $params ['_entity'] ))
			unset ( $params ['_entity'] );
		if (isset ( $params [$this->translator['mailNickname']] ))
			unset ( $params [$this->translator['mailNickname']] );

    $data = array();         // Data to update

    // Password stuff, if updated
    if (isset ( $params [$this->translator['password']] )){
      $password = (string)$this->getParam($params, 'password', true);
      $passwordPolicies = (string)$this->getParam($params, 'passwordPolicies', false, "DisablePasswordExpiration,DisableStrongPassword");
      $changePasswordAtNextLogin = (string)$this->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
      $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

      $data = [
        'passwordPolicies' => $passwordPolicies,
        'passwordProfile' => [
            'password' => $password,
            'forceChangePasswordNextSignIn' => $changePasswordAtNextLogin,
        ],
      ];

      unset ( $params [$this->translator['password']] );
      if (isset ( $params [$this->translator['passwordPolicies']] ))
        unset ( $params [$this->translator['passwordPolicies']] );
      if (isset ( $params [$this->translator['changePasswordAtNextLogins']] ))
        unset ( $params [$this->translator['changePasswordAtNextLogin']] );
    }

    foreach ( $params as $key => $value )
      if (!is_null($value)){
        $aux = isset ($this->rTranslator[$key]) ? (string)$this->rTranslator[$key] : $key;
        if (substr($aux,0,18) === "extensionAttribute")
          $data['onPremisesExtensionAttributes'][$aux] = is_array ( $value ) ? $value : ( string ) $value;
        else
          $data[$aux] = is_array ( $value ) ? $value : ( string ) $value;
      }

    //var_dump($data);
    try {
      $result = $this->connector->createRequest("PATCH", "/$entity/$id")
                                ->attachBody($data)
                                ->setReturnType(Model\User::class)
                                ->execute();

    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 1;
    }

    \Kuink\Core\TraceManager::add ( 'Updating a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return 0;	
  }  


  /**
   * Reset USER PASSWORD | Not yet!
   * @param array $params The params that are passed to update an entity record
   * ---> Missing: Delegated access!
   */
  function resetPassword($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $id = (string)$this->getParam($params, 'id', false);
    if ($id === ''){
      $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], true);
      $userPrincipalName = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
      try {
        $result = $this->connector->createRequest("GET", "/$entity/$userPrincipalName")
                                  ->setReturnType(Model\User::class)
                                  ->execute();
      } catch ( \Exception $e ) {
        return 0;
      }
      $id = $result->getId();
    }

    // Password stuff
    $password = (string)$this->getParam($params, 'password', true);
    $changePasswordAtNextLogin = (string)$this->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
    $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

    $data = [
      'passwordProfile' => [
        'password' => $password,
        'forceChangePasswordNextSignIn' => $changePasswordAtNextLogin,
      ],
    ];

    //var_dump($data);
    try {
      $result = $this->connector->createRequest("PATCH", "/$entity/$id")
                                ->attachBody($data)
                                ->setReturnType(Model\User::class)
                                ->execute();

    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Updating a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $result->getProperties();	
  }


  /**
   * Assignes a Licence to a USER
   * 
   */
  function assignLicense($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);

    $licenceSkuId = (string)$this->getParam($params, 'licenceSkuId', false, $this->licenceSkuId);

    // Licence Stuff!
    $licences = [
      'addLicenses' => [
          [
            'disabledPlans' => [],
            'skuId' => $licenceSkuId,
          ],
      ],
      'removeLicenses' => []
    ];

    try {
      $result = $this->connector->createRequest("POST", "/$entity/$id/assignLicense")
                                ->attachBody($licences)
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      var_dump($e);
      return 0;
    }

    return $result->getProperties(); 	
  }  
  

  /**
   * Deletes a USER
   * It goes to trash bin, deleted list
   */
  function delete($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);    

    //var_dump($id);
    try {
      $result = $this->connector->createRequest("DELETE", "/$entity/$id")
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Deleting id:'.$id.'  on entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $result->getProperties();
  }
  

  /**
   * Get a USER
   * Parameters are optional, example: query => displayName,givenName,id 
   */
  function load($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);

    $query = (string)$this->getParam($params, 'query', false);            // List of parameters
    if ($query !== ''){
      $query = '?$select='.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity/$id".$query)
                                ->setReturnType(Model\User::class)
                                ->execute();
    } catch ( \Exception $e ) {
      return 0;
    }

    // Set the return array, Translated
    $user = $this->objectArrayTranslated($result);

    return $user;
  }
  

  /**
   * Get all USERs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * https://docs.microsoft.com/en-us/graph/api/user-list?view=graph-rest-1.0&tabs=http
   * 
   */
  function getAll($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);
 
    $query = (string)$this->getParam($params, 'query', false);            // List of parameters
    if ($query !== ''){
      $query = '?$select='.$query.'&$top=999';
    }

    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity".$query)
                               ->setReturnType(Model\User::class)
                               ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    // Set the return array, Translated
    $users = $this->objectArrayTranslated($result);

    return $users;
  }
 
  
  /**
   * Changed USERs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * 
   */
  function changed($params) {
  	$this->connect();

    $entity = isset ($params['_entity']) ? (string)$this->getParam($params, '_entity', false) : $this->entity;
    $query = (string)$this->getParam($params, 'query', false);            // List of parameters

    if ($query !== ''){
      $query = '?$select='.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity/delta".$query)
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    return $result;
  }


  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaName($params) {
  	return null;
  }


  /**
   * ----------------------------------------------------------------------------------------------
   * Group Stuff
   */

  /**
   * Inserts a GROUP | +/-
   * 
   */
  function insertG($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $displayName = (string)$this->getParam($params, $this->translator['displayName'], true);
    $description = isset ($params[$this->translator['description']]) ? (string)$this->getParam($params, $this->translator['description'], false) : $displayName;

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], true);
    $groupType = (string)$this->getParam($params, $this->translator['groupType'], false, "Unified");
    $mailEnabled = isset ($params[$this->translator['mailEnabled']]) ? (string)$this->getParam($params, $this->translator['mailEnabled'], false) : true;
    $securityEnabled = isset ($params[$this->translator['securityEnabled']]) ? (bool)$this->getParam($params, $this->translator['securityEnabled'], false) : true;

    $visibility = (string)$this->getParam($params, $this->translator['visibility'], false, "Private");

    $userID = (string)$this->getParam($params, $this->translator['userID'], false);

    $collaborative = (bool)$this->getParam($params, $this->translator['collaborative'], false, false);
    if ($collaborative && isset($userID)){
      $groupType = "Unified";
      $mailEnabled = true;
      $owner = "https://graph.microsoft.com/v1.0/users/".$userID;
    }

    $data = [
      'displayName' => $displayName,
      'description' => $description,
      'groupTypes' => [
        $groupType
      ],
      'mailEnabled' => $mailEnabled,
      'mailNickname' => $mailNickname,
      'securityEnabled' => $securityEnabled,
      'visibility' => $visibility,
      'owners@odata.bind' => [
        $owner
      ],
    ];

    // Create group
    try {
      $result = $this->connector->createRequest("POST", "/$entity")
                                ->attachBody($data)
                                ->setReturnType(Model\Group::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }

    $group = $this->objectArrayTranslated($result);


    \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $group;
  }


  /**
   * Updates a GROUP | Not Ok
   * @param array $params The params that are passed to update an entity record
   */
  function updateG($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);

    /**
     * TODO
     * $allowExternalSenders = (bool)$this->getParam($params, $this->translator['allow_external_senders'], false, false);
     */
      
    if (isset ( $params ['_entity'] ))
			unset ( $params ['_entity'] );
		if (isset ( $params [$this->translator['mailNickname']] ))
			unset ( $params [$this->translator['mailNickname']] );

    // Password stuff, if updated
    if (isset ( $params [$this->translator['password']] )){
      $password = (string)$this->getParam($params, 'password', true);
      $passwordPolicies = (string)$this->getParam($params, 'passwordPolicies', false, "DisablePasswordExpiration,DisableStrongPassword");
      $changePasswordAtNextLogin = (string)$this->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
      $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

      $data = [
        'passwordPolicies' => $passwordPolicies,
        'passwordProfile' => [
            'password' => $password,
            'forceChangePasswordNextSignIn' => $changePasswordAtNextLogin,
        ],
      ];

      unset ( $params [$this->translator['password']] );
      if (isset ( $params [$this->translator['passwordPolicies']] ))
        unset ( $params [$this->translator['passwordPolicies']] );
      if (isset ( $params [$this->translator['changePasswordAtNextLogins']] ))
        unset ( $params [$this->translator['changePasswordAtNextLogin']] );
    }


    foreach ( $params as $key => $value ){
      if (!is_null($value)){
        $aux = $this->rTranslator[$key];
        $data->$aux = is_array ( $value ) ? $value : ( string ) $value;
      }
    }

    //var_dump($data);
    try {
      $result = $this->connector->createRequest("PATCH", "/$entity/$id")
                                ->attachBody($data)
                                ->setReturnType(Model\User::class)
                                ->execute();

    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Updating a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $result;	
  }  


  /**
   * Deletes a GROUP | Not Ok
   * It goes to trash bin, deleted list
   */
  function deleteG($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------
    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);    

    //var_dump($id);
    try {
      $result = $this->connector->createRequest("DELETE", "/$entity/$id")
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Deleting id:'.$id.'  on entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return 1;
  }


  /**
   * Get a GROUP
   * Parameters are optional, example: query => displayName,givenName,id 
   */
  function loadG($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);
    $id = (string)$this->getParam($params, 'id', true);

    $query = (string)$this->getParam($params, 'query', false);            // List of parameters
    if ($query !== ''){
      $query = '?$select='.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity/$id".$query)
                                ->setReturnType(Model\Group::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    return $result;
  }
  

  /**
   * Get all GROUPs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * https://docs.microsoft.com/en-us/graph/api/user-list?view=graph-rest-1.0&tabs=http
   * 
   */
  function getAllG($params) {
  	$this->connect();
    
    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);
 
    $query = (string)$this->getParam($params, 'query', false);            // List of parameters
    if ($query !== ''){
      $query = '?'.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity".$query)
                                ->setReturnType(Model\Group::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    // Set the return array, Translated
    $groups = $this->objectArrayTranslated($result);

    \Kuink\Core\TraceManager::add ( 'Get All:'.$entity.'  ~>'.$groups, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $groups;
  }
 

  /**
   * Add User to GROUP | +/-
   * 
   */
  function addUser($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $groupID = (string)$this->getParam($params, $this->translator['groupID'], true);
    $userID = (string)$this->getParam($params, $this->translator['userID'], true);
    $role = (bool)$this->getParam($params, $this->translator['isOwner'], false, 0);

    if ($role)
      $data = [
        'owners@odata.bind' => ["https://graph.microsoft.com/v1.0/users/".$userID,]
      ];
    else
      $data = [
        'members@odata.bind' => ["https://graph.microsoft.com/v1.0/users/".$userID,]
      ];

    //var_dump($data);
    try {
      $result = $this->connector->createRequest("PATCH", "/$entity/".$groupID)
                                ->attachBody($data)
                                ->setReturnType(Model\Group::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }

    // Set the return array, Translated
    $groups = $this->objectArrayTranslated($result);

    \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $groups;
  }


  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaNameG($params) {
  	return null;
  }


  /**
   * ----------------------------------------------------------------------------------------------
   * Team Stuff
   */

  /**
   * Inserts a Team
   * 
   */
  function insertT($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.team', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $groupID = (string)$this->getParam($params, $this->translator['groupID'], true);

    $data = [
      'template@odata.bind' => "https://graph.microsoft.com/v1.0/teamsTemplates('standard')",
      'group@odata.bind' => "https://graph.microsoft.com/v1.0/groups('".$groupID."')"
    ];

    try {
      $result = $this->connector->createRequest("POST", "/$entity")
                                ->attachBody($data)
                                ->setReturnType(Model\Team::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Assign Group to Team'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }
    $team = $this->objectArrayTranslated($result);


    \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $result;
  }


  /**
   * Updates a GROUP | Not Ok
   * @param array $params The params that are passed to update an entity record
   */
  function updateT($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);

    if (isset ( $params ['_entity'] ))
			unset ( $params ['_entity'] );
		if (isset ( $params [$this->translator['mailNickname']] ))
			unset ( $params [$this->translator['mailNickname']] );

    // Password stuff, if updated
    if (isset ( $params [$this->translator['password']] )){
      $password = (string)$this->getParam($params, 'password', true);
      $passwordPolicies = (string)$this->getParam($params, 'passwordPolicies', false, "DisablePasswordExpiration,DisableStrongPassword");
      $changePasswordAtNextLogin = (string)$this->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
      $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

      $data = [
        'passwordPolicies' => $passwordPolicies,
        'passwordProfile' => [
            'password' => $password,
            'forceChangePasswordNextSignIn' => $changePasswordAtNextLogin,
        ],
      ];

      unset ( $params [$this->translator['password']] );
      if (isset ( $params [$this->translator['passwordPolicies']] ))
        unset ( $params [$this->translator['passwordPolicies']] );
      if (isset ( $params [$this->translator['changePasswordAtNextLogins']] ))
        unset ( $params [$this->translator['changePasswordAtNextLogin']] );
    }


    foreach ( $params as $key => $value ){
      if (!is_null($value)){
        $aux = $this->rTranslator[$key];
        $data->$aux = is_array ( $value ) ? $value : ( string ) $value;
      }
    }

    //var_dump($data);
    try {
      $result = $this->connector->createRequest("PATCH", "/$entity/$id")
                                ->attachBody($data)
                                ->setReturnType(Model\User::class)
                                ->execute();

    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$e, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Updating a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $result;	
  }  


  /**
   * Deletes a GROUP | Not Ok
   * It goes to trash bin, deleted list
   */
  function deleteT($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------
    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);

    $mailNickname = (string)$this->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->getParam($params, 'id', true);    

    //var_dump($id);
    try {
      $result = $this->connector->createRequest("DELETE", "/$entity/$id")
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Deleting id:'.$id.'  on entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return 1;
  }


  /**
   * Get a GROUP
   * Parameters are optional, example: query => displayName,givenName,id 
   */
  function loadT($params) {
  	$this->connect();

    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);
    $id = (string)$this->getParam($params, 'id', true);

    $query = (string)$this->getParam($params, 'query', false);            // List of parameters
    if ($query !== ''){
      $query = '?$select='.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity/$id".$query)
                                ->setReturnType(Model\Group::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    return $result;
  }
  

  /**
   * Get all GROUPs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * https://docs.microsoft.com/en-us/graph/api/user-list?view=graph-rest-1.0&tabs=http
   * 
   */
  function getAllT($params) {
  	$this->connect();
    
    // TMP -> Goes into constructor
    $this->entity = $this->dataSource->getParam ( 'entity.group', true );
    // ------------------------------------------------------------------

    $entity = (string)$this->getParam($params, '_entity', false, $this->entity);
 
    $query = (string)$this->getParam($params, 'query', false);            // List of parameters
    if ($query !== ''){
      $query = '?'.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/$entity".$query)
                                ->setReturnType(Model\Group::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    // Set the return array, Translated
    $groups = $this->objectArrayTranslated($result);

    \Kuink\Core\TraceManager::add ( 'Get All:'.$entity.'  ~>'.$groups, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return $groups;
  }
 

  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaNameT($params) {
  	return null;
  }


  /**
   * ----------------------------------------------------------------------------------------------
   * Directory Service Stuff
   */

  /**
   * Permanently Deletes a ITEM
   * It goes to trash bin, deleted list
   */
  function permanentlyDelete($params) {
  	$this->connect();

    $entity = "/directory/deletedItems/";
    $id = (string)$this->getParam($params, 'id', true);    

    //var_dump($id);
    try {
      $result = $this->connector->createRequest("DELETE", "/$entity/$id")
                                ->setReturnType(Model\Directory::class)
                                ->execute();
    //var_dump($result);
    } catch ( \Exception $e ) {
      //var_dump($e);
      return 0;
    }

    \Kuink\Core\TraceManager::add ( 'Deleting id:'.$id.'  on entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );
    return 1;
  }


  /**
   * ====================================================================================
   * Auxiliary Functions | Methods
   */

  /**
   * Transforms an object to array of values
   */
  private function objectArrayTranslated($params) {

    $p = is_object($params) ? $params->getProperties() : $params;

    if (is_array($p)){
      // Set the return array, Translated
      $result = array();
      foreach ( $p as $key => $value ){
        if (isset($value)){
          if (is_array($value) or is_object($value)){
            $tmp = isset ($this->translator[$key]) ? (string)$this->translator[$key] : $key;
            $result[$tmp] = $this->objectArrayTranslated($value);
          }
          else {
            $tmp = isset ($this->translator[$key]) ? (string)$this->translator[$key] : $key;
            $result[$tmp] = $value;
          }
        }
      }
    } else
        return null;
    
  	return $result;
  }
}

?>
