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
abstract class Database
{
    protected static $instance = null;
    protected $db;

    protected function __construct()
    {
        $this->db = $this->getDB();

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

        $dbconf = new stdClass();
        $dbconf->user = isset($database['connection']['user']) ? $database['connection']['user'] : null;
        $dbconf->pass = isset($database['connection']['pass']) ? $database['connection']['pass'] : null;
        $dbconf->host = isset($database['connection']['host']) ? $database['connection']['host'] : '';
        $dbconf->port = isset($database['connection']['port']) ? $database['connection']['port'] : null;
        $dbconf->dbname = isset($database['connection']['database']) ? $database['connection']['database'] : '';

        // hayate supported db
        switch ($dsn)
        {
        case 'mysql':
        case 'mysqli':
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
        return new $classname($dbconf);
    }

    /*
    protected function connect()
    {
        // build DSN
        switch ($dsn)
        {
        case 'mysql':
        case 'mysqli':
        case 'sybase':
        case 'mssql':
        case 'dblib':
            {
                $dsn .= ':host='.$host;
                $dsn .= (is_numeric($port)) ? ';port='.$port : '';
                $dsn .= (!empty($dbname)) ? ';dbname='.$dbname : '';
            }
        break;
        case 'pgsql':
            {
                $dsn .= ':host='.$host;
                $dsn .= (is_numeric($port)) ? ' port='.$port : '';
                $dsn .= (!empty($dbname)) ? ' dbname='.$dbname : '';
            }
        break;
        case 'oci':
            {
                if (empty($host)) {
                    $dsn .= ':'.$dbname;
                }
                else {
                    $dsn .= ':dbname=//'.$host;
                    $dsn .= (!empty($port)) ? ':'.$port;
                    $dsn .= '/'.$dbname;
                }
            }
        case 'sqlite':
        case 'sqlite2':
            {
                $dsn .= ':'.$dbname;
            }
        default:
            throw new HayateException(sprintf(_('Driver "%s" currently not supported'), $dsn));
        }
    }
    */
}