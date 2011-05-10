<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 08:26:23 JST 2011
 */
class Hayate
{
    private static $instance = NULL;
    private $reg;

    private function __construct($config)
    {
        spl_autoload_register(array($this, 'autoload'));

        $conf = new \Hayate\Config($config);
        $this->reg = \Hayate\Registry::getInstance();
        $this->reg->set('config', $conf);
        $this->reg->set('modules', realpath($_SERVER['DOCUMENT_ROOT'].'/'.$conf->router['modules']));
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
     * autoload controllers and hayate files
     */
    private function autoload($classname)
    {
        var_dump($classname);

        if (0 === stripos($classname, 'Hayate'))
        {
            $parts = preg_split('/\\\\/', $classname, -1, PREG_SPLIT_NO_EMPTY);
            require_once $parts[1].'.php';
        }
        else if (stripos($classname, 'Controller') > 0)
        {
            require $this->reg->get('modules') .'/'. $this->classToPath($classname);
        }
    }

    private function classToPath($classname)
    {
        return strtolower(str_replace(array('\\','_'), DIRECTORY_SEPARATOR, $classname)).'.php';
    }
}
