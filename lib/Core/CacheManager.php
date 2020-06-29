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


namespace Kuink\Core;

class CacheType {
	const NONE = 0;
	const REQUEST = 1;
	const SESSION = 2;
	const APPLICATION = 3;
}

class CacheManager {
	
	private static $instance;
	private $cache;
	
	public function __construct(){
		$cache = array();
	}
    
	public static function getInstance(){
		if (!self::$instance)
			self::$instance = new \Kuink\Core\CacheManager();
		return self::$instance;
	}

	public function exists($key, $cacheType){
		switch ($cacheType) {
			case \Kuink\Core\CacheType::NONE: break;
			case \Kuink\Core\CacheType::REQUEST:
				return isset($this->cache[$key]);
				break;
			case \Kuink\Core\CacheType::SESSION:
			case \Kuink\Core\CacheType::APPLICATION:
				$value = ProcessOrchestrator::getSessionVariable ( '_CACHE', $key );
				return isset($value);
				break;
		}
		return false;
	}


	public function add($key, $object, $cacheType = \Kuink\Core\CacheType::NONE){
		switch ($cacheType) {
			case \Kuink\Core\CacheType::NONE: break;
			case \Kuink\Core\CacheType::REQUEST:
				$this->cache[$key] = $object;
				break;
			case \Kuink\Core\CacheType::SESSION:
			case \Kuink\Core\CacheType::APPLICATION:
				ProcessOrchestrator::setSessionVariable('_CACHE', $key, $object);
				break;
		}
	}

	public function get($key, $cacheType = \Kuink\Core\CacheType::NONE){
		switch ($cacheType) {
			case \Kuink\Core\CacheType::NONE: break;
			case \Kuink\Core\CacheType::REQUEST:
				return ($this->cache[$key]);
				break;
			case \Kuink\Core\CacheType::SESSION:
			case \Kuink\Core\CacheType::APPLICATION:
				return ProcessOrchestrator::getSessionVariable('_CACHE', $key);
				break;
		}
	}

	public function remove($key, $object, $cacheType = \Kuink\Core\CacheType::NONE){
		switch ($cacheType) {
			case \Kuink\Core\CacheType::NONE: break;
			case \Kuink\Core\CacheType::REQUEST:
				unset($this->cache[$key]);
				break;
			case \Kuink\Core\CacheType::SESSION:
			case \Kuink\Core\CacheType::APPLICATION:
				ProcessOrchestrator::unsetSessionVariable('_CACHE', $key);
				break;
		}
	}

	public function getCache(){
		return $this->cache;
	}

}

?>