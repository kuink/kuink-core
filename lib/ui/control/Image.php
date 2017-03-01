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
// along with Kuink Application Framework.  If not, see <http://www.gnu.org/licenses/>.


namespace Kuink\UI\Control;

class Image extends Control {
	var $streamType;
	var $streamGuid;
	var $title;
	
	function display() {
		$titleRaw =  (string)$this->getProperty('', 'title', false, '', $this->xml_definition);
		$title = \Kuink\Core\Language::getString($titleRaw, $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);
		
		$this->streamType = isset($this->bind_data[0]['type']) ? $this->bind_data[0]['type'] : '';
		$this->streamGuid = isset($this->bind_data[0]['guid']) ? $this->bind_data[0]['guid'] : '';
		if ($this->streamType == '')
			throw new \Exception('Image control bind type not found');
		if ($this->streamGuid == '')
			throw new \Exception('Image control bind guid not found');
		$this->render( array('type'=>$this->streamType, 'guid'=>$this->streamGuid, 'title'=>$title) );
	}

	function getHtml() {
		$html = '';
		return $html;
	}


}


?>
