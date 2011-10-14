<?php
/**
 * Hayate Framework
 * Copyright 2009-2011 Andrea Belvedere
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
 */
abstract class Hayate_ORM
{
    protected $db;
    protected $dbconfig = 'default';
    protected $fields = array();
    protected $primary_key = 'id';
    protected $loaded;
    protected $table_name;
    protected $class_name;
    protected $orderby = array();
    protected $hasOne = array();
    protected $hasMany = array();
    protected $cache = array();


    public function __construct($id = null)
    {
        if (! isset($this->table_name))
        {
            $this->table_name = Hayate_Inflector::pluralize(strtolower(str_ireplace('_model', '', get_class($this))));
        }
        if (!isset($this->class_name))
        {
            $this->class_name = strtolower(str_ireplace('_model', '', get_class($this)));
        }
        $this->setFields();
        $this->db = Hayate_Database::getInstance($this->dbconfig);
        $this->loaded = false;
        if (null !== $id)
        {
            $this->load($id);
        }
    }

    public static function factory($name, $id = null)
    {
        $classname = ucfirst(strtolower($name)).'_Model';
        return new $classname($id);
    }

    /**
     * Use this method to set models fields and optionally default
     * values using the add_field method for each field
     *
     * @return void
     */
    abstract public function setFields();


    public function load($id)
    {
        $this->db->where($this->primaryField($id), $id)
            ->get($this->table_name, $this);
        $this->loaded = !empty($this->{$this->primary_key});
    }

    public function loaded()
    {
        if (! $this->loaded)
        {
            $this->loaded = !empty($this->{$this->primary_key});
        }
        return $this->loaded;
    }

    /**
     * INSERTs or UPDATEs a model
     *
     * A model in INSERTed when its primary key is not set otherwise
     * is UPDATEd
     *
     * @return ORM This model
     */
    public function save()
    {
        try {
            if (empty($this->{$this->primary_key}))
            {
                $this->db->insert($this->table_name, $this->asArray(false));
                $this->{$this->primary_key} = $this->db->lastInsertId();
            }
            else
            {
                $this->db->update($this->table_name, $this->asArray(false),
                                  array($this->primary_key => $this->{$this->primary_key}));
            }
            $this->loaded = true;
            return $this;
        }
        catch (Hayate_Database_Exception $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new Hayate_Database_Exception($ex);
        }
    }

    public function primaryField($id)
    {
        return $this->primary_key;
    }

    public function find($id = null)
    {
        if (null !== $id)
        {
            $this->load($id);
        }
        else {
            $this->db->get($this->table_name, $this);
        }
        return $this;
    }

    public function findAll($limit = null, $offset = null)
    {
        return $this->db->orderby($this->orderby)
            ->getAll($this->table_name, get_class($this), $limit, $offset);
    }

    public function delete($id = null)
    {
        if (null !== $id)
        {
            $this->db->delete($this->table_name, array($this->primaryField($id) => $id));
        }
        else {
            if (! $this->loaded())
            {
                $this->db->get($this->table_name, $this);
            }
            $this->db->delete($this->table_name, array($this->primary_key => $this->{$this->primary_key}));
        }
        return $this;
    }

    public function count(array $where = array())
    {
        return $this->db->count($this->table_name, $where);
    }

    /**
     * @param bool $primary_key True by default, if true include the primary key
     *
     * @return array The model as associative array
     */
    public function asArray($primary_key = true)
    {
        $ret = array();
        foreach ($this->fields as $field => $obj)
        {
            if (! $primary_key && ($field == $this->primary_key))
            {
                continue;
            }
            $ret[$field] = $obj->value;
        }
        return $ret;
    }

    public function fromArray(array $fields)
    {
        foreach ($fields as $name => $value)
        {
            $this->$name = $value;
        }
        if (! empty($this->{$this->primary_key}))
        {
            $this->loaded = true;
        }
        return $this;
    }

    public function __call($method, array $args)
    {
        if (in_array($method, array('where','orwhere','distinct','orderby','groupby','limit')) &&
            is_callable(array($this->db, $method)))
        {
            $call = new ReflectionMethod($this->db, $method);
            $call->invokeArgs($this->db, $args);
            return $this;
        }
        throw new Hayate_Database_Exception(sprintf(_('method %s does not exists'), $method));
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name]->value;
        }
        else if (isset($this->cache[$name]))
        {
            return $this->cache[$name];
        }
        else if (in_array($name, $this->hasOne))
        {
            $fk = $name.'_id';
            $orm = Hayate_ORM::factory($name, $this->$fk);
            if ($orm->loaded())
            {
                $this->cache[$name] = $orm;
                return $orm;
            }
            return null;
        }
        else if (in_array($name, $this->hasMany))
        {
            $fk = $this->class_name.'_id';
            $orm = Hayate_ORM::factory($name)
                ->where($fk, $this->{$this->primary_key})
                ->findAll();
            if ($orm instanceof Hayate_Database_Iterator)
            {
                $this->cache[$name] = $orm;
                return $orm;
            }
            return null;
        }
        throw new Hayate_Database_Exception(sprintf(_('Field %s does not exists.'), $name));
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->fields))
        {
            $this->fields[$name]->value = $value;
        }
        else {
            throw new Hayate_Database_Exception(sprintf(_('Field %s does not exists.'), $name));
        }
    }

    public function __isset($name)
    {
        if (! array_key_exists($name, $this->fields))
        {
            return false;
        }
        return ! empty($this->fields[$name]->value);
    }

    public function __unset($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            $this->fields[$name]->value = $this->fields[$name]->default;
        }
    }

    public function __toString()
    {
        return print_r($this->asArray(true), true);
    }

    protected function addField($name, $value = null)
    {
        $this->fields[$name] = new stdClass();
        $this->fields[$name]->default = $value;
        $this->fields[$name]->value = $value;
    }
}
