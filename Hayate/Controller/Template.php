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
 * @package Hayate_Controller
 * @version 1.0
 *
 * Credits for this class go to Kohana
 * @see http://kohanaphp.com/
 */
abstract class Hayate_Controller_Template extends Controller
{
    public $auto_render = true;
    protected $template = 'template.html';

    public function __construct()
    {
        parent::__construct();

        $this->template = new View($this->template);
        if (true === $this->auto_render)
        {
            Event::add('hayate.post_dispatch', array($this, '_render'));
        }
    }

    public function _render()
    {
        if (true === $this->auto_render)
        {
            $this->template->render();
        }
    }
}