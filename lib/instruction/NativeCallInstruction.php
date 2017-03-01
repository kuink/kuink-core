<?php

namespace Kuink\Core\Instruction;

/**
 * Call a native object method
 *
 * @author paulo.tavares
 */
class NativeCallAttributes {
	const OBJECT = 'object';
	const METHOD = 'method';
	const PROPERTY = 'property';
}

class NativeCallInstruction extends \Kuink\Core\Instruction{
	
	/**
	 * Call a native object method
	 */
	static public function execute( $instManager, $instructionXmlNode ) {
		$object = (string)$instManager->getAttribute($instructionXmlNode, NativeCallAttributes::OBJECT, $instManager->variables, true);
		$method = (string)$instManager->getAttribute($instructionXmlNode, NativeCallAttributes::METHOD, false, null);
		$property = (string)$instManager->getAttribute($instructionXmlNode, NativeCallAttributes::PROPERTY, false, null);
		$params = $instManager->getParams( $instructionXmlNode );
		
		if ($method == null && $property == null)
			throw new \Exception('Method or Property not supplies to NativeCall instruction');

		$variable = $instManager->getVariable($object);
			
		if ($method != null) {
			$result = call_user_func_array(array($variable, $method), $params);
		}
		else {
			$result = $variable->{$property};
		}
		
		return $result;
	}
}

?>
