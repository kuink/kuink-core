<?php

namespace Kuink\Core\Instruction;

/**
 * If Instruction
 *
 * @author paulo.tavares
 */
class IfInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an If
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get inline Condition

		$conditionExpr = isset ( $instructionXmlNode ['condition'] ) ? ( string ) $instructionXmlNode ['condition'] : null;
		//kuink_mydebug('Condition', $conditionExpr);
		// Get the condition to the 'If' instruction
		$conditionXml = $instructionXmlNode->xpath ( './Condition' );
		
		if (! $conditionXml && ! $conditionExpr)
			throw new \Exception ( "Instruction: $instructionname. No conditions supplied to the instruction." );
		
		$value = true;
		// Execute the instructions inside the conditions

		if ($conditionXml) {
				$value = $instManager->executeInnerInstruction( $conditionXml );
		} else {
			// Parse the conditionExpr
			$eval = new \Kuink\Core\EvalExpr ();
			try {
				$value = $eval->e ( $conditionExpr, $instManager->variables, TRUE );
			} catch ( \Exception $e ) {
				throw new \Exception('If::eval condition::'.$conditionExpr.'::'.$e->getMessage());
			}
		}
			
		// Check if the value is true or false (boolean)
		if ($value) {
			// Execute only the 'Then' instructions
			$thenXml = $instructionXmlNode->xpath ( './Then' );
			if (! $thenXml)
				throw new \Exception ( "Instruction: $instructionname. No 'Then' block supplied to the instruction." );
			
			$returnValue = $instManager->executeInnerInstructions( $thenXml );
		} else {
			// Execute only the 'Else' instructions
			$elseXml = $instructionXmlNode->xpath ( './Else' );

			// If 'Else' does not exist, do nothing....
			$returnValue = (! $elseXml) ? null : $instManager->executeInnerInstructions( $elseXml );
		}

		return $returnValue;
	}
}

?>
