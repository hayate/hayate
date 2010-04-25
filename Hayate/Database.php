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
class Database
{
    protected static $db = array();

    private function __construct() {}

    public function __destruct()
    {
        foreach (self::$db as &$pdo)
        {
            if ($pdo instanceof Hayate_Database_Pdo)
            {
                Log::info('Closing db connection.');
                $pdo = null;
            }
        }
    }

    public static function instance($name = 'default')
    {
        if (isset(self::$db[$name]))
        {
            return self::$db[$name];
        }
        $config = Config::instance()->get('database.'.$name, null);
        if (null === $config) {
            throw new Hayate_Database_Exception(sprintf(_('Database config "%s" not found.'), $name));
        }
        self::$db[$name] = new Hayate_Database_Pdo(new Registry($config));
        return self::$db[$name];
    }
}