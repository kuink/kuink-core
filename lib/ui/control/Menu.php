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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework.  If not, see <http://www.gnu.org/licenses/>.


namespace Kuink\UI\Control;

class Menu extends Control {
	function display() {
		  $actionPermissions = $this->nodeconfiguration[\Kuink\Core\NodeConfKey::ACTION_PERMISSIONS];
          $actionarray=array();
          $utils = new \UtilsLib($this->nodeconfiguration, null);
          $root = ($this->xml_definition->xpath('./Action'));
          $parent=$this->xml_definition->xpath('.');
          $actionName = (string)$parent[0]['action'];
          $visible = isset($parent[0]['visible']) ? (string)$parent[0]['visible'] : 'true';
          
          
          
          	$menu['label'] = \Kuink\Core\Language::getString((string)$parent[0]['label'], $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);
          	$menu['href'] = ($actionName!='') ? $utils->ActionUrl(array(0=>$actionName)) : '#';
          	$menu['icon'] = (isset($parent[0]['icon'])) ? (string)$parent[0]['icon'] : 'circle';
          	$menu['id'] = (isset($parent[0]['id'])) ? (string)$parent[0]['id'] : '';
          	
          	
          	
          	foreach ($root as $action) {
          	
          		$name = isset($action['name']) ? (string)$action['name'] : '';
          		$id = isset($action['id']) ? (string)$action['id'] : '';
          		$label = isset($action['label']) ? (string)$action['label'] : $id;
          		$visible = isset($action['visible']) ? (string)$action['visible'] : 'true';
          		$modal = isset($action['modal']) ? (string)$action['modal'] : '';
          		$target = isset($action['target']) ? (string)$action['target'] : '_self';
          		$icon = isset($action['icon']) ? (string)$action['icon'] : 'circle';
          	
          		
          	
          		if ( $actionPermissions[ $name ] && $visible == 'true' ) {
          			//The user has permissions to execute this action
          			//Add this action to the array
          			$label = \Kuink\Core\Language::getString($label, $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);
          			
          			$url = $utils->ActionUrl(array(0=>$name));
          			$url = ($modal == 'true') ? $url.'&modal=true' : $url;
          			$menu['child'][] = array('href'=>$url, 'modal'=>$modal, 'label'=>$label, 'target'=>$target, "icon" => $icon, "id" => $id);
          		}
          	}
          	
          	
          	
          	//return $actionarray;
          	//$this->render( array("title"=>$title, "actions"=>$actionarray) );
          	$layout = \Kuink\UI\Layout\Layout::getInstance();
          	

          	
          	if ( $actionPermissions[$actionName] || ($actionName == '' && count($menu['child'])>0) ){
          		$layout->setNodeMenu($menu);
          	}
          	
          	
          	 
          
          
	}

	function getHtml() {
           return null;
	}
	
	
}


?>