<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 08:26:23 JST 2011
 */
require_once 'Controller.php';


class Hayate
{
    private static $instance = NULL;
    private $module_dir;
    private $config;

    private function __construct(array $config)
    {
        if (isset($config['view']))
        {
            require_once 'View.php';
            Hayate\View\Config::config($config);
            unset($config['view']);
        }
        $this->module_dir = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$config['module_dir']);
        $this->config = $config;
        spl_autoload_register(array($this, 'autoload'));
    }

    public static function getInstance($config)
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self(require_once $config);
        }
        return self::$instance;
    }

    public function run()
    {
        $dispatcher = new Hayate\Dispatcher($this->config);
        $dispatcher->dispatch();
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
