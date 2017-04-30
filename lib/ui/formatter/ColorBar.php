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
class ColorBar extends Formatter {
	function format($value, $params = null) {
		if (! is_numeric ( $value ))
			return $value;
		
		$min = ( int ) $this->getParam ( $params, 'min', true, 0 );
		$max = ( int ) $this->getParam ( $params, 'max', true, 100 );
		$decimals = ( int ) $this->getParam ( $params, 'decimals', false, 0 );
		$perc = ($value != 0) ? ( float ) (( int ) $value / ($max - $min)) * 100.0 : 0;
		$showValue = ( string ) $this->getParam ( $params, 'showvalue', false, 'true' );
		$pallete_name = ( string ) $this->getParam ( $params, 'pallete', false, 'blue' );
		
		$pallete = new Pallete ( $pallete_name );
		
		$display_value = ($showValue == 'true') ? number_format ( ( float ) $value, $decimals, ',', '.' ) : (($showValue == 'percentage') ? number_format ( ( float ) $perc, $decimals, ',', '.' ) . '%' : '&nbsp');
		$formatter = '
    	<div style="background-color: white; border: solid 1px ' . $pallete->medium . ';">
    		<div style="background-color: ' . $pallete->light . '; width: ' . $perc . '%; text-align: center; color: ' . $pallete->dark . '; border: solid 0px;">' . $display_value . '</div>
    	</div>';
		return $formatter;
	}
}

?>