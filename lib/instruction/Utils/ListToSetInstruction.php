<?php

namespace Kuink\Core\Instruction\Utils;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class ListToSetInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$separator = ( string ) self::getAttribute ( $instructionXmlNode, 'separator', $instManager->variables, false, ',' );
		$content = ( string ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		if (trim ( $content ) == '')
			$set = array ();
		else
			$set = explode ( $separator, $content );
		return $set;
	}
}

?>
