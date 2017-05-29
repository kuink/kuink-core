<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class ObjectToSetInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		
		$json = json_encode ( $content );
		$set = json_decode ( $json, true );
		
		return $set;
	}
}

?>
