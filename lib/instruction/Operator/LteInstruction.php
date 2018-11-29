<?php

namespace Kuink\Core\Instruction\Operator;

/**
 * Lte Instruction
 *
 * @author paulo.tavares
 */
class LteInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Test if first param is less then all others
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		$first = true;
		$firstValue = null;
		
		//Verify if params are less than the first
		foreach ( $params as $value ) {
			if ($first){
				$firstValue = $value;
				$first = false;
			}
			else 
				if (!($firstValue <= $value))
					return 0;
		}
		return 1;		
	}
}

?>
