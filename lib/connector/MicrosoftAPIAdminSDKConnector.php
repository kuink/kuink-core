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

//Include the Microsoft Graph classes
use \Microsoft\Graph\Graph;
use \Microsoft\Graph\Model;

/**
 * All differenciatied entities handled by this connector
 */
class MicrosoftAPIAdminSDKEntity {
	const USER = 'user';
	const GROUP = 'group';
  const TEAM = 'team';
	const DIRECTORY_SERVICE = 'directory.service';
}



/**
 * Description of MicrosoftAPIAdminSDKConnector
 * Use Microsoft Graph gateway to manage data and intelligence in Microsoft 365.
 *
 * @author jose.feio
 */
class MicrosoftAPIAdminSDKConnector extends \Kuink\Core\DataSourceMultiEntityConnector {
  var $accessToken = '';      // The Access Token, Azure configuration
  var $connector;             // The object holding the connection to the service
  var $domain;                // Default Domain
  var $licenceSkuId;          // Default Licence SKU Id

  function __construct($dataSource) {
    parent::__construct($dataSource);

    // Load datasource params
    $this->url          = $this->dataSource->getParam ('url', true );
    $this->clientId     = $this->dataSource->getParam ('clientId', true );
    $this->clientSecret = $this->dataSource->getParam ('clientSecret', true );
    $this->tenantId     = $this->dataSource->getParam ('tenantId', true );
    $this->resource     = $this->dataSource->getParam ('resource', true );

    // Generic Microsoft client configuration
    $this->domain = $this->dataSource->getParam ( 'domain', true );
    $this->alternativeDomain = $this->dataSource->getParam ( 'alternativeDomain', true );
    $this->licenceSkuId = $this->dataSource->getParam ( 'licenceSkuId', true );

		// Setup handler class names. The configEntityHandlers variable 
		$this->configEntityHandlers = [
      MicrosoftAPIAdminSDKEntity::USER => "\Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKUserHandler",
      MicrosoftAPIAdminSDKEntity::GROUP => "\Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKGroupHandler",
      MicrosoftAPIAdminSDKEntity::TEAM => "\Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKTeamHandler",
      MicrosoftAPIAdminSDKEntity::DIRECTORY_SERVICE => "\Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKDirectoryServiceHandler"
		];

  }

  function connect($entity=null) {
    if (!$this->connector) {
      
      $guzzle = new \GuzzleHttp\Client();
      $url = str_replace ('TENANTID', $this->tenantId, $this->url);
      $token = json_decode($guzzle->post($url, [
          'form_params' => [
              'client_id' => $this->clientId,
              'client_secret' => $this->clientSecret,
              'resource' => $this->resource,
              'grant_type' => 'client_credentials',
          ],
      ])->getBody()->getContents());
      $this->accessToken = $token->access_token;

      $this->connector = new Graph();
      $this->connector->setAccessToken($this->accessToken);

      // Setup specific entity connection properties
      if (isset($entity)) {
        $handler = $this->getEntityHandler($entity);
        $handler->connect();
      }
    }
  }
}



/**
 * Description of MicrosoftAPIAdminSDKConnectorCommon
 * Common methods for MicrosoftAPIAdminSDKConnector implementation.
 *
 * @author jose.feio
 */
abstract class MicrosoftAPIAdminSDKConnectorCommon {

  /**
   * Transforms an object to array of values
   */
  protected function objectArrayTranslated($params) {
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




/********************************************************************************************
 *  
 * Entity Handlers
 * 
 ********************************************************************************************/
/**
 * Class to handle all basic user operations
 *
 * @author jose.feio
 */

class MicrosoftAPIAdminSDKUserHandler extends \Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKConnectorCommon
                                      implements \Kuink\Core\ConnectorEntityHandlerInterface {
  var $connector; //The parent connector object to get all the context
  var $translator; //Array that will contain all mandatory fields for insert and update users
  var $rTranslator; //Calculated reversed translator


  public function __construct($connector) {
    $this->connector = $connector;

    $this->translator['id'] = \Kuink\Core\PersonProperty::ID;
    $this->translator['mailNickname'] = \Kuink\Core\PersonProperty::UID;
    $this->translator['givenName'] = \Kuink\Core\PersonProperty::GIVEN_NAME;
    $this->translator['surname'] = \Kuink\Core\PersonProperty::SURNAME;
    $this->translator['display_name'] = \Kuink\Core\PersonProperty::DISPLAY_NAME;
    $this->translator['name'] = \Kuink\Core\PersonProperty::NAME;

    $this->translator['mobilePhone'] = \Kuink\Core\PersonProperty::MOBILE;
    $this->translator['mail'] = \Kuink\Core\PersonProperty::EMAIL;
    $this->translator['domain'] = \Kuink\Core\PersonProperty::DOMAIN;

    $this->translator['password'] = \Kuink\Core\PersonProperty::PASSWORD;
    $this->translator['otherMails'] = \Kuink\Core\PersonProperty::RECOVERY_EMAIL;
    $this->translator['changePasswordAtNextLogin'] = \Kuink\Core\PersonProperty::CHANGE_PASSWORD;

    $this->translator['streetAddress'] = \Kuink\Core\PersonProperty::STREET_ADDRESS;
    $this->translator['postalCode'] = \Kuink\Core\PersonProperty::POSTAL_CODE;
    $this->translator['city'] = \Kuink\Core\PersonProperty::POSTAL_ADDRESS;

    $this->translator['preferredLanguage'] = \Kuink\Core\PersonProperty::PREFERRED_LANGUAGE;

    $this->translator['jobTitle'] = \Kuink\Core\PersonProperty::JOB_TITLE;

    $this->translator['officeLocation'] = 'office_location';
    $this->translator['usageLocation'] = 'usage_location';
    $this->translator['ageGroup'] = 'age_group';
    $this->translator['consentProvidedForMinor'] = 'consent_provided_for_minor';

    $this->translator['extensionAttribute1'] = 'attribute1';
    $this->translator['extensionAttribute2'] = 'attribute2';
    $this->translator['extensionAttribute3'] = 'attribute3';
    $this->translator['extensionAttribute4'] = 'attribute4';
    $this->translator['extensionAttribute5'] = 'attribute5';
    $this->translator['extensionAttribute6'] = 'attribute6';
    $this->translator['extensionAttribute7'] = 'attribute7';
    $this->translator['extensionAttribute8'] = 'attribute8';

    $this->translator['accountEnabled'] = 'account_enabled';

    $this->translator['createdDateTime'] = \Kuink\Core\PersonProperty::_CREATION;

    if (isset($this->translator)){
      $this->rTranslator = array();
      foreach ( $this->translator as $key => $value )
        $this->rTranslator[$value] = $key;
    }

  }

  
  /**
	 * Handler specific connection properties
	 */
	public function connect() {
		//In this entity we must set the delegation admin user
		//$this->connector->connector->setSubject($this->connector->delegatedAdmin);		
	}


  /**
   * Gets a USER
   * Parameters are optional, example: _attributes => displayName,givenName,id 
	 * 
	 * Example: Get the user "dummy.xpto" with the following attributes: id, mail and display name
	 * 		<DataAccess method="load" datasource="microsoftAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 * 			<Param name="_attributes">id,mail,displayName</Param>
	 * 		</DataAccess>
	 */
  public function load($params, $operators) {
  	$this->connect();

    $mailNickname = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '') {
      $userDomain = (string)$this->connector->getParam($params, $this->translator['domain'], false, $this->connector->domain);
      $id = $mailNickname.'@'.$userDomain;     // Uses userPrincipalName parameter
    } else
      $id = (string)$this->connector->getParam($params, 'id', false);

    $query = (string)$this->connector->getParam($params, '_attributes', false);            // List of parameters
    if ($query !== '') {
      $query = '?$select='.$query;
    }
    
    if (isset($id) && !empty($id)) {
      try {
        \Kuink\Core\TraceManager::add(' Loading user: '.$mailNickname, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
        $result = $this->connector->connector->createRequest("GET", "/users/$id".$query)
                                            ->setReturnType(Model\User::class)
                                            ->execute();
        //var_dump($result);
      } 
      catch (\Exception $e) {
        \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR loading user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
        \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
      
        return 1;
      }

      // Translate data before return
      return $this->objectArrayTranslated($result);
    }
    return 1;
  }
  

  /**
   * Gets all USERs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * https://docs.microsoft.com/en-us/graph/api/user-list?view=graph-rest-1.0&tabs=http
	 * 
	 * Example: Gets all of the users with the following attributes: id, mail and display name
	 * 		<DataAccess method="getAll" datasource="microsoftAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="_attributes">id,mail,displayName</Param>
	 * 		</DataAccess>
	 */
  public function getAll($params, $operators) {
  	$this->connect();

    $query = (string)$this->connector->getParam($params, '_attributes', false);            // List of parameters
    if ($query !== '') {
      $query = '?$select='.$query.'&$top=999';
    }

    try {
      \Kuink\Core\TraceManager::add(' Getting all users', \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("GET", "/users".$query)
                                            ->setReturnType(Model\User::class)
                                            ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR getting all users', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
    
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);
  }
 
  
	/**
	 * Inserts a USER
	 * 
	 * Example: Insert the user "dummy.xpto"
	 * 		<DataAccess method="insert" datasource="microsoftAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 * 			<Param name="given_name">Dummy</Param>
	 *			<Param name="surname">Xpto</Param>
	 *			<Param name="display_name">Dummy Xpto</Param>
	 *			<Param name="mobile">123456789</Param>
   *      <Param name="domain">something.pt</Param>
	 *			<Param name="password">password1234</Param>
	 *			<Param name="age_group">adult</Param>
	 * 		</DataAccess>
	 */
  public function insert($params) {
  	$this->connect();
    
    // Set USER data
    $givenName = (string)$this->connector->getParam($params, $this->translator['givenName'], true);
    $surname = (string)$this->connector->getParam($params, $this->translator['surname'], true);
    $displayName = (string)$this->connector->getParam($params, $this->translator['displayName'], false, $givenName." ".$surname);

    $userDomain = (string)$this->connector->getParam($params, $this->translator['domain'], false, $this->connector->domain);
    $mailNickname = (string)$this->connector->getParam($params, $this->translator['mailNickname'], true);
    $mail = (string)$this->connector->getParam($params, $this->translator['email'], false, $mailNickname.'@'.$userDomain);
    $userPrincipalName = $mailNickname.'@'.$userDomain;

    $jobTitle = isset ($params[$this->translator['jobTitle']]) ? (string)$this->connector->getParam($params, $this->translator['jobTitle'], false) : null;
    $mobilePhone = isset ($params[$this->translator['mobilePhone']]) ? (string)$this->connector->getParam($params, $this->translator['mobilePhone'], false) : null;
    $officeLocation = isset ($params[$this->translator['officeLocation']]) ? (string)$this->connector->getParam($params, $this->translator['officeLocation'], false) : null;

    $otherMails = (string)$this->connector->getParam($params, $this->translator['otherMails'], false, 
                    $mailNickname.'@'.$this->connector->alternativeDomain);

    $password = (string)$this->connector->getParam($params, $this->translator['password'], true);
    $passwordPolicies = (string)$this->connector->getParam($params, 'passwordPolicies', false, "DisablePasswordExpiration,DisableStrongPassword");
    $changePasswordAtNextLogin = (string)$this->connector->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
    $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

    $preferredLanguage = (string)$this->connector->getParam($params, $this->translator['preferredLanguage'], false, 
                            (string)$this->connector->dataSource->getParam ('preferredLanguage', true ));
    $usageLocation = (string)$this->connector->getParam($params, $this->translator['usageLocation'], false,
                        (string)$this->connector->dataSource->getParam('usageLocation', true ));

    $userType = isset ($params['userType']) ? (string)$this->connector->getParam($params, 'userType', false) : "Member";
    $ageGroup = (string)$this->connector->getParam($params, $this->translator['ageGroup'], false, null);
    $consentProvidedForMinor = (string)$this->connector->getParam($params, $this->translator['consentProvidedForMinor'], false, null);

    // Set the object with the data
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
      'consentProvidedForMinor' => $consentProvidedForMinor
    ];

    try {
      \Kuink\Core\TraceManager::add(' Inserting user: '.$mailNickname, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("POST", "/users")
                                           ->attachBody($data)
                                           ->setReturnType(Model\User::class)
                                           ->execute();
      //var_dump($result);
    }
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR creating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
      
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);
  }


	/**
	 * Updates a USER
	 * 
	 * Example: Updates the surname of the user "dummy.xpto"
	 * 		<DataAccess method="update" datasource="microsoftAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 *			<Param name="surname">Dummy</Param>
	 * 		</DataAccess>
   * 
   *  TODO: Add string 'domain', string 'password', string 'age_group', string 'consent_provided_for_minor' parameters.
	 */
  public function update($params) {
  	$this->connect();
    
    $mailNickname = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->connector->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->connector->getParam($params, 'id', true);

    if (isset($params[$this->translator['givenName']]) || isset($params[$this->translator['surname']])) {
      $givenName = $params[$this->translator['givenName']];
      $surname = $params[$this->translator['surname']];
			$params['displayName'] = $givenName.' '.$surname;
    }
    
    if (isset($params['_entity']))
			unset($params['_entity']);
    if (isset($params['_method']))
			unset($params['_method']);
    if (isset($params[$this->translator['mailNickname']]))
			unset($params[$this->translator['mailNickname']]);

    $data = array(); // Data to update

    // Password stuff, if updated
    if (isset ($params[$this->translator['password']])) {
      $password = (string)$this->connector->getParam($params, 'password', true);
      $passwordPolicies = (string)$this->connector->getParam($params, 'passwordPolicies', false, "DisablePasswordExpiration,DisableStrongPassword");
      $changePasswordAtNextLogin = (string)$this->connector->getParam($params, $this->translator['changePasswordAtNextLogin'], false);
      $changePasswordAtNextLogin = ($changePasswordAtNextLogin == 'true' ? true : false);

      /* Password, not implemented!
      $data = [
        'passwordPolicies' => $passwordPolicies,
        'passwordProfile' => [
            'password' => $password,
            'forceChangePasswordNextSignIn' => $changePasswordAtNextLogin,
        ],
      ];
      */

      unset ( $params [$this->translator['password']] );
      if (isset ( $params [$this->translator['passwordPolicies']] ))
        unset ( $params [$this->translator['passwordPolicies']] );
      if (isset ( $params [$this->translator['changePasswordAtNextLogins']] ))
        unset ( $params [$this->translator['changePasswordAtNextLogin']] );
    }

    foreach ( $params as $key => $value ) {
      if (!is_null($value)) {
        $aux = isset ($this->rTranslator[$key]) ? (string)$this->rTranslator[$key] : $key;
        if (substr($aux,0,18) === "extensionAttribute")
          $data['onPremisesExtensionAttributes'][$aux] = is_array ( $value ) ? $value : ( string ) $value;
        else
          $data[$aux] = is_array ( $value ) ? $value : ( string ) $value;
      }
    }
     
    try {
      \Kuink\Core\TraceManager::add(' Updating user '.$mailNickname, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("PATCH", "/users/$id")
                                           ->attachBody($data)
                                           ->setReturnType(Model\User::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
      
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);	
  }  


	/**
   * NOT IMPLEMENTED 
   * 
	 * Save a user
	 */
	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}


  /**
   * NOT IMPLEMENTED 
   * 
   * Resets a USER password
   * @param array $params The params that are passed to update an entity record
   * ---> Missing: Delegated access!
   */
  public function resetPassword($params) {
    \Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
  }


  /**
   * Assignes a Licence to a USER
   * 
   * Example: Assigns a license from connector configuration to user
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *     <Param name="_entity">user</Param>
   *     <Param name="_method">assignLicense</Param>
   *     <Param name="uid">dummy</Param>
   *     <Param name="licenceSkuId"></Param>
   *   </DataAccess>
   */
  public function assignLicense($params) {
  	$mailNickname = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->connector->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->connector->getParam($params, 'id', true);

    $licenceSkuId = (string)$this->connector->getParam($params, 'licenceSkuId', false, $this->connector->licenceSkuId);

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
      \Kuink\Core\TraceManager::add(' Assigning license '.$licenceSkuId.' to user '.$mailNickname, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("POST", "/users/$id/assignLicense")
                                           ->attachBody($licences)
                                           ->setReturnType(Model\User::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR assigning licence', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
    
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);	
  }


  /**
   * Gets a USER license details
   */
  function licenseDetails($params) {
    // Set USER by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'user','uid'=>$uid))['id'];
    else
      $id = (string)$this->connector->getParam($params, 'id', true);         // User ID

    //var_dump($id);
    try {
      $result = $this->connector->connector->createRequest("GET", "/users/$id/licenseDetails")
                                           ->setReturnType(Model\User::class)
                                           ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return $this->objectArrayTranslated($result);
  }


  /**
   * Deletes a USER
   * It goes to trash bin, deleted list
	 * 
	 * Example: Deletes the user "dummy.xpto"
	 * 		<DataAccess method="delete" datasource="microsoftAPIAdminSDK">
	 * 			<Param name="_entity">user</Param>
	 * 			<Param name="uid">dummy.xpto</Param>
	 * 		</DataAccess>
   */
  public function delete($params) {
  	$this->connect();

    $mailNickname = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($mailNickname !== '')
      $id = $mailNickname.'@'.$this->connector->domain;     // Uses userPrincipalName parameter
    else
      $id = (string)$this->connector->getParam($params, 'id', true);    

    try {
      \Kuink\Core\TraceManager::add(' Deleting user '.$mailNickname, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("DELETE", "/users/".$id)
                                            ->setReturnType(Model\User::class)
                                            ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR deleting user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
    
      return 1;
    }

    return 0;
  }


  /**
   * Changed USERs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * 
   */
  protected function changed($params) {
  	$this->connect();

    $query = (string)$this->connector->getParam($params, '_attributes', false);      // List of parameters

    if ($query !== ''){
      $query = '?$select='.$query;
    }
    
    //var_dump($id);
    try {
      $result = $this->connector->createRequest("GET", "/users/delta".$query)
                                ->setReturnType(Model\User::class)
                                ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);
  }


  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaName($params) {
  	return null;
  }
}




/**
 * Class to handle all basic GROUP operations
 *
 * @author jose.feio
 */

class MicrosoftAPIAdminSDKGroupHandler extends \Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKConnectorCommon
                                       implements \Kuink\Core\ConnectorEntityHandlerInterface {
  var $connector;   //The parent connector object to get all the context
  var $translator;  //Array that will contain all mandatory fields for insert and update users
  var $rTranslator; //Calculated reversed translator


  public function __construct($connector) {
    $this->connector = $connector;

    $this->translator['id'] = \Kuink\Core\PersonGroupProperty::ID;
    $this->translator['mailNickname'] = \Kuink\Core\PersonGroupProperty::UID;
    $this->translator['displayName'] = \Kuink\Core\PersonGroupProperty::DISPLAY_NAME;
    $this->translator['description'] = \Kuink\Core\PersonGroupProperty::DESCRIPTION;

    $this->translator['groupType'] = 'group_type';
    $this->translator['mailEnabled'] = 'mail_enabled';
    $this->translator['securityEnabled'] = 'security_enabled';
    $this->translator['visibility'] = \Kuink\Core\PersonGroupProperty::VISIBILITY;
    $this->translator['preferredDataLocation'] = \Kuink\Core\PersonGroupProperty::LOCATION;
    $this->translator['collaborative'] = 'is_collaborative';
    $this->translator['userID'] = 'id_user';
    $this->translator['userUID'] = 'uid_user';
    $this->translator['groupID'] = 'id_group';
    $this->translator['owner'] = 'owner';
    $this->translator['domain'] = 'domain';

    $this->translator['allow_external_senders'] = \Kuink\Core\PersonGroupProperty::ALLOW_EXTERNAL_SENDERS;
    $this->translator['auto_subscribe_new_members'] = \Kuink\Core\PersonGroupProperty::AUTO_SUBSCRIBE_NEW_MEMBERS;

    $this->translator['isOwner'] = \Kuink\Core\PersonGroupProperty::IS_OWNER;
    $this->translator['isMember'] = \Kuink\Core\PersonGroupProperty::IS_MEMBER;

    $this->translator['createdDateTime'] = \Kuink\Core\PersonGroupProperty::_CREATION;

    // Set Reverse Translator
    if (isset($this->translator)){
      $this->rTranslator = array();
      foreach ( $this->translator as $key => $value )
        $this->rTranslator[$value] = $key;
    }
  }

  
  /**
	 * Handler specific connection properties
	 */
	public function connect() {
	}


  /**
   * Gets a GROUP
   * 
   * Example: Get the group "_xpto" with the following attributes: mailNickname and id
   *   <DataAccess method="load" datasource="microsoftAPIAdminSDK">
   *     <Param name="_entity">group</Param>
   *     <Param name="uid">_xpto</Param>
   *     <Param name="_attributes">mailNickname,id</Param>
   *   </DataAccess>
   */
  function load($params, $operators=null) {
    // Set GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== ""){
      $groups = $this->connector->getAll(array('_entity'=>'group','_attributes'=>'mailNickname,id'));
      foreach ( $groups as $key => $value )
        if ($value[$this->translator['mailNickname']] == $uid){
          $id = $value['id'];
          break;
        }
    }
    // Get GROUP ID
    if (!isset($id)) {
      $id = (string)$this->connector->getParam($params, 'id', false);
    }
    
    // List of parameters
    $query = (string)$this->connector->getParam($params, '_attributes', false);
    if ($query !== '') {
      $query = '?$select='.$query;
    }

    if (isset($id) && !empty($id)) {
      try {
        \Kuink\Core\TraceManager::add(' Loading group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
        $result = $this->connector->connector->createRequest("GET", "/groups/$id".$query)
                                            ->setReturnType(Model\Group::class)
                                            ->execute();
        //var_dump($result);
      }
      catch (\Exception $e) {
        \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR loading group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
        \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
        
        return 1;
      }
      return $this->objectArrayTranslated($result);
    }
    return 1;
  }


  /**
   * Gets all GROUPs
   * Parameters are optional, example: query => $select=displayName,givenName,id
   * https://docs.microsoft.com/en-us/graph/api/user-list?view=graph-rest-1.0&tabs=http
   * 
   * Example: Gets all groups with the following attributes: mailNickname and id
   *   <DataAccess method="getAll" datasource="microsoftAPIAdminSDK">
   *     <Param name="_entity">group</Param>
   *     <Param name="_attributes">mailNickname,id</Param>
   *   </DataAccess>
   */
  function getAll($params, $operators=null) {
    // List of parameters
    $query = (string)$this->connector->getParam($params, '_attributes', false);
    if ($query !== '') {
      $query = '?$select='.$query.'&$top=999';
    }
    
    try {
      \Kuink\Core\TraceManager::add(' Loading all groups', \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("GET", "/groups".$query)
                                ->setReturnType(Model\Group::class)
                                ->execute();
      //var_dump($result);
    }
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR getting all groups', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			
      return 1;
    }

    return $this->objectArrayTranslated($result);
  }


  /**
   * Inserts a GROUP
   * 
	 * Example: Insert the group "_xpto" with "dummy.xpto" as owner
   *   <DataAccess method="insert" datasource="microsoftAPIAdminSDK">
   *    <Param name="uid">_xpto</Param>
   *    <Param name="display_name">XPTO</Param>
   *    <Param name="description">Xpto Group</Param>
   *    <Param name="mail_enabled">1</Param>
   *    <Param name="security_enabled">1</Param>
   *    <Param name="uid_user">dummy.xpto</Param>
   *    <Param name="is_owner">1</Param>
   *    <Param name="is_collaborative">1</Param>					
   *   </DataAccess>
   */
  function insert($params) {
    $displayName = (string)$this->connector->getParam($params, $this->translator['displayName'], true);
    $description = isset ($params[$this->translator['description']]) ? (string)$this->connector->getParam($params, $this->translator['description'], false) : $displayName;
    $mailNickname = (string)$this->connector->getParam($params, $this->translator['mailNickname'], true);
    $groupType = (string)$this->connector->getParam($params, $this->translator['groupType'], false);
    $mailEnabled = (isset ($params[$this->translator['mailEnabled']]) ? (int)$this->connector->getParam($params, $this->translator['mailEnabled'], false) : true) ? true : false;
    $securityEnabled = isset ($params[$this->translator['securityEnabled']]) ? (bool)$this->connector->getParam($params, $this->translator['securityEnabled'], false) : true;

    $visibility = (string)$this->connector->getParam($params, $this->translator['visibility'], false, "Private");

    // Set USER by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "")
      $user = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID))['id'];

    if ($user == null)
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], false);
    else
      $userID = $user;

    // $role = (bool)$this->connector->getParam($params, $this->translator['isOwner'], false, 0);

    $collaborative = (bool)$this->connector->getParam($params, $this->translator['collaborative'], false, false);
    
    if ($collaborative) {
      $mailEnabled = true;
      $groupTypes = ["Unified"];
    } else if ( empty($groupType) ) {
      $mailEnabled = false;
      $groupTypes = [];
    } else
      $groupTypes = [$groupType];    


    $data = [
      'displayName' => $displayName,
      'description' => $description,
      'groupTypes' => $groupTypes,
      'mailEnabled' => $mailEnabled,
      'mailNickname' => $mailNickname,
      'securityEnabled' => $securityEnabled,
      'visibility' => $visibility
    ];

    if (isset($userID) && !empty($userID)) {
      $owner = "https://graph.microsoft.com/v1.0/users/".$userID;
      $data['owners@odata.bind'] = [$owner];
    }

    // Create group
    try {
      \Kuink\Core\TraceManager::add(' Inserting group '.$mailNickname, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("POST", "/groups")
                                           ->attachBody($data)
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
      \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR creating group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
      \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
      
      return 1;
    }
    
    return $this->objectArrayTranslated($result);
  }


  /**
   * Updates a GROUP
   * @param array $params The params that are passed to update an entity record
   * 
	 * Example: Updates the group "_xpto"
   *   <DataAccess method="update" datasource="microsoftAPIAdminSDK">
   *    <Param name="uid">_xpto</Param>
   *    <Param name="description">Xpto Test Group</Param>				
   *   </DataAccess>
   */
  function update($params) {
    // Set GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->load(array('uid'=>$uid))['id'];
    // Get GROUP ID
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    // Unset config params
    if (isset ( $params ['_entity'] ))
			unset ( $params ['_entity'] );
    if (isset ( $params ['_method'] ))
			unset ( $params ['_method'] );
    if (isset ( $params [$this->translator['mailNickname']] ))
			unset ( $params [$this->translator['mailNickname']] );
    if (isset ( $params [$this->translator['id']] ))
			unset ( $params [$this->translator['id']] );

                                          
    $data = array(); // Data to update

    // Group type, if updated
    $collaborative = (bool)$this->connector->getParam($params, $this->translator['collaborative'], false, false);
    if ($collaborative){
      $data = ['groupTypes' => [ "Unified" ]];
      $data ['mailEnabled'] = true;
      unset ( $params [$this->translator['collaborative']] );
    }
    elseif (isset ( $params [$this->translator['groupType']] )){
        $groupType = (string)$this->connector->getParam($params, $this->translator['groupType'], false);
        $data = ['groupTypes' => [ $groupType ]];
        unset ( $params [$this->translator['groupType']] );
      }


    foreach ( $params as $key => $value ) {
      if (!is_null($value)) {
        $aux = isset ($this->rTranslator[$key]) ? (string)$this->rTranslator[$key] : $key;
        if ($aux === "securityEnabled")
          $data['securityEnabled'] = (bool)$this->connector->getParam($params, $this->translator['securityEnabled'], false);
        else
          $data[$aux] = is_array ($value) ? $value : (string) $value;
      }
    }
  
    /**
     * TODO
     * $allowExternalSenders = (bool)$this->connector->getParam($params, $this->translator['allow_external_senders'], false, false);
     */
    try {
      \Kuink\Core\TraceManager::add(' Updating group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("PATCH", "/groups/".$id)
                                           ->attachBody($data)
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    }
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
     
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);	
  }  


  /**
   * NOT IMPLEMENTED 
   * 
	 * Saves a GROUP
	 */
	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
	}


 /**
   * Deletes a GROUP
   * It goes to trash bin, deleted list
   * 
   * Example: Deletes the group "_dummy"
   *   <DataAccess method="delete" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="id">123456789ABCDEFGH</Param>
   *   </DataAccess>
   */
  function delete($params) {
  	$this->connect();

    // Get GROUP ID by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->load(array('uid'=>$uid))['id'];
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    try {
      \Kuink\Core\TraceManager::add(' Deleting group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("DELETE", "/groups/".$id)
                                            ->setReturnType(Model\Group::class)
                                            ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR deleting group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
		
      return 1;
    }

    return 0;
  }


  /**
   * Adds a USER to a GROUP
   * 
   * Example: Add user "dummy.xpto" to group "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">addUser</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="uid_user">dummy.xpto</Param>
   *    <Param name="is_owner">0</Param>
   *   </DataAccess>
   */
  function addUser($params) {
    // Get GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'group','uid'=>$uid))['id'];
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    // Get USER by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "") {
      $userDomain = (string)$this->connector->getParam($params, $this->translator['domain'], false, $this->connector->domain);
      $user = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID, $this->translator['domain']=>$userDomain))['id'];
    }
    if ($user == null)
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], true);
    else
      $userID = $user;

    // Set USER role
    $role = (bool)$this->connector->getParam($params, $this->translator['isOwner'], false, 0);
    if ($role) {
      $data = [
        'owners@odata.bind' => ["https://graph.microsoft.com/v1.0/users/".$userID,]
      ];
    }
    else {
      $data = [
        'members@odata.bind' => ["https://graph.microsoft.com/v1.0/users/".$userID,]
      ];
    }
      
    try {
      \Kuink\Core\TraceManager::add(' Adding user '.$userUID.' to group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("PATCH", "/groups/".$id)
                                           ->attachBody($data)
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR adding user to group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			
      return 1;
    }

    return 0;
  }


  /**
   * Removes a USER from a GROUP
   * 
   * Example: Remove user "dummy.xpto" from the group "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">removeUser</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="uid_user">dummy.xpto</Param>
   *   </DataAccess>
   */
  function removeUser($params) {    
    // Get GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array('_entity'=>'group','uid'=>$uid))['id'];
    else
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);
    if (!isset($id))
      return 1;
    
    // Get USER by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "")
      $userID = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID))['id'];
    if (!isset($userID))
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], true);

    try {
      \Kuink\Core\TraceManager::add(' Removing user '.$userUID.' from group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("DELETE", "/groups/".$id."/members/".$userID."/\$ref")
                                            ->setReturnType(Model\Group::class)
                                            ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
      \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR removing user from group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
      \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
      
      return 1;
    }

    return 0;
  }


  /**
   * Adds a USER as owner of a GROUP
   * 
   * Example: Add user "dummy.xpto" as owner of group "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">addOwner</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="uid_user">dummy.xpto</Param>
   *   </DataAccess>
   */
  function addOwner($params) {
    // Get GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'group','uid'=>$uid))['id'];
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    // Get USER by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "")
      $user = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID))['id'];
    if ($user == null)
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], true);
    else
      $userID = $user;

    // Set owner data
    $data['@odata.id'] = "https://graph.microsoft.com/v1.0/users/".$userID;
   
    try {
      \Kuink\Core\TraceManager::add(' Adding user '.$userUID.' as owner of group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("POST", "/groups/".$id."/owners/\$ref")
                                           ->attachBody($data)
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR adding owner of group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			
      return 1;
    }

    return 0;
  }


  /**
   * Removes a USER as owner of a GROUP
   * 
   * Example: Remove user "dummy.xpto" as owner of group "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">removeOwner</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="uid_user">dummy.xpto</Param>
   *   </DataAccess>
   */
  function removeOwner($params) {
    // Get GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'group','uid'=>$uid))['id'];
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    // Get USER by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "")
      $user = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID))['id'];
    if ($user == null)
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], true);
    else
      $userID = $user;
   
    try {
      \Kuink\Core\TraceManager::add(' Removing user '.$userUID.' as owner of group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("DELETE", "/groups/".$id."/owners/".$userID."/\$ref")
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR adding owner of group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			
      return 1;
    }

    return 0;
  }


  /**
   * List GROUP Users (members/owners)
   * Parameter:
   *    .is_owner => 1 -> List owners
   *    .is_owner = 0 or null -> List members
   * 
   * Example: List members of group "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">listUsers</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="is_owner">0</Param>
   *   </DataAccess>
   */
  function listUsers($params) {
    // Get GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'group','uid'=>$uid))['id'];
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);
                                        
    // Get user role
    if ((bool)$this->connector->getParam($params, $this->translator['isOwner'], false, 0))
      $role = 'owners';
    else
      $role = 'members';

    try {
      \Kuink\Core\TraceManager::add(' Listing users of group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("GET", "/groups/".$id."/".$role)
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    } 
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR getting users of group', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
		
      return 1;
    }

    return $this->objectArrayTranslated($result);
  }


  /**
   * Assignes a Licence to GROUP
   * 
   */
  public function assignLicense($params) {
    // Set GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->load(array('uid'=>$uid))['id'];
    // Get GROUP ID
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);
                                        
    $licenceSkuId = (string)$this->connector->getParam($params, 'licenceSkuId', false, $this->connector->licenceSkuId);

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
      \Kuink\Core\TraceManager::add(' Assigning license '.$licenceSkuId.' to group '.$uid, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);
      $result = $this->connector->connector->createRequest("POST", "/groups/$id/assignLicense")
                                           ->attachBody($licences)
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
      //var_dump($result);
    }
    catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR assigning licence', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
      
      return 1;
    }

    // Translate data before return
    return $this->objectArrayTranslated($result);	
  }


  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaNameG($params) {
  	return null;
  }
}




/**
 * Class to handle all basic TEAM operations
 *
 * @author jose.feio
 */

class MicrosoftAPIAdminSDKTeamHandler extends \Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKConnectorCommon
                                      implements \Kuink\Core\ConnectorEntityHandlerInterface {
  var $connector;   //The parent connector object to get all the context
  var $translator;  //Array that will contain all mandatory fields for insert and update users
  var $rTranslator; //Calculated reversed translator


  public function __construct($connector) {
    $this->connector = $connector;

    $this->translator['id'] = \Kuink\Core\PersonGroupProperty::ID;
    $this->translator['mailNickname'] = \Kuink\Core\PersonGroupProperty::UID;
    $this->translator['displayName'] = \Kuink\Core\PersonGroupProperty::DISPLAY_NAME;
    $this->translator['description'] = \Kuink\Core\PersonGroupProperty::DESCRIPTION;

    $this->translator['groupType'] = 'group_type';
    $this->translator['mailEnabled'] = 'mail_enabled';
    $this->translator['securityEnabled'] = 'security_enabled';
    $this->translator['visibility'] = 'visibility';
    $this->translator['collaborative'] = 'is_collaborative';
    $this->translator['userID'] = 'id_user';
    $this->translator['userUID'] = 'uid_user';
    $this->translator['groupID'] = 'id_group';
    $this->translator['owner'] = 'owner';
    $this->translator['domain'] = 'domain';
    $this->translator['membershipType'] = 'membership_type';
    $this->translator['isFavoriteByDefault'] = 'is_favorite_by_default';
    $this->translator['groupID'] = 'id_group';
    $this->translator['groupUID'] = 'uid_group';

    $this->translator['isOwner'] = \Kuink\Core\PersonGroupProperty::IS_OWNER;
    $this->translator['isMember'] = \Kuink\Core\PersonGroupProperty::IS_MEMBER;

    $this->translator['createdDateTime'] = \Kuink\Core\PersonProperty::_CREATION;

    // Set Reverse Translator
    if (isset($this->translator)){
      $this->rTranslator = array();
      foreach ( $this->translator as $key => $value )
        $this->rTranslator[$value] = $key;
    }
  }

  
  /**
	 * Handler specific connection properties
	 */
	public function connect() {
	}


  /**
   * Get a TEAM
   * Parameters are optional, example: query => displayName,givenName,id 
   *  .'convertToArray': 0,N,n -> return as object 
   */
  function load($params, $operators) {
                                            // Set GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== ""){
      $teams = $this->connector->getAll(array('_entity'=>'team','_attributes'=>'mailNickname,id'));
      foreach ( $teams as $key => $value )
        if ($value[$this->translator['mailNickname']] == $uid){
          $id = $value['id'];
          break;
        }
    }                                       // Get GROUP ID
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, 'id', false);
    
    //var_dump($id);
    if (isset($id) && !empty($id)) {
      try {
        $result = $this->connector->connector->createRequest("GET", "/teams/$id")
                                             ->setReturnType(Model\Team::class)
                                             ->execute();

      }
      catch (\Exception $e) {
        \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR loading team', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
        \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
        
        return 1;
      }
      // Convert TEAM to array?
      $c = (string)$this->connector->getParam($params, 'convertToArray', false, true);
      if ($c==0 OR $c=='N' OR $c=='n' OR $c=false)
        return $result;
      else
        return $this->objectArrayTranslated($result);
    }
    return 1;
  }


  /**
   * Get all TEAMSs
   * 
   */
  function getAll($params, $operators) {
    $teams = $this->connector->getAll(array('_entity'=>'group','_attributes'=>'id,resourceProvisioningOptions,mailNickname,displayName'));

    $result = array();                      // Search for "Team" in 'resourceProvisioningOptions' array key
    foreach ( $teams as $key => $value )
      if (in_array("Team",$value['resourceProvisioningOptions']))
        $result[]=array ($this->translator['id']=>$value[$this->translator['id']],
                         $this->translator['mailNickname']=>$value[$this->translator['mailNickname']],
                         $this->translator['displayName']=>$value[$this->translator['displayName']]);

    return $result;
  }


  /**
   * Inserts a TEAM, from GROUP
   * 
   */
  function insert($params) {
                                            // Set GROUP by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'group','uid'=>$uid))['id'];
                                            // Get GROUP ID
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    $data = [
      'template@odata.bind' => 'https://graph.microsoft.com/v1.0/teamsTemplates/standard',
      'group@odata.bind' => 'https://graph.microsoft.com/v1.0/groups/'.$id,
    ];

    try {
      $result = $this->connector->connector->createRequest("POST", "/teams")
                                           ->attachBody($data)
                                           ->setReturnType(Model\Team::class)
                                           ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return $this->objectArrayTranslated($result);
  }


  /**
   * Updates a TEAM
   * 
   */
  function update($params) {
  	\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}  


  /**
	 * Save a TEAM
	 */

	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}


  /**
   * List TEAM Users (members/owners)
   * 
   */
  function listUsers($params) {
                                            // Set TEAM by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array ('_entity'=>'group','uid'=>$uid))['id'];
                                            // Get GROUP ID
    if (!isset($id))
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    try {
      $result = $this->connector->connector->createRequest("GET", "/teams/".$id."/members")
                                           ->setReturnType(Model\Group::class)
                                           ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
      \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
      \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
      return 1;
    }

    return $this->objectArrayTranslated($result);
  }


  /**
   * Add User to TEAM
   * 
   * Example: Add user "dummy" to group "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">group</Param>
   *    <Param name="_method">addUser</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="uid_user">dummy</Param>
   *    <Param name="is_owner">0</Param>
   *   </DataAccess>
   */
  function addUser($params) {
                                            // Get TEAM by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array('_entity'=>'team','uid'=>$uid))['id'];
    else
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    if (!isset($id))
      return 1;
    
                                            // Get USER ID by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "")
      $userID = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID))['id'];

    if (!isset($userID))
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], true);
                                        
    $role = (bool)$this->connector->getParam($params, $this->translator['isOwner'], false, 0);

    if ($role)
      $data = [
        '@odata.type' => '#microsoft.graph.aadUserConversationMember',
        'roles' => ["owner"],
        'user@odata.bind' => 'https://graph.microsoft.com/v1.0/users/'.$userID,
      ];
    else
      $data = [
        '@odata.type' => '#microsoft.graph.aadUserConversationMember',
        'roles' => ["member"],
        'user@odata.bind' => 'https://graph.microsoft.com/v1.0/users/'.$userID,
      ];

    //var_dump($data);
    try {
      $result = $this->connector->connector->createRequest("POST", "/teams/".$id."/members")
                                           ->attachBody($data)
                                           ->setReturnType(Model\Team::class)
                                           ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
        \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
        \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
        return 1;
      }

    return 0;
  }


  /**
   * Remove User from TEAM | Not Ok
   * 
   * Example: Remove user "dummy" from team "_dummy"
   *   <DataAccess method="execute" datasource="microsoftAPIAdminSDK">
   *    <Param name="_entity">team</Param>
   *    <Param name="_method">removeUser</Param>
   *    <Param name="uid">_dummy</Param>
   *    <Param name="uid_user">dummy</Param>
   *   </DataAccess>
   */
  function removeUser($params) {
                                            // Get TEAM by UID or Azure ID
    $uid = (string)$this->connector->getParam($params, $this->translator['mailNickname'], false);
    if ($uid !== "")
      $id = $this->connector->load(array('_entity'=>'team','uid'=>$uid))['id'];
    else
      $id = (string)$this->connector->getParam($params, $this->translator['id'], true);

    if (!isset($id))
      return 1;
    
                                            // Get USER ID by UID or Azure ID
    $userUID = (string)$this->connector->getParam($params, $this->translator['userUID'], false);
    if ($userUID !== "")
      $userID = $this->connector->load(array ('_entity'=>'user','uid'=>$userUID))['id'];

    if (!isset($userID))
      $userID = (string)$this->connector->getParam($params, $this->translator['userID'], true);

                                            // Get USER Membership ID
    $teamUsers = $this->listUsers(array('id'=>$id));
    foreach ( $teamUsers as $key => $value )
      if ($value['userId'] == $userID){
        $memberID = $value['id'];
        break;
      }

    if (!isset($memberID))
      return 1;

    //var_dump($data);
    try {
      $result = $this->connector->connector->createRequest("DELETE", "/teams/".$id."/members/".$memberID)
                                            ->setReturnType(Model\Group::class)
                                            ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
      \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
      \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
      return 1;
    }

    return 0;
  }


  /**
   * Deletes a TEAM
   * It goes to trash bin, deleted list
   */
  function delete($params) {
  	\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
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
   * TEAM CHANNELS
   */

  function listChannels($params, $operators) {
    $team = $this->load($params, $operators);
    
    if ($team == 1)
      return 1;

    $id = $team['id'];

    if (isset($id) && !empty($id)) {
      try {
        // Get the channels for the team
        $result = $this->connector->connector->createRequest("GET", "/teams/$id/channels")
                                                    ->setReturnType(Model\Channel::class)
                                                    ->execute();
      }
      catch (\Exception $e) {
        \Kuink\Core\TraceManager::add ( __METHOD__.' ERROR loading channels', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
        \Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
        
        return 1;
      }
      // Convert to array?
      $c = (string)$this->connector->getParam($params, 'convertToArray', false, true);
      if ($c==0 OR $c=='N' OR $c=='n' OR $c=false)
        return $result;
      else
        return $this->objectArrayTranslated($result);
    }
    return 1;
  }
  function loadChannel($params, $operators) {
    $id = (string)$this->connector->getParam($params, $this->translator['id'], true);
    $group_data = [
      'id' => (string)$this->connector->getParam($params, $this->translator['groupID'], false),
      'uid' => (string)$this->connector->getParam($params, $this->translator['groupUID'], false)
    ];
    $team = $this->load($group_data, $operators);

    if ($team == 1)
      return 1;

    $team_id = $team['id'];

    try {
      $result = $this->connector->connector->createRequest("GET", "/teams/$team_id/channels/$id")
                      ->setReturnType(Model\Channel::class)
                      ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR loading channel', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    // Convert CHANNEL to array?
    $c = (string)$this->connector->getParam($params, 'convertToArray', false, true);
    if ($c==0 OR $c=='N' OR $c=='n' OR $c=false)
      return $result;
    else
      return $this->objectArrayTranslated($result);
  }
  function addChannel($params, $operators) {
    $team = $this->load($params, $operators);
    $displayName = (string)$this->connector->getParam($params, $this->translator['displayName'], true);
    $description = isset ($params[$this->translator['description']]) ? (string)$this->connector->getParam($params, $this->translator['description'], false) : $displayName;
    $membershipType = (string)$this->connector->getParam($params, $this->translator['membershipType'], true);
    $isFavoriteByDefault = (bool)$this->connector->getParam($params, $this->translator['isFavoriteByDefault'], true, true);


    if ($team == 1)
      return 1;

    $id = $team['id'];
    $data = [
      'displayName' => $displayName,
      'description' => $description,
      'membershipType' => $membershipType,
      'isFavoriteByDefault' => $isFavoriteByDefault,
    ];
  
    try {
      $result = $this->connector->connector->createRequest("POST", "/teams/$id/channels")
                      ->attachBody($data)
                      ->setReturnType(Model\Channel::class)
                      ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR creating channel', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return 0;
  }
  function deleteChannel($params, $operators) {
    $id = (string)$this->connector->getParam($params, $this->translator['id'], true);
    $group_data = [
      'id' => (string)$this->connector->getParam($params, $this->translator['groupID'], false),
      'uid' => (string)$this->connector->getParam($params, $this->translator['groupUID'], false)
    ];
    $team = $this->load($group_data, $operators);

    if ($team == 1)
      return 1;

    $team_id = $team['id'];

    try {
      $result = $this->connector->connector->createRequest("DELETE", "/teams/$team_id/channels/$id")
                      ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR deleting channel', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return 0;
  }
  function updateChannel($params, $operators) {
    $id = (string)$this->connector->getParam($params, $this->translator['id'], true);
    $group_data = [
      'id' => (string)$this->connector->getParam($params, $this->translator['groupID'], false),
      'uid' => (string)$this->connector->getParam($params, $this->translator['groupUID'], false)
    ];
    $team = $this->load($group_data, $operators);
    $displayName = (string)$this->connector->getParam($params, $this->translator['displayName'], true);
    $description = isset ($params[$this->translator['description']]) ? (string)$this->connector->getParam($params, $this->translator['description'], false) : $displayName;

    if ($team == 1)
      return 1;

    $team_id = $team['id'];
    $data = [];
    if (isset($displayName) && !empty($displayName)) {
      $data['displayName'] = $displayName;
    }
    if (isset($description) && !empty($description)) {
      $data['description'] = $description;
    }
  
    try {
      $result = $this->connector->connector->createRequest("PATCH", "/teams/$team_id/channels/$id")
                      ->attachBody($data)
                      ->setReturnType(Model\Channel::class)
                      ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating channel', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return 0;
  }
  
}




/**
 * Class to handle all basic DIRECTORY SERVICE operations
 *
 * @author jose.feio
 */

class MicrosoftAPIAdminSDKDirectoryServiceHandler extends \Kuink\Core\DataSourceConnector\MicrosoftAPIAdminSDKConnectorCommon
                                                  implements \Kuink\Core\ConnectorEntityHandlerInterface {
  var $connector;   //The parent connector object to get all the context


  public function __construct($connector) {
    $this->connector = $connector;
  }

  
  /**
	 * Handler specific connection properties
	 */
	public function connect() {
	}


  /**
   * Get a DIRECTORY SERVICE
   * Parameters are optional, example: query => displayName,givenName,id 
   */
  function load($params, $operators) {
    \Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
  }


  /**
   * Get all DIRECTORY SERVICE
   * 
   */
  function getAll($params, $operators) {
    \Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
  }


  /**
   * Inserts a DIRECTORY SERVICE item
   * 
   */
  function insert($params) {
    \Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
  }


  /**
   * Updates a DIRECTORY SERVICE
   * 
   */
  function update($params) {
  	\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}  


  /**
	 * Save a DIRECTORY SERVICE
	 */

	public function save($params) {
		\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
	}


  /**
   * Deletes a DIRECTORY SERVICE
   * It goes to trash bin, deleted list
   */
  function delete($params) {
  	\Kuink\Core\TraceManager::add ( __METHOD__.' Not implemented', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );
  }


  /**
   * Permanently Deletes a ITEM
   * It goes to trash bin, deleted list
   */
  function permanentlyDelete($params) {
    $id = (string)$this->connector->getParam($params, 'id', true);    

    //var_dump($id);
    try {
      $result = $this->connector->connector->createRequest("DELETE", "/directory/deletedItems/$id")
                                           ->setReturnType(Model\Directory::class)
                                           ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return 0;
  }


  /**
   * Restore Deleted ITEM
   */
  function restore($params) {
    $id = (string)$this->connector->getParam($params, 'id', true);         // Deleted Item ID

    //var_dump($id);
    try {
      $result = $this->connector->connector->createRequest("POST", "/directory/deletedItems/$id/restore")
                                           ->setReturnType(Model\Directory::class)
                                           ->execute();
    //var_dump($result);
    } catch (\Exception $e) {
			\Kuink\Core\TraceManager::add ( __METHOD__.' ERROR updating user', \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  				
			\Kuink\Core\TraceManager::add ( $e->getMessage(), \Kuink\Core\TraceCategory::ERROR, __CLASS__ );  	
			return 1;
    }

    return 0;
  }


  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaNameT($params) {
  	return null;
  }
}