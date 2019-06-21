<?php

namespace Kuink\Core\Instruction;

/**
 * Errors - gets a param defined in $_GET
 *
 * @author paulo.tavares
 */
class ErrorsInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$msgManager = \Kuink\Core\MessageManager::getInstance ();
		$errors = $msgManager->has_type ( \Kuink\Core\MessageType::ERROR );
		return $errors;
	}
}

?>
