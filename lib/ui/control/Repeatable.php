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
// along with Neon Application Framework.  If not, see <http://www.gnu.org/licenses/>.


namespace Kuink\UI\Control;

class Repeatable extends Control {
	function display() {
		$iconField =  
		$urlField =  (string)$this->getProperty('', 'url', false, '', $this->xml_definition);
		$labelField =  (string)$this->getProperty('', 'label', false, '', $this->xml_definition);

		$params = array();
		$params['data'] = $this->bind_data;
		$params['icon'] = (string)$this->getProperty('', 'icon', false, '', $this->xml_definition);
		$params['url'] = (string)$this->getProperty('', 'url', false, '', $this->xml_definition);
		$params['label'] = (string)$this->getProperty('', 'label', false, '', $this->xml_definition);
		$params['type'] = (string)$this->getProperty('', 'type', false, '', $this->xml_definition);
		$params['cols'] = (string)$this->getProperty('', 'cols', false, '', $this->xml_definition);
		$params['template'] = (string)$this->getProperty('', 'template', false, '', $this->xml_definition);
		$params['tooltip'] = (string)$this->getProperty('', 'tooltip', false, '', $this->xml_definition);
		
	$this->render( $params );
	}

	function getHtml() {
		$html = '';
		return $html;
	}


}


?>
