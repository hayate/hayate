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
class Registry extends ArrayObject
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
            require_once 'HayateException.php';
            throw new HayateException(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::offsetSet($index, $newval);
    }

    public function offsetUnset($index)
    {
        if (! $this->mutable) {
            require_once 'HayateException.php';
            throw new HayateException(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::offsetUnset($index);
    }

    public function __set($index, $newval)
    {
        if (! $this->mutable) {
            require_once 'HayateException.php';
            throw new HayateException(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::__set($index, $newval);
    }

    public function append($value)
    {
        if (! $this->mutable) {
            require_once 'HayateException.php';
            throw new HayateException(sprintf(_('This %s was created immutable.'), __CLASS__));
        }
        parent::append($value);
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this)) {
            return (false === empty($this[$name]));
        }
        return false;
    }
}