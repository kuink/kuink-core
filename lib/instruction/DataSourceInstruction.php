<?php

namespace Kuink\Core\Instruction;

/**
 * DatSource instruction
 *
 * @author paulo.tavares
 */
class DataSourceInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Set or get ds params
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		$parse = ( string ) self::getAttribute ( $instructionXmlNode, 'parse', $instManager->variables, false, 'false' );
		
		$dsName = ( string ) self::getAttribute ( $instructionXmlNode, 'name', $instManager->variables, true );
		$dsConnector = ( string ) self::getAttribute ( $instructionXmlNode, 'connector', $instManager->variables, false, '' );
		
		if ($dsConnector == '') {
			// This is a get of properties instead of a creation of a datasource
			if (\Kuink\Core\DataSourceManager::dataSourceExists ( $dsName )) {
				
				$ds = \Kuink\Core\DataSourceManager::getDataSource ( $dsName );
				return $ds->params;
			} else
				throw new \Exception ( 'Getting DataSource properties: DataSource not found ' . $dsName );
		}
		
		$dsLoad = ( string ) self::getAttribute ( $instructionXmlNode, 'load', $instManager->variables, false, '' );
		
		if (($dsConnector == '') && ($dsName == ''))
			throw new \Exception ( 'Datasource must specify either connector or name attributes.' );
		
		if ($dsLoad != '') {
			// Load the datasource from the database fw_datasource with code=the value of the load attribute
			$idCompany = $instManager->variables ['USER'] ['idCompany'];
			\Kuink\Core\DataSourceManager::addDataSourceFromDB ( $dsLoad, $idCompany, \Kuink\Core\DataSourceContext::NODE );
		} else {
			$dsParams = $instManager->getParams ( $instructionXmlNode );
			$dsParamsTransf = array ();
			foreach ( $dsParams as $dsKey => $dsParam )
				$dsParamsTransf [$dsKey] = ( string ) $dsParam;
				// var_dump($dsParamsTransf);
				// var_dump($instManager->variables);
			\Kuink\Core\DataSourceManager::addDataSource ( $dsName, $dsConnector, \Kuink\Core\DataSourceContext::NODE, $dsParamsTransf );
		}
		
		$newDS = \Kuink\Core\DataSourceManager::getDataSource ( $dsName );
		return $newDS->params;
	}
}

?>
