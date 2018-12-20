<?php

namespace Kuink\Core\Instruction;

/**
 * DataAccess Instruction
 *
 * @author paulo.tavares
 */
class CallInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a Call
	 */

	static public function execute($instManager, $instructionXmlNode) {
	// Getting Function name and parameters
		// var_dump( $instructionXmlNode );
		global $KUINK_TRACE;
		$library = self::getAttribute ( $instructionXmlNode, 'library', $instManager->variables, false ); //$this->get_inst_attr ( $instructionXmlNode, 'library', $instManager->variables, false );
		$functionName = self::getAttribute ( $instructionXmlNode, 'function', $instManager->variables, false ); //$this->get_inst_attr ( $instructionXmlNode, 'function', $instManager->variables, false );

		
		$KUINK_TRACE[] = 'Call: '.$library.','.$functionName;
		
		// Check if library as 4 elements, the last one is the function name
		$libSplit = explode ( ',', $library );
		if (count ( $libSplit ) == 4)
			$functionName = $libSplit [3];
		
		if ($functionName == '')
			throw new \Exception ( 'The function name must be supplied in attribute function or as the 4th param in library ' );
			
		$paramValues = $instManager->getParams( $instructionXmlNode, true ); //Get the params defined in params attribute
		
		if (trim ( $library ) != '') {
			// Call a function in a different application,process
			$libParts = explode ( ',', $library );
			if (count ( $libParts ) < 3) {
				throw new \Exception ( 'ERROR: library name '.$library.' must be appname,processname,nodename' );
			}
			
			$libAppName = trim ( $libParts [0] );
			$libProcessName = trim ( $libParts [1] );
			$libNodeName = trim ( $libParts [2] );
			// kuink_mydebug('CALL', $libAppName.'::'.$libProcessName.'::'.$libNodeName);
			
			$callAppName = ($libAppName == 'this') ? $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::APPLICATION] : $libAppName;
			$callProcessName = ($libProcessName == 'this') ? $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::PROCESS] : $libProcessName;
			$callNodeName = $libNodeName;
			
			// kuink_mydebug(__CLASS__, __FUNCTION__);
			$node = new \Kuink\Core\Node ( $callAppName, $callProcessName, $callNodeName );
			$runtime = new \Kuink\Core\Runtime ( $node, 'lib', $instManager->nodeConfiguration );
			
			$result = $runtime->execute ( $functionName, $paramValues, $instManager->exit );
		} else
			// Execute the local function
			$result = $instManager->runtime->function_execute ( $instManager->nodeConfiguration, $instManager->runtime->nodeManager->nodeXml, null, $functionName, $instManager->variables, $instManager->exit, $paramValues );
		
		$return = $result ['RETURN'];
		if (isset ( $result ['RETURN'] ))
			unset ( $result ['RETURN'] );
		
		foreach ( $result as $outParamName => $outParamValue ) {
			if (isset ( $paramValues [$outParamName] ))
				$instManager->variables [$paramValues [$outParamName]] = $outParamValue;
			else {
				throw new \Exception ( 'Function call must define a variable to store the output value of param ' . $outParamName );
			}
		}
		
		return $return;
	}

}

?>
