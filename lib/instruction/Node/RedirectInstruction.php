<?php

namespace Kuink\Core\Instruction\Node;

/**
 * Redirect Instruction
 *
 * @author paulo.tavares
 */
class RedirectInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a Redirect
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$global = ( string ) self::getAttribute ( $instructionXmlNode, 'global', $instManager->variables, false, 'false' );
		
		// Execute inner instructions to get the url to redirect
		$url = ( string ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		
		if ($global == 'false')
			redirect ( $url, '', 0 );
		else
			redirect ( $url, '', 1 );
		
		return $url;
	}
}

?>
