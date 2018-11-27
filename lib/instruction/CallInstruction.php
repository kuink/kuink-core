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
		$functionPArams = self::getAttribute ( $instructionXmlNode, 'params', $instManager->variables, false ); //$this->get_inst_attr ( $instructionXmlNode, 'params', $instManager->variables, false );
		
		$KUINK_TRACE[] = 'Call: '.$library.','.$functionName;
		
		// Check if library as 4 elements, the last one is the function name
		$libSplit = explode ( ',', $library );
		if (count ( $libSplit ) == 4)
			$functionName = $libSplit [3];
		
		if ($functionName == '')
			throw new \Exception ( 'The function name must be supplied in attribute function or as the 4th param in library ' );
			
			// kuink_mydebug('Function', $functionName);
			// kuink_mydebug('Library', $library);
			
		// Get all the params
		$paramsxml = $instructionXmlNode->xpath ( './Param' );
		
		// var_dump($functionName);
		// var_dump( $paramsxml );
		// var_dump( $instructionXmlNode );
		
		$paramValues = ($functionPArams == '') ? array () : $instManager->variables [$functionPArams];
		$paramVars = array ();
		
		$actionXmlNode = null;
		$actionname = '';
		foreach ( $paramsxml as $param ) {
			
			$paramname = ( string ) $param ['name'];
			$param_var = isset ( $param ['var'] ) ? ( string ) $param ['var'] : null;
			// var_dump($param);
			$paramValue = null;
			if ($param_var != null) {
				$paramValue = $instManager->variables [$param_var];
			} else if ($param->count () > 0) {
				$newinstruction_xmlnode = $param->children ();
				$value = $value = $instManager->executeInnerInstruction( $param );
				$paramValue = (is_array ( $value ) || ($value == null)) ? $value : ( string ) $value;
			} else
				$paramValue = ( string ) $param [0];
			
			$paramValues [$paramname] = $paramValue;
			$paramVars [$paramname] = $param_var;
		}
		// Adding params if variable is defined
		$paramsvar = isset ( $instructionXmlNode ['params'] ) ? ( string ) $instructionXmlNode ['params'] : '';
		if ($paramsvar != '') {
			$var = $instManager->variables [$paramsvar];
			foreach ( $var as $key => $value )
				$paramValues ["$key"] = $value;
		}
		// var_dump($functionName);
		// var_dump( $paramValues );
		
		if (trim ( $library ) != '') {
			// Call a function in a different application,process
			$lib_parts = explode ( ',', $library );
			if (count ( $lib_parts ) < 3) {
				throw new \Exception ( 'ERROR: library name must be appname,processname,nodename' );
			}
			
			$libAppName = trim ( $lib_parts [0] );
			$libProcessName = trim ( $lib_parts [1] );
			$libNodeName = trim ( $lib_parts [2] );
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
			if (isset ( $paramVars [$outParamName] ))
				$instManager->variables [$paramVars [$outParamName]] = $outParamValue;
			else
				throw new \Exception ( 'Function call must define a variable to store the output value of param ' . $outParamName );
		}
		
		return $return;
	}

}

?>
