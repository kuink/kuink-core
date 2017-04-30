<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class UuidInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Neon\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$utils = new \UtilsLib ( $instManager->nodeConfiguration, null );
		$uuid = $utils->Uuid ();
		return ( string ) $uuid;
	}
}

?>
