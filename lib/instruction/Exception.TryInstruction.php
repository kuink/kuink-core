<?php

namespace Kuink\Core\Instruction;

/**
 * If Instruction
 *
 * @author paulo.tavares
 */
class TryInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an If
	 */
	static public function execute($instManager, $instructionXmlNode) {
		global $KUINK_TRACE;
		
		//Get the condition to the 'If' instruction
		$instructionsXml = $instructionXmlNode->xpath('./Instructions');

		if (! $instructionsXml)
			throw new \Exception("Instruction: $instructionname. No 'Instructions' block supplied to the instruction.");

		$value = '';
		try {
				$instResult = $instManager->executeInnerInstructions( $instructionsXml );
		}
		catch ( \Kuink\Core\Exception\GenericException $e ) {
			$exceptionName = $e->name;
			$KUINK_TRACE[] = 'Exception detected '.$e->__toString(); 
			
			if ($exceptionName != '' && $exceptionName != null) {
				//This is a typed exception 
				//try to catch this exception directly
				$catchXml = $instructionXmlNode->xpath('./Catch[@exception="'.$exceptionName.'"]');
				//There's a match?
				if (!$catchXml || count($catchXml) == 0) {
					//If no then try to find a general catch
					$catchXml = $instructionXmlNode->xpath('./Catch[not(@exception)]');
				} else {
					$KUINK_TRACE[] = 'Direct Catch '.$exceptionName;
				}
			} else {
				//old behaviour
				$catchXml = $instructionXmlNode->xpath('./Catch');
			}
			
			//If 'Catch' does not exist, rethrown the exception....
			if (!$catchXml || count($catchXml) == 0) {
				$KUINK_TRACE[] = 'Exception not catched';
				throw $e;
			}
			$KUINK_TRACE[] = 'Default Catch';

			//Add the exception message to variables
			//Set the temporary EXCEPTION variable
			$instManager->variables['EXCEPTION']['name'] = $exceptionName;
			$instManager->variables['EXCEPTION']['message'] = (string)$e->__toString();

			$msgVar = (string)self::getAttribute ( $catchXml[0], 'msg', $instManager->variables, false ); //$msgVar = (string)$this->get_inst_attr($catchXml[0], 'msg', $instManager->variables, false);
			if ( $msgVar != '')
				$instManager->variables[ $msgVar ] = $e->getMessage();

			$value = $instManager->executeInnerInstructions( $catchXml );
			//Clean the last exception
			unset($instManager->variables['EXCEPTION']);
		}
		catch (\Exception $e) {
			$KUINK_TRACE[] = 'Exception: '.$e->getMessage();			
			//print_object(get_class($e));
			//print_object($e->getPrevious());
			
			$catchXml = $instructionXmlNode->xpath('./Catch');
			if (!$catchXml || count($catchXml) == 0) {
				$KUINK_TRACE[] = 'Exception not catched';
				throw $e;
			}
			$msgVar = (string)self::getAttribute ( $catchXml[0], 'msg', $instManager->variables, false );//$this->get_inst_attr($catchXml[0], 'msg', $instManager->variables, false);
			if ( $msgVar != '')
				$instManager->variables[ $msgVar ] = $e->getMessage();
			
			$value = $instManager->executeInnerInstructions( $catchXml );
		}
		return $value;
	}
}

?>
