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

class Uml extends Control {
	function display() {
		$imgFormat =  (string)$this->getProperty('', 'format', false, 'png', $this->xml_definition);
		$text = isset($this->bind_data[0]) ? (string)$this->bind_data[0] : '';
		$textNl = preg_replace('#<br\s*/?>#i', "\n", $text);		
		$encoded = $this->encodep($textNl);

		$title = (string)$this->getProperty('', 'title', false, '', $this->xml_definition);
		$title = \Kuink\Core\Language::getString($title, $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);

		$this->render( array('encoded'=>$encoded, 'format'=>$imgFormat, 'title'=>$title, 'text'=>$text) );
	}

	function getHtml() {
		$html = '';
		return $html;
	}


	//Helper funcrtions source: https://plantuml.com/code-php
	private function encodep($text) {
		//$data = utf8_encode($text);
		$compressed = gzdeflate($text, 9);
		return $this->encode64($compressed);
   }
   
   private function encode6bit($b) {
		if ($b < 10) {
			 return chr(48 + $b);
		}
		$b -= 10;
		if ($b < 26) {
			 return chr(65 + $b);
		}
		$b -= 26;
		if ($b < 26) {
			 return chr(97 + $b);
		}
		$b -= 26;
		if ($b == 0) {
			 return '-';
		}
		if ($b == 1) {
			 return '_';
		}
		return '?';
   }
   
   private function append3bytes($b1, $b2, $b3) {
		$c1 = $b1 >> 2;
		$c2 = (($b1 & 0x3) << 4) | ($b2 >> 4);
		$c3 = (($b2 & 0xF) << 2) | ($b3 >> 6);
		$c4 = $b3 & 0x3F;
		$r = "";
		$r .= $this->encode6bit($c1 & 0x3F);
		$r .= $this->encode6bit($c2 & 0x3F);
		$r .= $this->encode6bit($c3 & 0x3F);
		$r .= $this->encode6bit($c4 & 0x3F);
		return $r;
   }
   
   private function encode64($c) {
		$str = "";
		$len = strlen($c);
		for ($i = 0; $i < $len; $i+=3) {
			   if ($i+2==$len) {
					 $str .= $this->append3bytes(ord(substr($c, $i, 1)), ord(substr($c, $i+1, 1)), 0);
			   } else if ($i+1==$len) {
					 $str .= $this->append3bytes(ord(substr($c, $i, 1)), 0, 0);
			   } else {
					 $str .= $this->append3bytes(ord(substr($c, $i, 1)), ord(substr($c, $i+1, 1)),
						 ord(substr($c, $i+2, 1)));
			   }
		}
		return $str;
   }


}


?>
