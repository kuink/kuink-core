<?php

class AuthenticationLib
{
    const STRATEGY_PLAIN = 'PLAIN';
    const STRATEGY_LDAP = 'LDAP';

    protected $nodeConfiguration;
    protected $msgManager;

    protected $username;
    protected $password;
    protected $strategy;

    public function __construct($nodeConfiguration, $msgManager)
    {
        $this->nodeConfiguration = $nodeConfiguration;
        $this->msgManager = $msgManager;
    }

    public function authenticate($params)
    {
        try {
            $this->strategy = $this->extractStrategy($params['strategy']);
            $this->username = $this->getParamValue($params, 'username', false);
            $this->password = $this->getParamValue($params, 'password', false);
            return $this->doAuthentication();
        } catch (\Exception $e) {
            $this->msgManager->add(\Kuink\Core\MessageType::ERROR, $e->getMessage());
        }
        return false;
    }

    protected function doAuthentication() : bool
    {
        if ($this->strategy === self::STRATEGY_PLAIN) {
            $authenticator = new \Kuink\Adapter\Authentication\PlainStrategy($this->username, $this->password);
            return $authenticator->authenticate();
        }

        return false;
    }

    /**
     * Auxiliary function to get a param value from the name of the param
     *
     * @param array $params
     * @param string|int $key Param key
     * @param stdClass $default The default value if the param does not exists
     * @return string Param value
     */
    private function getParamValue($params, $key, $default = null)
    {
        return $params[$key] ? (string)$params[$key] : $default;
    }


    /**
     * Get the strategy by the param value
     *
     * @param string|\SimpleXMLElement $strategyParam. Default is null.
     * @return string
     * @throws \Exception When the authentication strategy is not valid
     */
    protected function extractStrategy($strategyParam = null)
    {
        $strategy = $strategyParam ? (string)$strategyParam : false;

        if (!$strategy) {
            return $this->getDefaultStrategy();
        }

        if (!$this->isValidStrategy($strategy)) {
            throw new \Exception("Authentication strategy $strategy is not valid");
        }

        return $strategy;
    }

    /**
     * Get the list of available strategies
     *
     * @return array
     */
    protected function getAvailableStrategies()
    {
        return [
            self::STRATEGY_PLAIN,
            self::STRATEGY_LDAP
        ];
    }

    /**
     * Check if a given strategy is valid
     *
     * @param string $strategy
     * @return boolean
     */
    protected function isValidStrategy($strategy)
    {
        return in_array($strategy, $this->getAvailableStrategies());
    }

    /**
     * Get the default strategy
     *
     * @return string
     */
    protected function getDefaultStrategy()
    {
        return self::STRATEGY_PLAIN;
    }
}
