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
namespace Kuink\UI\Control;

use Kuink\Core;

// Requires neonDatasource to load a datasource

/**
 * This class is the base class for each kuink control.
 * It provides the basic methods and properties that all controls must implement in order to function properly
 * 
 * @author ptavares
 *        
 */
abstract class Control {
	var $nodeconfiguration;
	var $xml_definition;
	var $properties;
	var $datasources;
	var $bind_data;
	var $name; // This control name
	var $type; // This control type: Form, Grid, Chart, etc...
	var $skeleton; // This control skeleton
	var $skin; // This control skin
	var $position; // position of the control in the template
	var $guid; // Control uniqueid
	var $refreshing; // Is this control being refreshed?
	
	/**
	 * Base Contructor
	 * 
	 * @param $nodeconfiguration -
	 *        	node configuration where the control is
	 * @param $msg_manager -
	 *        	this allow this control to set messages to the user
	 * @param $xml_definition -
	 *        	contains a pointer to an xml object containing the control
	 */
	function __construct($nodeconfiguration, $xml_definition) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->xml_definition = $xml_definition;
		
		$this->name = ( string ) $xml_definition ['name'];
		$this->type = ( string ) $xml_definition->getName ();
		// kuink_mydebug('NAME::', $this->name);
		$this->skeleton = ( string ) $this->getProperty ( $this->name, 'skeleton', false, '' );
		$this->skin = ( string ) $this->getProperty ( $this->name, 'skin', false, '' );
		$this->position = ( string ) $this->getProperty ( $this->name, 'position', false, '' );
		$this->guid = uniqid ();
		$this->refreshing = false;
	}
	function setRefreshing() {
		$this->refreshing = true;
	}
	
	/**
	 * Sets dinamically a property in the control
	 * 
	 * @param $params [0]
	 *        	- (string) key to set the property
	 *        	[1] - (string) the name of the property
	 *        	[2] - the value of the property
	 */
	function setProperty($params) {
		if (count ( $params ) == 3) {
			$key = ( string ) $this->getParam ( $params, 0, true );
			$property = ( string ) $this->getParam ( $params, 1, true );
			$value = $this->getParam ( $params, 2, true );
		} else if (count ( $params ) == 2) {
			$key = $this->name;
			$property = ( string ) $this->getParam ( $params, 0, true );
			$value = $this->getParam ( $params, 1, true );
		} else
			throw new \Exception ( $this->type . '->' . $this->name . '. setProperty: invalid number of parameters.' );
		
		$this->properties [$key] [$property] = $value;
	}
	
	/**
	 * Adds a new datasource to the control
	 * 
	 * @param $params [0]
	 *        	- (string) datasource name
	 *        	[1] - (array) datasource
	 */
	function addDataSource($params) {
		$ds_name = ( string ) $this->getParam ( $params, 0, true );
		$ds = $this->getParam ( $params, 1, true );
		
		// Convert all stdClass to Array
		$datasource = array ();
		foreach ( $ds as $key => $value ) {
			$new_value = ( array ) $value;
			$datasource [$key] = $new_value;
		}
		$ds = $datasource;
		
		$this->datasources [$ds_name] = $ds;
	}
	
	/**
	 * Sets a new definition of the control.
	 * This way a new definition of the control can be set dinamically in one node.
	 * 
	 * @param $params [0]
	 *        	- (string) the new definition
	 */
	function setDefinition($params) {
		$new_str_xml_definition = ( string ) $this->getParam ( $params, 0, true );
		
		libxml_use_internal_errors ( true );
		$new_def_xml = simplexml_load_string ( $new_str_xml_definition );
		$errors = libxml_get_errors ();
		
		$this->xml_definition = $new_def_xml;
	}
	
	/**
	 * Binds data to the control
	 * 
	 * @param $params [0]
	 *        	- (array) data to bind
	 */
	function bind($params) {
		// if ($this->name=='scoresList')
		// var_dump($params);
		$data = $this->getParam ( $params, 0, false );
		
		// var_dump($data);
		if (isset ( $data ))
			$this->bind_data [] = $data;
		// var_dump($this->bind_data);
	}
	
	/**
	 * Render the control in the LAYOUT singleton object
	 * 
	 * @param array $params        	
	 */
	function render($params) {
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		// Add the guid to the render
		$params ['_guid'] = $this->guid;
		$params ['_name'] = $this->name;
		$params ['_type'] = $this->type;
		$params ['_position'] = $this->position;
		$params ['_skin'] = $this->skin;
		$params ['_skin'] = $this->skeleton;
		
		$layout->addControl ( $this->type, $params, $this->skeleton, $this->skin, $this->position );
	}
	
	/**
	 * ABSTRACT: prints the control in the screen
	 */
	abstract function display();
	
	/**
	 * ABSTRACT: returns an html string of the control
	 */
	abstract function getHtml();
	
	/**
	 * Applies rwx permissions in the control
	 * 
	 * @param unknown_type $rwx        	
	 */
	public function applyRWX($rwx) {
		// Can be overriden in specific controls
	}
	
	// Auxiliary Functions
	/**
	 * Get a value from a parameter, it thrown an exception if the parameter is required and does not exists
	 * 
	 * @param (array) $params
	 *        	- array containing all the parameters
	 * @param (int) $num
	 *        	- index to return the value
	 * @param (bool) $mandatory
	 *        	- is this parameter mandatory?
	 * @param $default -
	 *        	default value to return if not mandatory set
	 * @throws Exception
	 */
	function getParam($params, $num, $mandatory, $default = null) {
		if (! isset ( $params [$num] ) && $mandatory)
			throw new \Exception ( $this->type . '->' . $this->name . ': Required parameter ' . $num . ' not found.' );
		
		if (! isset ( $params [$num] ))
			return $default;
		
		return $params [$num];
	}
	
	/**
	 * Gets a property from properties or directly from xml_definition
	 * 
	 * @param string $key        	
	 * @param string $property        	
	 * @param bool $mandatory        	
	 * @param string $default        	
	 * @param xml $xml
	 *        	- the element where to get the property. If null, get the property directly from xml_definition
	 *        	RETURNS the property value
	 */
	function getProperty($key, $property, $mandatory = false, $default = '', $xml = null, $parseBool = false) {
		$xml_element = ($xml) ? $xml : $this->xml_definition;
		
		$value = $default;
		
		if (isset ( $this->properties [( string ) $key] [( string ) $property] ))
			$value = $this->properties [( string ) $key] [( string ) $property];
		else if (isset ( $xml_element [( string ) $property] ))
			$value = ( string ) $xml_element [( string ) $property];
		
		if ($parseBool && $value != 'true' && $value != 'false') {
			// Parse the conditionExpr
			$eval = new \Kuink\Core\EvalExpr ();
			try {
				$data ['CAPABILITY'] = $this->nodeconfiguration ['capabilities'];
				$data ['ROLE'] = $this->nodeconfiguration ['roles'];
				$value = ($eval->e ( $value, $data, TRUE )) ? 'true' : 'false';
			} catch ( \Exception $e ) {
				var_dump ( 'Exception: eval' );
				die ();
			}
		}
		
		// kuink_mydebug($key.'.'.$property, $value);
		return $value;
	}
	
	/**
	 * Loads a datasource only once and store it in datasources property
	 * 
	 * @param (string) $datasourcename        	
	 * @param (string) $bindid        	
	 * @param (string) $bindvalue        	
	 */
	function loadDataSource($datasourcename, $bindid, $bindvalue) {
		// only load the datasource if the datasource is not loaded yet
		// kuink_mydebug('Datasource', $datasourcename);
		// kuink_mydebug('BindId', $bindid);
		// kuink_mydebug('BindValue', $bindvalue);
		if (! isset ( $this->datasources [$datasourcename] )) {
			// kuink_mydebug('Loading...', $datasourcename);
			$pos = strpos ( $datasourcename, 'table:' );
			if ($pos === 0 && ! $this->datasources [$datasourcename]) {
				// Get the options from the table only one time
				$table = str_replace ( 'table:', '', $datasourcename );
				$appName = ( string ) $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION];
				$databaseName = \Kuink\Core\DatabaseManager::applicationDefaultDB ( $appName );
				$datasource = new \Kuink\Core\DataSource ( null, 'framework,generic,getAll', 'framework', 'generic', $databaseName, $appName );
				$fields = $bindid . ',' . $bindvalue;
				$pars = array (
						'table' => $table,
						'fields' => $fields 
				);
				$selectoptions = $datasource->execute ( $pars );
				
				// Add this datasource to be filled in the next if
				$this->datasources [$datasourcename] = $selectoptions;
			}
			$pos = strpos ( $datasourcename, 'call:' );
			if ($pos === 0 && ! $this->datasources [$datasourcename]) {
				// Get the options from the table only one time
				
				$library = str_replace ( 'call:', '', $datasourcename );
				
				$globalParts = explode ( '(', $library );
				$callParams = null;
				$library = ( string ) $globalParts [0];
				if (count ( $globalParts ) == 2) {
					// Expand parameters
					$rawParams = ( string ) $globalParts [1];
					$rawParams = str_replace ( ')', '', $rawParams );
					
					$regVariableNameAndEqualSign = '[a-zA-Z0-9_\-\$\@\#\.\>]+=';
					$regValidValues = '[a-zA-Z0-9_\-\$\@\#\.\>,]*';
					$regFinal = '(' . $regVariableNameAndEqualSign . "\'+" . $regValidValues . "\'+" . '|' . $regVariableNameAndEqualSign . "\'{0}" . $regValidValues . "\'{0}" . ')';
					
					// preg_match_all("([a-zA-Z0-9_\-\$\@\#\.\>]+=\'+[a-zA-Z0-9_\-\$\@\#\.\>,]*\'+|[a-zA-Z0-9_\-\$\@\#\.\>]+=\'{0}[a-zA-Z0-9_\-\$\@\#\.\>]+\'{0})",
					preg_match_all ( $regFinal, $rawParams, $paramsPart );
					// $paramsPart=explode(',' ,$rawParams);
					// $paramsPart = preg_split("(?!\'*,*\')", $rawParams, null, PREG_SPLIT_DELIM_CAPTURE);
					// $paramsPart = preg_split("/','/", $rawParams, null, PREG_SPLIT_DELIM_CAPTURE);
					// $paramsPart = str_getcsv($rawParams,",","'","\\");
					// print_object($rawParams);
					// print_object($paramsPart);
					foreach ( $paramsPart [0] as $paramComplete ) {
						
						// print_object($paramComplete);
						$paramCompleteParts = explode ( '=', $paramComplete );
						if (count ( $paramCompleteParts ) != 2)
							throw new \Exception ( 'Invalid param format must be name=value and received ' . $paramComplete );
						$callParams [trim ( $paramCompleteParts [0] )] = trim ( str_replace ( "'", '', $paramCompleteParts [1] ) );
					}
				}
				
				$parts = explode ( ',', $library );
				if (count ( $parts ) != 4)
					throw new \Exception ( 'Invalid lirary,function name: ' . $datasourcename . count ( $parts ) );
				$node = new \Neon\Core\Node ( $parts [0], $parts [1], $parts [2] );
				$runtime = new \Neon\Core\Runtime ( $node, 'lib', null );
				
				$result = $runtime->execute ( $parts [3], $callParams );
				// print_object($result);
				
				$this->datasources [$datasourcename] = $result ['RETURN'];
			}
		}
		return;
	}
	
	// Find a value inside a datasource
	function datasourceFindValue($datasource, $bindid, $bindvalue, $id) {
		// kuink_mydebug($bindid, $bindvalue.'::'.$id);
		$value = $id;
		foreach ( $datasource as $data_item ) {
			$data_item = ( array ) $data_item;
			// var_dump($data_item);
			if (isset ( $data_item [$bindid] )) {
				if ($data_item [$bindid] == $id) {
					// var_dump($data_item[$bindid]);
					// kuink_mydebug($bindid, $bindvalue.'::'.$id.'::'.$data_item[$bindvalue]);
					$value = ( string ) $data_item [$bindvalue];
					break;
				}
			}
		}
		
		// kuink_mydebug($datasourcename.'.'.$bindid.'.'.$bindvalue.' - '.$id, $value);
		
		return $value;
	}
	function callFormatter($formatter_name, $value, $formatter_params = null, $formatter_params_expand_data = null) {
		// kuink_mydebug($formatter_name, $value);
		// Expand the formatter params data
		$this->expandFormatterParams ( $formatter_params_expand_data, $formatter_params );
		
		// If there is a datasource expand it
		if (isset ( $formatter_params ['datasource'] )) {
			$datasource = ( string ) $formatter_params ['datasource'];
			if (isset ( $this->datasources [$datasource] ))
				$formatter_params ['datasource'] = $this->datasources [$datasource];
			else
				throw new \Exception ( 'Datasource ' . $datasource . ' not found.' );
		}
		
		// kuink_mydebug($formatter_name, $value);
		$params = array ();
		$params [0] = $formatter_name;
		$params [1] = (isset ( $formatter_params ['method'] )) ? ( string ) $formatter_params ['method'] : 'format';
		$params [2] = $value;
		$params [3] = $formatter_params;
		
		$formatter = new \FormatterLib ( $this->nodeconfiguration, null );
		
		// var_dump( $formatter );
		
		// var_dump( $params );
		$result = $formatter->format ( $params );
		
		// kuink_mydebug($formatter_name.'.'.$value, $result);
		
		return $result;
	}
	
	/**
	 * Replaces $field with the corresponding value in the $data variable
	 * 
	 * @param unknown_type $data
	 *        	- dataset with row data
	 * @param unknown_type $params
	 *        	- params to the formatter
	 */
	function expandFormatterParams($data, &$params) {
		foreach ( $params as $key => $value ) {
			if ($value [0] == '$') {
				// It's a field name
				$field = substr ( $value, 1, strlen ( $value ) - 1 );
				
				// if the variable is not set in v$variables check in session variables
				$new_value = (isset ( $data [$field] )) ? ( string ) $data [$field] : '';
				// kuink_mydebug($field, $new_value);
				$params [$key] = $new_value;
			}
		}
		// var_dump($params);
		return $params;
	}
	
	/**
	 * Returns a renderer object for this type and style
	 * 
	 * @param
	 *        	$style
	 * @return the object renderer
	 */
	public function getRenderer($style) {
		// Returns the specific renderer for this style
		// $renderer = \Kuink\Control\Layout\LayoutFactory::getRenderer($style, $this->type);
		// return $renderer;
	}
	public function setContextVariable($key, $value) {
		$currentNode = \Kuink\Core\ProcessOrchestrator::getCurrentNode ();
		\Kuink\Core\ProcessOrchestrator::setProcessVariable ( '_' . $currentNode->nodeGuid . '_' . $this->type . '_' . $this->name, $key, $value );
	}
	public function getContextVariable($key) {
		$currentNode = \Kuink\Core\ProcessOrchestrator::getCurrentNode ();
		$value = \Kuink\Core\ProcessOrchestrator::getProcessVariable ( '_' . $currentNode->nodeGuid . '_' . $this->type . '_' . $this->name, $key );
		return $value;
	}
}

?>