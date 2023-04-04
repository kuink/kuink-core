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

class ParserFunctions {
	static function __empty($value) {
		return empty ( $value );
	}
	static function __length($value) {
		if (is_array ( $value ))
			return count ( $value );
		else
			return strlen ( $value );
	}
	static function __isArray($value) {
		return is_array ( $value );
	}
	// used only for captchas right now
	static function __isValid($value) {
		$check = \Kuink\Core\Captcha::isValid($value);
		return ($check) ? 1 : 0;
	}
	static function __toStr($value) {
		$result = ( string ) $value;
		if (is_array ( $value ))
			$result = json_encode ( $value );
		return $result;
	}
  static function __hasValue( $value ) {
  	return (($value !== '') && ($value !== null));
  }
}

?>