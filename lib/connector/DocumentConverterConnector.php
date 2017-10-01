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
class DocumentConverterConnector extends \Kuink\Core\DataSourceConnector {
	function connect() {
	}

	function execute($params) {
		global $KUINK_TRACE;
		global $KUINK_CFG;
		global $KUINK_BRIDGE_CFG;
		$this->connect();
		
		$idFile = (int)$this->getParam($params, 'id');
		$path = (string)$this->getParam($params, 'path', false, '');
		$newName = $this->getParam($params, 'newName', false, '');
	
		$format = (string)$this->getParam($params, 'format');
		
		$datasource = new \Kuink\Core\DataSource ( null, 'framework,generic,load', 'framework', 'generic' );
		$file = $datasource->execute ( array (
				'table' => 'fw_file',
				'id' => $idFile
		) );
		
		// full origin path
		$source = $KUINK_CFG->dataRoot . '/' . $file['path'] . '/' . $file['name'];
		$source = str_replace ( '//', '/', $source );
		
		// full destination path
		$newName = ($newName == '') ? str_replace($file['ext'], $format, $file['name']) : $newName.'.'.$format;
		
		$path = ($path == '') ? $file['path'] : $path;
		$target = $KUINK_CFG->dataRoot . '/' . $path . '/' . $newName;
		$target = str_replace ( '//', '/', $target );
				
		//Expand the params into the command
		$command = $this->dataSource->getParam('command', true);
		$command = sprintf($command, $format, $source, $target);
		$KUINK_TRACE[]=$command;
		
		$result = shell_exec($command);
		$KUINK_TRACE[]=$result;
		
		if (! file_exists($target)) {
			throw new \Exception('Cannot convert file to '.$format.'. Check if the convertion service "unoconv -l" is up and running');
		} else {
			//Registering the new file
			$fileSize = filesize($target);
			$fileExt = $format;
			$fileMime = 'application/'.$format;
			
			$fileLib = new \FileLib ( $this->nodeconfiguration, \Kuink\Core\MessageManager::getInstance () );
			
			$newFile = $fileLib->register( $newName, $path, $newName, $fileSize, $fileExt, $fileMime, $KUINK_BRIDGE_CFG->auth->user->id, '' );
		}
		
		return $newFile;
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
}

?>
