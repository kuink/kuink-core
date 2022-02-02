<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class JsonToSetInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$escape = self::getAttribute ( $instructionXmlNode, 'escape', $instManager->variables, false, 'true' );
		$content = ( string ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		if ($escape == 'false')
			$list = json_decode ( $content, true );
		else
			$list = json_decode ( $content, true, JSON_UNESCAPED_UNICODE );
		
		return $list;
	}
}

?>
