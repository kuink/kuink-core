<?php
/*
 * This file is a sample file for a connector
 * 
 * To use it:
 *    Change the classname
 *    Place it in the parent directory
 * 
 *    Create a datasource for it in either:
 *      - framework.xml (file in the root of kuink-apps for a framework wide datasource)
 *      - fw_datasource (table for company global datasource. It will always be loaded for every request in that company)
 *      - application.xml (file with the application definition to be used in that application context)
 *      - node.xml (inline with the code on a node. For a specific usage of an application)
 * 
 *    Xml Definition to define a datasource using this connector
 * 
 *      <DataSource name="sampleDataSource" connector="SampleConnector">
 *        <Param name="type">SampleType</Param>
 *      </DataSource>
 * 
 *    Example of a data access to load the record with the id "1" from entity "SampleEntity"
 * 
 *      <Var name="record" dump="true">
 *	      <DataAccess method="load" datasource="sampleDataSource">
 *          <Param name="_entity">SampleEntity</Param>
 *          <Param name="id">1</Param>
 * 	      </DataAccess>
 *      </Var>
 * 
 *    Note: In order to access the datasource if we need extra PHP code, like an external library, place
 *          the code in kuink-core/lib/tools directory
 *          Include those libraries in the file kuink-core/bootstrap/autoload 
 *          example: require_once ($KUINK_INCLUDE_PATH . 'lib/tools/zend_libs/autoload.php');
 */


namespace Kuink\Core\DataSourceConnector;

/**
 * Description of SampleConnector
 *
 * @author paulo.tavares
 */
class SampleConnector extends \Kuink\Core\DataSourceConnector{
  var $type;   //To hold the config value of type from the datasource xml
  var $sample; //The object holding the connection

  function connect( ) {
    $this->type = $this->dataSource->getParam ('type', true ); //Getting datasource param type (true->required; false->notRequired)

    //Usually want to connect only once per request
    if (!$this->sample) {
      $this->sample = 'Sample object'; //Usualy an instance of a class new \Class();

      \Kuink\Core\TraceManager::add ( 'Connecting to the datasource type:'.$this->type, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
    }
  }
  
  /**
   * Inserts a record in a datasource using this connector
   * @param    array  $params The params that are passed to insert an entity record
   */
  function insert($params) {
    $this->connect();

    $entity = (string)$this->getParam($params, '_entity', true); //_entity attribute is required
    \Kuink\Core\TraceManager::add ( 'Inserting a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
  }

  /**
   * Updates a record in a datasource using this connector
   * @param    array  $params The params that are passed to update an entity record
   */
  function update($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true); //_entity attribute is required
    \Kuink\Core\TraceManager::add ( 'Updating a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
  }  

  /**
   * Deletes a record in a datasource using this connector
   * @param    array  $params The params that are passed to delete an entity record
   */
  function delete($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true); //_entity attribute is required
    $id = (string)$this->getParam($params, 'id', true); //id attribute is required
    \Kuink\Core\TraceManager::add ( 'Deleting id:'.$id.'  on entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
  }
  
  /**
   * Loads a record from a datasource using this connector
   * @param    array  $params The params that are passed to load an entity record
   */
  function load($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true); //_entity attribute is required
    $id = (string)$this->getParam($params, 'id', true); //id attribute is required
    \Kuink\Core\TraceManager::add ( 'Loading id:'.$id.'  on entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
    
    //Returning a sample array
    return array('id'=>1,'name'=>'Sample 1');
  }
  
  /**
   * Get all records from a datasource using this connector
   * @param    array  $params The params that are passed to get all records of an entity
   */
  function getAll($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true); //_entity attribute is required
    \Kuink\Core\TraceManager::add ( 'Getting all records from entity:'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__ );  	
  }  

  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param    array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaName($params) {
  	return null;
  }
}

?>
