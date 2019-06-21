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
// require_once('kuink_classes.php');
class Person extends Formatter {
	function format($value, $params = null) {
		return $this->photoById ( $value, $params );
	}
	function photoById($value, $params = null) {
		global $KUINK_CFG;
		
		if (empty ( $value ))
			return '';
		
		$ids = explode ( ',', $value );
		
		$ext = ( string ) $this->getParam ( $params, 'ext', true );
		$path = ( string ) $this->getParam ( $params, 'path', true );
		$width = $this->getParam ( $params, 'width', false, 50 );
		$center = $this->getParam ( $params, 'center', false, 1 );
		$imageRoot = 'theme/' . $KUINK_CFG->theme . '/img/';
		if ($KUINK_CFG->imageRemote != '')
			$imageRoot = $KUINK_CFG->imageRemote;
		
		$html = '';
		foreach ( $ids as $id ) {
			if (trim ( $id ) != '') {
				$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
				$daParams ['_entity'] = 'person';
				$daParams ['id'] = $id;
				$record = $dataAccess->execute ( $daParams );
				// print_object($record);
				$fileName = ( string ) $record ['public_key'];
				$photo = $imageRoot . $path . '/' . $fileName . '.' . $ext;
				
				if ($path != '' && $KUINK_CFG->imageRemote == '') {
					$html .= '<img alt="' . $record ['display_name'] . '" title="' . $record ['display_name'] . '" src="stream.php?type=' . $path . '&guid=' . $fileName . '" style="padding: 2px;background-color: #fff;border: 1px solid #ccc;border: 1px solid rgba(0, 0, 0, 0.2);-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);height:auto; width:' . $width . 'px; max-width:200px; max-height:275px;"/>';
				} else {
					$html .= '<img alt="' . $record ['display_name'] . '" title="' . $record ['display_name'] . '" src="' . $photo . '" style="padding: 2px;background-color: #fff;border: 1px solid #ccc;border: 1px solid rgba(0, 0, 0, 0.2);-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);height:auto; width:' . $width . 'px; max-width:200px; max-height:275px;"/>';
					if ($center == 1) {
						$html = '<center>' . $html . '</center>';
					}
				}
			}
		}
		
		return $html;
	}
}

?>
