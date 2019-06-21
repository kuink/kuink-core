<?php

namespace Kuink\Core\Instruction;

/**
 * GetObjectProperty Instruction
 *
 * @author paulo.tavares
 */
class GetObjectPropertyInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * GetObjectProperty Instruction
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */

	//A list is a string, so an empty list is an empty string
	static public function execute($instManager, $instructionXmlNode) {

		$objectName = (string)$instManager->getAttribute( $instructionXmlNode, 'object', true);
		$propertyName = (string) $instManager->getAttribute( $instructionXmlNode, 'property', true);
		$object = $instManager->variables ["$objectName"];
		
		$return = $object->$propertyName;
		
		return $return;
	}
}

?>
