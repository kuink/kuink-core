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

use Kuink\Core\Configuration;

class Icon extends Formatter {
	function format($value, $params = null) {
		return $this->small ( $value, $params );
	}
	function simpleIcon($value, $params = null) {
		$icon = ( string ) $this->getParam ( $params, $value, false, 'default' );
		$size = ( string ) $this->getParam ( $params, 'size', true, '' );
		//return '<i class="fa fa-' . $icon . '" style="font-size: ' . $size . 'px"></i>';
		return '<i class="fa fa-' . $icon . '"></i>';
	}
	function small($value, $params = null) {
		global $KUINK_CFG;
		$default = ( string ) $this->getParam ( $params, 'default', false, '' );
		$icon = ( string ) $this->getParam ( $params, $value, false, 'default.png' );
		$size = ( string ) $this->getParam ( $params, 'size', true, '' );
		// kuink_mydebug($value, $icon);
		$configuration = Configuration::getInstance();
		$file = $configuration->web->www_root. '/theme/'. $configuration->theme->name .'/img/' . $icon;
		// Check if the icon exists...
		//if (file_exists ( $file ))
			$icon = $file;
		//else
		//	$icon = $KUINK_CFG->themeRoot . 'theme/' . $KUINK_CFG->theme . '/img/default.png';
			
			/*
		 * //Check if the icon exists...
		 * if ( file_exists('pix/icon_themes/standard/'.$icon))
		 * $icon = 'pix/icon_themes/standard/'.$icon;
		 * else
		 * $icon = 'pix/icon_themes/standard/default.png';
		 */
		
		return '<img align="left" src="' . $icon . '" style="height: ' . $size . 'px; width: auto;" />';
	}
	function repeater($value, $params = null) {
		global $KUINK_CFG;
		$formatter = '';
		$icon = ( string ) $this->getParam ( $params, 'icon', true );
		$size = ( string ) $this->getParam ( $params, 'size', true );
		
		$i_value = ( int ) $value;
		
		$icon = 'theme/' . Configuration::getInstance()->theme->name. '/img/' . $icon;
		for($i = 1; $i <= $i_value; $i ++)
			$formatter .= '<img align="left" src="' . $icon . '" style="height: ' . $size . 'px; width: auto;" />';
		
		return $formatter;
	}
	function percentage($value, $params = null) {
		$min = ( int ) $this->getParam ( $params, 'min', true );
		$max = ( int ) $this->getParam ( $params, 'max', true );
		$perc = ($value != 0) ? ( float ) (( int ) $value / ($max - $min)) * 100.0 : 0;
		$formatter = '
    	<!--table style="border: solid 0px;">
    	<tr>
    	<td-->
    	<div style="background-color: white; border: solid 1px #336699;">
    		<div style="background-color: #6699cc; width: ' . $perc . '%; text-align: center; color: white; border: solid 0px;">' . $value . '</div>
    	</div>
    	<!--/td>
    	</tr>
    	</table-->';
		return $formatter;
	}
}

?>