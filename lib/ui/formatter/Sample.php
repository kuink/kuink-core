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

/**
 * This is a sample file for a formatter 
 * 
 * To use it:
 *    Copy this file and paste it in the same directory with the name changed to the new formatter name
 *    Change the classname to the new formatter name
 * 		
 * This formatter will just wrap the value in a paragraph or in a header 
 * 
 * Xml definition of this formatter
 * 		Call formatter default format method
 * 			<Formatter name="Sample"/>
 * 		
 * 		Call formatter header method
 * 			<Formatter name="Sample" method="header"/>
 *
 * Usage example:
 * 		Use this Formatter on a form or grid and see how it looks like
 * 
 * @author paulo.tavares
 */

class Sample extends Formatter {
	
	//This function mus be implemented and will be the default method of Sample Formatter
	function format( $value, $params=null )
	{
		if (empty($value))
			return '';
	
		$result = '<p>Sample Formatter: '.$value.'</p>';

		return $result;
	}

	//Example of another way that sample uses to format the value
	function header( $value, $params=null )
	{
		if (empty($value))
			return '';
	
		$result = '<h1>Sample Formatter: '.$value.'</h1>';

		return $result;
	}


}

?>