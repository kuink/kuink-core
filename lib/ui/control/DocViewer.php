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

class DocViewer extends Control {
	function display() {
		$titleRaw = ( string ) $this->getProperty ( '', 'title', false, '', $this->xml_definition );
		$title = \Kuink\Core\Language::getString ( $titleRaw, $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] );
		
		$data = isset($this->bind_data [0]) ? (string) $this->bind_data [0] : null;
		$this->render ( array (
				'fileGuid' => $data,
				'title' => $title 
		) );
	}
	function getHtml() {
		$html = $this->name;
		return $html;
	}
}

?>
