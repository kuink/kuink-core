<?php

namespace Kuink\Core\Instruction\Loop;

/**
 * Repeat Instruction
 *
 * @author paulo.tavares
 */
class RepeatInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a Repeat Loop
	 */
	static public function execute($instManager, $instructionXmlNode) {

		// Get inline Condition raw, do not expand variables inside
		$conditionExpr = (string) self::getAttributeRaw ( $instructionXmlNode, 'until', $instManager->variables, true );

		$eval = new \Kuink\Core\EvalExpr ();		 			
		
		$condition = false;
		$value = '';
		while ( !$condition ) {
			$value = $instManager->executeInnerInstructions( $instructionXmlNode );
			//Reevaluate the condition to determine if next loop will occur
			$condition = $eval->e ( $conditionExpr, $instManager->variables, TRUE );
		}
		return $value;
	}
}

?>
