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
class Database implements Hayate_Database_Interface
{
    protected static $instance = null;
    protected $config;
    protected $conn;

    // query builder
    protected $select;
    protected $from;
    protected $where;
    protected $set;
    protected $distinct;
    protected $limit;
    protected $offset;
    protected $groupby;
    protected $orderby;


    protected function __construct()
    {
        $this->config = Config::instance()->get('database.*', null);
        if (null === $this->config) {
            throw new Hayate_Database_Exception(_('Missing database configuration.'));
        }
        $this->conn = null;
        $this->select = array();
        $this->from = array();
        $this->distinct = false;
    }

    public function __destruct()
    {
        if (isset($this->conn)) {
            $this->conn = null;
        }
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string|array If string comma separated list of columns
     * name or a variable number of string parameters of columns name,
     * if array and array of columns name
     */
    public function select($columns = '*')
    {
        if (func_num_args() > 1) {
            $columns = func_get_args();
        }
        else if (is_string($columns)) {
            $columns = explode(',', $columns);
        }
        else if (! is_array($columns)) {
            throw new Hayate_Database_Exception(sprintf(_('parameter passed to %s must be a string or an array'), __METHOD__));
        }
        foreach ($columns as $column)
        {
            if (($colum = trim($column)) == '') continue;

            if (false !== stripos($column, 'distinct'))
            {
                $this->distinct = true;
            }
            $this->select[] = $column;
        }
        return $this;
    }

    /**
     * @param string|array If string comma separated list of tables
     * name or a variable number of string parameters of tables name,
     * if array and array of tables name
     */
    public function from($tables)
    {
        if (func_num_args() > 1) {
            $tables = func_get_args();
        }
        else if (is_string($tables)) {
            $tables = explode(',', $tables);
        }
        else if (! is_array($tables)) {
            throw new Hayate_Database_Exception(sprintf(_('parameter passed to %s must be a string or an array'), __METHOD__));
        }
        foreach ($tables as $table)
        {
            if (($table = trim($table)) == '') continue;

            if (! in_array($table, $this->from))
            {
                $this->from[] = $table;
            }
        }
        return $this;
    }

    public function set($field, $value = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $val) {
                $this->set($key, $val);
            }
        }
        else if (is_string($field)) {
            $this->set[$this->prefix($field)] = $this->escape($value, true);
        }
        return $this;
    }

    public function update($table = null, array $set = null, array $where = null)
    {
        if (null === $table) {
            if (! isset($this->from[0])) {
                throw new Hayate_Database_Exception(_('Database name of table missing in update query.'));
            }
            $table = $this->from[0];
        }
        if (null !== $set) {
            $this->set($set);
        }
        if (empty($this->set)) {
            throw new Hayate_Database_Exception(_("Database SET missing in update query."));
        }
        if (null !== $where) {
            $this->where($where);
        }
        $sets = array();
        foreach ($this->set as $key => $val) {
            $sets[] = "{$key}={$val}";
        }
        $sql = "UPDATE ".$this->table_prefix().$table." SET ".implode(', ', $sets)." WHERE ".implode(' ', $this->where);
        $this->reset();
        return $this->query($sql);
    }

    public function insert($table = null, array $set = null)
    {
        if (null === $table) {
            if (! isset($this->from[0])) {
                throw new Hayate_Database_Exception(_('Database name of table missing in insert query.'));
            }
            $table = $this->from[0];
        }
        if (null !== $set) {
            $this->set($set);
        }
        if (empty($this->set)) {
            throw new Hayate_Database_Exception(_("Database SET missing in insert query."));
        }
        $sql = "INSERT INTO ".$this->table_prefix().$table.' ('.implode(', ', array_keys($this->set)).') VALUES ('.implode(', ', array_values($this->set)).')';
        $this->reset();
        return $this->query($sql);
    }

    public function where($field, $value = null, $quote = true)
    {
        return $this->compile_where($field, $value, 'AND', $quote);
    }

    public function orwhere($field, $value = null, $quote = true)
    {
        return $this->compile_where($field, $value, 'OR', $quote);
    }

    public function groupby($groupby)
    {
        if (is_array($groupby)) {
            foreach ($groupby as $by) {
                $this->groupby[] = $by;
            }
        }
        else if (is_string($groupby)) {
            $this->groupby[] = $groupby;
        }
        return $this;
    }

    public function orderby($orderby, $direction = null)
    {
        if (is_array($orderby)) {
            foreach ($orderby as $column => $direction) {
                $direction = strtoupper(trim($direction));
                if (! in_array($direction, array('ASC','DESC','RAND()','RANDOM()','NULL'))) {
                    $direction = 'ASC';
                }
                $this->orderby[] = $this->prefix($column).' '.$direction;
            }
        }
        else {
            return $this->orderby(array($orderby => $direction));
        }
        return $this;
    }

    public function get($table = null, $limit = null, $offset = null)
    {
        if (! empty($table) && is_string($table)) {
            $this->from($this->table_prefix().$table);
        }
        if (is_int($limit)) {
            $this->limit = $limit;
        }
        if (is_int($offset)) {
            $this->offset = $offset;
        }
        $sql = $this->compile_select();
        $this->reset();
        return $this->query($sql);
    }

    public function get_first($table = null)
    {
        return $this->get($this->table_prefix().$table, 1, 0)
            ->current();
    }

    public function has_operator($opt)
    {
        return (bool)preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?|BETWEEN/i', trim($opt));
    }

    protected function compile_where($field, $value, $opt, $quote)
    {
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $this->compile_where($key, $value, $opt, $quote);
            }
        }
        else {
            if (null === $value) {
                if (! $this->has_operator($field)) {
                    $field .= ' IS';
                }
                $value = ' NULL';
            }
            else if (is_bool($value)) {
                if (! $this->has_operator($field)) {
                    $field .= ' = ';
                }
                $value = (int)$value;
            }
            if (! $this->has_operator($field)) {
                $field .= ' = ';
            }
            if (count($this->where)) {
                $this->where[] = "{$opt} ".$this->prefix($field).$this->escape($value, $quote);
            }
            else {
                $this->where[] = $this->prefix($field).$this->escape($value, $quote);
            }
        }
        return $this;
    }

    protected function connect()
    {
        if (null === $this->conn) {
            $params = array();
            if (false !== strpos($this->config->dsn, 'mysql')) {
                $params[PDO::ATTR_PERSISTENT] = (true === $this->config->persistent) ? true : false;
                $params[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = (true === $this->config->buffered) ? true : false;
            }
            try {
                $this->conn = new PDO($this->config->dsn,
                                      $this->config->username,
                                      $this->config->password,
                                      $params);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                if (isset($this->config->charset))
                {
                    $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
                    switch ($driver)
                    {
                    case 'mysql':
                    case 'pgsql':
                        $stmt = $this->conn->prepare('SET NAMES ?');
                        break;
                    case 'sqlite':
                    case 'sqlite2':
                        $stmt = $this->conn->prepare('PRAGMA encoding = ?');
                        break;
                    }
                }
                if (isset($stmt)) {
                    $ans = $stmt->execute(array($this->config->charset));
                }
            }
            catch (PDOException $ex) {
                throw new Hayate_Database_Exception($ex->getMessage(),$ex->getCode(),$ex->getFile(),$ex->getLine());
            }
            catch (Exception $ex) {
                throw new Hayate_Database_Exception($ex->getMessage(),$ex->getCode(),$ex->getFile(),$ex->getLine());
            }
        }
        return $this->conn;
    }
}