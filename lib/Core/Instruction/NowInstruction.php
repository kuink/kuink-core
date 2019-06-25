<?php

namespace Kuink\Core\Instruction;

use Kuink\Core\Lib\DateTimeLib;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class NowInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$lib = new DateTimeLib ( $instManager->nodeConfiguration, null );
		$result = $lib->Now ( null );
		return ( string ) $result;
	}
}

?>
