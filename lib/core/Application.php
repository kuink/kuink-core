<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
namespace Kuink\Core;

/**
 * Enum values used for Url get params
 * 
 * @author ptavares
 *        
 */
class UrlParam {
	const PROCESS = 'startuc';
	const NODE = 'startnode';
	const EVENT = 'event';
	const ACTION = 'action';
	const ACTION_VALUE = 'actionvalue';
	const TRACE = 'trace';
	const ROLE = 'role';
	const DOC = 'doc';
	const MODAL = 'modal';
}

/**
 * Enum values used for nodeconfiguration keys
 * 
 * @author ptavares
 *        
 */
class NodeConfKey {
	const APPLICATION = 'customappname';
	const PROCESS = 'master_process_name';
	const NODE = 'startnode';
	const ACTION = 'action';
	const ACTION_VALUE = 'actionvalue';
	const EVENT = 'event';
	const BASEURL = 'baseurl';
	const CONFIG = 'config';
	const ROLES = 'roles';
	const CAPABILITIES = 'capabilities';
	const INSTANCE_CONFIG_RAW = 'instance_config_raw';
	const ACTION_PERMISSIONS = 'actionPermissions';
	const NODE_ROLES = 'nodeRoles';
	
	// Description of the referal node
	const REF_APPLICATION_DESC = 'REF_APPLICATION_DESC';
	const REF_PROCESS_DESC = 'REF_PROCESS_DESC';
	const REF_NODE_DESC = 'REF_NODE_DESC';
	const USER = 'USER';
	const SYSTEM = 'SYSTEM';
}

/**
 * Kuink application
 * 
 * @author ptavares
 *        
 */
class Application {
	private $name;
	private $lang; // Current user language
	private $config; // Application configuration from the application instance
	private $roles; // Roles of the user at a given moment
	private $capabilities; // Capabilities of the user at a given moment
	private $xmlDefinition; // object containing application.xml
	private $fwXmlDefinition; // object containing framework.xml
	private $defaultFlow; // Can be from menu or directly from instance
	private $isActive; // Is this application active?
	private $inMaintenance; // Is this application in maintenance?
	public $appManager; // Application manager
	public $nodeconfiguration;
	function __construct($name, $lang, $config) {
		global $KUINK_CFG;
		
		// If it's not a restart and there's an application in the stack, the use it
		// Get the application to execute from the top of process stack
		$reset = FALSE;
		if (isset ( $_GET ['reset'] ))
			$reset = ($_GET ['reset'] == 'true');
		
		$nameParts = explode ( ',', $name );
		$baseApplication = ( string ) $nameParts [0];
		$context = ProcessOrchestrator::prepareContext ( $baseApplication );
		
		$currentNode = ProcessOrchestrator::getCurrentNode ();
		if (! $reset) {
			$appName = (isset ( $currentNode ) && $currentNode->application != '') ? $currentNode->application : $name;
		} else {
			// Reset the stack;
			ProcessOrchestrator::clearContexts ();
			$appName = $name;
		}
		
		// The instance can define the application name as "application,process,event"
		// This way the default flow will not be taken from menu but from this name
		$nameParts = explode ( ',', $appName );
		$this->name = ( string ) $nameParts [0];
		if (isset ( $nameParts [1] ) && isset ( $nameParts [2] )) {
			$this->defaultFlow = new Flow ( $this->name, $nameParts [1], '', $nameParts [2] );
		}
		$this->lang = $lang;
		$this->config = $config;
		$this->roles = array ();
		$this->capabilities = array ();
		
		// Loads the config keys from instance
		$this->loadInstanceConfig ( $config );
		
		// Load framework.xml definiton
		$this->loadFrameworkDefinition ();
		
		if ($KUINK_CFG->useNewDataAccessInfrastructure)
			// Setup framework dataSources
			\Kuink\Core\DataSourceManager::setupFrameworkDS ( $this );
		else
			// Setup framework databases:: deprecated
			\Kuink\Core\DatabaseManager::setupFrameworkDB ( $this );
			
			// Load all applications data to appManager
		$this->appManager = new ApplicationManager ();
		$this->appManager->load ();
		// Check if this application exists
		if (! $this->appManager->applicationExists ( $this->name ))
			throw new \Exception ( 'Application ' . $this->name . ' does not exists or is not registered' );
			
			// Set the company id
		ProcessOrchestrator::setCompany ();
		
		if ($KUINK_CFG->useNewDataAccessInfrastructure)
			// Setup Company dataSources
			\Kuink\Core\DataSourceManager::setupCompanyDataSources ( ProcessOrchestrator::getCompany () );
			
			// Load application.xml now that we have the app base in apps dir
		$this->loadApplicationDefinition ();
		
		if ($KUINK_CFG->useNewDataAccessInfrastructure)
			// Setup framework dataSources
			\Kuink\Core\DataSourceManager::setupApplicationDS ( $this );
			
			// Loading language files
		Language::loadLanguageFiles ( $this->appManager, $this->name, $this->lang );
		
		// Check to see if this application is in maintenance mode or is not active
		$this->isActive = $this->appManager->getApplicationAttribute ( $this->name, 'is_active' );
		$this->inMaintenance = $this->appManager->getApplicationAttribute ( $this->name, 'in_maintenance' );
	}
	
	/**
	 * Gets $lang property
	 */
	public function getLang() {
		return $this->lang;
	}
	
	/**
	 * Gets the instance config property
	 */
	public function getInstanceConfig() {
		return $this->config;
	}
	
	/**
	 * Gets framework.xml object xml reference
	 */
	public function getFrameworkXml() {
		return $this->fwXmlDefinition;
	}
	
	/**
	 * Gets application.xml object xml reference
	 */
	public function getApplicationXml() {
		return $this->xmlDefinition;
	}
	
	/**
	 * Gets function params given a node and the function name
	 */
	public function getFunctionParams($node, $functionName) {
		$runtime = new Runtime ( $node, 'lib', null );
		
		$params = $runtime->getFunctionParams ( $functionName );
		
		$params = array ();
		return $params;
	}
	
	/**
	 * Loads the application xml definition file
	 */
	private function loadApplicationDefinition() {
		global $KUINK_CFG;
		
		$appBase = $this->appManager->getApplicationAttribute ( $this->name, 'app_base' );
		$appFileName = $KUINK_CFG->appRoot . 'apps/' . $appBase . '/' . $this->name . '/application.xml';
		
		libxml_use_internal_errors ( true );
		$this->xmlDefinition = simplexml_load_file ( $appFileName );
		$errors = libxml_get_errors ();
		if ($errors)
			throw new \Exception ( 'Cannot load application file ' . $appFileName );
	}
	
	/**
	 * Loads the application xml definition file
	 */
	private function loadFrameworkDefinition() {
		global $KUINK_CFG;
		
		$this->fwXmlDefinition = simplexml_load_file ( $KUINK_CFG->appRoot . 'apps/framework/framework.xml' );
	}
	
	/**
	 * Loads the configuration keys from the instance
	 * 
	 * @param unknown_type $config_raw        	
	 * @throws \Exception
	 */
	private function loadInstanceConfig($instanceConfigRaw) {
		global $KUINK_BRIDGE_CFG;
		
		$config = array ();
		if (trim ( $instanceConfigRaw ) != '') {
			libxml_use_internal_errors ( true );
			$instanceConfigXml = simplexml_load_string ( $instanceConfigRaw );
			$errors = libxml_get_errors ();
			if ($instanceConfigXml == null)
				throw new \Exception ( 'Cannot load instance configuration xml' );
			$instance_configs = $instanceConfigXml->xpath ( '/Configuration//Config' );
			foreach ( $instance_configs as $instance_config ) {
				$key = ( string ) $instance_config ['key'];
				$value = ( string ) $instance_config ['value'];
				$config [$key] = $value;
			}
			
			// load locally assigned roles
			$current_user_id = ($KUINK_BRIDGE_CFG->auth->user->id) ? ( string ) $KUINK_BRIDGE_CFG->auth->user->id : 0;
			$xpath_query = '/Configuration/Role[@user="' . ( string ) $current_user_id . '"]';
			$instance_roles = $instanceConfigXml->xpath ( $xpath_query );
			
			foreach ( $instance_roles as $role ) {
				$roleName = ( string ) $role ['name'];
				$this->addRole ( $roleName );
			}
		}
		unset ( $instanceConfigXml );
		
		$this->config = $config;
	}
	
	/**
	 * Loads the user roles from DB allocation tables
	 */
	private function loadRolesFromDB() {
		global $KUINK_BRIDGE_CFG, $KUINK_TRACE, $KUINK_CFG;
		
		$idCompany = ProcessOrchestrator::getCompany ();
		// Load the roles
		try {
			$idNumber = ($KUINK_BRIDGE_CFG->auth->user->id) ? ( string ) $KUINK_BRIDGE_CFG->auth->user->id : 0;
			$datasource = new \Kuink\Core\DataSource ( null, 'framework/framework,user,user.getRoles', 'framework', 'user' );
			$pars = array (
					'id_person' => $idNumber,
					'id_company' => $idCompany 
			);
			$alocs = $datasource->execute ( $pars );
			if (isset ( $alocs ))
				foreach ( $alocs as $aloc ) {
					if ($KUINK_CFG->useNewDataAccessInfrastructure)
						$this->addRole ( ( string ) $aloc ['code'] );
					else
						$this->addRole ( ( string ) $aloc->code );
				}
		} catch ( \Exception $exp ) {
			// var_dump( $exp );
			$KUINK_TRACE [] = 'Cannot load roles from user allocation tables...';
			// var_dump( $KUINK_TRACE );
			// throw new \Exception('Cannot load roles from user allocation tables...');
		}
		
		// Load capabilities
		// try {
		// $idNumber=($USER->idnumber) ? (string)$USER->idnumber : 0;
		// $datasource = new \Kuink\Core\DataSource( null, 'framework,user,user.getCapabilities','framework', 'user');
		// $pars=array( 'id_person'=>$idNumber, 'id_company' => $idCompany );
		// //TODO: change for the correct company id
		// $caps = $datasource->execute( $pars );
		// if (isset($caps))
		// foreach( $caps as $cap ) {
		// if ($KUINK_CFG->useNewDataAccessInfrastructure)
		// $this->addCapability( (string)$cap['code'] );
		// else
		// $this->addCapability( (string)$cap->code );
		// }
		// }
		// catch (\Exception $exp)
		// {
		// var_dump( $exp );
		// $KUINK_TRACE[] = 'Cannot load capabilities...';
		// //throw new \Exception('Cannot load capabilities...');
		// }
		// //var_dump( $this->capabilities );
		
		return;
	}
	
	/**
	 * Returns the default Flow object for the user current roles
	 */
	private function setDefaultFlow() {
		// var_dump($this->defaultFlow);
		if (isset ( $this->defaultFlow ))
			return;
			
			// Else get the default flow from the menu
		$flowdefault = array ();
		foreach ( $this->roles as $role => $value ) {
			$role_flow = $this->xmlDefinition->xpath ( '/Application/Menus//Menu[contains(@role, \'' . $role . '\') and not(@startuc=\'\') and (@default=\'true\')]' );
			if (! empty ( $role_flow ))
				$flowdefault [] = $role_flow;
		}
		// var_dump($flowdefault);
		if (count ( $flowdefault ) > 0)
			$this->defaultFlow = new Flow ( $this->name, ( string ) $flowdefault [0] [0] ['startuc'], '', ( string ) $flowdefault [0] [0] ['event'] );
	}
	
	/**
	 * Returns the application menu html
	 * @by Joao Patricio - Show the menu in topbar naviagtion
	 */
	public function getMenuHtml() {
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		
		$menu = array ();
		$get_role = isset ( $_GET ['role'] ) ? ( string ) $_GET ['role'] : '';
		// If user is admin then use the impersonate capability
		if (isset ( $this->roles ['framework.admin'] ))
			$currentrole = ($get_role == '') ? key ( $this->roles ) : $get_role;
			
			// Get the application top menu for the current role
		$flowMenu = $this->xmlDefinition->xpath ( '/Application/Menus/Menu[contains(@role, \'' . $currentrole . '\') and not(@startuc=\'\')]' );
		$menu = $this->makeMenuItems ( $flowMenu );
		
		// var_dump($menu);
		
		$layout->setAppMenu ( $menu );
	}
	function makeMenuItems($flowMenu) {
		global $KUINK_CFG;
		if ($flowMenu == null) { // if has no menu, stop the recursion. Just in case
			return false;
		}
		
		$menu = array ();
		$menuitemscount = count ( $flowMenu );
		$counter = 1;
		while ( list ( , $node ) = each ( $flowMenu ) ) {
			
			$templname = ($node ['template'] != '') ? $node ['template'] : 'default';
			
			/**
			 * if has children menus, then add it to attribute child *
			 */
			$childMenus = false;
			if ($node->xpath ( "./Menu" )) {
				$child = $node->xpath ( "./Menu" );
				if (isset ( $child ))
					$childMenus = $this->makeMenuItems ( $child ); // recursive instruction
			}
			
			// Checking user permission
			$roles = $this->roles;
			$hasRole = false;
			$menuRoles = ( string ) $node ['role'];
			foreach ( $roles as $role => $value )
				if (! (strpos ( $menuRoles, $role ) === false)) {
					$hasRole = true;
					break;
				}
			
			if ($hasRole) {
				$href = $KUINK_CFG->wwwRoot . '/' . $KUINK_CFG->kuinkRoot . '/view.php?id=' . $_GET ['id'] . '&idcontext=' . $_GET ['idcontext'] . '&startuc=' . $node ['startuc'] . '&startnode=' . $node ['startnode'] . '&event=' . $node ['event'] . '&trace=' . $_GET ['trace'];
				$hrefNoContext = ($KUINK_CFG->allowMultipleContexts) ? $KUINK_CFG->wwwRoot . '/' . $KUINK_CFG->kuinkRoot . '/view.php?id=' . $_GET ['id'] . '&idcontext=' . uniqid () . '&startuc=' . $node ['startuc'] . '&startnode=' . $node ['startnode'] . '&event=' . $node ['event'] . '&trace=' . $_GET ['trace'] : '';
				$menu [] = array (
						'label' => kuink_get_string ( ( string ) $node ['label'], $this->name ),
						'target' => ($this->target) ? kuink_get_string ( ( string ) $node ['target'], $this->target ) : '_self',
						'href' => $href,
						'hrefNoContext' => $hrefNoContext,
						'child' => $childMenus 
				);
			}
			
			if ($counter < $menuitemscount)
				$counter ++;
		}
		return $menu;
	}
	
	/**
	 * Displays the menu if config key HIDE_APP_MENU is false or not defined
	 */
	private function displayMenu() {
		$display_menu = isset ( $this->config ['HIDE_APP_MENU'] ) ? ! ($this->config ['HIDE_APP_MENU'] == 'true') : true;
		
		if ($display_menu)
			print ($this->getMenuHtml ()) ;
	}
	
	/**
	 * Add an external role to the user.
	 * If there are roles managed by the the external system, like moodle Teacher...
	 * 
	 * @param string $roleName        	
	 */
	function addRole($roleName) {
		$this->roles [$roleName] = 1;
	}
	
	/**
	 * Add an external role to the user.
	 * If there are roles managed by the the external system, like moodle Teacher...
	 * 
	 * @param string $roleName        	
	 */
	private function addCapability($capabilityName) {
		$this->capabilities [$capabilityName] = 1;
	}
	
	/**
	 * Runs the application.
	 * It will get all necessary params
	 */
	function run($node = null, $functionName = null, $function_params = null) {
		global $KUINK_CFG, $KUINK_BRIDGE_CFG, $SESSION, $KUINK_LAYOUT;
		
		$msgManager = \Kuink\Core\MessageManager::getInstance ();
		
		if (($this->isActive && ! $this->inMaintenance) || $this->isFwAdmin ()) {
			// Load user roles from allocation
			$this->loadRolesFromDB ();
			
			// Get the default flow
			$this->setDefaultFlow ();
			
			$runNode = ($node == null) ? ProcessOrchestrator::getNodeToExecute ( $this->roles, $this->defaultFlow ) : $node;
			
			$this->nodeconfiguration = $this->getNodeConfiguration ( $runNode );
			// var_dump($this->nodeconfiguration);
			// var_dump( $runNode );
			if ($functionName == null)
				$this->displayMenu ();
			$baseUrl = $KUINK_CFG->wwwRoot;
			if ($functionName == null) {
				$layout = \Kuink\UI\Layout\Layout::getInstance ();
				$layout->setBaseUrl ( $KUINK_CFG->wwwRoot );
				$layout->setLogOut ( $KUINK_BRIDGE_CFG->auth->user->firstName . ' ' . $KUINK_BRIDGE_CFG->auth->user->lastName, $KUINK_BRIDGE_CFG->auth->user->id, $KUINK_BRIDGE_CFG->auth->sessionKey );
			}
			$runtime = null;
			if ($functionName == null)
				$runtime = new Runtime ( $runNode, 'nodes', $this->nodeconfiguration );
			else
				$runtime = new Runtime ( $runNode, 'lib', $this->nodeconfiguration );
			
			if ($functionName == null)
				$result = $runtime->execute ();
			else // Execute directly a function
				$result = $runtime->execute ( $functionName, $function_params );
			
			while ( $runtime->eventRaised () ) {
				$eventRaisedName = $runtime->eventRaisedName ();
				$eventRaisedParams = $runtime->eventRaisedParams ();
				// $eventRaisedApp = $runtime->eventRaisedApplication();
				// $eventRaisedProcess = $runtime->eventRaisedProcess();
				// kuink_mydebug('event_raised', $eventRaisedName);
				
				// PMT::New ProcessOrchetrator
				$runNode = ProcessOrchestrator::getNodeToExecute ( $this->roles, null, $eventRaisedName );
				
				$this->nodeconfiguration = $this->getNodeConfiguration ( $runNode );
				$runtime = new Runtime ( $runNode, 'nodes', $this->nodeconfiguration, true, $eventRaisedParams );
				$result = $runtime->execute ();
			}
		} else {
			$msg = '';
			if (! $this->isActive)
				$msg = ( string ) kuink_get_string ( 'applicationIsInactive', 'framework', null );
			else
				$msg = ( string ) kuink_get_string ( 'applicationInMaintenance', 'framework', null );
			$msgManager->add ( \Kuink\Core\MessageType::ERROR, $msg );
		}
		
		if ((! $this->isActive || $this->inMaintenance) && $this->isFwAdmin ()) {
			$msg = '';
			if (! $this->isActive)
				$msg = ( string ) kuink_get_string ( 'applicationIsInactive', 'framework', null );
			else
				$msg = ( string ) kuink_get_string ( 'applicationInMaintenance', 'framework', null );
			$msgManager->add ( \Kuink\Core\MessageType::WARNING, $msg );
		}
		
		$msgManager->print_messages ();
		
		// Setting smarty company variables
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		$layout->setGlobalVariable ( '_userCompany', ProcessOrchestrator::getCompany () );
		$layout->setGlobalVariable ( '_userCompanies', ProcessOrchestrator::getCompanies () );
		
		return $result;
	}
	private function isFwAdmin() {
		return isset ( $this->roles ['framework.admin'] );
	}
	
	/**
	 * Get the framework configuration keys
	 */
	private function getFrameworkConfig() {
		$fw = $this->fwXmlDefinition;
		$configs = $fw->xpath ( '/Framework/Configuration//Config' );
		
		$fwConfig = null;
		
		foreach ( $configs as $config ) {
			$key = ( string ) $config ['key'];
			$value = ( string ) $config ['value'];
			$fwConfig [$key] = $value;
		}
		return $fwConfig;
	}
	
	/**
	 * Get the application configuration keys, overriding the framework configuration
	 * 
	 * @param array $fwConfig        	
	 * @return array
	 */
	private function getApplicationConfig($fwConfig) {
		$appConfigs = $this->xmlDefinition->xpath ( '/Application/Configuration//Config' );
		
		foreach ( $appConfigs as $appConfig ) {
			$key = ( string ) $appConfig ['key'];
			$value = ( string ) $appConfig ['value'];
			$fwConfig [$key] = $value;
		}
		return $fwConfig;
	}
	
	/**
	 * Builds a node configuration object to be passed to the runtime
	 * 
	 * @param Node $node        	
	 */
	private function getNodeConfiguration($node) {
		global $KUINK_CFG;
		$currentNode = ProcessOrchestrator::getCurrentNode ();
		
		$fwConfig = $this->getFrameworkConfig ();
		$fwConfig = $this->getApplicationConfig ( $fwConfig );
		
		$get_trace = isset ( $_GET [QueryStringParam::TRACE] ) ? ( string ) $_GET [QueryStringParam::TRACE] : '';
		$get_modal = isset ( $_GET [QueryStringParam::MODAL] ) ? ( string ) $_GET [QueryStringParam::MODAL] : 'false';
		$baseUrl = $KUINK_CFG->wwwRoot . '/' . $KUINK_CFG->kuinkRoot . '/view.php?id=' . $_GET [QueryStringParam::ID];
		$baseUrlParams = array (
				QueryStringParam::ID_CONTEXT => $_GET [QueryStringParam::ID_CONTEXT],
				QueryStringParam::TRACE => isset ( $_GET [QueryStringParam::TRACE] ) ? ( string ) $_GET [QueryStringParam::TRACE] : '',
				QueryStringParam::ACTION_VALUE => $currentNode->action_value,
				QueryStringParam::TRACE => $get_trace 
		);
		if ($get_modal != 'false' && $get_modal != 'widget')
			$baseUrlParams [QueryStringParam::MODAL] = $get_modal;
		
		$baseUrl = \Kuink\Core\Tools::setUrlParams ( $baseUrl, $baseUrlParams );
		
		$nodeconfiguration [NodeConfKey::APPLICATION] = $node->application;
		$nodeconfiguration [NodeConfKey::NODE] = $node->node;
		$nodeconfiguration [NodeConfKey::PROCESS] = $node->process;
		$nodeconfiguration [NodeConfKey::EVENT] = $currentNode->event;
		$nodeconfiguration [NodeConfKey::ACTION] = $currentNode->action;
		$nodeconfiguration [NodeConfKey::ACTION_VALUE] = $currentNode->actionValue;
		$nodeconfiguration [NodeConfKey::BASEURL] = $baseUrl;
		$nodeconfiguration [NodeConfKey::CONFIG] = $fwConfig;
		$nodeconfiguration [NodeConfKey::INSTANCE_CONFIG_RAW] = $this->config;
		$nodeconfiguration [NodeConfKey::ROLES] = $this->roles;
		$nodeconfiguration [NodeConfKey::CAPABILITIES] = $this->capabilities;
		$nodeconfiguration [NodeConfKey::REF_APPLICATION_DESC] = '';
		$nodeconfiguration [NodeConfKey::REF_PROCESS_DESC] = '';
		$nodeconfiguration [NodeConfKey::REF_NODE_DESC] = '';
		
		return $nodeconfiguration;
	}
}

?>