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
namespace Kuink\Core;

use Kuink\Core\Exception\Exception;
use Kuink\Core\Exception\ERROR_CODE;
use Kuink\Core\Exception\NodeLoad;
use Kuink\Core\Exception\NodeMustBeLoadedException;
use Kuink\Core\Exception\InvalidName;

class NodeType {
	const DATA_DEFINITION = 'dd';
	const API = 'api';
	const NODE = 'nodes';
	const DATA_ACCESS = 'dataaccess';
}

?>
