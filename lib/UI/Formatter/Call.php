<?php

// This file is part of Neon Application Framework
//
// Neon Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Neon Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Neon Application Framework. If not, see <http://www.gnu.org/licenses/>.
namespace Kuink\UI\Formatter;

class Call extends Formatter {
	function format($value, $params = null) {
		$library = ( string ) $this->getParam ( $params, 'library', true, '' );
		$function = ( string ) $this->getParam ( $params, 'function', true, '' );
		$key = ( string ) $this->getParam ( $params, 'key', false, '' );
		
		unset ( $params ['library'] );
		unset ( $params ['function'] );
		unset ( $params ['method'] );
		unset ( $params ['key'] );
		if (isset($params ['name']) && $params ['name'] == 'call')
			unset ( $params ['name'] );
		
		$formatter = $value;
		
		if ($library != '' && $function != '') {
			// Call a function in a different application,process
			$libParts = explode ( ',', $library );
			// print_object($lib_parts);
			if (count ( $libParts ) != 3)
				throw new \Exception ( 'ERROR: library name must be appname,processname,nodename' );
			
			$libAppname = trim ( $libParts [0] );
			$libProcessname = trim ( $libParts [1] );
			$libNodename = trim ( $libParts [2] );
			
			$callAppname = ($libAppname == 'this') ? $this->nodeconfiguration [NodeConfKey::APPLICATION] : $libAppname;
			$callProcessname = ($libProcessname == 'this') ? $this->nodeconfiguration [NodeConfKey::PROCESS] : $libProcessname;
			$callNodename = $libNodename;
			
			$node = new \Kuink\Core\Node ( $callAppname, $callProcessname, $callNodename );
			$runtime = new \Kuink\Core\Runtime ( $node, 'lib', $this->nodeconfiguration );
			
			$exit = 0;
			
			$params ['value'] = $value;
			
			$return = $runtime->execute ( $function, $params, $exit );
			if ($key != '')
				$formatter = $return ['RETURN'] [$key];
			else
				$formatter = ( string ) $return ['RETURN'];
		}
		return $formatter;
	}
}

?>