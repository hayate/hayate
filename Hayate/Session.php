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
        case 'database':
            $ses = Hayate_Session_Database::getInstance();
            break;
        case 'native':
            init_set('session.use_only_cookies', true);
            init_set('session.use_trans_sid', false);
            break;
        default:
            throw new Hayate_Exception(sprintf(_('Session driver: "%s" not supported.'), $driver));
        }
        if (isset($ses))
        {
            session_set_save_handler(array($ses, 'open'), array($ses, 'close'), array($ses, 'read'),
                                     array($ses, 'write'), array($ses, 'destroy'), array($ses, 'gc'));
        }

        Hayate_Event::add('hayate.shutdown', 'session_write_close');
        $this->create();
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create()
    {
        $this->destroy();
        $cookie = Hayate_Cookie::getInstance();
        $session_name = isset($this->config->session->name) ? $this->config->session->name : 'HayateSession';
        // only letters, numbers and underscore, at least one letter must be present
        if (preg_match('/^\d*[a-z][a-z0-9_]*$/i', $session_name) != 1)
        {
            throw new Hayate_Exception(sprintf(_('Invalid session name: %s'), $session_name));
        }
        // set session name
        session_name($session_name);
        // start the session
        session_start();
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

    public function regenerate()
    {
        session_regenerate_id();
    }

    public function destroy()
    {
        if ('' !== session_id())
        {
            $name = session_name();
            session_destroy();
            Hayate_Cookie::getInstance()->delete($name);
        }
    }

    // can't clone a session
    private function __clone() {}
}