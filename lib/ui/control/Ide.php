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

class Ide extends Control {
	var $baseUrl; //The base url to define actions or events

	function __construct($nodeconfiguration, $xml_definition) {
		parent::__construct ( $nodeconfiguration, $xml_definition );
				
		$baseUrl = $nodeconfiguration ['baseurl'];
		$url = \Kuink\Core\Tools::setUrlParams ( $baseUrl );
		$this->baseUrl = $url;		
	}

	function display() {
		$data = isset($this->bind_data [0]) ? ( string ) $this->bind_data [0] : null;
		$params = array();
		$params['baseUrl'] = $this->baseUrl;
		
		$this->render( $params );
	}

	function getHtml() {
		$html = $this->name;
		return $html;
	}
}

?>
