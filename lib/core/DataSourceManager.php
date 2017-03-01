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
	
	static public function setupDataSources( $application ) 
	{
		//global $KUINK_DATASOURCES;
		
		self::setupFrameworkDS($application);
		self::setupApplicationDS($application);
	}
	
	static public function setupCompanyDataSources( $idCompany )
	{
		//global $KUINK_DATASOURCES;
		
		$dataAccess = new \Kuink\Core\DataAccess('getAll', 'framework', 'datasource', '');
		$params['_entity'] = 'fw_datasource';
		$params['id_company'] = $idCompany;
			
		$resultset = $dataAccess->execute($params);
		foreach ($resultset as $datasource)
			self::addDataSourceXmlDefinition($datasource['xml_definition'], DataSourceContext::DB);
		
		//var_dump($KUINK_DATASOURCES);
		
		//Setup company specific datasources
	}
	
	
	static public function setupFrameworkDS( $application )
	{
		$fw = $application->getFrameworkXml();

		$dataSources = $fw->xpath('/Framework/DataSources/DataSource');

        //setup dataSources
		foreach ($dataSources as $dataSource)
			self::addDataSourceXml($dataSource, DataSourceContext::FRAMEWORK);
			
		return;
	}

	static public function setupApplicationDS( $application )
	{
		$app = $application->getApplicationXml();


		$dataSources = $app->xpath('/Application/DataSources/DataSource');

        //setup dataSources
		foreach ($dataSources as $dataSource)
			self::addDataSourceXml($dataSource, DataSourceContext::APPLICATION);
			
		return;
	}
	
	/***
	 * Setup the DataSources of a given application name
	 */
	static public function setupApplicationDSByName( $applicationName )
	{
		//TODO
		return;
	}
	
	static public function addDataSourceFromDB( $code, $idCompany, $context )
	{
		//Load the datasource from the database fw_datasource with code=the value of the load attribute
		$dataAccess = new \Kuink\Core\DataAccess('load', 'framework', 'datasource', '');
		$params['_entity'] = 'fw_datasource';
		$params['code'] = $code;
		$params['id_company'] = $idCompany;
			
		$resultset = $dataAccess->execute($params);
			
		if (!$resultset)
			throw new \Exception('Cannot load DataSource '.$code.' for company '.$idCompany );
			
		DataSourceManager::addDataSourceXmlDefinition($resultset['xml_definition'], $context);
		
		return;
	}
	
	static public function addDataSourceXml( $dataSource, $context )
	{
		$dsName = (string)$dataSource['name'];
		
        $dsConnector = (string)$dataSource['connector'];
        $dsParams = array();
        $dsParamsXml = $dataSource->xpath('./Param');
        foreach( $dsParamsXml as $dsParamXml ) {
          $dsParams[ (string)$dsParamXml['name'] ] = (string)$dsParamXml[0];
        }
        $ds = new DataSourceClass( $dsName, $dsConnector, $dsParams );
        self::addDataSource($dsName, $dsConnector, $context, $dsParams);
  
        return;
	}

	/**
	 * Adds a datasource given the xml deinition in a string
	 * @param unknown $datasourceXmlString
	 */
	static private function addDataSourceXmlDefinition($datasourceXmlString, $context)
	{
		global $KUINK_DATASOURCES;

		libxml_use_internal_errors( true );
		$eval_instructions_xml = simplexml_load_string($datasourceXmlString);
		$errors = libxml_get_errors();
		
		if ($eval_instructions_xml == null) {
			$errorMsg = '';
			foreach( $errors as $error)
				$errorMsg .= $error->message;
		
			throw new \Exception('Error loading eval instructions: '.$errorMsg);
		}
		
		
		self::addDataSourceXml($eval_instructions_xml, $context);
	
		return;
	}
	
	
	static public function addDataSource($dsName, $dsConnector, $context, $dsParams)
	{
		global $KUINK_DATASOURCES;
	
		$ds = new DataSourceClass( $dsName, $dsConnector, $context ,$dsParams );
		$KUINK_DATASOURCES[ $dsName ] = $ds;
	
		return;
	}	
	
	static public function dataSourceExists($dsName)
	{
		global $KUINK_DATASOURCES;
		
		return (isset($KUINK_DATASOURCES[$dsName]));
	}	

	static public function getDataSource($dsName)
	{
		global $KUINK_DATASOURCES;
		
		$ds = $KUINK_DATASOURCES[ $dsName ];
	
		return $ds;
	}	
	
	static public function beginTransaction() {
		global $KUINK_DATASOURCES;
		
		foreach ($KUINK_DATASOURCES as $ds)
			$ds->beginTransaction();
	}
	
	static public function commitTransaction() {
		global $KUINK_DATASOURCES;
		
		foreach ($KUINK_DATASOURCES as $ds)
			$ds->commitTransaction();
	}
	
	static public function rollbackTransaction() {
		global $KUINK_DATASOURCES;
		
		foreach ($KUINK_DATASOURCES as $ds)
			$ds->rollbackTransaction();
	}
  
}

?>
