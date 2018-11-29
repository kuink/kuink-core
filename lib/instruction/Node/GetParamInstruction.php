<?php

namespace Kuink\Core\Instruction\Node;

/**
 * GetPAram - gets a param defined in $_GET
 *
 * @author paulo.tavares
 */
class GetParamInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$paramName = (string) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true );
		$param = isset ( $_GET ["$paramName"] ) ? ( string ) $_GET ["$paramName"] : '';
		return $param;
	}
}

?>
