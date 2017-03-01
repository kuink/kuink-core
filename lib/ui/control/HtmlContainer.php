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


/**
 * Properties of the FORM
 * @author ptavares
 *
 */
class HtmlContainerProperty {
	const VISIBLE = 'visible';
	const LABEL = 'label';
	const STYLE = 'style';
	const BORDER = 'border';
}


/**
 * Default values for FORM properties
 * @author ptavares
 *
 */
class HtmlContainerPropertyDefaults {
	const VISIBLE = 'true';
	const LABEL = '';
	const STYLE = '';
	const BORDER = 'false';
}

class HtmlContainer extends Control {
	var $value;

	function __construct($nodeconfiguration, $xml_definition) {
		parent::__construct($nodeconfiguration, $xml_definition);
		
		$this->value = '';
	}
				
	function display() {
        $visible = $this->getProperty($id, HtmlContainerProperty::VISIBLE, false, HtmlContainerPropertyDefaults::VISIBLE);
        $label = $this->getProperty($id, HtmlContainerProperty::LABEL, false, HtmlContainerPropertyDefaults::LABEL);
        $label = \Kuink\Core\Language::getString($label, $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);
        $style = $this->getProperty($id, HtmlContainerProperty::STYLE, false, HtmlContainerPropertyDefaults::STYLE);
        $border = $this->getProperty($id, HtmlContainerProperty::BORDER, false, HtmlContainerPropertyDefaults::BORDER);

        if ($visible != 'true')
            return;
        
        $this->value = implode('',$this->bind_data);
        
        $params['label'] = $label;
        $params['border'] = $border;
        $params['style'] = $style;
        $params['value'] = $this->value;
        $this->render( $params );
	}

	function getHtml() {
        $visible = $this->getProperty($id, HtmlContainerProperty::VISIBLE, false, HtmlContainerPropertyDefaults::VISIBLE);
        $label = $this->getProperty($id, HtmlContainerProperty::LABEL, false, HtmlContainerPropertyDefaults::LABEL);
        $label = \Kuink\Core\Language::getString($label, $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION]);
        $style = $this->getProperty($id, HtmlContainerProperty::STYLE, false, HtmlContainerPropertyDefaults::STYLE);
        $border = $this->getProperty($id, HtmlContainerProperty::BORDER, false, HtmlContainerPropertyDefaults::BORDER);

        if ($visible != 'true')
            return;
		
        $this->value = implode('',$this->bind_data);
        
        $return = $this->value;
        
        //neon_mydebugxml( $return );
        return $return;
	}
	
	
}


?>