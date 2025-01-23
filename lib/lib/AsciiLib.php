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
class AsciiLib {
	var $nodeconfiguration;
	var $msg_manager;
	function __construct($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function createTable($params) {
		$tableData = $params [0];
		$tableHeaders = (isset ( $params [1] )) ? $params [1] : null;
		
		// Either simple
		$arrayKeys = array ();
		if (! isset ( $params [1] )) {
			foreach ( array_pop ( $tableData ) as $key => $columnData ) {
				$arrayKeys [] = $key;
			}
		} else {
			foreach ( $params [1] as $key ) {
				$arrayKeys [] = $key;
			}
		}
		
		// default width
		foreach ( $arrayKeys as $key ) {
			$columnWidths [] = strlen ( $key );
		}
		
		// optimize width
		foreach ( $tableData as $row ) {
			$i = 0;
			foreach ( $row as $column ) {
				if (strlen ( $column ) > $columnWidths [$i])
					$columnWidths [$i] = strlen ( $column );
				$i ++;
			}
		}
		
		// echo "<pre>";
		// var_dump($tableData);
		// echo "</pre>";
		
		$table = new Zend\Text\Table\Table ( array (
				'columnWidths' => $columnWidths 
		) );
		
		$table->appendRow ( $arrayKeys );
		$decorator = new Zend\Text\Table\Decorator\Ascii ();
		$table->setDecorator ( $decorator );
		// Or verbose
		foreach ( $tableData as $rowData ) {
			$row = new Zend\Text\Table\Row ();
			foreach ( $rowData as $columnData ) {
				$columnData = (string) $columnData;
				$row->appendColumn ( new Zend\Text\Table\Column ( $columnData ) );
			}
			$table->appendRow ( $row );
		}
		
		return ( string ) $table;
	}
}

?>
