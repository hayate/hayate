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
class Hayate_View_Smarty implements Hayate_View_Interface
{
    protected static $instance = null;
    protected $smarty;

    protected function __construct()
    {
        $config = Hayate_Config::getInstance();
        if (((! $config->get('view', false)) || (! isset($config->core->view['smarty_dir']))) && (! class_exists('Smarty', false)))
        {
            throw new Hayate_View_Exception(_('smarty_dir configuration parameter missing.'));
        }
        if (! class_exists('Smarty', false))
        {
            require_once rtrim($config->core->view['smarty_dir'], '\\/') . '/Smarty.class.php';
        }
        // finally we can instantiate
        $this->smarty = new Smarty();
        // and set the properties values
        $ro = new ReflectionObject($this->smarty);
        foreach ($config->core->view as $prop => $val)
        {
            if ($ro->hasProperty($prop))
            {
                $this->smarty->$prop = $val;
            }
        }
    }

    public static function getInstance()
    {
	if (null === self::$instance) {
	    self::$instance = new self();
	}
	return self::$instance;
    }

    public function get($name, $default = null)
    {
        if (null !== $this->smarty->get_template_vars($name)) {
            return $this->smarty->get_template_vars($name);
        }
        return $default;
    }

    public function set($name, $value = null)
    {
        $this->smarty->assign($name, $value);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return $this->smarty->get_template_vars($name) !== null;
    }

    public function __unset($name)
    {
        $this->smarty->clear_assign($name);
    }

    public function render($template)
    {
        $this->smarty->display($template.'.tpl');
    }

    public function fetch($template)
    {
        return $this->smarty->fetch($template.'.tpl');
    }

    public function __call($method, array $args)
    {
        try {
            $ro = new ReflectionObject($this->smarty);
            if ($ro->hasMethod($method))
	    {
                $rm = $ro->getMethod($method);
                return $rm->invokeArgs($this->smarty, $args);
            }
        }
        catch (Exception $ex) {
            throw new Hayate_View_Exception($ex->getMessage());
        }
    }
}