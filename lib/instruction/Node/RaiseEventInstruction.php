<?php

namespace Kuink\Core\Instruction\Node;

/**
 * Action Instruction
 *Action
 * @author paulo.tavares
 */
class RaiseEventInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an ActionValue operation. Returns the first non null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$baseurl = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::BASEURL];
		
		$eventName = ( string ) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, false );
		
		// Read the event parameters
		$params = $instManager->getParams ( $instructionXmlNode );
		if (isset ( $params )) {
			// Store these params in the global session variable EVENT_PARAMS
			\Kuink\Core\ProcessOrchestrator::setEventParams ( $params );
		}
		
		// Get the event name from within the node and execute all instructions there
		if ($eventName == '') {
			$eventName = (string) $instManager->executeInnerInstruction ( $instructionXmlNode );
		}
		// var_dump( $instruction_xmlnode );
		// die();
		$instManager->runtime->event_raised = true;
		$instManager->runtime->event_raised_name = $eventName;
		$instManager->runtime->event_raised_params = $params;

		return null;
	}
}

?>
