<?php

namespace Kuink\Core\Instruction;

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
		$unique = (string) self::getAttribute ( $instructionXmlNode, 'unique', $instManager->variables, false, 'false');		
		$separator = (string) self::getAttribute ( $instructionXmlNode, 'separator', $instManager->variables, false, ',');		
		$element = $params [0];
		$list = (string) $params [1];
		$arr = ($list != '') ? explode($separator, $list) : array();
		$new = ($element != '') ? explode($separator, $element) : array();

		foreach($new as $newListItem) {
			if (($unique=='true' && !in_array($newListItem, $arr)) || $unique=='false')
				$arr[] = $newListItem;
		}

		$newList = implode($separator, $arr);

		return (string)$newList;
	}

	//Removes an element from a list
	static public function reverse($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$list = (string) $params [0];
		$separator = (string) self::getAttribute ( $instructionXmlNode, 'separator', $instManager->variables, false, ',');		

		$arr = ($list != '') ? explode($separator, $list) : array();
		$arr = array_reverse($arr);

		$newList = implode($separator, $arr);
		return $newList;
	}	

	//Removes an element from a list
	static public function remove($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode );
		$element = $params [0];
		$list = (string) $params [1];
		$separator = (string) self::getAttribute ( $instructionXmlNode, 'separator', $instManager->variables, false, ',');				

		$arr = ($list != '') ? explode($separator, $list) : array();
		$arrDiff = array_diff($arr, array($element));
		$newList = implode($separator, $arrDiff);
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
