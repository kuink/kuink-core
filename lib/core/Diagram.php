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

namespace Kuink\Core;

class DiagramRelationType {
	const EXACTLY_ONE = '||--';
	const ONE_TO_MANY = '}|--';
	const ZERO_TO_ONE = '|o--';
	const ZERO_TO_MANY = '}o--';
}

/**
 * This class is the class that defines uml diagrams
 * @author ptavares
 */
class Diagram
{
	var $source; 

	/**
	 * Base Contructor
	 */
	function __construct() {
		$this->source = '';
	}

	public function getUml() {
		$this->source = '@startuml <br/>' . $this->source;
		$this->source .= '@enduml';
		return $this->source;
	}

	public function addTitle($title) {
		$this->source .= 'title '.$title.'<br/>';
	}

	public function addStart() {
		$this->source .= 'start <br/>';
	}

	public function addStop() {
		$this->source .= 'stop <br/>';
	}

	public function addState($state, $description) {
		$this->source .= $state.' : '.$description.' <br/>';
	}

	public function addStartFlow($startNode, $event) {
		$eventName = isset($event) ? ' : '.$event : '';
		$this->source .= '[*] --> '.$startNode.$eventName.'<br/>';
	}

	public function addEndFlow($endNode, $event) {
		$eventName = isset($event) ? ' : '.$event : '';
		$this->source .= $endNode.' --> [*] '.$eventName.'<br/>';
	}

	public function addFlow($startNode, $endNode, $event) {
		$eventName = isset($event) ? ' : '.$event : '';
		$this->source .= $startNode.' --> '.$endNode.$eventName.'<br/>';
	}		

	public function getHyperlink($url, $label) {
		return '[['.$url.' ' .$label.']]';
	}		

	public function addEntity($name, $multilang, $doc, $attributes) {
	/*
		entity "Entity02" as e02 {
		*e2_id : number <<generated>>
		--
		*e1_id : number <<FK>>
		other_details : text
		}	
	*/		
		$this->source .= 'entity "'.$name.'" {'.'<br/>';
		if ($doc != '')
			$this->source .= $doc.'<br/>--<br/>';
		foreach ($attributes as $attribute) {
			//kuink_mydebugObj('attribute', $attribute);
			$prefix = (isset($attribute['domain']) && ($attribute['domain']=='foreign' || $attribute['domain']=='id') ) ? '*' : '';
			$type = (isset($attribute['domain'])) ? $attribute['domain'] : $attribute['type'].' ('.$attribute['size'].')';
			$doc = (isset($attribute['doc']) && $attribute['doc']!='') ? ' ('.$attribute['doc'].')' : '';
			$this->source .= $prefix.'**'.$attribute['name'].'** : '.$type.$doc.'<br/>';	
		}
		$this->source .= '}'.'<br/>';
	}

	public function addEntityRelation($entityTo, $entityFrom, $relationType=DiagramRelationType::ZERO_TO_MANY) {
		/*
			e01 ||..o{ e02
			e01 |o..o{ e03
		*/		
			$this->source .= $entityFrom.' '.$relationType.' '.$entityTo.'<br/>';
	}
	

}

?>
