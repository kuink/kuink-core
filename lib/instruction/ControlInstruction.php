<?php

namespace Kuink\Core\Instruction;

/**
 * Control Instruction
 *
 * @author paulo.tavares
 */
class ControlInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Legacy libraries in kuink in a smooth way
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */

	//A list is a string, so an empty list is an empty string
	static public function execute($instManager, $instructionXmlNode) {

		$object = (string) $instManager->getAttribute( $instructionXmlNode, 'object', true);// $this->get_inst_attr ( $instruction_xmlnode, 'object', $variables, false );
		$method = $instManager->getAttribute( $instructionXmlNode, 'method', true);		
		$manager = $instManager->variables[$object];
		$params = $instManager->getParams ( $instructionXmlNode, true );
		$paramsVariable = (string) $instManager->getAttribute( $instructionXmlNode, 'params', false, null);// $this->get_inst_attr ( $instruction_xmlnode, 'object', $variables, false );

		if (method_exists ( $manager, $method ))
			$result = $manager->$method ( $params );
		else {
			//kuink_mydebugObj('object', $instManager->variables);
			throw new \Exception ( 'Control:: Invalid method in ' . $object.'->'.$method );		
		}

		return $result;
	}
}

?>
