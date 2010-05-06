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
            throw new Hayate_Exception(sprintf(_('Hayate requires PHP >= 5.1.2, but %s is installed.'), PHP_VERSION));
        }
        // if present load this application bootstrap file
        $bs = APPPATH . 'bootstrap.php';
        if (is_file($bs)) require_once $bs;

        // register error handler
        set_error_handler(array($this, 'error_handler'));

        // Hayate knows how to autoload its own classes
        if (false === spl_autoload_functions())
        {
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload');
            }
        }
        spl_autoload_register(array('Hayate_Bootstrap', 'autoload'));

        // register exception handler
        set_exception_handler(array(Hayate_Dispatcher::getInstance(), 'exceptionDispatch'));

        // load main config
        $config = Hayate_Config::load();

        if (false !== $config->get('error_reporting', false))
        {
            error_reporting($config->core->error_reporting);
        }
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
        if ($config->get('enable_hooks', false))
        {
            // if present load application hooks
            $hook = APPPATH . 'hook.php';
            if (is_file($hook))
            {
                require_once $hook;
            }
            $modules = $config->get('modules', array());
            $modules[] = $config->get('default_module', 'default');
            foreach ($modules as $module)
            {
                $hookpath = MODPATH . $module . '/hook.php';
                if (is_file($hookpath))
                {
                    require_once $hookpath;
                }
            }
        }
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
        static $run;
        // there will be only one
        if (true === $run) {
            return;
        }
        $request = Hayate_Request::getInstance();
        $dispatcher = Hayate_Dispatcher::getInstance();
        Hayate_Event::run('hayate.pre_dispatch', array($dispatcher));
        do {
            $request->dispatched(true);
            $dispatcher->dispatch();

            if ($request->dispatched())
            {
                Hayate_Event::run('hayate.post_dispatch', array($dispatcher, $request));
            }

        } while (false === $request->dispatched());

        $run = true;
        Hayate_Event::run('hayate.shutdown');
    }

    public static function autoload($classname)
    {
        $filepath = str_replace('_', DIRECTORY_SEPARATOR, $classname);
        require_once $filepath . '.php';
    }

    public function error_handler($errno, $errstr, $errfile = '', $errline = 0)
    {
        $ex = new Hayate_Exception($errstr, $errno);
        $ex->setFile($errfile);
        $ex->setLine($errline);
        throw $ex;
    }
}
