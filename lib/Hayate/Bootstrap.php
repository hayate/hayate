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
final class Hayate_Bootstrap
{
    private static $instance = null;

    private function __construct()
    {
        $include_path = get_include_path() . PATH_SEPARATOR;
        $include_path .= dirname(dirname(__FILE__));
        set_include_path($include_path);

        if (version_compare(PHP_VERSION, '5.1.2') < 0)
        {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(sprintf(_('Hayate requires php version >= 5.1.2, but %s installed.'), PHP_VERSION));
        }
        // if present load this application bootstrap file
        $bs = APPPATH . 'bootstrap.php';
        if (is_readable($bs)) require_once $bs;

        // TODO: register error handler

        // Hayate knows how to autoload its own classes
        if (false === spl_autoload_functions())
        {
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload');
            }
        }
        spl_autoload_register(array('Hayate_Bootstrap', 'autoload'));

        // load main config
        $config = Hayate_Config::load();

        if ($config->get('timezone', false))
        {
            date_default_timezone_set($config->core->timezone);
        }
        if ($config->get('charset', false))
        {
            mb_internal_encoding($config->core->charset);
        }
        if ($config->get('locale', false))
        {
            setLocale(LC_ALL, $config->core->locale);
        }

        /*
        // add include paths
        $include_path = get_include_path().PATH_SEPARATOR;
        $include_path .= dirname(__FILE__).PATH_SEPARATOR;
        $include_path .= dirname(__FILE__).'/Hayate'.PATH_SEPARATOR;
        $include_path .= APPPATH.PATH_SEPARATOR;
        $include_path .= APPPATH.'libs'.PATH_SEPARATOR;
        set_include_path($include_path);

        // register error handler
        set_error_handler(array($this, 'error_handler'));

        // load config files
        $this->load_configs();
        // set internal encoding
        mb_internal_encoding(Config::instance()->get('charset', 'UTF-8'));
        */
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run()
    {
        /*
        static $run;
        // it will run only once
        if (true === $run) {
            return;
        }
        $request = Request::instance();
        $dispatcher = Dispatcher::instance();
        Event::run('hayate.pre_dispatch');
        do {
            $request->dispatched(true);
            $dispatcher->dispatch();

        } while (false === $request->dispatched());

        Event::run('hayate.post_dispatch', array($dispatcher));
        $run = true;
        Event::run('hayate.shutdown');
        */
    }

    public static function autoload($classname)
    {
        $filepath = str_replace('_', DIRECTORY_SEPARATOR, $classname);

        var_dump($filepath);

        require_once $filepath . '.php';
    }

    public function error_handler($errno, $errstr, $errfile = '', $errline = 0)
    {
        $ex = new HayateException($errstr, $errno);
        $ex->setFile($errfile);
        $ex->setLine($errline);
        throw $ex;
    }

    private function load_configs()
    {
        $conf = Config::instance();
        $path = APPPATH.'config/';
        // only load files ending in .php
        $files = new RegexIterator(new DirectoryIterator($path), '/.*\.php$/i');
        foreach ($files as $file)
        {
            if ($file->isFile() && $file->isReadable())
            {
                // the last parameter is false = this config can't be modified
                $conf->load($file->getBasename('.php'), $file->getPathname(), false);
            }
        }
    }

    /**
     * @return array An array of activated modules directories paths
     * not including last forward slash
     */
    public static function modules()
    {
        static $ret;
        if (is_array($ret))
        {
            return $ret;
        }
        $config = Config::instance();
        $modules = $config->get('modules', array());
        $modules[] = $config->get('default_module', 'default');

        $ret = array();
        foreach ($modules as $module)
        {
            $path = APPPATH . 'modules/'.$module;
            if (is_dir($path))
            {
                $ret[] = $path;
            }
        }
        return $ret;
    }

    /**
     * @param string $type One of the known directories within app/modules
     */
    private static function find_file($dirname, $filename)
    {
        $config = Config::instance();
        $modules = $config->get('modules', array());
        $modules[] = $config->get('default_module', 'default');

        foreach ($modules as $module)
        {
            $filepath = APPPATH .'modules/'.$module.'/'.$dirname.'/'.$filename.'.php';
            if (is_file($filepath) && is_readable($filepath))
            {
                return $filepath;
            }
        }
        return $dirname.'/'.$filename.'.php';
    }
}
