<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of CmisRepository
 *
 * @author paulo.tavares
 */
class CmisRepositoryConnector extends \Kuink\Core\DataSourceConnector {
	var $ecmClient;
	var $repository;
	var $repositoryId;
	var $repositoryInfo;
	function connect() {
		if (! $this->ecmClient) {
			// Connect to the server
			$soapClient = 'CMISAlfrescoSoapClient'; // class name for CMIS Soap Client
			$cmisServices = array ( // service endpoints for CMIS services, appended to $url
					'RepositoryService' => 'RepositoryService?wsdl',
					'DiscoveryService' => 'DiscoveryService?wsdl',
					'MultiFilingService' => 'MultiFilingService?wsdl',
					'NavigationService' => 'NavigationService?wsdl',
					'ObjectService' => 'ObjectService?wsdl',
					'PolicyService' => 'PolicyService?wsdl',
					'RelationshipService' => 'RelationshipService?wsdl',
					'VersioningService' => 'VersioningService?wsdl',
					'ACLService' => 'ACLService?wsdl' 
			);
			$maxItems = 15; // max number of documents on a page
			$useQuery = true; // get document list with query, or with getChildren
			
			$url = $this->dataSource->getParam ( 'server', true );
			$user = $this->dataSource->getParam ( 'user', true );
			$passwd = $this->dataSource->getParam ( 'passwd', true );
			
			// Open a SOAP session to the CMIS repository
			$cmisSessionOptions = array (
					'CMISSoapClient' => $soapClient, // class name for custom SoapClient
					'CMISServices' => $cmisServices, // service endpoints
					'user_name' => $user, // $_SESSION['cmisSession']['username'],
					'password' => $passwd 
			) // $_SESSION['cmisSession']['password']
;
			try {
				$this->ecmClient = new \CMISWebService ( $url, $cmisSessionOptions, 'errorMessage' );
				$repositories = $this->ecmClient->getRepositories ();
				$this->repository = $repositories [0];
				$this->repositoryId = $repositories [0]->repositoryId;
				;
				$this->repositoryInfo = $this->ecmClient->getRepositoryInfo ( $this->repositoryId );
			} catch ( Exception $e ) {
				throw new \Exception ( $e );
			}
		}
	}
	function insert($params) {
		kuink_mydebug ( __CLASS__, __METHOD__ );
		
		$this->connect ();
		
		$repositoryName = $this->repository->repositoryName; // Using first repository only
		$rootFolderId = $this->repositoryInfo->rootFolderId;
		
		// Get params
		// _entity (folder)
		$_entity = $this->getParam ( $params, '_entity', true );
		
		// var_dump( $rootFolderId );
	}
	function update($params) {
		kuink_mydebug ( __CLASS__, __METHOD__ );
		
		$this->connect ();
	}
	function delete($params) {
		kuink_mydebug ( __CLASS__, __METHOD__ );
		
		$this->connect ();
	}
	function load($params) {
		$this->connect ();
		
		$id = $this->getParam ( $params, 'id', true );
		
		$result = $this->ecmClient->getObject ( $this->repositoryId, $id );
		
		$properties = array ();
		foreach ( $result->properties as $key => $value )
			$properties [$key] = $value->value [0];
		
		return $properties;
	}
	public function getSchemaName($params) {
		return null;
	}
}

?>
