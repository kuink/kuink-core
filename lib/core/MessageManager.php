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

class MessageManager {
	var $msgs;
	private static $instance;
	function __construct() {
		$this->msgs = array ();
		return;
	}
	
	/**
	 * Get current \Kuink\UI\Layout\Layout instance
	 */
	public static function getInstance() {
		if (! self::$instance)
			self::$instance = new MessageManager ();
		return self::$instance;
	}
	function add($msg_type, $msg_string) {
		$this->msgs [] = new Message ( $msg_type, $msg_string );
	}
	function get_messages() {
		$errors = $this->get_msgs_from_type ( MessageType::ERROR );
		$warnings = $this->get_msgs_from_type ( MessageType::WARNING );
		$informations = $this->get_msgs_from_type ( MessageType::INFORMATION );
		$successes = $this->get_msgs_from_type ( MessageType::SUCCESS );
		$exceptions = $this->get_msgs_from_type ( MessageType::EXCEPTION );
		return array_merge ( $errors, $warnings, $informations, $successes, $exceptions );
	}
	function print_messages() {
		$layout_msgs = array ();
		
		// @TODO STI: Joao Patricio - clean this code
		/*
		 * foreach($out_msgs as $msg)
		 * print($msg->get_formatted_msg());
		 */
		
		foreach ( $this->msgs as $msg )
			$layout_msgs [] = $msg->get_formatted_msg ();
		
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		$layout->addUserMessages ( $layout_msgs );
	}
	// MessageType::error
	function has_type($type) {
		foreach ( $this->msgs as $msg )
			if ($msg->type == $type)
				return 1;
		
		return 0;
	}
	function get_msgs_from_type($type) {
		$msgs_type = array ();
		
		foreach ( $this->msgs as $msg )
			if ($msg->type == $type)
				$msgs_type [] = $msg;
		
		return $msgs_type;
	}
}

?>