<?php

namespace Kuink\Core;

/**
 * Trace categories
 * @author paulotavares
 *
 */
class TraceCategory {
	const GENERAL = 'GENERAL';
	const CONNECTOR = 'CONNECTOR';
	const INSTRUCTION = 'INSTRUCTION';
}

/**
 * Class to manage traces
 * @author paulotavares
 *
 */
class TraceManager {
	static public function add( $message, $category = TraceCategory::GENERAL, $class='') 
	{
		global $KUINK_TRACE;
		
		$message = ($class == '') ? $message : $class.'::'.$message;
		
		$KUINK_TRACE[] = $category.'::'.$message;
	}
	  
}

?>
