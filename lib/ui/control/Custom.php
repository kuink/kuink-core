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
namespace Kuink\UI\Control;

class Custom extends Control {
	var $value;
	var $app_name;
	var $process_name;
	var $node_name;
	function __construct($nodeconfiguration, $xml_definition) {
		parent::__construct ( $nodeconfiguration, $xml_definition );
		
		$control_location = ( string ) $xml_definition ['control'];
		$control_parts = explode ( ',', $control_location );
		if (count ( $control_parts ) != 3)
			throw new \Exception ( 'Custom controls name must be appname,processname,nodename' );
		
		$ctrl_appname = trim ( $control_parts [0] );
		$ctrl_processname = trim ( $control_parts [1] );
		$ctrl_nodename = trim ( $control_parts [2] );
		
		$this->app_name = ($ctrl_appname == 'this') ? $nodeconfiguration ['customappname'] : $ctrl_appname;
		$this->process_name = ($ctrl_processname == 'this') ? $nodeconfiguration ['master_process_name'] : $ctrl_processname;
		$this->node_name = $ctrl_nodename;
		
		$this->value = '';
		
		return $this;
	}
	function bind($value) {
		$params = array ();
		foreach ( $value as $pkey => $pvalue )
			$params [$pkey] = ( string ) $pvalue;
		
		$this->value = $params;
	}
	function display() {
		$newnodeconfiguration = $this->nodeconfiguration;
		$params = array ();
		$value = '';
		if (key ( $this->value ) == '0') {
			// Single value
			$value = current ( $this->value );
		} else {
			// Set the params to the named parameter array
			$params = $this->value;
			$value = $this->value;
		}
		
		$visible = $this->getProperty ( $this->name, 'visible', false, 'true' );
		$isVisible = ($visible == 'true');
		
		if ($isVisible) {
			// var_dump( $value );
			$newnodeconfiguration ['actionvalue'] = $value;
			$newnodeconfiguration ['customappname'] = $this->app_name;
			$newnodeconfiguration ['master_process_name'] = $this->process_name;
			$newnodeconfiguration ['action'] = '';
			$node = new \Kuink\Core\Node ( $this->app_name, $this->process_name, $this->node_name );
			$runtime = new \Kuink\Core\Runtime ( $node, 'ui', $newnodeconfiguration, true, $params );
			// kuink_mydebug($this->name, $this->position );
			$runtime->forcePosition ( $this->position );
			$runtime->execute ();
		}
	}
	function getHtml() {
		$visible = $this->getProperty ( $this->name, 'visible', false, 'true' );
		$isVisible = ($visible == 'true');
		
		if ($isVisible) {
			// kuink_mydebug('CUSTOM CONTROL',$this->app_name.'::'.$this->process_name.'::'.$this->node_name);
			$newnodeconfiguration = $this->nodeconfiguration;
			$value = implode ( '', $this->value );
			$newnodeconfiguration ['actionvalue'] = $value;
			$newnodeconfiguration ['customappname'] = $this->app_name;
			$newnodeconfiguration ['master_process_name'] = $this->process_name;
			$newnodeconfiguration ['action'] = '';
			$node = new \Kuink\Core\Node ( $this->app_name, $this->process_name, $this->node_name );
			$runtime = new \Kuink\Core\Runtime ( $node, 'ui', $newnodeconfiguration, false );
			$html = $runtime->execute ();
		} else {
			$html = '';
		}
		return $html;
	}
}

?>