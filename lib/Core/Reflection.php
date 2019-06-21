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

class Reflection {
	static function getBases() {
		global $KUINK_CFG;
		$applications = array ();
		$apps = self::directoryContents ( $KUINK_CFG->appRoot . '/apps' );
		foreach ( $apps as $app )
			$applications [] = array (
					'name' => ( string ) $app 
			);
		
		return $applications;
	}
	
	/**
	 * Retuns all the applications
	 */
	static function getApplications($base) {
		global $KUINK_CFG;
		
		$applications = array ();
		$apps = self::directoryContents ( $KUINK_CFG->appRoot . '/apps/' . $base );
		foreach ( $apps as $app )
			$applications [] = array (
					'name' => ( string ) $app 
			);
		
		return $applications;
	}
	
	/**
	 * Check if a folder is really an application by checking application.xml
	 * 
	 * @param string $application        	
	 */
	static function isPhysicalApplication($base, $application) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		// print_object($base.' - '.$application);
		// print_object(file_exists($NEON_CFG->appRoot.'/apps/'.$base.'/'.$application.'/application.xml' ));
		
		$processes = array ();
		if (file_exists ( $KUINK_CFG->appRoot . '/apps/' . $base . '/' . $application . '/application.xml' ))
			return 1;
		else
			return 0;
	}
	
	/**
	 * Get all aplication processes
	 * 
	 * @param unknown_type $application        	
	 */
	static function getApplicationProcesses($application) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$processes = array ();
		$procs = self::directoryContents ( $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process' );
		
		foreach ( $procs as $proc )
			$processes [] = array (
					'name' => ( string ) $proc 
			);
		
		return $processes;
	}
	
	/**
	 * Get all libraries
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 */
	static function getProcessLibraries($application, $process) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$libs = self::directoryContents ( $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/lib' );
		
		$cleanLibs = array ();
		
		foreach ( $libs as $lib )
			$cleanLibs [] = array (
					'name' => ( string ) str_replace ( '.xml', '', str_replace ( $process . '_', '', $lib ) ) 
			);
		
		return $cleanLibs;
	}
	
	/**
	 * Get all nodes
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 */
	static function getProcessNodes($application, $process) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$nodes = self::directoryContents ( $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/nodes' );
		
		$cleanNodes = array ();
		
		foreach ( $nodes as $node )
			$cleanNodes [] = str_replace ( $process . '_', '', $node );
		
		return $cleanNodes;
	}
	
	/**
	 * Get process user controls
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 */
	static function getProcessUI($application, $process) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$nodes = self::directoryContents ( $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/ui' );
		
		$cleanNodes = array ();
		
		foreach ( $nodes as $node )
			$cleanNodes [] = str_replace ( $process . '_', '', $node );
		
		return $cleanNodes;
	}
	
	/**
	 * Get process data access
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 */
	static function getProcessDataaccess($application, $process) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$nodes = self::directoryContents ( $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/dataaccess' );
		
		return $nodes;
	}
	
	/**
	 * Get a generic node metadata
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 * @param unknown_type $node        	
	 * @param unknown_type $type        	
	 * @throws \Exception
	 * @return multitype:multitype:string multitype:multitype:string
	 */
	static function getGenericNodeMetadata($application, $process, $node, $type) {
		// global $KUINK_CFG;
		$nodeMetadata = array ();
		
		// load the node
		
		$nodeXml = self::loadNode ( $application, $process, $node, $type );
		
		// extract general information: reference(true,false) | referenceNode | doc
		$nodeDataXml = $nodeXml->xpath ( '/Node' );
		
		$nodeDoc = array ();
		$DocXml = $nodeXml->xpath ( '/Node/Doc' );
		
		// extract params
		$nodeParams = self::getNodeParams ( $nodeXml );
		
		// extract permissions: roles | capabilities
		$nodePermissions = self::getNodePermissions ( $nodeXml );
		
		// extract config
		$nodeConfig = self::getNodeConfig ( $nodeXml );
		
		// included libraries
		$nodeLibs = self::getNodeIncludedLibs ( $nodeXml );
		
		// extract screens
		$nodeScreens = self::getNodeScreens ( $nodeXml );
		
		// extract actions with permissions
		$nodeActions = self::getNodeActions ( $nodeXml );
		
		// extract functions: name | parameters (name, type, doc)
		$nodeFunctions = self::getNodeFunctions ( $nodeXml, $application, $process, $node );
		
		// extract all session variables used
		$nodeSessionVariables = self::getNodeSessionVariables ( $nodeXml );
		
		// extract screen names
		$nodeMetadata ['params'] = $nodeParams;
		$nodeMetadata ['session'] = $nodeSessionVariables;
		$nodeMetadata ['permissions'] = $nodePermissions;
		$nodeMetadata ['config'] = $nodeConfig;
		$nodeMetadata ['libraries'] = $nodeLibs;
		$nodeMetadata ['screens'] = $nodeScreens;
		$nodeMetadata ['actions'] = $nodeActions;
		$nodeMetadata ['functions'] = $nodeFunctions;
		
		return $nodeMetadata;
	}
	static public function getPublicApi($application, $process = null) {
		$processes = array ();
		
		if ($process) {
			$processes [] = $process;
		} else {
			$processes = self::getApplicationProcesses ( $application );
		}
		
		$publicApi = array ();
		foreach ( $processes as $proc ) {
			$genericData = self::getGenericNodeMetadata ( $application, $proc, 'api', 'lib' );
			
			$functionsData = $genericData ['functions'];
			foreach ( $functionsData as $fx ) {
				$scope = ( string ) $fx ['scope'];
				
				if ($scope == 'public') {
					$fx ['class'] = $application . ',' . $proc;
					$publicApi [] = $fx;
				}
			}
		}
		return $publicApi;
	}
	static public function getApi($application, $process = null, $node = 'api') {
		$processes = array ();
		
		if ($process) {
			$processes [] = $process;
		} else {
			$processes = self::getApplicationProcesses ( $application );
		}
		
		$api = array ();
		foreach ( $processes as $proc ) {
			$genericData = self::getGenericNodeMetadata ( $application, $proc, $node, 'lib' );
			
			$functionsData = $genericData ['functions'];
			foreach ( $functionsData as $fx ) {
				$scope = ( string ) $fx ['scope'];
				$fx ['class'] = $application . ',' . $proc;
				$api [] = $fx;
			}
		}
		return $api;
	}
	
	/**
	 * Get a function parameters
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 * @param unknown_type $node        	
	 * @param unknown_type $function        	
	 */
	static public function getLibraryFunctionParams($application, $process, $node, $function) {
		// Load the node
		$nodeXml = self::loadNode ( $application, $process, $node, 'lib' );
		
		$fxParams = self::getFunctionParams ( $nodeXml, $function );
		
		return $fxParams;
	}
	
	/**
	 * Get a function parameters
	 * 
	 * @param unknown_type $application        	
	 * @param unknown_type $process        	
	 * @param unknown_type $node        	
	 * @param unknown_type $function        	
	 */
	static public function getLibraryFunctionReturns($application, $process, $node, $function) {
		// Load the node
		$nodeXml = self::loadNode ( $application, $process, $node, 'lib' );
		
		$fxRet = self::getFunctionReturns ( $nodeXml, $function );
		
		return $fxRet;
	}
	static private function loadNode($application, $process, $node, $type) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$nodePath = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/' . $type . '/' . $process . '_' . $node . '.xml';
		
		libxml_use_internal_errors ( true );
		$nodeXml = simplexml_load_file ( $nodePath, 'SimpleXMLElement', LIBXML_NOCDATA );
		$errors = libxml_get_errors ();
		if ($nodeXml == null)
			throw new \Exception ( 'Cannot load node: ' . $nodePath );
		return $nodeXml;
	}
	
	/**
	 * Get node params
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeSessionVariablesWrite($nodeXml) {
		$objs = array ();
		$objXml = $nodeXml->xpath ( '//Var[@session="true"]' );
		foreach ( $objXml as $obj )
			$objs [] = ( string ) $obj ['name'];
		
		$read = self::getNodeSessionVariablesRead ( $nodeXml );
		
		$objs = array_diff ( $objs, $read );
		
		return array_unique ( $objs );
	}
	static private function getNodeSessionVariablesRead($nodeXml) {
		$objs = array ();
		$objXml = $nodeXml->xpath ( '//Var[@session="true"][not(node()) and not(text())]' );
		foreach ( $objXml as $obj )
			$objs [] = ( string ) $obj ['name'];
		
		return array_unique ( $objs );
	}
	static private function getNodeSessionVariables($nodeXml) {
		$objs = array ();
		$objXml = $nodeXml->xpath ( '//Var[@session="true"]' );
		foreach ( $objXml as $obj )
			$objs [] = ( string ) $obj ['name'];
		
		return array_unique ( $objs );
	}
	
	/**
	 * Get node params
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeParams($nodeXml) {
		$objs = array ();
		$objXml = $nodeXml->xpath ( '/Node/Params/Param' );
		foreach ( $objXml as $obj )
			$objs [] = ( string ) $obj ['name'];
		
		return $objs;
	}
	
	/**
	 * Get included libraries
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeIncludedLibs($nodeXml) {
		$objs = array ();
		$objXml = $nodeXml->xpath ( '/Node/Libraries/Use' );
		foreach ( $objXml as $obj )
			$objs [] = ( string ) $obj ['name'];
		
		return $objs;
	}
	
	/**
	 * Get node configuration options
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeConfig($nodeXml) {
		$nodeConfig = array ();
		$configXml = $nodeXml->xpath ( '/Node/Configuration/Config' );
		foreach ( $configXml as $config )
			$nodeConfig [] = array (
					'key' => ( string ) $config ['key'],
					'value' => ( string ) $config ['value'] 
			);
		
		return $nodeConfig;
	}
	
	/**
	 * Get node screens
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeScreens($nodeXml) {
		$nodeData = array ();
		$dataXml = $nodeXml->xpath ( '/Node/Screens/Screen' );
		foreach ( $dataXml as $data ) {
			$dataDoc = isset ( $data ['doc'] ) ? ( string ) $data ['doc'] : '';
			$docXml = $data->xpath ( 'Doc' );
			if (isset ( $docXml ))
				$dataDoc .= ( string ) $docXml [0];
			$nodeData [] = array (
					'id' => ( string ) $data ['id'],
					'doc' => $dataDoc 
			);
		}
		
		return $nodeData;
	}
	
	/**
	 * Get node permissions
	 * 
	 * @param unknown_type $nodeXml        	
	 * @return multitype:string multitype:string
	 */
	static private function getNodePermissions($nodeXml) {
		// var_dump( $nodeXml );
		$nodePermissions = array ();
		$permissionRolesXml = $nodeXml->xpath ( './Permissions//Role' );
		// var_dump( $permissionRolesXml );
		$nodePermissionRoles = array();
		foreach ( $permissionRolesXml as $role )
			$nodePermissionRoles [] = ( string ) $role ['name'];
		$nodePermissionCapabilities = array ();
		$permissionCapabilitiesXml = $nodeXml->xpath ( './Permissions//Capability' );
		foreach ( $permissionCapabilitiesXml as $capability )
			$nodePermissionCapabilities [] = ( string ) $capability ['name'];
		
		$nodePermissions = array (
				'roles' => $nodePermissionRoles,
				'capabilities' => $nodePermissionCapabilities 
		);
		
		return $nodePermissions;
	}
	
	/**
	 * Get node actions
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeActions($nodeXml) {
		$nodeActions = array ();
		$actionsXml = $nodeXml->xpath ( '/Node/Actions/Action' );
		foreach ( $actionsXml as $action ) {
			$actDoc = isset ( $action ['doc'] ) ? ( string ) $action ['doc'] : '';
			$docXml = $action->xpath ( 'Doc' );
			if (isset ( $docXml ))
				$actDoc .= isset($docXml [0]) ? ( string ) $docXml [0] : '';
			$permissions = self::getNodePermissions ( $action );
			$nodeActions [] = array (
					'name' => ( string ) $action ['name'],
					'type' => ( string ) $action ['type'],
					'screen' => ( string ) $action ['screen'],
					'doc' => $actDoc,
					'permissions' => $permissions 
			);
		}
		
		return $nodeActions;
	}
	
	/**
	 * Get node functions
	 * 
	 * @param unknown_type $nodeXml        	
	 */
	static private function getNodeFunctions($nodeXml, $application = null, $process = null, $node = null) {
		$nodeFunctions = array ();
		$fxXml = $nodeXml->xpath ( '/Node/Library/Function' );
		foreach ( $fxXml as $fx ) {
			// get function returns
			$returnsXml = $fx->xpath ( 'Return' );
			$returnsDoc = isset ( $returnsXml [0] ['doc'] ) ? ( string ) $returnsXml [0] ['doc'] : '';
			$returnsType = isset ( $returnsXml [0] ['type'] ) ? ( string ) $returnsXml [0] ['type'] : '';
			
			$external = isset ( $returnsXml [0] ) ? $returnsXml [0]->children () : null;
			
			$fxOutput = array ();
			$fxOutputSignature = '<strong>Returns</strong> (' . $returnsType . ') - ';
			$fxOutputSignature .= $returnsDoc . '<br/><br/>';
			if (is_array($external))
				foreach ( $external as $outParam ) {
					$extType = isset ( $outParam ['type'] ) ? ( string ) $outParam ['type'] : 'text';
					;
					$extName = isset ( $outParam ['name'] ) ? ( string ) $outParam ['name'] : '';
					;
					$extDoc = isset ( $outParam ['doc'] ) ? ( string ) $outParam ['doc'] : 'undefined';
					;
					$fxOutputSignature .= $extType . ' <strong>' . $extName . '</strong> - ' . $extDoc . '<br/>';
					$fxOutput [] = array (
							'name' => $extName,
							'type' => $extType,
							'doc' => $extDoc 
					);
				}
			
			// Errors
			$errorsXml = $fx->xpath ( 'Errors/Error' );
			$errors = array ();
			$errorSignature = '<i class="fa fa-exclamation-circle"></i>&nbsp;<strong>Errors:</strong><br/>';
			foreach ( $errorsXml as $error ) {
				$errors [( string ) $error ['code']] = array (
						'code' => ( string ) $error ['code'],
						'doc' => ( string ) $error ['doc'] 
				);
				$errorSignature .= 'code (' . $error ['code'] . ') - ' . $error ['doc'] . '<br/>';
			}
			
			// Exceptions
			$exceptionsXml = $fx->xpath ( 'Exceptions/Exception' );
			$exceptionsSignature = '<i class="fa fa-bomb"></i>&nbsp;<strong>Exceptions:</strong><br/>';
			foreach ( $exceptionsXml as $exception ) {
				$exceptionsSignature .= ( string ) $exception ['name'] . ' - ' . ( string ) $exception ['doc'] . '<br/>';
			}
			
			// Permissions
			$beginXml = $fx->xpath ( 'Begin' );
			$permissions = self::getNodePermissions ( $beginXml [0] );
			// var_dump($permissions);
			
			// Check to see if theres a Doc node
			$fxDoc = isset ( $fx ['doc'] ) ? ( string ) $fx ['doc'] : '';
			$docXml = $fx->xpath ( 'Doc' );
			if (isset ( $docXml ))
				$fxDoc .= isset($docXml [0]) ? ( string ) $docXml [0] : '';
				
				// Get function metadata
			$functionParams = self::getFunctionParams ( $nodeXml, ( string ) $fx ['name'] );
			
			// function signature
			$fxSignature = '<strong>' . ( string ) $fx ['name'] . '</strong>( ';
			$fxParamsSignature = array ();
			$fxParamsSignatureCompressed = '';
			$call = array ();
			// TODO:ugly hack
			$call ['call'] = '<Call library="' . $application . ',' . $process . ',api" function="' . ( string ) $fx ['name'] . '">' . "\n";
			foreach ( $functionParams as $fxParam ) {
				$required = $fxParam ['required'];
				$paramSign = isset ( $fxParam ['type'] ) ? ( string ) $fxParam ['type'] : 'unknown';
				$paramSign .= ' ';
				$paramSign .= isset ( $fxParam ['name'] ) ? '<strong>' . ( string ) $fxParam ['name'] . '</strong>' : 'undefined';
				$paramSign .= ($required == 'true') ? '<strong>*</strong>' : '';
				
				$fxParamsSignature [] = $paramSign;
				$fxParamDoc = isset ( $fxParam ['doc'] ) ? ( string ) $fxParam ['doc'] : 'undefined';
				$call ['call'] .= '<Param name="' . ( string ) $fxParam ['name'] . '"></Param>' . "\n";
			}
			$paramsSign = implode ( ', ', $fxParamsSignature );
			$call ['call'] .= '</Call>';
			
			//$call ['kuink'] = '<Textarea readonly="true" style="width:90%; height:100%; border:none "><Call library="' . $application . ',' . $process . ',api" function="' . ( string ) $fx ['name'] . '">' . "\n";
			$call ['fw'] = '<Call library="' . $application . ',' . $process . ',api" function="' . ( string ) $fx ['name'] . '">' . "\n";
			foreach ( $functionParams as $fxParam ) {
				/*
				 * $required = $fxParam['required'];
				 * $paramSign = isset($fxParam['type']) ? (string)$fxParam['type'] : 'unknown';
				 * $paramSign .= ' ';
				 * $paramSign .= isset($fxParam['name']) ? '<strong>'.(string)$fxParam['name'].'</strong>' : 'undefined';
				 * $paramSign .= ($required == 'true') ? '<strong>*</strong>' : '';
				 *
				 * $fxParamsSignature[] = $paramSign;
				 * $fxParamDoc = isset($fxParam['doc']) ? (string)$fxParam['doc'] : 'undefined';
				 * $fxParamsSignatureCompressed .= $paramSign .' - '.$fxParamDoc.($required=='true'?' <em>(required)</em>':'').'<br/>';
				 */
				$call ['fw'] .= '	<Param name="' . ( string ) $fxParam ['name'] . '"></Param>' . "\n";
			}
			$paramsSign = implode ( ', ', $fxParamsSignature );
			$call ['fw'] .= '</Call>';
			
			$call ['fw'] = '<pre>'.htmlentities( $call['fw'] ).'</pre>';////
			$library = $application . ',' . $process . ',' . $node;
			$fullQualifiedName = $library . ',' . ( string ) $fx ['name'];
			
			$fxSignature .= $paramsSign . ' )';
			
			$fxScope = isset ( $fx ['scope'] ) ? ( string ) $fx ['scope'] : 'protected';
			
			$fx = array (
					'name' => ( string ) $fx ['name'],
					'library' => $library,
					'signature' => $fxSignature,
					'doc' => $fxDoc,
					'returnSignature' => $fxOutputSignature,
					'return' => $fxOutput,
					'returnType' => $returnsType,
					'errors' => $errors,
					'errorsSignature' => $errorSignature,
					'exceptionsSignature' => $exceptionsSignature,
					'params' => $functionParams,
					'paramsSignature' => $fxParamsSignatureCompressed,
					'scope' => $fxScope,
					'call' => $call,
					'permissions' => $permissions,
					'fullQualifiedName' => $fullQualifiedName 
			);
			$nodeFunctions [] = $fx;
		}
		// var_dump( $nodeFunctions );
		return $nodeFunctions;
	}
	
	/**
	 * Get a function parameter
	 * 
	 * @param unknown_type $nodeXml        	
	 * @param unknown_type $function        	
	 * @return multitype:multitype:string
	 */
	static private function getFunctionParams($nodeXml, $function) {
		$paramsXml = $nodeXml->xpath ( '/Node/Library/Function[@name="' . $function . '"]/Params/Param' );
		
		$params = array ();
		foreach ( $paramsXml as $paramXml ) {
			$paramAttrs = $paramXml->attributes ();
			foreach ( $paramAttrs as $paramKey => $paramValue )
				$param [$paramKey] = ( string ) $paramValue;
			
			$param ['type'] = isset ( $param ['type'] ) ? ( string ) $param ['type'] : 'text';
			$param ['doc'] = isset ( $param ['doc'] ) ? ( string ) $param ['doc'] : '! No documentation.';
			$param ['required'] = isset ( $param ['required'] ) ? ( string ) $param ['required'] : 'false';
			
			$params [] = $param;
		}
		
		return $params;
	}
	static private function getFunctionReturns($nodeXml, $function) {
		$retXml = $nodeXml->xpath ( '/Node/Library/Function[@name="' . $function . '"]/Return' );
		$retXml = $retXml [0];
		$retType = isset ( $retXml ['type'] ) ? ( string ) $retXml ['type'] : 'value'; // value || single || multiple
		$retDoc = isset ( $retXml ['doc'] ) ? ( string ) $retXml ['doc'] : '! No documentation.';
		$ret = array ();
		$child = $retXml->children ();
		foreach ( $child as $ext ) {
			$name = isset ( $ext ['name'] ) ? ( string ) $ext ['name'] : '! no name defined.';
			$type = isset ( $ext ['type'] ) ? ( string ) $ext ['type'] : 'text';
			$doc = isset ( $ext ['doc'] ) ? ( string ) $ext ['doc'] : '! No documentation.';
			$extValue = array (
					'name' => $name,
					'type' => $type,
					'doc' => $doc 
			);
			$ret [] = $extValue;
		}
		
		return (array (
				'type' => $retType,
				'doc' => $retDoc,
				'external' => $ret 
		));
	}
	
	/**
	 * Lists a directory content
	 * 
	 * @param unknown_type $directory        	
	 */
	static private function directoryContents($directory) {
		// open this directory
		$dirArray = array();
		if (is_dir($directory)) {
			$myDirectory = opendir ( $directory );
			
			// get each entry
			while ( $entryName = readdir ( $myDirectory ) ) {
				if ($entryName != '.' and $entryName != '..')
					$dirArray [] = $entryName;
			}
			
			// close directory
			closedir ( $myDirectory );
			
			// sort 'em
			sort ( $dirArray );
			
			// remove self
			// unset( $dirArray[0] );
		}
		
		return $dirArray;
	}
}
