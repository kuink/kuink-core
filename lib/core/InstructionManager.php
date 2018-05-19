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
		$value = call_user_func ( $fn, $this, $instructionXmlNode );
		
		return $value;
	}
	
	/*
	 * Executes the first instruction inside
	 */
	public function executeInnerInstruction($instructionXmlNode) {
		// var_dump( $instructionXmlNode->count() );
		$result = null;
		if ($instructionXmlNode->count () > 0) {
			$newInstructionXmlNode = $instructionXmlNode->children ();
			$result = $this->execute ( $newInstructionXmlNode [0] );
		} else
			$result = $instructionXmlNode [0];
		
		return $result;
	}
	public function getParams($instructionXmlNode) {
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
		
		return ($params);
	}
	public function getAttribute($instruction, $attrName, $mandatory = 'false', $default = '') {
		if (! $mandatory && ! isset ( $instruction [$attrName] ))
			return $default;
		
		if ($mandatory && ! isset ( $instruction [$attrName] )) {
			$inst_name = $instruction->getname ();
			throw new \Exception ( 'Instruction "' . $inst_name . '" needs attribute "' . $attrName . '" which was not supplied.' );
		}
		$attr_value = ( string ) $instruction [$attrName];
		$type = $attr_value [0];
		$var_name = substr ( $attr_value, 1, strlen ( $attr_value ) - 1 );
		
		if ($type == '$' || $type == '#' || $type == '@') {
			$eval = new \Kuink\Core\EvalExpr ();
			$value = $eval->e ( $attr_value, $this->variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		} else
			$value = $attr_value;
		return ($value == '') ? $default : $value;
	}
	public function getVariable($name, $key=null) {
		if ($key == null || $key == '')
			return $this->variables [$name];
		else
			return $this->variables [$name] [$key];
	}
}

?>
