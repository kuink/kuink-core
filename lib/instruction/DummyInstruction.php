<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class DummyInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		//kuink_mydebug('Dummy');
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		//Do nothing only force that inner instructions are called in the instruction refactor infrastructure
		return $content;
	}
}

?>
