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
    public static function add($name, $callback, array $params = array(), &$ret = NULL)
    {
        $event = new stdClass();
        $event->callback = $callback;
        $event->params = $params;
        $event->ret = &$ret;
        self::$events[$name][] = $event;
    }

    /**
     * @param string $name The name of the Event to remove
     * @return void
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
     * @return void
     */
    public static function run($name, array $params = array())
    {
        if (isset(self::$events[$name]) && is_array(self::$events[$name]))
        {
            while (null !== ($event = array_shift(self::$events[$name])))
            {
                $params = array_merge($params, $event->params);

                if (2 == count($event->callback))
                {
                    $obj = $event->callback[0];
                    $method = $event->callback[1];

                    switch (count($params))
                    {
                    case 0:
                        $event->ret = $obj->$method();
                        break;
                    case 1:
                        $event->ret = $obj->$method($params[0]);
                        break;
                    case 2:
                        $event->ret = $obj->$method($params[0], $params[1]);
                        break;
                    case 3:
                        $event->ret = $obj->$method($params[0], $params[1], $params[2]);
                        break;
                    case 4:
                        $event->ret = $obj->$method($params[0], $params[1], $params[2], $params[3]);
                        break;
                    case 5:
                        $event->ret = $obj->$method($params[0], $params[1], $params[2], $params[3], $params[5]);
                        break;
                    default:
                        $event->ret = call_user_func_array($event->callback, $params);
                    }
                }
                else {
                    $event->ret = call_user_func_array($event->callback, $params);
                }
            }
            self::remove($name);
        }
    }
}
