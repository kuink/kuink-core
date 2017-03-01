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

namespace Kuink\UI\Formatter;

class Form extends Formatter
{
	function format( $value, $params=null ) {
		return $this->checkbox($value, $params);
	}
	
    function checkbox( $value, $params=null )
    {
    	$format='<input type="checkbox" name="'.$value.'" value="" />';
        return $format;
    }

    function text( $value, $params=null )
    {
    	$size = (string)$this->getParam($params, 'size', false, 'small');
    	$id = (string)$this->getParam($params, 'id', true, '_');
    	$column = (string)$this->getParam($params, 'column', true, '_');
    	
    	$format='<input type="text" class="input-'.$size.'" value="'.$value.'" id="'.$id.'_'.$column.'" name="'.$id.'_'.$column.'"/>';
    	return $format;
    }

    
    
    function textArea( $value, $params=null )
    {
    	$rows = (string)$this->getParam($params, 'rows', false, '5');
    	$cols = (string)$this->getParam($params, 'cols', false, '20');
    	$size = (string)$this->getParam($params, 'size', false, 'small');
    	$id = (string)$this->getParam($params, 'id', true, '_');
    	$column = (string)$this->getParam($params, 'column', true, '_');
    	$format = '<textarea class="input-'.$size.' " id="'.$id.'_'.$column.'" name="'.$id.'_'.$column.'" rows="'.$rows.'" cols="'.$cols.'" 0>'.$value.'</textarea>';
    	return $format;
    }

    function select( $value, $params=null )
    {
    	$rows = (string)$this->getParam($params, 'rows', false, '5');
    	$cols = (string)$this->getParam($params, 'cols', false, '20');
    	$size = (string)$this->getParam($params, 'size', false, 'small');
    	$id = (string)$this->getParam($params, 'id', true, '_');
    	$column = (string)$this->getParam($params, 'column', true, '_');
    	$datasource = $this->getParam($params, 'datasource', true, '_');
    	$bindid = (string)$this->getParam($params, 'bindid', true, '_');
    	$bindvalue = (string)$this->getParam($params, 'bindvalue', true, '_');
    	
    	$format = '<select id="'.$id.'_'.$column.'" name="'.$id.'_'.$column.'" class="input-'.$size.' " >';
    	$format .= '<option value="" ></option>';
    	foreach ($datasource as $row) {
    		$id = (string)$row->$bindid;
    		$value = (string)$row->$bindvalue;
    		$format .= '<option value="'.$id.'" >'.$value.'</option>';
    	}
    	$format .= '</select>';
    	//var_dump( $datasource );
    	//$format = '<textarea class="input-'.$size.' " id="'.$id.'_'.$column.'" name="'.$id.'_'.$column.'" rows="'.$rows.'" cols="'.$cols.'" 0>'.$value.'</textarea>';
    	return $format;
    }
    
}

?>