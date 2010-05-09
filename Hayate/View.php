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
 * @version 1.0
 */
class Hayate_View
{
    protected $view;
    protected $template;
    protected $vars;

    public function __construct($template)
    {
        $this->view = self::factory();
        $this->template = $template;
        $this->vars = array();
    }

    /**
     * display templates
     */
    public function render()
    {
        $this->view->render($this->template, $this->vars);
    }

    /**
     * return templates
     */
    public function fetch()
    {
        return $this->view->fetch($this->template, $this->vars);
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

    public function __toString()
    {
        try {
            return $this->fetch();
        }
        catch (Exception $ex) {
            restore_error_handler();
            trigger_error($ex->getMessage(), E_USER_ERROR);
            set_error_handler(array(Hayate_Bootstrap::getInstance(), 'error_handler'));
            return '';
        }
    }

    protected static function factory()
    {
        $config = Hayate_Config::getInstance()->get('view', array('name' => 'native'));

        if (isset($config['name']))
        {
            switch ($config['name']) {
            case 'smarty':
                return Hayate_View_Smarty::getInstance();
            case 'native':
                return Hayate_View_Native::getInstance();
            default:
                throw new Hayate_View_Exception(_('Supported views are "smarty" and "native".'));
            }
        }
        throw new Hayate_View_Exception(_('View name is missing.'));
    }
}