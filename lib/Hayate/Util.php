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
 * The Router class finds the module, controller and action to be
 * invoked by the dispatcher.
 *
 * @package Hayate
 */
class Hayate_Util
{
    /**
     * @return int UTC time in seconds
     */
    public static function TimeUTC()
    {
        $diff = date('Z');
        if ($diff < 0)
        {
            return date('U', time() + $diff);
        }
        return date('U', time() - $diff);
    }
}