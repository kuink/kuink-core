<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of XmlConnector
 *
 * @author paulo.tavares
 */
class XmlConnector extends \Kuink\Core\DataSourceConnector {
	var $dom;
	var $xpath;
	var $fileName;
	var $type;
	function connect() {
		global $KUINK_TRACE, $KUINK_CFG, $KUINK_APPLICATION;
		
		if (! $this->dom) {
			$this->fileName = $this->dataSource->getParam ( 'filename', true );
			$this->type = $this->dataSource->getParam ( 'type', true );
			
			$split = explode ( ',', $this->fileName );
			
			if (count ( $split ) > 0) {
				// Get the real file name
				$application = $split [0];
				$process = $split [1];
				$node = $split [2];
				$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
				if ($this->type == 'nodes' || $this->type == 'lib' || $this->type == 'ui')
					$this->fileName = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/' . $this->type . '/' . $this->type . '/' . $split [2] . '_' . $node . '.xml';
				else if ($this->type == 'bpmn')
					$this->fileName = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/' . $this->type . '/' . $node . '.' . $this->type;
				else
					$this->fileName = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/' . $this->type . '/' . $node . '.xml';
			}
			// var_dump($this->fileName);
			
			if (! file_exists ( $this->fileName ))
				throw new \Exception ( 'Error opening node file ' . $this->fileName );
				// throw new \Exception('Error opening node file: '.$this->application.','.$this->process.','.$this->type.','.$this->node);
			
			$this->dom = new \DOMDocument ();
			// libxml_use_internal_errors( true );
			// $this->dom = simplexml_load_file( $this->fileName );
			$this->dom->load ( $this->fileName ); // = simplexml_load_file( $this->fileName );
			
			$this->xpath = new \DOMXPath ( $this->dom );
			
			$context = $this->dom->documentElement;
			$xpath = new \DOMXPath ( $this->dom );
			foreach ( $xpath->query ( 'namespace::*', $context ) as $node ) {
				$ns = str_replace ( 'xmlns:', '', $node->nodeName );
				$this->xpath->registerNamespace ( $ns, $node->nodeValue );
				// var_dump($ns.' :: '.$node->nodeValue);
			}
			
			// $root = $this->dom->documentElement;
			// var_dump($root);
			
			if ($validateXml)
				$errors = libxml_get_errors ();
			
			if (! $this->dom) {
				$errors = null;
				if ($validateXml)
					$errors = libxml_get_errors ();
				$KUINK_TRACE [] = var_export ( $errors, true );
			} else
				$KUINK_TRACE [] = 'File: ' . $this->fileName;
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
		$data = $this->getAll ( $params );
		// var_dump($data[0]);
		// Get the first
		if (isset ( $data [0] ))
			return $data [0];
		
		return null;
	}
	function getAll($params) {
		$entity = ( string ) $this->getParam ( $params, '_entity', true );
		$filter = ( string ) $this->getParam ( $params, '_filter', false, '' );
		$this->connect ();
		
		$nodeList = $this->xpath->query ( $entity . $filter, $this->dom );
		
		$headlines = array ();
		foreach ( $nodeList as $name ) {
			$headline = $this->nodeToArray ( $name );
			$headlines [] = $headline;
		}
		
		return $headlines;
	}
	function nodeToArray($node) {
		$headline = array (
				'_name' => $node->nodeName 
		);
		if ($node->attributes->length)
			foreach ( $node->attributes as $i )
				$headline ['_attributes'] [$i->nodeName] = trim ( $i->nodeValue );
		
		if ($node->childNodes->length) {
			foreach ( $node->childNodes as $i ) {
				if ($i->nodeName == '#text')
					$headline ['_text'] = stripslashes ( trim ( $i->nodeValue ) );
				else if (isset ( $headline [$i->nodeName] )) {
					if (! isset ( $headline [$i->nodeName] ['_multiple'] )) {
						// To prevent key overwrite, we need this hack
						$copyNode = $headline [$i->nodeName];
						unset ( $headline [$i->nodeName] );
						
						$headline [$i->nodeName] [] = $copyNode;
						$headline [$i->nodeName] ['_multiple'] = '*';
					}
					$headline [$i->nodeName] [] = $this->nodeToArray ( $i );
					// Remove the _multiple aux key
					if (isset ( $headline [$i->nodeName] ['_multiple'] ))
						unset ( $headline [$i->nodeName] ['_multiple'] );
				} else
					$headline [$i->nodeName] = $this->nodeToArray ( $i );
			}
		}
		// var_dump($headline);
		return $headline;
	}

  public function getSchemaName($params) {
  	return null;
  }
}

?>
