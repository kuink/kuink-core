<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
namespace Kuink\Core;

class DataAccess {
	var $application;
	var $process;
	var $dataAccess;
	var $dataSourceName;
	var $dataAccessXml_domobject;
	var $directMethod; // call directly this method from the connector
	var $daApplication; // The app name of the dataaccess nid
	var $user; //The current user executing this dataaccess	
	var $cacheType;
	var $cacheKey;
	
	function __construct($dataAccessNid, $appName, $processName, $dataSourceName = '') {
		global $KUINK_CFG, $KUINK_TRACE, $KUINK_APPLICATION;
		$this->application = $appName;
		// DataSource can be specified in three different places:
		// 1) on the DataAccess call <DataAccess execute="this,this,foo" datasource="target"/>
		// 2) on de Dataaccess it self <Method datasource="target"
		// 3) in the application xml for application datasource
		// 4) in the framework.xml for global datasource
		if ($dataSourceName == '' || $dataSourceName == null)
			$dataSourceName = $KUINK_CFG->defaultDataSourceName;
		
		$this->dataSourceName = $dataSourceName;
		
		// var_dump($this->appName);
		
		// Explode method name to get application, process and method
		$dataAccessNidParts = explode ( ',', $dataAccessNid );
		if (count ( $dataAccessNidParts ) != 3 && count ( $dataAccessNidParts ) != 1)
			throw new \Exception ( 'DataAccess: name must be method or appName,processName,dataAccessName' );
		
		$this->dataAccessXml_domobject = null;
		$this->directMethod = (count ( $dataAccessNidParts ) == 1) ? $dataAccessNid : '';
		
		if ($this->directMethod == '') {
			// Load the XML file containing this DataAccess
			$daAppName = trim ( $dataAccessNidParts [0] );
			$daProcessName = trim ( $dataAccessNidParts [1] );
			$daName = trim ( $dataAccessNidParts [2] );
			
			// kuink_mydebug('METHOD PATH','apps/'.$methodappname.'/process/'.$methodprocessname.'/dataaccess/'.$methodname.'.xml');
			// Get the method parameters to pass to execute
			
			$daAppName = ($daAppName == 'this') ? $appName : $daAppName;
			$daProcessName = ($daProcessName == 'this') ? $processName : $daProcessName;
			
			$this->daApplication = $daAppName;
			$KUINK_TRACE [] = 'DataAccess: ' . $daAppName . ',' . $daProcessName . ',' . $daName;
			
			// kuink_mydebug('METHOD PATH','apps/'.$daAppName.'/process/'.$daProcessName.'/dataaccess/'.$daName.'.xml');
			
			$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $daAppName ) : '';
			// var_dump($appBase.' - '.$daAppName.' - '.$daProcessName.' - '.$daName.' - '.$dataSourceName);
			libxml_use_internal_errors ( true );
			$this->dataAccessXml_domobject = simplexml_load_file ( $KUINK_CFG->appRoot . 'apps/' . $appBase . '/' . $daAppName . '/process/' . $daProcessName . '/dataaccess/' . $daName . '.xml', 'SimpleXMLElement', LIBXML_NOCDATA );
			if ($this->dataAccessXml_domobject == null) {
				// Register the error
				echo "Failed loading XML\n";
				foreach ( libxml_get_errors () as $error ) {
					echo "\t", $error->message;
				}
				// kuink_mydebug('ERROR',"Loading XML file: " . 'apps/'.$methodappname.'/process/'.$methodprocessname.'/dataaccess/'.$methodname.'.xml');
			}
		}
	}

	function setCache($cacheType, $cacheKey) {
		$this->cacheType = $cacheType;
		$this->cacheKey = $cacheKey;
	}

	function setUser($user) {
		$this->user = $user;
	}

	function execute($params = null) {
		global $KUINK_DATASOURCES;
		global $KUINK_TRACE;
		global $KUINK_CFG;
		$records = null;
		// kuink_mydebug('Application', $this->application);

		$cacheManager = \Kuink\Core\CacheManager::getInstance();
		
		//Get from Cache
		if (($this->cacheType != \Kuink\Core\CacheType::NONE) && ($KUINK_CFG->useCache)) {
			$exists = $cacheManager->exists($this->cacheKey, $this->cacheType);
			if ($exists) {
				$KUINK_TRACE[] = 'Getting from cache...';
				return $cacheManager->get($this->cacheKey, $this->cacheType);
			}
		}
		
		$dataSourceName = $this->dataSourceName;
		
		$dataSource = $KUINK_DATASOURCES [$dataSourceName];
		$dataSource->setUser($this->user);
		// var_dump($dataSource);
		
		if (is_a ( $dataSource->connector, 'Kuink\Core\DataSourceConnector\SqlDatabaseConnector' )) {
			
			$newParams = null;
			foreach ( $params as $key => $value ) {
				$ignoreArrays = array ();
				// Ignore the arrays in multilang fields need to pass as arrays
				if (isset ( $params ['_multilang_fields'] )) {
					$ignoreArraysTemp = explode ( ',', $params ['_multilang_fields'] );
					foreach ( $ignoreArraysTemp as $ignoreKey )
						$ignoreArrays [] = trim ( $ignoreKey );
				}
				
				if (is_array ( $value ) && (! in_array ( $key, $ignoreArrays ))) {
					// Check to see if it comes from a fullSplit or is tocreate a string to use with IN
					if (isset ( $value [0] [0] ) && $value [0] [0] == '%')
						$newParams [$key] = $value;
					else {
						// implode to use with IN
						$arrayValues = array ();
						$KUINK_TRACE[] = 'Expand Parameter '.$key.' to use in IN';
						$KUINK_TRACE[] =  var_export($value, true);
						foreach ( $value as $arrayValue ) {
							//$KUINK_TRACE[] =  'Numeric: '.$arrayValue.' - '.is_numeric($arrayValue);
							if (is_numeric($arrayValue))
								$arrayValues [] = (int)$arrayValue;
							else if (is_string($arrayValue))
								$arrayValues [] = '\'' . $arrayValue . '\'';
							else
								$arrayValues [] = '\'' . (count(array_filter(array_keys($arrayValue), 'is_string')) > 0) ? '__array' : $arrayValue . '\'';
						}
						$KUINK_TRACE[] =  var_export($arrayValues, true);
						//$newParams [$key] = implode ( ',', $arrayValues );
						$newParams [$key] = $arrayValues;

						//Remove the first and last ' chars to avoid erros in bind params
						//$newParams [$key] = substr($newParams [$key],1,strlen($newParams [$key])-2);
						//print_object($newParams [$key]);
					}
				} else {
					if ((!is_array($value)) && (in_array($key, $ignoreArrays))) {
						//Check if the value has the multilang token and split it into a multilang array
						$multilangXmlString = '<'.$key.'>'.$value.'</'.$key.'>';
						//print_object($multilangXmlString);
						$multilangXml = simplexml_load_string($multilangXmlString);
						//print_object($multilangXml);
						$multilangArr = array();
						foreach($multilangXml->children() as $multilangEntry) {
							$multiLangKey = (string)$multilangEntry->getName();
							$multilangValue = (string)$multilangEntry[0];
							$multilangArr[$multiLangKey] = $multilangValue;
						}
						$newParams[$key] = $multilangArr;
					} else {
						//This is not a multilang field, neither an array. Add it as it is
							$newParams[$key] = $value;
					}
				}
			}
			/*
			 * $newParams = null;
			 * foreach ($params as $key => $value) {
			 * if (is_array($value)) {
			 * $arrayValues = array();
			 * foreach ($value as $arrayValue)
			 * $arrayValues[] = '\'' . $arrayValue . '\'';
			 * $newParams[$key] = implode(',', $arrayValues);
			 * }
			 * else
			 * $newParams[$key] = $value;
			 * }
			 */
		} else {
			$newParams = $params;
		}
		
		if ($this->dataAccessXml_domobject != null) {
			// Execute the DataAcess from the XML definition file
			try {
				$dataSourceName = isset ( $this->dataAccessXml_domobject ['datasource'] ) ? ( string ) $this->dataAccessXml_domobject ['datasource'] : $dataSourceName;
				$methods = $this->dataAccessXml_domobject->xpath ( './Body' );
				
				foreach ( $methods as $method ) {
					// foreach ($methods as $instruction) {
					foreach ( $method->children () as $instruction ) {
						
						// Call the DataSource Connector that executes the current DataAccess
						$connectorInstruction = ( string ) $instruction->getname ();
						// var_dump($connectorInstruction);
						// kuink_mydebug($dataSourceName, $connectorInstruction);
						$dataSource = $KUINK_DATASOURCES [$dataSourceName];
						if (! (isset ( $dataSource )))
							throw new \Exception ( 'DataSource ' . $dataSourceName . ' not found. Check for definition in framework.xml, application.xml, or the node it self.' );
							
							// replace the legacy params
							
						// Legacy
						$legacyParams ['table'] = (isset ( $newParams ['table'] )) ? $newParams ['table'] : null;
						$legacyParams ['fields'] = (isset ( $newParams ['fields'] )) ? $newParams ['fields'] : null;
						$legacyParams ['order'] = (isset ( $newParams ['order'] )) ? $newParams ['order'] : null;
						$legacyParams ['pagenum'] = (isset ( $newParams ['pagenum'] )) ? $newParams ['pagenum'] : null;
						$legacyParams ['pagesize'] = (isset ( $newParams ['pagesize'] )) ? $newParams ['pagesize'] : null;
						
						$newParams ['_entity'] = isset ( $newParams ['_entity'] ) ? $newParams ['_entity'] : $legacyParams ['table'];
						unset ( $newParams ['table'] );
						$newParams ['_attributes'] = isset ( $newParams ['_attributes'] ) ? $newParams ['_attributes'] : $legacyParams ['fields'];
						unset ( $newParams ['fields'] );
						$newParams ['_sort'] = isset ( $newParams ['_sort'] ) ? $newParams ['_sort'] : $legacyParams ['order'];
						unset ( $newParams ['order'] );
						$newParams ['_pageNum'] = isset ( $newParams ['_pageNum'] ) ? $newParams ['_pageNum'] : $legacyParams ['pagenum'];
						unset ( $newParams ['pagenum'] );
						$newParams ['_pageSize'] = isset ( $newParams ['_pageSize'] ) ? $newParams ['_pageSize'] : $legacyParams ['pagesize'];
						unset ( $newParams ['pagesize'] );
						
						unset ( $legacyParams ); // clear legacy parameters
						                      // $currentMethod = new \SimpleXMLElement('<Xml/>');
						                      // $newChild = simplexml_load_string($instruction->asXml());
						                      // $currentMethod->addChild($newChild)
						                      // var_dump($currentMethod->asXml());
						$currentMethod = current ( $instruction->xpath ( 'parent::*' ) );
						// Only add the _sql compatibility param if it has children
						$innerText = ( string ) $instruction [0];
						if (count ( $instruction->children () ) > 0 || $innerText != '')
							$newParams ['_sql'] = $currentMethod;
							// var_dump($newParams['_sql']);
						
						$connector = $dataSource->connector;
						$records = $connector->$connectorInstruction ( $newParams );
						// $records = array();
					}
				}
			} catch ( \Exception $e ) {
				$KUINK_TRACE [] = 'Exception: ' . var_export ( $e, TRUE );
				throw $e;
			}
		} else {
			// Execute the method directly
			
			$connectorInstruction = $this->directMethod;
			$dataSource = $KUINK_DATASOURCES [$dataSourceName];
			if (! (isset ( $dataSource )))
				throw new \Exception ( 'DataSource ' . $dataSourceName . ' not found. Check for definition in framework.xml, application.xml, or the node it self.' );
			$connector = $dataSource->connector;
			$records = $connector->$connectorInstruction ( $newParams );
		}
		//Set in cache
		if (($this->cacheType != \Kuink\Core\CacheType::NONE) && ($KUINK_CFG->useCache)) {
			if (!$cacheManager->exists($this->cacheKey, $this->cacheType)) {
				$KUINK_TRACE[] = 'Setting in cache...';
				$cacheManager->add($this->cacheKey, $records, $this->cacheType);
			}
		}
		
		// var_dump( $records );
		return $records;
	}
}

?>