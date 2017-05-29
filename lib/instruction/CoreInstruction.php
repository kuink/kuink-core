<?php

namespace Kuink\Core\Instruction;

/**
 * Call a native object method
 *
 * @author paulo.tavares
 */
class CoreAttributes {
	const METHOD = 'method';
}
class CoreInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Call a core object method
	 */
	static public function execute($instManager, $instructionXmlNode) {
		return null;
	}
	
	/**
	 * Calls a method from ProcessOrchestrator
	 * 
	 * @param unknown $instManager
	 *        	- The instruction Manager
	 * @param unknown $instructionXmlNode
	 *        	- The instruction to execute
	 */
	static public function processOrchestrator($instManager, $instructionXmlNode) {
		$method = ( string ) $instManager->getAttribute ( $instructionXmlNode, NativeCallAttributes::METHOD, true );
		
		$result = call_user_func ( '\Kuink\Core\ProcessOrchestrator::' . $method );
		
		return $result;
	}
}

?>
