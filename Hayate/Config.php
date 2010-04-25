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
 * @version 1.0
 */
class Config implements ArrayAccess
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
        $namespace = 'config';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (array_key_exists($namespace, $this->data))
        {
            if ('*' == $name)
            {
                return $this->data[$namespace];
            }
            if (isset($this->data[$namespace][$name]))
            {
                if ($slash && is_string($this->data[$namespace][$name]))
                {
                    return rtrim($this->data[$namespace][$name], '/\\') . DIRECTORY_SEPARATOR;
                }
                return $this->data[$namespace]->$name;
            }
        }
        return $default;
    }

    public function set($name, $value)
    {
        $namespace = 'config';
        if (false !== ($pos = strpos($name, '.'))) {
            $namespace = substr($name, 0, $pos);
            $name = str_replace($namespace.'.', '', $name);
        }
        if (! array_key_exists($namespace, $this->data)) {
            require_once 'HayateException.php';
            throw new HayateException(sprintf(_('namespace %s not found.'), $namespace));
        }
        $this->data[$namespace][$name] = $value;
    }

    /**
     * this is only useful for the default namespace 'config'
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * this is only useful for the default namespace 'config'
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        $namespace = 'config';
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
     * @param string $namespace The namespace
     * @param string|array $conf If String then is a path to a config
     * file
     * @param bool $mutable If true config object paramters values can
     * be changed, if false object in immutable
     */
    public function load($namespace, $conf, $mutable = false)
    {
        if (is_array($conf)) {
            $this->data[$namespace] = new Registry($conf, $mutable);
        }
        else if (is_string($conf) && file_exists($conf)) {
            require $conf;
            $this->data[$namespace] = new Registry($config, $mutable);
        }
        else {
            throw new HayateException(sprintf(_('Config "%s" could not be loaded.'), $namespace));
        }
    }

    /**
     * @see ArrayAccess::offsetExists
     */
    public function offsetExists($name)
    {
        $namespace = 'config';
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
        $namespace = 'config';
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