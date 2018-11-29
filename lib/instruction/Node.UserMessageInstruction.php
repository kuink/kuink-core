<?php

namespace Kuink\Core\Instruction;

/**
 * UserMessage Instruction
 *
 * @author paulo.tavares
 */
class UserMessageInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a UserMessageInstruction
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$type = ( string ) self::getAttribute ( $instructionXmlNode, 'type', $instManager->variables, true );
		
		if (! in_array ( $type, array (
				'error',
				'warning',
				'information',
				'success',
				'exception' 
		) ))
			throw new \Exception ( 'UserMessage: invalid type-' . $type . ' :: try type= "error" | "warning" | "information" | "success" | "Exception" ' );
		
		$text = ( string ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		
		$msgType = \Kuink\Core\MessageType::ERROR;
		switch ($type) {
			case "error" :
				$msgType = \Kuink\Core\MessageType::ERROR;
				break;
			case "warning" :
				$msgType = \Kuink\Core\MessageType::WARNING;
				break;
			case "information" :
				$msgType = \Kuink\Core\MessageType::INFORMATION;
				break;
			case "success" :
				$msgType = \Kuink\Core\MessageType::SUCCESS;
				break;
			case "exception" :
				$msgType = \Kuink\Core\MessageType::EXCEPTION;
				break;
			default :
				throw new \Exception ( 'UserMessage: invalid type-' . $type . ' :: try type= "error" | "warning" | "information" | "success" | "exception"' );
		}
		
		$msgManager = \Kuink\Core\MessageManager::getInstance ();
		$msgManager->add ( $msgType, $text );
		
		return;
	}
}

?>
