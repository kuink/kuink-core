<?php

namespace Kuink\Core\Instruction\Debug;

/**
 * Print Instruction
 *
 * @author paulo.tavares
 */
class PrintInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {

		$newLine = ( string ) self::getAttribute ( $instructionXmlNode, 'newline', $instManager->variables, false, 'false' );//$this->get_inst_attr ( $instruction_xmlnode, 'newline', $variables, false, 'false' );
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );

		if ($newLine == 'true')
			$content .= '<br/>';
		
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		$layout->addHtml ( $content, 'debugMessages' );
		
		return null;
	}
}

?>
