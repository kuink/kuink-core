<?php

namespace Kuink\Core\Instruction;

/**
 * Description
 *
 * @author paulo.tavares
 */
class MicroTimeInstruction extends \Kuink\Core\Instruction{
	
	/**
	 * Converts a list to a set
	 * 
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute( $instManager, $instructionXmlNode ) {

		return microtime(true);
	}
}

?>
