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
class Hayate_Database_Iterator implements Iterator, Countable
{
    protected $stm;
    protected $pdo;
    protected $offset;
    protected $model;
    protected $fetch_mode;
    protected $row;

    public function __construct(PDOStatement $stm, Hayate_Database_Pdo $pdo, $model = null)
    {
        $this->stm = $stm;
        $this->pdo = $pdo;
        $this->row = false;

        if (is_string($model))
        {
            $this->fetch_mode = PDO::FETCH_INTO;
            $this->model = strtolower(str_ireplace('model_', '', $model));
        }
        else {
            $this->fetch_mode = $pdo->fetch_mode;
            $this->stm->setFetchMode($pdo->fetch_mode);
        }
        $this->rewind();
    }

    public function __destruct()
    {
        $this->stm->closeCursor();
    }

    /**
     * @see Countable::count
     */
    public function count()
    {
        $match = array();
        if (preg_match('/^select(.+)from.+/i', $this->stm->queryString, $match) != 1)
        {
            return $this->stm->rowCount();
        }
        $count_query = preg_replace('/'.preg_quote($match[1]).'/', ' COUNT(*) ', $this->stm->queryString, 1);
        $stm = $this->pdo->query($count_query);
        return $stm->fetchColumn();
    }

    /**
     * @see Iterator::rewind
     */
    public function rewind()
    {
        $this->offset = 0;
        if ($this->fetch_mode == PDO::FETCH_INTO)
        {
            $this->row = ORM::factory($this->model);
            $this->stm->setFetchMode(PDO::FETCH_INTO, $this->row);
            $this->row = $this->stm->fetch(PDO::FETCH_INTO);
        }
        else {
            $this->row = $this->stm->fetch($this->fetch_mode, PDO::FETCH_ORI_FIRST);
        }
    }

    /**
     * @see Iterator::current
     */
    public function current()
    {
        return $this->row;
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
        if ($this->fetch_mode == PDO::FETCH_INTO)
        {
            $this->row = ORM::factory($this->model);
            $this->stm->setFetchMode(PDO::FETCH_INTO, $this->row);
            $this->row = $this->stm->fetch(PDO::FETCH_INTO);
        }
        else {
            $this->row = $this->stm->fetch($this->fetch_mode, PDO::FETCH_ORI_NEXT);
        }
        ++$this->offset;
    }

    /**
     * @see Iterator::valid
     */
    public function valid()
    {
        return ($this->row !== false);
    }
}