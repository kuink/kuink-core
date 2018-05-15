<?php
namespace Kuink\Adapter\Authentication;

abstract class UsernamePasswordStrategy
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * UsernamePasswordStrategyInterface constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Authenticate the user
     * @return bool
     */
    abstract public function authenticate() : bool;

}
