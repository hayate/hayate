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
 */
abstract class Hayate_ORM
{
    protected $db;
    protected $dbconfig = 'default';
    protected $fields = array();
    protected $primary_key = 'id';
    protected $foreign_key;
    protected $loaded;
    protected $table_name;
    protected $orderby = array();


    public function __construct($id = null)
    {
        if (! isset($this->table_name)) {
            $this->table_name = strtolower(str_ireplace('_model', '', get_class($this)));
        }
        $this->setFields();
        $this->db = Hayate_Database::instance($this->dbconfig);
        $this->loaded = false;
        $this->orderby = array($this->primary_key => 'ASC');
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
        $this->db->where($this->unique_key($id), $id)
            ->get($this->table_name, $this);
        $this->loaded = true;
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
                $this->db->insert($this->table_name, $this->as_array(false));
                $this->{$this->primary_key} = $this->db->lastInsertId();
            }
            else
            {
                $this->db->update($this->table_name, $this->as_array(false),
                                  array($this->primary_key => $this->{$this->primary_key}));
            }
            $this->loaded = true;
            return $this;
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
        catch (Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
    }

    public function unique_key($id)
    {
        return $this->primary_key;
    }

    public function find($id = null)
    {
        try {
            if (null !== $id)
            {
                $this->load($id);
            }
            else {
                $this->db->get($this->table_name, $this);
            }
            return $this;
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
    }

    public function find_all($limit = null, $offset = null)
    {
        try {
            return $this->db->orderby($this->orderby)
                ->get_all($this->table_name, get_class($this), $limit, $offset);
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
                    $this->db->get($this->table_name, $this);
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

    /**
     * @param bool $primary_key True by default, if true include the primary key
     *
     * @return array The model as associative array
     */
    public function as_array($primary_key = true)
    {
        $ret = array();
        foreach ($this->fields as $field => $obj)
        {
            if (! $primary_key && ($field == $this->primary_key)) continue;
            $ret[$field] = $obj->value;
        }
        return $ret;
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
        throw new Hayate_Database_Exception(sprintf(_('method %s does not exists'), $method));
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name]->value;
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
            throw new Hayate_ORM_Exception(sprintf(_('Field %s does not exists.'), $name));
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
        return print_r($this->fields, true);
    }

    protected function addField($name, $value = null)
    {
        $this->fields[$name] = new stdClass();
        $this->fields[$name]->default = $value;
        $this->fields[$name]->value = $value;
    }
}