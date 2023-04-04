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
 * DataSourceMultiEntityConnector is a generic connector for accessing datasources in which entities have different handlers
 *
 * @author paulo.tavares
 */
class DataSourceMultiEntityConnector extends \Kuink\Core\DataSourceConnector{
	/*
		$configEntityHandlers will contain the name of the class that will handle each of the entities
		$this->configEntityHandlers = [
			'user'  => "\Kuink\Core\DataSourceConnector\myConnectorUserHandler",
			'group' => "\Kuink\Core\DataSourceConnector\myConnectorGroupHandler",
			'xpto'  => "\Kuink\Core\DataSourceConnector\myConnectorXptoHandler"
		];
	*/
	var $configEntityHandlers;
	
	function __construct($dataSource) {
		parent::__construct($dataSource);
	}

	/**
	 * Instantiate all params in order to establish a connection
	 */
	function connect($entity=null) {
		//Setup specific entity connection properties
		if (isset($entity)) {
			$handler = $this->getEntityHandler($entity);
			$handler->connect();
		}
	}

	/**
	 * Loads an entity record
	 */
	function load($params, $operators=null) {
		//From what entity do we want to load the record
		$entity = (string) $this->getParam ( $params, '_entity', true); 

		//Connect to the datasource
		$this->connect($entity);

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the melthod from the handler
		$result = $handler->load($params, $operators);

		//Send the result back to the caller
		return $result;
	}

	/**
	 * Insert an entity record
	 */
	function insert($params) {
		//In which entity do we want to insert the record
		$entity = (string) $this->getParam ( $params, '_entity', true);

		//Connect to the datasource
		$this->connect($entity);

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the melthod from the handler
		$result = $handler->insert($params);

		//Send the result back to the caller
		return $result;
	}	

	/**
	 * Update an entity record
	 */
	function update($params) {
		//From what entity do we want to update the record
		$entity = (string) $this->getParam ( $params, '_entity', true);

		//Connect to the datasource
		$this->connect($entity);

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the melthod from the handler
		$result = $handler->update($params);

		//Send the result back to the caller
		return $result;
	}	

	/**
	 * Deletes an entity record
	 */
	function delete($params) {
		//From what entity do we want to update the record
		$entity = (string) $this->getParam ( $params, '_entity', true);

		//Connect to the datasource
		$this->connect($entity);

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the method from the handler
		$result = $handler->delete($params);

		//Send the result back to the caller
		return $result;
	}	

	/**
	 * Get all records of an entity
	 */
	function getAll($params, $operators=null) {
		//From what entity do we want to update the record
		$entity = (string) $this->getParam ( $params, '_entity', true);

		//Connect to the datasource
		$this->connect($entity);

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the method from the handler
		$result = $handler->getAll($params, $operators);

		//Send the result back to the caller
		return $result;
	}

	/**
	 * Execute a custom method of an entity
	 */
	function execute($params, $operators=null) {
		//From what entity do we want to update the record
		$entity = (string) $this->getParam ( $params, '_entity', true);

		//From what entity do we want to update the record
		$method = (string) $this->getParam ( $params, '_method', true);

		//Connect to the datasource
		$this->connect($entity);

		//Get the handler, because this connector can handle many different entities different from each other
		$handler = $this->getEntityHandler($entity);

		//Call the method from the handler
		$result = $handler->$method($params, $operators);

		//Send the result back to the caller
		return $result;
	}

	/**
	 * Get the schema name
	 */
	public function getSchemaName($params) {
  	return null;
  }
	
	/**
	 * Auxiliary function to convert an object to an array
	 */
  public function object_to_array($obj) {
	  $arrObj = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
	  $arr=array();
    foreach ( $arrObj as $key => $val ) {
      $val = (is_array ( $val ) || is_object ( $val )) ? $this->object_to_array ( $val ) : $val;
      $arr [$key] = $val;
    }
    return $arr;
  }

	/**
	 * Get the entity Service Object handler
	 */
	protected function getEntityHandler($entity) {
		if (!isset($this->configEntityHandlers[$entity]))
			throw new \Exception(__CLASS__.': Invalid entity handler for entity:'. $entity);

		$handler = $this->configEntityHandlers[$entity];
		
		return new $handler($this);
	}

}
