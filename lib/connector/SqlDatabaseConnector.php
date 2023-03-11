<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kuink\Core\DataSourceConnector;

use \Kuink\Core\TraceManager;
use \Kuink\Core\TraceCategory;
use Kuink\Core\NodeManager;
use Kuink\Core\NodeType;
use Kuink\Core\Exception\ParameterNotFound;
use Kuink\Core\Exception\DomainNotFound;
use Kuink\Core\Exception\PhysicalTypeNotFound;

class DDChanges {
	const ADD = 'Add';
	const REMOVE = 'Remove';
	const CHANGE = 'Change';
	const NOTHING = 'Nothing';
}

/**
 * Description of SqlDatabaseConnector
 *
 * @author paulo.tavares
 */
class SqlDatabaseConnector extends \Kuink\Core\DataSourceConnector {
	
	var $db; // The PDO object containing the connection
	var $lastAffectedRows; // The affected rows of last statement
	var $type; //The driver type, is very used
	
	function connect() {
		// kuink_mydebug(__CLASS__, __METHOD__);
		if (! $this->db) {
			$type = $this->dataSource->getParam ('type', true );
			$server = $this->dataSource->getParam ('server', true );
			$database = $this->dataSource->getParam ('database', true );
			$user = $this->dataSource->getParam ('user', true );
			$passwd = $this->dataSource->getParam ('passwd', true );
			$options = $this->dataSource->getParam ('options', false );

			//kuink_mydebug('Connect', $type.'::'.$server__);

			$this->type = $type;
			
			//Connect with specific drivers
			switch ($type) {
				case 'sqlsrv':
					$dsn = $type.':Server='.stripslashes($server).';Database='.$database;
					$options = array();//;array('Authentication'=>'SqlPassword');
					//$this->db = new \PDO ("sqlsrv:server=$server;database=$database;", $user, $passwd, $options);
					$this->db = new \PDO("$dsn", $user, $passwd, $options);
					$this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION ); 
					$this->db->exec ( "SET NOCOUNT ON;" );
					break;
				default:
					//This is the default dsn
					$dsn = "$type:host=$server;dbname=$database;$options";
					// Get the connection to the database
					$this->db = new \PDO ( $dsn, $user, $passwd );
					$this->db->exec ( "set names utf8" ); //TODO: Handle the utf8 problem
					$this->db->exec("SET SESSION group_concat_max_len = 1000000"); //TODO: Move this to datasource configuration			
					break;
			}
	
		}
	}

/***
 * Get the operator to filter the data
 */
private function getOperator($operator) {
	$result = '=';
	switch($operator) {
		case 'eq': $result = '='; break;
		case 'neq': $result = '!='; break;
		case 'gt': $result = '>'; break;
		case 'gte': $result = '>='; break;
		case 'lt': $result = '<'; break;
		case 'lte': $result = '<='; break;
	}
	return $result;
}

/***
 * Depending on the database type, identifiers must be enclosed in different ways 
 */
private function encloseIdentifier($identifier) {
	$enclose = '';
	if (!isset($this->type))
		$this->type = $this->dataSource->getParam ('type', true );		
	switch ($this->type) {
		case 'mysql':
			$enclose = "`$identifier`";
			break;
		case 'sqlsrv':
			$enclose = "[$identifier]";
			break;
		default:
			$enclose = $identifier;
			break;
	}
	return $enclose;
}

	/**
	 * This will receive the params of the statement and will transform
	 * the preparedStatementXml into a PDO ready string
	 * 
	 * @param unknown $params        	
	 * @return unknown
	 */
	private function prepareStatementToExecute($params, $count = false) {
		// Release the prepared statement for the next request
		if (! isset ( $params ['_sql'] ))
			throw new \Exception ( 'The param _sql was not supplied' );
		
		$xml = $params ['_sql'];
		// print_object($xml->asXml());
		
		$sqlXml = $xml->children ();
		
		$sql = ( string ) $this->xsql ( $sqlXml [0], $params, $count );
		
		return $sql;
	}
	
	/**
	 * inserts a record in the database and returns the inserted id
	 * 
	 * @see \Kuink\Core\DataSourceConnector::insert()
	 */
	function insert($params) {
		$this->connect ();

		$originalParams = $params;
		
		if (isset ( $params ['_multilang_fields'] )) {
			// Remove them from params
			$multilangFields = ( string ) $this->getParam ( $params, '_multilang_fields', false, '' );
			$multilangFieldsArray = explode ( ',', $multilangFields );
			foreach ( $multilangFieldsArray as $multilangField )
				unset ( $params [trim ( $multilangField )] );
		}
		
		unset ( $params ['_multilang_fields'] );
		if (isset ( $params ['_sql'] )) {
			$sql = $this->prepareStatementToExecute ( $params );
		} else {
			$sql = $this->getPreparedStatementInsert ( $params );
		}

		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		$this->executeSql ( $sql, $params );
		
		$insertId = $this->db->lastInsertId ();
		// print_object($insertId);
		
		// Handle the multilang
		$originalParams ['id'] = $insertId;
		$this->handleMultilang ( $originalParams, 1 ); // 1: Insert
		$paramsId = isset($params ['id']) ? $params ['id'] : '';

		$idToReturn = ($paramsId != '') ? $params ['id'] : $insertId;
		return $idToReturn;
	}
	
	/**
	 * Handle the multilang fields in the request
	 * 
	 * @param unknown $params
	 *        	- base params
	 * @param unknown $type
	 *        	- 1:Insert, 2:Update
	 */
	function handleMultilang($params, $type) {
		// Handle MULTILANG
		//kuink_mydebug('Multilang');
		$multilangFieldsArray = array ();
		if (isset ( $params ['_multilang_fields'] )) {
			// Create the sql statement to update the lang keys
			$idRefField = ( string ) $this->getParam ( $params, '_pk', false, 'id' );
			$idRef = ( string ) $this->getParam ( $params, $idRefField, false, 'id' );
			$multilangFields = ( string ) $this->getParam ( $params, '_multilang_fields', false, '' );
			// print_object($idRef);
			$multilangFieldsArray = explode ( ',', $multilangFields );
			$multilangFieldsArraySize = count ( $multilangFieldsArray );
			// print_object($multilangFieldsArraySize);
			
			for($i = 0; $i < $multilangFieldsArraySize; $i ++)
				$multilangFieldsArray [$i] = trim ( $multilangFieldsArray [$i] );
				
				// To get all the language keys
			$structure = $this->getParam ( $params, $multilangFieldsArray [0], false, '' );
			// print_object($structure);
			
			// Check if the multilang record exists, if so update or else create!!
			foreach ( $structure as $langCode => $langValue ) {
				$insertLangFields = array (
						'id' => $idRef,
						'lang' => $langCode 
				);
				foreach ( $multilangFieldsArray as $multilangField ) {
					$multilangFieldData = $this->getParam ( $params, $multilangField, false, '' );
					$insertLangFields [$multilangField] = ( string ) $multilangFieldData [$langCode];
				}
				
				$insertLangFields ['_entity'] = $params ['_entity'] . '_lang';
				if ($type == 2) { // Update
					$testLoadFields = array ();
					$testLoadFields ['_entity'] = $params ['_entity'] . '_lang';
					$testLoadFields ['id'] = $idRef;
					$testLoadFields ['lang'] = $langCode;
					$testRecord = $this->load ( $testLoadFields );
					
					// If there is allready a record, then update it, else insert it
					// print_object(count($testRecord) );
					if (count ( $testRecord ) > 0) {
						$insertLangFields ['id'] = $testRecord ['id'];
						$insertLangFields ['_pk'] = 'id,lang'; //These are the primary keys
						$this->update ( $insertLangFields );
					} else {
						$returnId = $this->insert ( $insertLangFields );
					}
				} else if ($type == 1) { // Insert
					$returnId = $this->insert ( $insertLangFields );
				}
			}
			// print_object('MULTILANG');
		}
		return $multilangFieldsArray;
	}
	function update($params) {
		$this->connect ();
		
		$multilangFieldsArray = $this->handleMultilang ( $params, 2 ); // 2: Update
		                                                            // Remove the multilang fields from the main update
		foreach ( $multilangFieldsArray as $multilangField ) {
			unset ( $params [$multilangField] );
		}
		
		if (isset ( $params ['_sql'] )) {
			$sql = $this->prepareStatementToExecute ( $params );
		} else {
			$sql = $this->getPreparedStatementUpdate ( $params );
		}
		
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		$this->executeSql ( $sql, $params, false, true, true );
		
		return $this->lastAffectedRows;
	}
	function save($params) {
		$this->connect ();
		
		$pk = $this->getParam ( $params, '_pk', false, 'id' );
		
		// Get the primary keys
		$pks = explode ( ',', $pk );
		
		// Check if all the primary keys are present, if so then update the record, else insert the record
		$allPksPresent = true;
		foreach ( $pks as $pk )
			$allPksPresent = $allPksPresent && isset ( $params [$pk] );
		
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		TraceManager::add ( ($allPksPresent) ? 'Save::Update' : 'Save::Insert', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		if ($allPksPresent)
			$result = $this->update ( $params );
		else
			$result = $this->insert ( $params );
		
		return $result;
	}
	function delete($params) {
		$this->connect ();
		$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
		$multilang = (string)$this->getParam($params, '_multilang', false, 'false');
		unset($params['_multilang']);
		$acl = ($aclPermissions == 'false') ? 'false' : 'true';
		$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::delete.all' : $aclPermissions;
		if (isset ( $params ['_sql'] )) {
			$sql = $this->prepareStatementToExecute ( $params );
		} else {
			$sql = $this->getPreparedStatementDelete ( $params );
		}
		
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		$canDelete = true;
		if ($acl == 'true') {
			//In this case only allow the deletion if the user has the right capabilities
			$aclPermissions = str_replace(',', "','", $aclPermissions);
			$aclPermissions = str_replace(' ', "", $aclPermissions);
			$aclPermissions = "'".$aclPermissions."'";
			//kuink_mydebug('ACL:', $aclPermissions);
			//Try to load the record with the permissions
			$record = $this->load($params);
			$canDelete = (count($record) > 0);
			//kuink_mydebugObj('Obj:',$record);
			//var_dump($canDelete);
			//var_dump($params);
		}
		if ($canDelete)
			$this->executeSql($sql, $params);
		
			$lastAffectedRows = $this->lastAffectedRows;
		
		//If multilang, then delete the language records also
		if ($multilang == 'true') {
			$entity = (string)$this->getParam($params, '_entity', true);
			$params['_entity'] = $entity.'_lang';
			$this->delete($params);
		}

		return $lastAffectedRows;
	}
	
	/**
	 * *
	 * For compatibility
	 */
	function execute($params) {
		$this->connect ();
		$sql = $this->prepareStatementToExecute ( $params );
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		$records = $this->executeSql ( $sql, $params );
		return $records;
	}
	
	/**
	 * *
	 * For compatibility
	 */
	function sql($params) {
		$this->connect ();
		
		$sql = $this->prepareStatementToExecute ( $params );
		
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		$records = $this->executeSql ( $sql, $params );
		return $records;
	}
	
	/**
	 * *
	 * For compatibility
	 */
	function sqlPaginated($params) {
		$this->connect ();
		
		$pageSize = 10;
		$pageNum = 0;
		
		$countSql = '';
		$querySql = '';
		
		// Test if the xsql node is in the SqlPaginated direct child
		// If so, get both count and query from it
		$xml = $params ['_sql'];
		
		$xsql = $xml->xpath ( './SqlPaginated/XSql' );
		if (! empty ( $xsql )) {
			$xsqlElements = $xml->xpath ( './SqlPaginated' );
			$xsqlElement = $xsqlElements [0];
			
			$countSql = $this->xsql ( $xsqlElement, $params, true );
			$querySql = $this->xsql ( $xsqlElement, $params, false );
		} else {
			$count_sql_node = $xml->xpath ( './SqlPaginated/CountFieldsSql' );
			$countSql = $this->xsql ( $count_sql_node [0], $params, false );
			// print_object( $count_sql );
			
			$query_sql_node = $xml->xpath ( './SqlPaginated/GetFieldsSql' );
			$querySql = $this->xsql ( $query_sql_node [0], $params, false );
			// print_object( $query_sql );
		}
		
		$pageNum = isset ( $params ['_pageNum'] ) ? ( int ) $params ['_pageNum'] : 0;
		$pageSize = isset ( $params ['_pageSize'] ) ? ( int ) $params ['_pageSize'] : 10;
		
		// print_object($pageNum);print_object($pageSize);
		// kuink_mydebug($pagenum, $pagesize);
		
		TraceManager::add ( "COUNT SQL: " . $countSql, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );
		
		// get the total number of records
		$total = $this->executeSql ( $countSql, $params ); // $DB->count_records_sql($count_sql);
		$totalRecords = 0;
		foreach($total as $totalItem) {
			$totalRecords = (int)$totalItem['_total'];
		}
		
		TraceManager::add ( "TOTAL RECORDS: " . $totalRecords, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );
		
		$limitFrom = ($pageNum) * $pageSize;
		$limitNum = $pageSize;
		
		// print_object($limitFrom);print_object($limitNum);
		
		// get the page records
		
		//$params = array ();
		if ($limitFrom != 0 || $limitNum != 0)
			$querySql .= ' LIMIT ' . $limitFrom . ',' . $limitNum;
		
		TraceManager::add ( 'SQL: ' . $querySql, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );		
		$records = $this->executeSql ( $querySql, $params );
		
		// Preparing the output
		$output ['total'] = ( string ) $totalRecords;
		$output ['records'] = $records;
		
		return $output;
	}

	private function transformMultilangData($data, $lang, $langInline) {
		$ignoreKeys = array ('id');
		$langKey = 'lang';
		
		if (isset ( $data [0] ))
			$structure = $data [0];
		else
			return;
			
			// print_object($langInline);
		if ($lang == '*' || $langInline == 'false') {
			// build an array with the name of the multilang keys
			$multilangKeys = array ();
			foreach ( $structure as $key => $value ) {
				if ($key != $langKey && ! in_array ( $key, $ignoreKeys ))
					$multilangKeys [] = $key;
			}
			
			// print_object($multilangKeys);
			
			$resultdData = array ();
			foreach ( $data as $record ) {
				foreach ( $multilangKeys as $multilangKey ) {
					// print_object($record[$langKey].'::'.$record[$multilangKey]);
					$resultdData [$multilangKey] [$record [$langKey]] = $record [$multilangKey];
				}
			}
		} else {
			// print_object($data[0]);
			// inject the translation directly in the field
			foreach ( $data [0] as $key => $record ) {
				$resultdData [$key] = isset ( $record ) ? $record : '';
			}
		}
		
		return $resultdData;
	}

	/*
	function load($params, $operators=null) {
		$records = $this->getAll($params, $operators);
		$record = (count ( $records ) > 0) ? $records [0] : null;
		return ($record);
	}*/	

	
	function load($params, $operators=null) {
		// kuink_mydebug(__CLASS__, __METHOD__);
		$this->connect ();
		
		$lang = ( string ) $this->getParam ( $params, '_lang', false, '' );
		$langInline = ( string ) $this->getParam ( $params, '_lang_inline', false, 'true' );
		
		if (isset ( $params ['_sql'] )) {
			$sql = $this->prepareStatementToExecute ( $params );
		} else {
			$sql = $this->getPreparedStatementSelect ( $params, true, $operators );
		}
		
		if ($this->db->inTransaction ())
			$sql .= ' FOR UPDATE';
		
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );	
		
		$records = $this->executeSql ( $sql, $params, true, false );
		$record = (count ( $records ) > 0) ? $records [0] : null;

		// add the multilang data if it is set
		$multilangTransformedRecords = array ();
		if ($lang != '') {
			$entity = ( string ) $this->getParam ( $params, '_entity', false, 'false' );
			// Get the multilang data
			$paramsMultilang = array ();
			$paramsMultilang ['_entity'] = $entity . '_lang';
			$paramsMultilang ['id'] = $record ['_multilang_id'];
			//kuink_mydebugObj('record', $record);
			if ($lang != '*') {
				$paramsMultilang ['lang'] = $lang;
			}
			
			$multilangRecords = $this->getAll ( $paramsMultilang );
			//kuink_mydebugObj('entity', $entity);
			//kuink_mydebugObj('paramsMultilang', $paramsMultilang);
			//kuink_mydebugObj('multilangRecords', $multilangRecords);
			$multilangTransformedRecords = $this->transformMultilangData ( $multilangRecords, $lang, $langInline );
			//kuink_mydebugObj('multilangTransformedRecords', $multilangTransformedRecords);
			unset ( $params ['_lang'] );
			unset ( $params ['_lang_inline'] );
			unset ( $record ['_multilang_id'] );
		}

		if (!empty($multilangTransformedRecords)) {
			if (count ( $multilangTransformedRecords > 0 )) {
				foreach ( $multilangTransformedRecords as $key => $multilangData )
				if ($key != 'id')
					$record[$key] = $multilangData;

			}
		}
		
		return $record;
	}
	
	
	/**
	 * *
	 * For campatibility
	 */
	function getRecords($params) {
		// kuink_mydebug(__CLASS__, __METHOD__);
		// For compatibility
		return $this->getAll ( $params );
	}
	function dataset($params) {
		$xml = $params ['_sql'];
		
		$datasets = $xml->xpath ( './Dataset' );
		if (count ( $datasets ) == 0)
			throw new \Exception ( 'The method Dataset needs the DataSet element' );
		
		$dataset = $datasets [0];
		
		$utils = new \UtilsLib ();
		$records = $utils->xmlToSet ( array (
				0 => $dataset->asXML () 
		) );
		
		// print_object( $records );
		return $records;
	}
	function getAll($params, $operators=null) {
		$this->connect ();
		
		$pageNum = $this->getParam ( $params, '_pageNum', false, 0 );
		$pageSize = $this->getParam ( $params, '_pageSize', false, 0 );
		$lang = (string)$this->getParam($params, '_lang', false, '');		
		$offset = $pageNum * $pageSize;
		
		if ($lang != '' && $lang == '*')
			throw new \Exception ( 'Invalid l_lang value. Cannot be * in getAll' );
		
		$countSql = '';
		if (isset ( $params ['_sql'] )) {
			$sql = $this->prepareStatementToExecute ( $params );
			if ($pageNum != 0 || $pageSize != 0) {
				$countSql = $this->prepareStatementToExecute ( $params, true );
				$sql .= ' LIMIT ' . $offset . ',' . $pageSize;
			}
		} else {
			if ($pageNum != 0 || $pageSize != 0)
				$countSql = $this->getPreparedStatementSelectCount ( $params, $operators );
			$sql = $this->getPreparedStatementSelect ( $params, false, $operators );
		}
		
		$totalRecords = 0;
		if ($pageNum != 0 || $pageSize != 0) {
			$total = $this->executeSql ( $countSql, $params );
			foreach ( $total [0] as $total )
				$totalRecords = ( int ) $total;
		}
		
		TraceManager::add ( __METHOD__, TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );	
		if ($pageNum != 0 || $pageSize != 0) {
			TraceManager::add ( 'CountSQL: '.$countSql, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );	
			TraceManager::add ( 'Total: '.$totalRecords, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );	
		}
		
		$records = $this->executeSql ( $sql, $params );
		if ($pageNum != 0 || $pageSize != 0) {
			$output ['total'] = ( string ) $totalRecords;
			$output ['records'] = $records;
		} else
			$output = $records;
			if ($lang != '') {
				//Expand multilang data
			}
		
		return $output;
	}

	/***
	 * $notSelect - For Sql Server error on update without $query->nextRowset();
	 */
	private function executeSql($sql, $params, $ignoreNulls = false, $allowEmptyParams = true, $notSelect=false) {
		// print_object($sql);
		$entity = $params ['_entity'];
		if (isset($params)) {
			unset ( $params ['_entity'] );
			unset ( $params ['_attributes'] );
			unset ( $params ['_sort'] );
			unset ( $params ['_pageNum'] );
			unset ( $params ['_pageSize'] );
			unset ( $params ['_pk'] );
			unset ( $params ['_sql'] );
			unset ( $params ['_debug_'] );
			unset ( $params ['_multilang_fields'] );
			//unset ( $params ['_lang'] );
			unset ( $params ['_acl']);
			unset ( $params ['_aclPermissions']);
		}

		if (isset($params) && is_array($params))
			foreach ( $params as $key => $value )
				if (($ignoreNulls && ($value == '')) || is_array($value))
					unset ( $params [$key] ); //ignore empty values and arrays
				// print_object($params);
		
		$params = (isset($params)) ? $params : array();
		if (count ( $params ) == 0 && ! $allowEmptyParams) {
			return null;
		}
		// Here we have some parameters
		$query = $this->db->prepare ( $sql );

		if (!$query) {
			TraceManager::add ( 'Database error', TraceCategory::ERROR, __CLASS__.'::'.__METHOD__ );	
			TraceManager::add ( $sql, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );	
			throw new \Exception ( 'Error preparing query ');
		}
		//if ($query === FALSE)
		//var_dump($query);
		//print($sql.'<br/>');

		//print_object($sql);
		//print_object($params);


		//Remove unused params from the query to use bind params
		$bindParams = array();
		$sqlTmp = $sql;
		while ( preg_match ( "/[\:][a-zA-Z0-9_]+/", $sqlTmp, $matches ) ) {
			$bindParamRaw = $matches [0];
			$bindParam = substr($bindParamRaw, -(strlen($bindParamRaw)-1));
			$bindParams[$bindParam] = ($params[$bindParam] === null) ? $params[$bindParam] : stripslashes($params[$bindParam]);
			$query->bindValue($bindParam, $bindParams[$bindParam]);				

			//Hack to prevent that params with ':' character create an infinite loop here
			$bindParams[$bindParam] = str_replace(':', '§§§§§§§§', $bindParams[$bindParam]);

			$errorInfo = $query->errorInfo ();
			if ($errorInfo[0] != '') {
				TraceManager::add ( 'Query bind param error', TraceCategory::ERROR, __CLASS__.'::'.__METHOD__ );
				TraceManager::add ( $errorInfo [0] . '|' . $errorInfo [1], TraceCategory::ERROR, __CLASS__.'::'.__METHOD__ );
			}
	
			$keys=array();
			if (is_string($bindParam))
				$keys[] = '/:'.$bindParam.'/';
			else
				$keys[] = '/[?]/';
			$sqlTmpReplaced = preg_replace($keys, $bindParams, $sqlTmp, 1, $count);//str_replace($matches[0],"'".$params[$bindParam]."'",$sqlTmp);			

			/*
			if ($sqlTmpReplaced == $sqlTmp) {
				//The replace was not executed... something wrong happened
				kuink_mydebug('Error', $sqlTmpReplaced);
				break;
			}
			*/
			
			$sqlTmp = $sqlTmpReplaced;
		}
		$performanceStart = microtime(true);

		$query->execute ();

		$performanceEnd = microtime(true);
		$performanceTime = $performanceEnd - $performanceStart;		
		
		TraceManager::add ( $this->pdoDebugStrParams($query), TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );
		TraceManager::add ( 'SQL Execution - (Time: '. number_format($performanceTime, 5).')', TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );		

		//var_dump($sql);
		//var_dump(count($params));

		// print_object($sql);
		//print_object($records);
		
		// Handle the errors
		$errorInfo = $query->errorInfo ();
		//kuink_mydebugObj('ErrorInfo', $errorInfo);
		//if ($this->type == 'sqlsrv')
		//	kuink_mydebugObj('ErrorInfo', $errorInfo);

		if ($errorInfo [0] !== '00000' || $errorInfo [1] != 0) {
			TraceManager::add ( 'Database Error: '.$errorInfo [0] . '|' . $errorInfo [1] . ' | '. $errorInfo [2] , TraceCategory::ERROR, __CLASS__.'::'.__METHOD__ );
			throw new \Exception ( 'Internal database error ('.$errorInfo [0].') - '.$errorInfo [1] );
		}

		if (($this->type == 'sqlsrv') && $notSelect) {
			$query->nextRowset();
		}

		$records = $query->fetchAll ( \PDO::FETCH_ASSOC );

		// print_object($records);
		$this->lastAffectedRows = $query->rowCount ();
		return $records;
	}

	function pdoDebugStrParams($stmt) {
		ob_start();
		$stmt->debugDumpParams();
		$r = ob_get_contents();
		ob_end_clean();
		return $r;
	}

	private function getPreparedStatementSelectCount($params, $operators=null) {
		$entity = $this->getParam ( $params, '_entity', true );
		$attributes = $this->getParam ( $params, '_attributes', false, '*' );
		$sort = isset ( $params ['_sort'] ) ? ' ORDER BY ' . $params ['_sort'] : '';
		$pageNum = $this->getParam ( $params, '_pageNum', false, 0 );
		$pageSize = $this->getParam ( $params, '_pageSize', false, 0 );
		$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
  	$acl = ($aclPermissions == 'false') ? 'false' : 'true';
  	$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::view.all' : $aclPermissions;
		
		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_pageNum'] );
		unset ( $params ['_pageSize'] );
		unset ( $params ['_pk'] );
  		unset ( $params ['_acl'] );
  		unset ( $params ['_aclPermissions'] );
		
		
		$where = (count ( $params ) > 0) ? ' WHERE ' : '';
		$count = 0;
		foreach ( $params as $key => $value ) {
			if ($count > 0)
				$where .= ' AND ';
			$operator = isset($operators[$key]) ? $this->getOperator($operators[$key])  : '=';				
			$where .= $this->encloseIdentifier($key) . ' '.$operator.' ' . ':' . $key . ' ';
			$count ++;
		}
		
		$entity = $this->encloseIdentifier($entity);
		if ($acl == 'true')  	
			$sql = "SELECT id_acl FROM $entity $where";
		else 
			$sql = "SELECT count(*) FROM $entity $where";
		
		if ($acl == 'true') {
			$aclPermissions = str_replace(',', "','", $aclPermissions);
			$aclPermissions = str_replace(' ', "", $aclPermissions);
			$aclPermissions = "'".$aclPermissions."'";
		
			$sql = "SELECT count(_aclBase.id_acl) FROM (".$sql.") _aclBase
					WHERE _aclBase.id_acl IN 
						(SELECT _aclc.id_acl FROM _fw_access_control_list_capability _aclc
						WHERE _aclc.id_acl = _aclBase.id_acl AND _aclc.code IN (".$aclPermissions.") AND _aclc.id_person='".$this->user['id']."') ";
		}
	
		return $sql;
	}

	private function getPreparedStatementSelect($params, $ignoreNulls = false, $operators=null) {
		$entity = $this->getParam ( $params, '_entity', true );
		$attributes = $this->getParam ( $params, '_attributes', false, '*' );
		$sort = isset ( $params ['_sort'] ) ? ' ORDER BY ' . $params ['_sort'] : '';
		$pageNum = $this->getParam ( $params, '_pageNum', false, 0 );
		$pageSize = $this->getParam ( $params, '_pageSize', false, 0 );
		$lang = ( string ) $this->getParam ( $params, '_lang', false, '' );
		$pk = $this->getParam ( $params, '_pk', false, 'id' );

  	$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
  	$acl = ($aclPermissions == 'false') ? 'false' : 'true';
  	$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::view.all' : $aclPermissions;
		
		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_pageNum'] );
		unset ( $params ['_pageSize'] );
		unset ( $params ['_pk'] );
		unset ( $params ['_debug_'] );
		unset ( $params ['_lang'] );
		unset ( $params ['_lang_inline'] );
	  	unset ( $params ['_acl'] );
  		unset ( $params ['_aclPermissions'] );
		
		$count = 0;
		$whereClauses = '';
		foreach ( $params as $key => $value ) {
			if (! $ignoreNulls || ($value != '')) {
				if ($count > 0)
					$whereClauses .= ' AND ';
				$operator = isset($operators[$key]) ? $this->getOperator($operators[$key]) : '=';
				$whereClauses .= 'e.'.$this->encloseIdentifier($key). ' '.$operator.' ' . ':' . $key . ' ';
				$count ++;
			}
		}
		$where = ($whereClauses != '') ? ' WHERE ' . $whereClauses : '';
		
		// Handle pagination
		$limit = '';
		if ($pageNum != 0 || $pageSize != 0) {
			// We have a pagination request
			$offset = ($pageNum) * $pageSize;
			$limit = ' LIMIT ' . $offset . ',' . $pageSize;
		}
		// print_object('AAAA');
		// print_object($pageNum);print_object($pageSize);
		
		// Handle Multilang
		$multilang = '';
		if ($lang != '') {
			$multilang = ' LEFT OUTER JOIN '.$this->encloseIdentifier($entity.'_lang').' l ON (l.id = e.id AND l.lang =\''.$lang.'\')';
		}
		
		// concatenate e. to all attributes
		
		if ($attributes != '*' && $lang == '') {
			$attrsArray = explode ( ',', $attributes );
			$newAttrs = array ();
			foreach ( $attrsArray as $attr )
				$newAttrs [] = 'e.' . trim ( $attr );
				// print_object($newAttrs);
			$attributes = implode ( ',', $newAttrs );
		}

		if ($attributes == '*' && $lang != '') {
			$attributes = 'e.id as _multilang_id, e.*, l.*';
		}
		
  	if ($acl == 'true')
  		$sql = 'SELECT '.$attributes.' FROM '.$this->encloseIdentifier($entity).' e '.$multilang.' '.$where.' '. $sort; //put the limit in the outer query not in inner query
  	else
  		$sql = 'SELECT '.$attributes.' FROM '.$this->encloseIdentifier($entity).' e '.$multilang.' '.$where.' '.$sort.' '.$limit;
  	
  	if ($acl == 'true') {
  		$aclPermissions = str_replace(',', "','", $aclPermissions);
  		$aclPermissions = str_replace(' ', "", $aclPermissions);
  		$aclPermissions = "'".$aclPermissions."'";
  		
  		$sql = "SELECT _aclBase.* FROM (".$sql.") _aclBase 
  				WHERE _aclBase.id_acl IN 
  					(SELECT _aclc.id_acl FROM _fw_access_control_list_capability _aclc
						 WHERE _aclc.id_acl = _aclBase.id_acl AND _aclc.code IN (".$aclPermissions.") AND _aclc.id_person='".$this->user['id']."') ".$limit;
  	}

  	return $sql;
	}

	private function getPreparedStatementInsert($params) {
		$entity = $this->getParam ( $params, '_entity', true );
  	$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
  	$acl = ($aclPermissions == 'false') ? 'false' : 'true';
		$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::view.all' : $aclPermissions;
				
		//kuink_mydebugObj('Params', $params);
		//die();

		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_pageNum'] );
		unset ( $params ['_pageSize'] );
		unset ( $params ['_pk'] );
  	unset ( $params ['_acl'] );
  	unset ( $params ['_aclPermissions'] );
		
		$fields = '';
		$values = '';
		$count = 0;
		foreach ( $params as $key => $value ) {
			if ($count > 0) {
				$fields .= ', ';
				$values .= ', ';
			}
			$fields .= $this->encloseIdentifier($key);
			$values .= ':' . $key;
			$count ++;
		}
	
		$sql = 'INSERT INTO '.$this->encloseIdentifier($entity).' ('.$fields.') VALUES ('.$values.')';
		
		return $sql;
	}

	private function getPreparedStatementDelete($params) {
		$entity = $this->getParam ( $params, '_entity', true );
  	$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
  	$acl = ($aclPermissions == 'false') ? 'false' : 'true';
  	$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::view.all' : $aclPermissions;
		
		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_pageNum'] );
		unset ( $params ['_pageSize'] );
		unset ( $params ['_pk'] );
  	unset ( $params ['_acl'] );
  	unset ( $params ['_aclPermissions'] );
		
		$where = '';
		$count = 0;
		foreach ( $params as $key => $value ) {
			if ($count > 0)
				$where .= ' AND ';
			$where .=  $this->encloseIdentifier($key) . ' = ' . ':' . $key . ' ';
			// $where .= $key.' = ? ';
			$count ++;
		}
		
		$entity = $this->encloseIdentifier($entity);
		$sql = "DELETE FROM $entity WHERE $where";
		
		return $sql;
	}

	private function getPreparedStatementUpdate($params) {
		$entity = $this->getParam ( $params, '_entity', true );
		$pk = $this->getParam ( $params, '_pk', false, 'id' );
		$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
  	$acl = ($aclPermissions == 'false') ? 'false' : 'true';
  	$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::update.all' : $aclPermissions;

		// Get the primary keys
		$pks = explode ( ',', $pk );
		
		unset ( $params ['_entity'] );
		unset ( $params ['_attributes'] );
		unset ( $params ['_sort'] );
		unset ( $params ['_pageNum'] );
		unset ( $params ['_pageSize'] );
		unset ( $params ['_pk'] );
		unset ( $params ['_multilang_fields'] );
  	unset ( $params ['_acl'] );
  	unset ( $params ['_aclPermissions'] );
		
		$where = '';
		$count = 0;
  	$onlyPks = array();		
		foreach ( $pks as $field ) {
			if ($count > 0)
				$where .= ' AND ';
			$onlyPks[$field] = isset($params[$field]) ? $params[$field] : null; 
			unset ( $params [$field] );
			$where .= $this->encloseIdentifier($field) . ' = ' . ':' . $field . ' ';
			$count ++;
		}
		
		$set = '';
		$count = 0;
		foreach ( $params as $key => $value ) {
			if (is_null ( $value ) || $value == '')
				$value = null;
			
			if ($count > 0)
				$set .= ', ';
			$set .= $this->encloseIdentifier($key) . ' = ' . ':' . $key . ' ';
			$count ++;
		}
		
		$canUpdate = true;
  	if ($acl == 'true') {
  		$onlyPks['_aclPermissions'] = $aclPermissions;
  		$onlyPks['_entity'] = $entity;
  		//Try to load the record with the permissions
  		//print_object($onlyPks);
  		$record = $this->load($onlyPks);
  		//print_object($record);
  		$canUpdate = (count($record) > 0);
		}
		
  	$entity = $this->encloseIdentifier($entity);
  	if ($canUpdate)
  		$sql = "UPDATE $entity SET $set WHERE $where";
  	else
  		$sql = "UPDATE $entity SET $set WHERE 1=0"; //Do nothing

		
		return $sql;
	}
	
	// Returns sql query from xsql
	// $count - replce select with select count(*) and remove order by
	private function xsql($instruction, $params, $count = false) {
  	$aclPermissions = (string)$this->getParam($params, '_aclPermissions', false, 'false');
  	$acl = ($aclPermissions == 'false') ? 'false' : 'true';
		$aclPermissions = ($aclPermissions == 'true') ? 'framework/generic::view.all' : $aclPermissions;
		$tablePrefix = $this->dataSource->getParam ( 'prefix', false, '' );
  	$hasGroupBy = false;
  	//Check if this has a xsql query
  	$xsql = $instruction->xpath('./XSql');
  	$is_xsql = (! empty($xsql));

  	$sql = '';
  	if (! $is_xsql)
  	{
  		$sql = (string)$instruction[0][0];
  	}
  	else
  	{
  		//Parse XSQL
			$sql = '';
			$limit = '';

  		$xinstructions = $xsql[0]->children();
  		//print_object($xinstructions);
  		foreach ($xinstructions as $xinst)
  		{
  			$xinst_name = $xinst->getname();
  			//print($xinst_name.'<br/>');
  			//print_object($params);

  			switch( $xinst_name )
  			{
  				case 'XSelect':
						$selectFields = trim($this->xparse($xinst, '', 'SELECT *', 'XField', $params));
						//If this is a select *, then we must remove the * because select field, * generates an sql error
						$selectFields = ($selectFields == '*') ? ' ' : ', '.$selectFields;
						$sql .= ($count && $acl == 'false') ? 'SELECT COUNT(*) AS _total '.$selectFields.' ' : $this->xparse($xinst, 'SELECT', 'SELECT *', 'XField', $params);
					break;
  				case 'XFrom':
  					$sql .= $this->xparse($xinst, 'FROM', 'FROM','XTable', $params);
  					break;
  				case 'XWhere':
  					$sql .= $this->xparse($xinst, 'WHERE','WHERE 1=1', 'XCondition', $params);
  					break;
  				case 'XGroupBy':
  					$hasGroupBy = true;
  					$sql .= $this->xparse($xinst, 'GROUP BY','', 'XCondition', $params);
  					break;
  				case 'XHaving':
  					$sql .= $this->xparse($xinst, 'HAVING', 'HAVING 1=1', 'XCondition', $params);
  					break;
  				case 'XOrderBy':
						//For compatibility mode try to find XORDER, if not then go and find XCondition
						$orderBy = $this->xparse($xinst, 'ORDER BY', '', 'XOrder', $params);
						if (trim($orderBy) == '')
							//Try to get it from a XCondition instead of a XOrder (Refactor will remove all XOrder and replace by XCondition)
							$orderBy = $this->xparse($xinst, 'ORDER BY', '', 'XCondition', $params);
  					$sql .= ($count) ? '' : $orderBy;
   					break;
  				default:
  					throw new \Exception('Invalid xsql instruction: '.$xinst_name);
  					break;
  			}

  		}
  	}
		//Expand parameters and table prefix
  	foreach ($params as $key => $value) {
  		//$param_value = mysql_escape_string($value);
			$param_value = $value;
			if (!is_array($param_value)) {
				//$sql = str_replace('{param->'.$key.'}', $param_value , $sql);
				//Prepare the statement for bind params
				$sql = str_replace('{@param->'.$key.'}', stripslashes($param_value) , $sql);
				$sql = str_replace('{param->'.$key.'}', ':'.$key , $sql);
				$sql = str_replace("':".$key."'", ':'.$key , $sql);
			}
		}
		$sql = str_replace('{table_prefix}', $tablePrefix , $sql);
		//TraceManager::add ( $sql, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );
		

  	if ($hasGroupBy && $count)
  		$sql = 'SELECT COUNT(*) as _total FROM ('.$sql.') __total';

		if ($acl == 'true') {
			$aclPermissions = str_replace(',', "','", $aclPermissions);
			$aclPermissions = str_replace(' ', "", $aclPermissions);
			$aclPermissions = "'".$aclPermissions."'";
			
			if ($count) {
				//$countSql = str_replace('select', 'select distinct ', strtolower($sql));	
				$sql = "SELECT count(_aclBase.id_acl) as _total FROM (".$sql.") _aclBase
						WHERE _aclBase.id_acl IN 
						(SELECT _aclc.id_acl FROM _fw_access_control_list_capability _aclc
							WHERE _aclc.id_acl = _aclBase.id_acl AND _aclc.code IN (".$aclPermissions.") AND _aclc.id_person='".$this->user['id']."' ".$limit.")";
			}
			else
				$sql = "SELECT _aclBase.* FROM (".$sql.") _aclBase 
						WHERE _aclBase.id_acl IN 
						(SELECT _aclc.id_acl FROM _fw_access_control_list_capability _aclc
							WHERE _aclc.id_acl = _aclBase.id_acl AND _aclc.code IN (".$aclPermissions.") AND _aclc.id_person='".$this->user['id']."' ".$limit.")";
		}

  	return $sql;
	}
	
	// xchild [XField, XCondition,...]
	private function xparse($instruction, $sql_prefix, $default, $xchild, $params) {
		$xinst_name = $instruction->getname ();
		$sql = $sql_prefix . ' ';
		
		$xfields = $instruction->xpath ( './' . $xchild );
		// print_object($xfields);
		
		foreach ( $xfields as $xfield ) {
			$optional = isset ( $xfield ['optional'] ) ? ( string ) $xfield ['optional'] : '';
			$condition = isset ( $xfield ['condition'] ) ? ( string ) trim ( $xfield ['condition'] ) : '';
			
			if ($condition != '') {
				// Parse the conditionExpr
				$eval = new \Kuink\Core\EvalExpr ();
				try {
					$result = $eval->e ( $condition, $params, TRUE );
				} catch ( \Exception $e ) {
					var_dump( 'Exception: eval' );
					die ();
				}
				if ($result)
					$sql .= ( string ) $xfield [0] . ' ';
			} else if ($optional != '') {
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
			
		return $sql;
	}

	public function getEntity($params) {
		$this->connect ();
		$database = $this->dataSource->getParam ( 'database', true );
		
		$entName = ( string ) $params ['_entity'];
		$this->db->exec ('SET GLOBAL innodb_stats_on_metadata=0;');
		$sql = "
		SELECT
 					c.ordinal_position as 'id',
 					c.column_name as 'name',
 					c.column_default as 'default',
 					IF(c.is_nullable = 'NO', 'true', 'false') as 'required',
 					c.data_type as 'type',
 					IF(c.character_maximum_length IS NULL, replace(replace(c.column_type, concat(c.data_type,'('),''),')', ''), c.character_maximum_length) as 'attributes',
					IF(c.character_maximum_length IS NULL, SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(c.column_type, '(', -1), ')', ''), ' ', 1), c.character_maximum_length) as 'length',
					(REGEXP_SUBSTR(c.column_type, '(?<=\\\\)).*')) as 'modifiers',
					c.column_key as 'key',
 					k.referenced_table_name as 'datasource',
 					k.referenced_column_name as 'bindid',
					c.extra,
					c.column_comment as comment
 				FROM
 					INFORMATION_SCHEMA.COLUMNS c
 					LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON (c.column_name = k.column_name AND c.table_name = k.table_name)
  				WHERE
  					c.table_name = '$entName' AND c.table_schema ='$database';";
		$records = $this->executeSql ( $sql, null );
		return $records;
	}
	
	/**
	 * Get the changes needed to apply this entity definition to a given database
	 *
	 * @param string $application
	 *        	- The node application
	 * @param string $process
	 *        	- The node process
	 * @param string $node
	 *        	- The node containing the entity definition
	 */
	public function getEntityChanges($params) {
		$application = ( string ) $params ['application'];
		$process = isset($params ['process']) ? ( string ) $params ['process'] : null;
		$node = ( string ) $params ['node'];
		$dropTablesBeforeCreate = (isset ( $params ['dropTablesBeforeCreate'] )) ? ( string ) $params ['dropTablesBeforeCreate'] : 'false';
		$drop = ($dropTablesBeforeCreate == 'true') ? true : false;
		
		$nodeManager = new NodeManager ( $application, $process, NodeType::DATA_DEFINITION, $node );
		
		$nodeManager->load ();
		
		$entities = $nodeManager->getEntities ( $nodeManager );
		
		$entityChanges = $this->getEntitiesWithChanges ( $nodeManager, $entities, $drop );
		
		// print_object($entityChanges);
		
		return $entityChanges;
	}
  
  	private function domainToArray($domain, $nodeManager) {
	  	$domArray = Array();
	  	
	  	if ($domain === null)
			return null;  	
	  	
			$domAttrs = $domain->attributes();
	  	
	  	foreach($domAttrs as $key=>$value)
	  		$domArray[$key] = (string)$value;
	  	
	  	return $domArray;
	}
  
	private function entityToArray($entity, $nodeManager) {
		// print_object($entity);
		$parent = current ( $entity->xpath ( 'parent::*' ) );
		$name = $this->getAttribute ( $parent, 'name', false, 'entity' );
		$multilang = $this->getAttribute ( $parent, 'multilang', false, 'multilang', 'false' );
		$doc = $this->getAttribute ( $parent, 'doc', false, 'doc', '' );
		$doc = wordwrap($doc, 50, '<br/>');
		
		$entArray = Array ();
		$entArray ['__attributes'] = array (
				'name' => $name,
				'multilang' => $multilang,
				'doc' => $doc,
		);
		
		foreach ( $entity->children () as $attrParent => $attr ) {
			$attrParentName = ( string ) $attrParent;
			if ($attrParentName == 'Attribute') {
				$entAttr = Array ();
				foreach ( $attr->attributes () as $attrsParent => $attrs ) {
					$attrName = ( string ) $attrsParent;
					$entAttr [$attrName] = ( string ) $attrs [0];
				}
				$entityAttrName = $entAttr ['name'];
				$entArray [$entityAttrName] = $entAttr;
			} else {
				$tplName = $this->getAttribute ( $attr, 'name', true, 'template' );
				$template = $nodeManager->getTemplate ( $tplName );
				if (! isset ( $template ))
					throw new \Kuink\Core\Exception\TemplateNotFound ( __CLASS__, $tplName );
					// print_object($template);
				$tplArray = $this->entityToArray ( $template, $nodeManager );
				// print_object($tplArray);
				unset ( $tplArray ['__attributes'] );
				$entArray = array_merge ( $entArray, $tplArray );
			}
		}
		// print_object($entAttr);
		return $entArray;
	}

	private function getEntitiesWithChanges($nodeManager, $entities, $drop = false) {
		$types = $this->getTypeConversion ();
		$changes = array ();
		foreach ( $entities as $entity ) {
			
			// Expand templates
			// print_object($entity);
			// $entTemplates = $entity->xpath('Attributes/Template');
			// print_object($entity->Attributes->Attribute);
			$entityArray = $this->entityToArray ( $entity->Attributes, $nodeManager );
			//print_object($entity);
			
			// Check to see if this entity is multilang, in this case we need to create two tables
			$multilang = $this->getAttribute ( $entity, 'multilang', false, 'multilang', 'false' );
			$name = $this->getAttribute ( $entity, 'name', true, 'multilang' );
			
			if ($multilang == 'true') {
				// Add this entity with no multilang attributes
				$entityArrayNoLang = Array ();
				$entityArrayNoLang ['__attributes'] = Array ();
				$entityArrayNoLang ['__attributes'] ['name'] = $name;
				$entityArrayNoLang ['__attributes'] ['multilang'] = $multilang;
				foreach ( $entityArray as $key => $field ) {
					$fieldMultilang = (isset ( $field ['multilang'] )) ? $field ['multilang'] : 'false';
					if ($fieldMultilang != 'true' && $key != '__attributes')
						$entityArrayNoLang [$field ['name']] = $field;
				}
				$entData = $this->getEntityWithChanges ( $nodeManager, $entityArrayNoLang, false, $drop );
				$changes [$entData ['name']] = $entData;

				// print_object($entityArray);
				
				// Add the corresponding multilang entity
				// $entData = $this->getEntityWithChanges( $nodeManager, $entityArray, true, $drop );
				// print_object($entData);
				$entityArrayLang = Array ();
				$entityArrayLang ['__attributes'] ['name'] = $name . '_lang';
				// $entityArrayLang['__attributes']['multilang'] = 'true';
				//$entityArrayLang['id'] = array('name'=>'id', 'domain'=>'id');
				$entityArrayLang['id'] = array('name'=>'id', 'domain'=>'foreignPk', 'refentity'=>$entData['name'], 'refattr'=>'id', 'pk'=>'true');
				$entityArrayLang['lang'] = array('name'=>'lang', 'domain'=>'langPk', 'pk'=>'true');

				foreach ( $entityArray as $key => $field ) {
					$fieldMultilang = (isset ( $field ['multilang'] )) ? $field ['multilang'] : 'false';
					if ($fieldMultilang == 'true' && $key != '__attributes')
						$entityArrayLang [$field ['name']] = $field;
				}
				// print_object($entityArrayLang);
				$entData = $this->getEntityWithChanges ( $nodeManager, $entityArrayLang, false, $drop );
				// print_object($entData);
				
				$changes [$entData ['name']] = $entData;
				// print_object($entData);
			} else {
				// Just add this entity
				$entData = $this->getEntityWithChanges ( $nodeManager, $entityArray, false, $drop );
				$changes [$entData ['name']] = $entData;
				// print_object($entData);
			}
			
			// Build relations
			$relations = $entity->xpath ( './Relations/Relation' );
			// print_object($relations);
			foreach ( $relations as $relation ) {
				$minOccurs = '1';
				$maxOccurs = '1';
				$relName = '';
				$sourceAttr = '';
				$minOccurs = $this->getAttribute ( $relation, 'minoccurs', true, 'relation' );
				$maxOccurs = $this->getAttribute ( $relation, 'maxoccurs', true, 'relation' );
				if ($minOccurs == '*' && $maxOccurs == '*') {
					$relName = $this->getAttribute ( $relation, 'name', true, 'relation in *:*' );
					$sourceAttr = $this->getAttribute ( $relation, 'sourceattr', true, 'relation in *:*' );
				}
				
				$targetAttr = $this->getAttribute ( $relation, 'targetattr', true, 'relation' );
				
				// print_object($maxOccurs);
				foreach ( $relation as $relatedEntity ) {
					$entDataRelated = $this->getEntityWithChanges ( $nodeManager, $relatedEntity );
					
					// We need this domain here to create the foreign keys
					$domain = $nodeManager->getDomain ( 'foreign' );
					$typeConverted = isset ( $types [$domain ['type']] ) ? $types [$domain ['type']] : '';
					$domain ['convType'] = $typeConverted ['type'];
					$domain ['convLength'] = ($domain ['size'] != '') ? $domain ['size'] : $typeConverted ['size'];
					$domain ['physType'] = '';
					$domain ['physLength'] = '';
					
					// print_object($relatedEntity);
					if (($minOccurs == '1' && $maxOccurs == '*') || ($minOccurs == '1' && $maxOccurs == '1')) {
						// Add a foreign key in the related table
						$entDataRelated ['attributes'] [] = array (
								'name' => $targetAttr,
								'domain' => 'id',
								'foreign' => $entData ['name'],
								'required' => 'true',
								'pk' => 'false',
								'autonumber' => 'false',
								'domainDef' => $domain,
								'changes' => DDChanges::ADD 
						);
						// print_object($entDataRelated);
					} else {
						if (($minOccurs == '*' && $maxOccurs == '*')) {
							// Create the relation table
							// print_object($entDataRelated);
							
							$indexes = array ();
							$indexes [] = array (
									'name' => 'ix_' . $sourceAttr . '_' . $targetAttr,
									'unique' => 'true',
									'attrs' => $sourceAttr . ',' . $targetAttr 
							);
							$attributes = array ();
							$attributes [] = array (
									'name' => 'id',
									'domain' => 'id',
									'foreign' => 'false',
									'required' => 'true',
									'pk' => 'true',
									'autonumber' => 'true',
									'domainDef' => $domain,
									'changes' => DDChanges::ADD 
							);
							$attributes [] = array (
									'name' => $sourceAttr,
									'domain' => 'foreign',
									'foreign' => $entData ['name'],
									'required' => 'true',
									'pk' => 'false',
									'autonumber' => 'false',
									'domainDef' => $domain,
									'changes' => DDChanges::ADD 
							);
							$attributes [] = array (
									'name' => $targetAttr,
									'domain' => 'foreign',
									'foreign' => $entDataRelated ['name'],
									'required' => 'true',
									'pk' => 'false',
									'autonumber' => 'false',
									'domainDef' => $domain,
									'changes' => DDChanges::ADD 
							);
							$entityDataRelatedNN = array (
									'name' => $relName,
									'change' => DDChanges::ADD,
									'attributes' => $attributes,
									'indexes' => $indexes 
							);
							$changes [$relName] = $entityDataRelatedNN;
							// print_object($entityDataRelatedNN);
						} else {
							;
							// Invalid relation type
						}
					}
					// print_object($entDataRelated);
					$changes [$entDataRelated ['name']] = $entDataRelated;
				}
			}
		}
		
		return $changes;
	}

	private function getTypeConversion() {
		$KUINKSql = array ();
		
		$KUINKSql ['string'] = array (
				'type' => 'varchar',
				'size' => '' 
		);
		$KUINKSql ['text'] = array (
				'type' => 'text',
				'size' => '' 
		);
		$KUINKSql ['longtext'] = array (
				'type' => 'longtext',
				'size' => '' 
		);
		
		$KUINKSql ['longint'] = array (
				'type' => 'bigint',
				'size' => '10' 
		);
		$KUINKSql ['int'] = array (
				'type' => 'bigint',
				'size' => '' 
		);
		$KUINKSql ['bool'] = array (
				'type' => 'tinyint',
				'size' => '1' 
		);
		
		$KUINKSql ['date'] = array (
				'type' => 'bigint',
				'size' => '10' 
		);
		$KUINKSql ['time'] = array (
				'type' => 'bigint',
				'size' => '10' 
		);
		$KUINKSql ['datetime'] = array (
				'type' => 'bigint',
				'size' => '10' 
		);
		
		$KUINKSql ['float'] = array (
				'type' => 'float',
				'size' => '' 
		);
		$KUINKSql ['double'] = array (
				'type' => 'double',
				'size' => '' 
		);
		$KUINKSql ['decimal'] = array (
				'type' => 'decimal',
				'size' => '' 
		);
		
		return $KUINKSql;
	}
	
	/**
	 * This is the main function to get Attributes of an entity
	 */
	private function getAttributeWithChanges($entAttr, $nodeManager, $entityName) {
		$types = $this->getTypeConversion ();
		// print_object($entAttr);
		$attr = array ();
		
		foreach ( $entAttr->attributes () as $key => $value )
			$attr [$key] = ( string ) $value;
		
		return $this->getExpandedAttribute ( $attr, $nodeManager, $entityName );
	}

	private function getExpandedAttribute($attr, $nodeManager, $entityName) {
		$types = $this->getTypeConversion ();
		
		// The domain of entity attribute
		if ($attr ['domain'] != '') {
			$domain = $nodeManager->getDomain ( $attr ['domain'] );
			if (! isset ( $domain ))
				throw new DomainNotFound ( __CLASS__, $attr ['domain'] );
				// Replace the attributes with the domain values
			$domainAttributes = $domain->attributes ();
			foreach ( $domainAttributes as $key => $value ) {
				if ($key != 'name' && $key != 'doc') {
					$attr [$key] = isset ( $entAttr [$key] ) ? ( string ) $entAttr [$key] : ( string ) $value;
				}
			}
		}
		
		// print_object($attr);
		
		// Validate Required Attributes
		$this->checkRequiredAttribute ( $attr, 'name', $entityName );
		$this->checkRequiredAttribute ( $attr, 'type', $entityName );
		
		$refEntity = $this->getAttribute ( $attr, 'refentity', false, null, '' );

		if ($refEntity != '') {
			$refEntityObj = $nodeManager->getEntity ( $refEntity );
			// print_object($refEntity);
			// print_object($refEntityObj);
			if (! isset ( $refEntityObj )) {
				var_dump( 'Entity ' . $refEntity . ' not found' );
			}
		}
		
		// Compare the domain to see if there are any changes
		$typeConverted = isset ( $types [$attr ['type']] ) ? $types [$attr ['type']] : null;
		// print_object($typeConverted);
		if ($typeConverted) {
			$attr ['_physType'] = $typeConverted ['type'];
			$attr ['_physSize'] = $typeConverted ['size'];
		} else {
			throw new PhysicalTypeNotFound ( __CLASS__, $entityName, ( string ) $attr ['name'], ( string ) $attr ['type'] );
		}
		
		// print_object($attr);
		return $attr;
	}
	
	/**
	 * Get the entity attributes
	 * If this entity is multilang, the flag @$onlyMultilangAttributes is used to decide:
	 * true - Only get the multilang attributes
	 * false - Only get the non multilang attributes
	 */
	private function getEntityWithChanges($nodeManager, $entity, $onlyMultilangAttributes = false, $drop = false) {
		$data = array ();
		//print_object($entity);
		$name = $entity ['__attributes'] ['name']; // $this->getAttribute($entity, 'name', true, 'entity');
		$multilang = isset ( $entity ['__attributes'] ['multilang'] ) ? $entity ['__attributes'] ['multilang'] : 'false'; // $this->getAttribute($entity, 'multilang', false, 'multilang', 'false');
		$types = $this->getTypeConversion ();
		
		$change = DDChanges::ADD;
		$operation = DDChanges::ADD;
		
		// CURRENTLY ONLY SUPPORTS ADD
		// Check if the entity exists in physycal model (database)
		$physical = (! $drop) ? $this->getEntity ( array (
				'_entity' => $name 
		) ) : Array ();
		// print_object($physical);
		$attrs = array ();
		
		if (count ( $physical ) == 0) {
			$change = DDChanges::ADD;
			// print_object('The entity '.$name.' DOESNT exists... adding');
		} else {
			$change = DDChanges::NOTHING;
			// print_object('The entity '.$name.' EXISTS.... check differences');
			
			// Check for differenced physical --> logical
			foreach ( $physical as $physicalAttr ) {
				$attr = Array ();
				// The physical model
				$phName = $this->getAttribute ( $physicalAttr, 'name', true, 'physical' );
				$phRequired = $this->getAttribute ( $physicalAttr, 'required', false, 'physical', 'false' );
				$phType = $this->getAttribute ( $physicalAttr, 'type', false, 'physical' );
				$phLength = $this->getAttribute ( $physicalAttr, 'length', false, 'physical' );
				$phModifiers = $this->getAttribute ( $physicalAttr, 'modifiers', false, 'physical' );
				$phKey = $this->getAttribute ( $physicalAttr, 'key', false, 'physical' );
				$phDatasource = $this->getAttribute ( $physicalAttr, 'datasource', false, 'physical' );
				$phBindId = $this->getAttribute ( $physicalAttr, 'bindid', false, 'physical' );
				$phDefault = str_replace("'","",$this->getAttribute ( $physicalAttr, 'default', false, 'physical' ));
				$phComment = $this->getAttribute ( $physicalAttr, 'comment', false, '' );
				
				$phDebug = 'Name: ' . $phName . '; ';
				$phDebug = $phDebug . 'Required: ' . $phRequired . '; ';
				$phDebug = $phDebug . 'Type: ' . $phType . '; ';
				$phDebug = $phDebug . 'Length: ' . $phLength . '; ';
				$phDebug = $phDebug . 'Modifiers: ' . $phModifiers . '; ';
				$phDebug = $phDebug . 'Key: ' . $phKey . '; ';
				$phDebug = $phDebug . 'Datasource: ' . $phDatasource . '; ';
				$phDebug = $phDebug . 'BindId: ' . $phBindId . '; ';
				$phDebug = $phDebug . 'Default: ' . $phDefault . '; ';
				$phDebug = $phDebug . 'Comment: ' . $phComment . '; ';
				// print_object('PHYSYCAL- '.$phDebug);

				// The corresponding entity model
				$entAttr = isset($entity [$phName]) ? $entity [$phName] : null; // $entity->xpath('Attributes/Attribute[@name="'.$phName.'"]');
				// $entAttr = @$entAttr[0];
				// print_object($entAttr);
				
				$attrChanges = DDChanges::NOTHING;
				if (isset ( $entAttr )) {
					$attr ['name'] = ( string ) $this->getAttribute ( $entAttr, 'name', true, 'entity' );
					$attr ['domain'] = ( string ) $this->getAttribute ( $entAttr, 'domain', false, 'entity' );
					
					// The domain of entity attribute
					if ($attr ['domain'] != '') {
						$domain = $nodeManager->getDomain ( $attr ['domain'] );
						if (! isset ( $domain ))
							throw new DomainNotFound ( __CLASS__, $attr ['domain'] );
					} else {
						$domain ['name'] = ( string ) $attr ['domain'];
						$domain ['type'] = ( string ) $this->getAttribute ( $entAttr, 'type', true, 'entity' );
						$domain ['size'] = ( string ) $this->getAttribute ( $entAttr, 'size', false, 'entity' );
						$domain ['unsigned'] = ( string ) $this->getAttribute ( $entAttr, 'unsigned', false, 'entity' );
						$domain ['zerofill'] = ( string ) $this->getAttribute ( $entAttr, 'zerofill', false, 'entity' );
						$domain ['autonumber'] = ( string ) $this->getAttribute ( $entAttr, 'autonumber', false, 'entity' );
						$domain ['pk'] = ( string ) $this->getAttribute ( $entAttr, 'pk', false, 'entity' );
						$domain ['required'] = ( string ) $this->getAttribute ( $entAttr, 'required', false, 'entity', 'false' );
					}
					
					$attr ['pk'] = isset($domain ['pk']) ? $domain ['pk'] : ''; $attr ['pk'] = isset($entAttr ['pk']) ? $entAttr ['pk'] : $attr['pk'];
					$attr ['unsigned'] = isset($domain ['unsigned']) ? $domain ['unsigned'] : ''; $attr ['unsigned'] = isset($entAttr ['unsigned']) ? $entAttr ['unsigned'] : $attr['unsigned'];
					$attr ['zerofill'] = isset($domain ['zerofill']) ? $domain ['zerofill'] : ''; $attr ['zerofill'] = isset($entAttr ['zerofill']) ? $entAttr ['zerofill'] : $attr['zerofill'];
					$attr ['autonumber'] = isset($domain ['autonumber']) ? $domain ['autonumber'] : ''; $attr ['autonumber'] = isset($entAttr ['autonumber']) ? $entAttr ['autonumber'] : $attr['autonumber'];
					$attr ['required'] = isset($domain ['required']) ? $domain ['required'] : ''; $attr ['required'] = isset($entAttr ['required']) ? $entAttr ['required'] : $attr['required'];
					$attr ['foreign'] = isset($domain ['foreign']) ? $domain ['foreign'] : ''; $attr ['foreign'] = isset($entAttr ['foreign']) ? $entAttr ['foreign'] : $attr['foreign'];
					$attr ['multilang'] = isset($domain ['multilang']) ? $domain ['multilang'] : ''; $attr ['multilang'] = isset($entAttr ['multilang']) ? $entAttr ['multilang'] : $attr['multilang'];
					$attr ['refentity'] = isset($domain ['refentity']) ? $domain ['refentity'] : ''; $attr ['refentity'] = isset($entAttr ['refentity']) ? $entAttr ['refentity'] : $attr['refentity'];
					$attr ['refattr'] = isset($domain ['refattr']) ? $domain ['refattr'] : ''; $attr ['refattr'] = isset($entAttr ['refattr']) ? $entAttr ['refattr'] : $attr['refattr'];
					$attr ['type'] = isset($domain ['type']) ? $domain ['type'] : ''; $attr ['type'] = isset($entAttr ['type']) ? $entAttr ['type'] : $attr['type'];
					$attr ['size'] = isset($domain ['size']) ? $domain ['size'] : ''; $attr ['size'] = isset($entAttr ['size']) ? $entAttr ['size'] : $attr['size'];
					$attr ['default'] = isset($domain ['default']) ? $domain ['default'] : ''; $attr ['default'] = isset($entAttr ['default']) ? $entAttr ['default'] : $attr['default'];
					$attr ['comment'] = isset($domain ['comment']) ? $domain ['comment'] : ''; $attr ['comment'] = isset($entAttr ['doc']) ? $entAttr ['doc'] : $attr['doc'];
					
					// Compare the domain to see if there is any changes
					$domType = ( string ) $domain ['type'];
					$typeConverted = isset ( $types [$domType] ) ? $types [$domType] : Array ();
					// print_object($typeConverted);
					
					// print_object($domain);
					$domain ['convType'] = $typeConverted ['type'];
					$domain ['convLength'] = ($domain ['size'] != '') ? $domain ['size'] : $typeConverted ['size'];
					$domain ['physType'] = $phType;
					$domain ['physLength'] = $phLength;
					
					$attr ['_physType'] = ( string ) $domain ['convType'];
					$attr ['_physSize'] = ( string ) $domain ['convLength'];
					
					$check ['type'] = $domain ['convType'];
					$check ['length'] = ($domain ['convLength'] != '') ? ( string ) $domain ['convLength'] : ( string ) $phLength; // If there's no length in domain or attribute, then use the physical
					$check ['required'] = ($attr ['required'] != '') ? ( string ) $attr ['required'] : ( string ) $domain ['required'];
					$check ['required'] = ($check ['required'] != '') ? ( string ) $check ['required'] : 'false';
					$check ['unsigned'] = ($attr ['unsigned'] != '') ? ( string ) $attr ['unsigned'] : ( string ) $domain ['unsigned'];
					$check ['unsigned'] = ($check ['unsigned'] != '') ? ( string ) $check ['unsigned'] : 'false';
					$check ['zerofill'] = ($attr ['zerofill'] != '') ? ( string ) $attr ['zerofill'] : ( string ) $domain ['zerofill'];
					$check ['zerofill'] = ($check ['zerofill'] != '') ? ( string ) $check ['zerofill'] : 'false';
					$check ['default'] = ($attr ['default'] != '') ? ( string ) $attr ['default'] : ( string ) $domain ['default'];
					$check ['comment'] = ($attr ['comment'] != '') ? ( string ) $attr ['comment'] : ( string ) $domain ['comment'];

					if ($check ['type'] != $phType || 
						$check ['length'] != $phLength || 
						$check ['required'] != $phRequired || 
						(($check ['default'] != $phDefault) && ($phDefault != 'NULL')) || 
						$check ['comment'] != $phComment  ||
						(($check ['unsigned'] == 'true' && $check ['zerofill'] != 'true') xor ( strpos($phModifiers, 'unsigned') !== false && strpos($phModifiers, 'zerofill') === false )) ||
						($check ['zerofill'] == 'true' xor  strpos($phModifiers, 'zerofill') !== false) ) {
						$change = DDChanges::CHANGE;
						$attrChanges = DDChanges::CHANGE;
					} else
						$attrChanges = DDChanges::NOTHING;
						
						// $attr['domainDef'] = (array)$domain;
					$attr ['debug'] = '';
					if ($attrChanges == DDChanges::CHANGE)
						$attr ['debug'] .= ' <i class="fa fa-arrow-circle-right" style="color:#0044cc">&nbsp;Change&nbsp;</i>';
					
					$attr ['debug'] .= '<strong>' . $attr ['name'] . '</strong> ' . $check ['type'] . '(' . $check ['length'] . ')';
					if ($check ['zerofill'] == 'true')
						$attr ['debug'] .= ' unsigned zerofill';
					elseif ($check ['unsigned'] == 'true')
						$attr ['debug'] .= ' unsigned';
					if ($check ['required'] == 'true')
						$attr ['debug'] .= ' required';
					if ($domain ['pk'] == 'true')
						$attr ['debug'] .= ' pk';
					if ($domain ['autonumber'] == 'true')
						$attr ['debug'] .= ' autonumber';
					if ($attr ['default'] != '')
						$attr ['debug'] .= ' default(' . $attr ['default'] . ')';
					$attr ['debug'] .= ' comment(' . $attr ['comment'] . ')';
					
					if ($attrChanges == DDChanges::CHANGE) {
						$phRequiredStr = ($phRequired == 'true') ? 'required' : '';
						$attr ['debug'] .= ' <i>from&nbsp;</i>' . $phName . ' ' . $phType . '(' . $phLength . ') ' . $phModifiers . ' ' . $phRequiredStr;
						if ($phDefault != '')
							$attr ['debug'] .= ' default(' . $phDefault . ')';
						$attr ['debug'] .= ' comment(' . $phComment . ')';
					}
					// print_object($attr['debug']);
				} else {
					// The attribute is in physycal but not in entity definition, so remove it
					$attr ['debug'] = '';
					$attr ['name'] = ( string ) $phName;
					$attr ['type'] = ( string ) $phType;
					$attr ['_physType'] = ( string ) $phType;
					$attr ['size'] = ( string ) $phLength;
					$attr ['_physSize'] = ( string ) $phLength;
					$attr ['debug'] .= '<strong>' . $attr ['name'] . '</strong> ' . $attr ['type'] . '(' . $attr ['size'] . ')';
					
					$pos = strpos ( $attr ['name'], '__rem_' );
					if ($pos === false) {
						$change = DDChanges::CHANGE;
						$attrChanges = DDChanges::REMOVE;
						$attr ['required'] = 'false'; // As the attribute will not be removed, it must not be required
						$attr ['default'] = ( string ) $phDefault;
						$attr ['newName'] = '__rem_' . $phName;
						
						$attr ['debug'] = ' <i class="fa fa-times-circle" style="color:#da4f49">&nbsp;Remove&nbsp;</i>';
						$attr ['debug'] .= '<strong>' . $phName . '</strong> ' . $phType . '(' . $phLength . ')';
					} else {
						$attrChanges = DDChanges::NOTHING;
					}
				}
				$attr ['changes'] = $attrChanges;
				
				// Compare them and check for changes...
				$attrs [$attr ['name']] = $attr;
			}
		}
		// print_object($change);
		$operation = $change; // (count($physical) == 0) ? DDChanges::ADD : DDChanges::CHANGE;
		                      
		// Check for new fields logical --> physical
		$entAttrs = $entity; // $entity->xpath('Attributes/Attribute');
		unset ( $entAttrs ['__attributes'] );
		//print_object($name);
		//print_object($entAttrs);
		
		$previousAttr = null;
		foreach ( $entAttrs as $entAttr ) {
			$attrName = $entAttr ['name'];
			// Check if the attr is in physical definition, if not add it to the entity
			$phFound = false;
			foreach ( $physical as $phAttr ) {
				$phAttrName = $phAttr ['name'];
				if ($phAttrName == $attrName)
					$phFound = true;
			}
			
			if (! $phFound) {
				//print_object($attrName);
				$attr = Array ();
				$attr ['debug'] = '';
				$attr ['name'] = $this->getAttribute ( $entAttr, 'name', true, 'entity' );
				$attr ['domain'] = $this->getAttribute ( $entAttr, 'domain', false, 'entity' );
				$attr ['foreign'] = $this->getAttribute ( $entAttr, 'foreign', false, 'entity' );
				if ($attr ['domain'] != '') {
					$domain = $nodeManager->getDomain ( $attr ['domain'] );
					$domain = $this->domainToArray ( $domain, $nodeManager );
					if (! isset ( $domain ))
						throw new DomainNotFound ( __CLASS__, $attr ['domain'] );
					
				} else {
					$domain ['name'] = $attr ['domain'];
					$domain ['type'] = $this->getAttribute ( $attr, 'type', true, 'entity' );
					$domain ['size'] = $this->getAttribute ( $attr, 'size', false, 'entity' );
					$domain ['pk'] = $this->getAttribute ( $attr, 'pk', false, 'entity', 'false' );
					$domain ['unsigned'] = $this->getAttribute ( $attr, 'unsigned', false, 'entity' );
					$domain ['zerofill'] = $this->getAttribute ( $attr, 'zerofill', false, 'entity' );
					$domain ['autonumber'] = $this->getAttribute ( $attr, 'autonumber', false, 'entity' );
					$domain ['required'] = ($attr ['required'] == '') ? $this->getAttribute ( $attr, 'required', false, 'entity', 'false' ) : $attr ['required'];
				}
				
				$attr ['pk'] = isset($domain ['pk']) ? $domain ['pk'] : ''; $attr ['pk'] = isset($entAttr ['pk']) ? $entAttr ['pk'] : $attr['pk'];
				$attr ['unsigned'] = isset($domain ['unsigned']) ? $domain ['unsigned'] : ''; $attr ['unsigned'] = isset($entAttr ['unsigned']) ? $entAttr ['unsigned'] : $attr['unsigned'];
				$attr ['zerofill'] = isset($domain ['zerofill']) ? $domain ['zerofill'] : ''; $attr ['zerofill'] = isset($entAttr ['zerofill']) ? $entAttr ['zerofill'] : $attr['zerofill'];
				$attr ['autonumber'] = isset($domain ['autonumber']) ? $domain ['autonumber'] : ''; $attr ['autonumber'] = isset($entAttr ['autonumber']) ? $entAttr ['autonumber'] : $attr['autonumber'];
				$attr ['required'] = isset($domain ['required']) ? $domain ['required'] : 'false'; $attr ['required'] = isset($entAttr ['required']) ? $entAttr ['required'] : $attr['required'];
				$attr ['foreign'] = isset($domain ['foreign']) ? $domain ['foreign'] : ''; $attr ['foreign'] = isset($entAttr ['foreign']) ? $entAttr ['foreign'] : $attr['foreign'];
				$attr ['multilang'] = isset($domain ['multilang']) ? $domain ['multilang'] : ''; $attr ['multilang'] = isset($entAttr ['multilang']) ? $entAttr ['multilang'] : $attr['multilang'];
				$attr ['refentity'] = isset($domain ['refentity']) ? $domain ['refentity'] : ''; $attr ['refentity'] = isset($entAttr ['refentity']) ? $entAttr ['refentity'] : $attr['refentity'];
				$attr ['refattr'] = isset($domain ['refattr']) ? $domain ['refattr'] : ''; $attr ['refattr'] = isset($entAttr ['refattr']) ? $entAttr ['refattr'] : $attr['refattr'];
				$attr ['type'] = isset($domain ['type']) ? $domain ['type'] : ''; $attr ['type'] = isset($entAttr ['type']) ? $entAttr ['type'] : $attr['type'];
				$attr ['size'] = isset($domain ['size']) ? $domain ['size'] : ''; $attr ['size'] = isset($entAttr ['size']) ? $entAttr ['size'] : $attr['size'];
				$attr ['default'] = isset($domain ['default']) ? $domain ['default'] : ''; $attr ['default'] = isset($entAttr ['default']) ? $entAttr ['default'] : $attr['default'];
				$attr ['comment'] = isset($domain ['comment']) ? $domain ['comment'] : ''; $attr ['comment'] = isset($entAttr ['doc']) ? $entAttr ['doc'] : $attr['doc'];

				$domType = ( string ) $domain ['type'];
				$typeConverted = isset ( $types [$domType] ) ? $types [$domType] : Array ();
				$domain ['convType'] = $typeConverted ['type'];
				$domain ['convLength'] = isset($typeConverted ['size']) ? $typeConverted ['size'] : '';
				$domain ['convLength'] = (isset($domain ['size']) && $domain ['size'] != '') ? $domain ['size'] : $domain ['convLength'];
				$check ['type'] = $domain ['convType'];
				$check ['length'] = $domain ['convLength'];
				$check ['unsigned'] = $attr ['unsigned'];
				$check ['zerofill'] = $attr ['zerofill'];
				$check ['required'] = $attr ['required'];
				//$check ['required'] = ($check ['required']) ? $check ['required'] : 'false';
				
				$attr ['_physType'] = $domain ['convType'];
				$attr ['_physSize'] = $domain ['convLength'];
				
				// print_object($domain);
				if (count ( $physical ) == 0)
					$change = DDChanges::ADD;
				else
					$change = DDChanges::CHANGE;
					
					// print_object($change);
				
				$attr ['changes'] = DDChanges::ADD;
				
				if ($attr ['changes'] == DDChanges::ADD) {
					// $phRequiredStr = ($phRequired == 'true') ? 'required' : '';
					$attr ['debug'] .= ' <i class="fa fa-plus-circle" style="color:#5bb75b">&nbsp;Add&nbsp;</i>';
					$attr['after'] = isset($previousAttr) ? $previousAttr['name'] : '';
				}
				$attr ['debug'] .= '<strong>' . $entAttr ['name'] . '</strong> ' . $domain ['convType'] . '(' . $domain ['convLength'] . ')';
				if ($check ['zerofill'] == 'true')
					$attr ['debug'] .= ' unsigned zerofill';
				elseif ($check ['unsigned'] == 'true')
						$attr ['debug'] .= ' unsigned';
				if ($check ['required'] == 'true')
					$attr ['debug'] .= ' required';
				if ($attr ['pk'] == 'true')
					$attr ['debug'] .= ' pk';
				if ($attr ['autonumber'] == 'true')
					$attr ['debug'] .= ' autonumber';
				if ($attr ['default'] != '')
					$attr ['debug'] .= ' default(' . $attr ['default'] . ')';
				$attr ['debug'] .= ' comment(' . $attr ['comment'] . ')';

				$attrMultiLang = $this->getAttribute ( $attr, 'multilang', false, 'multilang', 'false' );
				$attrMultiLang = ($attrMultiLang == '') ? 'false' : $attrMultiLang;
				// print_object($attrMultiLang);
				if (($multilang == 'true')) {
					//var_dump($onlyMultilangAttributes);
					//var_dump($attrMultiLang);
					if ($onlyMultilangAttributes && ($attrMultiLang == 'true'))
						$attrs [$attrName] = $attr;
					else if (! $onlyMultilangAttributes && ($attrMultiLang == 'false'))
						$attrs [$attrName] = $attr;
				} else {
					// print_object($attr);
					$attrs [$attrName] = $attr;
				}
			}
			$previousAttr = $entAttr;
		}
		
		$data ['entity'] = $name;
		$data ['name'] = $name;
		$data ['change'] = $change;
		$data ['attributes'] = $attrs;
		
		return $data;
	}

	public function applyEntityChanges($params) {
		$entityChanges = $this->getEntityChanges ( $params );
		// print_object($entityChanges);
		
		// CreateForeignKeyIndexes?
		$createForeignKeyIndexes = (isset ( $params ['createForeignIndexes'] )) ? ( string ) $params ['createForeignIndexes'] : 'false';
		$removeExistingIndexes = (isset ( $params ['removeExistingIndexes'] )) ? ( string ) $params ['removeExistingIndexes'] : 'false';
		$createForeignKeys = (isset ( $params ['createForeignKeys'] )) ? ( string ) $params ['createForeignKeys'] : 'false';
		$removeExistingForeignKeys = (isset ( $params ['removeExistingForeignKeys'] )) ? ( string ) $params ['removeExistingForeignKeys'] : 'false';
		$dropTablesBeforeCreate = (isset ( $params ['dropTablesBeforeCreate'] )) ? ( string ) $params ['dropTablesBeforeCreate'] : 'false';

		// build the SQL Statement
		$log = array ();
		$sqlStatementsArray = array ();
		$sqlForeignKeysArray = array ();
		
		foreach ( $entityChanges as $entity ) {
			if ($entity ['change'] == DDChanges::NOTHING)
				continue;
			$sqlStatement = '';
			// print_object($entity);
			if ($dropTablesBeforeCreate == 'true') {
				$sqlStatement .= 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS ' . $this->encloseIdentifier($entity ['name']) . '; ';
			}
			if ($entity ['change'] == DDChanges::ADD) {
				$sqlStatement .= 'CREATE TABLE IF NOT EXISTS ' . $this->encloseIdentifier($entity ['name']) . ' (';
			} else if ($entity ['change'] == DDChanges::CHANGE) {
				$sqlStatement .= 'ALTER TABLE ' . $this->encloseIdentifier($entity ['name']) . ' ';
			}
			$sqlAttributesArray = array ();
			$sqlPrimaryKeysArray = array ();
			$sqlUniquesArray = array ();
			$previousAttribute = null;
			foreach ( $entity ['attributes'] as $attribute ) {
				//kuink_mydebugObj($entity['name'], $attribute);
				$sqlAttribute = '';
				$sqlAttribute .= $this->encloseIdentifier($attribute ['name']);
				if (isset($attribute ['newName']) && $attribute ['newName'] != '')
					$sqlAttribute .= ' ' . $this->encloseIdentifier($attribute ['newName']); // If this is set then this attribute is to be renamed to this newName
				$sqlAttribute .= ' ' . $attribute ['_physType'] . ' ';
				if (($attribute ['_physSize'] != ''))
					$sqlAttribute .= ' (' . $attribute ['_physSize'] . ')';
				else if (($attribute ['size'] != ''))
					$sqlAttribute .= ' (' . $attribute ['size'] . ')';
					// print_object($attribute);
				$sqlAttribute .= ($attribute ['zerofill'] == 'true') ? ' UNSIGNED ZEROFILL' : (($attribute ['unsigned'] == 'true') ? ' UNSIGNED' : '');
				$sqlAttribute .= ($attribute ['required'] == 'true') ? ' NOT NULL' : '';
				$sqlAttribute .= ($attribute ['autonumber'] == 'true') ? ' AUTO_INCREMENT' : '';
				$sqlAttribute .= ($attribute ['default'] != '') ? ' DEFAULT \'' . ( string ) $attribute ['default'] . '\'' : '';
				$sqlAttribute .= ' COMMENT \'' . ( string ) $attribute ['comment'] . '\'';
				$pk = $this->getAttribute ( $attribute, 'pk', false, null, 'false' );
				if ($pk == 'true') {
					$sqlPrimaryKeysArray [] = $attribute ['name'];
				}
				$refEntity = $this->getAttribute ( $attribute, 'refentity', false, null, '' );
				if ($refEntity != '') {
					$refAttr = $this->getAttribute ( $attribute, 'refattr', true, $refEntity, '' );
					
					// Check to see if this entity in in another node
					if (strpos ( $refEntity, ',' )) {
						// This is an entity that is in another data definition node
						$splitedName = explode ( ',', $refEntity );
						if (count ( $splitedName ) != 3)
							throw new \Kuink\Core\Exception\InvalidName ( __CLASS__, $refEntity );
						$application = $splitedName [0];
						$node = $splitedName [1];
						$refEntityName = $splitedName [2];
						
						$sqlForeignKeysArray [] = array (
								'entity' => $entity ['name'],
								'attribute' => $attribute ['name'],
								'refentity' => $refEntityName,
								'refattr' => $refAttr 
						);
					} else {
						// In this node
						$sqlForeignKeysArray [] = array (
								'entity' => $entity ['name'],
								'attribute' => $attribute ['name'],
								'refentity' => $refEntity,
								'refattr' => $refAttr 
						);
					}
				}
				$unique = $this->getAttribute ( $attribute, 'unique', false, null, 'false' );
				if ($unique == 'true') {
					$sqlUniquesArray [] = $attribute ['name'];
				}
				// print_object($attribute);
				if ($attribute ['changes'] == DDChanges::ADD && $entity ['change'] == DDChanges::ADD)
					$sqlAttributesArray [] = $sqlAttribute;
				else if ($attribute ['changes'] == DDChanges::ADD && $entity ['change'] == DDChanges::CHANGE) {
					$sqlAttributePlain = 'ADD ' . $sqlAttribute . ' ';
					if ($attribute ['after'] != '') 
						$sqlAttributePlain .= ' AFTER '.$this->encloseIdentifier($attribute['after']);
					$sqlAttributesArray [] = $sqlAttributePlain;
				}
				else if ($attribute ['changes'] == DDChanges::CHANGE)
					$sqlAttributesArray [] = 'MODIFY ' . $sqlAttribute;
				else if ($attribute ['changes'] == DDChanges::REMOVE) {
					// print_object($sqlAttribute);
					$sqlAttributesArray [] = 'CHANGE ' . $sqlAttribute;
				}
				$previousAttribute = $attribute;
			}
			// print_object($sqlAttributesArray);
			
			$sqlAttributes = implode ( ',', $sqlAttributesArray );
			if ($entity ['change'] == DDChanges::ADD) {
				if (count ( $sqlPrimaryKeysArray ) > 0) {
					$sqlPrimarykeys = implode ( ',', $sqlPrimaryKeysArray);
					$sqlPrimarykeys = ' ,PRIMARY KEY (' . $sqlPrimarykeys . ')';
				} else {
					throw new \Kuink\Core\Exception\PrimaryKeyNotFound ( __CLASS__, $entity ['name'] );
					$sqlPrimarykeys = '';
				}
			}
			
			if ($entity ['change'] == DDChanges::ADD) {
				$sqlStatement .= $sqlAttributes . ' ' . $sqlPrimarykeys . ' )';
			} else if ($entity ['change'] == DDChanges::CHANGE) {
				$sqlStatement .= $sqlAttributes . ' ';
			}
			
			$sqlStatementsArray [$entity ['name']] = $sqlStatement;
			
			// Add unique indexes
			foreach ( $sqlUniquesArray as $uk ) {
				$sqlStatement = 'CREATE UNIQUE INDEX '.$this->encloseIdentifier('ix_' . $uk) . ' ON ' . $this->encloseIdentifier($entity ['name']) . ' ( ' . $this->encloseIdentifier($uk) . ');';
				$sqlStatementsArray [$entity ['name'] . ':ix_' . $uk] = $sqlStatement;
			}
			
			// print_object($entity);
			// print_object($sqlStatement);
			
			TraceManager::add ( $sqlStatement, TraceCategory::SQL, __CLASS__.'::'.__METHOD__ );
		}
		if ($removeExistingForeignKeys == 'true') {
			$database = $this->dataSource->getParam ( 'database', true );
			$table = $entity ['name'];
			$sqlStatementExistingFKs = "
				SELECT CONSTRAINT_NAME
				FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
				WHERE
					CONSTRAINT_SCHEMA = '$database' AND	TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL;";
			$existingFKs = $this->executeSql ( $sqlStatementExistingFKs, null );
			foreach ( $existingFKs as $fk ) {
				$sqlStatement = 'ALTER TABLE  ' . $this->encloseIdentifier($table) . ' DROP FOREIGN KEY ' . $this->encloseIdentifier($fk['CONSTRAINT_NAME']) . ';';
				$sqlStatementsArray ['-'.$table . ':' . $fk ['CONSTRAINT_NAME']] = $sqlStatement;
			}
		}		
		if ($removeExistingIndexes == 'true') {
			$database = $this->dataSource->getParam ( 'database', true );
			$table = $entity ['name'];
			$sqlStatementExistingIndexes = "
				SELECT INDEX_NAME
				FROM INFORMATION_SCHEMA.STATISTICS
				WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table' AND INDEX_NAME != 'PRIMARY';";
			$existingIndexes = $this->executeSql ( $sqlStatementExistingIndexes, null );
			foreach ( $existingIndexes as $index ) {
				$sqlStatement = 'DROP INDEX ' . $this->encloseIdentifier($index['INDEX_NAME']) . ' ON ' . $this->encloseIdentifier($table) . ';';
				$sqlStatementsArray ['-'.$table . ':' . $index ['INDEX_NAME']] = $sqlStatement;
			}
		}
		// Add the foreign keys indexes after the table creations to avoid invalid references
		if ($createForeignKeyIndexes == 'true') {
			foreach ( $sqlForeignKeysArray as $fk ) {
				$sqlStatement = 'CREATE INDEX ' . $this->encloseIdentifier('ix_'.$fk ['attribute']) . ' ON ' . $this->encloseIdentifier($fk ['entity']) . ' ( ' . $this->encloseIdentifier($fk ['attribute']) . ');';
				$sqlStatementsArray ['+'.$fk ['entity'] . ':ix_' . $fk ['attribute']] = $sqlStatement;
			}
		}
		if ($createForeignKeys == 'true') {
			foreach ( $sqlForeignKeysArray as $fk ) {
				// ALTER TABLE Orders ADD CONSTRAINT fk_PerOrders FOREIGN KEY (P_Id) REFERENCES Persons(P_Id)
				
				$sqlStatement = 'ALTER TABLE  ' . $this->encloseIdentifier($fk ['entity']) . ' ADD CONSTRAINT ' .$this->encloseIdentifier( 'fk_'.$fk ['entity'] . '_' . $fk ['attribute']) . ' FOREIGN KEY(' . $this->encloseIdentifier($fk ['attribute']) . ') REFERENCES ' . $this->encloseIdentifier($fk ['refentity']) . '( ' . $this->encloseIdentifier($fk ['refattr']) . ');';
				$sqlStatementsArray ['+'.$fk ['entity'] . ':fk_' . $fk ['attribute']] = $sqlStatement;
			}
		}
		
		// If it's all OK then Execute the SQL Statements
		
		foreach ( $sqlStatementsArray as $key => $sqlStatement ) {
			try {
				$this->executeSql ( $sqlStatement, null );
				$log [] = array (
						'entity' => $key,
						'status' => 'OK',
						'sqlStatement' => $sqlStatement 
				);
			} catch ( \Exception $e ) {
				$log [] = array (
						'entity' => $key,
						'status' => 'ERROR',
						'sqlStatement' => $sqlStatement 
				);
			}
		}
		
		// print_object($log);
		return $log;
	}

	public function getLogicalEntities($params) {
		$application = ( string ) $params ['application'];
		$process = isset($params ['process']) ? ( string ) $params ['process'] : null;
		$node = ( string ) $params ['node'];
		
		$nodeManager = new NodeManager ( $application, $process, NodeType::DATA_DEFINITION, $node );
		
		$nodeManager->load ();

		/*
		if ($attr ['domain'] != '') {
			$domain = $nodeManager->getDomain ( $attr ['domain'] );
			$domain = $this->domainToArray ( $domain, $nodeManager );
			if (! isset ( $domain ))
				throw new DomainNotFound ( __CLASS__, $attr ['domain'] );
		}
		*/
		
		$entities = (array)$nodeManager->getEntities ( $nodeManager );
		$entitiesArray = array();
		foreach ( $entities as $entity ) {			
			$entityArray = $this->entityToArray ( $entity->Attributes, $nodeManager );
			$entitiesArray[$entityArray['__attributes'][name]] = $entityArray;
		}
		return $entitiesArray;
	}

	public function getLogicalEntitiesUml( $params ) {
		$application = ( string ) $params ['application'];
		$node = ( string ) $params ['node'];				
		$template = ( string ) $params ['template'];
		$template = ($template == '') ? 'medium' : $template;
		kuink_mydebug('Template', $template);
		$logicalEntities = $this->getLogicalEntities($params);		
		//get all nodes
		$diagram = new \Kuink\Core\Diagram();
		$diagram->addTitle('Entities: '.$application.','.$node);

		//Get nodes first
		$relations = array();
		foreach ($logicalEntities as $logicalEntity) {
			$attributes = array();
			foreach ($logicalEntity as $logicalEntityAttrs) {

				if (isset($logicalEntityAttrs['domain']) && ($logicalEntityAttrs['domain'] == 'foreign') && (isset($logicalEntityAttrs['refentity']))) {
					$refEntitySplit = explode(',', $logicalEntityAttrs['refentity']);
					$relations[] = array('from'=>end($refEntitySplit), 'to'=>$logicalEntity['__attributes']['name']);
					$attributes[] = $logicalEntityAttrs;
				} else {
					if (isset($logicalEntityAttrs['domain']) && ($logicalEntityAttrs['domain'] == 'id') ) {
						$attributes[] = $logicalEntityAttrs;
					} else {
						if ($template=='complete' )
							$attributes[] = $logicalEntityAttrs;
					}
				} 	
			}
			$diagram->addEntity($logicalEntity['__attributes']['name'], $logicalEntity['__attributes']['multilang'], $logicalEntity['__attributes']['doc'], $attributes);			
		}
		foreach ($relations as $relation) {
			$diagram->addEntityRelation($relation['from'], $relation['to']);
		}

		return $diagram->getUml();
	}

	private function getAttribute($arr, $key, $required, $context, $default = '') {
		if (! isset ( $arr [$key] ) && $required) {
			$a = var_export ( $arr, true );
			throw new ParameterNotFound ( __CLASS__, $a . ' | ' . $context, $key );
		}
		$value = isset ( $arr [$key] ) ? ( string ) $arr [$key] : $default;
		return $value;
	}

	private function checkRequiredAttribute($arr, $key, $entityName) {
		if (! isset ( $arr [$key] ))
			throw new ParameterNotFound ( __CLASS__, $entityName, $key );
		return;
	}

	function beginTransaction() {
		global $KUINK_CFG;

		if ($KUINK_CFG->useTransactions) {
			$this->connect ();
			if (! $this->db->inTransaction ()) {
				$this->db->beginTransaction ();
				parent::beginTransaction ();
				TraceManager::add ( 'Transactions are enabled... begin transaction', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
			}
			return;
		}	else
			TraceManager::add ( 'Transactions are disabled... skipping begin', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
	}

	function commitTransaction() {
		global $KUINK_CFG;

		if ($KUINK_CFG->useTransactions) {		
			$this->connect ();
			if ($this->db->inTransaction ()) {
				$this->db->commit ();
				parent::commitTransaction ();
				TraceManager::add ( 'Transactions are enabled... commit transaction', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
			}
		} else
			TraceManager::add ( 'Transactions are disabled... skipping commit', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
		
		return;
	}

	function rollbackTransaction() {
		global $KUINK_CFG;

		if ($KUINK_CFG->useTransactions) {		
		$this->connect ();
		if (isset($this->db))
			if ($this->db->inTransaction ()) {
				$this->db->rollBack ();
				parent::rollbackTransaction ();
				TraceManager::add ( 'Transactions are enabled... rollback transaction', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );
			}
		} else
		TraceManager::add ( 'Transactions are disabled... skipping rollback', TraceCategory::GENERAL, __CLASS__.'::'.__METHOD__ );

		
		return;
	}

  public function getSchemaName($params) {
  	$schemaName = (string)$this->dataSource->getParam('database', false);
  	return $schemaName;
	}  
	
	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from 
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public static function interpolateQuery($query, $params) {
		$keys = array();
		$paramsTransformed = $params;

		foreach ($params as $key => $value) {
			if (is_string($key))
				$keys[] = '/:'.$key.'/';
			else
				$keys[] = '/[?]/';
			if (is_string($value))
				$paramsTransformed[$key] = '\''.$value.'\'';
		}

		$query = preg_replace($keys, $paramsTransformed, $query, 1, $count);
	
		#trigger_error('replaced '.$count.' keys');

		return $query;
	}	
}

?>
