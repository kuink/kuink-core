<?php

namespace Kuink\Core\Instruction;

/**
 * GetPAram - gets a param defined in $_GET
 *
 * @author paulo.tavares
 */
class ScriptInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Return null
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		global $KUINK_TRACE;
		$src = ( string )self::getAttribute ( $instructionXmlNode, 'source', $instManager->variables, true );
		$user = ( string ) self::getAttribute ( $instructionXmlNode, 'user', $instManager->variables, false, '' );//$this->get_inst_attr ( $instructionXmlNode, 'user', $variables, false, '' );
		$password = ( string ) self::getAttribute ( $instructionXmlNode, 'password', $instManager->variables, false, '' );//$this->get_inst_attr ( $instructionXmlNode, 'password', $variables, false, '' );
		
		$params = array ();
		$tParams =$instManager->getParams ( $instructionXmlNode );// $this->aux_get_param_values_complete ( $nodeconfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $variables, $exit );
		
		$paramsvar = (isset ( $instructionXmlNode ['params'] )) ? ( string ) $instructionXmlNode ['params'] : '';
		$tParamsAttr = ($paramsvar != '') ? $instManager->variables [$paramsvar] : array ();
		foreach ( $tParamsAttr as $tKey => $tValue )
			$tParams [] = $tValue;
		
		$params = $tParams;
		
		$scriptParams = '';
		foreach ( $params as $value )
			$scriptParams = $scriptParams . $value . ' ';
		
		if ($user != '')
			$script = 'echo ' . $password . ' | ' . 'sudo ' . $user . ' -S sh ' . $src . ' ' . $scriptParams;
		else
			$script = 'sh ' . $src . ' ' . $scriptParams;
		$KUINK_TRACE [] = 'Shell Script: ' . $script;
		$result = exec ( $script, $output );

		$KUINK_TRACE [] = $output;
		
		return $output;
	}
}

?>
