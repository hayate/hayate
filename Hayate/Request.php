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
class Hayate_Request
{
    protected static $instance = null;
    protected $dispatch;
    protected $method;

    protected function __construct()
    {
        $this->dispatch = false;
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function dispatched($dispatch = null)
    {
        if (is_bool($dispatch)) {
            $this->dispatch = $dispatch;
        }
        return $this->dispatch;
    }

    public function method()
    {
        return $this->method;
    }

    public function isPost()
    {
        return $this->method == 'post';
    }

    public function isGet()
    {
        return $this->method == 'get';
    }

    public function isPut()
    {
        return $this->method == 'put';
    }

    public function isHead()
    {
        return $this->method == 'head';
    }

    public function isAjax()
    {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Note: only http status 302 supported
     * Note: if http schema is not present, the given location is
     * assumend to be just a path and the schema + this hostname will
     * be prepended
     */
    public function redirect($location, $code = 302)
    {
	if (false === stripos($location, 'http', 0))
	{
	    $path = ltrim($location, '/');
	    $location = 'http://'.Hayate_URI::getInstance()->hostname();
	    $location .= '/'.$path;
	}
        header('Location: '.$location);
        if ($this->method() != 'head') {
            exit('<h1>'.$code.' - Found</h1><p><a href="'.$location.'">'.$location.'</a>');
        }
	exit();
    }

    public function refresh()
    {
        $this->redirect(Hayate_URI::getInstance()->current(), 302);
    }
}