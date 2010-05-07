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
class Hayate_Input
{
    protected static $instance;
    protected $params;

    protected function __construct()
    {
        $this->params = array('get' => array(),
                              'post' => array(),
                              'cookie' => array(),
                              'put' => array(),
                              'rawput' => array());
        if (Hayate_Config::getInstance()->get('xss_clean', true))
        {
            foreach ($_GET as $key => $val)
            {
                $this->params['get'][$key] = htmlentities($val, ENT_QUOTES, 'utf-8');
            }
            foreach ($_POST as $key => $val)
            {
                $this->params['post'][$key] = htmlentities($val, ENT_QUOTES, 'utf-8');
            }
            foreach ($_COOKIE as $key => $val)
            {
                $this->params['cookie'][$key] = htmlentities($val, ENT_QUOTES, 'utf-8');
            }
        }
        if (Hayate_Request::getInstance()->isPut())
        {
            $this->loadPut();
        }
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($name = null, $default = null)
    {
        if (null === $name) {
            return $this->params[__FUNCTION__];
        }
        return $this->param($name, $default, __FUNCTION__);
    }

    public function post($name = null, $default = null)
    {
        if (null === $name) {
            return $this->params[__FUNCTION__];
        }
        return $this->param($name, $default, __FUNCTION__);
    }

    public function put($name = null, $default = null)
    {
        if (is_array($this->params['put']))
        {
            if (null === $name) {
                return $this->params[__FUNCTION__];
            }
            return $this->param($name, $default, __FUNCTION__);
        }
        if (! empty($this->params['put']))
        {
            return $this->params['put'];
        }
        return $default;
    }

    public function cookie($name = null, $default = null)
    {
        if (null === $name) {
            return $this->params[__FUNCTION__];
        }
        return $this->param($name, $default, __FUNCTION__);
    }

    public function param($name, $default = null, $type = null)
    {
        if (null === $type)
        {
            switch (true)
            {
            case array_key_exists($name, $this->params['get']):
                return $this->params['get'][$name];
            case array_key_exists($name, $this->params['post']):
                return $this->params['post'][$name];
            case array_key_exists($name, $this->params['cookie']):
                return $this->params['cookie'][$name];
            }
            if (is_array($this->params['put']))
            {
                if (array_key_exists($name, $this->params['put']))
                {
                    return $this->params['put'][$name];
                }
            }
        }
        else if (array_key_exists($type, $this->params) &&
                 array_key_exists($name, $this->params[$type]) &&
                 ! empty($this->params[$type][$name]))
        {
            return $this->params[$type][$name];
        }
        return $default;
    }

    public function rawGet($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    public function rawPost($name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    public function rawCookie($name, $default = null)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    public function rawPut($name, $default = null)
    {
        return isset($this->params['rawput'][$name]) ? $this->params['rawput'][$name] : $default;
    }

    protected function loadPut()
    {
        $fp = fopen('php://input', 'r');
        $put_data = '';
        $length = 1024;
        $tmp = fread($fp, $length);
        while ((false !== $tmp) && (mb_strlen($tmp) > 0))
        {
            $put_data .= $tmp;
            $tmp = fread($fp, $length);
        }
        @fclose($fp);

        $put_data = trim($put_data);
        if (mb_strlen($put_data))
        {
            $query = array();
            @parse_str($put_data, $query);
            if (count($query) > 0)
            {
                foreach ($query as $key => $val)
                {
                    $this->params['put'][$key] = htmlentities($val, ENT_QUOTES, 'utf-8');
                    $this->params['rawput'][$key] = $val;
                }
            }
            else {
                $this->params['put'] = $put_data;
            }
        }
    }
}