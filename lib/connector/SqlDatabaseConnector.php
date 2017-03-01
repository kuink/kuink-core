<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kuink\Core\DataSourceConnector;


use Kuink\Core\NodeManager;
use Kuink\Core\NodeType;
use Kuink\Core\Exception\ParameterNotFound;
use Kuink\Core\Exception\DomainNotFound;
use Kuink\Core\Exception\PhysicalTypeNotFound;

class DDChanges{
	const ADD = 'add';
	const REMOVE = 'remove';
	const CHANGE = 'change';
	const NOTHING = 'nothing';
}

/**
 * Description of SqlDatabaseConnector
 *
 * @author paulo.tavares
 */
class SqlDatabaseConnector extends \Kuink\Core\DataSourceConnector{

  var $db; //The PDO object containing the connection
    var $lastAffectedRows; //The affected rows of last statement

  function connect( ) {
  	//kuink_mydebug(__CLASS__, __METHOD__);

     if (! $this->db) {
      $type = $this->dataSource->getParam('type', true);
      $server = $this->dataSource->getParam('server', true);
      $database = $this->dataSource->getParam('database', true);
      $user = $this->dataSource->getParam('user', true);
      $passwd = $this->dataSource->getParam('passwd', true);
      $options = $this->dataSource->getParam('options', false);

      $dsn = "$type:host=$server;dbname=$database;$options";
      //var_dump($dsn);

      //Get the connection to the database
      $this->db = new \PDO($dsn, $user, $passwd);
      $this->db->exec("set names utf8"); //Handle the utf8 problem
     }
  }

  /**
   * This will receive the params of the statement and will transform
   * the preparedStatementXml into a PDO ready string
   * @param unknown $params
   * @return unknown
   */
  private function prepareStatementToExecute($params, $count=false) {
  	//Release the prepared statement for the next request
  	if (!isset($params['_sql']))
  		throw new \Exception('The param _sql was not supplied');

  	$xml = $params['_sql'];
  	//var_dump($xml->asXml());

  	$sqlXml = $xml->children();

  	$sql = (string)$this->xsql($sqlXml[0], $params, $count);

  	return $sql;
  }

  /**
   * inserts a record in the database and returns the inserted id
   * @see \Kuink\Core\DataSourceConnector::insert()
   */
  function insert($params) {
  	global $KUINK_TRACE;

  	$this->connect();
  	
  	$originalParams = $params;

  	if (isset($params['_multilang_fields'])) {
  		//Remove them from params
  		$multilangFields = (string)$this->getParam($params, '_multilang_fields', false, '');
  		$multilangFieldsArray = explode(',', $multilangFields);
  		foreach ($multilangFieldsArray as $multilangField)
  			unset($params[trim($multilangField)]);
  	}
  	
  	unset($params['_multilang_fields']);  	
  	if (isset($params['_sql'])) {
  		$sql = $this->prepareStatementToExecute($params);
  	} else {
  		$sql = $this->getPreparedStatementInsert($params);
  	}

  	$KUINK_TRACE[] = __METHOD__;
  	$KUINK_TRACE[] = $sql;

  	$this->executeSql($sql, $params);

    $insertId = $this->db->lastInsertId();
    //var_dump($insertId);
    
    //Handle the multilang
    $originalParams['id'] = $insertId;
    $this->handleMultilang($originalParams, 1); //1: Insert
    
    return (isset($params['id'])) ? $params['id'] : $insertId;
  }

  /**
   * Handle the multilang fields in the request
   * @param unknown $params - base params 
   * @param unknown $type - 1:Insert, 2:Update
   */
  function handleMultilang($params, $type) {
  	//Handle MULTILANG
  	$multilangFieldsArray = array();
  	if (isset($params['_multilang_fields'])) {
  		//Create the sql statement to update the lang keys
  		$idRefField = (string)$this->getParam($params, '_pk', false, 'id');
  		$idRef = (string)$this->getParam($params, $idRefField, false, 'id');
  		$multilangFields = (string)$this->getParam($params, '_multilang_fields', false, '');
  		//var_dump($idRef);
  		$multilangFieldsArray = explode(',', $multilangFields);
  		$multilangFieldsArraySize = count($multilangFieldsArray);
  		//var_dump($multilangFieldsArraySize);
  	
  		for ($i=0; $i<$multilangFieldsArraySize; $i++)
  			$multilangFieldsArray[$i] = trim($multilangFieldsArray[$i]);
  	
  			//To get all the language keys
  			$structure = $this->getParam($params, $multilangFieldsArray[0], false, '');
  			//var_dump($structure);
  	
  			//Check if the multilang record exists, if so update or else create!!
  			foreach($structure as $langCode => $langValue) {
  				$insertLangFields = array('id_ref' => $idRef, 'lang' => $langCode);
  			foreach($multilangFieldsArray as $multilangField) {
  				$multilangFieldData = $this->getParam($params, $multilangField, false, '');
  				$insertLangFields[$multilangField] = (string)$multilangFieldData[$langCode];
  			}

  			$insertLangFields['_entity'] = $params['_entity'].'_lang';
  			if ($type == 2) { //Update 
	  			$testLoadFields = array();
	  			$testLoadFields['_entity'] = $params['_entity'].'_lang';
				$testLoadFields['id_ref'] = $idRef;
				$testLoadFields['lang'] = $langCode;
	  			$testRecord = $this->load($testLoadFields);
	  							
	  			//If there is allready a record, then update it, else insert it
	  			//var_dump(count($testRecord) );
				if (count($testRecord) > 0) {
					$insertLangFields['id'] = $testRecord['id'];
					$this->update($insertLangFields);
				}
				else {
					$returnId = $this->insert($insertLangFields);
				}
  			} else if ($type == 1) { //Insert
  				$returnId = $this->insert($insertLangFields);
  			}
  		}
	}
  	return $multilangFieldsArray;
  }
  
  function update($params) {
  	global $KUINK_TRACE;

  	$this->connect();

  	$multilangFieldsArray = $this->handleMultilang($params, 2); //2: Update
  	//Remove the multilang fields from the main update
  	foreach ($multilangFieldsArray as $multilangField) {
  		unset($params[$multilangField]);
  	}
  	
  	if (isset($params['_sql'])) {
  		$sql = $this->prepareStatementToExecute($params);
  	} else {
  		$sql = $this->getPreparedStatementUpdate($params);
  	}

  	$KUINK_TRACE[] = __METHOD__;
  	$KUINK_TRACE[] = $sql;

	$this->executeSql($sql, $params);

    return $this->lastAffectedRows;
  }

  function save($params) {
  	global $KUINK_TRACE;

  	$this->connect();

  	$pk = $this->getParam($params, '_pk', false, 'id');

  	//Get the primary keys
  	$pks = explode(',', $pk);

  	//Check if all the primary keys are present, if so then update the record, else insert the record
  	$allPksPresent = true;
  	foreach($pks as $pk)
  		$allPksPresent = $allPksPresent && isset($params[$pk]);

  	$KUINK_TRACE[] = __METHOD__;
  	$KUINK_TRACE[] = ($allPksPresent) ? 'Save::Update' : 'Save::Insert';

  	if ($allPksPresent)
  		$result = $this->update($params);
  	else
  		$result = $this->insert($params);

  	return $result;
  }


  function delete($params) {
  	global $KUINK_TRACE;

  	$this->connect();

  	if (isset($params['_sql'])) {
  		$sql = $this->prepareStatementToExecute($params);
  	} else {
  		$sql = $this->getPreparedStatementDelete($params);
  	}

  	$KUINK_TRACE[] = __METHOD__;
  	$KUINK_TRACE[] = $sql;

	$this->executeSql($sql, $params);
    return $this->lastAffectedRows;
  }

  /***
   * For compatibility
  */
  function execute($params) {
  	global $KUINK_TRACE;

  	$this->connect();
  	$sql = $this->prepareStatementToExecute($params);
  	$KUINK_TRACE[] = __METHOD__;
  	$KUINK_TRACE[] = $sql;

  	$records = $this->executeSql($sql, $params);
  	return $records;
  }

  /***
   * For compatibility
   */
  function sql($params) {
  	global $KUINK_TRACE;

  	$this->connect();

  	$sql = $this->prepareStatementToExecute($params);

  	$KUINK_TRACE[] = __METHOD__;
  	$KUINK_TRACE[] = $sql;

  	$records = $this->executeSql($sql, $params);
  	return $records;
  }

  /***
   * For compatibility
  */
  function sqlPaginated($params) {
  	global $KUINK_TRACE;

  	$this->connect();

	$pageSize = 10;
	$pageNum = 0;

	$countSql = '';
	$querySql = '';

	//Test if the xsql node is in the SqlPaginated direct child
	//If so, get both count and query from it
	$xml = $params['_sql'];

	$xsql = $xml->xpath('./SqlPaginated/XSql');
	if (!empty($xsql))
	{
		$xsqlElements = $xml->xpath('./SqlPaginated');
		$xsqlElement = $xsqlElements[0];

		$countSql = $this->xsql($xsqlElement, $params, true);
		$querySql = $this->xsql($xsqlElement, $params, false);
	}
	else
	{
		$count_sql_node = $xml->xpath('./SqlPaginated/CountFieldsSql');
		$countSql = $this->xsql($count_sql_node[0], $params, false);
		//var_dump( $count_sql );

		$query_sql_node = $xml->xpath('./SqlPaginated/GetFieldsSql');
		$querySql = $this->xsql($query_sql_node[0], $params, false);
		//var_dump( $query_sql );
	}

	$pageNum = isset($params['_pageNum']) ? (int)$params['_pageNum'] : 0;
	$pageSize = isset($params['_pageSize']) ? (int)$params['_pageSize'] : 10;

	//var_dump($pageNum);var_dump($pageSize);
	//kuink_mydebug($pagenum, $pagesize);

	$KUINK_TRACE[] = "COUNT SQL: ".$countSql;

	//get the total number of records
	$total = $this->executeSql($countSql, $params); //$DB->count_records_sql($count_sql);
	$totalRecords = 0;
	foreach($total[0] as $total)
		$totalRecords = (int)$total;

	$KUINK_TRACE[] = "TOTAL RECORDS: ".$totalRecords;

	$limitFrom = ($pageNum)*$pageSize;
	$limitNum = $pageSize;

	//var_dump($limitFrom);var_dump($limitNum);

	//get the page records

	$params=array();
	if ($limitFrom != 0 || $limitNum != 0)
		$querySql .= ' LIMIT '.$limitFrom.','.$limitNum;

	$KUINK_TRACE[] = "SQL: ".$querySql;
	$records = $this->executeSql($querySql, $params);

	//Preparing the output
	$output['total'] = (string)$totalRecords;
	$output['records'] = $records;

	return $output;
  }

  private function transformMultilangData($data, $lang, $langInline) {
  	$ignoreKeys = array('id', 'id_ref');
  	$langKey = 'lang';

  	if (isset($data[0]))
  		$structure = $data[0];
  	else
  		return;

  	//var_dump($langInline);
  	if ($lang == '*' || $langInline == 'false') {
	  	//build an array with the name of the multilang keys
	  	$multilangKeys = array();
	  	foreach ($structure as $key=>$value) {
	  		if ($key != $langKey && !in_array($key, $ignoreKeys))
	  			$multilangKeys[] = $key;
	  	}
	  	
	  	//var_dump($multilangKeys);
	  	
	  	$resultdData = array();
	  	foreach($data as $record) {
	  		foreach($multilangKeys as $multilangKey) {
	  			//var_dump($record[$langKey].'::'.$record[$multilangKey]);
	  			$resultdData[$multilangKey][$record[$langKey]] = $record[$multilangKey];
	  		}
	  	}
  	} else {
  		//var_dump($data[0]);
  		//inject the translation directly in the field
  		foreach($data[0] as $key => $record) {
  			$resultdData[$key] = isset($record) ? $record : '';
  		}
  	}
  	
  	return $resultdData;  	
  }
  
  function load($params) {
    //kuink_mydebug(__CLASS__, __METHOD__);
    global $KUINK_TRACE;

    $this->connect();
    
    $lang = (string)$this->getParam($params, '_lang', false, '');
    $langInline = (string)$this->getParam($params, '_lang_inline', false, 'true');    
    $multilangTransformedRecords = array();
    if ($lang != '') {
    	$entity = (string)$this->getParam($params, '_entity', false, 'false');
    	//Get the multilang data
    	$paramsMultilang=array();
    	$paramsMultilang['_entity'] = $entity.'_lang';
    	$paramsMultilang['id_ref'] = $params['id'];
    	if ($lang != '*') {
    		$paramsMultilang['lang'] = $lang;
    	}
    	$multilangRecords = $this->getAll($paramsMultilang);
    	
    	$multilangTransformedRecords = $this->transformMultilangData($multilangRecords, $lang, $langInline);
    	//var_dump($multilangTransformedRecords);
    	unset($params['_lang']);
    	unset($params['_lang_inline']);
    }

    if (isset($params['_sql'])) {
  		$sql = $this->prepareStatementToExecute($params);
  	} else {
  		$sql = $this->getPreparedStatementSelect($params, true);
  	}

  	if ($this->db->inTransaction())
  		$sql .= ' FOR UPDATE';
  		 
		$KUINK_TRACE[] = __METHOD__;
		$KUINK_TRACE[] = $sql;

    $records = $this->executeSql($sql, $params, true, false);
    
   	$record = (count($records) > 0) ? $records[0] : null;
   	//add the multilang data if it is set
   	if (count($multilangTransformedRecords>0)) {
   		foreach ($multilangTransformedRecords as $key=>$multilangData)
   			$record[$key] = $multilangData;
   	}
   	
    return $record;
  }


  /***
   * For campatibility
   */
  function getRecords($params) {
  	//kuink_mydebug(__CLASS__, __METHOD__);
  	//For compatibility
  	return $this->getAll($params);
  }

  function dataset($params)
  {
  	$xml = $params['_sql'];

  	$datasets = $xml->xpath('./Dataset');
  	if (count($datasets)==0)
  		throw new \Exception('The method Dataset needs the DataSet element');

  	$dataset = $datasets[0];

  	$utils = new \UtilsLib();
  	$records = $utils->xmlToSet( array(0 => $dataset->asXML()));

  	//var_dump( $records );
  	return $records;
  }


  function getAll($params) {
  	global $KUINK_TRACE;

  	$this->connect();

  	$pageNum = $this->getParam($params, '_pageNum', false, 0);
  	$pageSize = $this->getParam($params, '_pageSize', false, 0);
  	$offset = $pageNum*$pageSize;	
  	
  	if ($lang != '' && $lang == '*')
  		throw new \Exception('Invalid l_lang value. Cannot be * in getAll');
  	
  	$countSql = '';
  	if (isset($params['_sql'])) {
  		$sql = $this->prepareStatementToExecute($params);
  		if ($pageNum != 0 || $pageSize != 0) {
  			$countSql = $this->prepareStatementToExecute($params, true);
  			$sql .= ' LIMIT '.$offset.','.$pageSize;
  		}

  	} else {
  		if ($pageNum != 0 || $pageSize != 0) 
  			$countSql = $this->getPreparedStatementSelectCount($params);
  		$sql = $this->getPreparedStatementSelect($params);
  	}


  	$totalRecords = 0;
  	if ($pageNum != 0 || $pageSize != 0) {
  		 $total = $this->executeSql($countSql, $params);
  		 foreach($total[0] as $total)
  		 	$totalRecords = (int)$total;
  	}

  	$KUINK_TRACE[] = __METHOD__;
  	if ($pageNum != 0 || $pageSize != 0) {
  		$KUINK_TRACE[] = 'CountSql';
  		$KUINK_TRACE[] = $countSql;
  		$KUINK_TRACE[] = 'Total: '.$totalRecords;
  	}

  	$KUINK_TRACE[] = $sql;

  	$records = $this->executeSql($sql, $params);
  	if ($pageNum != 0 || $pageSize != 0) {
		$output['total'] = (string)$totalRecords;
		$output['records'] = $records;
  	} else
  		$output = $records;

    return $output;
  }

  private function executeSql($sql, $params, $ignoreNulls=false, $allowEmptyParams=true) {
  	global $KUINK_TRACE;

	//var_dump($sql);
  	unset($params['_entity']);
  	unset($params['_attributes']);
  	unset($params['_sort']);
  	unset($params['_pageNum']);
  	unset($params['_pageSize']);
  	unset($params['_pk']);
  	unset($params['_sql']);
  	unset($params['_debug_']);
  	unset($params['_multilang_fields']);

  	if ($ignoreNulls)
  		foreach($params as $key=>$value)
  			if ($value=='') unset($params[$key]);
  	//var_dump($params);
    if(count($params)==0 && !$allowEmptyParams){
      return null;
    }

    //Here we have some parameters
    //var_dump($sql);
    $query = $this->db->prepare($sql);
  	$query->execute($params);

  	//Handle the errors
  	$errorInfo = $query->errorInfo();
  	if ($errorInfo[0] != 0) {
  		$KUINK_TRACE[] = 'Database error';
  		$KUINK_TRACE[] = $sql;
  		$KUINK_TRACE[] = $errorInfo[0];
  		$KUINK_TRACE[] = $errorInfo[1];
  		$KUINK_TRACE[] = $errorInfo[2];
  		throw new \Exception('Internal database error');
  	}
  	
  	$records = $query->fetchAll(\PDO::FETCH_ASSOC);
  	//var_dump($records);
      $this->lastAffectedRows = $query->rowCount();
  	return $records;
  }


  private function getPreparedStatementSelectCount($params) {
  	$entity = $this->getParam($params, '_entity',true);
  	$attributes = $this->getParam($params, '_attributes', false, '*');
  	$sort = isset($params['_sort']) ? ' ORDER BY '.$params['_sort'] : '';
  	$pageNum = $this->getParam($params, '_pageNum', false, 0);
  	$pageSize = $this->getParam($params, '_pageSize', false, 0);

  	unset($params['_entity']);
  	unset($params['_attributes']);
  	unset($params['_sort']);
  	unset($params['_pageNum']);
  	unset($params['_pageSize']);
  	unset($params['_pk']);

  	$where = (count($params) > 0) ? ' WHERE ' : '';
  	$count = 0;
  	foreach($params as $key=>$value) {
  		if ($count > 0)
  			$where .= ' AND ';
  		$where .= '`'.$key.'` = '.':'.$key.' ';
  		$count++;
  	}

  	$sql = "SELECT count(*) FROM `$entity` $where";

  	return $sql;
  }

  private function getPreparedStatementSelect($params, $ignoreNulls=false) {
  	$entity = $this->getParam($params, '_entity',true);
  	$attributes = $this->getParam($params, '_attributes', false, '*');
  	$sort = isset($params['_sort']) ? ' ORDER BY '.$params['_sort'] : '';
  	$pageNum = $this->getParam($params, '_pageNum', false, 0);
  	$pageSize = $this->getParam($params, '_pageSize', false, 0);
  	$lang = (string)$this->getParam($params, '_lang', false, '');
  	$pk = $this->getParam($params, '_pk', false, 'id');
  	 
  	unset($params['_entity']);
  	unset($params['_attributes']);
  	unset($params['_sort']);
  	unset($params['_pageNum']);
  	unset($params['_pageSize']);
  	unset($params['_pk']);
  	unset($params['_debug_']);
  	unset($params['_lang']);
  	unset($params['_lang_inline']);
  	 

  	$count = 0;
  	$whereClauses = '';
  	foreach($params as $key=>$value) {
  		if (!$ignoreNulls || ($value!='')) {
	  		if ($count > 0)
	  			$whereClauses .= ' AND ';
	  		$whereClauses .= '`'.$key.'` = '.':'.$key.' ';
	  		$count++;
  		}
  	}
  	$where = ($whereClauses != '') ? ' WHERE '.$whereClauses : '';

  	//Handle pagination
  	$limit = '';
  	if ($pageNum != 0 || $pageSize != 0) {
  		//We have a pagination request
  		$offset = ($pageNum)*$pageSize;
  		$limit = ' LIMIT '.$offset.','.$pageSize;
  	}
  	//var_dump('AAAA');
  	//var_dump($pageNum);var_dump($pageSize);

  	//Handle Multilang
  	$multilang = '';
  	if ($lang != '') {
  		$multilang = ' LEFT OUTER JOIN '.$entity.'_lang l ON (l.id_ref = e.id AND l.lang =\''.$lang.'\')';
  	}  	
  	
  	//concatenate e. to all attributes
  	
  	if ($attributes != '*' && $lang == '') {
  		$attrsArray = explode(',', $attributes);
  		$newAttrs = array();
  		foreach ($attrsArray as $attr)
  			$newAttrs[] = 'e.'.trim($attr);
  		//var_dump($newAttrs);
  		$attributes = implode(',', $newAttrs);
  	}

  	$sql = "SELECT $attributes FROM `$entity` e $multilang $where $sort $limit";

  	return $sql;
  }

  private function getPreparedStatementInsert($params) {
  	$entity = $this->getParam($params, '_entity',true);

  	unset($params['_entity']);
  	unset($params['_attributes']);
  	unset($params['_sort']);
  	unset($params['_pageNum']);
  	unset($params['_pageSize']);
  	unset($params['_pk']);

  	$fields = '';
  	$values = '';
  	$count = 0;
  	foreach($params as $key=>$value) {
  		if ($count > 0) {
  			$fields .= ', ';
  			$values .= ', ';
  		}
  		$fields .= '`'.$key.'`';
  		$values .= ':'.$key;
  		$count++;
  	}

  	$sql = "INSERT INTO $entity ($fields) VALUES ($values)";

  	return $sql;
  }

  private function getPreparedStatementDelete($params) {
  	$entity = $this->getParam($params, '_entity',true);

  	unset($params['_entity']);
  	unset($params['_attributes']);
  	unset($params['_sort']);
  	unset($params['_pageNum']);
  	unset($params['_pageSize']);
  	unset($params['_pk']);

  	$where = '';
  	$count = 0;
  	foreach($params as $key=>$value) {
  		if ($count > 0)
  			$where .= ' AND ';
  		$where .= '`'.$key.'` = '.':'.$key.' ';
  		//$where .= $key.' = ? ';
  		$count++;
  	}

  	$sql = "DELETE FROM `$entity` WHERE $where";

  	return $sql;
  }

  private function getPreparedStatementUpdate($params) {
  	$entity = $this->getParam($params, '_entity', true);
  	$pk = $this->getParam($params, '_pk', false, 'id');

  	//Get the primary keys
  	$pks = explode(',', $pk);

  	unset($params['_entity']);
  	unset($params['_attributes']);
  	unset($params['_sort']);
  	unset($params['_pageNum']);
  	unset($params['_pageSize']);
  	unset($params['_pk']);
  	unset($params['_multilang_fields']);

  	$where = '';
  	$count = 0;
  	foreach($pks as $field) {
  		if ($count > 0)
  			$where .= ' AND ';
  		unset($params[$field]);
  		$where .= '`'.$field.'` = '.':'.$field.' ';
  		$count++;
  	}

  	$set = '';
  	$count = 0;
  	foreach($params as $key=>$value) {
  		if (is_null($value) || $value=='')
  			$value=null;

  		if ($count > 0)
  			$set .= ', ';
  		$set .= '`'.$key.'` = '.':'.$key.' ';
  		$count++;
  	}

  	$sql = "UPDATE `$entity` SET $set WHERE $where";

  	return $sql;
  }

  //Returns sql query from xsql
  //$count - replce select with select count(*) and remove order by
  private function xsql($instruction, $params, $count=false)
  {
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

  		$xinstructions = $xsql[0]->children();
  		//var_dump($xinstructions);
  		foreach ($xinstructions as $xinst)
  		{
  			$xinst_name = $xinst->getname();
  			//print($xinst_name.'<br/>');

  			switch( $xinst_name )
  			{
  				case 'XSelect':
  					$sql .= ($count) ? 'SELECT COUNT(*) ' : $this->xparse($xinst, 'SELECT', 'SELECT *', 'XField', $params);
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
  					$sql .= $this->xparse($xinst, 'HAVING', '', 'XCondition', $params);
  					break;
  				case 'XOrderBy':
  					$sql .= ($count) ? '' : $this->xparse($xinst, 'ORDER BY', '', 'XOrder', $params);                    break;
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
  		$sql = str_replace('{param->'.$key.'}', $param_value , $sql);
  	}
  	$sql = str_replace('{table_prefix}', "{$this->curr_db_prefix}" , $sql);

  	if ($hasGroupBy && $count)
  		$sql = 'SELECT COUNT(*) as total FROM ('.$sql.') _total';

  	//var_dump( $sql );
  	
  	//if ($this->db->inTransaction())
  	//$sql .= ' FOR UPDATE';

  	return $sql;
  }

  //xchild [XField, XCondition,...]
  private function xparse($instruction, $sql_prefix, $default,  $xchild, $params)
  {
  	$xinst_name = $instruction->getname();
  	$sql = $sql_prefix.' ';

  	$xfields = $instruction->xpath('./'.$xchild);
  	//var_dump($xfields);

  	foreach ($xfields as $xfield)
  	{
  		$optional = isset($xfield['optional']) ? (string)$xfield['optional'] : '';
  		$condition = isset($xfield['condition']) ? (string)trim($xfield['condition']) : '';

  		if ($condition != '') {
  			//Parse the conditionExpr
  			$eval = new \Kuink\Core\EvalExpr();
  			try {
  				$result = $eval->e( $condition, $params, TRUE);
  			} catch ( \Exception $e) {
  				var_dump('Exception: eval');
  				die();
  			}
  			if ($result)
  				$sql .= (string)$xfield[0].' ';
  		}
  		else if ($optional != '')
  		{
  			$value = isset($params[$optional]) ? $params[$optional] : '';

  			//Check to see if the $value is a string, if it is, repeat the XOptional
  			if (is_array($value)) {

  				foreach($value as $splitedValue) {
  					$xCond = (string)$xfield[0];
  					$xCond = str_replace('{param->'.$optional.'}', $splitedValue, $xCond);

  					$sql .= $xCond.' ';
  				}
  			}
  			else
  			if (trim($value) != '')
  				$sql .= (string)$xfield[0].' ';
  		}
  		else
  			$sql .= (string)$xfield[0].' ';
  	}

  	if ($sql == $sql_prefix.' ')
  		$sql = $default.' ';

  	//print('<br/>SQL::'.$sql.'<br/>');
  	return $sql;
  }

  public function getEntity($params) {
  	$this->connect();

  	$entName = (string)$params['_entity'];

  	$sql = "
  			SELECT
 					c.ordinal_position as 'id',
 					c.column_name as 'name',
 					c.column_default as 'default',
 					IF(c.is_nullable = 'NO', 'true', 'false') as 'required',
 					c.data_type as 'type',
 					IF(c.character_maximum_length IS NULL, c.numeric_precision, c.character_maximum_length) as length,
 					c.column_key as 'key',
 					k.referenced_table_name as 'datasource',
 					k.referenced_column_name as 'bindid',
 					c.extra
 				FROM
 					INFORMATION_SCHEMA.COLUMNS c
 					LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON (c.column_name = k.column_name AND c.table_name = k.table_name)
  				WHERE
  					c.table_name = '$entName';";
  	$records = $this->executeSql($sql, null);
  	return $records;
  }




  /**
   * Get the changes needed to apply this entity definition to a given database
   *
   * @param string $application - The node application
   * @param string $process - The node process
   * @param string $node - The node containing the entity definition
   */
  public function getEntityChanges($params) {
  	$application = (string) $params['application'];
  	$node = (string) $params['node'];

  	$nodeManager = new NodeManager($application, $process, NodeType::DATA_DEFINITION, $node);

  	$nodeManager->load();

  	$entities = $nodeManager->getEntities($nodeManager);

  	$entityChanges = $this->getEntitiesWithChanges( $nodeManager, $entities );

  	//var_dump($entityChanges);

  	return $entityChanges;
  }


  private function getEntitiesWithChanges( $nodeManager, $entities )  {
  	$types = $this->getTypeConversion();
  	$changes = array();
  	foreach($entities as $entity) {
			//Check to see if this entity is multilang, in this case we need to create two tables
			$multiLang = $this->getAttribute($entity, 'multilang', false, 'multilang', 'false');

			if ($multiLang == 'true') {
				//Add this entity with no multilang attributes
				$entData = $this->getEntityWithChanges( $nodeManager, $entity );
	  		$changes[$entData['name']] = $entData;
				//var_dump($entData);

				//Add the corresponding multilang entity
				$entData = $this->getEntityWithChanges( $nodeManager, $entity, true );
				$idAttr = array('name'=>'id', 'domain'=>'id');
				$idAttrs = $this->getExpandedAttribute($idAttr, $nodeManager);
				$idBaseAttr = array('name'=>'id_ref', 'domain'=>'foreign', 'refentity'=>$entData['name'], 'refattr'=>'id');
				$idBaseAttrs = $this->getExpandedAttribute($idBaseAttr, $nodeManager);
				$idLangAttr = array('name'=>lang, 'domain'=>'lang');
				$idLangAttrs = $this->getExpandedAttribute($idLangAttr, $nodeManager);

				array_unshift($entData['attributes'], $idLangAttrs);
				array_unshift($entData['attributes'], $idBaseAttrs);
				array_unshift($entData['attributes'], $idAttrs);

				$entData['name'] = $entData['name'].'_lang';
	  		$changes[$entData['name']] = $entData;
				//var_dump($entData);
			} else {
				//Just add this entity
				$entData = $this->getEntityWithChanges( $nodeManager, $entity );
	  		$changes[$entData['name']] = $entData;
			}

  		//Build relations
  		$relations = $entity->xpath('./Relations/Relation');
  		//var_dump($relations);
  		foreach ($relations as $relation) {
  			$minOccurs = '1';
  			$maxOccurs = '1';
  			$relName='';
  			$sourceAttr='';
  			$minOccurs = $this->getAttribute($relation, 'minoccurs', true, 'relation');
  			$maxOccurs = $this->getAttribute($relation, 'maxoccurs', true, 'relation');
  			if ($minOccurs == '*' && $maxOccurs == '*') {
  				$relName = $this->getAttribute($relation, 'name', true, 'relation in *:*');
  				$sourceAttr = $this->getAttribute($relation, 'sourceattr', true, 'relation in *:*');
  			}

  			$targetAttr = $this->getAttribute($relation, 'targetattr', true, 'relation');

  			//var_dump($maxOccurs);
  			foreach ($relation as $relatedEntity) {
  				$entDataRelated = $this->getEntityWithChanges( $nodeManager, $relatedEntity );

  				//We need this domain here to create the foreign keys
  				$domain = $nodeManager->getDomain( 'foreign' );
  				$typeConverted = isset($types[$domain['type']]) ? $types[$domain['type']] : '';
  				$domain['convType'] = $typeConverted['type'];
  				$domain['convLength'] = ($domain['size'] != '') ? $domain['size'] : $typeConverted['size'];
  				$domain['physType'] = '';
  				$domain['physLength'] = '';

  				//var_dump($relatedEntity);
  				if (($minOccurs == '1' && $maxOccurs == '*') || ($minOccurs == '1' && $maxOccurs == '1')) {
  					//Add a foreign key in the related table
  					$entDataRelated['attributes'][] = array('name'=>$targetAttr, 'domain'=>'id', 'foreign'=>$entData['name'], 'required'=>'true', 'pk'=>'false', 'autonumber'=>'false', 'domainDef'=>$domain, 'changes'=>DDChanges::ADD);
  					//var_dump($entDataRelated);
  				} else {
  					if (($minOccurs == '*' && $maxOccurs == '*') ) {
  						//Create the relation table
  						//var_dump($entDataRelated);

  						$indexes = array();
  						$indexes[] = array('name'=>'ix_'.$sourceAttr.'_'.$targetAttr,'unique'=>'true', 'attrs'=>$sourceAttr.','.$targetAttr);
  						$attributes = array();
  						$attributes[] = array('name'=>'id', 'domain'=>'id', 'foreign'=>'false', 'required'=>'true', 'pk'=>'true', 'autonumber'=>'true', 'domainDef'=>$domain, 'changes'=>DDChanges::ADD);
  						$attributes[] = array('name'=>$sourceAttr, 'domain'=>'foreign', 'foreign'=>$entData['name'], 'required'=>'true', 'pk'=>'false', 'autonumber'=>'false', 'domainDef'=>$domain, 'changes'=>DDChanges::ADD);
  						$attributes[] = array('name'=>$targetAttr, 'domain'=>'foreign', 'foreign'=>$entDataRelated['name'], 'required'=>'true', 'pk'=>'false', 'autonumber'=>'false', 'domainDef'=>$domain, 'changes'=>DDChanges::ADD);
  						$entityDataRelatedNN = array('name'=>$relName, 'change'=>DDChanges::ADD, 'attributes'=>$attributes, 'indexes'=>$indexes);
  						$changes[$relName] = $entityDataRelatedNN;
  						//var_dump($entityDataRelatedNN);
  					}
  					else {
  						;
  						//Invalid relation type
  					}
  				}
  				//var_dump($entDataRelated);
  				$changes[$entDataRelated['name']] = $entDataRelated;
  			}

  		}
  	}


  	return $changes;
  }

  private function getTypeConversion() {
	$neonSql = array();

	$neonSql['string'] = array('type'=>'varchar','size'=>'');
	$neonSql['text'] = array('type'=>'text','size'=>'');
	$neonSql['longtext'] = array('type'=>'longtext','size'=>'');

	$neonSql['longint'] = array('type'=>'bigint','size'=>'10');
	$neonSql['int'] = array('type'=>'bigint','size'=>'');
	$neonSql['bool'] = array('type'=>'tinyint','size'=>'1');

	$neonSql['date'] = array('type'=>'bigint','size'=>'10');
	$neonSql['time'] = array('type'=>'bigint','size'=>'10');
	$neonSql['datetime'] = array('type'=>'bigint','size'=>'10');

	$neonSql['float'] = array('type'=>'float','size'=>'');
	$neonSql['double'] = array('type'=>'double','size'=>'');
  $neonSql['decimal'] = array('type'=>'decimal','size'=>'');

	return $neonSql;
  }

	/**
		*This is the main function to get Attributes of an entity
		*/
  private function getAttributeWithChanges($entAttr, $nodeManager, $entityName) {
  	$types = $this->getTypeConversion();
  	//var_dump($entAttr);
  	$attr = array();

  	foreach($entAttr->attributes() as $key=>$value)
  		$attr[$key] = (string)$value;

		return $this->getExpandedAttribute($attr, $nodeManager);
  }

	private function getExpandedAttribute($attr, $nodeManager) {
		$types = $this->getTypeConversion();

		//The domain of entity attribute
  	if ( $attr['domain'] != '' ) {
  		$domain = $nodeManager->getDomain( $attr['domain'] );
  		if (!isset($domain))
  			throw new DomainNotFound(__CLASS__, $attr['domain']);
  		//Replace the attributes with the domain values
  		$domainAttributes = $domain->attributes();
  		foreach ($domainAttributes as $key=>$value) {
  			if ($key != 'name' && $key != 'doc') {
  				$attr[$key] = isset($entAttr[$key]) ? (string)$entAttr[$key] : (string)$value;
  			}
  		}
  	}

  	//var_dump($attr);

  	//Validate Required Attributes
  	$this->checkRequiredAttribute($attr, 'name', $entityName);
  	$this->checkRequiredAttribute($attr, 'type', $entityName);

  	$refEntity = $this->getAttribute($attr, 'refentity', false, null, '');
  	if ($refEntity != '') {
  		$refEntityObj = $nodeManager->getEntity($refEntity);
  		//var_dump($refEntity);
  		//var_dump($refEntityObj);
  		if (!isset($refEntityObj)) {
  			var_dump('Entity '.$refEntity.' not found');
  		}

  	}


  	//Compare the domain to see if there are any changes
  	$typeConverted = isset($types[$attr['type']]) ? $types[$attr['type']] : null;
  	//var_dump($typeConverted);
  	if ($typeConverted) {
  		$attr['_physType'] = $typeConverted['type'];
  		$attr['_physSize'] = $typeConverted['size'];
  	} else {
  		throw new PhysicalTypeNotFound(__CLASS__, $entityName, (string)$attr['name'], (string)$attr['type']);
  	}

  	//var_dump($attr);
  	return $attr;
	}

	/**
		*Get the entity attributes
		* If this entity is multilang, the flag @$onlyMultilangAttributes is used to decide:
		*		true - Only get the multilang attributes
		*		false - Only get the non multilang attributes
		*/
  private function getEntityWithChanges( $nodeManager, $entity, $onlyMultilangAttributes = false )  {

  	$data = array();

  	$name = $this->getAttribute($entity, 'name', true, 'entity');
		$multiLang = $this->getAttribute($entity, 'multilang', false, 'multilang', 'false');
  	//var_dump($name.'::'.$multiLang);


  	$change = DDChanges::ADD;
  	$operation = DDChanges::ADD;

  	//CURRENTLY ONLY SUPPORTS ADD
  	/*
  	//Check if the entity exists in physycal model (database)
  	$physical = $this->getEntity(array('_entity'=>$name));

  	$change = DDChanges::NOTHING;
  	if (count($physical) == 0)
  		$change = DDChanges::ADD;

  	$operation = (count($physical) == 0) ? DDChanges::ADD : DDChanges::CHANGE;
  	*/

  	//Process the attributes
  	$entAttrs = $entity->xpath('Attributes/*');
  	$attrs = array();


  	//Check for attributes to ADD
  	foreach ($entAttrs as $entAttr) {
  		if ($change == DDChanges::ADD) {

	  		$attrType = $entAttr->getName();

	  		if ($attrType == 'Template') {
	  			//Get all attributes from the template
	  			$tplName = $this->getAttribute($entAttr, 'name', true, 'template');
	  			$template = $nodeManager->getTemplate($tplName);
	  			if (!isset($template))
	  				throw new \Kuink\Core\Exception\TemplateNotFound(__CLASS__, $tplName);

	  			//var_dump($template);
	  			foreach ($template->children() as $tplAttr) {
	  				$attr = $this->getAttributeWithChanges($tplAttr, $nodeManager, $name);
						$attrMultiLang = $this->getAttribute($entAttr, 'multilang', false, 'multilang', 'false');

						if (($multiLang == 'true')) {
							if ($onlyMultilangAttributes && ($attrMultiLang == 'true'))
								$attrs[] = $attr;
							else if (!$onlyMultilangAttributes && ($attrMultiLang == 'false'))
								$attrs[] = $attr;
						} else {
			  			//var_dump($attr);
			  			$attrs[] = $attr;
						}
	  			}
	  		} else {
	  			$attr = $this->getAttributeWithChanges($entAttr, $nodeManager, $name);
					$attrMultiLang = $this->getAttribute($entAttr, 'multilang', false, 'multilang', 'false');
					//var_dump($attr);
					if (($multiLang == 'true')) {
						if ($onlyMultilangAttributes && ($attrMultiLang == 'true'))
							$attrs[] = $attr;
						else if (!$onlyMultilangAttributes && ($attrMultiLang == 'false'))
							$attrs[] = $attr;
					} else {
		  			//var_dump($attr);
		  			$attrs[] = $attr;
					}
	  		}
  		}
  	}

  	$data['name'] = $name;
  	$data['change'] = $change;
  	$data['attributes'] = $attrs;

  	return $data;
  }


  public function applyEntityChanges($params) {
  	global $KUINK_TRACE;
	$entityChanges = $this->getEntityChanges($params);

	//CreateForeignKeyIndexes?
	$createForeignKeyIndexes = (isset($params['createForeignIndexes'])) ? (string)$params['createForeignIndexes'] : 'false';
	$createForeignKeys= (isset($params['createForeignKeys'])) ? (string)$params['createForeignKeys'] : 'false';
	$dropTablesBeforeCreate = (isset($params['dropTablesBeforeCreate'])) ? (string)$params['dropTablesBeforeCreate'] : 'false';

	//var_dump($entityChanges);

	//build the SQL Statement
	$log = array();
	$sqlStatementsArray = array();
	$sqlForeignKeysArray = array();

	foreach($entityChanges as $entity) {
		$sqlStatement = '';
		if ($entity['change'] == DDChanges::ADD) {
			//Get the ADD table entity sql
			if ($dropTablesBeforeCreate == 'true') {
				$sqlStatement .= 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS `'.$entity['name'].'`; ';
			}
			$sqlStatement .= 'CREATE TABLE IF NOT EXISTS `'.$entity['name'].'` (';

			$sqlAttributesArray = array();
			$sqlPrimaryKeysArray = array();
			$sqlUniquesArray = array();

			foreach($entity['attributes'] as $attribute) {
				$sqlAttribute = '';
				$sqlAttribute .= '`'.$attribute['name'].'`';
				$sqlAttribute .= ' '.$attribute['_physType'].' ';
				if (($attribute['_physSize'] != ''))
					$sqlAttribute .= ' ('.$attribute['_physSize'].')';
				else if (($attribute['size'] != ''))
					$sqlAttribute .= ' ('.$attribute['size'].')';

				$sqlAttribute .= ($attribute['required'] == 'true') ? ' NOT NULL' : '';
				$sqlAttribute .= ($attribute['autonumber'] == 'true') ? ' AUTO_INCREMENT' : '';
        $sqlAttribute .= ($attribute['default'] != '') ? ' DEFAULT \''.(string)$attribute['default'].'\'' : '';        
				$pk = $this->getAttribute($attribute, 'pk', false, null, 'false');
				if ($pk== 'true') {
					$sqlPrimaryKeysArray[] = $attribute['name'];
				}
				$refEntity = $this->getAttribute($attribute, 'refentity', false, null, '');
				if ($refEntity != '') {
					$refAttr = $this->getAttribute($attribute, 'refattr', true, $refEntity, '');

					//Check to see if this entity in in another node
					if (strpos($refEntity, ',')) {
						//This is an entity that is in another data definition node
						$splitedName = explode(',', $refEntity);
						if (count($splitedName) != 3)
							throw new InvalidName(__CLASS__, $name);
						$application = $splitedName[0];
						$node = $splitedName[1];
						$refEntityName = $splitedName[2];

						$sqlForeignKeysArray[] = array('entity'=>$entity['name'], 'attribute'=>$attribute['name'], 'refentity'=>$refEntityName, 'refattr'=>$refAttr);

					} else {
						//In this node
						$sqlForeignKeysArray[] = array('entity'=>$entity['name'], 'attribute'=>$attribute['name'], 'refentity'=>$refEntity, 'refattr'=>$refAttr);
					}
				}
				$unique = $this->getAttribute($attribute, 'unique', false, null, 'false');
				if ($unique== 'true') {
					$sqlUniquesArray[] = $attribute['name'];
				}

				$sqlAttributesArray[] = $sqlAttribute;
			}

			$sqlAttributes = implode($sqlAttributesArray, ',');
			if (count($sqlPrimaryKeysArray) > 0) {
				$sqlPrimarykeys = implode($sqlPrimaryKeysArray, ',');
				$sqlPrimarykeys = ' ,PRIMARY KEY ('.$sqlPrimarykeys.')';
			} else {
				throw new \Kuink\Core\Exception\PrimaryKeyNotFound(__CLASS__, $entity['name']);
				$sqlPrimarykeys = '';
			}

			$sqlStatement .= $sqlAttributes.' '.$sqlPrimarykeys.' )';

			$sqlStatementsArray[$entity['name']] = $sqlStatement;

			//Add unique indexes
			foreach ($sqlUniquesArray as $uk) {
				$sqlStatement = 'CREATE UNIQUE INDEX `ix_'.$uk.'` ON `'.$entity['name'].'` ( `'.$uk.'`);';
				$sqlStatementsArray[$entity['name'].':ix_'.$uk] = $sqlStatement;
			}


			//var_dump($entity);
			//var_dump($sqlStatement);
			$KUINK_TRACE[] = $sqlStatement;
		}
	}

	//Add the foreign keys indexes after the table creations to avoid invalid references
	if ($createForeignKeyIndexes == 'true') {
		foreach ($sqlForeignKeysArray as $fk) {
			$sqlStatement = 'CREATE INDEX `ix_'.$fk['attribute'].'` ON `'.$fk['entity'].'` ( `'.$fk['attribute'].'`);';
			$sqlStatementsArray[$fk['entity'].':ix_'.$fk['attribute']] = $sqlStatement;
		}
	}
	if ($createForeignKeys == 'true') {
		foreach ($sqlForeignKeysArray as $fk) {
			//ALTER TABLE Orders ADD CONSTRAINT fk_PerOrders FOREIGN KEY (P_Id) REFERENCES Persons(P_Id)

			$sqlStatement = 'ALTER TABLE  `'.$fk['entity'].'` ADD CONSTRAINT `fk_'.$fk['entity'].'_'.$fk['attribute'].'` FOREIGN KEY(`'.$fk['attribute'].'`) REFERENCES `'.$fk['refentity'].'`( `'.$fk['refattr'].'`);';
			$sqlStatementsArray[$fk['entity'].':fk_'.$fk['attribute']] = $sqlStatement;
		}
	}



	//If it's all OK then Execute the SQL Statements
	foreach($sqlStatementsArray as $key=>$sqlStatement) {
		try {
			$this->executeSql($sqlStatement);
			$log[] = array('entity'=>$key, 'status'=>'OK', 'sqlStatement'=>$sqlStatement);
		}
		catch (\Exception $e) {
			$log[] = array('entity'=>$key, 'status'=>'ERROR', 'sqlStatement'=>$sqlStatement);
		}
	}

	//var_dump($log);
	return $log;
  }

  private function getAttribute($arr, $key, $required, $context, $default='') {
  	if (!isset($arr[$key]) && $required) {
  		$a = var_export($arr, true);
  		throw new ParameterNotFound(__CLASS__, $a.' | '.$context, $key);
  	}
  	$value = isset($arr[$key]) ? (string) $arr[$key] : $default;
  	return $value;
  }

  private function checkRequiredAttribute($arr, $key, $entityName) {
  	if (!isset($arr[$key]))
  		throw new ParameterNotFound(__CLASS__, $entityName, $key);
  	return;
  }

  function beginTransaction() {
  	if (!$this->db->inTransaction()) {
  		$this->db->beginTransaction();
  		parent::beginTransaction();
  	}
  	return;
  }
  
  function commitTransaction() {
  	if ($this->db->inTransaction()) {
  		$this->db->commit();
  		parent::commitTransaction();
  	}
  	
  	return;
  }

  function rollbackTransaction() {
  	if ($this->db->inTransaction()) {
  		$this->db->rollBack();
  		parent::rollbackTransaction();
  	}
  	
  	return;
  }
  
}

?>
