<?php

namespace Kuink\Core\Instruction;

/**
 * ForEach Instruction
 *
 * @author paulo.tavares
 */
class ForEachInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an ForEach
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$varName =  self::getAttribute ( $instructionXmlNode, 'var', $instManager->variables, true );
		$itemName = self::getAttribute ( $instructionXmlNode, 'item', $instManager->variables, true );
		$keyName = self::getAttribute ( $instructionXmlNode, 'key', $instManager->variables, false );
		
		$set = isset($instManager->variables [$varName]) ? $instManager->variables [$varName] : array();
		$value = '';
		if (is_array($set) || is_object($set))
			foreach ( $set as $key => $item ) {
				if (is_object ( $item ))
					$item = ( array ) $item;
				$instManager->variables [$itemName] = $item;
				$instManager->variables [$keyName] = $key;
				$value = $instManager->executeInnerInstructions( $instructionXmlNode );
			}
		
		return $value;
	}
}

?>
