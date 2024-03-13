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

class Menu extends Control {
	function display() {
		$actionPermissions = $this->nodeconfiguration [\Kuink\Core\NodeConfKey::ACTION_PERMISSIONS];
		$actionarray = array ();
		$utils = new \UtilsLib ( $this->nodeconfiguration, null );
		$root = ($this->xml_definition->xpath ( './Action' ));
		$parent = $this->xml_definition->xpath ( '.' );
		$actionName = ( string ) $parent [0] ['action'];
		$menuVisible = ( string ) $this->getProperty ( $this->name, 'visible', true, 'true', $this->xml_definition, true );

		$menu ['label'] = \Kuink\Core\Language::getString ( ( string ) $parent [0] ['label'], $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] );
		$menu ['href'] = ($actionName != '') ? $utils->ActionUrl ( array (
				0 => $actionName 
		) ) : '#';
		$menu ['icon'] = (isset ( $parent [0] ['icon'] )) ? ( string ) $parent [0] ['icon'] : 'circle-o';
		$menu ['id'] = (isset ( $parent [0] ['id'] )) ? ( string ) $parent [0] ['id'] : '';
		//var_dump(sizeof($this->dynamic_fields));
		$menuSize = !(isset($this->dynamic_fields)) ? 0 : sizeof ( $this->dynamic_fields );
		if ($menuSize <= 0) {
			foreach ( $root as $action ) {

				$name = isset ( $action ['name'] ) ? ( string ) $action ['name'] : '';
				$id = isset ( $action ['id'] ) ? ( string ) $action ['id'] : '';
				$label = isset ( $action ['label'] ) ? ( string ) $action ['label'] : $id;
				$visible = isset ( $action ['visible'] ) ? ( string ) $action ['visible'] : 'true';
				$modal = isset ( $action ['modal'] ) ? ( string ) $action ['modal'] : '';
				$target = isset ( $action ['target'] ) ? ( string ) $action ['target'] : '_self';
				$icon = isset ( $action ['icon'] ) ? ( string ) $action ['icon'] : 'circle-o';
				$value = isset ( $action ['value'] ) ? ( string ) $action ['value'] : '';
				//Keep compatibility with attribute value and actionvalue
				if ($value == '')
					$value = isset ( $action ['actionvalue'] ) ? ( string ) $action ['actionvalue'] : '';
			
				if ($actionPermissions [$name] && $visible == 'true') {
					// The user has permissions to execute this action
					// Add this action to the array
					$label = \Kuink\Core\Language::getString ( $label, $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] );
					
					$url = $utils->ActionUrl ( array (
							0 => $name 
					) );
					$url = ($modal == 'true') ? $url . '&modal=true' : $url;
					$url = ($value != '') ? $url . '&actionvalue=' . $value : $url;

					$menu ['child'] [] = array (
							'href' => $url,
							'modal' => $modal,
							'label' => $label,
							'target' => $target,
							"icon" => $icon,
							"id" => $id 
					);
				}
			}
		} else {
			if (isset($this->dynamic_fields))
				foreach ( $this->dynamic_fields as $action ) {
					$actionAttributes = $action [0];
					// print_object($actionAttributes);
					$name = isset ( $actionAttributes ['name'] ) ? ( string ) $actionAttributes ['name'] : '';
					$id = isset ( $actionAttributes ['id'] ) ? ( string ) $actionAttributes ['id'] : '';
					$label = isset ( $actionAttributes ['label'] ) ? ( string ) $actionAttributes ['label'] : $id;
					$visible = isset ( $actionAttributes ['visible'] ) ? ( string ) $actionAttributes ['visible'] : 'true';
					$modal = isset ( $actionAttributes ['modal'] ) ? ( string ) $actionAttributes ['modal'] : '';
					$target = isset ( $actionAttributes ['target'] ) ? ( string ) $actionAttributes ['target'] : '_self';
					$icon = isset ( $actionAttributes ['icon'] ) ? ( string ) $actionAttributes ['icon'] : 'circle-o';
					$value = isset ( $actionAttributes ['value'] ) ? ( string ) $actionAttributes ['value'] : '';
					//Keep compatibility with attribute value and actionvalue
					if ($value == '')
						$value = isset ( $actionAttributes ['actionvalue'] ) ? ( string ) $actionAttributes ['actionvalue'] : '';
					
					// print_object($actionPermissions[ $name ].' '.$visible);
					
					if ($actionPermissions [$name] && $visible == 'true') {
						// The user has permissions to execute this action
						// Add this action to the array
						$label = \Kuink\Core\Language::getString ( $label, $this->nodeconfiguration [\Kuink\Core\NodeConfKey::APPLICATION] );
						
						$url = $utils->ActionUrl ( array (
								0 => $name 
						) );
						$url = ($modal == 'true') ? $url . '&modal=true' : $url;
						$url = ($value != '') ? $url . '&actionvalue=' . $value : $url;
						$menu ['child'] [] = array (
								'href' => $url,
								'modal' => $modal,
								'label' => $label,
								'target' => $target,
								"icon" => $icon,
								"id" => $id 
						);
					}
				}
		}
		
		// return $actionarray;
		// $this->render( array("title"=>$title, "actions"=>$actionarray) );
		$layout = \Kuink\UI\Layout\Layout::getInstance ();
		
		// print_object(count($menu['child']));
		if ((isset($actionPermissions [$actionName]) || ($actionName == '' && isset($menu ['child']) && count ( $menu ['child'] ) > 0)) && $menuVisible == 'true')
			$layout->setNodeMenu ( $menu );
	}
	
	/**
	 * Dynamically adding a field to a form
	 * 
	 * @param unknown_type $field_properties        	
	 */
	function addField($fieldProperties) {
		$this->dynamic_fields [] = $fieldProperties;
		// print_object($fieldProperties);
		
		return;
	}
	function getHtml() {
		return null;
	}
}

?>