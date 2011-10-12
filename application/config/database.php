<?php
/**
 * Hayate Framework
 * Copyright 2009 Andrea Belvedere
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
 * Set database options
 *
 * Hayate supports connections to multiple databases
 * each connection setting should have the format:
 * $config['name_of_connection'] = array('dsn' => ....);
 *
 * default is used each time the database object is instantiated
 * without specifying a connection name
 *
 * Hayate usese PDO (PHP Data Objects) as database abstraction layer
 */

// dsn examples:
// mysql  - mysql:host=127.0.0.1;port=3306;dbname=hayate
// pgsql  - pgsql:host=127.0.0.1 port=5432 dbname=hayate
// oracle - oci:dbname=//127.0.0.1:1521/hayate
// sqlite - sqlite:/path/to/hayate.db or sqlite::memory
$config['default'] = array('dsn' => 'mysql:host=localhost;port=3306;dbname=visualdesign',
        // username
        'username' => 'name of user',
        // password
        'password' => 'the password',
        // connection charset encoding
        // corrently supported on
        // mysql,pgsql,sqlite,sqlite2
        'charset' => 'utf8',
        // return rows as stdClass if true or
        // associative array if false
        'object' => true,
        // persistent database connections
        // only implemented when using mysql driver
        'persistent' => false,
        // buffered query, only works with mysql
        // use with caution, as large queries can
        // be resource expensive
        'buffered' => true,
        // timeout uses PDO::ATTR_TIMEOUT
        // integer, number of seconds
        // if a connection can't be established
        // is useful to know why rather then let
        // the script timeout while trying to
        // get the connection.
        // Note: desired effect might be different
        // depending on used driver
        'timeout' => 10
);
