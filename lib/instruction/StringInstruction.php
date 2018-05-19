<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class StringInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$parse = ( string ) self::getAttribute ( $instructionXmlNode, 'parse', $instManager->variables, false, 'false' );
		
		if ($parse == 'true')
			return self::parse ( $instManager, $instructionXmlNode );
		else {
			$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
			return ( string ) $content;
		}
	}
	static public function parse($instManager, $instructionXmlNode) {
		$content = ( string ) $instManager->executeInnerInstruction ( $instructionXmlNode );
		$eval = new \Kuink\Core\EvalExpr ();
		$content = $eval->e ( $content, $instManager->variables, false, true, false ); // Eval and return a value without ''
		
		$content = str_replace ( '{EOL}', PHP_EOL, $content ); // replace the special end of line
		
		return ( string ) $content;
	}
	static public function explode($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$sep = ( string ) $params [0];
		$str = ( string ) $params [1];
		
		$set = explode ( $sep, $str );
		
		return $set;
	}
	static public function implode($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$sep = ( string ) $params [0];
		$arr = $params [1];
		
		$str = implode ( $sep, $arr );
		// print_object($str);
		
		return $str;
	}
	static public function replace($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$search = ( string ) $params [0];
		$replace = ( string ) $params [1];
		$subject = ( string ) $params [2];
		
		$result = str_replace ( $search, $replace, $subject );
		//var_dump ( $search );
		//var_dump ( $replace );
		//var_dump ( $subject );
		return $result;
	}
	static public function stripslashes($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$search = ( string ) $params [0];
		
		$result = stripslashes ( $search );
		return $result;
	}
	static public function concatenate($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$finalString = '';
		
		foreach ( $params as $param )
			$finalString .= ( string ) $param;
		
		return $finalString;
	}
	
	/**
	 * Returns the first word in params
	 * 
	 * @param unknown $instManager        	
	 * @param unknown $instructionXmlNode        	
	 * @return string
	 */
	static public function firstWord($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$finalString = '';
		
		foreach ( $params as $param )
			$finalString .= ( string ) $param;
		
		$finalString = trim ( $finalString );
		
		$parts = explode ( ' ', $finalString );
		
		$firstWord = ( string ) $parts [0];
		
		return $firstWord;
	}
	
	/**
	 * Returns the last word in params
	 * 
	 * @param unknown $instManager        	
	 * @param unknown $instructionXmlNode        	
	 * @return string
	 */
	static public function lastWord($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$finalString = '';
		
		foreach ( $params as $param )
			$finalString .= ( string ) $param;
		
		$finalString = trim ( $finalString );
		
		$parts = explode ( ' ', $finalString );
		
		$firstWord = ( string ) $parts [count ( $parts ) - 1];
		
		return $firstWord;
	}
}

?>
		