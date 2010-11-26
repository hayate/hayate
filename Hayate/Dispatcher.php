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
class Hayate_Dispatcher
{
    protected static $instance = null;
    protected $routedUri;
    protected $uri;
    protected $module;
    protected $controller = 'index';
    protected $action = 'index';
    protected $modulesPath;
    protected $params;
    protected $errorReporter;


    protected function __construct()
    {
        $route = Hayate_Router::getInstance();
        $route->route();
        $this->routedUri = $route->routedPath();
        $this->uri = $route->path();

        $this->module = Hayate_Config::getInstance()->get('default_module', 'default');
        $this->modulesPath = MODPATH;
        $this->route();
        $this->setErrorReporter(Hayate_Error_Default::getInstance());
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function dispatch()
    {
        $filepath = $this->modulesPath.$this->module().'/controllers/'.$this->controller().'.php';
        if (is_file($filepath))
        {
            // we found a valid controller
            // load this module bootstrap.php file if it exists
            $bs = $this->modulesPath.$this->module().'/bootstrap.php';
            if (is_file($bs))
            {
                require_once $bs;
            }
            require_once $filepath;
            $classname = ucfirst($this->module).'_'.ucfirst($this->controller).'Controller';
            $rfc = new ReflectionClass($classname);
            if ($rfc->isSubclassOf('Hayate_Controller') && $rfc->isInstantiable())
            {
                Hayate_Event::run('hayate.pre_controller', array($this));
                $controller = $rfc->newInstance();
                Hayate_Event::run('hayate.post_controller', array($this, $controller));
                Hayate_Event::run('hayate.pre_action', array($this));
                $action = $rfc->hasMethod($this->action()) ? $rfc->getMethod($this->action()) : $rfc->getMethod('__call');
                if ($action->isPublic() && (strpos($action->getName(), '_') !== 0))
                {
                    $action->invokeArgs($controller, $this->params());
                }
                else if ($action->getName() == '__call')
                {
                    $action->invoke($controller, $this->action(), $this->params());
                }
                Hayate_Event::run('hayate.post_action', array($this));
            }
        }
        else if (true !== Hayate_Event::run('hayate.404', array($this)))
        {
            $this->errorReporter->setStatus(404);
            throw new Hayate_Exception(sprintf(_('Requested page: "%s" not found.'), Hayate_URI::getInstance()->current()), 404);
        }
    }

    public function exceptionDispatch(Exception $ex)
    {
        try{
            if (Hayate_Event::run('hayate.exception', array($this, $ex))) return;

            // try to dispatch to the current module error.php controller
            $module = $this->module();
            $filepath = $this->modulesPath . $module . '/controllers/error.php';

            // if the error controller does not exists in the current module
            // look in the default module
            if (! is_file($filepath))
            {
                $module = Hayate_Config::getInstance()->get('default_module', 'default');
                $filepath = $this->modulesPath . $module . '/controllers/error.php';
            }
            if (is_file($filepath))
            {
                require_once $filepath;
                $classname = ucfirst($module).'_ErrorController';
                $rfc = new ReflectionClass($classname);
                if ($rfc->isSubclassOf('Hayate_Controller') && $rfc->isInstantiable())
                {
                    $controller = $rfc->newInstance();
                    $action = $rfc->hasMethod('index') ? $rfc->getMethod('index') : $rfc->getMethod('__call');
                    if ($action->isPublic())
                    {
                        $action->invokeArgs($controller, array($ex));
                    }
                }
            }
            else {
                $display_errors = Hayate_Config::getInstance()->get('display_errors', false);
                if ($display_errors && $this->errorReporter)
                {
                    Hayate_Event::remove('hayate.send_headers');
                    Hayate_Event::remove('hayate.render');
                    $this->errorReporter->setException($ex);
                    echo $this->errorReporter->report();
                }
            }
        }
        catch(Exception $ex){}
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

    public function setErrorReporter(Hayate_Error_Abstract $reporter)
    {
        $this->errorReporter = $reporter;
    }

    public function params($params = null)
    {
        if (null === $params)
        {
            if (! is_array($this->params))
            {
                $this->params = array();
            }
            return $this->params;
        }
        else if (is_array($params))
        {
            $this->params = $params;
        }
    }

    protected function route()
    {
        $segments = explode('/', $this->routedUri);
        $module = array_shift($segments);
        if (! empty($module))
        {
            $modulepath = $this->modulesPath . $module;
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