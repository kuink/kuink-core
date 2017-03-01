<?php

namespace Kuink\Core\Instruction;

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
	static public function execute( $instManager, $instructionXmlNode ) {
		global $KUINK_DATASOURCES;
		
		//TODO
		return '';
	}

}

?>
