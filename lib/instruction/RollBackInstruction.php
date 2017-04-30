<?php

namespace Kuink\Core\Instruction;

/**
 * DatSource instruction
 *
 * @author paulo.tavares
 */
class RollBackInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Set or get ds params
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		\Kuink\Core\DataSourceManager::rollbackTransaction ();
		return '';
	}
}

?>
