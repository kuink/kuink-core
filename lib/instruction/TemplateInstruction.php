<?php

namespace Kuink\Core\Instruction;

/**
 * Template Instruction
 *
 * @author paulo.tavares
 */
class TemplateInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Sends Template, expands a template
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$name = ( string ) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true); //$this->get_inst_attr ( $instructionXmlNode, 'name', $instManager->variables, true );
		$language = ( string ) self::getAttribute ( $instructionXmlNode, 'lang', $instManager->variables, false, $instManager->variables ['USER'] ['lang']); //$this->get_inst_attr ( $instructionXmlNode, 'lang', $instManager->variables, false, $instManager->variables ['USER'] ['lang'] );

		$params = array();
		$params [] = $name;
		$params [] = $language;
		$params [] = $instManager->getParams( $instructionXmlNode, true ); //Get the params defined in params attribute
		$tl = new \TemplateLib ( $instManager->nodeConfiguration, null );
		$result = $tl->ExecuteStandardTemplate ( $params );
		
		return $result;
	}
}

?>
