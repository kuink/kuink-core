<?php

namespace Kuink\Core\Instruction;

/**
 * NEq Instruction
 *
 * @author paulo.tavares
 */
class NEqInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return if params are not equal
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$eqInst = new EqInstruction();
		$result = $eqInst->execute($instManager, $instructionXmlNode);
		return (!$result);		
	}
}

?>
