<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class DummyInstruction extends \Kuink\Core\Instruction{
	
	/**
	 * Converts a list to a set
	 * 
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute( $instManager, $instructionXmlNode ) {
		$instManager->variables['test'] = 'MODIFIED';
		$instManager->exit = true;
		$instManager->break = true;
		return '';
	}
}

?>
