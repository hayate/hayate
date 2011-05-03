<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 08:26:23 JST 2011
 */
require_once 'Controller.php';
require_once 'Util.php';


class Hayate
{
    private static $instance = NULL;
    private $reg;

    private function __construct($config)
    {
        $conf = new \Hayate\Util\Config($config);
        $this->reg = \Hayate\Util\Registry::getInstance();
        $this->reg->set('config', $conf);
        $this->reg->set('modules', realpath($_SERVER['DOCUMENT_ROOT'].'/'.$conf->router['modules']));

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
        $config = $this->reg->get('config');

        $dispatcher = new Hayate\Dispatcher();
        $dispatcher->dispatch(new \Hayate\Router($config->router));
    }

    /**
     * autoload controllers
     */
    private function autoload($classname)
    {
        if (strpos($classname, 'Controller') > 0)
        {
            require $this->reg->get('modules') .'/'. $this->classToPath($classname);
        }
    }

    private function classToPath($classname)
    {
        return strtolower(str_replace(array('\\','_'), DIRECTORY_SEPARATOR, $classname)).'.php';
    }
}
