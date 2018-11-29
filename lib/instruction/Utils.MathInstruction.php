<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class MathInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 * 
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute( $instManager, $instructionXmlNode ) {
		return null;
	}

	static public function round( $instManager, $instructionXmlNode ) {
		$params = $instManager->getParams( $instructionXmlNode );
    	$value = (float) $params[0];
    	$precision = (isset($params[1])) ? $params[1] : 0;
    	return round($value, $precision);
	}

}

?>
