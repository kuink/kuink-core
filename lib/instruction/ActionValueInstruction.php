<?php

namespace Kuink\Core\Instruction;

/**
 * And Instruction
 *
 * @author paulo.tavares
 */
class ActionValueInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an ActionValue operation. Returns the first non null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		// check if this is a set or a get
		$set = (($instructionXmlNode->count () > 0) || ($instructionXmlNode[0] != ''));
		$value = '';
		// Are we setting a value or retrieving?
		if ($set) {
				$value = $instManager->executeInnerInstruction ( $instructionXmlNode );
				// Update the actionvalue
				$instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION_VALUE] = $value;
		} else
			$value = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION_VALUE];
		
		return $value;
	}
}

?>
