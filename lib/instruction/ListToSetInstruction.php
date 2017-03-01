<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class ListToSetInstruction extends \Kuink\Core\Instruction{
	
	/**
	 * Converts a list to a set
	 * 
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute( $instManager, $instructionXmlNode ) {
		$content = (string)$instManager->executeInnerInstruction( $instructionXmlNode );		
		$set = explode(',', $content);

		return $set;
	}
	
}

?>
