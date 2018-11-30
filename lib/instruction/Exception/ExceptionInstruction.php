<?php

namespace Kuink\Core\Instruction\Exception;

/**
 * Exception Instruction
 *
 * @author paulo.tavares
 */
class ExceptionInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Throws an Exception
	 */
	static public function execute($instManager, $instructionXmlNode) {
		global $KUINK_TRACE;
		$name = (string) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, false, '' );//$this->get_inst_attr($instructionXmlNode, 'name', $instManager->variables, false, '');
		$conditionExpr = (string) self::getAttribute ( $instructionXmlNode, 'condition', $instManager->variables, false, '' );//$this->get_inst_attr($instructionXmlNode, 'condition', $instManager->variables, false, '');
				
		//If condition is set then evaluate it and only continue if it is true
		if ($conditionExpr != '') {
			$eval = new \Kuink\Core\EvalExpr();
				$value = $eval->e( $conditionExpr, $instManager->variables, TRUE);
				if (!$value)
					return; //If the condition is not true then return immediately, else let it flow
		}
		$KUINK_TRACE[] = '*** EXCEPTION ('.$name.')';   
		if ($name != '') {	
			//This is an exception with a name specified so act differently
			// Get all the params
			$params = $instManager->getParams ( $instructionXmlNode );			
		
			$message = (string)\Kuink\Core\Language::getExceptionString($name, $instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::APPLICATION], $params);
			$KUINK_TRACE[] = '*** EXCEPTION ('.$name.') - '.$message;           	
			throw new \Kuink\Core\Exception\GenericException($name, $message);
		}
		//Legacy behavior
		//Execute inner instructions
		$message = 'No message';
		$code = '';
		//Execute inner instructions
		
		$message = $instManager->executeInnerInstruction ( $instructionXmlNode );

		$KUINK_TRACE[] = '*** EXCEPTION ('.$name.') - '.$message;
		switch ($code) {
			case 'ZeroRowsAffected':
				throw new \Kuink\Core\Exception\ZeroRowsAffected($message);
				break;
			case 'ClassNotFound':
				throw new \Kuink\Core\Exception\ClassNotFound($message);
				break;
			case 'InvalidParameters':
				throw new \Kuink\Core\Exception\InvalidParameters($message);
				break;

			default:
				throw new \Exception($message);
				break;
		}
		return $message;
	}
}

?>
