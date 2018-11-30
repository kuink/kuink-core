<?php

namespace Kuink\Core\Instruction;

/**
 * For Instruction
 *
 * @author paulo.tavares
 */
class ForInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a For Loop
	 */
	static public function execute($instManager, $instructionXmlNode) {

		// Get inline Condition
		$var = (string) self::getAttribute ( $instructionXmlNode, 'var', $instManager->variables, true ); //( string ) $this->get_inst_attr ( $instruction_xmlnode, 'var', $variables, true );
		$conditionExpr = (string) self::getAttributeRaw ( $instructionXmlNode, 'condition', $instManager->variables, true );
		$step = (int) self::getAttribute ( $instructionXmlNode, 'step', $instManager->variables, true ); //( int ) $this->get_inst_attr ( $instruction_xmlnode, 'step', $variables, true );
		$start = (int) self::getAttribute ( $instructionXmlNode, 'start', $instManager->variables, true ); //( int ) $this->get_inst_attr ( $instruction_xmlnode, 'start', $variables, false, null );
		
		if ($start !== null) {
			$instManager->variables[$var] = $start;
		}
			
			// Parse the conditionExpr
		$eval = new \Kuink\Core\EvalExpr ();
		$condition = $eval->e ( $conditionExpr, $instManager->variables, TRUE );
		
		$value = '';
		while ( $condition ) {
			$value = $instManager->executeInnerInstructions( $instructionXmlNode );
			$instManager->variables[$var] = $instManager->variables[$var] + $step;
			$condition = $eval->e ( $conditionExpr, $instManager->variables, TRUE );
		}
		return $value;
	}
}

?>
