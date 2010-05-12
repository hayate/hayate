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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library. If not, see <http://www.gnu.org/licenses/>.
*/
class Hayate_Session_Cookie implements Hayate_Session_Interface
{
    protected static $instance = null;
    protected $encrypt;
    protected $config;
    protected $name;
    protected $cookie;

    protected function __construct()
    {
        $cookie_config = Hayate_Config::load('cookie');
        $this->config = Hayate_Config::load('session');
        $this->cookie = Hayate_Cookie::getInstance();
        $this->encrypt = null;
        // if we want the session encrypted, and the cookie does not
        // encrypt then we encrypt here otherwise we let the cookie class encrypt
        if ((true === $this->config->session->encrypt) && (false === $cookie_config->cookie->encrypt))
        {
            $this->encrypt = Hayate_Crypto::getInstance();
        }
        $session_name = $this->config->get('session.name', 'HayateSession');
        $this->name = $session_name.'_';
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Executed when the session is being opened
     *
     * @param string $path The save path
     * @param string $name The session name
     * @return boolean
     */
    public function open($path, $name)
    {
        Hayate_Log::info(__METHOD__.' path: '.$path);
        Hayate_Log::info(__METHOD__.' name: '.$name);
        return true;
    }

    /**
     * Executed when the session operation is done
     *
     * @return boolean
     */
    public function close()
    {
        Hayate_Log::info(__METHOD__);
        return true;
    }

    /**
     * Read session
     *
     * @return string An empty string if there is no data to read
     */
    public function read($id)
    {
        Hayate_Log::info(__METHOD__.' '.$id);
        $value = (string)$this->cookie->get($this->name, '');
        if (empty($value)) return '';

        if ($this->encrypt)
        {
            $value = $this->encrypt->decrypt($value);
        }
        return $value;
    }

    /**
     * Write session, executed after the output stream is closed
     *
     * @param string $id Session id
     * @param string $data Session data
     * @return boolean
     */
    public function write($id, $data)
    {
        Hayate_Log::info(__METHOD__.' '.$id);
        if ($this->encrypt)
        {
            $data = $this->encrypt->encrypt($data);
        }
        if (mb_strlen($data) > 4048)
        {
            Hayate_Log::error(sprintf(_('Session "%s" exceeded the 4KB size limit.'), $id));
            return false;
        }
        $expire = $this->config->get('session.expire', null);

        $this->cookie->set($this->name, $data, $expire);
        return true;
    }

    /**
     * Executed when the session is destroyed
     *
     * @param string $id The Session id
     * @return boolean
     */
    public function destroy($id)
    {
        Hayate_Log::info(__METHOD__.' '.$id);
        $this->cookie->delete($this->name);
        return true;
    }

    /**
     * @param integer $maxlifetime Max session lifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        Hayate_Log::info(__METHOD__);
        return true;
    }
}