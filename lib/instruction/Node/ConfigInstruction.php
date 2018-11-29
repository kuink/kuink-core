<?php

namespace Kuink\Core\Instruction\Node;

/**
 * Config Instruction
 *
 * @author paulo.tavares
 */
class ConfigInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Gets a config key
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$configKey = self::getAttribute ( $instructionXmlNode, 'key', $instManager->variables, true );
		
		$config = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::CONFIG];
		
		$value = '';
		
		if (isset ( $config [$configKey] ))
			$value = ( string ) $config [$configKey];
		else {
			// get the value from fw_config
			$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
			$params ['_entity'] = 'fw_config';
			$params ['id_company'] = $instManager->variables ['USER'] ['idCompany'];
			$params ['code'] = $configKey;
			$resultset = $dataAccess->execute ( $params );
			if (isset ( $resultset ['value'] ))
				$value = ( string ) $resultset ['value'];
		}
		
		return $value;
	}
}

?>
