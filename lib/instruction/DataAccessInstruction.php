<?php

namespace Kuink\Core\Instruction;

/**
 * DataAccess Instruction
 *
 * @author paulo.tavares
 */
class DataAccessInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a DataAccess
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		global $KUINK_TRACE;
		$dataAccessNid = ( string ) self::getAttribute ( $instructionXmlNode, 'method', $instManager->variables, true ); //$this->get_inst_attr ( $instruction_xmlnode, 'method', $instManager->variables, true );
		$dataSourceName = ( string ) self::getAttribute ( $instructionXmlNode, 'datasource', $instManager->variables, false ); //$this->get_inst_attr ( $instruction_xmlnode, 'datasource', $instManager->variables, false );
		$KUINK_TRACE [] = 'DataAccess Execute: ' . $dataAccessNid;
		
		$paramsvar = ( string ) self::getAttribute ( $instructionXmlNode, 'params', $instManager->variables, false );
		$paramsOperators = array(); //If the param specifies an operator, then collect them here
		
		$appName = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::APPLICATION];
		$processName = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::PROCESS];
		
		$paramsxml = $instructionXmlNode->xpath ( './Param' );
		
		$params = null;
		$pks = null;
		foreach ( $paramsxml as $param ) {
			$paramName = ( string ) self::getAttribute( $param, 'name',$instManager->variables, true );
			$paramWildcard = ( string )  self::getAttribute( $param, 'wildcard',$instManager->variables, false );
			$paramOperator = ( string )  self::getAttribute( $param, 'op',$instManager->variables, false );
			if ($paramOperator != '') 
				$paramsOperators[$paramName] = $paramOperator;

			//$pk = isset ( $param ['pk'] ) ? ( string ) $param ['pk'] : '';
			$pk = (string) self::getAttribute( $param, 'pk',$instManager->variables, false );
			$paramValue = ( string ) self::getAttribute( $param, 'value',$instManager->variables, false, '' );
			if ($paramValue != '') {
				$value = $paramValue;
			} else {

				if ($param->count () > 0) {
					$value = $instManager->executeInnerInstruction ( $param );
				} else {
					$value = $param [0];
					if (is_a ( $value, '\SimpleXMLElement' ))
						$value = ( string ) $value;
				}
			}
			
			if (! empty ( $paramWildcard ) && ! empty ( $value )) {
				switch ($paramWildcard) {
					case 'full' :
						$value = '%' . str_replace ( ' ', '%', $value ) . '%';
						break;
					case 'left' :
						$value = '%' . $value;
						break;
					case 'right' :
						$value = $value . '%';
						break;
					case 'fullSplit' :
						$in = false;
						$splitedValue = '';
						$splited = array ();
						$len = strlen ( $value );
						for($i = 0; $i < $len; $i ++) {
							if ($value [$i] == '"')
								$in = ! ($in);
							
							if ($value [$i] == ' ' && ! $in) {
								$splited [] = '%' . $splitedValue . '%';
								$splitedValue = '';
							} else if ($value [$i] != '"')
								$splitedValue .= $value [$i];
						}
						if ($splitedValue != '')
							$splited [] = '%' . $splitedValue . '%';
						
						$value = $splited;
						break;
					default :
						throw new \Exception ( 'Invalid wildcard value:' . $paramWildcard );
				}
			}
			
			if ($pk != '')
				$pks [] = $paramName;;
			
				if (is_array($value) || is_object($value) || ($value==NULL) )
				$params [$paramName] = $value;
			else
				$params [$paramName] = ( string ) $value;
		}
		
		// Adding params if variable is defined
		if ($paramsvar != "") {
			$var = $instManager->variables [$paramsvar];
			if (isset($var) && $var !== '')
				foreach ( $var as $key => $value )
					$params ["$key"] = $value;
		}
		
		// Adding the _pk parameter
		// var_dump($pks);
		$params ['_pk'] = isset($pks) ? implode ( ',', $pks ) : null;
		
		// var_dump($dataAccessNid);
		// var_dump($instManager->nodeConfiguration);
		
		// Handle base template
		$_base = isset ( $params ['_base'] ) ? $params ['_base'] : null;
		if ($_base == 'true') {
			$dateTime = new \DateTimeLib ( $instManager->nodeConfiguration, null );
			unset ( $params ['_base'] );
			$params ['id_company'] = $instManager->variables ['USER'] ['idCompany'];
			
			if ($dataAccessNid == 'insert' || $dataAccessNid == 'execute') {
				$params ['_id_creator'] = $instManager->variables ['USER'] ['id'];
				$params ['_creation'] = $dateTime->Now ();
				$params ['_creation_ip'] = $instManager->variables ['USER'] ['ip'];
			}
			
			if ($dataAccessNid == 'insert' || $dataAccessNid == 'update' || $dataAccessNid == 'execute') {
				$params ['_id_updater'] = $instManager->variables ['USER'] ['id'];
				$params ['_modification'] = $dateTime->Now ();
				$params ['_modification_ip'] = $instManager->variables ['USER'] ['ip'];
			}
		}
		$dataAccess = new \Kuink\Core\DataAccess ( $dataAccessNid, $appName, $processName, $dataSourceName );
		$dataAccess->setUser($instManager->variables['USER']);
		$resultset = $dataAccess->execute ( $params, $paramsOperators );
		
		return $resultset;	
	}
}

?>
