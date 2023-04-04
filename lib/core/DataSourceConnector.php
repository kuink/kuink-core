<?php

namespace Kuink\Core;

/**
 * Interface used to create custom handlers for entities
 */
interface ConnectorEntityHandlerInterface {
	/**
	 * Setup entity specific connection properties
	 */
	public function connect();

	/**
	 * Loads a record from an entity
	 */
	public function load($params, $operators);

	/**
	 * Inserts a record in an entity
	 */
	public function insert($params);

	/**
	 * Updates a record in an entity
	 */
	public function update($params);

	/**
	 * Inserts or Updates a record in an entity
	 */
	public function save($params);

	/**
	 * Deletes a record from an entity
	 */
	public function delete($params);

	/**
	 * Get all records from an entity
	 */
	public function getAll($params, $operators);
}

/**
 * Implemented by every connector that can authenticate users
 */
interface ConnectorAuthenticationInterface {
	/**
	 * This will check if the user and password match to a valid login
	 */
	public function checkLogin($username, $password);

	/**
	 * Get an user object
	 */
	public function getUser($username);
}


/**
 * Replace the current DataSource class
 *
 * @author paulo.tavares
 */
abstract class DataSourceConnector {
	var $dataSource;
	var $transactionStarted;
	var $user; //The user performing this request	
	function __construct($dataSource) {
		$this->dataSource = $dataSource;

	}
	function getParam($params, $paramName, $paramRequired = false, $paramDefault = '') {
		if (! isset ( $params [$paramName] ) && $paramRequired)
			throw new \Exception ( 'Param name ' . $paramName . ' is required ' . __CLASS__ );
		
		return (isset ( $params [$paramName] ) ? $params [$paramName] : $paramDefault);
	}

	abstract function connect();
	abstract function insert($params);
	abstract function update($params);
	abstract function delete($params);
	abstract function load($params);
	abstract function getSchemaName($params);
  function setUser($user) {
  	$this->user = $user;
  }
	function beginTransaction() {
		$this->transactionStarted = 1;
		return;
	}
	function commitTransaction() {
		$this->transactionStarted = 0;
		return;
	}
	function rollbackTransaction() {
		$this->transactionStarted = 0;
		return;
	}
}

?>
