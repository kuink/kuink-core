<?php

namespace Kuink\Core\Instruction;

/**
 * And Instruction
 *
 * @author paulo.tavares
 */
class LangInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an AND logical operator in all params
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$key = self::getAttribute ( $instructionXmlNode, 'key', $instManager->variables, true );
		$params = $instManager->getParams ( $instructionXmlNode );
		$values = array ();
		
		foreach ( $params as $param )
			if ($param->count () > 0)
				$values [] = (string) $param;
			
			// var_dump($nodeconfiguration['customappname']);
		$string = ( string ) kuink_get_string ( $key, $instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::APPLICATION], $values );
		// kuink_mydebug($key, $string);
		return $string;		
	}
}

?>
