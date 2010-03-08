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
 * The Router class finds the module, controller and action to be
 * invoked by the dispatcher.
 *
 * @package Hayate
 * @version 1.0
 */
class Router
{
    protected static $instance = null;
    protected $routes = array();
    protected $config;
    protected $path;
    protected $routed_path;

    protected function __construct()
    {
        $this->config = Config::instance();
	$this->routes = $this->config->get('routes.*');
        $base_path = array();
        if (isset($this->config->base_path)) {
            $base_path = preg_split('|/|', $this->config->base_path, -1, PREG_SPLIT_NO_EMPTY);
        }
        $uri = URI::instance();
        $segments = $uri->segments();
        for ($i = 0; $i < count($base_path); $i++) {
            if (isset($segments[$i]) && ($segments[$i] == $base_path[$i])) {
                unset($segments[$i]);
            }
        }
        $this->path = $this->routed_path = implode('/',$segments);
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function route()
    {
        if (isset($this->routes[$this->path])) {
            $this->routed_path = $this->routes[$this->path];
        }
        else
	{
            foreach ($this->routes as $key => $val)
	    {
                if (preg_match('|^'.$key.'|u', $this->path) == 1)
		{
                    if (false !== strpos($val, '$')) {
                        $this->routed_path = preg_replace('|^'.$key.'|u', $val, $this->path);
                    }
                    else {
                        $this->routed_path = $val;
                    }
                    break;
                }
            }
        }
    }

    public function path()
    {
        return $this->path;
    }

    public function routedPath()
    {
        return $this->routed_path;
    }
}