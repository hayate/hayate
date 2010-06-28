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
 */
class Hayate_Database_Iterator implements Iterator, Countable
{
    protected $rows;
    protected $offset;
    protected $model;
    protected $rowCount;

    public function __construct(PDOStatement $stm, $model = null, $fetchMode = PDO::FETCH_OBJ)
    {
        $this->offset = 0;
        if (is_string($model))
        {
            $this->rows = $stm->fetchAll(PDO::FETCH_ASSOC);
            $this->model = strtolower(str_ireplace('_model', '', $model));
        }
        else {
            $this->rows = $stm->fetchAll($fetchMode);
            $this->model = null;
        }
        $this->rowCount = count($this->rows);
    }

    /**
     * @see Countable::count
     */
    public function count()
    {
        return $this->rowCount;
    }

    /**
     * @see Iterator::rewind
     */
    public function rewind()
    {
        $this->offset = 0;
    }

    /**
     * @see Iterator::current
     */
    public function current()
    {
        if ($this->model)
        {
            $orm = Hayate_ORM::factory($this->model);
            return $orm->fromArray($this->rows[$this->offset]);
        }
        return $this->rows[$this->offset];
    }

    /**
     * @see Iterator::key
     */
    public function key()
    {
        return $this->offset;
    }

    /**
     * @see Iterator::next
     */
    public function next()
    {
        ++$this->offset;
    }

    /**
     * @see Iterator::valid
     */
    public function valid()
    {
        return ($this->rowCount > $this->offset);
    }
}