<?php

namespace Kuink\Core\Instruction\Logic;

/**
 * Or Instruction
 *
 * @author paulo.tavares
 */
class OrInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an OR logical operator in all params
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		//Verify if params are less than the first
		foreach ( $params as $value ) {
			$lit = (bool)$value;
			if ($lit === true)
				return 1;
		}
		return 0;		
	}
}

?>
