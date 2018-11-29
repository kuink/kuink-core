<?php

namespace Kuink\Core\Instruction\Auth;

/**
 * Authentication instruction
 *
 * @author joao.patricio
 */
class AuthInstruction extends \Kuink\Core\Instruction {


	static public function execute($instManager, $instructionXmlNode)
    {
        // TODO: Implement execute() method.
    }

    /**
     * Set the current logged user. Receives one parameter, which is the fw_user record
     *
     * @param $instManager
     * @param $instructionXmlNode
     * @return bool True if the user is authenticated
     * @throws \Exception When the param is not defined
     */
    static public function setLoggedUser($instManager, $instructionXmlNode) {
        $params = $instManager->getParams ( $instructionXmlNode );
        $loggedUser = $params['user'] ?? ($params[0] ?? null);

        if (!$loggedUser){
            throw new \Exception('Auth: param user cannot be empty');
        }

        $_SESSION['kuink.logged'] = 1;
        $user = array();
        $user['id'] = $loggedUser['id'];
        $user['uid'] = $loggedUser['uid'] ?? null;
        $user['firstName'] = $loggedUser['display_name'];
        $user['lastName'] = '';

        // @Todo joao.patricio Add a default lang if not defined.
        $user['lang'] = $loggedUser['lang'];

        $_SESSION['kuink.logged.user'] = $user;

        return true;
    }


}

?>
