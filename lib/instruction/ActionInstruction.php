<?php

namespace Kuink\Core\Instruction;

/**
 * Action Instruction
 *Action
 * @author paulo.tavares
 */
class ActionInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an ActionValue operation. Returns the first non null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$actionName = (string) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true );//$this->get_inst_attr ( $instruction_xmlnode, 'name', $instManager->variables, true );
		$actionValue = (string) self::getAttribute ( $instructionXmlNode, 'value', $instManager->variables, false, null );//$this->get_inst_attr ( $instruction_xmlnode, 'name', $instManager->variables, true );
		$actionValueOld = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION_VALUE];
		
		//Change the action value before execution if it says so
		if ($actionValue != null)
			$instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION_VALUE] = $actionValue;
		
		$actionXml = $instManager->runtime->nodeManager->nodeXml->xpath ( '/Node/Actions/Action[@name="' . $actionName . '"]' );	

		if (! $actionXml)
			throw new \Exception ( 'Action ' . $actionName . ' does not exists.' );

		$actionScreen = ( string ) $actionXml [0] ['screen'];
		$postForm = isset ( $_GET ['form'] ) ? ( string ) $_GET ['form'] : '';	
		// Chech to see if the screen have a form with this name
		$formXml = $instManager->runtime->nodeManager->nodeXml->xpath ( '/Node/Screens/Screen[@id="' . $actionScreen . '"]/Form[@name="' . $postForm . '"]' );
	
		$clearPost = false;
		
		$msgManager = \Kuink\Core\MessageManager::getInstance ();
		if (! $msgManager->has_type ( \Kuink\Core\MessageType::ERROR )) {
			$clearPost = ($actionScreen != '' && $postForm != '' && ! $formXml);
		}
		
		// Clear the post
		if ($clearPost) {
			// Clean up post data
			unset ( $instManager->variables ['POSTDATA'] );
			foreach ( $_POST as $key => $value )
				if ($key != 'sesskey')
					unset ( $_POST [$key] );
		}
		
		// Execute the action without a browser redirect
		$instManager->runtime->action_execute ( $instManager->nodeConfiguration, $instManager->runtime->nodeManager->nodeXml, $actionXml, $actionName, $instManager->variables );

		//Restore the old action value
		$instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION_VALUE] = $actionValueOld;

		return null;
	}

	static public function getUrl($instManager, $instructionXmlNode) {
		$utils = new \UtilsLib ( $instManager->nodeConfiguration, null );
		$actionName = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$url = $utils->ActionUrl ( array (
			0 => $actionName 
	) );
		return $url;
	}

}

?>
