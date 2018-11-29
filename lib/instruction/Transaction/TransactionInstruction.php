<?php

namespace Kuink\Core\Instruction\Transaction;

/**
 * DatSource instruction
 *
 * @author paulo.tavares
 */
class TransactionInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Set or get ds params
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		\Kuink\Core\DataSourceManager::beginTransaction ();
		
		$value = $instManager->executeInnerInstructions( $instructionXmlNode );		
		
		\Kuink\Core\DataSourceManager::commitTransaction ();
		return $value;
	}
}

?>
