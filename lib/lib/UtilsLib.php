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
class UtilsLib {
	var $nodeconfiguration;
	var $msg_manager;
	function __construct($nodeconfiguration=null, $msg_manager=null) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function Guid($params) {
		if (function_exists ( 'com_create_guid' )) {
			return com_create_guid ();
		} else {
			mt_srand ( ( double ) microtime () * 10000 ); // optional for php 4.2.0 and up.
			$charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) );
			$hyphen = chr ( 45 ); // "-"
			$uuid = chr ( 123 ) . // "{"
substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 ) . chr ( 125 ); // "}"
			return $uuid;
		}
	}
	function GuidClean($params=null) {
		if (function_exists ( 'com_create_guid' )) {
			return com_create_guid ();
		} else {
			mt_srand ( ( double ) microtime () * 10000 ); // optional for php 4.2.0 and up.
			$charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) );
			$uuid = substr ( $charid, 0, 8 ) . substr ( $charid, 8, 4 ) . substr ( $charid, 12, 4 ) . substr ( $charid, 16, 4 ) . substr ( $charid, 20, 12 );
			return $uuid;
		}
	}
	function Uuid($params=null) {
		return exec ( 'cat /proc/sys/kernel/random/uuid' );
	}
	function ActionUrl($params) {
		if (count ( $params ) != 1)
			throw new Exception ( 'ActionUrl must have one parameter that specifies the action name. ' );
		
		$action_name = ( string ) $params [0];
		$baseurl = $this->nodeconfiguration ['baseurl'];
		$action_url = \Kuink\Core\Tools::setUrlParams ( $baseurl, array (
				'action' => $action_name 
		) );
		$app_name = $this->nodeconfiguration ['customappname'];
		return $action_url;
	}
	function MoodleCourseUrl($params) {
		global $KUINK_CFG;
		if (count ( $params ) != 1)
			throw new Exception ( 'MoodleCourseUrl must have one parameter that specifies the course id. ' );
		$courseId = ( string ) $params [0];
		$url = $KUINK_CFG->wwwRoot . "/course/view.php?id=" . $courseId;
		return $url;
	}
	function ActionUrlLink($params) {
		if (count ( $params ) != 1)
			throw new Exception ( 'ActionUrl must have one parameter that specifies the action name. ' );
		
		$action_name = ( string ) $params [0];
		$baseurl = $this->nodeconfiguration ['baseurl'];
		$url = new moodle_url ( $baseurl, array (
				'action' => $action_name 
		) );
		$action_url = $url->out ( false );
		$app_name = $this->nodeconfiguration ['customappname'];
		return '<a href="' . $action_url . '">' . kuink_get_string ( $action_name, $app_name ) . '</a>';
	}
	function EventUrlLink($params) {
		if (count ( $params ) != 1)
			throw new Exception ( 'EventUrl must have one parameter that specifies the event name. ' );
		
		$event_name = ( string ) $params [0];
		$baseurl = $this->nodeconfiguration ['baseurl'];
		$url = new moodle_url ( $baseurl, array (
				'event' => $event_name,
				'action' => '',
				'actionvalue' => '' 
		) );
		$event_url = $url->out ( false );
		$app_name = $this->nodeconfiguration ['customappname'];
		return '<a href="' . $event_url . '">' . kuink_get_string ( $event_name, $app_name ) . '</a>';
	}
	function setToXml($params) {
		if (count ( $params ) != 1)
			throw new Exception ( 'setToXml must have one parameter that specifies the set to convert. ' );
		
		$set = ( array ) $params [0];
		
		$xml = $this->neon_array2xml ( $set, 'set' );
		return $xml;
	}
	function xmlToSet($params) {
		if (count ( $params ) != 1)
			throw new Exception ( 'xmlToSet must have one parameter that is the xml string. ' );
		
		$xml = ( string ) $params [0];
		
		// neon_mydebugxml('xml', $xml);
		
		$set = $this->neon_xml2array ( $xml );
		return $set;
	}
	function arrayLength($params) {
		$arr = $params [0];
		return count ( $arr );
	}
	
	// PRIVATE FUNCTIONS
	
	// Converte um array em xml
	private function neon_array2xml($array, $form) {
		$xmls = "<$form>";
		foreach ( $array as $key => $value ) {
			// kuink_mydebug($key, $value);
			if (is_object ( $value ))
				$value = ( array ) $value;
			if (is_array ( $value ))
				$xmls .= $this->neon_array2xml ( $value, $key );
			else
				$xmls .= "<$key><![CDATA[$value]]></$key>";
			// $xmls .= "<$key>$value</$key>";
		}
		
		$xmls .= "</$form>";
		return $xmls;
	}
	
	// Coverte um xml em array
	private function neon_xml2array($formdata, $prefix = null) {
		$arraytoreturn = array ();
		
		if ($formdata) {
			$xml = simplexml_load_string ( $formdata );
			
			$arraytoreturn = $this->neon_xml2array_parse ( $xml, $prefix );
		}
		
		return $arraytoreturn;
	}
	private function neon_xml2array_parse($xml, $prefix) {
		if (! $xml) {
			$newArray = array ();
			return $newArray;
		}
		
		foreach ( $xml->children () as $parent => $child ) {
			$return ["{$prefix}{$parent}"] = $this->neon_xml2array_parse ( $child, '' ) ? $this->neon_xml2array_parse ( $child, '' ) : "$child";
		}
		return $return;
	}
	
	/**
	 * Split a string into an array
	 * 
	 * @param
	 *        	0 string to split
	 * @param
	 *        	1 pattern to split. Default: Comma ","
	 * @return Array if success, false otherwise
	 */
	function split($params) {
		$string = $params [0];
		$separator = (isset ( $params [1] )) ? ( string ) $params [1] : ',';
		return explode ( $separator, $string );
	}
	function implode($params) {
		return implode ( $params [1], $params [0] );
	}
	function base64Encode($params) {
		$str = $params [0];
		return base64_encode ( $str );
	}
	function base64Decode($params) {
		$str = $params [0];
		return base64_decode ( $str );
	}
	function jsonEncode($params) {
		$arr = $params [0];
		return json_encode ( $arr );
	}
	function jsonDecode($params) {
		$str = $params [0];
		$associative = $params [1];

		if ( is_null ( $associative ) || $associative == '' )
			return json_decode ( $str );
		else
			return json_decode ( $str, $associative );
	}
	
	/**
	 *
	 * @param
	 *        	0 Element to search
	 * @param
	 *        	1 Array (1 dimension) to perfom search
	 *        	
	 */
	function inArray($params) {
		$needle = $params [0];
		$arr = $params [1];
		return in_array ( $needle, $arr );
	}
	function getServerIp($params) {
		return $_SERVER ['SERVER_ADDR'];
	}
	function filterChanged($params) {
		$arr = $params [0];
		$out = array ();
		
		foreach ( $arr as $key => $value ) {
			if (isset($value ['CHANGED']) && ($value ['CHANGED'] == 1)) {
				$out [$key] = $value;
			}
		}
		
		return $out;
	}
	function filterSelected($params) {
		$arr = $params [0];
		$out = array ();
		
		foreach ( $arr as $key => $value ) {
			if ($value ['SELECTED'] == 1) {
				$out [$key] = $value;
			}
		}
		
		return $out;
	}
	function filterNotNull($params) {
		$arr = $params [0];
		$out = array ();
		
		foreach ( $arr as $key => $value ) {
			if ($value != '' && $key != '_debug_') {
				$out [$key] = $value;
			}
		}
		
		return $out;
	}

	function filterNotEmpty($params) {
		$arr = $params [0];
		$out = array ();
		
		foreach ( $arr as $key => $value ) {
			if ($value !== '' && $key !== '_debug_') {
				$out [$key] = $value;
			}
		}
		
		return $out;
	}


	function getMultiSelectedValues($params) {
		$postdata = $params [0];
		$field = ( string ) $params [1];
		$out = array ();
		
		foreach ( $postdata as $key => $value ) {
			if (! (strpos ( $key, $field . '_' ) === FALSE)) {
				$fieldSplited = explode ( $field . '_', $key );
				$id = $fieldSplited [1];
				$out [$id] = $value;
			}
		}
		
		return $out;
	}
	
	// function pivotTable($arr, $pivotLines, $pivotCols, $pivotData) {
	function pivotTable($params) {
		if (count ( $params ) != 4)
			throw new \Exception ( 'Pivot Table params: array, pivotLines, pivotCols, pivotData ' );
		$arr = $params [0];
		$pivotLines = explode ( ',', $params [1] );
		$pivotCols = explode ( ',', $params [2] );
		$pivotData = explode ( ',', $params [3] );
		
		// $pivotLines = (string)$params[1];
		// $pivotCols = (string)$params[2];
		// $pivotData = (string)$params[3];
		
		$pivotAux = array ();
		$fullIdOrder = array ();
		$lineOrder = 0;
		$uniqueColumns = array ();
		
		foreach ( $arr as $row ) {
			$row = ( array ) $row;
			foreach ( $pivotCols as $col ) {
				$uniqueColumns [$col] [$row [$col]] = $row [$col];
			}
		}
		
		foreach ( $arr as $row ) {
			$row = ( array ) $row;
			$fullId = '';
			$fullIdIndex = null;
			$pivotRow = array ();
			
			foreach ( $pivotLines as $line ) {
				$fullId .= isset($row [$line]) ? $row [$line] : '';
				$pivotRow [$line] = isset($row [$line]) ? $row [$line] : null;
			}
			if (! isset ( $fullIdOrder [$fullId] )) {
				$fullIdOrder [$fullId] = $lineOrder;
				$fullIdIndex = $lineOrder ++;
			} else
				$fullIdIndex = $fullIdOrder [$fullId];
				
				// Initialize all columns with empty value
			foreach ( $uniqueColumns as $ucols )
				foreach ( $ucols as $ucol )
					if (! isset ( $pivotAux [$fullIdIndex] [( string ) $ucol] ))
						foreach ( $pivotData as $data )
							if (count ( $pivotData ) > 1)
								$pivotAux [$fullIdIndex] [( string ) $ucol] ['__infer_' . $data] = '';
							else
								$pivotAux [$fullIdIndex] [( string ) $ucol] = '';
			
			foreach ( $pivotCols as $col ) {
				if (count ( $pivotData ) > 1)
					foreach ( $pivotData as $data )
						$pivotRow [$row [$col]] ['__infer_' . $data] = isset($row [$data]) ? $row [$data] : null;
				else
					$pivotRow [$row [$col]] = isset($row [$pivotData [0]]) ? $row [$pivotData [0]] : null;
			}
			
			$pivotAux [$fullIdIndex] = array_merge ( ( array ) $pivotAux [$fullIdIndex], ( array ) $pivotRow );
		}
		
		$pivot = array ();
		foreach ( $pivotAux as $pivotRow )
			$pivot [] = $pivotRow;
			
			// var_dump($pivot);
		return $pivot;
	}
	function pivotTableToCSV($params) {
		$pivotTable = $params [0];
		$headers = array ();
		$csv = '';
		// Determine headers
		reset ( $pivotTable );
		$firstRow = current ( $pivotTable );
		$count = 0;
		$dataIsArray = false;
		reset ( $firstRow );
		$firstValue = current ( $firstRow );
		foreach ( $firstValue as $key => $value ) {
			$dataIsArray = $dataIsArray || is_array ( $value );
			$count += (is_array ( $value )) ? count ( $value ) : 1;
			
			$count += substr_count ( $value, ',' );
		}
		foreach ( $firstRow as $key => $value ) {
			$csv .= ( string ) $key;
			
			for($i = 1; $i <= $count; $i ++)
				$csv .= ', ';
		}
		$csv .= PHP_EOL;
		
		foreach ( $pivotTable as $key => $value ) {
			
			foreach ( $value as $kKey => $kValue ) {
				$tmpCsv = '';
				if (is_Array ( $kValue ))
					foreach ( $kValue as $kpValue )
						$tmpCsv .= ( string ) $kpValue . ',';
				else {
					$tmpCsv .= ( string ) $kValue . ',';
				}
				$numOfCommas = substr_count ( $tmpCsv, ',' );
				for($i = $numOfCommas + 1; $i <= $count; $i ++)
					$tmpCsv .= ', ';
					// var_dump($tmpCsv);
				$csv .= $tmpCsv;
			}
			$csv .= PHP_EOL;
		}
		
		return $csv;
	}
	function getFirst($params) {
		$array = (! empty ( $params [0] )) ? $this->object_to_array ( $params [0] ) : false;
		return reset ( $array );
	}
	protected function object_to_array($obj) {
		$arrObj = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
		foreach ( $arrObj as $key => $val ) {
			$val = (is_array ( $val ) || is_object ( $val )) ? $this->object_to_array ( $val ) : $val;
			$arr [$key] = $val;
		}
		return $arr;
	}
}

?>
