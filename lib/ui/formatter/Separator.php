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

class Separator extends Formatter {
	function format($value, $params) {
		return $this->custom ( $value, $params );
	}
	function dot($value, $params) {
		$this->validateParams ( $params );
		$separator = '.';
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	function newLine($value, $params) {
		$this->validateParams ( $params );
		$separator = '<br/>';
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	function pipe($value, $params) {
		$this->validateParams ( $params );
		$separator = ' | ';
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	function slash($value, $params) {
		$this->validateParams ( $params );
		$separator = '/';
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	function space($value, $params) {
		$this->validateParams ( $params );
		$separator = '&nbsp;';
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	function underscore($value, $params) {
		$this->validateParams ( $params );
		$separator = '_';
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	function custom($value, $params) {
		$this->validateParams ( $params );
		$append = isset ( $params ['affix'] ) ? ( string ) $params ['affix'] : 'suffix';
		$separator = ( string ) $params ['separator'];
		if ($append == 'prefix')
			$result = $separator . $value;
		else
			$result = $value . $separator;
		
		return $result;
	}
	private function validateParams($params) {
		return;
	}
}

?>