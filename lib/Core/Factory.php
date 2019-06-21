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

/**
 * Factory to return an instance of an object by it's name
 * 
 * @author ptavares
 *        
 */
use Kuink\UI\Layout\NLayout;
use Kuink\UI\Layout\LayoutTemplate;

class Factory {
	/**
	 * Given the formatter name, returns the formatter object
	 * 
	 * @param string $name
	 *        	- Formatter name
	 * @param array $nodeconfiguration        	
	 * @param Kuink\Core\MessageManager $msg_manager        	
	 */
	static function getFormatter($name, $nodeconfiguration, $msg_manager) {
		global $KUINK_INCLUDE_PATH;
		if (file_exists ( $KUINK_INCLUDE_PATH . 'lib/ui/formatter/' . $name . '.php' )) {
			require_once ($KUINK_INCLUDE_PATH . 'lib/ui/formatter/' . $name . '.php');
			$class = 'Kuink\\UI\\Formatter\\' . $name;
			return new $class ( $nodeconfiguration, $msg_manager );
		} else
			throw new \Exception ( 'Formatter ' . $name . ' does not exists.' );
	}
	
	/**
	 * Given the control name, returns the control object
	 * 
	 * @param string $name
	 *        	- control name
	 * @param array $nodeconfiguration        	
	 * @param Neuton\Core\MessageManager $msg_manager        	
	 */
	static function getControl($type, $nodeconfiguration, $xml_definition) {
		global $KUINK_INCLUDE_PATH;
		$controlFile = $KUINK_INCLUDE_PATH . 'lib/ui/control/' . $type . '.php';
		if (file_exists ( $controlFile )) {
			require_once ($controlFile);
			$class = 'Kuink\\UI\\Control\\' . $type;
			return new $class ( $nodeconfiguration, $xml_definition );
		} else
			throw new \Exception ( 'Cannot create ' . $type . ' control object.' );
	}
	
	/**
	 * Given the library name, returns the Library object
	 * 
	 * @param string $name
	 *        	- Library name
	 * @param array $nodeconfiguration        	
	 * @param Kuink\Core\MessageManager $msg_manager        	
	 */
	static function getLibrary($name, $nodeconfiguration, $msg_manager) {
		global $KUINK_INCLUDE_PATH;
		require_once ($KUINK_INCLUDE_PATH . 'lib/lib/' . $name . '.php');
		$class = 'Kuink\\Core\\Library\\' . $name;
		return new $class ( $nodeconfiguration, $msg_manager );
	}

    /**
     *
     * @param string $type
     * @return mixed
     */
	static function getLayoutAdapter(string $type) {
		$class = 'Kuink\\UI\\Layout\\Adapter\\' . $type;
		return new $class ( Configuration::getInstance()->theme->name);
	}
	
	/**
	 *
	 * @param unknown_type $type        	
	 */
	static function getLayoutElement($type) {
		// TODO STI:Create object of the layoutElement given it's type
		return;
	}
	static function getDataSourceConnector($type, $dataSource) {
		global $KUINK_INCLUDE_PATH;

		$class = '\\Kuink\\Core\\DataSourceConnector\\' . $type;
		return new $class ( $dataSource );
	}
	static function getInstruction($name, $runtime) {
		global $KUINK_INCLUDE_PATH;
		
		require_once ($KUINK_INCLUDE_PATH . 'lib/instruction/' . $name . 'Instruction.php');
		$class = '\\Kuink\\Core\\Instruction\\' . $name . 'Instruction';
		return new $class ( $runtime );
	}
}

?>