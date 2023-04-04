<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class SetToXmlInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Converts a list to a set
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );

		$set = ( array ) $content;
	
		$xml = self::array2xml ( $set, 'set' );
		return $xml;
	}

	static private function array2xml($array, $form) {
		$xmls = "<$form>";
		foreach ( $array as $key => $value ) {
			if (is_object ( $value ))
				$value = ( array ) $value;
			if (is_array ( $value ))
				$xmls .= self::array2xml ( $value, $key );
			else
				$xmls .= "<$key><![CDATA[$value]]></$key>";
		}
		$xmls .= "</$form>";
		return $xmls;
	}
}

?>
