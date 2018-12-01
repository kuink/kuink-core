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
namespace Kuink;

// Set up SINGLETON OBJECTS
$KUINK_LAYOUT = null; // Handles all the output, layouts, templates and themes
$KUINK_TRACE = array (); // Holds tracing information of the execution
$KUINK_MANUAL_TRACE = array (); // Holds manual tracing in execution
$KUINK_DATABASES = array (); // Holds all database connection objects
$KUINK_DATASOURCES = array (); // Will replace $KUINK_DATABASES
$KUINK_TRANSLATION = null; // Holds pointers to xml language files
$KUINK_APPLICATION = null; // The Application object to run

/**
 * Kuink Core - The entry point in kuink universe
 * 
 * @author ptavares
 *        
 */
class Core {
	function __construct($bridgeConfig, $layoutAdapter, $kuinkCfg=null) {
		global $KUINK_CFG, $KUINK_LAYOUT, $KUINK_TRACE, $KUINK_MANUAL_TRACE, $KUINK_DATABASES, $KUINK_DATASOURCES, $KUINK_TRANSLATION, $KUINK_APPLICATION;

		date_default_timezone_set ( 'UTC' );
		if ($kuinkCfg != null)
			$KUINK_CFG = $kuinkCfg;
		$KUINK_LAYOUT = $layoutAdapter;
	}

	/**
	 * Run an application 
	 */
	public function run() {
		global $KUINK_CFG, $KUINK_LAYOUT, $KUINK_TRACE, $KUINK_MANUAL_TRACE, $KUINK_DATABASES, $KUINK_DATASOURCES, $KUINK_TRANSLATION, $KUINK_APPLICATION;

		$kuink_session_active = isset ( $_SESSION ['KUINK_CONTEXT'] ['KUINK_SESSION_ACTIVE'] ) ? $_SESSION ['KUINK_CONTEXT'] ['KUINK_SESSION_ACTIVE'] : 0;
		if ($kuink_session_active != 1 && isset($_GET ['startnode']) && $_GET ['startnode'] != '')
			redirect ( $KUINK_CFG->wwwRoot, 0 );
		
		if (isset($KUINK_CFG->displayNativeErrors) && $KUINK_CFG->displayNativeErrors) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
		
		// If so then the application will be given by the widget istead of the kuink configuration in moodle
		$application = $KUINK_CFG->application;
		$configuration = $KUINK_CFG->configuration;
		$lang = $KUINK_CFG->auth->user->lang;

		//Setting the modal default to widgetContainer to display widgets correctly
		$modal = isset($_GET['modal']) ? (string)$_GET['modal'] : '';
		//if ($modal == '') 
		//	$_GET['modal'] = 'widgetContainer';


		if (isset ( $_GET ['idWidget'] )) {
			$KUINK_APPLICATION = new Kuink\Core\Application ( $application, $lang, $configuration );
			
			$idWidget = ( string ) $_GET ['idWidget'];
			$node = new \Kuink\Core\Node ( 'framework', 'widget', 'api' );
			
			$wsParams ['uuid'] = $idWidget;
			
			$wsResult = $KUINK_APPLICATION->run ( $node, 'getByGuid', $wsParams );
			$widgetData = $wsResult ['RETURN'];
			
			$application = $widgetData ['init_flow'];
			//$application = $appParts[0];

			//var_dump($application);
			//If the init flow is composed by application,process,event
			//var_dump($application);
			//var_dump($_GET['idWidget']);

			$configuration = $widgetData ['configuration'];
			
			unset ( $_GET ['idWidget'] );
			$KUINK_DATABASES = array (); // Holds all database connection objects
			$KUINK_DATASOURCES = array (); // Will replace $KUINK_DATABASES
			$KUINK_TRANSLATION = null; // Holds pointers to xml language files
			$KUINK_APPLICATION = null; // The Application object to run
		}
		//print_object($application);
		// Creating the application
		$KUINK_APPLICATION = new \Kuink\Core\Application ( $application, $KUINK_CFG->auth->user->lang, $configuration );
		// Adding roles to the application
		foreach ( $KUINK_CFG->auth->roles as $role )
			$KUINK_APPLICATION->addRole ( ( string ) $role );
			
			// Run the application
			try {
				$KUINK_APPLICATION->run ();
			} catch (\Exception $e) {
				print($e->getMessage());
			} catch (Throwable $t) {
				print($t->getMessage());
				print_object($KUINK_TRACE);
			}

		// Render the screen
		//$KUINK_LAYOUT->render ();

		// Handling session expiration event
		$_SESSION ['KUINK_CONTEXT'] ['KUINK_SESSION_ACTIVE'] = 1;		
	}
	
	/**
	 * Call an api
	 */
	public function call($function) {
		global $KUINK_CFG, $KUINK_LAYOUT, $KUINK_TRACE, $KUINK_MANUAL_TRACE, $KUINK_DATABASES, $KUINK_DATASOURCES, $KUINK_TRANSLATION, $KUINK_APPLICATION;
		$function = isset ( $_GET ['neonfunction'] ) ? ( string ) $_GET ['neonfunction'] : '';
		$functionParsed = explode ( ',', $function );
		$idcontext = (isset ( $_GET ['idcontext'] )) ? $_GET ['idcontext'] : null;
		
		$bypass = ($_SESSION ['_kuink_api_security_bypass'] === true);
		// $bypass = true;
		
		$validRegisteredAPI = \Kuink\Core\ProcessOrchestrator::validRegisteredAPI ( $function, $idcontext, $bypass );
		
		if (! $validRegisteredAPI) {
			// var_dump(\Kuink\Core\ProcessOrchestrator::getRegisteredAPIs($idcontext));
			throw new \Exception ( 'No permission to execute API: ' . $function );
		}
		
		if (count ( $functionParsed ) != 4)
			throw new \Exception ( 'Kuink Function must be application,process,library,function' );
		
		$wsApp = $functionParsed [0];
		$wsProcess = $functionParsed [1];
		$wsLibrary = $functionParsed [2];
		$wsService = $functionParsed [3];
		
		// Creating the application
		$KUINK_APPLICATION = new \Kuink\Core\Application ( $wsApp, $USER->lang, '<Configuration/>' );
		$node = new \Kuink\Core\Node ( $wsApp, $wsProcess, $wsLibrary );
		// Get the function parametrs
		$wsParams = \Kuink\Core\Reflection::getLibraryFunctionParams ( $wsApp, $wsProcess, $wsLibrary, $wsService );
		$wsValues = $this->kuink_service_param_values ( $wsParams );
		// Validate the function params
		
		$wsResult = $KUINK_APPLICATION->run ( $node, $wsService, $wsValues );
		
		// $return = json_encode( $wsResult['RETURN'] );
		$return = $wsResult ['RETURN'];
		
		$returnArray = array ();
		$isArrayOfStd = 0;
		foreach ( $return as $data ) {
			if (is_object ( $data )) {
				$isArrayOfStd = 1;
				$returnArray [] = ( array ) $data;
			}
		}
		if ($isArrayOfStd == 0)
			$returnArray = $return;
			
			// var_dump( $returnArray );
		header ( 'Content-Type: application/json' );
		// header('Content-Type: application/x-www-form-urlencoded ');
		echo json_encode ( $returnArray );
		
	}
	
	/**
	 * Streams a file to the client
	 */
	public function stream($type, $guid) {
		global $KUINK_CFG, $KUINK_LAYOUT, $KUINK_TRACE, $KUINK_MANUAL_TRACE, $KUINK_DATABASES, $KUINK_DATASOURCES, $KUINK_TRANSLATION, $KUINK_APPLICATION;

		switch ($type) {
			case "photo" :
				$file = $guid . '.jpg';
				$base = $KUINK_CFG->imageRoot . $type . '/';

				var_dump($KUINK_CFG->imageRoot);

				$baseRP = realpath ( $base );
				$path = realpath ( $base . $file );
				$pos = strpos ( $path . '/', $baseRP );
				if ($pos === FALSE) {
					$file = 'default.jpg';
				}
				$size = getimagesize ( $base . $file );
				if ($size) {
					ob_clean ();
					header ( 'Content-Type: ' . $size ['mime'] );
					header ( 'Content-Length: ' . filesize ( $base . $file ) );
					readfile ( $base . $file );
				}
				break;
			
			case "bpmn" :
				$split = explode ( ',', $guid );
				$application = $split [0];
				$process = $split [1];
				$bpmn = $split [2];
				$KUINK_DATASOURCES = array ();
				$KUINK_APPLICATION = new Kuink\Core\Application ( 'framework', $USER->lang, null );
				
				$base = $KUINK_APPLICATION->appManager->getApplicationBase ( 'framework' );
				
				$filename = $KUINK_CFG->appRoot . 'apps/' . $base . '/' . $application . '/process/' . $process . '/bpmn/' . $bpmn . '.png';
				ob_clean ();
				header ( 'Content-Type: image/png' );
				header ( 'Content-Length: ' . filesize ( $filename ) );
				readfile ( $filename );
				
				break;
			
			case "file" :
				$KUINK_DATASOURCES = array ();
				$KUINK_APPLICATION = new \Kuink\Core\Application ( 'framework', 'pt', null );
				
				if (! empty ( $guid )) {
					$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
					$params ['_entity'] = 'fw_file';
					$params ['guid'] = $guid;
					$fileRecord = $dataAccess->execute ( $params );
					
					$file = (string) $fileRecord['name'];
					$path = (string) $fileRecord['path'];
		
					/* corect the file path based on $KUINK_CFG->uploadVirtualPrefix temporary key*/
					$path = str_replace($KUINK_CFG->uploadVirtualPrefix, '', $path);
		
					$pathName = $KUINK_CFG->uploadRoot.$path .'/'. $file;	
		
				} else {
					// Without guid
					header ( 'HTTP/1.0 404 not found' );
					print_error ( 'Not Allowed', 'error' );
				}
				
				if (file_exists ( $pathName ) and ! is_dir ( $pathName )) {
					ob_clean ();
					header ( 'Content-Type: ' . $fileRecord ['mimetype'] );
					header ( 'Content-Length: ' . filesize ( $pathName ) );
					header('Content-Disposition: attachment; filename="'.$guid.'.'.$fileRecord['ext'].'"');
		
					readfile ( $pathName );
				} else {
					header ( 'HTTP/1.0 404 not found' );
					print_error ( 'filenotfound', 'error' );
				}
				break;
			
			case "tmp" :
				$pathName = $KUINK_CFG->dataRoot . '/kuink/files/tmp/' . $guid;
				if (file_exists ( $pathName ) and ! is_dir ( $pathName )) {
					ob_clean ();
					$mimeType = mime_content_type($pathName);
					header( 'Content-Type: ' . $mimeType);
					header ( 'Content-Length: ' . filesize ( $pathName ) );
					header('Content-Disposition: attachment; filename="'.$guid);
					readfile ( $pathName );
				} else {
					header ( 'HTTP/1.0 404 not found' );
					print_error ( 'filenotfound', 'error' );
				}
				break;
			case "daily":
				$pathName = realPath($NEON_CFG->dataRoot.'/kuink/files/daily/'.$guid);
				if ((file_exists($pathName) and !is_dir($pathName)) and (strpos($pathName, $NEON_CFG->dataRoot.'/kuink/files/daily/') !== false)) {
					ob_clean();
					$mimeType = mime_content_type($pathName);
					header('Content-Type: '.$mimeType);
					header('Content-Length: '.filesize($pathName));
					header('Content-Disposition: attachment; filename="'.$guid);					
					readfile($pathName);	
				} else {
					header('HTTP/1.0 404 not found');
					print_error('filenotfound', 'error'); //this is not displayed on IIS??
				}
				break;
		}		
	}

	function kuink_service_param_value($wsParam) {
		$value = isset ( $_POST [$wsParam] ) ? $_POST [$wsParam] : $_GET [$wsParam];
		return stripslashes ( $value );
	}
	function kuink_service_param_values($wsParams) {
		$values = array ();
		foreach ( $wsParams as $wsParam ) {
			$name = ( string ) $wsParam ['name'];
			$values [$name] = $this->kuink_service_param_value ( $name );
		}
		return $values;
	}

}

?>
