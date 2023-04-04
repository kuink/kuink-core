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
 * Handles all opening stuff
 * 
 * @author ptavares
 *        
 */
class ApplicationManager {
	var $applications;
	function __construct() {
		$this->applications = array ();
	}
	
	/**
	 * *
	 * Load all application data from database
	 */
	public function load() {
		$da = new DataAccess ( 'getAll', '', '' );
		$da->setCache(\Kuink\Core\CacheType::SESSION, 'core/application::getAll');		
		$params = array ();
		$params ['_entity'] = 'fw_application';
		$apps = $da->execute ( $params );
	
		foreach ( $apps as $app )
			$this->applications [$app ['code']] = $app;
	}
	public function getApplicationBase($application) {
		return $this->getApplicationAttribute ( $application, 'app_base' );
	}
	public function applicationExists($application) {
		return isset ( $this->applications [$application] );
	}
	public function getApplicationAttribute($application, $attribute) {
		// if (!isset($this->applications[$application]))
		// throw new \Exception('Application '.$application.' not registered in fw_application');
		return isset($this->applications [$application] [$attribute]) ? $this->applications [$application] [$attribute] : null;
	}
}

?>