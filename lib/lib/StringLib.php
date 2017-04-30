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
class StringLib {
	function StringLib($nodeconfiguration, $msg_manager) {
		return;
	}
	function contains($params) {
		$original = ( string ) $params [0];
		$contains = ( string ) $params [1];
		if (strpos ( $original, $contains ) !== false) {
			return 1;
		}
		return 0;
	}
	function startsWith($params) {
		$original = ( string ) $params [0];
		$contains = ( string ) $params [1];
		// var_dump((strpos($original, $contains)));
		if (strpos ( $original, $contains ) === 0) {
			return 1;
		}
		return 0;
	}
	function Concatenate($params) {
		$final_string = '';
		
		foreach ( $params as $param )
			$final_string .= ( string ) $param;
		
		return $final_string;
	}
	function Replace($params) {
		$search = ( string ) $params [0];
		$replace = ( string ) $params [1];
		$string = ( string ) $params [2];
		
		return str_replace ( $search, $replace, $string );
	}
	function implode($params) {
		$sep = ( string ) $params [0];
		$arr = $params [1];
		$final_string = implode ( $sep, $arr );
		
		return $final_string;
	}
	function explode($params) {
		$sep = ( string ) $params [0];
		$str = ( string ) $params [1];
		$set = explode ( $sep, $str );
		
		return $set;
	}
	
	/*
	 * @author: Joao Patricio
	 * May be working. Not tested
	 *
	 * function SubStr($params){
	 *
	 * $content = (string)$params[0];
	 * $start = (string)$params[1];
	 * $length = (!empty($params[2])) ? (string)$params[2] : false;
	 *
	 * if ($length)
	 * return substr($content,$start,$length);
	 *
	 * return substr($content,$start);
	 *
	 * }
	 */
	function pad($params) {
		$number = ( int ) $params ['number'];
		$value = $params ['value'];
		$direction = (isset ( $params ['direction'] )) ? $params ['direction'] : 'lr';
		$direction = ($direction == 'lr') ? STR_PAD_RIGHT : STR_PAD_LEFT;
		$placeholder = (isset ( $params ['placeholder'] )) ? $params ['placeholder'] : '0';
		return str_pad ( $value, $number, $placeholder, $direction );
	}
	function substr($params) {
		$str = $params ['str'];
		$start = ( string ) $params ['start'];
		$length = ( string ) $params ['length'];
		
		// var_dump($str);
		// var_dump($start);
		// var_dump($length);
		
		return (empty ( $params ['length'] )) ? substr ( $str, $start ) : substr ( $str, $start, $length );
	}
	function nl2br($params) {
		return nl2br ( $params [0] );
	}
	function search($params) {
		$str = (isset ( $params [0] )) ? ( string ) $params [0] : null;
		$searchWord = (isset ( $params [1] )) ? ( string ) $params [1] : null;
		
		if (strpos ( $str, $searchWord ) !== false) {
			return 1;
		}
		
		return 0;
	}
	
	// search if a set of words are contained in a string
	function splitSearch($params) {
		$str = (isset ( $params [0] )) ? ( string ) $params [0] : null;
		$searchStr = (isset ( $params [1] )) ? ( string ) $params [1] : null;
		$caseSensitive = (isset ( $params [2] )) ? ( string ) $params [2] : 'false';
		
		// split and foreach with search
		$words = explode ( " ", $str );
		$searchWords = explode ( " ", $searchStr );
		foreach ( $searchWords as $searchWord ) {
			foreach ( $words as $word ) {
				if ($caseSensitive == 'true') {
					if (strpos ( $word, $searchWord ) !== false)
						return 1;
				} else {
					if (strpos ( strtolower ( $word ), strtolower ( $searchWord ) ) !== false)
						return 1;
				}
			}
		}
		
		return 0;
	}
	function regexp($params) {
		$regexp = (isset ( $params [0] )) ? ( string ) $params [0] : null;
		$str = (isset ( $params [1] )) ? ( string ) $params [1] : null;
		
		$match = preg_match_all ( $regexp, $str, $out );
		
		return isset ( $out [0] ) ? $out [0] : null;
	}
	function removeWhiteSpaces($params) {
		if (isset ( $params [0] ))
			return str_replace ( ' ', '', $params [0] );
		return;
	}
	function toUpper($params) {
		if (isset ( $params [0] ))
			return strtoupper ( $params [0] );
		return;
	}
}

?>
