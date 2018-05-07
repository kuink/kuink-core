<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

use Kuink\Core\TraceManager;
use Kuink\Core\TraceCategory;

/**
 * Description of XmlConnector
 *
 * @author paulo.tavares
 */
class OfficeConnector extends \Kuink\Core\DataSourceConnector {
	var $conn;
	var $fileName;
	var $idFile;
	function connect() {
		global $KUINK_CFG;
		
		if (! $this->conn) {
			$this->conn = new \clsTinyButStrong (); // new instance of TBS
			$this->conn->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin
			
			$this->fileName = ( string ) $this->dataSource->getParam ( 'filename', false, '' );
			$this->idFile = ( string ) $this->dataSource->getParam ( 'idFile', false, '' );
			
			if ($this->fileName == '' && $this->idFile == '')
				throw new \Exception ( 'Either idFle or filename must be supplied ' );
			
			if ($this->fileName != '')
				$template = $this->fileName;
			else {
				// Loading from id_file
				$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
				$params ['_entity'] = 'fw_file';
				$params ['id'] = $this->idFile;
				$fileRecord = $dataAccess->execute ( $params );
				
				if (count ( $fileRecord ) == 0)
					throw new \Exception ( 'Invalid file ' . $this->idFile );
				$template = $KUINK_CFG->dataRoot . '/' . $fileRecord ['path'] . '/' . $fileRecord ['name'];
			}
			
			TraceManager::add ( 'Loading template:' . $template, TraceCategory::CONNECTOR, __CLASS__ );
			
			$this->conn->LoadTemplate ( $template, OPENTBS_ALREADY_UTF8 ); // Also merge some [onload] automatic fields (depends of the type of document).
		}
	}
	function insert($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}
	function update($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}
	function delete($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}
	function load($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}
	function getAll($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}
	
	/**
	 * Set a property in the document
	 * 
	 * @param array $params        	
	 */
	function setProperty($params) {
		$this->connect ();
		
		// var_dump($this->conn);
		foreach ( $params as $propName => $propValue )
			$this->conn->PlugIn ( OPENTBS_ADD_CREDIT, $propValue, $propName );
		return '';
	}
	
	function setText($params) {
		$this->connect ();
		
		$block = array();
		foreach ( $params as $propName => $propValue )
			$block[$propName]= $propValue;
		$data = array();
		$data[] = $block;
		$this->conn->MergeBlock('document', $data);
		return '';
	}	
	
	/**
	 * Save the file with changed data
	 * 
	 * @param array $params        	
	 */
	function save($params) {
		global $KUINK_CFG;
		$this->connect ();
		
		$fileName = ( string ) $this->getParam ( $params, 'filename', false, '' );
		
		if ($fileName != '')
			$outputFileName = $fileName;
		else if ($this->fileName != '')
			$outputFileName = $this->fileName;
		else {
			// Loading from id_file
			$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'config' );
			$params ['_entity'] = 'fw_file';
			$params ['id'] = $this->idFile;
			$fileRecord = $dataAccess->execute ( $params );
			
			if (count ( $fileRecord ) == 0)
				throw new \Exception ( 'Invalid file ' . $this->idFile );
			$newName = str_replace ( '.', date ( 'Y-m-d-h-i' ), $fileRecord ['name'] );
			$outputFileName = $KUINK_CFG->dataRoot . '/' . $fileRecord ['path'] . '/' . $newName;
			$originalFileName = $KUINK_CFG->dataRoot . '/' . $fileRecord ['path'] . '/' . $fileRecord ['name'];
			
			// save the file with a different name, delete the previous file, replace the file with the updated one
			$this->conn->Show ( OPENTBS_FILE, $outputFileName ); // Also merges all [onshow] automatic fields.
			unlink ( $originalFileName );
			rename ( $outputFileName, $originalFileName );
			
			return;
		}
		
		TraceManager::add ( 'Saving file:' . $outputFileName, TraceCategory::CONNECTOR, __CLASS__ );
		$this->conn->Show ( OPENTBS_FILE, $outputFileName ); // Also merges all [onshow] automatic fields.
	}
	
	/**
	 * Streams the file out
	 * 
	 * @param array $params        	
	 */
	function stream($params) {
		$this->connect ();
		
		$outputFileName = str_replace ( '.', '_' . date ( 'Y-m-d' ) . $save_as . '.', $template );
		
		TraceManager::add ( 'Streaming file:' . $outputFileName, TraceCategory::CONNECTOR, __CLASS__ );
		ob_clean ();
		$this->conn->Show ( OPENTBS_DOWNLOAD, $outputFileName ); // Also merges all [onshow] automatic fields.
		exit ();
	}

	public function getSchemaName($params) {
		return null;
	}
}

?>
