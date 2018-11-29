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
		$setValue = isset ( $instructionXmlNode ['value'] ) ? $instructionXmlNode ['value'] : '';
		$set = (($instructionXmlNode->count () > 0) || ($instructionXmlNode [0] != ''));
		$session = self::getAttribute ( $instructionXmlNode, 'session', $instManager->variables, false, 'false' ); // Session variable
		$process = self::getAttribute ( $instructionXmlNode, 'process', $instManager->variables, false, 'false' ); // Process Variable

		$key = self::getAttribute ( $instructionXmlNode, 'key', $instManager->variables, false );
		$keyIsSet = isset ( $instructionXmlNode ['key'] ); // We must know if the key is set or not besides its value
		$keys = explode('->', $key);
		$keys = array_reverse($keys);

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
					$value = isset($instManager->variables [$varname]) ? self::getVarKeyInDepth($instManager, $instManager->variables[$varname], $keys) : ''; //isset($instManager->variables [$varname] [$key]) ? $instManager->variables [$varname] [$key] : null;
			}
		}
		
		if ($set) {
			$value = $instManager->executeInnerInstruction ( $instructionXmlNode, true ); //If there's an inner value directly get it as string
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
			$dumpVarName = ($key != '') ? $varname.'['.$key.']' : $varname;
			self::dumpVariable ( $dumpVarName, $value );
		}
		
		//Only set the value if this is a set...
		if ($set || $setValue != '' || $sum <> 0) {		
			// Setting the value in the variable
			if ($session == 'true') {
				ProcessOrchestrator::setSessionVariable ( $varname, $key, $value );
			} else if ($process == 'true') {
				ProcessOrchestrator::setProcessVariable ( $varname, $key, $value );
			} else { // local variable
				//Get the original variable
				$var = isset($instManager->variables[$varname]) ? $instManager->variables [$varname] : null; 
				if ($key != '')
					$instManager->variables[$varname] = self::setVarKeyInDepth($instManager, $var, $keys, $value);
				else {
					if ($keyIsSet && $key == '')
						$instManager->variables[$varname][] = $value;
					else
						$instManager->variables[$varname] = $value;
				}
			}
		}
		
		// Allways return the variable value
		return $value;
	}

	//Sets a key in an array with depth defined in keys
	static public function setVarKeyInDepth($instManager, $variable, $keys, $value) {
		$key = (string)array_pop($keys);//isset($keys[0]) ? $keys[0] : null;
		$key = trim($key);
		//Expand variable in key if it starts by [$ | # |@]
		$type = $key[0];
		if ($type == '$' || $type == '#' || $type == '@') {
			$eval = new \Kuink\Core\EvalExpr ();
			$key = $eval->e ( $key, $instManager->variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		}		

		if (count($keys) == 0) {
			if ($key == '__new') 
				$variable[] = $value;
			else	
				$variable[$key] = $value;
			return $variable;
		}	else {
			if (($key == '__new') || (!isset($variable[$key]))) {
				$new = array();
				$result = self::setVarKeyInDepth($instManager, $new, $keys, $value);
				if ($key == '__new')
					$variable[] = $result;
				else
					$variable[$key] = $result;
			}
			else	
				$variable[$key] = self::setVarKeyInDepth($instManager, $variable[$key], $keys, $value);
			return $variable;
		}
	}

	//Gets a key in an array with depth defined in keys
	static public function getVarKeyInDepth($instManager, $variable, $keys) {
		$key = (string)array_pop($keys);//isset($keys[0]) ? $keys[0] : null;
		$key = trim($key);
		//Expand variable in key if it starts by [$ | # |@]
		$type = $key[0];
		if ($type == '$' || $type == '#' || $type == '@') {
			$eval = new \Kuink\Core\EvalExpr ();
			$key = $eval->e ( $key, $instManager->variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		}		

		if ((count($keys) == 0) || (!isset($variable[$key])))
			return isset($variable[$key]) ? $variable[$key] : null;
		else
				return self::getVarKeyInDepth($instManager, $variable[$key], $keys);
	}	
}

?>
