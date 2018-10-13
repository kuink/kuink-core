<?php

namespace Kuink\Core\Instruction;

use Kuink\Core\ProcessOrchestrator;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class VarInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$varname = self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true );
		$clear = self::getAttribute ( $instructionXmlNode, 'clear', $instManager->variables, false, 'false' );
		$sum = self::getAttribute ( $instructionXmlNode, 'sum', $instManager->variables, false, 0 );
		$dump = self::getAttribute ( $instructionXmlNode, 'dump', $instManager->variables, false, 'false' );
		$key = self::getAttribute ( $instructionXmlNode, 'key', $instManager->variables, false );
		$keyIsSet = isset ( $instructionXmlNode ['key'] ); // We must know if the key is set or not besides its value
		$setValue = isset ( $instructionXmlNode ['value'] ) ? $instructionXmlNode ['value'] : '';
		$set = (($instructionXmlNode->count () > 0) || ($instructionXmlNode [0] != ''));
		$session = self::getAttribute ( $instructionXmlNode, 'session', $instManager->variables, false, 'false' ); // Session variable
		$process = self::getAttribute ( $instructionXmlNode, 'process', $instManager->variables, false, 'false' ); // Process Variable
		                                                                                                        
		// Clear the variable
		if ($clear == 'true') {
			if ($session == 'true') {
				ProcessOrchestrator::unsetSessionVariable ( $varname, $key );
			} else if ($process == 'true') {
				ProcessOrchestrator::unsetProcessVariable ( $varname, $key );
			} else { // local variable
				if ($key != '') {
					unset ( $instManager->variables [$varname] [$key] );
				} else
					unset ( $instManager->variables [$varname] );
			}
		}
		
		// Get the current variable value
		$value = null;
		if ($session == 'true') {
			$value = ProcessOrchestrator::getSessionVariable ( $varname, $key );
		} else if ($process == 'true') {
			$value = ProcessOrchestrator::getProcessVariable ( $varname, $key );
		} else { // local variable
			$variable = isset ( $instManager->variables [$varname] ) ? $instManager->variables [$varname] : null;
			if (gettype ( $variable ) == 'array') {
				if (count ( $variable ) == 1) {
					reset ( $variable );
					$aux = current ( $variable );
					if (gettype ( $aux ) == 'object')
						$variable = ( array ) $aux;
				}
			} else
				$variable = ( array ) $variable;
			
			$value = '';
			switch ($key) {
				case '' : $value = isset($instManager->variables [$varname]) ? $instManager->variables [$varname] : ''; break;
				case '__first' : $value=array_values($instManager->variables[$varname])[0]; break;
				case '__length' :$value=count($instManager->$variables[$varname]);break;			
				default :
				$value = $instManager->variables [$varname] [$key];
			}
		}
		
		if ($set) {
			$value = ( string ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		} else if ($setValue != '') {
			// Parse the value!!
			$eval = new \Kuink\Core\EvalExpr ();
			$value = $eval->e ( $setValue, $instManager->variables, FALSE ); // NOT BOOLEAN
		}
		
		// Sum the value
		if ($sum != 0) {
			$value = ( int ) $value + $sum;
		}
		
		// Cleanup unncessary spaces
		$value = (! is_array ( $value ) && (! is_object ( $value ))) ? trim ( $value ) : $value;
		
		// Dumping variable
		if ($dump == 'true') {
			self::dumpVariable ( $varname, $value );
		}
		
		
		//Only set the value if this is a set...
		if ($set || $setValue != '' || $sum <> 0) {		
			// Setting the value in the variable
			if ($session == 'true') {
				ProcessOrchestrator::setSessionVariable ( $varname, $key, $value );
			} else if ($process == 'true') {
				ProcessOrchestrator::setProcessVariable ( $varname, $key, $value );
			} else { // local variable
				if ($keyIsSet && $key != '') {
					$var = $instManager->variables [$varname];
					$var [$key] = (is_array ( $value )) ? $value : ( string ) $value;
					$instManager->variables [$varname] = $var;
				} else if ($keyIsSet && $key == '') {
					// Add an array entry
					$var = $instManager->variables [$varname];
					$var [] = (is_array ( $value )) ? $value : ( string ) $value;
					$instManager->variables [$varname] = $var;
				} else
					$instManager->variables [$varname] = $value;
			}
		}
		
		// Allways return the variable value
		return $value;
	}
}

?>
