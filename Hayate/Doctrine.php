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
class Hayate_Doctrine
{
    protected static $instance = null;
    protected $config;
    protected $conn;

    protected function __construct()
    {
        $this->config = Hayate_Config::load('doctrine', true);
        $doctrine_path = $this->config->get('doctrine.doctrine_dir', null, true);
        $doctrine_path .= 'Doctrine.php';
        if (! is_file($doctrine_path))
        {
            throw new Hayate_Exception(sprintf(_('Could not find doctrine path: %s'), $doctrine_path));
        }
        require_once $doctrine_path;

        // set up doctrine autoloading
        spl_autoload_register(array('Doctrine', 'autoload'));

        $dsn = $this->config->get('doctrine.dsn', null, false);
        if (empty($dsn))
        {
            throw new Hayate_Exception(_('Missing "dsn" in doctrine config file.'));
        }
        // At this point no actual connection to the database is created
        $this->conn = Doctrine_Manager::connection($dsn);

        if ($this->config->get('doctrine.models_autoload', false, false))
        {
            $this->conn->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);

            $models_dir = $this->config->get('doctrine.models_dir', false, false);
            if (is_bool($models_dir))
            {
                // models are inside modules directories
                if ($models_dir)
                {
                    $modules = array();
                    foreach (Hayate_Bootstrap::modules() as $module)
                    {
                        $modules[] = MODPATH . $module . '/models';
                    }
                    Doctrine_Core::loadModels($modules);
                }
                else {
                    Doctrine_Core::loadModels(APPPATH.'models');
                }
            }
            else if (is_array($models_dir) || is_string($models_dir))
            {
                Doctrine_Core::loadModels($models_dir);
            }
        }
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
}