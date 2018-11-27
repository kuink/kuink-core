<?php

namespace Kuink\Core\Instruction;

/**
 * Eq Instruction
 *
 * @author paulo.tavares
 */
class EqInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Test if params are equal
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		$firstValue = isset($params[0]) ? (string) $params[0] : null;
		//Verify if params are all equal
		foreach ( $params as $value ) {
			$lit = (string) $value;
			if ($lit != $firstValue)
				return 0;
		}
		return 1;		
	}
}

?>
