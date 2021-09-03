<?php

namespace Kuink\Core\Instruction;

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
		$escape = self::getAttribute ( $instructionXmlNode, 'escape', $instManager->variables, false, 'true' );
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		if ($escape == 'true')
			$list = json_encode ( $content, JSON_HEX_APOS|JSON_HEX_QUOT );
		else
			$list = json_encode ( $content, JSON_UNESCAPED_UNICODE );
		
		return $list;
	}
}

?>
