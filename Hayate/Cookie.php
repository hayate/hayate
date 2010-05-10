<?php
/**
* Hayate Framework
* Copyright 2009 Andrea Belvedere
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


    protected function __construct()
    {
        $this->config = Hayate_Config::load('cookie');
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        reutrn self::$instance;
    }

    public function set($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
    {

    }

    public function get($name, $default, $xss_clean = true)
    {

    }

    public function delete($name, $path, $domain
}