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

use Kuink\Core\NodeConfKey;
use Kuink\Core as Core;

/**
 * Properties of the FORM
 * 
 * @author ptavares
 *        
 */
class FormProperty {
	const NAME = 'name';
	const VISIBLE = 'visible';
	const FREEZE = 'freeze';
	const UNFREEZEBUTTONS = 'unfreezebuttons'; // if the form is freezed, show the (buttons) actions anyway
	const TITLE = 'title';
	const TYPE = 'type';
	const INFER = 'infer';
	const TABS = 'tabs'; // The tabs position top | left | right
	const BUTTONS_POSITION = 'buttonsposition'; // The tabs position top |bottom | both
	const PERSIST = 'persist'; // persist the data of the form through screens
}

/**
 * Default values for FORM properties
 * 
 * @author ptavares
 *        
 */
class FormPropertyDefaults {
	const NAME = '';
	const VISIBLE = 'true';
	const FREEZE = 'false';
	const UNFREEZEBUTTONS = 'false';
	const TITLE = '';
	const TYPE = FormType::DATA;
	const INFER = 'false';
	const TABS = 'top';
	const BUTTONS_POSITION = 'bottom';
	const PERSIST = 'false';
}

/**
 * Properties of the fields
 * 
 * @author ptavares
 *        
 */
class FieldProperty {
	const ID = 'id';
	const VISIBLE = 'visible';
	const LABEL = 'label';
	const NAME = 'name';
	const HELP = 'help';
	const REQUIRED = 'required';
	const SKELETON = 'skeleton';
	const SKIN = 'skin';
	const INLINE = 'inline';
	const DISABLED = 'disabled';
	const MAXLENGTH = 'maxlength';
	const FREEZE = 'freeze';
	const CONFIRM = 'confirm';
	const THEME = 'theme';
	const DEFAULT_VALUE = 'default';
	const DEFAULT_BUTTON = 'default';
	const MULTILANG = 'multilang';
	const DATASOURCE = 'datasource';
	const DATASOURCE_PARAMS = 'datasource-params';
	const DATASOURCE_INITIAL = 'datasource-initial';
	const BINDID = 'bindid';
	const BINDVALUE = 'bindvalue';
	const BINDIMAGE = 'bindimage';
	const BINDRESULTS = 'bindresults';
	const CONTAINER = 'container';
	const TYPE = 'type';
	const NOW = 'now';
	const SIZE = 'size';
	const COLS = 'cols';
	const ROWS = 'rows';
	const ICON = 'icon';
	const STARTYEAR = 'startyear';
	const STOPYEAR = 'stopyear';
	const NEWCONTEXT = 'newcontext';
	const ACTION = 'action';
	const EVENT = 'event';
	const DECORATION = 'decoration';
	const INPUT_SIZE = 'inputsize';
	const LABEL_SIZE = 'label-size';
	const LABEL_POSITION = 'label-position';
	const LABEL_URL = 'label-url';
	const LABEL_URL_DESCRIPTION = 'label-url-description';
	const MODAL = 'modal';
	const SEARCHABLE = 'searchable';
	const PRINTABLE = 'printable';
	const TITLE = 'title';
	const BODY = 'body';
	const FOOTER = 'footer';
	const COLLAPSIBLE = 'collapsible';
	const COLLAPSED = 'collapsed';
	const RUNAT = 'runat';
	const VALIDATE = 'validate';
	const ALLOW_DELETE = 'allowdelete';
	const CLOSE = 'close';	
}

/**
 * Defaults for column properties
 * 
 * @author ptavares
 *        
 */
class FieldPropertyDefaults {
	const ID = '';
	const VISIBLE = 'true';
	const LABEL = '';
	const NAME = '';
	const HELP = 'true';
	const REQUIRED = 'false';
	const SKELETON = '';
	const SKIN = '';
	const INLINE = 'false';
	const DISABLED = 'false';
	const MAXLENGTH = '';
	const FREEZE = 'false';
	const CONFIRM = 'false';
	const THEME = 'simple';
	const DEFAULT_VALUE = '';
	const DEFAULT_BUTTON = 'false';
	const DATASOURCE_PARAMS = '';
	const DATASOURCE_INITIAL = '';
	const MULTILANG = 'false';
	const DATASOURCE = '';
	const BINDID = '';
	const BINDVALUE = '';
	const BINDIMAGE = '';
	const BINDRESULTS = '';
	const CONTAINER = '';
	const TYPE = '';
	const NOW = 'true';
	const SIZE = '50';
	const COLS = '50';
	const ROWS = '5';
	const ICON = '';
	const STARTYEAR = '';
	const STOPYEAR = '';
	const ACTION = '';
	const NEWCONTEXT = 'false';
	const EVENT = '';
	const DECORATION = '';
	const INPUT_SIZE = 'large';
	const LABEL_SIZE = 'small';
	const LABEL_POSITION = 'left';	
	const LABEL_URL = '';
	const LABEL_URL_DESCRIPTION = '';	
	const MODAL = 'false';
	const SEARCHABLE = 'false';
	const PRINTABLE = 'true';
	const TITLE = '';
	const BODY = '';
	const FOOTER = '';
	const COLLAPSIBLE = 'false';
	const COLLAPSED = 'true';
	const RUNAT = 'server';
	const VALIDATE = 'true';
	const ALLOW_DELETE = 'false';	
	const CLOSE = 'false';	
}

/**
 * Form type
 * 
 * @author ptavares
 *        
 */
class FormType {
	const SEARCH = 'search';
	const DATA = 'data';
}

/**
 * All field types
 * 
 * @author ptavares
 *        
 */
class FieldType {
	// Containers
	const HEADER = 'Header';
	const TAB = 'Tab';
	const COLUMN = 'Column';
	const CONTAINER = 'Container';
	
	// Fields
	const cSTATIC = 'Static';
	const TEXT = 'Text';
	const CODE = 'Code';
	const NAME = 'Name';
	const DESCRIPTION = 'Description';
	const INT = 'Int';
	const USER = 'User';
	const FILE = 'File';
	const DATE = 'Date';
	const DATETIME = 'DateTime';
	const HTML = 'Html';
	const SELECT = 'Select';
	const CHECKBOXLIST = 'CheckBoxList';
	const TEXTAREA = 'TextArea';
	const HIDDEN = 'Hidden';
	const CAPTCHA = 'Captcha';
	const ACTION = 'Action';
	const LINK = 'Link';
	const BUTTON = 'Button';
	
	// Groups
	const ACTIONGROUP = 'ActionGroup';
	const BUTTONGROUP = 'ButtonGroup';
}
class Form extends Control {
	var $baseurl; // baseurl for for action
	var $dynamic_fields; // Fields dynamically added to a container
	var $freeze; // is this form freezed
	var $type; // Form type
	           
	// Needed to handle static fields and form freeze
	var $static_bind;
	var $static_fields;
	var $listFormFields; // will hold all id's of form fields of type list
	                     
	// data needed to render
	var $form; // array containing form properties
	var $fields; // array containing all fields
	var $rules; // array containing all STATIC rules applyien to fields (required, integer, etc...)
	var $buttonActions; // ButtonGroups
	var $tabs; // Tabs present in the form <Tab id="..."/>
	var $columns; // Number of columns in the form if it doesn't have TABS!! If it has, then tey will be in the $this->tabs array
	var $dataBound; // Data Bound to the form with datasource and formatters completed
	var $dataBoundWithoutFormatter; //Data Bound without the formatters applied to the data	
	var $fieldFormatter; // holds the formatters of the fields (used in bindData)
	var $rulesClient; // Array to hold client side rules (dynamic rules)
	var $rulesServer; // Array to hold server side rules (dynamic rules)
	var $buttonsPosition; // Position of the button group top | bottom | both
	var $defaultData; // Form default data
	var $postData; // Form post data
	function __construct($nodeconfiguration, $xml_definition) {
		parent::__construct ( $nodeconfiguration, $xml_definition );
		
		$baseurl = $nodeconfiguration ['baseurl'];
		$url = \Kuink\Core\Tools::setUrlParams ( $baseurl );
		$this->baseurl = $url . '&form=' . $this->name;
		
		$this->dynamic_fields = array ();
		$this->fields = array ();
		$this->rules = array ();
		$this->tabs = array ();
		$this->columns = 0;
		$this->dataBound = array ();
		$this->dataBoundWithoutFormatter = array(); //original values before formatter		
		$this->listFormFields = array ();
		$this->rulesClient = array ();
		$this->rulesServer = array ();
	
		//Add the first tab
		$this->addTab('_default', '');
				
		$this->static_bind = array ();
	}
	
	/***
	 * Adds a tab to the form
	 * @param $id tabid
	 * @param $label tab label
	 */
	private function addTab( $id, $label )
	{
		if ($id == '_default')
			$this->tabs[-1] = array(FieldProperty::ID=>$id, FieldProperty::LABEL=>$label, 'columns'=>array(0) );
		else
			$this->tabs[] = array(FieldProperty::ID=>$id, FieldProperty::LABEL=>$label, 'columns'=>array(0) );
	}

	/***
	 * Adds a column to the current tab
	 */
	private function addCurrentTabColumn()
	{
		if (count($this->tabs[count($this->tabs)-2]['columns']) == 0)
			$this->tabs[count($this->tabs)-2]['columns'][] = 1;
		else 
			$this->tabs[count($this->tabs)-2]['columns'][count($this->tabs[count($this->tabs)-2]['columns'])-1] += 1;
	}

	/***
	 * Closes a column to the current tab
	 */
	private function closeCurrentTabColumn()
	{
		$this->tabs[count($this->tabs)-2]['columns'][] = 0;
	}


	/***
	 * Determines if this form has tabs
	 */
	private function hasTabs()
	{
		return ((count($his->tabs) > 1) ? 1 : 0);
	}

	/**
	 * Dynamically adding a rule to a form field
	 * 
	 * @param unknown_type $field_properties        	
	 */
	function addRule($params) {
		global $KUINK_LAYOUT;
		$theme = $KUINK_LAYOUT->getTheme(); 

		$numParams = count ( $params );
		if (count ( $params ) == 5 || count ( $params ) == 6) {
			$id = ( string ) $this->getParam ( $params, 0, true );
			$ruleAttr = ( string ) $this->getParam ( $params, 1, true );
			$ruleCondition = ( string ) $this->getParam ( $params, 2, true );
			$runat = ( string ) $this->getParam ( $params, 3, false, 'server' );
			$ruleAttrValue = ( string ) $this->getParam ( $params, 4, true );
			$oldAttrValue = ( string ) $this->getParam ( $params, 5, false, '' );
		} else
			throw new \Exception ( $this->type . '->' . $this->name . '. addRule: invalid number of parameters.' );
		
		if ($runat == 'client') {
			$clientCondition = $this->conditionToJavascript($ruleCondition, ($theme=='default') ? $this->name : $this->guid);			
			$this->rulesClient [] = array (
					'field' => $id,
					'attr' => $ruleAttr,
					'valueTrue' => $ruleAttrValue,
					'valueFalse' => $oldAttrValue,
					'condition' => $clientCondition 
			);
		} else if ($runat == 'server')
			$this->rulesServer [] = array (
					'field' => $id,
					'attr' => $ruleAttr,
					'valueTrue' => $ruleAttrValue,
					'valueFalse' => $oldAttrValue,
					'condition' => $condition 
			);
		else
			throw new \Exception ( 'Invalid runat value in rule' );
		
		// var_dump($this->rulesClient);
	}
	
	/**
	 * Dynamically adding a field to a form
	 * 
	 * @param unknown_type $field_properties        	
	 */
	function addField($fieldProperties) {
		$this->dynamic_fields [] = $fieldProperties;
		return;
	}
	
	/**
	 * Dynamically add a rule to a given field
	 * 
	 * @param unknown $fieldRule        	
	 */
	function addFieldRule($fieldRule) {
		global $KUINK_LAYOUT;
		$theme = $KUINK_LAYOUT->getTheme();
		
		$fieldId = ( string ) $fieldRule [0];
		$rule = $fieldRule [1];
		
		// Get the field default value for attribute
		$formField = $this->getFieldXml ( $fieldId );
		$oldAttrValue = $this->getProperty ( $fieldId, $rule ['attr'], false, '', $formField );
		
		$runat = $rule ['runat'];
		if ($runat == 'client') {
			$datasource = $rule ['datasource'];
			$datasourceParams = $rule ['datasourceparams'];
			$clientCondition = $this->conditionToJavascript($rule['condition'], ($theme=='default') ? $this->name : $this->guid);			
			$clientValueTrue = isset ( $rule ['valuetrue'] ) ? $rule ['valuetrue'] : $rule ['value'];
			$clientValueFalse = isset ( $rule ['valuefalse'] ) ? $rule ['valuefalse'] : $oldAttrValue;
			
			if ($datasource != '') {
				// var_dump($datasource);
				$clientDataSourceUrl = $this->getDataSourceUrl ( $datasource, $datasourceParams );
				$clientDataSourceParams = $this->getDataSourceParams ( $datasourceParams );
				
				// Register API to the context
				\Kuink\Core\ProcessOrchestrator::registerAPI ( $datasource );
				$this->rulesClient [] = array (
						'field' => $fieldId,
						'attr' => $rule ['attr'],
						'bindid' => $rule ['bindid'],
						'bindvalue' => $rule ['bindvalue'],
						'datasource' => $clientDataSourceUrl,
						'datasourceparams' => $clientDataSourceParams 
				);
			} else
				$this->rulesClient [] = array (
						'field' => $fieldId,
						'attr' => $rule ['attr'],
						'valueTrue' => $clientValueTrue,
						'valueFalse' => $clientValueFalse,
						'condition' => $clientCondition 
				);
		} else if ($runat == 'server')
			; // $this->rulesServer[] = array( 'field'=>$id, 'attr'=>$ruleAttr, 'valueTrue'=>$ruleAttrValue, 'valueFalse'=>$oldAttrValue, 'condition'=>$condition);
	}
	
	/**
	 * Freezes a form
	 */
	function applyRWX($rwx) {
		$type = $this->getProperty ( $this->name, FormProperty::TYPE, false, FormPropertyDefaults::TYPE );
		
		if (($type == FormType::DATA) && ($rwx == 4)) {
			$this->setProperty ( array (
					$this->name,
					FormProperty::FREEZE,
					'true' 
			) );
			$this->setProperty ( array (
					$this->name,
					FormProperty::UNFREEZEBUTTONS,
					'true' 
			) );
		}
		
		return;
	}
	
	/**
	 * Get the current xml definition of field if it exists
	 * 
	 * @param unknown $id        	
	 */
	function getFieldXml($id) {
		$fieldsXml = $this->xml_definition->xpath ( "//*[@id='$id']" );
		
		return (count ( $fieldsXml ) > 0) ? $fieldsXml [0] : null;
	}
	
	/**
	 * Gets the form field attributes to render
	 * 
	 * @param unknown_type $formfield        	
	 * @param unknown_type $id        	
	 * @return Array();
	 */
	function getFormfieldAttributes($formfield, $id) {
		$attributes = array ();
		
		// var_dump( $this->properties );
		
		$attributes [FieldProperty::ID] = $id;
		
		$confirm = $this->getProperty ( $id, FieldProperty::CONFIRM, false, FieldPropertyDefaults::CONFIRM, $formfield );
		if ($confirm == 'true')
			$confirmText = Core\Language::getString ( 'ask_proceed', 'framework' );
		else if ($confirm != 'false')
			$confirmText = Core\Language::getString ( $confirm, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [FieldProperty::CONFIRM] = ($confirm == 'false') ? $confirm : $confirmText;
		
		$label = $this->getProperty ( $id, FieldProperty::LABEL, false, $id, $formfield );
		$label = Core\Language::getString ( $label, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [FieldProperty::LABEL] = $label;
		
		$title = $this->getProperty ( $id, FieldProperty::TITLE, false, $id, $formfield );
		$title = Core\Language::getString ( $title, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [FieldProperty::TITLE] = $title;
		
		$body = $this->getProperty ( $id, FieldProperty::BODY, false, $id, $formfield );
		$body = Core\Language::getString ( $body, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [FieldProperty::BODY] = $body;
		
		$footer = $this->getProperty ( $id, FieldProperty::FOOTER, false, $id, $formfield );
		$footer = Core\Language::getString ( $footer, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [FieldProperty::FOOTER] = $footer;
		
		$showHelp = $this->getProperty ( $id, FieldProperty::HELP, false, FieldPropertyDefaults::HELP, $formfield );
		$help = '';
		if ($showHelp != 'false')
			$help = ($showHelp == 'true' || $showHelp == '') ? \Kuink\Core\Language::getHelpString ( $id, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] ) : \Kuink\Core\Language::getHelpString ( $showHelp, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [FieldProperty::HELP] = $help;
		$attributes [FieldProperty::NAME] = $this->getProperty ( $id, FieldProperty::NAME, false, FieldPropertyDefaults::NAME, $formfield );
		$attributes [FieldProperty::REQUIRED] = $this->getProperty ( $id, FieldProperty::REQUIRED, false, FieldPropertyDefaults::REQUIRED, $formfield, true ); //Parse Bool to evaluate conditions
		$attributes [FieldProperty::DISABLED] = $this->getProperty ( $id, FieldProperty::DISABLED, false, FieldPropertyDefaults::DISABLED, $formfield, true ); //Parse Bool to evaluate conditions
		$attributes [FieldProperty::MAXLENGTH] = $this->getProperty ( $id, FieldProperty::MAXLENGTH, false, FieldPropertyDefaults::MAXLENGTH, $formfield );
		$attributes [FieldProperty::VISIBLE] = $this->getProperty ( $id, FieldProperty::VISIBLE, false, FieldPropertyDefaults::VISIBLE, $formfield, true ); //Parse Bool to evaluate conditions
		$attributes [FieldProperty::FREEZE] = $this->getProperty ( $id, FieldProperty::FREEZE, false, FieldPropertyDefaults::FREEZE, $formfield, true ); //Parse Bool to evaluate conditions
		$attributes [FieldProperty::INLINE] = $this->getProperty ( $id, FieldProperty::INLINE, false, FieldPropertyDefaults::INLINE, $formfield, true ); //Parse Bool to evaluate conditions
		$attributes [FieldProperty::SIZE] = $this->getProperty ( $id, FieldProperty::SIZE, false, FieldPropertyDefaults::SIZE, $formfield );
		$attributes [FieldProperty::ICON] = $this->getProperty ( $id, FieldProperty::ICON, false, FieldPropertyDefaults::ICON, $formfield );
		$attributes [FieldProperty::COLS] = $this->getProperty ( $id, FieldProperty::COLS, false, FieldPropertyDefaults::COLS, $formfield );
		$attributes [FieldProperty::ROWS] = $this->getProperty ( $id, FieldProperty::ROWS, false, FieldPropertyDefaults::ROWS, $formfield );
		$attributes [FieldProperty::MULTILANG] = $this->getProperty ( $id, FieldProperty::MULTILANG, false, FieldPropertyDefaults::MULTILANG, $formfield );
		$attributes [FieldProperty::DATASOURCE] = $this->getProperty ( $id, FieldProperty::DATASOURCE, false, FieldPropertyDefaults::DATASOURCE, $formfield );
		$attributes [FieldProperty::DATASOURCE_PARAMS] = $this->getProperty ( $id, FieldProperty::DATASOURCE_PARAMS, false, FieldPropertyDefaults::DATASOURCE_PARAMS, $formfield );
		$attributes [FieldProperty::DATASOURCE_INITIAL] = $this->getProperty ( $id, FieldProperty::DATASOURCE_INITIAL, false, FieldPropertyDefaults::DATASOURCE_INITIAL, $formfield );
		$attributes [FieldProperty::BINDID] = $this->getProperty ( $id, FieldProperty::BINDID, false, FieldPropertyDefaults::BINDID, $formfield );
		$attributes [FieldProperty::BINDVALUE] = $this->getProperty ( $id, FieldProperty::BINDVALUE, false, FieldPropertyDefaults::BINDVALUE, $formfield );
		$attributes [FieldProperty::BINDIMAGE] = $this->getProperty ( $id, FieldProperty::BINDIMAGE, false, FieldPropertyDefaults::BINDIMAGE, $formfield );
		$attributes [FieldProperty::BINDRESULTS] = $this->getProperty ( $id, FieldProperty::BINDRESULTS, false, FieldPropertyDefaults::BINDRESULTS, $formfield );
		$attributes [FieldProperty::STARTYEAR] = $this->getProperty ( $id, FieldProperty::STARTYEAR, false, FieldPropertyDefaults::STARTYEAR, $formfield );
		$attributes [FieldProperty::STOPYEAR] = $this->getProperty ( $id, FieldProperty::STOPYEAR, false, FieldPropertyDefaults::STOPYEAR, $formfield );
		$attributes [FieldProperty::NOW] = $this->getProperty ( $id, FieldProperty::NOW, false, FieldPropertyDefaults::NOW, $formfield );
		$attributes [FieldProperty::SKELETON] = $this->getProperty ( $id, FieldProperty::SKELETON, false, FieldPropertyDefaults::SKELETON, $formfield );
		$attributes [FieldProperty::SKIN] = $this->getProperty ( $id, FieldProperty::SKIN, false, FieldPropertyDefaults::SKIN, $formfield );
		$attributes [FieldProperty::TYPE] = $this->getProperty ( $id, FieldProperty::TYPE, false, FieldPropertyDefaults::TYPE, $formfield );
		$attributes [FieldProperty::ACTION] = $this->getProperty ( $id, FieldProperty::ACTION, false, FieldPropertyDefaults::ACTION, $formfield );
		$attributes [FieldProperty::EVENT] = $this->getProperty ( $id, FieldProperty::EVENT, false, FieldPropertyDefaults::EVENT, $formfield );
		$attributes [FieldProperty::DECORATION] = $this->getProperty ( $id, FieldProperty::DECORATION, false, FieldPropertyDefaults::DECORATION, $formfield );
		$attributes [FieldProperty::THEME] = $this->getProperty ( $id, FieldProperty::THEME, false, FieldPropertyDefaults::THEME, $formfield );
		$attributes [FieldProperty::INPUT_SIZE] = $this->getProperty ( $id, FieldProperty::INPUT_SIZE, false, FieldPropertyDefaults::INPUT_SIZE, $formfield );
		$attributes [FieldProperty::LABEL_SIZE] = $this->getProperty($id, FieldProperty::LABEL_SIZE, false, FieldPropertyDefaults::LABEL_SIZE, $formfield);
		$attributes [FieldProperty::LABEL_POSITION] = $this->getProperty($id, FieldProperty::LABEL_POSITION, false, FieldPropertyDefaults::LABEL_POSITION, $formfield);
		$attributes [FieldProperty::LABEL_URL] = $this->getProperty($id, FieldProperty::LABEL_URL, false, FieldPropertyDefaults::LABEL_URL, $formfield);
		$attributes [FieldProperty::LABEL_URL_DESCRIPTION] = $this->getProperty($id, FieldProperty::LABEL_URL_DESCRIPTION, false, FieldPropertyDefaults::LABEL_URL_DESCRIPTION, $formfield);
		$attributes [FieldProperty::DEFAULT_BUTTON] = $this->getProperty ( $id, FieldProperty::DEFAULT_BUTTON, false, FieldPropertyDefaults::DEFAULT_BUTTON, $formfield );
		$attributes [FieldProperty::MODAL] = $this->getProperty ( $id, FieldProperty::MODAL, false, FieldPropertyDefaults::MODAL, $formfield );
		$attributes [FieldProperty::SEARCHABLE] = $this->getProperty ( $id, FieldProperty::SEARCHABLE, false, FieldPropertyDefaults::SEARCHABLE, $formfield );
		$attributes [FieldProperty::COLLAPSIBLE] = $this->getProperty ( $id, FieldProperty::COLLAPSIBLE, false, FieldPropertyDefaults::COLLAPSIBLE, $formfield );
		$attributes [FieldProperty::COLLAPSED] = $this->getProperty ( $id, FieldProperty::COLLAPSED, false, FieldPropertyDefaults::COLLAPSED, $formfield );
		$attributes [FieldProperty::RUNAT] = $this->getProperty ( $id, FieldProperty::RUNAT, false, FieldPropertyDefaults::RUNAT, $formfield );
		$attributes [FieldProperty::NEWCONTEXT] = $this->getProperty ( $id, FieldProperty::NEWCONTEXT, false, FieldPropertyDefaults::NEWCONTEXT, $formfield );
		$attributes [FieldProperty::PRINTABLE] = $this->getProperty ( $id, FieldProperty::PRINTABLE, false, FieldPropertyDefaults::PRINTABLE, $formfield );
		$attributes [FieldProperty::VALIDATE] = $this->getProperty ( $id, FieldProperty::VALIDATE, false, FieldPropertyDefaults::VALIDATE, $formfield );
		$attributes [FieldProperty::ALLOW_DELETE] = $this->getProperty($id, FieldProperty::ALLOW_DELETE, false, FieldPropertyDefaults::ALLOW_DELETE, $formfield);
		$attributes [FieldProperty::CLOSE] = $this->getProperty($id, FieldProperty::CLOSE, false, FieldPropertyDefaults::CLOSE, $formfield);
		
		return $attributes;
	}
	
	/**
	 * Set the default data for the form
	 * 
	 * @param unknown_type $defaultData        	
	 */
	function setDefaultData($params) {
		$defaultData = ( array ) $params [0];
		$this->defaultData = $defaultData;
	}
	
	/**
	 * Set the postData
	 * 
	 * @param unknown_type $defaultData        	
	 */
	function setPostData($params) {
		$postData = ( array ) $params [0];
		$this->postData = $postData;
	}
	function getCurrentData() {
		// Order of data:
		// (1)bind data: Data bound by the action
		// (2)context stored data: stored data in a process variable
		// (3)default data: if none of the above set the
		$currentData = null;
		$persist = $this->getProperty ( $this->name, FormProperty::PERSIST, false, FormPropertyDefaults::PERSIST );
		$storedData = $this->getContextVariable ( $this->name . '_contextData' );
		//var_dump($storedData);
		// print_object($this->name.'_contextData');
		if (count ( $this->bind_data ) > 0) {
			// print_object('currentData::POSTDATA');
			// print_object($this->bind_data);
			$newBindDataArray = array ();
			foreach ( $this->bind_data as $bind_data )
				foreach ( $bind_data as $newBindKey => $newBindData )
					$newBindDataArray [$newBindKey] = $newBindData;
			//print_object($newBindDataArray);
			$currentData = $newBindDataArray;
			// return $this->bind_data[0];
		} else
		if (count ( $storedData ) > 0) {
			// print_object('currentData::CONTEXT');
			// return $storedData[0];
			$currentData =  $storedData;
		} else
		if (count ( $this->defaultData ) > 0) {
			// print_object('currentData::DEFAULT');
			$currentData =  $this->defaultData;
		}
		//var_dump($storedData);
		//var_dump($currentData);
		return $currentData;
	}
	
	/**
	 * Gets the options of a field
	 * 
	 * @param unknown_type $formfield        	
	 * @param unknown_type $attributes        	
	 */
	function getFormFieldOptions($formfield, $attributes) {
		$fieldOptions = array ();
		$fieldOptions [''] = '';
		
		$datasourcename = ( string ) $attributes [FieldProperty::DATASOURCE];
		$bindid = ( string ) $attributes [FieldProperty::BINDID];
		$bindvalue = ( string ) $attributes [FieldProperty::BINDVALUE];
		
		if ($datasourcename == "")
			foreach ( $formfield->children () as $options )
				foreach ( $options->children () as $option ) {
					$id = ( string ) $option ['id'];
					$name = ( string ) $option [0];
					$name_translated = Core\Language::getString ( $name, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
					$fieldOptions [$id] = $name_translated;
				}
		else {
			// Check if we can get datasource from a table
			//kuink_mydebug('Loading datasources: '.$datasourcename);

			// Get from POST
			$fieldname='';
			$elem = isset($_POST [$fieldname]) ? $_POST [$fieldname] : null;
			if ($elem)
				$fieldOptions [$id] = '';
		}
		return $fieldOptions;
	}
	
	/**
	 * If this field has formatters, then add them to the formatters collection
	 * 
	 * @param unknown_type $formfield        	
	 * @param unknown_type $id        	
	 */
	function addFormatter($formfield, $id) {
		$f_xml = $formfield->children ();
		foreach ( $f_xml as $child ) {
			$childname = ( string ) $child->getname ();
			if ($childname == 'Formatter') {
				$formatterName = ( string ) $child ['name'];
				$formatterAttributes = $child->attributes ();
				// print_object($formatter_attributes);
				$fAttributes = array ();
				foreach ( $formatterAttributes as $fAttrName => $fAttrValue ) {
					$fAttributes [( string ) $fAttrName] = ( string ) $fAttrValue;
				}
				// Add the params to the formatter
				$fParams = $child->children ();
				foreach ( $fParams as $fParam ) {
					// print_object($fParam);
					$fAttributes [( string ) $fParam ['name']] = ( string ) $fParam [0];
				}
				// print_object($fAttributes);
				
				// $this->fieldFormatter[ $id ] = array( $formatter_name => $formatter_attributes);
				$this->fieldFormatter [$id] = array (
						$formatterName => $fAttributes 
				);
			}
			// print_object($this->fieldFormatter);
		}
		return;
	}
	
	// If this field has rules, then register them
	private function addRules($formfield, $id, $attributes) {
		global $KUINK_LAYOUT;
		$theme = $KUINK_LAYOUT->getTheme();
		
		$f_xml = $formfield->children ();
		foreach ( $f_xml as $child ) {
			$childname = ( string ) $child->getname ();
			if ($childname == 'Rule') {
				$ruleRunAt = isset ( $child ['runat'] ) ? ( string ) $child ['runat'] : 'server';
				$ruleCondition = isset ( $child ['condition'] ) ? ( string ) $child ['condition'] : '';
				$ruleAttr = isset ( $child ['attr'] ) ? ( string ) $child ['attr'] : 'visible';
				$ruleAttrValue = isset ( $child ['value'] ) ? ( string ) $child ['value'] : '';
				$datasource = isset ( $child ['datasource'] ) ? ( string ) $child ['datasource'] : '';
				$datasourceParams = isset ( $child ['datasourceparams'] ) ? ( string ) $child ['datasourceparams'] : '';
				
				$oldAttrValue = isset($attributes [$ruleAttr]) ? ( string ) $attributes [$ruleAttr] : '';
				if ($ruleRunAt == 'client') {
					$clientCondition = $this->conditionToJavascript($ruleCondition ,($theme=='default') ? $this->name : $this->guid);
					if ($datasource != '') {
						// var_dump($datasource);
						$clientDataSourceUrl = $this->getDataSourceUrl ( $datasource, $datasourceParams );
						$clientDataSourceParams = $this->getDataSourceParams ( $datasourceParams );
						
						// Register API to the context
						\Kuink\Core\ProcessOrchestrator::registerAPI ( $datasource );
						$this->rulesClient [] = array (
								'field' => $id,
								'attr' => $ruleAttr,
								'bindid' => $attributes ['bindid'],
								'bindvalue' => $attributes ['bindvalue'],
								'datasource' => $clientDataSourceUrl,
								'datasourceparams' => $clientDataSourceParams 
						);
					} else
						$this->rulesClient [] = array (
								'field' => $id,
								'attr' => $ruleAttr,
								'valueTrue' => $ruleAttrValue,
								'valueFalse' => $oldAttrValue,
								'condition' => $clientCondition 
						);
				} else if ($ruleRunAt == 'server') {
					$this->rulesServer [] = array (
							'field' => $id,
							'attr' => $ruleAttr,
							'valueTrue' => $ruleAttrValue,
							'valueFalse' => $oldAttrValue,
							'condition' => $ruleCondition 
					);
				} else
					throw new \Exception ( 'Invalid runat value in rule' );
			}
		}
		
		return;
	}
	
	/**
	 * Overriding for backwards compatibility
	 * 
	 * @see Kuink\UI\Control.Control::setProperty()
	 */
	/*
	 * function setProperty( $params ) {
	 * $new[] = $this->name;
	 * $new[] = $params[0];
	 * $new[] = $params[1];
	 * parent::setProperty( $new );
	 * }
	 */
	
	/**
	 * For backwards compatibility
	 * 
	 * @param unknown_type $params        	
	 */
	function setFieldProperty($params) {
		parent::setProperty ( $params );
	}
	
	/**
	 * Builds the form from the current state
	 */
	function build() {
		$freeze = $this->getProperty ( $this->name, FormProperty::FREEZE, false, FormPropertyDefaults::FREEZE, $this->xml_definition, true );
		$visible = $this->getProperty ( $this->name, FormProperty::VISIBLE, false, FormPropertyDefaults::VISIBLE, $this->xml_definition, true);
		$title = $this->getProperty ( $this->name, FormProperty::TITLE, false, FormPropertyDefaults::TITLE );
		
		$this->buttonsPosition = $this->getProperty ( $this->name, FormProperty::BUTTONS_POSITION, false, FormPropertyDefaults::BUTTONS_POSITION );
		
		if ($visible != 'true')
			return;
		
		$form ['title'] = Core\Language::getString ( $title, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$form ['_guid'] = $this->getProperty( $this->name, FormProperty::NAME, false, $this->name);//Default theme doesnt support the guid so keep it this way
		$form ['name'] = $this->getProperty ( $this->name, FormProperty::NAME, false, $this->name );
		$form ['baseUrl'] = $this->nodeconfiguration [Core\NodeConfKey::BASEURL] . '&form=' . $this->name;
		$this->form = $form;
		
		$this->freeze = ($freeze == 'true') ? 1 : 0;
		// var_dump($this->dynamic_properties);
		
		$form = $this->xml_definition;
		
		foreach ( $form->children () as $formfield ) {
			$type = ( string ) $formfield->getname ();
			$id = ( string ) $formfield ['id'];
			
			// $field = new FormField($id, $type, $attributes, $options, $skeleton, $skin);
			$field = $this->expandField ( $formfield, $id, $type );
		}
	}
	
	/**
	 * Builds a form field from its current state
	 * 
	 * @param string $formfield        	
	 * @param string $id        	
	 * @param string $type        	
	 * @return
	 *
	 */
	function expandField($formfield, $id, $type) {
		$attributes = $this->getFormFieldAttributes ( $formfield, $id );
		$options = $this->getFormFieldOptions ( $formfield, $attributes );
		
		$visible = $attributes [FieldProperty::VISIBLE];
		$datasourcename = ( string ) $attributes [FieldProperty::DATASOURCE];
		$datasourceparams = ( string ) $attributes [FieldProperty::DATASOURCE_PARAMS];
		$bindid = ( string ) $attributes [FieldProperty::BINDID];
		$bindvalue = ( string ) $attributes [FieldProperty::BINDVALUE];
		$skeleton = $attributes [FieldProperty::SKELETON];
		$skin = $attributes [FieldProperty::SKIN];
		$freeze = $attributes [FieldProperty::FREEZE];
		$searchable = $attributes [FieldProperty::SEARCHABLE];
		$multilang = $attributes [FieldProperty::MULTILANG];
		
		// Handle multilang
		if ($multilang == 'true') {
			// load the datasource
			if (! isset ( $this->datasources ['_lang'] )) {
				$dataAccess = new \Kuink\Core\DataAccess ( 'getAll' );
				$params ['_entity'] = 'fw_lang';
				// $params['id_company'] = 1; //Hard coded for now... $variables['USER']['idCompany'];
				$params ['is_active'] = 1;
				$resultSet = $dataAccess->execute ( $params );
				$dsParams = array ();
				$dsParams [] = '_lang';
				$dsParams [] = $resultSet;
				$this->addDatasource ( $dsParams );
			}
		}
		
		if ($searchable == 'dynamic') {
			$registerDatasource = str_replace ( 'call:', '', $datasourcename );
			\Kuink\Core\ProcessOrchestrator::registerAPI ( $registerDatasource );
			
			// Register also the initial datasource
			if (isset ( $attributes [FieldProperty::DATASOURCE_INITIAL] )) {
				$datasourcenameInitial = ( string ) $attributes [FieldProperty::DATASOURCE_INITIAL];
				$registerDatasourceInitial = str_replace ( 'call:', '', $datasourcenameInitial );
				\Kuink\Core\ProcessOrchestrator::registerAPI ( $registerDatasourceInitial );
			}
		}
		
		$unfreezeButtons = $this->getProperty ( $this->name, FormProperty::UNFREEZEBUTTONS, false, FormPropertyDefaults::UNFREEZEBUTTONS );
		// kuink_mydebug( __METHOD__, $type.'.'.$id );
		
		// If is invisible, then return doing nothing
		if ($visible == 'false')
			return;
			
		//Add the tab to the tabs array
		if ($type == FieldType::TAB)
			$this->addTab($id, (string)$attributes[FieldProperty::LABEL]);

		if ($type == FieldType::COLUMN) {
			if ($attributes[FieldProperty::CLOSE] == 'true') 
				$this->closeCurrentTabColumn();
			else
				$this->addCurrentTabColumn();
		}

		
		if ($type == FieldType::CHECKBOXLIST || $attributes [FieldProperty::MULTILANG] == 'true')
			$this->listFormFields [] = $id;
		
		/**
		 * Use this to get html from captcha object
		 *
		 * if ($type == FieldType::CAPTCHA) {
		 * $fieldProperty = array();
		 * $this->dataBound[$id] = recaptcha_get_html($key);
		 * }
		 */
			
		// Handle Dynamic fields and containers
		if ($type == FieldType::CONTAINER) {
			// kuink_mydebug( __METHOD__, 'DynamicFields' );
			$this->buildDynamicFields ( $formfield, $id );
		}
		
		// Static all the fields if the form is static
		if (($this->freeze || $freeze == 'true') && ($type != FieldType::HEADER) && ($type != FieldType::TAB) && ($type != FieldType::COLUMN)) {
			if ($type == FieldType::CONTAINER)
				return;
			else {
				if ($type == FieldType::BUTTONGROUP && $unfreezeButtons == 'true')
					$this->buildButtonGroup ( $formfield, $id );
			}
			$type = FieldType::cSTATIC;
		}
		
		// Adding required
		if ($attributes [FieldProperty::REQUIRED] == 'true' && ($type != FieldType::cSTATIC))
			$this->rules [$id] [] = 'required';
			
		// Adding formatters
		if ($type == FieldType::cSTATIC || $type == FieldType::FILE) {
			$this->addFormatter ( $formfield, $id );
		}
		
		// Adding Rules
		$this->addRules ( $formfield, $id, $attributes );
		
		if ($type == FieldType::BUTTONGROUP) {
			$this->buildButtonGroup ( $formfield, $id );
		}
		if ($type == FieldType::ACTIONGROUP) {
			// The list of links to actions are set in the options array
			$options = $this->buildActionGroup ( $formfield, $id );
		}
		
		// Handle static fields, specially when Select fields are freezed!!
		// This is needed for binding the value instead of the id...
		if ($type == FieldType::cSTATIC) {
			if ($datasourcename != '')
				// Store this field to be bind when setting the data
				// This is usefull to when the bind as the id but we want to show the bind value
				$datasourceparams = ($datasourceparams != '') ? '('.$datasourceparams.')': $datasourceparams;
				$this->static_bind [$id] = $datasourcename.$datasourceparams . '|' . $bindid . '|' . $bindvalue;
			$this->static_fields [$id] = true;
			
			
			//options will return allways the empty option se if count(options) > 1 states that there are options
			if (count($options) > 1) {
				
				$datasource = array ();
				foreach ( $options as $key => $value )
					$datasource [$key] = array (
							'id' => $key,
							'name' => $value 
					);
				
				$datasourcename = 'static_' . $id;
				$this->datasources [$datasourcename] = $datasource;
				$this->static_bind [$id] = $datasourcename;
			}
			//kuink_mydebug('datasource', $this->static_bind [$id].'::'.count($options));
			
		}
		
		if ($type == FieldType::INT)
			$this->rules [$id] [] = 'integer';
			
			// Get the value of the field from the databound property
		$value = isset ( $this->dataBound [$id] ) ? $this->dataBound [$id] : null;
		$originalValue = isset( $this->dataBoundWithoutFormatter[ $id ] ) ? $this->dataBoundWithoutFormatter[ $id ] : null;		
		
		// Finally add the field to the fields collection
		// Do not add container field, just the dynamic fields included
		if ($type != FieldType::CONTAINER && $type != FieldType::BUTTONGROUP && $visible == 'true') {
			$field = array (
					'type' => $type,
					'attributes' => $attributes,
					'options' => $options,
					'skeleton' => $skeleton,
					'skin' => $skin,
					'value' => $value,
					'original'=>$originalValue 
			);
			$this->fields [$id] = $field;
		}
		
		return;
	}
	
	/**
	 * Gets a mandatory dynamic field property
	 * 
	 * @param unknown_type $dynamic_field        	
	 * @param unknown_type $property_name        	
	 * @throws Exception
	 */
	function getDynamicFieldProperty($dynamic_field, $property_name) {
		if (! isset ( $dynamic_field [$property_name] ))
			throw new \Exception ( 'Cannot add a dynamic field to a form without ' . $property_name . ' property' );
		return $dynamic_field [$property_name];
	}
	
	/**
	 * Build the dynamic fields in the form
	 * 
	 * @param unknown_type $mform        	
	 * @param unknown_type $formfield        	
	 * @param unknown_type $fieldname        	
	 */
	function buildDynamicFields($formfield, $fieldname) {
		// iterate through dynamic fields
		foreach ( $this->dynamic_fields as $dynamic_field ) {
			$dynamic_field = $dynamic_field [0];
			$dyn = $this->getDynamicFieldProperty ( $dynamic_field, 'container' );
			$id = $this->getDynamicFieldProperty ( $dynamic_field, 'id' );
			$type = $this->getDynamicFieldProperty ( $dynamic_field, 'type' );
			$options = isset ( $dynamic_field ['options'] ) ? $dynamic_field ['options'] : null;
			$dynValue = isset ( $dynamic_field ['value'] ) ? ( string ) $dynamic_field ['value'] : '';
			// var_dump( $options );
			
			// Put all dynamic field properties in the property array
			foreach ( $dynamic_field as $key => $value ) {
				//kuink_mydebug('setProperty id('.$id.') key('.$key.') value('.$value.')', '');
				
				if ($value !== null)
					parent::setProperty ( array (
							$id,
							$key,
							$value 
					) );
			}
				
				// If there are options, then create a datasource and set it
			if (! is_null ( $options )) {
				$datasource = $dyn . '.' . $id;

				parent::setProperty ( array (
						$id,
						'datasource',
						$datasource 
				) );
				parent::setProperty ( array (
						$id,
						'bindid',
						'id' 
				) );
				parent::setProperty ( array (
						$id,
						'bindvalue',
						'value' 
				) );
				$this->datasources [$datasource] = $options;
			}
			
			// Add this dynamic field if this is the dynamic container
			if ($dyn == $fieldname)
				$this->expandField ( $formfield, $id, $type );
			
			if ($dynValue != '') {
				// kuink_mydebug('binding...', $dynValue);
				// var_dump( $dynamic_field );
				$this->bind ( array (
						array (
								$id => $dynValue 
						) 
				) );
			}
		}
	}
	function buildActionGroup($formfield, $fieldname) {
		$actionarray = array ();
		foreach ( $formfield->children () as $action ) {
			$id = ( string ) $action [FieldProperty::ID];
			$attributes = $this->getFormfieldAttributes ( $action, $id );
			$name = ( string ) $attributes [FieldProperty::NAME];
			$label = ( string ) $attributes [FieldProperty::LABEL];
			$visible = ( string ) $attributes [FieldProperty::VISIBLE];
			$modal = ( string ) $attributes [FieldProperty::MODAL];
			
			$actionPermissions = $this->nodeconfiguration [NodeConfKey::ACTION_PERMISSIONS];
			if ($actionPermissions [$name] && $visible == 'true') {
				// The user has permissions to execute this action
				// Add this action to the array
				$label = Core\Language::getString ( $label, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
				$utils = new \UtilsLib ( $this->nodeconfiguration, null );
				$url = $utils->ActionUrl ( array (
						0 => $name 
				) );
				$url = ($modal != 'false') ? $url . '&modal=' . $modal : $url;
				$actionarray [$label] = array (
						'url' => $url,
						'modal' => $modal 
				);
			}
		}
		// var_dump( $actionarray );
		return $actionarray;
	}
	function buildButtonGroup($formfield, $fieldname) {
		// Iterate through dynamic fields
		// If there isn't any button with default="true" then set the default to the first non cancel type button
		$hasDefault = false;
		
		foreach ( $formfield->children () as $button ) {
			$id = ( string ) $button [FieldProperty::ID];
			$attributes = $this->getFormfieldAttributes ( $button, $id );
			
			$default = ( string ) $attributes [FieldProperty::DEFAULT_BUTTON];
			
			if ($default == 'true') {
				if (! $hasDefault)
					$hasDefault = true;
			}
		}
		
		foreach ( $formfield->children () as $button ) {
			$id = ( string ) $button [FieldProperty::ID];
			$attributes = $this->getFormfieldAttributes ( $button, $id );
			$type = ( string ) $attributes [FieldProperty::TYPE];
			$visible = ( string ) $attributes [FieldProperty::VISIBLE];
			$decoration = ( string ) $attributes [FieldProperty::DECORATION];
			$default = ( string ) $attributes [FieldProperty::DEFAULT_BUTTON];
			$help = ( string ) $attributes [FieldProperty::HELP];
			$runat = ( string ) $attributes [FieldProperty::RUNAT];
			$attributes [FieldProperty::HELP] = ($help == $id) ? 'false' : kuink_get_help_string ( $help );
			if (! $hasDefault) {
				if ($type != 'cancel' && $type != 'back' && $visible == 'true') {
					$hasDefault = true;
					$attributes [FieldProperty::DEFAULT_BUTTON] = 'true';
				}
			} else
				$attributes [FieldProperty::DEFAULT_BUTTON] = 'false';
			
			if ($visible == 'true') {
				$action = ( string ) $button [FieldProperty::ACTION];
				$event = ( string ) $button [FieldProperty::EVENT];
				
				$actionPermissions = $this->nodeconfiguration [NodeConfKey::ACTION_PERMISSIONS];
				if ($event != '' || $runat == 'client' || (isset($actionPermissions [$action]) && $actionPermissions [$action])) {
					// $attributes = $this->getFormfieldAttributes($button, $id);
					$skeleton = $attributes [FieldProperty::SKELETON];
					$skin = $attributes [FieldProperty::SKIN];
					$this->buttonActions [$id] = array (
							'type' => $type,
							'attributes' => $attributes,
							'skeleton' => $skeleton,
							'skin' => $skin 
					);
				}
			}
		}
	}
	
	/**
	 * Binds the data to the form resolving datasources and formatters
	 */
	function bindData() {
		$persist = $this->getProperty ( $this->name, FormProperty::PERSIST, false, FormPropertyDefaults::PERSIST );
		$storedData = $this->getContextVariable ( $this->name . '_contextData' );
		if (count ( $this->bind_data ) == 0) {
			// No bind data
			// print_object('NO POST DATA');
			if (is_array($storedData) && count ( $storedData ) > 0) {
				// Set context Data
				// print_object('SET CONTEXT DATA');
				// $this->bind_data = $storedData;
				$this->bind_data [] = $storedData;
			} else {
				// print_object('SET DEFAULT');
				$this->bind_data [] = $this->defaultData;
			}
		} else  // Store the data in context
			if ($persist == 'true') {
				// $this->setContextVariable($this->name.'_contextData', $this->bind_data);
				
				$newStoredData = array ();
				foreach ( $this->bind_data as $bind_data ) {
					if (is_array($bind_data))
						foreach ( $bind_data as $newBindKey => $newBindData )
							$newStoredData [$newBindKey] = $newBindData;
				}
				//kuink_mydebugObj('Setting...', $newStoredData);
				$this->setContextVariable ( $this->name . '_contextData', $newStoredData );
			}
		
		//var_dump( $this->bind_data );
		// print_object( $this->static_bind );
		//print_object( $this->datasources );
		// print_object( $this->dynamic_fields);
		
		$infer = $this->getProperty ( $this->name, FormProperty::INFER, false, FormPropertyDefaults::INFER );
		// Check if this FORM is to infer or not
		if ($infer == 'true') {
			// Build the grid columns
			$fields = array ();
			
			foreach ( $this->bind_data as $data ) {
				if (is_array($data) || is_object($data))
					foreach ( $data as $key => $value ) {
						$fields [] = $key;
					}
			}
			foreach ( $fields as $key => $value ) {
				$this->addField ( array (
						array (
								'container' => '_infer',
								'id' => $value,
								'type' => 'Text',
								'label' => $value 
						) 
				) );
			}
			// Create a container for the fields with predefined id:_infer
			$formField = new \SimpleXMLElement ( '<Container id="_infer"/>' );
			$this->buildDynamicFields ( $formField, '_infer' );
		}
		
		//merge all bind_data in one array overriding values from the order
		$bind_data = array();
		foreach ( $this->bind_data as $data ) {
			$data = ( array ) $data;
			$bind_data = array_merge($bind_data,$data);
		}
		$bind_data_original = $bind_data;

		//Load all datasources and rebuild options
		//build all the datasources at this time, because we have data so datasource parameters can be expanded
		foreach ($this->fields as $index=>$field) {
			$attributes = $field['attributes'];
			$datasourcename = ( string ) $attributes [FieldProperty::DATASOURCE];
			$bindid = ( string ) $attributes [FieldProperty::BINDID];
			$bindvalue = ( string ) $attributes [FieldProperty::BINDVALUE];
			$datasourceParams = ( string ) $attributes [FieldProperty::DATASOURCE_PARAMS];
			
			if ($datasourcename != '') {
				if ($datasourceParams != '')
					$datasourcename = $datasourcename.'('.$datasourceParams.')';
				//kuink_mydebug('loading datasource', $datasourcename);
				$this->loadDataSource($datasourcename, $bindid, $bindvalue, $bind_data);

				$fieldOptions = array ();
				$fieldOptions [''] = ''; //Add empty option		
				$selectoptions = array();
				if (isset($this->datasources [$datasourcename]))
					$selectoptions = $this->datasources [$datasourcename];

				if (is_array($selectoptions))
					foreach ( $selectoptions as $selectoption ) {
						if ((gettype ( $selectoption ) == 'array'))
							$id = isset($selectoption [$bindid]) ? $selectoption [$bindid] : null;	
						else
							$id = isset($selectoption->$bindid) ? $selectoption->$bindid : null;
						if ((gettype ( $selectoption ) == 'array'))
							$name = isset($selectoption [$bindid]) ? $selectoption [$bindvalue] : null;	
						else
							$name = isset($selectoption->$bindid) ? $selectoption->$bindvalue : null;
						$fieldOptions [$id] = $name;
					}				
				//Update field with options
				$field['options'] = $fieldOptions;
				$this->fields[$index] = $field;
			}
		}
		

		foreach ( $bind_data as $key => $value ) {
			// If this is a static bind, then expand the value from the datasource
			
			//Check to see if we have to load the datasource again because 

			$static_bind = isset ( $this->static_bind [( string ) $key] ) ? $this->static_bind [( string ) $key] : '';
			//kuink_mydebug($key,$value.'::'.$static_bind);
			//kuink_mydebugobj('Key',$key);
			//kuink_mydebugobj('Value',$value);
			
			if ($static_bind != '') {
				//kuink_mydebug( $static_bind );
				$source = explode ( '|', $static_bind );
				$datasourcename = ( string ) $source [0];
				$bindid = isset ( $source [1] ) ? ( string ) $source [1] : 'id';
				$bindvalue = isset ( $source [2] ) ? ( string ) $source [2] : 'name';
				//kuink_mydebug('datasource',$datasourcename.'.'.$bindid.'.'.$bindvalue.' - '.$value);
				$this->loadDataSource ( $datasourcename, $bindid, $bindvalue);
				//var_dump($bind_data);
				if (isset ( $this->datasources [$datasourcename] )) {
					$datasource = $this->datasources [$datasourcename];
					//kuink_mydebug('datasource', $datasource);
					// print_object( $datasource );
					$datasource_value = isset ( $datasource [$value] ) ? ( array ) $datasource [$value] : array ();
					// if (empty($datasource_value))
					$new_value = $this->datasourceFindValue ( $datasource, $bindid, $bindvalue, $value ); // print_object($datasource_value);
					// else
					// $new_value = (string)$datasource_value[$bindvalue];
					// neon_mydebug( $key, $value.'::'.htmlentities(utf8_decode($new_value)) );
					//kuink_mydebug($key.'.'.$value.'.', $new_value);
					if ($new_value != '')
						$bind_data [$key] = $new_value;
					else
						$bind_data [$key] = $value;
					// $bindable_value = $datasource[];
					// neon_mydebug('Binding...'.$key, $bind_data[$key]);
				}
			} elseif (isset ( $this->static_fields [( string ) $key] )) {
				// print_object($this->static_fields);
				// $bind_data[$key] = htmlentities(utf8_decode($value));
				$bind_data [$key] = $value;
			} else {
				// neon_mydebug($key,$value.'::'.$static_bind);
				$bind_data [$key] = $value;
			}
			//Save the value before applying the formatters
			//$bind_data_original[$key] = $value;
			
			// Check if there is a formatter
			// print_object( $this->fieldFormatter );
			$formatter_data = isset($this->fieldFormatter [( string ) $key]) ? $this->fieldFormatter [( string ) $key] : null;
			if ($formatter_data) {
				foreach ( $formatter_data as $f_name => $f_attributes ) {
					$attributes = null;
					foreach ( $f_attributes as $akey => $avalue ) {
						$attributes [( string ) $akey] = ( string ) $avalue;
					}
					
					$new_value = ( string ) $this->callFormatter ( $f_name, $value, $attributes, $bind_data );
					//kuink_mydebugobj('Key',$key);
					//kuink_mydebugobj('value',$value);
					//kuink_mydebugobj('Value',$new_value);

					// neon_mydebug($key, $new_value);
					// $bind_data[$key] = htmlentities(utf8_decode($new_value));
					$bind_data [$key] = $new_value;
				}
			}
		}
		
		// print_object($bind_data);
		$this->dataBound = array_merge ( $this->dataBound, $bind_data );
		$this->dataBoundWithoutFormatter = array_merge($this->dataBoundWithoutFormatter,$bind_data_original);				

	}
	
	/**
	 * Update the value of the fields in the fields variable
	 */
	function updateValues() {
		foreach ( $this->fields as $key => $value ) {
			$this->fields [$key] ['value'] = isset ( $this->dataBound [$key] ) ? $this->dataBound [$key] : '';
			$this->fields[ $key ]['original'] = isset( $this->dataBoundWithoutFormatter[ $key ] ) ? $this->dataBoundWithoutFormatter[ $key ] : '';
		}
		// var_dump( $this->fields );
	}
	public function getData() {
		$resultData = array ();
		
		foreach ( $this->bind_data as $data ) {
			$resultData = array_merge ( $resultData, $data );
		}
		return $resultData;
	}
	
	/**
	 * Displays the form control
	 * 
	 * @see Kuink\UI\Control.Control::display()
	 */
	function display() {
		$this->build ();
		$this->bindData ();
		$this->updateValues ();
		$this->calculateRows();	
		
		$kuinkUser = new \Kuink\Core\User ();
		$user = $kuinkUser->getUser ();
		
		$listFormFields = implode ( ',', $this->listFormFields );
		
		$params ['sHelp'] = Core\Language::getString ( 'help' );
		$params ['sClose'] = Core\Language::getString ( 'close' );
		$params ['sRequiredString'] = Core\Language::getString ( 'requiredString' );
		$params ['sRequiredFields'] = Core\Language::getString ( 'requiredFields', 'framework', array (
				Core\Language::getString ( 'requiredString' ) 
		) );
		
		$params ['form'] = $this->form;
		$params ['tabs'] = $this->tabs;
		$params ['buttonsPosition'] = $this->buttonsPosition;
		$params ['hasTabs'] = (count($this->tabs) > 1); //If there is only one tab, don't show the headers
		$params ['tabsPosition'] = $this->getProperty ( $this->name, FormProperty::TABS, false, FormPropertyDefaults::TABS );
		$params ['fields'] = $this->fields;
		$params ['rules'] = $this->rules;
		$params ['jsonDynamicRules'] = $this->rulesToJSON ();
		$params ['freeze'] = $this->freeze;
		$params ['buttonActions'] = $this->buttonActions;
		$params ['columns'] = $this->columns;
		$params ['listFormFields'] = $listFormFields;
		$params ['_languages'] = isset ( $this->datasources ['_lang'] ) ? $this->datasources ['_lang'] : null;
		
		$dateTimeLib = new \DateTimeLib ( $this->nodeconfiguration, null );
		$personTimeZoneOffset = $dateTimeLib->getTzOffset ( array (
				0 => $user ['timezone'] 
		) );
		$params ['personTimeZoneOffset'] = $personTimeZoneOffset;
		$params ['personTimeZone'] = $user ['timezone'];
		$params ['validate'] = isset($this->validate) ? $this->validate : null;
		
		// var_dump( $this->tabs );
		// var_dump( $this->columns );
		
		$this->render ( $params );
		
		return;
	}
	
	/**
	 * Get the control HTML for pdf purposes
	 * 
	 * @see Kuink\UI\Control.Control::getHtml()
	 */
	function getHtml() {
		$this->build ();
		$this->bindData ();
		$this->updateValues ();
		
		$elements = $this->fields;
		$in_header = 0;
		$num_headers = 0;
		$html = '';
		
		$title = $this->getProperty ( $this->name, FormProperty::TITLE, false, FormPropertyDefaults::TITLE );
		$langTitle = Core\Language::getString ( $title, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$html = ($langTitle != '') ? '<h3>' . $langTitle . '</h3>' : '';
		foreach ( $elements as $element ) {
			// $field = array( 'type' => $type, 'attributes'=>$attributes, 'options' => $options, 'skeleton'=>$skeleton, 'skin'=>$skin, 'value'=>$value );
			$type = ( string ) $element ['type'];
			$label = ( string ) $element ['attributes'] ['label'];
			$value = ( string ) $element ['value'];
			
			// kuink_mydebug( $type, $label.'::'.$value );
			
			if ($type == FieldType::HEADER)
				$html .= '<h4>' . $label . '</h4>';
			else
				// $html .= "<p><span style='width: 140px;'>{$label}</span> :: {$value}</p>";
				$html .= "<table class='full-border'><tr class='full-border'><td class='full-border'>{$label}</td><td class='full-border'>{$value}</td></tr></table>";
		}
		return $html;
	}
	
	// Will take a rule condition and build a javascript compatible expression
	private function conditionToJavascript($expr, $formName) {
		$expr2 = $expr;
		
		while ( preg_match ( '/[\$][a-zA-Z0-9_]+([->]*+[a-zA-Z0-9_]+)/', $expr2, $matches ) ) {
			$varName = substr ( $matches [0], 1, strlen ( $matches [0] ) );
			$varValue = '$(\'#' . $formName . '\').find(\'#' . $varName . '\').val()';
			$expr = str_replace ( $matches [0], $varValue, $expr );
			$expr2 = str_replace ( $matches [0], '', $expr2 );
		}
		return $expr;
	}
	private function getDataSourceUrl($datasource, $datasourceParams) {
		global $KUINK_CFG;
		$url = $KUINK_CFG->apiUrl . '&neonfunction=' . $datasource . '&' . $datasourceParams;
		return $url;
	}
	private function getDataSourceParams($datasourceParams) {
		$expr2 = $datasourceParams;
		$params = array ();
		while ( preg_match ( '/[\$][a-zA-Z0-9_]+([->]*+[a-zA-Z0-9_]+)/', $expr2, $matches ) ) {
			$varName = substr ( $matches [0], 1, strlen ( $matches [0] ) );
			$params [$varName] = $varName;
			$expr2 = str_replace ( $matches [0], '', $expr2 );
		}
		return $params;
	}
	private function rulesToJSON() {
		// var_dump($this->rulesClient);
		$rules = array ();
		foreach ( $this->rulesClient as $clientRule ) {
			$dataSource = isset($clientRule ['datasource']) ? $clientRule ['datasource'] : '';
			$dataSourceParams = isset($clientRule ['datasourceparams']) ? $clientRule ['datasourceparams'] : '';
			$bindId = isset($clientRule ['bindid']) ? $clientRule ['bindid'] : '';
			$bindValue = isset($clientRule ['bindvalue']) ? $clientRule ['bindvalue'] : '';
			$ruleCondition = isset($clientRule ['condition']) ? $clientRule ['condition'] : '';
			$ruleValueTrue = isset($clientRule ['valueTrue']) ? $clientRule ['valueTrue'] : '';
			$ruleValueFalse = isset($clientRule ['valueFalse']) ? $clientRule ['valueFalse'] : '';
			
			$rules [] = '{ "field": "' . $clientRule ['field'] . '", "condition": "' . $ruleCondition . '", "attr": "' . $clientRule ['attr'] . '", "value_true":"' . $ruleValueTrue . '", "value_false": "' . $ruleValueFalse . '", "datasource": "' . $dataSource  . '", "bindid": "' . $bindId . '", "bindvalue": "' . $bindValue . '", "datasourceparams":' . json_encode ( $dataSourceParams ) . '}';
		}
		$jsonRules = '[' . implode ( ',', $rules ) . ']';
		return $jsonRules;
	}

		//Calculate whether one field should create a row or not
		private function calculateRows(){
			$fields = array();
			//Making a copy ot the fields array to get an integer index. This will make the row calculation more simple
			$inline = false;
			foreach ($this->fields as $field) {
				$fields[] = $field;
			}
	
			//Count how many inline fields there are for each fields 
			$lastNumberOfInlineFields = 1;
			for ($i=0; $i<count($fields); $i++) {
				$numberOfInlineFields = 1;
				$j = $i+1;
				//print(' | '.(string)($fields[$i]['attributes']['id']));
				if ($lastNumberOfInlineFields == 1 && isset($fields[$j]))
					while (isset($fields[$j]) && ($fields[$j]['attributes']['inline'] != 'false') && ($j < count($fields))) {
						//print('*');
						$j++; 
						$numberOfInlineFields++;
					}
				if ($numberOfInlineFields > 1)
					$lastNumberOfInlineFields = $numberOfInlineFields;
				$fieldId = $fields[$i]['attributes']['id'];
				$nextFieldId = isset($fields[$i+1]) ? $fields[$i+1]['attributes']['id'] : null;
				$this->fields[$fieldId]['attributes']['_rowStart'] = (int)($this->fields[$fieldId]['attributes']['inline'] == 'false');
				$this->fields[$fieldId]['attributes']['_rowEnd'] = (int) (isset($this->fields[$nextFieldId]) && ($this->fields[$nextFieldId]['attributes']['inline'] == 'false') || ($i == count($fields)-1));
				$this->fields[$fieldId]['attributes']['_rowLength'] = ($i == count($fields)) ? 1 : $lastNumberOfInlineFields;
				if (isset($this->fields[$nextFieldId]) && ($this->fields[$nextFieldId]['attributes']['inline'] == 'false') || ($i == count($fields)))
					$lastNumberOfInlineFields = 1;
			}
			//print_object($this->fields);
		}
	
}
?>
