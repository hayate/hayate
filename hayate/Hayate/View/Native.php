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
 * @package Hayate_View
 * @version 1.0
 */
class Hayate_View_Native implements Hayate_View_Interface
{
    protected static $instance = null;
    protected $vars;

    protected function __construct()
    {
        $this->vars = array();
        $include_path = rtrim(get_include_path(),PATH_SEPARATOR).PATH_SEPARATOR;
        $dirinfo = new DirectoryIterator(APPPATH.'modules/');
        foreach ($dirinfo as $info)
	{
            if ($info->isDir() && !$info->isDot())
	    {
                $viewpath = $info->getPathname().DIRECTORY_SEPARATOR.'views';
                if (is_dir($viewpath)) {
                    $include_path .= $viewpath . PATH_SEPARATOR;
                }
            }
        }
        set_include_path($include_path);
    }

    public static function instance()
    {
	if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        return $default;
    }

    public function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                if (!empty($k)) {
                    $this->vars[$k] = $v;
                }
            }
        }
        else if (!empty($name)) {
            $this->vars[$name] = $value;
        }
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
        return isset($this->vars[$name]);
    }

    public function __unset($name)
    {
        unset($this->vars[$name]);
    }

    public function render($template)
    {
	extract($this->vars);
	ob_start();
	require_once($template.'.php');
	ob_end_flush();
    }

    public function fetch($template)
    {
	extract($this->vars);
	ob_start();
	require_once($template.'.php');
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
    }
}