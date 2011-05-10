<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Sat May  7 23:07:03 JST 2011
 */
namespace Hayate;

/**
 * Maps URI path to a model, controller and action
 * fires the Routed event when done
 */
class Router
{
    protected static $instance = NULL;

    protected $config;
    protected $modules;
    protected $path;
    protected $routes; // not yet used

    protected $module;
    protected $controller;
    protected $action;
    protected $args;


    public function __construct(array $config)
    {
        $reg = \Hayate\Registry::getInstance();
        $reg->set('router', $this);

        $this->config = $config;
        $this->modules = $reg->get('modules');
        $this->path = URI::getInstance()->path();
        $this->args = array(); // action arguments

        $this->route();
    }

    protected function route()
    {
        if (empty($this->path))
        {
            $this->module = $this->config['default_module'];
            $this->controller = $this->config['default_controller'];
            $this->action = $this->config['default_action'];

            return;
        }
        $parts = preg_split('/\//', $this->path, -1, PREG_SPLIT_NO_EMPTY);
        $module = array_shift($parts);
        if ('index.php' == $module)
        {
            // we are not using a url rewrite engine
            if (empty($parts))
            {
                $this->module = $this->config['default_module'];
                $this->controller = $this->config['default_controller'];
                $this->action = $this->config['default_action'];

                return;
            }
            else {
                // lets shift again
                $module = array_shift($parts);
            }
        }
        if ($this->isModule($module))
        {
            $this->module = $module;
            if (count($parts) > 0)
            {
                $this->controller = array_shift($parts);
                if (count($parts) > 0)
                {
                    $this->action = array_shift($parts);
                }
                else {
                    $this->action = $this->config['default_action'];
                }
            }
            else {
                $this->controller = $this->config['default_controller'];
                $this->action = $this->config['default_action'];
            }
        }
        else {
            // $module is considered to be a controller
            $this->module = $this->config['default_module'];
            $this->controller = $module;
            $this->action = $this->config['default_action'];
        }
        // assign left over parts to action arguments
        $this->args = $parts;
    }

    public function module()
    {
        return $this->module;
    }

    public function controller()
    {
        return $this->controller;
    }

    public function action()
    {
        return $this->action;
    }

    public function args()
    {
        return $this->args;
    }

    public function modulesPath()
    {
        return $this->modules;
    }

    public function addRoute(array $route)
    {
        throw new Exception(__METHOD__.' '._('Not yet implemented'));
    }

    private function isModule($module)
    {
        return is_dir($this->modules .'/'.$module);
    }
}
