<?php

namespace Kuink\Core\Instruction;

/**
 * Bool Instruction
 *
 * @author paulo.tavares
 */
class BoolInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Bool Types
	 *
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		return (bool) $content;
	}

	static public function true($instManager, $instructionXmlNode) {
		return TRUE;
	}

	static public function false($instManager, $instructionXmlNode) {
		return FALSE;
	}
	
	static public function parse($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$eval = new \Kuink\Core\EvalExpr ();
		$content = $eval->e ( $content, $instManager->variables, FALSE, FALSE, FALSE ); // Eval and return a value without ''
		return (bool) ($content);
	}

	static public function string($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$eval = new \Kuink\Core\EvalExpr ();
		$content = $eval->e ( $content, $instManager->variables, FALSE, FALSE, FALSE );
		return ((filter_var($content,  FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false');
	}

}

?>
