<?php
/**
 * Hayate Framework
 * Copyright 2009-2010 Andrea Belvedere
 *
 * Hayate is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @package Hayate
 */
class Hayate_Config implements Countable, ArrayAccess
{
    protected static $instance = null;
    protected $data;

    protected function __construct()
    {
        $this->data = array();
    }

    /**
     * @param string $name Name of the configuration file to load, the
     * default 'core' loads the main application config.php
     * file
     * @return Hayate_Config The loaded configuration file
     */
    public static function load($name = 'core', $required = true)
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        self::$instance->_load($name, $required);
        return self::$instance;
    }

    public static function getInstance()
    {
        return self::load();
    }

    public function get($name, $default = null, $slash = false)
    {
        $ns = 'core';
        if (false !== ($pos = strpos($name, '.')))
        {
            $ns = substr($name, 0, $pos);
            $name = substr($name, ++$pos);
        }
        // will try to load it if not loaded
        $this->_load($ns, false);
        if (isset($this->$ns) && isset($this->$ns->$name))
        {
            return $slash ? rtrim($this->$ns->$name, '\//') . DIRECTORY_SEPARATOR : $this->$ns->$name;
        }
        return $default;
    }

    protected function _load($name, $required)
    {
        if (isset($this->$name)) return;

        // first look in application/config folder
        $configname = $name.'.php';
        if ('core' == $name)
        {
            $configname = 'config.php';
        }
        $dirpath = APPPATH . 'config/';
        $files = new DirectoryIterator($dirpath);
        foreach ($files as $file)
        {
            $filepath = $file->getPathname();
            $filename = $file->getFilename();
            if (($filename == $configname) && is_file($filepath))
            {
                require_once $filepath;
        if (isset($config) && is_array($config))
        {
            $this->$name = new ArrayObject($config,ArrayObject::ARRAY_AS_PROPS);
        }
                return;
            }
        }
        // then look in modules config folder
        $modules = $this->get('modules', array());
        $modules[] = $this->get('default_module', 'default');
        foreach ($modules as $module)
        {
            $dirpath = MODPATH . $module . '/config/';
            if (is_dir($dirpath))
            {
                $files = new DirectoryIterator($dirpath);
                foreach ($files as $file)
                {
                    $filepath = $file->getPathname();
                    $filename = $file->getFilename();
                    if (($filename == $configname) && is_file($filepath))
                    {
                        require_once $filepath;
                        if (isset($config) && is_array($config))
                        {
                            $this->$name = new ArrayObject($config,ArrayObject::ARRAY_AS_PROPS);
                        }
                        return;
                    }
                }
            }
        }
        if ($required)
        {
            throw new Hayate_Exception(sprintf(_('%s%s Not found.'), $name, '.php'));
        }
    }

    public function __get($name)
    {
        // try to load it in case is not loaded
        $this->_load($name, false);
        return $this[$name];
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this->data))
        {
            return ($this->data[$name] !== null);
        }
        return false;
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * @see http://php.net/manual/en/class.countable.php
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @see http://php.net/manual/en/class.arrayaccess.php
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @see http://php.net/manual/en/class.arrayaccess.php
     */
    public function offsetExists($offset)
    {
        if (array_key_exists($offset, $this->data))
        {
            return ($this->data[$offset] !== null);
        }
        return false;
    }

    /**
     * @see http://php.net/manual/en/class.arrayaccess.php
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @see http://php.net/manual/en/class.arrayaccess.php
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->data))
        {
            return $this->data[$offset];
        }
        return null;
    }
}
