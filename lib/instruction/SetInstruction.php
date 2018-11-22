<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class SetInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Set Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$newSet = array ();
		foreach ( $instructionXmlNode->children () as $element ) {
			$elementName = self::getAttribute ( $element, 'name', $instManager->variables, false, '' ); 
			$elementKey = self::getAttribute ( $element, 'key', $instManager->variables, false, '' ); 
			$key = ($elementKey != '') ? $elementKey: $elementName;
			if ($element->count () > 0) {
				$newinstructionXmlNode = $element->children ();
				$value = $instManager->executeInnerInstruction ( $newinstructionXmlNode[0] );
			} else {
				$value = (string)$element;
			}
			if ($key != '')
				$newSet [$key] = $value;
			else
				$newSet [] = $value;
		}
		
		return $newSet;
	}

	//Reverses an array from an array
	static public function reverse($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$array = (array) $params[0];
		$array = array_reverse($array, true);
		return $array;
	}	


	//Pop an element from an array
	static public function pop($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$array = (array) $params[0];
		$first = array_pop($array);
		$result = array('pop'=>$first, 'array'=>$array);
		return $result;
	}	
}

?>
