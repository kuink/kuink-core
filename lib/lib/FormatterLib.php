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

/**
 * Use this lib to format a value, using kuink formatters
 * This lib works as a proxy to formatters.
 * 
 * @author Joao Patricio
 */
class FormatterLib {
	var $nodeconfiguration;
	function __construct($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
	}
	function format($params) {
		$formatter = ( string ) $params [0];
		$method = ( string ) $params [1];
		$value = (is_array ( $params [2] )) ? $params [2] : ( string ) $params [2];
		$params = (isset ( $params [3] )) ? $params [3] : null;
		
		$params ['method'] = $method;
		$formatted_value = ( string ) $this->call_formatter ( $formatter, $params, $value );
		// $formatted_value = call_user_func_array(array('Kuink\\Formatter\\'.$formatter, $method), array($value));
		// kuink_mydebug($value, $formatted_value);
		return $formatted_value;
	}
	private function call_formatter($formatter_name, $params, $value) {
		$formatter_name = str_replace ( 'Formatter', '', $formatter_name );
		// kuink_mydebug('Formatter', $formatter_name);
		$formatter = Kuink\Core\Factory::getFormatter ( $formatter_name, $this->nodeconfiguration, null );
		
		if (! $formatter)
			throw new Exception ( 'Formatter ' . $formatter_name . ' does not exists.' );
		
		$formatter_method = (isset ( $params ['method'] )) ? ( string ) $params ['method'] : 'format';
		
		// Check if the method exists
		if (method_exists ( $formatter, $formatter_method ))
			$result = ( string ) $formatter->$formatter_method ( $value, $params );
		else
			throw new Exception ( 'Formatter method ' . $formatter_name . '.' . $formatter_method . ' does not exists.' );
			
			// kuink_mydebug($formatter_name, $result);
		return $result;
	}
}

?>