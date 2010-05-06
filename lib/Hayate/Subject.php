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
abstract class Hayate_Subject implements SplSubject
{
    protected $observers;

    public function __construct()
    {
        $this->observers = array();
    }

    public function attach(Hayate_Observer $observer)
    {
        $this->observers["{$observer}"] = $observer;
    }

    public function detach(Hayatae_Observer $observer)
    {
        unset($this->observers["{$observer}"]);
    }

    abstract public function notify();
}