<?php

namespace Kuink\Core\Instruction;

use Kuink\Core\InstructionManager;

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
     * @param InstructionManager $instManager
     *            - The instruction Manager
     * @param mixed $instructionXmlNode
     *            - The instruction to execute
     * @return mixed
     * @throws \Exception
     */
	static public function processOrchestrator($instManager, $instructionXmlNode) {
		$method = ( string ) $instManager->getAttribute ( $instructionXmlNode, NativeCallAttributes::METHOD, true );
		
		$result = call_user_func ( '\Kuink\Core\ProcessOrchestrator::' . $method );
		
		return $result;
	}
}

?>
