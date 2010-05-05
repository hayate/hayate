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
class Dispatcher
{
    protected static $instance = null;
    protected $routed_uri;
    protected $uri;
    protected $module;
    protected $controller = 'index';
    protected $action = 'index';
    protected $modules_path;
    protected $params;


    protected function __construct()
    {
        $route = Router::instance();
        $route->route();
        $this->routed_uri = $route->routedPath();
        $this->uri = $route->path();

        $config = Config::instance();
        $this->module = isset($config->default_module) ? $config->default_module : 'default';
        $this->modules_path = APPPATH.'modules/';
        $this->route();
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function dispatch()
    {
        Event::run('hayate.dispatch', array($this));
        $filepath = $this->modules_path.$this->module().'/controllers/'.$this->controller().'.php';
        if (is_file($filepath) && is_readable($filepath))
        {
            require_once $filepath;
            $classname = ucfirst($this->module).'_'.ucfirst($this->controller);
            $rfc = new ReflectionClass($classname);
            if ($rfc->isSubclassOf('Controller') && $rfc->isInstantiable())
            {
                Event::run('hayate.pre_controller');
                $controller = $rfc->newInstance();
                Event::run('hayate.post_controller', array($controller));
                $action = $rfc->hasMethod($this->action()) ? $rfc->getMethod($this->action()) : $rfc->getMethod('__call');
                if ($action->isPublic() && (strpos($action->getName(), '_') !== 0)) {
                    $action->invokeArgs($controller, $this->params());
                }
                else if ($action->getName() == '__call') {
                    $action->invoke($controller, $this->action(), $this->params());
                }
            }
        }
        else {
            if (true !== Event::run('hayate.404', array($this)))
            {
                //throw new HayateException('Not Found', 404);
            }
        }
    }

    public function module($name = null)
    {
        if (null === $name) {
            return $this->module;
        }
        $this->module = $name;
    }

    public function controller($name = null)
    {
        if (null === $name) {
            return $this->controller;
        }
        $this->controller = $name;
    }

    public function action($name = null)
    {
        if (null === $name) {
            return $this->action;
        }
        $this->action = $name;
    }

    private function params()
    {
        if (! is_array($this->params))
        {
            $this->params = array();
        }
        return $this->params;
    }

    protected function route()
    {
        $segments = explode('/', $this->routed_uri);
        $module = array_shift($segments);
        if (! empty($module))
        {
            $modulepath = $this->modules_path . $module;
            if (is_dir($modulepath))
            {
                $this->module($module);
                $controller = array_shift($segments);
                $action = array_shift($segments);
                $this->controller = empty($controller) ? $this->controller : $controller;
                $this->action = empty($action) ? $this->action : $action;
            }
            else {
                $this->controller($module);
                $action = array_shift($segments);
                $this->action = empty($action) ? $this->action : $action;
            }
            $this->params = $segments;
        }
    }
}