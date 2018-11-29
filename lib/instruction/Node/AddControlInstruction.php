<?php

namespace Kuink\Core\Instruction\Node;

/**
 * AddControl Instruction
 *
 * @author paulo.tavares
 */
class AddControlInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Adds a control to the screen
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$type = ( string ) self::getAttribute ( $instructionXmlNode, 'control-type', $instManager->variables, true ); //$this->get_inst_attr ( $instruction_xmlnode, 'control-type', $variables, true );
		$name = ( string ) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true );//$this->get_inst_attr ( $instruction_xmlnode, 'name', $variables, true );
		
		$controlDefinitionStr = '<' . $type;
		foreach ( $instructionXmlNode->attributes () as $key => $value ) {
			$attrValue = self::getAttribute ( $instructionXmlNode, '$key', $instManager->variables, false ); //$this->get_inst_attr ( $instruction_xmlnode, $key, $variables );
			$controlDefinitionStr .= ' ' . $key . '="' . $attrValue . '"';
		}
		$controlDefinitionStr .= '/>';
		$controlDefinitionXml = new \SimpleXMLElement ( $controlDefinitionStr );
		
		$control = \Kuink\Core\Factory::getControl ( $type, $instManager->nodeConfiguration, $controlDefinitionXml );
		
		// Put the control in the screen flow
		$instManager->variables [$name] = $control;
		$instManager->runtime->current_controls [] = $control;		
		
		return $control;
	}
}

?>
