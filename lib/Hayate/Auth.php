<?php
/**
 * Hayate Framework
 * Copyright 2009-2011 Andrea Belvedere
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
 * @package Hayate
 * @version 1.0
 */
class Hayate_Auth
{
    const TWO_WEEKS = 1209600;
    const SUCCESS = TRUE;
    const ERROR_IDENTIFIER = 10;
    const ERROR_SECRET = 20;
    const AUTHID = 'authid';

    protected static $instance = NULL;
    protected $session;
    protected $cookie;
    protected $db;
    protected $status;
    protected $identity;


    // database table name
    protected $table = 'users';
    // database fields
    protected $identifier = 'username';
    protected $secret = 'password';


    protected function __construct()
    {
        $this->session = Hayate_Session::getInstance();
        $this->cookie = Hayate_Cookie::getInstance();
        $this->db = Hayate_Database::getInstance();
        $this->status = FALSE;
        $this->identity = NULL;
    }

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $identifier i.e. the database field holding the username or email
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param string $secret i.e. the database field holding the password
     * @return void
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @parma string $table Name of table holding users
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return bool TRUE if credentials are valid, FALSE otherwise
     */
    public function authenticate($identifier, $secret, $algo = NULL, $remember = FALSE)
    {
        $this->setStatus(FALSE);

        if (is_string($algo))
        {
            $secret = hash($algo, $secret);
        }
        try {
            $this->identity = $this->db->from($this->table)->where($this->identifier, $identifier)->get();
            if (! $this->identity)
            {
                $this->setStatus(self::ERROR_IDENTIFIER);
                return FALSE;
            }
            if (0 !== strcmp($secret, $identity->{$this->secret}))
            {
                $this->setStatus(self::ERROR_SECRET);
                return FALSE;
            }
            // unset the secret
            unset($this->identity->{$this->secret});
            // store in session
            $this->session->set(self::AUTHID, $this->identity);
            if (is_numeric($remember))
            {
                $this->cookie->set(self::AUTHID, $this->identity, $remember);
            }
            $this->setStatus(self::SUCCESS);

            return TRUE;
        }
        catch (Exception $ex)
        {
            Hayate_Log::error($ex);
        }
        return FALSE;
    }

    /**
     * @return bool|mixed FALSE if there is not authenticated user, or the stored identity
     */
    public function authenticated()
    {
        if ($this->session->exists(self::AUTHID))
        {
            return $this->session->get(self::AUTHID);
        }
        else if ($this->cookie->exists(self::AUTHID))
        {
            $identity = $this->cookie->get(self::AUTHID);
            $this->session->set(self::AUTHID, $identity);
            return $identity;
        }
        return FALSE;
    }

    /**
     * @return mixed The identity (i.e. database row) if authenticated, NULL|FALSE otherwise
     */
    public function identity()
    {
        return $this->identity;
    }

    /**
     * Clear authenticated entity
     * @return void
     */
    public function clear()
    {
        $this->session->delete(self::AUTHID);
        $this->cookie->delete(self::AUTHID);
    }

    public function status()
    {
        return $this->status;
    }

    protected function setStatus($status)
    {
        switch ($status)
        {
        case self::ERROR_IDENTIFIER:
        case self::ERROR_SECRET:
        case self::SUCCESS:
            $this->status = $status;
            break;
        default:
            $this->status = FALSE;
        }
    }
}
