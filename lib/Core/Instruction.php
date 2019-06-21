<?php

namespace Kuink\Core;

/**
 * Base class for all instructions
 *
 * @author paulo.tavares
 */
abstract class Instruction {
	// var $runtime;
	
	// function __construct( $runtime ) {
	// $this->runtime = $runtime;
	// }
	
	/**
	 * Gets the attribute value of a given instruction
	 * 
	 * @param xml $instruction        	
	 * @param string $attrName        	
	 * @param array $variables        	
	 * @param string $mandatory        	
	 * @param string $default        	
	 * @throws \Exception
	 * @return string
	 */
	static function getAttribute($instruction, $attrName, $variables, $mandatory = 'false', $default = '') {
		if (! $mandatory && ! isset ( $instruction [$attrName] ))
			return $default;
		
		if ($mandatory && ! isset ( $instruction [$attrName] )) {
			$instName = $instruction->getname ();
			throw new \Exception ( 'Instruction "' . $instName . '" needs attribute "' . $attrName . '" which was not supplied.' );
		}
		$attrValue = ( string ) $instruction [$attrName];
		$type = isset($attrValue [0]) ? $attrValue [0] : '';
		$var_name = substr ( $attrValue, 1, strlen ( $attrValue ) - 1 );
		
		if ($type == '$' || $type == '#' || $type == '@') {
			$eval = new \Kuink\Core\EvalExpr ();
			$value = $eval->e ( $attrValue, $variables, FALSE, TRUE, FALSE ); // Eval and return a value without ''
		} else
			$value = $attrValue;
		return ($value == '') ? $default : trim($value);
	}

	static function getAttributeRaw($instruction, $attrName, $variables, $mandatory = 'false', $default = '') {
		if (! $mandatory && ! isset ( $instruction [$attrName] ))
			return $default;
		
		if ($mandatory && ! isset ( $instruction [$attrName] )) {
			$instName = $instruction->getname ();
			throw new \Exception ( 'Instruction "' . $instName . '" needs attribute "' . $attrName . '" which was not supplied.' );
		}
		$attrValue = ( string ) $instruction [$attrName];
		
		return $attrValue;
	}

	static function dumpVariable($var, $value) {
		global $KUINK_MANUAL_TRACE;
		$msg = '<xmp class="prettyprint linenums">' . $var . '::';
		$msg .= var_export ( $value, true );
		$msg .= '</xmp>';
		$KUINK_MANUAL_TRACE [] = $msg;
	}

	abstract static public function execute($instManager, $instructionXmlNode);
}

?>
