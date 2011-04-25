<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 05:32:28 JST 2011
 */
namespace Hayate;

class Exception extends \Exception {}

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

class Dispatcher
{
    protected $config;
    protected $module_dir;
    protected $path;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->module_dir = realpath($_SERVER['DOCUMENT_ROOT'] .'/'.$config['module_dir']);
        $this->path = URI::getInstance()->path();
    }

    public function dispatch()
    {
        $bits = array();
        $parts = array();
        // if is empty we use all defaults
        if (empty($this->path))
        {
            $bits = $this->findController();
        }
        else {
            $parts = preg_split('/\//', $this->path, -1, PREG_SPLIT_NO_EMPTY);
            $bits = $this->findController($parts);
        }
        if (! is_file($bits['controllerPath']))
        {
            throw new Exception(URI::getInstance()->current(), 404);
        }
        require_once $bits['controllerPath'];
        $classname = $bits['moduleName'].'\Controller\\'.$bits['controllerName'];
        $controller = new $classname();
        $action = NULL;
        if (is_callable(array($controller, $bits['actionName'])))
        {
            $action = $bits['actionName'];
        }
        else if (is_callable(array($controller, '__call')))
        {
            $args = $parts;
            unset($parts);
            $parts = array();
            $parts[] = '__call';
            $parts[] = $args;
        }
        if ($action == NULL)
        {
            throw new Exception(URI::getInstance()->current(), 404);
        }
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
    }

    /**
     * @return array With controllerPath, moduleName, controllerName, actionName
     */
    protected function findController(array &$parts = array())
    {
        if (empty($parts))
        {
            return array('moduleName' => $this->config['default_module'],
                         'controllerName' => $this->config['default_controller'],
                         'actionName' => $this->config['default_action'],
                         'controllerPath' => $this->module_dir.'/'.$this->config['default_module'].'/controller/'.
                         $this->config['default_controller'].'.php');
        }
        $ret = array();
        $module = array_shift($parts);
        if ('index.php' == $module)
        {
            // we are not using a url rewrite engine
            if (empty($parts))
            {
                return array('moduleName' => $this->config['default_module'],
                             'controllerName' => $this->config['default_controller'],
                             'actionName' => $this->config['default_action'],
                             'controllerPath' => $this->module_dir.'/'.$this->config['default_module'].'/controller/'.
                             $this->config['default_controller'].'.php');
            }
            // lets shift again
            $module = array_shift($parts);
        }
        if ($this->isModule($module))
        {
            $ret['moduleName'] = $module;
            if (count($parts) > 0)
            {
                $ret['controllerName'] = array_shift($parts);
                $ret['controllerPath'] = $this->module_dir.'/'.$module.'/controller/'.$ret['controllerName'].'.php';
                if (count($parts) > 0)
                {
                    $ret['actionName'] = array_shift($parts);
                }
                else {
                    $ret['actionName'] = $this->config['default_action'];
                }
            }
            else {
                $ret['controllerName'] = $this->config['default_controller'];
                $ret['actionName'] = $this->config['default_action'];
                $ret['controllerPath'] = $this->module_dir.'/'.$module.'/controller/'.$ret['controllerName'].'.php';
            }
        }
        else {
            // $module is considered to be a controller
            $ret['moduleName'] = $this->config['default_module'];
            $ret['controllerName'] = $module;
            $ret['actionName'] = $this->config['default_action'];
            $ret['controllerPath'] = $this->module_dir.'/'.$ret['moduleName'].'/controller/'.$ret['controllerName'].'.php';
        }
        return $ret;
    }

    private function isModule($module)
    {
        return is_dir($this->module_dir .'/'.$module);
    }
}

abstract class Controller
{
    private $template = 'template.html';

    public function __construct()
    {
        var_dump(__METHOD__);
        $this->view = NULL;
        require_once 'View.php';
        $v = new \Hayate\View('bla');
    }

    public function __set($name, $value)
    {

    }
}
