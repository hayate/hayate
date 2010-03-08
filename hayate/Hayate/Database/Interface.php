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
interface Hayate_Database_Interface
{
    public function select($columns = '*');
    public function from($tables);
    public function set($field, $value = null);
    public function update($table = null, array $set = null, array $where = null);
    public function insert($table = null, array $set = null);
    public function where($field, $value = null, $quote = true);
    public function orwhere($field, $value = null, $quote = true);
    public function groupby($groupby);
    public function orderby($orderby, $direction = null);
    public function get($table = null, $limit = null, $offset = null);
    public function get_first($table = null);
}