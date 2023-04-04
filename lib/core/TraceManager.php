<?php

namespace Kuink\Core;

/**
 * Trace categories
 * 
 * @author paulotavares
 *        
 */
class TraceCategory {
	const GENERAL = 'GENERAL';
	const CONNECTOR = 'CONNECTOR';
	const INSTRUCTION = 'INSTRUCTION';
	const SQL = 'SQL';
	const ERROR = 'ERROR';
}

/**
 * Class to manage traces
 * 
 * @author paulotavares
 *        
 */
class TraceManager {
	static public function add($message, $category = TraceCategory::GENERAL, $class = '') {
		global $KUINK_TRACE;
		
		if (is_array($message)) {
			$newMessage = '';
			foreach ($message as $key=>$value) {
				$newMessage .= (! (is_object($value) || is_string($value))) ? $key.' => '.$value .'<br/>': '';
			}
		} else
			$newMessage = ($class == '') ? $message : $class . '::' . $message;
		
		$KUINK_TRACE[] = $category . '::' . htmlentities($newMessage);
	}
}

?>
