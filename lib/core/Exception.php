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
namespace Kuink\Core\Exception;

/**
 * Kuink Exception Codes
 * 100 - Node Errors
 *
 * @author jmpatricio
 *        
 */
class ERROR_CODE {
	const E_GENERIC = 000;	
	const E_NOT_IMPLEMENTED = 001;
	const E_NODE_LOAD = 101;
	const E_NODE_MUST_BE_LOADED = 102;
	const E_NODE_NOT_FOUND = 103;
	const E_INVALID_NAME = 104;
	const E_NODE_DOMAIN_NOT_FOUND = 121;
	const E_NODE_TEMPLATE_NOT_FOUND = 122;
	const E_ENTITY_PRIMARY_KEY_NOT_FOUND = 130;
	const E_ENTITY_PHYSICAL_TYPE_NOT_FOUND = 140;
	const E_PARAMETER_REQUIRED = 201;
}
class Exception extends \Exception {
	var $className;
	function __construct($className = "\Kuink\Core\Exception\Exception", $message = null, $code = null, $previous = null) {
		$this->className = $className;
		parent::__construct ( "Framework :: ('.$className.') (Code $code) " . $message, $code, $previous );
	}
	
	// custom string representation of object
	public function __toString() {
		return $this->className . ": [{$this->code}]: {$this->message}\n";
	}
}
//Generic Exception
class GenericException extends \Exception {
	var $name; //The exception name
	function __construct($name, $message){
		$code = ERROR_CODE::E_GENERIC;
		$this->name = $name;
		$previous = null;
		parent::__construct("Kuink Framework :: ($this->name) :: " . $message, $code, $previous);
	}
	
	public function __toString() {
		return  $this->className. ": [{$this->code}]: {$this->message}\n";
	}
}
// General
class NotImplementedException extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $method) {
		$message = 'Functionality not implemented (' . $method . ').';
		$code = ERROR_CODE::E_NOT_IMPLEMENTED;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}

// Node Exceptions
class ParameterNotFound extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $context, $key) {
		$message = 'Required Parameter (' . $context . ') (' . $key . ') not found.';
		$code = ERROR_CODE::E_PARAMETER_REQUIRED;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class DomainNotFound extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $key) {
		$message = 'Domain (' . $key . ') not found.';
		$code = ERROR_CODE::E_NODE_DOMAIN_NOT_FOUND;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class TemplateNotFound extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $key) {
		$message = 'Template (' . $key . ') not found.';
		$code = ERROR_CODE::E_NODE_TEMPLATE_NOT_FOUND;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class PrimaryKeyNotFound extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $key) {
		$message = 'Primary Key for entity (' . $key . ') not found.';
		$code = ERROR_CODE::E_ENTITY_PRIMARY_KEY_NOT_FOUND;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class PhysicalTypeNotFound extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $entityName, $attrName, $type) {
		$message = 'Physical type of kuink type (' . $type . ') not found for entity ' . $entityName . ', attribute ' . $attrName;
		$code = ERROR_CODE::E_ENTITY_PHYSICAL_TYPE_NOT_FOUND;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class InvalidName extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $name) {
		$message = 'Invalid name of entity (' . $name . ')';
		$code = ERROR_CODE::E_INVALID_NAME;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}

// Node Exceptions
class NodeLoadException extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $application, $process, $type, $node) {
		$message = 'Cannot load node ' . $application . ',' . $process . ',' . $type . ',' . $node;
		$code = ERROR_CODE::E_NODE_LOAD;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class NodeMustBeLoadedException extends \Exception {
	function __construct($className = "\Kuink\Core\Exception\Exception", $application, $process, $type, $node) {
		$message = 'Node Must be loaded ' . $application . ',' . $process . ',' . $type . ',' . $node;
		$code = ERROR_CODE::E_NODE_MUST_BE_LOADED;
		$previous = null;
		parent::__construct ( "Kuink Framework :: (Code $code) " . $message, $code, $previous );
	}
}
class ClassNotFound extends Exception {
	function __construct($message) {
		parent::__construct ( __CLASS__, $message, 100 );
	}
}
class InvalidParameters extends Exception {
	function __construct($message) {
		parent::__construct ( __CLASS__, $message, 101 );
	}
}
class ZeroRowsAffected extends Exception {
	function __construct($message) {
		parent::__construct ( __CLASS__, $message, 102 );
	}
}
class HttpRequestFailed extends Exception {
	function __construct($message) {
		parent::__construct ( __CLASS__, $message, 102 );
	}
}
