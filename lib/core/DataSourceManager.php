<?php

namespace Kuink\Core;

/**
 * Handles Datasources
 *
 * @author paulo.tavares
 */
class DataSourceContext {
	const FRAMEWORK = 'framework.xml';
	const APPLICATION = 'application.xml';
	const DB = 'database - company';
	const NODE = 'node';
}
class DataSourceManager {
	static public function setupDataSources($application) {
		// global $KUINK_DATASOURCES;
		self::setupFrameworkDS ( $application );
		self::setupApplicationDS ( $application );
	}
	static public function setupCompanyDataSources($idCompany) {
		// global $KUINK_DATASOURCES;
		$dataAccess = new \Kuink\Core\DataAccess ( 'getAll', 'framework', 'datasource', '' );
		$params ['_entity'] = 'fw_datasource';
		$params ['id_company'] = $idCompany;

		$resultset = $dataAccess->execute ( $params );
	
		foreach ( $resultset as $datasource ){
			self::addDataSourceXmlDefinition ( $datasource ['xml_definition'], DataSourceContext::DB );
		}
	
		//var_dump($KUINK_DATASOURCES);
		
		// Setup company specific datasources
	}
	static public function setupFrameworkDS($application) {		
		$fw = $application->getFrameworkXml ();

		$dataSources = $fw->xpath ( '/Framework/DataSources/DataSource' );
		
		// setup dataSources
		foreach ( $dataSources as $dataSource )
			self::addDataSourceXml ( $dataSource, DataSourceContext::FRAMEWORK );
			
		return;
	}
	static public function setupApplicationDS($application) {
		$app = $application->getApplicationXml ();
		
		if (! $app)
			throw new \Exception ( 'Cannot get application xml definition app:' );
		
		$dataSources = $app->xpath ( '/Application/DataSources/DataSource' );
		
		// setup dataSources
		foreach ( $dataSources as $dataSource )
			self::addDataSourceXml ( $dataSource, DataSourceContext::APPLICATION );
		
		return;
	}
	
	/**
	 * *
	 * Setup the DataSources of a given application name
	 */
	static public function setupApplicationDSByName($applicationName) {
		// TODO
		return;
	}
	static public function addDataSourceFromDB($code, $idCompany, $context) {
		// Load the datasource from the database fw_datasource with code=the value of the load attribute
		$dataAccess = new \Kuink\Core\DataAccess ( 'load', 'framework', 'datasource', '' );
		$params ['_entity'] = 'fw_datasource';
		$params ['code'] = $code;
		$params ['id_company'] = $idCompany;
		
		$resultset = $dataAccess->execute ( $params );
		
		if (! $resultset)
			throw new \Exception ( 'Cannot load DataSource ' . $code . ' for company ' . $idCompany );
		
		DataSourceManager::addDataSourceXmlDefinition ( $resultset ['xml_definition'], $context );
		
		return;
	}
	
	static private function buildParams($dsParamsXml)
	{
		$dsParams = array();
		foreach( $dsParamsXml as $dsParamXml ) {
			$paramName = (string)$dsParamXml['name'];
			$paramValue = trim((string)$dsParamXml[0]);
			
			if ($paramValue != '')
				$dsParams[$paramName] = $paramValue;
			else {
				//This is a parameter matrioska so build it
				$dsChildParamsXml = $dsParamXml->xpath('./Param');
				$buildedParams = self::buildParams($dsChildParamsXml);
				if (count($buildedParams) == 0)
					$dsParams[$paramName] = ''; //The matrioska is empty
				else 
					$dsParams[$paramName] = $buildedParams;
			}
		}
		return $dsParams; 
	}
	
	static public function addDataSourceXml($dataSource, $context) {
		global $KUINK_CFG;
		$dsName = ( string ) $dataSource ['name'];
		
		$dsConnector = ( string ) $dataSource ['connector'];
		$dsBypass = ( string ) $dataSource ['bypass'];
		if ($dsBypass == '' || $dsBypass == 'false')
			$dsBypass = 0;
		else {
			// evaluate the bypass expression
			$server_info ['name'] = $_SERVER ['SERVER_NAME'];
			$server_info ['ip'] = $_SERVER ['SERVER_ADDR'];
			$server_info ['port'] = $_SERVER ['SERVER_PORT'];
			$server_info ['wwwRoot'] = $KUINK_CFG->wwwRoot;
			$server_info ['appRoot'] = $KUINK_CFG->appRoot;
			$server_info ['apiUrl'] = $KUINK_CFG->apiUrl;
			$server_info ['streamUrl'] = $KUINK_CFG->streamUrl;
			$server_info ['guestUrl'] = $KUINK_CFG->guestUrl;
			$server_info ['environment'] = $KUINK_CFG->environment;
			$variables ['SYSTEM'] = $server_info;
			
			$eval = new \Kuink\Core\EvalExpr ();
			try {
				$value = $eval->e ( $dsBypass, $variables, TRUE );
			} catch ( \Exception $e ) {
				var_dump ( 'Exception: eval ' . $dsBypass );
				die ();
			}
			if ($value || $dsBypass == 'true')
				$dsBypass = 1;
			else
				$dsBypass = 0;
		}
		
		$dsParamsXml = $dataSource->xpath('./Param');
        
		$dsParams = self::buildParams($dsParamsXml);
        
		self::addDataSource ( $dsName, $dsConnector, $context, $dsParams, $dsBypass );
		
		return;
	}
	
	/**
	 * Adds a datasource given the xml deinition in a string
	 * 
	 * @param unknown $datasourceXmlString        	
	 */
	static private function addDataSourceXmlDefinition($datasourceXmlString, $context) {
		global $KUINK_DATASOURCES;
		
		libxml_use_internal_errors ( true );
		$eval_instructions_xml = simplexml_load_string ( $datasourceXmlString );
		$errors = libxml_get_errors ();
		
		if ($eval_instructions_xml == null) {
			$errorMsg = '';
			foreach ( $errors as $error )
				$errorMsg .= $error->message;
			
			throw new \Exception ( 'Error loading eval instructions: ' . $errorMsg );
		}
		
		self::addDataSourceXml ( $eval_instructions_xml, $context );
		
		return;
	}
	static public function addDataSource($dsName, $dsConnector, $context, $dsParams) {
		global $KUINK_DATASOURCES;
		
		$ds = new DataSourceClass ( $dsName, $dsConnector, $context, $dsParams );
		$KUINK_DATASOURCES [$dsName] = $ds;
		
		return;
	}
	static public function dataSourceExists($dsName) {
		global $KUINK_DATASOURCES;
		
		return (isset ( $KUINK_DATASOURCES [$dsName] ));
	}
	static public function getDataSource($dsName) {
		global $KUINK_DATASOURCES;
		
		$ds = $KUINK_DATASOURCES [$dsName];
		
		return $ds;
	}
	static public function beginTransaction() {
		global $KUINK_DATASOURCES;
		
		foreach ( $KUINK_DATASOURCES as $ds )
			$ds->beginTransaction ();
	}
	static public function commitTransaction() {
		global $KUINK_DATASOURCES;
		
		foreach ( $KUINK_DATASOURCES as $ds )
			$ds->commitTransaction ();
	}
	static public function rollbackTransaction() {
		global $KUINK_DATASOURCES;
		foreach ( $KUINK_DATASOURCES as $ds ) {
			$ds->rollbackTransaction ();
		}
	}
}

?>
