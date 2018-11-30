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

/**
 * Enum values used for nodeconfiguration keys
 * 
 * @author ptavares
 *        
 */
class NodeConfKey {
	const APPLICATION = 'customappname';
	const PROCESS = 'master_process_name';
	const NODE = 'startnode';
	const ACTION = 'action';
	const ACTION_VALUE = 'actionvalue';
	const EVENT = 'event';
	const BASEURL = 'baseurl';
	const CONFIG = 'config';
	const ROLES = 'roles';
	const CAPABILITIES = 'capabilities';
	const INSTANCE_CONFIG_RAW = 'instance_config_raw';
	const ACTION_PERMISSIONS = 'actionPermissions';
	const NODE_ROLES = 'nodeRoles';
	
	// Description of the referal node
	const REF_APPLICATION_DESC = 'REF_APPLICATION_DESC';
	const REF_PROCESS_DESC = 'REF_PROCESS_DESC';
	const REF_NODE_DESC = 'REF_NODE_DESC';
	const USER = 'USER';
	const SYSTEM = 'SYSTEM';
}

?>
