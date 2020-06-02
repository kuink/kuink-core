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
		if (Configuration::getInstance()->defaults->user->id != null)
			$idNumber = Configuration::getInstance()->defaults->user->id;

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
		$personDa = new DataAccess ( 'framework/framework,user,person.get', 'framework', 'user' );
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
		$kuinkUser ['idExternal'] = Configuration::getInstance()->defaults->user->id; // $current_user->idexternal;
		$kuinkUser ['lang'] = Configuration::getInstance()->defaults->user->lang;

		// @todoSTI: Joao Patricio get this value from person table
		$kuinkUser ['timezone'] = Configuration::getInstance()->defaults->timezone;
		// get the client ip address
		$kuinkUser ['ip'] = $_SERVER ["REMOTE_ADDR"] ?? "N/A";

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

		$this->nodepath = Configuration::getInstance()->web->www_root . '/' . $appBase . '/' . $node->application . '/process/' . $node->process . '/' . $node_type . '/' . $node->process . '_' . $node->node . '.xml';
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
		$this->msg_manager = MessageManager::getInstance (); // \neon_msg_manager();

		$kuinkUser = new User ();
		$kuink_user = $kuinkUser->getUser ();

		// initialize kuink global variable USER
		$this->variables ['USER'] = $kuink_user;
		$this->nodeconfiguration [NodeConfKey::USER] = $kuink_user;

		// Insert the user variable in layout
		$layout = Layout::getInstance ();
		$layout->setGlobalVariable ( '_user', $kuink_user );
		$configuration = Configuration::getInstance();
		// initialize kuink global variable SERVER
		$server_info ['name'] = $_SERVER ['SERVER_NAME'] ?? "N/A";
		$server_info ['ip'] = $_SERVER ['SERVER_ADDR'] ?? "N/A";
		$server_info ['port'] = $_SERVER ['SERVER_PORT'] ?? "N/A";
		$server_info ['userAgent'] = $_SERVER ['HTTP_USER_AGENT'] ?? "N/A";
		$server_info ['wwwRoot'] = $configuration->web->www_root;
		$server_info ['appRoot'] = $configuration->paths->apps;
		$server_info ['apiUrl'] = $configuration->web->www_root.'/api.php';
		$server_info ['streamUrl'] = $configuration->web->www_root.'/stream.php';
		$server_info ['guestUrl'] = $configuration->web->www_root;
		$server_info ['baseUploadDir'] = $configuration->paths->upload_dir; //(isset($this->nodeconfiguration ) && isset($this->nodeconfiguration [NodeConfKey::CONFIG])) ? (string)$this->nodeconfiguration [NodeConfKey::CONFIG] ['uploadFolderBase'] : '';
		//$config = (isset($this->nodeconfiguration ) && isset($this->nodeconfiguration [NodeConfKey::CONFIG])) ? (string)($this->nodeconfiguration [NodeConfKey::CONFIG] ['uploadFolderBase']) : '';
		$server_info ['fullUploadDir'] = $configuration->paths->upload_dir; //$KUINK_CFG->dataRoot . '/' . $config;
		$server_info ['environment'] = $configuration->environment;

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
			if (empty($capabilities) || $force) {
				//print_object('NEW...');
				if (!empty($roles)) {
					$rolesFilter = array();
					foreach ($roles as $roleName => $roleValue) {
						$rolesFilter[] = '\''.$roleName.'\'';
					}
					$rolesFilterStr = implode(',', $rolesFilter);
					//print_object($rolesFilterStr);

					if (!(Configuration::getInstance()->kuink->use_global_acl))
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
			$dataAccess = new \Kuink\Core\DataAccess('framework,acl,getPermissions', 'framework', 'acl');
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
			$rolesCleaned = str_replace("'", '"', $roles); // substr($roles,1,strlen($roles)-2);
			$params ['role_codes'] = $rolesCleaned;
			$resultset = $dataAccess->execute ( $params );
			//var_dump($resultset);


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
		ProcessOrchestrator::registerAPI ( 'framework,ticket,api,add' );
		ProcessOrchestrator::registerAPI ( 'framework,ticket,api,getHandlers' );
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
			// die(var_dump($this));
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
				$managername = '\\Kuink\\Core\\Lib\\' . $libname;

				// Bypass formatters and controls
				if ($libtype == 'lib') {
					$manager = new $managername ( $this->nodeconfiguration, MessageManager::getInstance () );

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
			if (Configuration::getInstance()->kuink->use_global_acl)
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
				$msg_manager = MessageManager::getInstance();
				$msg_manager->add(MessageType::EXCEPTION,sprintf('Exception:: %s %s', $e->getMessage(), $e->getTraceAsString()));
			}
			//Rollback transactions
			//global $KUINK_TRACE;
			//print_object($KUINK_TRACE);
			//die();
			//print_object($e->getMessage());
			DataSourceManager::rollbackTransaction();

		}
		catch(\Exception $e) {
			//TODO: - Set a new entry automatically in bugtracking tool

			// - Resgister user, timestamp, application, process, node, action, variables, instruction, executionstack?!?
		      if ($function_name) {
			      //throw new \Exception($e->getMessage());
		      		throw $e;
		  		} else {
		      	$msg_manager = MessageManager::getInstance();
		        $msg_manager->add(MessageType::EXCEPTION,'Exception:: '. $e->getMessage());
		      }
					//Rollback transactions
					//print_object($e->getMessage());
		      DataSourceManager::rollbackTransaction();
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

			if (empty($KUINK_MANUAL_TRACE))
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


				//Check to see if this is a duplicated control
				if (isset($this->current_controls [$uielem->name]))
					throw new \Exception('Duplicated control '.$uielem->type.'::'.$uielem->name.' in screen '.$screen_name);
				// Load in $variables all the objects
				$variables [$uielem_name] = $uielem;
				$this->current_controls [$uielem->name] = $uielem;
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

		if ($instructionname == 'Var') {
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
			}
		}
		$instManager = new \Kuink\Core\InstructionManager ( $this, $this->nodeManager, $nodeconfiguration, $variables);
		$result = $instManager->execute ( $instruction_xmlnode );

		// Update the variables
		$variables = $instManager->variables;
		$exit = $instManager->exit;

		return $result;
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

	/**
	 * Returns the kuink application object
	 */
	function getApplication() {
		global $KUINK_APPLICATION;
		return $KUINK_APPLICATION;
	}

	/**
	 * Returns the kuink core object
	 */
	function getCore() {
		global $KUINK_APPLICATION;
		return $KUINK_APPLICATION->core;
	}

}
