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


class SetLib
{
	var $nodeconfiguration;
	var $msg_manager;
    function  SetLib($nodeconfiguration, $msg_manager) {
    	$this->nodeconfiguration = $nodeconfiguration;
    	$this->msg_manager = $msg_manager;
        return;
    }

    /**
     * Inserts a value in a flatSet (string containing a list of values like 1,2,5,6)
     * @param unknown_type $params (flatSet, delimiter, value to insert)
     * @throws Exception
     * @return string
     */

    function flatSetInsert( $params )
    {
    	if (count($params) != 3)
    		throw new Exception(__METHOD__.' must have three parameters');

    	$flatSet = (string)$params[0];
    	$separator = (string)$params[1];
    	$value = (string)$params[2];

    	//kuink_mydebug('Insert', ':'.$flatSet.':'.' => '.$value);

    	if (empty($flatSet))
    		$set = array();
    	else
    		$set = explode($separator, $flatSet);

    	$found = false;
    	foreach( $set as $setKey => $setValue )
    		$found = $found || ($value == $setValue);
    	//var_dump($found);
    	if (!$found) {
	    	$set[] = $value;
	    	$flatSet = implode($separator, $set);
    	}

    	//kuink_mydebug('Insert', ':'.$flatSet);

    	return $flatSet;
    }

    /**
     * Removes a value in a flatSet (string containing a list of values like 1,2,5,6)
     * @param unknown_type $params (flatSet, delimiter, value to insert)
     * @throws Exception
     * @return string
     */
    function flatSetRemove( $params )
    {
    	if (count($params) != 3)
    		throw new Exception(__METHOD__.' must have three parameters');

    	$flatSet = (string)$params[0];
    	$separator = (string)$params[1];
    	$value = (string)$params[2];

    	//kuink_mydebug('Remove', ':'.$flatSet.':'.'.'.$separator.'.'.$value);

    	if (empty($flatSet))
    		return $flatSet;
    	else
    		$set = explode($separator, $flatSet);

    	$found = false;
    	foreach( $set as $setKey => $setValue )
    		if ($value == $setValue)
    			unset($set[$setKey]);

    	$flatSet = implode($separator, $set);

    	//kuink_mydebug('Remove', ':'.$flatSet);

    	return $flatSet;
    }

		function ValueIn( $params )
		{
			if (count($params) != 2)
    		throw new Exception('ValueIn must have two parameters that specifies the array and the value to check in for. ');

			$result = 0;
			foreach ($params[0] as $key => $value) {
				if ($value == $params[1]) {
					$result = 1;
				}
			}
			return $result;
		}

    function flatSetExplode( $params )
    {
    	if (count($params) != 2)
    		throw new Exception(__METHOD__.' must have two parameters');

    	$flatSet = (string)$params[0];
    	$separator = (string)$params[1];

    	//kuink_mydebug('Remove', ':'.$flatSet.':'.'.'.$separator.'.'.$value);

    	if (empty($flatSet))
    		return null;
    	else
    		$set = explode($separator, $flatSet);

    	return $set;
    }

    /**
    * Join two sets into one.
    * ==== MORE COMMENT ===
    * @TODO STI: Joao Patricio
    **/
    /*
    function joinSets($params){
      $one = $params[0];
      $two = $params[1];
      $common = $params[2];
      var_dump($one);
    }
    */

}

?>
