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


/*
 * This file is a sample file for generic library
 * 
 * To use it:
 *    Copy this file and paste it in the same directory with the name changed to the new library name
 *    Change the classname to the new library name
 * 
 * 		In the node, declare that we want to use this library
 * 		
 * 		<Node>
 * 			<Libraries>
 *    		<Use name="SampleLib" type="lib"/>
 * 			</Libraries>
 * 
 * 
 *    To call the method sample from SampleLib just add in the logic flow of an action or function:
 * 
 * 		<Var name="example">
 * 			<SampleLib method="sample">
 * 				<Param>A</Param>
 * 				<Param>B</Param>
 * 				<Param>C</Param>
 * 			</SampleLib>
 * 		</Var>
 * 
 * 		Variable "example" will take the value of "A | B | C"
 */

class SampleLib {
	function __construct($nodeconfiguration, $msg_manager) {
		return;
	}

	/**
	 * This sample function will take all params and return a concatenated string with their values separated by ' | '
	 * 
	 * @param  array  $params The params that are passed to lib. 
	 */
	function sample($params) {
		//When entering here, all params inner instructions have been executed
		$result = implode(' | ', $params);
		
		return $result;
	}

}

?>
