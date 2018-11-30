<?php

namespace Kuink\Core\Instruction\Loop;

/**
 * While Instruction
 *
 * @author paulo.tavares
 */
class WhileInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a While Loop
	 */
	static public function execute($instManager, $instructionXmlNode) {

		// Get inline Condition raw, do not expand variables inside
		$conditionExpr = (string) self::getAttributeRaw ( $instructionXmlNode, 'condition', $instManager->variables, true );
		 			
		// Parse the conditionExpr
		$eval = new \Kuink\Core\EvalExpr ();
		$condition = $eval->e ( $conditionExpr, $instManager->variables, TRUE );
		
		$value = '';
		while ( $condition ) {
			$value = $instManager->executeInnerInstructions( $instructionXmlNode );
			//Reevaluate the condition to determine if next loop will occur
			$condition = $eval->e ( $conditionExpr, $instManager->variables, TRUE );
		}
		return $value;
	}
}

?>
