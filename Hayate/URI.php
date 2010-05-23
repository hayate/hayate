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
 * @package Hayate
 */
class Hayate_URI
{
    protected static $instance = null;
    protected $current;
    protected $config;
    protected $segments;

    protected function __construct()
    {
        $this->config = Hayate_Config::getInstance();
        $this->current = $this->scheme().'://'.
            $this->hostname().'/'.$this->path().$this->query(true);
        $this->segments = preg_split('|/|', $this->path(), -1, PREG_SPLIT_NO_EMPTY);
        // make sure all segments are lower case
        $this->segments = array_map('mb_strtolower', $this->segments);
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function segments()
    {
        return $this->segments;
    }

    /**
     * When numerical index are passed, 0 is the first segment
     *
     * @param $seg int|string If int is the index of the segment path to return
     * if is string it will return the next segment
     */
    public function segment($seg = 0)
    {
        if (is_numeric($seg) && isset($this->segments[$seg])) {
            return $this->segments[$seg];
        }
        else if (is_string($seg))
        {
            while (false !== ($param = current($this->segments)))
            {
                if ($param == $seg)
                {
                    if (false !== ($ans = next($this->segments))) {
                        reset($this->segments);
                        return $ans;
                    }
                }
                next($this->segments);
            }
            reset($this->segments);
        }
        return null;
    }

    public function current()
    {
        return $this->current;
    }

    public function path()
    {
        $path = '';
        switch (true)
        {
        case isset($_SERVER['REQUEST_URI']):
            $path = $_SERVER['REQUEST_URI'];
            break;
        case isset($_SERVER['PATH_INFO']):
            $path = $_SERVER['PATH_INFO'];
            break;
        case isset($_SERVER['ORIG_PATH_INFO']):
            $path = $_SERVER['ORIG_PATH_INFO'];
            break;
        }
        return trim($path, '/');
    }

    public function query($question_mark = false)
    {
        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        return empty($query) ? '' : (($question_mark) ? '?'.$query : $query);
    }

    /**
     * TODO: check port issue
     */
    public function hostname()
    {
        $port = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '';
        if (isset($_SERVER['SERVER_NAME']) && strlen($_SERVER['SERVER_NAME']) > 0)
        {
            return $_SERVER['SERVER_NAME'].$port;
        }
        $hostname = $this->config->get('hostname', '');
        if (is_string($hostname) && strlen($hostname) > 0)
        {
            return $hostname.$port;
        }
        if (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST']) > 0)
        {
            return $_SERVER['HTTP_HOST'].$port;
        }
        throw new Hayate_Exception(_('A valid host name could not be determined.'));
    }

    public function scheme()
    {
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && ('off' != $_SERVER['HTTPS']))
        {
            return 'https';
        }
        return 'http';
    }

    public function __toString()
    {
        return $this->current;
    }
}