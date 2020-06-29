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
class Factory {
    /**
     * Given the formatter name, returns the formatter object
     *
     * @param string $name
     *            - Formatter name
     * @param array $nodeconfiguration
     * @param Kuink\Core\MessageManager $msg_manager
     * @return mixed
     * @throws \Exception
     */
	static function getFormatter($name, $nodeconfiguration, $msg_manager) {
		try {
            $class = 'Kuink\\UI\\Formatter\\' . $name;
            return new $class ( $nodeconfiguration, $msg_manager );
        } catch (\Throwable $e) {
            throw new \Exception ( 'Formatter ' . $name . ' does not exists.' , 0, $e);
        }


    }

    /**
     * Given the control name, returns the control object
     *
     * @param $type
     * @param array $nodeconfiguration
     * @param $xml_definition
     * @return mixed
     * @throws \Exception
     */
	static function getControl($type, $nodeconfiguration, $xml_definition) {
	    try {
            $class = 'Kuink\\UI\\Control\\' . $type;
            return new $class ( $nodeconfiguration, $xml_definition );
        } catch (\Throwable $e) {
            throw new \Exception ( 'Cannot create ' . $type . ' control object.' , 0, $e);
        }
	}

    /**
     * Given the library name, returns the Library object
     *
     * @param string $name
     *            - Library name
     * @param array $nodeconfiguration
     * @param Kuink\Core\MessageManager $msg_manager
     * @return mixed
     */
	static function getLibrary($name, $nodeconfiguration, $msg_manager) {
	    try {
            $class = 'Kuink\\Core\\Lib\\' . $name;
            return new $class ( $nodeconfiguration, $msg_manager );
        } catch (\Throwable $e) {
            throw new \Exception ( 'Cannot create ' . $name . ' lib.' , 0, $e);
        }

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
		// TODO:Create object of the layoutElement given it's type
		return;
	}
	static function getDataSourceConnector($type, $dataSource) {
		$class = '\\Kuink\\Core\\DataSourceConnector\\' . $type;
		return new $class ( $dataSource );
	}
	static function getInstruction($name, $runtime) {
	    try {
            $class = '\\Kuink\\Core\\Instruction\\' . $name . 'Instruction';
            return new $class ( $runtime );
        } catch (\Throwable $e) {
            throw new \Exception ( 'Cannot create ' . $name . ' lib.' , 0, $e);
        }
	}
}