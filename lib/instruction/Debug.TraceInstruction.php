<?php

namespace Kuink\Core\Instruction;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class TraceInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Handles String Types
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		global $KUINK_MANUAL_TRACE;
		// $content= $instruction_xmlnode[0];
		$label = ( string ) self::getAttribute ( $instructionXmlNode, 'label', $instManager->variables, false, '' );
		$content = (string) $instManager->executeInnerInstruction ( $instructionXmlNode );

		$msg = '';
		if ($label != '')
			// if ($content == $instruction_xmlnode[0])
			if ($content == '')
				$msg = '==== ' . $label . ' / ====<br/>';
			else
				$msg = '==== ' . $label . ' ====<br/>';
			
			// $msg .= ($content == $instruction_xmlnode[0]) ? '' : ' '.var_export($content,true);
		$msg .= var_export ( $content, true );
		
		// if ($label != '' && $content != $instruction_xmlnode[0])
		if ($label != '' && $content != '')
			$msg .= '<br/>==== / ' . $label . ' ====';
		
		$KUINK_MANUAL_TRACE [] = $msg;
		
		return null;
	}
}

?>
