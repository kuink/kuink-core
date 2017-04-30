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
		$actionPermissions = $this->nodeconfiguration [\Neon\Core\NodeConfKey::ACTION_PERMISSIONS];
		$actionarray = array ();
		$utils = new \UtilsLib ( $this->nodeconfiguration, null );
		$root = ($this->xml_definition->xpath ( './Action' ));
		$parent = $this->xml_definition->xpath ( '.' );
		$actionName = ( string ) $parent [0] ['action'];
		$visible = isset ( $parent [0] ['visible'] ) ? ( string ) $parent [0] ['visible'] : 'true';
		
		$menu ['label'] = \Neon\Core\Language::getString ( ( string ) $parent [0] ['label'], $this->nodeconfiguration [\Neon\Core\NodeConfKey::APPLICATION] );
		$menu ['href'] = ($actionName != '') ? $utils->ActionUrl ( array (
				0 => $actionName 
		) ) : '#';
		$menu ['icon'] = (isset ( $parent [0] ['icon'] )) ? ( string ) $parent [0] ['icon'] : 'circle';
		$menu ['id'] = (isset ( $parent [0] ['id'] )) ? ( string ) $parent [0] ['id'] : '';
		
		// print_object($this->dynamic_fields);
		if (sizeof ( $this->dynamic_fields ) <= 0) {
			foreach ( $root as $action ) {
				
				$name = isset ( $action ['name'] ) ? ( string ) $action ['name'] : '';
				$id = isset ( $action ['id'] ) ? ( string ) $action ['id'] : '';
				$label = isset ( $action ['label'] ) ? ( string ) $action ['label'] : $id;
				$visible = isset ( $action ['visible'] ) ? ( string ) $action ['visible'] : 'true';
				$modal = isset ( $action ['modal'] ) ? ( string ) $action ['modal'] : '';
				$target = isset ( $action ['target'] ) ? ( string ) $action ['target'] : '_self';
				$icon = isset ( $action ['icon'] ) ? ( string ) $action ['icon'] : 'circle';
				
				if ($actionPermissions [$name] && $visible == 'true') {
					// The user has permissions to execute this action
					// Add this action to the array
					$label = \Neon\Core\Language::getString ( $label, $this->nodeconfiguration [\Neon\Core\NodeConfKey::APPLICATION] );
					
					$url = $utils->ActionUrl ( array (
							0 => $name 
					) );
					$url = ($modal == 'true') ? $url . '&modal=true' : $url;
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
			foreach ( $this->dynamic_fields as $action ) {
				$actionAttributes = $action [0];
				// print_object($actionAttributes);
				$name = isset ( $actionAttributes ['name'] ) ? ( string ) $actionAttributes ['name'] : '';
				$id = isset ( $actionAttributes ['id'] ) ? ( string ) $actionAttributes ['id'] : '';
				$label = isset ( $actionAttributes ['label'] ) ? ( string ) $actionAttributes ['label'] : $id;
				$visible = isset ( $actionAttributes ['visible'] ) ? ( string ) $actionAttributes ['visible'] : 'true';
				$modal = isset ( $actionAttributes ['modal'] ) ? ( string ) $actionAttributes ['modal'] : '';
				$target = isset ( $actionAttributes ['target'] ) ? ( string ) $actionAttributes ['target'] : '_self';
				$icon = isset ( $actionAttributes ['icon'] ) ? ( string ) $actionAttributes ['icon'] : 'circle';
				$value = isset ( $actionAttributes ['value'] ) ? ( string ) $actionAttributes ['value'] : '';
				
				// print_object($actionPermissions[ $name ].' '.$visible);
				
				if ($actionPermissions [$name] && $visible == 'true') {
					// The user has permissions to execute this action
					// Add this action to the array
					$label = \Neon\Core\Language::getString ( $label, $this->nodeconfiguration [\Neon\Core\NodeConfKey::APPLICATION] );
					
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
		$layout = \Neon\UI\Layout\Layout::getInstance ();
		
		// print_object(count($menu['child']));
		if ($actionPermissions [$actionName] || ($actionName == '' && count ( $menu ['child'] ) > 0)) {
			$layout->setNodeMenu ( $menu );
		}
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