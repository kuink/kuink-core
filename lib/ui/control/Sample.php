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

/**
 * This is a sample file for an instruction 
 * 
 * To use it:
 *    Copy this file and paste it in the same directory with the name changed to the new control name
 *    Change the classname to the new control name
 * 		
 * This control will take an array and create a box with a title
 * Xml definition of this control
 * 		<Sample type="default|primary|info|error|warning|success" removable="true|false" collapsible="true|false">
 *			<Title attribute="The bind array attribute name"/>
 *			<Content attribute="The bind array attribute name"/>
 *			<Footer attribute="The bind array attribute name"/>
 * 		</Sample>
 *
 * Usage example:
 * 		Use this control on a screen, then inside an action, bind an array to it and see how it looks like
 * 
 * @author paulo.tavares
 */
class Sample extends Control {
	/*
	 * The display method is the mwthod that will be called 
	 */
	function display() {
		//Get the title and content values and render them
		//The $this->bind_data will hold all the binds
		//The $this->xml_definition will hold the xml object of this control

		//Fetch only the first bound array. In this case we want only the first bound array to the control
		$data = $this->bind_data[0]; 

		//Get the type mandatory control property type
		$type = ( string ) $this->getProperty ( '', 'type', true, null, $this->xml_definition );
		//Get the removable non mandatory control property removable which defaults to false
		$removable = ( string ) $this->getProperty ( '', 'removable', false, 'false', $this->xml_definition );
		//Get the collapsible non mandatory control property removable which defaults to false
		$collapsible = ( string ) $this->getProperty ( '', 'collapsible', false, 'true', $this->xml_definition );


		//Now get the title, content and footer xml elements and validate mandatory ones
		$titleXml = $this->getInnerElement('Title', true, $this->xml_definition);
		$contentXml = $this->getInnerElement('Content', true, $this->xml_definition);
		$footerXml = $this->getInnerElement('Footer', false, $this->xml_definition);

		$titleAttr = ( string ) $this->getProperty ( '', 'attribute', true, null, $titleXml );
		$contentAttr = ( string ) $this->getProperty ( '', 'attribute', true, null, $contentXml );
		$footerAttr = ( string ) $this->getProperty ( '', 'attribute', false, '', $footerXml );

		//Now that we have the array keys retrieve the values from the bound data
		$title = isset($data[$titleAttr]) ? (string) $data[$titleAttr] : '';
		$content = isset($data[$contentAttr]) ? (string) $data[$contentAttr] : '';
		$footer = isset($data[$footerAttr]) ? (string) $data[$footerAttr] : '';

		//The render method will call the theme ui/control/Sample.tpl file and will pass that array to the tpl file
		//The tpl file will construct the html and expand title and content from the array
		$this->render ( array (
				"type" => $type,
				"removable" => $removable,
				"collapsible" => $collapsible,
				"title" => $title,
				"content" => $content,
				"footer" => $footer,
		) );
	}

	/*
	 * This function return an html directly to the caller
	 */
	function getHtml() {
		$html = '';
		return $html;
	}
}

?>
