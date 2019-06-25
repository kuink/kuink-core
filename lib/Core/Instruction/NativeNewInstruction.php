<?php

namespace Kuink\Core\Instruction;

use ReflectionClass;

/**
 * Call a native object method
 *
 * @author paulo.tavares
 */
class NativeNewAttributes {
	const CLASSNAME = 'class';
}
class NativeNewInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Call a native object method
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$class = ( string ) $instManager->getAttribute ( $instructionXmlNode, NativeNewAttributes::CLASSNAME, true );
		$params = $instManager->getParams ( $instructionXmlNode );
		
		// Create a new instance of this object
		$rc = new ReflectionClass ( $class );
		$result = $rc->newInstanceArgs ( $params );
		
		// var_dump($result);
		return $result;
	}
}

?>
