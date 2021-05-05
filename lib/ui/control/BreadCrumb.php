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

class BreadCrumb extends Control {
	function display() {
		
		$params = array();
		$params['action'] = (string)$this->getProperty($this->name, 'action', true, '');
		$params['labelField'] = (string)$this->getProperty($this->name, 'label', false, 'label');
		$params['actionvalueField'] = (string)$this->getProperty($this->name, 'actionvalue', false, 'actionvalue');
		$params['title'] = (string)$this->getProperty($this->name, 'title', false, '');
		$params['title'] = \Kuink\Core\Language::getString ( $params['title'], $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] );

		$params['baseUrl'] = $this->nodeconfiguration [\Kuink\Core\NodeConfKey::BASEURL];
		$params['entries'] = $this->bind_data;
		$this->render ($params);
	}

	function getHtml() {
		$html = '';
		return $html;
	}
}

?>
