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

use Kuink\Core as Core;

/**
 * Default values for properties
 * 
 * @author ptavares
 *        
 */
class GridDefaults {
	const FREEZE = 'true';
	const VIEW = GridViewType::GRID;
	const PAGEABLE = 'false';
	const EXPORTABLE = 'true';
	const COLLAPSIBLE = 'false';
	const PAGING_ACTION = '';
	const VISIBLE = 'true';
	const TITLE = '';
	const SUBTITLE = '';
	const LEGEND = '';
	const PAGE_SIZE = 10;
	const ACTION_SEPARATOR = ' | ';
	const TRANSPOSE = 'false';
	const INFER = 'false';
	const PIVOT = 'false';
	const PIVOT_LINES = '';
	const PIVOT_COLS = '';
	const PIVOT_DATA = '';
	const PIVOT_SEPARATOR = '|';
	const PIVOT_SORT = '';	
	const TREE = 'false';
	const TREE_ID = '';
	const TREE_PARENT_ID = '';
	const TREE_COLLAPSED = 'true';
	const EXTEND_EDIT = 'false';
	const REFRESHABLE = 'false';
	const REFRESH_ACTION = '';
	const REFRESH_INTERVAL = '5000';
	const SHOW_COLUMNS = 'true';
}

/**
 * Properties of the GRID
 * 
 * @author ptavares
 *        
 */
class GridProperty {
	const FREEZE = 'freeze';
	const VIEW = 'view';
	const PAGE_SIZE = 'pagesize';
	const PAGEABLE = 'pageable';
	const COLLAPSIBLE = 'collapsible';
	const EXPORTABLE = 'exportable';
	const PAGING_ACTION = 'pagingaction';
	const TITLE = 'title';
	const SUBTITLE = 'subtitle';
	const LEGEND = 'legend';
	const VISIBLE = 'visible';
	const TRANSPOSE = 'transpose';
	const INFER = 'infer';
	const PIVOT = 'pivot';
	const PIVOT_LINES = 'pivotlines';
	const PIVOT_COLS = 'pivotcols';
	const PIVOT_DATA = 'pivotdata';
	const PIVOT_SEPARATOR = 'pivotseparator';
	const PIVOT_SORT = 'pivotsort';	
	const TREE = 'tree';
	const TREE_ID = 'treeid';
	const TREE_PARENT_ID = 'treeparentid';
	const TREE_COLLAPSED = 'treecollapsed';
	const EXTEND_EDIT = 'extendedit';
	const REFRESHABLE = 'refreshable';
	const REFRESH_ACTION = 'refreshaction';
	const REFRESH_INTERVAL = 'refreshinterval';
	const SHOW_COLUMNS = 'showcolumns';
}
class GridViewType {
	const GRID = 'grid';
	const CHART_BAR = 'bar';
	const CHART_COLUMN = 'column';
	const CHART_PIE = 'pie';
	const CHART_V2 = 'chart_v2';
	const CALENDAR = 'calendar';
}
class GridColumnType {
	const NUMBER = 'number';
	const BOOLEAN = 'boolean';
	const CSTATIC = 'static';
	const TEXT = 'text';
	const TEXTAREA = 'textarea';
	const SELECT = 'select';
	const CHECKBOX = 'checkbox';
	const DATE = 'date';
	const TIME = 'time';
	const DATETIME = 'datetime';
}

/**
 * Properties of the column
 * 
 * @author ptavares
 *        
 */
class GridColumnProperty {
	const ID = 'id';
	const NAME = 'name';
	const VISIBLE = 'visible';
	const LABEL = 'label';
	const TYPE = 'type';
	const INPUT_SIZE = 'inputsize';
	const SIZE = 'size';
	const COLS = 'cols';
	const COLSIZE = 'colsize';
	const ROWS = 'rows';
	const FREEZE = 'freeze';
	const DISABLED = 'disabled';
	const HELP = 'help';
	const ACTION = 'action';
	const ACTIONVALUE = 'actionvalue';
	const DATASOURCE = 'datasource';
	const BINDID = 'bindid';
	const BINDVALUE = 'bindvalue';
	const MAXLENGTH = 'maxlength';
	const REQUIRED = 'required';
	const SKIN = 'skin';
	const SKELETON = 'skeleton';
	const ICON = 'icon';
	const HIDDEN = 'hidden';
	const COLLAPSED = 'collapsed';
	const POST = 'post';
	const INLINE = 'inline';
	const NOWRAP = 'nowrap';
	const CONDITIONALFIELD = 'conditionalfield';
	const CONDITIONALVALUE = 'conditionalvalue';
	const CONDITION = 'condition';
	const SORTABLE = 'sortable';
	const DEFAULTSORT = 'defaultsort';
	const HIGHLIGHTSTYLE = 'highlightstyle';
	const HIGHLIGHTVALUE = 'highlightvalue';
	const HORIZONTALALIGN = 'horizontalalign';
	const VERTICALALIGN = 'verticalalign';
	const HEADERALIGN = 'headeralign';
}

/**
 * Defaults for column properties
 * 
 * @author ptavares
 *        
 */
class GridColumnDefaults {
	const NAME = '';
	const ID = '';
	const VISIBLE = 'true';
	const LABEL = '';
	const TYPE = GridColumnType::CSTATIC;
	const INPUT_SIZE = 'medium';
	const COLS = 20;
	const COLSIZE = 0;
	const ROWS = 4;
	const SIZE = 'medium';
	const FREEZE = 'false';
	const DISABLED = 'false';
	const HELP = 'false';
	const ACTION = '';
	const ACTIONVALUE = '';
	const DATASOURCE = '';
	const BINDID = '';
	const BINDVALUE = '';
	const MAXLENGTH = '';
	const REQUIRED = 'false';
	const SKIN = '';
	const SKELETON = '';
	const HIDDEN = 'false';
	const COLLAPSED = 'false';
	const INLINE = 'false';
	const NOWRAP = 'false';
	const ICON = '';
	const POST = 'false';
	const CONDITIONALFIELD = '';
	const CONDITIONALVALUE = '';
	const CONDITION = '';
	const SORTABLE = 'false';
	const DEFAULTSORT = '';
	const HIGHLIGHTSTYLE = '';
	const HIGHLIGHTVALUE = '1';
	const HORIZONTALALIGN = 'left';
	const VERTICALALIGN = 'top';
	const HEADERALIGN = 'left';
}
class GridContextVariables {
	const PAGE = 'page';
	const PAGE_SIZE = 'pagesize';
	const PAGE_SORT = 'sort';
	const PAGE_HIDDEN = 'hidden';
}
class Grid extends Control {
	var $baseurl;
	var $databind; // Data to bind to the table after formatters
	var $total;
	var $tableobj;
	var $currentpage;
	var $pageable;
	var $exportable;
	var $visible;
	var $collapsible;
	var $pagesize;
	var $page; // Current page
	var $sort; // Stores the sorting of this grid 1_asc 2_desc where 1 and 2 are the column numbers
	var $hidden; // Stores the hidden columns of this grid, can be changed in rendered grids
	var $tablecolumns;
	var $tablecolnotvisible; //Collumns not visible	
	var $tablesubcolumns;
	var $tableinfercolumns; // list of columns infered
	var $tableheaders;
	var $tablesubheaders;
	var $tablecolattributes;
	var $tablecolformatter;
	var $tablecolrules; // Rules applied to table column
	var $tablecoltype;
	var $tablecolinline; // Columns inline
	var $tableConfirmActions; // All actions that nedd confirmation are set here for modal creation
	var $hasactions;
	var $action_separator; // Use a separator for actions? (true|false)
	var $actions_horizontalalign;
	var $actions_verticalalign;
	var $actions;
	var $nodeconfiguration;
	var $pagingaction;
	var $title;
	var $subtitle;
	var $global_actions;
	var $is_form;
	var $built;
	var $export;
	var $_reccords;
	var $view_params; // params received in <View><Param /><View> --> Param must have name attribute | STI: Joao Patricio
	var $transpose; // Transposing this grid
	var $infer; // If set to true the grid columns will be built from the bind dataset before binding
	var $pivot;
	var $pivotlines;
	var $pivotcols;
	var $pivotdata;
	var $pivotseparator;
	var $pivotsort; //Sort the pivot data	
	var $dynamicColumns; // Fields dynamically added to a container
	var $dynamicRules; // Field rules dynamically added to a field
	var $dynamicFormatters; // Field rules dynamically added to a field
	var $requiredColumns; // List of columns required
	var $static_bind; // columns that have a datasource and the value must be lookup
	var $tree; // tree view or not
	var $treeid; // tree id
	var $treeparentid; // tree parent id
	var $extendEdit; // Extended edit for copy paste values
	var $refreshable; // This grid is refreshable?
	var $refreshAction; // Refresh action
	var $refreshInterval; // Refresh interval in miliseconds
	var $showColumns; //Indicates if the template will show the table header
	function __construct($nodeconfiguration, $xml_definition) {
		global $SESSION;
		$this->built = false;
		// kuink_mydebug('construct...','');
		parent::__construct ( $nodeconfiguration, $xml_definition );
		
		$this->dynamicColumns = array();
		$this->tablecolumns = array();
		$this->tablecolnotvisible = array();
		$this->pagesize = $this->getPageSize (null);
		
		$this->export = isset ( $_GET ['export'] ) ? true : false;
		$this->tableinfercolumns = array();
		
		// Getting the page to display
		// $currentStoredPage = \Kuink\Core\ProcessOrchestrator::getProcessVariable('__grid_'.$this->name, 'page');
		$currentStoredPage = $this->getContextVariable ( GridContextVariables::PAGE );
		// set current page
		if (isset ( $_GET [$this->name . '_page'] ))
			$this->page = $_GET [$this->name . '_page'];
		//else if (! empty ( $_POST ))		// Code commented to support the page keeping, if an action takes place:
		//	$this->page = 0;				// UNKNOWN IMPACT, to evaluate later...
		else
			$this->page = isset ( $currentStoredPage ) ? $currentStoredPage : 0;
		
		// Handle dynamic sorting of the table
		$this->sort = $this->getContextVariable ( GridContextVariables::PAGE_SORT );
		if (isset ( $_GET [$this->name . '_' . GridContextVariables::PAGE_SORT] )) {
			
			$currentSort = $_GET [$this->name . '_' . GridContextVariables::PAGE_SORT];
			
			$sortSplit = explode ( '_', $currentSort );
			if (count ( $sortSplit ) != 2)
				throw new \Exception ( 'Invalid grid sort value: ' . $currentSort );
			
			if ($sortSplit [1] != 'rem')
				$this->sort [$sortSplit [0]] = array (
						'column' => $sortSplit [0],
						'sort' => $sortSplit [1] 
				);
			else
				unset ( $this->sort [$sortSplit [0]] );
				
				// (string)$this->getContextVariable(GridContextVariables::PAGE_SORT);
			
			$this->setContextVariable ( GridContextVariables::PAGE_SORT, $this->sort );
			// var_dump($this->sort);
		}
		
		// Handle dynamic collapsed columns
		$this->hidden = $this->getContextVariable ( GridContextVariables::PAGE_HIDDEN );
		if (isset ( $_GET [$this->name . '_' . GridContextVariables::PAGE_HIDDEN] )) {
			$hidden = $_GET [$this->name . '_' . GridContextVariables::PAGE_HIDDEN];
			$this->hidden = explode ( ';', $hidden );
			$this->setContextVariable ( GridContextVariables::PAGE_HIDDEN, $this->hidden );
			// print_object($this->hidden);
		}
		
		// Getting the pagesize to display
		$currentStoredPageSize = $this->getContextVariable ( GridContextVariables::PAGE_SIZE );
		$defaultPageSize = $this->getProperty ( $this->name, GridProperty::PAGE_SIZE, false, GridDefaults::PAGE_SIZE );
		
		$pageable = $this->getProperty ( $this->name, GridProperty::PAGEABLE, false, GridDefaults::PAGEABLE );
		if ($this->export || $pageable != 'true')
			$pagesize = 0;
		else {
			if (isset ( $_GET [$this->name . '_pagesize'] ))
				$pagesize = $_GET [$this->name . '_pagesize'];
			else
				$pagesize = (isset($currentStoredPageSize) && ($currentStoredPageSize != '') ) ? $currentStoredPageSize : $defaultPageSize;
		}
		// \Kuink\Core\ProcessOrchestrator::setProcessVariable('__grid_'.$this->name, 'pagesize', $pagesize);
		if ($pageable == 'true') {
			// persist context variables
			$this->setContextVariable ( GridContextVariables::PAGE, $this->page );
			$this->setContextVariable ( GridContextVariables::PAGE_SIZE, $pagesize );
		}

		$this->pagesize = $pagesize;
	}
	function getPageSize($params) {
		return $this->pagesize;
	}
	function getCurrentPage($params) {
		return $this->page;
	}
	
	/**
	 * Get the query order by
	 * 
	 * @param unknown $params        	
	 */
	function getSort($params) {
		// Get the sort column names
		$tablexml = $this->xml_definition;
		$columns = $tablexml->xpath ( './Template/Columns/Column' );
		$count = 0;
		
		$useDefaultSort = (! isset ( $this->sort ));
		// var_dump($columns);
		foreach ( $columns as $column ) {
			$colName = ( string ) $column [GridColumnProperty::NAME];
			$sortable = ( string ) $column [GridColumnProperty::SORTABLE];
			$defaultSort = ( string ) $column [GridColumnProperty::DEFAULTSORT];
			if (($useDefaultSort) && ($defaultSort != '')) {
				// If the sort is empty set this default sort
				$this->sort [$count] = array (
						'column' => $count,
						'sort' => $defaultSort 
				);
				$this->setContextVariable ( GridContextVariables::PAGE_SORT, $this->sort );
			}
			// var_dump($sortable.$count);
			if (isset ( $this->sort [$count] )) {
				if ($sortable == 'true')
					$this->sort [$count] ['field'] = $colName;
				else
					$this->sort [$count] ['field'] = $sortable;
			}
			
			$count ++;
		}
		
		// var_dump($this->sort);
		$orderQueryArray = array ();
		if (isset($this->sort) && is_array($this->sort))
		foreach ( $this->sort as $sort ) {
			if (($sort ['sort'] != 'asc') && ($sort ['sort'] != 'desc'))
				throw new \Exception ( 'Invalid sort expression on grid ' . $sort ['sort'] );
			$orderQueryArray [] = $sort ['field'] . ' ' . $sort ['sort'];
		}
		
		return (count ( $orderQueryArray ) > 0) ? implode ( ',', $orderQueryArray ) : '';
	}
	
	/**
	 * Dynamically adding a field to a grid
	 * 
	 * @param unknown_type $field_properties        	
	 */
	function addColumn($columnProperties) {
		//kuink_mydebugObj($columnProperties);
		$this->dynamicColumns [] = $columnProperties;
		
		return;
	}
	
	/**
	 * Dynamically adding a rule to a grid
	 * 
	 * @param unknown_type $rule_properties        	
	 */
	function addRule($ruleProperties) {
		// var_dump($ruleProperties);
		$column = ( string ) $ruleProperties ['column'];
		$this->dynamicRules [$column] [] = $ruleProperties;
		return;
	}
	
	/**
	 * Dynamically adding a formatter to a grid
	 * 
	 * @param unknown_type $formatter_properties        	
	 */
	function addFormatter($formatterProperties) {
		$column = ( string ) $formatterProperties ['column'];
		$this->dynamicFormatters [$column] [] = $formatterProperties;
		return;
	}
	function getColumnAttributes($column, $name) {
		$grid_freezed = $this->getProperty ( $this->name, GridProperty::FREEZE, false, GridDefaults::FREEZE, null, true );
		
		$attributes = array ();
		
		// var_dump( $this->properties );
		$id = $this->getProperty ( $name, GridColumnProperty::ID, false, $name, $column );

		$attributes [GridColumnProperty::NAME] = $name;
		$attributes [GridColumnProperty::ID] = $id;
		
		$labelId = $this->getProperty ( $name, GridColumnProperty::LABEL, false, $name, $column );
		$label = Core\Language::getString ( $labelId, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [GridColumnProperty::LABEL] = $label;
		
		$showHelp = $this->getProperty ( $name, GridColumnProperty::HELP, false, GridColumnDefaults::HELP, $column );
		$help = '';
		if ($showHelp != 'false')
			$help = ($showHelp == 'true' || $showHelp == '') ? \Kuink\Core\Language::getHelpString ( $labelId, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] ) : \Kuink\Core\Language::getHelpString ( $showHelp, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
			//$help = ($showHelp == 'true' || $showHelp == '') ? \Kuink\Core\Language::getHelpString ( $id, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] ) : \Kuink\Core\Language::getHelpString ( $showHelp, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$attributes [GridColumnProperty::HELP] = $help;
		
		$attributes [GridColumnProperty::TYPE] = ($grid_freezed == 'true') ? GridColumnType::CSTATIC : $this->getProperty ( $name, GridColumnProperty::TYPE, false, GridColumnDefaults::TYPE, $column );
		$attributes [GridColumnProperty::REQUIRED] = $this->getProperty ( $name, GridColumnProperty::REQUIRED, false, GridColumnDefaults::REQUIRED, $column );
		$attributes [GridColumnProperty::DISABLED] = $this->getProperty ( $name, GridColumnProperty::DISABLED, false, GridColumnDefaults::DISABLED, $column );
		$attributes [GridColumnProperty::MAXLENGTH] = $this->getProperty ( $name, GridColumnProperty::MAXLENGTH, false, GridColumnDefaults::MAXLENGTH, $column );
		$attributes [GridColumnProperty::VISIBLE] = $this->getProperty ( $name, GridColumnProperty::VISIBLE, false, GridColumnDefaults::VISIBLE, $column );
		$attributes [GridColumnProperty::FREEZE] = $this->getProperty ( $name, GridColumnProperty::FREEZE, false, GridColumnDefaults::FREEZE, $column );
		$attributes [GridColumnProperty::INPUT_SIZE] = $this->getProperty ( $name, GridColumnProperty::INPUT_SIZE, false, GridColumnDefaults::INPUT_SIZE, $column );
		$attributes [GridColumnProperty::SIZE] = $this->getProperty ( $name, GridColumnProperty::SIZE, false, GridColumnDefaults::SIZE, $column );
		$attributes [GridColumnProperty::ICON] = $this->getProperty ( $name, GridColumnProperty::ICON, false, GridColumnDefaults::ICON, $column );
		$attributes [GridColumnProperty::COLS] = $this->getProperty ( $name, GridColumnProperty::COLS, false, GridColumnDefaults::COLS, $column );
		$attributes [GridColumnProperty::COLSIZE] = $this->getProperty ( $name, GridColumnProperty::COLSIZE, false, GridColumnDefaults::COLSIZE, $column );
		$attributes [GridColumnProperty::ROWS] = $this->getProperty ( $name, GridColumnProperty::ROWS, false, GridColumnDefaults::ROWS, $column );
		$attributes [GridColumnProperty::DATASOURCE] = $this->getProperty ( $name, GridColumnProperty::DATASOURCE, false, GridColumnDefaults::DATASOURCE, $column );
		$attributes [GridColumnProperty::BINDID] = $this->getProperty ( $name, GridColumnProperty::BINDID, false, GridColumnDefaults::BINDID, $column );
		$attributes [GridColumnProperty::BINDVALUE] = $this->getProperty ( $name, GridColumnProperty::BINDVALUE, false, GridColumnDefaults::BINDVALUE, $column );
		$attributes [GridColumnProperty::SKELETON] = $this->getProperty ( $name, GridColumnProperty::SKELETON, false, GridColumnDefaults::SKELETON, $column );
		
		$attributes [GridColumnProperty::HIDDEN] = $this->getProperty ( $name, GridColumnProperty::HIDDEN, false, GridColumnDefaults::HIDDEN, $column );
		// Hidden needs a special treatment once user can set manually the visibility of a column
		
		// If this field is hidden in xml then we must check if the user has chosen to see it.
		// We can see that if the user has allready choose fields by $this->hidden !== NULL
		if ($attributes [GridColumnProperty::HIDDEN] == 'true')
			if (($this->hidden !== NULL) && (! (in_array ( $name, $this->hidden ))))
				$attributes [GridColumnProperty::HIDDEN] = 'false';
		if (is_array($this->hidden) && in_array ( $name, $this->hidden ))
			$attributes [GridColumnProperty::HIDDEN] = 'true';
		
		$attributes [GridColumnProperty::COLLAPSED] = $this->getProperty ( $name, GridColumnProperty::COLLAPSED, false, GridColumnDefaults::COLLAPSED, $column );
		$attributes [GridColumnProperty::POST] = $this->getProperty ( $name, GridColumnProperty::POST, false, GridColumnDefaults::POST, $column );
		$attributes [GridColumnProperty::INLINE] = $this->getProperty ( $name, GridColumnProperty::INLINE, false, GridColumnDefaults::INLINE, $column );
		$attributes [GridColumnProperty::NOWRAP] = $this->getProperty ( $name, GridColumnProperty::NOWRAP, false, GridColumnDefaults::NOWRAP, $column );
		$attributes [GridColumnProperty::SORTABLE] = $this->getProperty ( $name, GridColumnProperty::SORTABLE, false, GridColumnDefaults::SORTABLE, $column );
		$attributes [GridColumnProperty::DEFAULTSORT] = $this->getProperty ( $name, GridColumnProperty::DEFAULTSORT, false, GridColumnDefaults::DEFAULTSORT, $column );
		
		$attributes [GridColumnProperty::CONDITIONALFIELD] = $this->getProperty ( $name, GridColumnProperty::CONDITIONALFIELD, false, GridColumnDefaults::CONDITIONALFIELD, $column );
		$attributes [GridColumnProperty::CONDITIONALVALUE] = $this->getProperty ( $name, GridColumnProperty::CONDITIONALVALUE, false, GridColumnDefaults::CONDITIONALVALUE, $column );
		$attributes [GridColumnProperty::CONDITION] = $this->getProperty ( $name, GridColumnProperty::CONDITION, false, GridColumnDefaults::CONDITION, $column );
		
		$attributes [GridColumnProperty::ACTION] = $this->getProperty ( $name, GridColumnProperty::ACTION, false, GridColumnDefaults::ACTION, $column );
		$attributes [GridColumnProperty::ACTIONVALUE] = $this->getProperty ( $name, GridColumnProperty::ACTIONVALUE, false, GridColumnDefaults::ACTIONVALUE, $column );

		$attributes [GridColumnProperty::HIGHLIGHTSTYLE] = $this->getProperty ( $name, GridColumnProperty::HIGHLIGHTSTYLE, false, GridColumnDefaults::HIGHLIGHTSTYLE, $column );
		$attributes [GridColumnProperty::HIGHLIGHTVALUE] = $this->getProperty ( $name, GridColumnProperty::HIGHLIGHTVALUE, false, GridColumnDefaults::HIGHLIGHTVALUE, $column );

		$attributes [GridColumnProperty::HORIZONTALALIGN] = $this->getProperty ( $name, GridColumnProperty::HORIZONTALALIGN, false, GridColumnDefaults::HORIZONTALALIGN, $column );
		$attributes [GridColumnProperty::VERTICALALIGN] = $this->getProperty ( $name, GridColumnProperty::VERTICALALIGN, false, GridColumnDefaults::VERTICALALIGN, $column );
		$attributes [GridColumnProperty::HEADERALIGN] = $this->getProperty ( $name, GridColumnProperty::HEADERALIGN, false, GridColumnDefaults::HEADERALIGN, $column );
		
		if ($attributes [GridColumnProperty::TYPE] == GridColumnType::SELECT || $attributes [GridColumnProperty::TYPE] == GridColumnType::CSTATIC)
			$attributes ['options'] = $this->getColumnOptions ( $column, $attributes );
		
		return $attributes;
	}
	
	/**
	 * Gets the options of a field
	 * 
	 * @param unknown_type $formfield        	
	 * @param unknown_type $attributes        	
	 */
	function getColumnOptions($formfield, $attributes) {
		$fieldOptions = array ();
		$fieldOptions [''] = '';
		
		$datasourcename = ( string ) $attributes [GridColumnProperty::DATASOURCE];
		$bindid = ( string ) $attributes [GridColumnProperty::BINDID];
		$bindvalue = ( string ) $attributes [GridColumnProperty::BINDVALUE];
		
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
			$this->loadDataSource ( $datasourcename, $bindid, $bindvalue );
			
			if ($this->datasources [$datasourcename] != null) {
				$selectoptions = $this->datasources [$datasourcename];
				foreach ( $selectoptions as $selectoption ) {
					$id = (gettype ( $selectoption ) == 'array') ? $selectoption [$bindid] : $selectoption->$bindid;
					$name = (gettype ( $selectoption ) == 'array') ? $selectoption [$bindvalue] : $selectoption->$bindvalue;
					$fieldOptions [$id] = $name;
				}
			}
		}
		return $fieldOptions;
	}
	
	/**
	 * Builds an array with the view parameters
	 * 
	 * @param
	 *        	$viewParams
	 * @return multitype:string NULL
	 */
	function buildViewParams($viewParams) {
		$outParams = array ();
		$params = $viewParams->Param;
		foreach ( $viewParams as $param ) {
			$attrs = $param->attributes ();
			$paramName = ( string ) $attrs ['name'];
			$paramValue = $param [0];
			// TODO: remove this width ignore hardcoded
			if ($paramName == 'width')
				continue;
			if (! isset ( $param->Param ))
				$outParams [$paramName] = ( string ) $param;
			else {
				$outParams [$paramName] = $this->buildViewParams ( $param );
			}
		}
		return $outParams;
	}
	
	/**
	 * Build the dynamic fields in the Grid
	 */
	function buildDynamicFields() {
		// print_object($this->dynamicColumns);
		// iterate through dynamic fields
		foreach ( $this->dynamicColumns as $dynamicColumn ) {
			$name = $dynamicColumn ['name'];
			$options = isset ( $dynamicColumn ['options'] ) ? $dynamicColumn ['options'] : null;
			// Put all dynamic field properties in the property array
			foreach ( $dynamicColumn as $key => $value )
				parent::setProperty ( array (
						$name,
						$key,
						$value 
				) );
				
				// If there are options, then create a datasource and set it
			if (! is_null ( $options )) {
				$datasource = $dyn . '.' . $id;
				parent::setProperty ( array (
						$name,
						'datasource',
						$datasource 
				) );
				parent::setProperty ( array (
						$name,
						'bindid',
						'id' 
				) );
				parent::setProperty ( array (
						$name,
						'bindvalue',
						'value' 
				) );
				$this->datasources [$datasource] = $options;
			}
		}
	}
	
	/**
	 * Builds the grid based on all attributes
	 */
	private function build() {
		global $SESSION;

		if ($this->built)
			return;
		
		//kuink_mydebug('Building...',$this->name);
		$this->buildDynamicFields (); // Add all the dynamic fields in the properties array
		
		$form = isset ( $_GET ['form'] ) ? '&form=' . $_GET ['form'] : '';
		
		$this->baseurl = ( string ) $this->nodeconfiguration [Core\NodeConfKey::BASEURL] . $form;
		
		$this->visible = ( string ) $this->getProperty ( $this->name, GridProperty::VISIBLE, false, GridDefaults::VISIBLE, null, true );
		$this->tree = ( string ) $this->getProperty ( $this->name, GridProperty::TREE, false, GridDefaults::TREE, null, true  );
		$this->treeid = ( string ) $this->getProperty ( $this->name, GridProperty::TREE_ID, false, GridDefaults::TREE_ID );
		$this->treeparentid = ( string ) $this->getProperty ( $this->name, GridProperty::TREE_PARENT_ID, false, GridDefaults::TREE_PARENT_ID );
		$this->exportable = ( string ) $this->getProperty ( $this->name, GridProperty::EXPORTABLE, false, GridDefaults::EXPORTABLE, null, true);
		
		$this->pageable = ( string ) $this->getProperty ( $this->name, GridProperty::PAGEABLE, false, GridDefaults::PAGEABLE );
		$this->collapsible = ( string ) $this->getProperty ( $this->name, GridProperty::COLLAPSIBLE, false, GridDefaults::COLLAPSIBLE, null, true  );
		// $this->pagesize = (string) $this->getProperty($this->name, GridProperty::PAGE_SIZE, false, GridDefaults::PAGE_SIZE);
		$this->pagingaction = ( string ) $this->getProperty ( $this->name, GridProperty::PAGING_ACTION, false, GridDefaults::PAGING_ACTION );
		$this->title = ( string ) $this->getProperty ( $this->name, GridProperty::TITLE, false, GridDefaults::TITLE );
		$this->subtitle = ( string ) $this->getProperty ( $this->name, GridProperty::SUBTITLE, false, GridDefaults::SUBTITLE );
		$this->is_form = false;
		$this->transpose = ( string ) $this->getProperty ( $this->name, GridProperty::TRANSPOSE, false, GridDefaults::TRANSPOSE );
		$this->infer = ( string ) $this->getProperty ( $this->name, GridProperty::INFER, false, GridDefaults::INFER, null, true  );
		
		$this->extendEdit = ( string ) $this->getProperty ( $this->name, GridProperty::EXTEND_EDIT, false, GridDefaults::EXTEND_EDIT );
		
		$this->refreshable = ( string ) $this->getProperty ( $this->name, GridProperty::REFRESHABLE, false, GridDefaults::REFRESHABLE );
		$this->refreshAction = ( string ) $this->getProperty ( $this->name, GridProperty::REFRESH_ACTION, false, GridDefaults::REFRESH_ACTION );
		$this->refreshInterval = ( string ) $this->getProperty ( $this->name, GridProperty::REFRESH_INTERVAL, false, GridDefaults::REFRESH_INTERVAL );

		$this->showColumns = ( string ) $this->getProperty ( $this->name, GridProperty::SHOW_COLUMNS, false, GridDefaults::SHOW_COLUMNS );
		
		$this->pivot = ( string ) $this->getProperty ( $this->name, GridProperty::PIVOT, false, GridDefaults::PIVOT );
		$this->pivotlines = ( string ) $this->getProperty ( $this->name, GridProperty::PIVOT_LINES, false, GridDefaults::PIVOT_LINES );
		$this->pivotcols = ( string ) $this->getProperty ( $this->name, GridProperty::PIVOT_COLS, false, GridDefaults::PIVOT_COLS );
		$this->pivotdata = ( string ) $this->getProperty ( $this->name, GridProperty::PIVOT_DATA, false, GridDefaults::PIVOT_DATA );
		$this->pivotseparator = ( string ) $this->getProperty ( $this->name, GridProperty::PIVOT_SEPARATOR, false, GridDefaults::PIVOT_SEPARATOR );
		$this->pivotsort = (string) $this->getProperty($this->name, GridProperty::PIVOT_SORT, false, GridDefaults::PIVOT_SORT);		
		if ($this->pivot == 'true')
			$this->infer = 'true';
			// Hack to rteplace form in base url
		$freeze = $this->getProperty ( $this->name, GridProperty::FREEZE, false, GridDefaults::FREEZE, null, true );
		if ($freeze != "true") {
			$getForm = isset($_GET ['form']) ? $_GET ['form'] : null;
			$this->baseurl = str_replace ( 'form=' . $getForm, 'form=' . $this->name, $this->baseurl );
		}
		// var_dump($this->collapsible);
		// kuink_mydebug('$this->pagingaction', (string)$this->pagingaction);
		// kuink_mydebug('$this->pagesize', $this->pagesize);
		if ($this->pagesize == 0)
			$this->pageable = 'false';
		
		if (($this->pageable == 'true') && ! $this->pagingaction)
			throw new \Exception ( 'GridManager: if the grid is pageable the attribute pagingaction must be defined! Grid:' . $tablename );
			
			// kuink_mydebug('LOAD', $this->name);
			// Get all columns
		$tablexml = $this->xml_definition;
		
		// var_dump($tablexml);
		$global_actions = $tablexml->xpath ( './Actions//Action' );
		
		/*
		 * Joao Patricio. this code adds view params to attribute $this->view_params as an array
		 * where $this->view_params = array("paramName"=>"paramValue") mathes <View><Param name="paramName">paramValue</Param><View>
		 * This applies to all params included under <View> element
		 */
		$view = $tablexml->xpath ( './View' );
		$this->view_params = isset($view[0]) ? $this->buildViewParams ( $view [0] ) : array();
		
		foreach ( $global_actions as $global_action ) {
			$this->is_form = true;
			$action_name = ( string ) $global_action ['name'];
			$this->global_actions [$action_name] ['name'] = $action_name;
			$this->global_actions [$action_name] ['label'] = isset ( $global_action ['label'] ) ? ( string ) $global_action ['label'] : ( string ) $global_action ['name'];
			$this->global_actions [$action_name] ['label'] = Core\Language::getString ( $this->global_actions [$action_name] ['label'], $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
			$this->global_actions [$action_name] ['color'] = ( string ) $global_action ['color'];
			$this->global_actions [$action_name] ['type'] = isset ( $global_action ['type'] ) ? ( string ) $global_action ['type'] : '';
			$this->global_actions [$action_name] ['decoration'] = isset ( $global_action ['decoration'] ) ? ( string ) $global_action ['decoration'] : '';
			$this->global_actions [$action_name] ['icon'] = isset ( $global_action ['icon'] ) ? ( string ) $global_action ['icon'] : '';
			$confirmMessage = '';
			switch ($global_action ['confirm']) {
				case 'true': $confirmMessage = \Kuink\Core\Language::getString ( 'ask_proceed', 'framework' ); break;
				case 'false': $confirmMessage = ''; break;
				default: $confirmMessage = Core\Language::getString ( $global_action ['confirm'], $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] ); break;
			}
			$this->global_actions [$action_name] ['confirm'] = $confirmMessage;
		}
		
		$actions = $tablexml->xpath ( './Template/Actions//Action' );
		$hasactions = (count ( $actions ) > 0);
		
		$actions_tag = $tablexml->xpath ( './Template/Actions' );
		$this->action_separator = isset ( $actions_tag [0] ['separator'] ) ? (( string ) $actions_tag [0] ['separator'] == 'true') : 'false';
		$this->actions_horizontalalign = isset ( $actions_tag [0] ['horizontalalign'] ) ? (( string ) $actions_tag [0] ['horizontalalign']) : 'left';
		$this->actions_verticalalign = isset ( $actions_tag [0] ['verticalalign'] ) ? (( string ) $actions_tag [0] ['verticalalign']) : 'top';
		
		$this->hasactions = $hasactions;
		$this->actions = $actions;
		
		// var_dump( $actions );
		$this->tablesubheaders = array ();
		$columns = $tablexml->xpath ( './Template/Columns/Column' );
		// var_dump( $columns );
		$table_columns = array ();
		$table_headers = array ();
		
		$table_colformatter = array ();
		$table_colrules = array ();
		$table_colinline = array ();
		$index = 0;
		$newColumns = array (); // Columns with dynamically added

		$table_collookup = array();
		$table_colattributes = array();		
		// Expand dynamic added columns
		foreach ( $columns as $column ) {
			$colname = ( string ) $this->getProperty ( '', GridColumnProperty::NAME, false, GridColumnDefaults::NAME, $column );			
			$colType = ( string ) $this->getProperty ( $colname, GridColumnProperty::TYPE, false, GridColumnDefaults::TYPE, $column );
			if ($colType == 'container') {
				// Add all the columns in this container
				foreach ( $this->properties as $dynColumn ) {
					$currentContainer = isset($dynColumn ['container']) ? ( string ) $dynColumn ['container'] : null;
					if ($currentContainer == $colname) {
						// Create a new element with the dynamic fields
						//kuink_mydebug('Dynamic:', $colname);
						//kuink_mydebug('Dynamic:', $currentContainer);
						$attrs = '';
						$rules = '';
						$formatters = '';
						foreach ( $dynColumn as $key => $value )
							$attrs .= ' ' . ( string ) $key . '="' . ( string ) $value . '"';
						$dynRules = $this->dynamicRules [$dynColumn ['name']];
						if (is_array($dynRules))
							foreach ( $dynRules as $dynRule )
								$rules .= '<Rule attr="' . ( string ) $dynRule ['attr'] . '" condition="' . ( string ) $dynRule ['condition'] . '"  value="' . ( string ) $dynRule ['value'] . '"/>';
						$dynFormatters = $this->dynamicFormatters [( string ) $dynColumn ['name']];
						if (is_array($dynFormatters))
							foreach ( $dynFormatters as $dynFormatter )
								$formatters .= '<Formatter name="' . ( string ) $dynFormatter ['name'] . '" method="' . ( string ) $dynFormatter ['method'] . '"/>';
						$newColumn = new \SimpleXMLElement ( '<Column ' . $attrs . '>' . $rules . $formatters . '</Column>' );
						// var_dump($dynFormatters);
						$newColumns [] = $newColumn;
						// var_dump($attrs);
					}
				}
			} else
				$newColumns [] = $column;
		}
		// var_dump($newColumns);
		
		foreach ( $newColumns as $column ) {
			$colname = ( string ) $this->getProperty ( '', GridColumnProperty::NAME, false, GridColumnDefaults::NAME, $column );
			
			$col_attributes = $this->getColumnAttributes ( $column, $colname );
			
			$visible = ( string ) $this->getProperty ( $colname, GridColumnProperty::VISIBLE, false, GridColumnDefaults::VISIBLE, $column );
			$coltype = ( string ) $this->getProperty ( $colname, GridColumnProperty::TYPE, false, GridColumnDefaults::TYPE, $column );
			
			$colRequired = ( string ) $this->getProperty ( $colname, GridColumnProperty::REQUIRED, false, GridColumnDefaults::REQUIRED, $column );
			if ($colRequired == 'true')
				$this->requiredColumns [$colname] = $colname;
			
			if ($coltype == 'container') {
				// Adding all the dynamic columns in this container
			}
			
			$table_collookup [$index] = ($col_attributes [GridColumnProperty::DATASOURCE] != GridColumnDefaults::DATASOURCE) ? 1 : 0;
			
			$this->tablecoltype [$index] = $coltype;
			
			if ($visible != 'true') {
				$this->tablecolnotvisible[] = $colname;
					continue;
			}
			
			$formatter = '';
			$method = '';
			
			// Check Formatters
			$attributes = null;
			$colformatter_collection = array ();
			$colRulesCollection = array ();
			if (count ( $column->children () ) > 0) {
				
				$formatters = $column->children ();
				foreach ( $formatters as $colformatter ) {
					// $colformatter = $formatters[0];
					$block = $colformatter->getname ();
					
					if ($block == 'Rule') {
						$ruleAttribute = ( string ) $colformatter ['attr'];
						$ruleAttrCondition = ( string ) $colformatter ['condition'];
						$ruleAttrCapability = ( string ) $colformatter ['capability'];
						$ruleAttrValue = ( string ) $colformatter ['value'];
						$ruleCondition = array (
								'condition' => $ruleAttrCondition,
								'capability' => $ruleAttrCapability,
								'value' => $ruleAttrValue 
						);
						
						$colRulesCollection [$ruleAttribute] = $ruleCondition;
						// var_dump($colRulesCollection);
					} else { // it is a formatter
						$formatter = ( string ) $colformatter ['name'];
						$method = ( string ) $colformatter ['method'];
						
						// if Old fashion formatter. Handle it!
						if ($block != 'Formatter')
							$formatter = str_replace ( 'Formatter', '', $block );
							
							// kuink_mydebug( $block, $formatter.'::'.$method );
						
						foreach ( $colformatter->attributes () as $key => $value )
							$attributes [( string ) $key] = ( string ) $value;
						foreach ( $colformatter->children () as $param )
							$attributes [( string ) $param ['name']] = ( string ) $param [0];
						$colformatter_collection [] = array (
								'formatter' => $formatter,
								'attributes' => $attributes 
						);
					}
				}
			}
			$colformatter = array ();
			
			// print($colname);
			$label = isset ( $column ['label'] ) ? ( string ) $column ['label'] : $colname;
			$collabel = Core\Language::getString ( $label, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
			
			// var_dump( $attributes );
			// kuink_mydebug($colname, $collabel);
			$table_columns [$index] = $colname;
			$table_headers [$index] = ($colRequired == 'true') ? $collabel . '<font style="color:red">&nbsp;*</font>' : $collabel;
			
			$table_colformatter [$index] = $colformatter_collection;
			$table_colrules [$index] = $colRulesCollection;
			
			$table_colattributes [$index] = $col_attributes;
			
			if (isset ( $columns [$index + 1] ))
				if ($columns [$index + 1] [GridColumnProperty::INLINE] == 'true')
					$table_colinline [$colname] = ( string ) $columns [$index + 1] [GridColumnProperty::NAME];
			
			$index ++;
		}
		
		if ($hasactions) {
			$table_columns [$index] = 'actions';
			$table_colattributes[$index]['horizontalalign'] = $this->actions_horizontalalign;
			$table_colattributes[$index]['verticalalign'] = $this->actions_verticalalign;
			//$table_columns [$index] ['horizontalalign'] = 'right';
			$table_headers [$index] = Core\Language::getString ( 'actions', $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		}
		// var_dump($table_headers);
		$this->tablecolumns = $table_columns;
		$this->tablecolformatter = $table_colformatter;
		$this->tablecolrules = $table_colrules;
		$this->tableheaders = $table_headers;
		$this->tablecolattributes = $table_colattributes;
		$this->static_bind = $table_collookup;
		$this->tablecolinline = $table_colinline;
		// print_object( $this->tablecolumns );
		// bind the data
		$this->bindData ();
		
		$this->built = true;
	}
	private function bindData() {
		// $url = new \moodle_url($this->baseurl);
		$baseurl = \Kuink\Core\Tools::setUrlParams ( $this->baseurl );
		
		if ($this->pivot == 'true') {
			$pivotLines = explode ( ',', $this->pivotlines );
			$pivotCols = explode ( ',', $this->pivotcols );
			$pivotData = explode ( ',', $this->pivotdata );
			$pivotSort = explode(',', $this->pivotsort);
			$count = 0;
			$utilsLib = new \UtilsLib ();
			$setLib = new \SetLib();			
			foreach ( $this->bind_data as $data ) {
				$unsortedData = $utilsLib->pivotTable(array((array) $data, $this->pivotlines, $this->pivotcols, $this->pivotdata));
				//zprint_object($unsortedData);
				if (trim($this->pivotsort != '')) {
					$mergedData = array_merge(array($unsortedData), $pivotSort);
					$sortedData = $setLib->SortBy($mergedData);
				} else 
					$sortedData = $unsortedData;
					
				$this->bind_data[$count++] = $sortedData;
			}
		}
		// var_dump($this->bind_data[0]);
		// Check if this GRID is to infer or not
		if ($this->infer == 'true') {
			foreach ($this->bind_data as $data) {
				if (is_array($data)) {
					$record = (array) reset($data);
					foreach ($record as $key => $value) {
							if (!in_array($key, $this->tablecolumns) && !in_array($key, $this->tablecolnotvisible)) { // && (strpos($key, '__infer_') > 0)
									$this->tableinfercolumns[] = $key;
									$this->tablecolumns[] = $key;
									$this->tableheaders[] = Core\Language::getString($key, $this->nodeconfiguration[Core\NodeConfKey::APPLICATION]);
							}
					}
				}
		}
			/*
			// Build the grid columns
			// var_dump($this->tablecolumns);
			if (is_array($this->bind_data) || is_object($this->bind_data)) {
				foreach ( $this->bind_data as $data ) {
					$record = ( array ) reset ( $data );
					foreach ( $record as $key => $value ) {
						if (is_array($this->tablecolumns) && !in_array($key, $this->tablecolumns) && is_array($this->tablecolnotvisible) && !in_array($key, $this->tablecolnotvisible)) { // && (strpos($key, '__infer_') > 0)					
							$this->tableinfercolumns [] = $key;
							$this->tablecolumns [] = $key;
							$this->tableheaders [] = $key;
						}
					}
				}
			}
			var_dump($this->tablecolumns);*/
		}
		$this->total = 0;
		if (is_array($this->bind_data) || is_object($this->bind_data))
		foreach ( $this->bind_data as $data ) {
			$data = ( array ) $data;
			// var_dump($data);
			if (isset ( $data ['total'] )) {
				$total = $data ['total'];
				$this->total = intval ( $total );
			} else {
				$this->total = $this->total + count ( $data );
			}
			
			// var_dump( $this->tablecolumns );
			$records = (isset ( $data ['records'] )) ? $data ['records'] : $data;
			$originalValuesArray = array ();																	 
			$new_data = array ();
			foreach ( $records as $record ) {
				$record = ( array ) $record;
				//var_dump($record);
				$inline = false;
				$datatoinsert = array ();
				$current_key = null;
				$index = 0; // column index
				
				foreach ( $this->tablecolumns as $column ) {
					$inline = in_array ( $column, $this->tablecolinline );
					$value = isset ( $record [$column] ) ? $record [$column] : '';
					$valuesArray = array ();
					$originalValuesArray = array ();
					if (is_array ( $value )) {
						// Sub Columns
						if (count ( $this->tablesubheaders ) == 0)
							foreach ( $value as $key => $sub ) {
								$subColIndex = array_search ( $key, $this->tablecolumns );
								$this->tablesubheaders [] = $this->tableheaders [$subColIndex];
							}
						$valuesArray = $value;
						$originalValuesArray = $value;
						$value = array_shift ( $valuesArray );
					} else
						$value = ( string ) $value;
					
					if (! $inline) {
						$current_key = ( string ) $column;
						$datatoinsert [$current_key] ['value'] = $value;
						$datatoinsert [$current_key] ['nonformatedvalue'] = $value;
					}
					// Handling rules
					$colRules = isset($this->tablecolrules [$index]) ? $this->tablecolrules [$index] : array();
					// initialize with the default column attributes that can be changed by the rules
					$colAttributes = isset($this->tablecolattributes [$index]) ? $this->tablecolattributes [$index] : array();
					foreach ( $colRules as $attrName => $ruleCondition ) {
						$condExpr = $ruleCondition ['condition'];
						$condCapability = $ruleCondition ['capability'];
						$condAttrValue = $ruleCondition ['value'];
						//$recordValue = ( string ) $record [$condField];
						$capabilityValue = isset ( $this->nodeconfiguration [\Kuink\Core\NodeConfKey::CAPABILITIES] [$condCapability] ) ? 1 : 0;
						
						// Parse the conditionExpr
						$eval = new \Kuink\Core\EvalExpr ();
						try {
							$evalResult = $eval->e ( $condExpr, $record, TRUE );
						} catch ( \Exception $e ) {
							var_dump ( 'Exception: evaluating ' . $condExpr );
							die ();
						}
						// kuink_mydebug($condExpr, $evalResult);
						// kuink_mydebug($attrName, $ruleCondition['field'].'/'.$ruleCondition['fieldvalue'].'/'.$ruleCondition['capability'].'/'.$ruleCondition['attrvalue'].'/'.$recordValue.'»'.$capabilityValue);
						if ($condExpr != '' && $evalResult == 1) {
							if ($attrName == 'value') {
								// $datatoinsert[$current_key]['value'] = $condAttrValue;
								$value = $record [$condAttrValue];
							} else
								$colAttributes [$attrName] = $condAttrValue;
						} else if ($condCapability != '' && $capabilityValue == 1) {
							$colAttributes [$attrName] = $condAttrValue;
						}
						// kuink_mydebug($attrName, $attrValue);
					}
					// Add the column attributes to the cell
					$datatoinsert [$current_key] ['attributes'] = $colAttributes;
					// var_dump($colAttributes);
					// Handling Static Binding
					$hasLookUp = false;
					if (isset ( $this->static_bind [$index] ) && $this->static_bind [$index] == 1) {
						// $type = (string)$this->tablecolattributes[$index][GridColumnProperty::TYPE];
						$type = ( string ) $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::TYPE];
						
						if ($type == GridColumnType::CSTATIC) {
							$datasource_name = ( string ) $this->tablecolattributes [$index] [GridColumnProperty::DATASOURCE];
							$bindid = ( string ) $this->tablecolattributes [$index] [GridColumnProperty::BINDID];
							$bindvalue = ( string ) $this->tablecolattributes [$index] [GridColumnProperty::BINDVALUE];
							
							$this->loadDataSource ( $datasource_name, $bindid, $bindvalue );
							
							$datasource = $this->datasources [$datasource_name];
							
							if (! isset ( $datasource ))
								throw new \Exception ( 'DataSource ' . $datasource_name . ' not found in ' . $this->type . ' ' . $this->name );
							$hasLookUp = true;
							if ($inline)
								$datatoinsert [$current_key] ['value'] .= ( string ) $this->datasourceFindValue ( $datasource, $bindid, $bindvalue, $value );
							else
								$datatoinsert [$current_key] ['value'] = ( string ) $this->datasourceFindValue ( $datasource, $bindid, $bindvalue, $value );
						} else if ($inline)
							$datatoinsert [$current_key] ['value'] .= $value;
						else
							$datatoinsert [$current_key] ['value'] = $value;
						// kuink_mydebug($bindvalue, $datatoinsert[$current_key]['value']);
					}
					
					// Handling Formatters
					// handle infered columns formatters
					$tableColFormatter = null;
					if (! in_array ( $column, $this->tableinfercolumns ) && (strpos ( $column, '__infer' ) === false)) {
						$tableColFormatter = isset($this->tablecolformatter [$index]) ? $this->tablecolformatter [$index] : null;
					} else {
						if (count ( $originalValuesArray ) > 0) {
							$arrayKeys = array_keys ( $originalValuesArray );
							$inferIndex = array_search ( $arrayKeys [0], $this->tablecolumns );
						} else
							$inferIndex = array_search ( '__infer', $this->tablecolumns );
						$tableColFormatter = isset($this->tablecolformatter [$inferIndex]) ? $this->tablecolformatter [$inferIndex] : null;
						// die();//kuink_mydebug($column,$inferIndex);
					}
					
					if (isset ( $tableColFormatter ) && count ( $tableColFormatter ) > 0) {
						$formatted_value = ($inline) ? $value : $datatoinsert [$current_key] ['value'];
						foreach ( $tableColFormatter as $formatter ) {
							$formatter_name = ( string ) $formatter ['formatter'];
							$formatter_params = $formatter ['attributes'];
							
							if ($formatter_name != '') {
								if (count ( $originalValuesArray ) > 0) {
									// $formatted_value = $this->callFormatter($formatter_name, $formatted_value, $formatter_params, (array) $originalValuesArray);
									if (! is_array ( $formatted_value )) // Use the formatted value (used in __infer columns)
										$formatted_value = $this->callFormatter ( $formatter_name, $formatted_value, $formatter_params, ( array ) $record [$column] );
									else // Treat the formatted value as an array
										$formatted_value = $this->callFormatter ( $formatter_name, $originalValuesArray, $formatter_params, ( array ) $record [$column] );
								} else
									$formatted_value = $this->callFormatter ( $formatter_name, $formatted_value, $formatter_params, ( array ) $record );
							} else
								$formatted_value = $datatoinsert [$current_key] ['value'];
						}
						if ($inline) {
							
							$datatoinsert [$current_key] ['value'] .= $formatted_value;
						} else
							$datatoinsert [$current_key] ['value'] = $formatted_value;
							$datatoinsert [$current_key] ['nonformatedvalue'] = $value;
					} else if ($inline && ! $hasLookUp) // If it has look up the value is allready concatenated
						$datatoinsert [$current_key] ['value'] = $datatoinsert [$current_key] ['value'] . $value;
						
						// If this is an array value, then concatenate values
						// var_dump($originalValuesArray);
						// var_dump($this->tablecolattributes);
					if (count ( $originalValuesArray ) > 0) {
						// var_dump($valuesArray);
						// remove values whose columns are not visible
						foreach ( $valuesArray as $inferKey => $inferValue ) {
							$colUnvisible = true;
							foreach ( $this->tablecolattributes as $colAttribute )
								if ($colAttribute ['name'] == $inferKey)
									$colUnvisible = false;
							if ($colUnvisible)
								// Remove the
								unset ( $valuesArray [$inferKey] );
							// var_dump('unvisible::'.$colUnvisible.'::'.$inferKey);
						}
						// var_dump($valuesArray);
						$datatoinsert [$current_key] ['value'] = (count ( $valuesArray ) > 0) ? $datatoinsert [$current_key] ['value'] . $this->pivotseparator . implode ( $this->pivotseparator, $valuesArray ) : $datatoinsert [$current_key] ['value'];
					}
					
					if ($this->tree == 'true') {
						$datatoinsert [$current_key] ['attributes'] ['treeid'] = isset($record [$this->treeid]) ? $record [$this->treeid] : null;
						$datatoinsert [$current_key] ['attributes'] ['treeparentid'] = isset($record [$this->treeparentid]) ? $record [$this->treeparentid] : null;
					}
					
					// If this is an action column then create the action link
					// $colActionName = isset($this->tablecolattributes[$index][GridColumnProperty::ACTION]) ? (string) $this->tablecolattributes[$index][GridColumnProperty::ACTION] : '';
					$colActionName = isset ( $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::ACTION] ) ? ( string ) $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::ACTION] : '';
					// $colActionValue = isset($this->tablecolattributes[$index][GridColumnProperty::ACTIONVALUE]) ? (string) $this->tablecolattributes[$index][GridColumnProperty::ACTIONVALUE] : '';
					$colActionValue = isset ( $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::ACTIONVALUE] ) ? ( string ) $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::ACTIONVALUE] : '';
					$colActionValueBind = isset ( $record [$colActionValue] ) ? $record [$colActionValue] : '';
					
					/* Handle conditions on the action */
					$actionCondField = isset ( $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::CONDITIONALFIELD] ) ? ( string ) $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::CONDITIONALFIELD] : '';
					$actionCondValue = isset ( $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::CONDITIONALVALUE] ) ? ( string ) $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::CONDITIONALVALUE] : '';
					$actionCondition = isset ( $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::CONDITION] ) ? ( string ) $datatoinsert [$current_key] ['attributes'] [GridColumnProperty::CONDITION] : '';
					;
					
					if ($actionCondField != '')
						$actionCondition = '$' . $actionCondField . ' == ' . $actionCondValue;
					
					$conditionTrue = 0;
					if ($actionCondition != '') {
						$eval = new \Kuink\Core\EvalExpr ();
						$conditionTrue = $eval->e ( $actionCondition, $record, TRUE ); // Eval and return a value without ''
					} else {
						$conditionTrue = TRUE;
					}
					
					$action_permissions = $this->nodeconfiguration [Core\NodeConfKey::ACTION_PERMISSIONS];
					if ($colActionName != '' && $colActionValue != '')
						if (! empty ( $action_permissions [$colActionName] ) && $conditionTrue) {
							// The user has permission
							$colActionUrl = \Kuink\Core\Tools::setUrlParams ( $baseurl, array (
									'action' => $colActionName,
									'actionvalue' => $colActionValueBind 
							) );
							//kuink_mydebug($colActionName, $colActionUrl);
							$colActionLabel = $datatoinsert [$current_key] ['value'];
							$colActionFormatted = '<a href="' . $colActionUrl . '">' . $colActionLabel . '</a>&nbsp;';
							$colAction_constructor = array ();
							$colAction_constructor ['url'] = $colActionUrl;
							$colAction_constructor ['label'] = $colActionLabel;
							$datatoinsert [$current_key] ['value'] = $colActionFormatted;
							$datatoinsert [$current_key] ['colAction_constructor'] = $colAction_constructor;
						}
					$index ++;
				}
				
				// var_dump( $datatoinsert );
				
				if ($this->hasactions) {
					$actions = '';
					$actions_constructor = array ();
					$action_separator = GridDefaults::ACTION_SEPARATOR;
					$count = 0;
					foreach ( $this->actions as $action ) {
						// $url = new \moodle_url($this->baseurl);
						// $baseurl = $url->out(false);
						$actionType = isset ( $action ['type'] ) ? ( string ) $action ['type'] : '';
						$actionIcon = isset ( $action ['icon'] ) ? ( string ) $action ['icon'] : '';
						$actionname = ( string ) $action ['name'];
						$actionlabel_print = isset ( $action ['label'] ) ? ( string ) $action ['label'] : ( string ) $action ['name'];
						$actionDecoration = isset ( $action ['decoration'] ) ? ( string ) $action ['decoration'] : '';
						$action_permissions = $this->nodeconfiguration [Core\NodeConfKey::ACTION_PERMISSIONS];

						// Allow actions to have type like buttons
						$actionTypeData = $this->getActionTypeData ( $actionType, $actionDecoration, $actionIcon);
						if (! empty ( $action_permissions [$actionname] )) {
							// print($has_permission);
							$actionvaluebind = ( string ) $action ['actionvalue'];
							$actionvalue = '';
							
							// Conditional actions. Get the fieldname and value to check if the action is to be shown
							$action_condfield = ( string ) $action ['conditionalfield'];
							$action_condvalue = ( string ) $action ['conditionalvalue'];
							$action_condition = ( string ) $action ['condition'];
							$cond_fieldvalue = '';
							
							foreach ( ( array ) $record as $key => $value ) {
								if ($key == $actionvaluebind)
									$actionvalue = $value;
								
								if ($key == $action_condfield)
									$cond_fieldvalue = $value;
							}
							$conditionTrue = 0;
							if ($action_condition) {
								$eval = new \Kuink\Core\EvalExpr ();
								$conditionTrue = $eval->e ( $action_condition, $record, TRUE ); // Eval and return a value without ''
								$cond_fieldvalue = $action_condition; // Hack to prevent ''=='' and show the action anyway
							}
							
							// var_dump($action);
							// $location = neon_set_url_params($baseurl, array('action'=>$actionname, 'actionvalue'=>$actionvalue));
							$location = \Kuink\Core\Tools::setUrlParams ( $baseurl, array (
									'action' => $actionname,
									'actionvalue' => $actionvalue 
							) );
							// print($location);
							// Check if the action is to be displayed due to conditional values
							$label = Core\Language::getString ( $actionlabel_print, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
							// $icon = ($actionIcon == '') ? '' : '<span class="badge badge-info"><i class="icon-'.$actionIcon.' icon-white"></i></span>';
							$iconName = ($actionIcon != '') ? $actionIcon : ( string ) $actionTypeData ['icon'];
							// print($iconName.' ' );
							
							$iconStyle = '';
							if (($actionTypeData ['icon-only']) && ($iconName != '')) {
								$iconStyle = 'color:#000;';
							}
							
							$icon = ($iconName == '') ? '' : '<i style="' . $iconStyle . '" class="fa fa-' . $iconName . ' ' . $actionTypeData ['icon-color'] . '"></i>';
							// var_dump($icon.'::'.$label);
							if (($actionTypeData ['icon-only']) && ($iconName != '')) {
								$actionlabel = $icon; // Just the icon with no label
							} else
								$actionlabel = ($iconName == '') ? $label : $icon . ' ' . $label;
								
								// $tooltip = ($actionIcon == '') ? '' : 'rel="tooltip" data-placement="top" data-original-title="'.$label.'"';
							$tooltip = ($iconName == '') ? '' : 'rel="tooltip" data-placement="top" title="' . $label . '"';
							// $tooltipdiv = ($actionIcon == '') ? '' :'<div class="tooltip fade top in" style="top: 5px; left: 221.5px; display: block; "><div class="tooltip-arrow"></div><div class="tooltip-inner">'.$label.'</div></div>';
							$tooltipdiv = '';
							
							if ($cond_fieldvalue == $action_condvalue || $conditionTrue == 1) {
								$action_url = '';
								$action_constructor = array ();
								$confirm_label = (( ( string ) $action ['confirm']) === '') ? 'false' : ( string ) $action ['confirm'];
								$newContext = (( ( string ) $action ['newcontext']) === '') ? 'false' : ( string ) $action ['newcontext'];
								$target = '';
								if ($newContext == 'true') {
									// Remove de idContext from the url then set target _blank
									$currentIdContext = \Kuink\Core\ProcessOrchestrator::getContextId ();
									// $newIdContext = \Kuink\Core\ProcessOrchestrator::copyContext(true);
									$location = str_replace ( $currentIdContext, '', $location );
									$location = $location . '&previousidcontext=' . $currentIdContext;
									$target = '_blank';
								}
								$confirm_text = '';
								if ($confirm_label != 'false') {
									
									$confirm_text = ($confirm_label == 'true') ? \Kuink\Core\Language::getString ( 'ask_proceed', 'framework' ) : \Kuink\Core\Language::getString ( $confirm_label, $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] );
									$confirm_label = 'true';
									$this->tableConfirmActions [] = array (
											'actionName' => $actionname,
											'actionValue' => $actionvalue,
											'href' => $location,
											'confirmText' => $confirm_text,
											'target' => $target 
									);
									
									$action_url = '<a href="#' . $actionname . $actionvalue . '" ' . $tooltip . ' role="button" class="' . $actionTypeData ['class'] . '" data-toggle="modal" target="' . $target . '"><span nowrap="true" style="white-space: nowrap; overflow-x: auto;">' . $actionlabel . '</span></a>';
								} else
									$action_url = '<a href="' . $location . '" ' . $tooltip . ' target="' . $target . '" class="' . $actionTypeData ['class'] . '"><span nowrap="true" style="white-space: nowrap; overflow-x: auto;">' . $actionlabel . '</span></a>&nbsp;';
								
								$action_url = $action_url . $tooltipdiv;
								
								// get action elements to construct in tpl
								$action_constructor ['name'] = $actionname;
								$action_constructor ['value'] = $actionvalue;
								$action_constructor ['location'] = $location;
								$action_constructor ['tooltip'] = $tooltip;
								$action_constructor ['class'] = $actionTypeData ['class'];
								$action_constructor ['target'] = $target;
								$action_constructor ['label'] = $actionlabel;
								$action_constructor ['confirm'] = $confirm_label;
								$action_constructor ['confirm_message'] = $confirm_text;
								
								if ($count > 0 && $this->action_separator === true) {
									$inSeparator = (trim ( $actions ) != '') ? $action_separator : '';
									$actions = $actions . $inSeparator . $action_url;
									$action_constructor ['separator'] = $inSeparator;
									$actions_constructor [] = $action_constructor;
								} else {
									$actions = $actions . $action_url;
									$actions_constructor [] = $action_constructor;
								}
								// print($icon.'::'.$label.'::'.$actions.'<br/>');
							}
						}
						$count ++;
						// $actions = $actions.' '.$actionname;
					}
					// print ($actions.'<br/>');
					
					// not erasing this to keep compatibility with older theme default
					$datatoinsert ['actions'] ['value'] = $actions;
					// new way of constructing actions in tpl
					$datatoinsert ['actions'] ['constructor'] = $actions_constructor;
					// var_dump( $datatoinsert );
				}
				
				// $this->tableobj->setup();
				// var_dump( $datatoinsert );
				unset ( $datatoinsert ['__infer'] ); // remove __infer data that is allways empty
				if (is_array($originalValuesArray))
					foreach ( $originalValuesArray as $origKey => $origValue )
						unset ( $datatoinsert [$origKey] );
				$new_data [] = $datatoinsert;
				// $this->tableobj->add_data( $datatoinsert );
			}
			
			$this->databind [] = $new_data;
		}
		
		// Remove inline and infered columns
		foreach ( $this->tablecolumns as $key => $column ) {
			if (array_search ( $column, $this->tablecolinline )) {
				unset ( $this->tablecolumns [$key] );
				unset ( $this->tableheaders [$key] );
			}
			if (strpos ( $column, '__infer' ) !== false) {
				unset ( $this->tablecolumns [$key] );
				unset ( $this->tableheaders [$key] );
			}
		}
	}
	private function getActionTypeData($actionType, $actionDecoration, $actionIcon) {
		$actionTypeData = array ();

		switch ($actionType) {
			case 'download' :
				$icon = ($actionIcon == '') ? 'cloud-download' : $actionIcon;
				$decoration = ($actionDecoration == '') ? 'success' : $actionDecoration;
				$actionTypeData = array (
						'icon' => $icon,
						'icon-color' => 'icon-white',
						'icon-only' => false,
						'class' => 'btn btn-'.$decoration 
				);
				break;
			case 'execute' :
				$icon = ($actionIcon == '') ? 'play' : $actionIcon;
				$decoration = ($actionDecoration == '') ? 'primary' : $actionDecoration;
				$actionTypeData = array (
					'icon' => $icon,
					'icon-color' => 'icon-white',
					'icon-only' => false,
					'class' => 'btn btn-'.$decoration 
			);
			break;
			case 'submit' :
				$icon = $actionIcon;
				$decoration = ($actionDecoration == '') ? 'primary' : $actionDecoration;
				$actionTypeData = array (
					'icon' => $icon,
					'icon-color' => 'icon-white',
					'icon-only' => false,
					'class' => 'btn btn-'.$decoration 
			);
			break;
			default :
				$actionTypeData = array (
						'icon' => $actionIcon,
						'icon-color' => 'icon-black',
						'icon-only' => true,
						'class' => '' 
				);
				break;
		}

		return $actionTypeData;
	}
	/**
	 * Replaces $field with the corresponding value in the $data variable
	 * 
	 * @param unknown_type $data
	 *        	- dataset with row data
	 * @param unknown_type $params
	 *        	- params to the formatter
	 */
	/*
	 * function expandFormatterParams($data, &$params)
	 * {
	 * foreach ($params as $key=>$value) {
	 * if ($value[0] == '$')
	 * {
	 * //It's a field name
	 * $field = substr($value, 1, strlen($value)-1);
	 *
	 * //if the variable is not set in v$variables check in session variables
	 * $new_value = (isset($data[$field])) ? (string)$data[$field] : '';
	 * //kuink_mydebug($field, $new_value);
	 * $params[$key] = $new_value;
	 * }
	 * }
	 * //var_dump($params);
	 * return $params;
	 * }
	 */
	function getHtml() {
        $this->build();

        $visible = (string) $this->getProperty($this->name, GridProperty::VISIBLE, false, GridDefaults::VISIBLE, null, true);

        if ($visible != 'true')
            return;

        $cols = '';
        $colLength = count($this->tableheaders);
        $currentCol = 0;
        foreach ($this->tableheaders as $col) {
            if ($currentCol < $colLength) {
                $cols .= '<th><strong>' . htmlentities($col, ENT_QUOTES, 'UTF-8') . '</strong></th>';
                $currentCol++;
            }
        }
        $rows = '';
        foreach ($this->databind as $bind_data)
            foreach ($bind_data as $row) {
                $rows .= '<tr >';
                foreach ((array) $row as $row_col)
                    $rows .= '<td style="font-size: 10pt; white-space: nowrap;">' . $row_col['value'] . '</td>';
                $rows .= '</tr>';
            }

        $html = '
		&nbsp;
		<table style="table-layout: fixed; font-size: 9pt" cellpadding="2" border="1"  >
				<thead>
				<tr>
				' . $cols . '
				</tr>
				</thead>

				' . $rows . '

		</table>
		&nbsp;
		';
		
		return $html;
	}
	function display() {
		$this->visible = ( string ) $this->getProperty ( $this->name, GridProperty::VISIBLE, false, GridDefaults::VISIBLE, null, true );
		
		if ($this->visible != 'true')
			return;
		
		$this->build ();
		
		$view = ( string ) $this->getProperty ( $this->name, GridProperty::VIEW, false, GridDefaults::VIEW );
		
		if ($view == GridViewType::GRID) {
			$this->displayGrid ();
		} else if ($view == GridViewType::CALENDAR) {
			$this->displayCalendar ( $view );
		} else if ($view == GridViewType::CHART_V2) {
			$this->displayChartV2 ( $view );
		} else {
			$this->displayChart ( $view );
		}
		// Export list
		// if ($this->exportable == 'true') {
		// $this->showExport('csv');
		// }
	}
	private function displayChart($view) {
		switch ($view) {
			case "pie" :
				$chart_type = "PieChart";
				break;
			case "column" :
				$chart_type = "ColumnChart";
				break;
			case "bar" :
				$chart_type = "BarChart";
				break;
			case "line" :
				$chart_type = "LineChart";
			case "combo" :
				$chart_type = "ComboChart";
				break;
			default :
				$chart_type = "PieChart";
				break;
		}
		
		$guid = new \UtilsLib ( $this->nodeconfiguration, null );
		$guid = $guid->GuidClean ( null );
		
		$width = (isset ( $this->view_params ['width'] )) ? $this->view_params ['width'] : 300; // @TODO STI: Joao Patricio - add 300 to a default value
		$height = (isset ( $this->view_params ['height'] )) ? $this->view_params ['height'] : 300; // @TODO STI: Joao Patricio - add 300 to a default value
		$bgColor = (isset ( $this->view_params ['bgColor'] )) ? $this->view_params ['bgColor'] : '#ffffff'; // @TODO STI: Joao Patricio - add #ffffff to a default value
		
		$headers = array ();
		$index = 0;
		foreach ( $this->tableheaders as $key => $value ) {
			// kuink_mydebug($key,$value);
			$type = ( string ) $this->tablecoltype [$key];
			$type = ($type == 'text') ? 'string' : $type;
			$headers [] = "data.addColumn('" . $type . "', '" . $value . "')";
		}
		$headers = implode ( ';', $headers ) . ';';
		
		// var_dump($this->databind);
		$rows = array ();
		foreach ( $this->databind as $data ) {
			foreach ( $data as $dataRow ) {
				$row = array ();
				$index = 0;
				foreach ( $dataRow as $key => $value ) {
					$type = ( string ) $this->tablecoltype [$index ++];
					if ($type == 'string')
						$row [] = "'" . $value ['value'] . "'";
					else
						$row [] = "" . $value ['value'] . "";
				}
				$rows [] = '[' . implode ( ',', $row ) . ']';
			}
		}
		$rows = "data.addRows([" . implode ( ',', $rows ) . "]);";
		
		$viewParamsJson = json_encode ( $this->view_params );
		$viewParamsJson = substr ( $viewParamsJson, 1, strlen ( $viewParamsJson ) - 2 );
		// var_dump( $viewParamsJson );
		
		$params ['name'] = $this->name;
		$params ['headers'] = $headers;
		$params ['confirmActions'] = $this->tableConfirmActions;
		$params ['viewParams'] = $viewParamsJson;
		$params ['rows'] = $rows;
		$params ['guid'] = $guid;
		$params ['type'] = $chart_type;
		$params ['title'] = $this->title;
		$params ['exportable'] = $this->exportable;
		$params ['exportTypes'] = array ('CSV', 'PDF-L', 'PDF-P');
		
		$params ['refreshable'] = $this->refreshable;
		$params ['refreshInterval'] = $this->refreshInterval;
		$params ['refreshUrl'] = $this->baseurl . '&action=' . $this->refreshAction . '&control=' . $this->name . '&modal=Control';
		$params ['showColumns'] = $this->showColumns;
		
		$params ['baseUrl'] = $this->baseurl . '&action=' . $this->pagingaction;
		$params ['action'] = isset ( $_GET ['action'] ) ? ( string ) $_GET ['action'] : 'init';
		$params ['properties'] = array (
				'width' => $width,
				'height' => $height,
				'bgcolor' => $bgColor 
		);
		$this->skeleton = '_chart';
		$this->render ( $params );
		
		return;
	}
	private function displayChartV2($view) {
		$params = array ();
		$params ['name'] = $this->name;
		$params ['title'] = Core\Language::getString ( $this->title, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$params ['subtitle'] = Core\Language::getString ( $this->subtitle, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		
		$params ['refreshable'] = $this->refreshable;
		$params ['refreshInterval'] = $this->refreshInterval;
		$params ['refreshUrl'] = $this->baseurl . '&action=' . $this->refreshAction . '&control=' . $this->name . '&modal=Control';
		
		// View Params
		$yAxisTitle = (isset ( $this->view_params ['yAxisTitle'] )) ? $this->view_params ['yAxisTitle'] : '';
		$params ['yAxisTitle'] = Core\Language::getString ( $yAxisTitle, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		
		$yAxisMin = (isset ( $this->view_params ['yAxisMin'] )) ? $this->view_params ['yAxisMin'] : 'null';
		$params ['yAxisMin'] = $yAxisMin;
		$yAxisMax = (isset ( $this->view_params ['yAxisMax'] )) ? $this->view_params ['yAxisMax'] : 'null';
		$params ['yAxisMax'] = $yAxisMax;
		$yAxisTickInterval = (isset ( $this->view_params ['yAxisTickInterval'] )) ? $this->view_params ['yAxisTickInterval'] : 'null';
		$params ['yAxisTickInterval'] = $yAxisTickInterval;
		$yAxisAllowDecimals = (isset ( $this->view_params ['yAxisAllowDecimals'] )) ? $this->view_params ['yAxisAllowDecimals'] : 'null';
		$params ['yAxisAllowDecimals'] = $yAxisAllowDecimals;
		
		$xAxisTitle = (isset ( $this->view_params ['xAxisTitle'] )) ? $this->view_params ['xAxisTitle'] : '';
		$params ['xAxisTitle'] = Core\Language::getString ( $xAxisTitle, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		
		$type = (isset ( $this->view_params ['type'] )) ? $this->view_params ['type'] : 'column';
		$params ['type'] = $type;
		
		$stacked = (isset ( $this->view_params ['stacked'] )) ? $this->view_params ['stacked'] : 'false';
		$params ['stacked'] = $stacked;
		
		$width = (isset ( $this->view_params ['width'] )) ? $this->view_params ['width'] : '100%';
		
		$height = (isset ( $this->view_params ['height'] )) ? $this->view_params ['height'] : '100%';
		
		$series = array ();
		$data = $this->bind_data;
		$data = isset($data [0]) ? $data [0] : array();
		
		// only one serie?
		$flag_onlyOneSerie = (count ( explode ( ',', $this->pivotdata ) ) == 1) ? true : false;
		
		foreach ( $data as $dataSet ) {
			$serie = array ();
			
			foreach ( $dataSet as $categoryName => $categoryValues ) {
				$empty = true;
				
				if ($flag_onlyOneSerie == false) {
					foreach ( $categoryValues as $serieName => $serieValue ) {
						
						if ((! empty ( $serieValue ) || $serieValue == '0')) {
							$serie ['name'] = $categoryName;
							$serieName = str_replace ( '__infer_', '', $serieName );
							$serieName = Core\Language::getString ( $serieName, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
							$serie ['values'] [$serieName] = $serieValue;
						}
					}
				} else if ((! empty ( $categoryValues ) || $categoryValues == '0')) {
					$flag_onlyOneSerie = true;
					$serie ['name'] = $categoryName;
					$serieName = Core\Language::getString ( $this->pivotdata, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
					$serie ['values'] [$serieName] = $categoryValues;
					$series [] = $serie;
				}
			}
			// var_dump($serie);
			if ($flag_onlyOneSerie == false)
				$series [] = $serie;
		}
		// translate serie names
		
		$serieNames = explode ( ',', $this->pivotdata );
		foreach ( $serieNames as $serieName ) {
			$params ['series'] [] = Core\Language::getString ( $serieName, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		}
		$params ['data'] = $series;
		$this->skeleton = '_chart_v2';
		//print_object($params);
		$uiParams ['jsonData'] = $params;
		$uiParams ['name'] = $this->name;
		$uiParams ['width'] = $width;
		$uiParams ['height'] = $height;
		$this->render ( $uiParams );
		return;
	}
	function array_transpose($array, $selectKey = false) {
		if (! is_array ( $array ))
			return false;
		$return = array ();
		foreach ( $array as $key => $value ) {
			if (! is_array ( $value ))
				return $array;
			if ($selectKey) {
				if (isset ( $value [$selectKey] ['value'] ))
					$return [] = $value [$selectKey] ['value'];
			} else {
				foreach ( $value as $key2 => $value2 ) {
					$return [$key2] [$key] ['value'] = $value2 ['value'];
				}
			}
		}
		return $return;
	}
	private function displayGrid() {
		$kuinkUser = new \Kuink\Core\User ();
		$kuink_user = $kuinkUser->getUser ();
		
		$globalActions = array ();
		$action_permissions = $this->nodeconfiguration ['actionPermissions'];
		if (isset ( $this->global_actions ))
			foreach ( $this->global_actions as $global_action ) {
				if (! empty ( $action_permissions [$global_action ['name']] )) {
					$globalAction = $global_action;
					$globalActions [$global_action ['name']] = $globalAction;
				}
			}
		
		$headers = array ();
		$data = array ();
		if ($this->transpose == 'true') {
			// var_dump($this->tableheaders);
			$tr = $this->array_transpose ( $this->databind [0], 0 );
			$headers = $tr [$this->tablecolumns [0]];
			$transposedHeaders = array ();
			foreach ( $headers as $key => $value ) {
				$transposedHeaders [] = $value ['value'];
			}
			$headers = $transposedHeaders;
			$data [0] [0] = $tr [$this->tablecolumns [1]];
		} else {
			$headers = $this->tableheaders;
			$data = $this->databind;
		}
		// var_dump($this->tablecolattributes);
		$params ['name'] = $this->name;
		$params ['title'] = Core\Language::getString ( $this->title, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		$params ['headers'] = $headers; // $this->tableheaders
		$params ['requiredColumns'] = $this->requiredColumns;
		$params ['confirmActions'] = $this->tableConfirmActions;
		$params ['columnAttributes'] = $this->tablecolattributes;
		$params ['data'] = $data; // $this->databind;
		$params ['baseUrl'] = $this->baseurl . '&action=' . $this->pagingaction;
		$params ['isForm'] = $this->is_form;
		$params ['isPageable'] = $this->pageable;
		$params ['pageSize'] = $this->pagesize;
		$params ['pageCurrent'] = $this->page;
		$params ['pageTotal'] = ($this->pagesize != 0) ? ( int ) ceil($this->total / $this->pagesize) : 1;
		$params ['recordsTotal'] = ( int ) ($this->total);
		$params ['globalActions'] = $globalActions;
		$params ['exportable'] = $this->exportable;
		$params ['extendEdit'] = $this->extendEdit;
		$params ['exportTypes'] = array ('CSV', 'PDF-L', 'PDF-P');
		$params ['sort'] = $this->sort;
		
		$params ['action'] = isset ( $_GET ['action'] ) ? ( string ) $_GET ['action'] : 'init';
		
		$params ['refreshing'] = $this->refreshing;
		$params ['refreshable'] = $this->refreshable;
		$params ['refreshInterval'] = $this->refreshInterval;
		$params ['refreshUrl'] = $this->baseurl . '&action=' . $this->refreshAction . '&control=' . $this->name . '&modal=Control';

		$params ['showColumns'] = $this->showColumns;
		
		$params ['freeze'] = $freeze = $this->getProperty ( $this->name, GridProperty::FREEZE, false, GridDefaults::FREEZE, null, true );
		$this->legend = $this->getProperty ( $this->name, GridProperty::LEGEND, false, GridDefaults::LEGEND );
		$params ['legend'] = Core\Language::getString ( $this->legend, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
		
		$params ['sFirst'] = Core\Language::getString ( 'first' );
		$params ['sLast'] = Core\Language::getString ( 'last' );
		$params ['sPrevious'] = Core\Language::getString ( 'previous' );
		$params ['sNext'] = Core\Language::getString ( 'next' );
		
		$params ['tree'] = $this->getProperty ( $this->name, GridProperty::TREE, false, GridDefaults::TREE );
		$params ['treeid'] = $this->getProperty ( $this->name, GridProperty::TREE_ID, false, GridDefaults::TREE_ID );
		$params ['treeparentid'] = $this->getProperty ( $this->name, GridProperty::TREE_PARENT_ID, false, GridDefaults::TREE_PARENT_ID );
		$params ['treecollapsed'] = $this->getProperty ( $this->name, GridProperty::TREE_COLLAPSED, false, GridDefaults::TREE_COLLAPSED );
		
		$dateTimeLib = new \DateTimeLib ( $this->nodeconfiguration, null );
		$personTimeZoneOffset = $dateTimeLib->getTzOffset ( array (
				0 => $kuink_user ['timezone']
		) );
		$params ['personTimeZoneOffset'] = $personTimeZoneOffset;
		$params ['personTimeZone'] = $kuink_user ['timezone'];
		$this->render ( $params );
		return;
	}

	public function export($type) {
        global $CFG;

        $this->setProperty(array($this->name,'freeze','true'));
        $this->build();


        $utils = new \UtilsLib($this->nodeconfiguration, null);
        $file_guid = $utils->GuidClean(null);

        $config = $this->nodeconfiguration['config'];

        $base_upload = $config['neonUploadFolderBase'];
        $upload_dir = $base_upload . '/tmp/';

        //Handle dupplication of slashes in configurations
        $upload_dir = str_replace('//', '/', $upload_dir);
        $filePath = $CFG->dataroot.'/'.$upload_dir;

        //neon_mydebug('Exporting...', $myFile);

        if ($type == 'CSV') {
            $fileName = $file_guid.'.csv';
            $myFile = $filePath.$fileName;
    
            if (!$handle = fopen($myFile, 'x+')) {
                throw new \Exception("Grid export: Cannot open file ($myFile)");
                return;
            }

            //write headers
            $fixed_headers = array();
            foreach ($this->tableheaders as $header) {
                $fixed_headers[] = '"' . str_replace('"', '""', html_entity_decode(strip_tags($header))) . '"';
            }
            $headers = implode("\t", $fixed_headers);
            $headers .= "\n";
            if (fwrite($handle, $headers) === FALSE) {
                throw new \Exception("Cannot write to file ($myFile)");
                exit;
            }

            //Write data
            foreach ($this->databind as $data) {
                foreach ($data as $row_key => $row_value) {
                    $fixed_data = array();
                    foreach ($row_value as $key => $value) {
                        $fixed_data[] = '"' . str_replace('"', '""', html_entity_decode(strip_tags($value['value']))) . '"';
                    }
                    $write_data = implode("\t", $fixed_data);
                    $write_data .= "\n";
                    if (fwrite($handle, $write_data) === FALSE) {
                        throw new \Exception("Cannot write to file ($myFile)");
                        exit;
                    }
                }
            }
			fclose($handle);
			$header = 'Content-Type: text/csv;charset=utf-8';
        } else if (($type=='PDF-L') || ($type=='PDF-P')) {
            $orientation = 'portrait';
            if ($type=='PDF-L') {
                $orientation = 'landscape';
            }
            $fileName = $file_guid.'.pdf';
            $myFile = $filePath.$fileName;            
            //pdf
            $html = '<body style="font-family: sans-serif; font-size:10px">' . $this->getHtml() . '</body>';
            $pdf = new \KuinkPDF($orientation, 'mm', 'a4', true, 'UTF-8', false, false);
            $pdf->AddPage();
            $pdf->writeHTML($html,true,false,true,false,'');
            $fh = fopen($myFile, 'x+') or die("can't open file. The file is not marked to be overriden.");
            $stringData = $pdf->Output('example_001.pdf', 'S');
            fwrite($fh, $stringData);
			fclose($fh);
			$header = 'Content-Type: application/pdf';
        }

        if (file_exists($myFile) and !is_dir($myFile)) {
            ob_clean();
            header($header);
            send_file($myFile, $fileName);
        } else {
            header('HTTP/1.0 404 not found');
            print_error('filenotfound', 'error'); //this is not displayed on IIS??
        }

        return;
    }

	private function displayCalendar($view) {
        //Check if there is an api or if the events are sent along
        if (isset($this->view_params['api'])) {
            \Kuink\Core\ProcessOrchestrator::registerAPI((string) $this->view_params['api']); 
        }

        $params['refreshable'] = $this->refreshable;
        $params['refreshInterval'] = $this->refreshInterval;
    	$params['refreshUrl'] = $this->baseurl . '&action=' . $this->refreshAction .'&control='.$this->name.'&modal=Control';
        $params['baseUrl'] = $this->baseurl . '&action=' . $this->pagingaction;

        $params['titleField'] = (isset($this->view_params['title'])) ? $this->view_params['title'] : 'title';
        $params['startDateField'] = (isset($this->view_params['startDate'])) ? $this->view_params['startDate'] : 'start_date';
        $params['endDateField'] = (isset($this->view_params['endDate'])) ? $this->view_params['endDate'] : 'end_date';

        $params['calendarOptions'] = $this->view_params;
        $params['data'] = $this->databind;
		$params ['printTitle'] = Core\Language::getString ( $this->title, $this->nodeconfiguration [Core\NodeConfKey::APPLICATION] );
        $this->skeleton = '_calendar';
        $this->render($params);
	}

	private function getDaysInMonth($thisYear, $thisMonth) {
		$date = getdate ( mktime ( 0, 0, 0, $thisMonth + 1, 0, $thisYear ) );
		
		return $date ["mday"] + 1;
	}
	private function getArrayMonth($datetime) {
		// get basic month information and initialize starter values.
		$dateArray = getdate ( $datetime );
		$mon = $dateArray ['mon'];
		$year = $dateArray ['year'];
		$numDaysInMonth = $this->getDaysInMonth ( $year, $mon );
		$week = 1;
		
		// for each day, get the current day's information and store in a result array
		// for that day, the week and the day of the week.
		// finally if that day is the last day of the week, start a new week.
		for($i = 1; $i < $numDaysInMonth; $i ++) {
			$timestamp = mktime ( 0, 0, 0, $mon, $i, $year );
			$dateArray = getdate ( $timestamp );
			$result [$i] = array (
					'wday' => $dateArray ['wday'],
					'week' => $week,
					'timestamp' => $timestamp 
			);
			if ($dateArray ['wday'] == 6) {
				$week = $week + 1;
			}
		}
		
		return $result;
	}
	private function getHTMLCalendar($datetime, $data, $dateField) {
		$html = '';
		$arrayCalendar = $this->getArrayMonth ( $datetime );
		$html .= '<TABLE class="table table-condensed">';
		$week = 1;
		
		$html .= '<tr><th>Segunda</th><th>Terça</th><th>Quarta</th><th>Quinta</th><th>Sexta</th></tr>';
		
		$html .= '<tr>';
		// add initial padding to month
		if ($arrayCalendar [1] ['wday'] != 0 && $arrayCalendar [1] ['wday'] != 6)
			for($start = 1; $start < $arrayCalendar [1] ['wday']; $start = $start + 1) {
				$html .= '<td></td>';
			}
			
			// for each day make a cell with
		$lastday = 1; // use for end month padding later
		foreach ( $arrayCalendar as $day => $result ) {
			// if we change weeks, start a new row
			if ($week != $result ['week']) {
				$html .= '<td></tr><tr>'; // week row
				$week = $result ['week'];
			}
			
			if ($result ['wday'] > 0 && $result ['wday'] < 6) {
				
				// start day cell
				$html .= '<td>';
				$html .= '<table class="table table-condensed table-bordered" >';
				$html .= '<tr><td height="100%">';
				$html .= date ( "D M j Y", $result ['timestamp'] );
				
				$html .= '</td></tr>';
				$html .= '<tr><td><table class="table table-striped table-condensed table-bordered">';
				
				foreach ( $data as $databind ) {
					$found = false;
					foreach ( $databind as $item ) {
						if (date ( "dmy", $result ['timestamp'] ) == date ( "dmy", $item [$dateField] )) {
							$found = true;
							$html .= '<tr>';
							foreach ( $item as $key => $value )
								if ($key != $dateField)
									$html .= '<td>' . $value . '</td>';
							$html .= '</tr>';
						}
					}
				}
				
				// if (!$found)
				// $html .= '<p>&nbsp</p>';
				
				$html .= '</table></td></tr>';
				$html .= '</table>';
				$html .= '</td>'; // end day cell
				$lastday = $day;
			}
		}
		
		// add final padding
		for($start = 1; $start <= 6 - $arrayCalendar [$lastday] ['wday']; $start = $start + 1)
			$html .= '<td></td>';
		
		$html .= '</tr>';
		
		$html .= '</TABLE>';
		
		return $html;
	}
}

?>
