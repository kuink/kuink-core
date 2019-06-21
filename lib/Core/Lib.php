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

class Lib {
	var $nodeconfiguration;
	var $msg_manager;
	function __construct($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function addParam($paramsDef, $name, $type, $mandatory, $default = null) {
		return $paramsDef [] = array (
				'name' => $name,
				'type' => $type,
				'mandatory' => $mandatory,
				'default' => $default 
		);
	}
	function paramsSignature($paramsDef) {
		$signature = array ();
		foreach ( $paramsDef as $paramDef ) {
			$signature [] = $paramDef ['type'] . ' ' . $paramDef ['name'] . (($paramDef ['mandatory']) ? '(mandatory)' : '') . ' default:' . $paramDef ['default'];
		}
		return implode ( ',', $signature );
	}
	function ckeckParams($paramsDef, $params) {
		$checkedParams = array (); // params after check is completed
		
		foreach ( $paramsDef as $paramDef ) {
			if ($paramDef ['mandatory'])
				if (! isset ( $paramDef ['name'] ))
					throw new \Exception ( $this->paramsSignature ( $paramsDef ) );
			$checkedParams [$paramDef ['name']] = isset ( $params [$paramDef ['name']] ) ? ( string ) $params [$paramDef ['name']] : ( string ) $paramDef ['default'];
		}
		
		return $checkedParams;
	}
	
	/**
	 * Get a param value with default value and mandatory
	 * 
	 * @param unknown_type $params        	
	 * @param unknown_type $name        	
	 * @param unknown_type $mandatory        	
	 * @param unknown_type $default        	
	 * @throws Exception
	 */
	function getParam($params, $name, $mandatory, $default) {
		if (! isset ( $params [$name] ) && $mandatory)
			throw new \Exception ( get_class () . ':: Required parameter ' . $name . ' not found' );
		
		if (! isset ( $params [$name] ))
			return $default;
		
		return $params [$name];
	}
}

?>