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
        $driver = isset($this->config->session->driver) ? $this->config->session->driver : 'native';
        switch ($driver)
        {
        case 'database':
	    // can we get a db connection ?
	    if (null === Hayate_Database::getInstance())
	    {
		throw new Hayate_Exception(sprintf(_('%s cannot use "database" driver as it is unable'.
						     ' to retrieve a valid database connection.'), __CLASS__));
	    }
            $ses = Hayate_Session_Database::getInstance();
            session_set_save_handler(array($ses, 'open'), array($ses, 'close'), array($ses, 'read'),
                                     array($ses, 'write'), array($ses, 'destroy'), array($ses, 'gc'));
            break;
        case 'native':
            break;
        default:
            throw new Hayate_Exception(sprintf(_('Session driver: "%s" not supported.'), $driver));
        }
        Hayate_Event::add('hayate.shutdown', 'session_write_close');
        ini_set('session.use_only_cookies', true);
        ini_set('session.use_trans_sid', 0);
        session_name($this->config->get('session.name', 'HayateSession'));

	// session will not work with a domain without top level
	$domain = $this->config->get('session.domain', $_SERVER['SERVER_NAME']);
	if (preg_match('/\.?.+\..+/', $domain) != 1) $domain = '';

        session_set_cookie_params($this->config->get('session.lifetime', 0),
                                  $this->config->get('session.path', '/'),
				  $domain,
                                  $this->config->get('session.secure', false),
                                  $this->config->get('session.httponly', false));
        session_start();
        Hayate_Log::info(sprintf(_('%s initialized.'), __CLASS__));
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __get($name)
    {
	return $this->get($name);
    }

    public function __set($name, $value)
    {
	$this->set($name, $value);
    }

    public function __isset($name)
    {
	return $this->exists($name);
    }

    public function __unset($name)
    {
	$this->delete($name);
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
        if (array_key_exists($name, $_SESSION))
        {
            return $_SESSION[$name];
        }
        return $default;
    }

    public function getOnce($name = null, $default = null)
    {
        if (null === $name)
        {
            $ans = $_SESSION;
            $_SESSION = array();
            return $ans;
        }
        if (array_key_exists($name, $_SESSION))
        {
            $value = $this->get($name, $default);
	    $this->delete($name);
            return $value;
        }
        return $default;
    }

    public function exists($name)
    {
	if (array_key_exists($name, $_SESSION))
	{
	    return (null !== $_SESSION[$name]);
	}
	return false;
    }

    public function regenerate()
    {
        session_regenerate_id();
    }

    public function destroy()
    {
        session_destroy();
    }

    public function id()
    {
        return session_id();
    }

    /**
     * @param string|array $name A session key to delete or an array
     * of session keys to delete
     */
    public function delete($name)
    {
        if (is_array($name))
        {
            foreach ($name as $key) $this->delete($key);
        }
        else if (is_string($name))
        {
	    unset($_SESSION[$name]);
        }
    }

    // can't clone a session
    private function __clone() {}
}