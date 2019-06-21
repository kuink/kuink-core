<?php

namespace Kuink\Core;

/**
 * Replace the current DataSource class
 *
 * @author paulo.tavares
 */
class DataSourceClass {
	var $name;
	var $bypass; // Every call bypasses the conection to the server
	var $params;
	var $context;
	var $connector; // the connector object
	var $transactionStarted;
	var $user; //current user making the request
	
	function __construct($name, $connector, $context, $params = null, $bypass = 0) {
		$this->name = $name;
		$this->params = $params;
		$this->context = $context;
		$this->bypass = $bypass;
		$this->transactionStarted = 0;
		
		$this->connector = Factory::getDataSourceConnector ( $connector, $this );
	}
  function setUser($user) {
  	$this->user = $user;
  	$this->connector->setUser($user);
  }
	function getParam($paramName, $required, $default = '') {
		if ($required && ! isset ( $this->params [$paramName] ))
			throw new \Exception ( "DataSource $this->name requires parameter $paramName" );
		
		return isset ( $this->params [$paramName] ) ? $this->params [$paramName] : $default;
	}
	function beginTransaction() {
		$this->connector->beginTransaction ();
		return;
	}
	function commitTransaction() {
		$this->connector->commitTransaction ();
		return;
	}
	function rollbackTransaction() {
		$this->connector->rollbackTransaction ();
		return;
	}
	
	/**
	 *
	 * @param type $dataAccessNid
	 *        	- the dataaccess xml definition app,process,object this,this,object defaults to app,process,object of the current app and process
	 * @param type $appName
	 *        	- current application name
	 * @param type $processName
	 *        	- current process name
	 * @param type $dataSourceName
	 *        	- name of the $KUINK_DATASOURCES
	 * @param type $params
	 *        	- params given to the call
	 */
	function execute($dataAccessNid, $appName, $processName, $dataSourceName = '', $params = null) {
		// Executes the datasource;
	}
}

?>

