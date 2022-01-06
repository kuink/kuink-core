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

class AppFileDownload extends Formatter {
	function format($value, $params = null) {
		return $this->small ( $value, $params );
	}

	function small($value, $params = null) {
		global $KUINK_CFG;

		if (empty ( $value ))
			return '-';
			
			// Try to get elements from $params this way no need to query database
		$ext = isset($params ['ext']) ? ( string ) $params ['ext'] : '';
		$application = isset($params ['application']) ? ( string ) $params ['application'] : '';
		$path = isset($params ['path']) ? '/'.( string ) $params ['path'].'/' : '';
		$style = isset($params ['style']) ? ( string ) $params ['style'] : 'height: 50px';
		$zoom = isset($params ['zoom']) ? ( string ) $params ['zoom'] : '';
		$fullUrl = isset($params ['fullUrl']) ? ( string ) $params ['fullUrl'] : 'false';
		$zoomClass = ($zoom == 'true') ? 'kuinkZoom' : '';

		$guid = $application.'/'.$path.$value;
		if($ext != '') {
			$guid .= '.' . $ext;
		}

		$class = $zoomClass;

		$contextId = \Kuink\Core\ProcessOrchestrator::getContextId ();
		$imageUrl = 'stream.php?idcontext=' . $contextId . '&type=app_file&guid=' . $guid;
		if ($fullUrl == 'true')
			$imageUrl = $KUINK_CFG->wwwRoot.'/'.$KUINK_CFG->streamUrl.'/'.$imageUrl;
		
		$imgHtml = '<img src="' . $imageUrl . '" class="'.$class.'" style="'.$style.'"/>';
		$returnHtml = '<table border="0" style="border: none;"><tr><td valign="top" style="display: flex">' . $imgHtml . '</td></tr></table>';
		
		return $returnHtml;
	}
}