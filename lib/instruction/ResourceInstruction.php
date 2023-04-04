<?php

namespace Kuink\Core\Instruction;

/**
 * Instuction to manage resources 
 *
 * @author paulo.tavares
 */
class ResourceInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles Set Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$object = self::getAttribute ( $instructionXmlNode, 'object', $instManager->variables, true, '' );		
	}

	//Get the path of a resource
	static public function getPath($instManager, $instructionXmlNode) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		$object = self::getAttribute ( $instructionXmlNode, 'object', $instManager->variables, true, '' );

		//Split to have app,process,resource
		$kid = explode(',', $object);
		if (count($kid) != 3) 
			throw new \Exception('Resource object invalid: '.$object);
		
		$rAppName = trim( $kid[0] );
		$rProcessName = trim( $kid[1] );
		$rName = trim( $kid[2] );
		$appBase = isset($KUINK_APPLICATION) ? $KUINK_APPLICATION->appManager->getApplicationBase($rAppName):'';
		$fullPath = $KUINK_CFG->appRoot.'apps/'.$appBase.'/'.$rAppName.'/process/'.$rProcessName.'/resources/'.$rName;
		if (!file_exists($fullPath))
			throw new \Exception('Resource file does not exists: '.$object);

		return $fullPath;
	}	
}

?>
