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

use Kuink\Core\Exception\Exception;
use Kuink\Core\Exception\ERROR_CODE;
use Kuink\Core\Exception\NodeLoad;
use Kuink\Core\Exception\NodeMustBeLoadedException;
use Kuink\Core\Exception\InvalidName;

class NodeType {
	const DATA_DEFINITION = 'dd';
	const API = 'api';
	const NODE = 'nodes';
	const DATA_ACCESS = 'dataaccess';
}
class NodeObject {
	const DOMAIN = '/Node/Domains/Domain';
	const TEMPLATE = '/Node/Templates/Template';
	const ENTITY_RECURSIVE = '/Node/Entities//Entity';
	const SCREEN = '/Node/Screens/Screen';
}

/**
 * Handles all opening stuff
 * 
 * @author ptavares
 *        
 */
class NodeManager {
	var $application;
	var $process;
	var $type;
	var $node;
	var $fileName;
	var $nodeXml;
	var $loaded; // is this node loaded?
	var $inherits; // from which this node inherits
	function __construct($application, $process, $type, $node) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		$this->application = $application;
		$this->process = $process;
		$this->type = $type;
		$this->node = $node;
		// var_dump($process.'::'. $type);
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		if ($this->process != '') {
			if ($type == 'nodes' || $type == 'lib' || $type == 'ui')
				$this->nodeFilename = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/' . $type . '/' . $process . '_' . $node . '.xml';
			else
				$this->nodeFilename = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/' . $type . '/' . $node . '.xml';
		} else
			$this->nodeFilename = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/' . $type . '/' . $node . '.xml';
		$this->loaded = 0;
	}
	public function exists() {
		return file_exists ( $this->fileName );
	}
	public function load($validateXml = false, $validateSchema = false) {
		global $KUINK_TRACE;
		
		if (! file_exists ( $this->nodeFilename ))
			throw new \Exception ( 'Error opening node file ' . $this->nodeFilename );
			// throw new \Exception('Error opening node file: '.$this->application.','.$this->process.','.$this->type.','.$this->node);
		
		if ($validateXml)
			libxml_use_internal_errors ( true );
		
		$this->nodeXml = simplexml_load_file ( $this->nodeFilename );
		
		if ($validateXml)
			$errors = libxml_get_errors ();
		
		if (! $this->nodeXml) {
			$errors = null;
			if ($validateXml)
				$errors = libxml_get_errors ();
			$KUINK_TRACE [] = var_export ( $errors, true );
		} else
			$KUINK_TRACE [] = 'File: ' . $this->nodeFilename;
		$this->loaded = 1;
		
		// Get the inherites node kid
		$nodeXml = $this->nodeXml->xpath ( '/Node' );
		$inherits = isset ( $nodeXml [0] ['inherits'] ) ? ( string ) $nodeXml [0] ['inherits'] : '';
		$this->inherits = $inherits;
		
		if ($validateSchema) {
			$this->validateSchema ();
		}
		return $this->loaded;
	}
	public function getInheritedNodeManager() {
		if ($this->inherits != '') {
			$splitedName = explode ( ',', $this->inherits );
			if (count ( $splitedName ) != 2)
				throw new InvalidName ( __CLASS__, $this->inherits );
			$application = $splitedName [0];
			$node = $splitedName [1];
			
			$newNodeManager = new NodeManager ( $application, '', NodeType::DATA_DEFINITION, $node );
			return $newNodeManager;
		}
		return null;
	}
	private function requireLoad() {
		if (! $this->loaded)
			throw new NodeMustBeLoadedException ( __CLASS__, $this->application, $this->process, $this->type, $$this->node );
	}
	
	// if attributeName is an array
	private function getNodeObject($value, $nodeType, $xpath, $attributeName) {
		$this->requireLoad ();
		
		$xpathCondition = '[';
		$conditions = count ( $attributeName );
		$countConditions = 0;
		if (is_array ( $attributeName )) {
			foreach ( $attributeName as $attr ) {
				$xpathCondition .= '@' . $attr . '="' . $value . '"';
				if ($countConditions < $conditions - 1)
					$xpathCondition .= ' or ';
				$countConditions ++;
			}
		} else {
			// only one condition
			$xpathCondition .= '@' . $attributeName . '="' . $value . '"';
		}
		$xpathCondition .= ']';
		
		if (strpos ( $value, ',' )) {
			// Is in another node
			$splitedName = explode ( ',', $value );
			if (count ( $splitedName ) != 3)
				throw new InvalidName ( __CLASS__, $value );
			$application = $splitedName [0];
			$node = $splitedName [1];
			$objectName = $splitedName [2];
			
			$newNodeManager = new NodeManager ( $application, '', $nodeType, $node );
			$newNodeManager->load ();
			
			$object = $newNodeManager->getNodeObject ( $objectName, $nodeType, $xpath, $attributeName );
		} else {
			// Try to see if the definition is in this file
			$nodeXpath = $xpath . $xpathCondition;
			// var_dump($nodeXpath);
			$objects = $this->nodeXml->xpath ( $nodeXpath );
			// var_dump($objects);
			$object = isset ( $objects [0] ) ? $objects [0] : null;
			
			if (! $object) {
				// try to see if the definition is inherited
				if ($this->inherits != '') {
					$newNodeManager = $this->getInheritedNodeManager ();
					$newNodeManager->load ();
					
					$object = $newNodeManager->getNodeObject ( $value, $nodeType, $xpath, $attributeName );
				}
			}
		}
		// var_dump($object);
		return $object;
	}
	public function getEntities() {
		$this->requireLoad ();
		$entities = $this->nodeXml->xpath ( '/Node/Entities/Entity' );
		return $entities;
	}
	public function getDomains() {
		$this->requireLoad ();
		$entities = $this->nodeXml->xpath ( '/Node/Domains' );
		return $entities;
	}
	public function getEntity($name) {
		return $this->getNodeObject ( $name, NodeType::DATA_DEFINITION, NodeObject::ENTITY_RECURSIVE, 'name' );
	}
	public function getDomain($name) {
		return $this->getNodeObject ( $name, NodeType::DATA_DEFINITION, NodeObject::DOMAIN, 'name' );
	}
	public function getTemplate($name) {
		return $this->getNodeObject ( $name, NodeType::DATA_DEFINITION, NodeObject::TEMPLATE, 'name' );
	}
	public function getScreen($name) {
		return $this->getNodeObject ( $name, NodeType::NODE, NodeObject::SCREEN, array (
				'id',
				'name' 
		) );
	}
	public function getScreenControls($name) {
		return $this->getNodeObject ( $name, NodeType::NODE, NodeObject::SCREEN . '[@name = "' . $name . '"]/*', array (
				'id',
				'name' 
		) );
	}
	public function validateSchema() {
		global $KUINK_CFG;
		var_dump ( 'validating schema ' . $this->type );
		
		// Enable user error handling
		libxml_use_internal_errors ( true );
		
		// We have to use the DomDocument for this and reload the node
		$domXml = new \DOMDocument ();
		$domXml->load ( $this->nodeFilename );
		$xsdFilename = $KUINK_CFG->appRoot . '/apps/framework/framework/schema/' . $this->type . '.xsd';
		var_dump ( $this->nodeFilename );
		var_dump ( $xsdFilename );
		$isValid = $domXml->schemaValidate ( $xsdFilename );
		$errors = libxml_get_errors ();
		var_dump ( $errors );
		libxml_clear_errors ();
		return;
	}
}

?>
