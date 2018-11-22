<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class SleepInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$interval = ( int ) self::getAttribute ( $instructionXmlNode, 'interval', $instManager->variables, false, 'false' );
		$content = ( int ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		sleep ( $interval + $content );
		return '';
	}
}

?>
