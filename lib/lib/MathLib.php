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
class MathLib {
	function __construct($nodeconfiguration, $msg_manager) {
		return;
	}
	function sum($params) {
		$count = count ( $params );
		$sum = 0;
		
		foreach ( $params as $param )
			$sum += ( float ) $param;
		
		return $sum;
	}
	function subtract($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		$sub = ( double ) $params [0] * 2; // The first will be subtracted
		
		foreach ( $params as $param )
			$sub -= ( float ) $param;
		
		return $sub;
	}
	function product($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		
		$total = 1;
		
		foreach ( $params as $param )
			$total *= ( float ) $param;
		
		return $total;
	}
	function intProduct($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		
		$total = 1;
		
		foreach ( $params as $param )
			$total *= ( int ) $param;
		
		return $total;
	}
	function intSum($params) {
		$count = count ( $params );
		$sum = 0;
		
		foreach ( $params as $param )
			$sum += ( int ) $param;
		
		return $sum;
	}
	function intSubtract($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		$sub = ( int ) $params [0] * 2; // The first will be subtracted
		
		foreach ( $params as $param )
			$sub -= ( int ) $param;
		
		return $sub;
	}
	function average($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		$params = (is_array ( $params [0] )) ? $params [0] : $params;
		
		$sum = 0;
		$count = 0;
		foreach ( $params as $param ) {
			if (!empty($param) && (is_numeric($param))) { 
				$sum += ( float ) $param;
				$count ++;
			}
		}
		
		$total = $sum / $count;
		
		return $total;
	}
	
	// Returns the minimum
	function min($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		$params = (is_array ( $params [0] )) ? $params [0] : $params;
		$min = ( float ) $params [0];
		
		foreach ( $params as $param ) {
			if (! empty ( $param )) {
				$current = ( float ) $param;
				$min = ($current < $min) ? $current : $min;
			}
		}
		return $min;
	}
	
	// Returns the maximum
	function max($params) {
		$count = count ( $params );
		if ($count == 0)
			return 0;
		$params = (is_array ( $params [0] )) ? $params [0] : $params;
		$max = ( float ) $params [0];
		
		foreach ( $params as $param ) {
			if (! empty ( $param )) {
				$current = ( float ) $param;
				$max = ($current > $max) ? $current : $max;
			}
		}
		
		return $max;
	}
	function round($params) {
		$value = ( float ) $params [0];
		$precision = (isset ( $params [1] )) ? $params [1] : 0;
		return round ( $value, $precision );
	}
	function random($params) {
		$min = ( int ) $params [0];
		$max = ( int ) $params [1];
		return rand ( $min, $max );
	}
}

?>
