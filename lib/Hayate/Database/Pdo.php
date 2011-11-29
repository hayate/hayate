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
class Hayate_Database_Pdo extends PDO
{
    protected $fetchMode;
    protected $lastQuery;

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


    public function __construct(array $config)
    {
        $this->fetchMode = (isset($config['object']) && (true === $config['object'])) ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
        $this->reset();

        // sanity checks
        if (! isset($config['dsn']) || empty($config['dsn']) || !is_string($config['dsn']))
        {
            throw new Hayate_Database_Exception(_('Missing or invalid dsn field in database configuration file.'));
        }
        $params = array();
        if(isset($config['timeout']) && is_numeric($config['timeout']))
        {
            $params[PDO::ATTR_TIMEOUT] = (int) $config['timeout'];
        }
        if(false !== stripos($config['dsn'], 'mysql'))
        {
            $params[PDO::ATTR_PERSISTENT] = (true === $config['persistent']) ? true : false;
            $params[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = (true === $config['buffered']) ? true : false;
        }
        try {
            $username = isset($config['username']) ? $config['username'] : null;
            $password = isset($config['password']) ? $config['password'] : null;

            parent::__construct($config['dsn'], $username, $password, $params);

            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (isset($config['charset']))
            {
                $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
                switch ($driver)
                {
                case 'mysql':
		    $stmt = $this->prepare("SET NAMES ?");
		    break;
                case 'pgsql':
                    $stmt = $this->prepare("SET NAMES '?'");
		    break;
                case 'sqlite':
                case 'sqlite2':
                    $stmt = $this->prepare("PRAGMA encoding='?'");
		    break;
                }
            }
            if (isset($stmt)) {
                $stmt->execute(array($config['charset']));
            }
        }
        catch (PDOException $ex) {
            Hayate_Log::error($ex->errorInfo, true);
            throw new Hayate_Database_Exception($ex);
        }
        catch (Exception $ex) {
            Hayate_Log::error($ex->getMessage());
            throw new Hayate_Database_Exception($ex);
        }
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
            if (! $this->hasOperator($c1))
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
            foreach ($field as $key => $val)
	    {
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
        $this->limit = is_numeric($limit) ? (int)$limit : null;
        $this->offset = is_numeric($offset) ? (int)$offset : null;
        return $this;
    }

    /**
     * Execute an update
     *
     * @param string $table Name of the table
     * @param array $set key/value pairs of fields to set
     * @param array $where The where clause i.e. array('field' => 'value')
     *
     * @return int Returns the number of affected rows
     */
    public function update($table = null, array $set = null, array $where = null)
    {
        if (null === $table)
        {
            if (! isset($this->from[0]))
            {
                throw new Hayate_Database_Exception(sprintf(_('Missing table name in: %s'), __METHOD__));
            }
            $table = $this->from[0];
        }
        if (null !== $set)
        {
            $this->set($set);
        }
        if (empty($this->set))
        {
            throw new Hayate_Database_Exception(_sprintf(("Missing set in: %s"), __METHOD__));
        }
        if (null !== $where)
        {
            $this->where($where);
        }
        $sets = array();
        foreach ($this->set as $key => $val)
        {
            $sets[] = "`{$key}`={$val}";
        }
        $sql = "UPDATE ".$table." SET ".implode(', ', $sets)." WHERE ".implode(' ', $this->where);
        return $this->exec($sql);
    }

    public function exec($sql)
    {
	$this->reset();
	return parent::exec($sql);
    }

    public function query($sql)
    {
        $this->reset();
        $argc = func_num_args();
        switch ($argc)
        {
        case 2:
            $mode = func_get_arg(1);
            return parent::query($sql, $mode);
        case 3:
            $mode = func_get_arg(1);
            $arg3 = func_get_arg(2);
            return parent::query($sql, $mode, $arg3);
        case 4:
            $mode = func_get_arg(1);
            $arg3 = func_get_arg(2);
            $arg4 = func_get_arg(3);
            return parent::query($sql, $mode, $arg3, $arg4);
        default:
            return parent::query($sql);
        }
    }

    /**
     * Execute an insert
     *
     * @param string $table The table name
     * @param array $set associative array of columns and values to insert
     *
     * @return int Returns the number of affected rows
     */
    public function insert($table = null, array $set = null)
    {
        if (null === $table)
        {
            if (! isset($this->from[0]))
            {
                throw new Hayate_Database_Exception(sprintf(_('Missing table name in: %s'), __METHOD__));
            }
            $table = $this->from[0];
        }
        if (null !== $set)
        {
            $this->set($set);
        }
        if (empty($this->set))
        {
            throw new Hayate_Database_Exception(_sprintf(("Missing set in: %s"), __METHOD__));
        }
        $keys = array_keys($this->set);
        array_walk($keys, function(&$key) {
		$key = "`".$key."`";
	    });
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', array_values($this->set)).')';
        return $this->exec($sql);
    }

    /**
     * Execute a delete
     *
     * @param string $table The table name
     * @param array $where Key/Value pair identifying the rows to delete
     *
     * @return int Returns the number of affected rows
     */
    public function delete($table = null, array $where = array())
    {
        if (null === $table)
        {
            if (! isset($this->from[0]))
            {
                throw new Hayate_Database_Exception(sprintf(_('Missing table name in: %s'), __METHOD__));
            }
            $table = $this->from[0];
        }
        $sql = 'DELETE FROM '.$table;
        if (count($where))
        {
            $this->where($where);
            $sql .= ' WHERE '.implode(' ', $this->where);
        }
        return $this->exec($sql);
    }

    public function count($table = null, array $where = array())
    {
        if (null === $table)
        {
            if (! isset($this->from[0]))
            {
                throw new Hayate_Database_Exception(sprintf(_('Missing table name in: %s'), __METHOD__));
            }
            $table = $this->from[0];
        }
        $sql = 'SELECT COUNT(*) FROM '.$table;
        if (count($where))
        {
            $this->where($where);
            $sql .= ' WHERE '.implode(' ', $this->where);
        }
        else if (count($this->where))
        {
            $sql .= ' WHERE '.implode(' ', $this->where);
        }
        try {
            $ret = $this->query($sql, self::FETCH_COLUMN, 0)
		->fetch(self::FETCH_NUM);
            return $ret[0];
        }
        catch (Exception $ex) {
            Hayate_Log::error("{$ex}");
        }
        return 0;
    }

    public function where($field, $value = null)
    {
        return $this->compileWhere($field, $value, 'AND');
    }

    public function orwhere($field, $value = null)
    {
        return $this->compileWhere($field, $value, 'OR');
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
        if (is_array($orderby))
        {
            foreach ($orderby as $column => $direction)
            {
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

    public function distinct($value = true)
    {
        $this->distinct = (bool)$value;
    }

    public function getAll($table = null, $model = null, $limit = null, $offset = null)
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
        $sql = $this->compileSelect();
        return $this->execute($sql, array(), $model);
    }

    /**
     * @return mixed Returns false if the record is not found
     */
    public function get($table = null, $model = null)
    {
        if (! empty($table) && is_string($table)) {
            $this->from($table);
        }
        $this->limit = 1;
        $this->offset = 0;
        $sql = $this->compileSelect();

        $mode = $this->fetchMode;
        try {
            switch (true)
            {
            case is_string($model):
                $mode = PDO::FETCH_CLASS;
                $stm = $this->query($sql, $mode, $model, array());
                break;
            case ($model instanceof Hayate_ORM):
                $mode = PDO::FETCH_INTO;
                $stm = $this->query($sql, $mode, $model);
                break;
            default:
                $stm = $this->query($sql);
            }
            $ret = $stm->fetch($mode);
            $stm->closeCursor();
            return $ret;
        }
        catch (PDOException $ex) {
            throw new Hayate_Database_Exception($ex);
        }
        catch (Exception $ex) {
            throw new Hayate_Database_Exception($ex);
        }
    }

    /**
     * Execute an sql query, if values contains elements the query is
     * first prepared
     *
     * @param string $query An sql query, optionally with place holders i.e. ... VALUES (?,?,?)
     * @param array $values If not empty values are going to be interpolate into the query
     * @param ORM|string $model If not null must be a model classname or model object
     *
     * @return int|array If the query was a DELETE, INSERT, or UPDATE
     * the number of affected rows is returned, for SELECT query an
     * array of rows is returned with SELECTs statements the
     * developers should be aware that the whole result set is going
     * to be hold in memory
     */
    public function execute($query, array $values = array(), $model = null)
    {
        try {
            $stm = $this->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

            if (count($values) > 0)
            {
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
            }
            $stm->execute();

            // store the query string
            $this->lastQuery = $stm->queryString;

            // log the query
            Hayate_Log::info($stm->queryString);
            // reset query builder's properties after each query
            $this->reset();
            //
            if (preg_match('/^DELETE|INSERT|UPDATE/i', $query) != 1)
            {
                return new Hayate_Database_Iterator($stm, $model, $this->fetchMode);
            }
            return $stm->rowCount();
        }
        catch (PDOException $ex) {
            throw new Hayate_Database_Exception($ex);
        }
        catch (Exception $ex) {
            throw new Hayate_Database_Exception($ex);
        }
    }

    public function quote($value, $parameter_type = PDO::PARAM_STR)
    {
        switch(true)
        {
	case is_bool($value):
	    return parent::quote($value, PDO::PARAM_BOOL);
	case is_null($value):
	    return parent::quote($value, PDO::PARAM_NULL);
	case is_int($value):
	    return parent::quote(intval($value), PDO::PARAM_INT);
	case is_numeric($value):
	    return parent::quote(intval($value), PDO::PARAM_INT);
	case is_string($value):
	    return parent::quote($value, PDO::PARAM_STR);
	default:
	    return parent::quote($value, $parameter_type);
        }
    }

    public function lastQuery()
    {
        return $this->lastQuery;
    }

    public function lastInsertId($name = null)
    {
        if (is_null($name))
        {
            $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
            if (strcasecmp($driver, 'pgsql') == 0)
            {
                $stm = $this->query('SELECT LASTVAL() AS last_id');
                $last_id = $stm->fetch(PDO::FETCH_ASSOC);
                return $last_id['last_id'];
            }
        }
        return parent::lastInsertId($name);
    }

    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * Called by the "get" and "getAll" methods
     */
    protected function compileSelect()
    {
        $sql = ($this->distinct === true) ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= (count($this->select) > 0) ? implode(',', $this->select) : '*';
        $sql .= (count($this->from) > 0) ? ' FROM '.implode(',', $this->from) : '';
        $sql .= (count($this->join) > 0) ? ' '.implode(' ', $this->join) : '';
        $sql .= (count($this->where) > 0) ? ' WHERE '.implode(' ', $this->where) : '';
        $sql .= (count($this->groupby) > 0) ? ' GROUP BY '.implode(',', $this->groupby) : '';
        $sql .= (count($this->orderby) > 0) ? ' ORDER BY '.implode(', ', $this->orderby) : '';
        $sql .= $this->compileLimit();
        return $sql;
    }

    protected function compileLimit()
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

    protected function compileWhere($field, $value, $opt)
    {
        if (is_array($field))
        {
            foreach ($field as $key => $value)
	    {
                $this->compileWhere($key, $value, $opt);
            }
        }
        else {
            if (null === $value)
            {
                if (! $this->hasOperator($field)) {
                    $field .= ' IS ';
                }
                $value = null;
            }
            else if (is_bool($value))
            {
                if (! $this->hasOperator($field)) {
                    $field .= '=';
                }
                $value = (int)$value;
            }
            if (! $this->hasOperator($field)) {
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

    protected function hasOperator($opt)
    {
        return (bool)preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?|BETWEEN/i', trim($opt));
    }
}
