<?php

namespace Kuink\Core\Instruction;

/**
 * Get a param value from the query string
 *
 * @author paulo.tavares
 */
class QueryStringInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Get the param value from the query string
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		return $this->get($instManager, $instructionXmlNode);
	}

	static public function get($instManager, $instructionXmlNode) {
		$param = self::getAttribute ( $instructionXmlNode, 'param', $instManager->variables, true );
		$value = null;
		if (isset($_GET[$param]))
			$value =  (string)$_GET[$param];
		return $value;
	}

}

?>
