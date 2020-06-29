<?php

namespace Kuink\Core\Instruction;

/**
 * Config Instruction
 *
 * @author paulo.tavares
 */
class LogInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Gets a config key
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$type = self::getAttribute ( $instructionXmlNode, 'type', $instManager->variables, true );//$this->get_inst_attr ( $instruction_xmlnode, 'type', $variables, true );
		$key = self::getAttribute ( $instructionXmlNode, 'key', $instManager->variables, true );//$this->get_inst_attr ( $instruction_xmlnode, 'key', $variables, true );
		$action = self::getAttribute ( $instructionXmlNode, 'action', $instManager->variables, false, $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION] );//$this->get_inst_attr ( $instruction_xmlnode, 'action', $variables, false, $nodeconfiguration [NodeConfKey::ACTION] );
		
		if ($key == '')
			$key = ( string ) $instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::ACTION_VALUE];
		if ($key == '')
			$key = '-';
		
		$params = $instManager->getParams ( $instructionXmlNode );	
		$paramsxml = $instructionXmlNode->xpath ( './Param' );
		
		$message = isset($param[0]) ? ( string ) $param [0] : '';
		
		$datasource = new \Kuink\Core\DataSource ( null, 'framework/framework,generic,insert', 'framework', 'generic' );
		
		$pars = array (
				'table' => 'fw_log',
				'id_user' => $KUINK_CFG->auth->user->id,
				'type' => $type,
				'application' => $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::APPLICATION],
				'process' => $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::PROCESS],
				'node' => $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::NODE],
				'action' => $action,
				'log_key' => ( string ) $key,
				'timestamp' => time (),
				'message' => $message 
		);
		$log = $datasource->execute ( $pars );
		
		return '';
	}
}

?>
