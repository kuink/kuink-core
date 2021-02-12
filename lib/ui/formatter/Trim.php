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

class Trim extends Formatter {
    function format( $value, $params )
    {
    	if ($value == '')
    		return '';
    	$length = (int)$this->getParam($params, 'length', true, 30);
    	$completeWord = (string)$this->getParam($params, 'completeWord', false, 'true');
    	
		if (strlen($value) <= $length)
			return $value;

    	$formattedValue = substr($value, 0, $length);
    	
    	if ($completeWord == 'true') {
    		if ($value[$length] != ' ') {
	    		$i = $length;
	    		$len = strlen($value);
	    		while (($i < $len) && ($value[$i] != ' ')) {
	    			$i++;
	    		}
	    		//print_object($formattedValue.'::'.substr($value, $length, $i-$length).'('.$length.','.$i.')'.$len);
	    		$formattedValue .= substr($value, $length, $i-$length);
    		}
    	}
    		
       	return $formattedValue.' (...)';
    }
}

?>