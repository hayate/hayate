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
class Hayate_Cookie
{
    protected static $instance = null;
    protected $config;
    protected $expire;
    protected $path;
    protected $domain;
    protected $secure;
    protected $httponly;
    protected $encrypt;

    const TWENTY_MINS = 1200;
    const FORTY_MINS = 2400;
    const ONE_HOUR = 3600;
    const TWO_HOURS = 7200;
    const ONE_DAY = 86400;
    const ONE_WEEK = 604800;
    const TWO_WEEKS = 1209600;
    const ONE_MONTH = 2419200;
    const FOR_EVER = 87091200;


    protected function __construct()
    {
        Hayate_Crypto::getInstance();
        $this->config = Hayate_Config::load('cookie');
        $this->encrypt = isset($this->config->cookie->encrypt) ? (bool)$this->config->cookie->encrypt : false;
        $this->expire = isset($this->config->cookie->expire) ? $this->config->cookie->expire : 0;
        $this->path = isset($this->config->cookie->path) ? $this->config->cookie->path : '/';
        $this->domain = isset($this->config->cookie->domain) ? $this->config->cookie->domain : Hayate_URI::getInstance()->hostname();
        $this->secure = isset($this->config->cookie->secure) ? (bool)$this->config->cookie->secure : false;
        $this->httponly = isset($this->config->cookie->httponly) ? (bool)$this->config->cookie->httponly : false;
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
     * @param string $name The name of this cookie
     * @param string $value The value for this cookie
     * @param integer $expire Time in seconds this cookie should be available
     * @param string $path The path within this hostname the cookie should be available
     * @param string $domain The domain where the cookie is available
     * @param bool $secure If true indicates the client that this cookie should not be sent over an unsecure connection
     * @param bool $httponly When true the cookie is only accessible via http protocol
     */
    public function set($name, $value = false, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $expire = is_null($expire) ? $this->expire : $expire;
        $path = is_null($path) ? $this->path : $path;
        $domain = is_null($domain) ? $this->domain : $domain;
        $secure = is_null($secure) ? (bool)$this->secure : $secure;
        $httponly = is_null($httponly) ? (bool)$this->httponly : $httponly;

        if ($this->encrypt)
        {
            $crypto = Hayate_Crypto::getInstance();
            $value = $crypto->encrypt($value);
        }
        $expiration = time() + $expire;
        setcookie($name, $value, $expiration, $path, $domain, $secure, $httponly);
    }

    /**
     * @param string $name The name of the cookie
     * @param mixed $default If $name is not set this value is returned
     * @param book $xss_clean If boolean it will overwrite the configuration settings (prevent xss attacts)
     * @return mixed The value of the cookie
     */
    public function get($name, $default = null, $xss_clean = null)
    {
        if (! isset($_COOKIE[$name]))
        {
            return $default;
        }
        $ans = $_COOKIE[$name];

        if ($this->encrypt)
        {
            $crypto = Hayate_Crypto::getInstance();
            $ans = $crypto->decrypt($ans);
        }

        $xss = Hayate_Config::getInstance()->get('xss_clean', false);
        if (is_bool($xss_clean))
        {
            $xss = $xss_clean;
        }
        return $xss ? htmlentities($ans, ENT_QUOTES, 'utf-8') : $ans;
    }

    /**
     * @param string $name The name of the cookie
     * @param string $path The path used when the cookie was set
     * @param string $domain The domain used when the cookie was set
     */
    public function delete($name, $path = null, $domain = null)
    {
        $path = is_null($path) ? $this->path : $path;
        $domain = is_null($domain) ? $this->domain : $domain;

        setcookie($name, '', time() - 3600, $path, $domain);
    }
}