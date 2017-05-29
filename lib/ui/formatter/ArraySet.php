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

class ArraySet extends Formatter {
	function format($value, $params = null) {
		$formatedString = (is_array ( $value )) ? '' : $value;
		if (is_array ( $value ))
			foreach ( $value as $key => $aValue )
				if (is_array ( $aValue ))
					$formatedString = $formatedString . $this->format ( $aValue, $params ) . '<br/>';
				else
					$formatedString = $formatedString . $key . ': ' . $aValue . '</br>';
		
		return ( string ) $formatedString;
	}
	function listLookup($value, $params = null) {
		$formatedString = (is_array ( $value )) ? '' : $value;
		$entity = ( string ) $this->getParam ( $params, 'entity', true );
		$key = ( string ) $this->getParam ( $params, 'key', true );
		$attribute = ( string ) $this->getParam ( $params, 'attribute', true );
		$prefix = ( string ) $this->getParam ( $params, 'prefix', false, '' );
		if (is_array ( $value )) {
			$result = array ();
			foreach ( $value as $aKey => $aValue ) {
				// lookup the value
				if ($aValue == 1 || $aValue == '1') {
					
					$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
					$daParams ['_entity'] = $entity;
					$daParams [$key] = $aKey;
					$record = $dataAccess->execute ( $daParams );
					$result [] = ( string ) $prefix . $record [$attribute];
				}
			}
			$formatedString = implode ( ',', $result );
		}
		return ( string ) $formatedString;
	}
}

?>