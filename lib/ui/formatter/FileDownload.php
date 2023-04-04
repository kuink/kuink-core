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
// require_once('neon_classes.php');
class FileDownload extends Formatter {
	function format($value, $params = null) {
		return $this->small ( $value, $params );
	}
	function small($value, $params = null) {
		// Ler os metadados da base de dados
		// global $DB;
		
		// var_dump($params);
		// kuink_mydebug('Value', $value);
		if (empty ( $value ))
			return '-';
			
			// Try to get elements from $params this way no need to query database
		$ext = isset($params ['fileExt']) ? ( string ) $params ['fileExt'] : '';
		$filename = isset($params ['fileName']) ? ( string ) $params ['fileName'] : '';
		$guid = isset($params ['fileGuid']) ? ( string ) $params ['fileGuid'] : '';
		$filesize = isset($params ['fileSize']) ? ( string ) $params ['fileSize'] : '';
		$original_name = isset($params ['fileOriginalName']) ? ( string ) $params ['fileOriginalName'] : '';
		$unlinked = isset($params ['unlinked']) ? ( string ) $params ['unlinked'] : '';
		
		if ($ext == '' || $filename == '' || $guid == '' || $filesize == '' || $original_name == '') {
			// Get the file data from database
			// kuink_mydebug('Value', $value);
			$conditions = array (
					'table' => 'fw_file',
					'id' => $value 
			);
			$datasource = new \Kuink\Core\DataSource ( null, 'framework,generic,load', 'framework', 'generic' );
			$record = $datasource->execute ( $conditions );
			// var_dump($record);
			if (! isset ( $record ['id'] ))
				return '-';
				
				// $record = $DB->get_record('file', $conditions);
				// var_dump($record);
			
			$ext = ( string ) $record ['ext'];
			$filename = ( string ) $record ['name'];
			$guid = ( string ) $record ['guid'];
			$filesize = $record ['size'];
			$original_name = $record ['original_name'];
			$unlinked = $record ['unlinked'];
		}
		
		// Check if the icon exists...
		if (file_exists ( 'kuink-core/pix/icon_themes/standard/' . $ext . '.png' ))
			$icon = 'kuink-core/pix/icon_themes/standard/' . $ext . '.png';
		else
			$icon = 'kuink-core/pix/icon_themes/standard/default.png';
		
		$contextId = \Kuink\Core\ProcessOrchestrator::getContextId ();
		// $img_html = '<a href="file.php?path='.$this->path.'&guid='.$guid.'"><img align="left" src="'.$icon.'" height="48" alt="'.$ext.'"/></a>';
		// $info_html = '<a href="file.php?path='.$this->path.'&guid='.$guid.'">'.$original_name.'</a>';
		$img_html = ($unlinked != '1') ? '<a href="stream.php?idcontext=' . $contextId . '&type=file&guid=' . $guid . '" target="_blank"><img align="left" src="' . $icon . '" style="height: 48px; width:auto;"  alt="' . $ext . '"/></a>' : '<img align="left" src="' . $icon . '" style="height: 48px; width:auto;" alt="' . $ext . '"/>';
		$info_html = ($unlinked != '1') ? '<a href="stream.php?idcontext=' . $contextId . '&type=file&guid=' . $guid . '" target="_blank">' . $original_name . '</a>' : $original_name;
		$info_html .= '<br>' . $this->FormatBytes ( $filesize );
		
		$div1 = '<div style="border: 0px; float: left; margin: 0px 10px 3px 0px;">' . $img_html . '</div>';
		$div2 = '<div width="100%" style="border: 0px">' . $info_html . '</div>';
		return $div1 . $div2;
		
		// kuink_mydebug ('FileDownload:id', $this->id);
		// kuink_mydebug ('FileDownload:filename', $filename);
		$return_html = '<table border="0" style="border: none;"><tr><td valign="top">' . $img_html . '</td><td valign="top">' . $info_html . '</td></tr></table>';
		
		return $return_html;
	}
	function url($value, $params = null) {
		global $KUINK_CFG;
		// Ler os metadados da base de dados
		// global $DB;
		
		// var_dump($params);
		// kuink_mydebug('Value', $value);
		if (empty ( $value ))
			return '-';
			
			// Try to get elements from $params this way no need to query database
		$ext = ( string ) $params ['fileExt'];
		$filename = ( string ) $params ['fileName'];
		$guid = ( string ) $params ['fileGuid'];
		$filesize = ( string ) $params ['fileSize'];
		$original_name = ( string ) $params ['fileOriginalName'];
		$unlinked = ( string ) $params ['unlinked'];
		
		if ($ext == '' || $filename == '' || $guid == '' || $filesize == '' || $original_name == '') {
			// Get the file data from database
			// kuink_mydebug('Value', $value);
			$conditions = array (
					'table' => 'fw_file',
					'id' => $value 
			);
			$datasource = new \Kuink\Core\DataSource ( null, 'framework,generic,load', 'framework', 'generic' );
			$record = $datasource->execute ( $conditions );
			// var_dump($record);
			if (! isset ( $record ['id'] ))
				return '-';
				
				// $record = $DB->get_record('file', $conditions);
				// var_dump($record);
			
			$ext = ( string ) $record ['ext'];
			$filename = ( string ) $record ['name'];
			$guid = ( string ) $record ['guid'];
			$filesize = $record ['size'];
			$original_name = $record ['original_name'];
			$unlinked = $record ['unlinked'];
		}
		
		$url = $KUINK_CFG->streamFileUrl . $guid;
		
		return $url;
	}

	public function preview($value, $params = null) {
		global $KUINK_CFG;

		$url = $this->url($value, $params);
		
		if ($url != '-') {
			$width = $this->getParam ( $params, 'width', false, 50 );
			$center = $this->getParam ( $params, 'center', false, 1 );

			$url = $KUINK_CFG->wwwRoot.'/'.$url;
	
			$html = '<img src="' . $url . '" style="padding: 2px;background-color: #fff;border: 1px solid #ccc;border: 1px solid rgba(0, 0, 0, 0.2);-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);height:auto; width:' . $width . 'px; max-width:200px; max-height:275px;"/>';		
			if ($center)
				$html = '<center>'.$html.'</center>';
		} 
		else 
			$html = '-';
		
		return $html;
	}


	private function FormatBytes($size) {
		$type = ($size > 1024 * 1024) ? 'MB' : 'KB';
		switch ($type) {
			case "KB" :
				$filesize = $size * .0009765625; // bytes to KB
				break;
			case "MB" :
				$filesize = ($size * .0009765625) * .0009765625; // bytes to MB
				break;
			case "GB" :
				$filesize = (($size * .0009765625) * .0009765625) * .0009765625; // bytes to GB
				break;
		}
		if ($filesize <= 0) {
			return $filesize = 'unknown file size';
		} else {
			return round ( $filesize, 2 ) . ' ' . $type;
		}
	}
}

?>