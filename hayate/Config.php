<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Tue Apr 26 16:50:48 JST 2011
 */
namespace Hayate;

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
}