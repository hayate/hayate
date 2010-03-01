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
abstract class Controller
{
    protected $request;
    protected $input;
    protected $params;

    public function __construct()
    {
	$this->request = Request::instance();
	$this->input = Input::instance();
	$this->params = array_merge($this->input->get(),$this->input->post());
    }

    public function forward($action, $controller = null, $module = null, array $params = array())
    {
	$dispatcher = Dispatcher::instance();
	$dispatcher->action($action);
	$dispatcher->controller($controller);
	$dispatcher->module($module);
	$this->params = array_merge($this->params, $params);
	$this->request->dispatched(false);
    }

    public function getParam($name, $default = null)
    {
	return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
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

    public function redirect($location, $code = 302)
    {
	$this->request->redirect($location, $code);
    }

    public function refresh()
    {
	$this->request->refresh();
    }

    /**
     * This will trigger a 404 if not overwritten
     */
    public function __call($method, array $args)
    {
	Log::info(__METHOD__);
	throw new HayateException('Not Found', 404);
    }
}