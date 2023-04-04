<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class XmlToSetInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a xml to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$content = (string)$instManager->executeInnerInstruction ( $instructionXmlNode );
	
		$set = self::xml2array ( $content );
		return $set;
	}

	// Coverte um xml em array
	static private function xml2array($formdata, $prefix = null) {
		$arraytoreturn = array ();
		
		if ($formdata) {
			$xml = simplexml_load_string ( $formdata );
			
			$arraytoreturn = self::xml2array_parse ( $xml, $prefix );
		}
		
		return $arraytoreturn;
	}

	static private function xml2array_parse($xml, $prefix) {
		if (! $xml) {
			$newArray = array ();
			return $newArray;
		}
		
		foreach ( $xml->children () as $parent => $child ) {
			$return ["{$prefix}{$parent}"] = self::xml2array_parse ( $child, '' ) ? self::xml2array_parse ( $child, '' ) : "$child";
		}
		return $return;
	}

}

?>
