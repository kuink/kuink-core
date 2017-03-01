<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class JsonToSetInstruction extends \Kuink\Core\Instruction{
	
	/**
	 * Converts a list to a set
	 * 
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute( $instManager, $instructionXmlNode ) {
		$content = (string)$instManager->executeInnerInstruction( $instructionXmlNode );

		$list = json_decode($content, true);

		return $list;
	}
}

?>
