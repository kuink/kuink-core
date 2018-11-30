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

use Kuink\Core\Instruction;
use Kuink\Core\Exception\Exception;
use Kuink\Core\Exception\ERROR_CODE;
use Kuink\Core\Exception\NodeLoad;
use Kuink\Core\Exception\NodeMustBeLoadedException;
use Kuink\Core\Exception\InvalidName;

/**
 * Handles all opening stuff
 * 
 * @author ptavares
 *        
 */
class InstructionManager {
	var $runtime; // The Runtime
	var $variables; // Variables to set or get values
	var $nodeManager; // From this object each instruction can access all the node xml
	var $nodeConfiguration; // The node configuration
	var $exit; // Exit flag
	var $break; // Break flag (break a for or a while, or a foreach loop)
	function __construct($runtime, $nodeManager, $nodeConfiguration, $variables) {
		$this->runtime = $runtime;
		$this->variables = $variables;
		$this->nodeManager = $nodeManager;
		$this->nodeConfiguration = $nodeConfiguration;

		//kuink_mydebugObj('NodeXml', $this->nodeXml);
		//print_object($this->nodeXml);
		//kuink_mydebugObj('ActionXml', $this->actionXml);
	}
	public function execute($instructionXmlNode) {
		// var_dump($instructionXmlNode);
		// Call this instruction
		$instructionName = ( string ) $instructionXmlNode->getName ();
		
		// Check if this is a composed instruction
		if (strpos ( $instructionName, '.' ) === False)
			$methodName = 'execute';
		else {
			$splitInstructionName = explode ( '.', $instructionName );
			$instructionName = $splitInstructionName [0];
			$methodName = $splitInstructionName [1];
		}
		$fn = array (
				'\\Kuink\\Core\\Instruction\\' . $instructionName . 'Instruction',
				$methodName 
		);
		if (!is_callable($fn)) {
			//Try to load a legacy library
			$fn = array (
				'\\Kuink\\Core\\Instruction\\LegacyLibraryInstruction',
				$methodName 
			);			
		}

		$value = call_user_func ( $fn, $this, $instructionXmlNode );
		
		return $value;
	}
	
	/*
	 * Executes the first instruction inside
	 */
	public function executeInnerInstruction($instructionXmlNode, $innerValueHasString=false) {
		// var_dump( $instructionXmlNode->count() );
		if (is_array($instructionXmlNode))
			$instructionXmlNode = $instructionXmlNode[0];
			
		$result = null;
		if ($instructionXmlNode->count () > 0) {
			$newInstructionXmlNode = $instructionXmlNode->children ();
			$result = $this->execute ( $newInstructionXmlNode [0] );
		} else
			$result = ($innerValueHasString) ? (string) $instructionXmlNode [0] : $instructionXmlNode [0];
		
		return $result;
	}

	/*
	 * Executes all instruction inside
	 */
	public function executeInnerInstructions($instructionXmlNode) {
		// var_dump( $instructionXmlNode->count() );
		$result = null;
		$instructions = $instructionXmlNode[0];

		if ($instructions->count() > 0) {
			foreach ($instructions as $newInstructionXmlNode) {
				$result = $this->executeInstruction( $newInstructionXmlNode[0] );
			}
		} else
			$result = (string)$instructions [0];
		
		return $result;
	}


	/*
	 * Executes an insruction directly the instruction
	 */
	public function executeInstruction($instructionXmlNode) {		
		$result = $this->execute ( $instructionXmlNode );
		
		return $result;
	}

	public function getCustomParams($instructionXmlNode, $customName) {
		$paramsXml = $instructionXmlNode->xpath ( './'.$customName );
		$params = array ();
		
		foreach ( $paramsXml as $param ) {
			$paramName = isset ( $param ['name'] ) ? ( string ) $param ['name'] : '';
			if ($param->count () > 0) {
				$value = $this->executeInnerInstruction ( $param );
			} else {
				$value = $param [0];
				if (is_a ( $value, '\SimpleXMLElement' ))
					$value = ( string ) $value;
			}
			// var_dump($value);
			if ($paramName == '')
				$params [] = $value;
			else
				$params [$paramName] = $value;
		}

		return ($params);
	}



	public function getParams($instructionXmlNode, $includeParamsAttribute=false) {
		$paramsXml = $instructionXmlNode->xpath ( './Param' );
		$params = array ();
		
		foreach ( $paramsXml as $param ) {
			$paramName = isset ( $param ['name'] ) ? ( string ) $param ['name'] : '';
			if ($param->count () > 0) {
				$value = $this->executeInnerInstruction ( $param );
			} else {
				$value = $param [0];
				if (is_a ( $value, '\SimpleXMLElement' ))
					$value = ( string ) $value;
			}
			// var_dump($value);
			if ($paramName == '')
				$params [] = $value;
			else
				$params [$paramName] = $value;
		}

		//Join the params defined in params atribute if define
		if ($includeParamsAttribute) {
			//Join the params defined in params atribute if defined				
			$paramsVar = $this->getAttribute($instructionXmlNode, 'params', false, null); //isset ( $instructionXmlNode ['params'] ) ? ( string ) $instructionXmlNode ['params'] : '';
			if ($paramsVar != null) {
				$var = $this->variables [$paramsVar];
				foreach ( $var as $paramKey => $paramValue )
					$params ["$paramKey"] = $paramValue;
			}
		}

		return ($params);
	}

	public function getAttribute($instruction, $attrName, $mandatory = 'false', $default = '') {
		if (! $mandatory && ! isset ( $instruction [$attrName] ))
			return $default;
		
		if ($mandatory && ! isset ( $instruction [$attrName] )) {
			$instName = $instruction->getname ();
			throw new \Exception ( 'Instruction "' . $instName . '" needs attribute "' . $attrName . '" which was not supplied.' );
		}
		$attrValue = ( string ) $instruction [$attrName];
		$type = $attrValue [0];
		//$var_name = substr ( $attrValue, 1, strlen ( $attrValue ) - 1 );
		
		if ($type == '$' || $type == '#' || $type == '@') {
			$eval = new \Kuink\Core\EvalExpr ();
			$value = $eval->e ( $attrValue, $this->variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		} else
			$value = $attrValue;
		return ($value == '') ? $default : trim($value);
	}

	public function getAttributeRaw($instruction, $attrName, $mandatory = 'false', $default = '') {
		if (! $mandatory && ! isset ( $instruction [$attrName] ))
			return $default;
		
		if ($mandatory && ! isset ( $instruction [$attrName] )) {
			$instName = $instruction->getname ();
			throw new \Exception ( 'Instruction "' . $instName . '" needs attribute "' . $attrName . '" which was not supplied.' );
		}
		$attrValue = ( string ) $instruction [$attrName];
		return trim($attrValue);
	}


	public function getVariable($name, $key=null) {
		if ($key == null || $key == '')
			return $this->variables [$name];
		else
			return $this->variables [$name] [$key];
	}
}

?>
