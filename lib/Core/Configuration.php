<?php
/**
 * Created by PhpStorm.
 * User: jmp
 * Date: 18-06-2019
 * Time: 0:21
 */

namespace Kuink\Core;

/**
 * Class Configuration
 * @package Kuink\Core
 */
class Configuration
{
    private static $instance;

    private $kv = [];

    /**
     * Get the configuration instance
     * @return Configuration
     */
    public static function getInstance(): Configuration
    {
        if (!self::$instance) {
            self::$instance = new Configuration();
        }
        return self::$instance;
    }

    /**
     * Set the instance
     * @param Configuration $configuration
     * @return Configuration
     */
    public static function setInstance(Configuration $configuration) : Configuration
    {
        self::$instance = $configuration;
        return self::getInstance();
    }


    /**
     * Create a configuration from an array
     * @param array $arr
     * @return Configuration
     */
    public static function creatFromArray(array $arr)
    {
        $obj = new self();
        $obj->kv = \json_decode(\json_encode($arr));
        self::setInstance($obj);
        return self::getInstance();
    }

    /**
     * Get the configuration value given a key
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->kv->$key ?? $default;
    }

    /**
     * Set a configuration value
     * @param string $key
     * @param mixed $value
     * @return Configuration
     */
    public function set(string $key, $value)
    {
        $this->kv->$key = $value;
        return $this;
    }

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param $name string
     * @return mixed
     * @link https://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * run when writing data to inaccessible members.
     *
     * @param $name string
     * @param $value mixed
     * @return void
     * @link https://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }


}