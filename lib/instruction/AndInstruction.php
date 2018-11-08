<?php

namespace Kuink\Core\Instruction;

/**
 * And Instruction
 *
 * @author paulo.tavares
 */
class AndInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an AND logical operator in all params
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		//Verify if params are less than the first
		foreach ( $params as $value ) {
			$lit = (bool)$value;
			if ($lit === false)
				return false;
		}
		return true;		
	}
}

?>
