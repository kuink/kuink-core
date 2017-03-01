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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework.  If not, see <http://www.gnu.org/licenses/>.


namespace Kuink\UI\Formatter;

class Formatter {
	
	var $nodeconfiguration;
	var $msg_manager;
	
	
	function __construct( $nodeconfiguration, $msg_manager ) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
		
	/**
	 * Get a param value for formatters with default value and mandatory
	 * @param unknown_type $params
	 * @param unknown_type $name
	 * @param unknown_type $mandatory
	 * @param unknown_type $default
	 * @throws Exception
	 */
	function getParam($params, $name, $mandatory, $default) {
		if (!isset($params[$name]) && $mandatory)
			throw new \Exception(get_class().':: Required parameter '.$name.' not found');
		
		if (!isset($params[$name]))
			return $default;
		
		return $params[$name];		
	}
}

?>