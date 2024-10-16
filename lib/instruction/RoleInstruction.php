<?php

namespace Kuink\Core\Instruction;

/**
 * Role instruction
 *
 * @author paulo.tavares
 */
class RoleInstruction extends \Kuink\Core\Instruction {
	static public function execute($instManager, $instructionXmlNode) {
		// Set the role
    $value = (string) $instManager->executeInnerInstructions( $instructionXmlNode );
		//kuink_mydebug('Role', $value);

		$clear = self::getAttribute ( $instructionXmlNode, 'clear', $instManager->variables, false );//$this->get_inst_attr ( $instruction_xmlnode, 'clear', $variables, false );
		// $node_roles = $nodeconfiguration[NodeConfKey::NODE_ROLES];
		$roles = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ROLES];
		
		$currentStackRoles = \Kuink\Core\ProcessOrchestrator::getNodeRoles ();
		//kuink_mydebugObj('CurrentStackRoles', $currentStackRoles);
		
		if ($clear == 'true') {
			if ($value == '') { // clear all
			    // TODO:: remove the dynamic roles
				if (isset($currentRoles) && is_array($currentRoles))
					foreach ( $currentRoles as $roleToDelete => $valueToDelete ) {
						unset ( $roles [$roleToDelete] );
						unset ( $currentStackRoles [$roleToDelete] );
					}
				\Kuink\Core\ProcessOrchestrator::setNodeRoles ( $currentStackRoles );
			} else { // clear just this role
				unset ( $roles [$value] );
			}
		} else {
			// The $value contains the role name to add
			$roles [$value] = 1;
			$currentStackRoles [$value] = 1;
			\Kuink\Core\ProcessOrchestrator::setNodeRoles ( $currentStackRoles );
		}

		//update the roles 
		$instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ROLES] = $roles;
		$instManager->runtime->nodeconfiguration = $instManager->nodeConfiguration;

		//update the action permissions
		$actionPermissions = $instManager->runtime->getActionPermissions ( $instManager->runtime->nodeManager->nodeXml );
		//kuink_mydebugObj('Action Permissions', $actionPermissions);
		$instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ROLES] = $roles;
    $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::ACTION_PERMISSIONS] = $actionPermissions;
    $instManager->runtime->nodeconfiguration = $instManager->nodeConfiguration;

		//Rebuild the capabilities
		$instManager->runtime->buildAllCapabilities (null, null, true);

		//Update the global variables to the new values
		$instManager->variables['ROLES'] = $instManager->runtime->nodeconfiguration[\Kuink\Core\NodeConfKey::ROLES];
		$instManager->variables['CAPABILITIES'] = $instManager->runtime->nodeconfiguration[\Kuink\Core\NodeConfKey::CAPABILITIES];		

		return $value;
    }
}

?>
