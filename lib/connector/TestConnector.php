<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kuink\Core\DataSourceConnector;

/**
 * Description of TestConnector
 *
 * @author paulo.tavares
 */
class TestConnector extends \Kuink\Core\DataSourceConnector{
  	
  function connect( ) {
  	global $NEON_TRACE;
  	
  }
  
  function insert($params) {
    $this->connect();
  }
  
  function update($params) {
  	$this->connect();
  }  

  function delete($params) {
  	$this->connect();
  }
  
  
  function load($params) {
  	$this->connect();
  	$name = $this->dataSource->getParam('name', false);
  	$params = $this->dataSource->getParam('params', false); 	 
  }
  
  function getAll($params) {
  }  

	public function getSchemaName($params) {
  	return null;
  }
}

?>
