<?php

namespace Kuink\Core\Instruction\Type;

/**
 * Description of Xml
 *
 * @author paulo.tavares
 */
class XmlInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Xml Instruction
	 */
	static public function execute($instManager, $instructionXmlNode) {
		return $instructionXmlNode[0];
	}
}

?>
