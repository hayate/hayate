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
abstract class ORM
{
    protected $db;
    protected $dbconfig = 'default';
    protected $fields = array();
    protected $private_key = 'id';
    protected $foreign_key;
    protected $loaded;
    protected $table_name;
    protected $orderby = array();


    public function __construct($id = null)
    {
        if (! isset($this->table_name)) {
            $this->table_name = strtolower(str_ireplace('_model', '', __CLASS__));
        }
        $this->db = Database::instance($dbconfig);
        $this->loaded = false;

        if ($id instanceof stdClass)
        {
            $this->result($id);
        }
        else if (null !== $id)
        {
            $this->load($id);
        }
    }

    public static function factory($name, $id = null)
    {
        $classname = ucfirst(strtolower($name)).'_Model';
        return new $classname($id);
    }

    public function unique_key($id)
    {
        return $this->private_key;
    }

    public function load($id)
    {
        try {
            // store fetch mode
            $mode = $this->db->fetchMode();
            // set fetch mode
            $this->db->fetchMode(PDO::FETCH_OBJ);
            $ret = $this->db->where($this->unique_key($id), $id)
                ->get_first($this->table_name);
            // restore fetch mode
            $this->db->fetchMode($mode);
            return $this->result($ret);
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
    }

    public function find($id = null)
    {
        try {
            if (null !== $id)
            {
                $this->load($id);
                return $this;
            }
            // store fetch mode
            $mode = $this->db->fetchMode();
            // set fetch mode
            $this->db->fetchMode(PDO::FETCH_OBJ);
            $ret = $this->db->get_first($this->table_name);
            // restore fetch mode
            $this->db->fetchMode($mode);
            return $this->result($ret);
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
    }

    public function find_all($limit = null, $offset = null)
    {
        try {
            // store fetch mode
            $mode = $this->db->fetchMode();
            // set fetch mode
            $this->db->fetchMode(PDO::FETCH_OBJ);
            $ret = $this->db->orderby($this->orderby)
                ->get($this->table_name, $limit, $offset);
            // restore fetch mode
            $this->db->fetchMode($mode);
            return $this->result($ret);
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
    }

    public function delete($id = null)
    {
        try {
            if (null !== $id)
            {
                $this->delete($this->table_name, array($this->unique_key($id) => $id));
            }
            else {
                if (! $this->loaded())
                {
                    // store fetch mode
                    $mode = $this->db->fetchMode();
                    // set fetch mode
                    $this->db->fetchMode(PDO::FETCH_OBJ);
                    $ret = $this->db->get_first($this->table_name);
                    // restore fetch mode
                    $this->db->fetchMode($mode);
                    $this->result($ret);
                }
                $pk = $this->primary_key;
                $this->delete($this->table_name, array($this->primary_key => $this->$pk));
            }
            return $this;
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
    }

    public function loaded()
    {
        if (! $this->loaded)
        {
            $pk = $this->primary_key;
            $this->loaded = isset($this->$pk);
        }
        return $this->loaded;
    }

    public function __call($method, array $args)
    {
        if (in_array($method, array('where','orwhere','distinct','orderby','groupby')) &&
            is_callable(array($this->db, $method)))
        {
            $call = new ReflectionMethod($this->db, $method);
            $call->invokeArgs($this->db, $args);
            return $this;
        }
        throw new Hayate_ORM_Exception(sprintf(_('method %s does not exists'), $method));
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name];
        }
        throw new Hayate_ORM_Exception(sprintf(_('Field %s does not exists.'), $name));
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->fields))
        {
            $this->fields[$name] = $value;
        }
        else {
            throw new Hayate_ORM_Exception(sprintf(_('Field %s does not exists.'), $name));
        }
    }

    public function __isset($name)
    {
        if (! array_key_exists($name, $this->fields))
        {
            return false;
        }
        return ! empty($this->fields[$name]);
    }

    public function __unset($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            $this->fields[$name] = null;
        }
    }

    protected function add_field($name, $value = null)
    {
        $this->fields[$name] = $value;
    }

    protected function result($result)
    {
        if ($result instanceof stdClass)
        {
            foreach (array_keys($this->fields) as $field)
            {
                $this->fields[$field] = $result->$field;
            }
            $this->loaded = true;
            return $this;
        }
        else if (is_array($result))
        {
            foreach (array_keys($this->fields) as $field)
            {
                if (isset($result[$field]))
                {
                    $this->fields[$field] = $result[$field];
                }
            }
            $this->loaded();
            return $this;
        }
        else if ($result instanceof Hayate_Database_Iterator)
        {
            return new Hayate_ORM_Iterator($result,__CLASS__);
        }
        else if (null === $result)
        {
            return $this;
        }
        throw new HayateException(sprintf(_('%s not supported.'), gettype($result)));
    }
}