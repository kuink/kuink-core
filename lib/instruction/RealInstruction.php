<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class RealInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Real Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */

	//A list is a string, so an empty list is an empty string
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		if (($content === '') || ($content === NULL))
			$content = 0.0;
		return floatval($content);
	}

	static public function parse($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$eval = new \Kuink\Core\EvalExpr ();
		$content = $eval->e ( $content, $instManager->variables, FALSE, FALSE, FALSE ); // Eval and return a value without ''
		return floatval($content);
	}

}

?>
