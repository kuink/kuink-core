<?php

namespace Kuink\Core\Instruction;

/**
 * Eval Instruction
 *
 * @author paulo.tavares
 */
class EvalInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an ActionValue operation. Returns the first non null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$value = (string) $instManager->executeInnerInstruction ( $instructionXmlNode );

		\libxml_use_internal_errors ( true );
		$evalInstructionsXml = \simplexml_load_string ( $value );
		$errors = \libxml_get_errors ();
		if ($evalInstructionsXml == null) {
			$errorMsg = '';
			foreach ( $errors as $error )
				$errorMsg .= $error->message;
			
			throw new \Exception ( 'Error loading eval instructions: ' . $errorMsg );
		}
		$container = ( string ) $evalInstructionsXml [0]->getName ();
		if ($container != 'Eval')
			throw new \Exception ( 'Expected Eval instruction as container.' );
		
		$evalValue = $instManager->executeInnerInstructions( $evalInstructionsXml );	

		return $evalValue;
	}
}

?>