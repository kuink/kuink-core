
<?php
/*
 * Smarty plugin
* -------------------------------------------------------------
* File:     block.translate.php
* Type:     block
* Name:     translate
* Purpose:  translate a block of text
* -------------------------------------------------------------
*/
function smarty_block_translate($params, $content, Smarty_Internal_Template $template, &$repeat)
{
	global $KUINK_APPLICATION;
	
	if (is_null($content)) {
		return;
	}
	// only output on the closing tag
	if(!$repeat){
		if (isset($content)) {
			$translation = $content;
			$application = isset( $params['app'] ) ? (string) $params['app'] : '' ;

			//if app param='' then get the current application
			if ($application == '')
				$translation = \Kuink\Core\Language::getString( $content, $KUINK_APPLICATION->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]  );
			else			
				$translation = \Kuink\Core\Language::getString( $content, $application );
						
			return $translation;
		}
	}
}
?>