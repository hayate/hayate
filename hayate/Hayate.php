<?php
/**
 * Hayate Framework
 * Copyright 2010 Andrea Belvedere
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
 * @version $Id: Hayate.php 39 2010-02-08 08:47:53Z andrea $
 */
final class Hayate
{
    private static $instance = null;
    private $application = false;
    private $dispatcher = 'default';
    private $autoload = true;

    private function __construct()
    {
        set_error_handler(array($this, 'error_handler'));
        // set include paths
        $include_path = get_include_path().PATH_SEPARATOR;
        $include_path .= dirname(__FILE__).PATH_SEPARATOR;
        $include_path .= APPPATH;
        set_include_path($include_path);

        require_once APPPATH.'config/config.php';
        require_once 'Hayate/Config.php';
        $reg = Hayate_Config::instance();
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
        require_once 'Hayate/Request.php';
        require_once 'Hayate/Dispatcher.php';
        $request = Hayate_Request::instance();
        $dispatcher = Hayate_Dispatcher::instance();
        
        do {
            // set the request as dispatched
            $request->dispatched(true);
            $dispatcher->dispatch();
            
        } while (false === $request->dispatched());
    }

    public function error_handler($errno, $errstr, $errfile = '', $errline = 0)
    {
        require_once 'Hayate/Exception.php';
        throw new Hayate_Exception($errstr, $errno, $errfile, $errline);
    }
}