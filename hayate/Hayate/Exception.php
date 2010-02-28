<?php
/**
 * Hayate Framework
 * Copyright 2010 Andrea Belvedere
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
 * @version $Id: Exception.php 38 2010-02-07 12:45:40Z andrea $
 */
class Hayate_Exception extends Exception
{
    public function __construct($errstr = '', $errno = 0, $errfile = null, $errline = -1)
    {
        parent::__construct($errstr, $errno);
        if (null !== $errfile) {
            $this->file = $errfile;
        }
        if ($errline != -1) {
            $this->line = $errline;
        }
    }
}