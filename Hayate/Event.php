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
class Hayate_Event
{
    protected static $events = array();

    /**
     * @param string $name The name of the Event
     * @param string|array $callback The callback method to register
     * @return void
     */
    public static function add($name, $callback)
    {
        self::$events[$name][] = $callback;
    }

    /**
     * @param string $name The name of the Event to remove
     * @retur void
     */
    public static function remove($name)
    {
        if (isset(self::$events[$name])) {
            unset(self::$events[$name]);
        }
    }

    /**
     * @param string $name The name of the Event
     * @param array $params Parameter(s) to pass to the callback method to run
     * @return mixed
     */
    public static function run($name, array $params = array())
    {
        if (isset(self::$events[$name]) && is_array(self::$events[$name]))
        {
            foreach (self::$events[$name] as $event)
            {
                if (is_callable($event))
                {
                    $ret = call_user_func_array($event, $params);
                    // return if we have a return value, else continue
                    // processing events
                    if (isset($ret))
                    {
                        self::remove($name);
                        return $ret;
                    }
                }
            }
            self::remove($name);
        }
        return null;
    }
}
