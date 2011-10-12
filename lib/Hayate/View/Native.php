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
class Hayate_View_Native extends Hayate_View_Abstract implements Hayate_View_Interface
{
    protected static $instance = null;

    protected function __construct()
    {
        $include_path = rtrim(get_include_path(),PATH_SEPARATOR).PATH_SEPARATOR;
        $modules = Hayate_Bootstrap::modules();
        foreach ($modules as $module)
        {
            $viewpath = MODPATH . $module.DIRECTORY_SEPARATOR.'views';
            if (is_dir($viewpath))
            {
                $include_path .= $viewpath . PATH_SEPARATOR;
            }
        }
        set_include_path($include_path);
    }

    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($template, array $args = array())
    {
        $params = array_merge($args, $this->vars);
        extract($params, EXTR_SKIP);
        ob_start();
        try {
            require($this->template($template));
        }
        catch (Exception $ex) {
            ob_end_clean();
            throw $ex;
        }
        ob_end_flush();
    }

    public function fetch($template, array $args = array())
    {
        $params = array_merge($args, $this->vars);
        extract($params, EXTR_SKIP);
        ob_start();
        try {
            require($this->template($template));
        }
        catch (Exception $ex) {
            ob_end_clean();
            throw $ex;
        }
        return ob_get_clean();
    }

    /**
     * If present return the template in the current module
     *
     * @param string $template
     * @return string
     */
    protected function template($template)
    {
        // retrieve current module
        $module = Hayate_Dispatcher::getInstance()->module();
        $template = ltrim($template, '\//');
        $tplfile = MODPATH . $module . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template.'.php';
        if (is_file($tplfile))
        {
            return $tplfile;
        }
        return $template.'.php';
    }
}
