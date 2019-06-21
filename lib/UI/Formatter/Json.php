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

class Json extends Formatter {
	function format($value, $params = null) {
		$formatted = $this->set2Json($value, $params);
		return $formatted;
	}

	function set2json($value, $params = null) {
		$formatted = json_encode($value);
		return $formatted;
	}

	function prettyPrint($value, $params = null) {
		$formatted = json_encode(json_decode($value), \JSON_PRETTY_PRINT);
		return $formatted;
	}

}

?>