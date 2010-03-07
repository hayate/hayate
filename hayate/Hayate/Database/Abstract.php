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
abstract class Hayate_Database_Abstract implements Hayate_Database_Interface
{
    protected $dsn;
    protected $user;
    protected $pword;

    public function __construct($dsn, $user = null, $pword = null)
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->pword = $pword;
    }

    abstract function connect();
    abstract function setCharset();


    public function from($table)
    {

    }

    public function where($field, $value = null)
    {

    }

    public function join($table, $field, $value = null)
    {

    }

    public function groupby($field)
    {

    }

    public function orderby($field, $direction)
    {

    }

    public function limit($offset, $count = null)
    {

    }

    public function find()
    {

    }

    public function findAll($offset = 0, $count = null)
    {

    }

    public function query($query, $params)
    {

    }

    public function insert($table, $fields, $values)
    {

    }

    public function update($table, $fields, $values)
    {

    }
}