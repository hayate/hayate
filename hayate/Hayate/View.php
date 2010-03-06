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
class View
{
    protected $view;
    protected $template;

    public function __construct($template)
    {
        $this->view = self::factory();
        $this->template = $template;
    }

    /**
     * display templates
     */
    public function render()
    {
        $this->view->render($this->template);
    }

    /**
     * return templates
     */
    public function fetch()
    {
        return $this->view->fetch($this->template);
    }

    public function get($name, $default = null)
    {
        return $this->view->get($name, $default);
    }

    public function set($name, $value = null)
    {
        $this->view->set($name, $value);
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
        return $this->view->__isset($name);
    }

    public function __toString()
    {
        return $this->fetch();
    }

    public function __unset($name)
    {
        $this->view->__unset($name);
    }

    protected static function factory()
    {
        $config = Config::instance()->get('view', 'native');

        if (isset($config['name']))
        {
            switch ($config['name']) {
            case 'smarty':
                return Hayate_View_Smarty::instance();
            case 'native':
                return Hayate_View_Native::instance();
            default:
                throw new Hayate_View_Exception(_('Supported views are "smarty" and "native".'));
            }
        }
        throw new Hayate_View_Exception(_('View name is missing.'));
    }
}