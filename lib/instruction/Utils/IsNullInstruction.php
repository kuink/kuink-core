<?php

namespace Kuink\Core\Instruction\Utils;

/**
 * And Instruction
 *
 * @author paulo.tavares
 */
class IsNullInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs an IsNull operation. Returns the first non null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		foreach ( $params as $param ) {
			if (is_array ( $param ) && count ( $param ) == 0)
				$param = null;
			
			if ($param != null)
				return $param;
		}
		return null;
	}
}

?>
