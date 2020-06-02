<?php
namespace Kuink\Core\Lib;

/**
 * Created by IntelliJ IDEA.
 * User: jmpatricio
 * Date: 16-05-2014
 * Time: 10:46
 */

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
class MetadataLib {
	var $nodeconfiguration;
	var $msg_manager;
	var $metadataDefinition;
	var $metadataDefinitionXml;
	var $metadataData; // array with data
	var $expandArray; // array with data (single array with the final keys)
	function __construct($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function expand($params) {
		if (count ( $params ) != 2)
			throw new Exception ( 'Metadata: must have two parameters. ' );
		
		$metaXmlDefinition = ( string ) $params ['matchMetadata'];
		
		$objectData = $this->object_to_array ( $params ['objectData'] );
		
		// Load the metadata definition
		$xml = simplexml_load_string ( $metaXmlDefinition );
		$xmlMeta = $xml->xpath ( '/Metadata' );
		
		$xmlMetaExpanded = '<Metadata';
		foreach ( $xmlMeta [0]->attributes () as $xmlMetaAttrName => $xmlMetaAttrValue ) {
			$eval = new \Kuink\Core\EvalExpr ();
			$xmlMetaAttrValueExpanded = $eval->e ( ( string ) $xmlMetaAttrValue, $objectData, FALSE, TRUE, FALSE ); // Eval and return a value without ''
			$xmlMetaExpanded .= ' ' . $xmlMetaAttrName . '="' . $xmlMetaAttrValueExpanded . '"';
		}
		
		$xmlMetaExpanded .= '>';
		foreach ( $xmlMeta [0]->children () as $xmlMetaChild ) {
			$xmlMetaExpanded .= $this->parseMetadataDefinition ( $xmlMetaChild, $objectData );
		}
		$xmlMetaExpanded .= '</Metadata>';
		
		$domxml = new DOMDocument ( '1.0' );
		$domxml->preserveWhiteSpace = false;
		$domxml->formatOutput = true;
		$domxml->loadXML ( $xmlMetaExpanded );
		
		return $domxml->saveXML ();
	}
	private function parseMetadataDefinition($xmlMeta, $objectData, $source = '') {
		$metaName = isset ( $xmlMeta ['name'] ) ? ( string ) $xmlMeta ['name'] : '';
		$metaMinOccurs = isset ( $xmlMeta ['minoccurs'] ) ? ( int ) $xmlMeta ['minoccurs'] : 0;
		
		if (isset ( $xmlMeta ['maxoccurs'] )) {
			$metaMaxOccurs = (( string ) $xmlMeta ['maxoccurs'] == '*') ? ( int ) PHP_INT_MAX : ( int ) $xmlMeta ['maxoccurs'];
		} else
			$metaMaxOccurs = 1;
		
		$metaSource = isset ( $xmlMeta ['source'] ) ? ( string ) $xmlMeta ['source'] : null;
		$metaExpanded = '';
		if ($metaSource == $source)
			$metaExpanded = '<Meta name="' . $metaName . '">';
		
		if ($xmlMeta->count () != 0) {
			if (($metaSource != '') && ($metaSource != $source)) {
				$sourceData = $objectData [$metaSource];
				$i = 1;
				foreach ( $sourceData as $sourceDataElement ) {
					$newObjectData = $objectData;
					$newObjectData [$metaSource] = ( array ) $sourceDataElement; // To get values outside this source object data
					$metaExpanded .= $this->parseMetadataDefinition ( $xmlMeta, $newObjectData, $metaSource );
					if ($i == $metaMaxOccurs)
						break;
					else
						$i ++;
				}
			} else {
				foreach ( $xmlMeta->children () as $xmlMetaChild ) {
					$metaExpanded .= $this->parseMetadataDefinition ( $xmlMetaChild, $objectData );
				}
			}
		} else {
			if ($metaMinOccurs > 1)
				throw new \Exception ( 'Meta element ' . $metaName . ' must have at least ' . $metaMinOccurs . ' ocurrences' );
			$metaValue = ( string ) $xmlMeta [0];
			$arr = $this->getDataToExpand ( $objectData );
			$value = $this->replaceMetadata ( $metaValue, $arr );
			$metaExpanded .= $value;
			// No children get the value and expand it
		}
		if ($metaSource == $source)
			$metaExpanded .= '</Meta>';
		
		return $metaExpanded;
	}

    public function getMetadataValue($params) {
        $metaXmlDefinition = (string)$params['metadata'];
        $key = (string)$params['key'];

        //Search for this meta key
        $xml = simplexml_load_string($metaXmlDefinition);
        $xmlMeta = $xml->xpath('//Meta[@name="'.$key.'"]');
        $value = (string) $xmlMeta[0][0];
        return $value;
	}
		
	private function _d($data, $label = null) {
		if ($label)
			echo "<pre>$label</pre>";
		echo '<pre>';
		var_dump ( $data );
		echo '</pre>';
		
		if ($label)
			echo "<pre>/ $label</pre>";
	}
	private function _dx($data, $label = null) {
		if ($label)
			echo "<pre>$label</pre>";
		echo '<xmp>';
		var_dump ( $data );
		echo '</xmp>';
		if ($label)
			echo "<pre>/ $label</pre>";
	}
	private function getDataToExpand($data) {
		$out = array ();
		foreach ( $data as $dataKey => $dataValue ) {
			$values = $this->expandData ( $dataKey, $dataValue );
			foreach ( $values as $valueKey => $value ) {
				if (is_array ( $value ))
					foreach ( $value as $k => $v )
						$out [$k] = $v;
				else
					$out [$valueKey] = $value;
			}
		}
		return $out;
	}
	private function expandData($dataKey, $dataValue) {
		if (! is_array ( $dataValue )) {
			$outKey = '$' . $dataKey;
			$out [$outKey] = $dataValue;
		} else
			foreach ( $dataValue as $subKey => $subValue ) {
				$out [] = $this->expandData ( $dataKey . '->' . $subKey, $subValue );
			}
		return $out;
	}
	private function replaceMetadata($metadata, $data = null) {
		if (is_array ( $data ))
			return strtr ( $metadata, $data );
		return '';
	}
	private function object_to_array($obj) {
		$arrObj = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
		foreach ( $arrObj as $key => $val ) {
			$val = (is_array ( $val ) || is_object ( $val )) ? $this->object_to_array ( $val ) : $val;
			$arr [$key] = $val;
		}
		return $arr;
	}
}