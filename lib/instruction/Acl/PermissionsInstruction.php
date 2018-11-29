<?php

namespace Kuink\Core\Instruction\Acl;

/**
 * Permissions instruction
 *
 * @author paulo.tavares
 */
class PermissionsInstruction extends \Kuink\Core\Instruction {
	static public function execute($instManager, $instructionXmlNode) {
        $result = $instManager->runtime->hasPermissions ( $instructionXmlNode );
        if ($result == 0)
            throw new \Exception ( 'No permission!' );
    }
}

?>
