<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 08:26:23 JST 2011
 */
require_once 'Controller.php';

use Hayate\Config;
use Hayate\Router;


class Hayate
{
    private static $instance = NULL;
    private $module_dir;
    private $config;

    private function __construct($config)
    {
        require_once 'Config.php';
        $this->config = new Config($config);

        $this->module_dir = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$this->config->module_dir);
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * @param string $config Path to a configuration file returning an array
     */
    public static function getInstance($config)
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function run()
    {
        $router = Router::getInstance();
        $router->setConfig($this->config);
        $router->route($this->module_dir);

        $dispatcher = new Hayate\Dispatcher();
        $dispatcher->dispatch($router);
    }

    private function autoload($classname)
    {
        if (strpos($classname, 'Controller') > 0)
        {
            require $this->module_dir .'/'. $this->classToPath($classname);
        }
    }

    private function classToPath($classname)
    {
        return strtolower(str_replace(array('\\','_'), DIRECTORY_SEPARATOR, $classname)).'.php';
    }
}
