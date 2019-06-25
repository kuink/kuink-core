<?php

namespace Kuink\Core\Instruction;

use Kuink\Core\Lib\UtilsLib;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class UuidInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$utils = new UtilsLib ( $instManager->nodeConfiguration, null );
		$uuid = $utils->Uuid ();
		return ( string ) $uuid;
	}
}

?>
