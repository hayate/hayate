<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Tue Apr 26 16:50:48 JST 2011
 */
namespace Hayate\Util;

class Config
{
    protected $config;

    /**
     * @param string|array $config If string must be the path to a file returning an array
     */
    public function __construct($config)
    {
        if (is_string($config))
        {
            $this->config = require $config;
        }
        else {
            $this->config = $config;
        }
    }

    public function get($name, $default = NULL)
    {
        if (isset($this->config[$name]))
        {
            return $this->config[$name];
        }
        return $default;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return isset($this->config[$name]);
    }
}

class Registry
{
    protected static $instance = NULL;
    protected $reg;

    protected function __construct()
    {
        $this->reg = array();
    }

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value)
    {
        $this->reg[$key] = $value;
    }

    public function get($key, $value = NULL)
    {
        if (array_key_exists($key, $this->reg))
        {
            return $this->reg[$key];
        }
        return $value;
    }
}