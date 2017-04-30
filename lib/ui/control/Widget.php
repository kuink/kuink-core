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
namespace Kuink\UI\Control;

class Widget extends Control {
	var $params;
	function __construct($nodeconfiguration, $xml_definition) {
		parent::__construct ( $nodeconfiguration, $xml_definition );
		$this->params = array ();
		
		return $this;
	}
	function bind($value) {
		// var_dump($value[0]);
		foreach ( $value [0] as $pkey => $pvalue )
			$this->params [$pkey] = ( string ) $pvalue;
	}
	function display() {
		$this->render ( $this->params );
	}
	function getHtml() {
		return;
	}
}

?>