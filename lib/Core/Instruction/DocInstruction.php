<?php

namespace Kuink\Core\Instruction;

/**
 * Doc Instruction
 *
 * @author paulo.tavares
 */
class DocInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		//Do nothing. Ignore this instruction because it's a comment
		return null;		
	}
}

?>
