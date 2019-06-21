<?php

namespace Kuink\Core\Instruction;

/**
 * Expr Instruction
 *
 * @author paulo.tavares
 */
class ExprInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Reolves an expression
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {

		$conditionExpr = self::getAttribute ( $instructionXmlNode, 'value', $instManager->variables, false, '' );
		$conditionExpr = ($conditionExpr == '') ? (string) $instManager->executeInnerInstruction ( $instructionXmlNode ) : $conditionExpr;

		$eval = new \Kuink\Core\EvalExpr ();
		return $eval->e ( $conditionExpr, $instManager->variables, FALSE );
	}
}

?>
