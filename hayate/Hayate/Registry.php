<?php
/**
 * Hayate Framework
 * Copyright 2010 Andrea Belvedere
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
 * @version $Id: Registry.php 38 2010-02-07 12:45:40Z andrea $
 */
class Hayate_Registry extends ArrayObject
{
    protected $mutable;

    public function __construct($input, $mutable = false, $flags = ArrayObject::ARRAY_AS_PROPS, $iterator_class = 'ArrayIterator')
    {
        parent::__construct($input, $flags, $iterator_class);
        $this->mutable = $mutable;
    }

    public function offsetSet($index, $newval)
    {
        if (! $this->mutable) {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::offsetSet($index, $newval);
    }

    public function offsetUnset($index)
    {
        if (! $this->mutable) {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::offsetUnset($index);
    }

    public function __set($index, $newval)
    {
        if (! $this->mutable) {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::__set($index, $newval);
    }

    public function append($value)
    {
        if (! $this->mutable) {
            require_once 'Hayate/Exception.php';
            throw new Hayate_Exception(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::append($value);
    }
}