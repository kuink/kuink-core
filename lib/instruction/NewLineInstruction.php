<?php

namespace Kuink\Core\Instruction;

/**
 * NewLine Instruction
 *
 * @author paulo.tavares
 */
class NewLineInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return <br/>
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		return '<br/>';		
	}
}

?>
