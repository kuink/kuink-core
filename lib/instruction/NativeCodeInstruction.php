<?php

namespace Kuink\Core\Instruction;

/**
 * Call a native code
 *
 * @author paulo.tavares
 */
class NativeCodeInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Call a core object method
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$code = (string)$instructionXmlNode [0];
		eval ( $code );
		return null;
	}
	
}

?>
