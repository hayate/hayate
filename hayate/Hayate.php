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
 * @version 1.0
 */
final class Hayate
{
    private static $instance = null;

    private function __construct()
    {
        // set include paths
        $include_path = get_include_path().PATH_SEPARATOR;
        $include_path .= dirname(__FILE__).PATH_SEPARATOR;
        $include_path .= dirname(__FILE__).'/Hayate'.PATH_SEPARATOR;
        $include_path .= APPPATH;
        set_include_path($include_path);

	// register error handler
        set_error_handler(array($this, 'error_handler'));

	// register hayate autoload
        if (false === spl_autoload_functions())
	{
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload');
            }
        }
        spl_autoload_register(array('Hayate', 'autoload'));

	// load hayate config
        $reg = Config::instance();
        // the last parameter is false = this config can't be modified
        $reg->load('hayate', APPPATH.'config/config.php', false);
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run()
    {
        $request = Request::instance();
        $dispatcher = Dispatcher::instance();
        do {
            $request->dispatched(true);
            $dispatcher->dispatch();

        } while (false === $request->dispatched());
    }

    public static function autoload($classname)
    {
	if (false === strpos($classname, '_'))
	{
	    $filename = HAYATE.'Hayate/'.$classname.'.php';
	}
	else {
	    $segs = explode('_', $classname);
	    if (is_array($segs)) {
		switch ($segs[0]) {
		case 'Hayate':
		    $filename = HAYATE.implode('/', $segs).'.php';
		    break;
		}
	    }
	}
	if (isset($filename) && is_file($filename) && is_readable($filename))
	{
	    require_once $filename;
	}
    }

    public function error_handler($errno, $errstr, $errfile = '', $errline = 0)
    {
	Log::error($errstr);
        //require_once 'HayateException.php';
        throw new HayateException($errstr, $errno, $errfile, $errline);
    }
}