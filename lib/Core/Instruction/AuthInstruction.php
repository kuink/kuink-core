<?php

namespace Kuink\Core\Instruction;

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
        $_SESSION['USER'] = $user;

        return true;
    }


    /**
     * Get the authenticated user
     *
     * @return array
     */
    static public function getLoggedUser($instManager, $instructionXmlNode) {
        if (static::isLoggedIn($instManager, $instructionXmlNode)) {
            return $_SESSION['kuink.logged.user'] ?? null;
        }
        return null;
    }


    /**
     * Check if there is a logged user
     *
     * @return boolean
     */
    static public function isLoggedIn($instManager, $instructionXmlNode) {
        return isset($_SESSION['kuink.logged']) && $_SESSION['kuink.logged'] == 1;
    }

    /**
     * Logout the current user
     *
     * @param mixed $instManager
     * @param mixed $instructionXmlNode
     * @return void
     */
    static public function logout($instManager, $instructionXmlNode) {

        $_SESSION['kuink.logged'] = 0;
        unset($_SESSION['kuink.logged.user'], $_SESSION['USER']);
        return true;
    }
    
}

?>
