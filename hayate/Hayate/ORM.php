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
    protected $config = 'default';
    protected $fields = array();
    protected $private_key = 'id';
    protected $foreign_key;
    protected $loaded;
    protected $table_name;

    public function __construct()
    {
        if (! isset($this->table_name)) {
            $this->table_name = strtolower(str_ireplace('_model', '', __CLASS__));
        }
        $this->db = Database::instance($config);
        $this->loaded = false;
    }

    public static function factory($name, $id = null)
    {
        $classname = ucfirst(strtolower($name)).'_Model';
        $orm = new $classname();
        if (null !== $id) {
            return $orm->load($id);
        }
        return $orm;
    }

    public function unique_key($id)
    {
        return $this->private_key;
    }

    public function load($id)
    {
        try {
            $this->db->fetchMode(PDO::FETCH_ASSOC);
            $ret = $this->db->where($this->unique_key($id), $id)
                ->get_first($this->table_name);
            foreach ($ret as $field => $value)
            {
                $this->$field = $value;
            }
        }
        catch (Hayate_Database_Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
        catch (Exception $ex) {
            throw new Hayate_ORM_Exception($ex);
        }
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

    protected function add_field($name, $value = null)
    {
        $this->fields[$name] = $value;
    }
}