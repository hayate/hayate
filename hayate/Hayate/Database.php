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
    protected $fecth_mode;
    protected $query;

    // query builder
    protected $select;
    protected $from;
    protected $where;
    protected $join;
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
        $this->fetch_mode = (isset($this->config->object) && (true === $this->config->object)) ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
        $this->query = '';

        $this->select = array();
        $this->from = array();
        $this->where = array();
        $this->set = array();
        $this->distinct = false;
        $this->limit = null;
        $this->offset = null;
        $this->groupby = array();
        $this->orderby = array();
        $this->join = array();
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
     * @param int $mode One of the predefined PDO::FETCH_* modes
     */
    public function fetchMode($mode)
    {
        $this->fetch_mode = $mode;
    }

    /**
     * reset query builder's parameters
     */
    public function reset()
    {
        $this->select = array();
        $this->from = array();
        $this->where = array();
        $this->set = array();
        $this->distinct = false;
        $this->limit = null;
        $this->offset = null;
        $this->groupby = array();
        $this->orderby = array();
        $this->join = array();
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
     * @param string|array $tables If string comma separated list of tables
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

    /**
     * Join
     *
     * @param string $table Table to join
     * @param array $on fields to join on
     * @param string $type Type of JOIN i.e. INNER, LEFT, RIGHT etc.
     * @param string $opt AND or OR for multiple fields join
     *
     * @return Database
     */
    public function join($table, array $on, $type = 'INNER', $opt = 'AND')
    {
        $join = '';
        foreach ($on as $c1 => $c2)
        {
            if (mb_strlen($join) > 0) {
                $join .= ' '.trim($opt).' ';
            }
            if (! $this->has_operator($c1))
            {
                $c1 .= '=';
            }
            $join .= $c1.$c2;
        }
        $this->join[] = trim($type).' JOIN '.trim($table).' ON '.$join;
        return $this;
    }


    /**
     * Set is used for building UPDATEs and INSERTs queries
     *
     * @param string|array $field If string is a field name if array must be field => value pair
     * @param string $value Value of the field
     */
    public function set($field, $value = null)
    {
        if (is_array($field))
        {
            foreach ($field as $key => $val) {
                $this->set($key, $val);
            }
        }
        else if (is_string($field))
        {
            $this->set[$field] = $this->quote($value);
        }
        return $this;
    }

    public function limit($limit, $offset = null)
    {
        $this->limit = is_int($limit) ? $limit : null;
        $this->offset = is_int($offset) ? $offset : null;
        return $this;
    }

    /**
     * Execute an update
     *
     * @param string $table Name of the table
     * @param array $set key/value pairs of fields to set
     * @param array $where The where clause i.e. array('field' => 'value')
     */
    public function update($table = null, array $set = null, array $where = null)
    {
        if (null === $table) {
            if (! isset($this->from[0])) {
                throw new Hayate_Database_Exception(sprintf(_('Missing table name in: %s'), __METHOD__));
            }
            $table = $this->from[0];
        }
        if (null !== $set) {
            $this->set($set);
        }
        if (empty($this->set)) {
            throw new Hayate_Database_Exception(_sprintf(("Missing set in: %s"), __METHOD__));
        }
        if (null !== $where) {
            $this->where($where);
        }
        $sets = array();
        foreach ($this->set as $key => $val) {
            $sets[] = "{$key}={$val}";
        }
        $sql = "UPDATE ".$table." SET ".implode(', ', $sets)." WHERE ".implode(' ', $this->where);
        return $this->query($sql);
    }

    public function insert($table = null, array $set = null)
    {
        if (null === $table) {
            if (! isset($this->from[0])) {
                throw new Hayate_Database_Exception(sprintf(_('Missing table name in: %s'), __METHOD__));
            }
            $table = $this->from[0];
        }
        if (null !== $set) {
            $this->set($set);
        }
        if (empty($this->set)) {
            throw new Hayate_Database_Exception(_sprintf(("Missing set in: %s"), __METHOD__));
        }
        $sql = "INSERT INTO ".$table.' ('.implode(', ', array_keys($this->set)).') VALUES ('.implode(', ', array_values($this->set)).')';
        return $this->query($sql);
    }

    public function where($field, $value = null)
    {
        return $this->compile_where($field, $value, 'AND');
    }

    public function orwhere($field, $value = null)
    {
        return $this->compile_where($field, $value, 'OR');
    }

    public function groupby($groupby)
    {
        if (is_array($groupby))
        {
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
                $this->orderby[] = $column.' '.$direction;
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
            $this->from($table);
        }
        if (is_int($limit)) {
            $this->limit = $limit;
        }
        if (is_int($offset)) {
            $this->offset = $offset;
        }
        $sql = $this->compile_select();
        return $this->query($sql);
    }

    public function get_first($table = null)
    {
        return $this->get($table, 1, 0)->current();
    }

    /**
     * Prepare an sql query
     *
     *
     * @param string $query The query to prepare i.e. SELECT x FROM y WHERE z=?;
     *
     * @return PDOStatement A prepared PDOStatement object
     */
    public function prepare($query)
    {
        try {
            return $this->connect()->prepare($query);
        }
        catch (PDOException $ex) {
            throw new Hayate_Database_Exception($ex);
        }
    }

    /**
     * Execute an sql query, if values contains elements the query is
     * first prepared
     *
     * @param string $query An sql query, optionally with place holders i.e. ... VALUES (?,?,?)
     * @param array $values If not empty values are going to be interpolate into the query
     * @param PDOStatement $stm If this parameter is not null it is
     * assumed that a query is already prepared
     *
     * @return int|array If the query was a DELETE, INSERT, or UPDATE
     * the number of affected rows is returned, for SELECT query an
     * array of rows is returned with SELECTs statements the
     * developers should be aware that the whole result set is going
     * to be hold in memory
     */
    public function query($query, array $values = array(), PDOStatement $stm = null)
    {
        try {
            if (count($values) > 0)
            {
                if (null === $stm) {
                    $stm = $this->prepare($query);
                }
                $i = 1;
                foreach ($values as $value)
                {
                    switch (true)
                    {
                    case is_bool($value):
                        $stm->bindParam($i++, $value, PDO::PARAM_BOOL);
                        break;
                    case is_null($value):
                        $stm->bindParam($i++, $value, PDO::PARAM_NULL);
                        break;
                    case is_int($value):
                        $stm->bindParam($i++, $value, PDO::PARAM_INT);
                        break;
                    case is_string($value):
                        $stm->bindParam($i++, $value, PDO::PARAM_STR);
                        break;
                    default:
                        $stm->bindParam($i++, $value);
                    }
                }
                $stm->execute();
            }
            else {
                $stm = $this->connect()->query($query);
            }

            // store the query string
            $this->query = $stm->queryString;

            // log the query (info level only)
            Log::info($stm->queryString);

            // reset query builder's properties after each query
            $this->reset();
            //
            if (preg_match('/^DELETE|INSERT|UPDATE/i', $query) == 1)
            {
                return $stm->rowCount();
            }
            return new Hayate_Database_Iterator($stm, $this->fetch_mode);
        }
        catch (PDOException $ex) {
            throw new Hayate_Database_Exception($ex);
        }
        catch (Exception $ex) {
            throw new Hayate_Database_Exception($ex);
        }
    }

    public function quote($value)
    {
        switch (true)
        {
        case is_bool($value):
            return $this->connect()->quote($value, PDO::PARAM_BOOL);
        case is_null($value):
            return $this->connect()->quote($value, PDO::PARAM_NULL);
        case is_int($value):
            return $this->connect()->quote($value, PDO::PARAM_INT);
        case is_string($value):
            return $this->connect()->quote($value, PDO::PARAM_STR);
        default:
            return $this->connect()->quote($value);
        }
    }

    public function getLastQuery()
    {
        return $this->query;
    }

    /**
     * This is called by the "get" method
     */
    protected function compile_select()
    {
        $sql = ($this->distinct === true) ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= (count($this->select) > 0) ? implode(',', $this->select) : '*';
        $sql .= (count($this->from) > 0) ? ' FROM '.implode(',', $this->from) : '';
        $sql .= (count($this->join) > 0) ? ' '.implode(' ', $this->join) : '';
        $sql .= (count($this->where) > 0) ? ' WHERE '.implode(' ', $this->where) : '';
        $sql .= (count($this->groupby) > 0) ? ' GROUP BY '.implode(',', $this->groupby) : '';
        $sql .= (count($this->orderby) > 0) ? ' ORDER BY '.implode(', ', $this->orderby) : '';
        $sql .= $this->compile_limit();
        return $sql;
    }

    protected function compile_limit()
    {
        $sql = '';
        if (is_int($this->limit))
        {
            $sql .= ' LIMIT '.$this->limit;
        }
        if (is_int($this->limit) && is_int($this->offset))
        {
            $sql .= ' OFFSET '.$this->offset;
        }
        return $sql;
    }

    protected function compile_where($field, $value, $opt)
    {
        if (is_array($field))
        {
            foreach ($field as $key => $value) {
                $this->compile_where($key, $value, $opt);
            }
        }
        else {
            if (null === $value)
            {
                if (! $this->has_operator($field)) {
                    $field .= ' IS ';
                }
                $value = null;
            }
            else if (is_bool($value))
            {
                if (! $this->has_operator($field)) {
                    $field .= '=';
                }
                $value = (int)$value;
            }
            if (! $this->has_operator($field)) {
                $field .= '=';
            }
            if (count($this->where)) {
                $this->where[] = "{$opt} ".$field.$this->quote($value);
            }
            else {
                $this->where[] = $field.$this->quote($value);
            }
        }
        return $this;
    }

    protected function has_operator($opt)
    {
        return (bool)preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?|BETWEEN/i', trim($opt));
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
                Log::error($ex->errorInfo, true);
                throw new Hayate_Database_Exception($ex);
            }
            catch (Exception $ex) {
                Log::error($ex->getMessage());
                throw new Hayate_Database_Exception($ex);
            }
        }
        return $this->conn;
    }
}