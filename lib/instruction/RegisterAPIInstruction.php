<?php

namespace Kuink\Core\Instruction;

/**
 * And Instruction
 *
 * @author paulo.tavares
 */
class RegisterAPIInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an ActionValue operation. Returns the first non null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$value = $instManager->executeInnerInstruction ( $instructionXmlNode );
		\Kuink\Core\ProcessOrchestrator::registerAPI ( ( string ) $value );
		return (string) $value;
	}
}

?>