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

class Xml extends Formatter {
	function format($value, $params = null) {
		$format = str_replace ( '<', '&lt;', $value );
		$format = str_replace ( '>', '&gt;', $format );
		
		return $format;
	}
	function prettyForm($value, $params = null) {
		$format = '<table>';
		if ($value != null) {
			try {
				$xml = simplexml_load_string ( $value );
			} catch ( \Exception $e ) {
				$format = '...';
			}
			$elems = $xml->children ();
			
			$format .= $this->prettyFormHelper ( $elems, 0 );
		}
		$format .= '</table>';
		
		return $format;
	}
	private function prettyFormHelper($elems, $level) {
		$spaces = '';
		for($i = 1; $i <= $level; $i ++)
			$spaces .= '&nbsp;&nbsp;';
		$format = '';
		foreach ( $elems as $elem ) {
			$childs = $elem->children ();
			if (sizeof ( $childs ) > 0) {
				foreach ( $elem->attributes () as $xmlMetaAttrName => $xmlMetaAttrValue )
					$format .= '<tr><td><strong>' . $spaces . $xmlMetaAttrValue . '</strong></td>' . '<td></td></tr>';
				$format .= $this->prettyFormHelper ( $childs, $level + 1 );
			} else {
				foreach ( $elem->attributes () as $xmlMetaAttrName => $xmlMetaAttrValue )
					$format .= '<tr><td><strong>' . $spaces . $xmlMetaAttrValue . '</strong></td>' . '<td>' . $elem [0] . '</td></tr>';
			}
		}
		
		return $format;
	}
}
?> 