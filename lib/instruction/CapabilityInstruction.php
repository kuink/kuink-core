<?php

namespace Kuink\Core\Instruction;

/**
 * Capability instruction
 *
 * @author paulo.tavares
 */
class CapabilityInstruction extends \Kuink\Core\Instruction {
	static public function execute($instManager, $instructionXmlNode) {
		$capabilities = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::CAPABILITIES];
		$name =  (string) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true );
		
		$value = isset ( $capabilities [$name] ) ? 1 : 0;
		return $value;
	}
}

?>
