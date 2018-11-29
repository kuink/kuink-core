<?php

namespace Kuink\Core\Instruction\Flow;

/**
 * Return Instruction
 *
 * @author paulo.tavares
 */
class ReturnInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs Returns a value to the caller
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$value = $instManager->executeInnerInstruction ( $instructionXmlNode );
		
		$instManager->variables['_RETURN_'] = $value;
		$instManager->exit = true;
		return $value;
	}
}

?>
