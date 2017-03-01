<?php

namespace Kuink\Core;

/**
 * Replace the current DataSource class
 *
 * @author paulo.tavares
 */
abstract class DataSourceConnector {
  var $dataSource;
  var $transactionStarted;
  
  function __construct($dataSource) {
    $this->dataSource = $dataSource;
  }

  function getParam($params, $paramName, $paramRequired = false, $paramDefault = '') {
    if (!isset( $params[$paramName] ) && $paramRequired)
      throw new \Exception('Param name '.$paramName.' is required '. __CLASS__);
    
    return(isset($params[$paramName]) ? $params[$paramName] : $paramDefault);
  }
  
  abstract function connect();
  
  abstract function insert($params);
  
  abstract function update($params);
  
  abstract function delete($params);
  
  abstract function load($params);

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
