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


/**
 * Properties of the FORM
 * @author ptavares
 *
 */
class FileDownloadProperty {
	const PATH = 'path';
	const LABEL = 'label';
	const VISIBLE = 'visible';
}


/**
 * Default values for FORM properties
 * @author ptavares
 *
 */
class FileDownloadPropertyDefaults {
	const PATH = '';
	const LABEL = '';
	const VISIBLE = 'true';
	
}

class FileDownload extends Control {
    var $id;
    var $field;
    var $path;
    var $label;
    var $file;

	function __construct($nodeconfiguration, $xml_definition) {
		parent::__construct($nodeconfiguration, $xml_definition);
	}
				
	function display() {
		$visible = $this->getProperty($id, FileDownloadProperty::VISIBLE, false, FileDownloadPropertyDefaults::VISIBLE);
		$path = $this->getProperty($id, FileDownloadProperty::PATH, false, FileDownloadPropertyDefaults::PATH);
        $label = $this->getProperty($id, FileDownloadProperty::LABEL, false, FileDownloadPropertyDefaults::LABEL);
        $label = \Kuink\Core\Language::getString($label, $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);

        if ($visible != 'true')
            return;

        $this->id = (string)$this->bind_data[0];

    	$params = array();
    	$params[0] = 'FileDownload';
    	$params[1] = 'format';
    	$params[2] = $this->id;
    	$params[3] = null;

    	$formatter = new \FormatterLib($this->nodeconfiguration, null);
    	
    	//var_dump( $formatter );
    
    	//var_dump( $params );
    	$content = $formatter->format( $params );
        
        $ctrlParams['content'] = $content;
        
        $this->render( $ctrlParams );
	}

	function getHtml() {
        return 'Contro::FileDownload';
	}
	
	
	private function FormatBytes($size)
	{
		$type = ($size > 1024*1024) ? 'MB' : 'KB';
		switch($type){
			case "KB":
				$filesize = $size * .0009765625; // bytes to KB
				break;
			case "MB":
				$filesize = ($size * .0009765625) * .0009765625; // bytes to MB
				break;
			case "GB":
				$filesize = (($size * .0009765625) * .0009765625) * .0009765625; // bytes to GB
				break;
		}
		if($filesize <= 0){
			return $filesize = 'unknown file size';
		}
		else{
			return round($filesize, 2).' '.$type;
		}
	}	
	
}


?>