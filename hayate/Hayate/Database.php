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
    protected $conn;

    protected function __construct()
    {
        $config = Config::instance()->get('database');
        if (! is_array($config)) {
            throw new Hayate_Database_Exception(_('Missing database configuration.'));
        }
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getDB()
    {
        // load database config
        $database = Config::instance()->config->get('database');
        // find requested driver
        $driver = (isset($database['connection']) && isset($database['connection']['driver'])) ?
            $database['connection']['driver'] : null;
        if (null === $driver)
        {
            throw new HayateException(_('Missing database configuration details'));
        }
        // list installed drivers
        $drivers = PDO::getAvailableDrivers();
        $dsn = strtolower($dsn);
        // check if requested driver is available
        if (! in_array($dsn, $drivers))
        {
            throw new HayateException(sprintf(_('Driver "%s" not installed'), $dsn));
        }

        // hayate supported db
        switch ($dsn)
        {
            case 'mysql':
            case 'mysqli':
                $classname = 'Hayate_Database_Mysql';
            break
            case 'sybase':
            case 'mssql':
            case 'dblib':
            case 'pgsql':
            case 'oci':
            case 'sqlite':
            case 'sqlite2':
                $classname = 'Hayate_Database_'.ucfirst($dsn);
            default:
                throw new HayateException(sprintf(_('Driver "%s" currently not supported'), $dsn));
        }
        return new $classname(new ArrayObject($database, ArrayObject::ARRAY_AS_PROPS));
    }

    public function from($table)
    {
        $this->db->from($table);
    }

    public function where($field, $value = null)
    {
        $this->db->where($field, $value);
    }

    public function join($table, $field, $value = null)
    {
        $this->db->join($table, $field, $value);
    }

    public function groupby($field)
    {
        $this->db->groupby($field);
    }

    public function orderby($field, $direction)
    {
        $this->db->orderby($field, $direction);
    }

    public function limit($offset, $count = null)
    {
        $this->db->limit($offset, $count);
    }

    public function find()
    {
        return $this->db->find();
    }

    public function findAll($offset = 0, $count = null)
    {
        return $this->db->findAll($offset, $count);
    }

    public function query($query, $params)
    {
        return $this->db->query($query, $params);
    }

    public function insert($table, $fields, $values)
    {
        return $this->db->insert($table, $fields, $values);
    }

    public function update($table, $fields, $values)
    {
        return $this->db->update($table, $fields, $values);
    }
}