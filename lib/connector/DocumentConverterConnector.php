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
		$this->connect();
		
		$format = $this->getParam($params, 'format');
		$source = $this->getParam($params, 'source');
		$target = $this->getParam($params, 'target');
		
		//Expand the params into the command
		$command = $this->dataSource->getParam('command', true);
		$command = sprintf($command, $format, $source, $target);
		$KUINK_TRACE[]=$command;
		
		$result = shell_exec($command);
		$KUINK_TRACE[]=$result;
		
		return $result;
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
