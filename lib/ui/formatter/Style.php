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

class Style extends Formatter {
	function format($value, $params) {
		return $this->bold ( $value, $params );
	}
	function bold($value, $params) {
		return '<strong>' . $value . '</strong>';
	}
	function italic($value, $params) {
		return '<i>' . $value . '</i>';
	}
	function nl2br($value, $params) {
		return nl2br ( $value );
	}

	function stringSafe( $value, $params ) {
    	if (trim($value) == '')
    		return $value;
		$chars = array(
			"À"=>"A","Á"=>"A","Â"=>"A","Ã"=>"A","Ä"=>"A","Å"=>"A",
			"Æ"=>"A","Ç"=>"C","È"=>"E","É"=>"E","Ê"=>"E","Ë"=>"E",
			"Ì"=>"I","Í"=>"I","Î"=>"I","Ï"=>"I","Ð"=>"Dj","Ñ"=>"N",
			"Ò"=>"O","Ó"=>"O","Ô"=>"O","Õ"=>"O","Ö"=>"O","Ø"=>"O",
			"Ù"=>"U","Ú"=>"U","Û"=>"U","Ü"=>"U","Ý"=>"Y","Þ"=>"B",
			"ß"=>"Ss","à"=>"a","á"=>"a","â"=>"a","ã"=>"a","ä"=>"a",
			"å"=>"a","æ"=>"a","ç"=>"c","è"=>"e","é"=>"e","ê"=>"e",
			"ë"=>"e","ì"=>"i","í"=>"i","î"=>"i","ï"=>"i","ð"=>"o",
			"ñ"=>"n","ò"=>"o","ó"=>"o","ô"=>"o","õ"=>"o","ö"=>"o",
			"ø"=>"o","ù"=>"u","ú"=>"u","û"=>"u","ü"=>"u","ý"=>"y",
			"þ"=>"b","ÿ"=>"y","Ă"=>"A","ă"=>"a","Ń"=>"N","ń"=>"n",
			"Š"=>"S","š"=>"s","Ž"=>"Z","ž"=>"z","ƒ"=>"f","Ș"=>"S",
			"ș"=>"s","Ț"=>"T","ț"=>"t",
			);			
		//Replace \n with space
		$formattedValue = $string = trim(preg_replace('/\s\s+/', ' ', $value)); 
		//Remove all characters that could harm a string
		$formattedValue =  strtr($formattedValue, $chars);
		$formattedValue = preg_replace('/[^a-zA-Z0-9" "-]/', '', $formattedValue);

		return $formattedValue;
    }	

	function nl2list($value, $params) {
		if (trim ( $value ) == '')
			return $value;
		
		$tag = isset ( $params [0] ) ? ( string ) $params [0] : 'ul';
		$bits = explode ( "\n", $value );
		
		$newstring = '<' . $tag . '>';
		
		foreach ( $bits as $bit ) {
			$newstring .= "<li>" . $bit . "</li>";
		}
		
		return $newstring . '</' . $tag . '>';
	}

	function size($value, $params) {
		$size = '75%';
		if (isset($params['size']))
				$size = (string)$params['size'];
		return '<span style="font-size: '.$size.'">'.$value.'</span>';
	}   
	
	function collapsible($value, $params) {
		
		if (isset($params['collapsed']))
			$collapsed = (string)$params['collapsed'];
		else
			$collapsed = 'false';

			$styleDisplay = ($collapsed == 'false') ? 'block' : 'none';

		$uuid = uniqid();
		$format = '
		<script>
		$(document).ready(function(){
			var showText = $("#showHide_'.$uuid.'").attr("showText");
			var hideText = $("#showHide_'.$uuid.'").attr("hideText");

			$("#showHide_'.$uuid.'").html('.(($collapsed == 'false') ? 'hideText' : 'showText').');
		});

		function showHideHeader_'.$uuid.'() {
			var showText = $("#showHide_'.$uuid.'").attr("showText");
			var hideText = $("#showHide_'.$uuid.'").attr("hideText");

			if( $("#head_'.$uuid.'").css("display") == "block") {
				$("#showHide_'.$uuid.'").html(showText);
				$("#head_'.$uuid.'").fadeOut();
			} else {
				$("#showHide_'.$uuid.'").html(hideText);
				$("#head_'.$uuid.'").fadeIn("slow");
			}
		}
	</script>';
		$format .=  '<a id="showHide_'.$uuid.'" showtext="mostrar" hidetext="ocultar" href="javascript:void(0);" onmousedown="showHideHeader_'.$uuid.'();">ocultar</a>';
		$format .=  '<div id="head_'.$uuid.'" name="head_'.$uuid.'" style="display: '.$styleDisplay.';">'.$value.'</div>';
		return $format;
	}
}
?>