<?php

namespace Kuink\Core\Instruction\Utils;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class SetToJsonInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$list = json_encode ( $content );
		
		return $list;
	}
}

?>
