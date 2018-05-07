<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
namespace Kuink\Core;

/**
 * Handles all language stuff
 * 
 * @author ptavares
 *        
 */
class Language {
	static public function loadLanguageFiles($appManager, $app_name, $lang) {
		global $KUINK_TRANSLATION;
		// Load language specific string objects
		$appBase = $appManager->getApplicationBase ( $app_name );
		$KUINK_TRANSLATION [$app_name] = self::loadLaguageFile ( $appBase . '/' . $app_name, $lang );
		$KUINK_TRANSLATION ['framework'] = self::loadLaguageFile ( 'framework/framework', $lang );
	}
	
	static private function getTranslation($xPathElement, $xPathAttribute, $identifier, $app_name='framework', $params=null){
		global $KUINK_TRANSLATION;
		global $KUINK_APPLICATION;
		
		$lang_manager = null;
		if (isset($KUINK_TRANSLATION[ $app_name ]))
			$lang_manager = $KUINK_TRANSLATION[ $app_name ];
		
		if (is_null( $lang_manager ))
		{
			$lang_manager = self::loadLaguageFile( $app_name, $KUINK_APPLICATION->getLang() );
	
			if ($lang_manager == null)
				return $identifier;
	
			$GLOBALS['KUINK_TRANSLATION'][ $app_name ] = $lang_manager;
		}
	
		if (!$identifier)
			return '';
	
		$langstring = $lang_manager->xpath($xPathElement.'[@'.$xPathAttribute.'="'. $identifier .'"]/@value');

		if ($langstring == null)
		{
			$string = $lang_manager->xpath($xPathElement.'[@'.$xPathAttribute.'="'. $identifier .'"]');
			$langstring = isset($string[0][0]) ? (string)$string[0][0] : '';
		}
		else
			$langstring = isset($langstring[0]['value']) ? (string)$langstring[0]['value'] : '';
			
		//Expand parameters
		$index = 0;
		if ($langstring != null && $params != null)
			foreach ($params as $param)
			{
				$langstring = str_replace ('{'.$index++.'}', (string)$param, $langstring);
			}
		
		$paramsList = (count($params) > 0) ? ' ('.implode(',', $params).')' : '';
		
		if ($app_name == 'framework')
			$result = ($langstring == null) ? $identifier.$paramsList : (string)$langstring;
		else
			$result = ($langstring == null) ? self::getTranslation($xPathElement, $xPathAttribute, (string)$identifier, 'framework', $params) : (string)$langstring;
		
		return($result);
	}
	static public function getHelpString($identifier, $app_name = 'framework') {
		global $KUINK_TRANSLATION;
		
		$lang_manager = $KUINK_TRANSLATION [$app_name];
		if (! $identifier)
			return '';
			// print($identifier);
			
		// get the identifier in the xml file
		$langstring = $lang_manager->xpath ( '/Lang/HelpStrings/Help[@key="' . $identifier . '"]' );
		
		$helpstring = isset ( $langstring [0] ) ? ( string ) $langstring [0] : '';
		
		if ($app_name == 'framework')
			$result = ($helpstring == '') ? $identifier : ( string ) $helpstring;
		else
			$result = ($helpstring == '') ? self::getHelpString ( ( string ) $identifier, 'framework' ) : ( string ) $helpstring;
			
			// kuink_mydebug($app_name.'.'.$identifier, $result);
			// print($result);
		return ($result);
	}
	static public function getString( $identifier, $appName='framework', $params=null ) {
		return self::getTranslation('/Lang/Strings/String', 'key', $identifier, $appName, $params);
	}
	static public function getExceptionString( $identifier, $appName='framework', $params=null ) {
		return self::getTranslation('/Lang/ExceptionStrings/Exception', 'name', $identifier, $appName, $params);
	}
	static private function loadLaguageFile($app_name, $lang) {
		global $KUINK_TRACE;
		global $KUINK_CFG;
		global $KUINK_APPLICATION;
		$neon_lang_file = null;
		
		$userlang_final = str_replace ( '_utf8', '', $lang );
		// Loading the kuink string language file
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $app_name ) : '';
		$langfilename = $KUINK_CFG->appRoot . 'apps/' . $appBase . '/' . $app_name . '/lang/' . $userlang_final . '.xml';
		
		if (! file_exists ( $langfilename ))
			$langfilename = $KUINK_CFG->appRoot . 'apps/' . $appBase . '/' . $app_name . '/lang/pt.xml';
		
		libxml_use_internal_errors ( true );
		$neon_lang_file = simplexml_load_file ( $langfilename );
		$errors = libxml_get_errors ();
		
		if (! $neon_lang_file) {
			$KUINK_TRACE [] = 'Loading lang file';
			$KUINK_TRACE [] = var_dump ( $errors );
			return '';
		}
		$KUINK_TRACE [] = "Language File lodaded: " . $langfilename;
		
		return $neon_lang_file;
	}
}

?>