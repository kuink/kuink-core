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
namespace Kuink\Core;

class Message {
	var $type;
	var $msg;
	function __construct($msg_type, $msg_string) {
		$this->type = $msg_type;
		$this->msg = $msg_string;
		return;
	}
	function get_formatted_msg() {
		$type = "information";
		switch ($this->type) {
			case MessageType::ERROR :
				$type = "error";
				break;
			case MessageType::INFORMATION :
				$type = "information";
				break;
			case MessageType::SUCCESS :
				$type = "success";
				break;
			case MessageType::WARNING :
				$type = "warning";
				break;
			case MessageType::EXCEPTION :
				$type = "exception";
				break;
		}
		
		return array (
				"type" => $type,
				"text" => $this->msg 
		);
		// @TODO STI: Joao Patricio - clean this code
		/*
		 * $color = 'black';
		 * $bkg_color = '#eeeeee';
		 * $icon = '';
		 * switch ($this->type)
		 * {
		 * case MessageType::ERROR:
		 * $color = 'red';
		 * $icon = 'neon_error.png';
		 * break;
		 * case MessageType::INFORMATION:
		 * $color = 'blue';
		 * $icon = 'neon_information.png';
		 * break;
		 * case MessageType::SUCCESS:
		 * $color = 'green';
		 * $icon = 'neon_success.png';
		 * break;
		 * case MessageType::WARNING:
		 * $color = 'orange';
		 * $icon = 'neon_warning.png';
		 * break;
		 * case MessageType::EXCEPTION:
		 * $bkg_color = '#333333';
		 * $color = 'white';
		 * $icon = 'neon_exception.png';
		 * break;
		 * }
		 *
		 * return '<div align="center" style="verticalte-align:middle; color:'.$color.';background-color:'.$bkg_color.';"><img height="20" style="vertical-align:middle; margin-right: 15px;" src="pix/icon_themes/standard/'.$icon.'"/><span><strong>'.$this->msg.'</strong></span></div>';
		 */
	}
}

?>