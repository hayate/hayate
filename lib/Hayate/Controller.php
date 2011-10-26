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
abstract class Hayate_Controller
{
    protected $request;
    protected $input;

    public function __construct()
    {
        $this->request = Hayate_Request::getInstance();
        $this->input = Hayate_Input::getInstance();
        Hayate_Event::add('hayate.post_dispatch', array($this, '_postDispatch'));
    }

    public function _init() {}

    public function _preDispatch() {}

    public function _postDispatch() {}


    public function forward($action, $controller = null, $module = null, array $params = array())
    {
        $dispatcher = Hayate_Dispatcher::getInstance();
        $dispatcher->action($action);
        $dispatcher->controller($controller);
        $dispatcher->module($module);
        $dispatcher->params($params);
        $this->request->dispatched(false);
    }

    public function getParam($name, $default = null)
    {
        return $this->input->param($name, $default);
    }

    public function get($name = null, $default = null)
    {
        return $this->input->get($name, $default);
    }

    public function post($name = null, $default = null)
    {
        return $this->input->post($name, $default);
    }

    public function cookie($name = null, $default = null)
    {
        return $this->input->cookie($name, $default);
    }

    public function put($name = null, $default = null)
    {
        return $this->input->put($name, $default);
    }

    public function has($name, &$where = NULL)
    {
        return $this->input->has($name, $where);
    }

    public function redirect($location, $code = 302)
    {
        $this->request->redirect($location, $code);
    }

    public function isPost()
    {
        return $this->request->isPost();
    }

    public function isGet()
    {
        return $this->request->isGet();
    }

    public function isPut()
    {
        return $this->request->isPut();
    }

    public function isHead()
    {
        return $this->request->isHead();
    }

    public function isAjax()
    {
        return $this->request->isAjax();
    }

    public function refresh()
    {
        $this->request->refresh();
    }

    /**
     * This will trigger an exception if not overwritten
     */
    public function __call($method, array $args)
    {
        Hayate_Log::info(__METHOD__ . ' '. sprintf(_('method "%s" not found.'), $method));
        throw new Hayate_Exception(sprintf(_('"%s" not found.'),
                                           Hayate_URI::getInstance()->current()), 400);
    }
}
