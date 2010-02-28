<?php
/**
 * Hayate Framework
 * Copyright 2010 Andrea Belvedere
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
 * @version $Id: Config.php 39 2010-02-08 08:47:53Z andrea $
 */
class Hayate_Config implements ArrayAccess
{
    protected static $instance = null;
    protected $data;

    protected function __construct() 
    {
        $this->data = array();
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($name, $default = null, $slash = false)
    {
        $namespace = 'hayate';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (array_key_exists($namespace, $this->data)) {
            if (isset($this->data[$namespace][$name])) {
                if ($slash && is_string($this->data[$namespace][$name])) {
                    return rtrim($this->data[$namespace][$name], '/\\') . DIRECTORY_SEPARATOR;
                }
                return $this->data[$namespace]->$name;
            }
        }
        return $default;
    }

    public function set($name, $value)
    {
        $namespace = 'hayate';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (! array_key_exists($namespace, $this->data)) {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(sprintf(_('namespace %s not found.'), $namespace));
        }
        $this->data[$namespace][$name] = $value;
    }

    /**
     * this is only useful for the default namespace 'hayate'
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * this is only useful for the default namespace 'hayate'
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        $namespace = 'hayate';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (array_key_exists($namespace, $this->data) &&
            array_key_exists($name, $this->data[$namespace])) {
            return $this->data[$namespace][$name] != null;
        }
        return false;
    }

    /**
     * @param $ns String The namespace
     * @param $conf String|array If String then is a path to a config file
     */
    public function load($namespace, $conf, $mutable = false)
    {
        require_once 'Hayate/Registry.php';
        if (is_array($conf)) {
            $this->data[$namespace] = new Hayate_Registry($conf, $mutable);
        }
        else if (is_string($conf) && file_exists($conf)) {
            require $conf;
            $this->data[$namespace] = new Hayate_Registry($config, $mutable);
        }
        else {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(_('Config file could not be loaded.'));
        }
    }

    /**
     * @see ArrayAccess::offsetExists
     */
    public function offsetExists($name)
    {
        $namespace = 'hayate';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (array_key_exists($namespace, $this->data)) {
            return array_key_exists($name, $this->data[$namespace]);
        }
        return false;
    }

    /**
     * @see ArrayAccess::offsetGet
     *
     * This acts a bit like in Ruby, it will return null if $name does
     * not exists
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * @see ArrayAccess::offsetSet
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @see ArrayAccess::offsetUnset
     */
    public function offsetUnset($name)
    {
        $namespace = 'hayate';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (array_key_exists($namespace, $this->data) &&
            array_key_exists($name, $this->data[$namespace])) {
            unset($this->data[$namespace][$name]);
        }
    }
}