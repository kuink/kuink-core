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

class DataSource {
	var $application;
	var $process;
	var $method;
	var $methodName;
	var $database;
	var $databaseApp;
	var $datasourcexml_domobject;
	var $curr_db_prefix;
	function __construct($screen_domobject, $datasourcename, $appname, $processname, $databasename = '', $databaseApp = '') {
		global $KUINK_CFG;
		global $KUINK_APPLICATION;
		// If the $screen_domobject is null then the $datasourcename has the method name
		$this->application = $appname;
		$this->database = $databasename;
		$this->databaseApp = ($databaseApp == '') ? $appname : $databaseApp;
		
		$this->methodName = $datasourcename;
		
		// Get method name
		if ($screen_domobject == null)
			$methodname = $datasourcename;
		else
			$methodname = $this->getdatasource_methodname ( $screen_domobject, $datasourcename );
			
			// Explode method name to get application, process and method
		$methodpathparts = explode ( ',', $methodname );
		if (count ( $methodpathparts ) != 3)
			throw new \Exception ( 'Datasource: Methods name must be appname,processname,methodname' );
		
		$methodappname = trim ( $methodpathparts [0] );
		$methodprocessname = trim ( $methodpathparts [1] );
		$methodname = trim ( $methodpathparts [2] );
		
		// kuink_mydebug('METHOD PATH','apps/'.$methodappname.'/process/'.$methodprocessname.'/dataaccess/'.$methodname.'.xml');
		// Get the method parameters to pass to execute
		
		$methodappname = ($methodappname == 'this') ? $appname : $methodappname;
		$methodprocessname = ($methodprocessname == 'this') ? $processname : $methodprocessname;
		
		// kuink_mydebug('METHOD PATH','apps/'.$methodappname.'/process/'.$methodprocessname.'/dataaccess/'.$methodname.'.xml');
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $methodappname ) : '';
		
		libxml_use_internal_errors ( true );
		$this->datasourcexml_domobject = simplexml_load_file ( Configuration::getInstance()->paths->apps. '/' . $appBase . '/' . $methodappname . '/process/' . $methodprocessname . '/dataaccess/' . $methodname . '.xml', 'SimpleXMLElement', LIBXML_NOCDATA );
		if ($this->datasourcexml_domobject == null) {
			// Register the error
			echo "Failed loading XML\n";
			foreach ( libxml_get_errors () as $error ) {
				echo "\t", $error->message;
			}
			// kuink_mydebug('ERROR',"Loading XML file: " . 'apps/'.$methodappname.'/process/'.$methodprocessname.'/dataaccess/'.$methodname.'.xml');
		}
		
		// $method = $this->datasourcexml_domobject->xpath('/method');
		// var_dump($method);
		// kuink_mydebug('methodname',$method[0]['name']);
	}
	function getdatasource_methodname($screen_domobject, $datasourcename) {
		// kuink_mydebug('getdatasource_methodname','Begin::'.$datasourcename);
		// getting the name of the method to execute
		// var_dump($screen_domobject);
		
		// $datasource = $screen_domobject->xpath('//datasources/datasource[@id="'.$datasourcename.'"]/method');
		$datasource = $screen_domobject->xpath ( '//datasource[@id="' . $datasourcename . '"]/method' );
		// var_dump($datasource);
		if ($datasource == null)
			kuink_mydebug ( 'ERROR: Datasource not found: ', $datasourcename );
			
			// var_dump($datasource);
			// kuink_mydebug('methodname',$datasource[0]['name']);
		return $datasource [0] ['name'];
	}

    /**
     * @param null $params
     * @return array|bool|mixed|void|null
     * @throws \Exception
     */
    function execute($params = null) {
		global $KUINK_DATABASES;
		global $KUINK_TRACE;
		global $KUINK_CFG;
		
		if (Configuration::getInstance()->kuink->use_new_data_access_infrastructure) {
			// Use the DataAccess Instead...
			$dataAccess = new DataAccess ( $this->methodName, $this->application, $this->process, $this->database );
			
			return ($dataAccess->execute ( $params ));
		}
		// Old legacy code from here
		
		$records = null;
		// var_dump($params);
		// kuink_mydebug('Application', $this->application);
		// Database can be specified in three different places:
		// 1) on the Execute call <Execute method="this,this,foo" database="target"/>
		// 2) on de Dataaccess it self <Method database="target"
		// 3) in the application xml
		// Get default database name for an application
		$database_name = DatabaseManager::applicationDefaultDB ( $this->application );
		$da_database_name = isset ( $this->datasourcexml_domobject ['database'] ) ? ( string ) $this->datasourcexml_domobject ['database'] : $this->database;
		$database_name = ($da_database_name == '') ? $database_name : $da_database_name;
		// kuink_mydebug('App Default Database', $database_name);
		
		$database = null;
		$KUINK_TRACE [] = 'Database:' . $database_name;
		$type = '';
		// Getting database object
		if ($database_name == '') {
			$this->curr_db_prefix = $KUINK_DATABASES [$KUINK_CFG->defaultDataSourceName]->prefix;
			$type = $KUINK_DATABASES [$KUINK_CFG->defaultDataSourceName]->type;
			$database = $KUINK_DATABASES [$KUINK_CFG->defaultDataSourceName]->db;
		} else {
			// Check if there is allready a connection active to this database
			if (isset ( $KUINK_DATABASES [$database_name] )) {
				$KUINK_TRACE [] = 'Database:' . $database_name . '::Connected';
				$this->curr_db_prefix = $KUINK_DATABASES [$database_name]->prefix;
				$type = $KUINK_DATABASES [$database_name]->type;
				$database = $KUINK_DATABASES [$database_name]->db;
			} else {
				// Get a connection
				$KUINK_TRACE [] = 'Database:' . $database_name . '::Getting Connection...';
				// $app_database = neon_setup_app_DB( $this->application, $database_name );
				$app_database = DatabaseManager::setupApplicationDB ( $this->databaseApp, $database_name );
				$this->curr_db_prefix = $app_database->prefix;
				$type = $app_database->type;
				$database = $app_database->db;
			}
		}
		
		$newParams = null;
		foreach ( $params as $key => $value ) {
			if (is_array ( $value )) {
				// Check to see if it comes froma fullSplit or is tocreate a string to use with IN
				if (isset ( $value [0] [0] ) && $value [0] [0] == '%')
					$newParams [$key] = $value;
				else {
					// implode to use with IN
					$arrayValues = array ();
					foreach ( $value as $arrayValue )
						$arrayValues [] = '\'' . $arrayValue . '\'';
					$newParams [$key] = implode ( ',', $arrayValues );
				}
			} else
				$newParams [$key] = $value;
		}
		// var_dump( $newParams );
		
		try {
			$methods = $this->datasourcexml_domobject->xpath ( '/Method/Body' );
			foreach ( $methods as $method ) {
				// var_dump($method);
				if ($method->getname () == 'Body')
					foreach ( $method->children () as $instruction ) {
						// kuink_mydebug('type',$type);
						if ($type != 'mongodb')
							switch ($instruction->getname ()) {
								case 'GetRecords' :
									// print('PARAMS:');
									// var_dump($params);
									$records = $this->getrecords ( $instruction, $newParams, $database );
									break;
								case 'Load' :
									$records = $this->load ( $instruction, $newParams, $database );
									break;
								case 'Insert' :
									$records = $this->insert ( $instruction, $newParams, $database );
									break;
								case 'Update' :
									$records = $this->update ( $instruction, $newParams, $database );
									break;
								case 'Delete' :
									$records = $this->delete ( $instruction, $newParams, $database );
									break;
								case 'Save' :
									$records = $this->save ( $instruction, $newParams, $database );
									break;
								case 'Execute' :
									$records = $this->execute_sql ( $instruction, $newParams, $database );
									break;
								
								case 'Sql' :
									$records = $this->sql ( $instruction, $newParams, $database );
									break;
								case 'SqlPaginated' :
									$records = $this->sqlpaginated ( $instruction, $newParams, $database );
									break;
								case 'Doc' :
									// Do nothing... documentation
									break;
								case 'Dataset' :
									$records = $this->dataset ( $instruction, $newParams, $database );
									break;
								default :
									throw new \Exception ( 'Datasource: instruction ' . $instruction->getname () . ' not found ' );
							}
						else
							$records = $this->mongo_execute ( $instruction, $params, $database );
					}
				// Mongo
			}
		} catch ( \Exception $e ) {
			$KUINK_TRACE [] = 'Exception: ' . var_export ( $e, TRUE );
			throw $e;
		}
		
		// var_dump( $records );
		return $records;
	}
	function getrecords($instruction, $params, $DB) {
		// var_dump($params);
		// var_dump($instruction);
		// global $DB;
		$dstable = (! isset ( $instruction ['table'] )) ? ( string ) $params ['table'] : ( string ) $instruction ['table'];
		$dsfield = (! isset ( $instruction ['field'] )) ? ( string ) $params ['field'] : ( string ) $instruction ['field'];
		$dsvalue = (! isset ( $instruction ['value'] )) ? ( string ) $params ['value'] : ( string ) $instruction ['value'];
		$dssort = (! isset ( $instruction ['sort'] )) ? ( string ) $params ['sort'] : ( string ) $instruction ['sort'];
		$dsfields = (! isset ( $instruction ['fields'] )) ? ( string ) $params ['fields'] : ( string ) $instruction ['fields'];
		
		$paramsr = array ();
		// print($dstable.'=>'."$dsvalue");
		// var_dump($paramsr);
		$records = $DB->get_records ( $dstable, $paramsr, $dssort, $dsfields );
		// $records =$DB->get_records('person_rel_type' , null, '', 'id,name');
		$recordsArray = array ();
		foreach ( $records as $record )
			$recordsArray [] = ( array ) $record;
			
			// $records =$DB->get_records($table="$dstable" , $field="$dsfield", $value="$dsvalue", $sort="$dssort", $fields="$dsfields");
			// var_dump($records);
		return $recordsArray;
	}
	function load($instruction, $params, $DB) {
		global $KUINK_TRACE;
		// print('BUMM');
		// global $DB;
		$instruction_table = isset ( $instruction ['table'] ) ? ( string ) $instruction ['table'] : '';
		$params_table = isset ( $params ['table'] ) ? ( string ) $params ['table'] : '';
		
		$dstable = ($instruction_table == '') ? $params_table : $instruction_table;
		// $dspk = isset($params['pk']) ? (string)$params['pk'] : 'id';
		// $dsid = $params[$dspk];
		// print('BUMM');
		
		// kuink_mydebug('LOAD dstable', $dstable);
		// kuink_mydebug('LOAD dsid', $dsid);
		
		// $conditions = array($dspk=>$dsid);
		unset ( $params ['table'] );
		// var_dump($params);
		// var_dump($dstable);
		
		$sql = $this->xsql ( $instruction, $params, false );
		if ($sql != '') {
			$KUINK_TRACE [] = 'SQL: ' . $sql;
			// var_dump($sql);
			$params = array ();
			$records = $DB->get_records_sql ( ( string ) $sql, $params );
			
			reset ( $records );
			$records = current ( $records );
			// var_dump($records);
		} else {
			$records = $DB->get_record ( $dstable, $params );
		}
		
		$records = ($records === FALSE) ? array () : ( array ) $records;
		
		// $records = $DB->get_record('fw_user', array('id'=>-1));
		// var_dump($records);
		return $records;
	}
	function insert($instruction, $params, $DB) {
		// print('INSERT::');
		// global $DB;
		global $KUINK_TRACE;
		
		$dstable = ( string ) ($instruction ['table'] == '') ? $params ['table'] : $instruction ['table'];
		$dstable = trim ( $dstable );
		
		$tableColumns = $DB->get_columns ( $dstable );
		
		// var_dump($DB->get_columns($dstable));
		
		unset ( $params ['table'] );
		
		$newParams = array ();
		foreach ( $params as $field => $value )
			if ($value != '' && ! is_null ( $value ) && isset ( $tableColumns [$field] ))
				$newParams ['`' . $field . '`'] = $value;
		
		$KUINK_TRACE [] = 'Table: ' . $dstable;
		$KUINK_TRACE [] = $newParams;
		// var_dump( $newParams );
		
		if (isset ( $params ['id'] ))
			$record = $this->insert_record_raw ( $dstable, $newParams, $DB );
		else
			// $record = $DB->insert_record($dstable, $newParams);
			$record = $DB->insert_record_raw ( $dstable, $newParams, $DB );
			
			// var_dump( $record );
		$KUINK_TRACE [] = $record;
		return $record;
	}
	function insert_record_raw($dstable, $params, $DB) {
		// var_dump($params);
		global $KUINK_TRACE;
		if (! is_array ( $params )) {
			$params = ( array ) $params;
		}
		
		if (empty ( $params )) {
			// die();
			throw new \Exception ( 'kuink::insert_record_raw() no fields found.' );
		}
		/*
		 * foreach ($params as $field=>$value )
		 * if (is_null($value) || $value=='')
		 * unset($params[$field]);
		 */
		
		$fields = implode ( ',', array_keys ( $params ) );
		$qms = array_fill ( 0, count ( $params ), '?' );
		$qms = implode ( ',', $qms );
		
		$sql = "INSERT INTO {$this->curr_db_prefix}$dstable ($fields) VALUES($qms)";
		
		$KUINK_TRACE [] = 'SQL: ' . $sql;
		$DB->execute ( $sql, $params );
		return;
	}
	
	// Se existir => update
	// Se não existir => insert
	function save($instruction, $params, $DB) {
		// var_dump($params);
		// var_dump($instruction);
		// global $DB;
		$dstable = ( string ) ($instruction ['table'] == '') ? $params ['table'] : $instruction ['table'];
		// print($dstable);
		// $dspk = isset($params['pk']);
		
		// var_dump($DB);
		// print('Antes...');
		$record = $this->load ( $instruction, $params, $DB );
		// print('Depois...');
		// var_dump($record);
		// var_dump( $record );
		
		if ($record) {
			// print('updating...');
			$record = $this->update ( $instruction, $params, $DB );
		} else {
			// print('inserting...');
			$record = $this->insert ( $instruction, $params, $DB );
		}
		
		// kuink_mydebug('DEBUG dstable', $dstable);
		// var_dump( $params );
		// kuink_mydebug('DEBUG dsid', $dsid);
		// $record = $DB->insert_record($dstable, $params);
		// kuink_mydebug('AFTER INSERT', $dsid);
		// $records = get_record("$dstable", $field1='id', $value1=$params['id'], $field2='', $value2='', $field3='', $value3='', $fields='*');
		// var_dump( $record );
		return $record;
	}
	function update($instruction, $params, $DB) {
		// var_dump('UPDATE::');
		// var_dump($params);
		// die();
		global $KUINK_TRACE;
		$dstable = ( string ) ($instruction ['table'] == '') ? $params ['table'] : "" . $instruction ['table'];
		$dspk = isset ( $params ['pk'] ) ? ( string ) $params ['pk'] : 'id';
		$dspk_value = $params [$dspk];
		// var_dump($dspk_value);
		
		$sets = array ();
		$sql = 'UPDATE ' . $this->curr_db_prefix . $dstable . ' SET ';
		
		$KUINK_TRACE [] = var_export ( $paramsarams, true );
		unset ( $params [$dspk] );
		unset ( $params ['pk'] );
		unset ( $params ['table'] );
		
		$newParams = array ();
		foreach ( $params as $field => $value )
			$newParams ['`' . $field . '`'] = $value;
			
			// var_dump($newParams);
		
		foreach ( $newParams as $field => $value ) {
			if (is_null ( $value ) || $value == '')
				$newParams [$field] = null;
			
			$sets [] = "$field = ?";
		}
		
		$newParams [] = $dspk_value;
		$sets = implode ( ',', $sets );
		
		$sql .= $sets . ' WHERE ' . $dspk . ' = ?'; // .$dspk_value;
		                                      
		// var_dump($params);
		
		$KUINK_TRACE [] = 'SQL: ' . $sql;
		$KUINK_TRACE [] = var_export ( $newParams, true );
		$DB->execute ( $sql, $newParams );
		return;
	}
	function delete($instruction, $params, $DB) {
		// global $DB;
		$dstable = ($instruction ['table'] == '') ? ( string ) $params ['table'] : ( string ) $instruction ['table'];
		// $dsid = $params['id'];
		unset ( $params ['table'] );
		
		$record = $DB->delete_records ( $dstable, $params );
		return $record;
	}
	function dataset($instruction, $params, $DB) {
		$utils = new \UtilsLib ();
		$records = $utils->xmlToSet ( array (
				0 => $instruction->asXML () 
		) );
		
		// kuink_mydebug('Dataset', $instruction->asXML());
		// var_dump( $records );
		return $records;
	}
	function sql($instruction, $params, $DB) {
		global $KUINK_TRACE;
		
		// print('Antes');
		// Get the query
		// var_dump($params);
		
		$sql = ( string ) $this->xsql ( $instruction, $params, false );
		// var_dump($sql);
		
		// kuink_mydebug("sql", $sql);
		// $sql = "SELECT r.id, r.name, r.code FROM fw_role r INNER JOIN fw_role_capability rc ON (rc.id_role = r.id) INNER JOIN fw_capability c ON (c.id = rc.id_capability) WHERE r.id_company = 1 AND c.code IN ('framework/company::manage','framework/all::admin')";
		
		$KUINK_TRACE [] = 'SQL: ' . $sql;
		
		$sqlParams = null;
		// kuink_mydebug('Sql', $sql);
		$records = $DB->get_records_sql ( $sql, null );
		
		// kuink_mydebug('Sql', $sql);
		// var_dump($records);
		return $records;
	}
	
	// Returns sql query from xsql
	// $count - replce select with select count(*) and remove order by
	function xsql($instruction, $params, $count = false) {
		$hasGroupBy = false;
		
		// Check if this has a xsql query
		$xsql = $instruction->xpath ( './XSql' );
		$is_xsql = (! empty ( $xsql ));
		
		$sql = '';
		if (! $is_xsql) {
			$sql = ( string ) $instruction [0] [0];
		} else {
			// Parse XSQL
			$sql = '';
			
			$xinstructions = $xsql [0]->children ();
			// var_dump($xinstructions);
			foreach ( $xinstructions as $xinst ) {
				$xinst_name = $xinst->getname ();
				// print($xinst_name.'<br/>');
				
				switch ($xinst_name) {
					case 'XSelect' :
						$sql .= ($count) ? 'SELECT COUNT(*) ' : $this->xparse ( $xinst, 'SELECT', 'SELECT *', 'XField', $params );
						break;
					case 'XFrom' :
						$sql .= $this->xparse ( $xinst, 'FROM', 'FROM', 'XTable', $params );
						break;
					case 'XWhere' :
						$sql .= $this->xparse ( $xinst, 'WHERE', 'WHERE 1=1', 'XCondition', $params );
						break;
					case 'XGroupBy' :
						$hasGroupBy = true;
						$sql .= $this->xparse ( $xinst, 'GROUP BY', '', 'XCondition', $params );
						break;
					case 'XHaving' :
						$sql .= $this->xparse ( $xinst, 'HAVING', '', 'XCondition', $params );
						break;
					case 'XOrderBy' :
						$sql .= ($count) ? '' : $this->xparse ( $xinst, 'ORDER BY', '', 'XOrder', $params );
						break;
					default :
						throw new \Exception ( 'Invalid xsql instruction: ' . $xinst_name );
						break;
				}
			}
		}
		// Expand parameters and table prefix
		foreach ( $params as $key => $value ) {
			// $param_value = mysql_escape_string($value);
			$param_value = $value;
			$sql = str_replace ( '{param->' . $key . '}', $param_value, $sql );
		}
		$sql = str_replace ( '{table_prefix}', "{$this->curr_db_prefix}", $sql );
		
		if ($hasGroupBy && $count)
			$sql = 'SELECT COUNT(*) as total FROM (' . $sql . ') _total';
			
			// var_dump( $sql );
		
		return $sql;
	}
	
	// xchild [XField, XCondition,...]
	function xparse($instruction, $sql_prefix, $default, $xchild, $params) {
		$xinst_name = $instruction->getname ();
		$sql = $sql_prefix . ' ';
		
		$xfields = $instruction->xpath ( './' . $xchild );
		// var_dump($xfields);
		
		foreach ( $xfields as $xfield ) {
			$optional = isset ( $xfield ['optional'] ) ? ( string ) $xfield ['optional'] : '';
			if ($optional != "") {
				$value = isset ( $params [$optional] ) ? $params [$optional] : '';
				
				// Check to see if the $value is a string, if it is, repeat the XOptional
				if (is_array ( $value )) {
					foreach ( $value as $splitedValue ) {
						$xCond = ( string ) $xfield [0];
						$xCond = str_replace ( '{param->' . $optional . '}', $splitedValue, $xCond );
						$sql .= $xCond . ' ';
					}
				} else if (trim ( $value ) != '')
					$sql .= ( string ) $xfield [0] . ' ';
			} else
				$sql .= ( string ) $xfield [0] . ' ';
		}
		
		if ($sql == $sql_prefix . ' ')
			$sql = $default . ' ';
			
			// print('<br/>SQL::'.$sql.'<br/>');
		return $sql;
	}
	function execute_sql($instruction, $params, $DB) {
		global $KUINK_TRACE;
		// global $DB;
		
		$sql = ( string ) $instruction [0] [0];
		// var_dump( $params );
		// die(var_dump($this->curr_db_prefix));
		$sql = str_replace ( '{table_prefix}', "{$this->curr_db_prefix}", $sql );
		// $sql = str_replace('{table_prefix}', 'mdl_' , $sql);
		
		// Expand parameters
		foreach ( $params as $key => $value ) {
			// kuink_mydebug($key, $value);
			$sql = str_replace ( '{param->' . $key . '}', "$value", $sql );
		}
		
		$KUINK_TRACE [] = "EXECUTE SQL: " . $sql;
		// kuink_mydebug('Before', "$sql");
		$records = $DB->execute ( $sql );
		// $records = $DB->execute("SELECT SCOPE_IDENTITY()");
		// kuink_mydebug('After', "$sql");
		// kuink_mydebug('ID', $DB->GetID());
		return $records;
	}
	function mongo_execute($instruction, $params, $DB) {
		global $KUINK_TRACE;
		
		$collection = ( string ) $instruction ['collection'];
		$method = ( string ) $instruction ['method'];
		$KUINK_TRACE [] = "MONGO EXECUTE: " . $collection . '.' . $method;
		
		if (isset ( $params ['collection'] )) {
			$collection = ( string ) $params ['collection'];
			unset ( $params ['collection'] );
		}
		
		// kuink_mydebug('MONGO Before', $collection.'.'.$method);
		if ($params)
			$records = $DB->$collection->$method ( $params );
		else
			$records = $DB->$collection->$method ();
			// $records = $DB->items->find();
		foreach ( $records as $obj ) {
			$return [] = $obj;
		}
		
		// kuink_mydebug('MONGO Before', $collection.'.'.$method);
		// var_dump($return);
		return $return;
	}
	function sqlpaginated($instruction, $params, $DB) {
		global $KUINK_TRACE;
		// global $DB;
		
		$pagesize = 10;
		$pagenum = 0;
		
		$count_sql = '';
		$query_sql = '';
		
		// Test if the xsql node is in the SqlPaginated direct child
		// If so, get both count and query from it
		
		$xsql = $instruction->xpath ( './XSql' );
		if (! empty ( $xsql )) {
			$count_sql = $this->xsql ( $instruction, $params, true );
			$query_sql = $this->xsql ( $instruction, $params, false );
		} else {
			$count_sql_node = $instruction->xpath ( 'CountFieldsSql' );
			$count_sql = $this->xsql ( $count_sql_node [0], $params, false );
			// var_dump( $count_sql );
			// kuink_mydebug('Done1!!', $count_sql);
			
			$query_sql_node = $instruction->xpath ( 'GetFieldsSql' );
			$query_sql = $this->xsql ( $query_sql_node [0], $params, false );
			// var_dump( $query_sql );
			// kuink_mydebug('Done2!!', $query_sql);
		}
		
		$pagenum = isset ( $params ['pagenum'] ) ? $params ['pagenum'] : '';
		$pagesize = isset ( $params ['pagesize'] ) ? $params ['pagesize'] : '';
		
		// kuink_mydebug($pagenum, $pagesize);
		
		$KUINK_TRACE [] = "COUNT SQL: " . $count_sql;
		
		// get the total number of records
		$total = $DB->count_records_sql ( $count_sql );
		$KUINK_TRACE [] = "TOTAL RECORDS: " . $total;
		
		$limitfrom = ($pagenum) * $pagesize;
		$limitnum = $pagesize;
		
		// get the page records
		// kuink_mydebug('Sql', "$sql");
		$KUINK_TRACE [] = "SQL: " . $query_sql;
		
		// $records = $DB->get_records_sql($query_sql, $limitfrom, $limitnum);
		// MIGRATION
		$params = array ();
		$records = $DB->get_records_sql ( $query_sql, $params, $limitfrom, $limitnum );
		
		// Preparing the output
		$output ['total'] = ( string ) $total;
		$output ['records'] = $records;
		
		// var_dump($output);
		return $output;
	}
}

?>