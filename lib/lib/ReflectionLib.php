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
class ReflectionLib extends \Kuink\Core\Lib {
	var $nodeconfiguration;
	var $msg_manager;
	function ReflectionLib($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	
	/**
	 * Returns a public APi of a given application
	 * 
	 * @return array
	 */
	function getPublicApi($params) {
		if (count ( $params ) < 1)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process' );
		
		$application = ( string ) $params ['application'];
		$process = isset ( $params ['process'] ) ? ( string ) $params ['process'] : '';
		if ($process == '')
			$api = \Kuink\Core\Reflection::getPublicApi ( $application );
		else
			$api = \Kuink\Core\Reflection::getPublicApi ( $application, $process );
		
		return $api;
	}
	
	/**
	 * Returns an APi of a given application
	 * 
	 * @return array
	 */
	function getApi($params) {
		if (count ( $params ) < 1)
			throw new Exception ( __METHOD__ . ' must have parameters: aplication, process, node(optional="api")' );
		
		$application = ( string ) $params ['application'];
		$process = isset ( $params ['process'] ) ? ( string ) $params ['process'] : '';
		$node = isset ( $params ['node'] ) ? ( string ) $params ['node'] : 'api';
		if ($process == '')
			$api = \Kuink\Core\Reflection::getApi ( $application );
		else
			$api = \Kuink\Core\Reflection::getApi ( $application, $process, $node );
		
		return $api;
	}
	function getBases($params) {
		$bases = \Kuink\Core\Reflection::getBases ();
		return $bases;
	}
	function getApplications($params) {
		$paramsDef [] = $this->addParam ( $paramsDef, 'base', 'text', true );
		$params = $this->ckeckParams ( $paramsDef, $params );
		
		$applications = \Kuink\Core\Reflection::getApplications ( ( string ) $params ['base'] );
		return $applications;
	}
	function isPhysicalApplication($params) {
		$paramsDef [] = $this->addParam ( $paramsDef, 'base', 'text', true );
		$paramsDef [] = $this->addParam ( $paramsDef, 'application', 'text', true );
		$params = $this->ckeckParams ( $paramsDef, $params );
		
		$isApp = \Kuink\Core\Reflection::isPhysicalApplication ( $params ['base'], $params ['application'] );
		
		return $isApp;
	}
	function getApplicationProcesses($params) {
		$paramsDef [] = $this->addParam ( $paramsDef, 'application', 'text', true );
		$params = $this->ckeckParams ( $paramsDef, $params );
		
		$processes = \Kuink\Core\Reflection::getApplicationProcesses ( $params ['application'] );
		
		return $processes;
	}
	function getProcessLibraries($params) {
		$paramsDef [] = $this->addParam ( $paramsDef, 'application', 'text', true );
		$paramsDef [] = $this->addParam ( $paramsDef, 'process', 'text', true );
		$params = $this->ckeckParams ( $paramsDef, $params );
		
		$libraries = \Kuink\Core\Reflection::getProcessLibraries ( $params ['application'], $params ['process'] );
		
		return $libraries;
	}
	function getFunctionMetadata($params) {
		$library = ( string ) $params [0];
		$function = isset ( $params [1] ) ? ( string ) $params [1] : '';
		
		$libSplit = explode ( ',', $library );
		$application = $libSplit [0];
		$process = $libSplit [1];
		$node = $libSplit [2];
		$function = ($function != '') ? $function : $libSplit [3];
		
		$api = \Kuink\Core\Reflection::getGenericNodeMetadata ( $application, $process, $node, 'lib' );
		$fxs = $api ['functions'];
		foreach ( $fxs as $fx ) {
			$fxName = ( string ) $fx ['name'];
			if ($fxName == $function)
				return $fx;
		}
		return null;
	}
	function setNode($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 5)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process, type, node, xmlDefinition, [override->false]' );
		
		$appBase = ( string ) $params ['base'];
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		$type = ( string ) $params ['type'];
		$node = ( string ) $params ['node'];
		$xmlDefinition = ( string ) $params ['xmlDefinition'];
		$override = isset ( $params ['override'] ) ? ( string ) $params ['override'] : 'false';
		
		$nodeName = ($type == 'nodes' || $type == 'lib') ? $process . '_' . $node . '.xml' : $node . '.xml';
		$filePath = $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/$type/";
		$fileName = $filePath . '/' . $nodeName;
		
		// Check if the path exists
		if (! is_dir ( $filePath ))
			mkdir ( $filePath );
		
		if (file_exists ( $fileName ) && $override = 'true')
			unlink ( $fileName );
		
		$domxml = new DOMDocument ( '1.0' );
		$domxml->preserveWhiteSpace = false;
		$domxml->formatOutput = true;
		$domxml->loadXML ( $xmlDefinition );
		$domxml->save ( $fileName );
		// file_put_contents($fileName, $xmlDefinition);
		
		return;
	}
	private function loadNode($filePath, $fileName) {
		libxml_use_internal_errors ( true );
		$nodeXml = simplexml_load_file ( $fileName, 'SimpleXMLElement', LIBXML_NOCDATA );
		$errors = libxml_get_errors ();
		if ($nodeXml == null)
			throw new \Exception ( 'Cannot load node: ' . $fileName );
		
		return $nodeXml;
	}
	private function xmlAdopt($root, $new) {
		$node = $root->addChild ( $new->getName (), ( string ) $new );
		foreach ( $new->attributes () as $attr => $value ) {
			$node->addAttribute ( $attr, $value );
		}
		foreach ( $new->children () as $ch ) {
			self::xmlAdopt ( $node, $ch );
		}
	}
	private function addElement($xml, $xPath, $xmlDefinition) {
		$xParents = $xml->xpath ( $xPath );
		$xParent = $xParents [0];
		
		// var_dump('<xmp>'.$xmlDefinition.'</xmp>');
		
		$xNewElement = new \SimpleXMLElement ( $xmlDefinition );
		self::xmlAdopt ( $xParent, $xNewElement );
		return $xml->asXml ();
	}
	function nodeAddElement($params) {
		global $KUINK_CFG;
		
		if (count ( $params ) < 7)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process, type, node, xPath, xmlDefinition, [override->false]' );
		
		$appBase = ( string ) $params ['base'];
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		$type = ( string ) $params ['type'];
		$node = ( string ) $params ['node'];
		$xPath = ( string ) $params ['xPath'];
		$xmlDefinition = ( string ) $params ['xmlDefinition'];
		$override = isset ( $params ['override'] ) ? ( string ) $params ['override'] : 'false';
		
		$nodeName = ($type == 'nodes' || $type == 'lib') ? $process . '_' . $node . '.xml' : $node . '.xml';
		
		$filePath = $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/$type/";
		$fileName = $filePath . '/' . $nodeName;
		
		$nodeXml = self::loadNode ( $filePath, $fileName );
		
		self::addElement ( $nodeXml, $xPath, $xmlDefinition );
		
		$setNodeParams = array ();
		$setNodeParams ['base'] = $appBase;
		$setNodeParams ['application'] = $application;
		$setNodeParams ['process'] = $process;
		$setNodeParams ['type'] = $type;
		$setNodeParams ['node'] = $node;
		$setNodeParams ['xmlDefinition'] = $nodeXml->asXml ();
		$setNodeParams ['override'] = 'true';
		self::setNode ( $setNodeParams );
	}
	function nodeExists($params) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		if (count ( $params ) < 4)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process, type, node' );
		
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		$type = ( string ) $params ['type'];
		$node = ( string ) $params ['node'];
		
		$base = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$nodeName = ($type == 'nodes' || $type == 'lib') ? $process . '_' . $node . '.xml' : $node . '.xml';
		
		$filePath = ($type == 'dd') ? $KUINK_CFG->appRoot . "apps/$base/$application/$type/" : $KUINK_CFG->appRoot . "apps/$base/$application/process/$process/$type/";
		$fileName = $filePath . '/' . $nodeName;
		
		// print_object($fileName);
		
		if (file_exists ( $fileName ) == 1)
			return 1;
		else
			return 0;
	}
	function processExists($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 2)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process' );
		
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		
		return is_dir ( $KUINK_CFG->appRoot . "apps/$application/process/$process" );
	}
	function applicationExists($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 1)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication' );
		
		$application = ( string ) $params ['application'];
		
		return is_dir ( $KUINK_CFG->appRoot . "apps/$application" );
	}
	function applicationAdd($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 3)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, xmlDefinition, [override->false]' );
			// var_dump($params);
		$appBase = ( string ) $params ['base'];
		$application = ( string ) $params ['application'];
		$xmlDefinition = ( string ) $params ['xmlDefinition'];
		$langXmlDefinition = (string) $params ['langXmlDefinition'];
		$override = isset ( $params ['override'] ) ? ( string ) $params ['override'] : 'false';
		
		$baseFilePath = $KUINK_CFG->appRoot . "apps/$appBase";
		
		$filePath = $KUINK_CFG->appRoot . "apps/$appBase/$application";
		$fileName = $filePath . '/application.xml';
		
		// Check if base exists
		if (! is_dir ( $baseFilePath ))
			mkdir ( $baseFilePath );
			
			// Check if the path exists
		if (! is_dir ( $filePath ))
			mkdir ( $filePath );
		
		if (file_exists ( $fileName ) && $override == 'true')
			unlink ( $fileName );
		
		if (! file_exists ( $fileName ))
			file_put_contents ( $fileName, $xmlDefinition );
		$filePath = $NEON_CFG->appRoot."apps/$appBase/$application/lang";
		$fileName = $filePath.'/pt.xml';      
		//Check if the path exists
		if (!is_dir($filePath))
			mkdir($filePath);
		if (file_exists($fileName) && $override == 'true')
			unlink($fileName);
		if (!file_exists($fileName))
			file_put_contents($fileName, $langXmlDefinition);
		return;
	}
	function processAdd($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 4)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process, xmlDefinition, [override->false]' );
		
		$appBase = ( string ) $params ['base'];
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		$xmlDefinition = ( string ) $params ['xmlDefinition'];
		$override = isset ( $params ['override'] ) ? ( string ) $params ['override'] : 'false';
		
		$filePath = $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process";
		$fileName = $filePath . '/process.xml';
		
		// Check if the path exists
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process" );
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process" );
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/dataaccess" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/dataaccess" );
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/nodes" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/nodes" );
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/lib" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/lib" );
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/ui" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/ui" );
		if (! is_dir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/templates" ))
			mkdir ( $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/templates" );
		
		if (file_exists ( $fileName ) && $override == 'true')
			unlink ( $fileName );
		
		if (! file_exists ( $fileName ))
			file_put_contents ( $fileName, $xmlDefinition );
		
		return;
	}
	function nodeAdd($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 6)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process, type, node, xmlDefinition, [override->false]' );
		
		$appBase = ( string ) $params ['base'];
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		$type = ( string ) $params ['type'];
		$node = ( string ) $params ['node'];
		$xmlDefinition = ( string ) $params ['xmlDefinition'];
		$override = isset ( $params ['override'] ) ? ( string ) $params ['override'] : 'false';
		
		$nodeName = ($type == 'nodes' || $type == 'lib') ? $process . '_' . $node . '.xml' : $node . '.xml';
		$filePath = $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/$type/";
		$fileName = $filePath . '/' . $nodeName;
		
		if (file_exists ( $fileName ) && $override == 'true')
			unlink ( $fileName );
		
		if (! file_exists ( $fileName ))
			file_put_contents ( $fileName, $xmlDefinition );
		
		return;
	}
	function dataaccessAdd($params) {
		global $KUINK_CFG;
		if (count ( $params ) < 5)
			throw new Exception ( __METHOD__ . ' must have one parameter: aplication, process, node, xmlDefinition, [override->false]' );
		
		$appBase = ( string ) $params ['base'];
		$application = ( string ) $params ['application'];
		$process = ( string ) $params ['process'];
		$node = ( string ) $params ['node'];
		$xmlDefinition = ( string ) $params ['xmlDefinition'];
		$override = isset ( $params ['override'] ) ? ( string ) $params ['override'] : 'false';
		
		$nodeName = $node . '.xml';
		$filePath = $KUINK_CFG->appRoot . "apps/$appBase/$application/process/$process/dataaccess";
		$fileName = $filePath . '/' . $nodeName;
		// var_dump($xmlDefinition);
		if (file_exists ( $fileName ) && $override == 'true')
			unlink ( $fileName );
		
		if (! file_exists ( $fileName )) {
			file_put_contents ( $fileName, $xmlDefinition );
			// var_dump('buum');
		}
		return;
	}
}

?>
