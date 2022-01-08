<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of AppFileConnector
 *
 * @author catarina.fernandes
 */
class AppFileConnector extends \Kuink\Core\DataSourceConnector {
  var $dir;
  var $type;   //To hold the config value of type from the datasource xml
  var $sample; //The object holding the connection

  function connect( ) {
    global $KUINK_CFG;
    $this->type = $this->dataSource->getParam ('type', true ); //Getting datasource param type (true->required; false->notRequired)
    $this->dir = $KUINK_CFG->uploadRoot. 'app_files';

    //Usually want to connect only once per request
    if (!$this->sample) {
      $this->sample = 'Sample object'; //Usually an instance of a class new \Class();

      \Kuink\Core\TraceManager::add( 'Connecting to the datasource type: ' . $this->type, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);  	
    }
  }
  
  /**
   * Inserts a record in a datasource using this connector
   * @param    array  $params The params that are passed to insert an entity record
   */
  function insert($params) {
    $this->connect();

    $entity = (string)$this->getParam($params, '_entity', true);
    $id = (string)$this->getParam($params, 'id', false);
    \Kuink\Core\TraceManager::add('Inserting a value on entity: ' . $entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);

    foreach( $_FILES as $type => $file ) {
      if($file ['error'] != 0) {
        throw new \Exception('Erro a fazer upload do ficheiro (' . $file ['error'] . ').');
      }
      else if($file ['size'] == 0) {
        $this->msg_manager->add(\Kuink\Core\MessageType::ERROR, 'Ficheiro vazio.' );
      }
      else {
        $path = $this->dir . '/' . $entity . '/' . $file['name'];
        move_uploaded_file($file['tmp_name'], $path);
      }
    }
  }

  /**
   * Updates a record in a datasource using this connector
   * @param    array  $params The params that are passed to update an entity record
   */
  function update($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true);
    \Kuink\Core\TraceManager::add( 'Updating a value on entity'.$entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);  	
  }  

  /**
   * Deletes a record in a datasource using this connector
   * @param    array  $params The params that are passed to delete an entity record
   */
  function delete($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true);
    $id = (string)$this->getParam($params, 'id', true);
    \Kuink\Core\TraceManager::add( 'Deleting id: ' . $id . '  on entity: ' . $entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);  
    
    $path = $this->dir . '/' . $entity . '/' . $id;

    unlink($path);
  }
  
  /**
   * Loads a record from a datasource using this connector
   * @param    array  $params The params that are passed to load an entity record
   */
  function load($params) {
    $this->connect();

    $entity = (string)$this->getParam($params, '_entity', true); 
    $id = (string)$this->getParam($params, 'id', true);
    \Kuink\Core\TraceManager::add( 'Loading id: ' . $id . '  on entity: ' . $entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);

    $path = $this->dir . '/' . $entity . '/' . $id;
    $info = $this->setFileInfo($path);

    //Returns an array with the file info
    return $info;
  }
  
  /**
   * Get all records from a datasource using this connector
   * @param    array  $params The params that are passed to get all records of an entity
   */
  function getAll($params) {
  	$this->connect();

    $entity = (string)$this->getParam($params, '_entity', true);
    \Kuink\Core\TraceManager::add( 'Getting all records from entity: ' . $entity, \Kuink\Core\TraceCategory::CONNECTOR, __CLASS__);

    $folder = $this->dir . '/' . $entity;
    $files = array_values(array_diff(scandir($folder), array('..', '.')));
    $result = array();

    foreach($files as $file) {
      $params['id'] = $file;
      $result[] = $this->load($params);
    
    }
    return $result;
  }

  /**
   * If this datasource have more than one schema then get it
   * For instance in a database server this could return the database name 
   * @param    array  $params The params that are passed to get all records of an entity
   */
	public function getSchemaName($params) {
  	return null;
  }


  /**
    * @param    path the path to the file
    */
  private function setFileInfo($path){
    $info = array();
    $pathInfo = pathinfo($path);
    $stat = stat($path);
    $info['realpath'] = realpath($path);

    if($info['realpath'] == false) {
      $info = array();
    }
    else {
      $info['id'] = $pathInfo['basename'];
      $info['dirname'] = $pathInfo['dirname'];
      $info['basename'] = $pathInfo['basename'];
      $info['filename'] = $pathInfo['filename'];
      $info['extension'] = $pathInfo['extension'];
      $info['mime'] = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
      $info['encoding'] = finfo_file(finfo_open(FILEINFO_MIME_ENCODING), $path);
      $info['size'] = $stat[7];
      $info['atime'] = $stat[8];
      $info['mtime'] = $stat[9];
      $info['permission'] = substr(sprintf('%o', fileperms($path)), -4);
      $info['fileowner'] = getenv('USERNAME');
    }

    return $info;
  }
}

?>
