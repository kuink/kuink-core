<?php

namespace Kuink\Core\Instruction\Type;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class ListInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles List Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */

	//A list is a string, so an empty list is an empty string
	static public function execute($instManager, $instructionXmlNode) {
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		return (string) $content;
	}

	//Adds an element to a list
	static public function add($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$element = $params [0];
		$list = (string) $params [1];
		$arr = ($list != '') ? explode(',', $list) : array();
		$arr[] = $element;
		$newList = implode(',', $arr);
		return (string)$newList;
	}

	//Removes an element from a list
	static public function remove($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$element = $params [0];
		$list = (string) $params [1];

		$arr = ($list != '') ? explode(',', $list) : array();
		$arrDiff = array_diff($arr, array($element));
		$newList = implode(',', $arrDiff);
		return $newList;
	}

	//Clears the list content
	static public function clear($instManager, $instructionXmlNode) {
		return '';
	}

	//Builds a set based on a list
	static public function toSet($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$list = (string) $params [0];
		
		$arr = ($list != '') ? explode(',', $list) : array();
		return $arr;
	}	

	//Builds a set based on a list
	static public function fromSet($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$set = $params [0];
		
		$list = implode(',', $set);
		return $list;
	}	

}

?>
