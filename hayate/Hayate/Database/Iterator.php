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
 * @package Hayate_Database
 * @version 1.0
 */
class Hayate_Database_Iterator implements Iterator, Countable, ArrayAccess
{
    protected $iterator;

    public function __construct(PDOStatement $stm, $fetch_mode)
    {
        $this->iterator = new ArrayIterator($stm->fetchAll($fetch_mode));
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @see Countable::count()
     */
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * @see ArrayAccess::offsetSet($offset, $value)
     */
    public function offsetSet($offset, $value)
    {
        throw new HayateException(sprintf(_('%s is immutable'), __CLASS__));
    }

    /**
     * @see ArrayAccess::offsetExists($offset)
     */
    public function offsetExists($offset)
    {
        return $this->iterator->offsetExists($offset);
    }

    /**
     * @see ArrayAccess::offsetUnset($offset)
     */
    public function offsetUnset($offset)
    {
        throw new HayateException(sprintf(_('%s is immutable'), __CLASS__));
    }

    /**
     * @see ArrayAccess::offsetGet($offset)
     */
    public function offsetGet($offset)
    {
        return $this->iterator->offsetGet($offset);
    }
}