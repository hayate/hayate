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
 * @version 1.0
 */
class Hayate_Database_Mysql extends Hayate_Database_Abstract
{
    public function __construct(stdClass $dbConf)
    {
        $dsn = 'mysql:host='.$dbConf->host;
        $dsn .= is_numeric($dbConf->port) ? ';port='.$dbConf->port;
        $dsn .= (!empty($dbConf->database)) ? ';dbname='.$dbConf->database;
        parent::__construct($dsn, $dbConf->user, $dbConf->pass);
    }
}