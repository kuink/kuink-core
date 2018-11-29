<?php

namespace Kuink\Core\Instruction\Logic;

/**
 * Not Instruction
 *
 * @author paulo.tavares
 */
class NotInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Apply the logical not operator
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		$value = isset($params[0]) ? $params[0] : $instManager->executeInnerInstruction ( $instructionXmlNode );

		return (!$value);		
	}
}

?>
