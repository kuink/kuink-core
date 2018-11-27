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

use Kuink\UI\Layout\Layout;
use Kuink\Core;
use Kuink\Core\Exception\ERROR_CODE;
use Kuink\Core\Exception\GenericException;


/**
 * Enum values used for Action types
 * 
 * @author ptavares
 *        
 */
class ActionType {
	const EXECUTE = 'execute';
	const WRITE = 'write';
	const READ = 'read';
}
class User {
	function getUser() {
		global $KUINK_CFG;
		//var_dump($KUINK_CFG->auth->user);
		if (isset($_SESSION['kuink.logged.user']))
			$kuinkUser = $_SESSION['kuink.logged.user'];
		else
			$kuinkUser = array ();	

		$currentNode = ProcessOrchestrator::getCurrentNode ();
		$rwx = isset($currentNode->rwx) ? $currentNode->rwx : null;
		$impersonate = isset($currentNode->idUserImpersonate) ? $currentNode->idUserImpersonate : null;
		$kuinkUser = array ();
		$kuinkUser ['rwx'] = $rwx;
		$kuinkUser ['isImpersonated'] = isset ( $impersonate ) ? 1 : 0;
		
		$idNumber = 0; // the guest id_number
		if ($KUINK_CFG->auth->user->id != null)
			$idNumber = $KUINK_CFG->auth->user->id;
			
			// Handle impersonated user
		if ($kuinkUser ['isImpersonated'])
			$kuinkUser ['id'] = $impersonate;
		else
			$kuinkUser ['id'] = isset ( $currentNode->idUser ) ? $currentNode->idUser : $idNumber;
			
			// get the idCompany
		$kuinkUser ['idCompany'] = ProcessOrchestrator::getCompany ();
		
		//var_dump($kuinkUser['id']);
		
		// Load the person from fw_person
		// Set the public key
		$personDa = new DataAccess ( 'framework,user,person.get', 'framework', 'user' );
		$personData = array (
				'id_person' => $kuinkUser ['id'],
				'id_company' => ProcessOrchestrator::getCompany () 
		);
		$person = $personDa->execute ( $personData );
		// var_dump($person);
		
		$kuinkUser ['idOriginal'] = isset ( $currentNode->idUser ) ? $currentNode->idUser : $idNumber;
		// get from database
		$kuinkUser ['uid'] = $person ['uid']; // $USER->username;
		$kuinkUser ['name'] = $person ['display_name']; // $USER->firstname . ' ' . $USER->lastname;
		$kuinkUser ['email'] = $person ['email']; // $USER->email;
		$kuinkUser ['publicKey'] = $person ['public_key']; // $USER->email;
		                                                 // $kuink_user['tipo'] = $current_user->tipo;
		$kuinkUser ['idExternal'] = $KUINK_CFG->auth->user->id; // $current_user->idexternal;
		$kuinkUser ['lang'] = $KUINK_CFG->auth->user->lang;
		
		// @todoSTI: Joao Patricio get this value from person table
		$kuinkUser ['timezone'] = $KUINK_CFG->defaultTimezone;
		// get the client ip address
		$kuinkUser ['ip'] = $_SERVER ["REMOTE_ADDR"];
		
		//var_dump($kuinkUser);
		return $kuinkUser;
	}
}
class Runtime {
	var $app_name;
	var $process_name;
	var $node_name;
	var $params;
	var $variables;
	var $libraries;
	var $current_controls;
	var $msg_manager;
	var $nodepath;
	var $nodeconfiguration;
	var $type;
	var $node_xml;
	var $nodeManager; // The object that loaded the node
	var $display; // Display the control or return just the html?
	var $event_raised; // Raised an event?
	var $event_raised_name; // Name of the event that is raised
	var $event_raised_params; // Raised Event params
	var $event_raised_app; // This event can be raised from another app if we are in a subprocess
	var $event_raised_process; // This event can be raised from another app,process if we are in a subprocess
	var $force_position; // Force all controls to this position
	var $show_trace; // Show trace
	var $show_source; // Should the source code be exported to the output?
	var $is_fw_admin; // This user is framework.admin?
	function __construct($node, $node_type, $nodeconfiguration, $display = true, $params = null) {
		global $KUINK_CFG, $SESSION, $KUINK_APPLICATION;
		
		$this->app_name = $node->application;
		$this->process_name = $node->process;
		$this->node_name = $node->node;
		
		$this->type = $node_type;
		// var_dump( $nodeconfiguration );
		
		$appBase = $KUINK_APPLICATION->appManager->getApplicationBase ( $node->application );
		
		$this->nodepath = $KUINK_CFG->appRoot . 'apps/' . $appBase . '/' . $node->application . '/process/' . $node->process . '/' . $node_type . '/' . $node->process . '_' . $node->node . '.xml';
		$this->nodeconfiguration = $nodeconfiguration;
		// replace appname with this one
		$this->nodeconfiguration [NodeConfKey::APPLICATION] = $node->application;
		$this->nodeconfiguration [NodeConfKey::PROCESS] = $node->process;
		// kuink_mydebug('nodepath', $this->nodepath);
		
		// var_dump($this->nodepath);
		// Display the control or return just the html?
		$this->display = $display;
		$this->force_position = '';
		
		// This variable will hold al the variables defines in node, if there are params, then they are initialized
		// $event_params = isset( $_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'] ) ? $_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'] : null;
		// var_dump('Antes');
		// $event_params = ProcessOrchestrator::getEventParams();
		$currentNode = ProcessOrchestrator::getCurrentNode ();
		// die();
		//print_r($currentNode->params);
		$this->params = isset($currentNode->params) ? $currentNode->params : null;
		if (isset($params) && is_array($params))
			foreach ( $params as $key => $value )
				$this->params [$key] = $value;
				
		// $this->params = empty( $params ) ? $currentNode->params : $params ;
		$this->variables [] = null;
		$this->libraries [] = null;
		
		// This object will hold the feedback user messages
		$this->msg_manager = \Kuink\Core\MessageManager::getInstance (); // \neon_msg_manager();
		
		$kuinkUser = new User ();
		$kuink_user = $kuinkUser->getUser ();
		
		// initialize kuink global variable USER
		$this->variables ['USER'] = $kuink_user;
		$this->nodeconfiguration [NodeConfKey::USER] = $kuink_user;
		
		// Insert the user variable in layout
		$layout = Layout::getInstance ();
		$layout->setGlobalVariable ( '_user', $kuink_user );
		
		// initialize kuink global variable SERVER
		$server_info ['name'] = $_SERVER ['SERVER_NAME'];
		$server_info ['ip'] = $_SERVER ['SERVER_ADDR'];
		$server_info ['port'] = $_SERVER ['SERVER_PORT'];
		$server_info ['userAgent'] = $_SERVER ['HTTP_USER_AGENT'];
		$server_info ['wwwRoot'] = $KUINK_CFG->wwwRoot;
		$server_info ['appRoot'] = $KUINK_CFG->appRoot;
		$server_info ['apiUrl'] = $KUINK_CFG->apiUrl;
		$server_info ['streamUrl'] = $KUINK_CFG->streamUrl;
		$server_info ['guestUrl'] = $KUINK_CFG->guestUrl;
		$server_info ['baseUploadDir'] = $KUINK_CFG->uploadRoot; //(isset($this->nodeconfiguration ) && isset($this->nodeconfiguration [NodeConfKey::CONFIG])) ? (string)$this->nodeconfiguration [NodeConfKey::CONFIG] ['uploadFolderBase'] : '';
		//$config = (isset($this->nodeconfiguration ) && isset($this->nodeconfiguration [NodeConfKey::CONFIG])) ? (string)($this->nodeconfiguration [NodeConfKey::CONFIG] ['uploadFolderBase']) : '';
		$server_info ['fullUploadDir'] = $KUINK_CFG->uploadRoot; //$KUINK_CFG->dataRoot . '/' . $config;
		$server_info ['environment'] = $KUINK_CFG->environment;
		
		$this->variables ['SYSTEM'] = $server_info;
		$this->nodeconfiguration [NodeConfKey::SYSTEM] = $server_info;
		
		$nodeContext ['application'] = isset($currentNode->application) ? $currentNode->application : null;
		$nodeContext ['process'] = isset($currentNode->process) ? $currentNode->process : null;
		$nodeContext ['node'] = isset($currentNode->node) ? $currentNode->node : null;
		$nodeContext ['action'] = isset($currentNode->action) ? $currentNode->action : null;
		$nodeContext ['idContext'] = ProcessOrchestrator::getContextId ();
		$nodeContext ['processGuid'] = isset($currentNode->nodeGuid) ? $currentNode->nodeGuid : null;
		$nodeContext ['nodeGuid'] = isset($currentNode->nodeGuid) ? $currentNode->nodeGuid : null;
		$nodeContext ['nid'] = (isset($currentNode) && $currentNode !== false) ? $currentNode->application . ',' . $currentNode->process . ',' . $currentNode->nodeGuid : ',,';
		$this->variables ['CONTEXT'] = $nodeContext;
		
		// kuink_mydebug('NODE', $node_name);
		
		$roles = isset($this->nodeconfiguration [NodeConfKey::ROLES]) ? $this->nodeconfiguration [NodeConfKey::ROLES] : null;
		$this->is_fw_admin = isset ( $roles ['framework.admin'] );
		
		return;
	}
	
	/**
	 * Build all role capabilities
	 * 
	 * @param $roles Node
	 *        	configuration roles
	 */
	public function buildAllCapabilities($idAcl=null, $aclCode=null, $force=false){
		Global $KUINK_CFG;
		
			$roles = isset($this->nodeconfiguration[NodeConfKey::ROLES]) ? $this->nodeconfiguration[NodeConfKey::ROLES] : null;
			$capabilities = isset($this->nodeconfiguration[NodeConfKey::CAPABILITIES]) ? $this->nodeconfiguration[NodeConfKey::CAPABILITIES] : null;
			
			//Only rebuild if the capabilites are not set
			if (count($capabilities) == 0 || $force) {
				//print_object('NEW...');
				if (count($roles)>0) {
					$rolesFilter = array();
					foreach ($roles as $roleName => $roleValue) {
						$rolesFilter[] = '\''.$roleName.'\'';
					}
					$rolesFilterStr = implode(',', $rolesFilter);
					//print_object($rolesFilterStr);
						
					if (!($KUINK_CFG->useGlobalACL))
						$this->buildCapabilitiesOfList($rolesFilterStr);
						
					if ($idAcl || $aclCode) {
						$this->buildCapabilitiesOfAcl($idAcl, $aclCode);
					}
				}
			}
	}


	/**
	 * Build capabilities from an access control list
	 * @param $idAcl - the acl identifier
	 */
	public function buildCapabilitiesOfAcl($idAcl, $aclCode){
		//print_object($idAcl);
		if ($idAcl || $aclCode){
			//get the value from fw_config
			$dataAccess = new \Kuink\Core\DataAccess('framework/framework,acl,getPermissions', 'framework', 'acl');
			$params['id'] = $idAcl;
			$params['code'] = $aclCode;			
			$params['id_person'] = $this->nodeconfiguration[NodeConfKey::USER]['id'];
			$params['id_company'] = $this->nodeconfiguration[NodeConfKey::USER]['idCompany'];
			$resultset = $dataAccess->execute($params);
			//print_object($resultset);
			if ($resultset){
				foreach ($resultset as $capability){
					$this->addCapability($capability);
				}
			}
			
			return $this->nodeconfiguration[NodeConfKey::CAPABILITIES];
		}
	}

	/**
	 * Build capabilities from a list of roles
	 * 
	 * @param $roles list
	 *        	of roles
	 */
	public function buildCapabilitiesOfList($roles) {
		if ($roles != '') {
			// get the value from fw_config
			$dataAccess = new \Kuink\Core\DataAccess ( 'framework/framework,user.role,getCapabilitiesOfList', 'framework', 'role' );
			$params ['role_codes'] = $roles;
			$resultset = $dataAccess->execute ( $params );
			if ($resultset) {
				foreach ( $resultset as $capability ) {
					$this->addCapability ( $capability );
				}
			}
			return isset($this->nodeconfiguration [NodeConfKey::CAPABILITIES]) ? $this->nodeconfiguration [NodeConfKey::CAPABILITIES] : array();
		}
	}
	
	/**
	 * Build capabilities from an access control list
	 * @param $idAcl - the acl identifier
	 */
	public function getAllRolesAcl($idAcl){
		//print_object($idAcl);
		$roles = null;
		if ($idAcl){
			//get the value from fw_config
			$dataAccess = new \Kuink\Core\DataAccess('framework/framework,acl,getRoles', 'framework', 'acl');
			$params['id'] = $idAcl;
			$params['id_person'] = $this->nodeconfiguration[NodeConfKey::USER]['id'];
			$resultset = $dataAccess->execute($params);
			$params['id_company'] = $this->nodeconfiguration[NodeConfKey::USER]['idCompany'];			
			//print_object($resultset);
			if ($resultset){
				foreach ($resultset as $role){
					$roles[$role['code']] = 1;
				}
			}
	
			return $roles;
		}
	}	
	
	/**
	 * Add capability to current nodeconfiguration
	 * 
	 * @param $capability Capability
	 *        	code
	 */
	private function addCapability($capability) {
		$this->nodeconfiguration [NodeConfKey::CAPABILITIES] [( string ) $capability ['code']] = $capability ['id'];
		return $capability;
	}
	public function forcePosition($position) {
		$this->force_position = $position;
	}
	
	/**
	 * Return if an event was raised
	 */
	public function eventRaised() {
		return $this->event_raised;
	}
	public function eventRaisedName() {
		return $this->event_raised_name;
	}
	public function eventRaisedParams() {
		return $this->event_raised_params;
	}
	public function eventRaisedApplication() {
		return $this->event_raised_app;
	}
	public function eventRaisedProcess() {
		return $this->event_raised_process;
	}
	public function getFunctionParams($function_name) {
		$params = array ();
		return $params;
	}
	
	// var_dump( $USER );
	
	// Can execute a node or a function
	// $exit - if the function execution runs Exit command this should be reported to the caller
	// The Exit command aborts the current action
	public function execute($function_name = null, $function_params = null, &$exit = null) {
		global $SESSION;
		global $KUINK_APPLICATION;
		global $KUINK_LAYOUT;

		// Registering global apis
		// This shouldn't be here
		\Kuink\Core\ProcessOrchestrator::registerAPI ( 'framework,ticket,api,add' );
		\Kuink\Core\ProcessOrchestrator::registerAPI ( 'framework,ticket,api,getHandlers' );
		// Clear event control variables
		$this->event_raised = false;
		$this->event_raised_name = '';
		$this->event_raised_params = array ();
		$this->event_raised_app = $this->app_name;
		$this->event_raised_process = $this->process_name;
		
		// Getting current action settings
		$actionname = isset($this->nodeconfiguration [NodeConfKey::ACTION]) ? $this->nodeconfiguration [NodeConfKey::ACTION] : null;
		$actionvalue = isset($this->nodeconfiguration [NodeConfKey::ACTION_VALUE]) ? $this->nodeconfiguration [NodeConfKey::ACTION_VALUE] : null;
		
		// kuink_mydebug('$actionname', $actionname);
		// kuink_mydebug('$actionvalue', $actionvalue);
		
		// $baseurl = cur_page_url();
		$baseurl = Tools::getPageUrl ();
		
		$get_trace = isset ( $_GET [UrlParam::TRACE] ) ? ( string ) $_GET [UrlParam::TRACE] : '';
		$get_role = isset ( $_GET [UrlParam::ROLE] ) ? ( string ) $_GET [UrlParam::ROLE] : '';
		$get_doc = isset ( $_GET [UrlParam::DOC] ) ? ( string ) $_GET [UrlParam::DOC] : '';
		
		$roles = isset($this->nodeconfiguration [NodeConfKey::ROLES]) ?  $this->nodeconfiguration [NodeConfKey::ROLES] : null;
		// var_dump($roles);
		
		$showtrace = $get_trace;
		$impersonate_role = $get_role;
		
		$nodexml = null;
		$html = ''; // html to return if this is a ui
		
		try {
			// Load the Xml node
			$nodeManager = new NodeManager ( $this->app_name, $this->process_name, $this->type, $this->node_name );
			$nodeManager->load ( false, false ); // Don't validate the xml neither the schema
			$nodexml = $nodeManager->nodeXml;
			
			// libxml_use_internal_errors( true );
			// $nodexml = simplexml_load_file($this->nodepath, 'SimpleXMLElement', LIBXML_NOCDATA);
			// $errors = libxml_get_errors();
			
			$this->node_xml = $nodexml;
			if ($nodexml == null)
				throw new \Exception ( 'Cannot load node: ' . $this->nodepath );
				
				// Handle node schema xsd validation if needed
			$validateSchema = $this->get_inst_attr ( $nodexml, 'validate', null, false, 'false' );
			if ($validateSchema == 'true') {
				// Forcing the schema validation. Use the new NodeManager class. Refactor the other code
				$nodeManager = new NodeManager ( $this->app_name, $this->process_name, $this->type, $this->node_name );
				$nodeManager->load ( true, true ); // validate the xml
			}
			$this->nodeManager = $nodeManager;
			
			// Get node attributes
			// Set event params if they exist, in the EVENT_PARAMS general variable
			$this->variables ['EVENT_PARAMS'] = ProcessOrchestrator::getEventParams (); // isset($_SESSION['KUINK_CONTEXT']['EVENT_PARAMS']) ? $_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'] : null;
	
			/**
			 * ***************************************
			 */
			// Building the parameters
			$node_params = array ();
			$params_xml = $nodexml->xpath ( '/Node/Params//Param' );
			// var_dump( $_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'][$param_name] );
			foreach ( $params_xml as $param ) {
				$param_name = ( string ) $param ['name'];
				// Get the value that coulb be from an instruction
				$exit = false;
				if ($param->count () > 0)
					foreach ( $param->children () as $instruction )
						$param_value = $this->instruction_execute ( $this->nodeconfiguration, $nodexml, null, $instruction, '', $this->variables, $exit );
				else
					$param_value = ( string ) $param [0];
				
				$eventParams = ProcessOrchestrator::getEventParams ();
				
				// if (isset($_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'][$param_name]))
				if (isset ( $eventParams [$param_name] ))
					$node_params [$param_name] = $eventParams [$param_name]; // $_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'][$param_name];
				else
					$node_params [$param_name] = $param_value;
			}

			/**
			 * ***************************************
			 */
			
			// Get the reference of the node or the subprocess - Get it from the EVENT_PARAMS variable if it starts with $
			$node_reference = $this->get_inst_attr ( $nodexml, 'reference', $this->variables ['EVENT_PARAMS'], false );
			
			// Check to see if the source code should be displayed or not
			// The code will be injected in the screen and actions
			$this->show_source = $this->get_inst_attr ( $nodexml, 'showsource', $this->variables ['EVENT_PARAMS'], false, 'false' );
			
			// Get the event to start the subprocess - Get it from the EVENT_PARAMS variable if it starts with $
			$node_event = $this->get_inst_attr ( $nodexml, 'event', $this->variables ['EVENT_PARAMS'], false, '' );
			
			 //kuink_mydebug('reference', $node_reference);
			
			// Check if this node is a reference to an existing node or is a subprocess
			if ($node_reference != '') {
				// reference: contains the reference node or the reference process
				// event: in the case os a reference process, this is the start event
				$node_parts = explode ( ',', $node_reference );
				if (count ( $node_parts ) != 3 && (count ( $node_parts ) != 2 && $node_event != ''))
					throw new \Exception ( 'ERROR: node source name must be appname,processname,nodename or appname,processname if event is set' );
					
					// Getting the reference application, process and node
				$refnodeappname = trim ( $node_parts [0] );
				$refnodeappname = ($refnodeappname == 'this') ? $this->app_name : $refnodeappname;
				$refnodeprocessname = trim ( $node_parts [1] );
				$refnodeprocessname = ($refnodeprocessname == 'this') ? $this->process_name : $refnodeprocessname;
				$refnodename = isset ( $node_parts [2] ) ? trim ( $node_parts [2] ) : '';
				
				// kuink_mydebug($refnodeappname, $refnodeprocessname.'->'.$node_event);
				
				$custumapp_name = ( string ) $this->nodeconfiguration [NodeConfKey::APPLICATION];
				$process_name = ( string ) $this->nodeconfiguration [NodeConfKey::PROCESS];
				$node_name = ( string ) $this->nodeconfiguration [NodeConfKey::NODE];
				
				$nodeconfiguration = $this->nodeconfiguration;
				$nodeconfiguration [NodeConfKey::REF_APPLICATION_DESC] = kuink_get_string ( $custumapp_name, $custumapp_name );
				$nodeconfiguration [NodeConfKey::REF_PROCESS_DESC] = kuink_get_string ( $process_name, $custumapp_name );
				$node_local_desc = kuink_get_string ( $node_name, $custumapp_name );
				$node_desc = ($node_local_desc == $node_name) ? '' : $node_local_desc;
				$nodeconfiguration [NodeConfKey::REF_NODE_DESC] = $node_desc;
				
				// If this is a reference node, execute the reference node directly
				if ($node_event == '') {
					// kuink_mydebug('RefNode', $node_reference);
					$ref_node = new Node ( $refnodeappname, $refnodeprocessname, $refnodename );
				} else {
					// else if this is a process reference, process the event
					// print('SubProcess :: '. $node_reference);
					// die();
					$node_event = ($node_event == '') ? 'init' : $node_event;
					$force_flow = new \Kuink\Core\Flow ( $refnodeappname, $refnodeprocessname, '', $node_event );
					// var_dump( $force_flow );
					
					// $ref_node = \Kuink\Core\ProcessOrchestrator::getNodeToExecute($refnodeappname, $nodeconfiguration[NodeConfKey::ROLES], null, null, $force_flow);
					$ref_node = ProcessOrchestrator::getNodeToExecute ( $nodeconfiguration [NodeConfKey::ROLES], null, null, $force_flow );
					// var_dump($node_event);
					
					$nodeconfiguration [NodeConfKey::APPLICATION] = $ref_node->application;
					$nodeconfiguration [NodeConfKey::PROCESS] = $ref_node->process;
					$nodeconfiguration [NodeConfKey::NODE] = $ref_node->node;
					$params_aux = array (
							// 'startuc' => $ref_node->process,
							// 'startnode' => $ref_node->node,
							// 'event' => '',
							// 'event' => $node_event,
							'action' => '',
							'actionvalue' => '' 
					);
					$nodeconfiguration [NodeConfKey::BASEURL] = Tools::setUrlParams ( $nodeconfiguration [NodeConfKey::BASEURL], $params_aux );
					// var_dump( $nodeconfiguration );
				}
				$ref_runtime = new Runtime ( $ref_node, $this->type, $nodeconfiguration, true, $node_params );
				
				// Handle events directly
				$html = $ref_runtime->execute ();
				$this->event_raised = $ref_runtime->eventRaised ();
				$this->event_raised_name = $ref_runtime->eventRaisedName ();
				$this->event_raised_params = $ref_runtime->eventRaisedParams ();
				// In this case the subprocess can be from another application, set the proper values
				$this->event_raised_app = $nodeconfiguration [NodeConfKey::APPLICATION];
				$this->event_raised_process = $nodeconfiguration [NodeConfKey::PROCESS];
				
				return $html;
			}
			
			// load node and instance configuration
			$config = isset($this->nodeconfiguration [NodeConfKey::CONFIG]) ? $this->nodeconfiguration [NodeConfKey::CONFIG] : null;
			$node_configs = $nodexml->xpath ( '/Node/Configuration//Config' );
			foreach ( $node_configs as $node_config ) {
				$key = ( string ) $node_config ['key'];
				$value = ( string ) $node_config ['value'];
				$config [$key] = $value;
			}
			
			// Load instance xml
			$instance_configs = $KUINK_APPLICATION->getInstanceConfig ();
			// var_dump( $instance_configs );
			foreach ( $instance_configs as $key => $value ) {
				$config [$key] = $value;
			}
			
			// Add global config
			$config ['APPLICATION'] = ( string ) $this->nodeconfiguration [NodeConfKey::APPLICATION];
			$config ['PROCESS'] = isset($this->nodeconfiguration [NodeConfKey::PROCESS]) ?  ( string ) $this->nodeconfiguration [NodeConfKey::PROCESS] : null;
			$config ['NODE'] = isset($this->nodeconfiguration [NodeConfKey::NODE]) ? ( string ) $this->nodeconfiguration [NodeConfKey::NODE] : null;
			$config ['ACTION'] = isset($this->nodeconfiguration [NodeConfKey::ACTION]) ? ( string ) $this->nodeconfiguration [NodeConfKey::ACTION] : null;
			$this->nodeconfiguration [NodeConfKey::CONFIG] = $config;
			
			// Set parameters if they exist
			$node_params = array ();
			$params_xml = $nodexml->xpath ( '/Node/Params//Param' );
			// var_dump($params_xml);
			foreach ( $params_xml as $param ) {
				$param_name = ( string ) $param ['name'];
				$exit = false;
				if ($param->count () > 0)
					foreach ( $param->children () as $instruction )
						$param_value = $this->instruction_execute ( $this->nodeconfiguration, $nodexml, null, $instruction, '', $this->variables, $exit );
				else
					$param_value = ( string ) $param [0];
					// kuink_mydebug($param_name, $param_value);
				$node_params [$param_name] = isset ( $this->params [$param_name] ) ? $this->params [$param_name] : $param_value;
			}
			$this->variables ['PARAMS'] = $node_params;
			// var_dump($node_params);
			
			// import libraries
			$libs = $nodexml->xpath ( '//Use' );
			foreach ( $libs as $use ) {
				$libtype = ( string ) $use ['type'];
				$libname = ( string ) $use ['name'];
				$managername = '\\' . $libname;
				
				// Bypass formatters and controls
				if ($libtype == 'lib') {
					$manager = new $managername ( $this->nodeconfiguration, \Kuink\Core\MessageManager::getInstance () );
					
					if (isset ( $manager ))
						$this->libraries [$libname] = $manager;
					else
						throw new \Exception ( 'Cannot load ' . $libname );
				}
			}
			// map node roles
			$node_roles = $this->getNodeRoles ( $roles, $nodexml );
			
			// if (isadmin() && $impersonate_role)
			if ($impersonate_role) {
				foreach ( $node_roles as $key => $value )
					if ($key == $impersonate_role)
						$node_roles [$key] = 'true';
					else
						$node_roles [$key] = 'false';
			}
			// var_dump($node_roles);
			
			// global $SESSION, $OUTPUT, $USER, $DB;
			$custumapp_name = ( string ) $this->nodeconfiguration [NodeConfKey::APPLICATION];
			$process_name = ( string ) $this->nodeconfiguration [NodeConfKey::PROCESS];
			$node = isset($this->nodeconfiguration [NodeConfKey::NODE]) ? ( string ) $this->nodeconfiguration [NodeConfKey::NODE] : null;
			
			// Add the roles stored in session:
			$new_roles = isset($this->nodeconfiguration [NodeConfKey::ROLES]) ? $this->nodeconfiguration [NodeConfKey::ROLES] : null;
			global $SESSION, $KUINK_CFG;
			
			$currentStackRoles = ProcessOrchestrator::getNodeRoles ();
			
			if (isset ( $currentStackRoles ))
				foreach ( $currentStackRoles as $role => $value ) {
					$node_roles [$role] = $value;
					$new_roles [$role] = $value;
				}
				// var_dump( $node_roles );
			$this->nodeconfiguration [NodeConfKey::NODE_ROLES] = $node_roles;
			$this->nodeconfiguration [NodeConfKey::ROLES] = $new_roles;
			if ($KUINK_CFG->useGlobalACL)
				$this->buildAllCapabilities(null, '_global');
			else
				$this->buildAllCapabilities();

			$this->variables['ROLES'] = isset($this->nodeconfiguration[NodeConfKey::ROLES]) ? $this->nodeconfiguration[NodeConfKey::ROLES] : null;
			$this->variables['CAPABILITIES'] = isset($this->nodeconfiguration[NodeConfKey::CAPABILITIES]) ? $this->nodeconfiguration[NodeConfKey::CAPABILITIES] : null;			
			$action_permissions = $this->getActionPermissions ( $nodexml );
			$this->nodeconfiguration [NodeConfKey::ACTION_PERMISSIONS] = $action_permissions;
			$actionname = isset($this->nodeconfiguration [NodeConfKey::ACTION]) ? $this->nodeconfiguration [NodeConfKey::ACTION] : null;
			// Execute the current action, if there's no current action execute the default action init
			$actionname = ($actionname != '') ? $actionname : 'init';
			// kuink_mydebug('$actionname', $actionname);
			
			// var_dump( $nodexml );
			// Get the action definition xml node

			if ($function_name) {
				// Execute directly a function
				// kuink_mydebug('Executing Function::'.$function_name);
				$action_xmlnode = null;
				$exit = 0;
				$value = $this->function_execute ( $this->nodeconfiguration, $nodexml, $action_xmlnode, $function_name, $this->variables, $exit, $function_params );
				return $value;
			} else {
				// Execute this action
				//kuink_mydebug('Executing action:',$actionname);

				$action_xmlnode = $this->action_get_xmlnode ( $this->nodeconfiguration, $nodexml, $actionname );
				$html = $this->action_execute ( $this->nodeconfiguration, $nodexml, $action_xmlnode, $actionname, $this->variables );
				// var_dump($this->nodeconfiguration);
			}
		} catch(\Error $e) {
			//TODO: - Set a new entry automatically in bugtracking tool
			
			// - Resgister user, timestamp, application, process, node, action, variables, instruction, executionstack?!?
			if ($function_name) {
				//throw new \Exception($e->getMessage());
				throw $e;
			} else {
				$msg_manager = \Kuink\Core\MessageManager::getInstance();
				$msg_manager->add(MessageType::EXCEPTION,'Exception:: '. $e->getMessage());
			}
			//Rollback transactions
			//global $KUINK_TRACE;
			//print_object($KUINK_TRACE);
			//die();			
			//print_object($e->getMessage());
			\Kuink\Core\DataSourceManager::rollbackTransaction();
				
		}
		catch(\Exception $e) {
			//TODO: - Set a new entry automatically in bugtracking tool

			// - Resgister user, timestamp, application, process, node, action, variables, instruction, executionstack?!?
		      if ($function_name) {
			      //throw new \Exception($e->getMessage());
		      		throw $e;
		  		} else {
		      	$msg_manager = \Kuink\Core\MessageManager::getInstance();
		        $msg_manager->add(MessageType::EXCEPTION,'Exception:: '. $e->getMessage());
		      }
					//Rollback transactions
					//print_object($e->getMessage());
		      \Kuink\Core\DataSourceManager::rollbackTransaction();
		}
		
		if ($this->event_raised) {
			// kuink_mydebug(__METHOD__,'Event Raised: '.$this->event_raised_name);
			return '';
		}
		
		$this->show_documentation ( $get_doc );
		
		// Framework admin have trace by default. Joao Patricio 2014-03-21
		if ($this->is_fw_admin) {
			$showtrace = true;
		}
		$this->show_trace ( $showtrace );
		
		return $html;
	}
	function show_documentation($show_documentation) {
		if ($show_documentation == 'true' && $this->type == 'nodes') {
			// var_dump($this->nodeconfiguration);
			// print('<img src="apps/'.$this->nodeconfiguration['customappname'].'/process/'.$this->nodeconfiguration['master_process_name'].'/process.png"/>');
		}
	}
	function show_trace($show_trace) {
		global $SESSION, $KUINK_TRACE, $KUINK_MANUAL_TRACE;
		
		if (1==1) {
		//if ($show_trace == 'true' && $this->type == 'nodes' && $this->is_fw_admin) {
			$html = '<div class="container-fluid"><div class="row-fluid"><div class="span12">';
			$html .= '<h4><i class="fa fa-wrench fa-2x" ></i> Developer Tracing Tools</h4>';
			$html .= '<br/>Manual Trace » ';
			$html .= '<a href="javascript:;" onmousedown="if(document.getElementById(\'manualTrace\').style.display == \'none\'){ document.getElementById(\'manualTrace\').style.display = \'block\'; }else{ document.getElementById(\'manualTrace\').style.display = \'none\'; }">Show/Hide</a><br/> ';
			
			if (count ( $KUINK_MANUAL_TRACE ) == 0)
				$html_display = 'none';
			else
				$html_display = 'block';
			
			$html .= '<div id="manualTrace" style="display:' . $html_display . '">';
			// var_dump($this->nodeconfiguration);
			$html .= '<pre class="pre-scrollable">';
			// var_dump( $KUINK_TRACE );
			if (isset($KUINK_MANUAL_TRACE))
				foreach ( $KUINK_MANUAL_TRACE as $key => $value ) {
					$dump = '';
					if (is_string($value))
						$dump = $value;
					else
						$dump = var_export($value, true);				
					$html .= $dump; //'<p>' . $key . ' => ' . $dump . '</p>';
				}
			$html .= '</pre></div>';
			
			$html .= '<br/>Process Stack » ';
			$html .= '<a href="javascript:;" onmousedown="if(document.getElementById(\'stack\').style.display == \'none\'){ document.getElementById(\'stack\').style.display = \'block\'; }else{ document.getElementById(\'stack\').style.display = \'none\'; }">Show/Hide</a><br/> ';
			$html .= '<div id="stack" style="display:none">';
			$html .= ProcessOrchestrator::getContextStackHtml ( false );
			$html .= '</div>';
			
			$html .= '<br/>Node Configuration » ';
			$html .= '<a href="javascript:;" onmousedown="if(document.getElementById(\'nodeconf\').style.display == \'none\'){ document.getElementById(\'nodeconf\').style.display = \'block\'; }else{ document.getElementById(\'nodeconf\').style.display = \'none\'; }">Show/Hide</a><br/> ';
			$html .= '<div id="nodeconf" style="display:none">';
			$html .= '<pre class="pre-scrollable"><p>' . var_export ( $this->nodeconfiguration, true ) . '</p></pre>';
			$html .= '</div>';
			
			$html .= "<br/>Trace » ";
			$html .= '<a href="javascript:;" onmousedown="if(document.getElementById(\'trace\').style.display == \'none\'){ document.getElementById(\'trace\').style.display = \'block\'; }else{ document.getElementById(\'trace\').style.display = \'none\'; }">Show/Hide</a><br/> ';
			$html .= '<div id="trace" style="display:none">';
			$html .= '<pre class="pre-scrollable">';
			//var_dump( $KUINK_TRACE );
			foreach ( $KUINK_TRACE as $key => $value ) {
				//if (is_string($value))
				//var_dump($value);
				$dump = '';
				if (is_string($value))
					$dump = $value;
				else
					$dump = var_export($value, true);

				$html .= '<pre>' . $key . ' => ' . $dump . '</pre>';
			}
			$html .= '</pre>';
			$html .= '</div>';
			
			
			$html .= "<br/>Variables » ";
			$html .= '<a href="javascript:;" onmousedown="if(document.getElementById(\'vars\').style.display == \'none\'){ document.getElementById(\'vars\').style.display = \'block\'; }else{ document.getElementById(\'vars\').style.display = \'none\'; }">Show/Hide</a><br/> ';
			$html .= '<div id="vars" style="display:none">';
			$html .= '<pre class="pre-scrollable"><p>' . var_export ( $this->variables, true ) . '</p></pre>';
			$html .= '</div>';
			
			
			$html .= '<br/>Session Variables » ';
			$html .= '<a href="javascript:;" onmousedown="if(document.getElementById(\'sess\').style.display == \'none\'){ document.getElementById(\'sess\').style.display = \'block\'; }else{ document.getElementById(\'sess\').style.display = \'none\'; }">Show/Hide</a><br/> ';
			$html .= '<div id="sess" style="display:none">';
			$html .= '<pre class="pre-scrollable"><p>' . var_export ( $_SESSION ['KUINK_CONTEXT'], true ) . '</p></pre>';
			$html .= '</div>';
			

			$html .= '</div></div></div>';
			
			$layout = \Kuink\UI\Layout\Layout::getInstance ();
			$layout->addHtml ( $html, 'trace' );
			
			// var_dump($_POST);
			// print('FIM');
		}
	}
	
	/**
	 * Show admin menu in topbar
	 * @Last-Change: Joao Patricio
	 * 
	 * @param type $impersonate_role        	
	 * @param type $node_roles        	
	 */
	function show_admin_header($impersonate_role, $node_roles) {
		
		// var_dump($node_roles);
		$roles = $this->nodeconfiguration [NodeConfKey::ROLES];
		
		// $baseurl = cur_page_url();
		$baseurl = Tools::getPageUrl ();
		
		if ($this->is_fw_admin && $this->type == 'nodes') {
			$menu = array ();
			
			// kuink_mydebug('BaseURL:',$baseurl);
			$baseurl = str_replace ( '&role=' . $impersonate_role, '', $baseurl );
			
			// print('<div align="right"><img src="pix/icon_themes/standard/bug.png" height="25"/> <a href="#">'.kuink_get_string('report_bug').'</a> </div>');
			$menu [] = array (
					"label" => kuink_get_string ( 'report_bug' ),
					"href" => "#" 
			);
			
			// print('<div align="right"><a href="'.$baseurl.'&trace=true">Trace</a>&nbsp;|&nbsp;<a href="'.$baseurl.'&doc=true">Documentation</a></div>');
			$menu [] = array (
					"label" => "Trace",
					"href" => $baseurl . '&trace=true' 
			);
			
			// print('<div align="right">');
			
			/*
			 * $curr_roles ='';
			 * if ($impersonate_role)
			 * $curr_roles = $impersonate_role;
			 * else
			 * foreach($node_roles as $key=>$value)
			 * if ($value == 'true')
			 * $curr_roles .= $curr_roles.$key.'&nbsp;';
			 */
			// print('Role actual: ['.$curr_roles.']&nbsp;');
			// print('</div>');
			// print('<div align="right">');
			// foreach($node_roles as $key=>$value)
			// {
			
			// print('<a href="'.$baseurl.'&role='.$key.'">'.$key.'</a>');
			// print('&nbsp;|&nbsp;');
			// }
			// $baseurl = str_replace('&role='.$impersonate_role,'',$baseurl);
			// print('<a href="'.$baseurl.'">Retomar</a>');
			// print('<p>'.$this->nodeconfiguration['startnode'].'</p>');
			// print('</div>');
			$layout = \Kuink\UI\Layout\Layout::getInstance ();
			$layout->setAdminMenu ( $menu );
		}
	}
	function getActionPermissions($node_xml) {
		// var_dump($node_roles);
		
		// Get all actions
		$action_permissions = array ();
		$actions = $node_xml->xpath ( '/Node/Actions/Action' );
		
		// Get current rwx permissions
		// $stack = ProcessOrchestrator::getCurrentStack();
		// $currentRWX = $stack->rwx;
		
		$stack = ProcessOrchestrator::getCurrentNode ();
		$currentRWX = isset($stack->rwx) ? $stack->rwx : null;
		
		// var_dump($actions);
		foreach ( $actions as $action ) {
			$action_name = ( string ) $action ['name'];
			$action_type = isset ( $action ['type'] ) ? ( string ) $action ['type'] : ActionType::READ; // defaults to read to backwards compatibility
			
			$has_permission = 0;
			// print ($has_permission.'::'.$currentRWX.'::'.$action_type.'::'.(($currentRWX & 4) == 4).'</br>');
			// print($action_name.'::'.$has_permission.'<br/>');
			// Check action permissions on roles and capabilities
			$permissions = $node_xml->xpath ( '/Node/Actions/Action[@name="' . $action_name . '"]/Permissions' );
			// var_dump( $permissions );
			if (count ( $permissions ) == 0)
				$has_permission += 1;
			else
				foreach ( $permissions as $permission )
					$has_permission += $this->hasPermissions ( $permission );
			
			if ($has_permission > 0) {
				// Now apply RWX permissions only if it allready has permissions in Roles|Capabilities
				switch ($action_type) {
					case ActionType::READ :
						// print(' VIEW');
						if ((($currentRWX & 4) == 4) || (($currentRWX & 2) == 2) || (($currentRWX & 1) == 1))
							$has_permission += 1;
						else
							$has_permission = - 1000;
						break;
					case ActionType::WRITE :
						// print(' ADD');
						if ((($currentRWX & 2) == 2) || (($currentRWX & 1) == 1))
							$has_permission += 1;
						else
							$has_permission = - 1000;
						break;
					case ActionType::EXECUTE :
						// print(' EXECUTE');
						if (($currentRWX & 1) == 1)
							$has_permission += 1;
						else
							$has_permission = - 1000;
						break;
					default :
						throw new \Exception ( 'Invalid action type:' . $action_type . ' for action:' . $action_name );
				}
			}
			
			// print($action_name.'::'.$has_permission.'<br/>');
			// Check action permissions on roles and capabilities
			/*
			 * $roles = $node_xml->xpath('/Node/Actions/Action[@name="'.$action_name.'"]/Permissions/Allow//Role');
			 * if (!$roles || count($roles) == 0) //default users can do the action
			 * $has_permission += 1;
			 * foreach ($roles as $role)
			 * {
			 * $role_name = (string)$role['name'];
			 * //print($role_name);
			 * if (isset($node_roles[$role_name]) && ($node_roles[$role_name] == 'true'))
			 * $has_permission += 1;
			 * }
			 *
			 * //Check action permissions on capabilities
			 * $capabilities = $node_xml->xpath('/Node/Actions/Action[@name="'.$action_name.'"]/Permissions/Allow//Capability');
			 * foreach ($capabilities as $capability)
			 * {
			 * $capability_name = (string)$capability['name'];
			 *
			 * if (isset($node_capabilities[$capability_name]) && ($node_capabilities[$capability_name] == 'true'))
			 * $has_permission += 1;
			 * }
			 * /*
			 */
			$action_permissions [$action_name] = ($has_permission > 0);
		}
		return $action_permissions;
	}
	function setRolesAndCapabilities($roles, $capabilities) {
		$this->nodeconfiguration[NodeConfKey::ROLES] = $roles;
		$this->nodeconfiguration[NodeConfKey::CAPABILITIES] = $capabilities;
	}
	function getNodeRoles($roles, $nodexml) {
		$perms = $nodexml->xpath ( '/Node/Permissions//Role' );
		$noderoles = array ();
		if (is_array($roles) || is_object($roles))
		foreach ( $roles as $role ) {
			$noderoles [( string ) $role] = 'true';
		}
		// var_dump( $perms );
		foreach ( $perms as $perm ) {
			$role_name = ( string ) $perm ['name'];
			if (count ( $perm->children () ) == 0) {
				$role_in_roles = in_array ( $role_name, $roles );
				// print($role_in_roles);
				// kuink_mydebug($role_name, $role_in_roles);
				// var_dump($roles);
				$noderoles [$role_name] = ($role_in_roles) ? 'true' : 'false';
			} else
				foreach ( $perm->children () as $role ) {
					$alloc_role = ( string ) $role ['role'];
					$alloc_unit = ( string ) $role ['unit'];
					if ($alloc_unit == null) {
						$role_in_roles = in_array ( $alloc_role, $roles );
						// print($role_in_roles);
						$noderoles [$role_name] = ($role_in_roles) ? 'true' : 'false';
					} else
						; // TODO get user allocation on unit
				}
		}
		// var_dump($noderoles);
		return $noderoles;
	}
	
	// gets the action xml node
	function action_get_xmlnode($nodeconfiguration, $nodexml, $actionname) {
		$action_xmlnode = $nodexml->xpath ( '//Node/Actions/Action[@name="' . $actionname . '"]' );
		if ($action_xmlnode == null)
			throw new \Exception ( "Action: $actionname not defined in the curent node." );
			// var_dump($action_xmlnode);
		return $action_xmlnode;
	}
	function action_execute(&$nodeconfiguration, $nodexml, $action_xmlnode, $actionname, &$variables) {
		global $KUINK_TRACE;
		global $KUINK_MANUAL_TRACE;
		global $KUINK_CFG;
		//kuink_mydebug('Executing action', $actionname);
		
		$context = ProcessOrchestrator::getContext ();
		
		$variables = $this->variables;
		$libraries = $this->libraries;
		
		$transaction = isset ( $action_xmlnode ['transaction'] ) ? ( string ) $action_xmlnode ['transaction'] : '';
		$KUINK_TRACE [] = "Action Name: " . $actionname;
		$KUINK_TRACE [] = "Action Transaction: " . $transaction;
		// kuink_mydebug('DoAction',$actionname);
		
		// Check if this is a screen action
		$screen_name = ( string ) $action_xmlnode [0] ['screen'];
		// var_dump( $nodeconfiguration );
		// kuink_mydebug( $nodeconfiguration['config']['NODE'], $actionname );
		
		$roles = $nodeconfiguration [NodeConfKey::ROLES];
		if (in_array ( 'framework.admin', $roles ) && $this->type == 'nodes') {
			$trace = isset ( $_GET ['trace'] ) ? ( string ) $_GET ['trace'] : '';
			if ($trace == 'true') {
				$layout = \Kuink\UI\Layout\Layout::getInstance ();
				$layout->addHtml ( '------------------------------------', 'context' );
				$layout->addHtml ( '[' . $nodeconfiguration [NodeConfKey::APPLICATION] . '] [' . $nodeconfiguration [NodeConfKey::PROCESS] . '] [' . $nodeconfiguration [NodeConfKey::NODE] . '] :: Screen [' . $screen_name . ']', 'context' );
				$layout->addHtml ( 'Event: ' . $nodeconfiguration [NodeConfKey::EVENT], 'context' );
				$layout->addHtml ( 'Action: ' . $nodeconfiguration [NodeConfKey::ACTION] . '->' . $nodeconfiguration [NodeConfKey::ACTION_VALUE], 'context' );
				// $layout->addHtml($html, 'context');
			}
		}
		
		$action_screen = ($screen_name != '');
		$KUINK_TRACE [] = "Action Screen: " . $screen_name;
		// var_dump( $KUINK_TRACE );
		// var_dump($nodexml);
		
		// Log level
		$log_level = isset ( $action_xmlnode [0] ['log'] ) ? ( string ) $action_xmlnode [0] ['log'] : '';
		// Check for a valid type
		if ($log_level != 'action' && $log_level != 'post' && $log_level != 'full' && $log_level != '')
			throw new \Exception ( 'Invalid log type: ' . $log_level );
		
		//var_dump((string)$nodeconfiguration [NodeConfKey::ACTION_VALUE]);
		$actionValue = is_array($nodeconfiguration [NodeConfKey::ACTION_VALUE]) ? '' : (string)$nodeconfiguration [NodeConfKey::ACTION_VALUE];
		$key = ($actionValue == '') ? '-' : $actionValue;
		
		$html = ''; // return the html if this is a ui node
		            
		// print('LOG LEVEL:'.$log_level);
		if ($log_level == 'action') {
			// print('LOGGING...');
			$datasource = new \Kuink\Core\DataSource ( null, 'framework/framework,generic,insert', 'framework', 'generic' );
			// 'timestamp'=> $now
			// var_dump($nodeconfiguration);
			$pars = array (
					'table' => 'log',
					'id_user' => $KUINK_CFG->auth->user->id,
					'type' => $log_level,
					'application' => $nodeconfiguration [NodeConfKey::APPLICATION],
					'process' => $nodeconfiguration [NodeConfKey::PROCESS],
					'node' => $nodeconfiguration [NodeConfKey::NODE],
					'action' => $actionname,
					'log_key' => $key,
					'timestamp' => time () 
			);
			$log = $datasource->execute ( $pars );
		}
		$postform = '';
		if ($this->type == 'nodes') {
			// Check if there's a post and load formdata into variable POSTDATA
			$postform = isset ( $_GET ['form'] ) ? $_GET ['form'] : '';
			$event = isset ( $_GET ['event'] ) ? $_GET ['event'] : '';
			
			// Getting the submitted data from POST.
			$variables ['POSTDATA'] = $this->get_submitted_data ();
		}
		// var_dump($variables['POSTDATA']);
		// var_dump($KUINK_TRACE);
		// if this is a screen object load all the screen objects, forms, grids, everything
		if ($action_screen) {
			$layout = Layout::getInstance ();
			
			$screen_obj = $nodexml->xpath ( '//Screen[@id="' . $screen_name . '"]' );
			
			if (! $screen_obj)
				throw new \Exception ( 'Screen "' . $screen_name . '" does not exist. Check the <Action ... screen=""> tag.' );
			
			if ($this->show_source == 'true') {
				// Show the source xml
				$layout->setScreenSource ( htmlentities ( $screen_obj [0]->asXml () ) );
				
				$actionsXml = $nodexml->xpath ( '/Node/Actions/Action' );
				$actionsSource = array ();
				foreach ( $actionsXml as $actionXml ) {
					$actionName = ( string ) $actionXml ['name'];
					$actionsSource [$actionName] = htmlentities ( $actionXml->asXml () );
				}
				$layout->setActionsSource ( $actionsSource );
			}
			
			// var_dump( $screen_obj );
			$screen_tmpl = isset ( $screen_obj [0] ['template'] ) ? ( string ) $screen_obj [0] ['template'] : '1col';
			
			// UI nodes will use the parent template for screen
			if ($this->type == 'nodes')
				$layout->setAppTemplate ( $screen_tmpl );
			
			$layout->setGlobalVariable ( '_idContext', $context->id );
			
			foreach ( $screen_obj [0]->children () as $screen_uielem ) {
				$uielem_type = ( string ) $screen_uielem->getName ();
				$uielem_name = ( string ) $screen_uielem ['name'];
				$KUINK_TRACE [] = "CreateObject: " . $uielem_type . '.' . $uielem_name;
				
				$uielem = \Kuink\Core\Factory::getControl ( $uielem_type, $nodeconfiguration, $screen_uielem );
				if (! $uielem)
					throw new \Exception ( 'Cannot create ' . $uielem_type . ' control object.' );
					
					// Automatically bind the POSTDATA to the form if it is the same (or have the same name...)
				if ($uielem_type == 'Form' && $uielem_name == $postform) {
					if (! empty ( $this->variables ['POSTDATA'] )) {
						// If the form has list typed fields, extract them and bind to thr form so they can appear properly
						$listFormFields = $_POST ['_FORM_LIST_FIELDS'];
						$listFormFieldsArr = explode ( ',', $listFormFields );
						if (! ((count ( $listFormFieldsArr ) == 1 && empty ( $listFormFieldsArr [0] )))) {
							// Avoid empty array...
							foreach ( $listFormFieldsArr as $listFormField ) {
								$bindArr = array ();
								foreach ( $variables ['POSTDATA'] as $postKey => $postValue ) {
									$field = explode ( '_', $postKey );
									if ($field [0] == $listFormField) {
										if (isset($field [1]))
											$bindArr [$field [1]] = $postValue;
									}
								}
								$uielem->bind ( array (
										array (
												$listFormField => $bindArr 
										) 
								) );
							}
						}
						// var_dump( $listFormFieldsArr );
						$uielem->bind ( array (
								$this->variables ['POSTDATA'] 
						) );
						// var_dump( $uielem );
					}
				}
				
				if ($this->force_position != '') {
					$uielem->position = $this->force_position;
				}
				
				// Load in $variables all the objects
				$variables [$uielem_name] = $uielem;
				$this->current_controls [] = $uielem;
			}
		}

		$instructions = $action_xmlnode [0]->children ();
		// var_dump( $instructions[0] );
		// Start executing instructions inside this action
		$exit = false;
		
		// var_dump( $variables );
		foreach ( $instructions as $instruction_xmlnode ) {
			// kuink_mydebug('Calling instruction', $instruction_xmlnode->getName());
			// var_dump( $instruction_xmlnode );
			// Execute this instruction
				$this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $variables, $exit );
			// print( $actionname.'::' );
			// print( (string)$exit );
			// Check if there is an event raised
			if ($this->event_raised) {
				// kuink_mydebug(__METHOD__,'Event Raised');
				return '';
			}
			
			if ($exit)
				break;
		}

		// Handle export from grids...
		// Export the grid data and redo the action.
		$export = isset ( $_GET ['export'] ) ? true : false;
		$grid_name = isset ( $_GET ['export'] ) ? ( string ) $_GET ['export'] : '';
		
		if ($export) {
			$export_type = isset ( $_GET ['exporttype'] ) ? ( string ) $_GET ['exporttype'] : '';
			$action_name = isset ( $_GET ['action'] ) ? ( string ) $_GET ['action'] : '';
			
			$object = $variables [$grid_name];
			if (isset($object)) {
				$object->export ( $export_type );
				
				unset ( $_GET ['export'] );
				unset ( $_GET ['exporttype'] );
			} //Else, if the object does not exist, then continue, maybe it's in a custom control
		}
		
		// If this is a screen and don't force exit in action, display it!!
		if ($action_screen) {
			// Display user messages before the rest of the screen
			global $SESSION;
			
			$template_name = isset ( $_GET ['template'] ) ? ( string ) $_GET ['template'] : '';
			if ($this->type == 'nodes' && $template_name != 'none') {
				$app_desc = ($this->nodeconfiguration [NodeConfKey::REF_APPLICATION_DESC] != '') ? $this->nodeconfiguration [NodeConfKey::REF_APPLICATION_DESC] : kuink_get_string ( $this->nodeconfiguration [NodeConfKey::APPLICATION], $this->nodeconfiguration [NodeConfKey::APPLICATION] );
				$proc_desc = ($this->nodeconfiguration [NodeConfKey::REF_PROCESS_DESC] != '') ? $this->nodeconfiguration [NodeConfKey::REF_PROCESS_DESC] : kuink_get_string ( $this->nodeconfiguration [NodeConfKey::PROCESS], $this->nodeconfiguration [NodeConfKey::APPLICATION] );
				$node_desc = ($this->nodeconfiguration [NodeConfKey::REF_NODE_DESC] != '') ? $this->nodeconfiguration [NodeConfKey::REF_NODE_DESC] : kuink_get_string ( $this->nodeconfiguration [NodeConfKey::NODE], $this->nodeconfiguration [NodeConfKey::APPLICATION] );
				// Show the header for admin or development purposes
				// $impersonate_role = ''; //Get it from url
				// $this->show_admin_header( $impersonate_role, $node_roles );
				
				// var_dump( $course );
				$url = Tools::getWWWRoot ();
				global $KUINK_CFG;

				$layout = \Kuink\UI\Layout\Layout::getInstance ();
				$breadcrumbEntries = array ();
				$breadcrumbEntries[] = array (
					'label' => Language::getString ( 'home' ),
					'href' => $url 
				);
				if (isset($KUINK_CFG->trigger->url))
					$breadcrumbEntries[] = array (
						'label' => $KUINK_CFG->trigger->label,
						'href'  => $KUINK_CFG->trigger->url,
						'last'  => false 
					);
				$breadcrumbEntries[] = array (
					'label' => $app_desc,
					'href' => $nodeconfiguration [NodeConfKey::BASEURL],
					'last' => false 
				);
				$breadcrumbEntries[] = array (
					'label' => $proc_desc,
					'href' => '',
					'last' => false 
				);
				$breadcrumbEntries[] = array (
					'label' => $node_desc,
					'href' => '',
					'last' => true 
				); 
				$layout->setBreadCrumb ( $breadcrumbEntries );
				$layout->setAppName ( $app_desc );
				$layout->setProcessName ( $proc_desc );
				$layout->setNodeName ( $node_desc );
				$layout->setRefresh($nodeconfiguration[NodeConfKey::BASEURL].'&action=init');
				// print '<h1 align="center" class="headingblock header outline"> '.$app_desc.' » '.$proc_desc.'</h1>';
				// print '<h2 align="center" class="headingblock header outline"> '. $node_desc.'</h2>';
			}
			
			if (! $exit) {
				$screen_obj = $nodexml->xpath ( '//Screen[@id="' . $screen_name . '"]' );
				// $screen_frmt_data = '';
				// foreach ($screen_obj[0]->children() as $screen_uielem)
				
				// We want jus this control
				$refreshControlName = isset ( $_GET ['control'] ) ? $_GET ['control'] : null;
				if (isset($this->current_controls))
				foreach ( $this->current_controls as $uielem ) {
					$currentNode = ProcessOrchestrator::getCurrentNode ();
					$rwx = $currentNode->rwx;
					$uielem->applyRWX ( $rwx );
					
					$uielem->nodeconfiguration = $this->nodeconfiguration;
					if ($this->display) {
						if ($refreshControlName == null || $refreshControlName == $uielem->name) {
							// var_dump($uielem->name .' '. $uielem->type);
							if ($refreshControlName == $uielem->name)
								$uielem->setRefreshing ();
							
							$uielem->display ( true );
						}
					} else // Collect the html
						$html .= $uielem->getHtml ();
				}
			}
			$this->current_controls = array ();
		}
		// If this is a redirect action, then msg_manager is stored in session
		return $html;
	}
	function get_submitted_data() {
		$postdata = array ();
		
		$form_type = isset ( $_POST ['_FORM_TYPE'] ) ? $_POST ['_FORM_TYPE'] : null;
		$multi = ($form_type == 'multi');
		
		$listFormFields = isset($_POST ['_FORM_LIST_FIELDS']) ? $_POST ['_FORM_LIST_FIELDS'] : null;
		$listFormFieldsArr = explode ( ',', $listFormFields );
		foreach ( $listFormFieldsArr as $listFormField ) {
			if ($listFormField != '') {
				$listFieldArray = array ();
				foreach ( $_POST as $postKey => $postValue ) {
					$field = explode ( '_', $postKey );
					if ($field [0] == $listFormField) {
						$listFieldArray [$field [1]] = $postValue;
						unset ( $_POST [$postKey] );
					}
				}
				if (! empty ( $listFieldArray ))
					$postdata [$listFormField] = $listFieldArray;
			}
		}
		
		foreach ( $_POST as $key => $value ) {
			if ($multi) {
				// if multi form then organize in form POSTDATA[id][field] = value
				$newKey = explode ( '-', $key );
				$id = isset($newKey [0]) ? $newKey [0] : '';
				$field = isset($newKey [1]) ? $newKey [1] : '';
				$postdata [$id] [$field] = $value;
			} else if (is_array ( $value )) {
				$valuearray = $value + array (
						'year' => 1970,
						'month' => 1,
						'day' => 1,
						'hour' => 0,
						'minute' => 0 
				);
				$postdata [$key] = make_timestamp ( $valuearray ['year'], $valuearray ['month'], $valuearray ['day'], $valuearray ['hour'], $valuearray ['minute'], 0, null, null );
			} else {
				$postdata [$key] = $value;
			}
		}
		
		// unsetting unwanted values
		unset ( $postdata ['sesskey'] );
		unset ( $postdata ['_qf__neon_xmlform'] );
		unset ( $postdata ['_FORM_TYPE'] );
		unset ( $postdata ['_FORM_LIST_FIELDS'] );
		unset ( $postdata ['CHANGED'] );
		
		// var_dump( $_POST );
		
		array_walk ( $postdata, array (
				$this,
				'secureSuperGlobalPOST' 
		) );
		// print( get_magic_quotes_gpc() );
		return $postdata;
	}
	function secureSuperGlobalPOST(&$value, $key) {
		if (get_magic_quotes_gpc ())
			$value = stripslashes ( $value );
			// $_POST[$key] = htmlspecialchars(stripslashes($_POST[$key]));
			// $_POST[$key] = str_ireplace("script", "blocked", $_POST[$key]);
			// $value = mysql_escape_string($value);
		return $value;
	}
	
	// Executa uma function dentro do nó xml.
	function function_execute($nodeconfiguration, $nodexml, $action_xmlnode, $function_name, &$variables, &$exit, $param_values) {
		global $KUINK_TRACE;
		
		$libraries = $this->libraries;
		
		$local_variables = $param_values;
		// kuink_mydebug('Executing action', $actionname);
		
		// Inject general variables to the function
		$local_variables ['POSTDATA'] = isset ( $variables ['POSTDATA'] ) ? $variables ['POSTDATA'] : '';
		$local_variables ['USER'] = $variables ['USER'];
		$local_variables ['SYSTEM'] = $variables ['SYSTEM'];
		$local_variables ['CONTEXT'] = $variables ['CONTEXT'];
		$local_variables ['EVENT_PARAMS'] = $variables ['EVENT_PARAMS'];
		$local_variables ['ROLES'] = $variables ['ROLES'];
		$local_variables ['CAPABILITIES'] = $variables ['CAPABILITIES'];
		
		// PARAMS will hold the function params data instead of node params
		// $local_variables['PARAMS'] = $variables['PARAMS'];
		$local_variables ['PARAMS'] = $param_values;
		
		$KUINK_TRACE [] = "Function Name: " . $function_name;
		$KUINK_TRACE [] = $param_values;
		
		// Get the function definition
		$funct_xmlnode = $nodexml->xpath ( '/Node/Library/Function [@name="' . $function_name . '"]' );
		
		if ($funct_xmlnode == null) {
			throw new GenericException('framework/runtime::functionNotFound', 'Function '.$nodeconfiguration[NodeConfKey::APPLICATION].','.$nodeconfiguration[NodeConfKey::PROCESS].','.$nodeconfiguration[NodeConfKey::NODE].','.$function_name.' not found! Check the function name in the node.');
		}
			
			// Get the function parameters to create the ones with default value
		$funct_params = $funct_xmlnode [0]->xpath ( './Params/Param' );
		
		foreach ( $funct_params as $funct_param ) {
			// Check to see if there is a default value for this parameter
			if ($funct_param [0] != null && $funct_param [0] != '') {
				$funct_param_name = ( string ) $funct_param ['name'];
				// If the value is not supplied in the call, then use the default
				if (! isset ( $local_variables ['PARAMS'] [$funct_param_name] )) {
					$local_variables ['PARAMS'] [$funct_param_name] = ( string ) $funct_param [0];
					$local_variables [$funct_param_name] = ( string ) $funct_param [0];
				}
			}
		}
		// var_dump($function_name);
		// var_dump($local_variables);
		
		// Get the begin directive to start execute the instructions
		$funct_begin = $funct_xmlnode [0]->xpath ( './Begin' );
		
		$instructions = $funct_begin [0]->children ();
		// var_dump( $instructions[0] );
		
		// Start executing instructions inside this action
		$exit = false;
		foreach ( $instructions as $instruction_xmlnode ) {
			// kuink_mydebug('Calling instruction', $instruction_xmlnode->getName());
			$this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, '', $local_variables, $exit );
			// var_dump($exit);
			if ($exit)
				break;
		}
		
		// Getting the return value of the function
		$value = isset ( $local_variables ['_RETURN_'] ) ? $local_variables ['_RETURN_'] : '';
		
		// if ($exit) {
		// kuink_mydebug('Exiting Function with: '.$value );
		// }
		
		$return ['RETURN'] = $value;
		// Getting output params
		$out_params = $funct_xmlnode [0]->xpath ( './Params//Param [@output="true"]' );
		foreach ( $out_params as $out_param ) {
			$out_param_name = ( string ) $out_param ['name'];
			// kuink_mydebug($out_param_name, $local_variables[ $out_param_name ]);
			$return [$out_param_name] = $local_variables [$out_param_name];
		}
		
		$exit = false; // Put exit in false mode
		return $return;
	}
	function get_inst_attr($instruction, $attr_name, $variables, $mandatory = 'false', $default = '') {
		global $SESSION;
		if (! $mandatory && ! isset ( $instruction [$attr_name] ))
			return $default;
		
		if ($mandatory && ! isset ( $instruction [$attr_name] )) {
			$inst_name = $instruction->getname ();
			throw new \Exception ( 'Instruction "' . $inst_name . '" needs attribute "' . $attr_name . '" which was not supplied.' );
		}
		$attr_value = ( string ) $instruction [$attr_name];
		$type = isset($attr_value[0]) ? $attr_value[0] : null;
		$var_name = substr ( $attr_value, 1, strlen ( $attr_value ) - 1 );
		
		if ($type == '$' || $type == '#' || $type == '@') {
			$eval = new \Kuink\Core\EvalExpr ();
			$value = $eval->e ( $attr_value, $variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		} else
			$value = $attr_value;
		return ($value == '') ? $default : $value;
	}
	function instruction_execute(&$nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, &$variables, &$exit) {
		global $KUINK_TRACE;
		
		$instructionname = $instruction_xmlnode->getName ();
		// kuink_mydebug('instruction', $instructionname);
		$result = null;
		
		$KUINK_TRACE [] = "Instruction: " . $instructionname;
		
		switch ($instructionname) {
			
			case 'Var' :
				// Hack: The unset of $variables[$varname][$key] is not working in inst_var so we had to move it here.
				$varname = $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
				$key = $this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, false );
				$clear = $this->get_inst_attr ( $instruction_xmlnode, 'clear', $variables, false );
				$session = $this->get_inst_attr ( $instruction_xmlnode, 'session', $variables, false );
				$process = $this->get_inst_attr ( $instruction_xmlnode, 'process', $variables, false );
				// var_dump($varname.'.«'.$clear.'».'.$key.'.'.$session.'.'.$process);
				if ($clear == 'true' && $session != 'true' && $process != 'true' && $key != '') {
					// var_dump('unsetting...'.$varname.'|'.$key);
					unset ( $variables [$varname] [$key] );
					$variables [$varname] = array_slice ( $variables [$varname], 0, count ( $variables [$varname] ) );
					// var_dump($variables[$varname]);
					$result = null;
				} else
					$result = $this->inst_var ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			
			//case 'ActionValue' :
			//	$result = $this->inst_actionvalue ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			/*case 'Call' :
				$result = $this->inst_call ( $nodeconfiguration, $nodexml, $instruction_xmlnode, $instructionname, $variables, $exit );
				break;*/
			case 'AddControl' :
				$result = $this->inst_addControl ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			//case 'Set' :
			//	$result = $this->inst_set ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			/*case 'ForEach' :
				$result = $this->inst_foreach ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			case 'While' :
				$result = $this->inst_while ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			case 'Repeat' :
				$result = $this->inst_repeat ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			case 'For' :
				$result = $this->inst_for ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			/*case 'Try' :
				$result = $this->inst_try ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			case 'Redirect' :
				$result = $this->inst_redirect ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			
			case 'NativeCode' :
				$result = $this->inst_nativecode ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			/*case 'GetParam' :
				$result = $this->inst_gethtmlparam ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'UserMessage' :
				$result = $this->inst_usermessage ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'Exception' :
				$result = $this->inst_exception ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'Mail' :
				$result = $this->inst_mail ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'GetObjectProperty' :
				$result = $this->inst_getobjectproperty ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'Config' :
				$result = $this->inst_getconfig ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'Print' :
				$result = $this->inst_print ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			//case 'IsNull' :
			//	$result = $this->inst_isnull ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			case 'RaiseEvent' :
				$result = $this->inst_raiseevent ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			
			/*case 'Log' :
				$result = $this->inst_log ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			case 'Action' :
				$result = $this->inst_doaction ( $nodeconfiguration, $nodexml, $instruction_xmlnode, $instructionname, $variables, $exit );
				break;
			case 'Eval' :
				// Custom code execution
				$result = $this->inst_eval ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			case 'Permissions' :
				$result = $this->inst_permissions ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				if ($result == 0)
					throw new \Exception ( 'No permission!' );
				break;
			case 'AccessControlList':
				$result = $this->inst_accessControlList( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname,$instructionname,  $variables, $exit );
				break;
			case 'Role' :
				$result = $this->inst_role ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			case 'Capability' :
				$result = $this->inst_capability ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			//case 'RegisterAPI' :
			//	$result = $this->inst_registerapi ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			
			/*case 'Empty' :
				$result = $this->inst_empty ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'If' :
				$result = $this->inst_if ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			//case 'Expr' :
			//	$result = $this->inst_expr ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Eq' :
			//	$result = $this->inst_eq ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'NEq' :
			//	$result = $this->inst_neq ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Lt' :
			//	$result = $this->inst_lt ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Lte' :
			//	$result = $this->inst_lte ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Gt' :
			//	$result = $this->inst_gt ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Gte' :
			//	$result = $this->inst_gte ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Not' :
			//	$result = $this->inst_not ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'And' :
			//	$result = $this->inst_and ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//Case 'Or' :
			//	$result = $this->inst_or ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			/*case 'Exit' :
				$exit = true;
				$result = null;
				break;*/
			//case 'Lang' :
			//	$result = $this->inst_Lang ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			//	break;
			//case 'Return' :
			//	$result = $this->inst_return ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
			/*case 'Errors' :
				$result = $this->inst_errors ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			case 'DoPDF' :
				$result = $this->inst_dopdf ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			case 'DoPDF_v2' :
				$result = $this->inst_dopdfV2 ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			case 'DoPDFByTemplate' :
				$result = $this->inst_dopdfByTemplate ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			/*
			case 'Trace' :
				$result = $this->inst_trace ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break; */
			/*case 'Xml' :
				$result = $this->inst_xml ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*
			case 'Int' :
				$result = $this->inst_int ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			*/
			/*case 'Guid' :
				$result = $this->inst_guid ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'Now' :
				$result = $this->inst_now ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			// case 'DataSource':
			// $result = $this->inst_datasource( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname,$instructionname, $variables, $exit );
			// break;
			/*
			case 'Sleep' :
				$result = $this->inst_sleep ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
				*/
			case 'Template' :
				$result = $this->inst_template ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			/*case 'Script' :
				$result = $this->inst_script ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			case 'Transaction' :
				$result = $this->inst_transaction ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;
			
			/*case 'Execute' :
				$result = $this->inst_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			/*case 'DataAccess' :
				$result = $this->inst_dataAccess ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				break;*/
			case 'DataAccess' :
			case 'Execute' :				
			case 'Exit' :					
			case 'UserMessage' :
			case 'String' :
			case 'String.parse' :
			case 'String.concatenate' :
			case 'String.explode' :
			case 'String.implode' :
			case 'String.replace' :
			case 'String.stripslashes' :
			case 'String.firstWord' :
			case 'String.lastWord' :
			case 'List' :
			case 'List.add' :
			case 'List.remove' :
			case 'List.clear' :
			case 'List.toSet' :
			case 'List.fromSet' :
			case 'Math.round' :
			case 'ListToSet' :
			case 'SetToList' :
			case 'SetToJson' :
			case 'JsonToSet' :
			case 'ObjectToSet' :
			case 'MicroTime' :
			case 'NativeNew' :
			case 'NativeCall' :
			case 'Core.processOrchestrator' :
			case 'EvalCondition' :
			case 'DataSource' :
			case 'Commit' :
			case 'RollBack' :
			case 'Dummy' :
			case 'Auth.setLoggedUser' :
			case 'Uuid' :
			case 'Guid' :
			case 'Int' :
			case 'Int.parse' :
			case 'Now' :			
			case 'Null' : 
			case 'NewLine' : 
			case 'Doc' :	
			case 'Eq' :											
			case 'NEq' :
			case 'Gt' :				
			case 'Gte' :							
			case 'Lt' :							
			case 'Lte' :	
			case 'Not' :	
			case 'And' :
			case 'IsNull' :
			case 'Or' :
			case 'Sleep' :
			case 'Set' :
			case 'Set.pop' :
			case 'Set.reverse' :
			case 'Lang' :
			case 'Trace' :
			case 'RegisterAPI' :
			case 'ActionValue' :
			case 'Expr' :	
			case 'Return' :	
			case 'Xml' :
			case 'If' :		
			case 'ForEach' :
			case 'Try' :
			case 'Exception' :
			case 'Call' :
			case 'Control' :
			case 'Config' :	
			case 'GetObjectProperty' :
			case 'Empty' :
			case 'Print' :
			case 'GetParam' :		
			case 'Script' :
			case 'Errors' :
			case 'Mail' :
			case 'Log' :			
				$result = $this->genericInstExecute ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $variables, $exit );
				break;
			default :
				// Check the libraries
				$manager = isset ( $this->libraries [$instructionname] ) ? $this->libraries [$instructionname] : null;
				// TODO: PMT this is just transitive while old controls are not migrated to Control
				if ($manager == null) {
					// $object = (string)$instruction_xmlnode[0]['object'];
					$object = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'object', $variables, false );
					// kuink_mydebug( $object );
					// $ctrl_name = str_replace('Control','', $instructionname);
					// $class = 'Kuink\\Control\\'.$ctrl_name;
					// $manager = new $class($nodeconfiguration, null);
					$manager = $variables [$object];
					
					$result = $this->inst_dolibrary_direct ( $manager, $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
				} else 
				// var_dump($manager);
				if ($manager == null)
					throw new \Exception ( "Instruction: $instructionname does not exist. Check libraries." );
				else
					$result = $this->inst_dolibrary ( $manager, $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		}
		
		return $result;
	}
	
	// Aux function to get the value of an instruction
	function inst_aux_getvalue($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		if (empty ( $instruction_xmlnode ))
			return '';
			// print($instruction_xmlnode["name"]);
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			// print($newinstruction_xmlnode->getName());
			$value = ( string ) $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else {
			$value = ( string ) $instruction_xmlnode [0];
		}
		
		return $value;
	}
	function inst_addControl($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$type = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'control-type', $variables, true );
		$name = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		
		$controlDefinitionStr = '<' . $type;
		foreach ( $instruction_xmlnode->attributes () as $key => $value ) {
			$attrValue = $this->get_inst_attr ( $instruction_xmlnode, $key, $variables );
			$controlDefinitionStr .= ' ' . $key . '="' . $attrValue . '"';
		}
		$controlDefinitionStr .= '/>';
		$controlDefinitionXml = new \SimpleXMLElement ( $controlDefinitionStr );
		
		$control = \Kuink\Core\Factory::getControl ( $type, $nodeconfiguration, $controlDefinitionXml );
		
		// Put the control in the screen flow
		$variables [$name] = $control;
		$this->current_controls [] = $control;
	}
	
	/**
	 * Send a mail
	 * 
	 * @param
	 *        	[Param] to
	 * @param
	 *        	[Param] to_email
	 * @param
	 *        	[Param] from
	 * @param
	 *        	[Param] from_email
	 * @param
	 *        	[Param] cc
	 * @param
	 *        	[Param] bcc
	 * @param
	 *        	[Param] reply_to
	 * @param
	 *        	[Param] subject
	 * @param
	 *        	[Param] body
	 * @param
	 *        	[Param] charset
	 * @param
	 *        	[Param] content_type
	 * @return JSON Mail headers
	 */
	function inst_mail($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// get named instruction params
		$params = $this->aux_get_named_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		
		// message object
		$message = new \Zend\Mail\Message ();
		
		// encoding and body
		$body = new \Zend\Mime\Part ( $params ['body'] );
		$body->type = $params ['content_type'];
		$messageBody = new \Zend\Mime\Message ();
		$messageBody->addPart ( $body );
		$message->setEncoding ( $params ['charset'] );
		
		// set body to message
		$message->setBody ( $messageBody );
		
		// subject
		$message->setSubject ( $params ['subject'] );
		
		// to, from, replyto
		$message->addTo ( $params ['to_email'], $params ['to'] );
		$message->addFrom ( $params ['from_email'], $params ['from'] );
		$message->addReplyTo ( $params ['reply_to'] );
		
		// bcc and cc
		$message->addBcc ( $params ['bcc'] );
		$message->addCc ( $params ['cc'] );
		
		// send message
		$transport = new \Zend\Mail\Transport\Sendmail ();
		$transport->send ( $message );
		
		$arrayHeaders = $message->getHeaders ()->toArray ();
		$arrayHeaders ['Charset'] = $message->getEncoding ();
		
		return json_encode ( $arrayHeaders );
	}
	
	// return true if there are errors
	function inst_errors($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$msg_manager = \Kuink\Core\MessageManager::getInstance ();
		$errors = $msg_manager->has_type ( \Kuink\Core\MessageType::ERROR );
		return $errors;
	}
	function inst_log($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_CFG;
		
		$type = $this->get_inst_attr ( $instruction_xmlnode, 'type', $variables, true );
		$key = $this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, true );
		$action = $this->get_inst_attr ( $instruction_xmlnode, 'action', $variables, false, $nodeconfiguration [NodeConfKey::ACTION] );
		
		// Check for a valid type
		/**
		 * if ($type != 'action' && $type != 'post' && $type != 'full')
		 * throw new \Exception('Invalid log type: '.$type);
		 */
		if ($key == '')
			$key = ( string ) $nodeconfiguration [NodeConfKey::ACTION_VALUE];
		if ($key == '')
			$key = '-';
		
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		$message = '';
		
		foreach ( $paramsxml as $param )
			if ($param->count () > 0)
				$message = $this->inst_aux_getvalue ( $nodeconfiguration, $nodexml, $action_xmlnode, $param [0], $actionname, $instructionname, $variables, $exit );
			else
				$message = ( string ) $param [0];
		
		$datasource = new \Kuink\Core\DataSource ( null, 'framework/framework,generic,insert', 'framework', 'generic' );
		// var_dump($nodeconfiguration);
		// kuink_mydebug('KEY', $key);
		
		$pars = array (
				'table' => 'fw_log',
				'id_user' => $KUINK_CFG->auth->user->id,
				'type' => $type,
				'application' => $nodeconfiguration [NodeConfKey::APPLICATION],
				'process' => $nodeconfiguration [NodeConfKey::PROCESS],
				'node' => $nodeconfiguration [NodeConfKey::NODE],
				'action' => $action,
				'log_key' => ( string ) $key,
				'timestamp' => time (),
				'message' => $message 
		);
		$log = $datasource->execute ( $pars );
		
		return '';
	}
	
	// return true if all the params are equal
	function inst_call($nodeconfiguration, $nodexml, $instruction_xmlnode, $instructionname, &$variables, &$exit) {
		// Getting Function name and parameters
		// var_dump( $instruction_xmlnode );
		global $KUINK_TRACE;
		$library = $this->get_inst_attr ( $instruction_xmlnode, 'library', $variables, false );
		$function_name = $this->get_inst_attr ( $instruction_xmlnode, 'function', $variables, false );
		$function_params = $this->get_inst_attr ( $instruction_xmlnode, 'params', $variables, false );
		
		$KUINK_TRACE[] = 'Call: '.$library.','.$function_name;
		
		// Check if library as 4 elements, the last one is the function name
		$libSplit = explode ( ',', $library );
		if (count ( $libSplit ) == 4)
			$function_name = $libSplit [3];
		
		if ($function_name == '')
			throw new \Exception ( 'The function name must be supplied in attribute function or as the 4th param in library ' );
			
			// kuink_mydebug('Function', $function_name);
			// kuink_mydebug('Library', $library);
			
		// Get all the params
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		// var_dump($function_name);
		// var_dump( $paramsxml );
		// var_dump( $instruction_xmlnode );
		
		$param_values = ($function_params == '') ? array () : $variables [$function_params];
		$param_vars = array ();
		
		$action_xmlnode = null;
		$actionname = '';
		foreach ( $paramsxml as $param ) {
			
			$paramname = ( string ) $param ['name'];
			$param_var = isset ( $param ['var'] ) ? ( string ) $param ['var'] : null;
			// var_dump($param);
			$param_value = null;
			if ($param_var != null) {
				$param_value = $variables [$param_var];
			} else if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
				$param_value = (is_array ( $value ) || ($value == null)) ? $value : ( string ) $value;
			} else
				$param_value = ( string ) $param [0];
			
			$param_values [$paramname] = $param_value;
			$param_vars [$paramname] = $param_var;
		}
		// Adding params if variable is defined
		$paramsvar = isset ( $instruction_xmlnode ['params'] ) ? ( string ) $instruction_xmlnode ['params'] : '';
		if ($paramsvar != '') {
			$var = $variables [$paramsvar];
			foreach ( $var as $key => $value )
				$param_values ["$key"] = $value;
		}
		// var_dump($function_name);
		// var_dump( $param_values );
		
		if (trim ( $library ) != '') {
			// Call a function in a different application,process
			$lib_parts = explode ( ',', $library );
			if (count ( $lib_parts ) < 3) {
				throw new \Exception ( 'ERROR: library name must be appname,processname,nodename' );
			}
			
			$lib_appname = trim ( $lib_parts [0] );
			$lib_processname = trim ( $lib_parts [1] );
			$lib_nodename = trim ( $lib_parts [2] );
			// kuink_mydebug('CALL', $lib_appname.'::'.$lib_processname.'::'.$lib_nodename);
			
			$call_appname = ($lib_appname == 'this') ? $this->nodeconfiguration [NodeConfKey::APPLICATION] : $lib_appname;
			$call_processname = ($lib_processname == 'this') ? $this->nodeconfiguration [NodeConfKey::PROCESS] : $lib_processname;
			$call_nodename = $lib_nodename;
			
			// kuink_mydebug(__CLASS__, __FUNCTION__);
			$node = new Node ( $call_appname, $call_processname, $call_nodename );
			$runtime = new Runtime ( $node, 'lib', $nodeconfiguration );
			
			$result = $runtime->execute ( $function_name, $param_values, $exit );
			// Import user messages inserrted in the function
			// foreach ($runtime->msg_manager->msgs as $msg)
			// $this->msg_manager->msgs[] = $msg;
		} else
			// Execute the local function
			$result = $this->function_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $function_name, $variables, $exit, $param_values );
		
		$return = $result ['RETURN'];
		if (isset ( $result ['RETURN'] ))
			unset ( $result ['RETURN'] );
		
		foreach ( $result as $out_param_name => $out_param_value ) {
			if (isset ( $param_vars [$out_param_name] ))
				$variables [$param_vars [$out_param_name]] = $out_param_value;
			else
				throw new \Exception ( 'Function call must define a variable to store the output value of param ' . $out_param_name );
		}
		
		return $return;
	}
	function inst_foreach($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$var_name = $this->get_inst_attr ( $instruction_xmlnode, 'var', $variables, true );
		$item_name = $this->get_inst_attr ( $instruction_xmlnode, 'item', $variables, true );
		$key_name = $this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, false );
		
		$set = isset($variables [$var_name]) ? $variables [$var_name] : array();
		$value = '';
		if (is_array($set) || is_object($set))
			foreach ( $set as $key => $item ) {
				// var_dump($key.'=>'.$item);
				// $variables[$item_name] = (array)$item;
				if (is_object ( $item ))
					$item = ( array ) $item;
				$variables [$item_name] = $item;
				$variables [$key_name] = $key;
				if ($instruction_xmlnode->count () > 0) {
					$instructions = $instruction_xmlnode->children ();
					foreach ( $instructions as $new_instruction ) {
						$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction, $actionname, $variables, $exit );
					}
				} else
					$value = $instruction_xmlnode [0];
			}
		
		return $value;
	}
	function inst_Lang($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		$key = $this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, true );
		
		$values = array ();
		
		foreach ( $paramsxml as $param )
			if ($param->count () > 0)
				$values [] = $this->inst_aux_getvalue ( $nodeconfiguration, $nodexml, $action_xmlnode, $param [0], $actionname, $instructionname, $variables, $exit );
			else
				$values [] = ( string ) $param [0];
			
			// var_dump($nodeconfiguration['customappname']);
		$string = ( string ) kuink_get_string ( $key, $nodeconfiguration [NodeConfKey::APPLICATION], $values );
		// kuink_mydebug($key, $string);
		return $string;
	}
	function inst_return($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		// print('BUMMMM');
		$value = '';
		
		if (count ( $instruction_xmlnode->children () ) > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			
			$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else {
			$value = ( string ) $instruction_xmlnode;
		}
		$variables ['_RETURN_'] = $value;
		// print('Returning...' . $value);
		$exit = true;
		return $value;
	}
	
	// return true if all the params are equal
	function inst_exception( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname,$instructionname,  &$variables, &$exit ) {
		global $KUINK_TRACE;
		$name = (string) $this->get_inst_attr($instruction_xmlnode, 'name', $variables, false, '');
		$conditionExpr = (string) $this->get_inst_attr($instruction_xmlnode, 'condition', $variables, false, '');
				
		//If condition is set then evaluate it and only continue if it is true
		if ($conditionExpr != '') {
			$eval = new \Kuink\Core\EvalExpr();
				$value = $eval->e( $conditionExpr, $variables, TRUE);
				if (!$value)
					return; //If the condition is not true then return immediately, else let it flow
		}
		$KUINK_TRACE[] = '*** EXCEPTION ('.$name.')';   
		if ($name != '') {	
			//This is an exception with a name specified so act differently
			$paramsxml = $instruction_xmlnode->xpath('./Param');
				
			$values = array();
			
			foreach($paramsxml as $param)
			if ($param->count() > 0)
				$values[] = $this->inst_aux_getvalue($nodeconfiguration, $nodexml, $action_xmlnode, $param[0], $actionname,$instructionname,  $variables, $exit);
			else
				$values[] = (string)$param[0];
		
			$message = (string)\Kuink\Core\Language::getExceptionString($name, $nodeconfiguration[NodeConfKey::APPLICATION], $values);
			$KUINK_TRACE[] = '*** EXCEPTION ('.$name.') - '.$message;           	
			throw new Exception\GenericException($name, $message);
		}
	 
		//Legacy behavior
		//Execute inner instructions

		$message = 'No message';
		$code = '';
		//Execute inner instructions

		$newinstruction_xmlnode = $instruction_xmlnode->children();
		if (count($newinstruction_xmlnode) > 0)
			$message = (string) $this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode[0], $actionname,  $variables, $exit);
		else
			$message = (string)$instruction_xmlnode[0];
		$KUINK_TRACE[] = '*** EXCEPTION ('.$name.') - '.$message;
		switch ($code) {
			case 'ZeroRowsAffected':
				throw new Exception\ZeroRowsAffected($message);
				break;
			case 'ClassNotFound':
				throw new Exception\ClassNotFound($message);
				break;
			case 'InvalidParameters':
				throw new Exception\InvalidParameters($message);
				break;

			default:
				throw new \Exception($message);
				break;
		}
	}
	
	// return true if all the params are equal
	function inst_usermessage($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$type = ( string ) $instruction_xmlnode ['type'];
		
		if (! in_array ( $type, array (
				'error',
				'warning',
				'information',
				'success',
				'exception' 
		) ))
			throw new \Exception ( 'UserMessage: invalid type-' . $type . ' :: try type= "error" | "warning" | "information" | "success" | "Exception" ' );
			// Execute inner instructions
		$newinstruction_xmlnode = $instruction_xmlnode->children ();
		if (count ( $newinstruction_xmlnode ) > 0)
			$text = ( string ) $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		else
			$text = ( string ) $instruction_xmlnode [0];
		
		$msgtype = \Kuink\Core\MessageType::ERROR;
		switch ($type) {
			case "error" :
				$msgtype = \Kuink\Core\MessageType::ERROR;
				break;
			case "warning" :
				$msgtype = \Kuink\Core\MessageType::WARNING;
				break;
			case "information" :
				$msgtype = \Kuink\Core\MessageType::INFORMATION;
				break;
			case "success" :
				$msgtype = \Kuink\Core\MessageType::SUCCESS;
				break;
			case "exception" :
				$msgtype = \Kuink\Core\MessageType::EXCEPTION;
				break;
			default :
				throw new \Exception ( 'UserMessage: invalid type-' . $type . ' :: try type= "error" | "warning" | "information" | "success" | "exception"' );
		}
		
		$msg_manager = \Kuink\Core\MessageManager::getInstance ();
		$msg_manager->add ( $msgtype, $text );
		
		return;
	}
	
	// return true if all the params are equal
	function inst_not($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$newinstruction_xmlnode = $instruction_xmlnode->children ();
		$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		
		// var_dump($value);
		$not = (! $value);
		return $not;
	}
	
	// return true if the value is empty
	function inst_empty($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$newinstruction_xmlnode = $instruction_xmlnode->children ();
		$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		
		return empty ( $value );
	}
	
	// return true if all the params are equal
	function inst_and($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->children (); // $instruction_xmlnode->xpath('.//Param');
		                                               
		// print('Instruction Equal');
		                                               // var_dump( $paramsxml );
		
		$values = array ();
		
		foreach ( $paramsxml as $param ) {
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param; // $param->children();
				$and_value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
				// kuink_mydebug( '::AND::', $and_value );
				$values [] = empty ( $and_value ) ? 0 : $and_value;
			}
			// else
			// $values[] = (bool)$param[0];
		}
		// AND return true if theres nothing to do
		if (count ( $values ) == 0)
			return true;
			
			// Check if the values are equal!!
		$first_value = ( bool ) ($values [0]);
		
		// Verificar se os valores são todos verdadeiros
		foreach ( $values as $value ) {
			$lit = ( bool ) $value;
			if ($lit != true)
				return false;
		}
		return true;
	}
	
	// return true if one param is true
	function inst_or($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->children (); // $instruction_xmlnode->xpath('.//Param');
		                                               
		// print('Instruction Equal');
		                                               // var_dump( $paramsxml );
		
		$values = array ();
		
		foreach ( $paramsxml as $param ) {
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param; // $param->children();
				$or_value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
				// kuink_mydebug( '::AND::', $and_value );
				$values [] = empty ( $or_value ) ? 0 : $or_value;
			}
		}
		// OR return true if theres nothing to do
		if (count ( $values ) == 0)
			return true;
			
			// Check if theres one boolean true value
		foreach ( $values as $value ) {
			$lit = ( bool ) $value;
			if ($lit)
				return true;
		}
		return false;
	}
	
	// return true if all the params are equal
	function inst_lt($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$first_value = '';
		$second_value = '';
		
		$this->aux_get_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit, $first_value, $second_value );
		
		return ($first_value < $second_value);
	}
	
	// return true if all the params are equal
	function inst_lte($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$first_value = '';
		$second_value = '';
		
		$this->aux_get_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit, $first_value, $second_value );
		
		return ($first_value <= $second_value);
	}
	
	// return true if all the params are equal
	function inst_gt($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$first_value = '';
		$second_value = '';
		
		$this->aux_get_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit, $first_value, $second_value );
		
		return ($first_value > $second_value);
	}
	
	// return true if all the params are equal
	function inst_gte($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$first_value = '';
		$second_value = '';
		
		$this->aux_get_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit, $first_value, $second_value );
		
		return ($first_value >= $second_value);
	}
	function aux_get_instruction_value($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			// kuink_mydebug($newinstruction_xmlnode[0]->getName(), $value);
		} else
			$value = ( string ) $instruction_xmlnode [0];
		
		return $value;
	}
	function aux_get_named_param_values($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		$values = array ();
		foreach ( $paramsxml as $param ) {
			$paramname = ( string ) $param ['name'];
			// kuink_mydebug('PARAM', $paramname);
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				// var_dump( $newinstruction_xmlnode[0]);
				$values [$paramname] = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$values [$paramname] = ( string ) $param [0];
		}
		return $values;
	}
	function aux_get_param_values($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit, &$first_value, &$second_value) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		$values = array ();
		foreach ( $paramsxml as $param ) {
			
			$paramname = $param ['name'];
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$values [] = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$values [] = ( string ) $param [0];
		}
		
		$first_value = ( string ) $values [0];
		$second_value = ( string ) $values [1];
		return;
	}
	function aux_get_param_values_complete($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		$values = array ();
		foreach ( $paramsxml as $param ) {
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$values [] = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$values [] = ( string ) $param [0];
		}
		
		return $values;
	}
	function inst_neq($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$value = $this->inst_eq ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		return (! $value);
	}
	
	// return true if all the params are equal
	function inst_eq($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get all the params
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		// print('Instruction Equal');
		// var_dump( $paramsxml );
		// var_dump( $instruction_xmlnode );
		
		$values;
		
		foreach ( $paramsxml as $param ) {
			
			$paramname = $param ['name'];
			// var_dump($param);
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$values [] = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$values [] = ( string ) $param [0];
		}
		// Check if the values are equal!!
		// print('VALUES::');
		// var_dump( $values );
		$first_value = ( string ) $values [0];
		
		// print('First Value:'.$first_value);
		// var_dump($first_value);
		
		// Verificar se os valores são todos iguais
		foreach ( $values as $value ) {
			// $lit = (string)$value[0];
			$lit = ( string ) $value;
			// var_dump($value);
			// print('%%'.$first_value.'='.$lit.'%%');
			if ($lit != $first_value)
				return false;
		}
		return true;
	}
	
	function inst_eval($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Eval instruction can have several childs with string instructions to execute.
		// We need to execute all the instructions first, and then execute their value because the value are other instructions to execute.
		// E.g. <Eval><Var name="code"/><Eval>
		// We need to execute the Var instructions and then load the result and execute all the instructions contained in the result
		$instructions = $instruction_xmlnode->children ();
		// var_dump( $instructions );
		// Execute all the Eval instructions
		$eval_value = '';
		foreach ( $instructions as $new_instruction ) {
			// var_dump($new_instruction);
			$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit );
			
			// Now load the result and execute it
			libxml_use_internal_errors ( true );
			$eval_instructions_xml = simplexml_load_string ( $value );
			$errors = libxml_get_errors ();
			
			if ($eval_instructions_xml == null) {
				$errorMsg = '';
				foreach ( $errors as $error )
					$errorMsg .= $error->message;
				
				throw new \Exception ( 'Error loading eval instructions: ' . $errorMsg );
			}
			
			$container = ( string ) $eval_instructions_xml [0]->getName ();
			if ($container != 'Eval')
				throw new \Exception ( 'Expected Eval instruction as container.' );
			
			$eval_instructions = $eval_instructions_xml [0]->children ();
			foreach ( $eval_instructions as $eval_instruction ) {
				$result = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $eval_instruction [0], $actionname, $variables, $exit );
				$eval_value .= (is_array($result) || is_object($result)) ? '' : $result;
			}
		}
		return $eval_value;
	}

	function inst_try( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname,$instructionname,  &$variables, &$exit )
	{
		global $KUINK_TRACE;
		
		//Get the condition to the 'If' instruction
		$instructionsxml = $instruction_xmlnode->xpath('./Instructions');

		if (! $instructionsxml)
			throw new \Exception("Instruction: $instructionname. No 'Instructions' block supplied to the instruction.");

		$instructions = $instructionsxml[0]->children();
		//print_object($instructions);
		$value = '';
		try {
			foreach ($instructions as $new_instruction ) {
				//print_object($exit);
				//print_object($new_instruction);
				$instResult = $this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction[0], $actionname,  $variables, $exit);
				if (is_string($instResult))
					$value .= $instResult;
			}
		}
		catch ( GenericException $e ) {
			$exceptionName = $e->name;
			//print_object(get_class($e));
			//print_object($e->name);
			
			$KUINK_TRACE[] = 'Exception detected '.$e->__toString(); 
			
			if ($exceptionName != '' && $exceptionName != null) {
				//This is a typed exception 
				
				//try to catch this exception directly
				$catchxml = $instruction_xmlnode->xpath('./Catch[@exception="'.$exceptionName.'"]');
				//There's a match?
				if (!$catchxml || count($catchxml) == 0) {
					//If no then try to find a general catch
					$catchxml = $instruction_xmlnode->xpath('./Catch[not(@exception)]');
				} else {
					$KUINK_TRACE[] = 'Direct Catch '.$exceptionName;
				}
			} else {
				//old behaviour
				$catchxml = $instruction_xmlnode->xpath('./Catch');
			}
			
			//If 'Catch' does not exist, rethrown the exception....
			if (!$catchxml || count($catchxml) == 0) {
				$KUINK_TRACE[] = 'Exception not catched';
				throw $e;
			}
			$KUINK_TRACE[] = 'Default Catch';

			//Add the exception message to variables
			//Set the temporary EXCEPTION variable
			$variables['EXCEPTION']['name'] = $exceptionName;
			$variables['EXCEPTION']['message'] = (string)$e->__toString();

            $msgVar = (string)$this->get_inst_attr($catchxml[0], 'msg', $variables, false);
            if ( $msgVar != '')
              $variables[ $msgVar ] = $e->getMessage();

			$instructions = $catchxml[0]->children();
			$value = '';
			foreach ($instructions as $new_instruction ) {
				$value .= $this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction[0], $actionname,  $variables, $exit);
			}
			//Clean the last exception
			unset($variables['EXCEPTION']);
		}
		catch (\Exception $e) {
			$KUINK_TRACE[] = 'Exception: '.$e->getMessage();			
			//print_object(get_class($e));
			//print_object($e->getPrevious());
			
			$catchxml = $instruction_xmlnode->xpath('./Catch');
			if (!$catchxml || count($catchxml) == 0) {
				$KUINK_TRACE[] = 'Exception not catched';
				throw $e;
			}
			$msgVar = (string)$this->get_inst_attr($catchxml[0], 'msg', $variables, false);
			if ( $msgVar != '')
				$variables[ $msgVar ] = $e->getMessage();
			
			$instructions = $catchxml[0]->children();
			$value = '';
			foreach ($instructions as $new_instruction ) {
				$value .= $this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction[0], $actionname,  $variables, $exit);
			}
		}
		return $value;
	}

	
	/**
	 * Gets a variable value
	 * 
	 * @param unknown_type $name        	
	 * @param unknown_type $key        	
	 * @param unknown_type $session        	
	 */
	/*
	 * function getVariableValue($name, $key, $session, $variables, $stringIsolation = true ) {
	 * global $SESSION;
	 *
	 * if ($session == 'true') {
	 * if ($key=='')
	 * $return = $_SESSION['KUINK_CONTEXT']['VARIABLES'][$name];
	 * else {
	 * $variable =$_SESSION['KUINK_CONTEXT']['VARIABLES'][$name];
	 * $return = $variable[$key];
	 * }
	 * } else {
	 * if ($key=='')
	 * $return = $variables[$name];
	 * else
	 * $return = $variables[$name][$key];
	 * }
	 *
	 * if (!is_numeric($return) && $stringIsolation )
	 * $return = "'".$return."'";
	 * return $return;
	 * }
	 */
	function inst_expr($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get inline Condition
		$conditionExpr = isset ( $instruction_xmlnode ['value'] ) ? ( string ) $instruction_xmlnode ['value'] : '';
		$eval = new \Kuink\Core\EvalExpr ();
		return $eval->e ( $conditionExpr, $variables, FALSE );
	}
	function inst_for($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get inline Condition
		$var = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'var', $variables, true );
		$conditionExpr = isset ( $instruction_xmlnode ['condition'] ) ? ( string ) $instruction_xmlnode ['condition'] : null;
		$step = ( int ) $this->get_inst_attr ( $instruction_xmlnode, 'step', $variables, true );
		$start = ( int ) $this->get_inst_attr ( $instruction_xmlnode, 'start', $variables, false, null );
		
		if ($start !== null) {
			$variables [$var] = $start;
		}
		if (! $conditionExpr)
			throw new \Exception ( "Instruction: $instructionname. No conditions supplied to the instruction." );
		
		if (! isset ( $variables [$var] ))
			throw new \Exception ( "Instruction: $instructionname. Variable " . $var . " must be initialized" );
			
			// Parse the conditionExpr
		$eval = new \Kuink\Core\EvalExpr ();
		$condition = $eval->e ( $conditionExpr, $variables, TRUE );
		
		$value = '';
		while ( $condition ) {
			$instructions = $instruction_xmlnode->children ();
			foreach ( $instructions as $new_instruction ) {
				$result = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit );
				if (!is_array($result))
					$value .= $result;
			}
			$variables [$var] = $variables [$var] + $step;
			$condition = $eval->e ( $conditionExpr, $variables, TRUE );
		}
		return $value;
	}
	function inst_repeat($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get inline Condition
		$conditionExpr = isset ( $instruction_xmlnode ['until'] ) ? ( string ) $instruction_xmlnode ['until'] : null;
		
		if (! $conditionExpr)
			throw new \Exception ( "Instruction: $instructionname. No until conditions supplied to the instruction." );
			
			// Parse the conditionExpr
		$eval = new \Kuink\Core\EvalExpr ();
		
		$condition = false;
		$value = '';
		while ( ! $condition ) {
			$instructions = $instruction_xmlnode->children ();
			foreach ( $instructions as $new_instruction ) {
				$value .= $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit );
			}
			$condition = $eval->e ( $conditionExpr, $variables, TRUE );
		}
		return $value;
	}
	function inst_while($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get inline Condition
		$conditionExpr = isset ( $instruction_xmlnode ['condition'] ) ? ( string ) $instruction_xmlnode ['condition'] : null;
		
		if (! $conditionExpr)
			throw new \Exception ( "Instruction: $instructionname. No conditions supplied to the instruction." );
			
			// Parse the conditionExpr
		$eval = new \Kuink\Core\EvalExpr ();
		$condition = $eval->e ( $conditionExpr, $variables, TRUE );
		
		$value = '';
		while ( $condition ) {
			$instructions = $instruction_xmlnode->children ();
			foreach ( $instructions as $new_instruction ) {
				$result = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit ); 
				if (!is_array($result))
					$value .= $result;
			}
			$condition = $eval->e ( $conditionExpr, $variables, TRUE );
		}
		return $value;
	}
	function inst_if($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// Get inline Condition
		$conditionExpr = isset ( $instruction_xmlnode ['condition'] ) ? ( string ) $instruction_xmlnode ['condition'] : null;
		// Get the condition to the 'If' instruction
		$conditionxml = $instruction_xmlnode->xpath ( './Condition' );
		
		// var_dump($conditionxml);
		
		if (! $conditionxml && ! $conditionExpr)
			throw new \Exception ( "Instruction: $instructionname. No conditions supplied to the instruction." );
		
		$value = true;
		// Execute the instructions inside the conditions
		
		if ($conditionxml) {
			if ($conditionxml [0]->count () == 0) {
				// No instructions found
				// print('No conditional instructions found...');
				$value = $conditionxml [0];
			} else {
				
				// Execute the instructions
				$newinstruction_xmlnode = $conditionxml [0]->children ();
				// var_dump($newinstruction_xmlnode);
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode, $actionname, $variables, $exit );
			}
		} else {
			// Parse the conditionExpr
			$eval = new \Kuink\Core\EvalExpr ();
			try {
				$value = $eval->e ( $conditionExpr, $variables, TRUE );
			} catch ( \Exception $e ) {
				var_dump ( 'Exception: eval' );
				die ();
			}
		}
		
		// Check if the value is true or false (boolean)
		if ($value) {
			// Execute only the 'Then' instructions
			// print('Execute only the Then instructions');
			$thenxml = $instruction_xmlnode->xpath ( './Then' );
			if (! $thenxml)
				throw new \Exception ( "Instruction: $instructionname. No 'Then' block supplied to the instruction." );
			
			$instructions = $thenxml [0]->children ();
			// var_dump($instructions);
			$returnValue = '';
			if ($instructions->count () == 0)
				$returnValue = ( string ) $thenxml [0];
			foreach ( $instructions as $new_instruction ) {
				// var_dump($exit);
				// var_dump($new_instruction);
				
				$returnValue = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit );
			}
			
			return $returnValue;
		} else {
			// Execute only the 'Else' instructions
			// print('Execute only the Else instructions');
			$elsexml = $instruction_xmlnode->xpath ( './Else' );
			
			// If 'Else' does not exist, do nothing....
			if (! $elsexml)
				return null;
			
			$instructions = $elsexml [0]->children ();
			// var_dump($instructions);
			$returnValue = '';
			if ($instructions->count () == 0)
				$returnValue = ( string ) $elsexml [0];
			foreach ( $instructions as $new_instruction ) {
				// print('NEW INST::');
				// var_dump($new_instruction);
				
				$returnValue = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit );
			}
			return $returnValue;
		}
	}
	
	/**
	 * Checks if the current user has permissions to proceed
	 * <Permission>
	 * <Allow type="and|or:or"> and must fill all directives, or must fill one
	 * <Role* name="xpto"/>
	 * <Capability* name="xpto"/>
	 * </Allow>
	 * </Permission>
	 * 
	 * @param type $permissionsXml        	
	 * @return bool
	 */
	function hasPermissions($permissionsXml) {
		$hasPermissionsResult = 0;
		
		// Check roles
		$allows = $permissionsXml->xpath ( './Allow' );
		if (! isset ( $allows ))
			throw new \Exception ( 'Permissions must have an allow section' );
		foreach ( $allows as $allow ) {
			$allowType = isset ( $allow ['type'] ) ? ( string ) $allow ['type'] : 'or';
			$permissions = $allow->children ();
			$hasPermissionsLocal = ($allowType == 'or') ? 0 : 1;
			$hasPermissionsResult = 0;
			foreach ( $permissions as $permission ) {
				
				$permissionType = $permission->getName ();
				$permissionName = ( string ) $permission ['name'];
				
				// var_dump($this->nodeconfiguration[NodeConfKey::CAPABILITIES]);
				// var_dump($permissionType . ' | '.$permissionName);
				
				if ($permissionType == 'Capability') {
					if (isset ( $this->nodeconfiguration [NodeConfKey::CAPABILITIES] [$permissionName] )) {
						// The capability is present
						if ($allowType == 'or')
							$hasPermissionsLocal = 1;
					} else {
						// The capability is not present
						if ($allowType == 'and')
							$hasPermissionsLocal = 0;
					}
				} else if ($permissionType == 'Role') {
					if (isset ( $this->nodeconfiguration [NodeConfKey::ROLES] [$permissionName] )) {
						// The capability is present
						if ($allowType == 'or')
							$hasPermissionsLocal = 1;
					} else {
						// The capability is present
						if ($allowType == 'and')
							$hasPermissionsLocal = 0;
					}
				} else
					throw new \Exception ( 'Invalid permission type ' . $permissionType . ' allowed Capability|Role' );
			}
			$hasPermissionsResult += $hasPermissionsLocal;
		}
		// var_dump($hasPermissionsResult);
		return ($hasPermissionsResult > 0);
	}
	function inst_permissions($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, &$exit) {
		/*
		 * $allow = $instruction_xmlnode->xpath('./Allow//Role');
		 * $node_roles = $nodeconfiguration[NodeConfKey::NODE_ROLES];
		 *
		 * $has_permission = 0;
		 *
		 * foreach( $allow as $role)
		 * {
		 * $role_name = (string)$role['name'];
		 * if ($node_roles[$role_name] == 'true')
		 * $has_permission += 1;
		 *
		 * }
		 */
		// var_dump($KUINK_TRACE);
		return $this->hasPermissions ( $instruction_xmlnode );
	}
	
	// TODO STI: PMT remove this function after controls migration
	// This removes the object as first parameter
	function inst_dolibrary_direct($manager, $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$objectname = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'object', $variables, false );
		$methodname = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'method', $variables, true );
		// $params = isset($instruction_xmlnode['params']) ? $instruction_xmlnode['params'] : array();
		$instParams = isset ( $instruction_xmlnode ['params'] ) ? ( string ) $instruction_xmlnode ['params'] : '';
		$params = array ();
		if ($instParams != '')
			$params = isset ( $variables [$instParams] ) ? $variables [$instParams] : array ();
		
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		// $params = null;
		
		$object = ($objectname == '') ? null : $variables [$objectname];
		
		foreach ( $paramsxml as $param ) {
			// var_dump($param);
			$paramname = isset ( $param ['name'] ) ? ( string ) $param ['name'] : '';
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$value = $param [0];
				
				// var_dump($value);
				// kuink_mydebug('name', $paramname);
			if ($paramname == '')
				$params [] = $value;
			else
				$params [$paramname] = $value;
		}
		
		$result = '';
		// Check if the method exists
		// var_dump($params);
		// var_dump( $manager );
		if (method_exists ( $manager, $methodname ))
			$result = $manager->$methodname ( $params );
		else
			throw new \Exception ( 'Unknown method ' . $methodname . ' for object ' . $objectname );
		
		return $result;
	}
	
	// Executes the method from a library
	function inst_dolibrary($manager, $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$objectname = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'object', $variables, false );
		$methodname = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'method', $variables, true );
		// print($methodname);
		// var_dump($instruction_xmlnode);
		
		/*
		 * if (! $methodname)
		 * throw new Exception('No method defined for '.(string)$manager);
		 */
		
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		$params = null;
		// print($instructionname);
		// var_dump($instruction_xmlnode);
		// var_dump($paramsxml);
		
		// Get the object
		
		$object = ($objectname == '') ? null : $variables [$objectname];
		
		if ($object)
			$params [] = $object;
		
		foreach ( $paramsxml as $param ) {
			// var_dump($param);
			
			$paramname = isset ( $param ['name'] ) ? ( string ) $param ['name'] : '';
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$value = $param [0];
				
				// var_dump($value);
			if ($paramname == '')
				$params [] = $value;
			else
				$params [$paramname] = $value;
		}
		
		$result = '';
		// Check if the method exists
		// var_dump($params);
		if (method_exists ( $manager, $methodname ))
			$result = $manager->$methodname ( $params );
		else
			throw new \Exception ( 'Unknown method ' . $methodname );
		
		return $result;
	}
	
	// if the first variable is null, returns the second, else returns the first variable
	function inst_isnull($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		// var_dump($instruction_xmlnode);
		
		foreach ( $paramsxml as $param ) {
			$paramname = $param ['name'];
			// print('PARAM');
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$value = $param [0];
			
			if (is_array ( $value ) && count ( $value ) == 0)
				$value = null;
			
			if ($value != null) {
				// var_dump($value);
				return $value;
			}
		}
		
		return null;
	}
	function inst_set($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$newSet = array ();
		// var_dump($instruction_xmlnode);
		// $content= $instruction_xmlnode[0];
		foreach ( $instruction_xmlnode->children () as $element ) {
			// $elementname = $element['name'];
			$elementName = $this->get_inst_attr ( $element, 'name', $variables, false, '' );
			$elementKey = $this->get_inst_attr ( $element, 'key', $variables, false, '' );
			$key = ($elementKey != '') ? $elementKey: $elementName;
			// print('ELEMENT::'.$elementname);
			if ($element->count () > 0) {
				$newinstruction_xmlnode = $element->children ();
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else {
				$value = $element;
			}
			if ($key != '')
				$newSet [$key] = $value; //is_array ( $value ) ? $value : ( string ) $value;
			else
				$newSet [] = $value; //is_array ( $value ) ? $value : ( string ) $value;
		}
		
		return $newSet;
	}

	function inst_clearvar($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$varname = ( string ) $instruction_xmlnode ['name'];
		
		if (isset ( $instruction_xmlnode ['key'] )) {
			$key = ( string ) $instruction_xmlnode ['key'];
			unset ( $variables [$varname] [$key] );
		} else
			unset ( $variables [$varname] );
		
		return;
	}
	function inst_raiseevent($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$baseurl = $nodeconfiguration [NodeConfKey::BASEURL];
		
		$eventname = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, false );
		// var_dump( $eventname );
		// die();
		
		// Read the event parameters
		$params = $this->aux_get_named_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		// var_dump($params);
		if (isset ( $params )) {
			global $SESSION;
			// Store these params in the global session variable EVENT_PARAMS
			ProcessOrchestrator::setEventParams ( $params );
			// $_SESSION['KUINK_CONTEXT']['EVENT_PARAMS'] = $params;
		}
		
		// Get the event name from within the node and execute all instructions there
		if ($eventname == '') {
			if ($instruction_xmlnode->count () > 0) {
				$newinstruction_xmlnode = $instruction_xmlnode->children ();
				// var_dump( $newinstruction_xmlnode[0] );
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$value = $instruction_xmlnode [0];
			
			$eventname = $value;
		}
		// var_dump( $instruction_xmlnode );
		// die();
		$this->event_raised = true;
		$this->event_raised_name = ( string ) $eventname;
		$this->event_raised_params = $params;
		
		// global $KUINK_TRACE;
		// $KUINK_TRACE[] = $this->event_raised_name;
		// $KUINK_TRACE[] = $this->event_raised_params;
		/*
		 * $url = new \moodle_url($baseurl);
		 * redirect($url->__toString() . '&'.UrlParam::EVENT.'='.$eventname, '', 0);
		 */
		//kuink_mydebug('Raise Event', $eventname);
		return null;
	}
	function inst_print($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$newLine = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'newline', $variables, false, 'false' );
		// $content= $instruction_xmlnode[0];
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$content = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$content = $instruction_xmlnode [0];
		
		if ($newLine == 'true')
			$content .= '<br/>';
		
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		$layout->addHtml ( $content, 'debugMessages' );
		
		return null;
	}
	function inst_trace($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_MANUAL_TRACE;
		// $content= $instruction_xmlnode[0];
		$label = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'label', $variables, false, '' );
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$content = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$content = ( string ) $instruction_xmlnode [0];
			
			// var_dump($content);
		$msg = '';
		if ($label != '')
			// if ($content == $instruction_xmlnode[0])
			if ($content == '')
				$msg = ' ==== ' . $label . ' / ====<br/>';
			else
				$msg = ' ==== ' . $label . ' ====<br/>';
			
			// $msg .= ($content == $instruction_xmlnode[0]) ? '' : ' '.var_export($content,true);
		$msg .= var_export ( $content, true );
		
		// if ($label != '' && $content != $instruction_xmlnode[0])
		if ($label != '' && $content != '')
			$msg .= '<br/>      ==== / ' . $label . ' ====';
		
		$KUINK_MANUAL_TRACE [] = $msg;
		
		return null;
	}
	function inst_getformdata($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$varname = ( string ) $instruction_xmlnode ['name'];
		$formdata = null;
		
		// kuink_mydebug('HELLO ', 'SUBMITTED **'.$varname);
		// var_dump( $variables );
		$mform = $variables [$varname];
		// var_dump( $mform );
		
		$mform->build ();
		
		if ($mform->is_submitted ()) {
			// kuink_mydebug('FORM: ', 'SUBMITTED **::'.$varname);
			$formdata = $mform->get_submitted_data ();
			// var_dump($formdata);
		}
		
		// var_dump( $mform );
		
		return $formdata;
	}
	function inst_getobjectproperty($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$objectname = $instruction_xmlnode ['object'];
		$propertyname = '' . $instruction_xmlnode ['property'];
		
		// var_dump( $instruction_xmlnode );
		
		$object = $variables ["$objectname"];
		// var_dump( $variable );
		
		$return = $object->$propertyname;
		
		// kuink_mydebug($objectname.'->'.$propertyname, $return);
		
		return $return;
	}
	function inst_getconfig($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$configkey = $this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, true );
		
		$config = $nodeconfiguration [NodeConfKey::CONFIG];
		
		$value = '';
		
		if (isset ( $config [$configkey] ))
			$value = ( string ) $config [$configkey];
		else {
			// get the value from fw_config
			$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
			$params ['_entity'] = 'fw_config';
			$params ['id_company'] = $variables ['USER'] ['idCompany'];
			$params ['code'] = $configkey;
			$resultset = $dataAccess->execute ( $params );
			if (isset ( $resultset ['value'] ))
				$value = ( string ) $resultset ['value'];
		}
		
		return $value;
	}
	function inst_gethtmlparam($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$paramname = $instruction_xmlnode ['name'];
		// print($paramname);
		$param = isset ( $_GET ["$paramname"] ) ? ( string ) $_GET ["$paramname"] : '';
		// print( $param );
		
		return $param;
	}
	function inst_nativecode($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$code = $instruction_xmlnode [0];
		// var_dump( $instruction_xmlnode );
		// kuink_mydebug('Executing Native Code', $code );
		eval ( $code );
		
		return null;
	}
	function inst_xml($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// var_dump($code= $instruction_xmlnode[0]);
		return $instruction_xmlnode [0];
	}
	function inst_guid($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$utils = new \UtilsLib ( $nodeconfiguration, null );
		$newGuid = $utils->GuidClean ( null );
		return ( string ) $newGuid;
	}
	function inst_now($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$lib = new \DateTimeLib ( $nodeconfiguration, null );
		$result = $lib->Now ( null );
		return ( string ) $result;
	}
	function inst_string($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$parse = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'parse', $variables, false, 'false' );
		// $content= $instruction_xmlnode[0];
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$content = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$content = $instruction_xmlnode [0];
		
		if ($parse == 'true') {
			$eval = new \Kuink\Core\EvalExpr ();
			$content = $eval->e ( $content, $variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		}
		return ( string ) $content;
	}

	/*
	function inst_int($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$parse = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'parse', $variables, false, 'false' );
		// $content= $instruction_xmlnode[0];
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$content = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$content = $instruction_xmlnode [0];
		
		if ($parse == 'true') {
			$eval = new \Kuink\Core\EvalExpr ();
			$content = $eval->e ( $content, $variables, FALSE, FALSE, FALSE ); // Eval and return a value without ''
		}
		return ( int ) $content;
	}	*/

	function inst_sleep($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$interval = ( int ) $this->get_inst_attr ( $instruction_xmlnode, 'interval', $variables, false, 'false' );
		sleep ( $interval );
		return '';
	}
	function inst_transaction($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		\Kuink\Core\DataSourceManager::beginTransaction ();
		
		$instructions = $instruction_xmlnode->children ();
		// var_dump($instructions);
		$returnValue = '';
		if ($instructions->count () == 0)
			$returnValue = ( string ) $instruction_xmlnode [0];
		
		foreach ( $instructions as $new_instruction ) {
			$returnValue = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $new_instruction [0], $actionname, $variables, $exit );
		}
		
		\Kuink\Core\DataSourceManager::commitTransaction ();
		return $returnValue;
	}
	function inst_template($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$name = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		$language = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'lang', $variables, false, $variables ['USER'] ['lang'] );
		
		// Parse the name with variables inside
		$eval = new \Kuink\Core\EvalExpr ();
		$name = $eval->e ( $name, $variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		
		$nameParts = explode ( ',', $name );
		if (count ( $nameParts ) != 3)
			throw new \Exception ( 'Template: name must be method or appName,processName,template' );
		
		$application = (trim ( $nameParts [0] ) == 'this') ? $nodeconfiguration [NodeConfKey::APPLICATION] : trim ( $nameParts [0] );
		$process = (trim ( $nameParts [1] ) == 'this') ? $nodeconfiguration [NodeConfKey::PROCESS] : trim ( $nameParts [1] );
		$template = trim ( $nameParts [2] );
		
		$params [] = $name;
		$params [] = $language;
		$tParams = $this->aux_get_named_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$paramsvar = (isset ( $instruction_xmlnode ['params'] )) ? ( string ) $instruction_xmlnode ['params'] : '';
		$tParamsAttr = ($paramsvar != '') ? $variables [$paramsvar] : array ();
		foreach ( $tParamsAttr as $tKey => $tValue )
			$tParams [$tKey] = $tValue;
		
		$params [] = $tParams;
		
		$tl = new \TemplateLib ( $nodeconfiguration, null );
		$result = $tl->ExecuteStandardTemplate ( $params );
		
		return $result;
	}
	function genericInstExecute($nodeConfiguration, $nodeXml, $actionXmlNode, $instructionXmlNode, $actionName, &$variables, &$exit) {
		$instManager = new \Kuink\Core\InstructionManager ( $this, $this->nodeManager, $nodeConfiguration, $variables);
		$result = $instManager->execute ( $instructionXmlNode );
		
		// Update the variables
		$variables = $instManager->variables;
		$exit = $instManager->exit;
		
		return $result;
	}
	function inst_script($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_TRACE;
		$src = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'source', $variables, true );
		$user = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'user', $variables, false, '' );
		$password = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'password', $variables, false, '' );
		
		$params = array ();
		$tParams = $this->aux_get_param_values_complete ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		
		$paramsvar = (isset ( $instruction_xmlnode ['params'] )) ? ( string ) $instruction_xmlnode ['params'] : '';
		$tParamsAttr = ($paramsvar != '') ? $variables [$paramsvar] : array ();
		foreach ( $tParamsAttr as $tKey => $tValue )
			$tParams [] = $tValue;
		
		$params = $tParams;
		
		$scriptParams = '';
		foreach ( $params as $value )
			$scriptParams = $scriptParams . $value . ' ';
		
		if ($user != '')
			$script = 'echo ' . $password . ' | ' . 'sudo ' . $user . ' -S sh ' . $src . ' ' . $scriptParams;
		else
			$script = 'sh ' . $src . ' ' . $scriptParams;
		$KUINK_TRACE [] = 'Shell Script: ' . $script;
		// $result = shell_exec($script);
		// $output = array();
		$result = exec ( $script, $output );
		// $result = shell_exec($script);
		// $KUINK_TRACE[] = 'Result: '.nl2br($result);
		$KUINK_TRACE [] = $output;
		
		return $output;
	}
	
	/*
	 * function inst_datasource( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname,$instructionname, &$variables, &$exit )
	 * {
	 * //global $KUINK_DATASOURCES;
	 * $dsName = (string)$this->get_inst_attr($instruction_xmlnode, 'name', $variables, true);
	 * $dsConnector = (string)$this->get_inst_attr($instruction_xmlnode, 'connector', $variables, false, '');
	 *
	 * if ($dsConnector == '') {
	 * //This is a get of properties instead of a creation of a datasource
	 * //var_dump($KUINK_DATASOURCES);
	 * if (DataSourceManager::dataSourceExists($dsName)) {
	 *
	 * $ds = DataSourceManager::getDataSource($dsName);
	 * //var_dump($ds);
	 * return $ds->params;
	 * }
	 * else
	 * throw new \Exception('Getting DataSource properties: DataSource not found '.$dsName);
	 * }
	 *
	 * $dsLoad = (string)$this->get_inst_attr($instruction_xmlnode, 'load', $variables, false, '');
	 *
	 * if (($dsConnector == '') && ($dsName == ''))
	 * throw new \Exception('Datasource must specify either connector or load attributes.');
	 *
	 * if ($dsLoad != '') {
	 * //Load the datasource from the database fw_datasource with code=the value of the load attribute
	 * $idCompany = $variables['USER']['idCompany'];
	 * DataSourceManager::addDataSourceFromDB($dsLoad, $idCompany, DataSourceContext::NODE);
	 * } else {
	 * $dsParams = $this->aux_get_named_param_values($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit);
	 * DataSourceManager::addDataSource($dsName, $dsConnector, DataSourceContext::NODE, $dsParams);
	 * }
	 *
	 * //var_dump($KUINK_DATASOURCES);
	 * return $dsParams;
	 * }
	 */
	function inst_execute($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_CFG;
		
		if ($KUINK_CFG->useNewDataAccessInfrastructure)
			return $this->inst_dataAccess ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		
		global $KUINK_TRACE;
		$method = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'method', $variables, true );
		$database = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'database', $variables, false, '' );
		// var_dump( (string)$method );
		$KUINK_TRACE [] = 'Method: ' . $method;
		
		$paramsvar = ( string ) $instruction_xmlnode ['params'];
		// var_dump( (string)$paramsvar );
		
		$customappname = $nodeconfiguration [NodeConfKey::APPLICATION];
		$master_process_name = $nodeconfiguration [NodeConfKey::PROCESS];
		
		$datasource = new \Kuink\Core\DataSource ( null, $method, $customappname, $master_process_name, $database );
		
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		// var_dump($instruction_xmlnode);
		
		$params = null;
		$pks = null;
		foreach ( $paramsxml as $param ) {
			$paramname = ( string ) $this->get_inst_attr ( $param, 'name', $variables, true );
			$param_wildcard = ( string ) $this->get_inst_attr ( $param, 'wildcard', $variables, false );
			$pk = isset ( $param ['pk'] ) ? ( string ) $param ['pk'] : '';
			// print('PARAM');
			if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$value = $param [0];
			
			if (! empty ( $param_wildcard ) && ! empty ( $value )) {
				switch ($param_wildcard) {
					case 'full' :
						$value = '%' . str_replace ( ' ', '%', $value ) . '%';
						break;
					case 'left' :
						$value = '%' . $value;
						break;
					case 'right' :
						$value = $value . '%';
						break;
					case 'fullSplit' :
						$in = false;
						$splitedValue = '';
						$splited = array ();
						for($i = 0; i < length ( $value ); $i ++) {
							if ($value [$i] == '"')
								$in = ! ($in);
							
							if ($value [$i] == ' ' && ! $in) {
								$splited [] = '%' . splitedValue . '%';
								$splitedValue = '';
							} else if ($value [$i] != '"')
								$splitedValue .= $value [$i];
						}
						//var_dump ( $splited );
						$value = $splited;
						
						/*
						 * $plited = explode(' ', $value);
						 * $arr = array();
						 * foreach ($plited as $partial)
						 * $arr[] = '%'.$partial.'%';
						 * $value = $arr;
						 */
						break;
					default :
						throw new \Exception ( 'Invalid wildcard value:' . $param_wildcard );
				}
			}
			
			if ($pk != '')
				$pks [$paramname] = $pk;
			
			$params [$paramname] = (is_array ( $value )) ? $value : ( string ) $value;
			// $params[ $paramname ] = (is_array($value)) ? $value : (string)mysql_escape_string($value);
			// $params[ $paramname ] = (string)$value;
		}
		
		// Adding params if variable is defined
		if ($paramsvar != "") {
			$var = $variables [$paramsvar];
			foreach ( $var as $key => $value )
				if ($key != 'CHANGED')
					/**
					 * joao.patricio 16-01-2014 -> when passed params directly from postada and postdata has javascript variable changed *
					 */
					$params ["$key"] = $value;
		}
		
		// var_dump($params);
		$resultset = $datasource->execute ( $params, $pks );
		
		// print('RESULTADO');'full'
		// var_dump($resultset);
		
		return $resultset;
	}
	function inst_dataAccess($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_TRACE;
		$dataAccessNid = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'method', $variables, true );
		$dataSourceName = ( string ) $this->get_inst_attr ( $instruction_xmlnode, 'datasource', $variables, false );
		// $database = (string)$this->get_inst_attr($instruction_xmlnode, 'database', $variables, false,'');
		
		// var_dump( (string)$method );
		$KUINK_TRACE [] = 'DataAccess Execute: ' . $dataAccessNid;
		
		$paramsvar = ( string ) $instruction_xmlnode ['params'];
		// var_dump( (string)$paramsvar );
		
		$customappname = $nodeconfiguration [NodeConfKey::APPLICATION];
		$master_process_name = $nodeconfiguration [NodeConfKey::PROCESS];
		
		$paramsxml = $instruction_xmlnode->xpath ( './Param' );
		
		// var_dump($instruction_xmlnode);
		
		$params = null;
		$pks = null;
		foreach ( $paramsxml as $param ) {
			$paramname = ( string ) $this->get_inst_attr ( $param, 'name', $variables, true );
			$param_wildcard = ( string ) $this->get_inst_attr ( $param, 'wildcard', $variables, false );
			$pk = isset ( $param ['pk'] ) ? ( string ) $param ['pk'] : '';
			$paramValue = ( string ) $this->get_inst_attr ( $param, 'value', $variables, false );
			if ($paramValue != '') {
				$value = $paramValue;
			} else {
				if ($param->count () > 0) {
					$newinstruction_xmlnode = $param->children ();
					$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
				} else
					$value = $param [0];
			}
			
			if (! empty ( $param_wildcard ) && ! empty ( $value )) {
				switch ($param_wildcard) {
					case 'full' :
						$value = '%' . str_replace ( ' ', '%', $value ) . '%';
						break;
					case 'left' :
						$value = '%' . $value;
						break;
					case 'right' :
						$value = $value . '%';
						break;
					case 'fullSplit' :
						$in = false;
						$splitedValue = '';
						$splited = array ();
						$len = strlen ( $value );
						for($i = 0; $i < $len; $i ++) {
							// var_dump($value[$i]);
							if ($value [$i] == '"')
								$in = ! ($in);
							
							if ($value [$i] == ' ' && ! $in) {
								$splited [] = '%' . $splitedValue . '%';
								$splitedValue = '';
							} else if ($value [$i] != '"')
								$splitedValue .= $value [$i];
						}
						if ($splitedValue != '')
							$splited [] = '%' . $splitedValue . '%';
							// var_dump($splited);
						
						$value = $splited;
						/*
						 * $plited = explode(' ', $value);
						 * $arr = array();
						 * foreach ($plited as $partial)
						 * $arr[] = '%'.$partial.'%';
						 * $value = $arr;
						 */
						break;
					default :
						throw new \Exception ( 'Invalid wildcard value:' . $param_wildcard );
				}
			}
			
			if ($pk != '')
				$pks [] = $paramname;
				// var_dump($paramname);
				// var_dump($value);
			
				if (is_array($value) || is_object($value) || ($value==NULL) )
				$params [$paramname] = $value;
			else
				$params [$paramname] = ( string ) $value;
			
			// var_dump($params[$paramname]);
			// $params[ $paramname ] = (is_array($value)) ? $value : (string)$value;
		}
		
		// Adding params if variable is defined
		if ($paramsvar != "") {
			$var = $variables [$paramsvar];
			foreach ( $var as $key => $value )
				$params ["$key"] = $value;
		}
		
		// Adding the _pk parameter
		// var_dump($pks);
		$params ['_pk'] = isset($pks) ? implode ( ',', $pks ) : null;
		
		// var_dump($dataAccessNid);
		// var_dump($nodeconfiguration);
		
		// Handle base template
		$_base = isset ( $params ['_base'] ) ? $params ['_base'] : null;
		if ($_base == 'true') {
			$dateTime = new \DateTimeLib ( $nodeconfiguration, null );
			unset ( $params ['_base'] );
			$params ['id_company'] = $variables ['USER'] ['idCompany'];
			
			if ($dataAccessNid == 'insert' || $dataAccessNid == 'execute') {
				$params ['_id_creator'] = $variables ['USER'] ['id'];
				$params ['_creation'] = $dateTime->Now ();
				$params ['_creation_ip'] = $variables ['USER'] ['ip'];
			}
			
			if ($dataAccessNid == 'insert' || $dataAccessNid == 'update' || $dataAccessNid == 'execute') {
				$params ['_id_updater'] = $variables ['USER'] ['id'];
				$params ['_modification'] = $dateTime->Now ();
				$params ['_modification_ip'] = $variables ['USER'] ['ip'];
			}
		}
		$method = (string)$instruction_xmlnode['method'];
		$dataAccess = new \Kuink\Core\DataAccess ( $dataAccessNid, $customappname, $master_process_name, $dataSourceName );
		$dataAccess->setUser($variables['USER']);
		$resultset = $dataAccess->execute ( $params );
		
		// var_dump($resultset);
		
		return $resultset;
	}
	function inst_actionvalue($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// check if this is a set or a get
		$set = (($instruction_xmlnode->count () > 0) || ($instruction_xmlnode [0] != ''));
		$value = '';
		// Are we setting a value or retrieving?
		if ($set) {
			if ($instruction_xmlnode->count () > 0) {
				$newinstruction_xmlnode = $instruction_xmlnode->children ();
				$value = ( string ) $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
				// var_dump($value);
			} else
				$value = ( string ) $instruction_xmlnode [0];
				
				// Update the actionvalue
			$nodeconfiguration [NodeConfKey::ACTION_VALUE] = $value;
		} else
			$value = $nodeconfiguration [NodeConfKey::ACTION_VALUE];
		
		return $value;
	}
	
	function inst_accessControlList( &$nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname,$instructionname,  &$variables, &$exit )
	{
		global $SESSION;
	
		//get the id_acl
		if ($instruction_xmlnode->count() > 0)
		{
			$newinstruction_xmlnode = $instruction_xmlnode->children();
			$idAcl = (string)$this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode[0], $actionname,  $variables, $exit);
		}
		else
			$idAcl = (string)$instruction_xmlnode[0];
	
		$this->buildAllCapabilities($idAcl,null,true);
		
		$roles = $nodeconfiguration[NodeConfKey::ROLES];
		$rolesAcl = $this->getAllRolesAcl($idAcl);
		//print_object($rolesAcl);
		foreach ($rolesAcl as $roleKey=>$roleValue)
			$roles[$roleKey] = 1;

		//$roles[$value] = 1;
		$nodeconfiguration[NodeConfKey::ROLES] = $roles;
		$this->nodeconfiguration[NodeConfKey::ROLES] = $roles;
		//print_object($nodeconfiguration[NodeConfKey::ROLES]);
		
		//$variables['ROLES'] = $this->nodeconfiguration[NodeConfKey::ROLES];
		$variables['CAPABILITIES'] = $this->nodeconfiguration[NodeConfKey::CAPABILITIES];
		$variables['ROLES'] = $this->nodeconfiguration[NodeConfKey::ROLES];
		$nodeconfiguration = $this->nodeconfiguration;

		return $idAcl;
	}	
	
	function inst_role(&$nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $SESSION;
		
		$custumapp_name = ( string ) $this->nodeconfiguration [NodeConfKey::APPLICATION];
		$process_name = ( string ) $this->nodeconfiguration [NodeConfKey::PROCESS];
		
		// Set the role
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$value = ( string ) $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$value = ( string ) $instruction_xmlnode [0];
			
			// kuink_mydebug('Role', $value);
		
		$clear = $this->get_inst_attr ( $instruction_xmlnode, 'clear', $variables, false );
		// $node_roles = $nodeconfiguration[NodeConfKey::NODE_ROLES];
		$roles = $nodeconfiguration [NodeConfKey::ROLES];
		
		$currentStackRoles = ProcessOrchestrator::getNodeRoles ();
		
		if ($clear == 'true') {
			if ($value == '') { // clear all
			                  // $node_roles = $this->getNodeRoles($roles, $this->node_xml);
			                  // TODO:: remove the dynamic roles
				if (isset($currentRoles) && is_array($currentRoles))
					foreach ( $currentRoles as $roleToDelete => $valueToDelete ) {
						unset ( $roles [$roleToDelete] );
						unset ( $currentStackRoles [$roleToDelete] );
					}
				ProcessOrchestrator::setNodeRoles ( $currentStackRoles );
				// unset($_SESSION['KUINK_ROLES'][$custumapp_name.$process_name]);
			} else { // clear just this role
				unset ( $roles [$value] );
				// unset($_SESSION['KUINK_ROLES'][$custumapp_name.$process_name][$value]);
			}
		} else {
			// The $value contains the role name to add
			$roles [$value] = 1;
			$currentStackRoles [$value] = 1;
			ProcessOrchestrator::setNodeRoles ( $currentStackRoles );
		}
		// var_dump($_SESSION['KUINK_ROLES']);
		// var_dump( $node_roles );
		$nodeconfiguration [NodeConfKey::ROLES] = $roles;
		$this->nodeconfiguration = $nodeconfiguration;
		$action_permissions = $this->getActionPermissions ( $nodexml );
		$nodeconfiguration [NodeConfKey::ACTION_PERMISSIONS] = $action_permissions;
		$this->nodeconfiguration = $nodeconfiguration;
		
		// var_dump($roles);
		// var_dump($nodeconfiguration);
		$this->buildAllCapabilities ();
		$variables['ROLES'] = $this->nodeconfiguration[NodeConfKey::ROLES];
		$variables['CAPABILITIES'] = $this->nodeconfiguration[NodeConfKey::CAPABILITIES];		
		return $value;
	}
	function inst_capability(&$nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$capabilities = $nodeconfiguration [NodeConfKey::CAPABILITIES];
		$name = $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		
		$value = isset ( $capabilities [$name] ) ? 1 : 0;
		return $value;
	}
	function inst_registerapi(&$nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$content = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$content = $instruction_xmlnode [0];
		
		\Kuink\Core\ProcessOrchestrator::registerAPI ( ( string ) $content );
		
		return ( string ) $content;
	}
	function getCompressedVarValue($var) {
		$session = false;
		
		$session = ($var [0] == '#');
		
		$varname = substr ( $string, 1 );
	}
	
	function inst_var($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $SESSION;
		
		$varname = $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		$clear = $this->get_inst_attr ( $instruction_xmlnode, 'clear', $variables, false, 'false' );
		$sum = $this->get_inst_attr ( $instruction_xmlnode, 'sum', $variables, false, 0 );
		$dump = $this->get_inst_attr ( $instruction_xmlnode, 'dump', $variables, false, 'false' );
		$key = $this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, false );
		$keyIsSet = isset ( $instruction_xmlnode ['key'] ); // We must know if the key is set or not besides its value
		$setValue = isset ( $instruction_xmlnode ['value'] ) ? (string)$instruction_xmlnode ['value'] : null;
		$set = (($instruction_xmlnode->count () > 0) || ($instruction_xmlnode [0] != ''));
		
		$session = $this->get_inst_attr ( $instruction_xmlnode, 'session', $variables, false, 'false' ); // Session variable
		$process = $this->get_inst_attr ( $instruction_xmlnode, 'process', $variables, false, 'false' ); // Process Variable
		                                                                                              
		// Clear the variable
		if ($clear == 'true') {
			if ($session == 'true') {
				ProcessOrchestrator::unsetSessionVariable ( $varname, $key );
			} else if ($process == 'true') {
				ProcessOrchestrator::unsetProcessVariable ( $varname, $key );
			} else { // local variable
			         // var_dump($variables[$varname]);
				if ($key != '') {
					// $varCopy = $variables[$varname];
					// unset( $varCopy[$key] );
					// $variables[$varname] = $varCopy;
					// $keyToClear = &$variables[$varname];
					// $keyToClear[$key] = null;
					unset ( $variables [$varname] [$key] );
					// $variables[$varname] = array_slice($variables[$varname],0,count($variables[$varname])-1);
					// var_dump($variables[$varname]);
				} else
					unset ( $variables [$varname] );
			}
		}
		
		// Get the current variable value
		$value = null;
		if ($session == 'true') {
			// $value = ($key != '') ? $_SESSION['KUINK_CONTEXT']['VARIABLES'][$varname][$key] : $_SESSION['KUINK_CONTEXT']['VARIABLES'][$varname];
			$value = ProcessOrchestrator::getSessionVariable ( $varname, $key );
		} else if ($process == 'true') {
			$value = ProcessOrchestrator::getProcessVariable ( $varname, $key );
		} else { // local variable
			$variable = isset ( $variables [$varname] ) ? $variables [$varname] : null;
			if (gettype ( $variable ) == 'array') {
				if (count ( $variable ) == 1) {
					reset ( $variable );
					$aux = current ( $variable );
					if (gettype ( $aux ) == 'object')
						$variable = ( array ) $aux;
				}
			} else
				$variable = ( array ) $variable;
				
				// $value = ($key == '') ? $variable : $variable[$key];
			$value = '';
			switch ($key) {
				case '' : $value = isset($variables [$varname]) ? $variables [$varname] : ''; break;
				case '__first' : $value = array_values ( $variables [$varname] ) [0]; break;
				case '__length' : $value = count ( $variables [$varname] ); break;
				default :
				$value = isset($variables [$varname] [$key]) ? $variables [$varname] [$key] : null; 
				//$value = $variables [$varname] [$key];
			}
		}
		
		if ($set) {
			if ($instruction_xmlnode->count () > 0) {
				$newinstruction_xmlnode = $instruction_xmlnode->children ();
				
				$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
			} else
				$value = ( string ) $instruction_xmlnode [0];
		} else if ($setValue != '') {
			// Parse the value!!
			$eval = new \Kuink\Core\EvalExpr ();
			$value = $eval->e ( $setValue, $variables, FALSE ); // NOT BOOLEAN
		}
		
		// Sum the value
		if ($sum != 0) {
			$value = ( int ) $value + $sum;
		}
		
		// Cleanup unncessary spaces
		$value = (! is_array ( $value ) && (! is_object ( $value )) && $value != null) ? trim ( $value ) : $value;
		
		// Dumping variable
		if ($dump == 'true') {
			$this->dumpVariable ( $varname, $value );
		}

		//Only set the value if this is a set...
		if ($set || $setValue != '' || $sum <> 0) {
			//Setting the value in the variable
			if ( $session == 'true' ) {
				ProcessOrchestrator::setSessionVariable($varname, $key, $value);
			} else if ($process == 'true') {
				ProcessOrchestrator::setProcessVariable($varname, $key, $value);
			} else { //local variable
				$varExists = isset($variables[$varname]);
				if ($keyIsSet && $key != '') {
						$var = ($varExists) ? $variables[$varname] : array();
						$var[$key]= (is_array($value) || $value == null) ? $value : (string)$value;
						$variables[$varname] = $var;
				} else if ($keyIsSet && $key == '') {
						//Add an array entry
						$var = ($varExists) ? $variables[$varname] : array();
						$var[]= (is_array($value)) ? $value : (string)$value;
						$variables[$varname] = $var;
				} else
					$variables[$varname] = $value;
			}
		}
		// Allways return the variable value
		return $value;
	}
	function dumpVariable($var, $value) {
		global $KUINK_MANUAL_TRACE;
		
		$msg = '<xmp class="prettyprint linenums">' . $var . '::';
		$msg .= var_export ( $value, true );
		$msg .= '</xmp>';
		$KUINK_MANUAL_TRACE [] = $msg;
		// $layout = \Kuink\UI\Layout\Layout::getInstance();
		// $layout->addHtml('<pre class="prettyprint linenums">'.$var.'::'.(string)var_export($value, true).'</pre>', 'debugMessages');
	}
	function inst_adddatasource($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		// print('ADD DATASOURCE');
		$loadobject = $instruction_xmlnode ['object'];
		$loadobjectname = ( string ) $instruction_xmlnode ['name'];
		$dsname = ( string ) $instruction_xmlnode ['dsname'];
		// var_dump( $instruction_xmlnode );
		// kuink_mydebug('$loadobject', $loadobject );
		// kuink_mydebug('$loadobjectname', $loadobjectname );
		// kuink_mydebug('BINDING: '.$loadobjectname, $loadobjectname );
		
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			$value = $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		} else
			$value = $param [0];
			
			// print('OBJECT####:::');
			// var_dump( $value );
		
		switch ($loadobject) {
			case 'form' :
				$mform = $variables [$loadobjectname];
				$mform->add_datasource ( $value, $dsname );
				break;
			case 'grid' :
				throw new \Exception ( "Grid object does no support adddatasource" );
				break;
			case 'feed' :
				throw new \Exception ( "Feed object does no support adddatasource" );
				break;
			
			default :
				throw new \Exception ( "Instruction: $instructionname. Object $loadobject does not exists. " );
		}
		
		return null;
	}
	function inst_redirect($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		$global = $this->get_inst_attr ( $instruction_xmlnode, 'global', $variables, false, 'false' );
		
		// Execute inner instructions
		$newinstruction_xmlnode = $instruction_xmlnode->children ();
		if (count ( $newinstruction_xmlnode ) > 0)
			$url = ( string ) $this->instruction_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode [0], $actionname, $variables, $exit );
		else
			$url = ( string ) $instruction_xmlnode [0];
		
		if ($global == 'false')
			redirect ( $url, '', 0 );
		else
			redirect ( $url, '', 1 );
		
		return $url;
	}
	function inst_doaction_direct($nodeconfiguration, $nodexml, $instruction_xmlnode, $instructionname, &$variables, $exit) {
		$actionname = $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		$action_xmlnode = $this->action_get_xmlnode ( $nodeconfiguration, $nodexml, $actionname );
		
		// Execute this action
		$this->action_execute ( $nodeconfiguration, $nodexml, $action_xmlnode, $actionname, $variables );
		
		return null;
	}
	function inst_doaction($nodeconfiguration, $nodexml, $instruction_xmlnode, $instructionname, &$variables, $exit) {
		global $SESSION, $KUINK_LAYOUT;
		$actionname = $this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		// $redirect = (string)$instruction_xmlnode['redirect'];
		
		// TODO - Change the ActionValue if the instruction says to
		
		$action_xml = $nodexml->xpath ( '/Node/Actions/Action[@name="' . $actionname . '"]' );
		
		if (! $action_xml)
			throw new \Exception ( 'Action ' . $actionname . ' does not exists.' );
		$action_screen = ( string ) $action_xml [0] ['screen'];
		$post_form = isset ( $_GET ['form'] ) ? ( string ) $_GET ['form'] : '';
		// print('Action::'.$actionname.' Screen::'.$action_screen.' Post::'.$post_form);
		
		// Chech to see if the screen have a form with this name
		$form_xml = $nodexml->xpath ( '/Node/Screens/Screen[@id="' . $action_screen . '"]/Form[@name="' . $post_form . '"]' );
		// var_dump($form_xml);
		// die();
		
		// Note: the redirect was not necessary since $_POST is cleaned up and forms work fine now
		$redirect = true;
		$clearPost = false;
		
		$msg_manager = \Kuink\Core\MessageManager::getInstance ();
		if (! $msg_manager->has_type ( \Kuink\Core\MessageType::ERROR )) {
			// $redirect = ($action_screen != '' && $post_form != '' && !$form_xml);
			$clearPost = ($action_screen != '' && $post_form != '' && ! $form_xml);
		}
		if ($redirect) {
			// var_dump('REDIRECT:...');
			$params = array ();
			$params [] = $actionname;
			$utils = new \UtilsLib ( $nodeconfiguration, null );
			$actionUrl = $utils->ActionUrl ( $params );
			
			/*
			 * $currentNode = ProcessOrchestrator::getCurrentNode();
			 * $currentNode->action = $actionname;
			 * $currentNode->url .= '&action='.$actionname;
			 * ProcessOrchestrator::setNode( $currentNode );
			 * $actionUrl = $currentNode->url;
			 */
			// $currentNode = ProcessOrchestrator::getCurrentNode();
			// $KUINK_LAYOUT->setRedirectHeader( $currentNode->url );
		}
		
		// Clear the post
		if ($clearPost) {
			// Clean up post data
			unset ( $variables ['POSTDATA'] );
			foreach ( $_POST as $key => $value )
				if ($key != 'sesskey')
					unset ( $_POST [$key] );
		}
		
		// Execute the action without a browser redirect
		$this->inst_doaction_direct ( $nodeconfiguration, $nodexml, $instruction_xmlnode, $instructionname, $variables, $exit );
		return null;
		
		/*
		 * $baseurl = $nodeconfiguration['baseurl'];
		 * //Build the url and replace the action value when changed...
		 * $url = new \moodle_url($baseurl, array ('actionvalue' => $nodeconfiguration['actionvalue']));
		 * $baseurl = $url->out(false);
		 *
		 * //Store the user messages in session to be shown in the next screen
		 * redirect($baseurl . '&action='.$actionname, '', 0);
		 *
		 * return null;
		 */
	}
	function inst_dopdf($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_CFG;
		// $content= $instruction_xmlnode[0];
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			// TODO STI: uncomment after defined the metadata
			// $content = $this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode[0], $actionname, &$variables, $exit);
		} else
			$content = $instruction_xmlnode [0];
		
		$paper = $this->get_inst_attr ( $instruction_xmlnode, 'paper', $variables, false, 'a4' );
		$orientation = $this->get_inst_attr ( $instruction_xmlnode, 'orientation', $variables, false, 'portrait' );
		
		// if no path is supplied go to tmp file
		$path = $this->get_inst_attr ( $instruction_xmlnode, 'path', $variables, false, 'tmp/' );
		// handle dupplication of /
		$path .= '/';
		
		// if register then the file will be inserted in file and the file id will be returned
		$register = $this->get_inst_attr ( $instruction_xmlnode, 'register', $variables, false, 'false' );
		// By default the file will not be downloaded
		$download = $this->get_inst_attr ( $instruction_xmlnode, 'download', $variables, false, 'true' );
		
		// The file should be overriden if exists?
		$override = $this->get_inst_attr ( $instruction_xmlnode, 'override', $variables, false, 'true' );
		$guid = new \UtilsLib ( $nodeconfiguration, null );
		$guid = $guid->GuidClean ( null );
		// If the filename is not supplied then return a guid
		$filename = $this->get_inst_attr ( $instruction_xmlnode, 'filename', $variables, false, $guid );
		$filename = $filename . '.pdf';
		
		$html = '
		<style>
		table *, p {

		font-size: 11pt;
	}
	table,tr,th,td {

	border: 1px solid #000;
	}
	.full-border {
	border: 1px solid $dcdcdc;
	}

	</style>
	';
		foreach ( $this->current_controls as $control ) {
			$controlHtml = $control->getHtml ();
			// $controlHtml='';
			$html .= $controlHtml;
		}
		// create new PDF document
		$pdf = new \TCPDF ( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );
		
		// set document information
		$meta_creator = ( string ) $this->get_meta_value ( 'creator', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_author = ( string ) $this->get_meta_value ( 'author', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_title = ( string ) $this->get_meta_value ( 'title', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_subject = ( string ) $this->get_meta_value ( 'subject', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_keywords = $this->get_meta_value ( 'keywords', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		
		$meta_keywords_list = implode ( ',', $meta_keywords );
		
		/*
		 * kuink_mydebug('creator', $meta_creator);
		 * kuink_mydebug('author', $meta_author);
		 * kuink_mydebug('title', $meta_title);
		 * kuink_mydebug('subject', $meta_subject);
		 * kuink_mydebug('keywords', $meta_keywords_list);
		 */
		
		$pdf->SetCreator ( $meta_creator );
		$pdf->SetAuthor ( $meta_author );
		$pdf->SetTitle ( $meta_title );
		$pdf->SetSubject ( $meta_subject );
		$pdf->SetKeywords ( $meta_keywords_list );
		
		// set default header data
		$pdf->SetHeaderData ( 'logo.jpeg', 50, "<Title>", "<SubTitle>" . date ( "Y-m-d H:i:s" ) );
		
		// set header and footer fonts
		$pdf->setHeaderFont ( Array (
				PDF_FONT_NAME_MAIN,
				'',
				PDF_FONT_SIZE_MAIN 
		) );
		$pdf->setFooterFont ( Array (
				PDF_FONT_NAME_DATA,
				'',
				PDF_FONT_SIZE_DATA 
		) );
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
		
		// set margins
		$pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
		$pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
		$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );
		
		// set auto page breaks
		$pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );
		
		// set image scale factor
		$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
		
		// set some language-dependent strings
		$pdf->setLanguageArray ( $l );
		
		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting ( true );
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont ( 'helvetica', '', 14, '', true );
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage ();
		
		// Print text using writeHTMLCell()
		$pdf->writeHTMLCell ( $w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true );
		
		$config = $nodeconfiguration [NodeConfKey::CONFIG];
		
		$base_upload = $KUINK_CFG->uploadRoot;
		$upload_dir = $base_upload . '/' . $path;
		
		// Handle dupplication of slashes in configurations
		$upload_dir = str_replace ( '//', '/', $upload_dir );
		
		$myFile = $KUINK_CFG->dataRoot . '/' . $upload_dir . $filename;
		// print($myFile);
		
		/*
		 * kuink_mydebug('path', $path);
		 * kuink_mydebug('register', $register);
		 * kuink_mydebug('filename', $filename);
		 * kuink_mydebug('download', $download);
		 * kuink_mydebug('myFile', $myFile);
		 */
		
		// Create the path if the directory doesn't exist
		if (! is_dir ( $KUINK_CFG->dataRoot . '/' . $upload_dir )) {
			$dir_parts = explode ( '/', $upload_dir );
			$sub_dirs = '/';
			foreach ( $dir_parts as $dir ) {
				// kuink_mydebug('Creating...', $KUINK_CFG->dataRoot.$sub_dirs.$dir);
				if (! is_dir ( $KUINK_CFG->dataRoot . $sub_dirs . $dir ))
					mkdir ( $KUINK_CFG->dataRoot . $sub_dirs . $dir );
				$sub_dirs .= $dir . '/';
			}
		}
		
		$flag = ($override == 'true') ? 'w+' : 'x+';
		
		$fh = fopen ( $myFile, $flag ) or die ( "can't open file. The file is not marked to be overriden." );
		$stringData = $pdf->Output ( 'example_001.pdf', 'S' );
		fwrite ( $fh, $stringData );
		fclose ( $fh );
		
		$id_file = null;
		
		$utils = new \UtilsLib ( $this->nodeconfiguration, \Kuink\Core\MessageManager::getInstance () );
		$file_guid = $utils->GuidClean ( null );
		
		if ($register == 'true') {
			// register the file in the database
			global $USER;
			
			$original_name = $filename;
			$path = $upload_dir;
			$name = $filename;
			$size = filesize ( $myFile );
			$ext = 'pdf';
			$mime = 'application/pdf';
			$id_user = ( string ) $variables ['USER'] ['id'];
			$desc = '';
			
			$filelib = new \FileLib ( $this->nodeconfiguration, \Kuink\Core\MessageManager::getInstance () );
			$id_file = $filelib->register ( $original_name, $path, $name, $size, $ext, $mime, $id_user, $desc, $file_guid );
		}
		
		if ($download == 'true') {
			$handler = ($register == 'true') ? 'stream.php?type=file&guid=' . $file_guid : 'stream.php?type=tmp&guid=' . $filename;
			print '
			<script>
			// open the window
			windowpopup = window.open("' . $handler . '", "Documento", "scrollbars=yes");
			//windowpopup.close();
			</script>';
			// var_dump($dompdf->output());
			// $dompdf->stream("documento.pdf", array("Attachment" => 1));
		}
		
		return $id_file;
	}
	function inst_dopdfV2($nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, &$variables, &$exit) {
		global $KUINK_CFG;
		// $content= $instruction_xmlnode[0];
		if ($instruction_xmlnode->count () > 0) {
			$newinstruction_xmlnode = $instruction_xmlnode->children ();
			// TODO STI: uncomment after defined the metadata
			// $content = $this->instruction_execute ($nodeconfiguration, $nodexml, $action_xmlnode, $newinstruction_xmlnode[0], $actionname, &$variables, $exit);
		} else
			$content = $instruction_xmlnode [0];
		
		$paper = $this->get_inst_attr ( $instruction_xmlnode, 'paper', $variables, false, 'a4' );
		$orientation = $this->get_inst_attr ( $instruction_xmlnode, 'orientation', $variables, false, 'portrait' );
		$unit = $this->get_inst_attr ( $instruction_xmlnode, 'unit', $variables, false, 'mm' );
		
		// if no path is supplied go to tmp file
		$path = $this->get_inst_attr ( $instruction_xmlnode, 'path', $variables, false, 'tmp/' );

		// handle dupplication of /
		$path .= '/';
		
		// if register then the file will be inserted in file and the file id will be returned
		$register = $this->get_inst_attr ( $instruction_xmlnode, 'register', $variables, false, 'false' );
		// By default the file will not be downloaded
		$download = $this->get_inst_attr ( $instruction_xmlnode, 'download', $variables, false, 'true' );
		
		$marginLeft = $this->get_inst_attr ( $instruction_xmlnode, 'marginleft', $variables, false, '5' );
		$marginRight = $this->get_inst_attr ( $instruction_xmlnode, 'marginright', $variables, false, '5' );
		$marginTop = $this->get_inst_attr ( $instruction_xmlnode, 'margintop', $variables, false, '5' );
		$marginBottom = $this->get_inst_attr ( $instruction_xmlnode, 'marginbottom', $variables, false, '10' );
		
		// Header defaults to false
		$header = $this->get_inst_attr ( $instruction_xmlnode, 'header', $variables, false, 'false' );
		// Footer defaults to true
		$footer = $this->get_inst_attr ( $instruction_xmlnode, 'footer', $variables, false, 'true' );
		
		// Get the background image
		$background = $this->get_inst_attr ( $instruction_xmlnode, 'background', $variables, false, '' );
		
		// The file should be overriden if exists?
		$override = $this->get_inst_attr ( $instruction_xmlnode, 'override', $variables, false, 'true' );
		$guid = new \UtilsLib ( $nodeconfiguration, null );
		$guid = $guid->GuidClean ( null );
		// If the filename is not supplied then return a guid
		$filename = $this->get_inst_attr ( $instruction_xmlnode, 'filename', $variables, false, $guid );
		$filename = $filename . '.pdf';
		
		/*
		 * foreach ($this->current_controls as $control) {
		 * //neon_mydebugxml( $control->name, '');
		 * $controlHtml = $control->getHtml();
		 * //neon_mydebugxml( $control->name, $controlHtml);
		 * //$controlHtml='';
		 * $html .= $controlHtml;
		 * }
		 */
		$params = $this->aux_get_named_param_values ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$html = $params ['content'];
		// create new PDF document
		
		// $pdf = new \TCPDF($orientation, $unit, $paper, true, 'UTF-8', false, false);
		$pdf = new \KuinkPDF ( $orientation, $unit, $paper, true, 'UTF-8', false, false );
		
		// set document information
		$meta_creator = ( string ) $this->get_meta_value ( 'creator', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_author = ( string ) $this->get_meta_value ( 'author', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_title = ( string ) $this->get_meta_value ( 'title', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_subject = ( string ) $this->get_meta_value ( 'subject', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_keywords = $this->get_meta_value ( 'keywords', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		$meta_template_code = $this->get_meta_value ( 'template', $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		
		$pdf->SetCreator ( $meta_creator );
		$pdf->SetAuthor ( $meta_author );
		$pdf->SetTitle ( $meta_title );
		$pdf->SetSubject ( $meta_subject );
		$pdf->SetKeywords ( $meta_keywords );
		$pdf->setTemplateCode ( $meta_template_code );
		
		// set header and footer fonts
		$pdf->setHeaderFont ( Array (
				PDF_FONT_NAME_DATA,
				'',
				PDF_FONT_SIZE_DATA 
		) );
		$pdf->setFooterFont ( Array (
				PDF_FONT_NAME_DATA,
				'',
				PDF_FONT_SIZE_DATA 
		) );
		$pdf->SetHeaderMargin ( 0 );
		if ($header == 'false')
			$pdf->setPrintHeader ( false );
		if ($footer == 'false')
			$pdf->setPrintFooter ( false );
			
			// set margins
		
		$pdf_margin_top = $marginTop;
		$pdf_margin_right = $marginRight;
		$pdf_margin_bottom = $marginBottom;
		$pdf_margin_left = $marginLeft;
		
		$pdf->SetMargins ( $pdf_margin_left, $pdf_margin_top, $pdf_margin_right );
		
		$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );
		
		// set auto page breaks
		$pdf->SetAutoPageBreak ( TRUE, $pdf_margin_bottom );
		
		// set image scale factor
		$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
		
		// set some language-dependent strings
		// $pdf->setLanguageArray($l);
		
		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting ( true );
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont ( 'helvetica', '', 14, '', true );
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage ();
		
		// Setting background image if specified in parameters
		
		if ($background != '')
			if ($orientation == 'landscape')
				$pdf->Image ( $background, $marginLeft, $marginTop, 297, 210, '', '', '', false, 0, '', false, false, 0 );
			else
				$pdf->Image ( $background, $marginLeft, $marginTop, 210, 297, '', '', '', false, 0, '', false, false, 0 );
			
			// Print text using writeHTMLCell()
		$pdf->writeHTML ( $html, true, false, true, false, '' );
		
		$config = $nodeconfiguration [NodeConfKey::CONFIG];
		
		$base_upload = $KUINK_CFG->uploadRoot;
		$upload_dir = $base_upload . '/' . $path;
		
		// Handle dupplication of slashes in configurations
		$upload_dir = str_replace ( '//', '/', $upload_dir );
		
		$myFile = $upload_dir . $filename;
		
		// Create the path if the directory doesn't exist
		if (! is_dir ( $upload_dir )) {
			$dir_parts = explode ( '/', $upload_dir );
			$sub_dirs = '/';
			foreach ( $dir_parts as $dir ) {
				
				if (! is_dir ( $sub_dirs . $dir ))
					mkdir ( $sub_dirs . $dir );
				$sub_dirs .= $dir . '/';
			}
		}
		
		$flag = ($override == 'true') ? 'w+' : 'x+';
		
		$fh = fopen ( $myFile, $flag ) or die ( "can't open file. The file is not marked to be overriden." );
		$stringData = $pdf->Output ( 'example_001.pdf', 'S' );
		fwrite ( $fh, $stringData );
		fclose ( $fh );
		
		$id_file = null;
		
		$utils = new \UtilsLib ( $this->nodeconfiguration, \Kuink\Core\MessageManager::getInstance () );
		$file_guid = $utils->GuidClean ( null );
		$filelib = new \FileLib ( $this->nodeconfiguration, \Kuink\Core\MessageManager::getInstance () );
		if ($register == 'true') {
			// register the file in the database
			$original_name = $filename;
			//$path = $upload_dir;
			$name = $filename;
			$size = filesize ( $myFile );
			$ext = 'pdf';
			$mime = 'application/pdf';
			$id_user = ( string ) $variables ['USER'] ['id'];
			$desc = '';
			
			$id_file = $filelib->register ( $original_name, $path, $name, $size, $ext, $mime, $id_user, $desc, $file_guid );
		}
		
		if ($download == 'true') {
			$handler = ($register == 'true') ? 'stream.php?type=file&guid=' . $file_guid : 'stream.php?type=tmp&guid=' . $filename;
			print '
			<script>
			// open the window
			windowpopup = window.open("' . $handler . '", "Documento", "scrollbars=yes");
			//windowpopup.close();
			</script>';
		}
		
		return $id_file;
	}
	function get_meta_value($meta, $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit) {
		$meta_xml = $instruction_xmlnode->xpath ( './/Meta[@name="' . $meta . '"]' );
		if (count ( $meta_xml ) > 0)
			$meta = $this->aux_get_instruction_value ( $nodeconfiguration, $nodexml, $action_xmlnode, $meta_xml [0], $actionname, $instructionname, $variables, $exit );
		else
			$meta = '';
		
		return $meta;
	}
}
