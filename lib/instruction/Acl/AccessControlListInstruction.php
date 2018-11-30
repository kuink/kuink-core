<?php

namespace Kuink\Core\Instruction\Acl;

/**
 * AccessControlList instruction
 *
 * @author paulo.tavares
 */
class AccessControlListInstruction extends \Kuink\Core\Instruction {
	static public function execute($instManager, $instructionXmlNode) {
        $idAcl = (string)$instManager->executeInnerInstructions( $instructionXmlNode );
	
		$instManager->runtime->buildAllCapabilities($idAcl,null,true);
		
		$roles = $instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::ROLES];
		$rolesAcl = $instManager->runtime->getAllRolesAcl($idAcl);
		//print_object($rolesAcl);
		foreach ($rolesAcl as $roleKey=>$roleValue)
			$roles[$roleKey] = 1;

		//$roles[$value] = 1;
		$instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::ROLES] = $roles;
		$instManager->runtime->nodeconfiguration[\Kuink\Core\NodeConfKey::ROLES] = $roles;
		
		$instManager->variables['CAPABILITIES'] = $instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::CAPABILITIES];
		$instManager->variables['ROLES'] = $instManager->nodeConfiguration[\Kuink\Core\NodeConfKey::ROLES];

        return $idAcl;        
    }
}

?>
