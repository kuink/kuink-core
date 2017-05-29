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
namespace Kuink\UI\Formatter;

class Pallete {
	var $light;
	var $medium;
	var $dark;
	function __construct($pallete) {
		switch ($pallete) {
			case 'blue' :
				$this->light = '#99ccff';
				$this->medium = '#6699cc';
				$this->dark = '#003366';
				break;
			case 'green' :
				$this->light = '#c0f92a';
				$this->medium = '#339900';
				$this->dark = '#015a01';
				break;
			case 'yellow' :
				$this->light = '#ffff00';
				$this->medium = '#fdca01';
				$this->dark = '#986601';
				break;
			case 'red' :
				$this->light = '#fd3301';
				$this->medium = '#d40000';
				$this->dark = '#980101';
				break;
			default :
				$this->light = '#99ccff';
				$this->medium = '#6699cc';
				$this->dark = '#003366';
				break;
		}
	}
}
class Badge extends Formatter {
	function format($value, $params = null) {
		$min = ( int ) $this->getParam ( $params, 'min', true, 0 );
		$max = ( int ) $this->getParam ( $params, 'max', true, 100 );
		$field = $this->getParam ( $params, 'field', false, '' );
		
		$variables ['value'] = $value;
		$variables ['max'] = $max;
		$variables ['field'] = $field;
		
		// var_dump( $params );
		foreach ( $params as $condition => $style ) {
			$condition = trim ( htmlspecialchars_decode ( $condition, ENT_QUOTES ) );
			if ($condition [0] != '$' and $condition != 1)
				continue;
			$eval = new \Kuink\Core\EvalExpr ();
			// Sometimes we want to pass
			$result = ($condition == 1) ? 1 : $eval->e ( $condition, $variables, TRUE );
			// print($condition.'::'.$result);
			if ($result) {
				$palleteName = $style;
				break;
			}
		}
		
		$decimals = ( int ) $this->getParam ( $params, 'decimals', false, 0 );
		$perc = ($value != 0) ? ( float ) (( int ) $value / ($max - $min)) * 100.0 : 0;
		$showValue = ( string ) $this->getParam ( $params, 'showvalue', false, 'true' );
		
		$displayValue = '&nbsp;';
		if ($showValue == 'true')
			$displayValue = (is_numeric ( $value )) ? number_format ( ( float ) $value, $decimals, ',', '.' ) : $value;
		else if ($showValue == 'percentage')
			$displayValue = number_format ( ( float ) $perc, $decimals, ',', '.' ) . '%';
			
			// $displayValue = $value;
		$formatter = '<span class="badge badge-' . $palleteName . '">' . $displayValue . '</span>';
		return $formatter;
	}
	
	/**
	 * Formats the value directly from the conditions
	 *
	 * @param type $value        	
	 * @param type $params        	
	 * @return string
	 */
	function direct($value, $params = null) {
		$variables ['value'] = $value;
		
		foreach ( $params as $condition => $style ) {
			$condition = trim ( htmlspecialchars_decode ( $condition, ENT_QUOTES ) );
			if ($condition [0] != '$')
				continue;
			$eval = new \Kuink\Core\EvalExpr ();
			$result = $eval->e ( $condition, $variables, TRUE );
			// print($condition.'::'.$result);
			if ($result) {
				$palleteName = $style;
				break;
			}
		}
		
		$formatter = '<span class="badge badge-' . $palleteName . '">' . $value . '</span>';
		return $formatter;
	}
}

?>