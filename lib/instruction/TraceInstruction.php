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
		$content = $instManager->executeInnerInstruction ( $instructionXmlNode );
		$contentToDisplay = (string) $content;
		if (is_object($content))
			$content = (array) $content;

		if (is_array($content)) {
			$contentToDisplay = '';
			foreach ($content as $entryKey=>$entryValue){
				$contentToDisplay .= $entryKey.' => '.$entryValue.'\n';
			}
			$contentToDisplay = nl2br($contentToDisplay);
		}

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
		
		$KUINK_MANUAL_TRACE [] = '<pre>'.htmlspecialchars($msg).'</pre>';
		
		return null;
	}
}

?>
