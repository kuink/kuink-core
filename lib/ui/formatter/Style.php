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

class Style extends Formatter {
	function format($value, $params) {
		return $this->bold ( $value, $params );
	}
	function bold($value, $params) {
		return '<strong>' . $value . '</strong>';
	}
	function italic($value, $params) {
		return '<i>' . $value . '</i>';
	}
	function nl2br($value, $params) {
		return nl2br ( $value );
	}
	function nl2list($value, $params) {
		if (trim ( $value ) == '')
			return $value;
		
		$tag = isset ( $params [0] ) ? ( string ) $params [0] : 'ul';
		$bits = explode ( "\n", $value );
		
		$newstring = '<' . $tag . '>';
		
		foreach ( $bits as $bit ) {
			$newstring .= "<li>" . $bit . "</li>";
		}
		
		return $newstring . '</' . $tag . '>';
	}
}
?>