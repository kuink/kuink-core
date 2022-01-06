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

	/*
	 * This function will look for any child elements given the $elementName 
	 * 
	 * @param    string  $elementName The element name to search the attribute
	 * @param    bool		 $mandatory The attribute is mandatory
	 * @param    object  $xml The root xmlDefinition
 	 * @return   string		the inner element xml
	 */
	static function getInnerElements($elementName, $mandatory=false, $xml=null) {
		$innerElementXmlCollection = $xml->xpath ( './'.$elementName);
		
		if (!isset($innerElementXmlCollection) && $mandatory)
			throw new \Exception ( ': Required xml element &lt;' . $elementName . '/&gt; not found.' );		

		return $innerElementXmlCollection;
	}	

	/*
	 * This function will look for any child elements given the $elementName 
	 * 
	 * @param    string  $elementName The element name to search the attribute
	 * @param    bool		 $mandatory The attribute is mandatory
	 * @param    object  $xml The root xmlDefinition
 	 * @return   string		the inner element xml
	 */
	static function getInnerElement($elementName, $mandatory=false, $xml=null) {
		$innerElementXmlCollection = self::getInnerElements($elementName, $mandatory, $xml);
		$innerElementXml = isset($innerElementXmlCollection[0]) ? $innerElementXmlCollection[0] : null;

		return $innerElementXml;
	}	

	abstract static public function execute($instManager, $instructionXmlNode);
}

?>
