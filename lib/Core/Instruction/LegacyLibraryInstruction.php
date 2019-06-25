<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class LegacyLibraryInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Legacy libraries in kuink in a smooth way
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */

	//A list is a string, so an empty list is an empty string
	static public function execute($instManager, $instructionXmlNode) {
		$library = $instructionXmlNode->getName();
		$managerName = '\\Kuink\\Core\\Lib\\' . $library;
		$manager = new $managerName($instManager->nodeConfiguration, \Kuink\Core\MessageManager::getInstance());
		$params = $instManager->getParams ( $instructionXmlNode );
		$method = $instManager->getAttribute( $instructionXmlNode, 'method');
		if (method_exists ( $manager, $method ))
			$result = $manager->$method ( $params );
		else
			throw new \Exception ( 'InstructionManager:: Invalid instruction in ' . $library.'->'.$methodname );		

		return $result;
	}

}

?>
