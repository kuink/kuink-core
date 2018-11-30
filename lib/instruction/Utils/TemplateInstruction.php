<?php

namespace Kuink\Core\Instruction\Utils;

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
				
		$nameParts = explode ( ',', $name );
		if (count ( $nameParts ) != 3)
			throw new \Exception ( 'Template: '.$name.' name must be method or appName,processName,template ' );
		
		$application = (trim ( $nameParts [0] ) == 'this') ? $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::APPLICATION] : trim ( $nameParts [0] );
		$process = (trim ( $nameParts [1] ) == 'this') ? $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::PROCESS] : trim ( $nameParts [1] );
		$template = trim ( $nameParts [2] );
		
		$params [] = $name;
		$params [] = $language;
		$params [] = $instManager->getParams( $instructionXmlNode, true ); //Get the params defined in params attribute
		$tl = new \TemplateLib ( $instManager->nodeConfiguration, null );
		$result = $tl->ExecuteStandardTemplate ( $params );
		
		return $result;
	}
}

?>
