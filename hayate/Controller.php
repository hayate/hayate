<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 05:32:28 JST 2011
 */
namespace Hayate {

    class Exception extends \Exception {}


    abstract class Event
    {
        protected $events = array();

        public function register($name, $callback, array $args = array(), &$ret = NULL)
        {
            $event = new \stdClass();
            $event->callback = $callback;
            $event->args = $args;
            $event->ret = $ret;
            $this->events[$name][] = $event;
        }

        public function unregister($name)
        {
            if (isset($this->events[$name]))
            {
                unset($this->events[$name]);
            }
        }

        public function fire($name)
        {
            if (isset($this->events[$name]))
            {
                for ($i = 0; $i < count($this->events[$name]); $i++)
                {
                    $event = $this->events[$name][$i];
                    switch (count($event->args))
                    {
                    case 0:
                        $event->ret = call_user_func($event->callback);
                        break;
                    case 1:
                        $event->ret = call_user_func($event->callback, $event->args[0]);
                        break;
                    case 2:
                        $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1]);
                        break;
                    case 3:
                        $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1], $event->args[2]);
                        break;
                    case 4:
                        $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1], $event->args[2], $event->args[3]);
                        break;
                    case 5:
                        $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1], $event->args[2], $event->args[3], $event->args[4]);
                        break;
                    default:
                        $event->ret = call_user_func_array($event->callback, $event->args);
                    }

                }
                unset($this->events[$name]);
            }
        }
    }

    class URI
    {
        protected $scheme;
        protected $hostname;
        protected $port;
        protected $path;
        protected $query;
        protected $current;

        protected static $instance = NULL;


        protected function __construct()
        {
            $this->current = $this->scheme().'://'.$this->hostname();
            $this->current .= strlen($this->port()) ? ':'.$this->port() : '';
            $this->current .= '/'.$this->path();
            $this->current .= mb_strlen($this->query(), 'UTF-8') ? '?'.$this->query() : '';
        }

        public static function getInstance()
        {
            if (NULL === self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function scheme()
        {
            if (isset($this->scheme)) return $this->scheme;

            $this->scheme = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && ('off' != $_SERVER['HTTPS'])) ? 'https' : 'http';
            return $this->scheme;
        }

        public function hostname()
        {
            if (isset($this->hostname)) return $this->hostname;

            $this->hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] :
                isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : isset($_SERVER['HTTP_HOST']) ?
                $_SERVER['HTTP_HOST'] : '';

            return $this->hostname;
        }

        public function port()
        {
            if (isset($this->port)) return $this->port;

            $this->port = isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != '80') ? $_SERVER['SERVER_PORT'] : '';
            return $this->port;
        }

        public function path()
        {
            if (isset($this->path)) return $this->path;

            switch (true)
            {
            case isset($_SERVER['REQUEST_URI']):
                $this->path = $_SERVER['REQUEST_URI'];
                // make sure there is no query part
                $this->path = str_replace('?'.$_SERVER['QUERY_STRING'], '', $this->path);
                break;
            case isset($_SERVER['PATH_INFO']):
                $this->path = $_SERVER['PATH_INFO'];
                break;
            case isset($_SERVER['ORIG_PATH_INFO']):
                $this->path = $_SERVER['ORIG_PATH_INFO'];
                break;
            }
            $this->path = trim($this->path, '/');
            return $this->path;
        }

        public function query()
        {
            if (isset($this->query)) return $this->query;

            $this->query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
            return $this->query;
        }

        public function current()
        {
            return $this->current;
        }
    }

    class Request
    {

    }

    /**
     * Maps URI path to a model, controller and action
     * fires the Routed event when done
     */
    class Router
    {
        protected static $instance = NULL;

        protected $module_dir;
        protected $path;
        protected $routes; // not yet used

        protected $module;
        protected $controller;
        protected $action;
        protected $args;

        protected $default_module;
        protected $default_controller;
        protected $default_action;


        protected function __construct()
        {
            $this->module_dir = realpath($_SERVER['DOCUMENT_ROOT'] .'/'.$config['module_dir']);
            $this->path = URI::getInstance()->path();
            $this->args = array(); // action arguments
        }

        public static function getInstance()
        {
            if (NULL === self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function route($module_dir)
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

        public function modulePath()
        {
            return $this->module_dir;
        }

        public function addRoute(array $route)
        {
            throw new Exception(__METHOD__.' '._('Not yet implemented'));
        }

        private function isModule($module)
        {
            return is_dir($this->module_dir .'/'.$module);
        }
    }

    /**
     * Finishes off the job started by the router,
     * it dispatches the request to the model/controller/action
     * assigned by the router
     * it fires Dispatched event when done
     */
    class Dispatcher
    {
        const Dispatched = 'Dispatched';

        public function __construct() {}


        public function dispatch(Router $router)
        {
            $controllerPath = $router->modulePath() .'/'. $router->module() .'/controller/'. $router->controller() .'.php';
            if (! is_file($controllerPath))
            {
                throw new Exception(URI::getInstance()->current(), 404);
            }

            require_once $controllerPath;
            $classname = $router->module().'\Controller\\'.$router->controller();
            $controller = new $classname();
            $action = $router->action();
            $parts = $router->args();

            switch (count($parts))
            {
            case 0:
                $controller->$action();
                break;
            case 1:
                $controller->$action($parts[0]);
                break;
            case 2:
                $controller->$action($parts[0], $parts[1]);
                break;
            case 3:
                $controller->$action($parts[0], $parts[1], $parts[2]);
                break;
            case 4:
                $controller->$action($parts[0], $parts[1], $parts[2], $parts[3]);
                break;
            case 5:
                $controller->$action($parts[0], $parts[1], $parts[2], $parts[3], $pargs[4]);
                break;
            default:
                // all right then
                call_user_func_array(array($controller, $action), $parts);
            }
            $controller->fire(Dispatcher::Dispatched);
        }
    }

    abstract class Controller extends Event
    {
        public function __construct()
        {
            var_dump(__METHOD__);
            $this->register(Dispatcher::Dispatched, array($this, 'dispatched'));
        }
        protected function dispatched() {}
    }
}

namespace Hayate\View {

    abstract class Controller extends \Hayate\Controller
    {
        protected $template = 'template.html';
        protected $render = FALSE;

        public function __construct()
        {
            parent::__construct();
            require_once 'View.php';

            if (TRUE === $this->render)
            {
                $this->template = new \Hayate\View($this->template);
                $this->register(\Hayate\Dispatcher::Dispatched, array($this, 'render'));
            }
        }

        protected function render()
        {
            if ($this->render)
            {
                var_dump('Rendering: '.__METHOD__);
            }
        }
    }
}

