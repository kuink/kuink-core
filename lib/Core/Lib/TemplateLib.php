<?php
namespace Kuink\Core\Lib;

use Kuink\Core\NodeManager;
use Kuink\UI\Layout\Layout;
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
class TemplateLib {
	var $nodeconfiguration;
	var $msg_manager;
	function __construct($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function GetTemplateHtml($params) {
		global $KUINK_BRIDGE_CFG;
		$templateName = $params [0];
		$data = $params [1];
		//echo "<pre>";
		//var_dump($templateName);
		//var_dump($data);
		//echo "</pre>";
		$d = \Kuink\UI\Layout\Adapter\Smarty::getTemplate ( $templateName, $data, $KUINK_BRIDGE_CFG->theme );
		return $d;
	}
	function ExecuteTemplate($params) {
		$application = ( string ) $params [0];
		$process = ( string ) $params [1];
		$templateName = ( string ) $params [2];
		$data = $params [3];
		$src = isset ( $params [4] ) ? ( string ) $params [4] : null;
		/*
		 * echo "<pre>";
		 * var_dump($data);
		 * echo "</pre>";
		 */
		if ($src != null)
			$data = array (
					'data' => $data,
					'source' => $src 
			);
			// print_object($data);
		$layout = Layout::getInstance ();			
		$d = $layout->getApplicationTemplate ( $application, $process, $templateName, $data );
		
		return $d;
	}
	function ExecuteStandardTemplate($params) {
		global $KUINK_APPLICATION;
		
		$name = ( string ) $params [0];
		
		$nameParts = explode ( ',', $name );
		if (!empty($nameParts) && count($nameParts) != 3)
			throw new \Exception ( 'Template: name must be method or appName,processName,template' );
		
		$application = (trim ( $nameParts [0] ) == 'this') ? $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] : trim ( $nameParts [0] );
		$process = (trim ( $nameParts [1] ) == 'this') ? $this->nodeconfiguration [\Kuink\Core\NodeConfKey::PROCESS] : trim ( $nameParts [1] );
		$templateName = trim ( $nameParts [2] );
		
		$lang = isset ( $params [1] ) ? $params [1] : $KUINK_APPLICATION->getLang ();
		$data = $params [2];
		
		// Read the template file
		$nodeManager = new \Kuink\Core\NodeManager ( $application, $process, 'templates', $templateName . '.' . $lang );
		
		// check if the file exists, else try to open in portuguese
		if (! $nodeManager->exists ())
			$nodeManager = new \Kuink\Core\NodeManager ( $application, $process, 'templates', $templateName . '.pt' );
		
		$nodeManager->load ();
		
		$keys = $nodeManager->nodeXml->xpath ( '/Template/*' );
		
		$result = array ();
		
		// Process all the keys
		foreach ( $keys as $key ) {
			$keyName = ( string ) $key->getName ();
			$keyValue = (isset ( $key ['value'] )) ? ( string ) $key ['value'] : ( string ) $key [0];
			$template = (isset ( $key ['template'] )) ? ( string ) $key ['template'] : null;
			
			if (!empty($key->children())) {
				// Handle the child nodes
				$childs = array ();
				foreach ( $key->children () as $child ) {
					$childName = $child->getName ();
					$childValue = (isset ( $child ['value'] )) ? ( string ) $child ['value'] : ( string ) $child [0];
					$eval = new \Kuink\Core\EvalExpr ();
					$childValue = $eval->e ( $childValue, $data, FALSE, TRUE, FALSE ); // Eval and return a value without ''
					
					$childs [$childName] = $childValue;
				}
				$parsedValue = $childs;
			} else {
				// If template isset then get the template from smarty
				if ($template) {
					$layout = Layout::getInstance ();
					$parsedValue = $layout->getApplicationTemplate ( $application, $process, $templateName, $data );
				}
				else {
					$eval = new \Kuink\Core\EvalExpr ();
					$cleanedData = array ();
					foreach ( $data as $key => $value ) {
						$cleanedData [$key] = ($value === null) ? '' : $value;
					}
					
					$parsedValue = $eval->e ( $keyValue, $cleanedData, FALSE, TRUE, FALSE ); // Eval and return a value without ''
				}
			}
			$result [$keyName] = $parsedValue;
		}
		
		return $result;
	}
	function expandTemplate($params) {
		$templateCode = ( string ) $params [0];
		$data = $params [1];
		$layout = Layout::getInstance ();
		$d = $layout->getApplicationTemplate( $templateCode, $data );
		return $d;
	}
}

?>
