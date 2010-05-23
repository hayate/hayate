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
 */
class Hayate_View_Smarty extends Hayate_View_Abstract implements Hayate_View_Interface
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
        if (version_compare($this->smarty->_version, '3.0') < 0)
        {
            $this->is_smarty_2 = true;
        }
    }

    public static function getInstance()
    {
	if (null === self::$instance) {
	    self::$instance = new self();
	}
        self::$instance->clearAllAssign();
	return self::$instance;
    }

    public function render($template, array $args = array())
    {
        $this->smarty->assign($args);
        $this->smarty->display($template.'.tpl');
    }

    public function fetch($template, array $args = array())
    {
        $this->smarty->assign($args);
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