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

		if (empty ( $value ))
			return '-';
			
			// Try to get elements from $params this way no need to query database
		$ext = isset($params ['ext']) ? ( string ) $params ['ext'] : '';
		$application = isset($params ['application']) ? ( string ) $params ['application'] : '';
		$path = isset($params ['path']) ? '/'.( string ) $params ['path'].'/' : '';
		$height = isset($params ['height']) ? (string) $params ['height'] : '50px';
		$width = isset($params ['width']) ? ( string ) $params ['width'] : '';

		$height = 'height="'.$height.'"';
		$width = ($width == '') ? '' : 'width="'.$width.'"';

		$guid = $application.'/'.$path.$value.'.'.$ext;

		$contextId = \Kuink\Core\ProcessOrchestrator::getContextId ();
		$imgHtml = '<img src="stream.php?idcontext=' . $contextId . '&type=app_file&guid=' . $guid . '" '.$height.' '.$width.'/>';
		$returnHtml = '<table border="0" style="border: none;"><tr><td valign="top">' . $imgHtml . '</td></tr></table>';
		
		return $returnHtml;
	}
}