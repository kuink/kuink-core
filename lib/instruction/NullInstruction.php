<?php

namespace Kuink\Core\Instruction;

/**
 * Null Instruction
 *
 * @author paulo.tavares
 */
class NullInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		return null;		
	}
}

?>
