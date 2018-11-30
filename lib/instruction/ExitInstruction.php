<?php

namespace Kuink\Core\Instruction;

/**
 * Exit Instruction
 *
 * @author paulo.tavares
 */
class ExitInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs Exit
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$instManager->exit = true;
		return null;
	}
}

?>
