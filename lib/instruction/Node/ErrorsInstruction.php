<?php

namespace Kuink\Core\Instruction\Node;

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
		$errors = $msgMaanager->has_type ( \Kuink\Core\MessageType::ERROR );
		return $errors;
	}
}

?>
