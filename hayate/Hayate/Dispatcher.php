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
 * @version $Id: Dispatcher.php 39 2010-02-08 08:47:53Z andrea $
 */
class Hayate_Dispatcher
{
    protected static $instance = null;
    protected $routed_uri;
    protected $uri;
    protected $module;
    protected $controller = 'index';
    protected $action = 'index';
    protected $modules_path;
    protected $params = array();


    protected function __construct()
    {
        require_once 'Hayate/Router.php';
        $route = Hayate_Router::instance();
        $route->route();
        $this->routed_uri = $route->routedPath();
        $this->uri = $route->path();

        require_once 'Hayate/Config.php';
        $config = Hayate_Config::instance();
        $this->module = isset($config->default_module) ? $config->default_module : 'default';
        $this->modules_path = APPPATH.'modules/';
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
        $segments = explode('/', $this->routed_uri);
        $module = array_shift($segments);
        if (null !== $module) 
        {
            $modulepath = $this->module_path . $module;
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
        }
        require_once $this->modules_path.$module.'/controllers/'.$this->controller.'.php';
        $classname = ucfirst($this->module).'_'.ucfirst($this->controller);

        $rfc = new ReflectionClass($this->.'_'.$this->getController().'_Controller');
        if ($rfc->isSubclassOf('Hayate_Controller_Abstract') && $rfc->isInstantiable()) {
            $controller = $rfc->newInstance();
            $action = $rfc->hasMethod($this->getAction()) ? $rfc->getMethod($this->getAction()) : $rfc->getMethod('__call');
            if ($action->isPublic() && (strpos($action->getName(), '_') !== 0)) {
                $action->invokeArgs($controller, $this->getParams());
            }
            else if ($action->getName() == '__call') {
                $action->invoke($controller, $this->getAction(), $this->getParams());
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
}