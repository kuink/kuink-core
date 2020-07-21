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
		$content = $eval->e ( $content, $instManager->variables, false, true, false, true ); // Eval and return a value without ''
		
		$content = str_replace ( '{EOL}', PHP_EOL, $content ); // replace the special end of line
		
		return ( string ) $content;
	}

	static public function explode($instManager, $instructionXmlNode) {
		$trim = ( string ) self::getAttribute ( $instructionXmlNode, 'trim', $instManager->variables, false, '' );
		$ltrim = ( string ) self::getAttribute ( $instructionXmlNode, 'ltrim', $instManager->variables, false, '' );
		$rtrim = ( string ) self::getAttribute ( $instructionXmlNode, 'rtrim', $instManager->variables, false, '' );
		$params = $instManager->getParams ( $instructionXmlNode );
		$sep = ( string ) $params [0];
		$str = ( string ) $params [1];
		
		$set = explode ( $sep, $str );

		$trimmedSet = array();
		foreach ($set as $value) {
			if ($trim != '')
				$value = trim($value, $trim);
			if ($ltrim != '')
				$value = ltrim($value, $ltrim);
			if ($rtrim != '')
				$value = rtrim($value, $rtrim);
			$trimmedSet[] = $value;
		}
		
		return $trimmedSet;
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

	/**
	 * Returns the n left characters of the sctring
	 * 
	 * @param unknown $instManager        	
	 * @param unknown $instructionXmlNode        	
	 * @return string
	 */
	static public function left($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );		
		$search = ( string ) $params [0];
		$n = ( int ) $params [1];
		$result = substr($search, 0, $n);
		//kuink_mydebug('left', $result);
		return $result;
	}	

	/**
	 * Returns the n right characters of the sctring
	 * 
	 * @param unknown $instManager        	
	 * @param unknown $instructionXmlNode        	
	 * @return string
	 */
	static public function right($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );		
		$search = ( string ) $params [0];
		$n = ( int ) $params [1];
		$result = substr($search, $n*(-1));
		//kuink_mydebug('right', $result);
		return $result;
	}	

	/**
	 * Explodes a string in the last word before the width 
	 * Example 1: explodeWord(10, 'This is a sentence'); -> ['This is a','sentence']
	 * Example 2: explodeWord(9, 'This is a sentence'); -> ['This is','a sentence']
	 */
	static public function explodeWord( $instManager, $instructionXmlNode ) {
		$params = $instManager->getParams( $instructionXmlNode );
		$marker = '_§§_';
		$width = (string)$params[0];
		$str = (string)$params[1];
		$wrapped = wordwrap($str, $width, $marker, false);
		$lines = explode($marker, $wrapped);		
	
		return $lines;
	}


	/**
	 * Trims a string (removes white spaces from left and right)
	 */
	static public function trim( $instManager, $instructionXmlNode ) {
		$content = $instManager->executeInnerInstruction( $instructionXmlNode );
		return trim($content);
	}


}

?>
		