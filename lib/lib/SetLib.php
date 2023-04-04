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
class SetLib {
	var $nodeconfiguration;
	var $msg_manager;
	function __construct($nodeconfiguration=null, $msg_manager=null) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	
	/**
	 * Inserts a value in a flatSet (string containing a list of values like 1,2,5,6)
	 * 
	 * @param unknown_type $params
	 *        	(flatSet, delimiter, value to insert)
	 * @throws Exception
	 * @return string
	 */
	function flatSetInsert($params) {
		if (count ( $params ) != 3)
			throw new Exception ( __METHOD__ . ' must have three parameters' );
		
		$flatSet = ( string ) $params [0];
		$separator = ( string ) $params [1];
		$value = ( string ) $params [2];
		
		// kuink_mydebug('Insert', ':'.$flatSet.':'.' => '.$value);
		
		if (empty ( $flatSet ))
			$set = array ();
		else
			$set = explode ( $separator, $flatSet );
		
		$found = false;
		foreach ( $set as $setKey => $setValue )
			$found = $found || ($value == $setValue);
			// var_dump($found);
		if (! $found) {
			$set [] = $value;
			$flatSet = implode ( $separator, $set );
		}
		
		// kuink_mydebug('Insert', ':'.$flatSet);
		
		return $flatSet;
	}
	
	/**
	 * Removes a value in a flatSet (string containing a list of values like 1,2,5,6)
	 * 
	 * @param unknown_type $params
	 *        	(flatSet, delimiter, value to insert)
	 * @throws Exception
	 * @return string
	 */
	function flatSetRemove($params) {
		if (count ( $params ) != 3)
			throw new Exception ( __METHOD__ . ' must have three parameters' );
		
		$flatSet = ( string ) $params [0];
		$separator = ( string ) $params [1];
		$value = ( string ) $params [2];
		
		// kuink_mydebug('Remove', ':'.$flatSet.':'.'.'.$separator.'.'.$value);
		
		if (empty ( $flatSet ))
			return $flatSet;
		else
			$set = explode ( $separator, $flatSet );
		
		$found = false;
		foreach ( $set as $setKey => $setValue )
			if ($value == $setValue)
				unset ( $set [$setKey] );
		
		$flatSet = implode ( $separator, $set );
		
		// kuink_mydebug('Remove', ':'.$flatSet);
		
		return $flatSet;
	}
	
	/**
	 * Checks if value exists in array
	 * 
	 * @param unknown_type $params
	 *        	(array, value)
	 * @throws Exception
	 * @return boolean
	 */
	function ValueIn($params) {
		if (count ( $params ) != 2)
			throw new Exception ( 'ValueIn must have two parameters that specifies the array and the value to check in for. ' );
		
		$result = 0;
		foreach ( $params [0] as $key => $value ) {
			if ($value == $params [1]) {
				$result = 1;
			}
		}
		return $result;
	}
	
	/**
	 * Checks if key exists in array
	 * 
	 * @param unknown_type $params
	 *        	(array, key)
	 * @throws Exception
	 * @return boolean
	 */
	function KeyIn($params) {
		if (count ( $params ) != 2)
			throw new Exception ( 'ValueIn must have two parameters that specifies the array and the key to check in for. ' );
		
		$result = 0;
		foreach ( $params [0] as $key => $value ) {
			if ($key == $params [1]) {
				$result = 1;
			}
		}
		return $result; // return array_key_exists($params[1], $params[0]);
	}

	/**
	 * Checks if array is multidimentional
	 * @param unknown_type $params (array)
	 * @throws Exception
	 * @return boolean
	 */
	function is_multi_array( $params ) {
		rsort( $params[0] );
		return isset( $params[0][0] ) && is_array( $params[0][0] );
	}
	

	/**
	 * sort array by value(s) from key(s)
	 * 
	 * @param unknown_type $params
	 *        	(array, key(s))
	 * @throws Exception
	 * @return string
	 */
	function SortBy($params) {
		if (count ( $params ) < 2)
			throw new Exception ( 'SortBy must have two or more parameters that specifies the array and the key(s) of the value(s) to sort by. ' );
		
		$array = $params [0];
		$keys = array_slice ( $params, 1 );
		//print_r($array);
		
		usort ( $array, function ($a, $b) use ($keys) {
			$encode_chars_array = array (
					"Á" => "A",
					"À" => "A",
					"Ã" => "A",
					"Â" => "A",
					"á" => "a",
					"à" => "a",
					"ã" => "a",
					"â" => "a",
					"É" => "E",
					"È" => "E",
					"Ẽ" => "E",
					"Ê" => "E",
					"é" => "e",
					"è" => "e",
					"ẽ" => "e",
					"ê" => "e",
					"Í" => "I",
					"Ì" => "I",
					"Ĩ" => "I",
					"Î" => "I",
					"í" => "i",
					"ì" => "i",
					"ĩ" => "i",
					"î" => "i",
					"Ó" => "O",
					"Ò" => "O",
					"Õ" => "O",
					"Ô" => "O",
					"ó" => "o",
					"ò" => "o",
					"õ" => "o",
					"ô" => "o",
					"Ú" => "U",
					"Ù" => "U",
					"Ũ" => "U",
					"Û" => "U",
					"ú" => "u",
					"ù" => "u",
					"ũ" => "u",
					"û" => "u",
					"Ç" => "C",
					"ç" => "c",
					".ª" => "a",
					".º" => "o" 
			);
			foreach ($keys as $field) {
				$arrayA = strtr($a[$field], $encode_chars_array);
				$arrayB = strtr($b[$field], $encode_chars_array);
				$arrayA = is_string($arrayA) ? strtolower($arrayA) : $arrayA;
				$arrayB = is_string($arrayB) ? strtolower($arrayB) : $arrayB;
				$diff = strnatcmp($arrayA, $arrayB); //strnatcasecmp($a[$field], $b[$field]);
				if($diff != 0) {
						return $diff;
				}
			}
			
			return 0;
		} );
		
		return $array;
	}

	/**
	 * 
	 */
	function sortByExtended($params) {
		if (count ( $params ) < 2)
			throw new Exception ( 'sortByExtended must have two or more parameters that specifies the array and the key(s) of the value(s) to sort by. ' );

		$array = $params [0];
		$keys = array_slice ( $params, 1 );
		
		$sortParams = Array();
		foreach($keys as $key) {
			$column = array_column($array, $key['key']);
			$sortParams[] = $column;
			
			if ($key['sort'] == 'asc') 
				$sort = SORT_ASC;
			else
				$sort = SORT_DESC;

			if ($key['type'] == 'string') 
				$type = SORT_LOCALE_STRING | SORT_FLAG_CASE;
			else
				$type = SORT_NUMERIC;

			$sortParams[] = $sort;				
			$sortParams[] = $type;				
			//kuink_mydebug('Sort:', $key['key'].' | '.$key['type'].' | '.$key['sort'].' | '. $sort);
		}	
		$sortParams[] = &$array;
		call_user_func_array('array_multisort', $sortParams);

		return $array;
	}

	function orderColumns($params) {
		
		if (count ( $params ) != 3)
			throw new Exception ( 'orderColumns must have three parameters that specifies the array and ordered keys array and the column name toretrieve the value from the ordered keys arrays. ' );
		$array = $params [0];
		$order = $params [1];
		$orderColumn = (string)$params [2];

		$orderParsed = array();
		foreach ($order as $orderField)
			$orderParsed[]=$orderField[$orderColumn];

		$orderedArray = array();
		foreach ($array as $row) {
			//First place the fields without specific order
			$orderedField = array();	
			foreach ($row as $fieldKey=>$fieldValue) {
				if (!in_array(trim($fieldKey), $orderParsed)) {
					//kuink_mydebug($fieldKey);
					$orderedField[$fieldKey] = $fieldValue;		
				}
			}
			//Now place the ordered keys
			foreach ($orderParsed as $orderField)
				if (isset($row[$orderField])) {
					//kuink_mydebug('Setting: '.$orderField);
					$orderedField[$orderField] = $row[$orderField];		
				}
			$orderedArray[] = $orderedField;
		}

		return $orderedArray;
	}	
	
	/**
	 * insert key and/or value(s) into array
	 * 
	 * @param unknown_type $params
	 *        	(typeOfAction, array, key and/or value(s))
	 * @throws Exception
	 * @return string
	 */
	function InsertInto($params) {
		if (! count ( $params ) < 3) {
			$typeOfAction = $params [0];
			if (is_array ( $params [1] ))
				$array = $params [1];
			else
				$array = [ ];
			if ($typeOfAction == 0) {
				foreach ( array_slice ( $params, 2 ) as $key => $value ) {
					array_push ( $array, $value );
				}
			} else if ($typeOfAction == 1) {
				if (count ( $params ) != 4)
					throw new Exception ( 'insertInto must have 4 parameters that specifies the type of action [1], array, key and value to insert in. ' );
				
				$key = $params [2];
				$value = $params [3];
				
				$array [$key] [] = $value;
			} else {
				throw new Exception ( 'insertInto must have a valid type of action: [0->insert value(s)] , [1->insert pair key, value]' );
			}
		} else {
			throw new Exception ( 'insertInto must have at least three parameters that specifies the type of action: [0->insert value(s)] - [1->insert pair key, value], array, key and/or value(s). ' );
		}
		return $array;
	}
	
	/**
	 * remove element from array
	 * 
	 * @param unknown_type $params
	 *        	(array, key)
	 * @throws Exception
	 * @return string
	 */
	function RemoveFrom($params) {
		if (count ( $params ) != 2)
			throw new Exception ( 'removeFrom must have two parameters that specifies the array and the key to remove. ' );
		
		$array = $params [0];
		$key = $params [1];
		
		unset ( $array [$key] );
		
		return $array;
	}
	function flatSetExplode($params) {
		if (count ( $params ) != 2)
			throw new Exception ( __METHOD__ . ' must have two parameters' );
		
		$flatSet = ( string ) $params [0];
		$separator = ( string ) $params [1];
		
		// kuink_mydebug('Remove', ':'.$flatSet.':'.'.'.$separator.'.'.$value);
		
		if (empty ( $flatSet ))
			return null;
		else
			$set = explode ( $separator, $flatSet );
		
		return $set;
	}
	
	/**
	 * paginate an array
	 * 
	 * @param unknown_type $params
	 *        	(array, pagenum, pagesize)
	 * @throws Exception
	 * @return string
	 */
	function paginate( $params )
	{
		if (count($params) != 3)
			throw new Exception(__METHOD__.' must have two parameters');
		$array = is_array($params["array"][0]) == 1 ? $params["array"] : array($params["array"]);
		$pagenum = $params["pagenum"];
		$pagesize = $params["pagesize"];
		$result = array();
		$result['records'] = array_slice($array, ($pagenum*$pagesize), $pagesize);
		$result['total'] = count($array);

		return $result;
	}


    /**
    * Join two sets into one.
    * ==== MORE COMMENT ===
    **/
    
    function merge($params){
      $one = $params[0];
      $two = $params[1];
      $merged = array_merge($one, $two);
      
      return $merged;
    }

    function reverse($params){
      $set = $params[0];
      $reverse = array_reverse($set);
      
      return $reverse;
    }

}

?>
