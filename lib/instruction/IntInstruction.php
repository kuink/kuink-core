<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class IntInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Int Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */

	//A list is a string, so an empty list is an empty string
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		return (int) $content;
	}

	static public function parse($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$eval = new \Kuink\Core\EvalExpr ();
		$content = $eval->e ( $content, $instManager->variables, FALSE, FALSE, FALSE ); // Eval and return a value without ''
		return round($content);
	}

}

?>
