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

namespace Kuink\UI\Screen;


/**
 * This class is the class that defines all screens
 * @author ptavares
 */
class Screen
{
	var $runtime; //The runtime that holds this screen
	var $nodeConfiguration; //The node configuration object	
	var $nodeManager; //The node manager to get access to the xml definition of the screen
	var $name; //This control name
	var $template; //The template name used in this screen
	var $controls; //All the controls loaded in this screen
	var $xmlDefinition; //This screen xml definition

	/**
	 * Base Contructor
	 * @param $runtime - the runtime that holds this screen
	 * @param $nodeConfiguration - The node configuration to get access to node configuration
	 * @param $nodeManager - The node configuration to get access to the xml configuration
	 */
	function __construct($runtime, $nodeConfiguration, $nodeManager, $name) {
		$this->nodeConfiguration = $nodeConfiguration;
		$this->nodeManager = $nodeManager;
		$this->runtime = $runtime;
		$this->name = $name;
		
		$this->controls = array();
	}

	/**
	 * Loads all the controls in the screen
	 */
	public function loadControls() {
		$this->nodeManager->getScreenControls($this->name);	
		//$this->controls[] = $control;
	}
	
	
	
	/**
	 * Adds a new control to the screen
	 * @param unknown $control
	 */
	public function addControl($control) {
		$this->controls[] = $control;
	}
	
}

?>
