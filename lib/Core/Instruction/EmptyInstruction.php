<?php

namespace Kuink\Core\Instruction;

/**
 * Empty Instruction
 *
 * @author paulo.tavares
 */
class EmptyInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Checks for Empty value
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$value = $instManager->executeInnerInstruction ( $instructionXmlNode );
		return empty ( $value );
	}
}

?>
