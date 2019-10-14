<?php

namespace Kuink\Core\Instruction;

/**
 * Password related instructions
 *
 * @author joao.patricio
 */
class PasswordInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Performs a Call
	 */

	static public function execute($instManager, $instructionXmlNode) {
        // not implemented yed
    }

    /**
     * Check if the password is correct against the hash
     *
     * @param mixed $instManager
     * @param mixed $instructionXmlNode
     * @return boolean
     */
    static public function isCorrect($instManager, $instructionXmlNode): bool {
        $params = $instManager->getParams ( $instructionXmlNode );

        
        if (empty($params['hash']) || empty($params['password'])) {
            return false;
        }

        return password_verify(strval($params['password']), strval($params['hash']));
    }
}