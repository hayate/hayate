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
class Hayate_Session
{
    protected static $instance = null;
    protected $config;

    protected function __construct()
    {
        $this->config = Hayate_Config::load('session');
        $driver = isset($this->config->session->driver) ? $this->config->session->driver : 'cookie';
        switch ($driver)
        {
        case 'cookie':
            $ses = Hayate_Session_Cookie::getInstance();
            break;
        case 'file':
            $ses = Hayate_Session_File::getInstance();
            break;
        case 'database':
            $ses = Hayate_Session_Database::getInstance();
            break;
        default:
            throw new Hayate_Exception(sprintf(_('Session driver: "%s" not supported.'), $driver));
        }
        $ans = session_set_save_handler(array($ses, 'open'), array($ses, 'close'), array($ses, 'read'),
                                        array($ses, 'write'), array($ses, 'destroy'), array($ses, 'gc'));
        session_start();
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($name, $value = null)
    {
        if (is_array($name))
        {
            foreach ($name as $key => $value)
            {
                $this->set($key, $value);
            }
        }
        else {
            $_SESSION[$name] = $value;
        }
    }

    public function get($name = null, $default = null)
    {
        if (null === $name)
        {
            return $_SESSION;
        }
        if (isset($_SESSION[$name]))
        {
            return $_SESSION[$name];
        }
        return $default;
    }
}